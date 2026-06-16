<?php
/**
 * Single Product – Cozy Fandom Design
 * Template override: woocommerce/single-product.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
    the_post();
    global $product;
    wc_setup_product_data( $GLOBALS['post'] );

    do_action( 'woocommerce_before_single_product' );

    if ( post_password_required() ) {
        echo get_the_password_form(); // phpcs:ignore
        continue;
    }
    ?>

    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'cozy-single-product max-w-6xl mx-auto', $product ); ?>>

        <!-- Decorative blobs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-20 left-0 w-72 h-72 bg-cozy-accent/5 rounded-full blur-3xl -z-10 pointer-events-none" aria-hidden="true"></div>

        <!-- Breadcrumb -->
        <nav class="mb-6 text-xs text-cozy-coffee/50 flex items-center gap-1.5 flex-wrap" aria-label="Ruta de navegación">
            <?php woocommerce_breadcrumb( [
                'delimiter'   => '<span class="text-cozy-coffee/30 mx-0.5">/</span>',
                'wrap_before' => '',
                'wrap_after'  => '',
                'before'      => '',
                'after'       => '',
                'home'        => __( 'Inicio', 'woocommerce' ),
            ] ); ?>
        </nav>

        <!-- ============================================================ -->
        <!-- PRODUCT LAYOUT: image + summary                               -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-start mb-12">

            <!-- Left: Product images -->
            <div class="cozy-product-gallery">
                <?php do_action( 'woocommerce_before_single_product_summary' ); ?>
            </div>

            <!-- Right: Product summary -->
            <div class="summary entry-summary flex flex-col gap-5">

                <?php
                // Category pill above title
                $cat_ids = $product->get_category_ids();
                if ( ! empty( $cat_ids ) ) :
                    $term = get_term( reset( $cat_ids ), 'product_cat' );
                    if ( $term && ! is_wp_error( $term ) ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"
                       class="self-start inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 no-underline hover:bg-cozy-mint hover:text-white transition-colors">
                        🌿 <?php echo esc_html( $term->name ); ?>
                    </a>
                    <?php endif;
                endif; ?>

                <?php do_action( 'woocommerce_single_product_summary' ); ?>

            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TABS (description, reviews) + RELATED                         -->
        <!-- ============================================================ -->
        <div class="cozy-product-lower">
            <?php do_action( 'woocommerce_after_single_product_summary' ); ?>
        </div>

        <?php do_action( 'woocommerce_after_single_product' ); ?>

    </div>

    <?php
endwhile;

do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );
get_footer( 'shop' );
