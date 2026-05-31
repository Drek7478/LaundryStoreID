/**
 * ============================================
 * LAUNDRYSTOREID - MAIN JAVASCRIPT v3.0
 * Tema: Violet + Cyan + Slate
 * Framework: Bootstrap 5 + FontAwesome 6
 * ============================================
 */

// ============================================
// DOCUMENT READY - INITIALIZE ALL FUNCTIONS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavbarScroll();
    initQuantitySteppers();
    initPaymentSelectors();
    initAdminSidebar();
    initTooltips();
    initAutoDismissAlerts();
    initImagePreview();
    initStockColorIndicator();
    initFilterPills();
    initPageTransition();
    initSmoothScroll();
    
    // Show toast notification if exists
    const toastElement = document.querySelector('.toast');
    if (toastElement) {
        const toast = new bootstrap.Toast(toastElement, {
            delay: 4000,
            animation: true
        });
        toast.show();
    }
    
    // Log initialization
    console.log('🚀 LaundryStoreID v3.0 - Initialized');
});

// ============================================
// 1. NAVBAR SCROLL EFFECT
// ============================================
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar-main');
    if (!navbar) return;
    
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScrollY = currentScrollY;
    });
}

// ============================================
// 2. QUANTITY STEPPERS
// ============================================
function initQuantitySteppers() {
    document.querySelectorAll('.qty-stepper').forEach(stepper => {
        const input = stepper.querySelector('.qty-input');
        const minusBtn = stepper.querySelector('.qty-btn:first-child');
        const plusBtn = stepper.querySelector('.qty-btn:last-child');
        
        if (!input) return;
        
        // Minus button
        if (minusBtn) {
            minusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let value = parseInt(input.value) || 1;
                const min = parseInt(input.getAttribute('min')) || 1;
                if (value > min) {
                    input.value = value - 1;
                    triggerChange(input);
                }
            });
        }
        
        // Plus button
        if (plusBtn) {
            plusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let value = parseInt(input.value) || 0;
                const max = parseInt(input.getAttribute('max')) || 999;
                if (value < max) {
                    input.value = value + 1;
                    triggerChange(input);
                }
            });
        }
        
        // Validate on manual input change
        input.addEventListener('change', function() {
            validateQtyInput(this);
        });
        
        // Validate on blur
        input.addEventListener('blur', function() {
            validateQtyInput(this);
        });
    });
}

function validateQtyInput(input) {
    let value = parseInt(input.value);
    const min = parseInt(input.getAttribute('min')) || 1;
    const max = parseInt(input.getAttribute('max')) || 999;
    
    if (isNaN(value) || value < min) {
        input.value = min;
    }
    if (value > max) {
        input.value = max;
    }
}

function triggerChange(element) {
    const event = new Event('change', { bubbles: true });
    element.dispatchEvent(event);
}

// ============================================
// 3. PAYMENT METHOD SELECTOR
// ============================================
function initPaymentSelectors() {
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all siblings
            const parent = this.parentElement;
            if (parent) {
                parent.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
            }
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Check the hidden radio input
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                
                // Trigger change event on radio
                const event = new Event('change', { bubbles: true });
                radio.dispatchEvent(event);
            }
        });
    });
}

