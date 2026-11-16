<?php
/**
 * Cache warmer (re-warms after each deploy; purging stays manual).
 *
 * Why: with a few visits a day almost every visitor lands on a page whose
 * LiteSpeed cache entry has expired (1-week TTL) or was never built, so they
 * wait for a full PHP render (1–3.5 s on the shared server). LiteSpeed's own
 * crawler can't help: the host enforces a load limit (25) far below the
 * shared server's usual load, so it aborts. This walks the important URLs in
 * small batches from WP-Cron, so the cache is already built when a real
 * visitor arrives. Requests for pages already cached are answered by
 * LiteSpeed without PHP, so a pass is cheap.
 *
 * Also: git deploys don't purge LiteSpeed, so visitors kept seeing the old
 * HTML. Purge manually (LiteSpeed + hPanel CDN together), then this re-warms.
 *
 * WP-Cron only fires on visits; for a reliable schedule add a server cron in
 * hPanel: every 15 min → wget -q -O - "https://thecozyfandom.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

const COZY_WARM_HOOK       = 'cozy_cache_warm';
const COZY_WARM_BATCH      = 30;   // URLs per run
const COZY_WARM_MAX_SECS   = 45;   // stop a run early on a slow server
const COZY_WARM_USER_AGENT = 'CozyCacheWarmer/1.0 (+bot)';

add_filter( 'cron_schedules', function ( $s ) {
    $s['cozy_20min'] = [ 'interval' => 20 * MINUTE_IN_SECONDS, 'display' => 'Cada 20 minutos' ];
    return $s;
} );

add_action( 'init', function () {
    if ( ! wp_next_scheduled( COZY_WARM_HOOK ) ) {
        wp_schedule_event( time() + 300, 'cozy_20min', COZY_WARM_HOOK );
    }
} );

// wp_loaded, not init: LiteSpeed only hooks its litespeed_purge_all listener during init.
add_action( 'wp_loaded', 'cozy_warm_check_deploy' );

/* ------------------------------------------------------------------ */
/*  Purge once per deploy                                              */
/* ------------------------------------------------------------------ */
function cozy_warm_code_version() {
    $dir   = get_stylesheet_directory();
    $files = array_merge(
        [ $dir . '/functions.php', $dir . '/style.css', $dir . '/assets/css/main.min.css', $dir . '/assets/js/cozy-main.js' ],
        (array) glob( $dir . '/inc/*.php' ),
        (array) glob( $dir . '/*.php' ),
        (array) glob( $dir . '/woocommerce/*/*.php' )
    );
    $sig = '';
    foreach ( $files as $f ) {
        $sig .= $f . ':' . @filemtime( $f ) . ':' . @filesize( $f ) . '|';
    }
    return md5( $sig );
}

function cozy_warm_check_deploy() {
    // Stat the files at most once a minute (cached pages never reach PHP anyway).
    if ( get_transient( 'cozy_warm_deploy_checked' ) ) return;
    set_transient( 'cozy_warm_deploy_checked', 1, MINUTE_IN_SECONDS );

    $ver = cozy_warm_code_version();
    $old = get_option( 'cozy_deploy_version' );
    if ( $old === $ver ) return;

    update_option( 'cozy_deploy_version', $ver, false );

    // NO automatic purge here. litespeed_purge_all also deletes LiteSpeed's
    // combined CSS/JS files, while the Hostinger CDN (hcdn) keeps serving old
    // HTML that points at them → 404 JS on the live site (2026-11-16). After a
    // deploy, purge manually: LiteSpeed "Purge All" + hPanel CDN "Flush cache".
    delete_transient( 'cozy_warm_urls' );
    update_option( 'cozy_warm_offset', 0, false );
    if ( ! wp_next_scheduled( 'cozy_cache_warm_once' ) ) {
        wp_schedule_single_event( time() + 30, 'cozy_cache_warm_once' );
    }
}

