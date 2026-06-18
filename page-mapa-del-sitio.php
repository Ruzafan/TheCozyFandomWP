<?php
/**
 * Template Name: Mapa del sitio
 * Sitemap page — HTML sitemap for users and search engines.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div class="max-w-5xl mx-auto px-6 md:px-10 py-14 md:py-20">

    <div class="mb-10">
        <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Navega por la tienda</span>
        <h1 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Mapa del sitio</h1>
        <p class="text-sm text-cozy-coffee/65 mt-3 max-w-lg">Encuentra rápidamente cualquier sección de The Cozy Fandom.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

        <?php
        /* ---- helper to render a section card ---- */
        function cozy_sitemap_section( $title, $icon_class, $links ) {
            ?>
            <div class="bg-white rounded-[20px] border border-cozy-sand p-6 shadow-sm">
                <h2 class="font-serif font-bold text-cozy-coffee text-base mb-4 flex items-center gap-2">
                    <i class="fa-solid <?php echo esc_attr( $icon_class ); ?> text-cozy-mint text-sm" aria-hidden="true"></i>
                    <?php echo esc_html( $title ); ?>
                </h2>
                <ul class="space-y-2">
                    <?php foreach ( $links as $label => $url ) : ?>
                    <li>
                        <a href="<?php echo esc_url( $url ); ?>"
                           class="text-sm text-cozy-coffee/70 hover:text-cozy-mint transition-colors flex items-center gap-1.5">
                            <span class="text-cozy-mint/50 text-[10px]">›</span>
                            <?php echo esc_html( $label ); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }

        /* ---- Main pages ---- */
        $shop_url    = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
        $account_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'myaccount' ) ) : home_url( '/' );
        $cart_url    = class_exists( 'WooCommerce' ) ? wc_get_cart_url() : home_url( '/' );
        $blog_page_id = get_option( 'page_for_posts' );
        $blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blog/' );

        cozy_sitemap_section( 'Páginas principales', 'fa-house', [
            'Inicio'    => home_url( '/' ),
            'Boutique'  => $shop_url,
            'Blog'      => $blog_url,
            'Mi cuenta' => $account_url,
            'Carrito'   => $cart_url,
        ] );

        /* ---- Categories ---- */
        $uncategorised_id = absint( get_option( 'default_product_cat' ) );
        $top_cats = get_terms( [
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
            'parent'     => 0,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        if ( ! is_wp_error( $top_cats ) && ! empty( $top_cats ) ) {
            $cat_links = [];
            foreach ( $top_cats as $cat ) {
                $url = get_term_link( $cat );
                if ( ! is_wp_error( $url ) ) {
                    $cat_links[ $cat->name ] = $url;
                    // Subcategories
                    $sub_cats = get_terms( [
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'parent'     => $cat->term_id,
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                    ] );
                    if ( ! is_wp_error( $sub_cats ) ) {
                        foreach ( $sub_cats as $sub ) {
                            $sub_url = get_term_link( $sub );
                            if ( ! is_wp_error( $sub_url ) ) {
                                $cat_links[ '  ' . $sub->name ] = $sub_url;
                            }
                        }
                    }
                }
            }
            cozy_sitemap_section( 'Categorías', 'fa-tag', $cat_links );
        }

        /* ---- Legal & info ---- */
        function cozy_sitemap_legal_link( $slug ) {
            $page = get_page_by_path( $slug );
            return $page ? get_permalink( $page ) : '#';
        }

        cozy_sitemap_section( 'Información', 'fa-circle-info', [
            'Envíos y devoluciones' => cozy_sitemap_legal_link( 'envios-y-devoluciones' ),
            'Política de privacidad' => cozy_sitemap_legal_link( 'politica-de-privacidad' ),
            'Términos y condiciones' => cozy_sitemap_legal_link( 'terminos-y-condiciones' ),
        ] );

        /* ---- Blog posts (last 10) ---- */
        $blog_posts = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'numberposts'    => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
        if ( ! empty( $blog_posts ) ) {
            $post_links = [];
            foreach ( $blog_posts as $bp ) {
                $post_links[ $bp->post_title ] = get_permalink( $bp );
            }
            cozy_sitemap_section( 'Últimas entradas del blog', 'fa-pen-nib', $post_links );
        }
        ?>

    </div>

    <?php
    /* XML sitemap hint for admins */
    if ( current_user_can( 'manage_options' ) ) : ?>
    <p class="mt-10 text-[11px] text-cozy-coffee/40">
        El sitemap XML para buscadores está en
        <a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>" class="underline hover:text-cozy-mint" target="_blank" rel="noopener noreferrer">/wp-sitemap.xml</a>
        (generado automáticamente por WordPress).
    </p>
    <?php endif; ?>

</div>

<?php get_footer( 'shop' ); ?>
