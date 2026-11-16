<?php
/**
 * Cozy Fandom – Trust signals at the moment of decision
 *
 *  - cozy_checkout_reassurance(): compact "pago seguro · devoluciones ·
 *    oficiales · dudas" strip under every "Finalizar compra" button (cart
 *    page, cart drawer, checkout). Payment methods are read from the
 *    gateways actually enabled in WooCommerce, never hardcoded, so the strip
 *    can't promise a method the store doesn't take.
 *  - cozy_reviews_disclosure(): how reviews are verified. Required in Spain
 *    since RDL 24/2021 (Omnibus directive): a shop showing reviews must say
 *    whether and how it checks they come from real buyers.
 *  - cozy_render_home_reviews(): latest customer reviews on the home, with
 *    the store-wide average. Hidden until there are at least
 *    COZY_HOME_REVIEWS_MIN reviews — a near-empty block hurts more than none.
 *    Reviews are shown newest first regardless of rating (picking only the
 *    good ones would be a misleading practice under the same law).
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

const COZY_HOME_REVIEWS_MIN = 3;

/* ------------------------------------------------------------------ */
/*  PAYMENT METHODS — names of the gateways enabled in WooCommerce     */
/* ------------------------------------------------------------------ */
function cozy_enabled_payment_method_names() {
    static $names = null;
    if ( null !== $names ) return $names;
    $names = [];
    if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) return $names;

    foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
        if ( 'yes' !== $gateway->enabled ) continue;
        $title = trim( wp_strip_all_tags( (string) $gateway->get_title() ) );
        if ( '' !== $title ) $names[] = $title;
    }
    $names = array_values( array_unique( $names ) );
    return $names;
}