/* ------------------------------------------------------------------ */
/*  URL list (most important first)                                    */
/* ------------------------------------------------------------------ */
function cozy_warm_urls() {
    $cached = get_transient( 'cozy_warm_urls' );
    if ( is_array( $cached ) ) return $cached;

    $urls = [ home_url( '/' ) ];
    if ( function_exists( 'wc_get_page_permalink' ) ) {
        $urls[] = wc_get_page_permalink( 'shop' );
    }

    foreach ( [ 'product_brand', 'product_cat' ] as $tax ) {
        if ( ! taxonomy_exists( $tax ) ) continue;
        $terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ] );
        if ( is_wp_error( $terms ) ) continue;
        foreach ( $terms as $t ) {
            if ( 'uncategorized' === $t->slug ) continue;
            $link = get_term_link( $t );
            if ( ! is_wp_error( $link ) ) $urls[] = $link;
        }
    }

    // Products: best sellers first, in stock only.
    $ids = get_posts( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 400,
        'fields'         => 'ids',
        'meta_key'       => 'total_sales',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => [ [ 'key' => '_stock_status', 'value' => 'instock' ] ],
        'no_found_rows'  => true,
    ] );
    foreach ( $ids as $id ) {
        $urls[] = get_permalink( $id );
    }

    foreach ( get_pages( [ 'post_status' => 'publish' ] ) as $page ) {
        $urls[] = get_permalink( $page );
    }
    $posts = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'fields' => 'ids' ] );
    foreach ( $posts as $id ) {
        $urls[] = get_permalink( $id );
    }

    // Never warm private/dynamic pages.
    $skip = array_filter( [
        function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
        function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '',
        function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '',
    ] );
    $urls = array_values( array_unique( array_filter( $urls, function ( $u ) use ( $skip ) {
        return $u && ! in_array( $u, $skip, true );
    } ) ) );

    set_transient( 'cozy_warm_urls', $urls, 6 * HOUR_IN_SECONDS );
    return $urls;
}

/* ------------------------------------------------------------------ */
/*  Runner                                                             */
/* ------------------------------------------------------------------ */
function cozy_warm_run( $batch = COZY_WARM_BATCH ) {
    if ( get_transient( 'cozy_warm_lock' ) ) return;
    set_transient( 'cozy_warm_lock', 1, 2 * MINUTE_IN_SECONDS );

    $urls   = cozy_warm_urls();
    $total  = count( $urls );
    $offset = (int) get_option( 'cozy_warm_offset', 0 );
    if ( $offset >= $total ) $offset = 0;

    $start  = microtime( true );
    $stats  = [ 'hit' => 0, 'miss' => 0, 'error' => 0 ];
    $done   = 0;

    while ( $done < $batch && $done < $total && ( microtime( true ) - $start ) < COZY_WARM_MAX_SECS ) {
        $url = $urls[ ( $offset + $done ) % $total ];
        $res = wp_remote_get( $url, [
            'timeout'     => 20,
            'redirection' => 2,
            'user-agent'  => COZY_WARM_USER_AGENT,
            'headers'     => [ 'Accept' => 'text/html', 'Accept-Encoding' => 'gzip' ],
            'cookies'     => [],
        ] );
        if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
            $stats['error']++;
        } elseif ( 'hit' === strtolower( (string) wp_remote_retrieve_header( $res, 'x-litespeed-cache' ) ) ) {
            $stats['hit']++;
        } else {
            $stats['miss']++;
        }
        $done++;
    }

    update_option( 'cozy_warm_offset', ( $offset + $done ) % max( 1, $total ), false );
    update_option( 'cozy_warm_last', [
        'time'  => time(),
        'urls'  => $done,
        'total' => $total,
        'secs'  => round( microtime( true ) - $start, 1 ),
    ] + $stats, false );
    delete_transient( 'cozy_warm_lock' );
}

add_action( COZY_WARM_HOOK, function () { cozy_warm_run(); } );
// Right after a deploy: rebuild the most important pages first (home, shop, licences…).
add_action( 'cozy_cache_warm_once', function () { cozy_warm_run( 60 ); } );

// Product / term changes purge their pages; queue a quick re-warm.
foreach ( [ 'save_post_product', 'edited_product_cat', 'edited_product_brand' ] as $hook ) {
    add_action( $hook, function () {
        delete_transient( 'cozy_warm_urls' );
        if ( ! wp_next_scheduled( 'cozy_cache_warm_once' ) ) {
            wp_schedule_single_event( time() + 120, 'cozy_cache_warm_once' );
        }
    } );
}
