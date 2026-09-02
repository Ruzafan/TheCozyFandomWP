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

    if (typeof gtag === 'function') {
        var badge = document.getElementById('cart-badge');
        gtag('event', 'view_cart', {
            currency: 'EUR',
            value: badge ? (parseFloat(badge.getAttribute('data-cart-value')) || 0) : 0
        });
    }
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

/* ---------- COOKIE CONSENT BANNER ---------- */
function cozySetCookie(name, value, days) {
    var expires = new Date(Date.now() + days * 864e5).toUTCString();
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = name + '=' + value + '; expires=' + expires + '; path=/; SameSite=Lax' + secure;
}
window.cozyAcceptConsent = function () {
    cozySetCookie('cozy_consent', 'granted', 180);
    if (typeof window.cozyLoadGA === 'function') window.cozyLoadGA();
    if (typeof window.cozyLoadGTM === 'function') window.cozyLoadGTM();
    var banner = document.getElementById('cozy-consent-banner');
    if (banner) banner.remove();
};
window.cozyRejectConsent = function () {
    cozySetCookie('cozy_consent', 'denied', 180);
    var banner = document.getElementById('cozy-consent-banner');
    if (banner) banner.remove();
};
window.cozyOpenCookieSettings = function () {
    var banner = document.getElementById('cozy-consent-banner');
    if (banner) {
        banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var prevTransition = banner.style.transition;
        var prevShadow = banner.style.boxShadow;
        banner.style.transition = 'box-shadow 0.2s ease';
        banner.style.boxShadow = '0 0 0 3px #6ee7b7';
        setTimeout(function () {
            banner.style.boxShadow = prevShadow;
            banner.style.transition = prevTransition;
        }, 1200);
        return;
    }
    document.cookie = 'cozy_consent=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax';
    window.location.reload();
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

/* ---------- SHOP FILTER DRAWER ---------- */
window.openFilters = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    var sidebar   = document.getElementById('cozy-shop-filters');
    var overlay   = document.getElementById('cozy-filters-overlay');
    var toggleBtn = document.querySelector('[aria-controls="cozy-shop-filters"]');
    if (!sidebar) return;
    
    sidebar.classList.remove('-translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
};
window.closeFilters = function () {
    var sidebar   = document.getElementById('cozy-shop-filters');
    var overlay   = document.getElementById('cozy-filters-overlay');
    var toggleBtn = document.querySelector('[aria-controls="cozy-shop-filters"]');
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
};

/* ---------- PRODUCT LIGHTBOX GALLERY ---------- */
window.openCozyLightbox = function (index) {
    var gallery = document.querySelector('.cozy-gallery');
    if (!gallery) return;
    
    var images = [];
    try {
        images = JSON.parse(gallery.getAttribute('data-images') || '[]');
    } catch(e) {
        console.error(e);
        return;
    }
    
    if (!images.length) return;
    
    // Create the lightbox DOM elements if not exists
    var lightbox = document.getElementById('cozy-lightbox');
    if (lightbox) {
        if (lightbox.parentNode) {
            lightbox.parentNode.removeChild(lightbox);
        }
    }
    
    lightbox = document.createElement('div');
    lightbox.id = 'cozy-lightbox';
    lightbox.className = 'fixed inset-0 z-[2000] bg-cozy-coffee/95 backdrop-blur-md flex flex-col justify-between p-4 transition-opacity duration-300 opacity-0';
    
    lightbox.innerHTML = 
        '<div class="flex items-center justify-between text-white/70 px-4 py-2 relative z-10">' +
            '<span class="text-xs font-bold font-mono" id="cozy-lightbox-counter">1 / 1</span>' +
            '<button type="button" id="cozy-lightbox-close" class="text-white/70 hover:text-white transition-colors p-2 text-2xl border-0 bg-transparent cursor-pointer" aria-label="Cerrar">&times;</button>' +
        '</div>' +
        '<div class="flex-grow flex items-center justify-center relative overflow-hidden my-4">' +
            '<button id="cozy-lightbox-prev" class="absolute left-4 z-10 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-all cursor-pointer border-0" aria-label="Anterior">' +
                '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>' +
            '</button>' +
            '<div class="w-full h-full flex items-center justify-center max-w-5xl px-12">' +
                '<img id="cozy-lightbox-img" class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl transition-all duration-300 opacity-0 transform scale-95" src="" alt="Vista ampliada">' +
            '</div>' +
            '<button id="cozy-lightbox-next" class="absolute right-4 z-10 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-all cursor-pointer border-0" aria-label="Siguiente">' +
                '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>' +
            '</button>' +
        '</div>' +
        '<div class="h-16 flex items-center justify-center gap-2 overflow-x-auto py-2 px-4 relative z-10" id="cozy-lightbox-thumbs"></div>';
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    // Add opacity transition
    setTimeout(function() {
        lightbox.classList.remove('opacity-0');
    }, 10);
    
    var currentIdx = index || 0;
    
    function updateLightbox() {
        var img = document.getElementById('cozy-lightbox-img');
        var counter = document.getElementById('cozy-lightbox-counter');
        if (!img || !counter) return;
        
        img.classList.add('opacity-0');
        img.classList.add('scale-95');
        
        setTimeout(function() {
            img.src = images[currentIdx];
            img.onload = function() {
                img.classList.remove('opacity-0');
                img.classList.remove('scale-95');
            };
            counter.textContent = (currentIdx + 1) + ' / ' + images.length;
        }, 150);
        
        // Update active thumbnail
        var lightboxThumbs = document.querySelectorAll('.cozy-lightbox-thumb');
        lightboxThumbs.forEach(function(t, idx) {
            if (idx === currentIdx) {
                t.style.opacity = '1';
                t.style.borderColor = '#88C4B5';
            } else {
                t.style.opacity = '0.5';
                t.style.borderColor = 'rgba(255,255,255,0.1)';
            }
        });
    }
    
    // Generate thumbnails inside the lightbox
    var thumbsContainer = document.getElementById('cozy-lightbox-thumbs');
    if (thumbsContainer && images.length > 1) {
        images.forEach(function(src, idx) {
            var thumb = document.createElement('button');
            thumb.className = 'cozy-lightbox-thumb w-10 h-10 rounded-lg overflow-hidden border-2 transition-all p-0 flex-shrink-0 cursor-pointer bg-white';
            thumb.style.borderStyle = 'solid';
            thumb.innerHTML = '<img class="w-full h-full object-cover" src="' + src + '">';
            thumb.onclick = function() {
                currentIdx = idx;
                updateLightbox();
            };
            thumbsContainer.appendChild(thumb);
        });
    }
    
    // Event listener for close button
    var closeBtn = document.getElementById('cozy-lightbox-close');
    if (closeBtn) closeBtn.onclick = function(e) {
        e.stopPropagation();
        closeCozyLightbox();
    };

    // Event listeners for prev / next
    var prevBtn = document.getElementById('cozy-lightbox-prev');
    var nextBtn = document.getElementById('cozy-lightbox-next');
    
    if (images.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    } else {
        if (prevBtn) prevBtn.onclick = function(e) {
            e.stopPropagation();
            currentIdx = (currentIdx - 1 + images.length) % images.length;
            updateLightbox();
        };
        if (nextBtn) nextBtn.onclick = function(e) {
            e.stopPropagation();
            currentIdx = (currentIdx + 1) % images.length;
            updateLightbox();
        };
    }
    
    updateLightbox();
    
    // Close on overlay click
    lightbox.onclick = function(e) {
        if (e.target.id === 'cozy-lightbox' || e.target.closest('.w-full.h-full.flex.items-center')) {
            closeCozyLightbox();
        }
    };
    
    // Keydown listeners for keyboard navigation
    window.cozyLightboxKeydown = function(e) {
        if (e.key === 'Escape') {
            closeCozyLightbox();
        } else if (e.key === 'ArrowLeft' && images.length > 1) {
            currentIdx = (currentIdx - 1 + images.length) % images.length;
            updateLightbox();
        } else if (e.key === 'ArrowRight' && images.length > 1) {
            currentIdx = (currentIdx + 1) % images.length;
            updateLightbox();
        }
    };
    document.addEventListener('keydown', window.cozyLightboxKeydown);
};

window.closeCozyLightbox = function () {
    var lightbox = document.getElementById('cozy-lightbox');
    if (!lightbox) return;
    
    lightbox.classList.add('opacity-0');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', window.cozyLightboxKeydown);
    
    setTimeout(function() {
        if (lightbox.parentNode) {
            lightbox.parentNode.removeChild(lightbox);
        }
    }, 300);
};

/* ---------- GUEST WISHLIST (localStorage — no login required) ---------- */
function cozyGetGuestWishlist() {
    try {
        return JSON.parse(localStorage.getItem('cozy_guest_wishlist') || '[]');
    } catch (e) {
        return [];
    }
}
function cozySaveGuestWishlist(ids) {
    localStorage.setItem('cozy_guest_wishlist', JSON.stringify(ids));
}
function cozyFetchWishlistItemsHtml(ids, callback) {
    if (!ids.length) { callback([]); return; }
    var body = new FormData();
    body.append('action', 'cozy_get_wishlist_items');
    ids.forEach(function (id) { body.append('ids[]', id); });
    fetch(cozyAjax.url, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) { callback(res && res.success ? res.data.items : []); })
        .catch(function () { callback([]); });
}

