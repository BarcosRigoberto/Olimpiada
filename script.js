document.addEventListener('DOMContentLoaded', function() {

    // --- CÓDIGO DEL PERFIL DROPDOWN (MOVÍDO AQUÍ DESDE HEADER.PHP) ---
    const profileToggle = document.getElementById('profileDropdownToggle');
    const profileContent = document.getElementById('profileDropdownContent');

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

    // --- LÓGICA DEL CARRITO DE COMPRAS ---

    const cartCountElement = document.getElementById('cart-count');
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    let cartItemCount = parseInt(cartCountElement.textContent) || 0;
    
    function updateCartCount() {
        if (cartCountElement) {
            cartCountElement.textContent = cartItemCount;
            cartCountElement.classList.add('animate');
            setTimeout(() => {
                cartCountElement.classList.remove('animate');
            }, 300);
        }
    }

    function showAddedFeedback(button) {
        const originalText = button.innerHTML;
        const originalBgColor = button.style.backgroundColor;
        const originalColor = button.style.color;

        button.innerHTML = '¡Añadido! <i class="fas fa-check"></i>';
        button.disabled = true; 
        button.style.backgroundColor = '#28a745';
        button.style.color = 'white';

        setTimeout(() => {
            button.innerHTML = originalText;
            // Solo vuelve a habilitar el botón si el usuario está logueado
            if (isUserLoggedIn) {
                button.disabled = false; 
            }
            button.style.backgroundColor = originalBgColor;
            button.style.color = originalColor;
        }, 2000);
    }

    // --- LÓGICA PRINCIPAL: MANEJO DEL CLIC EN EL BOTÓN AÑADIR AL CARRITO ---

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            // Bloquea la acción si el usuario no está logueado
            if (!isUserLoggedIn) {
                alert('Debes iniciar sesión para añadir productos al carrito.');
                // Opcional: Redirigir a la página de login si lo prefieres
                // window.location.href = 'login.php'; 
                return; // Detiene la ejecución del fetch
            }

            const packageId = this.dataset.id;
            const packageName = this.dataset.name;
            const packagePrice = this.dataset.price;

            showAddedFeedback(this); // Muestra feedback visual inmediatamente

            fetch('agregar_carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_paquete=${packageId}&nombre_paquete=${encodeURIComponent(packageName)}&precio_paquete=${packagePrice}`
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    console.error('La respuesta del servidor no es JSON. Contenido:', response.text());
                    return response.text().then(text => { throw new Error('Respuesta no JSON: ' + text); });
                }
            })
            .then(data => {
                if (data.success) {
                    cartItemCount++;
                    updateCartCount();
                    console.log('¡Paquete añadido al carrito con éxito en el servidor!');
                } else {
                    // Si el servidor indica que no está logueado, también alertamos
                    if (data.message === 'No autorizado: Debes iniciar sesión para añadir productos al carrito.') {
                        alert('Debes iniciar sesión para añadir productos al carrito.');
                        // Opcional: Redirigir
                        // window.location.href = 'login.php'; 
                    } else {
                        alert('Error al añadir el paquete: ' + data.message);
                    }
                    console.error('Error del servidor:', data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición Fetch:', error);
                alert('Hubo un error al conectar con el servidor o procesar la respuesta.');
            });
        });
    });

    // Esta llamada es importante para que el contador muestre el valor inicial al cargar la página
    updateCartCount();

});