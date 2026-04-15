/**
 * ENHANCED UI/UX JAVASCRIPT UTILITIES
 * Modern interactive functionality and user experience improvements
 * Version: 2.0
 * Date: November 21, 2025
 */

(function($) {
    'use strict';

    // ========================================
    // TOAST NOTIFICATION SYSTEM
    // ========================================
    window.showToast = function(type, title, message, duration = 5000) {
        // Create toast container if not exists
        if ($('.toast-container').length === 0) {
            $('body').append('<div class="toast-container"></div>');
        }

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = $(`
            <div class="toast toast-${type}">
                <div class="toast-icon">
                    <i class="fa ${icons[type]}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    ${message ? `<div class="toast-message">${message}</div>` : ''}
                </div>
                <div class="toast-close">
                    <i class="fa fa-times"></i>
                </div>
            </div>
        `);

        $('.toast-container').append(toast);

        // Close button
        toast.find('.toast-close').on('click', function() {
            toast.addClass('toast-removing');
            setTimeout(() => toast.remove(), 300);
        });

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.addClass('toast-removing');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    };

    // ========================================
    // LOADING OVERLAY
    // ========================================
    window.showLoading = function(message = 'Processing...') {
        if ($('.loading-overlay').length > 0) return;

        const overlay = $(`
            <div class="loading-overlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <h4>${message}</h4>
                    <p class="text-muted">Please wait...</p>
                </div>
            </div>
        `);

        $('body').append(overlay);
    };

    window.hideLoading = function() {
        $('.loading-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    };

    // ========================================
    // BUTTON LOADING STATE
    // ========================================
    $.fn.buttonLoading = function(loading) {
        return this.each(function() {
            const $btn = $(this);
            if (loading) {
                $btn.data('original-text', $btn.html());
                $btn.prop('disabled', true).addClass('btn-loading');
            } else {
                $btn.prop('disabled', false).removeClass('btn-loading');
                if ($btn.data('original-text')) {
                    $btn.html($btn.data('original-text'));
                }
            }
        });
    };

    // ========================================
    // ENHANCED CONFIRM DIALOG
    // ========================================
    window.confirmAction = function(options) {
        const defaults = {
            title: 'Are you sure?',
            message: 'This action cannot be undone.',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            confirmClass: 'btn-danger',
            onConfirm: function() {}
        };

        const settings = $.extend({}, defaults, options);

        const modal = $(`
            <div class="modal fade modal-enhanced" id="confirmModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">
                                <i class="fa fa-exclamation-triangle"></i> ${settings.title}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <p>${settings.message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                ${settings.cancelText}
                            </button>
                            <button type="button" class="btn ${settings.confirmClass}" id="confirmBtn">
                                ${settings.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        $('body').append(modal);
        modal.modal('show');

        modal.find('#confirmBtn').on('click', function() {
            modal.modal('hide');
            settings.onConfirm();
        });

        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    };

    // ========================================
    // AUTO-SAVE FORM
    // ========================================
    $.fn.autoSave = function(options) {
        const settings = $.extend({
            interval: 30000, // 30 seconds
            url: null,
            onSave: function(data) {},
            onError: function(error) {}
        }, options);

        return this.each(function() {
            const $form = $(this);
            let timer;
            let hasChanges = false;

            $form.on('input change', function() {
                hasChanges = true;
                clearTimeout(timer);
                timer = setTimeout(function() {
                    if (hasChanges) {
                        saveForm();
                    }
                }, settings.interval);
            });

            function saveForm() {
                const formData = $form.serialize();

                $.ajax({
                    url: settings.url || $form.attr('action'),
                    method: 'POST',
                    data: formData + '&_autosave=1',
                    success: function(response) {
                        hasChanges = false;
                        showToast('success', 'Auto-saved', 'Changes saved automatically', 2000);
                        settings.onSave(response);
                    },
                    error: function(xhr) {
                        settings.onError(xhr);
                    }
                });
            }
        });
    };

    // ========================================
    // ENHANCED DATATABLE INITIALIZATION
    // ========================================
    window.initEnhancedDataTable = function(selector, options = {}) {
        const defaults = {
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                 '<"row"<"col-sm-12"Br>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    }
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-text-o"></i> CSV',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-default btn-sm',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i> Columns',
                    className: 'btn btn-default btn-sm'
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries to show",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found",
                emptyTable: "No data available in table",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        };

        const config = $.extend(true, {}, defaults, options);

        // Check if table exists and has data
        const $table = $(selector);
        if ($table.length === 0) {
            console.warn('Table not found:', selector);
            return null;
        }

        // Wrap table in enhanced container
        if (!$table.closest('.datatable-enhanced').length) {
            $table.wrap('<div class="datatable-enhanced"></div>');
        }

        return $table.DataTable(config);
    };

    // ========================================
    // FORM VALIDATION ENHANCEMENT
    // ========================================
    $.fn.enhancedValidation = function() {
        return this.each(function() {
            const $form = $(this);

            $form.on('submit', function(e) {
                let isValid = true;

                // Remove previous errors
                $form.find('.error-message').remove();
                $form.find('.has-error').removeClass('has-error');

                // Check required fields
                $form.find('[required]').each(function() {
                    const $field = $(this);
                    if (!$field.val() || $field.val().trim() === '') {
                        isValid = false;
                        $field.closest('.form-group').addClass('has-error');
                        $field.after('<span class="error-message text-danger">This field is required</span>');
                    }
                });

                // Check email fields
                $form.find('input[type="email"]').each(function() {
                    const $field = $(this);
                    const email = $field.val();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (email && !emailRegex.test(email)) {
                        isValid = false;
                        $field.closest('.form-group').addClass('has-error');
                        $field.after('<span class="error-message text-danger">Please enter a valid email address</span>');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    showToast('error', 'Validation Error', 'Please fill in all required fields correctly');
                    
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $form.find('.has-error').first().offset().top - 100
                    }, 500);
                }

                return isValid;
            });
        });
    };

    // ========================================
    // AJAX FORM SUBMISSION
    // ========================================
    $.fn.ajaxForm = function(options) {
        const settings = $.extend({
            onSuccess: function(response) {},
            onError: function(error) {},
            showLoading: true,
            showSuccess: true
        }, options);

        return this.each(function() {
            const $form = $(this);

            $form.on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const $submitBtn = $form.find('button[type="submit"]');

                if (settings.showLoading) {
                    $submitBtn.buttonLoading(true);
                }

                $.ajax({
                    url: $form.attr('action'),
                    method: $form.attr('method') || 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $submitBtn.buttonLoading(false);

                        if (settings.showSuccess) {
                            showToast('success', 'Success', response.message || 'Operation completed successfully');
                        }

                        settings.onSuccess(response);

                        // Reset form if needed
                        if (response.reset) {
                            $form[0].reset();
                        }

                        // Redirect if specified
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.buttonLoading(false);

                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }

                        showToast('error', 'Error', message);
                        settings.onError(xhr);
                    }
                });
            });
        });
    };

    // ========================================
    // BULK SELECTION HELPER
    // ========================================
    window.initBulkSelect = function(tableSelector) {
        const $table = $(tableSelector);
        const $selectAll = $table.find('#select-all');
        const $checkboxes = $table.find('.select-item');

        $selectAll.on('change', function() {
            $checkboxes.prop('checked', this.checked);
            updateBulkActions();
        });

        $checkboxes.on('change', function() {
            const allChecked = $checkboxes.length === $checkboxes.filter(':checked').length;
            $selectAll.prop('checked', allChecked);
            updateBulkActions();
        });

        function updateBulkActions() {
            const selectedCount = $checkboxes.filter(':checked').length;
            $('.bulk-actions').toggle(selectedCount > 0);
            $('.selected-count').text(selectedCount);
        }
    };

    // ========================================
    // CLIPBOARD COPY
    // ========================================
    window.copyToClipboard = function(text) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        showToast('success', 'Copied', 'Text copied to clipboard', 2000);
    };

    // ========================================
    // INITIALIZE ON DOM READY
    // ========================================
    $(document).ready(function() {
        // Auto-dismiss alerts
        $('.alert-dismissible').each(function() {
            const $alert = $(this);
            setTimeout(() => {
                $alert.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        });

        // Smooth scroll to top
        $('a[href="#top"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 600);
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize popovers
        $('[data-toggle="popover"]').popover();

        // Auto-focus first input in modals
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('input:text, textarea').filter(':visible:first').focus();
        });

        // Handle AJAX errors globally
        $(document).ajaxError(function(event, xhr, settings, error) {
            if (xhr.status === 419) {
                showToast('error', 'Session Expired', 'Your session has expired. Please refresh the page.');
            } else if (xhr.status === 403) {
                showToast('error', 'Access Denied', 'You do not have permission to perform this action.');
            } else if (xhr.status === 500) {
                showToast('error', 'Server Error', 'An internal server error occurred. Please contact support.');
            }
        });

        // Add loading state to all forms with data-ajax attribute
        $('form[data-ajax="true"]').ajaxForm();

        // Add validation to all forms with data-validate attribute
        $('form[data-validate="true"]').enhancedValidation();
    });

})(jQuery);

// ========================================
// UTILITY FUNCTIONS
// ========================================

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format currency
function formatCurrency(amount, currency = 'Rp') {
    return currency + ' ' + formatNumber(amount.toFixed(2));
}

// Debounce function
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

// Generate random ID
function generateId(prefix = 'id') {
    return prefix + '_' + Math.random().toString(36).substr(2, 9);
}

// Export functions
window.formatNumber = formatNumber;
window.formatCurrency = formatCurrency;
window.debounce = debounce;
window.generateId = generateId;
