<?php
session_start();

// Redirigir si no hay sesión activa
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
require_once 'header.php'; 

$usuario_id = $_SESSION['id'];
$nombre_usuario_actual = $_SESSION['username'];
$foto_perfil_actual = $_SESSION['foto_perfil'] ?? 'uploads/profiles/default.jpg';

?>

<style>
.editar-perfil-container {
    max-width: 500px;
    margin: 50px auto;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
}

.editar-perfil-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: var(--primary-color);
}

.editar-perfil-container input, .editar-perfil-container button {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.editar-perfil-container button {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.editar-perfil-container button:hover {
    background-color: #009ac1;
}
</style>
<script>
function confirmarGuardado() {
    return confirm("¿Estás seguro de que querés guardar los cambios?");
}
</script>
<link rel="stylesheet" type="text/css" href="indstyle.css">
<div class="editar-perfil-container">
    <h2>Editar Perfil</h2>
    <form action="guardar_edicion_perfil.php" method="POST" enctype="multipart/form-data">
        <label>Nombre de Usuario:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($nombre_usuario_actual); ?>" required>

        <label>Cambiar Foto de Perfil:</label>
        <input type="file" name="foto_perfil" accept="image/*">

        <button type="submit" onclick="return confirmarGuardado()">Guardar Cambios</button>
    </form>
</div>
<?php require_once 'footer.php'; ?> 