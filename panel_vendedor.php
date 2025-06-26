<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que este archivo contiene tu conexión $mysqli

// 1. Verificar autenticación y rol
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['rol'] !== 'vendedor') {
    // Si no está logueado o no es vendedor, redirigir al login
    header("Location: login.php");
    exit();
}

// 2. Obtener el ID del vendedor logueado
$id_vendedor = $_SESSION['id'];
$nombre_vendedor = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);

// 3. Preparar mensaje de éxito/error (del procesamiento de añadir/editar/eliminar)
$mensaje = "";
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alert alert-success mt-3'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alert alert-danger mt-3'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
}

// 4. Consulta para obtener los paquetes de este vendedor
$paquetes = []; // Inicializamos un array para guardar los paquetes
$sql = "SELECT id, nombre, destino, precio, duracion, imagen FROM paquetes WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_vendedor); // 'i' para integer
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $paquetes[] = $fila;
        }
    }
    $stmt->close();
} else {
    $mensaje = "<div class='alert alert-danger mt-3'>Error al preparar la consulta de paquetes: " . $mysqli->error . "</div>";
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Vendedor - Aventura Global</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { padding-top: 20px; background-color: #f8f9fa; }
        .container { max-width: 960px; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .table img { max-width: 80px; height: auto; border-radius: 4px; }
        .btn-group-sm > .btn, .btn-sm { padding: .25rem .5rem; font-size: .875rem; border-radius: .2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">Panel de Vendedor</h2>
        <p class="lead text-center">Bienvenido, **<?php echo $nombre_vendedor; ?>**! Aquí puedes gestionar tus paquetes de viaje.</p>

        <?php echo $mensaje; // Mostrar mensajes de éxito/error ?>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Mis Paquetes Publicados</h3>
            <a href="añadir_paquete.php" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Paquete
            </a>
        </div>

        <?php if (empty($paquetes)): ?>
            <div class="alert alert-info text-center" role="alert">
                Aún no has publicado ningún paquete. ¡Anímate a añadir el primero!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Imagen</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Destino</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Duración (días)</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $contador = 1; ?>
                        <?php foreach ($paquetes as $paquete): ?>
                            <tr>
                                <th scope="row"><?php echo $contador++; ?></th>
                                <td><img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($paquete['nombre']); ?>" class="img-fluid"></td>
                                <td><?php echo htmlspecialchars($paquete['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($paquete['destino']); ?></td>
                                <td>$<?php echo number_format($paquete['precio'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($paquete['duracion']); ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Acciones de Paquete">
                                        <a href="detalle_paquete.php?id=<?php echo $paquete['id']; ?>" class="btn btn-info btn-sm" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                                        <a href="editar_paquete.php?id=<?php echo $paquete['id']; ?>" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Eliminar" onclick="confirmarEliminar(<?php echo $paquete['id']; ?>, '<?php echo htmlspecialchars($paquete['nombre'], ENT_QUOTES); ?>')"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <hr class="my-4">
        <div class="text-center">
            <a href="index.php" class="btn btn-info me-2"><i class="fas fa-home"></i> Volver al Inicio</a>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function confirmarEliminar(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar el paquete "${nombre}"? Esta acción es irreversible.`)) {
                window.location.href = `eliminar_paquete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>