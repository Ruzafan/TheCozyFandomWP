<?php
/**
 * Template Name: Cozy Cart Page
 * Description: Dedicated page template for WooCommerce Cart Page
 * Template Post Type: page
 *
 * @package cozy-fandom-child
 */

get_header();
?>

<div id="cozy-cart-page" class="min-h-[60vh] py-6 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <?php
        if ( function_exists( 'WC' ) && isset( WC()->cart ) && WC()->cart ) {
            if ( WC()->cart->is_empty() ) {
                $empty_template = get_stylesheet_directory() . '/woocommerce/cart/cart-empty.php';
                if ( file_exists( $empty_template ) ) {
                    include $empty_template;
                } else {
                    wc_get_template( 'cart/cart-empty.php' );
                }
            } else {
                $cart_template = get_stylesheet_directory() . '/woocommerce/cart/cart.php';
                if ( file_exists( $cart_template ) ) {
                    include $cart_template;
                } else {
                    echo do_shortcode( '[woocommerce_cart]' );
                }
            }
        } else {
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
        }
        ?>
    </div>
</div>

<?php
get_footer();
