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
<div class="relative overflow-hidden bg-gradient-to-b from-cozy-cream to-cozy-sand rounded-[32px] p-6 md:p-8 border border-cozy-sand mb-10">

    <div class="absolute top-0 right-0 w-72 h-72 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

    <!-- Title row -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-2">
                🌿 Boutique de Selección
            </div>
            <h1 class="font-serif text-2xl md:text-3xl font-bold text-cozy-coffee m-0">
                <?php woocommerce_page_title(); ?>
            </h1>
        </div>

        <?php if ( ! is_wp_error( $categories ) && count( $categories ) >= 3 ) : ?>
        <div class="hidden md:flex items-center gap-2 shrink-0">
            <button onclick="cozyScrollCat(-1)" aria-label="<?php esc_attr_e( 'Categoría anterior', 'woocommerce' ); ?>"
                class="w-10 h-10 rounded-2xl bg-white border border-cozy-sand hover:border-cozy-mint hover:bg-cozy-mintLight text-cozy-coffee flex items-center justify-center transition-all shadow-sm">
                <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
            </button>
            <button onclick="cozyScrollCat(1)" aria-label="<?php esc_attr_e( 'Siguiente categoría', 'woocommerce' ); ?>"
                class="w-10 h-10 rounded-2xl bg-white border border-cozy-sand hover:border-cozy-mint hover:bg-cozy-mintLight text-cozy-coffee flex items-center justify-center transition-all shadow-sm">
                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Carousel track -->
    <div id="cozy-cat-carousel" class="cozy-cat-carousel" role="list" aria-label="<?php esc_attr_e( 'Filtrar por categoría', 'woocommerce' ); ?>">

        <!-- "Todos" card -->
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
           role="listitem"
           class="cozy-cat-card <?php echo ! $current_cat ? 'cozy-cat-card--active' : ''; ?>"
           <?php echo ! $current_cat ? 'aria-current="true"' : ''; ?>>
            <div class="cozy-cat-card__img">
                <div class="w-full h-full flex items-center justify-center bg-cozy-mintLight">
                    <i class="fa-solid fa-store text-cozy-mint text-3xl" aria-hidden="true"></i>
                </div>
            </div>
            <div class="cozy-cat-card__body">
                <span class="cozy-cat-card__name"><?php esc_html_e( 'Todos', 'woocommerce' ); ?></span>
                <span class="cozy-cat-card__count">
                    <?php
                    $total = wp_count_posts( 'product' );
                    echo absint( $total->publish ) . ' productos';
                    ?>
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
            <div class="cozy-cat-card__img">
                <?php if ( $img_src ) : ?>
                <img src="<?php echo esc_url( $img_src ); ?>"
                     alt="<?php echo esc_attr( $cat->name ); ?>"
                     loading="lazy" width="200" height="120"
                     class="w-full h-full object-cover">
                <?php else : ?>
                <div class="w-full h-full flex items-center justify-center bg-cozy-sand">
                    <i class="fa-solid fa-tag text-cozy-coffee/40 text-2xl" aria-hidden="true"></i>
                </div>
                <?php endif; ?>
            </div>
            <div class="cozy-cat-card__body">
                <span class="cozy-cat-card__name"><?php echo esc_html( $cat->name ); ?></span>
                <span class="cozy-cat-card__count">
                    <?php echo absint( $cat->count ) . ' ' . esc_html( _n( 'producto', 'productos', $cat->count, 'woocommerce' ) ); ?>
                </span>
            </div>
        </a>
        <?php endforeach; endif; ?>

    </div>

    <?php do_action( 'woocommerce_archive_description' ); ?>

</div>

<!-- ============================================================ -->
<!-- PRODUCTS                                                       -->
<!-- ============================================================ -->
<?php if ( woocommerce_product_loop() ) : ?>

    <div class="cozy-sort-bar flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <?php woocommerce_result_count(); ?>
        <?php woocommerce_catalog_ordering(); ?>
    </div>

    <?php woocommerce_product_loop_start(); ?>

    <?php while ( have_posts() ) : the_post(); ?>
        <?php wc_get_template_part( 'content', 'product' ); ?>
    <?php endwhile; ?>

    <?php woocommerce_product_loop_end(); ?>

    <?php woocommerce_pagination(); ?>

<?php else : ?>

    <?php do_action( 'woocommerce_no_products_found' ); ?>

<?php endif; ?>

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
