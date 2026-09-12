/* Deterministic Cloudflare stub; tests the plugin's real browser script without network calls. */
(function () {
    'use strict';
    const widgets = [];
    let resetCalls = 0;
    window.turnstile = {
        render: function (element, options) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = options['response-field-name'];
            element.appendChild(input);
            widgets.push({ element: element, options: options, input: input });
            return String(widgets.length - 1);
        },
        reset: function () { resetCalls++; }
    };
    document.addEventListener('DOMContentLoaded', function () {
        window.setTimeout(async function () {
            let assertions = 0;
            function expect(value, message) {
                assertions++;
                if (!value) throw new Error(message);
            }
            function submits(form) {
                return form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            }
            try {
                const contact = document.getElementById('contact');
                const reset = document.getElementById('reset');
                const forgot = document.getElementById('forgot');
                expect(widgets.length === 3, 'three widgets render, missing target stays dormant');
                expect(document.getElementById('reset-widget').parentElement === reset, 'reset widget moves into form rendered after hook');
                expect(document.getElementById('forgot-widget').parentElement === forgot, 'forgot widget moves into earlier form');
                expect(!document.getElementById('reset-widget').hidden, 'moved widget becomes visible');
                expect(reset.firstElementChild.id === 'reset-widget', 'widget precedes wrapped submit control');
                expect(!submits(contact), 'unsolved contact submission is blocked');
                expect(!submits(reset), 'unsolved reset submission is blocked');
                const first = widgets.find(function (widget) { return widget.options.action === 'contact'; });
                first.input.value = 'token';
                first.options.callback('token');
                expect(submits(contact), 'solved widget permits submission');
                expect(new FormData(contact).get('cf-turnstile-response') === 'token', 'token is submitted in its own form');
                expect(!submits(forgot), 'solving one form does not unlock another');
                first.options['expired-callback']();
                expect(!submits(contact), 'expired widget blocks submission');
                first.options.callback('new-token');
                first.options['error-callback']('error');
                expect(!submits(contact), 'error callback blocks submission');
                first.options.callback('new-token');
                first.options['timeout-callback']();
                expect(!submits(contact), 'timeout callback blocks submission');
                first.options.callback('new-token');
                contact.reset();
                expect(resetCalls === 1 && !submits(contact), 'form reset resets challenge and submission gate');
                first.options.callback('new-token');
                window.userspiceTurnstile.reset(contact);
                expect(resetCalls === 2 && !submits(contact), 'AJAX reset resets challenge and submission gate');
                window.userspiceTurnstile.render();
                expect(widgets.length === 3, 'repeat render does not duplicate widgets');
                const dynamic = document.createElement('form');
                dynamic.innerHTML = '<div class="us-turnstile" data-sitekey="test" data-action="dynamic" data-theme="auto" data-size="normal" data-language="auto" data-response-field="dynamic-token"><div class="us-turnstile-widget"></div><p class="us-turnstile-status"></p></div>';
                document.body.appendChild(dynamic);
                window.userspiceTurnstile.render(dynamic);
                await Promise.resolve();
                expect(widgets.length === 4, 'dynamic form renders on demand');
                expect(widgets[3].input.name === 'dynamic-token', 'custom response field reaches widget');
                expect(!submits(dynamic), 'dynamic form is protected');
                document.getElementById('test-results').textContent = 'PASS: ' + assertions + ' browser assertions.';
                document.body.dataset.testResult = 'passed';
            } catch (error) {
                document.getElementById('test-results').textContent = 'FAIL: ' + error.message;
                document.body.dataset.testResult = 'failed';
            }
        }, 0);
    });
}());
