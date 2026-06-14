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

<div class="cozy-my-account py-12 px-6 md:px-12 max-w-7xl mx-auto relative overflow-hidden">

    <!-- Decorative background blobs -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
    <div class="absolute bottom-10 left-10 w-72 h-72 bg-cozy-accent/5 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

    <!-- ============================================================ -->
    <!-- ACCOUNT HEADER                                                 -->
    <!-- ============================================================ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 shrink-0 rounded-[16px] bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-2xl border border-cozy-mint/20 shadow-sm">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-1">
                    🌿 Mi cuenta
                </div>
                <h1 class="font-serif text-2xl md:text-3xl font-bold text-cozy-coffee">
                    Hola, <?php echo esc_html( $display_name ); ?> ✨
                </h1>
            </div>
        </div>

        <a href="<?php echo esc_url( $shop_url ); ?>"
           class="self-start sm:self-auto flex items-center gap-2 bg-white border border-cozy-sand hover:bg-cozy-mintLight hover:border-cozy-mint text-cozy-coffee px-5 py-2.5 rounded-2xl text-xs font-bold transition-all no-underline shadow-sm">
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
