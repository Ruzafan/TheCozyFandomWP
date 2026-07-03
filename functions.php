<?php
/**
 * Cozy Fandom Child Theme — functions & definitions
 */

require_once get_stylesheet_directory() . '/inc/cozy-icons.php';

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

// Filter shop queries when ?cat_filter=slug1,slug2 is in the URL (multi-select categories)
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( ! $q->is_main_query() || is_admin() ) return;
    if ( ! is_shop() ) return;

    $raw   = sanitize_text_field( wp_unslash( $_GET['cat_filter'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
    $slugs = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
    if ( empty( $slugs ) ) return;

    $tq   = (array) $q->get( 'tax_query' );
    $tq[] = [
        'taxonomy' => 'product_cat',
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

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [ 'parent-style' ],
        filemtime( get_stylesheet_directory() . '/style.css' )
    );

    wp_enqueue_style(
        'cozy-tailwind',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        [ 'child-style' ],
        filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_fandom_enqueue_styles' );

/* ------------------------------------------------------------------ */
/*  SCRIPTS                                                             */
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

    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-cart-fragments' );
    }
}
add_action( 'wp_enqueue_scripts', 'cozy_fandom_enqueue_scripts', 20 );

/* ------------------------------------------------------------------ */
/*  NEWSLETTER — Mailchimp API subscription                            */
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
        wp_send_json_error( [ 'message' => 'Newsletter no configurada.' ] );
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
        wp_send_json_error( [ 'message' => 'Error de conexión. Inténtalo de nuevo.' ] );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code === 200 || $code === 201 ) {
        wp_send_json_success();
    } else {
        $body   = json_decode( wp_remote_retrieve_body( $response ), true );
        wp_send_json_error( [ 'message' => $body['detail'] ?? 'Error al suscribir. Inténtalo de nuevo.' ] );
    }
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
            <button onclick="toggleFavorite(<?php echo absint( $product_id ); ?>)"
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
        '<span id="cart-badge" class="%sabsolute -top-1 -right-1 bg-cozy-mint text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm">%d</span>',
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
            <button onclick="closeCart()" class="text-xs font-bold text-cozy-mint hover:underline">¡Empezar a explorar!</button>
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
function cozy_fandom_home_product_card( $product, $badge_label = '', $badge_icon = '' ) {
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
            <div class="bg-cozy-cream rounded-2xl h-56 flex items-center justify-center overflow-hidden mb-4 relative">
                <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); // phpcs:ignore ?>
                <?php if ( $badge_label ) : ?>
                <span class="absolute top-3 left-3 bg-cozy-mint text-cozy-coffee text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    <?php echo esc_html( $badge_icon ); ?> <?php echo esc_html( $badge_label ); ?>
                </span>
                <?php endif; ?>
                <button onclick="toggleFavorite(<?php echo absint( $product->get_id() ); ?>)"
                        class="cozy-fav-btn cozy-fav-icon absolute bottom-3 right-3 w-8 h-8 bg-white/80 backdrop-blur-sm flex items-center justify-center text-cozy-coffee/40 hover:text-red-400 hover:bg-white shadow-sm"
                        data-product-id="<?php echo absint( $product->get_id() ); ?>"
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
               data-quantity="1"
               <?php endif; ?>
               class="<?php echo $product->is_in_stock() ? 'bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee hover:text-white' : 'bg-cozy-sand text-cozy-coffee/60 pointer-events-none'; ?> <?php echo $is_ajax ? 'add_to_cart_button ajax_add_to_cart' : ''; ?> p-2.5 px-4 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 min-w-0 overflow-hidden no-underline">
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