/* ---------- TOGGLE FAVORITE ---------- */
function cozyTrackAddToWishlist(productId, productName, productPrice) {
    if (typeof gtag !== 'function') return;
    gtag('event', 'add_to_wishlist', {
        currency: 'EUR',
        value: productPrice || 0,
        items: [{
            item_id:   String(productId),
            item_name: productName || '',
            price:     productPrice || 0,
            quantity:  1
        }]
    });
}

window.toggleFavorite = function (productId, productName, productPrice) {
    if (typeof cozyAjax === 'undefined') return;

    if (!cozyAjax.isLoggedIn) {
        var guestIds = cozyGetGuestWishlist();
        var idx = guestIds.indexOf(productId);
        var nowFavorited;
        if (idx > -1) {
            guestIds.splice(idx, 1);
            nowFavorited = false;
        } else {
            guestIds.push(productId);
            nowFavorited = true;
        }
        cozySaveGuestWishlist(guestIds);
        cozyUpdateFavBtns(productId, nowFavorited);
        cozyUpdateFavBadge(guestIds.length);

        if (nowFavorited) {
            cozyTrackAddToWishlist(productId, productName, productPrice);
            cozyFetchWishlistItemsHtml([productId], function (items) {
                items.forEach(function (item) { cozyAddFavItem(item.html); });
            });
        } else {
            cozyRemoveFavItem(productId);
        }
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
                cozyTrackAddToWishlist(productId, productName, productPrice);
                cozyAddFavItem(data.item_html);
            } else {
                cozyRemoveFavItem(productId);
            }
        });
};

