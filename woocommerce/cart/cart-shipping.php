<?php
/**
 * Shipping Methods Display – Cozy Fandom Design
 * Template override: woocommerce/cart/cart-shipping.php
 *
 * @package WooCommerce\Templates
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
?>
<div class="woocommerce-shipping-totals shipping space-y-2">
    <div class="flex items-start justify-between text-cozy-coffee/70 gap-2">
        <span class="font-medium text-xs sm:text-sm shrink-0"><?php echo ( 'Envío' === $package_name || 'Shipping' === $package_name ) ? 'Gastos de envío' : wp_kses_post( $package_name ); ?></span>
        <div class="text-right min-w-0">
            <?php if ( $available_methods ) : ?>
                <?php if ( 1 === count( $available_methods ) ) : ?>
                    <?php
                    $method = current( $available_methods );
                    ?>
                    <span class="font-bold text-cozy-coffee text-xs sm:text-sm">
                        <?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?>
                    </span>
                    <input type="hidden" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" />
                <?php else : ?>
                    <ul id="shipping_method" class="woocommerce-shipping-methods space-y-1.5 text-right list-none p-0 m-0">
                        <?php foreach ( $available_methods as $method ) : ?>
                            <li class="flex items-center justify-end gap-2 text-xs">
                                <input type="radio" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method accent-cozy-mint" <?php checked( $method->id, $chosen_method ); ?> />
                                <label for="shipping_method_<?php echo $index; ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" class="cursor-pointer text-cozy-coffee font-medium">
                                    <?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ( is_cart() && $formatted_destination ) : ?>
                    <p class="woocommerce-shipping-destination text-[11px] text-cozy-coffee/50 m-0 mt-1">
                        <?php
                        printf( esc_html__( 'Envío a %s.', 'woocommerce' ), '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
                        $calculator_text = esc_html__( 'Cambiar dirección', 'woocommerce' );
                        ?>
                    </p>
                <?php endif; ?>

            <?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
                <span class="text-xs text-cozy-coffee/60">
                    <?php echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Calculado en el checkout', 'woocommerce' ) ) ); ?>
                </span>
            <?php else : ?>
                <span class="text-xs text-cozy-coffee/60">
                    <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', __( 'No hay opciones de envío disponibles', 'woocommerce' ) ) ); ?>
                </span>
                <?php $calculator_text = esc_html__( 'Cambiar dirección', 'woocommerce' ); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $show_shipping_calculator ) : ?>
        <div class="mt-2 text-right">
            <?php woocommerce_shipping_calculator( $calculator_text ); ?>
        </div>
    <?php endif; ?>
</div>
