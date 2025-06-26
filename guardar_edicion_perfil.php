<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$username_nuevo = trim($_POST['username']);

$conn = new mysqli("localhost", "root", "", "pagviajes");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validar si se quiere cambiar la foto
$foto_perfil_path = $_SESSION['foto_perfil'];

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['foto_perfil']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_ext, $allowed_ext)) {
        $upload_dir = "uploads/profiles/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $nuevo_nombre = uniqid('profile_', true) . '.' . $file_ext;
        $ruta_final = $upload_dir . $nuevo_nombre;

        if (move_uploaded_file($file_tmp, $ruta_final)) {
            $foto_perfil_path = $ruta_final;
        }
    }
}

// Actualizar en la BD
$sql = "UPDATE usuario SET username = ?, foto_perfil = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $username_nuevo, $foto_perfil_path, $usuario_id);

if ($stmt->execute()) {
    $_SESSION['username'] = $username_nuevo;
    $_SESSION['foto_perfil'] = $foto_perfil_path;
    header("Location: usuario.php?actualizado=1");
    exit();
} else {
    header("Location: editar_usuario.php?error=1");
    exit();
}