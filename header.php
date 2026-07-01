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

    <!-- ── Announcement bar ──────────────────────────────────── -->
    <div class="cozy-announcement-bar">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        Envíos gratis en pedidos de más de <strong>60 €</strong>
    </div>

    <!-- ── Row 1: Logo · Search · Icons ───────────────────────── -->
    <div class="ast-primary-header-bar">
        <div class="cozy-hdr-inner">

            <!-- Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-hdr-logo" aria-label="<?php bloginfo( 'name' ); ?>">
                <?php
                $logo_id = get_theme_mod( 'custom_logo' );
                if ( $logo_id ) :
                    echo wp_get_attachment_image( $logo_id, array( 80, 80 ), false, [
                        'class' => 'cozy-hdr-logo__img',
                        'alt'   => get_bloginfo( 'name' ),
                    ] );
                endif; ?>
                <div class="cozy-hdr-logo__text-wrap">
                    <span class="cozy-hdr-logo__name"><?php bloginfo( 'name' ); ?></span>
                    <?php if ( get_bloginfo( 'description' ) ) : ?>
                    <span class="cozy-hdr-logo__tagline"><?php bloginfo( 'description' ); ?></span>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Search -->
            <div class="cozy-hdr-search-wrap relative flex-1 min-w-0">
                <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-hdr-search">
                    <input type="search" name="s"
                           value="<?php echo esc_attr( get_search_query() ); ?>"
                           placeholder="<?php esc_attr_e( 'Buscar productos...', 'woocommerce' ); ?>"
                           class="cozy-hdr-search__input"
                           autocomplete="off">
                    <input type="hidden" name="post_type" value="product">
                    <button type="submit" class="cozy-hdr-search__btn" aria-label="<?php esc_attr_e( 'Buscar', 'woocommerce' ); ?>">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </button>
                </form>
                <div id="cozy-search-suggestions" class="cozy-search-suggestions hidden"></div>
            </div>

            <!-- Icons -->
            <div class="cozy-hdr-actions">

                <?php
                $account_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : home_url( '/' );
                $fav_user_id = get_current_user_id();
                $fav_ids_raw = is_user_logged_in() ? (array) get_user_meta( $fav_user_id, '_cozy_wishlist', true ) : [];
                $fav_ids     = array_values( array_filter( array_map( 'absint', $fav_ids_raw ) ) );
                $fav_count   = count( $fav_ids );
                ?>

                <!-- Desktop-only icons (hidden on mobile — appear inside sidebar) -->
                <div class="cozy-hdr-actions-icons">
                    <a href="<?php echo esc_url( $account_url ); ?>" class="cozy-hdr-icon" aria-label="<?php esc_attr_e( 'Mi cuenta', 'woocommerce' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>

                    <button onclick="openFavorites()" class="cozy-hdr-icon relative" aria-label="Mis favoritos">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <span id="fav-badge" class="<?php echo $fav_count > 0 ? '' : 'hidden '; ?>absolute -top-0.5 -right-0.5 bg-red-400 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm">
                            <?php echo absint( $fav_count ); ?>
                        </span>
                    </button>

                    <button onclick="openCart()" class="cozy-hdr-icon cozy-hdr-cart" aria-label="<?php esc_attr_e( 'Carrito', 'woocommerce' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <?php if ( class_exists( 'WooCommerce' ) && WC()->cart ) :
                            $count = WC()->cart->get_cart_contents_count(); ?>
                        <span id="cart-badge" class="<?php echo $count > 0 ? '' : 'hidden '; ?>absolute -top-0.5 -right-0.5 bg-cozy-mint text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm">
                            <?php echo absint( $count ); ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Hamburger (always visible — opens sidebar on mobile) -->
                <button onclick="toggleMobileMenu()" class="cozy-hdr-icon cozy-hdr-hamburger" aria-label="Menú" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>

            </div>
        </div>
    </div>

</header>

