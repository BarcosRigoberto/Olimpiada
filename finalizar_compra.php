<?php
session_start();
require_once 'conexion.php'; 
require_once 'header.php'; // Incluir header aquí para usarlo si es necesario.

// Bloquear acceso a vendedores y administradores
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'vendedor' || $_SESSION['rol'] === 'admin')) {
        header("Location: index.php?error=" . urlencode("Las cuentas de vendedor y administrador no tienen acceso a la finalización de compra."));
        exit();
    }
}

// Redireccionar si no hay un ID de sesión de carrito
if (!isset($_SESSION['session_id_carrito'])) {
    // Si no hay un ID de sesión para el carrito, no hay carrito activo, redirige al inicio o al carrito vacío
    header('Location: index.php'); // O 'Location: carrito.php' para que vea que está vacío
    exit;
}
$sessionIdCarrito = $_SESSION['session_id_carrito'];

$mensaje = ''; // Para mostrar mensajes al usuario (éxito, error, etc.)
$estado = 'Pendiente'; // Asegúrate de que el estado inicial coincida con tu columna 'estado'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_compra'])) {
    // Recoger y sanitizar los datos del formulario
    $nombre_cliente = $mysqli->real_escape_string(trim($_POST['nombre_cliente'] ?? ''));
    $direccion_cliente = $mysqli->real_escape_string(trim($_POST['direccion_cliente'] ?? ''));
    $telefono_cliente = $mysqli->real_escape_string(trim($_POST['telefono_cliente'] ?? ''));
    $email_cliente = $mysqli->real_escape_string(trim($_POST['email_cliente'] ?? ''));
    
    // Aquí puedes añadir más validaciones de datos (ej. email válido, campos no vacíos)
    if (empty($nombre_cliente) || empty($direccion_cliente) || empty($email_cliente)) {
        $mensaje = '<p class="error-msg">Por favor, complete todos los campos obligatorios.</p>';
    } else {
        // --- CORRECCIÓN AQUÍ: USAR $_SESSION['id'] EN LUGAR DE $_SESSION['usuario_id'] ---
        $usuario_id = $_SESSION['id'] ?? NULL; // Obtener el ID del usuario logueado
        // Si el usuario no está logueado, $usuario_id será NULL.
        // Asegúrate de que tu columna usuario_id en la BD permita NULL si es opcional.
        // Si es obligatoria, deberías añadir una verificación y error si $usuario_id es NULL aquí.
        // if ($usuario_id === NULL) { $mensaje = '<p class="error-msg">Debes iniciar sesión para completar la compra.</p>'; }
        // ----------------------------------------------------------------------------------

        // Calcular el total_pedido justo antes de guardarlo (para mayor seguridad)
        $total_pedido_db = 0;
        $itemsEnCarritoConfirmacion = [];
        $stmt_confirm = $mysqli->prepare("SELECT c.id AS carrito_item_id, c.paquete_id, c.cantidad, p.nombre, p.precio 
                                             FROM carrito c JOIN paquetes p ON c.paquete_id = p.id 
                                             WHERE c.session_id = ?");
        if ($stmt_confirm) {
            $stmt_confirm->bind_param("s", $sessionIdCarrito);
            $stmt_confirm->execute();
            $res_confirm = $stmt_confirm->get_result();
            while ($row_confirm = $res_confirm->fetch_assoc()) {
                $itemsEnCarritoConfirmacion[] = $row_confirm;
                $total_pedido_db += $row_confirm['precio'] * $row_confirm['cantidad'];
            }
            $stmt_confirm->close();
        }

        if (empty($itemsEnCarritoConfirmacion)) {
            $mensaje = '<p class="error-msg">Tu carrito está vacío, no se puede finalizar la compra.</p>';
            // Podrías redirigir al carrito aquí si quieres
        } else {
            // --- INICIO DE TRANSACCIÓN ---
            $mysqli->begin_transaction();
            try {
                // 1. Insertar el pedido principal en la tabla `pedidos`
                $stmt_pedido = $mysqli->prepare("INSERT INTO pedidos (usuario_id, fecha_pedido, total_pedido, nombre_cliente, direccion_cliente, telefono_cliente, email_cliente, estado) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)");
                if (!$stmt_pedido) {
                    throw new Exception("Error de preparación de pedido: " . $mysqli->error);
                }
                // Los tipos de datos de bind_param deben coincidir con tus columnas.
                // 'i' para usuario_id (INT), 'd' para total_pedido (DECIMAL/DOUBLE), 'ssssss' para los strings
                $stmt_pedido->bind_param("idsssss", $usuario_id, $total_pedido_db, $nombre_cliente, $direccion_cliente, $telefono_cliente, $email_cliente, $estado);
                
                if (!$stmt_pedido->execute()) {
                    throw new Exception("Error al insertar pedido: " . $stmt_pedido->error);
                }
                $pedido_id = $mysqli->insert_id; // Obtener el ID del pedido recién insertado
                $stmt_pedido->close();

                // 2. Insertar los detalles del pedido en la tabla `detalle_pedido`
                // ¡Confirmamos que tienes esta tabla porque la usas aquí!
                foreach ($itemsEnCarritoConfirmacion as $item) {
                    $stmt_detalle = $mysqli->prepare("INSERT INTO detalle_pedido (pedido_id, paquete_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    if (!$stmt_detalle) {
                        throw new Exception("Error de preparación de detalle: " . $mysqli->error);
                    }
                    $stmt_detalle->bind_param("iiid", $pedido_id, $item['paquete_id'], $item['cantidad'], $item['precio']);
                    if (!$stmt_detalle->execute()) {
                        throw new Exception("Error al insertar detalle: " . $stmt_detalle->error);
                    }
                    $stmt_detalle->close();
                }
                
                // 3. Vaciar el carrito del usuario después de que el pedido se ha guardado
                $stmt_vaciar = $mysqli->prepare("DELETE FROM carrito WHERE session_id = ?");
                if (!$stmt_vaciar) {
                    throw new Exception("Error de preparación al vaciar carrito: " . $mysqli->error);
                }
                $stmt_vaciar->bind_param("s", $sessionIdCarrito);
                if (!$stmt_vaciar->execute()) {
                    throw new Exception("Error al vaciar carrito: " . $stmt_vaciar->error);
                }
                $stmt_vaciar->close();

                $mysqli->commit(); // Confirmar la transacción: todo fue bien

                $mensaje = '<p class="success-msg">¡Gracias por tu compra! Tu pedido ha sido recibido con ID: #' . $pedido_id . '</p>';
                // Una vez que el carrito se vacía y el pedido se confirma, podrías redirigir a una página de "gracias"
                // header('Location: gracias_por_comprar.php?pedido=' . $pedido_id); exit;
                
                // Si no rediriges, limpia la sesión del carrito para que no intente mostrar items vacíos
                unset($_SESSION['session_id_carrito']); 

            } catch (Exception $e) {
                $mysqli->rollback(); // Revertir la transacción: algo salió mal
                $mensaje = '<p class="error-msg">Hubo un error al procesar tu compra. Por favor, inténtalo de nuevo. ' . $e->getMessage() . '</p>';
                error_log("Error en finalizar_compra: " . $e->getMessage()); // Para depuración en el log del servidor
            }
        }
    }
}

