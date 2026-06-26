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

// Pull product categories (exclude WooCommerce's "Uncategorised" default)
$uncategorised_id = absint( get_option( 'default_product_cat' ) );
$categories = get_terms( [
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
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
?>

<div class="cozy-shop-layout px-3 py-4 sm:p-6 md:p-8">

    <!-- ==================================================== -->
    <!-- HERO BANNER                                          -->
    <!-- ==================================================== -->
    <?php
    $hero_title = 'Tu Fandom Favorito, en su versión más Cozy';
    $hero_desc  = 'Explora nuestra colección de papelería bonita, escritorios temáticos, figuras sorpresa (blindboxes) y merchandising oficial de tus licencias preferidas. Todo seleccionado para llenar tu rincón de calidez.';
    $hero_badge = '🌿 Geek Boutique Oficial';
    $hero_emoji = '📚✨';
    
    if ( is_product_category() ) {
        $cat_obj    = get_queried_object();
        $hero_title = single_term_title( '', false );
        $hero_desc  = category_description();
        if ( ! $hero_desc ) {
            $hero_desc = sprintf( 'Explora todos los productos de la categoría %s en nuestra tienda geek.', $hero_title );
        }
        $hero_badge = '📁 Categoría de Tienda';
        $hero_emoji = '🎁✨';
    } elseif ( is_search() ) {
        $hero_title = sprintf( 'Resultados para: "%s"', get_search_query() );
        $hero_desc  = 'Aquí tienes todos los productos que coinciden con tu búsqueda. ¡Esperamos que encuentres tu próximo favorito!';
        $hero_badge = '🔍 Buscador Cozy';
        $hero_emoji = '🔎✨';
    }
    ?>
    <div class="cozy-shop-hero bg-gradient-to-br from-white to-[#F9F5EE] border border-cozy-sand rounded-[32px] p-6 md:p-10 mb-8 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden" id="cozy-shop-hero">
        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-[#EAF6F3] opacity-60 filter blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-[#F5EDE0] opacity-50 filter blur-3xl pointer-events-none"></div>
        
        <div class="space-y-3 max-w-2xl relative z-10 text-center md:text-left">
            <span class="inline-block text-[10px] uppercase tracking-widest font-bold text-cozy-mint bg-cozy-mintLight px-3 py-1 rounded-full"><?php echo esc_html( $hero_badge ); ?></span>
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-cozy-coffee leading-tight m-0"><?php echo esc_html( $hero_title ); ?></h1>
            <p class="text-xs sm:text-sm text-cozy-coffee/70 leading-relaxed m-0">
                <?php echo wp_kses_post( $hero_desc ); ?>
            </p>
        </div>
        
        <div class="hidden md:flex items-center justify-center shrink-0 w-24 h-24 bg-white rounded-3xl border border-cozy-sand/50 shadow-inner relative z-10 rotate-3 hover:rotate-0 transition-transform duration-300">
            <span class="text-3xl"><?php echo esc_html( $hero_emoji ); ?></span>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- CATEGORY CAROUSEL                                    -->
    <!-- ==================================================== -->
    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
        <div class="relative mb-8">
            <div class="cozy-cat-carousel" id="cozy-cat-carousel">
                <?php
                $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
                $is_all_active = ! $current_cat && empty( $_GET['cat_filter'] );
                ?>
                <a href="<?php echo esc_url( $shop_url ); ?>" 
                   class="cozy-cat-card<?php echo $is_all_active ? ' cozy-cat-card--active' : ''; ?>">
                    <div class="cozy-cat-card__body">
                        <span class="cozy-cat-card__name">✨ Ver Todo</span>
                        <span class="cozy-cat-card__count">Todos los productos</span>
                    </div>
                </a>
                
                <?php foreach ( $categories as $cat ) :
                    $is_active = false;
                    if ( $current_cat && $current_cat->term_id === $cat->term_id ) {
                        $is_active = true;
                    } else {
                        $raw_cats = sanitize_text_field( wp_unslash( $_GET['cat_filter'] ?? '' ) );
                        $selected_cats = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw_cats ) ) ) );
                        if ( in_array( $cat->slug, $selected_cats, true ) ) {
                            $is_active = true;
                        }
                    }
                    
                    $cat_url = get_term_link( $cat );
                    if ( is_wp_error( $cat_url ) ) {
                        continue;
                    }
                ?>
                <a href="<?php echo esc_url( $cat_url ); ?>" 
                   class="cozy-cat-card<?php echo $is_active ? ' cozy-cat-card--active' : ''; ?>">
                    <div class="cozy-cat-card__body">
                        <span class="cozy-cat-card__name">🍃 <?php echo esc_html( $cat->name ); ?></span>
                        <span class="cozy-cat-card__count"><?php echo esc_html( $cat->count ); ?> productos</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

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

    <!-- ==================================================== -->
    <!-- FILTERS DROPDOWN PANEL                               -->
    <!-- ==================================================== -->
    <aside class="cozy-shop-filters hidden bg-white border border-cozy-sand rounded-[24px] p-6 mb-8 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="cozy-shop-filters">

        <?php
        the_widget( 'WC_Widget_Layered_Nav_Filters', [], [
            'before_widget' => '<div class="cozy-filter-widget cozy-active-filters">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="cozy-filter-widget__title">',
            'after_title'   => '</h3>',
        ] );

        // Multi-select category filter
        $top_cats = get_terms( [
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
            'parent'     => 0,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );
        if ( ! is_wp_error( $top_cats ) && ! empty( $top_cats ) ) :
            $current_cat_obj = is_product_category() ? get_queried_object() : null;
            $raw_cats        = sanitize_text_field( wp_unslash( $_GET['cat_filter'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
            $selected_cats   = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw_cats ) ) ) );
            // When on a category archive, treat that category as selected
            if ( $current_cat_obj ) {
                $selected_cats = array_values( array_unique( array_merge( [ $current_cat_obj->slug ], $selected_cats ) ) );
            }
            $cat_base_url = remove_query_arg( [ 'cat_filter', 'paged' ], get_permalink( wc_get_page_id( 'shop' ) ) );
            ?>
            <div class="cozy-filter-widget">
                <h3 class="cozy-filter-widget__title">Categorías</h3>
                <ul class="cozy-license-list">
                    <?php foreach ( $top_cats as $tcat ) :
                        $is_top_checked = in_array( $tcat->slug, $selected_cats, true );

                        $sub_cats = get_terms( [
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => $tcat->term_id,
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                        ] );
                        $has_sub = ! is_wp_error( $sub_cats ) && ! empty( $sub_cats );

                        $any_sub_checked = false;
                        if ( $has_sub ) {
                            foreach ( $sub_cats as $sc ) {
                                if ( in_array( $sc->slug, $selected_cats, true ) ) { $any_sub_checked = true; break; }
                            }
                        }
                        $is_expanded = $is_top_checked || $any_sub_checked;

                        $new_top_sel = $is_top_checked
                            ? array_values( array_diff( $selected_cats, [ $tcat->slug ] ) )
                            : array_merge( $selected_cats, [ $tcat->slug ] );
                        $top_href = $new_top_sel
                            ? add_query_arg( 'cat_filter', implode( ',', $new_top_sel ), $cat_base_url )
                            : $cat_base_url;
                    ?>
                    <li class="cozy-cat-filter-item<?php echo $has_sub ? ' cozy-cat-filter-item--has-children' : ''; ?>">
                        <div class="cozy-cat-filter-row">
                            <a href="<?php echo esc_url( $top_href ); ?>"
                               class="cozy-license-link flex-1<?php echo $is_top_checked ? ' is-active' : ''; ?>">
                                <span class="cozy-license-box" aria-hidden="true">
                                    <?php if ( $is_top_checked ) : ?>
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                    <?php endif; ?>
                                </span>
                                <span class="cozy-license-name"><?php echo esc_html( $tcat->name ); ?></span>
                            </a>
                            <?php if ( $has_sub ) : ?>
                            <button class="cozy-cat-filter-toggle<?php echo $is_expanded ? ' is-open' : ''; ?>"
                                    aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
                                    aria-label="Expandir <?php echo esc_attr( $tcat->name ); ?>"
                                    onclick="cozyCatToggle(this)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php if ( $has_sub ) : ?>
                        <ul class="cozy-cat-filter-children<?php echo $is_expanded ? ' is-open' : ''; ?>">
                            <?php foreach ( $sub_cats as $scat ) :
                                $is_scat_checked = in_array( $scat->slug, $selected_cats, true );
                                $new_scat_sel    = $is_scat_checked
                                    ? array_values( array_diff( $selected_cats, [ $scat->slug ] ) )
                                    : array_merge( $selected_cats, [ $scat->slug ] );
                                $scat_href = $new_scat_sel
                                    ? add_query_arg( 'cat_filter', implode( ',', $new_scat_sel ), $cat_base_url )
                                    : $cat_base_url;
                            ?>
                            <li>
                                <a href="<?php echo esc_url( $scat_href ); ?>"
                                   class="cozy-license-link<?php echo $is_scat_checked ? ' is-active' : ''; ?>" style="padding-left:1.25rem">
                                    <span class="cozy-license-box" aria-hidden="true">
                                        <?php if ( $is_scat_checked ) : ?>
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                        <?php endif; ?>
                                    </span>
                                    <span class="cozy-license-name"><?php echo esc_html( $scat->name ); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif;

        the_widget( 'WC_Widget_Price_Filter', [ 'title' => __( 'Precio', 'woocommerce' ) ], $cozy_widget_args );

        // Licencia filter — custom taxonomy with checkbox-style links
        $all_licenses = get_terms( [ 'taxonomy' => 'product_licencia', 'hide_empty' => false ] );
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

</div>
<?php wc_reset_loop(); ?>

<script>
(function () {
    function cozyScrollCat(dir) {
        var el = document.getElementById('cozy-cat-carousel');
        if (el) el.scrollBy({ left: dir * 210, behavior: 'smooth' });
    }
    window.cozyScrollCat = cozyScrollCat;
}());
</script>

<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
get_footer( 'shop' );
