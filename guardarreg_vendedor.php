<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que este archivo contiene tu conexión $mysqli

// Asegúrate de que los datos vengan por POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: registro_vendedor.php");
    exit();
}

// 1. Obtener y limpiar datos del formulario (personales)
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// 2. Obtener y limpiar datos del formulario (empresa)
$nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
$cuit = trim($_POST['cuit'] ?? '');
$direccion_empresa = trim($_POST['direccion_empresa'] ?? '');
$telefono_empresa = trim($_POST['telefono_empresa'] ?? '');
$descripcion_empresa = trim($_POST['descripcion_empresa'] ?? '');

// Validar que los campos obligatorios no estén vacíos
if (empty($nombre) || empty($apellido) || empty($username) || empty($email) || empty($password) ||
    empty($nombre_empresa) || empty($direccion_empresa)) {
    header("Location: registro_vendedor.php?error=" . urlencode("Todos los campos obligatorios (personales y de empresa) deben ser completados."));
    exit();
}

// Verificar si el usuario o email ya existen
$sql_check = "SELECT id FROM usuario WHERE username = ? OR email = ?";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();

if ($resultado_check->num_rows > 0) {
    header("Location: registro_vendedor.php?error=" . urlencode("Ese nombre de usuario o email ya está registrado."));
    exit();
}
$stmt_check->close();

// --- Manejo de la subida de la foto de perfil (igual que en guardarreg.php) ---
$foto_perfil_path = 'uploads/profiles/default.png'; 

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['foto_perfil']['tmp_name'];
    $file_name = $_FILES['foto_perfil']['name'];
    $file_size = $_FILES['foto_perfil']['size'];
    $file_type = $_FILES['foto_perfil']['type'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    $max_file_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($file_ext, $allowed_extensions)) {
        header("Location: registro_vendedor.php?error=" . urlencode("Formato de imagen no permitido. Solo JPG, JPEG, PNG, GIF."));
        exit();
    }

    if ($file_size > $max_file_size) {
        header("Location: registro_vendedor.php?error=" . urlencode("La imagen es demasiado grande. Máximo 2MB."));
        exit();
    }

    $new_file_name = uniqid('profile_', true) . '.' . $file_ext;
    $upload_directory = 'uploads/profiles/'; 

    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }

    $destination = $upload_directory . $new_file_name;

    if (move_uploaded_file($file_tmp_name, $destination)) {
        $foto_perfil_path = $destination;
    } else {
        error_log("Error al mover el archivo subido para vendedor: " . $_FILES['foto_perfil']['error'] . " para el usuario: " . $username);
    }
}

// Hashear la contraseña
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// --- INICIO DE TRANSACCIÓN ---
$mysqli->begin_transaction();

try {
    // 1. Insertar el usuario en la tabla `usuario` con el rol 'vendedor'
    $rol_vendedor = 'vendedor'; // Definimos el rol explícitamente
    $sql_insert_usuario = "INSERT INTO usuario (nombre, apellido, username,nombre_empresa, email, contraseña, foto_perfil, rol) VALUES (?, ?, ?, ?, ?, ?, ?,?)";
    $stmt_insert_usuario = $mysqli->prepare($sql_insert_usuario);
    if (!$stmt_insert_usuario) {
        throw new Exception("Error de preparación de usuario: " . $mysqli->error);
    }
    $stmt_insert_usuario->bind_param("ssssssss", $nombre, $apellido, $username,$nombre_empresa, $email, $hashed_password, $foto_perfil_path, $rol_vendedor);
    
    if (!$stmt_insert_usuario->execute()) {
        throw new Exception("Error al insertar usuario: " . $stmt_insert_usuario->error);
    }
    $usuario_id = $mysqli->insert_id; // Obtener el ID del usuario recién insertado
    $stmt_insert_usuario->close();

    // 2. Insertar los datos de la empresa en la tabla `vendedores`
    $sql_insert_vendedor = "INSERT INTO vendedores (usuario_id, nombre_empresa, cuit, direccion_empresa, telefono_empresa, descripcion_empresa) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert_vendedor = $mysqli->prepare($sql_insert_vendedor);
    if (!$stmt_insert_vendedor) {
        throw new Exception("Error de preparación de vendedor: " . $mysqli->error);
    }
    $stmt_insert_vendedor->bind_param("isssss", $usuario_id, $nombre_empresa, $cuit, $direccion_empresa, $telefono_empresa, $descripcion_empresa);
    
    if (!$stmt_insert_vendedor->execute()) {
        throw new Exception("Error al insertar datos de vendedor: " . $stmt_insert_vendedor->error);
    }
    $stmt_insert_vendedor->close();

    $mysqli->commit(); // Confirmar la transacción: todo fue bien

    // Registro exitoso, iniciar sesión automáticamente
    $_SESSION['id'] = $usuario_id;
    $_SESSION['username'] = $username;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['foto_perfil'] = $foto_perfil_path;
    $_SESSION['logged_in'] = true;
    $_SESSION['rol'] = $rol_vendedor; // Guardar el rol en la sesión

    header("Location: index.php?success=" . urlencode("¡Registro de vendedor exitoso! Ya has iniciado sesión."));
    exit();

} catch (Exception $e) {
    $mysqli->rollback(); // Revertir la transacción: algo salió mal
    error_log("Error en guardarreg_vendedor: " . $e->getMessage()); // Para depuración en el log del servidor
    header("Location: registro_vendedor.php?error=" . urlencode("Error al registrar como vendedor: " . $e->getMessage()));
    exit();
}

$mysqli->close();
?>