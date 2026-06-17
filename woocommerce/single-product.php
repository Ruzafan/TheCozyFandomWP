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
        <!-- TRUST BADGES                                                   -->
        <!-- ============================================================ -->
        <div class="cozy-product-trust bg-cozy-mintLight/40 rounded-[20px] border border-cozy-mint/15 px-6 md:px-10 py-5 mb-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-[12px] bg-white flex items-center justify-center shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-cozy-coffee m-0 leading-tight" style="font-size:13px">Empaquetado Aesthetic</p>
                        <p class="text-cozy-coffee/60 m-0 leading-tight" style="font-size:11px">Unboxings que enamoran</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-[12px] bg-white flex items-center justify-center shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-cozy-coffee m-0 leading-tight" style="font-size:13px">Envíos Rápidos</p>
                        <p class="text-cozy-coffee/60 m-0 leading-tight" style="font-size:11px">24/48h en Península</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-[12px] bg-white flex items-center justify-center shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-cozy-coffee m-0 leading-tight" style="font-size:13px">Pagos 100% Seguros</p>
                        <p class="text-cozy-coffee/60 m-0 leading-tight" style="font-size:11px">Tarjeta, PayPal o Bizum</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-[12px] bg-white flex items-center justify-center shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-cozy-coffee m-0 leading-tight" style="font-size:13px">Atención Cercana</p>
                        <p class="text-cozy-coffee/60 m-0 leading-tight" style="font-size:11px">Te ayudamos por WhatsApp</p>
                    </div>
                </div>

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
