<?php
// --- INICIO: LÍNEAS PARA DEPURACIÓN (¡IMPORTANTE: Desactivar o comentar en un entorno de producción!) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN: LÍNEAS PARA DEPURACIÓN ---

session_start();
require_once 'conexion.php'; // Asegúrate de que tu archivo de conexión a la base de datos sea correcto

// Verificar si el usuario está logueado y si es un vendedor o administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    $_SESSION['mensaje_error'] = "Acceso denegado. Debes ser vendedor o administrador para añadir paquetes.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar y sanear los datos del formulario.
    // Usamos filter_input para una sanitización robusta y segura.
    // ¡IMPORTANTE!: NO usamos mysqli->real_escape_string() aquí.
    // bind_param se encarga del escape de datos para la base de datos de forma segura.
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $descomp = trim(filter_input(INPUT_POST, 'descomp', FILTER_SANITIZE_STRING));
    $destino = trim(filter_input(INPUT_POST, 'destino', FILTER_SANITIZE_STRING));
    
    // Validar y sanear los campos numéricos
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $duracion = filter_input(INPUT_POST, 'duracion', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT); 
    
    $id_usuario_creador = $_SESSION['id']; // El ID del usuario que está creando el paquete

    // Validación básica de campos obligatorios y valores válidos
    if (empty($nombre) || empty($descripcion) || empty($descomp) || empty($destino) || 
        $precio === false || $precio <= 0 || 
        $duracion === false || $duracion <= 0 || 
        $rating === false || $rating < 1 || $rating > 5) {
        
        $_SESSION['mensaje_error'] = "Por favor, completa todos los campos obligatorios y asegúrate que los valores sean válidos (precio/duración > 0, rating entre 1 y 5).";
        header("Location: añadir_paquete.php"); // Redirige de nuevo al formulario de añadir
        exit();
    }

    $ruta_imagen = ''; // Inicializamos la ruta de la imagen

    // --- MANEJO DE LA IMAGEN DE SUBIDA ---
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/Portadas/"; 
        // Genera un nombre de archivo único para evitar colisiones
        $image_name = uniqid() . "_" . str_replace(" ", "_", basename($_FILES["imagen"]["name"]));
        $target_file = $target_dir . $image_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Verificar si el directorio de destino existe, si no, intenta crearlo
        if (!is_dir($target_dir)) {
            // Intenta crear el directorio con permisos 0777 (para desarrollo, ajustar en producción)
            if (!mkdir($target_dir, 0777, true)) { 
                $_SESSION['mensaje_error'] = "Error: No se pudo crear el directorio de subida de imágenes. Verifica los permisos del servidor.";
                $uploadOk = 0;
            }
        }

        // Verificar si el archivo es una imagen real
        if ($uploadOk) {
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if($check === false) {
                $_SESSION['mensaje_error'] = "El archivo subido no es una imagen o está corrupto.";
                $uploadOk = 0;
            }
        }
        
        // Verificar el tamaño del archivo (máximo 5MB)
        if ($uploadOk && $_FILES["imagen"]["size"] > 5000000) { 
            $_SESSION['mensaje_error'] = "Lo siento, la imagen es demasiado grande. El tamaño máximo permitido es 5MB.";
            $uploadOk = 0;
        }

        // Permitir solo ciertos formatos de archivo
        if($uploadOk && !in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            $_SESSION['mensaje_error'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF para las imágenes.";
            $uploadOk = 0;
        }

        // Si alguna validación de subida falla, redirige con el mensaje de error
        if ($uploadOk == 0) {
            header("Location: añadir_paquete.php");
            exit();
        } else {
            // Si todas las validaciones pasan, intentar mover el archivo subido
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                $ruta_imagen = $target_file;
            } else {
                $_SESSION['mensaje_error'] = "Hubo un error al subir la imagen. Código de error: " . $_FILES["imagen"]["error"];
                error_log("Error al mover el archivo subido: " . $_FILES["imagen"]["tmp_name"] . " a " . $target_file); // Log para depuración
                header("Location: añadir_paquete.php");
                exit();
            }
        }
    } else {
        // Si no se subió una imagen o hubo un error en la subida inicial
        $_SESSION['mensaje_error'] = "Debes seleccionar una imagen para el paquete.";
        header("Location: añadir_paquete.php");
        exit();
    }
    // --- FIN MANEJO DE LA IMAGEN ---

    // Insertar el nuevo paquete en la base de datos usando sentencias preparadas
    $stmt = $mysqli->prepare("INSERT INTO paquetes (nombre, descripcion, destino, precio, duracion, descomp, imagen, rating, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // Enlazar los parámetros a la consulta preparada.
        // Los tipos ('s': string, 'd': double/float, 'i': integer) deben coincidir con tus columnas.
        $stmt->bind_param(
            "sssdissdi", 
            $nombre, 
            $descripcion, 
            $destino, 
            $precio, 
            $duracion, 
            $descomp, 
            $ruta_imagen, 
            $rating, 
            $id_usuario_creador
        );
        
        if ($stmt->execute()) {
            $_SESSION['mensaje_exito'] = "¡Paquete '$nombre' añadido exitosamente!";
            header("Location: panel_vendedor.php"); // Redirige al panel del vendedor después de añadir
            exit();
        } else {
            $_SESSION['mensaje_error'] = "Error al añadir el paquete a la base de datos: " . $stmt->error;
            error_log("Error de MySQL al insertar un paquete: " . $stmt->error); // Log el error para depuración
            header("Location: añadir_paquete.php"); // Redirige de vuelta al formulario con el error
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje_error'] = "Error en la preparación de la consulta SQL (INSERT): " . $mysqli->error;
        error_log("Error de MySQL en prepare (añadir paquete): " . $mysqli->error); // Log el error para depuración
        header("Location: añadir_paquete.php"); // Redirige de vuelta al formulario con el error
        exit();
    }
} else {
    // Si la solicitud no es POST (acceso directo a este archivo sin enviar formulario)
    $_SESSION['mensaje_error'] = "Acceso inválido al procesador de paquetes. Se esperaba un método POST.";
    header("Location: panel_vendedor.php");
    exit();
}

$mysqli->close(); // Cerrar la conexión a la base de datos
?>