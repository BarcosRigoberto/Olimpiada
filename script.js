document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA DEL CARRITO DE COMPRAS ---

    const cartCountElement = document.getElementById('cart-count');
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    // Inicializamos el contador del carrito.
    // En un proyecto real, este valor vendría de la sesión del usuario o de la base de datos.
    let cartItemCount = 0;

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Evita que la página se recargue

            // Obtenemos los datos del producto desde los atributos 'data-*' del botón
            const packageId = this.dataset.id;
            const packageName = this.dataset.name;
            const packagePrice = this.dataset.price;

            console.log(`Añadido al carrito: ${packageName} (ID: ${packageId}, Precio: $${packagePrice})`);
            
            // Incrementamos el contador visual del carrito
            cartItemCount++;
            updateCartCount();

            // Damos feedback visual al usuario
            showAddedFeedback(this);

            // Aquí, en un futuro, llamaríamos a una función para añadir el item al
            // localStorage o enviar una petición al servidor (backend) para guardarlo en la base de datos.
            // Ejemplo: addItemToCartSession(packageId, packageName, packagePrice);
        });
    });

    function updateCartCount() {
        cartCountElement.textContent = cartItemCount;
        // Agregamos una pequeña animación para llamar la atención
        cartCountElement.classList.add('animate');
        setTimeout(() => {
            cartCountElement.classList.remove('animate');
        }, 300); // La duración de la animación en CSS
    }

    function showAddedFeedback(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '¡Añadido! <i class="fas fa-check"></i>';
        button.disabled = true;
        button.style.backgroundColor = '#28a745'; // Color verde de éxito

        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
            button.style.backgroundColor = ''; // Vuelve al color original del CSS
        }, 2000); // El mensaje dura 2 segundos
    }

    // --- CSS ADICIONAL PARA LA ANIMACIÓN (se inyecta con JS) ---
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .cart-count.animate {
            animation: pop 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);

});