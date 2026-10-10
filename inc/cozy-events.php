<?php
/**
 * Cozy Fandom – Anonymous first-party event log
 *
 * Stores a lightweight, anonymous copy of the core funnel (page view, product
 * view, add/remove-to-cart, checkout steps, purchase) in our own database,
 * independent of GA4/GTM. This exists because GA4 only fires after the
 * visitor accepts the consent banner, and is blocked outright by ad-blockers
 * — so it structurally undercounts. This log is unconditional, first-party
 * and PHP-side, so it isn't affected by either.
 *
 * "Anonymous but still shows the flow": each visitor gets a session hash
 * derived from IP + User-Agent + a salt that rotates every day at midnight.
 * Events sharing a hash on the same day are the same visit, so we can
 * reconstruct that visit's path (view_item_list -> view_item -> add_to_cart
 * -> ...). The salt rotating daily means the hash can never be used to
 * follow the same visitor across two different days, and the raw IP is
 * never stored — only this one-way hash.
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/*  TABLE                                                               */
/* ------------------------------------------------------------------ */
define( 'COZY_EVENTS_TABLE_VERSION', '1.1' );

function cozy_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'cozy_events';
}

function cozy_events_install() {
    global $wpdb;
    $table           = cozy_events_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        session_hash CHAR(16) NOT NULL,
        event_type VARCHAR(32) NOT NULL,
        item_list_name VARCHAR(191) NULL,
        product_id BIGINT UNSIGNED NULL,
        value DECIMAL(10,2) NULL,
        page_path VARCHAR(191) NULL,
        referrer VARCHAR(191) NULL,
        utm_source VARCHAR(64) NULL,
        utm_medium VARCHAR(64) NULL,
        utm_campaign VARCHAR(100) NULL,
        device VARCHAR(8) NULL,
        detail VARCHAR(191) NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY session_day (session_hash, created_at),
        KEY event_type (event_type, created_at),
        KEY product_id (product_id, event_type)
    ) {$charset_collate};" );

    update_option( 'cozy_events_table_version', COZY_EVENTS_TABLE_VERSION );
}

add_action( 'after_switch_theme', 'cozy_events_install' );
add_action( 'init', function () {
    if ( get_option( 'cozy_events_table_version' ) !== COZY_EVENTS_TABLE_VERSION ) {
        cozy_events_install();
    }
} );

/* ------------------------------------------------------------------ */
/*  ANONYMOUS SESSION HASH — rotates daily, never stores the raw IP     */
/* ------------------------------------------------------------------ */
function cozy_events_session_hash() {
    static $hash = null;
    if ( null !== $hash ) return $hash;

    $salt_option = 'cozy_events_daily_salt';
    $today       = gmdate( 'Y-m-d' );
    $salt        = get_option( $salt_option );

    if ( ! is_array( $salt ) || $salt['date'] !== $today ) {
        $salt = [ 'date' => $today, 'value' => wp_generate_password( 32, false ) ];
        update_option( $salt_option, $salt, false );
    }

    $ip = cozy_events_client_ip();
    $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

    $hash = substr( hash( 'sha256', $ip . '|' . $ua . '|' . $salt['value'] ), 0, 16 );
    return $hash;
}

/* The site sits behind Hostinger's CDN, so REMOTE_ADDR can be an edge node
   (different per request) rather than the visitor — which would split one
   visit across several hashes. Prefer the forwarded client IP when present.
   It's only ever hashed, so a spoofed header gains nothing. */
function cozy_events_client_ip() {
    foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
        if ( empty( $_SERVER[ $key ] ) ) continue;
        $ip = trim( explode( ',', $_SERVER[ $key ] )[0] );
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) return $ip;
    }
    return '';
}

function cozy_events_is_bot() {
    $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ( '' === $ua ) return true;
    return (bool) preg_match( '/bot|crawl|spider|slurp|facebookexternalhit|headless|lighthouse|scrapy|curl|wget|python|java\/|go-http|okhttp|axios|node-fetch|httpclient|libwww/i', $ua );
}

function cozy_events_device() {
    $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ( preg_match( '/ipad|tablet|android(?!.*mobile)/i', $ua ) ) return 'tablet';
    if ( preg_match( '/mobi|iphone|ipod|android/i', $ua ) ) return 'mobile';
    return 'desktop';
}

