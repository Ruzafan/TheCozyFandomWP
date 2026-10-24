<?php
/**
 * Shop incentives — trust and basket-size nudges
 *
 *  - Optional reduced-shipping tier ("2,99 € desde 30 €") messaging. WooCommerce
 *    can't charge a cart-total tier by itself (it needs a tiered-shipping
 *    plugin), so the copy only appears once it's switched on in
 *    WooCommerce → Ajustes → Envío → Opciones de envío. Showing "2,99 €" while
 *    checkout still charges 5,99 € would cost more trust than it earns.
 *  - Product page box under "Añadir al carrito": official-licence line,
 *    estimated delivery dates (computed in the browser so cached pages never
 *    show a stale date) and the shipping thresholds.
 *  - "Completa tu pedido" suggestions in the cart drawer, picked to close the
 *    gap to free shipping.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/*  Reduced-shipping tier settings                                     */
/* ------------------------------------------------------------------ */
add_filter( 'woocommerce_get_settings_shipping', function( $settings, $section ) {
    if ( 'options' !== $section ) {
        return $settings;
    }
    $settings[] = [
        'title' => 'Mensajes de envío reducido (tema Cozy Fandom)',
        'type'  => 'title',
        'desc'  => 'Solo cambia los textos de la web (barra superior, carrito y ficha de producto). El precio real lo cobran tus métodos de envío: configura antes la tarifa por tramos (p. ej. con un plugin como Flexible Shipping) y después activa esta casilla.',
        'id'    => 'cozy_reduced_shipping_section',
    ];
    $settings[] = [
        'title'   => 'Mostrar envío reducido',
        'desc'    => 'Anunciar la tarifa reducida en la web',
        'id'      => 'cozy_reduced_shipping_enabled',
        'type'    => 'checkbox',
        'default' => 'no',
    ];
    $settings[] = [
        'title'             => 'Desde (€)',
        'id'                => 'cozy_reduced_shipping_min',
        'type'              => 'number',
        'default'           => '30',
        'custom_attributes' => [ 'step' => '0.01', 'min' => '0' ],
    ];
    $settings[] = [
        'title'             => 'Precio del envío reducido (€)',
        'id'                => 'cozy_reduced_shipping_cost',
        'type'              => 'number',
        'default'           => '2.99',
        'custom_attributes' => [ 'step' => '0.01', 'min' => '0' ],
    ];
    $settings[] = [ 'type' => 'sectionend', 'id' => 'cozy_reduced_shipping_section' ];
    return $settings;
}, 10, 2 );

/* [ 'min' => 30.0, 'cost' => 2.99 ] when the tier is switched on and sits
   below the free-shipping threshold, otherwise null. */
function cozy_reduced_shipping() {
    if ( 'yes' !== get_option( 'cozy_reduced_shipping_enabled', 'no' ) ) {
        return null;
    }
    $min  = (float) get_option( 'cozy_reduced_shipping_min', 30 );
    $cost = (float) get_option( 'cozy_reduced_shipping_cost', 2.99 );
    return ( $min > 0 && $min < cozy_get_free_shipping_threshold() ) ? [ 'min' => $min, 'cost' => $cost ] : null;
}

// "60 €" / "2,99 €": no decimals for round amounts.
function cozy_price_short( $amount ) {
    return wc_price( $amount, [ 'decimals' => floor( $amount ) == $amount ? 0 : 2 ] );
}

/* Announcement-bar copy. */
function cozy_shipping_announcement_html() {
    $free    = cozy_get_free_shipping_threshold_html();
    $reduced = cozy_reduced_shipping();
    if ( $reduced ) {
        return sprintf(
            'Envío a solo <strong>%1$s</strong> desde %2$s y <strong>gratis</strong> desde %3$s',
            cozy_price_short( $reduced['cost'] ),
            cozy_price_short( $reduced['min'] ),
            $free
        );
    }
    return 'Envíos gratis en pedidos de más de <strong>' . $free . '</strong>';
}

