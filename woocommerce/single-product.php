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
                // Top row: category pill + favorites button
                $cat_ids  = $product->get_category_ids();
                $cat_term = null;
                if ( ! empty( $cat_ids ) ) {
                    $maybe = get_term( reset( $cat_ids ), 'product_cat' );
                    if ( $maybe && ! is_wp_error( $maybe ) ) $cat_term = $maybe;
                }
                ?>
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <?php if ( $cat_term ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $cat_term ) ); ?>"
                       class="self-start inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 no-underline hover:bg-cozy-mint hover:text-white transition-colors">
                        🌿 <?php echo esc_html( $cat_term->name ); ?>
                    </a>
                    <?php else : ?>
                    <div></div>
                    <?php endif; ?>

                    <button type="button" data-action="toggle-favorite"
                            class="cozy-fav-btn inline-flex items-center gap-1.5 border border-cozy-sand rounded-2xl px-3 py-1.5 text-xs font-medium text-cozy-coffee/50 hover:border-red-200 hover:text-red-400 transition-all"
                            data-product-id="<?php echo get_the_ID(); ?>"
                            aria-label="Guardar en favoritos">
                        <svg class="cozy-fav-heart" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <span class="cozy-fav-label">Guardar</span>
                    </button>
                </div>

                <?php do_action( 'woocommerce_single_product_summary' ); ?>

            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TRUST BADGES                                                   -->
        <!-- ============================================================ -->
        <div class="cozy-product-trust bg-cozy-mintLight/40 rounded-[20px] border border-cozy-mint/15 px-6 md:px-10 py-5 mb-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">

                <?php
                get_template_part( 'template-parts/trust-badges', null, [
                    'icon_bg_class'   => 'bg-white shadow-sm',
                    'text_size_title' => 'text-[13px]',
                    'text_size_desc'  => 'text-[11px]',
                    'text_desc_muted' => 'text-cozy-coffee/60',
                    'item_class'      => '',
                    'has_border'      => false,
                ] );
                ?>

            </div>
        </div>

        <!-- Return policy reassurance -->
        <p class="text-[11px] text-cozy-coffee/50 text-center mb-8 -mt-4">
            <a href="<?php echo esc_url( cozy_fandom_legal_link( 'envios-y-devoluciones' ) ); ?>"
               class="underline hover:text-cozy-mint transition-colors">Devoluciones en 14 días</a>
            &nbsp;·&nbsp; Envíos seguros &nbsp;·&nbsp; Pago 100% encriptado
        </p>

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
