<?php
/**
 * Payment methods – Cozy Fandom Design
 * Template override: woocommerce/myaccount/payment-methods.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.9.0
 */

defined( 'ABSPATH' ) || exit;

$has_methods = (bool) $saved_methods;
$types       = wc_get_account_payment_methods_types();

do_action( 'woocommerce_before_account_payment_methods', $has_methods );
?>

<div class="cozy-account-payment-methods max-w-4xl">

	<?php if ( $has_methods ) : ?>

		<div class="mb-6">
			<table class="woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">
				<thead>
					<tr>
						<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
							<th class="woocommerce-PaymentMethod-<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $saved_methods as $type => $methods ) : ?>
						<?php foreach ( $methods as $method ) : ?>
							<tr class="payment-method<?php echo ! empty( $method['is_default'] ) ? ' default-payment-method' : ''; ?>">
								<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
									<td class="woocommerce-PaymentMethod-<?php echo esc_attr( $column_id ); ?> payment-method-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( empty( $column_name ) ? '' : $column_name ); ?>">
										<?php
										if ( has_action( 'woocommerce_account_payment_methods_column_' . $column_id ) ) {
											do_action( 'woocommerce_account_payment_methods_column_' . $column_id, $method );
										} elseif ( 'method' === $column_id ) {
											if ( ! empty( $method['method']['last4'] ) ) {
												/* translators: 1: credit card type 2: last 4 digits */
												echo sprintf( esc_html__( '%1$s finaliza en %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ), esc_html( $method['method']['last4'] ) );
											} else {
												echo esc_html( $method['method']['title'] ?? '' );
											}
										} elseif ( 'expires' === $column_id ) {
											echo esc_html( $method['expires'] );
										} elseif ( 'actions' === $column_id ) {
											foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
												echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>&nbsp;';
											}
										}
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="p-6 text-center bg-white rounded-2xl border border-cozy-sand mb-6">
			<?php echo cozy_icon( 'credit-card', '36', 'text-cozy-coffee/20 block mb-3 mx-auto' ); ?>
			<p class="text-sm text-cozy-coffee/70 m-0">
				<?php esc_html_e( 'No hay métodos de pago guardados.', 'woocommerce' ); ?>
			</p>
		</div>

	<?php endif; ?>

	<?php if ( WC()->payment_gateways() == true ) : ?>
		<a class="button woocommerce-button inline-flex items-center gap-2" href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>">
			<?php echo cozy_icon( 'plus', '14' ); ?>
			<?php esc_html_e( 'Añadir método de pago', 'woocommerce' ); ?>
		</a>
	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_account_payment_methods', $has_methods ); ?>
