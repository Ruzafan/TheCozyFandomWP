<?php
/**
 * Trust pages — "Preguntas frecuentes" and "Sobre nosotros"
 *
 *  - [cozy_shipping_rates] and [cozy_contact_options] shortcodes, so page copy
 *    follows the real settings (shipping tiers, WhatsApp number) instead of
 *    going stale when they change.
 *  - One-shot seed: creates /preguntas-frecuentes/ (published) and
 *    /sobre-nosotros/ (draft, a guided outline for the owner to fill in) if
 *    no page with that slug exists yet.
 *  - cozy_published_page_link(): footer/sitemap links only once a page is live.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/*  Shortcodes                                                         */
/* ------------------------------------------------------------------ */
add_shortcode( 'cozy_shipping_rates', function() {
    if ( ! function_exists( 'cozy_get_free_shipping_threshold_html' ) ) {
        return '';
    }
    $reduced = function_exists( 'cozy_reduced_shipping' ) ? cozy_reduced_shipping() : null;
    $free    = cozy_get_free_shipping_threshold_html();
    if ( $reduced ) {
        return sprintf(
            'El envío es <strong>gratis en pedidos de más de %1$s</strong>, cuesta <strong>%2$s en pedidos desde %3$s</strong> y 5,99 € en el resto.',
            $free,
            cozy_price_short( $reduced['cost'] ),
            cozy_price_short( $reduced['min'] )
        );
    }
    return sprintf( 'El envío es <strong>gratis en pedidos de más de %s</strong> y cuesta 5,99 € en el resto.', $free );
} );

add_shortcode( 'cozy_contact_options', function() {
    $email    = 'hola@thecozyfandom.com';
    $whatsapp = preg_replace( '/[^0-9]/', '', (string) get_option( 'cozy_whatsapp_number', '' ) );
    $items    = [ sprintf( '📧 Email: <a href="mailto:%1$s">%1$s</a>', esc_html( $email ) ) ];
    if ( $whatsapp ) {
        $items[] = sprintf( '💬 WhatsApp: <a href="%s" target="_blank" rel="noopener">escríbenos aquí</a> (+%s)', esc_url( 'https://wa.me/' . $whatsapp ), esc_html( $whatsapp ) );
    }
    return '<ul><li>' . implode( '</li><li>', $items ) . '</li></ul>';
} );

/* URL of a published page by slug, or null (draft / missing). */
function cozy_published_page_link( $slug ) {
    $page = get_page_by_path( $slug );
    return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : null;
}

/* ------------------------------------------------------------------ */
/*  One-shot page seed                                                 */
/* ------------------------------------------------------------------ */
const COZY_TRUST_PAGES_SEED_VERSION = '2026-10-30';

function cozy_trust_pages_faq_content() {
    $q = function( $question, $answer ) {
        return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$question}</summary><!-- wp:paragraph -->\n<p>{$answer}</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n\n";
    };
    $h = function( $title ) {
        return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$title}</h2>\n<!-- /wp:heading -->\n\n";
    };
    $returns = esc_url( home_url( '/envios-y-devoluciones/' ) );

    return "<!-- wp:paragraph -->\n<p>Aquí tienes las respuestas a lo que más nos preguntáis. Si no encuentras lo que buscas, escríbenos y te contestamos encantados.</p>\n<!-- /wp:paragraph -->\n\n"
        . $h( 'Envíos' )
        . $q( '¿A dónde enviáis?', 'Enviamos a toda la España peninsular. Si vives en otra zona, escríbenos a hola@thecozyfandom.com y miramos opciones para tu envío.' )
        . $q( '¿Cuánto cuesta el envío?', '[cozy_shipping_rates] El coste exacto siempre aparece en el carrito antes de pagar.' )
        . $q( '¿Cuánto tarda en llegar mi pedido?', 'Entre 3 y 5 días laborables desde que confirmas el pedido. En cuanto sale, te enviamos por email el número de seguimiento.' )
        . $h( 'Devoluciones' )
        . $q( '¿Puedo devolver un producto?', "Sí, tienes 14 días naturales desde que lo recibes, siempre que esté sin usar y en su embalaje original. Tienes todos los detalles en <a href=\"{$returns}\">envíos y devoluciones</a>." )
        . $q( '¿Puedo devolver una blind box?', 'Sí, siempre que la caja esté sin abrir: una vez abierta no se puede saber qué figura contenía.' )
        . $q( '¿Y si mi pedido llega dañado?', 'Escríbenos por email o WhatsApp con una foto del paquete y del producto, y te ayudamos a solucionarlo lo antes posible.' )
        . $h( 'Productos' )
        . $q( '¿Vuestros productos son oficiales?', 'Sí. Todos nuestros productos son oficiales o tienen licencia oficial de su marca: Disney, Harry Potter, Pokémon, Snoopy, Pusheen, Sanrio y el resto de licencias que encontrarás en la tienda.' )
        . $q( '¿Cómo preparáis los pedidos?', 'Empaquetamos cada pedido con cariño para que llegue en perfecto estado y abrirlo sea un pequeño momento cozy.' )
        . $h( 'Pagos' )
        . $q( '¿Qué métodos de pago aceptáis?', 'Puedes pagar con tarjeta, Google Pay o Apple Pay.' )
        . $q( '¿Es seguro pagar en la web?', 'Sí. El pago va 100% cifrado y nunca vemos ni guardamos los datos de tu tarjeta.' )
        . $h( '¿Hablamos?' )
        . "<!-- wp:paragraph -->\n<p>Si tienes cualquier duda antes o después de tu compra, estamos aquí:</p>\n<!-- /wp:paragraph -->\n\n"
        . "<!-- wp:shortcode -->\n[cozy_contact_options]\n<!-- /wp:shortcode -->";
}

