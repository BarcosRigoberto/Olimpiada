<?php
session_start();

// Conexión a la BD
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'pagviajes';

$conn = new mysqli($host, $usuario, $contrasena, $basedatos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password']; // En producción, hashea esto

// Verificar si el usuario o email ya existen
$sql = "SELECT id FROM usuario WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    header("Location: registro.php?error=1");
    exit();
}

// Insertar usuario
$sql = "INSERT INTO usuario (nombre, apellido, username, email, contraseña) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellido, $username, $email, $password);
$stmt->execute();

header("Location: registro.php?success=1");
exit();
