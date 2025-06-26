<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado y si es un vendedor o administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    header("Location: login.php"); // Redirigir si no es vendedor/admin o no está logueado
    exit();
}

$paquete = null;
$mensaje = "";

// 1. Obtener el ID del paquete de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $paquete_id = intval($_GET['id']);

    // 2. Buscar el paquete en la base de datos
    $sql = "SELECT id, nombre, descripcion, destino, precio, duracion, descomp, imagen, rating, id_usuario
            FROM paquetes 
            WHERE id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $paquete_id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $paquete = $resultado->fetch_assoc();

            // Verificar si el usuario logueado es el autor del paquete o un administrador
            if ($_SESSION['rol'] === 'vendedor' && $paquete['id_usuario'] !== $_SESSION['id']) {
                $_SESSION['mensaje_error'] = "No tienes permiso para editar este paquete.";
                header("Location: panel_vendedor.php");
                exit();
            }
        } else {
            $_SESSION['mensaje_error'] = "Paquete no encontrado.";
            header("Location: panel_vendedor.php"); // Redirigir si el paquete no existe
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje_error'] = "Error en la preparación de la consulta: " . $mysqli->error;
        header("Location: panel_vendedor.php");
        exit();
    }
} else {
    $_SESSION['mensaje_error'] = "ID de paquete inválido o no proporcionado.";
    header("Location: panel_vendedor.php"); // Redirigir si no hay ID
    exit();
}

// Mensajes de éxito o error después de un intento de edición
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alert alert-success'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alert alert-danger'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
}

// Incluimos el header para mantener la estructura de la página
$pageTitle = "Editar Paquete";
$activePage = "panel"; // O la página que uses para el panel de vendedor
require_once 'header.php';
?>

<style>
    /* VARIABLES (Si no las tenés definidas globalmente en un archivo CSS) */
    :root {
        --primary-color: #007bff; /* Azul de Bootstrap */
        --secondary-color: #6c757d; /* Gris de Bootstrap */
    }

    /* Aseguramos que el contenedor principal use los mismos estilos que en detalle_paquete.php */
    .edit-package-container {
        background-color: #f8f9fa; /* Un fondo ligeramente gris para que resalte */
        border-radius: 15px; /* Bordes más redondeados */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Sombra más suave y extendida */
        padding: 40px; /* Más padding para un look espacioso */
        margin-bottom: 3rem; /* Espacio inferior */
        
        /* Mismos anchos que en detalle_paquete.php para consistencia */
        max-width: 800px; 
        margin-left: auto;
        margin-right: auto;
        margin-top: 40px; /* Espacio desde la parte superior */
    }

    .edit-package-container h2 {
        font-size: 2.5rem;
        color: var(--primary-color); /* Usamos la variable */
        margin-bottom: 2rem;
        font-weight: 700;
        text-align: center;
    }

    .form-label {
        font-weight: 600;
        color: #343a40;
        margin-bottom: .5rem;
    }

    /* Estilos generales para inputs y textareas con form-control */
    .form-control {
        border-radius: .5rem;
        padding: .75rem 1rem;
        border: 1px solid #ced4da;
        width: 100%; /* Asegura que ocupe todo el ancho disponible */
        display: block; /* Para que ocupe su propia línea si es necesario */
    }

    /* Aplica 'resize' ESPECÍFICAMENTE a los textareas con la clase form-control */
    textarea.form-control {
        resize: vertical; /* Permite solo redimensionamiento vertical. Usa 'none' para deshabilitar completamente */
        /* Si 'none' no funciona bien en algún navegador, a veces 'overflow: auto;' ayuda */
        overflow: auto; 
    }

    /* Aseguramos que los input de tipo file no sean redimensionables, aunque no suelen serlo */
    input[type="file"].form-control {
        resize: none; 
    }


    .form-control:focus {
        border-color: var(--primary-color); /* Usamos la variable */
        box-shadow: 0 0 0 .25rem rgba(0, 123, 255, .25);
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Estilos para botones - Usamos las variables definidas */
    .btn-custom-primary {
        background-color: var(--primary-color);
        color: white;
        padding: 0.8rem 1.8rem; 
        border-radius: 50px; 
        font-size: 1.1rem;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-custom-primary:hover {
        background-color: #0056b3; 
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.2);
    }

    .btn-custom-secondary {
        background-color: var(--secondary-color); /* Usamos la variable */
        color: white;
        padding: 0.8rem 1.8rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-custom-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.2);
    }

    /* Estilo para la imagen actual */
    .current-image-preview {
        max-width: 250px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 10px;
        margin-bottom: 20px;
        display: block; /* Para centrar si es necesario, o mantener a la izquierda */
    }

    /* Media Queries para Responsividad */
    @media (max-width: 991.98px) {
        .edit-package-container {
            padding: 30px;
            max-width: 700px; 
        }
        .edit-package-container h2 {
            font-size: 2rem;
        }
        .d-md-flex { /* Bootstrap 5 flexbox */
            flex-direction: column; /* Apila los botones en pantallas más pequeñas */
        }
        .btn-custom-primary, .btn-custom-secondary {
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            width: 100%; /* Botones de ancho completo */
            margin-bottom: 10px; /* Espacio entre botones apilados */
        }
    }

    @media (max-width: 767.98px) {
        .edit-package-container {
            padding: 20px;
            max-width: 100%; /* Ocupa todo el ancho disponible en móviles */
            border-radius: 0;
            box-shadow: none;
        }
        .edit-package-container h2 {
            font-size: 1.8rem;
        }
        .current-image-preview {
            max-width: 100%;
        }
        .btn-custom-primary, .btn-custom-secondary {
            width: 100%; /* Botones de ancho completo en móviles */
            margin-bottom: 10px;
        }
    }