/* ------------------------------------------------------------------ */
/*  REASSURANCE STRIP                                                  */
/* ------------------------------------------------------------------ */
function cozy_checkout_reassurance( $variant = 'default' ) {
    $returns_url = function_exists( 'cozy_published_page_link' ) ? cozy_published_page_link( 'envios-y-devoluciones' ) : null;
    $contact_url = function_exists( 'cozy_published_page_link' ) ? cozy_published_page_link( 'contacto' ) : null;
    $methods     = cozy_enabled_payment_method_names();

    $lock  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
    $back  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>';
    $check = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
    $chat  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

    $rows   = [];
    $rows[] = [ $lock, '<strong>Pago 100% seguro</strong>' . ( $methods ? ' · ' . esc_html( implode( ', ', array_slice( $methods, 0, 4 ) ) ) : '' ) ];
    $rows[] = [ $back, $returns_url
        ? '<a href="' . esc_url( $returns_url ) . '" class="underline hover:text-cozy-mint">14 días para devolverlo</a> · gratis si llega dañado o defectuoso'
        : '14 días para devolverlo · gratis si llega dañado o defectuoso' ];
    $rows[] = [ $check, 'Productos <strong>oficiales con licencia</strong>' ];
    if ( $contact_url ) {
        $rows[] = [ $chat, '¿Dudas? <a href="' . esc_url( $contact_url ) . '" class="underline hover:text-cozy-mint">Te respondemos nosotros</a>' ];
    }

    $wrap = 'drawer' === $variant
        ? 'cozy-reassurance mt-1 space-y-1.5 text-[11px] text-cozy-coffee/65'
        : 'cozy-reassurance mt-4 p-4 bg-cozy-cream/60 rounded-2xl border border-cozy-sand/70 space-y-2 text-xs text-cozy-coffee/75';

    ob_start();
    ?>
    <ul class="<?php echo esc_attr( $wrap ); ?> list-none m-0 pl-0" aria-label="Compra segura">
        <?php foreach ( $rows as $row ) : ?>
            <li class="flex items-start gap-2 m-0 leading-snug">
                <span class="text-cozy-mint shrink-0 mt-px"><?php echo $row[0]; // phpcs:ignore -- static SVG ?></span>
                <span><?php echo wp_kses( $row[1], [ 'a' => [ 'href' => [], 'class' => [] ], 'strong' => [] ] ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

/* Checkout: right under "Realizar el pedido".
   Classic checkout has a hook for it. The store uses the Checkout BLOCK,
   which React renders client-side, so the strip is printed right after the
   block — never inside it: React owns that DOM and injected nodes can break
   its re-renders. On mobile that lands directly under "Realizar el pedido"
   (the order summary sits above the form there). */
add_action( 'woocommerce_review_order_after_submit', function () {
    echo cozy_checkout_reassurance(); // phpcs:ignore -- escaped inside
} );

add_filter( 'render_block_woocommerce/checkout', function ( $content ) {
    ob_start();
    ?>
    <div id="cozy-checkout-reassurance" class="max-w-xl"><?php echo cozy_checkout_reassurance(); // phpcs:ignore -- escaped inside ?></div>
    <?php
    return $content . ob_get_clean();
} );

/* ------------------------------------------------------------------ */
/*  REVIEWS DISCLOSURE (RDL 24/2021)                                   */
/* ------------------------------------------------------------------ */
function cozy_reviews_disclosure() {
    $verified_only = 'yes' === get_option( 'woocommerce_review_rating_verification_required' );
    $text = ( $verified_only
        ? 'Solo pueden opinar clientes que han comprado este producto en nuestra tienda.'
        : 'Las opiniones marcadas como «propietario verificado» son de clientes que han comprado el producto en nuestra tienda.' )
        . ' Pedimos opinión a todos los clientes tras la entrega y publicamos todas, buenas y malas.';
    return '<p class="cozy-reviews-disclosure text-[11px] text-cozy-coffee/55 mt-1 mb-4">ⓘ ' . esc_html( $text ) . '</p>';
}

/* ------------------------------------------------------------------ */
/*  HOME — latest customer reviews + store-wide average                */
/* ------------------------------------------------------------------ */
function cozy_store_review_stats() {
    global $wpdb;
    $row = $wpdb->get_row(
        "SELECT COUNT(*) AS n, AVG(CAST(m.meta_value AS DECIMAL(3,2))) AS avg_rating
         FROM {$wpdb->comments} c
         JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID AND p.post_type = 'product' AND p.post_status = 'publish'
         JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = 'rating'
         WHERE c.comment_approved = '1' AND c.comment_type = 'review' AND m.meta_value > 0"
    );
    return [
        'count'   => $row ? (int) $row->n : 0,
        'average' => $row && $row->avg_rating ? round( (float) $row->avg_rating, 1 ) : 0,
    ];
}

/* Public display name: first name + initial ("Laura M."), never the full name. */
function cozy_review_author_short( $comment ) {
    $name  = trim( (string) $comment->comment_author );
    $parts = preg_split( '/\s+/', $name );
    if ( ! $parts || '' === $parts[0] ) return 'Cliente';
    $short = $parts[0];
    if ( count( $parts ) > 1 ) $short .= ' ' . mb_strtoupper( mb_substr( end( $parts ), 0, 1 ) ) . '.';
    return $short;
}

function cozy_stars_html( $rating, $size = 14 ) {
    $out = '<span class="inline-flex text-cozy-accent" aria-label="' . esc_attr( sprintf( '%s de 5 estrellas', number_format_i18n( $rating, 1 ) ) ) . '">';
    for ( $i = 1; $i <= 5; $i++ ) {
        $fill = $rating >= $i - 0.25 ? 'currentColor' : 'none';
        $out .= '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 12 2"/></svg>';
    }
    return $out . '</span>';
}

function cozy_render_home_reviews() {
    $stats = cozy_store_review_stats();
    if ( $stats['count'] < COZY_HOME_REVIEWS_MIN ) return;

    $reviews = get_comments( [
        'post_type'  => 'product',
        'type'       => 'review',
        'status'     => 'approve',
        'number'     => 3,
        'orderby'    => 'comment_date_gmt',
        'order'      => 'DESC',
        'meta_query' => [ [ 'key' => 'rating', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ] ],
    ] );
    if ( ! $reviews ) return;
    ?>
    <section id="opiniones" class="py-16 px-6 md:px-12 max-w-7xl mx-auto" aria-labelledby="opiniones-title">
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-1.5 bg-cozy-mintLight text-cozy-coffee text-xs font-bold px-4 py-1.5 rounded-full border border-cozy-mint/20 mb-3">
                💬 Lo que dicen nuestros clientes
            </span>
            <h2 id="opiniones-title" class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee mb-2">Opiniones reales</h2>
            <p class="flex items-center justify-center gap-2 text-sm text-cozy-coffee/70 m-0">
                <?php echo cozy_stars_html( $stats['average'], 16 ); // phpcs:ignore ?>
                <strong class="text-cozy-coffee"><?php echo esc_html( number_format_i18n( $stats['average'], 1 ) ); ?>/5</strong>
                · <?php echo esc_html( sprintf( _n( '%s valoración', '%s valoraciones', $stats['count'], 'cozy-fandom-child' ), number_format_i18n( $stats['count'] ) ) ); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <?php foreach ( $reviews as $review ) :
                $rating   = (int) get_comment_meta( $review->comment_ID, 'rating', true );
                $verified = (bool) get_comment_meta( $review->comment_ID, 'verified', true );
                $product  = wc_get_product( $review->comment_post_ID );
                $text     = wp_trim_words( wp_strip_all_tags( $review->comment_content ), 40, '…' );
                ?>
                <figure class="bg-white rounded-[24px] p-6 border border-cozy-sand shadow-sm flex flex-col gap-3 m-0">
                    <?php echo cozy_stars_html( $rating ); // phpcs:ignore ?>
                    <blockquote class="text-sm text-cozy-coffee/85 leading-relaxed m-0 flex-1">“<?php echo esc_html( $text ); ?>”</blockquote>
                    <figcaption class="text-xs text-cozy-coffee/60 m-0">
                        <strong class="text-cozy-coffee"><?php echo esc_html( cozy_review_author_short( $review ) ); ?></strong>
                        <?php if ( $verified ) : ?> · <span class="text-cozy-mint font-bold">✓ Compra verificada</span><?php endif; ?>
                        <?php if ( $product ) : ?>
                            <br>sobre <a href="<?php echo esc_url( get_permalink( $product->get_id() ) . '#reviews' ); ?>" class="underline hover:text-cozy-mint"><?php echo esc_html( $product->get_name() ); ?></a>
                        <?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <p class="text-center text-[11px] text-cozy-coffee/50 mt-5 mb-0">Mostramos las opiniones más recientes, sin filtrar por puntuación.</p>
    </section>
    <?php
}
