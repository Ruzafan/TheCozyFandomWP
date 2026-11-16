<?php
/**
 * "Valora tu compra" — private, per-order review link.
 *
 * Why: product review tabs are hidden while a product has 0 reviews (see
 * functions.php → woocommerce_product_tabs), so guest buyers never see a form.
 * Instead every completed order gets a secret link (HMAC of order id + order
 * key) that opens a page listing that order's products with a rating + text
 * field each. No account needed. Reviews are saved as verified-owner reviews
 * and held for moderation; once approved the product tab appears on its own.
 *
 * Where the link shows up:
 *  - "Pedido completado" customer email (button)
 *  - My Account → Pedidos ("Valorar" action)
 *  - wp-admin order screen ("Copiar enlace" — to send by WhatsApp)
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

const COZY_RR_QV = 'cozy_valorar';

/* ------------------------------------------------------------------ */
/*  Link + order helpers                                               */
/* ------------------------------------------------------------------ */
function cozy_rr_token( WC_Order $order ) {
    return substr( hash_hmac( 'sha256', $order->get_id() . '|' . $order->get_order_key(), wp_salt( 'auth' ) ), 0, 24 );
}

function cozy_rr_url( WC_Order $order ) {
    return add_query_arg( [ COZY_RR_QV => $order->get_id(), 'k' => cozy_rr_token( $order ) ], home_url( '/' ) );
}

function cozy_rr_is_eligible( WC_Order $order ) {
    return $order->has_status( apply_filters( 'cozy_rr_statuses', [ 'completed' ] ) );
}

/** Distinct published products of the order (variations → parent product). */
function cozy_rr_products( WC_Order $order ) {
    $out = [];
    foreach ( $order->get_items() as $item ) {
        if ( ! $item instanceof WC_Order_Item_Product ) continue;
        $pid = (int) $item->get_product_id();
        if ( ! $pid || isset( $out[ $pid ] ) ) continue;
        $product = wc_get_product( $pid );
        if ( ! $product || 'publish' !== $product->get_status() ) continue;
        $out[ $pid ] = $product;
    }
    return $out;
}

/** product_id => comment_id of the reviews already left through this order. */
function cozy_rr_done( WC_Order $order ) {
    $done = $order->get_meta( '_cozy_rr_done' );
    return is_array( $done ) ? $done : [];
}

/** "Marta García López" → "Marta G." (never publish a customer's full name). */
function cozy_rr_author_name( WC_Order $order ) {
    $first = trim( (string) $order->get_billing_first_name() );
    $last  = trim( (string) $order->get_billing_last_name() );
    if ( '' === $first ) return 'Cliente';
    $first = preg_split( '/\s+/', $first )[0];
    return $last ? $first . ' ' . mb_strtoupper( mb_substr( $last, 0, 1 ) ) . '.' : $first;
}

/* ------------------------------------------------------------------ */
/*  Submission                                                         */
/* ------------------------------------------------------------------ */
function cozy_rr_handle_post( WC_Order $order ) {
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['cozy_rr_nonce'] ) ) {
        return null;
    }
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cozy_rr_nonce'] ) ), 'cozy_rr_' . $order->get_id() ) ) {
        return [ 'saved' => 0, 'errors' => [ 'La página ha caducado. Recárgala e inténtalo de nuevo.' ] ];
    }

    $ratings = isset( $_POST['rating'] ) && is_array( $_POST['rating'] ) ? wp_unslash( $_POST['rating'] ) : [];
    $texts   = isset( $_POST['review'] ) && is_array( $_POST['review'] ) ? wp_unslash( $_POST['review'] ) : [];
    $done    = cozy_rr_done( $order );
    $author  = cozy_rr_author_name( $order );
    $saved   = 0;
    $errors  = [];

    foreach ( cozy_rr_products( $order ) as $pid => $product ) {
        if ( isset( $done[ $pid ] ) ) continue;
        $rating = absint( $ratings[ $pid ] ?? 0 );
        $text   = trim( sanitize_textarea_field( (string) ( $texts[ $pid ] ?? '' ) ) );
        if ( ! $rating && '' === $text ) continue; // left blank on purpose

        if ( $rating < 1 || $rating > 5 ) {
            $errors[] = sprintf( 'Elige de 1 a 5 estrellas para «%s».', $product->get_name() );
            continue;
        }
        if ( mb_strlen( $text ) < 10 ) {
            $errors[] = sprintf( 'Cuéntanos un poco más sobre «%s» (mínimo 10 caracteres).', $product->get_name() );
            continue;
        }

        $comment_id = wp_insert_comment( [
            'comment_post_ID'      => $pid,
            'comment_author'       => $author,
            'comment_author_email' => $order->get_billing_email(),
            'comment_content'      => mb_substr( $text, 0, 2000 ),
            'comment_type'         => 'review',
            'comment_approved'     => 0,
            'user_id'              => (int) $order->get_customer_id(),
            'comment_author_IP'    => '',
            'comment_agent'        => 'cozy-review-request',
        ] );
        if ( ! $comment_id ) continue;

        add_comment_meta( $comment_id, 'rating', $rating, true );
        add_comment_meta( $comment_id, 'verified', 1, true );
        add_comment_meta( $comment_id, '_cozy_rr_order', $order->get_id(), true );
        $done[ $pid ] = $comment_id;
        $saved++;

        if ( get_option( 'moderation_notify' ) ) {
            wp_notify_moderator( $comment_id );
        }
    }

    if ( $saved ) {
        $order->update_meta_data( '_cozy_rr_done', $done );
        $order->save();
        $order->add_order_note( sprintf( 'El cliente ha enviado %d valoración(es) desde el enlace «Valora tu compra» (pendientes de aprobar en Productos → Valoraciones).', $saved ) );
        if ( function_exists( 'cozy_log_event' ) ) {
            cozy_log_event( 'review_submitted', [ 'value' => $saved, 'detail' => 'order:' . $order->get_id(), 'page_path' => false ] );
        }
    }

    if ( ! $saved && ! $errors ) {
        $errors[] = 'Elige las estrellas y escribe unas palabras sobre al menos un producto.';
    }

    return [ 'saved' => $saved, 'errors' => $errors ];
}

