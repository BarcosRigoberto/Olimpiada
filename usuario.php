<?php
session_start();
require_once 'conexion.php'; // Asegúrate de incluir tu archivo de conexión aquí

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Mi Perfil - " . htmlspecialchars($_SESSION['username']);
require_once 'header.php'; 

$usuario_id = $_SESSION['id'];
$username = $_SESSION['username'];
$nombre_completo = $_SESSION['nombre']; 
$email = $_SESSION['email'] ?? ''; // Asegúrate de tener el email en sesión
$foto_perfil = $_SESSION['foto_perfil'] ?? 'uploads/profiles/default.jpg'; 

$rol_usuario = $_SESSION['rol'] ?? 'cliente'; // Asume 'cliente' por defecto

$datos_empresa = null; // Inicializamos para los datos de la empresa

// Si el usuario es vendedor, cargamos sus datos de empresa
if ($rol_usuario === 'vendedor') {
    $sql_empresa = "SELECT nombre_empresa, cuit, direccion_empresa, telefono_empresa, descripcion_empresa, logo_empresa 
                    FROM vendedores 
                    WHERE usuario_id = ?";
    $stmt_empresa = $mysqli->prepare($sql_empresa);

    if ($stmt_empresa) {
        $stmt_empresa->bind_param("i", $usuario_id);
        $stmt_empresa->execute();
        $resultado_empresa = $stmt_empresa->get_result();

        if ($resultado_empresa->num_rows === 1) {
            $datos_empresa = $resultado_empresa->fetch_assoc();
        }
        $stmt_empresa->close();
    } else {
        error_log("Error al preparar la consulta de empresa en usuario.php: " . $mysqli->error);
    }
}
?>

<style>
/* Tu CSS existente */
.profile-page-container {
    max-width: 800px;
    margin: 50px auto;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    font-family: 'Poppins', sans-serif;
}

.profile-page-container img.profile-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--primary-color, #007bff);
    margin-bottom: 20px;
}

.profile-page-container h1 {
    color: var(--primary-color);
    margin-bottom: 10px;
    text-align: center;
}

.profile-page-container p {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 8px;
    text-align: center; /* Centrar texto dentro del párrafo */
}

.profile-actions {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap; /* Para que los botones se ajusten en pantallas pequeñas */
    justify-content: center; /* Centrar botones */
    gap: 15px;
}

.profile-actions a {
    background-color: var(--secondary-color);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: background-color 0.3s ease;
    white-space: nowrap; /* Evita que el texto del botón se rompa */
}

.profile-actions a:hover {
    background-color: #009ac1;
}

/* Nuevo estilo para los datos de la empresa */
.company-details {
    width: 100%;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center; /* Asegura que el contenido se centre */
}

.company-details h2 {
    color: var(--primary-color);
    margin-bottom: 15px;
    font-size: 1.8rem;
}

.company-details p {
    font-size: 1rem;
    margin-bottom: 5px;
}

.company-logo-preview {
    max-width: 100px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-top: 10px;
    margin-bottom: 15px;
    display: block; /* Para centrarlo con margin: auto */
    margin-left: auto;
    margin-right: auto;
}
</style>
<link rel="stylesheet" type="text/css" href="indstyle.css">
<div class="profile-page-container">
    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" class="profile-large">
    <h1>Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?></h1>
    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    
    <?php if ($rol_usuario === 'vendedor' && $datos_empresa): // Mostrar datos de empresa si es vendedor y hay datos ?>
    <div class="company-details">
        <h2>Datos de la Empresa</h2>
        <?php if (!empty($datos_empresa['logo_empresa'])): ?>
            <img src="<?php echo htmlspecialchars($datos_empresa['logo_empresa']); ?>" alt="Logo de la empresa" class="company-logo-preview">
        <?php endif; ?>
        <p><strong>Nombre de Empresa:</strong> <?php echo htmlspecialchars($datos_empresa['nombre_empresa'] ?? 'N/A'); ?></p>
        <p><strong>CUIT/NIF:</strong> <?php echo htmlspecialchars($datos_empresa['cuit'] ?? 'N/A'); ?></p>
        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($datos_empresa['direccion_empresa'] ?? 'N/A'); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($datos_empresa['telefono_empresa'] ?? 'N/A'); ?></p>
        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($datos_empresa['descripcion_empresa'] ?? 'N/A'); ?></p>
    </div>
    <?php endif; ?>

    <div class="profile-actions">
        <?php 
        if ($rol_usuario === 'vendedor') {
            echo '<a href="editar_cuenta_empresa.php" class="btn btn-primary">Editar Perfil de Empresa</a>';
            echo '<a href="panel_vendedor.php" class="btn btn-primary">Panel de Vendedor</a>'; // Opción útil
        } else {
            echo '<a href="editar_usuario.php" class="btn btn-primary">Editar Perfil</a>';
            echo '<a href="mis_reservas.php" class="btn btn-primary">Mis Reservas</a>';
        }
        ?>
        <a href="logout.php" class="btn btn-primary">Cerrar Sesión</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>