// --- 2. OBTENER Y MOSTRAR LOS ÍTEMS DEL CARRITO (para mostrar el resumen antes de confirmar) ---
$total = 0;
$itemsEnCarritoActual = [];

// Solo intenta obtener los ítems si todavía existe un session_id_carrito
if (isset($_SESSION['session_id_carrito'])) {
    $stmt = $mysqli->prepare("SELECT c.id AS carrito_item_id, c.paquete_id, c.cantidad, p.nombre, p.precio, p.imagen 
                                 FROM carrito c 
                                 JOIN paquetes p ON c.paquete_id = p.id 
                                 WHERE c.session_id = ?");

    if ($stmt === false) {
        $mensaje .= "<p class='error-msg'>Error de preparación de consulta al cargar carrito: " . $mysqli->error . "</p>";
    } else {
        $stmt->bind_param("s", $sessionIdCarrito);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $itemsEnCarritoActual[] = $row;
            $total += $row['precio'] * $row['cantidad'];
        }
        $stmt->close();
    }
}

?>

<link rel="stylesheet" type="text/css" href="indstyle.css">
<style>
    /* Estilos específicos para esta página si los necesitas, o reutiliza los de indstyle.css */
    .finalizar-compra-container {
        margin-top: 5px;
        text-align: center;
        max-width: 800px;
        margin: 100px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        font-family: 'Poppins', sans-serif;
    }
    .resumen-items {
        text-align: left;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
        padding-bottom: 20px;
    }
    .resumen-items h3 {
        color: #333;
        margin-bottom: 15px;
    }
    .resumen-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #f0f0f0;
    }
    .resumen-item:last-child {
        border-bottom: none;
    }
    .resumen-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    .resumen-item-info {
        flex-grow: 1;
    }
    .resumen-total {
        font-size: 1.6em;
        font-weight: 700;
        color: #007bff;
        text-align: right;
        margin-top: 20px;
    }
    .formulario-compra {
        text-align: left;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .formulario-compra h3 {
        color: #333;
        margin-bottom: 15px;
    }
    .formulario-compra label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #555;
    }
    .formulario-compra input[type="text"],
    .formulario-compra input[type="email"] {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1em;
    }
    .btn-confirmar {
        background-color: #28a745; /* Verde para confirmar */
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 20px;
    }
    .btn-confirmar:hover {
        background-color: #218838;
    }
    .error-msg {
        color: #dc3545;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .success-msg {
        color: #28a745;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
</style>

<div class="finalizar-compra-container">
    <h2>Finalizar Compra</h2>

    <?php echo $mensaje; // Mostrar mensajes de éxito o error ?>

    <?php if (empty($itemsEnCarritoActual) && !isset($_POST['confirmar_compra'])): // Mostrar si el carrito está vacío y no se acaba de confirmar ?>
        <p>Tu carrito está vacío. No puedes finalizar la compra.</p>
        <p><a href="index.php" class="btn-primary">Volver al inicio</a></p>
    <?php elseif (empty($itemsEnCarritoActual) && isset($_POST['confirmar_compra'])): // Esto se mostrará si el carrito estaba vacío al confirmar ?>
        <p>Tu compra ha sido procesada o el carrito ya estaba vacío.</p>
        <p><a href="index.php" class="btn-primary">Volver al inicio</a></p>
    <?php else: // Si hay ítems en el carrito, muestra el resumen y el formulario ?>
        <div class="resumen-items">
            <h3>Resumen de tu Pedido</h3>
            <?php foreach ($itemsEnCarritoActual as $item): ?>
                <div class="resumen-item">
                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                    <div class="resumen-item-info">
                        <p><strong><?php echo htmlspecialchars($item['nombre']); ?></strong></p>
                        <p>Cantidad: <?php echo htmlspecialchars($item['cantidad']); ?></p>
                        <p>Precio Unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                        <p>Subtotal: $<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <p class="resumen-total">Total del Pedido: $<?php echo number_format($total, 0, ',', '.'); ?> USD</p>
        </div>

        <div class="formulario-compra">
            <h3>Detalles de Envío y Contacto</h3>
            <form action="finalizar_compra.php" method="POST">
                <label for="nombre_cliente">Nombre Completo:</label>
                <input type="text" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido'] ?? ''); ?>" required>

                <label for="direccion_cliente">Dirección de Envío:</label>
                <input type="text" id="direccion_cliente" name="direccion_cliente" required>

                <label for="telefono_cliente">Teléfono de Contacto:</label>
                <input type="text" id="telefono_cliente" name="telefono_cliente">

                <label for="email_cliente">Email de Contacto:</label>
                <input type="email" id="email_cliente" name="email_cliente" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>

                <button type="submit" name="confirmar_compra" class="btn-confirmar">Confirmar Compra</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>