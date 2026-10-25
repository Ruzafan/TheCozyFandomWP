<?php
/**
 * GA4 "Datos de artículos" export
 *
 * wp-admin → Herramientas → Exportar a GA4 downloads a CSV that GA4's
 * Data Import (type "Datos de artículos" / Item data) accepts as-is.
 *
 * Join key is item_id = WooCommerce product ID, the same value every gtag
 * ecommerce event in functions.php / cozy-main.js sends. Variations get their
 * own row (variation ID, parent's brand/categories) so any ID GA receives
 * finds a match.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function() {
    add_management_page(
        'Exportar productos a GA4',
        'Exportar a GA4',
        'manage_woocommerce',
        'cozy-ga4-export',
        'cozy_ga4_export_page'
    );
} );

function cozy_ga4_export_page() {
    $url = wp_nonce_url( admin_url( 'admin-post.php?action=cozy_ga4_export' ), 'cozy_ga4_export' );
    ?>
    <div class="wrap">
        <h1>Exportar productos a Google Analytics 4</h1>
        <p>Descarga un CSV con todos los productos para importarlo en GA4 como <strong>Datos de artículos</strong>.</p>
        <p><a href="<?php echo esc_url( $url ); ?>" class="button button-primary">Descargar CSV</a></p>
        <h2>Cómo importarlo</h2>
        <ol>
            <li>GA4 → Administrar → Importación de datos → <em>Crear fuente de datos</em>.</li>
            <li>Tipo de datos: <strong>Datos de artículos</strong>. Sube el CSV.</li>
            <li>Clave de unión: <code>item_id</code> → ID de artículo. Asigna el resto de columnas con el mismo nombre (marca, categorías, nombre, variante).</li>
        </ol>
        <p>Cuando añadas o cambies productos, descarga el CSV de nuevo y vuelve a subirlo en la misma fuente de datos.</p>
    </div>
    <?php
}

add_action( 'admin_post_cozy_ga4_export', function() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'No tienes permiso para esto.', 403 );
    }
    check_admin_referer( 'cozy_ga4_export' );

    $columns = [ 'item_id', 'item_name', 'item_brand', 'item_category', 'item_category2', 'item_category3', 'item_category4', 'item_category5', 'item_variant' ];
    $rows    = [];

    $ids = wc_get_products( [
        'status' => [ 'publish', 'private' ],
        'limit'  => -1,
        'return' => 'ids',
    ] );

    foreach ( $ids as $id ) {
        $product = wc_get_product( $id );
        if ( ! $product ) continue;

        $brand = cozy_ga4_brand( $id );
        $cats  = cozy_ga4_category_path( $id );

        $rows[] = cozy_ga4_row( (string) $id, $product->get_name(), $brand, $cats, '' );

        if ( $product->is_type( 'variable' ) ) {
            foreach ( $product->get_children() as $child_id ) {
                $variation = wc_get_product( $child_id );
                if ( ! $variation ) continue;
                $variant = wc_get_formatted_variation( $variation, true, false, false );
                $rows[]  = cozy_ga4_row( (string) $child_id, $product->get_name(), $brand, $cats, $variant );
            }
        }
    }

    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="ga4-articulos-' . gmdate( 'Y-m-d' ) . '.csv"' );

    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, $columns );
    foreach ( $rows as $row ) {
        fputcsv( $out, $row );
    }
    fclose( $out );
    exit;
} );

function cozy_ga4_row( $id, $name, $brand, array $cats, $variant ) {
    $cats = array_pad( array_slice( $cats, 0, 5 ), 5, '' );
    return array_merge( [ $id, cozy_ga4_clean( $name ), cozy_ga4_clean( $brand ) ], array_map( 'cozy_ga4_clean', $cats ), [ cozy_ga4_clean( $variant ) ] );
}

/* GA4 rejects line breaks inside cells; also strip tags/entities from names. */
function cozy_ga4_clean( $value ) {
    $value = wp_strip_all_tags( html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' ) );
    return trim( preg_replace( '/\s+/', ' ', $value ) );
}

function cozy_ga4_brand( $product_id ) {
    if ( ! taxonomy_exists( 'product_brand' ) ) return '';
    $terms = get_the_terms( $product_id, 'product_brand' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return '';
    return $terms[0]->name;
}

/**
 * Category hierarchy top → bottom, for item_category … item_category5.
 * Uses Yoast's primary category when set, else the deepest assigned one,
 * skipping "Uncategorized".
 */
function cozy_ga4_category_path( $product_id ) {
    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return [];

    $default = (int) get_option( 'default_product_cat' );
    $terms   = array_values( array_filter( $terms, function( $t ) use ( $default ) {
        return (int) $t->term_id !== $default && 'uncategorized' !== $t->slug;
    } ) );
    if ( empty( $terms ) ) return [];

    $chosen  = null;
    $primary = (int) get_post_meta( $product_id, '_yoast_wpseo_primary_product_cat', true );
    foreach ( $terms as $t ) {
        if ( $primary && (int) $t->term_id === $primary ) { $chosen = $t; break; }
    }
    if ( ! $chosen ) {
        $depth = -1;
        foreach ( $terms as $t ) {
            $d = count( get_ancestors( $t->term_id, 'product_cat', 'taxonomy' ) );
            if ( $d > $depth ) { $depth = $d; $chosen = $t; }
        }
    }

    $path = array_reverse( get_ancestors( $chosen->term_id, 'product_cat', 'taxonomy' ) );
    $path[] = $chosen->term_id;
    return array_map( function( $tid ) {
        $term = get_term( $tid, 'product_cat' );
        return ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
    }, $path );
}
