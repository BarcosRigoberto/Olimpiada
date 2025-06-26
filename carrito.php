<?php
session_start();
require_once 'conexion.php'; 
require_once 'header.php'; // Lo moví arriba para asegurar que las sesiones y la conexión estén listas.

// Bloquear acceso a vendedores y administradores
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'vendedor' || $_SESSION['rol'] === 'admin')) {
        header("Location: index.php?error=" . urlencode("Las cuentas de vendedor y administrador no tienen acceso al carrito de compras."));
        exit();
    }
}

if (!isset($_SESSION['session_id_carrito'])) {
    $_SESSION['session_id_carrito'] = session_id();
}
$sessionIdCarrito = $_SESSION['session_id_carrito'];

?>
<link rel="stylesheet" type="text/css" href="indstyle.css">

<div class="carrito">
    <h2>Tu carrito</h2>
    <?php
    $total = 0;
    $itemsEnCarrito = [];

    // Consulta para obtener los ítems del carrito para esta sesión
    $stmt = $mysqli->prepare("SELECT c.id AS carrito_item_id, c.paquete_id, c.cantidad, p.nombre, p.precio, p.imagen 
                                 FROM carrito c 
                                 JOIN paquetes p ON c.paquete_id = p.id 
                                 WHERE c.session_id = ?");
    
    if ($stmt === false) {
        echo "<p>Error de preparación de consulta: " . $mysqli->error . "</p>";
    } else {
        $stmt->bind_param("s", $sessionIdCarrito);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $itemsEnCarrito[] = $row;
        }
        $stmt->close();
    }

    if (!empty($itemsEnCarrito)) {
        foreach ($itemsEnCarrito as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $total += $subtotal;
            ?>
            <div class="carrito-item" id="item-<?php echo $item['carrito_item_id']; ?>"> 
                <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="item-imagen">
                <div class="item-info">
                    <p class="item-nombre"><?php echo htmlspecialchars($item['nombre']); ?></p>
                    <p class="item-cantidad">Cantidad: <?php echo htmlspecialchars($item['cantidad']); ?></p>
                    <p class="item-precio">Precio Unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                    <p class="item-subtotal">Subtotal: $<?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                </div>
                <button class="btn-eliminar-item" data-id="<?php echo $item['carrito_item_id']; ?>">
                    <i class="fas fa-times"></i> 
                </button>
            </div>
            <?php
        }
        ?>
        <div class="carrito-resumen">
            <h3 id="total-carrito">Total: $<?php echo number_format($total, 0, ',', '.'); ?> USD</h3>
            <a href='finalizar_compra.php' class="btn btn-primary">Finalizar compra</a>
        </div>
        <?php
    } else {
        echo "<p id='carrito-vacio-mensaje'>Tu carrito está vacío.</p>";
    }
    ?>
</div>

<script>
console.log('DEBUG: Script de carrito.php cargado.'); 

document.addEventListener('DOMContentLoaded', function() {
    console.log('DEBUG: DOMContentLoaded disparado.'); 
    const deleteButtons = document.querySelectorAll('.btn-eliminar-item');
    const carritoDiv = document.querySelector('.carrito');
    
    const testTotalElement = document.getElementById('total-carrito');
    console.log('DEBUG: Intentando obtener #total-carrito al inicio del DOMContentLoaded:', testTotalElement);
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.id; 

            if (!confirm('¿Estás seguro de que quieres eliminar este paquete del carrito?')) {
                return; 
            }
            
            console.log('DEBUG: Enviando solicitud para eliminar item_id:', itemId);
            
            fetch('eliminar_del_carrito.php', {
                method: 'POST', 
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}` 
            })
            .then(response => {
                console.log('DEBUG: Respuesta de la petición Fetch recibida.', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('DEBUG: Datos JSON recibidos:', data);

                if (data.success) { 
                    const itemToRemove = document.getElementById(`item-${itemId}`);
                    if (itemToRemove) {
                        itemToRemove.remove(); 
                        console.log('DEBUG: Elemento removido del DOM.');
                        
                        if (typeof updateCartCount === 'function') {
                            updateCartCount(data.newCartCount);
                            console.log('DEBUG: Contador del header actualizado a:', data.newCartCount);
                        }

                        const totalCarritoElement = document.getElementById('total-carrito');
                        console.log('DEBUG: Elemento total-carrito encontrado (después de eliminar):', totalCarritoElement);

                        if (totalCarritoElement) {
                            const formattedTotal = data.newCartTotal.toLocaleString('es-AR', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).replace(/\./g, ','); 

                            totalCarritoElement.textContent = `Total: $${formattedTotal} USD`;
                            console.log('DEBUG: Total del carrito actualizado a:', formattedTotal);
                        } else {
                            console.error('ERROR: No se encontró el elemento con ID "total-carrito" (después de eliminar).');
                        }

                        const remainingItems = document.querySelectorAll('.carrito-item').length;
                        if (remainingItems === 0) {
                            carritoDiv.innerHTML = '<h2>Tu carrito</h2><p id="carrito-vacio-mensaje">Tu carrito está vacío.</p>';
                            const resumenDiv = document.querySelector('.carrito-resumen');
                            if (resumenDiv) resumenDiv.remove(); 
                            console.log('DEBUG: Carrito marcado como vacío.');
                        } 
                    } else {
                        console.error('ERROR: No se pudo encontrar el elemento del ítem a remover con ID:', `item-${itemId}`);
                    }
                } else { 
                    alert('Error al eliminar el paquete: ' + data.message);
                    console.error('DEBUG: Error reportado por el servidor:', data.message);
                }
            })
            .catch(error => {
                console.error('ERROR en la petición Fetch (catch):', error); 
                alert('Hubo un error al conectar con el servidor.'); 
            });
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>

<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>

<style>
/* ... Tus estilos CSS aquí ... */
    .carrito {
        margin-top: 5px;
        text-align: center;
        max-width: 600px;
        margin: 100px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        font-family: 'Poppins', sans-serif;
    }

    .carrito-item {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        text-align: left;
        position: relative; /* Importante para posicionar el botón de eliminar */
    }

    .carrito-item:last-child {
        border-bottom: none;
    }

    .item-imagen {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }

    .item-info {
        flex-grow: 1;
    }

    .item-nombre {
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .item-cantidad, .item-precio, .item-subtotal {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 3px;
    }

    .item-subtotal {
        font-weight: 700;
        color: #007bff;
    }

    .carrito-resumen {
        margin-top: 30px;
        border-top: 1px solid #eee;
        padding-top: 20px;
        text-align: right;
    }

    .carrito-resumen h3 {
        color: #333;
        font-size: 1.5em;
        margin-bottom: 20px;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    /* Estilos para el botón de eliminar */
    .btn-eliminar-item {
        background: none;
        border: none;
        color: #dc3545; /* Rojo para indicar eliminar */
        font-size: 1.5em;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        transition: background-color 0.2s ease, color 0.2s ease;
        position: absolute; /* Posicionamiento absoluto */
        top: 10px; /* Ajusta la posición vertical */
        right: 10px; /* Ajusta la posición horizontal */
    }

    .btn-eliminar-item:hover {
        background-color: #f8d7da; /* Fondo suave al pasar el mouse */
        color: #c82333; /* Rojo más oscuro */
    }
</style>