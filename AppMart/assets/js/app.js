/**
 * AppMart Main JavaScript
 * C:\xampp\htdocs\AppMart\assets\js\app.js
 * Create at 2508041600 Ver1.00
 */

// App namespace
window.AppMart = {
    // Configuration
    config: {
        baseUrl: window.location.origin,
        apiUrl: window.location.origin + '/api',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    },

    // Utility functions
    utils: {
        // Make AJAX requests
        ajax: function(url, options = {}) {
            const defaultOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            // Add CSRF token for POST requests
            if (options.method === 'POST' && AppMart.config.csrfToken) {
                defaultOptions.headers['X-CSRF-Token'] = AppMart.config.csrfToken;
            }

            const finalOptions = Object.assign(defaultOptions, options);

            return fetch(url, finalOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    throw error;
                });
        },

        // Show notification
        notify: function(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                min-width: 300px;
                animation: slideIn 0.3s ease-out;
            `;

            document.body.appendChild(notification);

            // Auto remove after duration
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, duration);
        },

        // Format price
        formatPrice: function(price) {
            if (price == 0) {
                return 'Free';
            }
            return '$' + parseFloat(price).toFixed(2);
        },

        // Debounce function
        debounce: function(func, wait) {
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
    },

    // Form handling
    forms: {
        // Setup form validation
        setupValidation: function(formSelector) {
            const form = document.querySelector(formSelector);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                let isValid = true;
                const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        AppMart.forms.showFieldError(input, 'This field is required');
                        isValid = false;
                    } else {
                        AppMart.forms.clearFieldError(input);
                    }
                });

                // Email validation
                const emailInputs = form.querySelectorAll('input[type="email"]');
                emailInputs.forEach(input => {
                    if (input.value && !AppMart.forms.isValidEmail(input.value)) {
                        AppMart.forms.showFieldError(input, 'Please enter a valid email address');
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        },

        // Show field error
        showFieldError: function(field, message) {
            AppMart.forms.clearFieldError(field);
            field.classList.add('error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            errorDiv.style.cssText = 'color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;';
            
            field.parentNode.appendChild(errorDiv);
        },

        // Clear field error
        clearFieldError: function(field) {
            field.classList.remove('error');
            const errorDiv = field.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        },

        // Email validation
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    },

    // Search functionality
    search: {
        // Initialize search functionality
        init: function() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                const debouncedSearch = AppMart.utils.debounce(AppMart.search.performSearch, 300);
                searchInput.addEventListener('input', debouncedSearch);
            }
        },

        // Perform search
        performSearch: function(event) {
            const query = event.target.value.trim();
            if (query.length < 2) {
                AppMart.search.clearResults();
                return;
            }

            const searchUrl = `${AppMart.config.apiUrl}/search?q=${encodeURIComponent(query)}`;
            
            AppMart.utils.ajax(searchUrl)
                .then(data => {
                    AppMart.search.displayResults(data.results || []);
                })
                .catch(error => {
                    console.error('Search error:', error);
                    AppMart.search.clearResults();
                });
        },

        // Display search results
        displayResults: function(results) {
            const resultsContainer = document.getElementById('search-results');
            if (!resultsContainer) return;

            if (results.length === 0) {
                resultsContainer.innerHTML = '<p>No results found.</p>';
                return;
            }

            const resultsHtml = results.map(app => `
                <div class="search-result-item">
                    <h4><a href="/apps/show?id=${app.id}">${app.title}</a></h4>
                    <p>${app.short_description || app.description.substring(0, 100) + '...'}</p>
                    <span class="price">${AppMart.utils.formatPrice(app.price)}</span>
                </div>
            `).join('');

            resultsContainer.innerHTML = resultsHtml;
        },

        // Clear search results
        clearResults: function() {
            const resultsContainer = document.getElementById('search-results');
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
        }
    },

    // App management
    apps: {
        // Delete app confirmation
        confirmDelete: function(appId, appTitle) {
            if (confirm(`Are you sure you want to delete "${appTitle}"? This action cannot be undone.`)) {
                const deleteUrl = `/apps/delete?id=${appId}`;
                
                AppMart.utils.ajax(deleteUrl, { method: 'POST' })
                    .then(data => {
                        if (data.success) {
                            AppMart.utils.notify('App deleted successfully', 'success');
                            // Reload page or remove element
                            location.reload();
                        } else {
                            AppMart.utils.notify(data.message || 'Failed to delete app', 'error');
                        }
                    })
                    .catch(error => {
                        AppMart.utils.notify('An error occurred while deleting the app', 'error');
                    });
            }
        },

        // Toggle featured status
        toggleFeatured: function(appId) {
            const toggleUrl = `/admin/apps/toggle-featured?id=${appId}`;
            
            AppMart.utils.ajax(toggleUrl, { method: 'POST' })
                .then(data => {
                    if (data.success) {
                        AppMart.utils.notify('App status updated', 'success');
                        location.reload();
                    } else {
                        AppMart.utils.notify(data.message || 'Failed to update app status', 'error');
                    }
                })
                .catch(error => {
                    AppMart.utils.notify('An error occurred while updating the app', 'error');
                });
        }
    },

    // Initialize application
    init: function() {
        // Setup form validation for all forms
        document.querySelectorAll('form').forEach(form => {
            AppMart.forms.setupValidation(`#${form.id}`);
        });

        // Initialize search
        AppMart.search.init();

        // Add click handlers for dynamic content
        document.addEventListener('click', function(e) {
            // Handle delete confirmations
            if (e.target.matches('[data-confirm-delete]')) {
                e.preventDefault();
                const appId = e.target.dataset.appId;
                const appTitle = e.target.dataset.appTitle;
                AppMart.apps.confirmDelete(appId, appTitle);
            }

            // Handle featured toggle
            if (e.target.matches('[data-toggle-featured]')) {
                e.preventDefault();
                const appId = e.target.dataset.appId;
                AppMart.apps.toggleFeatured(appId);
            }
        });

        console.log('AppMart application initialized');
    }
};

// CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .form-input.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', AppMart.init);