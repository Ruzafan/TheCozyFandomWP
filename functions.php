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

/* ------------------------------------------------------------------ */
/*  STYLES                                                              */
/* ------------------------------------------------------------------ */
function cozy_fandom_enqueue_styles() {
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

    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-cart-fragments' );
    }
}
add_action( 'wp_enqueue_scripts', 'cozy_fandom_enqueue_scripts', 20 );

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
/*  NAVIGATION WALKER                                                   */
/* ------------------------------------------------------------------ */
class Cozy_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}
    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        $active = in_array( 'current-menu-item', (array) $data_object->classes, true ) ? ' text-cozy-mint' : '';
        $output .= '<a href="' . esc_url( $data_object->url ) . '"'
            . ' class="hover:text-cozy-mint transition-colors text-cozy-coffee font-medium text-sm' . $active . '">'
            . esc_html( $data_object->title )
            . '</a>';
    }
    public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}
}

function cozy_nav_fallback() {
    $shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/tienda' );
    $links = [
        [ home_url( '/' ),   'Inicio'        ],
        [ '#categorias',     'Categorías'    ],
        [ $shop_url,         'Boutique'      ],
        [ '#filosofia',      'Filosofía Cozy' ],
    ];
    foreach ( $links as [ $url, $label ] ) {
        echo '<a href="' . esc_url( $url ) . '" class="hover:text-cozy-mint transition-colors text-cozy-coffee font-medium text-sm">' . esc_html( $label ) . '</a>';
    }
}
