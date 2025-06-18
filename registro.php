<?php
session_start();

// Si ya hay una sesión iniciada, redirige al usuario logueado (puedes ajustar la redirección)
if (isset($_SESSION['id'])) { // Usamos 'id' de la sesión que se establece en login_validar.php y guardarreg.php
    header("Location: index.php"); // O a perfil.php
    exit();
}

$pageTitle = "Registrarse - Aventura Global";
require_once 'header.php'; // Asegúrate de que header.php tenga session_start()

?>
<link rel="stylesheet" type="text/css" href="indstyle.css">
<style>
/* Tus estilos CSS existentes para el contenedor de registro */
.registro-container {
    max-width: 450px;
    margin: 100px auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
}

.registro-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--primary-color);
}

.registro-container form {
    display: flex;
    flex-direction: column;
}

.registro-container input {
    padding: 12px;
    margin-bottom: 15px; /* Ajustado para que la etiqueta no quede pegada */
    border: 1px solid #ccc;
    border-radius: 8px;
}

/* Estilo específico para el input[type="file"] */
.registro-container input[type="file"] {
    padding: 8px; /* Un poco menos de padding para el file input */
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f8f8f8; /* Para diferenciarlo */
}

/* Estilo para la etiqueta del campo de archivo */
.registro-container label {
    margin-bottom: 5px;
    color: #555;
    font-size: 0.95rem;
}

/* Estilo para el texto de ayuda del campo de archivo */
.registro-container small {
    color: #777;
    margin-top: -10px; /* Reduce el espacio con el input */
    margin-bottom: 20px; /* Espacio antes del siguiente elemento */
    display: block; /* Para que ocupe su propia línea */
    font-size: 0.85rem;
}


.registro-container button {
    background-color: var(--secondary-color);
    color: white;
    padding: 12px;
    border: none;
    border-radius: 30px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.registro-container button:hover {
    background-color: #009ac1;
}

.error-msg, .success-msg {
    text-align: center;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.error-msg {
    color: red;
}

.success-msg {
    color: green;
}
</style>
<div class="registro-container">
    <h2>Crea tu cuenta</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <form action="guardarreg.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="text" name="username" placeholder="Nombre de usuario" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <label for="foto_perfil">Foto de perfil (opcional):</label>
        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg, image/png, image/gif">
        <small>Formatos: JPG, PNG, GIF. Max 2MB.</small>

        <button type="submit">Registrarse</button>
    </form>

    <p style="text-align: center; margin-top: 15px;">
        ¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión aquí</a>
    </p>
</div>

<?php require_once 'footer.php'; ?>