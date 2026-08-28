<?php
/**
 * Cozy Fandom Child Theme — functions & definitions
 */

require_once get_stylesheet_directory() . '/inc/cozy-icons.php';
require_once get_stylesheet_directory() . '/inc/coming-soon.php';

/* ------------------------------------------------------------------ */
/*  RESOURCE PRELOADS (Critical Fonts & LCP Hero Banner)              */
/* ------------------------------------------------------------------ */
add_action( 'wp_head', function() {
    $font_dir = get_stylesheet_directory_uri() . '/assets/fonts/';
    ?>
    <link rel="preload" href="<?php echo esc_url( $font_dir . 'PlusJakartaSans-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo esc_url( $font_dir . 'PlusJakartaSans-Bold.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo esc_url( $font_dir . 'PlayfairDisplay-SemiBold.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
    <?php if ( is_front_page() ) : ?>
    <link rel="preload" as="image" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/banner.webp' ); ?>" fetchpriority="high">
    <?php endif; ?>
    <?php
}, 0 );

/* ------------------------------------------------------------------ */
/*  GOOGLE ANALYTICS (GA4) — gated behind cookie consent                */
/* ------------------------------------------------------------------ */
/* No analytics cookie or network request happens until the visitor accepts
   the banner below (cozy_render_consent_banner()). Defining the dataLayer
   array and a gtag() stub up front is safe on its own — pushing to a plain
   JS array sets no cookie and contacts no server; the actual gtag.js script
   (which is what starts the _ga cookies) only loads after consent. */
define( 'COZY_GA4_ID', 'G-3KDLH6MJ94' );

add_action( 'wp_head', function() {
    if ( is_admin() ) return;
    $consent = isset( $_COOKIE['cozy_consent'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['cozy_consent'] ) ) : '';
    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        window.cozyGaLoaded = false;
        window.cozyLoadGA = function () {
            if ( window.cozyGaLoaded ) return;
            window.cozyGaLoaded = true;
            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( COZY_GA4_ID ); ?>';
            document.head.appendChild(s);
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js( COZY_GA4_ID ); ?>');
        };
        <?php if ( 'granted' === $consent ) : ?>
        window.cozyLoadGA();
        <?php endif; ?>
    </script>
    <?php
}, 1 );

/* ------------------------------------------------------------------ */
/*  COOKIE CONSENT BANNER                                               */
/* ------------------------------------------------------------------ */
/* Only rendered for visitors who haven't decided yet (no cozy_consent
   cookie). "Aceptar"/"Rechazar" are wired via the data-action dispatcher
   in cozy-main.js, consistent with the rest of the site's CSP-safe pattern. */
add_action( 'wp_footer', function() {
    if ( is_admin() || isset( $_COOKIE['cozy_consent'] ) ) return;
    ?>
    <div id="cozy-consent-banner"
         class="fixed inset-x-0 bottom-0 z-[3000] bg-cozy-coffee text-white/90 px-6 py-5 md:py-4 flex flex-col md:flex-row md:items-center gap-4 md:gap-6 shadow-2xl"
         role="dialog" aria-label="Aviso de cookies">
        <p class="text-xs md:text-[13px] leading-relaxed m-0 flex-1">
            Usamos cookies analíticas para entender cómo usas la tienda y mejorarla. No se activan hasta que las aceptas.
            <a href="<?php echo esc_url( cozy_fandom_legal_link( 'politica-de-cookies' ) ); ?>" class="underline hover:text-cozy-mint">Más información</a>.
        </p>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" data-action="consent-reject"
                    class="border border-white/30 hover:border-white/60 text-white/80 hover:text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors">
                Rechazar
            </button>
            <button type="button" data-action="consent-accept"
                    class="bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee text-xs font-bold px-5 py-2.5 rounded-xl transition-colors">
                Aceptar
            </button>
        </div>
    </div>
    <?php
}, 5 );

/* ------------------------------------------------------------------ */
/*  GA4 ECOMMERCE — core funnel events (view_item, purchase)            */
/*  add_to_cart is pushed client-side (cozy-main.js, added_to_cart hook) */
/*  begin_checkout is pushed client-side (data-action="begin-checkout") */
/* ------------------------------------------------------------------ */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) return;
    global $product;
    if ( ! $product instanceof WC_Product ) return;
    ?>
    <script>
    gtag('event', 'view_item', {
        currency: 'EUR',
        value: <?php echo wp_json_encode( (float) $product->get_price() ); ?>,
        items: [{
            item_id:   <?php echo wp_json_encode( (string) $product->get_id() ); ?>,
            item_name: <?php echo wp_json_encode( $product->get_name() ); ?>,
            price:     <?php echo wp_json_encode( (float) $product->get_price() ); ?>,
            quantity:  1
        }]
    });
    </script>
    <?php
}, 20 );

/* Full-fidelity view_cart (with items[]) on the real Cart page.
   The drawer also pushes a lighter view_cart (value only) when opened —
   see openCart() in cozy-main.js. */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'is_cart' ) || ! is_cart() || WC()->cart->is_empty() ) return;

    $items = [];
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        $items[] = [
            'item_id'   => (string) $product->get_id(),
            'item_name' => $product->get_name(),
            'price'     => (float) wc_get_price_to_display( $product ),
            'quantity'  => $cart_item['quantity'],
        ];
    }
    ?>
    <script>
    gtag('event', 'view_cart', {
        currency: <?php echo wp_json_encode( get_woocommerce_currency() ); ?>,
        value:    <?php echo wp_json_encode( (float) WC()->cart->get_cart_contents_total() ); ?>,
        items:    <?php echo wp_json_encode( $items ); ?>
    });
    </script>
    <?php
}, 20 );

add_action( 'woocommerce_thank_you', function( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( ! $order || $order->get_meta( '_cozy_ga4_tracked' ) ) return;

    $items = [];
    foreach ( $order->get_items() as $item ) {
        $items[] = [
            'item_id'   => (string) $item->get_product_id(),
            'item_name' => $item->get_name(),
            'price'     => (float) $order->get_item_total( $item, false, false ),
            'quantity'  => $item->get_quantity(),
        ];
    }
    ?>
    <script>
    gtag('event', 'purchase', {
        transaction_id: <?php echo wp_json_encode( $order->get_order_number() ); ?>,
        value:          <?php echo wp_json_encode( (float) $order->get_total() ); ?>,
        tax:            <?php echo wp_json_encode( (float) $order->get_total_tax() ); ?>,
        shipping:       <?php echo wp_json_encode( (float) $order->get_shipping_total() ); ?>,
        currency:       <?php echo wp_json_encode( $order->get_currency() ); ?>,
        items:          <?php echo wp_json_encode( $items ); ?>
    });
    </script>
    <?php
    $order->update_meta_data( '_cozy_ga4_tracked', 1 );
    $order->save();
}, 20 );

