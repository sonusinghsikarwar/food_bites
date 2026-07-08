// assets/js/menu.js

// Change Main Card Image
function setCardMainImg(productId, src) {
    const mainImg = document.getElementById(`card-main-img-${productId}`);
    if (mainImg) {
        mainImg.style.opacity = '0.3';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 120);
    }
}

// Toggle Wishlist status helper
function toggleWishlist(productId) {
    const btn = document.getElementById(`wish-btn-${productId}`);
    if (!btn) return;
    const icon = btn.querySelector('i');
    if (icon.classList.contains('fa-regular')) {
        icon.className = 'fa-solid fa-heart text-danger';
        showToast('Added to your wishlist!', 'success');
    } else {
        icon.className = 'fa-regular fa-heart text-danger';
        showToast('Removed from your wishlist.', 'info');
    }
}

// Grid selectors quantity adjustments
function changeGridQty(productId, amount) {
    const qtyInput = document.getElementById(`grid-qty-${productId}`);
    if (!qtyInput) return;
    let qty = parseInt(qtyInput.value) + amount;
    if (qty < 1) qty = 1;
    qtyInput.value = qty;
}

// Add Item using quantity selectors
function addGridItemToCart(productId) {
    const qtyInput = document.getElementById(`grid-qty-${productId}`);
    if (!qtyInput) return;
    const qty = parseInt(qtyInput.value);
    addToCart(productId, qty);
}

// Open Quick View Modal via AJAX with Frequently Bought Suggestion
function openQuickView(productId) {
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    const container = document.getElementById('quickview-content');
    
    // Premium loading state inside modal
    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-2 text-muted text-xs">Curating Chef's Kiss details...</p>
        </div>
    `;
    modal.show();

    fetch(`api/get_product.php?id=${productId}`)
    .then(res => res.text())
    .then(html => {
        container.innerHTML = html;
    })
    .catch(() => {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-danger">
                <p>Failed to load premium product details. Please try again.</p>
            </div>
        `;
    });
}

// Add combo package directly to cart
function addComboToCart() {
    addToCart(1, 1);
    setTimeout(() => addToCart(8, 1), 300);
    setTimeout(() => addToCart(4, 1), 600);
    setTimeout(() => {
        Swal.fire({
            title: 'Combo Pack Added!',
            text: 'Classic Burger, Fries, and Soda have been successfully added to your cart.',
            icon: 'success',
            confirmButtonColor: '#E5A93C'
        });
    }, 900);
}

// Global toast alert helper
function showToast(message, icon = 'success') {
    const toastMsg = document.getElementById('toastMsg');
    const cartToast = document.getElementById('cartToast');
    if (toastMsg && cartToast) {
        toastMsg.innerText = message;
        const toast = new bootstrap.Toast(cartToast);
        toast.show();
    }
}
