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
            '<button onclick="closeCozyLightbox()" class="text-white/70 hover:text-white transition-colors p-2 text-2xl border-0 bg-transparent cursor-pointer" aria-label="Cerrar">&times;</button>' +
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

