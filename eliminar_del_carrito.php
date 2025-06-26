<?php
// --- INICIO DE LÍNEAS DE DEPURACIÓN (TEMPORALES) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (ob_get_level()) ob_end_clean();
// --- FIN DE LÍNEAS DE DEPURACIÓN ---

session_start();
require_once 'conexion.php'; // Asegúrate de que esta conexión se abre correctamente

header('Content-Type: application/json');

if (!isset($_SESSION['session_id_carrito'])) {
    $_SESSION['session_id_carrito'] = session_id(); 
}
$sessionIdCarrito = $_SESSION['session_id_carrito']; // Asegura que tienes el ID de sesión del carrito

$success = false;
$message = '';
$newCartItemsCount = 0; // Inicializamos el contador de ítems para devolverlo
$newCartTotal = 0;      // Inicializamos el nuevo total para devolverlo

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];

    if (!is_numeric($itemId) || $itemId <= 0) {
        $message = 'ID de ítem inválido.';
    } else {
        // PRECAUCIÓN: Elimina el ítem del carrito, asegurándose de que pertenezca a la sesión actual.
        // Esto previene que un usuario elimine ítems de otro carrito.
        $stmt = $mysqli->prepare("DELETE FROM carrito WHERE id = ? AND session_id = ?"); 
        
        if ($stmt === false) {
            $message = 'Error de preparación de consulta DELETE: ' . $mysqli->error;
        } else {
            $stmt->bind_param("is", $itemId, $sessionIdCarrito); 
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = true;
                    $message = 'Paquete eliminado del carrito.';
                } else {
                    $message = 'No se encontró el paquete en el carrito para esta sesión o ya había sido eliminado.';
                }
            } else {
                $message = 'Error al ejecutar DELETE: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
} else {
    $message = 'Solicitud inválida.';
}

// --- CALCULAR EL NUEVO CONTEO DE ÍTEMS Y EL NUEVO TOTAL DEL CARRITO DESPUÉS DE LA OPERACIÓN ---
// Esta consulta ahora obtiene AMBOS valores: el total de ítems (sumando cantidades)
// y el total monetario (sumando cantidad * precio)
$stmt = $mysqli->prepare("SELECT SUM(c.cantidad) AS total_items, SUM(c.cantidad * p.precio) AS total_precio 
                           FROM carrito c 
                           JOIN paquetes p ON c.paquete_id = p.id 
                           WHERE c.session_id = ?");

if ($stmt) {
    $stmt->bind_param("s", $sessionIdCarrito);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $newCartItemsCount = $row['total_items'] ?? 0;
    $newCartTotal = $row['total_precio'] ?? 0; // Asignamos el nuevo total monetario
    
    $stmt->close();
} else {
    // Si la preparación falla, registramos el error y aseguramos que los valores sean 0
    error_log("Error al preparar la consulta de conteo/total: " . $mysqli->error);
    $newCartItemsCount = 0;
    $newCartTotal = 0;
}
// --- FIN CÁLCULO NUEVO TOTAL Y CONTEO ---

// Siempre verifica que $mysqli sea un objeto antes de intentar cerrarlo
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close(); // Cerrar la conexión
}

echo json_encode([
    'success' => $success,
    'message' => $message,
    'newCartCount' => $newCartItemsCount, // Devuelve el nuevo conteo de ítems
    'newCartTotal' => $newCartTotal      // ¡Devuelve el nuevo total monetario!
]);
exit;
?>