function cozy_trust_pages_about_content() {
    $h = function( $title ) {
        return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$title}</h2>\n<!-- /wp:heading -->\n\n";
    };
    $p = function( $text ) {
        return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
    };
    return $p( '<em>✏️ Borrador: sustituye los textos entre corchetes y añade tus fotos. La página no se verá en la web hasta que la publiques. Borra esta línea antes de publicar.</em>' )
        . $h( 'Hola, somos The Cozy Fandom' )
        . $p( '[Preséntate en 2-3 frases: quién eres, desde dónde envías (p. ej. «una pequeña tienda desde Barcelona») y qué te une a los fandoms.]' )
        . $p( '[📷 Añade aquí una foto tuya, de tu rincón o de tu mesa de empaquetado. Las caras generan mucha confianza.]' )
        . $h( 'Cómo empezó todo' )
        . $p( '[Cuenta por qué abriste la tienda: la colección que empezó todo, qué echabas de menos al comprar regalos frikis, qué querías hacer diferente.]' )
        . $h( 'Lo que nos importa' )
        . $p( '<strong>Productos 100% oficiales.</strong> Todo lo que vendemos es oficial o tiene licencia oficial de su marca. Nada de imitaciones.' )
        . $p( '[<strong>Elegidos uno a uno.</strong> Explica cómo seleccionas lo que entra en la tienda.]' )
        . $p( '[<strong>Empaquetado con cariño.</strong> Describe cómo preparas cada pedido: papel de seda, una nota, una pegatina…]' )
        . $h( 'Estamos aquí para ayudarte' )
        . $p( '¿Tienes dudas sobre un producto o un pedido? Escríbenos, respondemos nosotros mismos:' )
        . "<!-- wp:shortcode -->\n[cozy_contact_options]\n<!-- /wp:shortcode -->";
}

function cozy_trust_pages_contact_content() {
    $p = function( $text ) {
        return "<!-- wp:paragraph -->
<p>{$text}</p>
<!-- /wp:paragraph -->

";
    };
    $h = function( $title ) {
        return "<!-- wp:heading -->
<h2 class=\"wp-block-heading\">{$title}</h2>
<!-- /wp:heading -->

";
    };
    $faq     = esc_url( home_url( '/preguntas-frecuentes/' ) );
    $returns = esc_url( home_url( '/envios-y-devoluciones/' ) );
    return $p( '¿Tienes una duda sobre un producto, un pedido o una devolución? Escríbenos y te respondemos nosotros mismos, normalmente en menos de 24 horas laborables.' )
        . "<!-- wp:shortcode -->
[cozy_contact_options]
<!-- /wp:shortcode -->

"
        . $h( 'Dónde estamos' )
        . $p( 'The Cozy Fandom es una tienda online con sede en Lliçà de Vall (08185, Barcelona, España). Enviamos a toda la España peninsular; no tenemos tienda física.' )
        . $h( 'Antes de escribirnos' )
        . $p( "Puede que tu respuesta ya esté en las <a href=\"{$faq}\">preguntas frecuentes</a> o en nuestra <a href=\"{$returns}\">política de envíos y devoluciones</a>." );
}

function cozy_trust_pages_seed_run() {
    $log   = [];
    $pages = [
        'preguntas-frecuentes' => [ 'Preguntas frecuentes', 'publish', 'cozy_trust_pages_faq_content' ],
        'sobre-nosotros'       => [ 'Sobre nosotros', 'draft', 'cozy_trust_pages_about_content' ],
        'contacto'             => [ 'Contacto', 'publish', 'cozy_trust_pages_contact_content' ],
    ];
    foreach ( $pages as $slug => [ $title, $status, $content_fn ] ) {
        if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
            $log[] = "$slug ya existía";
            continue;
        }
        $id    = wp_insert_post( [
            'post_type'    => 'page',
            'post_name'    => $slug,
            'post_title'   => $title,
            'post_status'  => $status,
            'post_content' => call_user_func( $content_fn ),
        ], true );
        $log[] = is_wp_error( $id ) ? "$slug error: " . $id->get_error_message() : "$slug creada ($status)";
    }
    return $log;
}

/* Runs on the first request after a deploy (not only in wp-admin), so new
   pages go live straight away. add_option() fails when the option already
   exists, which makes it a lock against two requests seeding at once. */
add_action( 'init', function() {
    if ( get_option( 'cozy_trust_pages_seed_version' ) === COZY_TRUST_PAGES_SEED_VERSION ) {
        return;
    }
    $lock = get_option( 'cozy_trust_pages_seed_lock' );
    if ( $lock && time() - (int) $lock < 300 ) {
        return;
    }
    delete_option( 'cozy_trust_pages_seed_lock' );
    if ( ! add_option( 'cozy_trust_pages_seed_lock', time(), '', false ) ) {
        return;
    }
    update_option( 'cozy_trust_pages_seed_log', cozy_trust_pages_seed_run(), false );
    update_option( 'cozy_trust_pages_seed_version', COZY_TRUST_PAGES_SEED_VERSION, false );
    delete_option( 'cozy_trust_pages_seed_lock' );
    do_action( 'litespeed_purge_all' );
}, 20 );
