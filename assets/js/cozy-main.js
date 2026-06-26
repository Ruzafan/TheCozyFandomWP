/* ─── Global UI functions ──────────────────────────────────────────
   Defined outside any IIFE so inline onclick handlers can always
   reach them, even before DOMContentLoaded fires.
─────────────────────────────────────────────────────────────────── */

/* ---------- CART DRAWER ---------- */
window.openCart = function (e) {
    if (e) e.preventDefault();
    document.getElementById('cart-drawer').classList.remove('translate-x-full');
    document.getElementById('cart-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};
window.closeCart = function () {
    document.getElementById('cart-drawer').classList.add('translate-x-full');
    document.getElementById('cart-overlay').classList.add('hidden');
    document.body.style.overflow = '';
};

/* ---------- FAVORITES DRAWER ---------- */
window.openFavorites = function () {
    var drawer  = document.getElementById('fav-drawer');
    var overlay = document.getElementById('fav-overlay');
    if (drawer)  drawer.classList.remove('translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};
window.closeFavorites = function () {
    var drawer  = document.getElementById('fav-drawer');
    var overlay = document.getElementById('fav-overlay');
    if (drawer)  drawer.classList.add('translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
};

/* ---------- LOGIN MODAL ---------- */
window.openLoginModal = function () {
    var modal = document.getElementById('login-modal-overlay');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
};
window.closeLoginModal = function () {
    var modal = document.getElementById('login-modal-overlay');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
};

/* ---------- MOBILE MENU ---------- */
window.toggleMobileMenu = function () {
    var sidebar = document.getElementById('cozy-nav-sidebar');
    var overlay = document.getElementById('mobile-menu-overlay');
    var btn     = document.querySelector('.cozy-hdr-hamburger');
    if (!sidebar) return;
    var isOpen = sidebar.classList.toggle('is-open');
    if (overlay) overlay.classList.toggle('is-open', isOpen);
    if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.style.overflow = isOpen ? 'hidden' : '';
};
window.closeMobileMenu = function () {
    var sidebar = document.getElementById('cozy-nav-sidebar');
    var overlay = document.getElementById('mobile-menu-overlay');
    var btn     = document.querySelector('.cozy-hdr-hamburger');
    if (sidebar) sidebar.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-open');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
};

/* ---------- NAV DROPDOWN ---------- */
window.cozyToggleDropdown = function (btn) {
    var item = btn.closest('.cozy-nav-item');
    if (!item) return;
    var isOpen = item.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.querySelectorAll('.cozy-nav-item.is-open').forEach(function (other) {
        if (other !== item) {
            other.classList.remove('is-open');
            var otherBtn = other.querySelector('[aria-expanded]');
            if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
        }
    });
};

/* ---------- SIDEBAR CATEGORY TOGGLE ---------- */
window.cozyCatToggle = function (btn) {
    var item = btn.closest('.cozy-cat-filter-item');
    if (!item) return;
    var list   = item.querySelector('.cozy-cat-filter-children');
    var isOpen = list.classList.toggle('is-open');
    btn.classList.toggle('is-open', isOpen);
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
};

/* ---------- SHOP FILTER DRAWER (mobile) ---------- */
window.openFilters = function () {
    var sidebar   = document.getElementById('cozy-shop-filters');
    var overlay   = document.getElementById('cozy-filter-overlay');
    var toggleBtn = document.querySelector('[aria-controls="cozy-shop-filters"]');
    if (sidebar)   sidebar.classList.add('is-open');
    if (overlay)   overlay.classList.remove('hidden');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
};
window.closeFilters = function () {
    var sidebar   = document.getElementById('cozy-shop-filters');
    var overlay   = document.getElementById('cozy-filter-overlay');
    var toggleBtn = document.querySelector('[aria-controls="cozy-shop-filters"]');
    if (sidebar)   sidebar.classList.remove('is-open');
    if (overlay)   overlay.classList.add('hidden');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
};

/* ---------- TOGGLE FAVORITE ---------- */
window.toggleFavorite = function (productId) {
    if (typeof cozyAjax === 'undefined') return;

    if (!cozyAjax.isLoggedIn) {
        window.openLoginModal();
        return;
    }

    var body = new FormData();
    body.append('action', 'cozy_toggle_favorite');
    body.append('nonce', cozyAjax.favNonce);
    body.append('product_id', productId);

    fetch(cozyAjax.url, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res || !res.success) return;
            var data = res.data;
            cozyUpdateFavBtns(productId, data.is_favorited);
            cozyUpdateFavBadge(data.count);
            if (data.is_favorited && data.item_html) {
                cozyAddFavItem(data.item_html);
            } else {
                cozyRemoveFavItem(productId);
            }
        });
};

/* ─── Global keyboard / click handlers ─────────────────────────── */
document.addEventListener('click', function (e) {
    if (!e.target.closest('.cozy-nav-item')) {
        document.querySelectorAll('.cozy-nav-item.is-open').forEach(function (item) {
            item.classList.remove('is-open');
            var b = item.querySelector('[aria-expanded]');
            if (b) b.setAttribute('aria-expanded', 'false');
        });
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        window.closeFilters();
        window.closeFavorites();
        window.closeLoginModal();
        window.closeMobileMenu();
    }
});

/* ─── Sticky header ─────────────────────────────────────────────── */
(function () {
    var header = document.getElementById('masthead');
    if (!header) return;
    window.addEventListener('scroll', function () {
        header.classList.toggle('cozy-scrolled', window.scrollY > 60);
    }, { passive: true });
})();

/* ─── Favorites helpers (pure DOM) ──────────────────────────────── */
function cozyUpdateFavBtns(productId, isFav) {
    document.querySelectorAll('.cozy-fav-btn[data-product-id="' + productId + '"]').forEach(function (btn) {
        btn.classList.toggle('is-favorited', isFav);
        var label = btn.querySelector('.cozy-fav-label');
        if (label) label.textContent = isFav ? 'Guardado' : 'Guardar';
    });
}
function cozyUpdateFavBadge(count) {
    var badge = document.getElementById('fav-badge');
    if (!badge) return;
    badge.textContent = count;
    badge.classList.toggle('hidden', count === 0);
}
function cozyAddFavItem(html) {
    var container = document.getElementById('fav-items');
    if (!container) return;
    var empty = document.getElementById('fav-empty');
    if (empty) empty.remove();
    container.insertAdjacentHTML('beforeend', html);
}
function cozyRemoveFavItem(productId) {
    var item = document.querySelector('#fav-items .cozy-fav-item[data-product-id="' + productId + '"]');
    if (item) item.remove();
    var container = document.getElementById('fav-items');
    if (container && !container.querySelector('.cozy-fav-item')) {
        container.innerHTML = '<div id="fav-empty" class="text-center py-12 space-y-4">'
            + '<svg class="mx-auto" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color:rgba(58,49,40,0.2)"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>'
            + '<p style="font-size:0.875rem;color:rgba(58,49,40,0.6)">Aún no tienes favoritos guardados.</p>'
            + '<button onclick="closeFavorites()" style="font-size:0.75rem;font-weight:700;color:#88C4B5">¡Descubre la tienda!</button>'
            + '</div>';
    }
}

/* ─── WooCommerce event listeners (vanilla JS, no jQuery) ───────── */
(function () {
    'use strict';

    /* Open cart drawer after AJAX add-to-cart.
       WooCommerce triggers 'added_to_cart' as a jQuery event on document.body.
       Since WC itself loads jQuery, we can listen for it via the jQuery bridge
       if jQuery is available, or fall back to a MutationObserver on cart-badge. */
    if (typeof jQuery !== 'undefined') {
        jQuery(document.body).on('added_to_cart', function () {
            window.openCart();
        });
        jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
            var badge = document.getElementById('cart-badge');
            if (!badge) return;
            var count = parseInt(badge.textContent.trim(), 10) || 0;
            badge.classList.toggle('hidden', count === 0);
        });
    }

    /* Mark already-favorited products on page load */
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof cozyAjax !== 'undefined' && cozyAjax.favorites && cozyAjax.favorites.length) {
            cozyAjax.favorites.forEach(function (id) {
                cozyUpdateFavBtns(id, true);
            });
        }
    });

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

        var body = new FormData();
        body.append('action', 'cozy_newsletter_subscribe');
        body.append('nonce', cozyAjax.nonce);
        body.append('email', input.value.trim());

        fetch(cozyAjax.url, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (form)    form.classList.add('hidden');
                    if (success) success.classList.remove('hidden');
                } else {
                    if (btn) { btn.disabled = false; btn.textContent = originalText; }
                    alert(res.data && res.data.message ? res.data.message : 'Ha ocurrido un error. Inténtalo de nuevo.');
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = originalText; }
                alert('Error de conexión. Inténtalo de nuevo.');
            });
    };

    /* ---------- LIVE SEARCH SUGGESTIONS ---------- */
    var searchInput = document.querySelector('.cozy-hdr-search__input');
    var suggestionsContainer = document.getElementById('cozy-search-suggestions');
    var searchTimeout = null;
    var selectedIndex = -1;

    if (searchInput && suggestionsContainer) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            var term = searchInput.value.trim();

            if (term.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.classList.add('hidden');
                selectedIndex = -1;
                return;
            }

            searchTimeout = setTimeout(function () {
                suggestionsContainer.innerHTML = '<div class="cozy-suggestion-loading">Buscando productos...</div>';
                suggestionsContainer.classList.remove('hidden');
                selectedIndex = -1;

                fetch(cozyAjax.url + '?action=cozy_ajax_search&term=' + encodeURIComponent(term))
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.success && res.data) {
                            renderSuggestions(res.data);
                        } else {
                            renderSuggestions([]);
                        }
                    })
                    .catch(function () {
                        suggestionsContainer.innerHTML = '<div class="cozy-suggestion-empty">Error de conexión.</div>';
                    });
            }, 250);
        });

        searchInput.addEventListener('keydown', function (e) {
            var items = suggestionsContainer.querySelectorAll('.cozy-suggestion-item');
            if (suggestionsContainer.classList.contains('hidden') || !items.length) {
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= items.length) {
                    selectedIndex = 0;
                }
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) {
                    selectedIndex = items.length - 1;
                }
                updateSelection(items);
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                suggestionsContainer.classList.add('hidden');
                searchInput.blur();
            }
        });

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.classList.add('hidden');
            }
        });

        // Re-open suggestions if input focused and has text
        searchInput.addEventListener('focus', function () {
            var term = searchInput.value.trim();
            if (term.length >= 2 && suggestionsContainer.children.length > 0) {
                suggestionsContainer.classList.remove('hidden');
            }
        });
    }

    function renderSuggestions(products) {
        if (!products || !products.length) {
            suggestionsContainer.innerHTML = '<div class="cozy-suggestion-empty">No se encontraron productos. 🌿</div>';
            return;
        }

        var html = '';
        products.forEach(function (p) {
            html += '<a href="' + p.url + '" class="cozy-suggestion-item">' +
                        '<img src="' + p.image + '" class="cozy-suggestion-thumb" alt="' + p.title + '">' +
                        '<div class="cozy-suggestion-info">' +
                            '<h4 class="cozy-suggestion-title">' + p.title + '</h4>' +
                            '<span class="cozy-suggestion-price">' + p.price + '</span>' +
                        '</div>' +
                    '</a>';
        });
        suggestionsContainer.innerHTML = html;
        selectedIndex = -1;
    }

    function updateSelection(items) {
        items.forEach(function (item, idx) {
            if (idx === selectedIndex) {
                item.classList.add('is-selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('is-selected');
            }
        });
    }

})();

