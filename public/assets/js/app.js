/**
 * E-commerce Platform JavaScript
 * Main application JavaScript file
 */

// Global variables
let cartCount = 0;
let notifications = [];

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    updateCartCount();
    loadCartItems();
    initializeNotifications();
    initializeFormValidation();
    initializeLazyLoading();
}

/**
 * Show notification to user
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-${getNotificationIcon(type)} mr-2"></i>
                <span>${message}</span>
            </div>
            <button onclick="hideNotification(this)" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto hide after duration
    setTimeout(() => {
        hideNotification(notification.querySelector('button'));
    }, duration);
}

/**
 * Hide notification
 */
function hideNotification(button) {
    const notification = button.closest('.notification');
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Update cart count in header
 */
function updateCartCount() {
    fetch('/api/cart/count', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cartCount = data.count;
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

/**
 * Load cart items for sidebar
 */
function loadCartItems() {
    fetch('/api/cart/items', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartSidebar(data.data);
        }
    })
    .catch(error => {
        console.error('Error loading cart items:', error);
    });
}

/**
 * Update cart sidebar content
 */
function updateCartSidebar(cartData) {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSubtotal = document.getElementById('cart-subtotal');
    
    if (!cartItemsContainer) return;
    
    if (cartData.items && cartData.items.length > 0) {
        let itemsHtml = '';
        cartData.items.forEach(item => {
            itemsHtml += `
                <div class="cart-item flex items-center space-x-3">
                    <img src="/assets/uploads/${item.image || 'placeholder.jpg'}" 
                         alt="${item.name}" class="w-12 h-12 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm">${item.name}</h4>
                        <p class="text-gray-500 text-xs">$${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <button onclick="removeFromCart(${item.id})" 
                            class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            `;
        });
        cartItemsContainer.innerHTML = itemsHtml;
        
        if (cartSubtotal) {
            cartSubtotal.textContent = `$${parseFloat(cartData.subtotal).toFixed(2)}`;
        }
    } else {
        cartItemsContainer.innerHTML = '<p class="text-gray-500 text-center">Your cart is empty</p>';
        if (cartSubtotal) {
            cartSubtotal.textContent = '$0.00';
        }
    }
}

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1) {
    const data = {
        product_id: productId,
        quantity: quantity
    };
    
    fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            loadCartItems();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add product to cart', 'error');
    });
}

/**
 * Remove product from cart
 */
function removeFromCart(cartItemId) {
    fetch('/api/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ cart_item_id: cartItemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            loadCartItems();
            showNotification('Product removed from cart', 'info');
        } else {
            showNotification(data.message || 'Failed to remove product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to remove product', 'error');
    });
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(cartItemId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartItemId);
        return;
    }
    
    fetch('/api/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            cart_item_id: cartItemId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            loadCartItems();
            // Update page totals if on cart page
            if (window.location.pathname === '/cart') {
                location.reload();
            }
        } else {
            showNotification(data.message || 'Failed to update quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update quantity', 'error');
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    else if (type === 'password' && value) {
        if (value.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long';
        }
    }
    
    // Confirmation validation
    else if (field.hasAttribute('data-confirm')) {
        const confirmField = document.getElementById(field.getAttribute('data-confirm'));
        if (confirmField && value !== confirmField.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    let errorElement = field.parentNode.querySelector('.form-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'form-error';
        field.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorElement = field.parentNode.querySelector('.form-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Initialize lazy loading for images
 */
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

/**
 * Initialize notifications system
 */
function initializeNotifications() {
    // Check for new notifications periodically
    if (isUserLoggedIn()) {
        setInterval(checkForNotifications, 30000); // Check every 30 seconds
    }
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return document.querySelector('body').hasAttribute('data-user-id');
}

/**
 * Check for new notifications
 */
function checkForNotifications() {
    fetch('/api/notifications/unread', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notifications.length > 0) {
            // Update notification badge
            updateNotificationBadge(data.count);
        }
    })
    .catch(error => {
        console.error('Error checking notifications:', error);
    });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

/**
 * Format price with currency
 */
function formatPrice(amount, currency = '$') {
    return currency + parseFloat(amount).toFixed(2);
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Search functionality
 */
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
    const debouncedSearch = debounce(function(query) {
        if (query.length > 2) {
            // Implement search suggestions
            showSearchSuggestions(query);
        }
    }, 300);
    
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

/**
 * Show search suggestions
 */
function showSearchSuggestions(query) {
    fetch('/api/search/suggestions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ query: query })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchSuggestions(data.suggestions);
        }
    })
    .catch(error => {
        console.error('Error fetching suggestions:', error);
    });
}

/**
 * Display search suggestions
 */
function displaySearchSuggestions(suggestions) {
    // Implementation for search suggestions dropdown
    console.log('Search suggestions:', suggestions);
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('data-tooltip');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.position = 'absolute';
    tooltip.style.background = '#333';
    tooltip.style.color = 'white';
    tooltip.style.padding = '5px 10px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.zIndex = '1000';
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    element.tooltipElement = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(event) {
    const element = event.target;
    if (element.tooltipElement) {
        element.tooltipElement.remove();
        element.tooltipElement = null;
    }
}

// Initialize tooltips on load
document.addEventListener('DOMContentLoaded', initializeTooltips);