/**
 * ITQuty - Enhanced UX JavaScript
 * Quick Wins Implementation
 * - Loading states
 * - Confirmation dialogs
 * - Tooltips
 * - Better form validation feedback
 */

(function($) {
    'use strict';

    // ===================================
    // 1. GLOBAL LOADING SPINNER
    // ===================================
    
    // Create loading overlay HTML with proper ARIA attributes for accessibility
    const loadingHTML = `
        <div id="global-loading" 
             class="global-loading" 
             role="alert" 
             aria-live="assertive" 
             aria-busy="false">
            <div class="loading-overlay"></div>
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status" aria-label="Loading">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="loading-text" aria-live="polite">Please wait...</div>
            </div>
        </div>
    `;
    
    // Inject loading HTML on page load
    $(document).ready(function() {
        if ($('#global-loading').length === 0) {
            $('body').append(loadingHTML);
        }
    });
    
    // Track loading state with UX best practice delay (1 second threshold)
    let loadingTimer = null;
    let isAjaxActive = false;
    let ajaxStartTime = null;
    
    // Show loading on AJAX requests (with 1s delay per UX best practices)
    $(document).ajaxStart(function() {
        isAjaxActive = true;
        ajaxStartTime = Date.now();
        
        // Best Practice: Only show spinner if request takes more than 1 second
        // For operations < 1 second, don't show spinner to avoid flickering
        loadingTimer = setTimeout(function() {
            if (isAjaxActive) {
                $('#global-loading').addClass('show').attr('aria-busy', 'true');
            }
        }, 1000); // 1 second threshold per UX best practices
    });
    
    $(document).ajaxStop(function() {
        isAjaxActive = false;
        
        const elapsedTime = ajaxStartTime ? Date.now() - ajaxStartTime : 0;
        
        // Clear timer if it hasn't triggered yet
        if (loadingTimer) {
            clearTimeout(loadingTimer);
            loadingTimer = null;
        }
        
        // If spinner was shown, ensure it's visible for at least 300ms
        // to prevent jarring flash (min display time)
        if ($('#global-loading').hasClass('show')) {
            const minDisplayTime = 300;
            const timeShown = elapsedTime >= 1000 ? elapsedTime - 1000 : 0;
            const remainingTime = Math.max(0, minDisplayTime - timeShown);
            
            setTimeout(function() {
                $('#global-loading').removeClass('show').attr('aria-busy', 'false');
            }, remainingTime);
        }
        
        ajaxStartTime = null;
    });
    
    // Function to manually show/hide loading with custom message
    window.showLoading = function(message, skipDelay) {
        if (message) {
            $('#global-loading .loading-text').text(message);
        }
        
        // Clear any existing timer
        if (loadingTimer) {
            clearTimeout(loadingTimer);
            loadingTimer = null;
        }
        
        // For manual calls, can optionally skip delay
        if (skipDelay === true) {
            $('#global-loading').addClass('show').attr('aria-busy', 'true');
        } else {
            // Still apply small delay for better UX
            loadingTimer = setTimeout(function() {
                $('#global-loading').addClass('show').attr('aria-busy', 'true');
            }, 500);
        }
    };
    
    window.hideLoading = function() {
        // Clear any existing timer
        if (loadingTimer) {
            clearTimeout(loadingTimer);
            loadingTimer = null;
        }
        
        $('#global-loading').removeClass('show').attr('aria-busy', 'false');
    };

    // ===================================
    // 2. CONFIRMATION DIALOGS
    // ===================================
    
    /**
     * Enhanced delete confirmation
     * Usage: Add class 'delete-confirm' to delete buttons/links
     * Add data-item-name attribute for better messages
     */
    $(document).on('click', '.delete-confirm', function(e) {
        e.preventDefault();
        const $this = $(this);
        const itemName = $this.data('item-name') || 'this item';
        const confirmText = $this.data('confirm-text') || `Are you sure you want to delete ${itemName}?`;
        const url = $this.attr('href') || $this.data('url');
        const form = $this.closest('form');
        
        if (confirm(confirmText + '\n\nThis action cannot be undone!')) {
            if (form.length) {
                showLoading('Deleting...');
                form.submit();
            } else if (url) {
                showLoading('Deleting...');
                window.location.href = url;
            }
        }
    });
    
    /**
     * Generic confirmation for any action
     * Usage: Add class 'confirm-action' to any button/link
     * Add data-confirm-message for custom message
     */
    $(document).on('click', '.confirm-action', function(e) {
        const confirmMessage = $(this).data('confirm-message') || 'Are you sure you want to continue?';
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });

    // ===================================
    // 3. FORM ENHANCEMENTS
    // ===================================
    
    /**
     * Disable submit button on form submission to prevent double-submit
     */
    $('form').on('submit', function(e) {
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
        
        // Don't disable if form has validation errors
        if ($form[0].checkValidity && !$form[0].checkValidity()) {
            return;
        }
        
        // Disable button and show loading
        $submitBtn.prop('disabled', true);
        
        const originalText = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Re-enable after 5 seconds as fallback
        setTimeout(function() {
            $submitBtn.prop('disabled', false).html(originalText);
        }, 5000);
    });
    
    /**
     * Auto-focus first input field in forms
     */
    $(document).ready(function() {
        $('form input[type="text"], form input[type="email"], form textarea').first().focus();
    });
    
    /**
     * Character counter for textareas with maxlength
     */
    $('textarea[maxlength]').each(function() {
        const $textarea = $(this);
        const maxLength = $textarea.attr('maxlength');
        const $counter = $('<small class="form-text text-muted char-counter"></small>');
        $textarea.after($counter);
        
        function updateCounter() {
            const remaining = maxLength - $textarea.val().length;
            $counter.text(`${remaining} characters remaining`);
            
            if (remaining < 20) {
                $counter.addClass('text-warning').removeClass('text-muted');
            } else {
                $counter.addClass('text-muted').removeClass('text-warning');
            }
        }
        
        updateCounter();
        $textarea.on('input', updateCounter);
    });

    // ===================================
    // 4. TOOLTIP INITIALIZATION
    // ===================================
    
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Re-initialize tooltips for dynamically added content
        $(document).on('mouseenter', '[data-toggle="tooltip"]', function() {
            if (!$(this).data('bs.tooltip')) {
                $(this).tooltip('show');
            }
        });
    });

    // ===================================
    // 5. TABLE ENHANCEMENTS
    // ===================================
    
    /**
     * Select all checkbox functionality
     */
    $(document).on('change', '#selectAll', function() {
        const checked = $(this).prop('checked');
        $('.row-select').prop('checked', checked);
        updateBulkActionsVisibility();
    });
    
    $(document).on('change', '.row-select', function() {
        updateSelectAllState();
        updateBulkActionsVisibility();
    });
    
    function updateSelectAllState() {
        const total = $('.row-select').length;
        const checked = $('.row-select:checked').length;
        $('#selectAll').prop('checked', total === checked && total > 0);
    }
    
    function updateBulkActionsVisibility() {
        const selectedCount = $('.row-select:checked').length;
        if (selectedCount > 0) {
            $('.bulk-actions-toolbar').slideDown(200);
            $('.selected-count').text(selectedCount);
        } else {
            $('.bulk-actions-toolbar').slideUp(200);
        }
    }

    // ===================================
    // 6. SEARCH ENHANCEMENTS
    // ===================================
    
    /**
     * Debounced search input
     */
    let searchTimeout;
    $(document).on('input', '.search-input-debounce', function() {
        const $input = $(this);
        const delay = $input.data('debounce-delay') || 500;
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $input.closest('form').submit();
        }, delay);
    });

    // ===================================
    // 7. NOTIFICATION ENHANCEMENTS
    // ===================================
    
    /**
     * Auto-dismiss alerts after 5 seconds
     */
    $(document).ready(function() {
        $('.alert.auto-dismiss').each(function() {
            const $alert = $(this);
            setTimeout(function() {
                $alert.fadeOut(400, function() {
                    $(this).remove();
                });
            }, 5000);
        });
    });

    // ===================================
    // 8. KEYBOARD SHORTCUTS
    // ===================================
    
    /**
     * Global keyboard shortcuts
     * Ctrl+K or Cmd+K: Focus search
     * Esc: Close modals/dropdowns
     */
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const $search = $('#global-search, .global-search-input, input[name="search"]').first();
            if ($search.length) {
                $search.focus().select();
            }
        }
        
        // Esc: Close modals
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
            $('.dropdown-menu').removeClass('show');
        }
    });

    // ===================================
    // 9. COPY TO CLIPBOARD
    // ===================================
    
    /**
     * Copy to clipboard functionality
     * Usage: Add class 'copy-to-clipboard' and data-clipboard-text attribute
     */
    $(document).on('click', '.copy-to-clipboard', function() {
        const text = $(this).data('clipboard-text');
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Show feedback
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-check"></i> Copied!');
        setTimeout(function() {
            $btn.html(originalHtml);
        }, 2000);
    });

    // ===================================
    // 10. BREADCRUMB ACTIVE STATE
    // ===================================
    
    /**
     * Highlight active menu items based on current URL
     */
    $(document).ready(function() {
        const currentPath = window.location.pathname;
        $('.sidebar-menu a').each(function() {
            const href = $(this).attr('href');
            if (href && currentPath.includes(href) && href !== '/') {
                $(this).closest('li').addClass('active');
                $(this).closest('.treeview').addClass('active menu-open');
            }
        });
    });

    // ===================================
    // 11. DYNAMIC SELECT DEPENDENCIES
    // ===================================
    
    /**
     * Cascade select dropdowns
     * Example: When division changes, filter locations
     */
    window.setupSelectDependency = function(parentSelector, childSelector, dataUrl) {
        $(document).on('change', parentSelector, function() {
            const parentValue = $(this).val();
            const $child = $(childSelector);
            
            if (!parentValue) {
                $child.html('<option value="">Select...</option>').prop('disabled', true);
                return;
            }
            
            $child.prop('disabled', true);
            showLoading('Loading options...');
            
            $.ajax({
                url: dataUrl,
                data: { parent_id: parentValue },
                success: function(data) {
                    let options = '<option value="">Select...</option>';
                    $.each(data, function(key, value) {
                        options += `<option value="${value.id}">${value.name}</option>`;
                    });
                    $child.html(options).prop('disabled', false);
                },
                error: function() {
                    $child.html('<option value="">Error loading options</option>');
                },
                complete: function() {
                    hideLoading();
                }
            });
        });
    };

    // ===================================
    // 12. PRINT FUNCTIONALITY
    // ===================================
    
    /**
     * Print specific elements
     * Usage: Add class 'print-trigger' and data-print-target
     */
    $(document).on('click', '.print-trigger', function() {
        const target = $(this).data('print-target');
        const $printArea = $(target);
        
        if ($printArea.length) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print</title>');
            printWindow.document.write('<link rel="stylesheet" href="/css/all.css">');
            printWindow.document.write('</head><body>');
            printWindow.document.write($printArea.html());
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    });

    // ===================================
    // 13. AUTO-SAVE DRAFT (for forms)
    // ===================================
    
    /**
     * Auto-save form data to localStorage
     * Usage: Add class 'auto-save-form' to form
     */
    $('.auto-save-form').each(function() {
        const $form = $(this);
        const formId = $form.attr('id') || 'form-' + Math.random();
        const storageKey = 'draft-' + formId;
        
        // Load draft
        const draft = localStorage.getItem(storageKey);
        if (draft) {
            try {
                const data = JSON.parse(draft);
                $.each(data, function(name, value) {
                    $form.find(`[name="${name}"]`).val(value);
                });
                
                // Show restore notification
                const $notice = $(`
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-info-circle"></i> Draft restored.
                        <button type="button" class="btn btn-sm btn-link clear-draft">Clear draft</button>
                    </div>
                `);
                $form.prepend($notice);
                
                $notice.find('.clear-draft').on('click', function() {
                    localStorage.removeItem(storageKey);
                    $notice.remove();
                });
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
        
        // Auto-save on input
        $form.on('input change', 'input, textarea, select', debounce(function() {
            const formData = {};
            $form.find('input, textarea, select').each(function() {
                const $field = $(this);
                if ($field.attr('name') && $field.val()) {
                    formData[$field.attr('name')] = $field.val();
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(formData));
        }, 1000));
        
        // Clear draft on successful submit
        $form.on('submit', function() {
            localStorage.removeItem(storageKey);
        });
    });
    
    // Debounce utility
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

})(jQuery);
