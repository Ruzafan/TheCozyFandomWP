<?php
/**
 * Template Name: Cozy Fandom – Home
 * Front page template for The Cozy Fandom storefront.
 */
get_header();
?>
<div id="cozy-front-page">
<?php

/* License collection cards — URL filters shop by product_licencia slug */
$cozy_cats = [
    [
        'title'       => 'El Rincón de Snoopy',
        'licencia'    => 'snoopy',
        'icon'        => 'fa-paw',
        'image'       => get_stylesheet_directory_uri() . '/assets/images/snoopy-heart.png',
        'description' => 'El beagle más adorable del mundo en tazas, figuras, papelería y mucho más. Todo con ese toque cozy que enamora.',
        'link_text'   => 'Ver colección Snoopy',
        'card_bg'     => 'bg-white',
        'icon_bg'     => 'bg-cozy-mintLight',
        'icon_color'  => 'text-cozy-mint',
        'ring_color'  => 'bg-cozy-mint/10',
    ],
    [
        'title'       => 'Escoge tu Casa',
        'licencia'    => 'harry-potter',
        'icon'        => 'fa-hat-wizard',
        'description' => 'Gryffindor, Slytherin, Hufflepuff o Ravenclaw. Llena tu rincón de magia con la colección de Harry Potter.',
        'link_text'   => 'Entrar al castillo',
        'card_bg'     => 'bg-cozy-sand',
        'icon_bg'     => 'bg-white',
        'icon_color'  => 'text-cozy-accent',
        'ring_color'  => 'bg-cozy-accent/10',
    ],
    [
        'title'       => 'La Magia de Disney',
        'licencia'    => 'disney',
        'icon'        => 'fa-star',
        'description' => 'Princesas, clásicos y villanos de ensueño. Objetos coleccionables y detalles mágicos para los fans de siempre.',
        'link_text'   => 'Explorar Disney',
        'card_bg'     => 'bg-white',
        'icon_bg'     => 'bg-cozy-mintLight',
        'icon_color'  => 'text-cozy-mint',
        'ring_color'  => 'bg-cozy-mint/10',
    ],
];
?>

