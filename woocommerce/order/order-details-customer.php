<?php
/**
 * Order Customer Details – Cozy Fandom Design
 * Template override: woocommerce/order/order-details-customer.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details mb-8 clear-both w-full block" style="display: block !important; width: 100% !important; float: none !important; clear: both !important;">

	<h2 class="woocommerce-column__title font-serif text-xl sm:text-2xl font-bold text-cozy-coffee mb-6 flex items-center gap-2 m-0 w-full block clear-both" style="display: flex !important; width: 100% !important; max-width: 100% !important; float: none !important; clear: both !important; margin-bottom: 1.5rem !important;">
		<?php echo cozy_icon( 'location-dot', '20', 'text-cozy-mint' ); ?>
		<?php esc_html_e( 'Customer details', 'woocommerce' ); ?>
	</h2>

	<div class="grid grid-cols-1 <?php echo $show_shipping ? 'md:grid-cols-2' : ''; ?> gap-6 w-full clear-both" style="display: grid !important; width: 100% !important; max-width: 100% !important; float: none !important; clear: both !important;">

		<!-- Billing Address -->
		<div class="bg-white rounded-[28px] p-6 sm:p-8 border border-cozy-sand shadow-sm flex flex-col justify-between">
			<div>
				<span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-cozy-mint mb-3">
					<?php echo cozy_icon( 'credit-card', '14' ); ?>
					<?php esc_html_e( 'Billing address', 'woocommerce' ); ?>
				</span>
				<address class="not-italic text-sm text-cozy-coffee/85 leading-relaxed font-normal m-0 p-0 border-0">
					<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
				</address>
			</div>

			<?php if ( $order->get_billing_phone() || $order->get_billing_email() ) : ?>
			<div class="mt-4 pt-3 border-t border-cozy-sand/50 flex flex-col gap-1 text-xs text-cozy-coffee/75">
				<?php if ( $order->get_billing_phone() ) : ?>
					<p class="m-0 flex items-center gap-1.5">
						<span>📞</span> <?php echo esc_html( $order->get_billing_phone() ); ?>
					</p>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?>
					<p class="m-0 flex items-center gap-1.5">
						<span>✉️</span> <?php echo esc_html( $order->get_billing_email() ); ?>
					</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
		</div>

		<?php if ( $show_shipping ) : ?>
		<!-- Shipping Address -->
		<div class="bg-white rounded-[28px] p-6 sm:p-8 border border-cozy-sand shadow-sm flex flex-col justify-between">
			<div>
				<span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-cozy-mint mb-3">
					<?php echo cozy_icon( 'truck', '14' ); ?>
					<?php esc_html_e( 'Shipping address', 'woocommerce' ); ?>
				</span>
				<address class="not-italic text-sm text-cozy-coffee/85 leading-relaxed font-normal m-0 p-0 border-0">
					<?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
				</address>
			</div>

			<?php if ( $order->get_shipping_phone() ) : ?>
			<div class="mt-4 pt-3 border-t border-cozy-sand/50 text-xs text-cozy-coffee/75">
				<p class="m-0 flex items-center gap-1.5">
					<span>📞</span> <?php echo esc_html( $order->get_shipping_phone() ); ?>
				</p>
			</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order ); ?>
		</div>
		<?php endif; ?>

	</div>

	<?php // Ocultar información adicional a petición del diseño ?>

</section>
