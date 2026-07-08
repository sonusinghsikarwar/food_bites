// assets/js/pos.js
// POS terminal client-side interactive logic

let posCart = [];
let activeCategory = 'all';

// Filter Catalog by Category Tabs
function filterCategory(categorySlug) {
    activeCategory = categorySlug;
    
    // Highlight category button
    document.querySelectorAll('.pos-cat-btn').forEach(btn => {
        btn.className = 'btn btn-outline-warning rounded-pill btn-sm px-3 fw-bold pos-cat-btn';
    });
    
    const activeBtn = document.getElementById(`cat-btn-${categorySlug}`);
    if (activeBtn) {
        activeBtn.className = 'btn btn-warning rounded-pill btn-sm px-3 fw-bold pos-cat-btn';
    }
    
    filterPOSCatalog();
}

// Filter Catalog by search box + category slug
function filterPOSCatalog() {
    const searchVal = document.getElementById('posSearch').value.toLowerCase();
    
    document.querySelectorAll('.pos-product-item').forEach(item => {
        const name = item.getAttribute('data-name');
        const cat = item.getAttribute('data-category');
        
        const matchesSearch = name.includes(searchVal);
        const matchesCat = (activeCategory === 'all' || cat === activeCategory);
        
        if (matchesSearch && matchesCat) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}

// Show/Hide Table input depending on Order Type selection
function togglePOSTableInput() {
    const type = document.getElementById('posOrderType').value;
    const tableGroup = document.getElementById('pos-table-group');
    if (type === 'dine_in') {
        tableGroup.classList.remove('hidden');
    } else {
        tableGroup.classList.add('hidden');
    }
}

// Add Item to POS Cart array
function addPOSItem(product) {
    const existing = posCart.find(item => item.product_id === product.product_id);
    
    if (existing) {
        if (existing.quantity + 1 > product.stock) {
            Swal.fire('Out of Stock', `Only ${product.stock} units available in stock.`, 'warning');
            return;
        }
        existing.quantity += 1;
    } else {
        posCart.push({
            product_id: product.product_id,
            name: product.name,
            price: product.price,
            quantity: 1,
            stock: product.stock
        });
    }
    
    renderPOSCart();
}

// Remove or Decrease quantity
function changePOSQty(productId, amount) {
    const item = posCart.find(i => i.product_id === productId);
    if (!item) return;
    
    item.quantity += amount;
    if (item.quantity <= 0) {
        posCart = posCart.filter(i => i.product_id !== productId);
    } else if (item.quantity > item.stock) {
        Swal.fire('Out of Stock', `Only ${item.stock} units available in stock.`, 'warning');
        item.quantity = item.stock;
    }
    
    renderPOSCart();
}

// Render POS cart lists
function renderPOSCart() {
    const container = document.getElementById('pos-cart-items-container');
    if (!container) return;
    
    if (posCart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 text-muted my-auto">
                <i class="fa-solid fa-calculator display-4 mb-2"></i>
                <p class="small mb-0">Select items to begin checkout</p>
            </div>
        `;
        calculatePOSTotals();
        return;
    }
    
    let html = '';
    posCart.forEach(item => {
        const total = (item.price * item.quantity).toFixed(2);
        html += `
            <div class="d-flex align-items-center justify-content-between p-2 rounded-4 border bg-white dark-bg-card shadow-sm text-sm">
                <div class="flex-grow-1 min-w-0 pr-2">
                    <h6 class="fw-bold mb-0 text-truncate text-dark dark-text-white">${item.name}</h6>
                    <span class="text-warning fw-semibold">${currencySymbol}${item.price.toFixed(2)}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm border rounded-pill overflow-hidden" style="max-width: 90px;">
                        <button onclick="changePOSQty(${item.product_id}, -1)" class="btn btn-light border-0 py-0 px-2"><i class="fa-solid fa-minus text-xxs"></i></button>
                        <input type="text" class="form-control text-center border-0 bg-transparent py-0 px-1 shadow-none fw-semibold" value="${item.quantity}" readonly style="font-size: 0.8rem;">
                        <button onclick="changePOSQty(${item.product_id}, 1)" class="btn btn-light border-0 py-0 px-2"><i class="fa-solid fa-plus text-xxs"></i></button>
                    </div>
                    <span class="fw-bold text-end text-dark dark-text-white" style="min-width: 60px;">${currencySymbol}${total}</span>
                    <button onclick="changePOSQty(${item.product_id}, -${item.quantity})" class="btn btn-link text-danger p-0 ms-2 shadow-none"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    calculatePOSTotals();
}

// Calculate billing totals
function calculatePOSTotals() {
    let subtotal = 0;
    posCart.forEach(item => {
        subtotal += (item.price * item.quantity);
    });
    
    const tax = (subtotal * taxRate) / 100;
    const discountInput = document.getElementById('posDiscount');
    const discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
    
    let grandtotal = subtotal + tax - discount;
    if (grandtotal < 0) grandtotal = 0;
    
    document.getElementById('pos-subtotal').innerText = `${currencySymbol}${subtotal.toFixed(2)}`;
    document.getElementById('pos-tax').innerText = `${currencySymbol}${tax.toFixed(2)}`;
    document.getElementById('pos-grandtotal').innerText = `${currencySymbol}${grandtotal.toFixed(2)}`;
}

// POS order checkout transaction
function checkoutPOSOrder() {
    if (posCart.length === 0) {
        Swal.fire('POS Cart Empty', 'Please select food items from the left menu grid first.', 'warning');
        return;
    }
    
    const discountInput = document.getElementById('posDiscount');
    const payload = {
        customer_name: document.getElementById('custName').value || 'Walk-in Customer',
        customer_phone: document.getElementById('custPhone').value || '',
        order_type: document.getElementById('posOrderType').value,
        table_no: document.getElementById('posTableNo').value || '',
        payment_method: document.getElementById('posPayment').value,
        discount_amount: discountInput ? parseFloat(discountInput.value) || 0 : 0,
        cart_items: posCart
    };
    
    Swal.fire({
        title: 'Billing Confirmation',
        text: `Do you want to confirm checkout billing for this order?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, bill and print receipt!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show Loader overlay
            const loader = document.getElementById('loader-overlay');
            if (loader) loader.classList.remove('hidden');
            
            fetch('api/pos_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (loader) loader.classList.add('hidden');
                
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Print Receipt',
                        cancelButtonText: 'New Order'
                    }).then((click) => {
                        if (click.isConfirmed) {
                            window.open(`../invoice.php?order_no=${encodeURIComponent(data.order_no)}`, '_blank');
                        }
                        // Clear cart
                        posCart = [];
                        document.getElementById('custName').value = '';
                        document.getElementById('custPhone').value = '';
                        document.getElementById('posDiscount').value = '0.00';
                        document.getElementById('posTableNo').value = '';
                        document.getElementById('posOrderType').value = 'take_away';
                        togglePOSTableInput();
                        renderPOSCart();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Billing transaction failed.', 'error');
                }
            })
            .catch(() => {
                if (loader) loader.classList.add('hidden');
                Swal.fire('Connection Error', 'Failed to reach checkout endpoint.', 'error');
            });
        }
    });
}