/* ---------- PRODUCT GALLERY SLIDER (main image strip, not the lightbox) ---------- */
function cozyGalleryUpdate(gallery, index) {
    var track  = gallery.querySelector('.cozy-gallery__track');
    var slides = track ? track.querySelectorAll('.cozy-gallery__slide') : [];
    var thumbs = gallery.querySelectorAll('.cozy-gallery__thumb');
    var total  = slides.length;
    if (!total) return;

    if (index < 0) index = total - 1;
    if (index >= total) index = 0;

    gallery.setAttribute('data-current', index);

    if (track) track.style.transform = 'translateX(-' + (index * 100) + '%)';

    slides.forEach(function (s, i) {
        s.setAttribute('aria-hidden', i !== index ? 'true' : 'false');
    });
    thumbs.forEach(function (t, i) {
        t.classList.toggle('is-active', i === index);
        t.setAttribute('aria-selected', i === index ? 'true' : 'false');
    });

    if (thumbs[index]) {
        thumbs[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
}
function cozyGalleryCurrent(gallery) {
    return parseInt(gallery.getAttribute('data-current') || '0', 10);
}

/* ---------- PAGINATION — scroll to top on page change ---------- */
document.addEventListener('click', function (e) {
    if (e.target.closest('.page-numbers:not(.dots):not(.current)')) {
        try { sessionStorage.setItem('cozy_scroll_top', '1'); } catch (err) {}
    }
});
if (window.location.search.indexOf('paged=') !== -1 || /\/page\/\d+\//.test(window.location.pathname)) {
    try {
        if (sessionStorage.getItem('cozy_scroll_top')) {
            sessionStorage.removeItem('cozy_scroll_top');
            window.scrollTo(0, 0);
        }
    } catch (err) {}
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cozy-gallery__track').forEach(function (track) {
        var gallery = track.closest('.cozy-gallery');
        if (!gallery) return;
        var startX = 0;
        track.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
        }, { passive: true });
        track.addEventListener('touchend', function (e) {
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                cozyGalleryUpdate(gallery, cozyGalleryCurrent(gallery) + (diff > 0 ? 1 : -1));
            }
        }, { passive: true });
    });
});

document.addEventListener('keydown', function (e) {
    var gallery = document.querySelector('.cozy-gallery');
    if (!gallery || !gallery.matches(':focus-within')) return;
    if (e.key === 'ArrowLeft')  { e.preventDefault(); cozyGalleryUpdate(gallery, cozyGalleryCurrent(gallery) - 1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); cozyGalleryUpdate(gallery, cozyGalleryCurrent(gallery) + 1); }
});

/* ─── Global keyboard / click handlers ─────────────────────────── */

/* Central dispatcher for data-action="..." — replaces inline onclick=""
   attributes, which are blocked by this site's Content-Security-Policy
   (script-src-attr 'none'). Keep every clickable control's behaviour wired
   here instead of via markup attributes. */
document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-action]');
    if (!el) return;

    switch (el.getAttribute('data-action')) {
        case 'open-favorites':
            window.openFavorites();
            break;
        case 'close-favorites':
            window.closeFavorites();
            break;
        case 'open-cart':
            window.openCart(e);
            break;
        case 'close-cart':
            window.closeCart();
            break;
        case 'toggle-mobile-menu':
            window.toggleMobileMenu();
            break;
        case 'close-mobile-menu':
            window.closeMobileMenu();
            break;
        case 'close-mobile-menu-open-favorites':
            window.closeMobileMenu();
            setTimeout(window.openFavorites, 320);
            break;
        case 'close-mobile-menu-open-cart':
            window.closeMobileMenu();
            setTimeout(window.openCart, 320);
            break;
        case 'toggle-dropdown':
            window.cozyToggleDropdown(el);
            break;
        case 'close-login-modal':
            window.closeLoginModal();
            break;
        case 'open-filters':
            window.openFilters(e);
            break;
        case 'close-filters':
            window.closeFilters();
            break;
        case 'toggle-favorite':
            window.toggleFavorite(
                parseInt(el.getAttribute('data-product-id'), 10),
                el.getAttribute('data-product-name') || '',
                parseFloat(el.getAttribute('data-product-price')) || 0
            );
            break;
        case 'gallery-open':
            window.openCozyLightbox(parseInt(el.getAttribute('data-index'), 10));
            break;
        case 'gallery-prev': {
            var galleryPrev = el.closest('.cozy-gallery');
            if (galleryPrev) cozyGalleryUpdate(galleryPrev, cozyGalleryCurrent(galleryPrev) - 1);
            break;
        }
        case 'gallery-next': {
            var galleryNext = el.closest('.cozy-gallery');
            if (galleryNext) cozyGalleryUpdate(galleryNext, cozyGalleryCurrent(galleryNext) + 1);
            break;
        }
        case 'gallery-thumb': {
            var galleryThumb = el.closest('.cozy-gallery');
            if (galleryThumb) cozyGalleryUpdate(galleryThumb, parseInt(el.getAttribute('data-index'), 10));
            break;
        }
        case 'consent-accept':
            window.cozyAcceptConsent();
            break;
        case 'consent-reject':
            window.cozyRejectConsent();
            break;
        case 'open-cookie-settings':
            window.cozyOpenCookieSettings();
            break;
        case 'toggle-ultra-cozy':
            window.toggleUltraCozy();
            break;
        case 'set-weather':
            if (window.cozySetWeather) window.cozySetWeather(el.getAttribute('data-weather'));
            break;
        case 'close-weather-guide':
            if (window.closeWeatherGuide) window.closeWeatherGuide();
            break;
        case 'open-tea-oracle':
            if (window.cozyOpenOracle) window.cozyOpenOracle();
            break;
        case 'refresh-oracle':
            if (window.cozyRefreshOracle) window.cozyRefreshOracle();
            break;
        case 'close-oracle':
            if (window.cozyCloseOracle) window.cozyCloseOracle();
            break;
        case 'begin-checkout':
            if (typeof gtag === 'function') {
                gtag('event', 'begin_checkout', {
                    currency: 'EUR',
                    value: parseFloat(el.getAttribute('data-value')) || 0
                });
            }
            break;
    }
});