/* ------------------------------------------------------------------ */
/*  GA4 — sign_up / login                                               */
/*  Both hooks fire mid-request (during form processing, before any      */
/*  output), so the event itself is flashed via a one-time cookie and    */
/*  fired on the very next page load (the post-login/registration        */
/*  redirect), then cleared so it never fires twice.                      */
/* ------------------------------------------------------------------ */
add_action( 'user_register', function( $user_id ) {
    if ( is_admin() ) return; // skip accounts created manually from wp-admin
    setcookie( 'cozy_ga_signup', '1', time() + MINUTE_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
} );

add_action( 'wp_login', function( $user_login, $user ) {
    if ( $user instanceof WP_User && user_can( $user, 'manage_options' ) ) return; // skip the store owner's own logins
    setcookie( 'cozy_ga_login', '1', time() + MINUTE_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
}, 10, 2 );

add_action( 'wp_footer', function() {
    $is_signup = ! empty( $_COOKIE['cozy_ga_signup'] );
    $is_login  = ! empty( $_COOKIE['cozy_ga_login'] );
    if ( ! $is_signup && ! $is_login ) return;

    setcookie( 'cozy_ga_signup', '', time() - HOUR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN );
    setcookie( 'cozy_ga_login', '', time() - HOUR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN );
    ?>
    <script>
    gtag('event', '<?php echo $is_signup ? 'sign_up' : 'login'; ?>', { method: 'woocommerce_account' });
    </script>
    <?php
}, 21 );

/* ------------------------------------------------------------------ */
/*  THEME SETUP                                                         */
/* ------------------------------------------------------------------ */
function cozy_fandom_theme_setup() {
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'custom-logo' );
    add_editor_style( 'style.css' );

    register_nav_menus( [
        'cozy-primary-menu' => __( 'Menú principal Cozy', 'cozy-fandom-child' ),
    ] );
}
add_action( 'after_setup_theme', 'cozy_fandom_theme_setup' );

/* Remove Astra's flex wrapper on front page so our sections stack vertically */
add_action( 'wp', function() {
    if ( is_front_page() ) {
        remove_action( 'wp_body_open', 'astra_body_top' );
    }
}, 5 );

/* The cart drawer opens automatically on added_to_cart, so the WC
   "X se ha añadido a tu carrito" banner is redundant — remove it. */
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

/* Single product page: remove redundant category/tag output.
   - woocommerce_template_single_meta (priority 40) outputs the SKU/Category/Tag row.
   - Astra injects the product category above the title at priority 3 via its own hook.
   Both are replaced by the custom category pill in single-product.php. */
add_action( 'wp', function() {
    if ( ! is_product() ) return;
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
    remove_action( 'woocommerce_single_product_summary', 'astra_woo_product_category', 3 );
    remove_action( 'woocommerce_single_product_summary', 'astra_woo_single_product_taxonomy', 3 );
}, 20 );

// Show 24 products per page on the shop/category listing instead of the default 12
add_filter( 'loop_shop_per_page', function () {
    return 24;
}, 20 );

/* ------------------------------------------------------------------ */
/*  LICENCIA — URL-based shop filtering via WooCommerce Brands (Marca) */
/* ------------------------------------------------------------------ */

// Filter shop/category queries when ?licencia=snoopy,disney is in the URL
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( ! $q->is_main_query() || is_admin() ) return;
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) return;

    $raw   = sanitize_text_field( wp_unslash( $_GET['licencia'] ?? '' ) );
    $slugs = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
    if ( empty( $slugs ) ) return;

    $tq   = (array) $q->get( 'tax_query' );
    $tq[] = [
        'taxonomy' => 'product_brand',
        'field'    => 'slug',
        'terms'    => array_values( $slugs ),
        'operator' => 'IN',
    ];
    $q->set( 'tax_query', $tq );
} );

/* Hide the WooCommerce "Marca" brand from product detail pages.
   Brands are for internal use / filtering only, not customer-facing.
   WC_Brands hooks show_brand() into woocommerce_product_meta_end at
   plugins_loaded:11, so we remove it at init (which runs later). */
add_action( 'init', function() {
    if ( ! empty( $GLOBALS['WC_Brands'] ) ) {
        remove_action( 'woocommerce_product_meta_end', [ $GLOBALS['WC_Brands'], 'show_brand' ] );
    }
} );

/* WooCommerce core auto-hooks WC_Privacy_Policy::registration_privacy_policy_text()
   onto woocommerce_register_form, which duplicates the privacy text our
   woocommerce/myaccount/form-login.php template already renders manually
   (with its own cozy styling). Remove the core auto-injected copy, keeping
   only the theme's styled one. */
add_action( 'init', function() {
    global $wp_filter;
    if ( empty( $wp_filter['woocommerce_register_form'] ) ) {
        return;
    }
    foreach ( $wp_filter['woocommerce_register_form']->callbacks as $priority => $callbacks ) {
        foreach ( $callbacks as $callback ) {
            $fn = $callback['function'];
            if ( is_array( $fn ) && is_object( $fn[0] ) && 'WC_Privacy_Policy' === get_class( $fn[0] ) ) {
                remove_action( 'woocommerce_register_form', $fn, $priority );
            }
        }
    }
}, 20 );

/* Catalog/shop-loop product images were capped at 300x300 (WooCommerce's
   default), even though uploads and the single-product image go up to
   600-700px. Bump the registered "woocommerce_thumbnail" size so listings
   render sharp. Width alone only produces a 600x600 crop when the store's
   cropping ratio is "1:1"; height is forced too so it's 600x600 regardless
   of that setting. Existing images still need Regenerate Thumbnails to
   actually produce files at the new size. */
add_filter( 'pre_option_woocommerce_thumbnail_image_width', function() {
    return 600;
} );
add_filter( 'pre_option_woocommerce_thumbnail_image_height', function() {
    return 600;
} );
add_filter( 'pre_option_woocommerce_thumbnail_cropping', function() {
    return '1:1';
} );

/* ------------------------------------------------------------------ */
/*  PRODUCT REVIEWS — verified purchasers only                          */
/* ------------------------------------------------------------------ */
/* Only users who actually bought the product can leave a review.
   Combines WC's verification setting + WP's login-required comment setting. */
add_filter( 'pre_option_woocommerce_review_rating_verification_required', '__return_yes' );
add_filter( 'pre_option_comment_registration', '__return_one' ); // WP: login required to comment

/* ------------------------------------------------------------------ */
/*  STYLES                                                              */
/* ------------------------------------------------------------------ */
function cozy_fandom_enqueue_styles() {
    /* Google Fonts removed — now self-hosted via @font-face in input.css */
    /* Font Awesome removed — replaced by inline SVGs (inc/cozy-icons.php) */
    /* style.css is kept only as WordPress theme header metadata, no HTTP request needed */

    wp_enqueue_style(
        'cozy-main-style',
        get_stylesheet_directory_uri() . '/assets/css/main.min.css',
        [],
        filemtime( get_stylesheet_directory() . '/assets/css/main.min.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_fandom_enqueue_styles' );

/* ------------------------------------------------------------------ */
/*  SCRIPTS & PERFORMANCE                                              */
/* ------------------------------------------------------------------ */
function cozy_fandom_enqueue_scripts() {
    wp_enqueue_script(
        'cozy-main',
        get_stylesheet_directory_uri() . '/assets/js/cozy-main.js',
        [],
        filemtime( get_stylesheet_directory() . '/assets/js/cozy-main.js' ),
        true
    );

    $fav_ids = is_user_logged_in()
        ? array_values( array_filter( array_map( 'absint', (array) get_user_meta( get_current_user_id(), '_cozy_wishlist', true ) ) ) )
        : [];

    wp_localize_script( 'cozy-main', 'cozyAjax', [
        'url'        => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'cozy_newsletter' ),
        'favNonce'   => wp_create_nonce( 'cozy_favorites' ),
        'isLoggedIn' => is_user_logged_in(),
        'favorites'  => $fav_ids,
        'loginUrl'   => class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : wp_login_url(),
    ] );

    /* Only load wc-add-to-cart for AJAX buttons on shop/catalog/front pages.
       We intentionally avoid wc-cart-fragments on general browsing to eliminate
       the blocking /?wc-ajax=get_refreshed_fragments background AJAX request. */
    if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_front_page() ) ) {
        wp_enqueue_script( 'wc-add-to-cart' );
    }
}
add_action( 'wp_enqueue_scripts', 'cozy_fandom_enqueue_scripts', 20 );

