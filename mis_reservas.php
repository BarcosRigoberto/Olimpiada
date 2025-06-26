<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que este archivo contiene tu conexión $mysqli

// Verifica si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Si no está logueado, redirigir al login
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para ver tus reservas."));
    exit();
}

// Obtener el ID del usuario logueado
$usuario_id = $_SESSION['id']; // Asumo que tu sesión guarda el ID del usuario como $_SESSION['id']

$pageTitle = "Mis Reservas";
$activePage = "reservas"; // Define esta variable para que el header pueda marcar la página activa si tienes navegación
require_once 'header.php'; // Incluye tu encabezado HTML

$pedidos = [];
$mensaje = '';

// Consulta para obtener los pedidos del usuario
// Unir con la tabla 'detalle_pedido' y 'paquetes' para obtener los nombres de los paquetes.
// Consideramos que un pedido puede tener múltiples paquetes, por eso agrupamos por pedido.
$sql = "SELECT 
            p.id AS pedido_id, 
            p.fecha_pedido, 
            p.total_pedido, 
            p.estado,
            GROUP_CONCAT(CONCAT(dp.cantidad, 'x ', paq.nombre) SEPARATOR ' <br> ') AS paquetes_comprados_str
        FROM 
            pedidos p
        JOIN 
            detalle_pedido dp ON p.id = dp.pedido_id
        JOIN 
            paquetes paq ON dp.paquete_id = paq.id
        WHERE 
            p.usuario_id = ?
        GROUP BY 
            p.id, p.fecha_pedido, p.total_pedido, p.estado
        ORDER BY 
            p.fecha_pedido DESC";

$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $pedidos[] = $row;
        }
    } else {
        $mensaje = '<p class="info-msg">Aún no tienes reservas realizadas.</p>';
    }
    $stmt->close();
} else {
    $mensaje = '<p class="error-msg">Error al preparar la consulta de reservas: ' . $mysqli->error . '</p>';
}

$mysqli->close(); // Cerrar la conexión
?>

<link rel="stylesheet" type="text/css" href="indstyle.css"> <style>
    /* Estilos básicos para la página de reservas */
    .reservas-container {
        max-width: 900px;
        margin: 50px auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .reservas-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }
    .reservas-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .reservas-table th, .reservas-table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    .reservas-table th {
        background-color: #f2f2f2;
        color: #555;
    }
    .reservas-table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .reservas-table tbody tr:hover {
        background-color: #e9e9e9;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        color: white;
    }
    .status-pendiente { background-color: #ffc107; color: #333;} /* Amarillo */
    .status-completado { background-color: #28a745; } /* Verde */
    .status-cancelado { background-color: #dc3545; } /* Rojo */
    .info-msg, .error-msg {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }
    .info-msg {
        background-color: #e0f7fa;
        color: #007bff;
        border: 1px solid #007bff;
    }
    .error-msg {
        background-color: #f8d7da;
        color: #dc3545;
        border: 1px solid #dc3545;
    }
</style>

<div class="reservas-container">
    <h2>Mis Reservas</h2>

    <?php if ($mensaje): ?>
        <?php echo $mensaje; ?>
    <?php else: ?>
        <table class="reservas-table">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha Pedido</th>
                    <th>Paquetes</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pedido['pedido_id']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                        <td><?php echo $pedido['paquetes_comprados_str']; ?></td>
                        <td>$<?php echo number_format($pedido['total_pedido'], 0, ',', '.'); ?> USD</td>
                        <td>
                            <?php 
                                $status_class = '';
                                if ($pedido['estado'] == 'Pendiente') {
                                    $status_class = 'status-pendiente';
                                } else if ($pedido['estado'] == 'Completado') {
                                    $status_class = 'status-completado';
                                } else if ($pedido['estado'] == 'Cancelado') {
                                    $status_class = 'status-cancelado';
                                }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($pedido['estado']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>