/* Path only — the query string is dropped so nothing personal (search terms,
   order keys, emails in links) ends up in the log. */
function cozy_events_clean_path( $url ) {
    $path = wp_parse_url( (string) $url, PHP_URL_PATH );
    if ( ! $path ) return null;
    return mb_substr( sanitize_text_field( $path ), 0, 191 );
}

/* Page the event happened on: for AJAX calls (add/remove to cart) that's the
   referring page, for normal page loads it's the request itself. */
function cozy_events_current_path() {
    if ( wp_doing_ajax() || ! empty( $_GET['wc-ajax'] ) ) {
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
        if ( ! $referer || wp_parse_url( $referer, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) return null;
        return cozy_events_clean_path( $referer );
    }
    return cozy_events_clean_path( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
}

/* True once this session has sent at least one JS beacon — i.e. it's a real
   browser that rendered a page, not a client that only requests URLs. */
function cozy_events_session_has_beacon() {
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        'SELECT 1 FROM ' . cozy_events_table_name() . " WHERE session_hash = %s AND event_type IN ('page_view','view_item','view_item_list') LIMIT 1",
        cozy_events_session_hash()
    ) );
}

/* ------------------------------------------------------------------ */
/*  LOGGER                                                              */
/* ------------------------------------------------------------------ */
function cozy_log_event( $event_type, $args = [] ) {
    if ( is_admin() && ! wp_doing_ajax() ) return;                 // skip wp-admin screens
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) return; // skip the store owner's own traffic
    if ( cozy_events_is_bot() ) return;

    global $wpdb;
    $defaults = [
        'item_list_name' => null,
        'product_id'     => null,
        'value'          => null,
        'page_path'      => null,   // null = detect from the request, false = don't store
        'referrer'       => null,
        'utm_source'     => null,
        'utm_medium'     => null,
        'utm_campaign'   => null,
        'detail'         => null,
    ];
    $args = wp_parse_args( $args, $defaults );

    if ( null === $args['page_path'] ) {
        $args['page_path'] = cozy_events_current_path();
    } elseif ( false === $args['page_path'] ) {
        $args['page_path'] = null;
    }

    $wpdb->insert(
        cozy_events_table_name(),
        [
            'session_hash'   => cozy_events_session_hash(),
            'event_type'     => $event_type,
            'item_list_name' => $args['item_list_name'],
            'product_id'     => $args['product_id'],
            'value'          => $args['value'],
            'page_path'      => $args['page_path'],
            'referrer'       => $args['referrer'],
            'utm_source'     => $args['utm_source'],
            'utm_medium'     => $args['utm_medium'],
            'utm_campaign'   => $args['utm_campaign'],
            'device'         => cozy_events_device(),
            'detail'         => $args['detail'],
            'created_at'     => current_time( 'mysql', true ),
        ],
        [ '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );
}

/* ------------------------------------------------------------------ */
/*  HOOKS — mirror the GA4 funnel, but fire unconditionally             */
/* ------------------------------------------------------------------ */

/* page_view / view_item_list / view_item — these pages are served from
   LiteSpeed's full-page cache, so PHP never runs on a cache hit. They're
   logged by a same-origin beacon instead (the JS runs on every view, cached
   or not). Transactional events below (cart/checkout/purchase) are dynamic
   pages/AJAX and stay server-side. */
add_action( 'wp_footer', function () {
    if ( is_admin() || is_feed() || is_robots() ) return;
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) return; // the REST beacon can't tell who's an admin, so skip here

    $context = [ 'type' => 'page_view' ];

    if ( function_exists( 'is_product' ) && is_product() ) {
        $context = [ 'type' => 'view_item', 'product_id' => get_queried_object_id() ];
    } elseif ( is_front_page() ) {
        // The home shows product cards with add-to-cart buttons, so it's a list too.
        $context = [ 'type' => 'view_item_list', 'list_name' => 'Inicio' ];
    } elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() || is_tax( 'product_brand' ) || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) ) {
        $context = [
            'type'      => 'view_item_list',
            'list_name' => ( is_product_category() || is_tax( 'product_brand' ) ) ? single_term_title( '', false ) : ( is_search() ? 'Resultados de búsqueda' : 'Tienda' ),
        ];
    }
    ?>
    <script>
    (function () {
        var url = <?php echo wp_json_encode( esc_url_raw( rest_url( 'cozy/v1/event' ) ) ); ?>;
        /* Also used by cozy-main.js for client-only events (cart drawer). */
        window.cozyLogEvent = function (type, extra) {
            var data = extra || {};
            data.type = type;
            data.path = location.pathname;
            // Only external referrers say where the visit came from.
            if (document.referrer && document.referrer.indexOf(location.origin) !== 0) data.ref = document.referrer;
            try {
                var q = new URLSearchParams(location.search);
                ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) {
                    if (q.get(k)) data[k] = q.get(k);
                });
                if (!data.utm_source && q.get('gclid'))  { data.utm_source = 'google';   data.utm_medium = 'cpc'; }
                if (!data.utm_source && q.get('fbclid')) { data.utm_source = 'facebook'; }
            } catch (e) {}
            var payload = JSON.stringify(data);
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, new Blob([payload], { type: 'application/json' }));
            } else {
                fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: payload, keepalive: true });
            }
        };
        var context = <?php echo wp_json_encode( $context ); ?>;
        var type = context.type;
        delete context.type;
        window.cozyLogEvent(type, context);
    })();
    </script>
    <?php
}, 30 );

