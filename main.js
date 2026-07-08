// assets/js/main.js
// Modern AJAX cart controls, dynamic theme togglers, and custom animations

document.addEventListener('DOMContentLoaded', function() {
    // Hide Loader
    const loader = document.getElementById('loader-overlay');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = 0;
            setTimeout(() => loader.classList.add('hidden'), 300);
        }, 400);
    }

    // Theme Toggle Handler
    const toggler = document.getElementById('theme-toggler');
    if (toggler) {
        toggler.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            document.cookie = `dark_mode=${isDark}; path=/; max-age=${365 * 24 * 60 * 60}`;
            
            // Update Toggler Icon
            const icon = toggler.querySelector('i');
            if (isDark) {
                icon.className = 'fa-solid fa-sun fs-5';
            } else {
                icon.className = 'fa-solid fa-moon fs-5';
            }
        });
        
        // Initial Icon Set
        const icon = toggler.querySelector('i');
        if (document.body.classList.contains('dark-mode') && icon) {
            icon.className = 'fa-solid fa-sun fs-5';
        }
    }
});

// Toast notification helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `custom-toast`;
    
    let iconClass = 'fa-solid fa-circle-check text-success';
    if (type === 'error') iconClass = 'fa-solid fa-circle-xmark text-danger';
    if (type === 'warning') iconClass = 'fa-solid fa-triangle-exclamation text-warning';

    toast.innerHTML = `<i class="${iconClass}"></i><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = 0;
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Add Item To Cart dynamically
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('api/cart_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            // Update badge count
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.innerText = data.cart_count;
            }
        } else {
            showToast(data.message || 'Error adding item', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Network error, please try again.', 'error');
    });
}