/* ------------------------------------------------------------------ */
/*  Page                                                               */
/* ------------------------------------------------------------------ */
add_action( 'template_redirect', function () {
    if ( ! isset( $_GET[ COZY_RR_QV ] ) || ! function_exists( 'wc_get_order' ) ) return;

    // Private, per-order page: never cache, never index.
    if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
    do_action( 'litespeed_control_set_nocache', 'cozy review link' );
    nocache_headers();
    header( 'X-Robots-Tag: noindex, nofollow', true );
    add_filter( 'wp_robots', function () { return [ 'noindex' => true, 'nofollow' => true ]; }, 999 );
    add_filter( 'pre_get_document_title', function () { return 'Valora tu compra – ' . get_bloginfo( 'name' ); }, 999 );

    $id    = absint( $_GET[ COZY_RR_QV ] );
    $key   = sanitize_text_field( wp_unslash( $_GET['k'] ?? '' ) );
    $order = $id ? wc_get_order( $id ) : null;
    if ( ! $order instanceof WC_Order || '' === $key || ! hash_equals( cozy_rr_token( $order ), $key ) ) {
        status_header( 404 );
        cozy_rr_render( null, null );
        exit;
    }

    $result = cozy_rr_is_eligible( $order ) ? cozy_rr_handle_post( $order ) : null;
    cozy_rr_render( $order, $result );
    exit;
}, 1 );

