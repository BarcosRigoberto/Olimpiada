<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Aventura Global'; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="main-header">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Aventura Global ✈️</a>
            <ul class="navbar-nav">
                <li><a href="index.php" class="nav-link <?php echo ($activePage === 'inicio') ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="#" class="nav-link <?php echo ($activePage === 'destinos') ? 'active' : ''; ?>">Destinos</a></li>
                <li><a href="#" class="nav-link <?php echo ($activePage === 'ofertas') ? 'active' : ''; ?>">Ofertas</a></li>
                <li><a href="#" class="nav-link <?php echo ($activePage === 'contacto') ? 'active' : ''; ?>">Contacto</a></li>
            </ul>
            <div class="navbar-right">
                <a href="usuario.php" class="nav-link">Mi Cuenta</a>
                <a href="carrito.php" class="nav-link cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </nav>
    </header>

    <main>