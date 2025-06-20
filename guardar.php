<?php
session_start();

// Conectar a la BD
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'pagviajes';

$conn = new mysqli($host, $usuario, $contrasena, $basedatos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$username = $_POST['username'];
$password = $_POST['password'];

// Buscar usuario
$sql = "SELECT id, contraseña FROM usuario WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    // Verificar contraseña (plaintext, pero idealmente con hash)
    if ($password === $usuario['contraseña']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        header("Location: index.php");
        exit();
    }
}

// Fallo de login
header("Location: login.php?error=1");
exit();
