(function (window, $) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    var modalId = 'itappCriticalConfirmModal';
    var pendingConfirmation = null;

    function ensureModalExists() {
        if (document.getElementById(modalId)) {
            return;
        }

        var markup = [
            '<div class="modal fade" id="' + modalId + '" tabindex="-1" role="dialog" aria-hidden="true">',
            '  <div class="modal-dialog modal-sm" role="document">',
            '    <div class="modal-content">',
            '      <div class="modal-header">',
            '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
            '        <h4 class="modal-title" data-confirm-title>Confirm Action</h4>',
            '      </div>',
            '      <div class="modal-body">',
            '        <p data-confirm-message>Are you sure you want to continue?</p>',
            '      </div>',
            '      <div class="modal-footer">',
            '        <button type="button" class="btn btn-default" data-dismiss="modal" data-confirm-cancel>Cancel</button>',
            '        <button type="button" class="btn btn-danger" data-confirm-submit>Confirm</button>',
            '      </div>',
            '    </div>',
            '  </div>',
            '</div>'
        ].join('');

        $('body').append(markup);

        $(document).on('click', '#' + modalId + ' [data-confirm-submit]', function () {
            if (typeof pendingConfirmation === 'function') {
                var callback = pendingConfirmation;
                pendingConfirmation = null;
                $('#' + modalId).modal('hide');
                callback();
            }
        });

        $(document).on('hidden.bs.modal', '#' + modalId, function () {
            pendingConfirmation = null;
        });
    }

    function resolveLabel(key, fallback) {
        if (!key) {
            return fallback;
        }

        var resolvers = [
            window.ticketShowLabel,
            window.meetingShowLabel,
            window.assetRequestShowLabel
        ];

        for (var i = 0; i < resolvers.length; i += 1) {
            if (typeof resolvers[i] === 'function') {
                var resolved = resolvers[i](key, fallback);
                if (resolved) {
                    return resolved;
                }
            }
        }

        return fallback;
    }

    function disableSubmitControls(form) {
        var $form = $(form);

        $form.find('button[type="submit"], input[type="submit"]').each(function () {
            var $button = $(this);
            if ($button.prop('disabled')) {
                return;
            }

            var originalText = $button.is('input') ? $button.val() : $button.html();
            if (!$button.attr('data-original-text')) {
                $button.attr('data-original-text', originalText);
            }

            var submittingText = $button.attr('data-submitting-text') || '<i class="fa fa-spinner fa-spin"></i> Processing...';
            $button.prop('disabled', true);

            if ($button.is('input')) {
                $button.val('Processing...');
            } else {
                $button.html(submittingText);
            }
        });
    }

    function showConfirmation(options) {
        ensureModalExists();

        var title = options.title || 'Confirm Action';
        var message = options.message || 'Are you sure you want to continue?';
        var cancelText = options.cancelText || 'Cancel';
        var confirmText = options.confirmText || 'Confirm';
        var confirmClass = options.confirmClass || 'btn-danger';

        var $modal = $('#' + modalId);
        $modal.find('[data-confirm-title]').text(title);
        $modal.find('[data-confirm-message]').text(message);
        $modal.find('[data-confirm-cancel]').text(cancelText);

        var $confirmButton = $modal.find('[data-confirm-submit]');
        $confirmButton.text(confirmText);
        $confirmButton.removeClass('btn-danger btn-warning btn-success btn-primary btn-info');
        $confirmButton.addClass(confirmClass);

        pendingConfirmation = options.onConfirm;
        $modal.modal('show');
    }

    window.itappActionConfirm = function (options) {
        showConfirmation(options || {});
    };

    $(document).on('submit', 'form[data-confirm-message], form[data-confirm-i18n-key]', function (event) {
        var form = this;

        if (form.getAttribute('data-confirmed') === 'true') {
            form.removeAttribute('data-confirmed');
            if (form.getAttribute('data-disable-on-submit') === 'true') {
                disableSubmitControls(form);
            }
            return true;
        }

        event.preventDefault();

        var title = form.getAttribute('data-confirm-title') || 'Confirm Action';
        var fallbackMessage = form.getAttribute('data-confirm-message') || 'Are you sure you want to continue?';
        var messageKey = form.getAttribute('data-confirm-i18n-key') || '';
        var message = resolveLabel(messageKey, fallbackMessage);

        showConfirmation({
            title: title,
            message: message,
            confirmText: form.getAttribute('data-confirm-button') || 'Confirm',
            cancelText: form.getAttribute('data-cancel-button') || 'Cancel',
            confirmClass: form.getAttribute('data-confirm-class') || 'btn-danger',
            onConfirm: function () {
                form.setAttribute('data-confirmed', 'true');
                if (form.getAttribute('data-disable-on-submit') === 'true') {
                    disableSubmitControls(form);
                }
                form.submit();
            }
        });

        return false;
    });

    $(document).on('submit', 'form[data-disable-on-submit="true"]', function () {
        if (this.getAttribute('data-submitted') === 'true') {
            return false;
        }

        this.setAttribute('data-submitted', 'true');
        disableSubmitControls(this);
        return true;
    });
})(window, window.jQuery);
