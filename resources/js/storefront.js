// Storefront JavaScript - Alpine.js Components and Utilities

// Global Storefront State
document.addEventListener('alpine:init', () => {
    // Cart Store
    Alpine.store('cart', {
        items: [],
        totals: {
            subtotal: 0,
            tax_amount: 0,
            shipping: 0,
            discount: 0,
            grand_total: 0
        },
        coupon: null,
        isOpen: false,

        init() {
            // Load cart from session via Livewire
            this.$watch('items', (value) => {
                this.updateTotals();
            });
        },

        get itemCount() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        get formattedSubtotal() {
            return this.formatCurrency(this.totals.subtotal);
        },

        get formattedGrandTotal() {
            return this.formatCurrency(this.totals.grand_total);
        },

        updateTotals() {
            this.totals.subtotal = this.items.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);

            // Tax calculation would come from server
            this.totals.grand_total = this.totals.subtotal + this.totals.tax_amount + this.totals.shipping - this.totals.discount;
        },

        formatCurrency(amount) {
            // This will be overridden by the blade template with shopProfile->formatCurrency
            return new Intl.NumberFormat('en-BD', {
                style: 'currency',
                currency: 'BDT',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        }
    });

    // Product Gallery Store
    Alpine.store('productGallery', {
        currentImage: 0,
        images: [],

        setImage(index) {
            this.currentImage = index;
        },

        next() {
            this.currentImage = (this.currentImage + 1) % this.images.length;
        },

        prev() {
            this.currentImage = (this.currentImage - 1 + this.images.length) % this.images.length;
        }
    });

    // Search Modal Store
    Alpine.store('searchModal', {
        isOpen: false,
        query: '',
        results: [],
        loading: false,
        debounceTimer: null,

        open() {
            this.isOpen = true;
            this.query = '';
            this.results = [];
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },

        close() {
            this.isOpen = false;
            this.query = '';
            this.results = [];
            document.body.style.overflow = '';
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        async search() {
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            this.debounceTimer = setTimeout(async () => {
                if (this.query.length < 2) {
                    this.results = [];
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`/api/storefront/search?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();
                    this.results = data.products || [];
                } catch (error) {
                    console.error('Search error:', error);
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            }, 300);
        }
    });

    // Mobile Menu Store
    Alpine.store('mobileMenu', {
        isOpen: false,

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        }
    });

    // Language Switcher Store
    Alpine.store('languageSwitcher', {
        isOpen: false,
        currentLocale: 'en',

        toggle() {
            this.isOpen = !this.isOpen;
        },

        setLocale(locale) {
            this.currentLocale = locale;
            this.isOpen = false;

            // Submit language change form
            const form = document.getElementById('language-form');
            if (form) {
                form.action = `/language/${locale}`;
                form.submit();
            }
        }
    });

    // Toast Notifications Store
    Alpine.store('toasts', {
        items: [],

        add(message, type = 'info', duration = 5000) {
            const id = Date.now();
            this.items.push({ id, message, type });

            if (duration > 0) {
                setTimeout(() => this.remove(id), duration);
            }

            return id;
        },

        remove(id) {
            const index = this.items.findIndex(item => item.id === id);
            if (index > -1) {
                this.items.splice(index, 1);
            }
        },

        success(message, duration) {
            return this.add(message, 'success', duration);
        },

        error(message, duration) {
            return this.add(message, 'error', duration);
        },

        warning(message, duration) {
            return this.add(message, 'warning', duration);
        },

        info(message, duration) {
            return this.add(message, 'info', duration);
        }
    });

    // Product Quick View Store
    Alpine.store('quickView', {
        isOpen: false,
        product: null,
        loading: false,

        open(productId) {
            this.loading = true;
            this.isOpen = true;
            document.body.style.overflow = 'hidden';

            fetch(`/api/storefront/products/${productId}/quick-view`)
                .then(response => response.json())
                .then(data => {
                    this.product = data.product;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Quick view error:', error);
                    this.loading = false;
                    this.close();
                });
        },

        close() {
            this.isOpen = false;
            this.product = null;
            document.body.style.overflow = '';
        }
    });

    // Compare Products Store
    Alpine.store('compare', {
        products: [],
        maxItems: 4,

        get count() {
            return this.products.length;
        },

        has(productId) {
            return this.products.some(p => p.id === productId);
        },

        add(product) {
            if (this.has(product.id)) return false;
            if (this.products.length >= this.maxItems) return false;

            this.products.push(product);
            this.persist();
            return true;
        },

        remove(productId) {
            const index = this.products.findIndex(p => p.id === productId);
            if (index > -1) {
                this.products.splice(index, 1);
                this.persist();
            }
        },

        clear() {
            this.products = [];
            this.persist();
        },

        persist() {
            localStorage.setItem('compare_products', JSON.stringify(this.products));
        },

        load() {
            const stored = localStorage.getItem('compare_products');
            if (stored) {
                try {
                    this.products = JSON.parse(stored);
                } catch (e) {
                    this.products = [];
                }
            }
        }
    });

    // Wishlist Store
    Alpine.store('wishlist', {
        items: [],

        get count() {
            return this.items.length;
        },

        has(productId) {
            return this.items.some(item => item.id === productId);
        },

        toggle(product) {
            if (this.has(product.id)) {
                this.remove(product.id);
                return false;
            } else {
                this.add(product);
                return true;
            }
        },

        add(product) {
            if (!this.has(product.id)) {
                this.items.push(product);
                this.persist();
            }
        },

        remove(productId) {
            const index = this.items.findIndex(item => item.id === productId);
            if (index > -1) {
                this.items.splice(index, 1);
                this.persist();
            }
        },

        persist() {
            localStorage.setItem('wishlist', JSON.stringify(this.items));
        },

        load() {
            const stored = localStorage.getItem('wishlist');
            if (stored) {
                try {
                    this.items = JSON.parse(stored);
                } catch (e) {
                    this.items = [];
                }
            }
        }
    });
});

// Global Utility Functions
window.Storefront = {
    // Format currency using shop profile settings
    formatCurrency: function(amount, currency = 'BDT', locale = 'en-BD') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    },

    // Format number with locale
    formatNumber: function(number, locale = 'en-BD') {
        return new Intl.NumberFormat(locale).format(number);
    },

    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    // Throttle function
    throttle: function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Copy to clipboard
    copyToClipboard: function(text) {
        return navigator.clipboard.writeText(text).then(() => {
            Alpine.store('toasts').success('Copied to clipboard!');
        }).catch(() => {
            Alpine.store('toasts').error('Failed to copy');
        });
    },

    // Share product
    shareProduct: function(product) {
        if (navigator.share) {
            navigator.share({
                title: product.name,
                text: product.short_description || product.description,
                url: window.location.href
            }).catch(console.error);
        } else {
            // Fallback: copy URL
            this.copyToClipboard(window.location.href);
        }
    },

    // Smooth scroll to element
    scrollTo: function(selector, offset = 80) {
        const element = document.querySelector(selector);
        if (element) {
            const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: elementPosition - offset,
                behavior: 'smooth'
            });
        }
    },

    // Get URL parameter
    getUrlParam: function(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    // Update URL parameter without reload
    updateUrlParam: function(key, value) {
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        window.history.replaceState({}, '', url);
    },

    // Initialize tooltips
    initTooltips: function() {
        // Simple tooltip implementation
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
                tooltip.textContent = this.dataset.tooltip;
                tooltip.style.top = `${this.getBoundingClientRect().bottom + 5}px`;
                tooltip.style.left = `${this.getBoundingClientRect().left}px`;
                document.body.appendChild(tooltip);
                this._tooltip = tooltip;
            });

            el.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    },

    // Initialize lazy loading
    initLazyLoading: function() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        if (img.dataset.srcset) {
                            img.srcset = img.dataset.srcset;
                            img.removeAttribute('data-srcset');
                        }
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img.lazy').forEach(img => {
                imageObserver.observe(img);
            });
        }
    },

    // Handle form submission with loading state
    handleFormSubmit: function(form, submitButton) {
        form.addEventListener('submit', function() {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';
            }
        });
    },

    // Initialize all components
    init: function() {
        this.initTooltips();
        this.initLazyLoading();

        // Auto-close alerts
        document.querySelectorAll('[data-auto-dismiss]').forEach(alert => {
            const delay = parseInt(alert.dataset.autoDismiss) || 5000;
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(100%)';
                setTimeout(() => alert.remove(), 300);
            }, delay);
        });

        // Keyboard navigation for modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close any open modals/drawers
                if (Alpine.store('cart').isOpen) {
                    Alpine.store('cart').close();
                }
                if (Alpine.store('searchModal').isOpen) {
                    Alpine.store('searchModal').close();
                }
                if (Alpine.store('mobileMenu').isOpen) {
                    Alpine.store('mobileMenu').close();
                }
                if (Alpine.store('quickView').isOpen) {
                    Alpine.store('quickView').close();
                }
            }
        });

        // Load persisted data
        Alpine.store('compare').load();
        Alpine.store('wishlist').load();

        console.log('Storefront initialized');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.Storefront.init();
});

// Livewire integration helpers
document.addEventListener('livewire:load', () => {
    // Listen for cart updates
    Livewire.on('cartUpdated', (data) => {
        Alpine.store('cart').items = data.items;
        Alpine.store('cart').totals = data.totals;
        Alpine.store('cart').coupon = data.coupon;
    });

    // Listen for toast notifications
    Livewire.on('toast', (data) => {
        Alpine.store('toasts').add(data.message, data.type, data.duration);
    });

    // Listen for language change
    Livewire.on('languageChanged', (locale) => {
        Alpine.store('languageSwitcher').currentLocale = locale;
    });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.Storefront;
}