// ============================================
// 4. TOAST NOTIFICATION SYSTEM
// ============================================
function showToast(message, type = 'success', duration = 4000) {
    // Create toast container
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    
    // Determine colors and icons
    let bgClass, icon, textColor;
    
    switch(type) {
        case 'success':
            bgClass = 'linear-gradient(135deg, #059669, #10B981)';
            icon = 'fas fa-check-circle';
            textColor = 'white';
            break;
        case 'error':
        case 'danger':
            bgClass = 'linear-gradient(135deg, #DC2626, #EF4444)';
            icon = 'fas fa-times-circle';
            textColor = 'white';
            break;
        case 'warning':
            bgClass = 'linear-gradient(135deg, #D97706, #F59E0B)';
            icon = 'fas fa-exclamation-triangle';
            textColor = 'white';
            break;
        case 'info':
            bgClass = 'linear-gradient(135deg, #0891B2, #06B6D4)';
            icon = 'fas fa-info-circle';
            textColor = 'white';
            break;
        default:
            bgClass = 'linear-gradient(135deg, #059669, #10B981)';
            icon = 'fas fa-check-circle';
            textColor = 'white';
    }
    
    // Build toast HTML
    toastContainer.innerHTML = `
        <div class="toast show border-0 shadow-lg" role="alert" 
             style="border-radius: 12px; background: ${bgClass}; color: ${textColor}; min-width: 280px;">
            <div class="d-flex align-items-center px-3 py-2">
                <i class="${icon}" style="font-size: 1.2rem; margin-right: 10px;"></i>
                <div class="toast-body p-0 fw-medium">${message}</div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Append to body
    document.body.appendChild(toastContainer);
    
    // Initialize Bootstrap Toast
    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, {
        delay: duration,
        animation: true,
        autohide: true
    });
    
    // Show toast
    toast.show();
    
    // Remove from DOM after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        if (toastContainer.parentNode) {
            toastContainer.parentNode.removeChild(toastContainer);
        }
    });
    
    // Also remove on close button click
    const closeBtn = toastElement.querySelector('.btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            toast.hide();
        });
    }
}

// ============================================
// 5. CART BADGE ANIMATION
// ============================================
function animateCartBadge() {
    const badge = document.querySelector('.cart-badge');
    if (!badge) return;
    
    // Remove and re-add animation class
    badge.classList.remove('bounce');
    void badge.offsetWidth; // Trigger reflow
    badge.classList.add('bounce');
}

/**
 * Update cart badge count
 * @param {number} count - New cart count
 */
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (!badge) return;
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
        animateCartBadge();
    } else {
        badge.style.display = 'none';
    }
}

// ============================================
// 6. ADMIN SIDEBAR TOGGLE
// ============================================
function initAdminSidebar() {
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!toggleBtn || !sidebar) return;
    
    // Toggle sidebar
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        if (overlay) {
            overlay.classList.toggle('show');
        }
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    });
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    
    // Close sidebar when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Close sidebar when clicking a nav link (mobile only)
    sidebar.querySelectorAll('.nav-item-admin').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                setTimeout(() => {
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                    document.body.style.overflow = '';
                }, 200);
            }
        });
    });
}

// ============================================
// 7. TOOLTIPS INITIALIZATION
// ============================================
function initTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 300, hide: 100 },
            placement: 'top',
            trigger: 'hover'
        });
    });
}

// ============================================
// 8. AUTO DISMISS ALERTS
// ============================================
function initAutoDismissAlerts() {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// ============================================
// 9. IMAGE PREVIEW FOR UPLOAD
// ============================================
function initImagePreview() {
    document.querySelectorAll('.image-upload-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (!file || !preview) return;
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Format file tidak diizinkan! (JPG, PNG, GIF)', 'error');
                this.value = '';
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showToast('Ukuran file maksimal 2MB!', 'error');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    });
}

// ============================================
// 10. STOCK COLOR INDICATOR
// ============================================
function initStockColorIndicator() {
    document.querySelectorAll('.stock-indicator').forEach(input => {
        // Set initial color
        updateStockColor(input);
        
        // Update on input change
        input.addEventListener('input', function() {
            updateStockColor(this);
        });
        
        // Update on change
        input.addEventListener('change', function() {
            updateStockColor(this);
        });
    });
}

function updateStockColor(input) {
    const value = parseInt(input.value) || 0;
    
    // Remove all border color classes
    input.classList.remove('border-success', 'border-warning', 'border-danger');
    
    // Add appropriate class
    if (value > 20) {
        input.classList.add('border-success');
    } else if (value >= 5) {
        input.classList.add('border-warning');
    } else {
        input.classList.add('border-danger');
    }
}

// ============================================
// 11. FILTER PILLS
// ============================================
function initFilterPills() {
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.addEventListener('click', function(e) {
            const group = this.getAttribute('data-group');
            
            // If part of a group, remove active from siblings
            if (group) {
                document.querySelectorAll(`.filter-pill[data-group="${group}"]`).forEach(p => {
                    p.classList.remove('active');
                });
                this.classList.add('active');
            } else {
                // Toggle if no group
                this.classList.toggle('active');
            }
            
            // If has data-filter attribute, trigger filtering
            const filterValue = this.getAttribute('data-filter');
            const filterTarget = this.getAttribute('data-target');
            
            if (filterValue && filterTarget) {
                applyFilter(filterTarget, filterValue);
            }
        });
    });
}

function applyFilter(targetSelector, filterValue) {
    const items = document.querySelectorAll(`[data-filter-item]`);
    
    items.forEach(item => {
        const category = item.getAttribute('data-category');
        
        if (filterValue === 'all' || category === filterValue) {
            item.style.display = '';
            // Add fade-in animation
            item.style.opacity = '0';
            item.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                item.style.opacity = '1';
            }, 10);
        } else {
            item.style.display = 'none';
        }
    });
}

// ============================================
// 12. PAGE TRANSITION
// ============================================
function initPageTransition() {
    // Add fade-in class to body
    document.body.classList.add('fade-in');
    
    // Add transition to all links for smooth page changes
    document.querySelectorAll('a:not([target="_blank"]):not([data-bs-toggle])').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only for internal links
            if (href && !href.startsWith('#') && !href.startsWith('javascript') && !href.startsWith('http')) {
                // Add exit animation
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.2s ease';
            }
        });
    });
}

// ============================================
// 13. SMOOTH SCROLL
// ============================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ============================================
// 14. CONFIRM DELETE DIALOG
// ============================================
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return confirm(message);
}

/**
 * Show styled confirmation dialog
 * @param {string} message - Confirmation message
 * @param {function} callback - Function to call if confirmed
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
    }
}

// ============================================
// 15. ENLARGE IMAGE MODAL
// ============================================
function enlargeImage(src) {
    // Remove existing modal if any
    const existingModal = document.getElementById('imagePreviewModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'imagePreviewModal';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0" style="background: transparent;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="${src}" class="img-fluid" style="max-height: 85vh; border-radius: 12px; cursor: zoom-out;" 
                         onclick="this.closest('.modal').querySelector('.btn-close').click()">
                </div>
            </div>
        </div>
    `;
    
    // Append to body
    document.body.appendChild(modal);
    
    // Initialize and show modal
    const modalInstance = new bootstrap.Modal(modal, {
        keyboard: true,
        backdrop: true
    });
    
    modalInstance.show();
    
    // Remove modal from DOM after hidden
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function closeOnEscape(e) {
        if (e.key === 'Escape') {
            modalInstance.hide();
            document.removeEventListener('keydown', closeOnEscape);
        }
    });
}

