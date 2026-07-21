<?php
/**
 * Cozy Fandom – Inline SVG Icon Library
 *
 * Replaces Font Awesome dependency with lightweight inline SVGs.
 * Each function returns an SVG string. All icons are decorative by default
 * (aria-hidden="true") — add your own aria-label on the parent if needed.
 *
 * Usage:  echo cozy_icon_arrow_right( '14' );
 *         echo cozy_icon( 'basket-shopping', '16' );
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Central dispatcher — call any icon by slug.
 *
 * @param string $name Icon slug (e.g. 'arrow-right').
 * @param string $size Width and height in px (default 16).
 * @param string $extra_class Additional CSS classes.
 * @return string SVG markup or empty string if icon not found.
 */
function cozy_icon( $name, $size = '16', $extra_class = '' ) {
    $fn = 'cozy_icon_' . str_replace( '-', '_', $name );
    if ( function_exists( $fn ) ) {
        return $fn( $size, $extra_class );
    }
    return '';
}

/* ─── Helper to build the opening <svg> tag ─────────────────── */
function cozy_icon_svg_open( $size, $extra_class = '', $viewbox = '0 0 24 24', $fill = 'none', $stroke = 'currentColor' ) {
    $cls = 'cozy-icon' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' );
    return sprintf(
        '<svg class="%s" width="%s" height="%s" viewBox="%s" fill="%s" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">',
        $cls,
        esc_attr( $size ),
        esc_attr( $size ),
        esc_attr( $viewbox ),
        esc_attr( $fill ),
        esc_attr( $stroke )
    );
}

/* ────────────────────────────────────────────────────────────── */
/*  SOLID ICONS (fill-based, no stroke)                          */
/* ────────────────────────────────────────────────────────────── */

/* ── Arrows ─────────────────────────────────────────────────── */
function cozy_icon_arrow_right( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M5 12h14M12 5l7 7-7 7"/>' .
        '</svg>';
}

function cozy_icon_arrow_right_long( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M5 12h14M12 5l7 7-7 7"/>' .
        '</svg>';
}

function cozy_icon_chevron_left( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="m15 18-6-6 6-6"/>' .
        '</svg>';
}

function cozy_icon_chevron_right( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="m9 18 6-6-6-6"/>' .
        '</svg>';
}

function cozy_icon_arrow_up_right_from_square( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>' .
        '<polyline points="15 3 21 3 21 9"/>' .
        '<line x1="10" y1="14" x2="21" y2="3"/>' .
        '</svg>';
}

/* ── E-commerce ─────────────────────────────────────────────── */
function cozy_icon_basket_shopping( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M2 3h2l3.6 7.59L6.25 13a2 2 0 0 0 2 2.5h7.5"/>' .
        '<circle cx="10" cy="20" r="1"/>' .
        '<circle cx="18" cy="20" r="1"/>' .
        '<path d="M7.5 10.5h12l-1.5 5h-9z"/>' .
        '</svg>';
}

function cozy_icon_bag_shopping( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>' .
        '<line x1="3" y1="6" x2="21" y2="6"/>' .
        '<path d="M16 10a4 4 0 0 1-8 0"/>' .
        '</svg>';
}

function cozy_icon_store( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M3 9V21h18V9"/>' .
        '<path d="M1 9h22"/>' .
        '<path d="m5 1 2 8"/>' .
        '<path d="m19 1-2 8"/>' .
        '<path d="M9 21v-6h6v6"/>' .
        '</svg>';
}

/* ── Actions / UI ───────────────────────────────────────────── */
function cozy_icon_xmark( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M18 6 6 18M6 6l12 12"/>' .
        '</svg>';
}

function cozy_icon_plus( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<line x1="12" y1="5" x2="12" y2="19"/>' .
        '<line x1="5" y1="12" x2="19" y2="12"/>' .
        '</svg>';
}

function cozy_icon_eye( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>' .
        '<circle cx="12" cy="12" r="3"/>' .
        '</svg>';
}

function cozy_icon_ban( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<circle cx="12" cy="12" r="10"/>' .
        '<line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>' .
        '</svg>';
}

function cozy_icon_magnifying_glass( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<circle cx="11" cy="11" r="8"/>' .
        '<path d="m21 21-4.35-4.35"/>' .
        '</svg>';
}

/* ── Status / info ──────────────────────────────────────────── */
function cozy_icon_box_open( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>' .
        '<polyline points="3.27 6.96 12 12.01 20.73 6.96"/>' .
        '<line x1="12" y1="22.08" x2="12" y2="12"/>' .
        '</svg>';
}

function cozy_icon_fire( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M12 2c.5 3.5-1.5 6-1.5 6s2-1 3 1c1.5 3-2 5.5-2 5.5s4-1.5 3.5-5c-.5-3-2-4-2-4s1.5 2 0 4-4 2.5-4 2.5 1-2 0-4-2.5-4-2.5-4 .5 3-1 5-3 2.5-3 2.5 1-3 3-5C6 4 5 2 5 2c3 0 5 1.5 7 0z"/>' .
        '</svg>';
}

function cozy_icon_triangle_exclamation( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/>' .
        '<line x1="12" y1="9" x2="12" y2="13"/>' .
        '<line x1="12" y1="17" x2="12.01" y2="17"/>' .
        '</svg>';
}

function cozy_icon_circle_info( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<circle cx="12" cy="12" r="10"/>' .
        '<path d="M12 16v-4"/>' .
        '<path d="M12 8h.01"/>' .
        '</svg>';
}