/* Backdrop-click-to-close: only fires when the click lands directly on the
   element carrying data-close-on-self (i.e. not bubbled from its content). */
document.addEventListener('click', function (e) {
    if (!e.target.hasAttribute || !e.target.hasAttribute('data-close-on-self')) return;
    switch (e.target.getAttribute('data-close-on-self')) {
        case 'close-login-modal':
            window.closeLoginModal();
            break;
        case 'close-oracle':
            if (window.cozyCloseOracle) window.cozyCloseOracle();
            break;
    }
});

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
            + '<button type="button" data-action="close-favorites" style="font-size:0.75rem;font-weight:700;color:#88C4B5">¡Descubre la tienda!</button>'
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
        jQuery(document.body).on('added_to_cart', function (e, fragments, cart_hash, $button) {
            window.openCart();

            if (typeof gtag === 'function' && $button && $button.length) {
                gtag('event', 'add_to_cart', {
                    currency: 'EUR',
                    value: parseFloat($button.data('product-price')) || 0,
                    items: [{
                        item_id:   String($button.data('product_id') || ''),
                        item_name: $button.data('product-name') || '',
                        price:     parseFloat($button.data('product-price')) || 0,
                        quantity:  parseInt($button.data('quantity'), 10) || 1
                    }]
                });
            }
        });
        jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
            var badge = document.getElementById('cart-badge');
            if (!badge) return;
            var count = parseInt(badge.textContent.trim(), 10) || 0;
            badge.classList.toggle('hidden', count === 0);
        });
    }

    /* Mark already-favorited products on page load, and reconcile the
       guest wishlist (localStorage) — either merge it into the account
       right after login, or render it into the drawer for a logged-out visitor. */
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof cozyAjax === 'undefined') return;

        if (cozyAjax.isLoggedIn) {
            (cozyAjax.favorites || []).forEach(function (id) {
                cozyUpdateFavBtns(id, true);
            });

            var guestIds = cozyGetGuestWishlist();
            if (guestIds.length) {
                var body = new FormData();
                body.append('action', 'cozy_merge_wishlist');
                body.append('nonce', cozyAjax.favNonce);
                guestIds.forEach(function (id) { body.append('ids[]', id); });

                fetch(cozyAjax.url, { method: 'POST', body: body, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res || !res.success) return;
                        localStorage.removeItem('cozy_guest_wishlist');
                        guestIds.forEach(function (id) { cozyUpdateFavBtns(id, true); });
                        cozyUpdateFavBadge(res.data.count);
                        (res.data.added || []).forEach(function (item) { cozyAddFavItem(item.html); });
                    });
            }
        } else {
            var guestFavIds = cozyGetGuestWishlist();
            if (guestFavIds.length) {
                guestFavIds.forEach(function (id) { cozyUpdateFavBtns(id, true); });
                cozyUpdateFavBadge(guestFavIds.length);
                cozyFetchWishlistItemsHtml(guestFavIds, function (items) {
                    items.forEach(function (item) { cozyAddFavItem(item.html); });
                    var validIds = items.map(function (item) { return item.id; });
                    if (validIds.length !== guestFavIds.length) {
                        cozySaveGuestWishlist(validIds);
                        cozyUpdateFavBadge(validIds.length);
                    }
                });
            }
        }
    });

    /* NEWSLETTER — primary submission handled by Hostinger Reach subscription block.
       Fallback AJAX submission handler for the native newsletter form when Reach is absent: */
    var cozyNewsletterForm = document.querySelector('form.newsletter-form:not(.hostinger-reach-block-subscription-form-wrapper)');
    if (cozyNewsletterForm) {
        cozyNewsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var emailInput = cozyNewsletterForm.querySelector('input[type="email"]');
            var consentInput = cozyNewsletterForm.querySelector('input[name="marketing_consent"]');
            var statusBox = cozyNewsletterForm.querySelector('.cozy-newsletter-status');
            var submitBtn = cozyNewsletterForm.querySelector('button[type="submit"]');

            if (!consentInput || !consentInput.checked) {
                if (statusBox) {
                    statusBox.textContent = 'Debes aceptar la política de privacidad para continuar.';
                    statusBox.className = 'cozy-newsletter-status text-center py-2 text-xs font-bold text-red-500';
                }
                return;
            }

            var email = emailInput ? emailInput.value.trim() : '';
            if (!email) return;

            if (submitBtn) submitBtn.disabled = true;

            var fd = new FormData();
            fd.append('action', 'cozy_newsletter_subscribe');
            fd.append('email', email);
            var nonce = cozyNewsletterForm.querySelector('input[name="nonce"]');
            if (nonce) fd.append('nonce', nonce.value);

            fetch(typeof cozyAjax !== 'undefined' ? cozyAjax.url : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: fd
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (statusBox) {
                    if (res.success) {
                        statusBox.textContent = '🌿 ¡Bienvenida/o al Cozy Club! Te hemos enviado tu 5% de descuento.';
                        statusBox.className = 'cozy-newsletter-status text-center py-2 text-xs font-bold text-cozy-mint';
                        if (emailInput) emailInput.value = '';
                    } else {
                        statusBox.textContent = (res.data && res.data.message) || 'Ha ocurrido un error. Inténtalo de nuevo.';
                        statusBox.className = 'cozy-newsletter-status text-center py-2 text-xs font-bold text-red-500';
                    }
                }
            })
            .catch(function () {
                if (statusBox) {
                    statusBox.textContent = 'Error de conexión. Inténtalo de nuevo más tarde.';
                    statusBox.className = 'cozy-newsletter-status text-center py-2 text-xs font-bold text-red-500';
                }
            })
            .finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

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
                        var results = (res.success && res.data) ? res.data : [];
                        renderSuggestions(results);
                        if (typeof gtag === 'function') {
                            gtag('event', 'search', {
                                search_term: term,
                                results_count: results.length
                            });
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

    /* ---------- AJAX SHOP FILTERS & PAGINATION ---------- */
    function isShopLink(link) {
        if (!link) return false;
        // Intercept links in filters widget, pagination, category carousel
        if (link.closest('#cozy-shop-filters') || 
            link.closest('.woocommerce-pagination') || 
            link.closest('.cozy-cat-carousel') ||
            link.classList.contains('cozy-cat-card')) {
            return true;
        }
        // Intercept "Limpiar filtros" link in sort bar
        if (link.closest('.cozy-sort-bar') && link.getAttribute('href') && (link.textContent.indexOf('Limpiar') > -1 || link.href.indexOf('filter') > -1 || link.href.indexOf('shop') > -1)) {
            return true;
        }
        return false;
    }

    function serializeFormToUrl(form, urlObj) {
        var inputs = form.querySelectorAll('input, select, textarea');
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (input.name && !input.disabled) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) {
                        urlObj.searchParams.set(input.name, input.value);
                    }
                } else if (input.type === 'submit' || input.type === 'button') {
                    // Skip buttons
                } else {
                    urlObj.searchParams.set(input.name, input.value);
                }
            }
        }
    }

    window.cozyLoadShopUrl = function (url, scroll, push) {
        if (push === undefined) push = true;
        var container = document.getElementById('cozy-products-container');
        var filtersPanel = document.getElementById('cozy-shop-filters');
        var sortBar = document.querySelector('.cozy-sort-bar');
        
        if (container) {
            container.classList.add('cozy-loading');
        }
        
        fetch(url)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                
                // Swap products container content
                var newContainer = doc.getElementById('cozy-products-container');
                if (newContainer && container) {
                    container.innerHTML = newContainer.innerHTML;
                }
                
                // Swap hero banner content
                var hero = document.getElementById('cozy-shop-hero');
                var newHero = doc.getElementById('cozy-shop-hero');
                if (newHero && hero) {
                    hero.innerHTML = newHero.innerHTML;
                    hero.className = newHero.className;
                }

                // Swap category carousel content
                var carousel = document.getElementById('cozy-cat-carousel');
                var newCarousel = doc.getElementById('cozy-cat-carousel');
                if (newCarousel && carousel) {
                    carousel.innerHTML = newCarousel.innerHTML;
                }

                // Swap filters content
                var newFilters = doc.getElementById('cozy-shop-filters');
                if (newFilters && filtersPanel) {
                    var wasHidden = filtersPanel.classList.contains('hidden');
                    filtersPanel.innerHTML = newFilters.innerHTML;
                    if (wasHidden) {
                        filtersPanel.classList.add('hidden');
                    } else {
                        filtersPanel.classList.remove('hidden');
                    }
                }
                
                // Swap sort bar content
                var newSortBar = doc.querySelector('.cozy-sort-bar');
                if (newSortBar && sortBar) {
                    sortBar.innerHTML = newSortBar.innerHTML;
                }
                
                // Update page URL if requested
                if (push) {
                    window.history.pushState(null, '', url);
                }
                
                // Re-initialize WooCommerce price slider
                if (typeof jQuery !== 'undefined') {
                    jQuery(document.body).trigger('init_price_filter');
                }
                
                // Scroll to container top if needed
                if (scroll && container) {
                    var yOffset = -100;
                    var y = container.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: y, behavior: 'smooth' });
                }
            })
            .catch(function (error) {
                console.error('AJAX load failed, redirecting:', error);
                window.location.href = url;
            })
            .finally(function () {
                if (container) {
                    container.classList.remove('cozy-loading');
                }
            });
    };

    // Click handler for AJAX links
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (isShopLink(link)) {
            e.preventDefault();
            var shouldScroll = !!link.closest('.woocommerce-pagination');
            window.cozyLoadShopUrl(link.href, shouldScroll);
        }
    });

    // Intercept change event on orderby select
    document.addEventListener('change', function (e) {
        var orderSelect = e.target.closest('.woocommerce-ordering select.orderby');
        if (orderSelect) {
            e.preventDefault();
            e.stopPropagation();
            
            var form = orderSelect.closest('form');
            var actionUrl = form.getAttribute('action') || window.location.href.split('?')[0];
            var urlObj = new URL(actionUrl, window.location.origin);
            
            serializeFormToUrl(form, urlObj);
            
            // Preserve other active params from current URL
            var currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach(function (value, key) {
                if (!urlObj.searchParams.has(key)) {
                    urlObj.searchParams.set(key, value);
                }
            });
            
            urlObj.searchParams.delete('paged');
            window.cozyLoadShopUrl(urlObj.toString(), false);
        }
    }, true);

    // Intercept submit event on forms inside filters panel
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.closest('#cozy-shop-filters') || form.classList.contains('woocommerce-ordering')) {
            e.preventDefault();
            
            var actionUrl = form.getAttribute('action') || window.location.href.split('?')[0];
            var urlObj = new URL(actionUrl, window.location.origin);
            
            serializeFormToUrl(form, urlObj);
            
            // Preserve other active params from current URL
            var currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach(function (value, key) {
                if (!urlObj.searchParams.has(key)) {
                    urlObj.searchParams.set(key, value);
                }
            });
            
            urlObj.searchParams.delete('paged');
            window.cozyLoadShopUrl(urlObj.toString(), false);
        }
    }, true);

    // Popstate handling for browser back/forward buttons
    window.addEventListener('popstate', function () {
        var container = document.getElementById('cozy-products-container');
        if (container) {
            window.cozyLoadShopUrl(window.location.href, false, false);
        }
    });

})();

