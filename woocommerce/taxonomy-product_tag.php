<?php
/**
 * Product Tag Archive ("Colecciones") – Cozy Fandom Design
 * Template override: woocommerce/taxonomy-product_tag.php
 *
 * Powers curated "collection" landing pages — one per product tag,
 * no category carousel and no filters sidebar. The public URL of
 * these pages is controlled by WooCommerce's "Product tag base"
 * permalink setting (WP Admin -> Settings -> Permalinks -> Product
 * data). Set it to "tienda/collection" to get URLs like
 * /tienda/collection/peli-y-manta/ — any new tag automatically gets
 * a working page at that URL, no further code changes needed.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$term = get_queried_object();

do_action( 'woocommerce_before_main_content' );
?>

<!-- ============================================================ -->
<!-- COLLECTION HEADER                                              -->
<!-- ============================================================ -->
<div class="relative overflow-hidden bg-gradient-to-b from-cozy-cream to-cozy-sand rounded-[32px] p-6 md:p-8 border border-cozy-sand mb-10 text-center">
    <div class="absolute top-0 right-0 w-72 h-72 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

    <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-3">
        🌿 Colección
    </div>
    <h1 class="font-serif text-2xl md:text-4xl font-bold text-cozy-coffee m-0">
        <?php echo esc_html( $term->name ); ?>
    </h1>
    <?php if ( $term->description ) : ?>
    <div class="text-sm text-cozy-coffee/70 mt-3 max-w-2xl mx-auto"><?php echo wc_format_content( $term->description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    <?php endif; ?>
</div>

<!-- ============================================================ -->
<!-- PRODUCTS                                                       -->
<!-- ============================================================ -->
<?php
wc_setup_loop();

if ( woocommerce_product_loop() ) : ?>
    <div class="p-6 md:p-8">
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
<?php else : ?>

    <?php do_action( 'woocommerce_no_products_found' ); ?>

<?php endif;
wc_reset_loop(); ?>

<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
get_footer( 'shop' );
