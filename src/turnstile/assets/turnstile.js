(function () {
    'use strict';
    if (window.userspiceTurnstile) return;
    const nonce = document.currentScript ? document.currentScript.nonce : '';
    let loading;
    const resetters = new WeakMap();

    function api() {
        if (window.turnstile && typeof window.turnstile.render === 'function') {
            return Promise.resolve(window.turnstile);
        }
        if (loading) return loading;
        loading = new Promise(function (resolve, reject) {
            const script = document.createElement('script');
            const timer = window.setTimeout(function () { reject(new Error('Turnstile timed out')); }, 20000);
            window.userspiceTurnstileLoaded = function () {
                window.clearTimeout(timer);
                resolve(window.turnstile);
            };
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=userspiceTurnstileLoaded';
            script.async = true;
            if (nonce) script.nonce = nonce;
            script.onerror = function () {
                window.clearTimeout(timer);
                reject(new Error('Turnstile failed to load'));
            };
            document.head.appendChild(script);
        });
        return loading;
    }

    function render(root) {
        (root || document).querySelectorAll('.us-turnstile').forEach(function (container) {
            if (container.dataset.initialized) return;
            if (container.dataset.target) {
                const submit = document.querySelector(container.dataset.target);
                const targetForm = submit && submit.closest('form');
                if (!targetForm) return;
                // Insert within the form, before the submit control even when it is wrapped in a paragraph.
                let before = submit;
                while (before.parentElement !== targetForm) before = before.parentElement;
                targetForm.insertBefore(container, before);
                container.hidden = false;
            }
            const form = container.closest('form');
            if (!form) return;
            container.dataset.initialized = '1';
            const status = container.querySelector('.us-turnstile-status');
            let solved = false;
            let widgetId;
            function message(text) { status.textContent = text; }
            form.addEventListener('submit', function (event) {
                if (!solved) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    message('Please complete the security check before submitting.');
                }
            }, true);
            api().then(function (turnstile) {
                widgetId = turnstile.render(container.querySelector('.us-turnstile-widget'), {
                    sitekey: container.dataset.sitekey,
                    action: container.dataset.action || undefined,
                    theme: container.dataset.theme,
                    size: container.dataset.size,
                    language: container.dataset.language,
                    'response-field-name': container.dataset.responseField,
                    'refresh-expired': 'auto',
                    callback: function () { solved = true; message('Security check complete.'); },
                    'expired-callback': function () { solved = false; message('Security check expired. Please complete it again.'); },
                    'timeout-callback': function () { solved = false; message('Security check timed out. Please try again.'); },
                    'error-callback': function () { solved = false; message('Security check unavailable. Please refresh the page and try again.'); }
                });
                container.dataset.widgetId = widgetId;
                const resetWidget = function () {
                    solved = false;
                    message('Loading security check…');
                    turnstile.reset(widgetId);
                };
                resetters.set(container, resetWidget);
                form.addEventListener('reset', resetWidget);
            }).catch(function () {
                message('Security check could not load. Please refresh the page and try again.');
            });
        });
    }
    window.userspiceTurnstile = {
        render: render,
        reset: function (form) {
            form.querySelectorAll('.us-turnstile').forEach(function (container) {
                const resetWidget = resetters.get(container);
                if (resetWidget) resetWidget();
            });
        }
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { render(); });
    else render();
}());
