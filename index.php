<?php
require_once 'conexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario está logueado.
$isUserLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true; 

$cartItemsCount = 0;

if (isset($_SESSION['session_id_carrito'])) {
    $sessionIdCarrito = $_SESSION['session_id_carrito'];

    $stmt = $mysqli->prepare("SELECT SUM(cantidad) AS total_productos FROM carrito WHERE session_id = ?");

    if ($stmt) {
        $stmt->bind_param("s", $sessionIdCarrito);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if ($row && $row['total_productos'] !== null) {
            $cartItemsCount = (int)$row['total_productos'];
        }
        $stmt->close();
    }
}

$pageTitle = "Aventura Global - Tu Próximo Destino";
$activePage = "inicio"; 

require_once 'header.php';

// Obtener los paquetes junto con el nombre de la empresa del autor
$paquetes = [];
// MODIFICACIÓN CRUCIAL AQUÍ:
// 1. Seleccionamos 'u.nombre_empresa'.
// 2. Usamos COALESCE para el valor por defecto 'Aventura Global' si 'nombre_empresa' es NULL.
$sql = "SELECT p.*, COALESCE(u.nombre_empresa, 'Aventura Global') AS autor_display_name
        FROM paquetes p
        LEFT JOIN usuario u ON p.id_usuario = u.id
        ORDER BY p.id DESC"; // Puedes ajustar el orden si lo deseas

$res = $mysqli->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $paquetes[] = $row;
    }
    $res->free(); // Liberar el conjunto de resultados
} else {
    error_log("Error al obtener paquetes con autor: " . $mysqli->error);
}
?>


<section class="hero-section">
    <div class="hero-content">
        <h1>Encuentra tu Próxima Aventura</h1>
        <p style="margin-left:0 auto ; margin-right:0 auto ;">Explora los destinos más increíbles del mundo. Preparamos el viaje de tus sueños.</p>
        <form class="search-form" action="buscar.php" method="get" style="margin: 0 auto;">
            <input type="text" name="q" placeholder="¿A dónde quieres ir? (Ej: París, Caribe...)" class="search-input">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
</section>

<section class="featured-destinations">
    <h2 class="section-title" id="Pop">Destinos Populares</h2>
    <div class="packages-grid">

        <?php 
        if (!empty($paquetes)) {
            foreach ($paquetes as $paquete): 
        ?>
                <article class="package-card">
                    <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="<?php echo htmlspecialchars($paquete['nombre']); ?>" class="package-image">
                    <div class="package-info">
                        <h3 class="package-title"><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                        <p class="package-description">
                            <?php 
                            // Aquí usamos nl2br para los saltos de línea al mostrar en HTML (si los datos ya están limpios de \r\n)
                            $display_description = nl2br(htmlspecialchars($paquete['descripcion']));
                            if (strlen($display_description) > 150) { // Limita a 150 caracteres
                                echo substr($display_description, 0, 150) . "...";
                            } else {
                                echo $display_description;
                            }
                            ?>
                        </p>
                        <div class="package-details">
                            <span class="package-rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($paquete['rating']); ?></span>
                        </div>
                        
                        <p class="package-author">
                            <small class="text-muted">Publicado por: 
                                <?php 
                                // MOSTRAMOS LA NUEVA COLUMNA ALIASADA
                                echo htmlspecialchars($paquete['autor_display_name']);
                                ?>
                            </small>
                        </p>

                        <div class="package-footer">
                            <p class="package-price">$<?php echo number_format($paquete['precio'], 0, ',', '.'); ?> USD</p>
                            <a href="detalle_paquete.php?id=<?php echo $paquete['id']; ?>" class="btn btn-primary">Ver Detalles</a>
                        </div>
                    </div>
                </article>
        <?php 
            endforeach; 
        } else {
            echo "<p>No hay paquetes disponibles en este momento.</p>"; 
        }
        ?>
    </div>
</section>

<?php
echo '<script type="text/javascript">';
echo 'const isUserLoggedIn = ' . ($isUserLoggedIn ? 'true' : 'false') . ';';
echo '</script>';

require_once 'footer.php';
$mysqli->close(); // Cierra la conexión a la BD al final del script
?>