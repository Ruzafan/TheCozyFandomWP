<?php
/**
 * Orders – Cozy Fandom Design
 * Template override: woocommerce/myaccount/orders.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.5.0
 *
 * @var stdClass $customer_orders
 * @var int      $current_page
 * @var bool     $has_orders
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<div class="cozy-account-orders max-w-4xl">

	<div class="flex items-center justify-between mb-6">
		<h2 class="font-serif text-xl sm:text-2xl font-bold text-cozy-coffee flex items-center gap-2 m-0">
			<?php echo cozy_icon( 'bag-shopping', '22', 'text-cozy-mint' ); ?>
			<?php esc_html_e( 'Orders', 'woocommerce' ); ?>
		</h2>
	</div>

	<?php if ( $has_orders ) : ?>

		<div class="space-y-3.5 mb-8">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order );
				$item_count = $order->get_item_count();
				$status     = $order->get_status();
				$status_label = wc_get_order_status_name( $status );

				$status_styles = [
					'completed'  => 'bg-green-50  text-green-700  border-green-100',
					'processing' => 'bg-cozy-mintLight text-cozy-mint border-cozy-mint/20',
					'pending'    => 'bg-amber-50   text-amber-600  border-amber-100',
					'cancelled'  => 'bg-red-50     text-red-500    border-red-100',
					'refunded'   => 'bg-gray-50    text-gray-500   border-gray-100',
					'on-hold'    => 'bg-orange-50  text-orange-500 border-orange-100',
					'draft'      => 'bg-cozy-sand/60 text-cozy-coffee/70 border-cozy-sand/80',
				];
				$badge = isset( $status_styles[ $status ] ) ? $status_styles[ $status ] : 'bg-cozy-sand text-cozy-coffee border-cozy-sand';
				$actions = wc_get_account_orders_actions( $order );
				?>

				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:p-5 rounded-2xl bg-white border border-cozy-sand/80 hover:border-cozy-mint shadow-xs hover:shadow-sm transition-all">
					<!-- Order Info (Icon + Number + Date) -->
					<div class="flex items-center gap-3.5">
						<div class="w-10 h-10 shrink-0 rounded-xl bg-cozy-cream border border-cozy-sand/80 flex items-center justify-center text-cozy-coffee/40">
							<?php echo cozy_icon( 'bag-shopping', '16' ); ?>
						</div>
						<div>
							<span class="block text-sm font-bold text-cozy-coffee">
								<?php echo esc_html( sprintf( __( 'Pedido #%s', 'woocommerce' ), $order->get_order_number() ) ); ?>
							</span>
							<span class="text-xs text-cozy-coffee/50">
								<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
							</span>
						</div>
					</div>

					<!-- Order Stats & Action Links -->
					<div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-5 pt-2 sm:pt-0 border-t sm:border-t-0 border-cozy-sand/40">
						<span class="text-sm sm:text-base font-bold text-cozy-coffee">
							<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
						</span>

						<span class="text-xs font-bold px-3 py-1 rounded-full border <?php echo esc_attr( $badge ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>

						<div class="flex items-center gap-2">
							<?php
							if ( ! empty( $actions ) ) {
								foreach ( $actions as $key => $action ) {
									$is_view = ( 'view' === $key );
									echo '<a href="' . esc_url( $action['url'] ) . '" class="' . sanitize_html_class( $key ) . ' text-xs font-semibold no-underline transition-colors ' . ( $is_view ? 'text-cozy-coffee/70 hover:text-cozy-mint' : 'text-cozy-mint hover:text-cozy-mintDark underline' ) . '" aria-label="' . esc_attr( $action['name'] ) . '">' . esc_html( $action['name'] ) . ( $is_view ? ' →' : '' ) . '</a>';
								}
							} else {
								echo '<a href="' . esc_url( $order->get_view_order_url() ) . '" class="text-xs text-cozy-coffee/70 hover:text-cozy-mint font-semibold transition-colors no-underline">Ver →</a>';
							}
							?>
						</div>
					</div>
				</div>

			<?php } ?>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination flex items-center justify-between mt-6 pt-4 border-t border-cozy-sand/50">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button inline-flex items-center gap-1.5 bg-white border border-cozy-sand hover:bg-cozy-cream text-cozy-coffee text-xs font-bold px-4 py-2.5 rounded-xl transition-colors no-underline shadow-xs" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
						<?php echo cozy_icon( 'chevron-left', '12' ); ?>
						<?php esc_html_e( 'Previous', 'woocommerce' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button inline-flex items-center gap-1.5 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee text-xs font-bold px-4 py-2.5 rounded-xl transition-all no-underline shadow-xs ml-auto" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
						<?php esc_html_e( 'Next', 'woocommerce' ); ?>
						<?php echo cozy_icon( 'chevron-right', '12' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="text-center py-12 px-6 border-2 border-dashed border-cozy-sand rounded-3xl bg-white shadow-xs">
			<?php echo cozy_icon( 'box-open', '44', 'text-cozy-coffee/20 block mb-3 mx-auto' ); ?>
			<h3 class="font-serif text-lg font-bold text-cozy-coffee mb-1"><?php esc_html_e( 'No orders found', 'woocommerce' ); ?></h3>
			<p class="text-xs sm:text-sm text-cozy-coffee/60 mb-6"><?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
			   class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold px-6 py-3 rounded-2xl text-xs transition-all no-underline shadow-sm">
				<?php echo cozy_icon( 'basket-shopping', '14' ); ?>
				<?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
			</a>
		</div>

	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
