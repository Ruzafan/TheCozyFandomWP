<?php
/**
 * My Account – Cozy Fandom Design
 * Template override: woocommerce/myaccount/my-account.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$display_name = $current_user->first_name
    ?: ( strpos( (string) $current_user->display_name, '@' ) === false ? $current_user->display_name : '' )
    ?: $current_user->user_login;
$shop_url     = get_permalink( wc_get_page_id( 'shop' ) );
?>

<div class="cozy-my-account py-12 px-6 md:px-12 max-w-7xl mx-auto">

    <!-- ============================================================ -->
    <!-- ACCOUNT HEADER                                                 -->
    <!-- ============================================================ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 shrink-0 rounded-[16px] bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-2xl">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-0.5">Mi cuenta</span>
                <h1 class="font-serif text-2xl md:text-3xl font-bold text-cozy-coffee">
                    Hola, <?php echo esc_html( $display_name ); ?> 🌿
                </h1>
            </div>
        </div>

        <a href="<?php echo esc_url( $shop_url ); ?>"
           class="self-start sm:self-auto flex items-center gap-2 bg-white border border-cozy-sand hover:bg-cozy-mintLight hover:border-cozy-mint text-cozy-coffee px-5 py-2.5 rounded-2xl text-xs font-bold transition-all no-underline">
            <i class="fa-solid fa-basket-shopping text-cozy-mint" aria-hidden="true"></i>
            Seguir comprando
        </a>

    </div>

    <!-- ============================================================ -->
    <!-- NAVIGATION (rendered by navigation.php override)              -->
    <!-- ============================================================ -->
    <?php do_action( 'woocommerce_account_navigation' ); ?>

    <!-- ============================================================ -->
    <!-- CONTENT AREA                                                   -->
    <!-- ============================================================ -->
    <div class="bg-white rounded-[32px] p-6 md:p-8 border border-cozy-sand shadow-sm cozy-account-content">
        <?php do_action( 'woocommerce_account_content' ); ?>
    </div>

</div>
