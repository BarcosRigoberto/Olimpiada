<?php
session_start();

require_once 'conexion.php'; // Asegúrate de que este archivo contiene tu conexión $mysqli

// Validar método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Usar los nombres de campos correctos: 'username' y 'contraseña'
    $username = trim($_POST["username"] ?? ''); // Usamos ?? '' para evitar warnings si no existen
    $contraseña = trim($_POST["contraseña"] ?? '');

    if (empty($username) || empty($contraseña)) {
        $_SESSION['login_error'] = "Por favor, completa ambos campos."; // Usamos una sesión para el error
        header("Location: login.php"); // Redirige a login.php
        exit;
    }

    // Consulta clave: Seleccionar 'id', 'nombre', 'contraseña', 'foto_perfil' Y 'rol'
    // ¡Asegúrate de que el nombre de la tabla sea 'usuario' y no 'usuarios'!
    $stmt = $mysqli->prepare("SELECT id, nombre, contraseña, foto_perfil, rol FROM usuario WHERE username = ?");
    
    if ($stmt) {
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
                $_SESSION["foto_perfil"] = $usuario["foto_perfil"]; 
                $_SESSION['logged_in'] = true; 
                
                // ¡*** ESTA ES LA LÍNEA CLAVE PARA EL CARRITO ***!
                // Guarda el rol del usuario en la sesión
                $_SESSION['rol'] = $usuario['rol']; 

                // Redirigir según el rol, si lo necesitas. Por ahora, asumimos que todos van a index.php
                // Si tienes diferentes roles con diferentes páginas de inicio, podrías hacer esto:
                // if ($usuario['rol'] === 'admin') {
                //     header("Location: panel_admin.php");
                // } elseif ($usuario['rol'] === 'vendedor') {
                //     header("Location: panel_vendedor.php");
                // } else {
                //     header("Location: index.php"); // Por defecto para 'comprador'
                // }

                header("Location: index.php"); // Redirige a la página principal
                exit;

            } else {
                $_SESSION['login_error'] = "Contraseña incorrecta.";
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = "Usuario no encontrado.";
            header("Location: login.php");
            exit;
        }

        $stmt->close();
    } else {
        $_SESSION['login_error'] = "Error en la preparación de la consulta: " . $mysqli->error;
        header("Location: login.php");
        exit;
    }

    $mysqli->close(); // Cierra la conexión después de usarla
} else {
    // Si no es un POST, redirigir al login para evitar acceso directo
    $_SESSION['login_error'] = "Acceso no autorizado.";
    header("Location: login.php");
    exit;
}
?>