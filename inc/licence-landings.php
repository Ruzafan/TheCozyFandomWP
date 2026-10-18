<?php
/**
 * Licence & category landings
 *
 * Turns the existing product_brand (/marca/…/) and product_cat archives into
 * proper landing pages instead of creating new pages that would compete with
 * them for the same searches:
 *
 *  - A "landing content" field per term (editable in wp-admin under the term),
 *    printed below the product grid on page 1: H2/H3 sections + FAQ.
 *    The short intro above the grid is the term's normal Description field.
 *  - Blog posts about a licence get an inline callout and a product strip
 *    pointing to that licence's landing (hub & spoke internal linking).
 *  - A one-shot seed fills the pilot landings (Snoopy, Pokémon) if empty.
 */

defined( 'ABSPATH' ) || exit;

const COZY_LANDING_META = 'cozy_landing_content';

/* ------------------------------------------------------------------ */
/*  Admin field: "Contenido de la landing" on licence/category screens */
/* ------------------------------------------------------------------ */
function cozy_landing_taxonomies() {
    return [ 'product_brand', 'product_cat' ];
}

add_action( 'init', function() {
    foreach ( cozy_landing_taxonomies() as $taxonomy ) {
        add_action( "{$taxonomy}_edit_form_fields", 'cozy_landing_edit_field', 20 );
        add_action( "edited_{$taxonomy}", 'cozy_landing_save_field' );
    }
} );

function cozy_landing_edit_field( $term ) {
    $content = (string) get_term_meta( $term->term_id, COZY_LANDING_META, true );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="cozy-landing-content">Contenido de la landing (debajo de los productos)</label></th>
        <td>
            <?php wp_editor( $content, 'cozy-landing-content', [
                'textarea_name' => COZY_LANDING_META,
                'textarea_rows' => 18,
                'media_buttons' => false,
            ] ); ?>
            <p class="description">Texto SEO que aparece al final de la página, solo en la página 1. Usa títulos H2/H3. Para preguntas frecuentes desplegables, en la pestaña "Código": &lt;details&gt;&lt;summary&gt;Pregunta&lt;/summary&gt;&lt;p&gt;Respuesta&lt;/p&gt;&lt;/details&gt;. La intro corta de arriba es el campo "Descripción".</p>
        </td>
    </tr>
    <?php
}

