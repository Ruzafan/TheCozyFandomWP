<?php
/**
 * My Account Navigation – Cozy Fandom Design
 * Template override: woocommerce/myaccount/navigation.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

/* Icon map for known WooCommerce endpoints */
$cozy_nav_icons = [
    'dashboard'       => 'fa-house',
    'orders'          => 'fa-box',
    'downloads'       => 'fa-download',
    'edit-address'    => 'fa-location-dot',
    'edit-account'    => 'fa-user-pen',
    'payment-methods' => 'fa-credit-card',
    'customer-logout' => 'fa-right-from-bracket',
];

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="cozy-account-nav mb-8" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
    <ul class="flex flex-wrap gap-2 list-none m-0 p-0" role="list">
        <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
            $classes    = explode( ' ', wc_get_account_menu_item_classes( $endpoint ) );
            $is_active  = in_array( 'is-active', $classes, true );
            $icon       = isset( $cozy_nav_icons[ $endpoint ] ) ? $cozy_nav_icons[ $endpoint ] : 'fa-circle-dot';
            $is_logout  = $endpoint === 'customer-logout';
        ?>
        <li class="<?php echo $is_logout ? 'ml-auto' : ''; ?> m-0 p-0">
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
               class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all no-underline
                      <?php if ( $is_logout ) : ?>
                          bg-white border border-cozy-sand text-cozy-coffee/60 hover:border-red-200 hover:text-red-400 hover:bg-red-50
                      <?php elseif ( $is_active ) : ?>
                          bg-cozy-mint text-cozy-coffee shadow-md
                      <?php else : ?>
                          bg-white border border-cozy-sand text-cozy-coffee/80 hover:bg-cozy-mintLight hover:border-cozy-mint hover:text-cozy-coffee
                      <?php endif; ?>">
                <i class="fa-solid <?php echo esc_attr( $icon ); ?> text-sm" aria-hidden="true"></i>
                <?php echo esc_html( $label ); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
