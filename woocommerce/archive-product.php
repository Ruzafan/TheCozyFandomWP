<?php
/**
 * Shop Archive – Cozy Fandom Design
 * Template override: woocommerce/archive-product.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

// Pull top-level product categories (exclude WooCommerce's "Uncategorised" default)
$uncategorised_id = absint( get_option( 'default_product_cat' ) );
$categories = get_terms( [
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
    'parent'     => 0,
    'orderby'    => 'count',
    'order'      => 'DESC',
] );
$current_cat = is_product_category() ? get_queried_object() : null;

do_action( 'woocommerce_before_main_content' );
?>

<!-- ============================================================ -->
<!-- PRODUCTS                                                       -->
<!-- ============================================================ -->
<?php
wc_setup_loop();

$cozy_widget_args = [
    'before_widget' => '<div class="cozy-filter-widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="cozy-filter-widget__title">',
    'after_title'   => '</h3>',
];
// Detect any active filters for the mobile button indicator
$_cozy_has_filters = false;
foreach ( [ 'licencia', 'min_price', 'max_price', 'rating_filter', 'cat_filter' ] as $_fk ) {
    if ( ! empty( $_GET[ $_fk ] ) ) { $_cozy_has_filters = true; break; } // phpcs:ignore WordPress.Security.NonceVerification
}
if ( ! $_cozy_has_filters ) {
    foreach ( array_keys( $_GET ) as $_fk ) {
        if ( strncmp( $_fk, 'filter_', 7 ) === 0 ) { $_cozy_has_filters = true; break; } // phpcs:ignore WordPress.Security.NonceVerification
    }
}
$_cozy_clear_url = get_permalink( wc_get_page_id( 'shop' ) );

// Plain shop root with nothing filtered/sorted/searched yet -> show the category picker instead of products.
$cozy_show_category_grid = is_shop() && ! is_search() && ! $_cozy_has_filters && empty( $_GET['orderby'] ); // phpcs:ignore WordPress.Security.NonceVerification
?>

<div class="cozy-shop-layout px-3 py-4 sm:p-6 md:p-8">

<?php if ( $cozy_show_category_grid ) : ?>

    <!-- ==================================================== -->
    <!-- CATEGORY PICKER (shop root, nothing filtered yet)     -->
    <!-- ==================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-[15px] gap-y-[15px]">
        <?php foreach ( $categories as $cozy_cat ) :
            $cozy_cat_thumb_id = get_term_meta( $cozy_cat->term_id, 'thumbnail_id', true );
            $cozy_cat_image    = $cozy_cat_thumb_id ? wp_get_attachment_image_url( $cozy_cat_thumb_id, 'large' ) : wc_placeholder_img_src( 'large' );
        ?>
        <a href="<?php echo esc_url( get_term_link( $cozy_cat ) ); ?>"
           class="cozy-cat-picker-card group block bg-white rounded-[24px] overflow-hidden border border-cozy-sand shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 no-underline">
            <div class="bg-cozy-cream h-56 sm:h-64 lg:h-72 overflow-hidden">
                <img src="<?php echo esc_url( $cozy_cat_image ); ?>" alt="<?php echo esc_attr( $cozy_cat->name ); ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
            </div>
            <div class="p-4 sm:p-5 text-center">
                <h3 class="text-lg sm:text-lg font-bold text-cozy-coffee m-0"><?php echo esc_html( $cozy_cat->name ); ?></h3>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

<?php else : ?>

    <div class="cozy-sort-bar flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <?php woocommerce_result_count(); ?>
            <?php if ( $_cozy_has_filters ) : ?>
            <a href="<?php echo esc_url( $_cozy_clear_url ); ?>"
               class="text-xs font-bold text-cozy-mint hover:text-cozy-mintDark transition-colors no-underline">
                Limpiar filtros
            </a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openFilters()" aria-controls="cozy-shop-filters" aria-expanded="false"
                    class="cozy-filter-btn py-2 px-4 rounded-xl border border-cozy-sand bg-white text-xs font-bold text-cozy-coffee hover:bg-cozy-mintLight hover:border-cozy-mint flex items-center gap-2 transition-all cursor-pointer">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                Filtrar <?php if ( $_cozy_has_filters ) : ?><span class="w-1.5 h-1.5 rounded-full bg-cozy-mint inline-block"></span><?php endif; ?>
            </button>
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <!-- Filter Overlay -->
    <div id="cozy-filters-overlay"
         class="hidden fixed inset-0 bg-cozy-coffee/30 z-[1000] backdrop-blur-sm"
         onclick="closeFilters()" aria-hidden="true"></div>

    <!-- ==================================================== -->
    <!-- FILTERS DRAWER PANEL                                 -->
    <!-- ==================================================== -->
    <aside class="cozy-shop-filters fixed top-0 left-0 h-full w-full max-w-xs sm:max-w-sm bg-white z-[1001] flex flex-col shadow-2xl transition-transform duration-300 -translate-x-full" id="cozy-shop-filters">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-6 border-b border-cozy-sand shrink-0">
            <h2 class="font-serif text-xl font-bold text-cozy-coffee m-0">Filtros</h2>
            <button onclick="closeFilters()"
                    class="w-9 h-9 rounded-full bg-cozy-cream hover:bg-cozy-sand flex items-center justify-center text-cozy-coffee transition-colors border-0 cursor-pointer"
                    aria-label="Cerrar filtros">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Scrollable Widgets Container -->
        <div class="flex-grow overflow-y-auto p-6 space-y-6">

        <?php
        the_widget( 'WC_Widget_Layered_Nav_Filters', [], [
            'before_widget' => '<div class="cozy-filter-widget cozy-active-filters">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="cozy-filter-widget__title">',
            'after_title'   => '</h3>',
        ] );

        // Subcategory filter — only shown while browsing inside a category
        if ( $current_cat ) :
            $sub_cats = get_terms( [
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'parent'     => $current_cat->term_id,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ] );
            if ( ! is_wp_error( $sub_cats ) && ! empty( $sub_cats ) ) :
                $raw_cats      = sanitize_text_field( wp_unslash( $_GET['cat_filter'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $selected_cats = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw_cats ) ) ) );
                $cat_base_url  = remove_query_arg( [ 'cat_filter', 'paged' ] );
                ?>
                <div class="cozy-filter-widget">
                    <h3 class="cozy-filter-widget__title">Subcategorías</h3>
                    <ul class="cozy-license-list">
                        <?php foreach ( $sub_cats as $scat ) :
                            $checked = in_array( $scat->slug, $selected_cats, true );
                            $new_sel = $checked
                                ? array_values( array_diff( $selected_cats, [ $scat->slug ] ) )
                                : array_merge( $selected_cats, [ $scat->slug ] );
                            $href = $new_sel
                                ? add_query_arg( 'cat_filter', implode( ',', $new_sel ), $cat_base_url )
                                : $cat_base_url;
                        ?>
                        <li>
                            <a href="<?php echo esc_url( $href ); ?>"
                               class="cozy-license-link<?php echo $checked ? ' is-active' : ''; ?>">
                                <span class="cozy-license-box" aria-hidden="true">
                                    <?php if ( $checked ) : ?>
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                    <?php endif; ?>
                                </span>
                                <span class="cozy-license-name"><?php echo esc_html( $scat->name ); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif;
        endif;

        the_widget( 'WC_Widget_Price_Filter', [ 'title' => __( 'Precio', 'woocommerce' ) ], $cozy_widget_args );

        // Licencia filter — WooCommerce Brands (product_brand taxonomy)
        $all_licenses = get_terms( [ 'taxonomy' => 'product_brand', 'hide_empty' => false ] );
        if ( ! is_wp_error( $all_licenses ) && ! empty( $all_licenses ) ) :
            $raw_sel      = sanitize_text_field( wp_unslash( $_GET['licencia'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
            $selected     = array_filter( array_map( 'sanitize_title', explode( ',', $raw_sel ) ) );
            $base_url     = remove_query_arg( 'licencia' );
            ?>
            <div class="cozy-filter-widget">
                <h3 class="cozy-filter-widget__title">Licencia</h3>
                <ul class="cozy-license-list">
                    <?php foreach ( $all_licenses as $lic ) :
                        $slug    = $lic->slug;
                        $checked = in_array( $slug, $selected, true );
                        $new_sel = $checked
                            ? array_values( array_diff( $selected, [ $slug ] ) )
                            : array_merge( $selected, [ $slug ] );
                        $href = $new_sel
                            ? add_query_arg( 'licencia', implode( ',', $new_sel ), $base_url )
                            : $base_url;
                    ?>
                    <li>
                        <a href="<?php echo esc_url( $href ); ?>"
                           class="cozy-license-link<?php echo $checked ? ' is-active' : ''; ?>">
                            <span class="cozy-license-box" aria-hidden="true">
                                <?php if ( $checked ) : ?>
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                <?php endif; ?>
                            </span>
                            <span class="cozy-license-name"><?php echo esc_html( $lic->name ); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif;

        the_widget( 'WC_Widget_Rating_Filter', [ 'title' => __( 'Valoración', 'woocommerce' ) ], [
            'before_widget' => '<div class="cozy-filter-widget cozy-rating-filter">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="cozy-filter-widget__title">',
            'after_title'   => '</h3>',
        ] );
        foreach ( wc_get_attribute_taxonomies() as $tax ) {
            the_widget( 'WC_Widget_Layered_Nav', [
                'title'      => $tax->attribute_label,
                'attribute'  => $tax->attribute_name,
                'query_type' => 'and',
            ], $cozy_widget_args );
        }
        ?>
        </div>
    </aside>

    <!-- ==================================================== -->
    <!-- PRODUCT GRID                                           -->
    <!-- ==================================================== -->
    <div id="cozy-products-container" class="transition-opacity duration-300">
        <?php if ( woocommerce_product_loop() ) : ?>
            <?php woocommerce_product_loop_start(); ?>

            <?php while ( have_posts() ) : the_post(); ?>
                <?php wc_get_template_part( 'content', 'product' ); ?>
            <?php endwhile; ?>

            <?php woocommerce_product_loop_end(); ?>

            <?php do_action( 'woocommerce_after_shop_loop' ); ?>
        <?php else : ?>
            <?php do_action( 'woocommerce_no_products_found' ); ?>
        <?php endif; ?>
    </div>

<?php endif; ?>

</div>
<?php wc_reset_loop(); ?>
<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
get_footer( 'shop' );