<!-- ============================================================ -->
<!--  HERO SECTION                                                 -->
<!-- ============================================================ -->
<section id="home" class="relative py-12 px-6 md:px-12 overflow-hidden">

    <!-- Background decorative blobs -->
    <div class="absolute top-10 right-10 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
    <div class="absolute bottom-10 left-10 w-72 h-72 bg-cozy-accent/5 rounded-full blur-3xl -z-10" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <!-- Left: Copy -->
        <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20">
                🌿 Concepto Cozy Geek Boutique
            </div>
            <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-semibold leading-tight text-cozy-coffee">
                Tu rincón friki <br class="hidden md:inline">
                <span class="italic text-cozy-mint font-normal">más acogedor</span>.
            </h1>
            <p class="text-base md:text-lg text-cozy-coffee/80 max-w-xl mx-auto lg:mx-0 font-light leading-relaxed">
                Coleccionables bonitos, papelería aesthetic y detalles con alma para un hogar relajado. Merchandising oficial seleccionado con un toque cálido y sofisticado.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">
                <?php
                $shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : '#productos';
                ?>
                <a href="<?php echo esc_url( $shop_url ); ?>" class="w-full sm:w-auto text-center bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-semibold px-8 py-4 rounded-2xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    Explorar la Tienda <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Right: Product showcase widget -->
        <div class="lg:col-span-5 relative">
            <div class="bg-white rounded-[32px] p-6 shadow-xl border border-cozy-sand relative overflow-hidden max-w-md mx-auto hover:rotate-1 transition-transform duration-300">
                <div class="bg-cozy-sand/40 rounded-2xl h-64 flex items-center justify-center mb-6 relative group overflow-hidden">
                    <svg class="w-48 h-48 text-cozy-coffee/80" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M65 60 H35 C32 60 30 58 30 55 V40 C30 37 32 35 35 35 H65 C68 35 70 37 70 40 V55 C70 58 68 60 65 60 Z" fill="#F5EDE0"/>
                        <path d="M70 42 H74 C76 42 78 44 78 46 V50 C78 52 76 54 74 54 H70"/>
                        <path d="M45 28 Q47 24 45 20 M50 28 Q52 24 50 20 M55 28 Q57 24 55 20" stroke-linecap="round" stroke-dasharray="2"/>
                        <rect x="25" y="66" width="50" height="12" rx="2" fill="#FAF6EE"/>
                        <line x1="25" y1="70" x2="75" y2="70"/><line x1="25" y1="74" x2="75" y2="74"/>
                        <path d="M30 63 V69 M38 63 V69 M46 63 V69 M54 63 V69 M62 63 V69 M70 63 V69" stroke="#88C4B5" stroke-width="2" stroke-linecap="round"/>
                        <path d="M15 60 Q15 45 22 48 Q25 43 28 52 Q22 55 15 60 Z" fill="#88C4B5" opacity="0.8"/>
                        <rect x="16" y="60" width="10" height="12" rx="2" fill="#D4A373"/>
                    </svg>
                    <span class="absolute bottom-4 left-4 bg-white/90 text-[10px] font-bold tracking-wider px-3 py-1 rounded-full text-cozy-coffee">ESTILO DE VIDA COZY</span>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-cozy-mint font-semibold uppercase tracking-wider">Línea Escritorio</span>
                    <div class="flex text-amber-400 text-xs" aria-label="5 estrellas">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <h3 class="font-serif text-lg font-bold text-cozy-coffee">Pack Escritorio Mágico</h3>
                <p class="text-xs text-cozy-coffee/60 mt-1 mb-4">La conjunción perfecta de tranquilidad para tu rincón de trabajo.</p>
                <div class="flex items-center justify-between pt-2 border-t border-cozy-sand">
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee hover:text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping"></i> Ver tienda
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================ -->
<!--  TRUST BADGES                                                 -->
<!-- ============================================================ -->
<section id="garantias" class="bg-white border-t border-b border-cozy-cream rounded-[32px] mb-10">
    <div class="max-w-7xl mx-auto px-6 md:px-12 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-8">

            <div class="flex items-center gap-4">
                <div class="shrink-0 w-12 h-12 rounded-[14px] bg-cozy-mintLight flex items-center justify-center">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <h4 class="text-cozy-coffee font-bold m-0" style="font-size:15px">Empaquetado Aesthetic</h4>
                    <p class="text-cozy-coffee/70 m-0" style="font-size:13px">Unboxings que enamoran</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="shrink-0 w-12 h-12 rounded-[14px] bg-cozy-mintLight flex items-center justify-center">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div>
                    <h4 class="text-cozy-coffee font-bold m-0" style="font-size:15px">Envíos Rápidos</h4>
                    <p class="text-cozy-coffee/70 m-0" style="font-size:13px">24/48h en Península</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="shrink-0 w-12 h-12 rounded-[14px] bg-cozy-mintLight flex items-center justify-center">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <h4 class="text-cozy-coffee font-bold m-0" style="font-size:15px">Pagos 100% Seguros</h4>
                    <p class="text-cozy-coffee/70 m-0" style="font-size:13px">Tarjeta, Google Pay, Apple Pay o Amazon Pay</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="shrink-0 w-12 h-12 rounded-[14px] bg-cozy-mintLight flex items-center justify-center">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-cozy-coffee font-bold m-0" style="font-size:15px">Atención Cercana</h4>
                    <p class="text-cozy-coffee/70 m-0" style="font-size:13px">Te ayudamos por WhatsApp</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================ -->
