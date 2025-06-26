<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que esto incluye tu conexión $mysqli

header('Content-Type: application/json'); // Asegura que la respuesta sea JSON

// *** VERIFICACIÓN CRÍTICA: SOLO PERMITIR SI EL USUARIO ESTÁ LOGUEADO ***
// ¡Asegúrate de que 'logged_in' o 'usuario_id' se setee en tu login!
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado: Debes iniciar sesión para añadir productos al carrito.']);
    exit; // Detener la ejecución del script
}
// *******************************************************************

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paquete_id = filter_input(INPUT_POST, 'id_paquete', FILTER_VALIDATE_INT);
    $nombre_paquete = filter_input(INPUT_POST, 'nombre_paquete', FILTER_SANITIZE_STRING); // Nota: 'nombre_paquete' y 'precio_paquete' no se usan en este script, solo 'paquete_id' y 'cantidad'
    $precio_paquete = filter_input(INPUT_POST, 'precio_paquete', FILTER_VALIDATE_FLOAT); // Solo se usa el ID del paquete en la lógica del carrito

    // Validaciones básicas de los datos recibidos
    if ($paquete_id === false || $paquete_id === null || 
        // Aunque no se usen, es buena práctica validar si se reciben del frontend
        empty($nombre_paquete) || 
        $precio_paquete === false || $precio_paquete === null) {
        echo json_encode(['success' => false, 'message' => 'Datos de paquete inválidos o incompletos.']);
        exit;
    }

    // Si el usuario está logueado, aseguramos que tenga un session_id_carrito
    if (!isset($_SESSION['session_id_carrito'])) {
        $_SESSION['session_id_carrito'] = session_id(); // Usa el ID de la sesión actual como ID del carrito
    }
    $sessionIdCarrito = $_SESSION['session_id_carrito'];

    $cantidad = 1; // Siempre se añade un producto a la vez

    // Verificar si el paquete ya está en el carrito para esta sesión
    $stmt = $mysqli->prepare("SELECT id, cantidad FROM carrito WHERE session_id = ? AND paquete_id = ?");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta (SELECT): ' . $mysqli->error]);
        exit;
    }
    $stmt->bind_param("si", $sessionIdCarrito, $paquete_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $itemExistente = $res->fetch_assoc();
    $stmt->close();

    $success = false;
    $message = '';

    if ($itemExistente) {
        // El paquete ya está en el carrito, actualizar su cantidad
        $nuevaCantidad = $itemExistente['cantidad'] + $cantidad;
        $stmt = $mysqli->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta (UPDATE): ' . $mysqli->error]);
            exit;
        }
        $stmt->bind_param("ii", $nuevaCantidad, $itemExistente['id']);
        if ($stmt->execute()) {
            $success = true;
            $message = 'Cantidad del paquete actualizada en el carrito.';
        } else {
            $message = 'Error al actualizar la cantidad: ' . $stmt->error;
        }
        $stmt->close();

    } else {
        // El paquete no está en el carrito, insertarlo como nuevo ítem
        $stmt = $mysqli->prepare("INSERT INTO carrito (session_id, paquete_id, cantidad) VALUES (?, ?, ?)");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta (INSERT): ' . $mysqli->error]);
            exit;
        }
        $stmt->bind_param("sii", $sessionIdCarrito, $paquete_id, $cantidad);
        if ($stmt->execute()) {
            $success = true;
            $message = 'Paquete añadido al carrito.';
        } else {
            $message = 'Error al añadir el paquete: ' . $stmt->error;
        }
        $stmt->close();
    }

    // CALCULAR EL NUEVO TOTAL DE ÍTEMS EN EL CARRITO DESPUÉS DE LA OPERACIÓN
    $newCartItemsCount = 0;
    // Es más apropiado sumar la cantidad total de productos, no solo el conteo de ítems distintos.
    $stmt = $mysqli->prepare("SELECT SUM(cantidad) AS total_items FROM carrito WHERE session_id = ?"); 
    if ($stmt) {
        $stmt->bind_param("s", $sessionIdCarrito);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $newCartItemsCount = $row['total_items'] ?? 0;
        $stmt->close();
    }
    // --- FIN CÁLCULO NUEVO TOTAL ---

} else {
    // Si la solicitud no es POST
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}

$mysqli->close(); // Cerrar la conexión a la base de datos

// Devolver la respuesta JSON con el nuevo conteo del carrito
echo json_encode([
    'success' => $success,
    'message' => $message,
    'newCartCount' => $newCartItemsCount 
]);
exit;
?>