/* ─── MODO ULTRA-COZY (Tarde de Lluvia, Té & Chimenea) ───────────── */
(function() {
    'use strict';
    var isUltraActive = false;
    var currentWeather = 'rain'; // 'rain', 'autumn', 'snow', 'sakura'
    var audioCtx = null;
    var rainGain = null;
    var fireGain = null;
    var animFrameId = null;
    var canvas = null;
    var ctx = null;
    var particles = [];

    function populateParticles() {
        if (!canvas) return;
        particles = [];
        var isMobile = window.innerWidth < 640;
        var isTablet = window.innerWidth < 1024;
        var count = isMobile ? 25 : (isTablet ? 60 : 120);

        for (var i = 0; i < count; i++) {
            if (currentWeather === 'rain') {
                particles.push({
                    type: 'rain',
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    l: isMobile ? (Math.random() * 16 + 10) : (Math.random() * 26 + 14),
                    xs: (Math.random() - 0.5) * 1.2 - 1.5,
                    ys: Math.random() * 8 + 11,
                    o: Math.random() * 0.4 + 0.3,
                    w: isMobile ? 1.6 : 2.2
                });
            } else if (currentWeather === 'autumn') {
                particles.push({
                    type: 'autumn',
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * 10 + 6,
                    speedY: Math.random() * 1.5 + 0.8,
                    speedX: (Math.random() - 0.5) * 1.8,
                    angle: Math.random() * Math.PI * 2,
                    spin: (Math.random() - 0.5) * 0.04,
                    o: Math.random() * 0.6 + 0.3,
                    hue: Math.random() * 35 + 15
                });
            } else if (currentWeather === 'snow') {
                particles.push({
                    type: 'snow',
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    radius: Math.random() * 3.5 + 1.5,
                    speedY: Math.random() * 1.0 + 0.5,
                    sway: Math.random() * 0.03 + 0.01,
                    swayOffset: Math.random() * Math.PI * 2,
                    o: Math.random() * 0.7 + 0.3
                });
            } else if (currentWeather === 'sakura') {
                particles.push({
                    type: 'sakura',
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * 9 + 5,
                    speedY: Math.random() * 1.2 + 0.6,
                    speedX: (Math.random() - 0.5) * 1.5,
                    angle: Math.random() * Math.PI * 2,
                    spin: (Math.random() - 0.5) * 0.03,
                    o: Math.random() * 0.6 + 0.3
                });
            }
        }

        // Add warm golden fire embers if weather is rain
        if (currentWeather === 'rain') {
            var emberCount = isMobile ? 8 : (isTablet ? 20 : 35);
            for (var j = 0; j < emberCount; j++) {
                particles.push({
                    type: 'ember',
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    r: Math.random() * 2.2 + 1,
                    ys: -(Math.random() * 0.8 + 0.3),
                    xs: (Math.random() - 0.5) * 0.6,
                    o: Math.random() * 0.7 + 0.3,
                    hue: Math.random() * 30 + 25
                });
            }
        }
    }

    function initCanvas() {
        if (canvas) return;
        canvas = document.createElement('canvas');
        canvas.id = 'cozy-rain-canvas';
        document.body.appendChild(canvas);
        ctx = canvas.getContext('2d');
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
    }

    function resizeCanvas() {
        if (!canvas) return;
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        populateParticles();
    }

    function renderCanvas() {
        if (!isUltraActive || !ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            
            if (p.type === 'rain') {
                ctx.lineCap = 'round';
                ctx.lineWidth = p.w || 2;
                ctx.strokeStyle = 'rgba(100, 155, 170, ' + p.o + ')';
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p.x + p.xs, p.y + p.l);
                ctx.stroke();
                p.x += p.xs;
                p.y += p.ys;
                if (p.y > canvas.height) {
                    p.y = -30;
                    p.x = Math.random() * canvas.width;
                }
            } else if (p.type === 'ember') {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'hsla(' + p.hue + ', 85%, 65%, ' + p.o + ')';
                ctx.fill();
                p.y += p.ys;
                p.x += p.xs;
                if (p.y < -10) {
                    p.y = canvas.height + 10;
                    p.x = Math.random() * canvas.width;
                }
            } else if (p.type === 'autumn') {
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.angle);
                ctx.fillStyle = 'hsla(' + p.hue + ', 75%, 45%, ' + p.o + ')';
                ctx.beginPath();
                ctx.ellipse(0, 0, p.size, p.size / 2.2, Math.PI / 4, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();

                p.y += p.speedY;
                p.x += p.speedX + Math.sin(p.angle) * 0.8;
                p.angle += p.spin;
                if (p.y > canvas.height + 20) {
                    p.y = -20;
                    p.x = Math.random() * canvas.width;
                }
            } else if (p.type === 'snow') {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255, 255, 255, ' + p.o + ')';
                ctx.fill();

                p.y += p.speedY;
                p.swayOffset += p.sway;
                p.x += Math.sin(p.swayOffset) * 0.8;
                if (p.y > canvas.height + 10) {
                    p.y = -10;
                    p.x = Math.random() * canvas.width;
                }
            } else if (p.type === 'sakura') {
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.angle);
                ctx.fillStyle = 'rgba(255, 182, 193, ' + p.o + ')';
                ctx.beginPath();
                ctx.ellipse(0, 0, p.size, p.size / 1.8, Math.PI / 3, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();

                p.y += p.speedY;
                p.x += p.speedX + Math.sin(p.angle) * 0.8;
                p.angle += p.spin;
                if (p.y > canvas.height + 20) {
                    p.y = -20;
                    p.x = Math.random() * canvas.width;
                }
            }
        }

        animFrameId = requestAnimationFrame(renderCanvas);
    }

    function initAudio() {
        if (audioCtx) return;
        try {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            audioCtx = new AudioContextClass();

            var bufferSize = audioCtx.sampleRate * 2;
            var noiseBuffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            var output = noiseBuffer.getChannelData(0);
            var b0 = 0, b1 = 0, b2 = 0, b3 = 0, b4 = 0, b5 = 0, b6 = 0;
            for (var i = 0; i < bufferSize; i++) {
                var white = Math.random() * 2 - 1;
                b0 = 0.99886 * b0 + white * 0.0555179;
                b1 = 0.99332 * b1 + white * 0.0750759;
                b2 = 0.96900 * b2 + white * 0.1538520;
                b3 = 0.86650 * b3 + white * 0.3104856;
                b4 = 0.55000 * b4 + white * 0.5329522;
                b5 = -0.7616 * b5 - white * 0.0168980;
                output[i] = b0 + b1 + b2 + b3 + b4 + b5 + b6 + white * 0.5362;
                output[i] *= 0.04;
                b6 = white * 0.115926;
            }
            var rainSource = audioCtx.createBufferSource();
            rainSource.buffer = noiseBuffer;
            rainSource.loop = true;

            var rainFilter = audioCtx.createBiquadFilter();
            rainFilter.type = 'lowpass';
            rainFilter.frequency.value = 800;

            rainGain = audioCtx.createGain();
            rainGain.gain.value = 0;

            rainSource.connect(rainFilter);
            rainFilter.connect(rainGain);
            rainGain.connect(audioCtx.destination);
            rainSource.start(0);

            fireGain = audioCtx.createGain();
            fireGain.gain.value = 0;
            fireGain.connect(audioCtx.destination);

            function scheduleCrackle() {
                if (!audioCtx) return;
                if (isUltraActive && currentWeather === 'rain') {
                    var now = audioCtx.currentTime;
                    if (Math.random() < 0.45) {
                        var osc = audioCtx.createOscillator();
                        var g = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(120 + Math.random() * 350, now);
                        g.gain.setValueAtTime(0.025 + Math.random() * 0.035, now);
                        g.gain.exponentialRampToValueAtTime(0.0001, now + 0.03 + Math.random() * 0.04);
                        osc.connect(g);
                        g.connect(fireGain);
                        osc.start(now);
                        osc.stop(now + 0.08);
                    }
                }
                setTimeout(scheduleCrackle, 80 + Math.random() * 220);
            }
            scheduleCrackle();

        } catch (e) {
            console.error('Cozy audio synth failed:', e);
        }
    }

    function setAudioState(active) {
        if (active && currentWeather !== 'none') {
            initAudio();
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            if (rainGain && fireGain && audioCtx) {
                var mult = currentWeather === 'rain' ? 1 : 0.2;
                rainGain.gain.setTargetAtTime(0.18 * mult, audioCtx.currentTime, 0.8);
                fireGain.gain.setTargetAtTime(0.25 * mult, audioCtx.currentTime, 0.8);
            }
        } else {
            if (rainGain && fireGain && audioCtx) {
                rainGain.gain.setTargetAtTime(0, audioCtx.currentTime, 0.5);
                fireGain.gain.setTargetAtTime(0, audioCtx.currentTime, 0.5);
            }
        }
    }

    window.toggleUltraCozy = function(forcedState) {
        if (typeof forcedState === 'boolean') {
            isUltraActive = forcedState;
        } else {
            isUltraActive = !isUltraActive;
        }

        document.body.classList.toggle('cozy-ultra-mode', isUltraActive);
        document.documentElement.classList.toggle('cozy-ultra-mode', isUltraActive);

        // Sync toggle checkboxes
        ['cozy-ultra-checkbox', 'cozy-ultra-checkbox-mobile'].forEach(function(id) {
            var cb = document.getElementById(id);
            if (cb) cb.checked = isUltraActive;
        });
        ['cozy-drink-btn','cozy-drink-widget','cozy-weather-selector','tea-oracle-btn'].forEach(function(id) {
            var weatherSel = document.getElementById(id);
            if (weatherSel) {
                if (isUltraActive) {
                    weatherSel.classList.remove('hidden');
                    weatherSel.classList.add('flex');
                } else {
                    weatherSel.classList.add('hidden');
                    weatherSel.classList.remove('flex');
                }
            }
        });

        try {
            localStorage.setItem('cozy_ultra_mode', isUltraActive ? 'active' : 'inactive');
        } catch(e) {}

        initCanvas();
        if (isUltraActive) {
            updateWeatherButtons(currentWeather);
            checkAndShowWeatherGuide();
            if (!animFrameId) renderCanvas();
        } else {
            var guide = document.getElementById('cozy-weather-guide');
            if (guide) {
                guide.classList.add('hidden', 'translate-y-2', 'opacity-0');
                guide.classList.remove('translate-y-0', 'opacity-100');
            }
            var selector = document.getElementById('cozy-weather-selector');
            if (selector) selector.classList.remove('cozy-weather-pulse');

            if (animFrameId) {
                cancelAnimationFrame(animFrameId);
                animFrameId = null;
            }
        }

        setAudioState(isUltraActive);
    };

    function updateWeatherButtons(mode) {
        var btns = document.querySelectorAll('#cozy-weather-selector .cozy-weather-btn');
        btns.forEach(function(btn) {
            if (btn.getAttribute('data-weather') === mode) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        });
    }

    var guideTimeoutId = null;

    function checkAndShowWeatherGuide() {
        var guideDismissed = false;
        try {
            guideDismissed = localStorage.getItem('cozy_weather_guide_dismissed') === 'true';
        } catch(e) {}

        if (guideDismissed) return;

        var guide = document.getElementById('cozy-weather-guide');
        var selector = document.getElementById('cozy-weather-selector');
        if (!guide) return;

        if (selector) selector.classList.add('cozy-weather-pulse');

        setTimeout(function() {
            if (!isUltraActive) return;
            guide.classList.remove('hidden');
            requestAnimationFrame(function() {
                guide.classList.remove('translate-y-2', 'opacity-0');
                guide.classList.add('translate-y-0', 'opacity-100');
            });
        }, 500);

        if (guideTimeoutId) clearTimeout(guideTimeoutId);
        guideTimeoutId = setTimeout(function() {
            window.closeWeatherGuide();
        }, 15000);
    }

    window.closeWeatherGuide = function() {
        var guide = document.getElementById('cozy-weather-guide');
        var selector = document.getElementById('cozy-weather-selector');
        if (selector) selector.classList.remove('cozy-weather-pulse');
        if (guide) {
            guide.classList.remove('translate-y-0', 'opacity-100');
            guide.classList.add('translate-y-2', 'opacity-0');
            setTimeout(function() {
                guide.classList.add('hidden');
            }, 300);
        }
        if (guideTimeoutId) {
            clearTimeout(guideTimeoutId);
            guideTimeoutId = null;
        }
        try {
            localStorage.setItem('cozy_weather_guide_dismissed', 'true');
        } catch(e) {}
    };

    window.cozySetWeather = function(mode) {
        currentWeather = mode || 'none';
        updateWeatherButtons(currentWeather);
        window.closeWeatherGuide();
        populateParticles();
        setAudioState(isUltraActive);

        try {
            localStorage.setItem('cozy_weather', currentWeather);
        } catch(e) {}
    }; 

    var oracleQuotes = [
        "Tómate un respiro: las cosas más bonitas florecen despacio.",
        "Una taza de té caliente y un buen momento tienen el poder de reconfortar cualquier día.",
        "No hay prisa en este rincón del mundo. Respira hondo y disfruta del presente.",
        "El secreto de la calma está en encontrar alegría en los pequeños detalles cotidianos.",
        "Tu propio ritmo es el correcto; no necesitas correr para llegar donde mereces estar.",
        "Envuelve tu corazón en una manta suave y date permiso para descansar.",
        "Un sorbo a la vez, una sonrisa a la vez, un instante de paz a la vez.",
        "Hoy es un día perfecto para mimarte con una bebida calentita y tranquilidad.",
        "La paz interior es el regalo más acogedor que te puedes conceder hoy.",
        "Deja que la lluvia se lleve las prisas y que el calor del hogar te abrace.",
        "Un rincón cómodo, tu bebida favorita y un momento para ti: la combinación perfecta.",
        "Haz una pausa, cierra los ojos un segundo y siente la gratitud del momento.",
        "Recuerda que descansar también es una forma maravillosa de avanzar.",
        "Las pequeñas alegrías de hoy son los recuerdos más acogedores de mañana.",
        "Que tu día sea tan cálido y reconfortante como tu primera taza de la mañana.",
        "Permítete disfrutar de la lentitud: la vida sabe mejor a fuego lento.",
        "Enciende una velita, respira suave y recuerda lo lejos que has llegado.",
        "Cada día tiene su propio encanto cuando decides mirarlo con calma.",
        "El autocuidado no es un lujo, es tu refugio más cálido y necesario.",
        "Abraza el silencio, saborea tu tiempo y déjate cuidar por la calma."
    ];

    window.cozyOpenOracle = function() {
        window.cozyRefreshOracle();
        var modal = document.getElementById('cozy-oracle-modal');
        if (modal) modal.classList.remove('hidden');
    };

    window.cozyRefreshOracle = function() {
        var quoteEl = document.getElementById('cozy-oracle-quote');
        if (!quoteEl) return;
        var randomIdx = Math.floor(Math.random() * oracleQuotes.length);
        quoteEl.textContent = '"' + oracleQuotes[randomIdx] + '"';
    };

    window.cozyCloseOracle = function() {
        var modal = document.getElementById('cozy-oracle-modal');
        if (modal) modal.classList.add('hidden');
    };

    document.addEventListener('mousemove', function(e) {
        if (!isUltraActive) return;
        if (Math.random() > 0.4) return;
        var p = document.createElement('div');
        p.className = 'cozy-sparkle-particle';
        var size = Math.random() * 6 + 4;
        p.style.width = size + 'px';
        p.style.height = size + 'px';
        p.style.left = e.clientX + 'px';
        p.style.top = e.clientY + 'px';
        document.body.appendChild(p);
        setTimeout(function() { p.remove(); }, 800);
    });

    document.addEventListener('DOMContentLoaded', function() {
        var saved = false;
        try {
            saved = localStorage.getItem('cozy_ultra_mode') === 'active';
            var savedWeather = localStorage.getItem('cozy_weather');
            if (savedWeather) {
                currentWeather = savedWeather;
            }
        } catch(e) {}

        if (saved) {
            window.toggleUltraCozy();
        }
    });

})();

