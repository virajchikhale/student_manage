/* Shared client for login, registration and password reset. Needs jQuery and head_meta() on the page. */
(function (w, $) {
    'use strict';

    var token = $('meta[name="csrf-token"]').attr('content');

    function api(name, data) {
        var d = $.Deferred();
        $.ajax({
            type: 'POST',
            url: (w.APP_ROOT || '') + '/api/' + name + '.php',
            data: data || {},
            dataType: 'json',
            headers: { 'X-CSRF-Token': token }
        }).done(function (res) {
            d.resolve(res);
        }).fail(function (xhr) {
            var m = xhr.responseJSON && xhr.responseJSON.error;
            d.reject(m || 'Network error, please try again.');
        });
        return d.promise();
    }

    function showError(msg) {
        var box = $('#alert');
        if (box.length) {
            box.text(msg).show();
        } else {
            alert(msg);
        }
    }

    /* Inputs referenced by inline onchange="" attributes in the pages */
    w.selectnone = function () {};
    w.disable = function () { $('#alert').hide(); };
    w.passvalid = function () {
        if ($('#password').val().length < 8) {
            alert('Password must be at least 8 characters');
            $('#password').val('').focus();
        }
    };
    w.passcon = function () {
        if ($('#password').val() !== $('#cpassword').val()) {
            alert('Passwords do not match, please try again');
            $('#cpassword').val('').focus();
        }
    };

    function sendOtp(role, purpose, email) {
        return api('otp', { role: role, purpose: purpose, email: email }).done(function (res) {
            var msg = 'We have sent an OTP to ' + email;
            if (res.demo_otp) {
                msg += '\n\nDEMO MODE: your OTP is ' + res.demo_otp;
            }
            alert(msg);
            $('#otp, #otpin').first().focus();
        }).fail(function (m) {
            alert(m);
        });
    }

    var Auth = { api: api, sendOtp: sendOtp };

    /* ---- Login pages ---- */
    Auth.initLogin = function (role) {
        $('#alert').hide();
        function go() {
            $('#alert').hide();
            api('login', { role: role, email: $('#email').val().trim(), password: $('#password').val() })
                .done(function (res) { w.location.href = res.redirect; })
                .fail(function (m) {
                    showError(m);
                    $('#password').val('').focus();
                });
        }
        w.response = go;
        $('form.validate-form, .login-form form').off('submit').on('submit', function (e) {
            e.preventDefault();
            go();
        });
    };

    /* ---- Registration pages ---- */
    Auth.initRegister = function (role, opts) {
        opts = opts || {};
        var lastOtpEmail = '';

        w.checkmobno = function () {
            var v = $('#phoneno').val().trim();
            if (!v) { return; }
            api('check', { field: 'phone', value: v }).done(function (r) {
                if (r.taken) {
                    alert('This phone number already exists in the system');
                    $('#phoneno').val('').focus();
                }
            }).fail(function (m) { alert(m); $('#phoneno').val('').focus(); });
        };

        w.emailvalid = function () {
            var email = $('#email').val().trim();
            if (!email || email === lastOtpEmail) { return; }
            api('check', { field: 'email', value: email }).done(function (r) {
                if (r.taken) {
                    alert('This email already exists in the system');
                    $('#email').val('').focus();
                } else {
                    lastOtpEmail = email;
                    sendOtp(role, 'reg', email);
                }
            }).fail(function (m) { alert(m); $('#email').val('').focus(); });
        };

        w.response = function () {
            var data = {
                role: role,
                fname: $('#fname, #first_name').first().val(), lname: $('#lname, #last_name').first().val(),
                email: $('#email').val().trim(), phoneno: $('#phoneno').val().trim(),
                password: $('#password').val(), otp: $('#otp').val().trim()
            };
            if ($('#password').val() !== $('#cpassword').val()) {
                alert('Passwords do not match, please try again');
                return;
            }
            (opts.fields || []).forEach(function (f) { data[f] = $('#' + f).val(); });
            var btn = $('#submit, #test').prop('disabled', true);
            api('register', data).done(function (res) {
                alert('Signed up successfully');
                w.location.href = res.redirect;
            }).fail(function (m) {
                alert(m);
                btn.prop('disabled', false);
            });
        };
    };

    /* ---- Forgot password page ---- */
    Auth.initForgot = function (role) {
        var email = '';
        $('#send_otp').on('click', function () {
            email = $('#email').val().trim();
            sendOtp(role, 'forgot', email).done(function () {
                $('#otp_box, #pass_box').show();
            });
        });
        $('#reset').on('click', function () {
            if ($('#password').val() !== $('#cpassword').val()) { return alert('Passwords do not match'); }
            api('reset_password', {
                role: role, email: email, otp: $('#otpin').val().trim(), password: $('#password').val()
            }).done(function (res) {
                alert('Password updated successfully');
                w.location.href = res.redirect;
            }).fail(function (m) { alert(m); });
        });
    };

    /* ---- Admin dashboard actions ---- */
    Auth.admin = function (action, data, done) {
        api('admin', $.extend({ action: action }, data)).done(done || function () { w.location.reload(); })
            .fail(function (m) { alert(m); });
    };

    w.Auth = Auth;
})(window, jQuery);
