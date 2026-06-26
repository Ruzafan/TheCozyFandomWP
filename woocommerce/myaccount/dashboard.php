<?php
/**
 * My Account Dashboard – Cozy Fandom Design
 * Template override: woocommerce/myaccount/dashboard.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$raw_name      = $current_user->first_name
    ?: ( strpos( (string) $current_user->display_name, '@' ) === false ? $current_user->display_name : '' )
    ?: $current_user->user_login;
$display_name  = $raw_name;

/* Stats */
$order_count = wc_get_customer_order_count( $current_user->ID );
$total_spent = wc_price( wc_get_customer_total_spent( $current_user->ID ) );

/* Recent orders (last 3) */
$recent_orders = wc_get_orders( [
    'customer' => $current_user->ID,
    'limit'    => 3,
    'orderby'  => 'date',
    'order'    => 'DESC',
] );

do_action( 'woocommerce_before_account_dashboard' );
?>

<!-- ============================================================ -->
<!-- RECENT ORDERS                                                  -->
<!-- ============================================================ -->
<div class="mb-10">

    <div class="flex items-center justify-between mb-5">
        <h2 class="font-serif text-lg font-bold text-cozy-coffee">Pedidos recientes</h2>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"
           class="text-xs text-cozy-mint hover:text-cozy-mintDark font-bold transition-colors no-underline">
            Ver todos <?php echo cozy_icon( 'arrow-right-long', '12' ); ?>
        </a>
    </div>

    <?php if ( $recent_orders ) : ?>
    <div class="space-y-3">
        <?php foreach ( $recent_orders as $order ) :
            $status       = $order->get_status();
            $status_label = wc_get_order_status_name( $status );

            $status_styles = [
                'completed'  => 'bg-green-50  text-green-700  border-green-100',
                'processing' => 'bg-cozy-mintLight text-cozy-mint border-cozy-mint/20',
                'pending'    => 'bg-amber-50   text-amber-600  border-amber-100',
                'cancelled'  => 'bg-red-50     text-red-500    border-red-100',
                'refunded'   => 'bg-gray-50    text-gray-500   border-gray-100',
                'on-hold'    => 'bg-orange-50  text-orange-500 border-orange-100',
            ];
            $badge = isset( $status_styles[ $status ] ) ? $status_styles[ $status ] : 'bg-cozy-sand text-cozy-coffee border-cozy-sand';
        ?>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-cozy-cream border border-cozy-sand hover:border-cozy-mint transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 shrink-0 rounded-xl bg-white border border-cozy-sand flex items-center justify-center text-cozy-coffee/40">
                    <?php echo cozy_icon( 'bag-shopping', '14' ); ?>
                </div>
                <div>
                    <span class="block text-xs font-bold text-cozy-coffee">
                        <?php echo esc_html( sprintf( __( 'Pedido #%s', 'woocommerce' ), $order->get_order_number() ) ); ?>
                    </span>
                    <span class="text-[11px] text-cozy-coffee/50">
                        <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                <span class="text-sm font-bold text-cozy-coffee"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border <?php echo esc_attr( $badge ); ?>">
                    <?php echo esc_html( $status_label ); ?>
                </span>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"
                   class="text-xs text-cozy-coffee/60 hover:text-cozy-mint font-medium transition-colors no-underline">
                    Ver →
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
    <div class="text-center py-12 border-2 border-dashed border-cozy-sand rounded-2xl">
        <?php echo cozy_icon( 'box-open', '40', 'text-cozy-coffee/20 block mb-3' ); ?>
        <p class="text-sm text-cozy-coffee/60 mb-4">Aún no tienes pedidos. ¡Es hora de explorar!</p>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
           class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-semibold px-6 py-3 rounded-2xl text-xs transition-all no-underline">
            <?php echo cozy_icon( 'basket-shopping', '14' ); ?> Ver la tienda
        </a>
    </div>
    <?php endif; ?>

</div>

<!-- ============================================================ -->
<!-- QUICK LINKS                                                    -->
<!-- ============================================================ -->
<div>
    <h2 class="font-serif text-lg font-bold text-cozy-coffee mb-5">Accesos rápidos</h2>
    <div class="cozy-quicklinks-grid">

        <?php
        $quick_links = [
            [
                'url'   => wc_get_account_endpoint_url( 'edit-address' ),
                'icon'  => 'location-dot',
                'title' => 'Mis Direcciones',
                'desc'  => 'Gestiona dónde recibir tus pedidos.',
            ],
            [
                'url'   => wc_get_account_endpoint_url( 'edit-account' ),
                'icon'  => 'user-pen',
                'title' => 'Mis Datos',
                'desc'  => 'Actualiza tu información personal.',
            ],
           /* [
                'url'   => wc_get_account_endpoint_url( 'downloads' ),
                'icon'  => 'download',
                'title' => 'Descargas',
                'desc'  => 'Accede a tus productos digitales.',
            ],*/
        ];
        foreach ( $quick_links as $ql ) : ?>
        <a href="<?php echo esc_url( $ql['url'] ); ?>"
           class="group bg-cozy-cream hover:bg-white border border-cozy-sand hover:border-cozy-mint rounded-[24px] p-5 flex items-start gap-4 transition-all no-underline hover:shadow-md">
            <div class="w-10 h-10 shrink-0 rounded-2xl bg-white group-hover:bg-cozy-mintLight flex items-center justify-center text-cozy-mint transition-colors">
                <?php echo cozy_icon( $ql['icon'], '16' ); ?>
            </div>
            <div>
                <span class="block text-xs font-bold text-cozy-coffee mb-0.5"><?php echo esc_html( $ql['title'] ); ?></span>
                <span class="text-[11px] text-cozy-coffee/60 leading-relaxed"><?php echo esc_html( $ql['desc'] ); ?></span>
            </div>
        </a>
        <?php endforeach; ?>

    </div>
</div>

<?php do_action( 'woocommerce_after_account_dashboard' ); ?>