// Defer cozy-main.js to avoid render-blocking
add_filter( 'script_loader_tag', function( $tag, $handle ) {
    if ( 'cozy-main' === $handle && false === strpos( $tag, 'defer' ) ) {
        return str_replace( '<script ', '<script defer ', $tag );
    }
    return $tag;
}, 10, 2 );

// Disable cart fragments on catalog/content pages to eliminate slow AJAX polling
add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_cart() && ! is_checkout() ) {
        wp_dequeue_script( 'wc-cart-fragments' );
    }
}, 99 );

/* ------------------------------------------------------------------ */
/*  PERFORMANCE TRANSIENTS (Nav Menu & Front Page Queries)            */
/* ------------------------------------------------------------------ */

/**
 * Returns cached header navigation (categories + licenses).
 * Eliminates 10-15 SQL queries on every single page load.
 */
function cozy_get_header_nav_data() {
    $cached = get_transient( 'cozy_header_nav_data' );
    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $uncategorised_id = absint( get_option( 'default_product_cat' ) );
    $nav_cats_raw = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
        'parent'     => 0,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 10,
    ] );

    $nav_cats = [];
    if ( ! is_wp_error( $nav_cats_raw ) && ! empty( $nav_cats_raw ) ) {
        foreach ( $nav_cats_raw as $cat ) {
            $cat_url = get_term_link( $cat );
            if ( is_wp_error( $cat_url ) ) continue;

            $children_raw = get_terms( [
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'parent'     => $cat->term_id,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ] );

            $children = [];
            if ( ! is_wp_error( $children_raw ) && ! empty( $children_raw ) ) {
                foreach ( $children_raw as $child ) {
                    $child_url = get_term_link( $child );
                    if ( is_wp_error( $child_url ) ) continue;
                    $children[] = [
                        'term_id' => $child->term_id,
                        'name'    => $child->name,
                        'url'     => $child_url,
                    ];
                }
            }

            $nav_cats[] = [
                'term_id'  => $cat->term_id,
                'name'     => $cat->name,
                'url'      => $cat_url,
                'children' => $children,
            ];
        }
    }

    $nav_licenses_raw = get_terms( [ 'taxonomy' => 'product_brand', 'hide_empty' => false ] );
    $nav_licenses = [];
    if ( ! is_wp_error( $nav_licenses_raw ) && ! empty( $nav_licenses_raw ) ) {
        foreach ( $nav_licenses_raw as $lic ) {
            $nav_licenses[] = [
                'term_id' => $lic->term_id,
                'name'    => $lic->name,
                'slug'    => $lic->slug,
            ];
        }
    }

    $data = [
        'cats'     => $nav_cats,
        'licenses' => $nav_licenses,
    ];

    set_transient( 'cozy_header_nav_data', $data, 12 * HOUR_IN_SECONDS );
    return $data;
}

/**
 * Returns cached product IDs for the front page "Nuevos" section.
 */
function cozy_get_home_new_product_ids() {
    $cached = get_transient( 'cozy_home_new_pids' );
    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $products = function_exists( 'wc_get_products' ) ? wc_get_products( [
        'limit'   => 4,
        'status'  => 'publish',
        'orderby' => 'date',
        'order'   => 'DESC',
        'return'  => 'ids',
    ] ) : [];

    set_transient( 'cozy_home_new_pids', $products, 6 * HOUR_IN_SECONDS );
    return $products;
}

/**
 * Returns cached product IDs for the front page "Top Ventas" section.
 */
function cozy_get_home_top_product_ids() {
    $cached = get_transient( 'cozy_home_top_pids' );
    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $products = function_exists( 'wc_get_products' ) ? wc_get_products( [
        'limit'   => 4,
        'status'  => 'publish',
        'tag'     => [ 'top-sell' ],
        'orderby' => 'date',
        'order'   => 'DESC',
        'return'  => 'ids',
    ] ) : [];

    set_transient( 'cozy_home_top_pids', $products, 6 * HOUR_IN_SECONDS );
    return $products;
}

/**
 * Invalidate navigation & home product transients on changes.
 */
function cozy_clear_performance_transients() {
    delete_transient( 'cozy_header_nav_data' );
    delete_transient( 'cozy_home_new_pids' );
    delete_transient( 'cozy_home_top_pids' );
}
add_action( 'created_product_cat',   'cozy_clear_performance_transients' );
add_action( 'edited_product_cat',    'cozy_clear_performance_transients' );
add_action( 'delete_product_cat',    'cozy_clear_performance_transients' );
add_action( 'created_product_brand', 'cozy_clear_performance_transients' );
add_action( 'edited_product_brand',  'cozy_clear_performance_transients' );
add_action( 'delete_product_brand',  'cozy_clear_performance_transients' );
add_action( 'save_post_product',     'cozy_clear_performance_transients' );

/* ------------------------------------------------------------------ */
/*  NEWSLETTER — Hostinger Reach subscription block                    */
/* ------------------------------------------------------------------ */

/**
 * Renders a Hostinger Reach subscription form outside the block editor by
 * running its block markup through do_blocks(), the same rendering path
 * Gutenberg uses. Avoids hardcoding the plugin's internal REST endpoint,
 * which isn't public API and could change without notice.
 *
 * inc/coming-soon.php builds its page manually and never calls wp_head()/
 * wp_footer(). That's a bigger problem than just "assets don't get printed":
 * WordPress core fires the 'wp_enqueue_scripts' action — the hook virtually
 * every plugin, including Reach, uses to register/enqueue its scripts and
 * styles in the first place — from inside wp_head() (see wp-includes/
 * default-filters.php: add_action('wp_head', 'wp_enqueue_scripts', 1)).
 * Skip wp_head() and that action never fires, so Reach's submit-handling JS
 * never even gets registered, let alone printed — confirmed live: the form
 * fell back to a native GET submit with the email sitting in the URL.
 * Firing the action ourselves first gives Reach (and everything else) its
 * normal chance to register before we render the block and print its assets.
 */
function cozy_reach_subscription_form( $form_id ) {
    if ( ! did_action( 'wp_enqueue_scripts' ) ) {
        do_action( 'wp_enqueue_scripts' );
    }

    echo do_blocks( '<!-- wp:hostinger-reach/subscription {"formId":"' . esc_attr( $form_id ) . '"} /-->' );

    /* Print whatever ended up in the queue rather than looking up the block's
     * declared style/script handles by name — an earlier version of this did
     * that (via WP_Block_Type_Registry) and it silently printed nothing,
     * because Reach's actual submit-handling script isn't necessarily
     * registered against the block type itself (e.g. it can be a plugin-wide
     * handle enqueued unconditionally on 'wp_enqueue_scripts', unrelated to
     * this specific block's block.json). Printing the whole queue sidesteps
     * having to guess Reach's internal handle names. On pages that already
     * ran wp_head()/wp_footer() normally (front-page.php), everything queued
     * there is already marked "done" and gets skipped here — this only ever
     * prints what wasn't already printed. */
    wp_print_styles();
    wp_print_scripts();
}

