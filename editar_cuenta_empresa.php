<?php
session_start();
require_once 'conexion.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y si es un vendedor o administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    $_SESSION['mensaje_error'] = "Acceso denegado. Debes ser vendedor o administrador para editar una cuenta de empresa.";
    header("Location: login.php");
    exit();
}

$datos_usuario = null;
$datos_empresa = null;
$mensaje = "";

// Obtener el ID del usuario logueado
$id_usuario_sesion = $_SESSION['id'];

// --- CONSULTAR DATOS DEL USUARIO (tabla 'usuario') ---
// Ahora seleccionamos 'username' y 'nombre'
$sql_usuario = "SELECT id, username, nombre, email, rol FROM usuario WHERE id = ?";
$stmt_usuario = $mysqli->prepare($sql_usuario);

if ($stmt_usuario) {
    $stmt_usuario->bind_param("i", $id_usuario_sesion);
    $stmt_usuario->execute();
    $resultado_usuario = $stmt_usuario->get_result();

    if ($resultado_usuario->num_rows === 1) {
        $datos_usuario = $resultado_usuario->fetch_assoc();
        // Verificar que el ID del usuario logueado coincida si no es admin
        if ($_SESSION['rol'] === 'vendedor' && $datos_usuario['id'] !== $id_usuario_sesion) {
            $_SESSION['mensaje_error'] = "No tienes permiso para editar esta cuenta de empresa.";
            header("Location: panel_vendedor.php");
            exit();
        }
    } else {
        $_SESSION['mensaje_error'] = "Datos de usuario no encontrados.";
        header("Location: panel_vendedor.php");
        exit();
    }
    $stmt_usuario->close();
} else {
    $_SESSION['mensaje_error'] = "Error en la preparación de la consulta de usuario: " . $mysqli->error;
    header("Location: panel_vendedor.php");
    exit();
}

// --- CONSULTAR DATOS DE LA EMPRESA (tabla 'empresa') ---
// Si el usuario es vendedor, intentamos cargar los datos de su empresa
if ($datos_usuario['rol'] === 'vendedor' || $datos_usuario['rol'] === 'admin') {
    $sql_empresa = "SELECT nombre_empresa, cuit, direccion_empresa, telefono_empresa, descripcion_empresa, logo_empresa 
                    FROM vendedores 
                    WHERE usuario_id = ?";
    $stmt_empresa = $mysqli->prepare($sql_empresa);

    if ($stmt_empresa) {
        $stmt_empresa->bind_param("i", $id_usuario_sesion);
        $stmt_empresa->execute();
        $resultado_empresa = $stmt_empresa->get_result();

        if ($resultado_empresa->num_rows === 1) {
            $datos_empresa = $resultado_empresa->fetch_assoc();
        } 
        // No es un error si no se encuentran datos de empresa,
        // significa que el vendedor aún no ha completado su perfil de empresa.
        // Los campos del formulario simplemente estarán vacíos.
        $stmt_empresa->close();
    } else {
        $_SESSION['mensaje_error'] = "Error en la preparación de la consulta de empresa: " . $mysqli->error;
        header("Location: panel_vendedor.php");
        exit();
    }
}


// Mensajes de éxito o error después de un intento de guardado
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alert alert-success'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alert alert-danger'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
}

// Incluimos el header para mantener la estructura de la página
$pageTitle = "Editar Cuenta de Empresa";
$activePage = "perfil"; // O la página que uses para el perfil del usuario
require_once 'header.php';
?>