add_action( 'rest_api_init', function () {
    register_rest_route( 'cozy/v1', '/event', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => function ( WP_REST_Request $request ) {
            if ( cozy_events_is_bot() ) {
                return new WP_REST_Response( null, 204 );
            }

            $body = json_decode( $request->get_body(), true );
            $type = is_array( $body ) ? ( $body['type'] ?? '' ) : '';
            if ( ! in_array( $type, [ 'page_view', 'view_item', 'view_item_list', 'view_cart' ], true ) ) {
                return new WP_REST_Response( null, 400 );
            }

            // Flood guard: cap events per anonymous session per minute so the
            // open endpoint can't be used to bloat the table. Counted straight
            // from the table (covered by the session_day index) rather than a
            // per-session transient, which meant two wp_options writes on every
            // page view when there's no persistent object cache.
            global $wpdb;
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . cozy_events_table_name() . ' WHERE session_hash = %s AND created_at >= %s',
                cozy_events_session_hash(),
                gmdate( 'Y-m-d H:i:s', time() - MINUTE_IN_SECONDS )
            ) );
            if ( $count >= 60 ) {
                return new WP_REST_Response( null, 429 );
            }

            $args = [ 'page_path' => cozy_events_clean_path( $body['path'] ?? '' ) ?: false ];

            // Where the visit came from: external referrer host + campaign tags.
            $ref_host = strtolower( (string) wp_parse_url( (string) ( $body['ref'] ?? '' ), PHP_URL_HOST ) );
            $ref_host = preg_replace( '/^www\./', '', $ref_host );
            $own_host = preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
            if ( $ref_host && $ref_host !== $own_host ) {
                $args['referrer'] = mb_substr( sanitize_text_field( $ref_host ), 0, 191 );
            }
            foreach ( [ 'utm_source' => 64, 'utm_medium' => 64, 'utm_campaign' => 100 ] as $key => $max ) {
                if ( ! empty( $body[ $key ] ) && is_string( $body[ $key ] ) ) {
                    $args[ $key ] = mb_substr( sanitize_text_field( $body[ $key ] ), 0, $max );
                }
            }

            /* view_cart from the cart drawer — the cart isn't loaded on REST
               requests, so the value comes from the page (same figure GA4 gets). */
            if ( 'view_cart' === $type ) {
                $args['detail'] = 'drawer';
                $args['value']  = min( max( (float) ( $body['value'] ?? 0 ), 0 ), 99999 );
            }
            if ( 'view_item' === $type ) {
                $product = wc_get_product( absint( $body['product_id'] ?? 0 ) );
                if ( ! $product ) return new WP_REST_Response( null, 400 );
                $args['product_id'] = $product->get_id();
                $args['value']      = (float) $product->get_price();
            }
            if ( 'view_item_list' === $type ) {
                $args['item_list_name'] = mb_substr( sanitize_text_field( $body['list_name'] ?? '' ), 0, 191 );
            }

            cozy_log_event( $type, $args );
            return new WP_REST_Response( null, 204 );
        },
    ] );
} );