// Core has already verified the term-edit nonce before edited_{$taxonomy} fires.
function cozy_landing_save_field( $term_id ) {
    if ( ! isset( $_POST[ COZY_LANDING_META ] ) || ! current_user_can( 'manage_product_terms' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }
    update_term_meta( $term_id, COZY_LANDING_META, wp_kses_post( wp_unslash( $_POST[ COZY_LANDING_META ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
}

/* ------------------------------------------------------------------ */
/*  Front end: landing content below the product grid                 */
/* ------------------------------------------------------------------ */
function cozy_render_landing_content() {
    if ( is_paged() || ! is_tax( cozy_landing_taxonomies() ) ) {
        return;
    }
    $content = (string) get_term_meta( get_queried_object_id(), COZY_LANDING_META, true );
    if ( '' === trim( $content ) ) {
        return;
    }
    ?>
    <section class="cozy-landing-content cozy-post-content max-w-3xl mt-12 pt-10 border-t border-cozy-sand">
        <?php echo wp_kses_post( wpautop( $content ) ); ?>
    </section>
    <?php
}

/* ------------------------------------------------------------------ */
/*  Blog → licence landing links                                      */
/* ------------------------------------------------------------------ */
/* First match wins, so specific licences go before broad ones (Disney, anime).
   Matched against the post title + slug only — body text is too noisy. */
function cozy_licence_post_rules() {
    return [
        'snoopy'       => '/snoopy|peanuts|woodstock/i',
        'pokemon'      => '/pok[eé]mon|pikachu|eevee/i',
        'harry-potter' => '/harry[ -]potter|hogwarts/i',
        'pusheen'      => '/pusheen/i',
        'hello-kitty'  => '/hello[ -]kitty|kuromi|sanrio|my[ -]melody|cinnamoroll/i',
        'star-wars'    => '/star[ -]wars|grogu|mandalorian/i',
        'disney'       => '/disney|stitch|mickey|minnie/i',
        'anime'        => '/anime|ghibli|totoro|demon[ -]slayer/i',
    ];
}

/* The licence a post is about: an explicit `_cozy_licence` post meta (term
   slug) wins, otherwise the title/slug rules above. */
function cozy_post_licence_term( $post_id = 0 ) {
    $post_id = $post_id ?: get_the_ID();
    if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
        return null;
    }
    $slug = (string) get_post_meta( $post_id, '_cozy_licence', true );
    if ( '' === $slug ) {
        $haystack = get_the_title( $post_id ) . ' ' . get_post_field( 'post_name', $post_id );
        foreach ( cozy_licence_post_rules() as $rule_slug => $pattern ) {
            if ( preg_match( $pattern, $haystack ) ) {
                $slug = $rule_slug;
                break;
            }
        }
    }
    if ( '' === $slug ) {
        return null;
    }
    $term = get_term_by( 'slug', $slug, 'product_brand' );
    return ( $term && $term->count > 0 ) ? $term : null;
}

// Inline callout before the post's second H2 — early enough to be seen.
add_filter( 'the_content', function( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }
    $term = cozy_post_licence_term();
    if ( ! $term || preg_match_all( '/<h2[\s>]/i', $content, $m, PREG_OFFSET_CAPTURE ) < 2 ) {
        return $content;
    }
    $callout = sprintf(
        '<p class="cozy-licence-callout bg-cozy-mintLight border border-cozy-mint/30 rounded-2xl px-5 py-4 text-sm">🎁 <strong>¿Buscas regalos de %1$s?</strong> <a href="%2$s">Descubre toda la colección de %1$s en la tienda →</a></p>',
        esc_html( $term->name ),
        esc_url( get_term_link( $term ) )
    );
    $offset = $m[0][1][1];
    return substr( $content, 0, $offset ) . $callout . substr( $content, $offset );
}, 20 );

// Product strip after the post body (called from single.php).
function cozy_render_post_licence_products() {
    $term = cozy_post_licence_term();
    if ( ! $term || ! function_exists( 'cozy_fandom_home_product_card' ) ) {
        return;
    }
    $ids = get_posts( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'fields'         => 'ids',
        'tax_query'      => [ [ 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => $term->term_id ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
        'meta_query'     => [ [ 'key' => '_stock_status', 'value' => 'instock' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
    ] );
    if ( ! $ids ) {
        return;
    }
    ?>
    <section class="cozy-post-licence-products mt-12 pt-10 border-t border-cozy-sand" data-ga-list-name="<?php echo esc_attr( 'Blog: ' . $term->name ); ?>">
        <h2 class="font-serif text-2xl font-bold text-cozy-coffee m-0 mb-6">Regalos de <?php echo esc_html( $term->name ); ?> en la tienda</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ( $ids as $id ) {
                $product = wc_get_product( $id );
                if ( $product ) {
                    cozy_fandom_home_product_card( $product );
                }
            } ?>
        </div>
        <div class="text-center mt-8">
            <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"
               class="inline-flex items-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-semibold px-7 py-3.5 rounded-2xl shadow-md transition-all no-underline">
                Ver todos los regalos de <?php echo esc_html( $term->name ); ?> <?php echo cozy_icon( 'arrow-right', '12' ); ?>
            </a>
        </div>
    </section>
    <?php
}

// The cards' "Añadir al carrito" needs wc-add-to-cart, which is otherwise only
// enqueued on shop pages (without it the button just links to the product).
add_action( 'wp_enqueue_scripts', function() {
    if ( class_exists( 'WooCommerce' ) && is_singular( 'post' ) && cozy_post_licence_term( get_queried_object_id() ) ) {
        wp_enqueue_script( 'wc-add-to-cart' );
    }
}, 21 );

/* ------------------------------------------------------------------ */
/*  One-shot seed for the pilot landings (fills only empty fields)    */
/* ------------------------------------------------------------------ */
const COZY_LANDING_SEED_VERSION = '2026-10-18';

/* {{cat:slug}} / {{product:slug}} placeholders are resolved to real URLs at
   seed time, so links survive any permalink-base change made before then. */
function cozy_landing_resolve_links( $html ) {
    return preg_replace_callback( '/\{\{(cat|product):([a-z0-9-]+)\}\}/', function( $m ) {
        if ( 'cat' === $m[1] ) {
            $link = get_term_link( $m[2], 'product_cat' );
            return is_wp_error( $link ) ? '#' : esc_url( $link );
        }
        $product = get_page_by_path( $m[2], OBJECT, 'product' );
        return $product ? esc_url( get_permalink( $product ) ) : '#';
    }, $html );
}

function cozy_landing_seed_data() {
    $shipping_faq = '<details><summary>¿Cuánto tarda el envío?</summary><p>Enviamos a España peninsular en 3-5 días laborables. El envío es gratis en pedidos de más de 60 € y cuesta 5,99 € en el resto.</p></details>';

    return [
        'snoopy' => [
            'intro'   => 'Snoopy, Woodstock y toda la pandilla de Peanuts para tu día a día: tazas para el café de la mañana, platos con forma de flor y de estrella, bolsas y pequeños detalles para regalar. Un toque nostálgico y muy cozy para cualquier rincón de casa.',
            'landing' => '
<h2>Regalos de Snoopy para cada fan de Peanuts</h2>
<p>Snoopy lleva más de 70 años siendo el beagle más soñador del mundo, y encaja igual de bien en un piso de estudiantes que en la casa de alguien que creció leyendo las tiras de Charlie Brown. Aquí reunimos los productos de Snoopy y Peanuts que nos encantaría tener en casa: útiles, bonitos y con ese aire relajado que invita a quedarse.</p>

<h3>Tazas de Snoopy para el café o el té</h3>
<p>Si el día no empieza sin una bebida caliente, las <a href="{{product:taza-snoopy-happy-camper}}">tazas de Snoopy</a> de 330 ml son un regalo seguro. Para quien necesita más dosis, la <a href="{{product:taza-3d-snoopy-con-tapa}}">taza 3D de Snoopy con tapa</a> tiene 500 ml y mantiene la bebida caliente más tiempo. Puedes ver todas en <a href="{{cat:tazas-y-vasos}}">tazas y vasos</a>.</p>

<h3>Platos y bandejas para vestir la mesa</h3>
<p>Los platos de cerámica con forma de flor y de estrella, con Snoopy, Woodstock o Snoopy vaquero, sirven igual para el postre que como vaciabolsillos o joyero en la entrada. Completa la colección con la <a href="{{product:bandeja-decorativa-pandilla-snoopy}}">bandeja de la pandilla</a> o el <a href="{{product:tarro-de-ceramica-con-tapa-snoopy-flores}}">tarro de cerámica con tapa</a>. Todos en <a href="{{cat:platos}}">platos</a>.</p>

<h3>Detalles de Snoopy para el amigo invisible</h3>
<p>Para presupuestos pequeños funcionan muy bien la <a href="{{product:crema-de-manos-snoopy-75ml}}">crema de manos de Snoopy</a> o la <a href="{{product:bolsa-termica-porta-alimentos-ecologica-snoopy-woodstock}}">bolsa térmica de Snoopy y Woodstock</a> para llevar la comida. Y si te gustan las sorpresas, la <a href="{{product:figura-escenario-sorpresa-snoopy-peanuts-series}}">figura sorpresa con escenario de Peanuts</a> no te dice qué personaje te toca hasta abrirla.</p>

<h3>Snoopy en tu escritorio</h3>
<p>Para trabajar o jugar con un poco más de calma, la <a href="{{product:alfombrilla-xl-snoopy-relax}}">alfombrilla XL Snoopy Relax</a> cubre teclado y ratón. Más modelos en <a href="{{cat:alfombrillas}}">alfombrillas de escritorio</a>.</p>

<h2>Preguntas frecuentes sobre los productos de Snoopy</h2>
<details><summary>¿Qué regalo de Snoopy puedo hacer por poco dinero?</summary><p>La crema de manos y la bolsa térmica de Snoopy cuestan menos de 6 €, y las tazas y los platos están por debajo de 15 €. Son ideales para el amigo invisible.</p></details>
<details><summary>¿Los platos de Snoopy se pueden usar para comer?</summary><p>Sí, son platos de cerámica pensados para la mesa. Muchos clientes también los usan como decoración, joyero o vaciabolsillos.</p></details>
' . $shipping_faq . '
<details><summary>¿Puedo devolver un producto?</summary><p>Sí, tienes 14 días naturales desde que lo recibes, siempre que esté sin usar y en su embalaje original.</p></details>
',
        ],
        'pokemon' => [
            'intro'   => 'Blind box de Pokémon, puzzles de 500 y 1000 piezas, boles de Pikachu y Eevee y la mochila Loungefly de los Pokémon iniciales. Regalos para entrenadores de todas las edades que quieren llevar su pasión por Pokémon a casa.',
            'landing' => '
<h2>Regalos Pokémon para entrenadores de todas las edades</h2>
<p>Pokémon es de esos fandoms que unen generaciones: quien empezó con Game Boy y quien lo descubrió ayer comparten el mismo cariño por Pikachu y Eevee. En esta colección hemos juntado regalos Pokémon para coleccionar, para montar en una tarde tranquila y para usar cada día.</p>

<h3>Blind box de Pokémon: la emoción de no saber cuál te toca</h3>
<p>Las blind box son cajas cerradas: sabes de qué colección es, pero no qué figura hay dentro hasta abrirla. Tenemos varias colecciones de Pokémon, como <a href="{{product:blind-box-pokemon-faroles-magicos}}">Faroles Mágicos</a>, <a href="{{product:blind-box-pokemon-noche-estrellada}}">Noche Estrellada</a>, <a href="{{product:blind-box-pokemon-ventanas-japonesas}}">Ventanas Japonesas</a> o <a href="{{product:blind-box-pokemon-en-terrario}}">Pokémon en Terrario</a>. Son un regalo perfecto para el amigo invisible y engancha querer completar la colección. Todas en <a href="{{cat:figuras-sorpresa}}">figuras sorpresa</a>.</p>

<h3>Puzzles de Pokémon para tardes de manta</h3>
<p>Para desconectar sin pantallas: el <a href="{{product:puzzle-pokemon-parque-de-eevee-y-amigos-1000-piezas}}">puzzle Parque de Eevee y amigos</a> y el <a href="{{product:puzzle-pokemon-1000-piezas}}">puzzle Pokémon de 1000 piezas</a> son un reto para toda una tarde, y el <a href="{{product:puzzle-redondo-pokemon-500-piezas}}">puzzle redondo de 500 piezas</a> es más rápido de terminar. Más en <a href="{{cat:puzzles}}">puzzles</a>.</p>

<h3>Pikachu también en la cocina</h3>
<p>El <a href="{{product:bol-para-ramen-con-palillos-pokemon-pikachu-face-500-ml}}">bol de ramen de Pikachu con palillos</a> y el <a href="{{product:bol-de-ceramica-pokemon-coleccion-pikachu-y-eevee-500-ml}}">bol de cerámica de Pikachu y Eevee</a> convierten cualquier cena en un plan. Ver todos los <a href="{{cat:boles}}">boles</a>.</p>

<h3>Para fans de Loungefly</h3>
<p>La <a href="{{product:mochila-iniciales-pokemon-loungefly}}">mochila Loungefly de los Pokémon iniciales</a> es el capricho definitivo para quien colecciona Loungefly o quiere llevar a sus iniciales favoritos a todas partes.</p>

<h2>Preguntas frecuentes sobre los regalos Pokémon</h2>
<details><summary>¿Qué es una blind box de Pokémon?</summary><p>Es una caja cerrada con una figura de una colección concreta. No sabes qué modelo te toca hasta abrirla, y por eso es tan divertida de regalar y de coleccionar.</p></details>
<details><summary>¿Qué regalo Pokémon hago para el amigo invisible?</summary><p>Una blind box o un puzzle encajan en el presupuesto típico de amigo invisible y gustan tanto a niños como a adultos.</p></details>
' . $shipping_faq . '
<details><summary>¿Puedo devolver una blind box?</summary><p>Tienes 14 días naturales para devolver cualquier producto sin usar y en su embalaje original. En el caso de las blind box, eso significa con la caja sin abrir.</p></details>
',
        ],
    ];
}

function cozy_landing_seed_run() {
    $log = [];
    foreach ( cozy_landing_seed_data() as $slug => $data ) {
        $term = get_term_by( 'slug', $slug, 'product_brand' );
        if ( ! $term ) {
            $log[] = "$slug no encontrado";
            continue;
        }
        if ( '' === trim( $term->description ) ) {
            wp_update_term( $term->term_id, 'product_brand', [ 'description' => $data['intro'] ] );
        }
        if ( '' === trim( (string) get_term_meta( $term->term_id, COZY_LANDING_META, true ) ) ) {
            update_term_meta( $term->term_id, COZY_LANDING_META, cozy_landing_resolve_links( trim( $data['landing'] ) ) );
        }
        $log[] = "$slug ok";
    }
    return $log;
}

add_action( 'admin_init', function() {
    if ( get_option( 'cozy_landing_seed_version' ) === COZY_LANDING_SEED_VERSION || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    update_option( 'cozy_landing_seed_log', cozy_landing_seed_run(), false );
    update_option( 'cozy_landing_seed_version', COZY_LANDING_SEED_VERSION, false );
    do_action( 'litespeed_purge_all' );
} );
