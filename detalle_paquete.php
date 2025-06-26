<?php
session_start();
require_once 'conexion.php'; 

// 1. Verificar si el usuario está logueado para habilitar/deshabilitar el botón de carrito
$isUserLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// 2. Lógica para obtener el paquete
$paquete = null; 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $paquete_id = intval($_GET['id']); 
    // MODIFICADO: Seleccionamos 'descomp' en lugar de 'incluye' y la 'descripcion' normal
    $sql = "SELECT p.id, p.nombre, p.descripcion, p.destino, p.precio, p.duracion, p.descomp, p.imagen, p.rating, u.nombre AS autor_nombre 
            FROM paquetes p
            LEFT JOIN usuario u ON p.id_usuario = u.id
            WHERE p.id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $paquete_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $paquete = $resultado->fetch_assoc();
        } else {
            // Redirigir si el paquete no se encuentra
            header("Location: index.php?error=paquete_no_encontrado");
            exit();
        }
        $stmt->close();
    } else {
        // Log de errores de la base de datos
        error_log("Error al preparar la consulta de detalle de paquete: " . $mysqli->error);
        header("Location: index.php?error=error_bd_detalle");
        exit();
    }
} else {
    // Redirigir si el ID es inválido
    header("Location: index.php?error=id_imvalido");
    exit();
}

// 3. Configurar título de la página y página activa para el header (¡antes de incluirlo!)
$pageTitle = $paquete ? htmlspecialchars($paquete['nombre']) . " - Aventura Global" : "Detalle del Paquete";
$activePage = "paquete"; 

// Incluimos el header.php. Esto abre HTML, HEAD, BODY y carga todos los CSS/Scripts globales.
require_once 'header.php'; 
?>

