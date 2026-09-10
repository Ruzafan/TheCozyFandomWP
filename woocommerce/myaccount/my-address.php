<?php
/**
 * My Account Addresses – Cozy Fandom Design
 * Template override: woocommerce/myaccount/my-address.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}
?>

<p class="text-xs sm:text-sm text-cozy-coffee/70 mb-6 leading-relaxed">
	<?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'Las siguientes direcciones se utilizarán por defecto en la página de pago.', 'woocommerce' ) ); ?>
</p>

<div class="woocommerce-Addresses grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

<?php foreach ( $get_addresses as $name => $address_title ) :
	$address      = wc_get_account_formatted_address( $name );
	$button_label = $address ? __( 'Editar', 'woocommerce' ) : __( 'Añadir', 'woocommerce' );
?>

	<div class="woocommerce-Address bg-white border border-cozy-sand rounded-[24px] p-4 sm:p-5 shadow-sm flex flex-col gap-4">
		<header class="woocommerce-Address-title bg-cozy-cream/60 border border-cozy-sand/80 rounded-[18px] p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
			<h3 class="font-serif text-sm sm:text-base font-bold text-cozy-coffee m-0">
				<?php echo esc_html( $address_title ); ?>
			</h3>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>"
			   class="edit inline-flex items-center justify-center self-start sm:self-auto text-xs font-bold text-cozy-mint hover:text-white bg-cozy-mintLight hover:bg-cozy-mint border border-cozy-mint/30 px-3.5 py-1.5 rounded-full transition-all no-underline shrink-0">
				<?php echo cozy_icon( 'pen', '12', 'mr-1.5' ); ?>
				<?php echo esc_html( $button_label ); ?>
			</a>
		</header>
		<address class="not-italic text-xs sm:text-sm text-cozy-coffee/80 leading-relaxed pt-1">
			<?php
				if ( $address ) {
					echo wp_kses_post( $address );
				} else {
					echo '<span class="text-cozy-coffee/50 italic">Aún no has configurado esta dirección.</span>';
				}
			?>
		</address>
	</div>

<?php endforeach; ?>

</div>