/**
 * Injects a mandatory GDPR/marketing-consent checkbox into the Reach
 * subscription block's rendered HTML.
 *
 * The form itself has a GDPR field configured in the Reach dashboard (the
 * standalone hosted version at reach-forms.hostingerusercontent.com renders
 * it), but the WordPress block's own PHP render + JS
 * (assets/dist/blocks/subscription-view.js) has no GDPR support at all —
 * checked its source directly, no reference to consent/gdpr anywhere in it.
 * So the block just never outputs the field, regardless of the form's config.
 *
 * Fixing it doesn't need touching Reach's JS: that script builds its POST
 * body from `new FormData(form)`, so any input we add inside the same
 * <form> — as long as it has a `name` — rides along automatically. Nesting
 * it under "metadata." matches the convention the block's own hidden fields
 * already use (metadata.plugin), so it lands in the same metadata object
 * Reach stores on the contact.
 */
add_filter( 'render_block_hostinger-reach/subscription', function ( $block_content ) {
    $consent_field = '<div class="hostinger-reach-block-form-field cozy-reach-consent">'
        . '<label class="cozy-reach-consent__label">'
        . '<input type="checkbox" name="metadata.marketing_consent" value="1" required>'
        . '<span>Acepto recibir comunicaciones de marketing de The Cozy Fandom.</span>'
        . '</label>'
        . '</div>';

    return str_replace( '<button', $consent_field . '<button', $block_content );
}, 10, 1 );

/* ------------------------------------------------------------------ */
/*  NEWSLETTER — Mailchimp API subscription (legacy, unused by default) */
/* ------------------------------------------------------------------ */

// Settings fields in WP Admin > Ajustes > Generales
add_action( 'admin_init', function () {
    // Mailchimp API Key
    register_setting( 'general', 'cozy_mailchimp_api_key', 'sanitize_text_field' );
    add_settings_field(
        'cozy_mailchimp_api_key',
        'Mailchimp API Key (Newsletter)',
        function () {
            $val = get_option( 'cozy_mailchimp_api_key', '' );
            echo '<input type="text" name="cozy_mailchimp_api_key" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us14">';
            echo '<p class="description">Clave de API de Mailchimp. Encuéntrala en <a href="https://admin.mailchimp.com/account/api/" target="_blank">admin.mailchimp.com/account/api</a></p>';
        },
        'general'
    );

    // Mailchimp List ID
    register_setting( 'general', 'cozy_mailchimp_list_id', 'sanitize_text_field' );
    add_settings_field(
        'cozy_mailchimp_list_id',
        'Mailchimp List ID (Newsletter)',
        function () {
            $val = get_option( 'cozy_mailchimp_list_id', '' );
            echo '<input type="text" name="cozy_mailchimp_list_id" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="667877ef18">';
            echo '<p class="description">ID de la lista (Audience ID) de Mailchimp.</p>';
        },
        'general'
    );

    // WhatsApp Number
    register_setting( 'general', 'cozy_whatsapp_number', 'sanitize_text_field' );
    add_settings_field(
        'cozy_whatsapp_number',
        'WhatsApp Number',
        function () {
            $val = get_option( 'cozy_whatsapp_number', '' );
            echo '<input type="text" name="cozy_whatsapp_number" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="34612345678">';
            echo '<p class="description">Número de WhatsApp (con código de país, sin +, sin espacios ni guiones). Si se deja vacío, el botón flotante no se mostrará.</p>';
        },
        'general'
    );

    // Instagram URL
    register_setting( 'general', 'cozy_instagram_url', 'esc_url_raw' );
    add_settings_field(
        'cozy_instagram_url',
        'Instagram URL',
        function () {
            $val = get_option( 'cozy_instagram_url', '' );
            echo '<input type="url" name="cozy_instagram_url" value="' . esc_url( $val ) . '" class="regular-text" placeholder="https://instagram.com/tu_perfil">';
            echo '<p class="description">Enlace al perfil de Instagram.</p>';
        },
        'general'
    );

    // TikTok URL
    register_setting( 'general', 'cozy_tiktok_url', 'esc_url_raw' );
    add_settings_field(
        'cozy_tiktok_url',
        'TikTok URL',
        function () {
            $val = get_option( 'cozy_tiktok_url', '' );
            echo '<input type="url" name="cozy_tiktok_url" value="' . esc_url( $val ) . '" class="regular-text" placeholder="https://tiktok.com/@tu_perfil">';
            echo '<p class="description">Enlace al perfil de TikTok.</p>';
        },
        'general'
    );
} );

add_action( 'wp_ajax_cozy_newsletter_subscribe',        'cozy_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_cozy_newsletter_subscribe', 'cozy_newsletter_subscribe' );

function cozy_newsletter_subscribe() {
    check_ajax_referer( 'cozy_newsletter', 'nonce' );

    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Por favor introduce un email válido.' ] );
    }

    $result = cozy_mailchimp_upsert_subscriber( $email );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success();
}

/**
 * Adds/updates a subscriber in Mailchimp using the theme's configured API key + list.
 * Shared by the footer newsletter form and the "coming soon" waitlist form.
 *
 * @return true|WP_Error True on success, WP_Error with a user-facing message on failure.
 */
function cozy_mailchimp_upsert_subscriber( $email ) {
    // Check theme option → MC4WP plugin → Mailchimp for WooCommerce plugin
    $api_key = get_option( 'cozy_mailchimp_api_key', '' );
    if ( ! $api_key ) {
        $mc4wp   = get_option( 'mc4wp', [] );
        $api_key = $mc4wp['api_key'] ?? '';
    }
    if ( ! $api_key ) {
        $mc_options = get_option( 'mailchimp-woocommerce', [] );
        $api_key    = $mc_options['api_key'] ?? '';
    }

    if ( ! $api_key ) {
        return new WP_Error( 'cozy_no_mailchimp', 'Newsletter no configurada.' );
    }

    // Data center is the suffix after the last dash (e.g. "us14")
    $dc      = substr( $api_key, strrpos( $api_key, '-' ) + 1 );
    $list_id = get_option( 'cozy_mailchimp_list_id', '667877ef18' );
    $url     = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members/" . md5( strtolower( $email ) );

    $response = wp_remote_request( $url, [
        'method'  => 'PUT',
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode( [
            'email_address' => $email,
            'status_if_new' => 'subscribed',
            'status'        => 'subscribed',
        ] ),
        'timeout' => 10,
    ] );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'cozy_mailchimp_conn', 'Error de conexión. Inténtalo de nuevo.' );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code === 200 || $code === 201 ) {
        return true;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    return new WP_Error( 'cozy_mailchimp_api', $body['detail'] ?? 'Error al suscribir. Inténtalo de nuevo.' );
}

/* ------------------------------------------------------------------ */
/*  FAVORITOS — wishlist stored in user meta                           */
/* ------------------------------------------------------------------ */
add_action( 'wp_ajax_cozy_toggle_favorite', 'cozy_toggle_favorite' );

