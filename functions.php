<?php
/**
 * Cozy Fandom Child Theme — functions & definitions
 */

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

/* ------------------------------------------------------------------ */
/*  LICENCIA — custom product taxonomy + URL-based shop filtering      */
/* ------------------------------------------------------------------ */
add_action( 'init', function () {
    register_taxonomy( 'product_licencia', 'product', [
        'label'             => 'Licencia',
        'public'            => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'licencia' ],
    ] );

    foreach ( [ 'Pokemon', 'Garfield', 'Snoopy', 'Disney', 'Pusheen' ] as $name ) {
        if ( ! get_term_by( 'name', $name, 'product_licencia' ) ) {
            wp_insert_term( $name, 'product_licencia' );
        }
    }
} );

// Filter shop/category queries when ?licencia=snoopy,disney is in the URL
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( ! $q->is_main_query() || is_admin() ) return;
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) return;

    $raw   = sanitize_text_field( wp_unslash( $_GET['licencia'] ?? '' ) );
    $slugs = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
    if ( empty( $slugs ) ) return;

    $tq   = (array) $q->get( 'tax_query' );
    $tq[] = [
        'taxonomy' => 'product_licencia',
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
/*  STYLES                                                              */
/* ------------------------------------------------------------------ */
function cozy_fandom_enqueue_styles() {
    wp_enqueue_style(
        'cozy-google-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'cozy-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        [],
        '6.4.0'
    );

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
        [ 'jquery' ],
        wp_get_theme()->get( 'Version' ),
        true
    );

    wp_localize_script( 'cozy-main', 'cozyAjax', [
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'cozy_newsletter' ),
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
add_action( 'wp_ajax_cozy_newsletter_subscribe',        'cozy_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_cozy_newsletter_subscribe', 'cozy_newsletter_subscribe' );

function cozy_newsletter_subscribe() {
    check_ajax_referer( 'cozy_newsletter', 'nonce' );

    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Por favor introduce un email válido.' ] );
    }

    // Mailchimp for WooCommerce stores the API key in this option
    $mc_options = get_option( 'mailchimp-woocommerce', [] );
    $api_key    = $mc_options['api_key'] ?? '';

    if ( ! $api_key ) {
        wp_send_json_error( [ 'message' => 'Newsletter no configurada.' ] );
    }

    // Data center is the suffix after the last dash (e.g. "us14")
    $dc      = substr( $api_key, strrpos( $api_key, '-' ) + 1 );
    $list_id = '667877ef18';
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
            <i class="fa-solid fa-box-open text-cozy-coffee/20 text-5xl block"></i>
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
                <i class="fa-solid fa-xmark text-sm"></i>
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

    /* Swap these for the real profile URLs once they're live */
    $instagram_url = '#';
    $tiktok_url     = '#';
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
                    <div class="flex items-center gap-3 pt-1">
                        <a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="Instagram"
                           class="w-10 h-10 rounded-full bg-white/10 hover:bg-cozy-mint flex items-center justify-center text-white hover:text-cozy-coffee transition-colors">
                            <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo esc_url( $tiktok_url ); ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="TikTok"
                           class="w-10 h-10 rounded-full bg-white/10 hover:bg-cozy-mint flex items-center justify-center text-white hover:text-cozy-coffee transition-colors">
                            <i class="fa-brands fa-tiktok" aria-hidden="true"></i>
                        </a>
                    </div>
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
                <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> The Cozy Fandom. Todos los derechos reservados.</span>
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
                <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); ?>
                <?php if ( $badge_label ) : ?>
                <span class="absolute top-3 left-3 bg-cozy-mint text-cozy-coffee text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    <?php echo esc_html( $badge_icon ); ?> <?php echo esc_html( $badge_label ); ?>
                </span>
                <?php endif; ?>
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
        <div class="flex items-center justify-between pt-4 border-t border-cozy-sand mt-4">
            <span class="text-base font-bold text-cozy-coffee"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
               class="<?php echo $product->is_in_stock() ? 'bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee hover:text-white' : 'bg-cozy-sand text-cozy-coffee/60'; ?> p-2.5 px-4 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-eye" aria-hidden="true"></i> Ver producto
            </a>
        </div>
    </div>
    <?php
}

