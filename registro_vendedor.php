<?php
session_start();
// Incluye el header para la estructura básica de la página
require_once 'header.php'; 

$pageTitle = "Registro de Vendedor";
?>

<link rel="stylesheet" type="text/css" href="indstyle.css"> <style>
    /* Estilos específicos para el formulario de registro de vendedor */
    .registro-vendedor-container {
        max-width: 600px;
        margin: 100px auto;
        padding: 40px;
        background-color: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
    }
    .registro-vendedor-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }
    .registro-vendedor-container label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }
    .registro-vendedor-container input[type="text"],
    .registro-vendedor-container input[type="email"],
    .registro-vendedor-container input[type="password"],
    .registro-vendedor-container textarea {
        width: calc(100% - 22px); /* Ajuste para padding */
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box; /* Incluye padding en el ancho */
    }
    .registro-vendedor-container textarea {
        resize: vertical; /* Permite redimensionar verticalmente */
        min-height: 80px;
    }
    .registro-vendedor-container button {
        background-color: #007bff;
        color: white;
        padding: 15px 25px;
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }
    .registro-vendedor-container button:hover {
        background-color: #0056b3;
    }
    .error-msg, .success-msg {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
    }
    .error-msg {
        background-color: #f8d7da;
        color: #dc3545;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="registro-vendedor-container">
    <h2>Registro de Cuenta de Vendedor</h2>

    <?php
    if (isset($_GET['error'])) {
        echo '<p class="error-msg">' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>

    <form action="guardarreg_vendedor.php" method="POST" enctype="multipart/form-data">
        <h3>Datos Personales de la Cuenta</h3>
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required>

        <label for="username">Nombre de Usuario (Login):</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="foto_perfil">Foto de Perfil (Opcional):</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">

        <h3>Datos de la Empresa</h3>
        <label for="nombre_empresa">Nombre de la Empresa:</label>
        <input type="text" id="nombre_empresa" name="nombre_empresa" required>

        <label for="cuit">CUIT/NIF:</label>
        <input type="text" id="cuit" name="cuit">

        <label for="direccion_empresa">Dirección de la Empresa:</label>
        <input type="text" id="direccion_empresa" name="direccion_empresa" required>

        <label for="telefono_empresa">Teléfono de Contacto de la Empresa:</label>
        <input type="text" id="telefono_empresa" name="telefono_empresa">

        <label for="descripcion_empresa">Descripción de la Empresa:</label>
        <textarea id="descripcion_empresa" name="descripcion_empresa" rows="4"></textarea>

        <button type="submit">Registrarme como Vendedor</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>