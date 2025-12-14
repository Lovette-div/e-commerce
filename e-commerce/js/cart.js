// cart.js - Handle all cart UI interactions

/**
 * Recalculate and update cart totals
 */
function recalculateCartTotals() {
    let total = 0;
    
    // Get all cart items
    const cartItems = document.querySelectorAll('.cart-item');
    
    cartItems.forEach(item => {
        const cartId = item.getAttribute('data-cart-id');
        const price = parseFloat(item.getAttribute('data-price'));
        const qtyInput = item.querySelector(`.qty-value-${cartId}`);
        const qty = qtyInput ? parseInt(qtyInput.value) : 0;
        
        const subtotal = price * qty;
        total += subtotal;
        
        // Update item subtotal display
        const subtotalElement = item.querySelector(`.item-subtotal[data-cart-id="${cartId}"]`);
        if (subtotalElement) {
            subtotalElement.textContent = 'GHS' + subtotal.toFixed(2);
        }
    });
    
    // Update summary totals
    const subtotalElement = document.getElementById('subtotalAmount');
    const totalElement = document.getElementById('totalAmount');
    
    if (subtotalElement) {
        subtotalElement.textContent = 'GHS' + total.toFixed(2);
    }
    
    if (totalElement) {
        totalElement.textContent = 'GHS' + total.toFixed(2);
    }
}

/**
 * Update quantity of a cart item
 */
async function updateQuantity(cartId, newQty) {
    if (newQty < 1) {
        if (!confirm('Remove this item from cart?')) {
            return;
        }
        removeItem(cartId);
        return;
    }

    try {
        const response = await fetch('../actions/functions/update_quantity_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart_id: cartId,
                qty: newQty
            })
        });

        const data = await response.json();

        if (data.status) {
            // Update quantity input
            const qtyInput = document.querySelector(`.qty-value-${cartId}`);
            if (qtyInput) {
                qtyInput.value = newQty;
            }
            
            // Recalculate totals
            recalculateCartTotals();
            
            // Update cart badge
            if (data.cart_count !== undefined) {
                updateCartBadge(data.cart_count);
            }
            
            showNotification('success', 'Quantity updated');
        } else {
            alert(data.message || 'Failed to update quantity');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating quantity');
    }
}

/**
 * Remove item from cart
 */
async function removeItem(cartId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }

    try {
        const response = await fetch('../actions/functions/remove_from_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart_id: cartId
            })
        });

        const data = await response.json();

        if (data.status) {
            // Remove item from DOM with animation
            const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`);
            if (itemElement) {
                itemElement.style.transition = 'opacity 0.3s';
                itemElement.style.opacity = '0';
                setTimeout(() => {
                    itemElement.remove();
                    
                    // Recalculate totals after removing item
                    recalculateCartTotals();
                    
                    // Check if cart is empty
                    const cartContainer = document.getElementById('cartItemsContainer');
                    const remainingItems = cartContainer.querySelectorAll('.cart-item');
                    
                    if (remainingItems.length === 0) {
                        location.reload();
                    }
                }, 300);
            }

            // Update cart badge
            if (data.cart_count !== undefined) {
                updateCartBadge(data.cart_count);
            }

            showNotification('success', data.message);
        } else {
            alert(data.message || 'Failed to remove item');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while removing item');
    }
}

/**
 * Empty entire cart
 */
async function emptyCart() {
    if (!confirm('Are you sure you want to empty your entire cart?')) {
        return;
    }

    try {
        const response = await fetch('../actions/functions/empty_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.status) {
            location.reload();
        } else {
            alert(data.message || 'Failed to empty cart');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while emptying cart');
    }
}

/**
 * Update cart badge count
 */
function updateCartBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (badge) {
        badge.textContent = count;
        if (count === 0) {
            badge.style.display = 'none';
        }
    }
}

/**
 * Show notification
 */
function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add to cart function (for use on product pages)
async function addToCart(productId, qty = 1) {
    try {
        const response = await fetch('../actions/functions/add_to_cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                qty: qty
            })
        });

        const data = await response.json();

        if (data.status) {
            showNotification('success', data.message);
            
            // Update cart badge if count is returned
            if (data.cart_count !== undefined) {
                updateCartBadge(data.cart_count);
            }
            
            // Optional: redirect to cart after a delay
            setTimeout(() => {
                window.location.href = 'cart.php';
            }, 1500);
        } else {
            alert(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding to cart');
    }
}