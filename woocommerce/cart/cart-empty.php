<?php
/**
 * Empty Cart Page – Cozy Fandom Design
 * Template override: woocommerce/cart/cart-empty.php
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

// Clear any notices queued on empty cart page so no redundant top banners render
if ( function_exists( 'wc_clear_notices' ) ) {
    wc_clear_notices();
}

remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<style>
		/* Hide default WooCommerce notice banner on empty cart page */
		.woocommerce-info,
		.woocommerce-message,
		.cart-empty.woocommerce-info {
			display: none !important;
		}
	</style>

	<div class="cozy-empty-cart-wrapper py-6 md:py-12 max-w-6xl mx-auto px-4">
		<!-- Hero Empty Cart Card -->
		<div class="cozy-empty-cart-card bg-white rounded-[32px] p-8 md:p-14 border border-cozy-sand shadow-sm max-w-xl mx-auto text-center space-y-6">
			<div class="w-20 h-20 rounded-[24px] bg-cozy-mintLight flex items-center justify-center text-cozy-mint mx-auto shadow-sm transition-transform hover:scale-105">
				<?php echo cozy_icon( 'bag-shopping', '40' ); ?>
			</div>

			<div class="space-y-2">
				<h1 class="font-serif text-2xl md:text-3xl font-bold text-cozy-coffee m-0">Tu carrito está vacío</h1>
				<p class="text-sm text-cozy-coffee/70 max-w-md mx-auto leading-relaxed m-0">
					Parece que aún no has añadido ningún tesoro a tu cesta. ¡Explora nuestras colecciones y encuentra algo especial para ti!
				</p>
			</div>

			<div class="pt-2">
				<a class="button wc-backward inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold px-8 py-3.5 rounded-2xl shadow-sm hover:shadow-md transition-all text-sm no-underline"
				   href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
					<?php echo cozy_icon( 'arrow-left', '14' ); ?>
					<?php esc_html_e( 'Volver a la tienda', 'woocommerce' ); ?>
				</a>
			</div>
		</div>

		<!-- Recommended Products Grid -->
		<?php
		$product_ids = get_posts( array(
			'post_type'        => 'product',
			'post_status'      => 'publish',
			'posts_per_page'   => 4,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'fields'           => 'ids',
			'suppress_filters' => true,
		) );

		if ( ! empty( $product_ids ) ) : ?>
			<div class="cozy-empty-cart-suggestions mt-14 pt-10 border-t border-cozy-sand/80">
				<div class="text-center mb-8 space-y-1">
					<span class="text-xs font-bold text-cozy-mint uppercase tracking-wider block">Recomendaciones Cozy</span>
					<h2 class="font-serif text-xl md:text-2xl font-bold text-cozy-coffee m-0">Novedades que te encantarán ✨</h2>
				</div>

				<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
					<?php
					foreach ( $product_ids as $pid ) {
						$product = wc_get_product( $pid );
						if ( $product && function_exists( 'cozy_fandom_home_product_card' ) ) {
							cozy_fandom_home_product_card( $product, 'Novedad', '✨' );
						}
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
