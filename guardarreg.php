<?php
session_start();

require_once 'conexion.php';

// Asegúrate de que los datos vengan por POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: registro.php"); // Redirigir si no es una solicitud POST
    exit();
}

// Obtener y limpiar datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']); // Nombre del campo de contraseña del formulario es 'password'

// Validar que los campos obligatorios no estén vacíos
if (empty($nombre) || empty($apellido) || empty($username) || empty($email) || empty($password)) {
    header("Location: registro.php?error=" . urlencode("Todos los campos obligatorios deben ser completados."));
    exit();
}

// Verificar si el usuario o email ya existen
$sql_check = "SELECT id FROM usuario WHERE username = ? OR email = ?";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();

if ($resultado_check->num_rows > 0) {
    header("Location: registro.php?error=" . urlencode("Ese nombre de usuario o email ya está registrado."));
    exit();
}
$stmt_check->close(); // Cierra el statement después de usarlo

// --- Manejo de la subida de la foto de perfil ---
// Ruta de la imagen de perfil por defecto
$foto_perfil_path = 'uploads/profiles/default.png'; 

// Verificar si se ha subido un archivo sin errores
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['foto_perfil']['tmp_name'];
    $file_name = $_FILES['foto_perfil']['name'];
    $file_size = $_FILES['foto_perfil']['size'];
    $file_type = $_FILES['foto_perfil']['type'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    $max_file_size = 2 * 1024 * 1024; // 2MB

    // Validar extensión
    if (!in_array($file_ext, $allowed_extensions)) {
        header("Location: registro.php?error=" . urlencode("Formato de imagen no permitido. Solo JPG, JPEG, PNG, GIF."));
        exit();
    }

    // Validar tamaño
    if ($file_size > $max_file_size) {
        header("Location: registro.php?error=" . urlencode("La imagen es demasiado grande. Máximo 2MB."));
        exit();
    }

    // Crear un nombre único para la imagen para evitar colisiones
    $new_file_name = uniqid('profile_', true) . '.' . $file_ext;
    $upload_directory = 'uploads/profiles/'; // Directorio donde se guardarán las imágenes

    // Asegúrate de que el directorio exista y tenga permisos de escritura
    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0755, true); // Crea el directorio si no existe con permisos 0755
    }

    $destination = $upload_directory . $new_file_name;

    // Mover el archivo subido de la ubicación temporal a la ubicación deseada
    if (move_uploaded_file($file_tmp_name, $destination)) {
        $foto_perfil_path = $destination; // Si se sube correctamente, usa esta ruta
    } else {
        // Si hay un error al mover el archivo, se usará la imagen por defecto y se puede loggear el error
        error_log("Error al mover el archivo subido: " . $_FILES['foto_perfil']['error'] . " para el usuario: " . $username);
        // La $foto_perfil_path ya está establecida a la por defecto, así que no se necesita cambiar aquí
    }
}

// Hashear la contraseña antes de guardarla en la BD
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario en la base de datos, incluyendo la foto de perfil
// Asegúrate de que tu tabla 'usuario' tenga una columna 'foto_perfil' (VARCHAR)
$sql_insert = "INSERT INTO usuario (nombre, apellido, username, email, contraseña, foto_perfil) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_insert = $mysqli->prepare($sql_insert);
$stmt_insert->bind_param("ssssss", $nombre, $apellido, $username, $email, $hashed_password, $foto_perfil_path);

if ($stmt_insert->execute()) {
    // Registro exitoso, iniciar sesión automáticamente
    $_SESSION['id'] = $mysqli->insert_id; // Obtiene el ID del usuario recién insertado
    $_SESSION['username'] = $username;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['foto_perfil'] = $foto_perfil_path; // Guardar la ruta de la foto en la sesión
    
    // *** AÑADE ESTA LÍNEA CLAVE: Marcar al usuario como logueado ***
    $_SESSION['logged_in'] = true; 

    header("Location: index.php?success=" . urlencode("¡Registro exitoso! Ya has iniciado sesión."));
    exit();
} else {
    // Error al registrar
    header("Location: registro.php?error=" . urlencode("Error al registrar: " . $stmt_insert->error));
    exit();
}

$stmt_insert->close();
$mysqli->close();

?>