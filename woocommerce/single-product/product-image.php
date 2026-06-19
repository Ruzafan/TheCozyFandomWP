<?php
/**
 * Custom Product Gallery – Cozy Fandom
 * Template override: woocommerce/single-product/product-image.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

global $product;

$main_id     = $product->get_image_id();
$gallery_ids = $product->get_gallery_image_ids();
$all_ids     = $main_id ? array_merge( [ $main_id ], $gallery_ids ) : $gallery_ids;

if ( empty( $all_ids ) ) {
    echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$total = count( $all_ids );
?>

<div class="cozy-gallery" data-total="<?php echo absint( $total ); ?>">

    <!-- Main image / slider track -->
    <div class="cozy-gallery__main">
        <div class="cozy-gallery__track" id="cozy-gallery-track">
            <?php foreach ( $all_ids as $i => $image_id ) :
                $alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
                if ( ! $alt ) $alt = $product->get_name();
            ?>
            <div class="cozy-gallery__slide" aria-hidden="<?php echo $i > 0 ? 'true' : 'false'; ?>">
                <?php echo wp_get_attachment_image( $image_id, 'woocommerce_single', false, [
                    'class'   => 'cozy-gallery__img',
                    'loading' => $i === 0 ? 'eager' : 'lazy',
                    'alt'     => esc_attr( $alt ),
                ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ( $total > 1 ) : ?>
        <button class="cozy-gallery__arrow cozy-gallery__arrow--prev"
                onclick="cozyGalleryNav(-1)"
                aria-label="<?php esc_attr_e( 'Imagen anterior', 'woocommerce' ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button class="cozy-gallery__arrow cozy-gallery__arrow--next"
                onclick="cozyGalleryNav(1)"
                aria-label="<?php esc_attr_e( 'Siguiente imagen', 'woocommerce' ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
    </div>

    <?php if ( $total > 1 ) : ?>
    <div class="cozy-gallery__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Miniaturas del producto', 'woocommerce' ); ?>">
        <?php foreach ( $all_ids as $i => $image_id ) : ?>
        <button class="cozy-gallery__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                onclick="cozyGalleryGoTo(<?php echo absint( $i ); ?>)"
                role="tab"
                aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                aria-label="<?php printf( esc_attr__( 'Imagen %d', 'woocommerce' ), $i + 1 ); ?>">
            <?php echo wp_get_attachment_image( $image_id, 'thumbnail', false, [
                'loading' => 'lazy',
                'alt'     => '',
            ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
(function () {
    var current = 0;
    var total   = <?php echo absint( $total ); ?>;

    window.cozyGalleryGoTo = function (index) {
        if (index < 0)      index = total - 1;
        if (index >= total) index = 0;
        current = index;

        var track  = document.getElementById('cozy-gallery-track');
        var slides = track  ? track.querySelectorAll('.cozy-gallery__slide')  : [];
        var thumbs = document.querySelectorAll('.cozy-gallery__thumb');

        if (track) track.style.transform = 'translateX(-' + (current * 100) + '%)';

        slides.forEach(function (s, i) {
            s.setAttribute('aria-hidden', i !== current ? 'true' : 'false');
        });
        thumbs.forEach(function (t, i) {
            t.classList.toggle('is-active', i === current);
            t.setAttribute('aria-selected', i === current ? 'true' : 'false');
        });

        /* Scroll the active thumb into view */
        if (thumbs[current]) {
            thumbs[current].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    };

    window.cozyGalleryNav = function (dir) {
        cozyGalleryGoTo(current + dir);
    };

    /* Touch/swipe support */
    var track = document.getElementById('cozy-gallery-track');
    if (track && total > 1) {
        var startX = 0;
        track.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
        }, { passive: true });
        track.addEventListener('touchend', function (e) {
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) cozyGalleryNav(diff > 0 ? 1 : -1);
        }, { passive: true });
    }

    /* Keyboard navigation when gallery is focused */
    document.addEventListener('keydown', function (e) {
        var gallery = document.querySelector('.cozy-gallery');
        if (!gallery || !gallery.matches(':focus-within')) return;
        if (e.key === 'ArrowLeft')  { e.preventDefault(); cozyGalleryNav(-1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); cozyGalleryNav(1); }
    });
}());
</script>
