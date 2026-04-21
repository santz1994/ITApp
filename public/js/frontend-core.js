(function (window, document) {
    'use strict';

    var FrontendCore = {};

    function resolveTheme(theme) {
        return theme === 'dark' ? 'dark' : 'light';
    }

    function getConfig() {
        return window.ITAPP_FRONTEND_CONFIG || {};
    }

    function getPreferenceUpdateUrl() {
        var config = getConfig();
        var template = String(config.preferenceUpdateUrlTemplate || '');

        if (template === '') {
            return '';
        }

        return template.replace('__KEY__', 'theme');
    }

    function updateToggleVisuals(theme) {
        var toggles = document.querySelectorAll('[data-theme-toggle]');

        Array.prototype.forEach.call(toggles, function (toggle) {
            var label = toggle.querySelector('[data-theme-label]');
            var icon = toggle.querySelector('[data-theme-icon]');
            var nextTheme = theme === 'dark' ? 'light' : 'dark';

            if (label) {
                label.textContent = nextTheme === 'dark' ? 'Dark Mode' : 'Light Mode';
            }

            if (icon) {
                icon.className = 'fa ' + (theme === 'dark' ? 'fa-sun-o' : 'fa-moon-o');
            }

            toggle.setAttribute('data-current-theme', theme);
        });
    }

    function applyTheme(theme) {
        var resolvedTheme = resolveTheme(theme);
        document.body.setAttribute('data-itapp-theme', resolvedTheme);
        window.localStorage.setItem('itapp_theme', resolvedTheme);
        updateToggleVisuals(resolvedTheme);
        return resolvedTheme;
    }

    function persistTheme(theme) {
        var targetUrl = getPreferenceUpdateUrl();

        if (targetUrl === '') {
            return;
        }

        fetch(targetUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ value: theme })
        }).catch(function () {
            // Ignore persistence errors to keep UI responsive.
        });
    }

    function bindThemeToggle() {
        var toggles = document.querySelectorAll('[data-theme-toggle]');

        if (!toggles.length) {
            return;
        }

        var initialTheme = resolveTheme(
            window.localStorage.getItem('itapp_theme') || document.body.getAttribute('data-itapp-theme') || 'light'
        );

        applyTheme(initialTheme);

        Array.prototype.forEach.call(toggles, function (toggle) {
            toggle.addEventListener('click', function () {
                var currentTheme = resolveTheme(document.body.getAttribute('data-itapp-theme'));
                var nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme);
                persistTheme(nextTheme);
            });
        });
    }

    function bindDisableOnSubmit() {
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || form.getAttribute('data-disable-on-submit') !== 'true') {
                return;
            }

            if (form.getAttribute('data-submitted') === 'true') {
                event.preventDefault();
                return;
            }

            form.setAttribute('data-submitted', 'true');

            var submitElements = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            Array.prototype.forEach.call(submitElements, function (submitElement) {
                if (submitElement.disabled) {
                    return;
                }

                submitElement.disabled = true;

                if (submitElement.tagName === 'BUTTON') {
                    if (!submitElement.getAttribute('data-original-html')) {
                        submitElement.setAttribute('data-original-html', submitElement.innerHTML);
                    }

                    var loadingLabel = submitElement.getAttribute('data-loading-label') || 'Processing...';
                    submitElement.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + loadingLabel;
                }

                if (submitElement.tagName === 'INPUT') {
                    if (!submitElement.getAttribute('data-original-value')) {
                        submitElement.setAttribute('data-original-value', submitElement.value);
                    }

                    submitElement.value = submitElement.getAttribute('data-loading-label') || 'Processing...';
                }
            });
        }, true);
    }

    FrontendCore.init = function () {
        bindThemeToggle();
        bindDisableOnSubmit();
    };

    window.ITAppFrontend = FrontendCore;

    document.addEventListener('DOMContentLoaded', function () {
        FrontendCore.init();
    });
})(window, document);
