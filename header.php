<?php
// header.php

// Esto debe ser lo primero, sin espacios ni HTML antes.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ruta a la imagen de perfil por defecto
$default_profile_image = 'assets/img/default_avatar.png'; 

// Determina la URL de la imagen de perfil a mostrar
$profile_image_url = $default_profile_image;
if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
    $profile_image_url = htmlspecialchars($_SESSION['foto_perfil']);
}

// Para resaltar la página activa en el nav
$activePage = $activePage ?? '';

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
    
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Estilos para el contenedor del icono de perfil y el menú desplegable */
        .profile-dropdown {
            position: relative;
            display: inline-block;
            vertical-align: middle;
            /* Elimina los estilos de hover del CSS para que JavaScript los controle */
        }

        .profile-dropdown-toggle {
            display: block;
            padding: 0;
            cursor: pointer;
            border: none;
            background: none;
            line-height: 1;
            /* Asegúrate de que el focus state sea visible para accesibilidad */
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
        .profile-dropdown-toggle:focus .profile-icon { /* Añadido focus state */
            border-color: var(--secondary-color, #0056b3);
        }

        .dropdown-content {
            display: none; /* Por defecto oculto, JS lo mostrará */
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

        /* Clase que JS añadirá para mostrar el menú */
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

        /* Ajuste para los enlaces de login/registro cuando no hay sesión */
        .navbar-right .nav-link {
            padding: 8px 15px;
        }

        /* Asegurar que los elementos del navbar-right estén alineados */
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Aventura Global ✈️</a>
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
                            <a href="logout.php">Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-link <?php echo ($activePage === 'login') ? 'active' : ''; ?>">Iniciar Sesión</a>
                    <a href="registro.php" class="nav-link <?php echo ($activePage === 'registro') ? 'active' : ''; ?>">Registrarse</a>
                <?php endif; ?>
                <a href="carrito.php" class="nav-link cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </nav>
    </header>

    <main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileToggle = document.getElementById('profileDropdownToggle');
            const profileContent = document.getElementById('profileDropdownContent');

            if (profileToggle && profileContent) {
                // Función para mostrar/ocultar el menú
                function toggleDropdown() {
                    profileContent.classList.toggle('show');
                    profileToggle.setAttribute('aria-expanded', profileContent.classList.contains('show'));
                }

                // Evento click en el icono/botón de perfil
                profileToggle.addEventListener('click', function(event) {
                    event.stopPropagation(); // Evita que el clic se propague al document
                    toggleDropdown();
                });

                // Cierra el menú si se hace clic fuera de él
                document.addEventListener('click', function(event) {
                    if (!profileToggle.contains(event.target) && !profileContent.contains(event.target)) {
                        if (profileContent.classList.contains('show')) {
                            profileContent.classList.remove('show');
                            profileToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                // Opcional: Cerrar el menú si se presiona ESC
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && profileContent.classList.contains('show')) {
                        profileContent.classList.remove('show');
                        profileToggle.setAttribute('aria-expanded', 'false');
                        profileToggle.focus(); // Devuelve el foco al botón de alternar
                    }
                });
            }
        });
    </script>