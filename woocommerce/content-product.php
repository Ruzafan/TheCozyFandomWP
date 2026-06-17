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

// Category label
$cat_ids  = $product->get_category_ids();
$cat_name = '';
if ( ! empty( $cat_ids ) ) {
    $term = get_term( reset( $cat_ids ), 'product_cat' );
    if ( $term && ! is_wp_error( $term ) ) {
        $cat_name = $term->name;
    }
}
?>

<li <?php wc_product_class( 'bg-white rounded-[24px] p-4 border border-cozy-sand shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col', $product ); ?>>

    <!-- Product image -->
    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block no-underline">
        <div class="bg-cozy-cream rounded-2xl h-56 flex items-center justify-center overflow-hidden mb-4 relative">
            <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); ?>
            <?php if ( $product->is_on_sale() ) : ?>
            <span class="absolute top-3 left-3 bg-cozy-mint text-cozy-coffee text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                <?php esc_html_e( 'Oferta', 'woocommerce' ); ?>
            </span>
            <?php endif; ?>
            <?php if ( ! $product->is_in_stock() ) : ?>
            <span class="absolute top-3 right-3 bg-cozy-sand text-cozy-coffee/70 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                <?php esc_html_e( 'Agotado', 'woocommerce' ); ?>
            </span>
            <?php endif; ?>
        </div>
    </a>

    <!-- Text content (fills available space, pushing CTA to bottom) -->
    <div class="flex-grow">
        <?php if ( $cat_name ) : ?>
        <span class="text-[10px] text-cozy-mint font-bold uppercase tracking-wider block mb-1">
            <?php echo esc_html( $cat_name ); ?>
        </span>
        <?php endif; ?>

        <h3 class="font-bold text-sm text-cozy-coffee line-clamp-2 mb-0">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
               class="hover:text-cozy-mint transition-colors no-underline">
                <?php echo esc_html( $product->get_name() ); ?>
            </a>
        </h3>

        <?php if ( $product->get_short_description() ) : ?>
        <p class="text-[11px] text-cozy-coffee/60 mt-1 line-clamp-2 leading-relaxed m-0">
            <?php echo wp_strip_all_tags( $product->get_short_description() ); ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- Price + Add to cart (always at bottom) -->
    <div class="flex items-center justify-between pt-4 border-t border-cozy-sand mt-4">

        <span class="text-base font-bold text-cozy-coffee">
            <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </span>

        <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
           class="<?php echo $product->is_in_stock() ? 'bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee hover:text-white' : 'bg-cozy-sand text-cozy-coffee/60'; ?> px-4 py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 no-underline">
            <i class="fa-solid fa-eye text-[10px]" aria-hidden="true"></i>
            <?php esc_html_e( 'Ver producto', 'woocommerce' ); ?>
        </a>

    </div>


</li>