/* add_to_cart — both the AJAX handler and the non-JS fallback */
add_action( 'woocommerce_add_to_cart', function ( $cart_item_key, $product_id, $quantity ) {
    /* The buttons are plain ?add-to-cart= links, which scrapers follow without
       ever rendering a page. A real visitor only reaches the link fallback
       after loading a page (and so sending a beacon); anything else is noise. */
    $is_link = ! wp_doing_ajax() && empty( $_GET['wc-ajax'] ) && isset( $_GET['add-to-cart'] )
        && 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? '' );
    if ( $is_link && ! cozy_events_session_has_beacon() ) return;

    $product = wc_get_product( $product_id );
    cozy_log_event( 'add_to_cart', [
        'product_id' => $product_id,
        'value'      => $product ? (float) $product->get_price() * $quantity : null,
    ] );
}, 10, 3 );

/* remove_from_cart */
add_action( 'woocommerce_cart_item_removed', function ( $cart_item_key, $cart ) {
    $item = $cart->removed_cart_contents[ $cart_item_key ] ?? null;
    if ( ! $item || empty( $item['data'] ) ) return;
    cozy_log_event( 'remove_from_cart', [
        'product_id' => $item['data']->get_id(),
        'value'      => (float) $item['data']->get_price() * $item['quantity'],
    ] );
}, 10, 2 );

/* view_cart */
add_action( 'wp_footer', function () {
    if ( ! function_exists( 'is_cart' ) || ! is_cart() || WC()->cart->is_empty() ) return;
    cozy_log_event( 'view_cart', [ 'value' => WC()->cart->get_cart_contents_total(), 'detail' => 'page' ] );
}, 20 );

/* begin_checkout / add_shipping_info / add_payment_info — checkout page load
   approximates begin_checkout; the two later steps aren't distinguishable
   server-side on a classic single-page checkout without a JS ping, so this
   log intentionally stops at begin_checkout for the anonymous copy. */
add_action( 'wp_footer', function () {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() || WC()->cart->is_empty() ) return;
    cozy_log_event( 'begin_checkout', [ 'value' => WC()->cart->get_cart_contents_total() ] );
}, 20 );

/* checkout_error — the visitor pressed "place order" and validation bounced
   them. Only the error codes are kept (e.g. billing_phone_required, terms),
   never what they typed. */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
    if ( ! is_wp_error( $errors ) || ! $errors->has_errors() ) return;
    cozy_log_event( 'checkout_error', [
        'value'  => WC()->cart ? WC()->cart->get_cart_contents_total() : null,
        'detail' => mb_substr( implode( ',', $errors->get_error_codes() ), 0, 191 ),
    ] );
}, 10, 2 );

/* place_order — order created, payment not yet confirmed. The gap between
   this and purchase is abandoned/failed payments. Classic and block checkout. */
function cozy_events_log_place_order( $order ) {
    if ( ! $order instanceof WC_Order ) return;
    cozy_log_event( 'place_order', [
        'value'  => (float) $order->get_total(),
        'detail' => mb_substr( (string) $order->get_payment_method(), 0, 191 ),
    ] );
}
add_action( 'woocommerce_checkout_order_processed', function ( $order_id, $posted_data, $order ) {
    cozy_events_log_place_order( $order );
}, 10, 3 );
add_action( 'woocommerce_store_api_checkout_order_processed', 'cozy_events_log_place_order' );

/* purchase */
add_action( 'woocommerce_thank_you', function ( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( ! $order || $order->get_meta( '_cozy_events_tracked' ) ) return;

    foreach ( $order->get_items() as $item ) {
        cozy_log_event( 'purchase', [
            'product_id' => $item->get_product_id(),
            'value'      => (float) $order->get_item_total( $item, false, false ) * $item->get_quantity(),
            'page_path'  => false, // the thank-you URL carries the order number
        ] );
    }
    $order->update_meta_data( '_cozy_events_tracked', 1 );
    $order->save();
}, 20 );