<!-- ── Row 2: Category nav (desktop) / Sidebar drawer (mobile) ──
     Intentionally outside <header> so its z-index participates in the
     root stacking context, not masthead's (which would cap it at z-index 999).
-->
<div class="cozy-hdr-nav-row" id="cozy-nav-sidebar">

        <!-- Close button + title — only rendered on mobile via CSS -->
        <div class="cozy-mobile-nav-header">
            <span class="cozy-mobile-nav-title">Menú</span>
            <button onclick="closeMobileMenu()" class="cozy-mobile-nav-close" aria-label="Cerrar menú">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Quick-access icons inside sidebar (mobile only) -->
        <div class="cozy-mobile-nav-actions">
            <a href="<?php echo esc_url( $account_url ); ?>" class="cozy-mobile-nav-action-btn" aria-label="<?php esc_attr_e( 'Mi cuenta', 'woocommerce' ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="cozy-mobile-nav-action-label">Mi cuenta</span>
            </a>
            <button onclick="closeMobileMenu(); setTimeout(openFavorites, 320);" class="cozy-mobile-nav-action-btn" aria-label="Mis favoritos">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <?php if ( $fav_count > 0 ) : ?>
                <span class="cozy-mobile-action-badge"><?php echo absint( $fav_count ); ?></span>
                <?php endif; ?>
                <span class="cozy-mobile-nav-action-label">Favoritos</span>
            </button>
            <button onclick="closeMobileMenu(); setTimeout(openCart, 320);" class="cozy-mobile-nav-action-btn" aria-label="<?php esc_attr_e( 'Carrito', 'woocommerce' ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <?php if ( class_exists( 'WooCommerce' ) && WC()->cart ) :
                    $mob_cart_n = WC()->cart->get_cart_contents_count();
                    if ( $mob_cart_n > 0 ) : ?>
                <span class="cozy-mobile-action-badge"><?php echo absint( $mob_cart_n ); ?></span>
                <?php endif; endif; ?>
                <span class="cozy-mobile-nav-action-label">Carrito</span>
            </button>
        </div>

        <div class="cozy-hdr-nav-inner">
            <nav id="mobile-menu" class="cozy-hdr-nav" aria-label="Navegación principal">
                <?php
                $shop_url    = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
                $is_shop_pg  = is_shop() && ! is_product_category();
                ?>
                <a href="<?php echo esc_url( $shop_url ); ?>"
                   class="cozy-nav-link<?php echo $is_shop_pg ? ' cozy-nav-link--active' : ''; ?>">
                    Tienda
                </a>
                <?php
                $uncategorised_id = absint( get_option( 'default_product_cat' ) );
                $nav_cats = get_terms( [
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
                    'parent'     => 0,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'number'     => 10,
                ] );
                $current_cat = is_product_category() ? get_queried_object() : null;

                if ( ! is_wp_error( $nav_cats ) ) :
                    foreach ( $nav_cats as $cat ) :
                        $cat_url = get_term_link( $cat );
                        if ( is_wp_error( $cat_url ) ) continue;

                        $children = get_terms( [
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => $cat->term_id,
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                        ] );
                        $has_children = ! is_wp_error( $children ) && ! empty( $children );

                        $is_active = $current_cat && (
                            $current_cat->term_id === $cat->term_id ||
                            $current_cat->parent  === $cat->term_id
                        );

                        if ( $has_children ) : ?>
                        <div class="cozy-nav-item cozy-nav-has-dropdown">
                            <button class="cozy-nav-link cozy-nav-link--has-arrow<?php echo $is_active ? ' cozy-nav-link--active' : ''; ?>"
                                    aria-haspopup="true" aria-expanded="false"
                                    onclick="cozyToggleDropdown(this)">
                                <?php echo esc_html( $cat->name ); ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="cozy-nav-dropdown" role="menu">
                                <a href="<?php echo esc_url( $cat_url ); ?>"
                                   class="cozy-nav-dropdown__link cozy-nav-dropdown__link--all<?php echo ( $current_cat && $current_cat->term_id === $cat->term_id ) ? ' is-active' : ''; ?>"
                                   role="menuitem">
                                    Ver todos
                                </a>
                                <div class="cozy-nav-dropdown__divider"></div>
                                <?php foreach ( $children as $child ) :
                                    $child_url = get_term_link( $child );
                                    if ( is_wp_error( $child_url ) ) continue;
                                    $is_child_active = $current_cat && $current_cat->term_id === $child->term_id;
                                ?>
                                <a href="<?php echo esc_url( $child_url ); ?>"
                                   class="cozy-nav-dropdown__link<?php echo $is_child_active ? ' is-active' : ''; ?>"
                                   role="menuitem">
                                    <?php if ( $is_child_active ) : ?>
                                    <span class="cozy-nav-dropdown__check" aria-hidden="true">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1.5 5 3.8 7.5 8.5 2.5"/></svg>
                                    </span>
                                    <?php endif; ?>
                                    <?php echo esc_html( $child->name ); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else :
                            $is_active_plain = $current_cat && $current_cat->term_id === $cat->term_id;
                        ?>
                        <a href="<?php echo esc_url( $cat_url ); ?>"
                           class="cozy-nav-link<?php echo $is_active_plain ? ' cozy-nav-link--active' : ''; ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </a>
                        <?php endif;
                    endforeach;
                endif;

                // Licencias dropdown
                $nav_licenses = get_terms( [ 'taxonomy' => 'product_brand', 'hide_empty' => false ] );
                if ( ! is_wp_error( $nav_licenses ) && ! empty( $nav_licenses ) ) :
                    $raw_lic     = isset( $_GET['licencia'] ) ? sanitize_text_field( wp_unslash( $_GET['licencia'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
                    $active_lics = $raw_lic ? array_filter( array_map( 'sanitize_title', explode( ',', $raw_lic ) ) ) : [];
                    $has_act_lic = ! empty( $active_lics );
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
                                $lic_href      = $is_lic_active
                                    ? remove_query_arg( 'licencia', $shop_url )
                                    : add_query_arg( 'licencia', $lic->slug, remove_query_arg( 'licencia', $shop_url ) );
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

<!-- Mobile menu overlay (closes sidebar when clicked) -->
<div id="mobile-menu-overlay" onclick="closeMobileMenu()" aria-hidden="true"></div>

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
<!-- FAVORITES DRAWER                                               -->
<!-- ============================================================ -->
<div id="fav-overlay"
     class="hidden fixed inset-0 bg-cozy-coffee/30 z-[1000] backdrop-blur-sm"
     onclick="closeFavorites()" aria-hidden="true"></div>

<div id="fav-drawer"
     class="translate-x-full fixed top-0 right-0 h-full w-full max-w-sm bg-white z-[1001] flex flex-col shadow-2xl"
     role="dialog" aria-modal="true" aria-label="Mis favoritos">

    <div class="flex items-center justify-between p-6 border-b border-cozy-sand">
        <h2 class="font-serif text-xl font-bold text-cozy-coffee">Mis favoritos</h2>
        <button onclick="closeFavorites()"
                class="w-9 h-9 rounded-full bg-cozy-cream hover:bg-cozy-sand flex items-center justify-center text-cozy-coffee transition-colors"
                aria-label="Cerrar favoritos">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <div id="fav-items" class="p-6 overflow-y-auto flex-grow">
        <?php if ( is_user_logged_in() ) :
            if ( ! empty( $fav_ids ) ) :
                foreach ( $fav_ids as $fav_pid ) :
                    cozy_render_favorite_item( $fav_pid );
                endforeach;
            else : ?>
            <div id="fav-empty" class="text-center py-12 space-y-4">
                <svg class="mx-auto text-cozy-coffee/20" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <p class="text-sm text-cozy-coffee/60">Aún no tienes favoritos guardados.</p>
                <button onclick="closeFavorites()" class="text-xs font-bold text-cozy-mint hover:underline">¡Descubre la tienda!</button>
            </div>
            <?php endif;
        else : ?>
        <div class="text-center py-12 space-y-4">
            <svg class="mx-auto text-cozy-coffee/20" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <p class="text-sm text-cozy-coffee/60">Inicia sesión para ver tus favoritos.</p>
            <a href="<?php echo esc_url( $account_url ); ?>"
               class="inline-block text-xs font-bold bg-cozy-mint text-white px-4 py-2 rounded-xl hover:bg-cozy-mintDark transition-colors no-underline">
                Iniciar sesión
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="p-6 border-t border-cozy-sand bg-white mt-auto">
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
           onclick="closeFavorites()"
           class="block w-full text-center border border-cozy-sand hover:border-cozy-coffee text-cozy-coffee font-medium py-3 rounded-2xl transition-colors text-sm no-underline">
            Seguir explorando
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================ -->
<!-- LOGIN MODAL (for non-logged users trying to favorite)         -->
<!-- ============================================================ -->
<div id="login-modal-overlay"
     class="hidden fixed inset-0 bg-cozy-coffee/50 z-[2000] flex items-center justify-center p-4 backdrop-blur-sm"
     onclick="closeLoginModal()">
    <div class="bg-white rounded-[32px] p-8 max-w-sm w-full shadow-2xl relative text-center"
         onclick="event.stopPropagation()">
        <button onclick="closeLoginModal()"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-cozy-cream hover:bg-cozy-sand flex items-center justify-center text-cozy-coffee transition-colors"
                aria-label="Cerrar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>

        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>

        <h3 class="font-serif text-xl font-bold text-cozy-coffee mb-2">Guarda tus favoritos</h3>
        <p class="text-sm text-cozy-coffee/60 mb-6 leading-relaxed">
            Inicia sesión para guardar productos y acceder a ellos cuando quieras.
        </p>

        <div class="space-y-3">
            <a href="<?php echo esc_url( $account_url ); ?>"
               class="block w-full text-center bg-cozy-mint hover:bg-cozy-mintDark text-white font-bold py-3.5 rounded-2xl transition-colors text-sm no-underline">
                Iniciar sesión
            </a>
            <a href="<?php echo esc_url( add_query_arg( 'action', 'register', $account_url ) ); ?>"
               class="block w-full text-center border border-cozy-sand hover:border-cozy-coffee text-cozy-coffee font-medium py-3 rounded-2xl transition-colors text-sm no-underline">
                Crear cuenta gratis
            </a>
        </div>
    </div>
</div>

<?php
$cozy_whatsapp = get_option( 'cozy_whatsapp_number', '' );
if ( ! empty( $cozy_whatsapp ) ) :
    $cozy_whatsapp_url = 'https://wa.me/' . esc_attr( preg_replace( '/[^0-9]/', '', $cozy_whatsapp ) ) . '?text=' . rawurlencode( 'Hola, me gustaría obtener más información.' );
    ?>
    <a href="<?php echo esc_url( $cozy_whatsapp_url ); ?>"
       target="_blank" rel="noopener noreferrer"
       aria-label="Contactar por WhatsApp"
       class="fixed bottom-6 right-6 z-[999] w-14 h-14 rounded-full shadow-lg hover:shadow-xl flex items-center justify-center transition-all duration-200 group"
       style="background-color:#25D366">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        <span class="absolute right-full mr-3 bg-cozy-coffee text-white text-xs font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none shadow-md">
            Contáctanos
        </span>
    </a>
<?php endif; ?>

<!-- ============================================================ -->
<!-- PAGE WRAPPER (closed by footer.php)                           -->
<!-- ============================================================ -->
<div id="content" class="site-content">
<div class="ast-container">
<div id="primary" class="content-area primary-content-area">
<main id="main" class="site-main" role="main">
