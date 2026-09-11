<?php
/**
 * Cart Page – Cozy Fandom Design
 * Template override: woocommerce/cart/cart.php
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<div class="cozy-cart-container py-6 md:py-10 max-w-6xl mx-auto px-4">

    <!-- Header -->
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-2">
                🛍️ Tu Cesta
            </span>
            <h1 class="font-serif text-2xl md:text-3xl font-bold text-cozy-coffee m-0">Carrito de compras</h1>
        </div>
        <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ?: home_url( '/' ) ) ); ?>"
           class="inline-flex items-center gap-2 text-xs font-bold text-cozy-coffee hover:text-cozy-mint transition-colors no-underline">
            <?php echo cozy_icon( 'arrow-left', '12' ); ?> Seguir comprando
        </a>
    </div>

    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        <?php do_action( 'woocommerce_before_cart_table' ); ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Products List (Left side) -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <div class="bg-white rounded-[28px] border border-cozy-sand p-4 sm:p-6 shadow-sm">
                    <h2 class="font-serif text-lg font-bold text-cozy-coffee mb-4 pb-3 border-b border-cozy-sand/80">
                        Productos en tu carrito (<?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>)
                    </h2>

                    <div class="divide-y divide-cozy-sand/60">
                        <?php
                        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                ?>
                                <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <!-- Image + Name -->
                                    <div class="flex items-center gap-4 min-w-0 flex-1">
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl bg-cozy-cream border border-cozy-sand p-1.5 overflow-hidden flex items-center justify-center">
                                            <?php
                                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', [ 'class' => 'w-full h-full object-contain' ] ), $cart_item, $cart_item_key );
                                            if ( ! $product_permalink ) {
                                                echo $thumbnail;
                                            } else {
                                                printf( '<a href="%s" class="block w-full h-full">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                            }
                                            ?>
                                        </div>

                                        <div class="min-w-0 flex-1 space-y-1">
                                            <h3 class="font-bold text-xs sm:text-sm text-cozy-coffee leading-snug m-0">
                                                <?php
                                                if ( ! $product_permalink ) {
                                                    echo wp_kses_post( $_product->get_name() );
                                                } else {
                                                    echo wp_kses_post( sprintf( '<a href="%s" class="hover:text-cozy-mint transition-colors no-underline">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) );
                                                }
                                                ?>
                                            </h3>
                                            <div class="text-xs text-cozy-coffee/60">
                                                Precio: <span class="font-bold text-cozy-coffee"><?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?></span>
                                            </div>
                                            <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                                        </div>
                                    </div>

                                    <!-- Quantity + Subtotal + Remove -->
                                    <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-cozy-sand/40">
                                        <div class="flex items-center gap-2">
                                            <?php
                                            if ( $_product->is_sold_individually() ) {
                                                $min_quantity = 1;
                                                $max_quantity = 1;
                                            } else {
                                                $min_quantity = 0;
                                                $max_quantity = $_product->get_max_purchase_quantity();
                                            }

                                            $product_quantity = woocommerce_quantity_input(
                                                array(
                                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                                    'input_value'  => $cart_item['quantity'],
                                                    'max_value'    => $max_quantity,
                                                    'min_value'    => $min_quantity,
                                                    'product_name' => $_product->get_name(),
                                                ),
                                                $_product,
                                                false
                                            );
                                            echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                            ?>
                                        </div>

                                        <div class="text-right">
                                            <span class="block text-xs text-cozy-coffee/50 sm:hidden">Subtotal:</span>
                                            <span class="text-sm font-bold text-cozy-coffee">
                                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                                            </span>
                                        </div>

                                        <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>"
                                           class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors text-xs no-underline"
                                           aria-label="Eliminar artículo"
                                           data-product_id="<?php echo esc_attr( $product_id ); ?>"
                                           data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>">
                                            ✕
                                        </a>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <div class="mt-6 pt-4 border-t border-cozy-sand/80 flex items-center justify-between flex-wrap gap-4">
                        <?php if ( wc_coupons_enabled() ) : ?>
                        <div class="flex items-center gap-2 flex-1 min-w-[240px]">
                            <input type="text" name="coupon_code" class="bg-cozy-cream border border-cozy-sand rounded-xl px-3.5 py-2 text-xs text-cozy-coffee placeholder:text-cozy-coffee/40 flex-1 focus:outline-none focus:border-cozy-mint" id="coupon_code" placeholder="Código de cupón" />
                            <button type="submit" class="bg-cozy-cream hover:bg-cozy-mintLight border border-cozy-sand hover:border-cozy-mint text-cozy-coffee text-xs font-bold px-4 py-2 rounded-xl transition-all" name="apply_coupon" value="Aplicar cupón">
                                Aplicar
                            </button>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold text-xs px-5 py-2.5 rounded-xl transition-colors ml-auto border-0 cursor-pointer" name="update_cart" value="Actualizar carrito">
                            Actualizar carrito
                        </button>
                    </div>

                    <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                </div>
            </div>

            <!-- Summary / Totals (Right side) -->
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                <div class="bg-white rounded-[28px] border border-cozy-sand p-6 shadow-sm space-y-5">
                    <h2 class="font-serif text-lg font-bold text-cozy-coffee m-0 pb-3 border-b border-cozy-sand/80">
                        Resumen del pedido
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between text-cozy-coffee/70">
                            <span>Subtotal</span>
                            <span class="font-bold text-cozy-coffee"><?php wc_cart_totals_subtotal_html(); ?></span>
                        </div>

                        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                            <div class="flex items-center justify-between text-green-600 text-xs">
                                <span>Cupón: <?php echo esc_html( $code ); ?></span>
                                <span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                            </div>
                        <?php endforeach; ?>

                    
                        <?php if ( WC()->cart->needs_shipping() ) : ?>
                            <div class="pt-2 border-t border-cozy-sand/40">
                                <?php
                                if ( WC()->cart->show_shipping() ) {
                                    wc_cart_totals_shipping_html();
                                } else {
                                    $shipping_total_raw = (float) WC()->cart->get_shipping_total() + (float) WC()->cart->get_shipping_tax();
                                    $formatted_shipping = WC()->cart->get_cart_shipping_total();
                                    ?>
                                    <div class="flex items-center justify-between text-cozy-coffee/70 text-sm">
                                        <span>Gastos de envío</span>
                                        <span class="font-bold text-cozy-coffee text-xs">
                                            <?php
                                            if ( 0.0 === $shipping_total_raw && WC()->cart->get_cart_contents_total() > 0 && ! empty( $formatted_shipping ) && ( false !== strpos( $formatted_shipping, '0,00' ) || false !== strpos( $formatted_shipping, '0.00' ) || false !== strpos( mb_strtolower( $formatted_shipping ), 'gratis' ) ) ) {
                                                echo '<span class="text-green-600 font-bold">Gratis</span>';
                                            } elseif ( ! empty( $formatted_shipping ) && false === strpos( $formatted_shipping, '0,00' ) && false === strpos( $formatted_shipping, '0.00' ) ) {
                                                echo wp_kses_post( $formatted_shipping );
                                            } elseif ( $shipping_total_raw > 0 ) {
                                                echo wc_price( $shipping_total_raw );
                                            } else {
                                                echo esc_html__( 'Calculado en el checkout', 'woocommerce' );
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                            <div class="flex items-center justify-between text-cozy-coffee/70">
                                <span><?php echo esc_html( $fee->name ); ?></span>
                                <span class="font-bold text-cozy-coffee"><?php wc_cart_totals_fee_html( $fee ); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="pt-3 border-t border-cozy-sand flex items-start justify-between text-base gap-2">
                            <span class="font-bold text-cozy-coffee shrink-0">Total</span>
                            <div class="text-right">
                                <span class="font-bold text-cozy-coffee text-lg"><?php wc_cart_totals_order_total_html(); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
                           class="w-full bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold py-3.5 px-6 rounded-2xl transition-all shadow-sm hover:shadow-md text-sm text-center block no-underline">
                            Finalizar compra →
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
