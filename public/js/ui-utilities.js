/**
 * UI Utility Functions for IT Quty System
 */

// Namespace untuk aplikasi
window.ITQuty = window.ITQuty || {};

ITQuty.UI = {
    /**
     * Show loading spinner
     */
    showLoading: function(text = 'Loading...') {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            const loadingText = spinner.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = text;
            }
            spinner.style.display = 'flex';
        }
    },

    /**
     * Hide loading spinner
     */
    hideLoading: function() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'none';
        }
    },

    /**
     * Show confirmation dialog
     */
    confirm: function(message, callback, options = {}) {
        const defaults = {
            title: 'Confirmation',
            confirmText: 'Yes',
            cancelText: 'Cancel',
            type: 'warning'
        };
        
        const settings = Object.assign(defaults, options);
        
        if (typeof swal !== 'undefined') {
            swal({
                title: settings.title,
                text: message,
                type: settings.type,
                showCancelButton: true,
                confirmButtonText: settings.confirmText,
                cancelButtonText: settings.cancelText
            }).then(function(result) {
                if (result.value && typeof callback === 'function') {
                    callback();
                }
            });
        } else if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },

    /**
     * Add loading state to button
     */
    loadingButton: function(button, loading = true) {
        if (loading) {
            button.classList.add('btn-loading');
            button.disabled = true;
        } else {
            button.classList.remove('btn-loading');
            button.disabled = false;
        }
    },

    /**
     * Format number with thousand separators
     */
    formatNumber: function(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    },

    /**
     * Copy text to clipboard
     */
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showSuccess('Text copied to clipboard!');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showSuccess('Text copied to clipboard!');
        }
    },

    /**
     * Validate form before submission
     */
    validateForm: function(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            showError('Please fill in all required fields.');
        }
        
        return isValid;
    },

    /**
     * Auto-hide alerts after specified time
     */
    autoHideAlerts: function(selector = '.alert', delay = 5000) {
        const alerts = document.querySelectorAll(selector);
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 300);
            }, delay);
        });
    },

    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    },

    /**
     * Initialize popovers
     */
    initPopovers: function() {
        if (typeof $ !== 'undefined' && $.fn.popover) {
            $('[data-toggle="popover"]').popover();
        }
    },

    /**
     * Smooth scroll to element
     */
    scrollTo: function(element, offset = 0) {
        const target = typeof element === 'string' ? document.querySelector(element) : element;
        if (target) {
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    },

    /**
     * Debounce function for search inputs
     */
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
};

// AJAX Setup untuk CSRF token
ITQuty.Ajax = {
    setup: function() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    },

    /**
     * Standard AJAX request with error handling
     */
    request: function(options) {
        const defaults = {
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                ITQuty.UI.showLoading();
            },
            complete: function() {
                ITQuty.UI.hideLoading();
            },
            error: function(xhr, status, error) {
                let message = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    message = 'Session expired. Please refresh the page.';
                } else if (xhr.status === 403) {
                    message = 'You do not have permission to perform this action.';
                } else if (xhr.status === 404) {
                    message = 'The requested resource was not found.';
                } else if (xhr.status >= 500) {
                    message = 'Server error. Please contact administrator.';
                }
                
                showError(message);
            }
        };
        
        const settings = Object.assign(defaults, options);
        
        if (typeof $ !== 'undefined') {
            return $.ajax(settings);
        }
    }
};

// Form utilities
ITQuty.Form = {
    /**
     * Serialize form data as object
     */
    serializeObject: function(form) {
        const formData = new FormData(form);
        const object = {};
        
        formData.forEach(function(value, key) {
            if (object[key]) {
                if (!Array.isArray(object[key])) {
                    object[key] = [object[key]];
                }
                object[key].push(value);
            } else {
                object[key] = value;
            }
        });
        
        return object;
    },

    /**
     * Reset form and clear errors
     */
    reset: function(form) {
        form.reset();
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(function(field) {
            field.classList.remove('error');
        });
        
        const errorMessages = form.querySelectorAll('.help-block.error');
        errorMessages.forEach(function(message) {
            message.remove();
        });
    },

    /**
     * Show validation errors on form
     */
    showErrors: function(form, errors) {
        // Clear existing errors first
        const existingErrors = form.querySelectorAll('.help-block.error');
        existingErrors.forEach(function(error) {
            error.remove();
        });
        
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(function(field) {
            field.classList.remove('error');
        });
        
        // Show new errors
        Object.keys(errors).forEach(function(fieldName) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('error');
                
                const errorMessage = document.createElement('span');
                errorMessage.className = 'help-block error';
                errorMessage.textContent = errors[fieldName][0];
                
                field.parentNode.appendChild(errorMessage);
            }
        });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Setup AJAX
    ITQuty.Ajax.setup();
    
    // Initialize UI components
    ITQuty.UI.initTooltips();
    ITQuty.UI.initPopovers();
    ITQuty.UI.autoHideAlerts();
    
    // Form validation on submit
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!ITQuty.UI.validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-save drafts (if enabled)
    const draftForms = document.querySelectorAll('form[data-auto-save="true"]');
    draftForms.forEach(function(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(function(input) {
            input.addEventListener('input', ITQuty.UI.debounce(function() {
                // Save draft logic here
                const formData = ITQuty.Form.serializeObject(form);
                localStorage.setItem('draft_' + form.id, JSON.stringify(formData));
            }, 1000));
        });
    });
});