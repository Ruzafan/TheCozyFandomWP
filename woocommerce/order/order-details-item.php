<?php
/**
 * Order Item Details – Cozy Fandom Design
 * Template override: woocommerce/order/order-details-item.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 *
 * @var WC_Order_Item_Product $item
 * @var bool                  $show_purchase_note
 * @var string                $purchase_note
 * @var WC_Product            $product
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
?>
<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item border-b border-cozy-sand/50', $item, $order ) ); ?>">

	<td class="woocommerce-table__product-name product-name py-4 px-2 sm:px-4 align-middle">
		<div class="flex items-center gap-3.5">
			<!-- Product Thumbnail -->
			<div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-cozy-cream border border-cozy-sand/70 overflow-hidden shrink-0 flex items-center justify-center shadow-xs">
				<?php if ( $product && $product->get_image_id() ) : ?>
					<?php echo wp_get_attachment_image( $product->get_image_id(), array( 64, 64 ), false, array( 'class' => 'w-full h-full object-cover' ) ); ?>
				<?php else : ?>
					<div class="w-full h-full flex items-center justify-center text-cozy-coffee/30 text-xl">🎁</div>
				<?php endif; ?>
			</div>

			<!-- Product Details -->
			<div class="min-w-0 flex-1">
				<?php
				$is_visible        = $product && $product->is_visible();
				$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

				echo '<div class="font-bold text-sm sm:text-base text-cozy-coffee leading-snug">';
				echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s" class="hover:text-cozy-mint transition-colors no-underline">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );
				echo '</div>';

				$qty          = $item->get_quantity();
				$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

				if ( $refunded_qty ) {
					$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
				} else {
					$qty_display = esc_html( $qty );
				}

				echo '<div class="mt-1 flex items-center gap-2">';
				echo '<span class="inline-flex items-center text-xs font-bold bg-cozy-mintLight text-cozy-coffee px-2.5 py-0.5 rounded-md border border-cozy-mint/20">Cant: ' . $qty_display . '</span>';
				echo '</div>';

				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

				wc_display_item_meta( $item );

				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
				?>
			</div>
		</div>
	</td>

	<td class="woocommerce-table__product-total product-total py-4 px-2 sm:px-4 text-right align-middle font-bold text-sm sm:text-base text-cozy-coffee whitespace-nowrap">
		<?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore ?>
	</td>

</tr>

<?php if ( $show_purchase_note && $purchase_note ) : ?>
<tr class="woocommerce-table__product-purchase-note product-purchase-note bg-cozy-cream/30">
	<td colspan="2" class="p-3 text-xs text-cozy-coffee/80 italic border-b border-cozy-sand/50">
		<?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
	</td>
</tr>
<?php endif; ?>