function cozy_toggle_favorite() {
    check_ajax_referer( 'cozy_favorites', 'nonce' );

    $product_id = absint( $_POST['product_id'] ?? 0 );
    if ( ! $product_id ) {
        wp_send_json_error( [ 'message' => 'Producto no válido.' ] );
    }

    $user_id   = get_current_user_id();
    $favorites = array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id, '_cozy_wishlist', true ) ) ) );

    $key = array_search( $product_id, $favorites, true );
    if ( false !== $key ) {
        unset( $favorites[ $key ] );
        $is_favorited = false;
        $item_html    = '';
    } else {
        $favorites[]  = $product_id;
        $is_favorited = true;
        ob_start();
        cozy_render_favorite_item( $product_id );
        $item_html = ob_get_clean();
    }

    update_user_meta( $user_id, '_cozy_wishlist', array_values( $favorites ) );

    wp_send_json_success( [
        'is_favorited' => $is_favorited,
        'count'        => count( $favorites ),
        'product_id'   => $product_id,
        'item_html'    => $item_html,
    ] );
}

/* ------------------------------------------------------------------ */
/*  FAVORITOS DE INVITADO — guests keep their wishlist in localStorage;   */
/*  these two endpoints let the browser render item cards for IDs it     */
/*  already has, and merge them into the account once the guest logs in. */
/* ------------------------------------------------------------------ */
add_action( 'wp_ajax_cozy_get_wishlist_items',        'cozy_get_wishlist_items' );
add_action( 'wp_ajax_nopriv_cozy_get_wishlist_items',  'cozy_get_wishlist_items' );

function cozy_get_wishlist_items() {
    $ids   = isset( $_POST['ids'] ) ? array_values( array_filter( array_map( 'absint', (array) $_POST['ids'] ) ) ) : [];
    $items = [];

    foreach ( $ids as $id ) {
        if ( ! wc_get_product( $id ) ) continue;
        ob_start();
        cozy_render_favorite_item( $id );
        $items[] = [ 'id' => $id, 'html' => ob_get_clean() ];
    }

    wp_send_json_success( [ 'items' => $items ] );
}

add_action( 'wp_ajax_cozy_merge_wishlist', 'cozy_merge_wishlist' );

function cozy_merge_wishlist() {
    check_ajax_referer( 'cozy_favorites', 'nonce' );

    $incoming = isset( $_POST['ids'] ) ? array_values( array_filter( array_map( 'absint', (array) $_POST['ids'] ) ) ) : [];
    if ( empty( $incoming ) ) {
        wp_send_json_success( [ 'count' => 0, 'added' => [] ] );
    }

    $user_id  = get_current_user_id();
    $existing = array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id, '_cozy_wishlist', true ) ) ) );
    $new_ids  = array_values( array_diff( $incoming, $existing ) );
    $merged   = array_values( array_unique( array_merge( $existing, $new_ids ) ) );

    update_user_meta( $user_id, '_cozy_wishlist', $merged );

    $added = [];
    foreach ( $new_ids as $id ) {
        if ( ! wc_get_product( $id ) ) continue;
        ob_start();
        cozy_render_favorite_item( $id );
        $added[] = [ 'id' => $id, 'html' => ob_get_clean() ];
    }

    wp_send_json_success( [ 'count' => count( $merged ), 'added' => $added ] );
}

function cozy_render_favorite_item( $product_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) return;
    ?>
    <div class="cozy-fav-item flex items-center gap-3 py-3 border-b border-cozy-sand" data-product-id="<?php echo absint( $product_id ); ?>">
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="shrink-0">
            <div class="w-16 h-16 rounded-xl overflow-hidden bg-cozy-cream flex items-center justify-center">
                <?php echo $product->get_image( 'thumbnail', [ 'class' => 'w-full h-full object-cover' ] ); // phpcs:ignore ?>
            </div>
        </a>
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-xs text-cozy-coffee line-clamp-2 mb-0.5">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="hover:text-cozy-mint transition-colors no-underline">
                    <?php echo esc_html( $product->get_name() ); ?>
                </a>
            </h4>
            <p class="text-xs text-cozy-coffee/60 m-0"><?php echo $product->get_price_html(); // phpcs:ignore ?></p>
        </div>
        <div class="flex flex-col items-center gap-1.5 shrink-0">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
               class="text-[10px] font-bold bg-cozy-mintLight text-cozy-mint px-2.5 py-1 rounded-lg hover:bg-cozy-mint hover:text-white transition-colors no-underline">
                Ver
            </a>
            <button type="button" data-action="toggle-favorite" data-product-id="<?php echo absint( $product_id ); ?>"
                    class="text-cozy-coffee/30 hover:text-red-400 transition-colors leading-none"
                    aria-label="Quitar de favoritos">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/*  WOOCOMMERCE CART FRAGMENTS                                          */
/* ------------------------------------------------------------------ */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
    if ( ! class_exists( 'WooCommerce' ) ) return $fragments;

    $count = WC()->cart->get_cart_contents_count();

    $fragments['#cart-badge'] = sprintf(
        '<span id="cart-badge" data-cart-value="%s" class="%sabsolute -top-1 -right-1 bg-cozy-mint text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm">%d</span>',
        esc_attr( WC()->cart->get_cart_contents_total() ),
        $count > 0 ? '' : 'hidden ',
        $count
    );

    $fragments['#cart-total'] = '<span id="cart-total" class="text-lg text-cozy-coffee">' . WC()->cart->get_cart_total() . '</span>';

    ob_start();
    cozy_render_mini_cart();
    $fragments['#cart-items'] = ob_get_clean();

    return $fragments;
} );

