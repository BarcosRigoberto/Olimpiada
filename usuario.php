<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Mi Perfil - " . htmlspecialchars($_SESSION['username']);
require_once 'header.php'; 

$usuario_id = $_SESSION['id'];
$username = $_SESSION['username'];
$nombre_completo = $_SESSION['nombre']; 
$foto_perfil = $_SESSION['foto_perfil'] ?? 'uploads/profiles/default.jpg'; 

?>

<style>

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
    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
    <?php if (isset($_SESSION['email'])): // Si también guardas el email en la sesión ?>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <?php endif; ?>
    <div class="profile-actions">
        <a href="editar_usuario.php">Editar Perfil</a>
        <a href="mis_reservas.php">Mis Reservas</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>
</div>

<?php require_once 'footer.php'; // Incluye el footer ?>