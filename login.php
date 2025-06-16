<?php
session_start();

// Si ya está logueado, redirigir al perfil
if (isset($_SESSION['usuario_id'])) {
    header("Location: usuario.php");
    exit();
}

$pageTitle = "Iniciar Sesión - Aventura Global";
require_once 'header.php';
?>
<link rel="stylesheet" type="text/css" href="indstyle.css">
<style>
.login-container {
    max-width: 400px;
    margin: 100px auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
}

.login-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--primary-color);
}

.login-container form {
    display: flex;
    flex-direction: column;
}

.login-container input[type="text"],
.login-container input[type="password"] {
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.login-container button {
    background-color: var(--primary-color);
    color: white;
    padding: 12px;
    border: none;
    border-radius: 30px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.login-container button:hover {
    background-color: #005a8d;
}

.error-msg {
    color: red;
    font-size: 0.9rem;
    text-align: center;
    margin-bottom: 15px;
}
</style>

<div class="login-container">
    <h2>Iniciar Sesión</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg">Usuario o contraseña incorrectos.</div>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST">
        <input type="text" name="username" placeholder="Nombre de usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">
    ¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
</p>
</div>

<?php require_once 'footer.php'; ?>
