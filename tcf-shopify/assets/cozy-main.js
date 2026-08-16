/**
 * The Cozy Fandom – Shopify OS 2.0 Theme Engine
 * Complete interactive suite: Ultra-Cozy Weather Canvas, Audio Synthesizer,
 * Cart Drawer AJAX, Wishlist / Favorites, Drink Companion, Tea Oracle & Predictive Search.
 */

(function () {
    'use strict';

    /* ─── Global State & Storage Keys ─── */
    var STORAGE_WISHLIST = 'cozy_shopify_favorites';
    var STORAGE_ULTRA    = 'cozy_ultra_mode';
    var STORAGE_WEATHER  = 'cozy_weather';
    var STORAGE_DRINK    = 'cozy_drink_choice';

    /* ─── Toast Notifications ─── */
    window.cozyShowToast = function (msg) {
        var existing = document.getElementById('cozy-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'cozy-toast';
        toast.className = 'fixed top-5 right-5 z-[4000] bg-cozy-coffee text-white text-xs font-bold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2 border border-white/20 transition-all duration-300 transform -translate-y-4 opacity-0 pointer-events-none';
        toast.innerHTML = msg;
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.remove('-translate-y-4', 'opacity-0');
        });

        setTimeout(function () {
            toast.classList.add('-translate-y-4', 'opacity-0');
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        }, 3200);
    };

    /* ─── Drawer & Modal Helpers ─── */
    window.openCart = function () {
        var drawer  = document.getElementById('cart-drawer');
        var overlay = document.getElementById('cart-overlay');
        if (drawer)  drawer.classList.remove('translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeCart = function () {
        var drawer  = document.getElementById('cart-drawer');
        var overlay = document.getElementById('cart-overlay');
        if (drawer)  drawer.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.openFavorites = function () {
        var drawer  = document.getElementById('fav-drawer');
        var overlay = document.getElementById('fav-overlay');
        renderFavoritesDrawer();
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

    window.toggleMobileMenu = function () {
        var sidebar = document.getElementById('cozy-nav-sidebar');
        var overlay = document.getElementById('mobile-menu-overlay');
        if (!sidebar) return;
        var isOpen = sidebar.classList.toggle('translate-x-0');
        sidebar.classList.toggle('-translate-x-full', !isOpen);
        if (overlay) overlay.classList.toggle('hidden', !isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    window.closeMobileMenu = function () {
        var sidebar = document.getElementById('cozy-nav-sidebar');
        var overlay = document.getElementById('mobile-menu-overlay');
        if (sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        }
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };

    /* ─── Shopify AJAX Cart Engine ─── */
    function formatMoney(cents) {
        var fmt = (window.cozyShop && window.cozyShop.moneyFormat) || '€{{amount}}';
        var price = (cents / 100).toFixed(2).replace('.', ',');
        return fmt.replace('{{amount}}', price).replace('{{amount_with_comma_separator}}', price);
    }

    function refreshCartDrawer() {
        fetch('/cart.js')
            .then(function (res) { return res.json(); })
            .then(function (cart) {
                // Update badge
                var badge = document.getElementById('cart-badge');
                if (badge) {
                    badge.textContent = cart.item_count;
                    badge.classList.toggle('hidden', cart.item_count === 0);
                }

                // Update subtotal
                var subtotalEl = document.getElementById('cart-drawer-total');
                if (subtotalEl) {
                    subtotalEl.textContent = formatMoney(cart.total_price);
                }

                // Update items HTML
                var container = document.getElementById('cart-drawer-items');
                if (!container) return;

                if (cart.item_count === 0) {
                    container.innerHTML = '<div id="cart-empty-msg" class="text-center py-12 space-y-3"><div class="text-4xl">🧺</div><p class="text-sm text-cozy-coffee/60">Tu carrito está vacío.</p><button type="button" data-action="close-cart" class="text-xs font-bold text-cozy-mint hover:underline bg-transparent border-0 cursor-pointer">¡Explorar la boutique!</button></div>';
                    return;
                }

                var html = '';
                cart.items.forEach(function (item) {
                    var imgHtml = item.image
                        ? '<img src="' + item.image + '" alt="' + (item.title || '') + '" class="w-full h-full object-contain">'
                        : '<div class="w-full h-full flex items-center justify-center text-xl">🧸</div>';

                    var variantHtml = (item.variant_title && item.variant_title !== 'Default Title')
                        ? '<span class="text-[11px] text-cozy-coffee/50 block">' + item.variant_title + '</span>'
                        : '';

                    html += '<div class="flex items-center gap-4 py-3 border-b border-cozy-sand/40 last:border-none" data-line-key="' + item.key + '">'
                        + '<a href="' + item.url + '" class="shrink-0 w-16 h-16 rounded-xl bg-cozy-cream overflow-hidden border border-cozy-sand/60 p-1">' + imgHtml + '</a>'
                        + '<div class="flex-1 min-w-0">'
                        + '<a href="' + item.url + '" class="text-xs font-bold text-cozy-coffee hover:text-cozy-mint block truncate no-underline">' + item.product_title + '</a>'
                        + variantHtml
                        + '<div class="flex items-center justify-between mt-2">'
                        + '<span class="text-xs font-bold text-cozy-coffee">' + formatMoney(item.price) + '</span>'
                        + '<div class="flex items-center gap-2">'
                        + '<span class="text-xs text-cozy-coffee/60">x' + item.quantity + '</span>'
                        + '<button type="button" data-action="remove-cart-item" data-line-key="' + item.key + '" class="text-[11px] text-red-400 hover:text-red-600 bg-transparent border-0 cursor-pointer p-0 ml-2 font-semibold">Eliminar</button>'
                        + '</div></div></div></div>';
                });

                container.innerHTML = html;
            })
            .catch(function (err) {
                console.error('Error fetching cart:', err);
            });
    }

    function quickAddToCart(variantId, qty) {
        qty = qty || 1;
        fetch('/cart/add.js', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id: variantId, quantity: qty })
        })
        .then(function (res) { return res.json(); })
        .then(function (item) {
            refreshCartDrawer();
            openCart();
            window.cozyShowToast('✨ Añadido al carrito: ' + (item.product_title || 'Producto'));
        })
        .catch(function (err) {
            console.error('Error adding to cart:', err);
            window.cozyShowToast('⚠️ No se pudo añadir el producto.');
        });
    }

    function removeCartItem(lineKey) {
        fetch('/cart/change.js', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id: lineKey, quantity: 0 })
        })
        .then(function () {
            refreshCartDrawer();
            window.cozyShowToast('🗑️ Producto eliminado del carrito');
        })
        .catch(function (err) {
            console.error('Error removing cart item:', err);
        });
    }

    function saveGiftNote(text) {
        fetch('/cart/update.js', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ attributes: { 'Nota de regalo': text } })
        });
    }

    /* ─── Favorites / Wishlist Engine ─── */
    function getFavorites() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_WISHLIST)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveFavorites(favs) {
        try {
            localStorage.setItem(STORAGE_WISHLIST, JSON.stringify(favs));
        } catch (e) {}
        updateFavoriteButtons();
    }

    function toggleFavorite(item) {
        var favs = getFavorites();
        var idx = favs.findIndex(function (f) { return String(f.id) === String(item.id); });

        if (idx >= 0) {
            favs.splice(idx, 1);
            saveFavorites(favs);
            window.cozyShowToast('🤍 Eliminado de tus favoritos');
        } else {
            favs.push(item);
            saveFavorites(favs);
            window.cozyShowToast('💖 ¡Guardado en tus favoritos!');
        }
        renderFavoritesDrawer();
    }

    function updateFavoriteButtons() {
        var favs = getFavorites();
        var favIds = favs.map(function (f) { return String(f.id); });

        // Update badge
        var badge = document.getElementById('fav-badge');
        if (badge) {
            badge.textContent = favs.length;
            badge.classList.toggle('hidden', favs.length === 0);
        }

        // Update all heart buttons
        document.querySelectorAll('[data-action="toggle-favorite"]').forEach(function (btn) {
            var pid = String(btn.getAttribute('data-product-id'));
            var isFav = favIds.indexOf(pid) >= 0;
            btn.classList.toggle('is-favorited', isFav);
            var heart = btn.querySelector('.cozy-fav-heart');
            if (heart) {
                if (isFav) {
                    heart.setAttribute('fill', '#f87171');
                    heart.setAttribute('stroke', '#f87171');
                } else {
                    heart.setAttribute('fill', 'none');
                    heart.setAttribute('stroke', 'currentColor');
                }
            }
        });
    }

    function renderFavoritesDrawer() {
        var container = document.getElementById('fav-items');
        if (!container) return;

        var favs = getFavorites();
        if (favs.length === 0) {
            container.innerHTML = '<div id="fav-empty" class="text-center py-12 space-y-4"><svg class="mx-auto text-cozy-coffee/20" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg><p class="text-sm text-cozy-coffee/60">Aún no tienes favoritos guardados.</p><button type="button" data-action="close-favorites" class="text-xs font-bold text-cozy-mint hover:underline bg-transparent border-0 cursor-pointer">¡Descubre la tienda!</button></div>';
            return;
        }

        var html = '';
        favs.forEach(function (f) {
            var imgHtml = f.image
                ? '<img src="' + f.image + '" alt="' + (f.title || '') + '" class="w-full h-full object-contain">'
                : '<div class="w-full h-full flex items-center justify-center text-xl">🧸</div>';

            html += '<div class="flex items-center gap-4 py-3 border-b border-cozy-sand/40 last:border-none">'
                + '<a href="' + (f.url || '#') + '" class="shrink-0 w-16 h-16 rounded-xl bg-cozy-cream overflow-hidden border border-cozy-sand/60 p-1">' + imgHtml + '</a>'
                + '<div class="flex-1 min-w-0">'
                + '<a href="' + (f.url || '#') + '" class="text-xs font-bold text-cozy-coffee hover:text-cozy-mint block truncate no-underline">' + f.title + '</a>'
                + '<span class="text-xs font-bold text-cozy-coffee block mt-1">' + f.price + '</span>'
                + '</div>'
                + '<button type="button" data-action="remove-favorite" data-product-id="' + f.id + '" class="text-red-400 hover:text-red-600 bg-transparent border-0 cursor-pointer p-1 text-base" title="Quitar">✕</button>'
                + '</div>';
        });

        container.innerHTML = html;
    }

    /* ─── Predictive Live Search ─── */
    function initPredictiveSearch() {
        var input = document.querySelector('.cozy-hdr-search__input');
        var resultsBox = document.getElementById('cozy-search-suggestions');
        if (!input || !resultsBox) return;

        var debounceTimer = null;

        input.addEventListener('input', function () {
            var query = input.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                resultsBox.classList.add('hidden');
                resultsBox.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function () {
                fetch('/search/suggest.json?q=' + encodeURIComponent(query) + '&resources[type]=product&resources[limit]=5')
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        var products = (data.resources && data.resources.results && data.resources.results.products) || [];
                        if (products.length === 0) {
                            resultsBox.innerHTML = '<div class="p-4 text-center text-xs text-cozy-coffee/60">No encontramos tesoros para "' + query + '".</div>';
                            resultsBox.classList.remove('hidden');
                            return;
                        }

                        var html = '<div class="p-2 divide-y divide-cozy-sand/40">';
                        products.forEach(function (p) {
                            html += '<a href="' + p.url + '" class="flex items-center gap-3 p-2.5 hover:bg-cozy-mintLight rounded-xl transition-colors no-underline text-cozy-coffee">'
                                + '<img src="' + (p.image || '') + '" alt="" class="w-10 h-10 object-contain rounded-lg bg-cozy-cream border border-cozy-sand">'
                                + '<div class="flex-1 min-w-0">'
                                + '<p class="text-xs font-bold truncate m-0">' + p.title + '</p>'
                                + '<span class="text-[11px] font-bold text-cozy-mint">' + p.price + '</span>'
                                + '</div>'
                                + '</a>';
                        });
                        html += '</div>';

                        resultsBox.innerHTML = html;
                        resultsBox.classList.remove('hidden');
                    })
                    .catch(function () {
                        resultsBox.classList.add('hidden');
                    });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });
    }

    /* ─── Tea Oracle Engine ─── */
    var oracleQuotes = [
        "Un té caliente, tu rincón favorito y tiempo para ti. Eso es magia.",
        "Las pequeñas cosas bonitas hacen los días grandes.",
        "Rodéate de cosas que te hagan sonreír sin darte cuenta.",
        "Hoy es un día perfecto para pausar y disfrutar de tu universo favorito.",
        "El hogar no es un lugar, es la calma que sientes en tu rincón.",
        "Un poco de magia y una pizca de confort transforman cualquier tarde.",
        "Disfruta de la lectura, el aroma a café y los detalles con alma."
    ];

    window.cozyOpenOracle = function () {
        window.cozyRefreshOracle();
        var modal = document.getElementById('cozy-oracle-modal');
        if (modal) modal.classList.remove('hidden');
    };

    window.cozyRefreshOracle = function () {
        var quoteEl = document.getElementById('cozy-oracle-quote');
        if (!quoteEl) return;
        var randomIdx = Math.floor(Math.random() * oracleQuotes.length);
        quoteEl.textContent = '"' + oracleQuotes[randomIdx] + '"';
    };

    window.cozyCloseOracle = function () {
        var modal = document.getElementById('cozy-oracle-modal');
        if (modal) modal.classList.add('hidden');
    };

    /* ─── Drink Companion Engine ─── */
    function initDrinkCompanion() {
        var saved = localStorage.getItem(STORAGE_DRINK) || '🍵|Matcha Latte';
        var parts = saved.split('|');
        setDrink(parts[0], parts[1]);
    }

    function setDrink(emoji, name) {
        var emojiEl = document.getElementById('cozy-drink-emoji');
        var nameEl  = document.getElementById('cozy-drink-name');
        if (emojiEl) emojiEl.textContent = emoji;
        if (nameEl)  nameEl.textContent = name;
        try {
            localStorage.setItem(STORAGE_DRINK, emoji + '|' + name);
        } catch(e) {}
    }

    /* ─── Ultra-Cozy Weather Canvas & Audio Synth ─── */
    var isUltraActive = false;
    var currentWeather = 'rain';
    var canvas, ctx, animFrameId;
    var particles = [];
    var audioCtx = null, rainGain = null, fireGain = null;

    function initCanvas() {
        canvas = document.getElementById('cozy-rain-canvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        populateParticles();
    }

    function resizeCanvas() {
        if (!canvas) return;
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    function populateParticles() {
        particles = [];
        if (!canvas || currentWeather === 'none') return;

        var count = window.innerWidth < 768 ? 30 : 90;
        for (var i = 0; i < count; i++) {
            particles.push(createParticle());
        }
    }

    function createParticle() {
        var w = (canvas && canvas.width) || window.innerWidth;
        var h = (canvas && canvas.height) || window.innerHeight;

        if (currentWeather === 'rain') {
            return {
                type: 'rain',
                x: Math.random() * w,
                y: Math.random() * h,
                speedY: 7 + Math.random() * 8,
                speedX: -0.5 - Math.random() * 1,
                len: 12 + Math.random() * 12,
                o: 0.15 + Math.random() * 0.25
            };
        } else if (currentWeather === 'autumn') {
            return {
                type: 'autumn',
                x: Math.random() * w,
                y: Math.random() * h,
                speedY: 1 + Math.random() * 2,
                speedX: Math.random() * 1.5 - 0.75,
                size: 6 + Math.random() * 8,
                angle: Math.random() * Math.PI * 2,
                spin: (Math.random() - 0.5) * 0.04,
                hue: 25 + Math.random() * 25,
                o: 0.5 + Math.random() * 0.3
            };
        } else if (currentWeather === 'snow') {
            return {
                type: 'snow',
                x: Math.random() * w,
                y: Math.random() * h,
                speedY: 0.8 + Math.random() * 1.5,
                sway: 0.02 + Math.random() * 0.03,
                swayOffset: Math.random() * Math.PI * 2,
                radius: 2 + Math.random() * 3,
                o: 0.3 + Math.random() * 0.4
            };
        } else if (currentWeather === 'sakura') {
            return {
                type: 'sakura',
                x: Math.random() * w,
                y: Math.random() * h,
                speedY: 1.2 + Math.random() * 1.8,
                speedX: Math.random() * 1 - 0.5,
                size: 5 + Math.random() * 7,
                angle: Math.random() * Math.PI * 2,
                spin: (Math.random() - 0.5) * 0.03,
                o: 0.6 + Math.random() * 0.3
            };
        }
    }

    function renderCanvas() {
        if (!ctx || !canvas) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            if (p.type === 'rain') {
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p.x + p.speedX * 2, p.y + p.len);
                ctx.strokeStyle = 'rgba(136, 196, 181, ' + p.o + ')';
                ctx.lineWidth = 1.8;
                ctx.stroke();

                p.y += p.speedY;
                p.x += p.speedX;
                if (p.y > canvas.height + 20) { p.y = -20; p.x = Math.random() * canvas.width; }
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
                if (p.y > canvas.height + 20) { p.y = -20; p.x = Math.random() * canvas.width; }
            } else if (p.type === 'snow') {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255, 255, 255, ' + p.o + ')';
                ctx.fill();

                p.y += p.speedY;
                p.swayOffset += p.sway;
                p.x += Math.sin(p.swayOffset) * 0.8;
                if (p.y > canvas.height + 10) { p.y = -10; p.x = Math.random() * canvas.width; }
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
                if (p.y > canvas.height + 20) { p.y = -20; p.x = Math.random() * canvas.width; }
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
                output[i] = (b0 + b1 + b2 + b3 + b4 + b5 + b6 + white * 0.5362) * 0.04;
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
            if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
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

    window.toggleUltraCozy = function (forcedState) {
        isUltraActive = (typeof forcedState === 'boolean') ? forcedState : !isUltraActive;

        document.body.classList.toggle('cozy-ultra-mode', isUltraActive);
        document.documentElement.classList.toggle('cozy-ultra-mode', isUltraActive);

        ['cozy-ultra-checkbox', 'cozy-ultra-checkbox-mobile'].forEach(function (id) {
            var cb = document.getElementById(id);
            if (cb) cb.checked = isUltraActive;
        });

        ['cozy-drink-widget', 'cozy-weather-selector', 'tea-oracle-btn'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden', !isUltraActive);
                if (isUltraActive && id !== 'tea-oracle-btn') el.classList.add('flex');
            }
        });

        try { localStorage.setItem(STORAGE_ULTRA, isUltraActive ? 'active' : 'inactive'); } catch (e) {}

        initCanvas();
        if (isUltraActive) {
            if (!animFrameId) renderCanvas();
        } else {
            if (animFrameId) { cancelAnimationFrame(animFrameId); animFrameId = null; }
        }

        setAudioState(isUltraActive);

        var msg = isUltraActive
            ? '🌧️ Modo Ultra-Cozy activado: Lluvia, chimenea y tu bebida calentita 🍵'
            : '☀️ Modo normal restaurado';
        window.cozyShowToast(msg);
    };

    window.cozySetWeather = function (mode) {
        currentWeather = mode || 'none';
        populateParticles();
        setAudioState(isUltraActive);
        try { localStorage.setItem(STORAGE_WEATHER, currentWeather); } catch (e) {}

        var labels = {
            none: '✨ Modo Despejado (Sin efectos)',
            rain: '🌧️ Clima: Lluvia & Chimenea',
            autumn: '🍂 Clima: Otoño Dorado',
            snow: '❄️ Clima: Nieve Silenciosa',
            sakura: '🌸 Clima: Primavera Sakura'
        };
        window.cozyShowToast(labels[currentWeather] || 'Clima cambiado');
    };

    /* ─── Global Event Dispatcher ─── */
    document.addEventListener('click', function (e) {
        var target = e.target.closest('[data-action]');
        if (!target) {
            // Dismiss modals with data-close-on-self
            var dismissModal = e.target.closest('[data-close-on-self]');
            if (dismissModal && e.target === dismissModal) {
                dismissModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
            return;
        }

        var action = target.getAttribute('data-action');

        switch (action) {
            case 'open-cart':
                e.preventDefault();
                openCart();
                break;

            case 'close-cart':
                e.preventDefault();
                closeCart();
                break;

            case 'open-favorites':
                e.preventDefault();
                openFavorites();
                break;

            case 'close-favorites':
                e.preventDefault();
                closeFavorites();
                break;

            case 'toggle-favorite':
                e.preventDefault();
                toggleFavorite({
                    id: target.getAttribute('data-product-id'),
                    title: target.getAttribute('data-product-title'),
                    price: target.getAttribute('data-product-price'),
                    image: target.getAttribute('data-product-image'),
                    url: target.getAttribute('data-product-url')
                });
                break;

            case 'remove-favorite':
                e.preventDefault();
                toggleFavorite({ id: target.getAttribute('data-product-id') });
                break;

            case 'quick-add':
                e.preventDefault();
                quickAddToCart(target.getAttribute('data-variant-id'), 1);
                break;

            case 'remove-cart-item':
                e.preventDefault();
                removeCartItem(target.getAttribute('data-line-key'));
                break;

            case 'toggle-gift-note':
                var field = document.getElementById('cozy-gift-field');
                if (field) field.classList.toggle('hidden', !target.checked);
                break;

            case 'toggle-mobile-menu':
                e.preventDefault();
                toggleMobileMenu();
                break;

            case 'close-mobile-menu':
                e.preventDefault();
                closeMobileMenu();
                break;

            case 'close-mobile-menu-open-favorites':
                closeMobileMenu();
                openFavorites();
                break;

            case 'close-mobile-menu-open-cart':
                closeMobileMenu();
                openCart();
                break;

            case 'open-tea-oracle':
                e.preventDefault();
                window.cozyOpenOracle();
                break;

            case 'refresh-oracle':
                e.preventDefault();
                window.cozyRefreshOracle();
                break;

            case 'close-oracle':
                e.preventDefault();
                window.cozyCloseOracle();
                break;

            case 'toggle-drink-menu':
                e.preventDefault();
                var dm = document.getElementById('cozy-drink-modal');
                if (dm) dm.classList.toggle('hidden');
                break;

            case 'close-drink-modal':
                e.preventDefault();
                var dm2 = document.getElementById('cozy-drink-modal');
                if (dm2) dm2.classList.add('hidden');
                break;

            case 'select-drink':
                e.preventDefault();
                var data = target.getAttribute('data-drink').split('|');
                setDrink(data[0], data[1]);
                var dm3 = document.getElementById('cozy-drink-modal');
                if (dm3) dm3.classList.add('hidden');
                window.cozyShowToast('☕ Bebida elegida: ' + data[1]);
                break;

            case 'toggle-ultra-cozy':
                window.toggleUltraCozy();
                break;

            case 'set-weather':
                e.preventDefault();
                window.cozySetWeather(target.getAttribute('data-weather'));
                break;

            case 'consent-accept':
                try { localStorage.setItem('cozy_cookie_consent', 'granted'); } catch(e) {}
                var cb = document.getElementById('cozy-consent-banner');
                if (cb) cb.remove();
                break;

            case 'consent-reject':
                try { localStorage.setItem('cozy_cookie_consent', 'denied'); } catch(e) {}
                var cb2 = document.getElementById('cozy-consent-banner');
                if (cb2) cb2.remove();
                break;

            case 'open-cookie-settings':
                e.preventDefault();
                try { localStorage.removeItem('cozy_cookie_consent'); } catch(e) {}
                var cb3 = document.getElementById('cozy-consent-banner');
                if (cb3) cb3.classList.remove('hidden');
                break;
        }
    });

    // Sync Gift Note typing debounce
    var giftText = document.getElementById('cozy-gift-text');
    if (giftText) {
        var giftTimer = null;
        giftText.addEventListener('input', function () {
            clearTimeout(giftTimer);
            giftTimer = setTimeout(function () {
                saveGiftNote(giftText.value);
            }, 600);
        });
    }

    // Escape Key Listener
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCart();
            closeFavorites();
            closeMobileMenu();
            window.cozyCloseOracle();
            var dm = document.getElementById('cozy-drink-modal');
            if (dm) dm.classList.add('hidden');
        }
    });

    /* ─── Initialize on DOM Ready ─── */
    document.addEventListener('DOMContentLoaded', function () {
        updateFavoriteButtons();
        initPredictiveSearch();
        initDrinkCompanion();

        // Restore cookie consent banner if undecided
        var consent = localStorage.getItem('cozy_cookie_consent');
        if (!consent) {
            var cb = document.getElementById('cozy-consent-banner');
            if (cb) cb.classList.remove('hidden');
        }

        // Restore Ultra Cozy Mode
        var savedUltra = localStorage.getItem(STORAGE_ULTRA);
        if (savedUltra === 'active') {
            window.toggleUltraCozy(true);
        }

        // Restore Weather
        var savedWeather = localStorage.getItem(STORAGE_WEATHER);
        if (savedWeather) {
            currentWeather = savedWeather;
            populateParticles();
        }
    });

})();
