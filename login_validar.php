<?php
session_start();

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "pagviajes");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validar método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $contraseña = trim($_POST["contraseña"]);

    if (empty($username) || empty($contraseña)) {
        header("Location: login.php?error=Por favor completá ambos campos");
        exit;
    }

    
    $stmt = $conn->prepare("SELECT id, nombre, contraseña FROM usuario WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contraseña, $usuario["contraseña"])) {
            // Inicio de sesión correcto
            $_SESSION["id"] = $usuario["id"];
            $_SESSION["username"] = $username;
            $_SESSION["nombre"] = $usuario["nombre"];

            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=Contraseña incorrecta");
            exit;
        }
    } else {
        header("Location: login.php?error=Usuario no encontrado");
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php?error=Acceso inválido");
    exit;
}
?>