// ============================================
// 16. COPY TO CLIPBOARD
// ============================================
function copyToClipboard(text) {
    // Use modern Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Berhasil disalin ke clipboard! 📋', 'success', 2500);
        }).catch(() => {
            // Fallback
            fallbackCopyToClipboard(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '-9999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('Berhasil disalin ke clipboard! 📋', 'success', 2500);
        } else {
            showToast('Gagal menyalin teks', 'error', 3000);
        }
    } catch (err) {
        showToast('Gagal menyalin teks', 'error', 3000);
    }
    
    document.body.removeChild(textArea);
}

// ============================================
// 17. FORMAT RUPIAH
// ============================================
function formatRupiah(angka) {
    const number = parseInt(angka) || 0;
    return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Format number to Rupiah with prefix
 * @param {number} angka - Number to format
 * @param {string} prefix - Currency prefix
 * @returns {string} Formatted currency string
 */
function formatCurrency(angka, prefix = 'Rp ') {
    const number = parseInt(angka) || 0;
    return prefix + number.toLocaleString('id-ID');
}

// ============================================
// 18. DEBOUNCE FUNCTION
// ============================================
function debounce(func, wait = 300) {
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

// ============================================
// 19. THROTTLE FUNCTION
// ============================================
function throttle(func, limit = 300) {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => {
                inThrottle = false;
            }, limit);
        }
    };
}

