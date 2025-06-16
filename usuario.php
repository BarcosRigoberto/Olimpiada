<?php
session_start();
$pageTitle = "Mi Perfil - Aventura Global";
$activePage = "perfil"; // Puedes usar esto en tu navbar para marcarlo como activo

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$usuario = 'root';
$contrasena = ''; // Cambia si tienes contraseña
$basedatos = 'pagviajes';

$conn = new mysqli($host, $usuario, $contrasena, $basedatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nombre, apellido, username, email FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit();
}

$conn->close();

require_once 'header.php';
?>

<style>
.perfil-container {
    max-width: 800px;
    margin: 80px auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
}

.perfil-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--primary-color);
}

.perfil-dato {
    margin-bottom: 20px;
}

.perfil-dato label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
}

.perfil-dato span {
    display: block;
    color: #333;
    font-size: 1.1rem;
}

.btn-logout {
    display: inline-block;
    margin-top: 30px;
    padding: 10px 20px;
    background-color: var(--secondary-color);
    color: white;
    border-radius: 30px;
    text-align: center;
    transition: background-color 0.3s ease;
}

.btn-logout:hover {
    background-color: #009ac1;
}
</style>

<div class="perfil-container">
    <h2>Perfil del Usuario</h2>

    <div class="perfil-dato">
        <label>Nombre:</label>
        <span><?= htmlspecialchars($usuario['nombre']) ?></span>
    </div>

    <div class="perfil-dato">
        <label>Apellido:</label>
        <span><?= htmlspecialchars($usuario['apellido']) ?></span>
    </div>

    <div class="perfil-dato">
        <label>Nombre de usuario:</label>
        <span><?= htmlspecialchars($usuario['username']) ?></span>
    </div>

    <div class="perfil-dato">
        <label>Email:</label>
        <span><?= htmlspecialchars($usuario['email']) ?></span>
    </div>

    <a href="logout.php" class="btn-logout">Cerrar sesión</a>
</div>

<?php require_once 'footer.php'; ?>