function cozy_rr_render( $order, $result ) {
    $products = $order ? cozy_rr_products( $order ) : [];
    $done     = $order ? cozy_rr_done( $order ) : [];
    $pending  = array_diff_key( $products, $done );
    $posted_r = ( $result && ! empty( $_POST['rating'] ) && is_array( $_POST['rating'] ) ) ? wp_unslash( $_POST['rating'] ) : [];
    $posted_t = ( $result && ! empty( $_POST['review'] ) && is_array( $_POST['review'] ) ) ? wp_unslash( $_POST['review'] ) : [];
    $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

    get_header();
    ?>
    <div id="cozy-page" class="min-h-[60vh] bg-gradient-to-b from-cozy-cream to-cozy-sand/30 px-4 sm:px-6 py-10 md:py-16">
        <div class="max-w-2xl mx-auto">
            <h1 class="font-serif text-3xl md:text-4xl text-cozy-coffee text-center mb-3">Valora tu compra</h1>

            <?php if ( ! $order ) : ?>
                <p class="text-center text-cozy-coffee/75">Este enlace no es válido o ha caducado. Si quieres dejarnos tu opinión, escríbenos y te mandamos uno nuevo.</p>

            <?php elseif ( ! cozy_rr_is_eligible( $order ) ) : ?>
                <p class="text-center text-cozy-coffee/75">Podrás valorar tu pedido en cuanto lo recibas. ¡Te avisaremos por email!</p>

            <?php elseif ( ! $pending ) : ?>
                <div class="bg-white rounded-3xl border border-cozy-sand p-8 text-center shadow-sm">
                    <p class="text-4xl mb-3">💛</p>
                    <p class="text-lg font-bold text-cozy-coffee mb-2">¡Gracias por tu opinión!</p>
                    <p class="text-sm text-cozy-coffee/70 mb-6">La publicaremos en la tienda en cuanto la revisemos. Ayuda muchísimo a otras personas a decidirse.</p>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="inline-block bg-cozy-mint hover:bg-cozy-mintDark text-white font-bold px-6 py-3 rounded-full transition-colors">Volver a la tienda</a>
                </div>

            <?php else : ?>
                <p class="text-center text-cozy-coffee/75 mb-8">
                    Hola <?php echo esc_html( preg_split( '/\s+/', trim( (string) $order->get_billing_first_name() ) . ' ' )[0] ); ?>, ¿qué te ha parecido tu pedido?
                    Tu opinión, buena o mala, ayuda a otras personas a decidir. Puedes valorar solo los productos que quieras.
                </p>

                <?php if ( $result && $result['saved'] ) : ?>
                    <div class="mb-6 p-4 rounded-2xl bg-cozy-mintLight text-cozy-coffee text-sm">¡Gracias! Hemos guardado <?php echo (int) $result['saved']; ?> valoración(es). Puedes valorar el resto cuando quieras.</div>
                <?php endif; ?>
                <?php if ( $result && $result['errors'] ) : ?>
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm" role="alert">
                        <?php foreach ( $result['errors'] as $err ) : ?><p class="m-0"><?php echo esc_html( $err ); ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-5">
                    <?php wp_nonce_field( 'cozy_rr_' . $order->get_id(), 'cozy_rr_nonce' ); ?>
                    <?php foreach ( $products as $pid => $product ) : ?>
                        <div class="bg-white rounded-3xl border border-cozy-sand p-5 md:p-6 shadow-sm">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 shrink-0 rounded-2xl overflow-hidden bg-cozy-cream"><?php echo $product->get_image( 'thumbnail', [ 'class' => 'w-full h-full object-cover' ] ); // phpcs:ignore ?></div>
                                <p class="font-bold text-cozy-coffee leading-snug m-0"><?php echo esc_html( $product->get_name() ); ?></p>
                            </div>
                            <?php if ( isset( $done[ $pid ] ) ) : ?>
                                <p class="text-sm text-cozy-mintDark font-bold m-0">✓ Ya lo has valorado, ¡gracias!</p>
                            <?php else : ?>
                                <?php $cur = absint( $posted_r[ $pid ] ?? 0 ); ?>
                                <fieldset class="mb-3 border-0 p-0 m-0">
                                    <legend class="text-sm font-semibold text-cozy-coffee mb-1 p-0">Tu puntuación</legend>
                                    <div class="cozy-rr-stars">
                                        <?php for ( $i = 5; $i >= 1; $i-- ) : $fid = 'r' . $pid . '_' . $i; ?>
                                            <input type="radio" id="<?php echo esc_attr( $fid ); ?>" name="rating[<?php echo (int) $pid; ?>]" value="<?php echo (int) $i; ?>" <?php checked( $cur, $i ); ?>>
                                            <label for="<?php echo esc_attr( $fid ); ?>" title="<?php echo esc_attr( $i . ' de 5' ); ?>"><span class="screen-reader-text"><?php echo esc_html( $i . ' de 5 estrellas' ); ?></span>★</label>
                                        <?php endfor; ?>
                                    </div>
                                </fieldset>
                                <label for="t<?php echo (int) $pid; ?>" class="block text-sm font-semibold text-cozy-coffee mb-1">Tu opinión</label>
                                <textarea id="t<?php echo (int) $pid; ?>" name="review[<?php echo (int) $pid; ?>]" rows="3" maxlength="2000"
                                    class="w-full rounded-2xl border border-cozy-sand bg-cozy-cream/50 p-3 text-sm text-cozy-coffee focus:outline-none focus:border-cozy-mint"
                                    placeholder="¿Qué tal la calidad? ¿Es como en las fotos? ¿Fue un regalo?"><?php echo esc_textarea( (string) ( $posted_t[ $pid ] ?? '' ) ); ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <p class="text-[11px] text-cozy-coffee/55 leading-snug">Publicaremos tu opinión con tu nombre y la inicial de tu apellido (<?php echo esc_html( cozy_rr_author_name( $order ) ); ?>) y la etiqueta «compra verificada». Publicamos todas las opiniones, buenas y malas; solo retiramos las que incluyan datos personales o lenguaje ofensivo.</p>
                    <button type="submit" class="w-full bg-cozy-mint hover:bg-cozy-mintDark text-white font-bold py-4 rounded-full transition-colors">Enviar mi opinión</button>
                </form>
                <style>
                    .cozy-rr-stars{display:inline-flex;flex-direction:row-reverse;gap:2px}
                    .cozy-rr-stars input{position:absolute;opacity:0;width:1px;height:1px}
                    .cozy-rr-stars label{font-size:36px!important;line-height:1!important;margin:0!important;padding:0 2px!important;cursor:pointer;color:#e6dccf;transition:color .15s}
                    .cozy-rr-stars input:checked~label,.cozy-rr-stars label:hover,.cozy-rr-stars label:hover~label{color:#D4A373}
                    .cozy-rr-stars input:focus-visible+label{outline:2px solid #88C4B5;outline-offset:2px;border-radius:4px}
                </style>
            <?php endif; ?>
        </div>
    </div>
    <?php
    get_footer();
}

/* ------------------------------------------------------------------ */
/*  Where the link is offered                                          */
/* ------------------------------------------------------------------ */

// 1) "Pedido completado" email.
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text, $email = null ) {
    if ( $sent_to_admin || ! $order instanceof WC_Order || ! $email || 'customer_completed_order' !== $email->id ) return;
    if ( ! cozy_rr_products( $order ) ) return;
    $url = cozy_rr_url( $order );

    if ( $plain_text ) {
        echo "\n¿Qué te ha parecido tu pedido? Valóralo en 1 minuto, sin registrarte:\n" . esc_url_raw( $url ) . "\n\n";
        return;
    }
    ?>
    <div style="margin:28px 0;padding:22px;border-radius:14px;background:#FCF9F5;border:1px solid #F2E6D5;text-align:center;">
        <p style="margin:0 0 6px;font-size:17px;font-weight:bold;color:#3A3128;">¿Qué te ha parecido tu pedido?</p>
        <p style="margin:0 0 16px;font-size:14px;color:#3A3128;">Tu opinión ayuda a otras personas a decidirse. Es 1 minuto y no necesitas cuenta.</p>
        <a href="<?php echo esc_url( $url ); ?>" style="display:inline-block;background:#88C4B5;color:#ffffff;font-weight:bold;text-decoration:none;padding:12px 26px;border-radius:999px;">★ Valorar mi compra</a>
    </div>
    <?php
}, 20, 4 );

// 2) My Account → Pedidos.
add_filter( 'woocommerce_my_account_my_orders_actions', function ( $actions, $order ) {
    if ( $order instanceof WC_Order && cozy_rr_is_eligible( $order ) && array_diff_key( cozy_rr_products( $order ), cozy_rr_done( $order ) ) ) {
        $actions['cozy_valorar'] = [ 'url' => cozy_rr_url( $order ), 'name' => 'Valorar' ];
    }
    return $actions;
}, 10, 2 );

// 3) wp-admin order screen: copyable link (to send by WhatsApp etc.).
add_action( 'woocommerce_admin_order_data_after_order_details', function ( $order ) {
    if ( ! $order instanceof WC_Order || ! cozy_rr_products( $order ) ) return;
    $url   = cozy_rr_url( $order );
    $done  = count( cozy_rr_done( $order ) );
    $total = count( cozy_rr_products( $order ) );
    ?>
    <div class="form-field form-field-wide" style="margin-top:12px;">
        <label for="cozy-rr-link"><strong>Enlace «Valora tu compra»</strong></label>
        <div style="display:flex;gap:6px;">
            <input type="text" id="cozy-rr-link" readonly value="<?php echo esc_attr( $url ); ?>" style="flex:1;" onclick="this.select()">
            <button type="button" class="button" onclick="var i=document.getElementById('cozy-rr-link');i.select();(navigator.clipboard?navigator.clipboard.writeText(i.value):Promise.reject()).catch(function(){document.execCommand('copy');});this.textContent='¡Copiado!';">Copiar</button>
        </div>
        <p class="description" style="margin-top:4px;">
            <?php echo esc_html( sprintf( 'Valorados: %d de %d productos.', $done, $total ) ); ?>
            <?php if ( ! cozy_rr_is_eligible( $order ) ) : ?> El enlace funcionará cuando el pedido esté <em>Completado</em>.<?php endif; ?>
            Se envía solo en el email de «Pedido completado».
        </p>
    </div>
    <?php
} );