/* Cart-drawer progress message for a given cart subtotal. */
function cozy_shipping_progress_html( $subtotal ) {
    $free    = cozy_get_free_shipping_threshold();
    $reduced = cozy_reduced_shipping();
    $strong  = '<strong class="text-cozy-mintDark">%s</strong>';

    if ( $subtotal >= $free ) {
        return '¡Enhorabuena! Tienes ' . sprintf( $strong, 'Envío GRATIS' );
    }
    if ( $reduced && $subtotal < $reduced['min'] ) {
        return sprintf(
            'Te faltan %1$s para envío a %2$s',
            sprintf( $strong, wc_price( $reduced['min'] - $subtotal ) ),
            sprintf( $strong, cozy_price_short( $reduced['cost'] ) )
        );
    }
    $prefix = $reduced ? sprintf( 'Envío a %s ✓ · ', cozy_price_short( $reduced['cost'] ) ) : '';
    return $prefix . sprintf(
        'Te faltan %1$s para %2$s',
        sprintf( $strong, wc_price( $free - $subtotal ) ),
        sprintf( $strong, 'Envío GRATIS' )
    );
}

/* ------------------------------------------------------------------ */
/*  Product page: licence + delivery + shipping box                    */
/* ------------------------------------------------------------------ */
add_action( 'woocommerce_single_product_summary', function() {
    global $product;
    if ( ! $product instanceof WC_Product ) {
        return;
    }
    $reduced = cozy_reduced_shipping();
    $row     = 'flex items-start gap-2.5 m-0';
    ?>
    <div class="cozy-product-assurance bg-white border border-cozy-sand rounded-2xl px-4 py-3.5 text-xs text-cozy-coffee/80 space-y-2.5">
        <p class="<?php echo esc_attr( $row ); ?>">
            <span class="shrink-0 text-cozy-mint"><?php echo cozy_icon( 'check', '14' ); ?></span>
            <span><strong class="text-cozy-coffee">Producto original</strong> con licencia oficial</span>
        </p>
        <?php if ( $product->is_in_stock() ) : ?>
        <p class="<?php echo esc_attr( $row ); ?>">
            <span class="shrink-0 text-cozy-mint"><?php echo cozy_icon( 'truck', '14' ); ?></span>
            <span data-cozy-delivery data-min-days="3" data-max-days="5">Recíbelo en <strong class="text-cozy-coffee">3-5 días laborables</strong> (España peninsular)</span>
        </p>
        <?php cozy_delivery_dates_script(); ?>
        <?php endif; ?>
        <p class="<?php echo esc_attr( $row ); ?>">
            <span class="shrink-0 text-cozy-mint"><?php echo cozy_icon( 'box', '14' ); ?></span>
            <span>
                <strong class="text-cozy-coffee">Envío gratis</strong> desde <?php echo wp_kses_post( cozy_get_free_shipping_threshold_html() ); ?>
                <?php if ( $reduced ) : ?>
                y solo <strong class="text-cozy-coffee"><?php echo wp_kses_post( cozy_price_short( $reduced['cost'] ) ); ?></strong> desde <?php echo wp_kses_post( cozy_price_short( $reduced['min'] ) ); ?>
                <?php endif; ?>
            </span>
        </p>
    </div>
    <?php
}, 35 ); // right after the add-to-cart form (30)

/* Estimated delivery dates, filled in the browser so cached pages never show a
   stale date. Inline right after the element, and flagged data-no-optimize /
   data-no-defer so LiteSpeed's "delay JS" doesn't hold it until the visitor
   first interacts (cozy-main.js is delayed, so the dates can't live there).
   Counts business days after today, skipping weekends and Spanish national
   holidays (policy: 3-5 días laborables desde la confirmación del pedido). */