</style>

<div class="container">
    <div class="edit-package-container">
        <h2>Editar Paquete de Viaje: <?php echo htmlspecialchars($paquete['nombre']); ?></h2>
        <?php echo $mensaje; ?>
        <form action="procesar_editar_paquete.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($paquete['id']); ?>">
            <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($paquete['imagen']); ?>">

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Paquete:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($paquete['nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción Corta (para el listado):</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($paquete['descripcion']); ?></textarea>
                <small class="form-text text-muted">Esta descripción aparecerá en la página principal y listados.</small>
            </div>
            <div class="mb-3">
                <label for="descomp" class="form-label">Descripción Completa (para el detalle del paquete):</label>
                <textarea class="form-control" id="descomp" name="descomp" rows="6" required><?php echo htmlspecialchars($paquete['descomp']); ?></textarea>
                <small class="form-text text-muted">Esta descripción detallada aparecerá en la página del paquete.</small>
            </div>
            <div class="mb-3">
                <label for="destino" class="form-label">Destino:</label>
                <input type="text" class="form-control" id="destino" name="destino" value="<?php echo htmlspecialchars($paquete['destino']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio:</label>
                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($paquete['precio']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="duracion" class="form-label">Duración (días):</label>
                <input type="number" class="form-control" id="duracion" name="duracion" min="1" value="<?php echo htmlspecialchars($paquete['duracion']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1.0 - 5.0):</label>
                <input type="number" class="form-control" id="rating" name="rating" step="0.1" min="1" max="5" value="<?php echo htmlspecialchars($paquete['rating']); ?>" required>
                <small class="form-text text-muted">Valora el paquete de 1.0 a 5.0.</small>
            </div>
            <div class="mb-3">
                <label for="imagen" class="form-label">Cambiar Imagen del Paquete:</label>
                <?php if (!empty($paquete['imagen'])): ?>
                    <p class="mb-2">Imagen actual:</p>
                    <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="Imagen actual del paquete" class="current-image-preview">
                <?php else: ?>
                    <p class="mb-2">No hay imagen actual.</p>
                <?php endif; ?>
                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                <small class="form-text text-muted">Deja en blanco para mantener la imagen actual. Sube una nueva imagen JPG, PNG o GIF.</small>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <button type="submit" class="btn btn-custom-primary"><i class="fas fa-save me-2"></i> Guardar Cambios</button>
                <a href="panel_vendedor.php" class="btn btn-custom-secondary"><i class="fas fa-times-circle me-2"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php 
require_once 'footer.php'; 
$mysqli->close();
?>