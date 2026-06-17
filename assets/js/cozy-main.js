(function ($) {
    'use strict';

    /* ---------- CART DRAWER ---------- */
    function openCart(e) {
        if (e) e.preventDefault();
        document.getElementById('cart-drawer').classList.remove('translate-x-full');
        document.getElementById('cart-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        document.getElementById('cart-drawer').classList.add('translate-x-full');
        document.getElementById('cart-overlay').classList.add('hidden');
        document.body.style.overflow = '';
    }

    window.openCart  = openCart;
    window.closeCart = closeCart;

    /* Open drawer after AJAX add-to-cart */
    $(document.body).on('added_to_cart', function () {
        openCart();
    });

    /* Sync badge visibility after WC fragment refresh */
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
        var badge = document.getElementById('cart-badge');
        if (!badge) return;
        var count = parseInt(badge.textContent.trim(), 10) || 0;
        badge.classList.toggle('hidden', count === 0);
    });

    /* ---------- MOBILE MENU ---------- */
    window.toggleMobileMenu = function () {
        var menu = document.getElementById('mobile-menu');
        if (menu) menu.classList.toggle('hidden');
    };

    /* ---------- STICKY HEADER ---------- */
    (function () {
        var header = document.getElementById('masthead');
        if (!header) return;
        window.addEventListener('scroll', function () {
            header.classList.toggle('cozy-scrolled', window.scrollY > 60);
        }, { passive: true });
    })();

    /* ---------- NEWSLETTER — Mailchimp via WP AJAX ---------- */
    window.handleNewsletterSubmit = function (e) {
        e.preventDefault();
        var form    = document.getElementById('newsletter-form');
        var success = document.getElementById('newsletter-success');
        var btn     = form ? form.querySelector('button[type="submit"]') : null;
        var input   = form ? form.querySelector('input[type="email"]') : null;

        if (!form || !input) return;

        var originalText = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'Enviando…'; }

        $.post(cozyAjax.url, {
            action: 'cozy_newsletter_subscribe',
            nonce:  cozyAjax.nonce,
            email:  input.value.trim()
        })
        .done(function (res) {
            if (res.success) {
                if (form)    form.classList.add('hidden');
                if (success) success.classList.remove('hidden');
            } else {
                if (btn) { btn.disabled = false; btn.textContent = originalText; }
                alert(res.data && res.data.message ? res.data.message : 'Ha ocurrido un error. Inténtalo de nuevo.');
            }
        })
        .fail(function () {
            if (btn) { btn.disabled = false; btn.textContent = originalText; }
            alert('Error de conexión. Inténtalo de nuevo.');
        });
    };

})(jQuery);
