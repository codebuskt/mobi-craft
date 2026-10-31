// Admin Panel JavaScript - Alpine.js Components and Utilities

// Global Admin State
document.addEventListener('alpine:init', () => {
    // Sidebar Toggle Store
    Alpine.store('sidebar', {
        isOpen: false,

        toggle() {
            this.isOpen = !this.isOpen;
            document.body.style.overflow = this.isOpen ? 'hidden' : '';
        },

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        }
    });

    // Modal Store
    Alpine.store('modal', {
        isOpen: false,
        component: null,
        data: {},

        open(component, data = {}) {
            this.component = component;
            this.data = data;
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            this.component = null;
            this.data = {};
            document.body.style.overflow = '';
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

    // Confirm Dialog Store
    Alpine.store('confirm', {
        isOpen: false,
        title: '',
        message: '',
        onConfirm: null,
        onCancel: null,
        variant: 'danger', // 'danger', 'warning', 'info'

        open(options) {
            this.title = options.title || 'Confirm';
            this.message = options.message || 'Are you sure?';
            this.onConfirm = options.onConfirm || (() => {});
            this.onCancel = options.onCancel || (() => {});
            this.variant = options.variant || 'danger';
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            this.title = '';
            this.message = '';
            this.onConfirm = null;
            this.onCancel = null;
            this.variant = 'danger';
            document.body.style.overflow = '';
        },

        confirm() {
            if (this.onConfirm) {
                this.onConfirm();
            }
            this.close();
        },

        cancel() {
            if (this.onCancel) {
                this.onCancel();
            }
            this.close();
        }
    });

    // Data Table Store
    Alpine.store('dataTable', {
        // Per-table state management
        tables: {},

        getTableState(tableId) {
            if (!this.tables[tableId]) {
                this.tables[tableId] = {
                    page: 1,
                    perPage: 25,
                    sortBy: null,
                    sortDirection: 'asc',
                    filters: {},
                    search: '',
                    selected: [],
                    loading: false
                };
            }
            return this.tables[tableId];
        },

        setPage(tableId, page) {
            this.getTableState(tableId).page = page;
        },

        setPerPage(tableId, perPage) {
            this.getTableState(tableId).perPage = perPage;
            this.getTableState(tableId).page = 1;
        },

        setSort(tableId, column) {
            const state = this.getTableState(tableId);
            if (state.sortBy === column) {
                state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortBy = column;
                state.sortDirection = 'asc';
            }
        },

        setFilter(tableId, key, value) {
            const state = this.getTableState(tableId);
            if (value === '' || value === null || value === undefined) {
                delete state.filters[key];
            } else {
                state.filters[key] = value;
            }
            state.page = 1;
        },

        setSearch(tableId, search) {
            this.getTableState(tableId).search = search;
            this.getTableState(tableId).page = 1;
        },

        toggleSelection(tableId, id) {
            const state = this.getTableState(tableId);
            const index = state.selected.indexOf(id);
            if (index > -1) {
                state.selected.splice(index, 1);
            } else {
                state.selected.push(id);
            }
        },

        selectAll(tableId, ids) {
            this.getTableState(tableId).selected = ids;
        },

        clearSelection(tableId) {
            this.getTableState(tableId).selected = [];
        },

        setLoading(tableId, loading) {
            this.getTableState(tableId).loading = loading;
        },

        reset(tableId) {
            this.tables[tableId] = {
                page: 1,
                perPage: 25,
                sortBy: null,
                sortDirection: 'asc',
                filters: {},
                search: '',
                selected: [],
                loading: false
            };
        }
    });
});

// Global Utility Functions
window.Admin = {
    // Format currency
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

    // Show confirm dialog
    confirm: function(options) {
        Alpine.store('confirm').open(options);
    },

    // Show toast
    toast: function(message, type = 'info', duration = 5000) {
        Alpine.store('toasts').add(message, type, duration);
    },

    // Initialize tooltips
    initTooltips: function() {
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
                // Close sidebar on mobile
                if (Alpine.store('sidebar').isOpen) {
                    Alpine.store('sidebar').close();
                }
                // Close any open modals
                if (Alpine.store('modal').isOpen) {
                    Alpine.store('modal').close();
                }
                // Close confirm dialog
                if (Alpine.store('confirm').isOpen) {
                    Alpine.store('confirm').close();
                }
            }
        });

        console.log('Admin panel initialized');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.Admin.init();
});

// Livewire integration helpers
document.addEventListener('livewire:load', () => {
    // Listen for toast notifications
    Livewire.on('toast', (data) => {
        Alpine.store('toasts').add(data.message, data.type, data.duration);
    });

    // Listen for confirm dialogs
    Livewire.on('confirm', (data) => {
        Alpine.store('confirm').open(data);
    });

    // Listen for modal opens
    Livewire.on('openModal', (data) => {
        Alpine.store('modal').open(data.component, data.data);
    });

    // Listen for language change
    Livewire.on('languageChanged', (locale) => {
        // Refresh page or update locale-specific elements
        console.log('Language changed to:', locale);
    });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.Admin;
}