function cozy_delivery_dates_script() {
    ?>
    <script data-no-optimize="1" data-no-defer="1">
    (function () {
        var H = ['01-01', '01-06', '05-01', '08-15', '10-12', '11-01', '12-06', '12-08', '12-25'];
        function biz(d) {
            var md = ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
            return d.getDay() !== 0 && d.getDay() !== 6 && H.indexOf(md) === -1;
        }
        function add(n) {
            var d = new Date(); d.setHours(0, 0, 0, 0);
            while (n > 0) { d.setDate(d.getDate() + 1); if (biz(d)) n--; }
            return d;
        }
        function fmt(d, month) {
            var o = { weekday: 'long', day: 'numeric' };
            if (month) o.month = 'long';
            return d.toLocaleDateString('es-ES', o).replace(',', '');
        }
        document.querySelectorAll('[data-cozy-delivery]').forEach(function (el) {
            var a = add(parseInt(el.getAttribute('data-min-days'), 10) || 3);
            var b = add(parseInt(el.getAttribute('data-max-days'), 10) || 5);
            el.innerHTML = 'Pídelo hoy y recíbelo entre el <strong class="text-cozy-coffee">' + fmt(a, a.getMonth() !== b.getMonth()) +
                '</strong> y el <strong class="text-cozy-coffee">' + fmt(b, true) + '</strong>';
        });
    })();
    </script>
    <?php
}

/* ------------------------------------------------------------------ */
/*  Cart drawer: "Completa tu pedido"                                  */
/* ------------------------------------------------------------------ */
/* Up to 3 products to suggest for the current cart:
   1. the one that best closes the gap to free shipping (same licence first),
   2. another from the same licence/category, closest to the gap,
   3. a cheap add-on ("detallito") under 7 €.
   Only simple, in-stock products (one-click AJAX add) not already in the cart. */