<style>
    /* Estilos globales y del contenedor general de Bootstrap */
    .container {
        padding-top: 4rem; /* Un poco más de espacio superior */
        padding-bottom: 4rem; 
    }

    /* Contenedor principal de la sección de detalles del paquete (Imagen + Info) */
    .package-main-section {
        background-color: #f8f9fa; /* Un fondo ligeramente gris para que resalte */
        border-radius: 15px; /* Bordes más redondeados */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Sombra más suave y extendida */
        padding: 40px; /* Más padding para un look espacioso */
        margin-bottom: 3rem; /* Espacio antes de la siguiente sección */
        
        max-width: 800px; /* Ancho máximo más reducido para esta sección específica */
        margin-left: auto; /* Centra la sección dentro del .container de Bootstrap */
        margin-right: auto; /* Centra la sección dentro del .container de Bootstrap */
    }

    /* Estilos para la imagen del paquete */
    .package-detail-image {
        width: 100%; 
        height: auto; 
        max-height: 280px; /* Altura máxima para un look de miniatura / integrado */
        object-fit: cover; 
        border-radius: 10px; 
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); /* Sombra más sutil en la imagen */
        display: block;
        margin: 0 auto; /* Centra la imagen dentro de su columna */
        transition: transform 0.3s ease; /* Pequeña animación al pasar el mouse */
    }

    .package-detail-image:hover {
        transform: scale(1.02); /* Ligeramente más grande al pasar el mouse */
    }

    /* Estilos para la información del paquete (lado derecho) */
    .package-info-block {
        padding-left: 25px; /* Más espacio a la izquierda para separar de la imagen */
        padding-top: 10px; /* Pequeño ajuste para la alineación vertical */
    }

    .package-info-block h1 {
        font-size: 2.8rem; /* Título principal más grande y llamativo */
        color: var(--primary-color, #007bff); /* Usa variable CSS o un color por defecto */
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .package-info-block .lead {
        font-size: 1.3rem; /* Tamaño para el destino */
        color: var(--dark-color, #343a40); 
        margin-bottom: 0.8rem;
    }

    .package-info-block .price {
        font-size: 2.2rem; /* Precio prominente */
        color: var(--success-color, #28a745); /* Color de éxito */
        font-weight: 800;
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .package-info-block p {
        font-size: 1.1rem; /* Texto general del paquete */
        margin-bottom: 0.7rem;
        line-height: 1.6;
    }

    .package-info-block small.text-muted {
        display: block;
        margin-top: 1.2rem;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .package-info-block .rating .fas {
        color: #FFD700; /* Estrellas doradas */
        margin-right: 3px;
    }

    /* Estilos para la sección de Descripción y Qué Incluye */
    .package-description-section {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        padding: 40px;

        max-width: 800px; /* Ancho máximo más reducido para esta sección específica */
        margin-left: auto; /* Centra la sección */
        margin-right: auto; /* Centra la sección */
        margin-top: 3rem; /* Asegura espacio si estaba ya con margin-top */
    }

    .package-description-section h3 {
        font-size: 2.2rem; /* Títulos de sección */
        color: var(--primary-color, #007bff);
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 15px;
    }

    .package-description-section h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 80px; /* Línea decorativa debajo del título */
        height: 4px;
        background-color: var(--secondary-color, #6c757d);
        border-radius: 2px;
    }

    .package-description-section p {
        line-height: 1.9; /* Mayor espacio entre líneas para lectura */
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 2rem;
    }

    .package-description-section ul {
        padding-left: 0;
        list-style: none;
    }

    .package-description-section ul li {
        margin-bottom: 15px; /* Más espacio entre los ítems de la lista */
        font-size: 1.1rem;
        color: #444;
        display: flex;
        align-items: flex-start; /* Alinea ícono con el inicio del texto */
    }

    .package-description-section ul li .fas {
        margin-right: 12px;
        color: var(--primary-color, #007bff); /* Color del check-circle */
        font-size: 1.2em; /* Ícono un poco más grande */
        flex-shrink: 0; /* Evita que el ícono se encoja */
    }

    /* Estilos para botones */
    .btn-custom-primary {
        background-color: var(--primary-color, #007bff);
        color: white;
        padding: 1rem 2rem; /* Botones más grandes */
        border-radius: 50px; /* Muy redondeados (pastilla) */
        font-size: 1.15rem;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-custom-primary:hover {
        background-color: #0056b3; /* Un poco más oscuro al pasar el mouse */
        transform: translateY(-2px); /* Efecto de levitación */
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.2);
    }

    .btn-custom-primary:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    .btn-custom-secondary {
        background-color: #6c757d; 
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-size: 1.15rem;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-custom-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.2);
    }

    /* Media Queries para Responsividad */
    @media (max-width: 991.98px) { /* Tablets y dispositivos más pequeños */
        .package-main-section, .package-description-section {
            padding: 30px;
            max-width: 700px; /* Ajusta el ancho máximo para tablets */
        }
        .package-info-block {
            padding-left: 0; 
            margin-top: 2rem; 
        }
        .package-detail-image {
            max-height: 250px; 
        }
        .package-info-block h1 {
            font-size: 2.2rem;
        }
        .package-info-block .price {
            font-size: 1.8rem;
        }
        .package-description-section h3 {
            font-size: 1.8rem;
        }
        .btn-custom-primary, .btn-custom-secondary {
            padding: 0.8rem 1.5rem;
            font-size: 1.05rem;
        }
    }

    @media (max-width: 767.98px) { /* Móviles */
        .package-main-section, .package-description-section {
            padding: 20px;
            max-width: 100%; 
            border-radius: 0; 
            box-shadow: none; 
        }
        .package-detail-image {
            max-height: 200px; 
            margin-bottom: 1.5rem; 
        }
        .package-info-block {
            margin-top: 0; 
        }
        .package-info-block h1 {
            font-size: 1.8rem;
        }
        .package-info-block .lead {
            font-size: 1rem;
        }
        .package-info-block .price {
            font-size: 1.6rem;
        }
        .package-info-block p {
            font-size: 0.95rem;
        }
        .package-description-section h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .package-description-section p, .package-description-section ul li {
            font-size: 0.9rem;
        }
        .package-description-section ul li .fas {
            font-size: 1em;
            margin-right: 8px;
        }
        .btn-custom-primary, .btn-custom-secondary {
            font-size: 0.95rem;
            padding: 0.7rem 1.2rem;
        }
    }
</style>

<div class="container my-5">
    <?php if ($paquete): ?>
        <div class="row g-4 package-main-section">
            <div class="col-md-4 d-flex align-items-center justify-content-center">
                <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" class="package-detail-image" alt="<?php echo htmlspecialchars($paquete['nombre']); ?>">
            </div>
            <div class="col-md-8">
                <div class="package-info-block">
                    <h1 class="mb-3"><?php echo htmlspecialchars($paquete['nombre']); ?></h1>
                    <p class="lead"><strong>Destino:</strong> <?php echo htmlspecialchars($paquete['destino']); ?></p>
                    <p class="price">Precio: $<?php echo number_format($paquete['precio'], 2, ',', '.'); ?> USD</p>
                    <p><strong>Duración:</strong> <?php echo htmlspecialchars($paquete['duracion']); ?> días</p>
                    <p class="rating"><strong>Rating:</strong> 
                        <?php 
                        for ($i = 0; $i < floor($paquete['rating']); $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        if ($paquete['rating'] - floor($paquete['rating']) >= 0.5) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        }
                        ?>
                        (<?php echo htmlspecialchars($paquete['rating']); ?>)
                    </p>
                    <p><small class="text-muted">Publicado por: 
                        <?php 
                        if (!empty($paquete['autor_nombre'])) {
                            echo htmlspecialchars($paquete['autor_nombre']);
                        } else {
                            echo "Aventura Global";
                        }
                        ?>
                    </small></p>
                    
                    <div class="mt-4 pt-3 border-top">
                        <button class="btn btn-custom-primary w-100 mb-3" 
                                data-id="<?php echo $paquete['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($paquete['nombre']); ?>" 
                                data-price="<?php echo $paquete['precio']; ?>"
                                <?php echo $isUserLoggedIn ? '' : 'disabled title="Debes iniciar sesión para añadir al carrito"'; ?>>
                            <i class="fas fa-cart-plus me-2"></i> Añadir al Carrito
                        </button>
                        
                        <a href="index.php" class="btn btn-custom-secondary w-100"><i class="fas fa-arrow-left me-2"></i> Volver a los paquetes</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="package-description-section mt-5">
            <h3 class="mb-3">Descripción Completa</h3>
            <p><?php echo nl2br(htmlspecialchars($paquete['descomp'])); ?></p> 

            </div>

    <?php else: ?>
        <div class="alert alert-danger text-center py-5">
            <h2 class="mb-4">¡Oops!</h2>
            <p class="lead">El paquete solicitado no pudo ser encontrado.</p>
            <a href="index.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left me-2"></i> Volver al inicio</a>
        </div>
    <?php endif; ?>
</div>

<?php 
// Pasar la variable PHP a JavaScript para su uso en scripts del carrito (si aplica)
echo '<script type="text/javascript">';
echo 'const isUserLoggedIn = ' . ($isUserLoggedIn ? 'true' : 'false') . ';';
echo '</script>';
?>

<?php 
require_once 'footer.php'; 
$mysqli->close(); // Cerrar la conexión a la base de datos al final
?>