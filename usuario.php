<?php
session_start();

// Si el usuario NO ha iniciado sesión, redirigirlo al login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Mi Perfil - " . htmlspecialchars($_SESSION['username']); // Título dinámico
require_once 'header.php'; // Incluye el header para la navegación

// Puedes obtener los datos del usuario de la sesión
$usuario_id = $_SESSION['id'];
$username = $_SESSION['username'];
$nombre_completo = $_SESSION['nombre']; // Asumiendo que 'nombre' es el nombre completo o solo el nombre
$foto_perfil = $_SESSION['foto_perfil'] ?? 'uploads/profiles/default.jpg'; // Usar la de la sesión o la por defecto

// Opcional: Si necesitas más datos del usuario que no están en la sesión,
// puedes hacer una consulta a la base de datos aquí usando $usuario_id.
/*
$conn = new mysqli("localhost", "root", "", "pagviajes");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT email, ... FROM usuario WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$datos_extra_usuario = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
*/

?>

<style>
/* Estilos para tu página de perfil (puedes moverlos a un CSS externo si prefieres) */
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
}

.profile-page-container p {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 8px;
}

.profile-actions {
    margin-top: 20px;
    display: flex;
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
}

.profile-actions a:hover {
    background-color: #009ac1;
}
</style>
<link rel="stylesheet" type="text/css" href="indstyle.css">
<div class="profile-page-container">
    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" class="profile-large">
    <h1>Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?></h1>
    <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
    <?php if (isset($_SESSION['email'])): // Si también guardas el email en la sesión ?>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <?php endif; ?>
    <div class="profile-actions">
        <a href="editar_perfil.php">Editar Perfil</a>
        <a href="mis_reservas.php">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>
</div>

<?php require_once 'footer.php'; // Incluye el footer ?>