/* ------------------------------------------------------------------ */
/*  MINI CART RENDERER                                                  */
/* ------------------------------------------------------------------ */
function cozy_render_mini_cart() {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    ?>
    <div id="cart-items" class="p-6 overflow-y-auto flex-grow space-y-4">
    <?php if ( WC()->cart->is_empty() ) : ?>
        <div class="text-center py-12 space-y-4">
            <?php echo cozy_icon( 'box-open', '48', 'text-cozy-coffee/20 block' ); ?>
            <p class="text-sm text-cozy-coffee/60">Aún no hay tesoros en tu carrito.</p>
            <button type="button" data-action="close-cart" class="text-xs font-bold text-cozy-mint hover:underline">¡Empezar a explorar!</button>
        </div>
    <?php else :
        foreach ( WC()->cart->get_cart() as $key => $item ) :
            $product    = $item['data'];
            $name       = $product->get_name();
            $qty        = $item['quantity'];
            $price      = WC()->cart->get_product_price( $product );
            $remove_url = wc_get_cart_remove_url( $key );
            $thumb      = $product->get_image( 'thumbnail', [ 'class' => 'w-full h-full object-cover' ] );
        ?>
        <div class="flex items-center gap-3 py-3 border-b border-cozy-sand">
            <div class="w-16 h-16 rounded-xl overflow-hidden bg-cozy-cream shrink-0"><?php echo $thumb; ?></div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-xs text-cozy-coffee line-clamp-2"><?php echo esc_html( $name ); ?></h4>
                <p class="text-xs text-cozy-coffee/60 mt-0.5"><?php echo $price; ?> × <?php echo $qty; ?></p>
            </div>
            <a href="<?php echo esc_url( $remove_url ); ?>"
               class="shrink-0 text-cozy-coffee/40 hover:text-red-400 transition-colors ml-2"
               title="Eliminar">
                <?php echo cozy_icon( 'xmark', '14' ); ?>
            </a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/*  CUSTOM FOOTER                                                       */
/* ------------------------------------------------------------------ */
/* Resolves a legal page by slug; falls back to '#' until the page is created in WP Admin */
function cozy_fandom_legal_link( $slug ) {
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : '#';
}

function cozy_fandom_render_footer() {
    $shop_url    = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
    $account_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : home_url( '/' );
    $cart_url    = class_exists( 'WooCommerce' ) ? wc_get_cart_url() : home_url( '/' );
    $blog_page_id = get_option( 'page_for_posts' );
    $blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blog/' );

    $instagram_url = get_option( 'cozy_instagram_url', '' );
    $tiktok_url    = get_option( 'cozy_tiktok_url', '' );
    ?>
    <footer class="cozy-footer bg-cozy-coffee text-white/70 pt-14 pb-6 px-6 md:px-12 relative overflow-hidden">
        <div class="absolute top-0 left-1/4 w-72 h-72 bg-cozy-mint/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="max-w-7xl mx-auto relative z-10">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-white/10">

                <!-- Brand + social -->
                <div class="space-y-4">
                    <span class="font-serif text-xl font-bold text-white block">🌿 The Cozy Fandom</span>
                    <p class="text-xs text-white/60 leading-relaxed max-w-xs">
                        Coleccionables bonitos, papelería aesthetic y detalles con alma para un hogar relajado.
                    </p>
                    <?php if ( ( ! empty( $instagram_url ) && $instagram_url !== '#' ) || ( ! empty( $tiktok_url ) && $tiktok_url !== '#' ) ) : ?>
                    <div class="flex items-center gap-3 pt-1">
                        <?php if ( ! empty( $instagram_url ) && $instagram_url !== '#' ) : ?>
                        <a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="Instagram"
                           class="w-10 h-10 rounded-full bg-white/10 hover:bg-cozy-mint flex items-center justify-center text-white hover:text-cozy-coffee transition-colors">
                            <?php echo cozy_icon( 'instagram', '16' ); ?>
                        </a>
                        <?php endif; ?>
                        <?php if ( ! empty( $tiktok_url ) && $tiktok_url !== '#' ) : ?>
                        <a href="<?php echo esc_url( $tiktok_url ); ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="TikTok"
                           class="w-10 h-10 rounded-full bg-white/10 hover:bg-cozy-mint flex items-center justify-center text-white hover:text-cozy-coffee transition-colors">
                            <?php echo cozy_icon( 'tiktok', '16' ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tienda -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Tienda</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-cozy-mint transition-colors">Inicio</a></li>
                        <li><a href="<?php echo esc_url( $shop_url ); ?>" class="hover:text-cozy-mint transition-colors">Boutique</a></li>
                        <li><a href="<?php echo esc_url( $blog_url ); ?>" class="hover:text-cozy-mint transition-colors">Blog</a></li>
                        <li><a href="<?php echo esc_url( $account_url ); ?>" class="hover:text-cozy-mint transition-colors">Mi cuenta</a></li>
                        <li><a href="<?php echo esc_url( $cart_url ); ?>" class="hover:text-cozy-mint transition-colors">Carrito</a></li>
                    </ul>
                </div>

                <!-- Legal / información -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Información</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?php echo esc_url( cozy_fandom_legal_link( 'envios-y-devoluciones' ) ); ?>" class="hover:text-cozy-mint transition-colors">Envíos y devoluciones</a></li>
                        <li><a href="<?php echo esc_url( cozy_fandom_legal_link( 'politica-de-privacidad' ) ); ?>" class="hover:text-cozy-mint transition-colors">Política de privacidad</a></li>
                        <li><a href="<?php echo esc_url( cozy_fandom_legal_link( 'politica-de-cookies' ) ); ?>" class="hover:text-cozy-mint transition-colors">Política de cookies</a></li>
                        <li><button type="button" data-action="open-cookie-settings" class="hover:text-cozy-mint transition-colors bg-transparent border-0 p-0 m-0 cursor-pointer text-left font-inherit">Cambiar mi decisión sobre cookies</button></li>
                        <li><a href="<?php echo esc_url( cozy_fandom_legal_link( 'terminos-y-condiciones' ) ); ?>" class="hover:text-cozy-mint transition-colors">Términos y condiciones</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/mapa-del-sitio/' ) ); ?>" class="hover:text-cozy-mint transition-colors">Mapa del sitio</a></li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Contacto</h4>
                    <p class="text-xs text-white/60 leading-relaxed mb-3">¿Tienes dudas? Te atendemos con un té calentito 🍵</p>
                    <a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="text-xs font-bold text-cozy-mint hover:underline break-all">
                        <?php echo esc_html( get_option( 'admin_email' ) ); ?>
                    </a>
                </div>

            </div>

            <div class="pt-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-white/40">
                <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> The Cozy Fandom. Todos los derechos reservados.</span>
                <span>Hecho con 🌿 para fans cozy</span>
            </div>

        </div>
    </footer>
    <?php
}
add_action( 'wp_footer', 'cozy_fandom_render_footer' );

/* ------------------------------------------------------------------ */
/*  HOME PRODUCT CARD (shared by "Nuevos" and "Top ventas" sections)   */
/* ------------------------------------------------------------------ */
function cozy_fandom_home_product_card( $product, $badge_label = '', $badge_icon = '', $rank = 0 ) {
    $cat_ids  = $product->get_category_ids();
    $cat_name = '';
    if ( ! empty( $cat_ids ) ) {
        $term = get_term( reset( $cat_ids ), 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            $cat_name = $term->name;
        }
    }
    ?>
    <div class="bg-white rounded-[24px] p-4 border border-cozy-sand shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
        <div>
            <!-- Product Image -->
            <div class="bg-white rounded-2xl h-56 flex items-center justify-center overflow-hidden mb-4 relative">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block w-full h-full no-underline">
                    <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); // phpcs:ignore ?>
                </a>
                <?php if ( $rank > 0 ) : ?>
                <div class="absolute top-3 left-3 w-8 h-8 bg-cozy-coffee rounded-[10px] flex items-center justify-center z-10 shadow-md">
                    <span class="font-sans text-xs font-bold text-white/80 leading-none"><?php echo absint( $rank ); ?></span>
                </div>
                <?php elseif ( $badge_label ) : ?>
                <span class="absolute top-3 left-3 bg-cozy-mint text-cozy-coffee text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    <?php echo esc_html( $badge_icon ); ?> <?php echo esc_html( $badge_label ); ?>
                </span>
                <?php endif; ?>
                <button type="button" data-action="toggle-favorite"
                        class="cozy-fav-btn cozy-fav-icon absolute bottom-3 right-3 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center text-cozy-coffee/40 hover:text-red-400 hover:bg-white shadow-sm"
                        data-product-id="<?php echo absint( $product->get_id() ); ?>"
                        data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
                        data-product-price="<?php echo esc_attr( $product->get_price() ); ?>"
                        aria-label="Guardar en favoritos">
                    <svg class="cozy-fav-heart" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
            </div>
            <!-- Category label -->
            <?php if ( $cat_name ) : ?>
            <span class="text-[10px] text-cozy-mint font-bold uppercase tracking-wider block mb-1"><?php echo esc_html( $cat_name ); ?></span>
            <?php endif; ?>
            <!-- Name -->
            <h3 class="font-bold text-sm text-cozy-coffee line-clamp-2">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="hover:text-cozy-mint transition-colors">
                    <?php echo esc_html( $product->get_name() ); ?>
                </a>
            </h3>
        </div>
        <!-- Price + Add to cart -->
        <div class="flex items-center justify-between pt-4 border-t border-cozy-sand mt-4 gap-1">
            <span class="text-base font-bold text-cozy-coffee"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <?php
            $is_ajax = $product->is_type( 'simple' ) && $product->is_in_stock() && $product->is_purchasable();
            ?>
            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
               <?php if ( $is_ajax ) : ?>
               data-product_id="<?php echo absint( $product->get_id() ); ?>"
               data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
               data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
               data-product-price="<?php echo esc_attr( $product->get_price() ); ?>"
               data-quantity="1"
               <?php endif; ?>
               class="<?php echo $product->is_in_stock() ? 'bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee' : 'bg-cozy-sand text-cozy-coffee/60 pointer-events-none'; ?> <?php echo $is_ajax ? 'add_to_cart_button ajax_add_to_cart' : ''; ?> p-2.5 px-4 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 min-w-0 overflow-hidden no-underline">
                <?php echo cozy_icon( $is_ajax ? 'basket-shopping' : ( $product->is_in_stock() ? 'eye' : 'ban' ), '14', 'shrink-0' ); ?>
                <span class="truncate"><?php echo $is_ajax ? 'Añadir al carrito' : ( $product->is_in_stock() ? 'Ver opciones' : 'Sin stock' ); ?></span>
            </a>
        </div>
    </div>
    <?php
}

/* ─── My Account menu tweaks ────────────────────────────────── */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    unset( $items['downloads'] );
    return $items;
} );

