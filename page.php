<?php
/**
 * Page Template – Cozy Fandom Design
 *
 * Handles standard WordPress pages (legal pages, about, contact, etc.)
 * as well as WooCommerce special pages (Cart, Checkout).
 *
 * @package cozy-fandom-child
 */

get_header();
the_post();

$is_wc_page = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );
?>

<div id="cozy-page" class="min-h-[60vh]">

    <?php if ( $is_wc_page ) : ?>

        <!-- WooCommerce Cart / Checkout Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8 md:py-14">
            <div class="cozy-wc-page-content">
                <?php the_content(); ?>
            </div>
        </div>

    <?php else : ?>

        <!-- Standard Content & Legal Page Layout -->
        <div class="bg-gradient-to-b from-cozy-cream to-cozy-sand/30 pt-8 pb-16 md:pt-12 md:pb-24 px-4 sm:px-6 md:px-8 relative overflow-hidden">
            <!-- Decorative background glow -->
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

            <div class="max-w-4xl mx-auto">

                <!-- Page Header / Hero -->
                <header class="text-center mb-8 md:mb-12">
                    <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-4 shadow-sm">
                        🌸 The Cozy Fandom
                    </div>
                    <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-cozy-coffee tracking-tight">
                        <?php the_title(); ?>
                    </h1>
                </header>

                <!-- Page Content Card with Cozy aesthetics & mobile-responsive padding -->
                <article class="bg-white rounded-[24px] md:rounded-[32px] border border-cozy-sand/80 p-6 sm:p-10 md:p-14 shadow-sm cozy-post-content">
                    <?php the_content(); ?>
                </article>

            </div>
        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
