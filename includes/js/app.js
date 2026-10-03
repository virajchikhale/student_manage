/* Behaviour shared by all signed-in pages: toasts, confirm dialog, declarative API forms/buttons. Needs jQuery, Bootstrap, auth.js. */
(function (w, $) {
    'use strict';

    var App = {};

    /* ---- toasts (also survive one page reload) ---- */
    App.toast = function (msg, type) {
        var ok = type !== 'err';
        var el = $('<div class="toast-x ' + (ok ? 'ok' : 'err') + '"><i class="fas ' +
            (ok ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i><span></span></div>');
        el.find('span').text(msg);
        $('#toasts').append(el);
        setTimeout(function () { el.fadeOut(250, function () { el.remove(); }); }, ok ? 3200 : 5200);
    };
    App.flash = function (msg) {
        try { sessionStorage.setItem('flash', msg); } catch (e) { /* storage blocked: skip */ }
    };
    $(function () {
        try {
            var m = sessionStorage.getItem('flash');
            if (m) { sessionStorage.removeItem('flash'); App.toast(m); }
        } catch (e) { /* ignore */ }
    });

    /* ---- confirm dialog ---- */
    App.confirm = function (text, onYes) {
        $('#confirmText').text(text);
        $('#confirmOk').off('click').on('click', function () {
            $('#confirmModal').modal('hide');
            onYes();
        });
        $('#confirmModal').modal('show');
    };

    /* ---- API ---- */
    App.api = function (endpoint, data) {
        return Auth.api(endpoint, data);
    };

    App.fail = function (m) { App.toast(m, 'err'); };

    /* Run an API call, then reload the page with a flash message (or call done). */
    App.run = function (endpoint, data, okMsg, done) {
        return App.api(endpoint, data).done(function (res) {
            if (done) { done(res); return; }
            App.flash(okMsg || 'Done');
            w.location.reload();
        }).fail(App.fail);
    };

    /* ---- sidebar ---- */
    $(document).on('click', '#burger', function () { $('#sidebar').toggleClass('open'); $('#backdrop').toggleClass('show'); });
    $(document).on('click', '#backdrop', function () { $('#sidebar').removeClass('open'); $('#backdrop').removeClass('show'); });

    /* ---- <form data-api="student" data-action="save"> ----
       Sends every named field. On success the form fires "app:ok" (with the response);
       unless the form has data-reload="false" the page reloads with a toast. */
    $(document).on('submit', 'form[data-api]', function (e) {
        e.preventDefault();
        var $f = $(this), data = {}, btn = $f.find('[type=submit]').first();
        $.each($f.serializeArray(), function (_, p) { data[p.name] = p.value; });
        data.action = $f.data('action');
        btn.prop('disabled', true);
        App.api($f.data('api'), data).done(function (res) {
            $f.trigger('app:ok', [res]);
            if ($f.data('reload') === false || $f.attr('data-reload') === 'false') {
                btn.prop('disabled', false);
                return;
            }
            App.flash($f.data('ok') || 'Saved');
            w.location.reload();
        }).fail(function (m) {
            btn.prop('disabled', false);
            App.fail(m);
        });
    });

    /* ---- <button data-api="course" data-action="delete" data-id="3" data-confirm="Delete it?"> ---- */
    $(document).on('click', 'button[data-api]:not([type=submit]), a[data-api]', function (e) {
        e.preventDefault();
        var $b = $(this), data = $.extend({ action: $b.data('action') }, $b.data('payload') || {});
        if ($b.data('id') !== undefined) { data.id = $b.data('id'); }
        function go() { App.run($b.data('api'), data, $b.data('ok') || 'Done'); }
        if ($b.data('confirm')) { App.confirm($b.data('confirm'), go); } else { go(); }
    });

    /* ---- reset a modal form before it opens in "create" mode ---- */
    App.openForm = function (modalSel, values, title) {
        var $m = $(modalSel), $f = $m.find('form');
        $f[0].reset();
        $f.find('input[type=hidden]:not([name=action])').val('');
        $.each(values || {}, function (k, v) {
            var $i = $f.find('[name="' + k + '"]');
            if ($i.is(':radio')) { $i.filter('[value="' + v + '"]').prop('checked', true); } else { $i.val(v); }
        });
        if (title) { $m.find('.modal-title').text(title); }
        $m.modal('show');
    };

    /* ---- auto-submit filter forms when a select changes ---- */
    $(document).on('change', 'form.auto-filter select, form.auto-filter input[type=date]', function () { this.form.submit(); });

    w.App = App;
})(window, jQuery);
