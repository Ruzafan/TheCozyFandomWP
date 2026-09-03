<?php
/**
 * Thankyou page – Cozy Fandom Design
 * Template override: woocommerce/checkout/thankyou.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

$shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
?>

<div class="cozy-thankyou pt-6 sm:pt-10 pb-12 px-4 sm:px-6 md:px-8 max-w-4xl mx-auto relative">

    <!-- Decorative ambient blobs -->
    <div class="absolute top-0 right-0 w-80 h-80 bg-cozy-mint/15 rounded-full blur-3xl -z-10 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute bottom-10 left-0 w-80 h-80 bg-cozy-accent/10 rounded-full blur-3xl -z-10 pointer-events-none" aria-hidden="true"></div>

    <div class="woocommerce-order">

        <?php if ( $order ) : ?>

            <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

            <?php if ( $order->has_status( 'failed' ) ) : ?>

                <!-- ==================================================== -->
                <!-- FAILED ORDER CARD                                    -->
                <!-- ==================================================== -->
                <div class="bg-white rounded-[28px] sm:rounded-[32px] p-6 sm:p-10 border border-red-100 shadow-sm text-center mb-8">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center text-red-400 border border-red-100 shadow-sm">
                        <?php echo cozy_icon( 'triangle-exclamation', '28' ); ?>
                    </div>
                    <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-xs font-bold px-3.5 py-1 rounded-full border border-red-200/60 mb-3">
                        Pago no completado
                    </span>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-cozy-coffee mb-3 leading-snug">
                        Vaya, ha ocurrido un problema con el pago
                    </h1>
                    <p class="text-sm sm:text-base text-cozy-coffee/75 max-w-md mx-auto leading-relaxed mb-6">
                        La entidad bancaria ha denegado la transacción. No te preocupes, los artículos siguen reservados para ti.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"
                           class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-semibold px-6 py-3 rounded-2xl shadow-sm transition-all no-underline">
                            <?php echo cozy_icon( 'credit-card', '14' ); ?>
                            Reintentar el pago
                        </a>
                        <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
                           class="inline-flex items-center gap-2 bg-white border border-cozy-sand hover:bg-cozy-cream text-cozy-coffee font-semibold px-6 py-3 rounded-2xl transition-colors no-underline">
                            Ir a Mi Cuenta
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else : ?>

                <!-- ==================================================== -->
                <!-- SUCCESS CONFIRMATION HERO CARD                       -->
                <!-- ==================================================== -->
                <div class="bg-white rounded-[28px] sm:rounded-[36px] p-6 sm:p-10 border border-cozy-sand shadow-sm text-center mb-8 relative">
                    
                    <!-- Top subtle badge -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 rounded-full bg-cozy-mintLight flex items-center justify-center text-cozy-mint border border-cozy-mint/30 shadow-sm">
                        <?php echo cozy_icon( 'check', '32' ); ?>
                    </div>

                    <span class="inline-flex items-center gap-1.5 bg-cozy-mintLight text-cozy-coffee text-xs font-bold px-3.5 py-1 rounded-full border border-cozy-mint/20 mb-3">
                        ✨ ¡Pedido confirmado!
                    </span>

                    <h1 class="font-serif text-2xl sm:text-4xl font-bold text-cozy-coffee mb-3 leading-snug pt-1">
                        ¡Muchas gracias por tu compra<?php echo $order->get_billing_first_name() ? ', ' . esc_html( $order->get_billing_first_name() ) : ''; ?>! 🌿
                    </h1>

                    <p class="text-sm sm:text-base text-cozy-coffee/75 max-w-lg mx-auto leading-relaxed mb-8">
                        Hemos recibido tu pedido correctamente. Ya estamos preparando tus tesoros con todo el mimo que merecen.
                        <?php if ( $order->get_billing_email() ) : ?>
                        <br><span class="inline-block mt-2 bg-cozy-cream/80 text-cozy-coffee text-xs sm:text-sm font-medium px-3.5 py-1.5 rounded-xl border border-cozy-sand/60">
                            ✉️ Confirmación enviada a <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>
                        </span>
                        <?php endif; ?>
                    </p>

                    <!-- Status Progress Bar -->
                    <div class="max-w-xl mx-auto mb-8 p-4 bg-cozy-cream/40 rounded-2xl border border-cozy-sand/60">
                        <div class="grid grid-cols-3 gap-2 relative">
                            <!-- Step 1 -->
                            <div class="flex flex-col items-center text-center z-10">
                                <div class="w-9 h-9 rounded-full bg-cozy-mint text-white font-bold flex items-center justify-center text-xs shadow-sm mb-1.5">
                                    1
                                </div>
                                <span class="text-xs font-bold text-cozy-coffee">Recibido</span>
                                <span class="text-[10px] text-cozy-coffee/60 hidden sm:block">¡Pago ok!</span>
                            </div>
                            <!-- Step 2 -->
                            <div class="flex flex-col items-center text-center z-10">
                                <div class="w-9 h-9 rounded-full bg-white border-2 border-cozy-mint text-cozy-mint font-bold flex items-center justify-center text-xs shadow-sm mb-1.5">
                                    2
                                </div>
                                <span class="text-xs font-bold text-cozy-coffee">Preparando</span>
                                <span class="text-[10px] text-cozy-coffee/60 hidden sm:block">Con cariño 🎁</span>
                            </div>
                            <!-- Step 3 -->
                            <div class="flex flex-col items-center text-center z-10">
                                <div class="w-9 h-9 rounded-full bg-white border-2 border-cozy-sand text-cozy-coffee/40 font-bold flex items-center justify-center text-xs mb-1.5">
                                    3
                                </div>
                                <span class="text-xs font-medium text-cozy-coffee/60">En camino</span>
                                <span class="text-[10px] text-cozy-coffee/40 hidden sm:block">Envío rápido 🚚</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overview key-values pills grid -->
                    <?php
                    $raw_payment_title   = $order->get_payment_method_title() ?: 'Confirmado';
                    $clean_payment_title = trim( wp_strip_all_tags( $raw_payment_title ) );
                    if ( empty( $clean_payment_title ) ) {
                        $clean_payment_title = 'Confirmado';
                    }
                    ?>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                        <div class="bg-cozy-cream/60 rounded-2xl p-3.5 sm:p-4 border border-cozy-sand/70">
                            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-cozy-coffee/50 block mb-0.5">Nº de pedido</span>
                            <span class="font-bold text-sm sm:text-base text-cozy-coffee block">#<?php echo esc_html( $order->get_order_number() ); ?></span>
                        </div>
                        <div class="bg-cozy-cream/60 rounded-2xl p-3.5 sm:p-4 border border-cozy-sand/70">
                            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-cozy-coffee/50 block mb-0.5">Fecha</span>
                            <span class="font-bold text-sm sm:text-base text-cozy-coffee block"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
                        </div>
                        <div class="bg-cozy-cream/60 rounded-2xl p-3.5 sm:p-4 border border-cozy-sand/70">
                            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-cozy-coffee/50 block mb-0.5">Total</span>
                            <span class="font-bold text-sm sm:text-base text-cozy-coffee block"><?php echo $order->get_formatted_order_total(); // phpcs:ignore ?></span>
                        </div>
                        <div class="bg-cozy-cream/60 rounded-2xl p-3.5 sm:p-4 border border-cozy-sand/70">
                            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-cozy-coffee/50 block mb-0.5">Método de pago</span>
                            <span class="font-bold text-sm sm:text-base text-cozy-coffee block truncate" title="<?php echo esc_attr( $clean_payment_title ); ?>">
                                <?php echo esc_html( $clean_payment_title ); ?>
                            </span>
                        </div>
                    </div>

                </div>

            <?php endif; ?>

            <!-- Payment instructions (BACS, Cheque, etc.) wrapped in card if present -->
            <?php
            ob_start();
            do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
            $gateway_output = ob_get_clean();
            if ( ! empty( trim( $gateway_output ) ) ) : ?>
                <div class="cozy-gateway-instructions bg-white rounded-[28px] sm:rounded-[32px] p-6 sm:p-8 border border-cozy-sand shadow-sm mb-8">
                    <?php echo $gateway_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endif; ?>

            <!-- Order Details Table & Customer Details -->
            <div class="cozy-order-details-wrap">
                <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
            </div>

            <!-- WhatsApp Support & Actions Banner -->
            <?php
            $cozy_whatsapp = get_option( 'cozy_whatsapp_number', '' );
            if ( ! empty( $cozy_whatsapp ) ) :
                $wa_url = 'https://wa.me/' . esc_attr( preg_replace( '/[^0-9]/', '', $cozy_whatsapp ) ) . '?text=' . rawurlencode( 'Hola! Tengo una duda sobre mi pedido #' . $order->get_order_number() );
            ?>
            <div class="bg-white rounded-[24px] p-6 border border-cozy-sand shadow-sm text-center my-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        💬
                    </div>
                    <div>
                        <h4 class="font-serif font-bold text-cozy-coffee text-base m-0">¿Tienes alguna duda sobre tu pedido?</h4>
                        <p class="text-xs text-cozy-coffee/70 m-0">Estamos disponibles en WhatsApp para atenderte de forma personalizada.</p>
                    </div>
                </div>
                <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold px-5 py-2.5 rounded-xl shadow-sm transition-all text-xs no-underline shrink-0">
                    Contactar por WhatsApp
                </a>
            </div>
            <?php endif; ?>

            <!-- Back to Shop Button -->
            <div class="text-center mt-8">
                <a href="<?php echo esc_url( $shop_url ); ?>"
                   class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold px-8 py-3.5 rounded-2xl shadow-md hover:shadow-lg transition-all no-underline transform hover:-translate-y-0.5">
                    <?php echo cozy_icon( 'basket-shopping', '16' ); ?>
                    Seguir explorando la boutique
                </a>
            </div>

        <?php else : ?>

            <!-- ==================================================== -->
            <!-- FALLBACK NO ORDER DATA                               -->
            <!-- ==================================================== -->
            <div class="bg-white rounded-[32px] p-8 sm:p-12 border border-cozy-sand shadow-sm text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-cozy-mintLight flex items-center justify-center text-cozy-mint border border-cozy-mint/30 shadow-sm">
                    <?php echo cozy_icon( 'check', '28' ); ?>
                </div>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-cozy-coffee mb-2">
                    ¡Muchas gracias por tu compra!
                </h1>
                <p class="text-sm text-cozy-coffee/70 mb-6">
                    Tu pedido ha sido recibido y se encuentra en proceso.
                </p>
                <a href="<?php echo esc_url( $shop_url ); ?>"
                   class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold px-7 py-3 rounded-2xl shadow-sm transition-all no-underline">
                    <?php echo cozy_icon( 'store', '16' ); ?>
                    Volver a la tienda
                </a>
            </div>

        <?php endif; ?>

    </div>

</div>
