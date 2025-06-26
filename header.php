<?php
// header.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php'; 

$default_profile_image = 'uploads/profiles/default.jpg'; 
$profile_image_url = $default_profile_image;
if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
    $profile_image_url = htmlspecialchars($_SESSION['foto_perfil']);
}

$activePage = $activePage ?? '';

// --- NUEVA LÓGICA PARA EL CONTADOR DEL CARRITO ---
$cartItemsCount = 0; // Valor por defecto

// Solo intenta obtener el contador si el usuario es un comprador o no está logueado
// y si existe una session_id_carrito
if ( (!isset($_SESSION['logged_in']) || (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['rol']) && $_SESSION['rol'] === 'comprador')) && isset($_SESSION['session_id_carrito'])) {
    $sessionIdCarrito = $_SESSION['session_id_carrito'];
    
    // Consulta para contar el total de ítems distintos o la suma de cantidades
    // Si quieres contar el NÚMERO DE PAQUETES DISTINTOS en el carrito:
    $stmt = $mysqli->prepare("SELECT COUNT(id) AS total_items FROM carrito WHERE session_id = ?");
    
    // Si quieres sumar la CANTIDAD TOTAL de productos (ej. 2 remeras + 3 pantalones = 5 items):
    // $stmt = $mysqli->prepare("SELECT SUM(cantidad) AS total_items FROM carrito WHERE session_id = ?");

    if ($stmt) {
        $stmt->bind_param("s", $sessionIdCarrito);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $cartItemsCount = $row['total_items'] ?? 0;
        $stmt->close();
    }
}
// --- FIN NUEVA LÓGICA ---

?>
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
    
    <link rel="stylesheet" href="indstyle.css">

    <script>
        // Esta variable se define globalmente, DISPONIBLE para script.js
        // Ahora $cartItemsCount siempre tendrá el valor correcto de la BD
        const initialCartItemCount = <?php echo $cartItemsCount; ?>;
    </script>

    <style>
        /* ... Tus estilos existentes ... */
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .cart-count.animate {
            animation: pop 0.3s ease-out;
        }
        /* Tus estilos para el perfil y dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
            vertical-align: middle;
        }
        .profile-dropdown-toggle {
            display: block;
            padding: 0;
            cursor: pointer;
            border: none;
            background: none;
            line-height: 1;
            outline: none; 
        }
        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color, #007bff);
            vertical-align: middle;
            transition: border-color 0.3s ease;
        }
        .profile-icon:hover,
        .profile-dropdown-toggle:focus .profile-icon {
            border-color: var(--secondary-color, #0056b3);
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            right: 0;
            border-radius: 5px;
            overflow: hidden;
            padding: 5px 0;
            margin-top: 10px;
        }
        .dropdown-content.show {
            display: block;
        }
        .dropdown-content a {
            color: #333;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            text-align: left;
            white-space: nowrap;
            transition: background-color 0.2s ease;
        }
        .dropdown-content a:hover {
            background-color: #e9e9e9;
        }
        .navbar-right .nav-link {
            padding: 8px 15px;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        /* Estilos para el contador del carrito */
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .cart-count.animate {
            animation: pop 0.3s ease-out;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">AVENTURA GLOBAL</a>
            <ul class="navbar-nav">
                <li><a href="index.php" class="nav-link <?php echo ($activePage === 'inicio') ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="#Pop" class="nav-link <?php echo ($activePage === 'destinos') ? 'active' : ''; ?>">Destinos</a></li>
                <li><a href="#footer" class="nav-link <?php echo ($activePage === 'contacto') ? 'active' : ''; ?>">Contacto</a></li>
            </ul>
            <div class="navbar-right">
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="profile-dropdown">
                        <button class="profile-dropdown-toggle" id="profileDropdownToggle" aria-haspopup="true" aria-expanded="false">
                            <img src="<?php echo $profile_image_url; ?>" alt="Perfil" class="profile-icon">
                        </button>
                        <div class="dropdown-content" id="profileDropdownContent">
                            <a href="usuario.php">Mi Perfil</a>
                            
                            <?php if (isset($_SESSION['rol'])): ?>
                                <?php if ($_SESSION['rol'] === 'comprador'): ?>
                                    <a href="mis_reservas.php">Mis Reservas</a>
                                <?php elseif ($_SESSION['rol'] === 'vendedor'): ?>
                                    <a href="panel_vendedor.php">Mi Panel de Vendedor</a>
                                <?php elseif ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="panel_admin.php">Panel de Administración</a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <a href="logout.php">Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-link <?php echo ($activePage === 'login') ? 'active' : ''; ?>">Iniciar Sesión</a>
                    <a href="registro.php" class="nav-link <?php echo ($activePage === 'registro') ? 'active' : ''; ?>">Registrarse</a>
                <?php endif; ?>

                <?php 
                // Mostrar el carrito solo si el usuario no está logueado O si es un comprador
                if (!isset($_SESSION['logged_in']) || (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['rol']) && $_SESSION['rol'] === 'comprador')): 
                ?>
                    <a href="carrito.php" class="nav-link cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count" class="cart-count"><?php echo $cartItemsCount; ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileToggle = document.getElementById('profileDropdownToggle');
            const profileContent = document.getElementById('profileDropdownContent');
            const cartCountElement = document.getElementById('cart-count'); 
            
            if (cartCountElement) {
                cartCountElement.textContent = initialCartItemCount;
            }

            if (profileToggle && profileContent) {
                function toggleDropdown() {
                    profileContent.classList.toggle('show');
                    profileToggle.setAttribute('aria-expanded', profileContent.classList.contains('show'));
                }

                profileToggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    toggleDropdown();
                });

                document.addEventListener('click', function(event) {
                    if (!profileToggle.contains(event.target) && !profileContent.contains(event.target)) {
                        if (profileContent.classList.contains('show')) {
                            profileContent.classList.remove('show');
                            profileToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && profileContent.classList.contains('show')) {
                        profileContent.classList.remove('show');
                        profileToggle.setAttribute('aria-expanded', 'false');
                        profileToggle.focus();
                    }
                });
            }
        });

        function updateCartCount(newCount) {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = newCount;
                cartCountElement.classList.add('animate');
                setTimeout(() => {
                    cartCountElement.classList.remove('animate');
                }, 300); 
            }
        }
    </script>