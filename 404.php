<?php
/**
 * 404 Error Page – Cozy Fandom Design
 *
 * @package cozy-fandom-child
 */

get_header();

$shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
$home_url = home_url( '/' );

$uncategorised_id = absint( get_option( 'default_product_cat' ) );
$top_cats = get_terms( [
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'exclude'    => $uncategorised_id ? [ $uncategorised_id ] : [],
    'parent'     => 0,
    'number'     => 4,
    'orderby'    => 'count',
    'order'      => 'DESC',
] );
?>

<div id="cozy-404" class="min-h-[70vh] bg-gradient-to-b from-cozy-cream to-cozy-sand/40 py-12 md:py-20 px-4 sm:px-6 md:px-8 relative overflow-hidden flex items-center justify-center">

    <!-- Decorative background glow blobs -->
    <div class="absolute top-10 left-1/4 w-80 h-80 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
    <div class="absolute bottom-10 right-1/4 w-80 h-80 bg-cozy-accent/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

    <div class="max-w-2xl w-full mx-auto text-center">

        <!-- Main 404 Card -->
        <div class="bg-white rounded-[28px] md:rounded-[36px] border border-cozy-sand/80 p-8 sm:p-12 shadow-sm relative">

            <!-- Cozy Icon / Illustration Badge -->
            <div class="w-20 h-20 rounded-full bg-cozy-mintLight flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner border border-cozy-mint/20">
                🍵
            </div>

            <div class="inline-flex items-center gap-1.5 bg-cozy-mintLight text-cozy-mint text-[11px] font-bold px-3.5 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-4">
                ✨ Error 404 · Rincón no encontrado
            </div>

            <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-cozy-coffee tracking-tight mb-4">
                ¡Vaya! Este rincón parece estar vacío
            </h1>

            <p class="text-sm sm:text-base text-cozy-coffee/75 max-w-md mx-auto leading-relaxed mb-8">
                La página que buscas no existe, ha cambiado de lugar o se ha tomado un descanso con una taza de té caliente.
            </p>

            <!-- Search Form -->
            <div class="max-w-md mx-auto mb-8">
                <form role="search" method="get" action="<?php echo esc_url( $home_url ); ?>" class="relative flex items-center">
                    <input type="search" name="s"
                           placeholder="¿Qué estás buscando? (ej. figuras, tazas...)"
                           class="w-full bg-cozy-cream/80 border-2 border-cozy-sand rounded-2xl py-3.5 pl-5 pr-12 text-sm text-cozy-coffee placeholder:text-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:bg-white transition-all shadow-inner"
                           autocomplete="off">
                    <input type="hidden" name="post_type" value="product">
                    <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 bg-cozy-mint hover:bg-cozy-mintDark text-white rounded-xl flex items-center justify-center transition-colors border-0 cursor-pointer shadow-sm"
                            aria-label="Buscar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </button>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5">
                <a href="<?php echo esc_url( $shop_url ); ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-white text-sm font-bold px-6 py-3.5 rounded-2xl shadow-sm hover:shadow transition-all no-underline">
                    <?php echo function_exists('cozy_icon') ? cozy_icon( 'basket-shopping', '16' ) : '🛍️'; ?>
                    Explorar la Boutique
                </a>
                <a href="<?php echo esc_url( $home_url ); ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-cozy-cream text-cozy-coffee text-sm font-bold px-6 py-3.5 rounded-2xl border border-cozy-sand transition-all no-underline shadow-sm">
                    <?php echo function_exists('cozy_icon') ? cozy_icon( 'house', '16' ) : '🏠'; ?>
                    Ir a la Portada
                </a>
            </div>

            <!-- Suggested Categories -->
            <?php if ( ! is_wp_error( $top_cats ) && ! empty( $top_cats ) ) : ?>
            <div class="mt-10 pt-8 border-t border-cozy-sand/60">
                <span class="text-xs text-cozy-coffee/50 font-bold uppercase tracking-wider block mb-3">
                    Categorías populares
                </span>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <?php foreach ( $top_cats as $tcat ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $tcat ) ); ?>"
                       class="inline-flex items-center gap-1.5 bg-cozy-cream hover:bg-cozy-mintLight text-cozy-coffee/80 hover:text-cozy-mint text-xs font-bold px-3.5 py-1.5 rounded-full border border-cozy-sand transition-colors no-underline">
                        <?php echo esc_html( $tcat->name ); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<?php get_footer(); ?>
