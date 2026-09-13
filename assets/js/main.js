/**
 * HORAA STORE - Main Storefront JavaScript
 * Handles AJAX Cart, Wishlist, Live Search, Coupons & UI Animations
 */

document.addEventListener('DOMContentLoaded', function () {
    initLiveSearch();
    initQuantitySelectors();
    initImageGallery();
});

/**
 * Toast Notification Popup
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container-cyber';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-cyber ${type === 'error' ? 'border-danger' : ''}`;
    const icon = type === 'error' ? 'fa-exclamation-triangle text-magenta' : 'fa-check-circle text-cyan';

    toast.innerHTML = `
        <i class="fas ${icon} fa-lg"></i>
        <div class="flex-grow-1">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

/**
 * Add Product to Cart via AJAX
 */
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update Navbar Cart Counter
            const counter = document.getElementById('nav-cart-count');
            if (counter) {
                counter.textContent = data.cart_count;
                counter.classList.add('pulse');
                setTimeout(() => counter.classList.remove('pulse'), 600);
            }
        } else {
            showToast(data.message || 'Error adding to cart.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server communication error.', 'error');
    });
}

/**
 * Toggle Product Wishlist via AJAX
 */
function toggleWishlist(productId, btnElement) {
    const formData = new FormData();
    formData.append('action', 'toggle_wishlist');
    formData.append('product_id', productId);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            if (btnElement) {
                if (data.in_wishlist) {
                    btnElement.classList.add('active');
                } else {
                    btnElement.classList.remove('active');
                }
            }
            const countElem = document.getElementById('nav-wishlist-count');
            if (countElem && data.wishlist_count !== undefined) {
                countElem.textContent = data.wishlist_count;
            }
        } else {
            showToast(data.message || 'Please log in to add items to wishlist.', 'error');
        }
    })
    .catch(err => console.error(err));
}

/**
 * Live Search Suggestions dropdown
 */
function initLiveSearch() {
    const searchInput = document.getElementById('global-search-input');
    const dropdown = document.getElementById('search-dropdown');

    if (!searchInput || !dropdown) return;

    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`api.php?action=search_suggestions&q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(items => {
                    if (items.length === 0) {
                        dropdown.innerHTML = '<div class="p-3 text-muted">No products found.</div>';
                    } else {
                        let html = '';
                        items.forEach(item => {
                            const price = item.sale_price ? `NPR ${parseFloat(item.sale_price).toFixed(2)}` : `NPR ${parseFloat(item.price).toFixed(2)}`;
                            html += `
                                <a href="product.php?id=${item.id}" class="search-item">
                                    <img src="${item.image || 'assets/images/placeholder.jpg'}" alt="${item.name}">
                                    <div>
                                        <div class="fw-bold">${item.name}</div>
                                        <div class="text-cyan font-monospace">${price}</div>
                                    </div>
                                </a>
                            `;
                        });
                        dropdown.innerHTML = html;
                    }
                    dropdown.classList.add('active');
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
}

/**
 * Quantity Counter UI (+ / -)
 */
function initQuantitySelectors() {
    document.querySelectorAll('.qty-box').forEach(box => {
        const minusBtn = box.querySelector('.qty-minus');
        const plusBtn = box.querySelector('.qty-plus');
        const input = box.querySelector('.qty-input');

        if (!minusBtn || !plusBtn || !input) return;

        minusBtn.addEventListener('click', () => {
            let val = parseInt(input.value) || 1;
            if (val > 1) {
                input.value = val - 1;
                input.dispatchEvent(new Event('change'));
            }
        });

        plusBtn.addEventListener('click', () => {
            let val = parseInt(input.value) || 1;
            let max = parseInt(input.getAttribute('max')) || 99;
            if (val < max) {
                input.value = val + 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
}

/**
 * Product Gallery Thumbnail Switcher
 */
function initImageGallery() {
    const mainImg = document.getElementById('product-main-view');
    const thumbs = document.querySelectorAll('.thumb-item');

    if (!mainImg || thumbs.length === 0) return;

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function () {
            thumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const newSrc = this.querySelector('img').getAttribute('src');
            mainImg.setAttribute('src', newSrc);
        });
    });
}

/**
 * Apply Coupon Code in Cart / Checkout
 */
function applyCouponCode() {
    const couponInput = document.getElementById('coupon-input');
    if (!couponInput) return;
    const code = couponInput.value.trim();
    if (!code) {
        showToast('Please enter a coupon code.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'apply_coupon');
    formData.append('code', code);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    });
}
