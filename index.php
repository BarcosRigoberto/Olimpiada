<?php
$pageTitle = "Aventura Global - Tu Próximo Destino";
$activePage = "inicio"; 

require_once 'header.php';

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "pagviajes");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Obtener los paquetes
$paquetes = [];
$sql = "SELECT * FROM paquetes";
$res = $mysqli->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $paquetes[] = $row;
    }
}
?>
<link rel="stylesheet" type="text/css" href="indstyle.css">


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

<?php if (isset($_SESSION['username'])): // Podés validar que esté logueado ?>
    <div style="text-align: right; margin: 20px;">
        <a href="subir_producto.php" class="btn btn-success" style="padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">
            + Agregar nuevo paquete
        </a>
    </div>
<?php endif; ?>

<section class="featured-destinations">
    <h2 class="section-title" id="Pop">Destinos Populares</h2>
    <div class="packages-grid">

        <?php foreach ($paquetes as $paquete): ?>
            <article class="package-card">
                <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="<?php echo htmlspecialchars($paquete['nombre']); ?>" class="package-image">
                <div class="package-info">
                    <h3 class="package-title"><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                    <p class="package-description"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                    <div class="package-details">
                        
                        <span class="package-rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($paquete['rating']); ?></span>
                    </div>
                    <div class="package-footer">
                        <p class="package-price">$<?php echo number_format($paquete['precio'], 0, ',', '.'); ?> USD</p>
                        <button class="btn btn-secondary add-to-cart-btn" 
                                data-id="<?php echo $paquete['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($paquete['nombre']); ?>" 
                                data-price="<?php echo $paquete['precio']; ?>">
                            Añadir al Carrito
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

    </div>
</section>

<?php

require_once 'footer.php';
?>