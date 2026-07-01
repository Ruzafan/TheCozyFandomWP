<?php
/**
 * Shop pagination — WooCommerce template override.
 * Shows prev/next SVG arrows + truncated page numbers (1 ... X [cur] Y ... N).
 */
defined( 'ABSPATH' ) || exit;

global $wp_query;
if ( $wp_query->max_num_pages <= 1 ) return;

$svg_prev = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
$svg_next = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
?>
<nav class="woocommerce-pagination cozy-pagination" aria-label="<?php esc_attr_e( 'Navegación de páginas', 'woocommerce' ); ?>">
<?php
echo paginate_links( apply_filters( 'woocommerce_pagination_args', [
    'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
    'format'    => '',
    'add_args'  => false,
    'current'   => max( 1, get_query_var( 'paged' ) ),
    'total'     => $wp_query->max_num_pages,
    'prev_text' => '<span class="screen-reader-text">' . __( 'Anterior', 'woocommerce' ) . '</span>' . $svg_prev,
    'next_text' => '<span class="screen-reader-text">' . __( 'Siguiente', 'woocommerce' ) . '</span>' . $svg_next,
    'type'      => 'list',
    'end_size'  => 1,
    'mid_size'  => 1,
] ) );
?>
</nav>
