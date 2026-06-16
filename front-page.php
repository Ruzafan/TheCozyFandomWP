<?php
/**
 * Template Name: Cozy Fandom – Home
 * Front page template for The Cozy Fandom storefront.
 */
get_header();
?>
<div id="cozy-front-page">
<?php

/* Category card config (icon & copy are static; URL resolves to real WC category) */
$cozy_cats = [
    [
        'title'       => 'Escritorio y Organización',
        'slug'        => 'escritorio-organizacion',
        'icon'        => 'fa-pen-nib',
        'description' => 'Papelería premium, cuadernos de tela, bolígrafos cuquis y planificadores zen de tus fandoms favoritos.',
        'link_text'   => 'Explorar papelería',
        'card_bg'     => 'bg-white',
        'icon_bg'     => 'bg-cozy-mintLight',
        'icon_color'  => 'text-cozy-mint',
        'ring_color'  => 'bg-cozy-mint/10',
    ],
    [
        'title'       => 'Rincón del Coleccionista',
        'slug'        => 'coleccionista',
        'icon'        => 'fa-cube',
        'description' => 'Figuras de importación, miniaturas de dioramas, Blind Boxes de ensueño y cajas sorpresa de anime.',
        'link_text'   => 'Ver figuras adorables',
        'card_bg'     => 'bg-cozy-sand',
        'icon_bg'     => 'bg-white',
        'icon_color'  => 'text-cozy-accent',
        'ring_color'  => 'bg-cozy-accent/10',
    ],
    [
        'title'       => 'Hogar y Confort',
        'slug'        => 'hogar-confort',
        'icon'        => 'fa-mug-saucer',
        'description' => 'Tazas de cerámica premium, botellas de acero frío, toallas para picnic aesthetic y cojines blanditos.',
        'link_text'   => 'Descubrir hogar',
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
<section id="home" class="relative py-12 md:py-24 px-6 md:px-12 overflow-hidden bg-cozy-cream">

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
                <a href="#filosofia" class="w-full sm:w-auto text-center border-2 border-cozy-coffee/20 hover:border-cozy-coffee hover:bg-cozy-coffee hover:text-white text-cozy-coffee font-semibold px-8 py-4 rounded-2xl transition-all">
                    Nuestra Filosofía
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
<!--  BRAND VALUES                                                 -->
<!-- ============================================================ -->
<section class="bg-white py-12 px-6 md:px-12 border-y border-cozy-sand">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 shrink-0 rounded-2xl bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-xl">
                <i class="fa-solid fa-certificate" aria-hidden="true"></i>
            </div>
            <div>
                <h4 class="font-bold text-cozy-coffee text-base mb-1">Productos 100% Oficiales</h4>
                <p class="text-xs text-cozy-coffee/70 leading-relaxed">Licencias seleccionadas con mucho mimo y bajo los estándares estéticos más limpios.</p>
            </div>
        </div>

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 shrink-0 rounded-2xl bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-xl">
                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
            </div>
            <div>
                <h4 class="font-bold text-cozy-coffee text-base mb-1">Envío Súper Protegido</h4>
                <p class="text-xs text-cozy-coffee/70 leading-relaxed">Cajas cuidadas, packaging libre de plásticos dañinos y unboxing mágico garantizado.</p>
            </div>
        </div>

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 shrink-0 rounded-2xl bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-xl">
                <i class="fa-solid fa-heart" aria-hidden="true"></i>
            </div>
            <div>
                <h4 class="font-bold text-cozy-coffee text-base mb-1">Atención de Fan a Fan</h4>
                <p class="text-xs text-cozy-coffee/70 leading-relaxed">Amamos lo que hacemos. Si tienes dudas, te atendemos con un té calentito online.</p>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================ -->
<!--  CATEGORIES SECTION                                           -->
<!-- ============================================================ -->
<section id="categorias" class="py-16 md:py-24 px-6 md:px-12 max-w-7xl mx-auto">

    <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Colecciones Seleccionadas</span>
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Tu rincón, tus reglas</h2>
        <p class="text-sm text-cozy-coffee/70 mt-3">Encuentra los tesoros ideales divididos en tres universos que encajan perfectamente con tu decoración.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <?php foreach ( $cozy_cats as $cat ) :
        $term    = get_term_by( 'slug', $cat['slug'], 'product_cat' );
        $cat_url = $term ? get_term_link( $term ) : esc_url( get_permalink( wc_get_page_id( 'shop' ) ) );
    ?>
        <div class="group <?php echo esc_attr( $cat['card_bg'] ); ?> rounded-[32px] p-8 border border-cozy-sand shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between min-h-[320px] relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 <?php echo esc_attr( $cat['ring_color'] ); ?> rounded-full group-hover:scale-150 transition-transform duration-500" aria-hidden="true"></div>
            <div>
                <div class="w-12 h-12 rounded-2xl <?php echo esc_attr( $cat['icon_bg'] ); ?> flex items-center justify-center <?php echo esc_attr( $cat['icon_color'] ); ?> text-lg mb-6">
                    <i class="fa-solid <?php echo esc_attr( $cat['icon'] ); ?>" aria-hidden="true"></i>
                </div>
                <h3 class="font-serif text-2xl font-bold text-cozy-coffee mb-2"><?php echo esc_html( $cat['title'] ); ?></h3>
                <p class="text-xs text-cozy-coffee/70 leading-relaxed max-w-[200px]"><?php echo esc_html( $cat['description'] ); ?></p>
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
<!--  FEATURED PRODUCTS SECTION                                    -->
<!-- ============================================================ -->
<section id="productos" class="bg-cozy-sand/50 py-16 md:py-24 px-6 md:px-12 relative">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Boutique de Selección</span>
                <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Los favoritos de la comunidad</h2>
                <p class="text-sm text-cozy-coffee/70 mt-2">Productos de distribuidores oficiales seleccionados especialmente para el verano.</p>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        $products = function_exists( 'wc_get_products' ) ? wc_get_products( [
            'limit'   => 6,
            'status'  => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
        ] ) : [];

        if ( $products ) :
            foreach ( $products as $product ) :
                $cat_ids  = $product->get_category_ids();
                $cat_name = '';
                if ( ! empty( $cat_ids ) ) {
                    $term = get_term( reset( $cat_ids ), 'product_cat' );
                    if ( $term && ! is_wp_error( $term ) ) $cat_name = $term->name;
                }
        ?>
            <div class="bg-white rounded-[24px] p-4 border border-cozy-sand shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                <div>
                    <!-- Product Image -->
                    <div class="bg-cozy-cream rounded-2xl h-56 flex items-center justify-center overflow-hidden mb-4 relative">
                        <?php echo $product->get_image( 'medium', [ 'class' => 'w-full h-full object-cover' ] ); ?>
                    </div>
                    <!-- Category label -->
                    <?php if ( $cat_name ) : ?>
                    <span class="text-[10px] text-cozy-mint font-bold uppercase tracking-wider block mb-1"><?php echo esc_html( $cat_name ); ?></span>
                    <?php endif; ?>
                    <!-- Name -->
                    <h3 class="font-bold text-sm text-cozy-coffee line-clamp-2">
                        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="hover:text-cozy-mint transition-colors">
                            <?php echo esc_html( $product->get_name() ); ?>
                        </a>
                    </h3>
                </div>
                <!-- Price + Add to cart -->
                <div class="flex items-center justify-between pt-4 border-t border-cozy-sand mt-4">
                    <span class="text-base font-bold text-cozy-coffee"><?php echo $product->get_price_html(); ?></span>
                    <?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
                    <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                       data-quantity="1"
                       data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                       data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                       class="ajax_add_to_cart add_to_cart_button bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee hover:text-white p-2.5 px-4 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5"
                       aria-label="Añadir <?php echo esc_attr( $product->get_name() ); ?> al carrito">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir
                    </a>
                    <?php else : ?>
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
                       class="bg-cozy-sand text-cozy-coffee p-2.5 px-4 rounded-xl text-xs font-bold flex items-center gap-1.5">
                        Ver producto
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach;
        else : ?>
            <div class="col-span-3 text-center py-16">
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
<!--  PHILOSOPHY / ABOUT SECTION                                   -->
<!-- ============================================================ -->
<section id="filosofia" class="py-16 md:py-24 px-6 md:px-12 max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <!-- Left: Illustration -->
        <div class="lg:col-span-5">
            <div class="bg-cozy-sand rounded-[32px] p-8 border border-cozy-coffee/10 shadow-inner relative overflow-hidden">
                <div class="h-80 flex items-center justify-center">
                    <svg class="w-64 h-64 text-cozy-coffee" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <rect x="35" y="10" width="30" height="40" rx="15" fill="#FAF6EE" stroke="#4A3F35"/>
                        <line x1="50" y1="10" x2="50" y2="50" stroke="#4A3F35"/>
                        <line x1="35" y1="30" x2="65" y2="30" stroke="#4A3F35"/>
                        <circle cx="45" cy="22" r="4" fill="#88C4B5" stroke="none"/>
                        <path d="M20 70 C20 60 25 55 35 55 H65 C75 55 80 60 80 70 V85 H20 V70 Z" fill="#F5EDE0" stroke="#4A3F35"/>
                        <rect x="25" y="70" width="50" height="10" rx="4" fill="#FAF6EE"/>
                        <ellipse cx="50" cy="73" rx="8" ry="5" fill="#D4A373" stroke="#4A3F35"/>
                        <path d="M56 70 Q58 66 57 71" stroke="#4A3F35"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right: Text -->
        <div class="lg:col-span-7 space-y-6">
            <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block">Filosofía Cozy Geek</span>
            <h2 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">Tu pasión y el diseño, en completa armonía</h2>
            <p class="text-sm md:text-base text-cozy-coffee/80 leading-relaxed font-light">
                En <strong>The Cozy Fandom</strong> creemos firmemente que tu amor por los videojuegos, el anime clásico o el cine fantástico no tiene por qué estar reñido con un hogar luminoso, ordenado y relajante.
            </p>
            <p class="text-xs md:text-sm text-cozy-coffee/70 leading-relaxed">
                Buscamos alejarnos del merchandising plástico estándar. Preferimos tazas con esmaltes artesanales, papelería de portadas limpias y pequeños coleccionables que se sienten como obras de arte botánico sobre tu escritorio. Queremos que cada unboxing sea un ritual de relax.
            </p>
            <div class="pt-4 grid grid-cols-2 gap-4">
                <div class="border-l-4 border-cozy-mint pl-4">
                    <span class="block text-2xl font-serif font-bold text-cozy-coffee">100%</span>
                    <span class="text-xs text-cozy-coffee/60">Licencia Oficial</span>
                </div>
                <div class="border-l-4 border-cozy-mint pl-4">
                    <span class="block text-2xl font-serif font-bold text-cozy-coffee">0%</span>
                    <span class="text-xs text-cozy-coffee/60">Plásticos Innecesarios</span>
                </div>
            </div>
        </div>

    </div>
</section>

<?php
?>
</div><!-- /#cozy-front-page -->
<?php
get_footer();
