<?php
$pageTitle = "Aventura Global - Tu Próximo Destino";
$activePage = "inicio"; 

require_once 'header.php';


?>
<link rel="stylesheet" type="text/css" href="indstyle.css">


<section class="hero-section">
    <div class="hero-content">
        <h1>Encuentra tu Próxima Aventura</h1>
        <p>Explora los destinos más increíbles del mundo. Preparamos el viaje de tus sueños.</p>
        <form class="search-form">
            <input type="text" placeholder="¿A dónde quieres ir? (Ej: París, Caribe...)" class="search-input">
            <button type="submit" class="btn btn-primary">Buscar</button>
            
        </form>
    </div>
</section>

<section class="featured-destinations">
    <h2 class="section-title">Destinos Populares</h2>
    <div class="packages-grid">

        <?php foreach ($paquetes as $paquete): ?>
            <article class="package-card">
                <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="<?php echo htmlspecialchars($paquete['nombre']); ?>" class="package-image">
                <div class="package-info">
                    <h3 class="package-title"><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                    <p class="package-description"><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                    <div class="package-details">
                        <span class="package-duration"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($paquete['duracion']); ?></span>
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