<!--  NEW PRODUCTS SECTION                                         -->
<!-- ============================================================ -->
<section id="nuevos" class="bg-cozy-sand/50 py-16 px-6 md:px-12 relative rounded-[32px]">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Recién Llegados</span>
                <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Lo más nuevo en la boutique</h2>
                <p class="text-sm text-cozy-coffee/70 mt-2">Los últimos tesoros en incorporarse a nuestra colección.</p>
            </div>
            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
            <div class="mt-4 md:mt-0">
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                   class="bg-white hover:bg-cozy-mintLight text-cozy-coffee px-5 py-2 rounded-full text-xs font-medium border border-cozy-sand transition-colors">
                    Ver todos los productos →
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $new_products = function_exists( 'wc_get_products' ) ? wc_get_products( [
            'limit'   => 4,
            'status'  => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
        ] ) : [];

        if ( $new_products ) :
            foreach ( $new_products as $product ) {
                cozy_fandom_home_product_card( $product, 'Nuevo', '✨' );
            }
        else : ?>
            <div class="col-span-4 text-center py-16">
                <i class="fa-solid fa-store text-cozy-coffee/20 text-5xl block mb-4" aria-hidden="true"></i>
                <p class="text-cozy-coffee/60 text-sm">Cargando productos... Asegúrate de que WooCommerce está activo y tienes productos publicados.</p>
                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>"
                   class="inline-block mt-4 bg-cozy-mint text-cozy-coffee px-6 py-2 rounded-xl text-sm font-bold">
                    Añadir primer producto
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>

    </div>
</section>

<!-- ============================================================ -->
<!--  CATEGORIES SECTION                                           -->
<!-- ============================================================ -->
<section id="categorias" class="py-16 px-6 md:px-12 max-w-7xl mx-auto rounded-[32px]">

    <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Universos Favoritos</span>
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Elige tu fandom</h2>
        <p class="text-sm text-cozy-coffee/70 mt-3">Sumérgete en el universo que más te llama. Colecciones pensadas para los fans de verdad.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <?php
    $_shop_base = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );
    foreach ( $cozy_cats as $cat ) :
        $cat_url = add_query_arg( 'licencia', $cat['licencia'], $_shop_base );
    ?>
        <div class="group <?php echo esc_attr( $cat['card_bg'] ); ?> rounded-[32px] p-8 border border-cozy-sand shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between min-h-[320px] relative overflow-hidden">
            <?php if ( empty( $cat['image'] ) ) : ?>
            <div class="absolute -right-8 -bottom-8 w-32 h-32 <?php echo esc_attr( $cat['ring_color'] ); ?> rounded-full group-hover:scale-150 transition-transform duration-500" aria-hidden="true"></div>
            <?php endif; ?>
            <?php if ( ! empty( $cat['image'] ) ) : ?>
            <img src="<?php echo esc_url( $cat['image'] ); ?>"
                 alt="" aria-hidden="true" loading="lazy"
                 class="absolute -bottom-3 -right-3 w-44 h-44 object-contain object-bottom pointer-events-none select-none group-hover:scale-105 transition-transform duration-500 z-0">
            <?php endif; ?>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-2xl <?php echo esc_attr( $cat['icon_bg'] ); ?> flex items-center justify-center <?php echo esc_attr( $cat['icon_color'] ); ?> text-lg mb-6">
                    <i class="fa-solid <?php echo esc_attr( $cat['icon'] ); ?>" aria-hidden="true"></i>
                </div>
                <h3 class="font-serif text-2xl font-bold text-cozy-coffee mb-2"><?php echo esc_html( $cat['title'] ); ?></h3>
                <p class="text-xs text-cozy-coffee/70 leading-relaxed <?php echo ! empty( $cat['image'] ) ? 'max-w-[160px]' : 'max-w-[200px]'; ?>"><?php echo esc_html( $cat['description'] ); ?></p>
            </div>
            <div class="pt-6 z-10">
                <a href="<?php echo esc_url( $cat_url ); ?>" class="inline-flex items-center gap-2 text-xs font-bold text-cozy-coffee hover:text-cozy-mint transition-colors">
                    <?php echo esc_html( $cat['link_text'] ); ?> <i class="fa-solid fa-arrow-right-long group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

</section>