<style>
    /* Asegúrate de que estas variables estén definidas, ya sea aquí o en tu CSS global */
    :root {
        --primary-color: #007bff;
        --secondary-color: #6c757d;
    }
    .edit-account-container {
        background-color: #f8f9fa;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        margin-bottom: 3rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        margin-top: 40px;
    }
    .edit-account-container h2 {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 2rem;
        font-weight: 700;
        text-align: center;
    }
    .form-label {
        font-weight: 600;
        color: #343a40;
        margin-bottom: .5rem;
    }
    .form-control {
        border-radius: .5rem;
        padding: .75rem 1rem;
        border: 1px solid #ced4da;
        width: 100%;
        display: block;
    }
    textarea.form-control {
        resize: vertical;
        overflow: auto; 
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 .25rem rgba(0, 123, 255, .25);
    }
    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }
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
        background-color: var(--secondary-color);
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
    .current-logo-preview {
        max-width: 150px; /* Tamaño más pequeño para un logo */
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 10px;
        margin-bottom: 20px;
        display: block;
    }
    @media (max-width: 991.98px) {
        .edit-account-container { padding: 30px; max-width: 700px; }
        .edit-account-container h2 { font-size: 2rem; }
        .d-md-flex { flex-direction: column; }
        .btn-custom-primary, .btn-custom-secondary { width: 100%; margin-bottom: 10px; }
    }
    @media (max-width: 767.98px) {
        .edit-account-container { padding: 20px; max-width: 100%; border-radius: 0; box-shadow: none; }
        .edit-account-container h2 { font-size: 1.8rem; }
        .current-logo-preview { max-width: 100%; }
        .btn-custom-primary, .btn-custom-secondary { width: 100%; margin-bottom: 10px; }
    }
</style>

<div class="container">
    <div class="edit-account-container">
        <h2>Editar Cuenta de Empresa</h2>
        <?php echo $mensaje; ?>
        <form action="procesar_editar_cuenta_empresa.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($datos_usuario['id']); ?>">
            <input type="hidden" name="logo_actual" value="<?php echo htmlspecialchars($datos_empresa['logo_empresa'] ?? ''); ?>">
            <input type="hidden" name="empresa_existe" value="<?php echo ($datos_empresa !== null) ? 'true' : 'false'; ?>">


            <div class="mb-3">
                <label for="username" class="form-label">Nombre de Usuario:</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($datos_usuario['username'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nombre_completo" class="form-label">Nombre Completo:</label>
                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($datos_usuario['nombre'] ?? ''); ?>">
                <small class="form-text text-muted">Este es tu nombre personal, no el de tu empresa.</small>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($datos_usuario['email'] ?? ''); ?>" required>
            </div>
            
            <hr class="my-4"> <h3>Datos de la Empresa</h3>

            <div class="mb-3">
                <label for="nombre_empresa" class="form-label">Nombre de la Empresa:</label>
                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($datos_empresa['nombre_empresa'] ?? ''); ?>" required>
                <small class="form-text text-muted">Este nombre aparecerá públicamente en tus paquetes.</small>
            </div>
            <div class="mb-3">
                <label for="cuit" class="form-label">CUIT/NIF (Identificación Fiscal):</label>
                <input type="text" class="form-control" id="cuit" name="cuit" value="<?php echo htmlspecialchars($datos_empresa['cuit'] ?? ''); ?>">
                <small class="form-text text-muted">Número de identificación fiscal de tu empresa.</small>
            </div>
            <div class="mb-3">
                <label for="direccion_empresa" class="form-label">Dirección de la Empresa:</label>
                <input type="text" class="form-control" id="direccion_empresa" name="direccion_empresa" value="<?php echo htmlspecialchars($datos_empresa['direccion_empresa'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="telefono_empresa" class="form-label">Teléfono de la Empresa:</label>
                <input type="tel" class="form-control" id="telefono_empresa" name="telefono_empresa" value="<?php echo htmlspecialchars($datos_empresa['telefono_empresa'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="descripcion_empresa" class="form-label">Descripción de la Empresa:</label>
                <textarea class="form-control" id="descripcion_empresa" name="descripcion_empresa" rows="4"><?php echo htmlspecialchars($datos_empresa['descripcion_empresa'] ?? ''); ?></textarea>
                <small class="form-text text-muted">Una breve descripción de tu empresa que puede aparecer en tu perfil.</small>
            </div>
            
            <div class="mb-3">
                <label for="logo_empresa" class="form-label">Logo de la Empresa:</label>
                <?php if (!empty($datos_empresa['logo_empresa'])): ?>
                    <p class="mb-2">Logo actual:</p>
                    <img src="<?php echo htmlspecialchars($datos_empresa['logo_empresa']); ?>" alt="Logo actual de la empresa" class="current-logo-preview">
                <?php else: ?>
                    <p class="mb-2">No hay logo actual.</p>
                <?php endif; ?>
                <input type="file" class="form-control" id="logo_empresa" name="logo_empresa" accept="image/*">
                <small class="form-text text-muted">Sube tu logo (JPG, PNG, GIF). Deja en blanco para mantener el actual. Máx. 2MB.</small>
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