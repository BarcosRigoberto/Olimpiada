<?php
session_start();

$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'pagviajes';

$conn = new mysqli($host, $usuario, $contrasena, $basedatos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Traer más datos del usuario, incluyendo foto_perfil
$sql = "SELECT id, nombre, email, contraseña, foto_perfil FROM usuario WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    if (password_verify($password, $usuario['contraseña'])) {
        // Guardar en sesión todos los datos necesarios
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['username'] = $username;
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['email'] = $usuario['email'];

        // ✅ Solo poner default si la foto está vacía
        $_SESSION['foto_perfil'] = !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'uploads/profiles/default.jpg';

        header("Location: index.php");
        exit();
    }
}

// Usuario o contraseña incorrectos
header("Location: login.php?error=1");
exit();