<!-- ============================================================ -->
<!--  TOP SELLS SECTION (products tagged "top-sell")               -->
<!-- ============================================================ -->
<section id="top-ventas" class="bg-cozy-sand/50 py-16 px-6 md:px-12 relative rounded-[32px]">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs font-bold text-cozy-accent uppercase tracking-widest block mb-2">Los Más Buscados</span>
                <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Top ventas de la comunidad</h2>
                <p class="text-sm text-cozy-coffee/70 mt-2">Los favoritos indiscutibles de nuestros fans cozy.</p>
            </div>
            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
            <div class="mt-4 md:mt-0">
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                   class="bg-white hover:bg-cozy-mintLight text-cozy-coffee px-5 py-2 rounded-full text-xs font-medium border border-cozy-sand transition-colors">
                    Ver todos los productos →
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        $top_products = function_exists( 'wc_get_products' ) ? wc_get_products( [
            'limit'   => 4,
            'status'  => 'publish',
            'tag'     => [ 'top-sell' ],
            'orderby' => 'date',
            'order'   => 'DESC',
        ] ) : [];

        if ( $top_products ) :
            foreach ( $top_products as $product ) {
                cozy_fandom_home_product_card( $product, 'Top Venta', '🔥' );
            }
        else : ?>
            <div class="col-span-4 text-center py-16">
                <i class="fa-solid fa-fire text-cozy-coffee/20 text-5xl block mb-4" aria-hidden="true"></i>
                <p class="text-cozy-coffee/60 text-sm">Aún no hay productos marcados como "Top Venta".</p>
                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                <p class="text-cozy-coffee/50 text-xs mt-2">Añade la etiqueta <code>top-sell</code> a tus productos más vendidos desde Productos → Editar producto → Etiquetas del producto.</p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"
                   class="inline-block mt-4 bg-cozy-mint text-cozy-coffee px-6 py-2 rounded-xl text-sm font-bold">
                    Gestionar productos
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>

    </div>
</section>


<!-- ============================================================ -->
<!--  NEWSLETTER                                                   -->
<!-- ============================================================ -->
<section id="newsletter" class="px-6 md:px-12 py-16 md:py-20 rounded-[32px]">
    <div class="max-w-2xl mx-auto bg-cozy-sand rounded-[40px] px-8 md:px-16 py-16 md:py-20 text-center relative overflow-hidden">

        <!-- Decorative blobs -->
        <div class="absolute -top-16 -left-16 w-64 h-64 bg-cozy-cream rounded-full blur-3xl opacity-60 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-16 -right-16 w-64 h-64 bg-cozy-cream rounded-full blur-3xl opacity-60 pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col items-center gap-5">

            <!-- Badge -->
            <span class="inline-flex items-center gap-1.5 bg-white text-cozy-accent text-xs font-bold px-4 py-1.5 rounded-full border border-cozy-accent/20">
                ✨ Únete al Cozy Club
            </span>

            <!-- Title -->
            <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee leading-tight m-0">
                Un rincón de paz en tu<br> bandeja de entrada.
            </h2>

            <!-- Body -->
            <p class="text-sm md:text-base text-cozy-coffee/80 max-w-md leading-relaxed m-0">
                Apúntate a nuestra newsletter y recibe un <strong>10% de descuento</strong> en tu primer pedido. Cero spam, solo novedades bonitas, tips de decoración geek y ofertas exclusivas para la comunidad.
            </p>

            <!-- Form -->
            <form id="newsletter-form" onsubmit="handleNewsletterSubmit(event)" class="w-full flex flex-col sm:flex-row gap-3 mt-2">
                <input type="email" required
                       placeholder="tu@email.com"
                       class="flex-1 bg-white border-2 border-white rounded-2xl px-5 py-3.5 text-sm text-cozy-coffee placeholder-cozy-coffee/40 outline-none focus:border-cozy-mint transition-colors"
                       style="border-radius:16px">
                <button type="submit"
                        class="shrink-0 bg-cozy-mint hover:bg-cozy-mintDark text-white font-bold px-7 py-3.5 rounded-2xl text-sm transition-all hover:-translate-y-0.5 hover:shadow-lg whitespace-nowrap"
                        style="border-radius:16px">
                    Quiero mi descuento
                </button>
            </form>

            <!-- Success state -->
            <div id="newsletter-success" class="hidden text-center py-2">
                <span class="text-cozy-mint font-bold text-sm">🌿 ¡Bienvenida al club! Revisa tu bandeja de entrada.</span>
            </div>

            <!-- Micro-text -->
            <p class="text-cozy-coffee/60 m-0" style="font-size:11px">
                Prometemos cuidar de tus datos tanto como cuidamos nuestros envíos.
            </p>

        </div>
    </div>
</section>

</div><!-- /#cozy-front-page -->
<?php
get_footer();
