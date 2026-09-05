<?php
/**
 * Empty Cart Page – Cozy Fandom Design
 * Template override: woocommerce/cart/cart-empty.php
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<div class="cozy-empty-cart-container bg-white rounded-[32px] p-8 md:p-14 border border-cozy-sand shadow-sm max-w-xl mx-auto my-8 text-center space-y-6">
		<div class="w-20 h-20 rounded-[24px] bg-cozy-mintLight flex items-center justify-center text-cozy-mint mx-auto shadow-sm">
			<?php echo cozy_icon( 'box-open', '40' ); ?>
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
<?php endif; ?>