/* ------------------------------------------------------------------ */
/*  RETENTION — purge anything older than 13 months, weekly             */
/* ------------------------------------------------------------------ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'cozy_events_purge' ) ) {
        wp_schedule_event( time(), 'weekly', 'cozy_events_purge' );
    }
} );
add_action( 'cozy_events_purge', function () {
    global $wpdb;
    $table = cozy_events_table_name();
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", gmdate( 'Y-m-d H:i:s', strtotime( '-13 months' ) ) ) );
} );

/* ------------------------------------------------------------------ */
/*  ADMIN REPORT — wp-admin > Herramientas > Eventos Cozy                */
/* ------------------------------------------------------------------ */
add_action( 'admin_menu', function () {
    add_management_page( 'Eventos Cozy', 'Eventos Cozy', 'manage_options', 'cozy-events', 'cozy_events_render_report' );
} );

function cozy_events_render_report() {
    global $wpdb;
    $table = cozy_events_table_name();
    $days  = isset( $_GET['days'] ) ? max( 1, absint( $_GET['days'] ) ) : 30;
    $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

    // Funnel step counts (distinct sessions per step, so a step isn't inflated by repeat views)
    $steps = [ 'page_view', 'view_item_list', 'view_item', 'add_to_cart', 'view_cart', 'begin_checkout', 'place_order', 'purchase' ];
    $funnel = [];
    foreach ( $steps as $step ) {
        $funnel[ $step ] = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT session_hash) FROM {$table} WHERE event_type = %s AND created_at >= %s",
            $step, $since
        ) );
    }

    // Top products by view_item, with add_to_cart / purchase alongside for a quick ratio
    $top_products = $wpdb->get_results( $wpdb->prepare(
        "SELECT product_id,
                SUM(event_type = 'view_item')    AS views,
                SUM(event_type = 'add_to_cart')  AS carts,
                SUM(event_type = 'purchase')     AS purchases
         FROM {$table}
         WHERE created_at >= %s AND product_id IS NOT NULL
         GROUP BY product_id
         ORDER BY views DESC
         LIMIT 20",
        $since
    ) );

    // Visitor flow — most common (this step -> next step) transitions within the same session/day
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT session_hash, event_type, created_at FROM {$table} WHERE created_at >= %s ORDER BY session_hash, created_at",
        $since
    ) );
    $transitions = [];
    $prev_session = null;
    $prev_event   = null;
    foreach ( $rows as $row ) {
        if ( $row->session_hash !== $prev_session ) {
            $prev_event = null;
        }
        if ( null !== $prev_event ) {
            $key = $prev_event . ' → ' . $row->event_type;
            $transitions[ $key ] = ( $transitions[ $key ] ?? 0 ) + 1;
        }
        $prev_event   = $row->event_type;
        $prev_session = $row->session_hash;
    }
    arsort( $transitions );
    $transitions = array_slice( $transitions, 0, 15, true );

    echo '<div class="wrap"><h1>Eventos Cozy (anónimo, primera parte)</h1>';
    echo '<p>Copia interna del funnel, independiente de GA4 — no requiere que el visitante acepte cookies y no se ve afectada por bloqueadores de anuncios.</p>';

    echo '<form method="get"><input type="hidden" name="page" value="cozy-events">';
    echo '<label>Últimos días: <input type="number" name="days" value="' . esc_attr( $days ) . '" min="1" style="width:70px"></label> ';
    submit_button( 'Actualizar', 'secondary', '', false );
    echo '</form>';

    echo '<h2>Funnel (sesiones únicas por paso)</h2><table class="widefat striped" style="max-width:500px"><tbody>';
    foreach ( $funnel as $step => $count ) {
        echo '<tr><td>' . esc_html( $step ) . '</td><td><strong>' . esc_html( $count ) . '</strong></td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>Productos más vistos</h2><table class="widefat striped" style="max-width:700px">';
    echo '<thead><tr><th>Producto</th><th>Vistas</th><th>Al carrito</th><th>Comprados</th></tr></thead><tbody>';
    foreach ( $top_products as $p ) {
        $title = $p->product_id ? get_the_title( $p->product_id ) : '(desconocido)';
        echo '<tr><td>' . esc_html( $title ) . '</td><td>' . esc_html( $p->views ) . '</td><td>' . esc_html( $p->carts ) . '</td><td>' . esc_html( $p->purchases ) . '</td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>Flujo de navegación (transiciones más comunes)</h2><table class="widefat striped" style="max-width:500px"><tbody>';
    foreach ( $transitions as $key => $count ) {
        echo '<tr><td>' . esc_html( $key ) . '</td><td><strong>' . esc_html( $count ) . '</strong></td></tr>';
    }
    echo '</tbody></table></div>';
}
