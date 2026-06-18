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
<!-- CATEGORY CAROUSEL                                             -->
<!-- ============================================================ -->
<div class="bg-cozy-sand rounded-[32px] px-6 md:px-8 pt-5 pb-5 border border-cozy-sand mb-10">

    <?php if ( ! is_wp_error( $categories ) && count( $categories ) >= 3 ) : ?>
    <div class="flex justify-end mb-3">
        <div class="flex items-center gap-2">
            <button onclick="cozyScrollCat(-1)" aria-label="<?php esc_attr_e( 'Categoría anterior', 'woocommerce' ); ?>"
                class="w-9 h-9 rounded-2xl bg-white border border-cozy-sand hover:border-cozy-mint hover:bg-cozy-mintLight text-cozy-coffee flex items-center justify-center transition-all shadow-sm">
                <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
            </button>
            <button onclick="cozyScrollCat(1)" aria-label="<?php esc_attr_e( 'Siguiente categoría', 'woocommerce' ); ?>"
                class="w-9 h-9 rounded-2xl bg-white border border-cozy-sand hover:border-cozy-mint hover:bg-cozy-mintLight text-cozy-coffee flex items-center justify-center transition-all shadow-sm">
                <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Carousel track -->
    <div id="cozy-cat-carousel" class="cozy-cat-carousel" role="list" aria-label="<?php esc_attr_e( 'Filtrar por categoría', 'woocommerce' ); ?>">

        <!-- "Todos" card -->
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
           role="listitem"
           class="cozy-cat-card <?php echo ! $current_cat ? 'cozy-cat-card--active' : ''; ?>"
           <?php echo ! $current_cat ? 'aria-current="true"' : ''; ?>>
            <div class="cozy-cat-card__body">
                <span class="cozy-cat-card__name"><?php esc_html_e( 'Todos', 'woocommerce' ); ?></span>
                <span class="cozy-cat-card__count">
                    <?php $total = wp_count_posts( 'product' );
                    echo absint( $total->publish ) . ' productos'; ?>
                </span>
            </div>
        </a>

        <?php if ( ! is_wp_error( $categories ) ) :
            foreach ( $categories as $cat ) :
                $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                $img_src      = $thumbnail_id
                    ? wp_get_attachment_image_url( $thumbnail_id, 'medium_large' )
                    : '';
                $is_active = $current_cat && $current_cat->term_id === $cat->term_id;
        ?>
        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
           role="listitem"
           class="cozy-cat-card <?php echo $is_active ? 'cozy-cat-card--active' : ''; ?>"
           <?php echo $is_active ? 'aria-current="true"' : ''; ?>>
            <div class="cozy-cat-card__body">
                <span class="cozy-cat-card__name"><?php echo esc_html( $cat->name ); ?></span>
                <span class="cozy-cat-card__count">
                    <?php echo absint( $cat->count ) . ' ' . esc_html( _n( 'producto', 'productos', $cat->count, 'woocommerce' ) ); ?>
                </span>
            </div>
        </a>
        <?php endforeach; endif; ?>

    </div>


</div>

<!-- ============================================================ -->
<!-- PRODUCTS                                                       -->
<!-- ============================================================ -->
<?php
wc_setup_loop();

if ( woocommerce_product_loop() ) :
    $cozy_widget_args = [
        'before_widget' => '<div class="cozy-filter-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="cozy-filter-widget__title">',
        'after_title'   => '</h3>',
    ];
    ?>
    <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">

        <!-- ==================================================== -->
        <!-- FILTERS SIDEBAR                                        -->
        <!-- ==================================================== -->
        <aside class="cozy-shop-filters space-y-5">
            <?php
            the_widget( 'WC_Widget_Layered_Nav_Filters', [], [
                'before_widget' => '<div class="cozy-filter-widget cozy-active-filters">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="cozy-filter-widget__title">',
                'after_title'   => '</h3>',
            ] );
            the_widget( 'WC_Widget_Price_Filter', [ 'title' => __( 'Precio', 'woocommerce' ) ], $cozy_widget_args );

            // Licencia filter — custom taxonomy with checkbox-style links
            $all_licenses = get_terms( [ 'taxonomy' => 'product_licencia', 'hide_empty' => false ] );
            if ( ! is_wp_error( $all_licenses ) && ! empty( $all_licenses ) ) :
                $raw_sel      = sanitize_text_field( wp_unslash( $_GET['licencia'] ?? '' ) );
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
            /* Filter by Attribute (color, talla, etc.) appears here automatically
               once attributes are created in Productos → Atributos — none exist yet. */
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
        <div>
            <div class="cozy-sort-bar flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <?php woocommerce_result_count(); ?>
                <?php woocommerce_catalog_ordering(); ?>
            </div>

            <?php woocommerce_product_loop_start(); ?>

            <?php while ( have_posts() ) : the_post(); ?>
                <?php wc_get_template_part( 'content', 'product' ); ?>
            <?php endwhile; ?>

            <?php woocommerce_product_loop_end(); ?>

            <?php do_action( 'woocommerce_after_shop_loop' ); ?>

            <?php woocommerce_pagination(); ?>
        </div>

    </div>
<?php else : ?>

    <?php do_action( 'woocommerce_no_products_found' ); ?>

<?php endif;
wc_reset_loop(); ?>

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