/* ─── Edit Account: hide currency plugin field + add Cerrar cuenta ─ */
add_action( 'woocommerce_edit_account_form_end', function () {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('p.form-row, .woocommerce-form-row, .form-row').forEach(function (row) {
            var lbl = row.querySelector('label');
            if (lbl && /currency/i.test(lbl.textContent || lbl.innerText || '')) {
                row.remove();
            }
        });
        document.querySelectorAll('[class*="currency"],[id*="currency"]').forEach(function (el) {
            var row = el.closest('p.form-row, .woocommerce-form-row, .form-row');
            if (row) { row.remove(); } else { el.remove(); }
        });
    });
    </script>
    <?php
} );

add_action( 'woocommerce_after_edit_account_form', function () {
    ?>
    <div style="margin-top:2rem;padding-top:1.25rem;border-top:1px solid #fecaca;">
        <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(74,63,53,.4);margin:0 0 .6rem;">Zona de peligro</p>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'delete-account' ) ); ?>"
           style="display:inline-flex;align-items:center;gap:.5rem;font-size:0.75rem;font-weight:600;color:#f87171;text-decoration:none;">
            <?php echo cozy_icon( 'user-xmark', '14' ); ?>
            Cerrar y eliminar mi cuenta
        </a>
        <p style="font-size:0.7rem;color:rgba(74,63,53,.4);margin:.4rem 0 0;">Te pediremos confirmación antes de borrar nada.</p>
    </div>
    <?php
} );

/* ─── Delete Account endpoint ────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_endpoint( 'delete-account', EP_ROOT | EP_PAGES );
} );

add_action( 'woocommerce_account_delete-account_endpoint', function () {
    if (
        isset( $_POST['cozy_delete_account'], $_POST['_wpnonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'cozy_delete_account' )
    ) {
        $user_id = get_current_user_id();
        if ( $user_id && ! user_can( $user_id, 'manage_options' ) ) {
            $password = isset( $_POST['cozy_confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['cozy_confirm_password'] ) ) : '';
            $user     = get_userdata( $user_id );

            if ( $user && wp_check_password( $password, $user->user_pass, $user->ID ) ) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user( $user_id );
                wp_logout();
                wp_safe_redirect( add_query_arg( 'cuenta_eliminada', '1', home_url( '/' ) ) );
                exit;
            } else {
                wp_safe_redirect( add_query_arg( 'error', 'password', wc_get_account_endpoint_url( 'delete-account' ) ) );
                exit;
            }
        }
    }
    wc_get_template( 'myaccount/delete-account.php' );
} );

/* ─── Rename flat-rate shipping label ───────────────────────── */
add_filter( 'woocommerce_shipping_rate_label', function ( $label, $method ) {
    if ( $method->get_method_id() === 'flat_rate' ) {
        $label = 'Gastos de envío';
    }
    return $label;
}, 10, 2 );

/* ─── Tracking number block on view-order page ──────────────── */
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
    $codigo  = $order->get_meta( 'numero_seguimiento', true );
    $enlace  = $order->get_meta( 'enlace_seguimiento', true );

    if ( empty( $codigo ) ) {
        return;
    }

    $has_link = ! empty( $enlace ) && filter_var( $enlace, FILTER_VALIDATE_URL );
    ?>
    <div style="margin-top:1.5rem;background:#FAFAF8;border:1px solid #EEE4D8;border-radius:20px;padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div style="width:40px;height:40px;background:#D4EDE1;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <?php echo cozy_icon( 'truck', '16', 'text-[#3A7D5A]' ); ?>
        </div>
        <div style="flex:1;min-width:0;">
            <p style="margin:0 0 .2rem;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(74,63,53,.45);">Número de seguimiento</p>
            <?php if ( $has_link ) : ?>
            <a href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener noreferrer"
               style="font-size:.95rem;font-weight:700;color:#3A7D5A;text-decoration:none;word-break:break-all;display:inline-flex;align-items:center;gap:.4rem;">
                <?php echo esc_html( $codigo ); ?>
                <?php echo cozy_icon( 'arrow-up-right-from-square', '10', 'opacity-70' ); ?>
            </a>
            <?php else : ?>
            <span style="font-size:.95rem;font-weight:700;color:#3A4A3A;word-break:break-all;"><?php echo esc_html( $codigo ); ?></span>
            <?php endif; ?>
        </div>
        <?php if ( $has_link ) : ?>
        <a href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener noreferrer"
           style="flex-shrink:0;background:#88c4b5;color:#3a3128;font-size:.75rem;font-weight:700;padding:.5rem 1.1rem;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
            <?php echo cozy_icon( 'magnifying-glass', '14' ); ?>
            Rastrear paquete
        </a>
        <?php endif; ?>
    </div>
    <?php
} );


/* ─── AJAX Product Search Suggestions ────────────────────────── */
add_action( 'wp_ajax_cozy_ajax_search',        'cozy_ajax_search' );
add_action( 'wp_ajax_nopriv_cozy_ajax_search', 'cozy_ajax_search' );

function cozy_ajax_search() {
    $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
    if ( strlen( $term ) < 2 ) {
        wp_send_json_success( [] );
        exit;
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_success( [] );
        exit;
    }

    $products = wc_get_products( [
        'status'     => 'publish',
        'limit'      => 6,
        's'          => $term,
        'visibility' => 'catalog',
    ] );

    $suggestions = [];
    foreach ( $products as $product ) {
        $suggestions[] = [
            'id'    => $product->get_id(),
            'title' => $product->get_name(),
            'url'   => $product->get_permalink(),
            'image' => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) ?: wc_placeholder_img_src(),
            'price' => $product->get_price_html(),
        ];
    }

    wp_send_json_success( $suggestions );
    exit;
}

