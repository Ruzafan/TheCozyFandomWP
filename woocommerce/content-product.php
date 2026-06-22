<?php
/**
 * Product Card – Cozy Fandom Design
 * Template override: woocommerce/content-product.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>

<li <?php wc_product_class( 'bg-white rounded-[24px] overflow-hidden border border-cozy-sand shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col', $product ); ?>>

    <!-- Full-bleed image -->
    <div class="relative">
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block no-underline">
            <div class="bg-cozy-cream h-36 sm:h-44 overflow-hidden">
                <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); ?>
            </div>
        </a>

        <?php if ( $product->is_on_sale() ) : ?>
        <span class="absolute top-2 left-2 bg-cozy-mint text-cozy-coffee text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider pointer-events-none z-[1]">
            <?php esc_html_e( 'Oferta', 'woocommerce' ); ?>
        </span>
        <?php elseif ( ! $product->is_in_stock() ) : ?>
        <span class="absolute top-2 left-2 bg-cozy-sand text-cozy-coffee/70 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider pointer-events-none z-[1]">
            <?php esc_html_e( 'Agotado', 'woocommerce' ); ?>
        </span>
        <?php endif; ?>

        <button onclick="toggleFavorite(<?php echo absint( $product->get_id() ); ?>)"
                class="cozy-fav-btn cozy-fav-icon absolute top-2 right-2 z-10 w-7 h-7 bg-white/80 backdrop-blur-sm flex items-center justify-center text-cozy-coffee/40 hover:text-red-400 hover:bg-white shadow-sm"
                data-product-id="<?php echo absint( $product->get_id() ); ?>"
                aria-label="<?php esc_attr_e( 'Guardar en favoritos', 'woocommerce' ); ?>">
            <svg class="cozy-fav-heart" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
    </div>

    <!-- Content -->
    <div class="p-2.5 sm:p-4 flex flex-col gap-2 flex-grow">

        <h3 class="font-bold text-[10px] sm:text-[11px] text-cozy-coffee/80 uppercase line-clamp-2 leading-snug flex-grow m-0">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
               class="hover:text-cozy-mint transition-colors no-underline">
                <?php echo esc_html( $product->get_name() ); ?>
            </a>
        </h3>

        <div class="flex items-center justify-between gap-1.5 pt-2 border-t border-cozy-sand/50">
            <span class="text-sm font-bold text-cozy-coffee shrink-0">
                <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
            <?php $is_ajax = $product->is_type( 'simple' ) && $product->is_in_stock() && $product->is_purchasable(); ?>
            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
               <?php if ( $is_ajax ) : ?>
               data-product_id="<?php echo absint( $product->get_id() ); ?>"
               data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
               data-quantity="1"
               <?php endif; ?>
               class="<?php echo $product->is_in_stock() ? 'bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee' : 'bg-cozy-sand text-cozy-coffee/60 pointer-events-none'; ?> <?php echo $is_ajax ? 'add_to_cart_button ajax_add_to_cart' : ''; ?> px-2 sm:px-2.5 py-1.5 rounded-full text-[10px] font-bold transition-colors flex items-center gap-1 no-underline shrink-0">
                <i class="fa-solid <?php echo $is_ajax ? 'fa-basket-shopping' : ( $product->is_in_stock() ? 'fa-eye' : 'fa-ban' ); ?> text-[9px]" aria-hidden="true"></i>
                <span class="hidden sm:inline"><?php echo $is_ajax ? 'Añadir al carrito' : ( $product->is_in_stock() ? 'Ver opciones' : 'Sin stock' ); ?></span>
                <span class="sm:hidden"><?php echo $is_ajax ? '+' : ( $product->is_in_stock() ? 'Ver' : '—' ); ?></span>
            </a>
        </div>

    </div>

</li>
