<?php
/**
 * Order details – Cozy Fandom Design
 * Template override: woocommerce/order/order-details.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 *
 * @var int $order_id
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
if ( 0 === (int) $order->get_user_id() ) {
	$show_customer_details = true;
}

if ( ! empty( $downloads ) ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details bg-white rounded-[28px] sm:rounded-[32px] p-6 sm:p-8 border border-cozy-sand shadow-sm mb-8">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title font-serif text-xl sm:text-2xl font-bold text-cozy-coffee mb-6 flex items-center gap-2 m-0">
		<?php echo cozy_icon( 'box-open', '20', 'text-cozy-mint' ); ?>
		<?php esc_html_e( 'Order details', 'woocommerce' ); ?>
	</h2>

	<div class="overflow-x-auto -mx-2 sm:mx-0">
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details w-full">

			<thead>
				<tr>
					<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
					<th class="woocommerce-table__product-table product-total text-right"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php
				do_action( 'woocommerce_order_details_before_order_table_items', $order );

				foreach ( $order_items as $item_id => $item ) {
					$product = $item->get_product();

					wc_get_template(
						'order/order-details-item.php',
						array(
							'order'              => $order,
							'item_id'            => $item_id,
							'item'               => $item,
							'show_purchase_note' => $show_purchase_note,
							'purchase_note'      => $product ? $product->get_purchase_note() : '',
							'product'            => $product,
						)
					);
				}

				do_action( 'woocommerce_order_details_after_order_table_items', $order );
				?>
			</tbody>

			<?php if ( ! empty( $actions ) ) : ?>
			<tfoot>
				<tr>
					<th class="order-actions--heading"><?php esc_html_e( 'Actions', 'woocommerce' ); ?>:</th>
					<td class="text-right">
						<?php
						$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
						foreach ( $actions as $key => $action ) {
							$action_aria_label = empty( $action['aria-label'] )
								? sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() )
								: $action['aria-label'];

							echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . ' order-actions-button inline-block bg-cozy-mint text-cozy-coffee text-xs font-bold px-4 py-2 rounded-xl" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
						}
						?>
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>

			<tfoot>
				<?php
				foreach ( $order->get_order_item_totals() as $key => $total ) {
					?>
					<tr class="order-total-row-<?php echo esc_attr( $key ); ?>">
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td class="text-right"><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
				?>
				<?php if ( $order->get_customer_note() ) : ?>
					<tr class="order-total-row-customer-note">
						<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
						<td class="text-right">
						<?php
						$customer_note = wc_wptexturize_order_note( $order->get_customer_note() );
						echo wp_kses( nl2br( $customer_note ), array( 'br' => array() ) );
						?>
						</td>
					</tr>
				<?php endif; ?>
			</tfoot>
		</table>
	</div>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