// ============================================
// 20. BUTTON LOADING STATE
// ============================================
function setButtonLoading(button, loadingText = 'Memproses...') {
    // Save original state
    const originalHTML = button.innerHTML;
    const originalWidth = button.offsetWidth;
    
    button.setAttribute('data-original-html', originalHTML);
    button.setAttribute('data-original-width', originalWidth);
    
    // Set loading state
    button.classList.add('loading', 'disabled');
    button.setAttribute('disabled', 'disabled');
    button.style.minWidth = originalWidth + 'px';
    button.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <span>${loadingText}</span>
    `;
}

function resetButtonLoading(button) {
    // Get original state
    const originalHTML = button.getAttribute('data-original-html');
    const originalWidth = button.getAttribute('data-original-width');
    
    // Reset state
    button.classList.remove('loading', 'disabled');
    button.removeAttribute('disabled');
    button.style.minWidth = '';
    
    if (originalHTML) {
        button.innerHTML = originalHTML;
    }
    
    button.removeAttribute('data-original-html');
    button.removeAttribute('data-original-width');
}

// ============================================
// 21. PASSWORD TOGGLE VISIBILITY
// ============================================
function togglePassword(inputId, iconId) {
    const passwordField = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (!passwordField || !toggleIcon) return;
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// ============================================
// 22. AJAX FORM SUBMIT (Optional Utility)
// ============================================
async function submitFormAjax(formElement, options = {}) {
    const {
        onSuccess = null,
        onError = null,
        loadingText = 'Mengirim...',
        resetOnSuccess = false
    } = options;
    
    const submitBtn = formElement.querySelector('button[type="submit"]');
    
    try {
        // Set loading state
        if (submitBtn) setButtonLoading(submitBtn, loadingText);
        
        // Get form data
        const formData = new FormData(formElement);
        
        // Send request
        const response = await fetch(formElement.action, {
            method: formElement.method || 'POST',
            body: formData
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            // Check if it's a redirect (HTML response)
            if (text.includes('<html') || text.includes('<!DOCTYPE')) {
                // It's a full page response, probably a redirect
                document.open();
                document.write(text);
                document.close();
                return;
            }
            data = { message: text };
        }
        
        // Handle success
        if (response.ok) {
            if (data.message) {
                showToast(data.message, 'success');
            }
            if (resetOnSuccess) {
                formElement.reset();
            }
            if (typeof onSuccess === 'function') {
                onSuccess(data);
            }
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
        
    } catch (error) {
        // Handle error
        showToast(error.message || 'Terjadi kesalahan jaringan', 'error');
        if (typeof onError === 'function') {
            onError(error);
        }
    } finally {
        // Reset button state
        if (submitBtn) resetButtonLoading(submitBtn);
    }
}

// ============================================
// 23. SEARCH DEBOUNCE (Untuk Live Search)
// ============================================
function initLiveSearch(inputSelector, targetSelector, searchFunction) {
    const searchInput = document.querySelector(inputSelector);
    if (!searchInput) return;
    
    const debouncedSearch = debounce(async function() {
        const query = searchInput.value.trim();
        
        if (query.length < 2 && query.length > 0) return;
        
        try {
            const results = await searchFunction(query);
            const target = document.querySelector(targetSelector);
            
            if (target) {
                // Update target with results
                if (typeof results === 'string') {
                    target.innerHTML = results;
                }
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 400);
    
    searchInput.addEventListener('input', debouncedSearch);
}

// ============================================
// 24. RESPONSIVE HELPER
// ============================================
function isMobile() {
    return window.innerWidth <= 767.98;
}

function isTablet() {
    return window.innerWidth > 767.98 && window.innerWidth <= 991.98;
}

function isDesktop() {
    return window.innerWidth > 991.98;
}

// ============================================
// 25. STORAGE HELPERS
// ============================================
const StorageHelper = {
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.warn('Storage full or unavailable');
        }
    },
    
    get(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    
    remove(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.warn('Storage unavailable');
        }
    },
    
    clear() {
        try {
            localStorage.clear();
        } catch (e) {
            console.warn('Storage unavailable');
        }
    }
};

// ============================================
// 26. COOKIE HELPERS
// ============================================
const CookieHelper = {
    set(name, value, days = 7) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    },
    
    get(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    },
    
    remove(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`;
    }
};

// ============================================
// 27. PRICE CALCULATOR (Untuk Cart/Checkout)
// ============================================
const PriceCalculator = {
    /**
     * Calculate subtotal for a single item
     */
    calculateSubtotal(price, quantity) {
        return price * quantity;
    },
    
    /**
     * Calculate total from array of items
     */
    calculateTotal(items) {
        return items.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    },
    
    /**
     * Format price to display
     */
    format(amount) {
        return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
    }
};

// ============================================
// 28. DOM MANIPULATION HELPERS
// ============================================
const DOMHelper = {
    /**
     * Create element with attributes and content
     */
    createElement(tag, attributes = {}, content = '') {
        const element = document.createElement(tag);
        
        // Set attributes
        Object.keys(attributes).forEach(key => {
            if (key === 'class' || key === 'className') {
                element.className = attributes[key];
            } else if (key === 'style' && typeof attributes[key] === 'object') {
                Object.assign(element.style, attributes[key]);
            } else {
                element.setAttribute(key, attributes[key]);
            }
        });
        
        // Set content
        if (content) {
            if (typeof content === 'string') {
                element.innerHTML = content;
            } else if (content instanceof HTMLElement) {
                element.appendChild(content);
            }
        }
        
        return element;
    },
    
    /**
     * Show element
     */
    show(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.style.display = '';
        }
    },
    
    /**
     * Hide element
     */
    hide(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.style.display = 'none';
        }
    },
    
    /**
     * Toggle element visibility
     */
    toggle(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            if (element.style.display === 'none') {
                element.style.display = '';
            } else {
                element.style.display = 'none';
            }
        }
    }
};

// ============================================
// 29. EXPORT MODULES (ES Module compatible)
// ============================================
// If using ES modules, you can export these functions
// export {
//     showToast,
//     confirmDelete,
//     enlargeImage,
//     copyToClipboard,
//     formatRupiah,
//     setButtonLoading,
//     resetButtonLoading,
//     togglePassword,
//     StorageHelper,
//     CookieHelper,
//     PriceCalculator,
//     DOMHelper
// };

// ============================================
// 30. CONSOLE WELCOME MESSAGE
// ============================================
console.log(`
%c🧺 LaundryStoreID v3.0 %cReady
%cPremium E-Commerce Theme
%cViolet + Cyan + Slate
`,
    'font-size: 18px; font-weight: bold; color: #7C3AED;',
    'font-size: 14px; color: #10B981;',
    'font-size: 12px; color: #64748B;',
    'font-size: 12px; color: #06B6D4;'
);

// ============================================
// END OF MAIN.JS
// ============================================