/* ── Transport / delivery ───────────────────────────────────── */
function cozy_icon_truck( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<rect x="1" y="3" width="15" height="13" rx="2"/>' .
        '<path d="M16 8h4l3 5v5h-7V8z"/>' .
        '<circle cx="5.5" cy="18.5" r="2.5"/>' .
        '<circle cx="18.5" cy="18.5" r="2.5"/>' .
        '</svg>';
}

/* ── User / account ─────────────────────────────────────────── */
function cozy_icon_user( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>' .
        '<circle cx="12" cy="7" r="4"/>' .
        '</svg>';
}

function cozy_icon_user_plus( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>' .
        '<circle cx="8.5" cy="7" r="4"/>' .
        '<line x1="20" y1="8" x2="20" y2="14"/>' .
        '<line x1="23" y1="11" x2="17" y2="11"/>' .
        '</svg>';
}

function cozy_icon_user_pen( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M11.5 15H7a4 4 0 0 0-4 4v2"/>' .
        '<circle cx="9.5" cy="7" r="4"/>' .
        '<path d="M17.5 12.5 21 16l-3 3-3.5-3.5 3-3z"/>' .
        '</svg>';
}

function cozy_icon_user_xmark( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>' .
        '<circle cx="8.5" cy="7" r="4"/>' .
        '<line x1="18" y1="8" x2="23" y2="13"/>' .
        '<line x1="23" y1="8" x2="18" y2="13"/>' .
        '</svg>';
}

function cozy_icon_right_from_bracket( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>' .
        '<polyline points="16 17 21 12 16 7"/>' .
        '<line x1="21" y1="12" x2="9" y2="12"/>' .
        '</svg>';
}

/* ── Objects ────────────────────────────────────────────────── */
function cozy_icon_key( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.78 7.78 5.5 5.5 0 0 1 7.78-7.78zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>' .
        '</svg>';
}

function cozy_icon_heart( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>' .
        '</svg>';
}

function cozy_icon_mug_saucer( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M17 8h1a4 4 0 0 1 0 8h-1"/>' .
        '<path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>' .
        '<line x1="6" y1="1" x2="6" y2="4"/>' .
        '<line x1="10" y1="1" x2="10" y2="4"/>' .
        '<line x1="14" y1="1" x2="14" y2="4"/>' .
        '</svg>';
}

function cozy_icon_pen_nib( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="m14 4 7 7-8.5 8.5a5 5 0 0 1-2.83 1.41L3 22l1.09-6.67a5 5 0 0 1 1.41-2.83L14 4z"/>' .
        '<path d="m14 4 3-3 7 7-3 3"/>' .
        '<circle cx="11.5" cy="14.5" r="2.5"/>' .
        '</svg>';
}

function cozy_icon_credit_card( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<rect x="1" y="4" width="22" height="16" rx="2"/>' .
        '<line x1="1" y1="10" x2="23" y2="10"/>' .
        '</svg>';
}

function cozy_icon_box( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>' .
        '<polyline points="3.27 6.96 12 12.01 20.73 6.96"/>' .
        '<line x1="12" y1="22.08" x2="12" y2="12"/>' .
        '</svg>';
}

function cozy_icon_location_dot( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>' .
        '<circle cx="12" cy="10" r="3"/>' .
        '</svg>';
}

function cozy_icon_tag( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>' .
        '<line x1="7" y1="7" x2="7.01" y2="7"/>' .
        '</svg>';
}

function cozy_icon_house( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>' .
        '<polyline points="9 22 9 12 15 12 15 22"/>' .
        '</svg>';
}

function cozy_icon_circle_dot( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<circle cx="12" cy="12" r="10"/>' .
        '<circle cx="12" cy="12" r="3"/>' .
        '</svg>';
}

function cozy_icon_download( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>' .
        '<polyline points="7 10 12 15 17 10"/>' .
        '<line x1="12" y1="15" x2="12" y2="3"/>' .
        '</svg>';
}

/* ── Category icons (front page) ────────────────────────────── */
function cozy_icon_paw( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class, '0 0 24 24', 'currentColor', 'none' ) .
        '<ellipse cx="8" cy="8" rx="2.5" ry="3"/>' .
        '<ellipse cx="16" cy="8" rx="2.5" ry="3"/>' .
        '<ellipse cx="5" cy="14" rx="2" ry="2.5"/>' .
        '<ellipse cx="19" cy="14" rx="2" ry="2.5"/>' .
        '<path d="M12 22c-3 0-5-2.5-5-5s2-4 5-4 5 1.5 5 4-2 5-5 5z"/>' .
        '</svg>';
}

function cozy_icon_hat_wizard( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<path d="M12 2L4 20h16L12 2z"/>' .
        '<path d="M8 14h8"/>' .
        '<path d="M14 8l2-1"/>' .
        '</svg>';
}

function cozy_icon_star( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>' .
        '</svg>';
}

/* ── Brand icons (social) ───────────────────────────────────── */
function cozy_icon_instagram( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class ) .
        '<rect x="2" y="2" width="20" height="20" rx="5"/>' .
        '<circle cx="12" cy="12" r="5"/>' .
        '<line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>' .
        '</svg>';
}

function cozy_icon_tiktok( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class, '0 0 24 24', 'currentColor', 'none' ) .
        '<path d="M16.6 5.82A4.28 4.28 0 0 1 15 2h-3v14.5a2.5 2.5 0 1 1-2-2.45V11a5.5 5.5 0 1 0 5 5.5V10a7.18 7.18 0 0 0 4.2 1.36V8.23a4.28 4.28 0 0 1-2.6-2.41z"/>' .
        '</svg>';
}

function cozy_icon_whatsapp( $size = '16', $extra_class = '' ) {
    return cozy_icon_svg_open( $size, $extra_class, '0 0 24 24', 'currentColor', 'none' ) .
        '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>' .
        '</svg>';
}
