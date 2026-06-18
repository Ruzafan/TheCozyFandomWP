<?php
/**
 * Cozy Fandom Child – Custom Header
 * Two-row layout: [Logo + Search + Icons] / [Category nav]
 *
 * @package cozy-fandom-child
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="hfeed site ast-page-builder-template">
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'astra' ); ?></a>

<!-- ============================================================ -->
<!-- HEADER                                                         -->
<!-- ============================================================ -->
<header id="masthead" class="site-header ast-primary-header" itemtype="https://schema.org/WPHeader" itemscope>

    <!-- ── Row 1: Logo · Search · Icons ───────────────────────── -->
    <div class="ast-primary-header-bar">
        <div class="cozy-hdr-inner">

            <!-- Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-hdr-logo" aria-label="<?php bloginfo( 'name' ); ?>">
                <?php
                $logo_id = get_theme_mod( 'custom_logo' );
                if ( $logo_id ) :
                    echo wp_get_attachment_image( $logo_id, array( 320, 80 ), false, [
                        'class' => 'cozy-hdr-logo__img',
                        'alt'   => get_bloginfo( 'name' ),
                    ] );
                else : ?>
                <span class="cozy-hdr-logo__text">🌿 <?php bloginfo( 'name' ); ?></span>
                <?php endif; ?>
            </a>

            <!-- Search -->
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-hdr-search">
                <input type="search" name="s"
                       value="<?php echo esc_attr( get_search_query() ); ?>"
                       placeholder="<?php esc_attr_e( 'Buscar productos...', 'woocommerce' ); ?>"
                       class="cozy-hdr-search__input">
                <input type="hidden" name="post_type" value="product">
                <button type="submit" class="cozy-hdr-search__btn" aria-label="<?php esc_attr_e( 'Buscar', 'woocommerce' ); ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
            </form>

            <!-- Icons -->
            <div class="cozy-hdr-actions">

                <?php $account_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : home_url( '/' ); ?>
                <a href="<?php echo esc_url( $account_url ); ?>" class="cozy-hdr-icon" aria-label="<?php esc_attr_e( 'Mi cuenta', 'woocommerce' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>

                <button onclick="openCart()" class="cozy-hdr-icon cozy-hdr-cart" aria-label="<?php esc_attr_e( 'Carrito', 'woocommerce' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <?php if ( class_exists( 'WooCommerce' ) && WC()->cart ) :
                        $count = WC()->cart->get_cart_contents_count(); ?>
                    <span id="cart-badge" class="<?php echo $count > 0 ? '' : 'hidden '; ?>absolute -top-0.5 -right-0.5 bg-cozy-mint text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm">
                        <?php echo absint( $count ); ?>
                    </span>
                    <?php endif; ?>
                </button>

                <!-- Mobile hamburger -->
                <button onclick="toggleMobileMenu()" class="cozy-hdr-icon cozy-hdr-hamburger" aria-label="Menú" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>

            </div>
        </div>
    </div>

    <!-- ── Row 2: Category nav ─────────────────────────────────── -->
    <div class="cozy-hdr-nav-row">
        <div class="cozy-hdr-nav-inner">
            <nav id="mobile-menu" class="cozy-hdr-nav" aria-label="Navegación principal">
                <?php
                $uncategorised_id = absint( get_option( 'default_product_cat' ) );
                $nav_cats = get_terms( [
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'number'     => 10,
                ] );
                $current_cat = is_product_category() ? get_queried_object() : null;

                if ( ! is_wp_error( $nav_cats ) ) :
                    foreach ( $nav_cats as $cat ) :
                        $cat_url   = get_term_link( $cat );
                        $is_active = $current_cat && $current_cat->term_id === $cat->term_id;
                        if ( is_wp_error( $cat_url ) ) continue;
                        ?>
                        <a href="<?php echo esc_url( $cat_url ); ?>"
                           class="cozy-nav-link<?php echo $is_active ? ' cozy-nav-link--active' : ''; ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </a>
                        <?php
                    endforeach;
                endif;

                // Licencias dropdown
                $nav_licenses = get_terms( [ 'taxonomy' => 'product_licencia', 'hide_empty' => false ] );
                if ( ! is_wp_error( $nav_licenses ) && ! empty( $nav_licenses ) ) :
                    $raw_lic     = isset( $_GET['licencia'] ) ? sanitize_text_field( wp_unslash( $_GET['licencia'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
                    $active_lics = $raw_lic ? array_filter( array_map( 'sanitize_title', explode( ',', $raw_lic ) ) ) : [];
                    $has_act_lic = ! empty( $active_lics );
                    $shop_url    = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
                    ?>
                    <div class="cozy-nav-item cozy-nav-has-dropdown">
                        <button class="cozy-nav-link cozy-nav-link--has-arrow<?php echo $has_act_lic ? ' cozy-nav-link--active' : ''; ?>"
                                aria-haspopup="true" aria-expanded="false"
                                onclick="cozyToggleDropdown(this)">
                            Licencias
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="cozy-nav-dropdown" role="menu">
                            <?php foreach ( $nav_licenses as $lic ) :
                                $is_lic_active = in_array( $lic->slug, $active_lics, true );
                                $lic_href      = add_query_arg( 'licencia', $lic->slug, remove_query_arg( 'licencia', $shop_url ) );
                            ?>
                            <a href="<?php echo esc_url( $lic_href ); ?>"
                               class="cozy-nav-dropdown__link<?php echo $is_lic_active ? ' is-active' : ''; ?>"
                               role="menuitem">
                                <?php if ( $is_lic_active ) : ?>
                                <span class="cozy-nav-dropdown__check" aria-hidden="true">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                </span>
                                <?php endif; ?>
                                <?php echo esc_html( $lic->name ); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php
                endif;
                ?>
            </nav>
        </div>
    </div>

</header>

<!-- ============================================================ -->
<!-- CART DRAWER                                                    -->
<!-- ============================================================ -->
<div id="cart-overlay"
     class="hidden fixed inset-0 bg-cozy-coffee/30 z-[1000] backdrop-blur-sm"
     onclick="closeCart()" aria-hidden="true"></div>

<div id="cart-drawer"
     class="translate-x-full fixed top-0 right-0 h-full w-full max-w-sm bg-white z-[1001] flex flex-col shadow-2xl"
     role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Carrito de compra', 'woocommerce' ); ?>">

    <div class="flex items-center justify-between p-6 border-b border-cozy-sand">
        <h2 class="font-serif text-xl font-bold text-cozy-coffee">Tu carrito</h2>
        <button onclick="closeCart()"
                class="w-9 h-9 rounded-full bg-cozy-cream hover:bg-cozy-sand flex items-center justify-center text-cozy-coffee transition-colors"
                aria-label="Cerrar carrito">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <?php cozy_render_mini_cart(); ?>

    <div class="p-6 border-t border-cozy-sand bg-white space-y-3 mt-auto">
        <div class="flex items-center justify-between text-sm font-medium text-cozy-coffee/70">
            <span>Subtotal</span>
            <?php if ( class_exists( 'WooCommerce' ) && WC()->cart ) : ?>
            <span id="cart-total" class="text-lg text-cozy-coffee"><?php echo WC()->cart->get_cart_total(); // phpcs:ignore ?></span>
            <?php endif; ?>
        </div>
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
           class="block w-full text-center bg-cozy-mint hover:bg-cozy-mintDark text-white font-bold py-3.5 rounded-2xl transition-colors text-sm">
            Finalizar pedido
        </a>
        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
           class="block w-full text-center border border-cozy-sand hover:border-cozy-coffee text-cozy-coffee font-medium py-3 rounded-2xl transition-colors text-sm">
            Ver carrito
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================ -->
<!-- PAGE WRAPPER (closed by footer.php)                           -->
<!-- ============================================================ -->
<div id="content" class="site-content">
<div class="ast-container">
<div id="primary" class="content-area primary-content-area">
<main id="main" class="site-main" role="main">
