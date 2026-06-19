<?php
/**
 * Single Product Reviews — Cozy Fandom override
 * Mirrors woocommerce/templates/single-product-reviews.php (WC 8.x)
 *
 * Custom: renders the login-required message inline so comment_form()
 * is never called for logged-out users, and wraps comment_form() in a
 * try/catch to prevent plugin hooks (e.g. WooCommerce Payments) from
 * surfacing a critical-error banner inside the reviews panel.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) return;
?>
<div id="reviews" class="woocommerce-Reviews">

    <div id="comments">

        <?php if ( have_comments() ) : ?>
            <ol class="commentlist woocommerce-Reviews-list">
                <?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [
                    'callback'          => 'woocommerce_comments',
                    'reverse_top_level' => false,
                ] ) ); ?>
            </ol>

            <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
                <nav class="woocommerce-pagination">
                    <?php paginate_comments_links( [
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                        'type'      => 'list',
                    ] ); ?>
                </nav>
            <?php endif; ?>

        <?php else : ?>
            <p class="woocommerce-noreviews">
                <?php esc_html_e( 'No hay valoraciones aún.', 'woocommerce' ); ?>
            </p>
        <?php endif; ?>

    </div>

    <div id="review_form_wrapper">

        <?php if ( ! is_user_logged_in() ) : ?>
            <p class="must-log-in" style="font-size:0.875rem;color:rgba(74,63,53,0.6);margin-top:1rem">
                <?php printf(
                    wp_kses(
                        __( 'Debes %1$siniciar sesión%2$s para dejar una valoración.', 'cozy-fandom-child' ),
                        [ 'a' => [ 'href' => [], 'class' => [] ] ]
                    ),
                    '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" style="color:#88C4B5;font-weight:600">',
                    '</a>'
                ); ?>
            </p>

        <?php else :
            $comment_form = [
                'title_reply'         => have_comments()
                    ? __( 'Add a review', 'woocommerce' )
                    : sprintf( __( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
                'title_reply_to'      => __( 'Leave a Reply to %s', 'woocommerce' ),
                'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
                'title_reply_after'   => '</span>',
                'comment_notes_after' => '',
                'label_submit'        => __( 'Submit', 'woocommerce' ),
                'logged_in_as'        => '',
                'comment_field'       => '',
                'fields'              => [],
            ];
            ?>
            <div id="review_form">
                <?php
                // Wrap in try/catch + output buffer so a fatal error from any
                // plugin hook (e.g. WooCommerce Payments) doesn't surface as
                // "Ha habido un error crítico" inside the reviews panel.
                try {
                    ob_start();
                    comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
                    ob_end_flush();
                } catch ( \Throwable $e ) {
                    ob_end_clean();
                }
                ?>
            </div>
        <?php endif; ?>

    </div>

</div>
