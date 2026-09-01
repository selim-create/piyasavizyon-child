jQuery(document).ready(function ($) {
    var turnstileWidgetId = null;

    function ensureHoneypot($form) {
        if ($form.find('[name="pv_website"]').length) return;
        var $trap = $('<div>', {'aria-hidden': 'true', css: {position: 'absolute', left: '-10000px', width: '1px', height: '1px', overflow: 'hidden'}});
        $trap.append($('<label>').text('Website').append($('<input>', {type: 'text', name: 'pv_website', tabindex: '-1', autocomplete: 'off'})));
        $form.append($trap);
    }

    function ensureTurnstile($form) {
        if (!window.pvAuthSecurity || !pvAuthSecurity.turnstileEnabled || $form.attr('id') !== 'register') return;
        var $container = $form.find('#pv-register-turnstile');
        if (!$container.length) {
            $container = $('<div>', {id: 'pv-register-turnstile', css: {margin: '14px 0'}});
            var $submit = $form.find('input[type="submit"], button[type="submit"]').first();
            if ($submit.length) $container.insertBefore($submit); else $form.append($container);
        }
        if (turnstileWidgetId === null && window.turnstile) {
            turnstileWidgetId = window.turnstile.render($container.get(0), {sitekey: pvAuthSecurity.turnstileSiteKey});
        }
    }

    var $registerForm = $('form#register');
    if ($registerForm.length) {
        ensureHoneypot($registerForm);
        ensureTurnstile($registerForm);
    }

    $('form#login, form#register').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        if ($.fn.valid && !$form.valid()) return false;
        $('#check', this).show().text(ajax_auth_object.loadingmessage);

        var isRegister = $form.attr('id') === 'register';
        var action = isRegister ? 'ajaxregister' : 'ajaxlogin';
        var username = isRegister ? $('#signonname').val() : $('form#login #username').val();
        var password = isRegister ? $('#signonpassword').val() : $('form#login #password').val();
        var email = isRegister ? $('#email').val() : '';
        var security = isRegister ? $('#signonsecurity').val() : $('form#login #security').val();
        var turnstileResponse = '';

        if (isRegister && window.pvAuthSecurity && pvAuthSecurity.turnstileEnabled) {
            turnstileResponse = $form.find('[name="cf-turnstile-response"]').val() || '';
            if (!turnstileResponse) {
                $('#check', this).show().text(pvAuthSecurity.turnstileMessage);
                ensureTurnstile($form);
                return false;
            }
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_auth_object.ajaxurl,
            data: {
                action: action,
                username: username,
                password: password,
                email: email,
                security: security,
                pv_website: isRegister ? ($form.find('[name="pv_website"]').val() || '') : '',
                'cf-turnstile-response': turnstileResponse
            },
            success: function (data) {
                $('#check', $form).text(data.message || 'İşlem tamamlanamadı.');
                if (data.loggedin === true) {
                    window.setTimeout(function () { document.location.href = ajax_auth_object.redirecturl; }, 1000);
                    return;
                }
                if (isRegister && turnstileWidgetId !== null && window.turnstile) window.turnstile.reset(turnstileWidgetId);
            },
            error: function () {
                $('#check', $form).text('İşlem tamamlanamadı. Lütfen tekrar deneyin.');
                if (isRegister && turnstileWidgetId !== null && window.turnstile) window.turnstile.reset(turnstileWidgetId);
            }
        });
        return false;
    });

    if ($('#register').length && $.fn.validate) {
        $('#register').validate({rules: {password2: {equalTo: '#signonpassword'}}});
    } else if ($('#login').length && $.fn.validate) {
        $('#login').validate();
    }
});