function cozy_cart_suggestions( $remaining, $limit = 3 ) {
    $cart_ids = [];
    $brands   = [];
    $cats     = [];
    foreach ( WC()->cart->get_cart() as $item ) {
        $pid        = $item['product_id'];
        $cart_ids[] = $pid;
        $brands     = array_merge( $brands, wp_get_post_terms( $pid, 'product_brand', [ 'fields' => 'ids' ] ) );
        $cats       = array_merge( $cats, wp_get_post_terms( $pid, 'product_cat', [ 'fields' => 'ids' ] ) );
    }
    $brands = array_unique( array_filter( $brands, 'is_int' ) );
    $cats   = array_unique( array_filter( $cats, 'is_int' ) );

    $base = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'post__not_in'   => $cart_ids,
        'meta_query'     => [ [ 'key' => '_stock_status', 'value' => 'instock' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
        'tax_query'      => [ [ 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'simple' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
    ];

    $related_ids = [];
    if ( $brands || $cats ) {
        $tax = [ 'relation' => 'OR' ];
        if ( $brands ) {
            $tax[] = [ 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => $brands ];
        }
        if ( $cats ) {
            $tax[] = [ 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $cats ];
        }
        $q              = $base;
        $q['tax_query'] = [ 'relation' => 'AND', $base['tax_query'][0], $tax ]; // phpcs:ignore WordPress.DB.SlowDBQuery
        $q['posts_per_page'] = 40;
        $related_ids = get_posts( $q );
    }

    $cheap_q                   = $base;
    $cheap_q['posts_per_page'] = 6;
    $cheap_q['meta_query'][]   = [ 'key' => '_price', 'value' => 7, 'type' => 'DECIMAL', 'compare' => '<=' ]; // phpcs:ignore WordPress.DB.SlowDBQuery
    $cheap_q['orderby']        = 'rand';
    $cheap_ids                 = get_posts( $cheap_q );

    $load = function( $ids ) {
        return array_values( array_filter( array_map( 'wc_get_product', $ids ), function( $p ) {
            return $p && $p->is_visible() && $p->is_purchasable() && $p->is_in_stock() && (float) $p->get_price() > 0;
        } ) );
    };
    $related = $load( $related_ids );
    $cheap   = $load( $cheap_ids );

    // Same licence ranks above same category.
    $same_brand = function( $p ) use ( $brands ) {
        return $brands && array_intersect( $brands, wp_get_post_terms( $p->get_id(), 'product_brand', [ 'fields' => 'ids' ] ) );
    };
    usort( $related, function( $a, $b ) use ( $same_brand, $remaining ) {
        $ba = $same_brand( $a ) ? 0 : 1;
        $bb = $same_brand( $b ) ? 0 : 1;
        if ( $ba !== $bb ) {
            return $ba - $bb;
        }
        // Prefer products that close the gap, cheapest first; then the priciest of the rest.
        $pa = (float) $a->get_price();
        $pb = (float) $b->get_price();
        $ca = $pa >= $remaining;
        $cb = $pb >= $remaining;
        if ( $ca !== $cb ) {
            return $ca ? -1 : 1;
        }
        return $ca ? $pa <=> $pb : $pb <=> $pa;
    } );

    $picked = [];
    foreach ( array_slice( $related, 0, 2 ) as $p ) {
        $picked[ $p->get_id() ] = $p;
    }
    foreach ( $cheap as $p ) {
        if ( count( $picked ) >= $limit ) {
            break;
        }
        $picked[ $p->get_id() ] = $p;
    }
    foreach ( $related as $p ) { // top up if the cheap pool was empty
        if ( count( $picked ) >= $limit ) {
            break;
        }
        $picked[ $p->get_id() ] = $p;
    }
    return array_slice( array_values( $picked ), 0, $limit );
}

function cozy_render_cart_suggestions( $subtotal ) {
    $free = cozy_get_free_shipping_threshold();
    if ( WC()->cart->is_empty() || $subtotal >= $free ) {
        return;
    }
    $remaining   = $free - $subtotal;
    $suggestions = cozy_cart_suggestions( $remaining );
    if ( ! $suggestions ) {
        return;
    }
    ?>
    <div class="cozy-cart-suggestions pt-2" data-ga-list-name="Carrito: Completa tu pedido">
        <p class="font-serif font-bold text-sm text-cozy-coffee m-0">Completa tu pedido</p>
        <p class="text-[11px] text-cozy-coffee/60 m-0 mb-3">Añade <?php echo wp_kses_post( wc_price( $remaining ) ); ?> más y el envío es gratis</p>
        <ul class="space-y-2.5 m-0 p-0 list-none">
            <?php foreach ( $suggestions as $p ) : ?>
            <li class="flex items-center gap-3 bg-cozy-cream/60 border border-cozy-sand rounded-xl p-2"
                data-ga-item-id="<?php echo absint( $p->get_id() ); ?>"
                data-ga-item-name="<?php echo esc_attr( $p->get_name() ); ?>"
                data-ga-item-price="<?php echo esc_attr( $p->get_price() ); ?>">
                <a href="<?php echo esc_url( $p->get_permalink() ); ?>" class="w-12 h-12 rounded-lg overflow-hidden bg-white shrink-0 block">
                    <?php echo $p->get_image( 'thumbnail', [ 'class' => 'w-full h-full object-contain', 'loading' => 'lazy' ] ); // phpcs:ignore ?>
                </a>
                <div class="flex-1 min-w-0">
                    <a href="<?php echo esc_url( $p->get_permalink() ); ?>" class="block font-bold text-[11px] text-cozy-coffee line-clamp-2 no-underline hover:text-cozy-mint"><?php echo esc_html( $p->get_name() ); ?></a>
                    <span class="text-[11px] text-cozy-coffee/60"><?php echo wp_kses_post( $p->get_price_html() ); ?></span>
                </div>
                <a href="<?php echo esc_url( $p->get_permalink() ); ?>" rel="nofollow"
                   data-product_id="<?php echo absint( $p->get_id() ); ?>"
                   data-product_sku="<?php echo esc_attr( $p->get_sku() ); ?>"
                   data-product-name="<?php echo esc_attr( $p->get_name() ); ?>"
                   data-product-price="<?php echo esc_attr( $p->get_price() ); ?>"
                   data-quantity="1"
                   aria-label="<?php echo esc_attr( 'Añadir al carrito: ' . $p->get_name() ); ?>"
                   class="add_to_cart_button ajax_add_to_cart shrink-0 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee text-[11px] font-bold px-3 py-1.5 rounded-full no-underline transition-colors flex items-center gap-1">
                    <?php echo cozy_icon( 'plus', '10' ); ?> Añadir
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
