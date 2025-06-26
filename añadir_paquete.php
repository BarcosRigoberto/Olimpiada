<?php
session_start();

// Verificar si el usuario está logueado y si es un vendedor o administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    header("Location: login.php"); // Redirigir si no es vendedor/admin o no está logueado
    exit();
}

require_once 'conexion.php'; // Tu archivo de conexión a la base de datos

$mensaje = "";
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alert alert-success'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alert alert-danger'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Nuevo Paquete - Aventura Global</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 700px; }
        /* Estilos CSS adicionales si los tenés del ajuste anterior para los campos */
        .form-control {
            width: 100%; /* Asegura que los inputs ocupen todo el ancho */
            display: block;
        }
        textarea.form-control {
            resize: vertical; /* Permite redimensionar solo verticalmente */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Añadir Nuevo Paquete de Viaje</h2>
        <?php echo $mensaje; ?>
        <form action="procesar_añadir_paquete.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Paquete:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción Corta (para el listado):</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                <small class="form-text text-muted">Esta descripción aparecerá en la página principal y listados.</small>
            </div>
            <div class="mb-3">
                <label for="descomp" class="form-label">Descripción Completa (para el detalle del paquete):</label>
                <textarea class="form-control" id="descomp" name="descomp" rows="6" required></textarea>
                <small class="form-text text-muted">Esta descripción detallada aparecerá en la página del paquete.</small>
            </div>
            <div class="mb-3">
                <label for="destino" class="form-label">Destino:</label>
                <input type="text" class="form-control" id="destino" name="destino" required>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio:</label>
                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="duracion" class="form-label">Duración (días):</label>
                <input type="number" class="form-control" id="duracion" name="duracion" min="1" required>
            </div>
            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1.0 - 5.0):</label>
                <input type="number" class="form-control" id="rating" name="rating" step="0.1" min="1" max="5" required>
                <small class="form-text text-muted">Valora el paquete de 1.0 a 5.0.</small>
            </div>
            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen del Paquete:</label>
                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
                <small class="form-text text-muted">Sube una imagen JPG, PNG o GIF para el paquete.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Añadir Paquete</button>
            <a href="panel_vendedor.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>