<?php
// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "pagviajes");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Obtener el término de búsqueda
$busqueda = isset($_GET['q']) ? $mysqli->real_escape_string($_GET['q']) : '';

$resultados = [];
if ($busqueda !== '') {
    $sql = "SELECT * FROM paquetes WHERE nombre LIKE '%$busqueda%' OR destino LIKE '%$busqueda%'";
    $res = $mysqli->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $resultados[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href=indstyle.css>
    <meta charset="UTF-8">
    <title>Resultados de búsqueda</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .search-package-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .search-package-card h3 {
            margin-top: 0;
        }
        .search-package-card p {
            margin: 10px 0;
        }
        .search-back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .search-back-link:hover {
            text-decoration: underline;
        }
        .search-bar-section {
            width: 100%;
            max-width: 700px;
            margin: 40px auto 0 auto;
            padding: 0;
            background: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <section class="search-bar-section">
        <form class="search-form" action="buscar.php" method="get" style="margin: 0 auto;">
            <input type="text" name="q" placeholder="¿A dónde quieres ir? (Ej: París, Caribe...)" class="search-input" value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </section>
    <h1 style="text-align:center; margin-top:30px;">Resultados de búsqueda para "<?php echo htmlspecialchars($busqueda); ?>"</h1>
    <?php if (count($resultados) > 0): ?>
        <div class="search-packages-grid">
            <?php foreach ($resultados as $paquete): ?>
                <article class="search-package-card">
                    <img src="<?php echo htmlspecialchars($paquete['imagen']); ?>" alt="<?php echo htmlspecialchars($paquete['nombre']); ?>" class="package-image">
                    <h3><?php echo htmlspecialchars($paquete['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($paquete['descripcion']); ?></p>
                    <p><strong>Destino:</strong> <?php echo htmlspecialchars($paquete['destino']); ?></p>
                    <p><strong>Precio:</strong> $<?php echo number_format($paquete['precio'], 0, ',', '.'); ?> USD</p>
                    <button class="btn btn-secondary add-to-cart-btn" 
                                data-id="<?php echo $paquete['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($paquete['nombre']); ?>" 
                                data-price="<?php echo $paquete['precio']; ?>">
                            Añadir al Carrito
                        </button>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center;">No se encontraron resultados.</p>
    <?php endif; ?>
    <div style="text-align:center;">
        <a href="index.php" class="search-back-link">Volver al inicio</a>
    </div>


</html></body></body>
</html>