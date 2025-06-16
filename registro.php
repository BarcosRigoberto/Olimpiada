<?php
session_start();

$pageTitle = "Registrarse - Aventura Global";
require_once 'header.php';
?>

<style>
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
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
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
<link rel="stylesheet" type="text/css" href="indstyle.css">
<div class="registro-container">
    <h2>Crea tu cuenta</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg">Ese usuario o email ya está registrado.</div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="success-msg">¡Registro exitoso! Ya puedes iniciar sesión.</div>
    <?php endif; ?>

    <form action="procesar_registro.php" method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="text" name="username" placeholder="Nombre de usuario" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>