/* ------------------------------------------------------------------ */
/*  TELÉFONO OBLIGATORIO — lo exige el transportista                    */
/* ------------------------------------------------------------------ */
/* Cubre: checkout clásico (shortcode), y las pantallas de dirección de
   facturación/envío en "Mi cuenta". El checkout por BLOQUES (Gutenberg)
   no lee estos filtros — ese se configura aparte, ver nota más abajo. */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['required'] = true;
        $fields['billing']['billing_phone']['label'] = 'Teléfono *';
    }
    if ( isset( $fields['shipping']['shipping_phone'] ) ) {
        $fields['shipping']['shipping_phone']['required'] = true;
        $fields['shipping']['shipping_phone']['label'] = 'Teléfono *';
    }
    return $fields;
}, 9999 );

add_filter( 'woocommerce_billing_fields', function ( $fields ) {
    if ( isset( $fields['billing_phone'] ) ) {
        $fields['billing_phone']['required'] = true;
    }
    return $fields;
}, 9999 );

add_filter( 'woocommerce_shipping_fields', function ( $fields ) {
    if ( ! isset( $fields['shipping_phone'] ) ) {
        $fields['shipping_phone'] = [
            'label'        => __( 'Teléfono', 'woocommerce' ),
            'required'     => true,
            'type'         => 'tel',
            'class'        => [ 'form-row-wide' ],
            'priority'     => 25,
            'validate'     => [ 'phone' ],
            'autocomplete' => 'tel',
        ];
    } else {
        $fields['shipping_phone']['required'] = true;
    }
    return $fields;
}, 9999 );

/* Validación en servidor: bloquea el pedido si falta el teléfono,
   sea cual sea el checkout usado (clásico shortcode o bloques Gutenberg).
   Los filtros de arriba solo cambian el HTML del formulario; esto es lo
   que realmente lo hace obligatorio a nivel de pedido. */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
    $billing_phone  = ! empty( $data['billing_phone'] ) ? trim( $data['billing_phone'] ) : '';
    $shipping_phone = ! empty( $data['shipping_phone'] ) ? trim( $data['shipping_phone'] ) : '';

    if ( empty( $billing_phone ) && empty( $shipping_phone ) ) {
        $errors->add( 'billing_phone', 'Por favor introduce un número de teléfono — es necesario para la entrega.' );
    }
}, 10, 2 );

add_action( 'woocommerce_store_api_checkout_update_order_from_request', function ( $order, $request ) {
    $phone = $order->get_billing_phone() ?: $order->get_shipping_phone();
    if ( ! $phone ) {
        throw new Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'cozy_phone_required',
            'Por favor introduce un número de teléfono — es necesario para la entrega.',
            400
        );
    }
    // Aseguramos que el teléfono quede guardado tanto en facturación como en envío para las agencias de transporte
    if ( ! $order->get_billing_phone() && $phone ) {
        $order->set_billing_phone( $phone );
    }
    if ( ! $order->get_shipping_phone() && $phone ) {
        $order->set_shipping_phone( $phone );
    }
}, 10, 2 );

/* ------------------------------------------------------------------ */
/*  ADMIN — filtro "Solo con 1 imagen" en el listado de Productos       */
/* ------------------------------------------------------------------ */
/* Detecta productos con imagen destacada pero sin ninguna imagen de
   galería (_product_image_gallery vacío o inexistente), útil para
   encontrar fichas incompletas tras una importación. */
add_action( 'restrict_manage_posts', function( $post_type ) {
    if ( 'product' !== $post_type ) return;
    $selected = isset( $_GET['cozy_single_image'] ) ? sanitize_text_field( wp_unslash( $_GET['cozy_single_image'] ) ) : '';
    ?>
    <select name="cozy_single_image">
        <option value=""><?php esc_html_e( 'Todas las imágenes', 'cozy-fandom-child' ); ?></option>
        <option value="1" <?php selected( $selected, '1' ); ?>><?php esc_html_e( 'Solo con 1 imagen', 'cozy-fandom-child' ); ?></option>
    </select>
    <?php
} );

add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( 'product' !== $query->get( 'post_type' ) ) return;
    if ( empty( $_GET['cozy_single_image'] ) ) return;

    $meta_query   = (array) $query->get( 'meta_query' );
    $meta_query[] = [
        'key'     => '_thumbnail_id',
        'compare' => 'EXISTS',
    ];
    $meta_query[] = [
        'relation' => 'OR',
        [
            'key'     => '_product_image_gallery',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key'     => '_product_image_gallery',
            'value'   => '',
            'compare' => '=',
        ],
    ];
    $query->set( 'meta_query', $meta_query );
} );

/* ------------------------------------------------------------------ */
/*  ADMIN — Filtro "Sin marca / Sin licencia" en listado de Productos  */
/* ------------------------------------------------------------------ */
/* 1. Añade la opción "— Sin marca —" dentro del desplegable de marcas
   existente en la pantalla de Productos de wp-admin. */
add_filter( 'wp_dropdown_cats', function( $output, $r ) {
    global $pagenow, $post_type;
    if ( ! is_admin() || 'edit.php' !== $pagenow || 'product' !== $post_type ) {
        return $output;
    }

    if ( isset( $r['taxonomy'] ) && 'product_brand' === $r['taxonomy'] ) {
        $selected = isset( $_GET['product_brand'] ) ? sanitize_text_field( wp_unslash( $_GET['product_brand'] ) ) : '';
        $is_selected = in_array( $selected, [ 'no_brand', 'cozy_no_brand', 'none', 'sin-marca' ], true );
        $no_brand_option = '<option value="no_brand"' . ( $is_selected ? ' selected="selected"' : '' ) . '>— Sin marca / Sin licencia —</option>';

        // Insertar justo después de la primera opción ("Filtrar por marca")
        $pos = strpos( $output, '</option>' );
        if ( false !== $pos ) {
            $output = substr_replace( $output, '</option>' . "\n\t" . $no_brand_option, $pos, 9 );
        }
    }
    return $output;
}, 10, 2 );

/* 2. Modifica la consulta para buscar productos sin ninguna marca asignada (NOT EXISTS). */
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( 'product' !== $query->get( 'post_type' ) ) return;

    $brand = isset( $_GET['product_brand'] ) ? sanitize_text_field( wp_unslash( $_GET['product_brand'] ) ) : '';
    if ( in_array( $brand, [ 'no_brand', 'cozy_no_brand', 'none', 'sin-marca' ], true ) ) {
        // Limpiar el query var para evitar que WP intente buscar un término con slug 'no_brand'
        unset( $query->query_vars['product_brand'], $query->query['product_brand'] );

        $tax_query = (array) $query->get( 'tax_query' );
        // Eliminar cualquier condición previa sobre product_brand
        $tax_query = array_filter( $tax_query, function( $clause ) {
            if ( is_array( $clause ) && isset( $clause['taxonomy'] ) && 'product_brand' === $clause['taxonomy'] ) {
                return false;
            }
            return true;
        } );

        // Añadir la condición NOT EXISTS para filtrar productos sin marca
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'operator' => 'NOT EXISTS',
        ];

        $query->set( 'tax_query', $tax_query );
    }
}, 5 );



