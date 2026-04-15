/**
 * Form Validation System
 * 
 * This file provides:
 * - jQuery Validation plugin integration
 * - Custom validation rules
 * - Real-time validation feedback
 * - Standardized error display
 * - Form-specific validation configurations
 */

(function($) {
    'use strict';

    const FormValidation = {
        config: {
            errorClass: 'has-error',
            successClass: 'has-success',
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('help-block');
                
                // Special handling for select2, radio, checkbox
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2-container'));
                } else if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.prop('type') === 'radio' || element.prop('type') === 'checkbox') {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').removeClass(validClass).addClass(errorClass);
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').removeClass(errorClass).addClass(validClass);
                $(element).removeClass('is-invalid').addClass('is-valid');
            }
        },

        /**
         * Initialize validation system
         */
        init: function() {
            this.setupCustomRules();
            this.setupCustomMessages();
            this.initializeAllForms();
        },

        /**
         * Setup custom validation rules
         */
        setupCustomRules: function() {
            // Asset tag format (alphanumeric with dash)
            $.validator.addMethod('assetTag', function(value, element) {
                return this.optional(element) || /^[A-Za-z0-9\-]+$/.test(value);
            }, 'Asset tag must contain only letters, numbers, and dashes.');

            // MAC address validation
            $.validator.addMethod('macAddress', function(value, element) {
                return this.optional(element) || /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(value);
            }, 'Please enter a valid MAC address (e.g., AA:BB:CC:DD:EE:FF).');

            // IP address validation (already built-in but custom message)
            $.validator.addMethod('ipAddress', function(value, element) {
                return this.optional(element) || /^(\d{1,3}\.){3}\d{1,3}$/.test(value);
            }, 'Please enter a valid IP address (e.g., 192.168.1.1).');

            // Phone number validation
            $.validator.addMethod('phone', function(value, element) {
                return this.optional(element) || /^[0-9+\-\s()]+$/.test(value);
            }, 'Please enter a valid phone number.');

            // Serial number (alphanumeric with special chars)
            $.validator.addMethod('serialNumber', function(value, element) {
                return this.optional(element) || /^[A-Za-z0-9\-_/]+$/.test(value);
            }, 'Serial number must contain only letters, numbers, dashes, underscores, and slashes.');

            // Date not in future
            $.validator.addMethod('notFuture', function(value, element) {
                if (!value) return true;
                const today = new Date();
                today.setHours(23, 59, 59, 999);
                const inputDate = new Date(value);
                return inputDate <= today;
            }, 'Date cannot be in the future.');

            // Date not in past
            $.validator.addMethod('notPast', function(value, element) {
                if (!value) return true;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const inputDate = new Date(value);
                return inputDate >= today;
            }, 'Date cannot be in the past.');

            // Strong password
            $.validator.addMethod('strongPassword', function(value, element) {
                return this.optional(element) || 
                    (/[a-z]/.test(value) && // lowercase letter
                     /[A-Z]/.test(value) && // uppercase letter
                     /[0-9]/.test(value) && // digit
                     value.length >= 8);    // minimum length
            }, 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and be at least 8 characters long.');

            // Unique asset tag (requires AJAX)
            $.validator.addMethod('uniqueAssetTag', function(value, element, param) {
                if (this.optional(element)) return true;
                
                let isValid = false;
                const assetId = $(element).data('asset-id') || '';
                
                $.ajax({
                    url: '/api/validate/asset-tag',
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                    data: {
                        asset_tag: value,
                        exclude_id: assetId
                    },
                    success: function(response) {
                        isValid = response.available;
                    }
                });
                
                return isValid;
            }, 'This asset tag already exists.');

            // Unique email (requires AJAX)
            $.validator.addMethod('uniqueEmail', function(value, element) {
                if (this.optional(element)) return true;
                
                let isValid = false;
                const userId = $(element).data('user-id') || '';
                
                $.ajax({
                    url: '/api/validate/email',
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                    data: {
                        email: value,
                        exclude_id: userId
                    },
                    success: function(response) {
                        isValid = response.available;
                    }
                });
                
                return isValid;
            }, 'This email address is already registered.');
        },

        /**
         * Setup custom validation messages
         */
        setupCustomMessages: function() {
            $.extend($.validator.messages, {
                required: 'This field is required.',
                email: 'Please enter a valid email address.',
                url: 'Please enter a valid URL.',
                date: 'Please enter a valid date.',
                number: 'Please enter a valid number.',
                digits: 'Please enter only digits.',
                maxlength: $.validator.format('Please enter no more than {0} characters.'),
                minlength: $.validator.format('Please enter at least {0} characters.'),
                rangelength: $.validator.format('Please enter a value between {0} and {1} characters long.'),
                range: $.validator.format('Please enter a value between {0} and {1}.'),
                max: $.validator.format('Please enter a value less than or equal to {0}.'),
                min: $.validator.format('Please enter a value greater than or equal to {0}.'),
            });
        },

        /**
         * Initialize all forms with data-validate attribute
         */
        initializeAllForms: function() {
            const self = this;

            // Ticket forms
            $('#createTicketForm, #editTicketForm').each(function() {
                self.initializeTicketForm($(this));
            });

            // Asset forms
            $('#createAssetForm, #editAssetForm').each(function() {
                self.initializeAssetForm($(this));
            });

            // User forms
            $('#createUserForm, #editUserForm').each(function() {
                self.initializeUserForm($(this));
            });

            // Maintenance log forms
            $('#createMaintenanceForm, #editMaintenanceForm').each(function() {
                self.initializeMaintenanceForm($(this));
            });

            // Generic forms with data-validate attribute
            $('form[data-validate="true"]').each(function() {
                self.initializeGenericForm($(this));
            });
        },

        /**
         * Initialize ticket form validation
         */
        initializeTicketForm: function($form) {
            $form.validate($.extend({}, this.config, {
                rules: {
                    subject: {
                        required: true,
                        minlength: 5,
                        maxlength: 255
                    },
                    body: {
                        required: true,
                        minlength: 10
                    },
                    priority_id: {
                        required: true
                    },
                    type_id: {
                        required: true
                    },
                    status_id: {
                        required: true
                    },
                    assigned_to: {
                        number: true
                    },
                    location_id: {
                        number: true
                    },
                    asset_id: {
                        number: true
                    },
                    due_date: {
                        date: true,
                        notPast: true
                    }
                },
                messages: {
                    subject: {
                        required: 'Please enter a ticket subject.',
                        minlength: 'Subject must be at least 5 characters.',
                        maxlength: 'Subject cannot exceed 255 characters.'
                    },
                    body: {
                        required: 'Please describe the ticket.',
                        minlength: 'Description must be at least 10 characters.'
                    },
                    priority_id: 'Please select a priority.',
                    type_id: 'Please select a ticket type.',
                    status_id: 'Please select a status.'
                }
            }));
        },

        /**
         * Initialize asset form validation
         */
        initializeAssetForm: function($form) {
            const isEdit = $form.attr('id') === 'editAssetForm';
            
            $form.validate($.extend({}, this.config, {
                rules: {
                    asset_tag: {
                        required: true,
                        assetTag: true,
                        maxlength: 255,
                        uniqueAssetTag: !isEdit
                    },
                    serial_number: {
                        serialNumber: true,
                        maxlength: 255
                    },
                    model_id: {
                        required: true
                    },
                    division_id: {
                        number: true
                    },
                    supplier_id: {
                        number: true
                    },
                    purchase_date: {
                        date: true,
                        notFuture: true
                    },
                    warranty_months: {
                        number: true,
                        min: 0,
                        max: 120
                    },
                    ip_address: {
                        ipAddress: true
                    },
                    mac_address: {
                        macAddress: true
                    },
                    status_id: {
                        required: true
                    },
                    assigned_to: {
                        number: true
                    },
                    notes: {
                        maxlength: 1000
                    }
                },
                messages: {
                    asset_tag: {
                        required: 'Please enter an asset tag.',
                        maxlength: 'Asset tag cannot exceed 255 characters.'
                    },
                    serial_number: {
                        maxlength: 'Serial number cannot exceed 255 characters.'
                    },
                    model_id: 'Please select an asset model.',
                    purchase_date: 'Please enter a valid purchase date.',
                    warranty_months: {
                        min: 'Warranty months must be at least 0.',
                        max: 'Warranty months cannot exceed 120 (10 years).'
                    },
                    status_id: 'Please select a status.',
                    notes: 'Notes cannot exceed 1000 characters.'
                }
            }));
        },

        /**
         * Initialize user form validation
         */
        initializeUserForm: function($form) {
            const isEdit = $form.attr('id') === 'editUserForm';
            
            $form.validate($.extend({}, this.config, {
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 255,
                        uniqueEmail: !isEdit
                    },
                    password: {
                        required: !isEdit,
                        minlength: 8,
                        strongPassword: true
                    },
                    password_confirmation: {
                        required: function() {
                            return $('#password').val().length > 0;
                        },
                        equalTo: '#password'
                    },
                    division_id: {
                        number: true
                    },
                    phone: {
                        phone: true,
                        maxlength: 20
                    }
                },
                messages: {
                    name: {
                        required: 'Please enter a name.',
                        minlength: 'Name must be at least 3 characters.',
                        maxlength: 'Name cannot exceed 255 characters.'
                    },
                    email: {
                        required: 'Please enter an email address.',
                        email: 'Please enter a valid email address.',
                        maxlength: 'Email cannot exceed 255 characters.'
                    },
                    password: {
                        required: 'Please enter a password.',
                        minlength: 'Password must be at least 8 characters.'
                    },
                    password_confirmation: {
                        required: 'Please confirm your password.',
                        equalTo: 'Password confirmation does not match.'
                    },
                    phone: 'Please enter a valid phone number.'
                }
            }));
        },

        /**
         * Initialize maintenance form validation
         */
        initializeMaintenanceForm: function($form) {
            $form.validate($.extend({}, this.config, {
                rules: {
                    asset_id: {
                        required: true
                    },
                    ticket_id: {
                        number: true
                    },
                    maintenance_type: {
                        required: true
                    },
                    description: {
                        required: true,
                        minlength: 10
                    },
                    performed_by: {
                        required: true
                    },
                    performed_at: {
                        required: true,
                        date: true,
                        notFuture: true
                    },
                    cost: {
                        number: true,
                        min: 0,
                        max: 99999999.99
                    },
                    status: {
                        required: true
                    },
                    notes: {
                        maxlength: 1000
                    },
                    next_maintenance_date: {
                        date: true,
                        notPast: true
                    }
                },
                messages: {
                    asset_id: 'Please select an asset.',
                    maintenance_type: 'Please select a maintenance type.',
                    description: {
                        required: 'Please describe the maintenance performed.',
                        minlength: 'Description must be at least 10 characters.'
                    },
                    performed_by: 'Please select who performed the maintenance.',
                    performed_at: 'Please specify when the maintenance was performed.',
                    cost: {
                        min: 'Cost must be at least 0.',
                        max: 'Cost is too large.'
                    },
                    status: 'Please select a status.',
                    notes: 'Notes cannot exceed 1000 characters.'
                }
            }));
        },

        /**
         * Initialize generic form validation
         */
        initializeGenericForm: function($form) {
            $form.validate(this.config);
        },

        /**
         * Validate form programmatically
         */
        validateForm: function(formSelector) {
            const $form = $(formSelector);
            if ($form.length && $form.data('validator')) {
                return $form.valid();
            }
            return true;
        },

        /**
         * Reset form validation
         */
        resetForm: function(formSelector) {
            const $form = $(formSelector);
            if ($form.length && $form.data('validator')) {
                $form.data('validator').resetForm();
                $form.find('.form-group').removeClass('has-error has-success');
                $form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            }
        },

        /**
         * Show validation errors manually
         */
        showErrors: function(formSelector, errors) {
            const $form = $(formSelector);
            if ($form.length && $form.data('validator')) {
                $form.data('validator').showErrors(errors);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        FormValidation.init();
    });

    // Expose to window for manual usage
    window.FormValidation = FormValidation;

})(jQuery);
