<?php
/**
 * Agent readiness — makes the store easy to read and use for AI agents.
 *
 *  1. robots.txt: one clean group, Content-Signal, Sitemap.        (robots_txt)
 *  2. /sitemap.xml → 301 to Yoast's /sitemap_index.xml.
 *  3. Organization schema: phone, email, address, sameAs.           (Yoast filter)
 *  4. Home page: ItemList of the products shown, with Offer prices.
 *  5. llms.txt (written by Yoast): no BOM before the "# " title, plain-text
 *     link descriptions.
 *  6. Markdown version of pages for `Accept: text/markdown`.
 *  7. Read-only MCP server (REST: /wp-json/cozy/v1/mcp) + its Server Card at
 *     /.well-known/mcp/server-card.json.
 *  8. WebMCP tools registered in the browser (search, product, add to cart).
 *
 * Business facts used across these live in cozy_agent_business().
 */

defined( 'ABSPATH' ) || exit;

const COZY_AGENT_READY_VERSION = '2026-10-30.1';

/* ------------------------------------------------------------------ */
/*  Shared facts                                                       */
/* ------------------------------------------------------------------ */
function cozy_agent_site_url( $path = '/' ) {
    // Raw option: TranslatePress filters home_url() to add the /es/ prefix.
    return untrailingslashit( get_option( 'home' ) ) . $path;
}

function cozy_agent_business() {
    $whatsapp = preg_replace( '/[^0-9]/', '', (string) get_option( 'cozy_whatsapp_number', '34624544646' ) );
    $same_as  = array_values( array_filter( [
        get_option( 'cozy_instagram_url' ) ?: 'https://www.instagram.com/thecozyfandom/',
        get_option( 'cozy_tiktok_url' ),
    ] ) );
    return [
        'name'        => 'The Cozy Fandom',
        'description' => 'Tienda online de regalos frikis y decoración kawaii: tazas, book nooks, figuras sorpresa (blind box) y decoración oficial de Harry Potter, Snoopy, Pusheen, Pokémon, Disney y más. Envíos a la España peninsular.',
        'email'       => 'hola@thecozyfandom.com',
        'telephone'   => $whatsapp ? '+' . $whatsapp : '',
        'whatsapp'    => $whatsapp ? 'https://wa.me/' . $whatsapp : '',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Lliçà de Vall',
            'postalCode'      => '08185',
            'addressRegion'   => 'Barcelona',
            'addressCountry'  => 'ES',
        ],
        'same_as'     => $same_as,
    ];
}

/* Policy facts shared by the MCP server, WebMCP and the Markdown home page. */
function cozy_agent_store_policies() {
    $free = function_exists( 'cozy_get_free_shipping_threshold_html' ) ? wp_strip_all_tags( cozy_get_free_shipping_threshold_html() ) : '60 €';
    $shipping = sprintf( 'Envío gratis en pedidos de más de %s; 5,99 € en el resto.', html_entity_decode( $free ) );
    if ( function_exists( 'cozy_reduced_shipping' ) && ( $reduced = cozy_reduced_shipping() ) && function_exists( 'cozy_price_short' ) ) {
        $shipping = sprintf(
            'Envío gratis en pedidos de más de %1$s; %2$s en pedidos desde %3$s; 5,99 € en el resto.',
            html_entity_decode( $free ),
            html_entity_decode( wp_strip_all_tags( cozy_price_short( $reduced['cost'] ) ) ),
            html_entity_decode( wp_strip_all_tags( cozy_price_short( $reduced['min'] ) ) )
        );
    }
    $b = cozy_agent_business();
    return [
        'currency'        => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'EUR',
        'shipping_area'   => 'España peninsular',
        'shipping_cost'   => $shipping,
        'delivery_time'   => '3-5 días laborables desde la confirmación del pedido.',
        'returns'         => '14 días naturales desde la recepción, producto sin usar y en su embalaje original (blind box: caja sin abrir).',
        'payment_methods' => 'Tarjeta, Google Pay y Apple Pay.',
        'authenticity'    => 'Todos los productos son oficiales o con licencia oficial.',
        'contact'         => array_filter( [ 'email' => $b['email'], 'telephone' => $b['telephone'], 'whatsapp' => $b['whatsapp'] ] ),
        'pages'           => array_filter( [
            'shop'     => cozy_agent_site_url( '/es/tienda/' ),
            'shipping' => cozy_agent_site_url( '/es/envios-y-devoluciones/' ),
            'faq'      => function_exists( 'cozy_published_page_link' ) ? cozy_published_page_link( 'preguntas-frecuentes' ) : null,
            'contact'  => function_exists( 'cozy_published_page_link' ) ? cozy_published_page_link( 'contacto' ) : null,
            'about'    => function_exists( 'cozy_published_page_link' ) ? cozy_published_page_link( 'sobre-nosotros' ) : null,
            'terms'    => cozy_agent_site_url( '/es/terminos-y-condiciones/' ),
            'legal'    => cozy_agent_site_url( '/es/aviso-legal/' ),
        ] ),
    ];
}

/* ------------------------------------------------------------------ */
/*  One-shot tasks after a deploy                                       */
/* ------------------------------------------------------------------ */
add_action( 'init', function() {
    if ( get_option( 'cozy_agent_ready_version' ) === COZY_AGENT_READY_VERSION ) {
        return;
    }
    update_option( 'cozy_agent_ready_version', COZY_AGENT_READY_VERSION, false );
    cozy_agent_htaccess_sync();
    // Rebuild Yoast's llms.txt with the filters below (it's a static file).
    wp_schedule_single_event( time(), 'wpseo_llms_txt_population' );
    // robots.txt and pages are cached by LiteSpeed for days.
    do_action( 'litespeed_purge_all' );
}, 30 );

/* ------------------------------------------------------------------ */
/*  1. robots.txt                                                      */
/* ------------------------------------------------------------------ */
/* Replaces the WooCommerce + Yoast output (two separate "User-agent: *"
   groups) with a single group. Runs after Yoast (priority 99999). */
add_filter( 'robots_txt', function( $output, $public ) {
    if ( ! $public ) {
        return $output;
    }
    $lines = [
        '# The Cozy Fandom — regalos frikis y decoración kawaii (tienda online, España)',
        '# Resumen para agentes: ' . cozy_agent_site_url( '/llms.txt' ),
        '# Servidor MCP: ' . cozy_agent_site_url( '/.well-known/mcp/server-card.json' ),
        '# Content signals: https://contentsignals.org/',
        '',
        'User-agent: *',
        'Content-Signal: search=yes, ai-input=yes, ai-train=no',
        'Allow: /',
        'Allow: /wp-admin/admin-ajax.php',
        'Disallow: /wp-admin/',
        'Disallow: /wp-content/uploads/wc-logs/',
        'Disallow: /wp-content/uploads/woocommerce_transient_files/',
        'Disallow: /wp-content/uploads/woocommerce_uploads/',
        'Disallow: /*?add-to-cart=',
        'Disallow: /*?*add-to-cart=',
        '',
        'Sitemap: ' . cozy_agent_site_url( '/sitemap_index.xml' ),
        '',
    ];
    return implode( "\n", $lines );
}, PHP_INT_MAX, 2 );

/* ------------------------------------------------------------------ */
/*  2 & 7. Raw paths handled before TranslatePress adds /es/            */
/* ------------------------------------------------------------------ */
add_action( 'after_setup_theme', function() {
    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';

    if ( '/sitemap.xml' === $path ) {
        wp_redirect( cozy_agent_site_url( '/sitemap_index.xml' ), 301, 'The Cozy Fandom' );
        exit;
    }

    if ( in_array( $path, [ '/.well-known/mcp/server-card.json', '/.well-known/mcp.json' ], true ) ) {
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Cache-Control: public, max-age=3600' );
        echo wp_json_encode( cozy_agent_server_card(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        exit;
    }
}, 0 );

/* ------------------------------------------------------------------ */
/*  3. Organization schema (Yoast)                                      */
/* ------------------------------------------------------------------ */
add_filter( 'wpseo_schema_organization', function( $data ) {
    if ( ! is_array( $data ) ) {
        return $data;
    }
    $b = cozy_agent_business();
    $data['description'] = $b['description'];
    $data['email']       = $b['email'];
    if ( $b['telephone'] ) {
        $data['telephone'] = $b['telephone'];
    }
    $data['address'] = $b['address'];
    $data['sameAs']  = array_values( array_unique( array_merge( (array) ( $data['sameAs'] ?? [] ), $b['same_as'] ) ) );
    $data['contactPoint'] = [
        '@type'             => 'ContactPoint',
        'contactType'       => 'customer service',
        'email'             => $b['email'],
        'telephone'         => $b['telephone'],
        'availableLanguage' => [ 'es', 'en' ],
        'areaServed'        => 'ES',
    ];
    $data['areaServed'] = 'ES';
    return $data;
} );

/* ------------------------------------------------------------------ */
/*  4. Home page: products with prices                                  */
/* ------------------------------------------------------------------ */
function cozy_agent_home_products() {
    $ids = array_merge(
        function_exists( 'cozy_get_home_new_product_ids' ) ? (array) cozy_get_home_new_product_ids() : [],
        function_exists( 'cozy_get_home_top_product_ids' ) ? (array) cozy_get_home_top_product_ids() : []
    );
    $products = [];
    foreach ( array_unique( array_map( 'absint', $ids ) ) as $id ) {
        $p = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
        if ( $p && $p->is_visible() && '' !== $p->get_price() ) {
            $products[] = $p;
        }
    }
    return $products;
}

function cozy_agent_offer( WC_Product $p ) {
    return [
        '@type'         => 'Offer',
        'price'         => wc_format_decimal( wc_get_price_to_display( $p ), wc_get_price_decimals() ),
        'priceCurrency' => get_woocommerce_currency(),
        'availability'  => $p->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => get_permalink( $p->get_id() ),
        'seller'        => [ '@id' => cozy_agent_site_url( '/#organization' ) ],
    ];
}

add_action( 'wp_head', function() {
    if ( ! is_front_page() || ! function_exists( 'wc_get_product' ) ) {
        return;
    }
    $items = [];
    foreach ( cozy_agent_home_products() as $i => $p ) {
        $image = wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_single' );
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => array_filter( [
                '@type'  => 'Product',
                'name'   => $p->get_name(),
                'url'    => get_permalink( $p->get_id() ),
                'image'  => $image ?: null,
                'sku'    => $p->get_sku() ?: null,
                'offers' => cozy_agent_offer( $p ),
            ] ),
        ];
    }
    if ( ! $items ) {
        return;
    }
    $list = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Novedades y más vendidos — The Cozy Fandom',
        'url'             => cozy_agent_site_url( '/es/tienda/' ),
        'numberOfItems'   => count( $items ),
        'itemListElement' => $items,
    ];
    echo "\n<script type=\"application/ld+json\" class=\"cozy-home-products\">" . wp_json_encode( $list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 30 );

/* ------------------------------------------------------------------ */
/*  5. llms.txt (Yoast)                                                 */
/* ------------------------------------------------------------------ */
// A UTF-8 BOM before "# Title" makes parsers miss the top-level heading.
add_filter( 'wpseo_llmstxt_encoding_prefix', '__return_empty_string' );

// Product short descriptions contain raw HTML; keep one plain sentence-ish line.
add_filter( 'wpseo_llmstxt_link_description', function( $description ) {
    $text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( (string) $description ), ENT_QUOTES, 'UTF-8' ) ) );
    $text = trim( preg_replace( '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', $text ) );
    return wp_html_excerpt( $text, 220, '…' );
} );

/* ------------------------------------------------------------------ */
/*  6. Markdown for agents                                              */
/* ------------------------------------------------------------------ */
/* True when the client ranks text/markdown at least as high as text/html. */
function cozy_agent_wants_markdown() {
    $accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? strtolower( (string) $_SERVER['HTTP_ACCEPT'] ) : '';
    if ( false === strpos( $accept, 'text/markdown' ) ) {
        return false;
    }
    $q = [ 'md' => 0.0, 'html' => 0.0 ];
    foreach ( explode( ',', $accept ) as $part ) {
        $bits  = array_map( 'trim', explode( ';', $part ) );
        $type  = array_shift( $bits );
        $value = 1.0;
        foreach ( $bits as $b ) {
            if ( 0 === strpos( $b, 'q=' ) ) {
                $value = (float) substr( $b, 2 );
            }
        }
        if ( 'text/markdown' === $type ) {
            $q['md'] = max( $q['md'], $value );
        } elseif ( 'text/html' === $type ) {
            $q['html'] = max( $q['html'], $value );
        }
    }
    return $q['md'] > 0 && $q['md'] >= $q['html'];
}

/* Small HTML → Markdown conversion for post/page content. */
function cozy_agent_html_to_md( $html ) {
    $html = preg_replace( '#<(script|style|noscript|svg|form|iframe)\b[^>]*>.*?</\1>#is', '', (string) $html );
    $html = preg_replace_callback( '#<h([1-6])\b[^>]*>(.*?)</h\1>#is', function( $m ) {
        return "\n\n" . str_repeat( '#', max( 2, (int) $m[1] ) ) . ' ' . trim( wp_strip_all_tags( $m[2] ) ) . "\n\n";
    }, $html );
    $html = preg_replace_callback( '#<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', function( $m ) {
        $text = trim( wp_strip_all_tags( $m[2] ) );
        return '' === $text ? '' : '[' . $text . '](' . $m[1] . ')';
    }, $html );
    $html = preg_replace( '#<(strong|b)\b[^>]*>(.*?)</\1>#is', '**$2**', $html );
    $html = preg_replace( '#<(em|i)\b[^>]*>(.*?)</\1>#is', '*$2*', $html );
    $html = preg_replace( '#<summary\b[^>]*>(.*?)</summary>#is', "\n\n**$1**\n\n", $html );
    $html = preg_replace( '#<li\b[^>]*>#i', "\n- ", $html );
    $html = preg_replace( '#<br\s*/?>#i', "\n", $html );
    $html = preg_replace( '#</(p|div|ul|ol|details|section|table|tr|blockquote)>#i', "\n\n", $html );
    $text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );
    $text = preg_replace( "/[ \t]+/", ' ', $text );
    $text = preg_replace( "/ *\n */", "\n", $text );
    return trim( preg_replace( "/\n{3,}/", "\n\n", $text ) );
}

function cozy_agent_product_md_line( WC_Product $p ) {
    $price = html_entity_decode( wp_strip_all_tags( wc_price( wc_get_price_to_display( $p ) ) ), ENT_QUOTES, 'UTF-8' );
    return sprintf( '- [%s](%s) — %s%s', $p->get_name(), get_permalink( $p->get_id() ), $price, $p->is_in_stock() ? '' : ' (agotado)' );
}

function cozy_agent_markdown_for_request() {
    $b      = cozy_agent_business();
    $pol    = cozy_agent_store_policies();
    $footer = "\n\n---\n\n"
        . "**The Cozy Fandom** · " . $b['email'] . ( $b['telephone'] ? ' · ' . $b['telephone'] . ' (WhatsApp)' : '' ) . "\n"
        . "Envíos: " . $pol['shipping_cost'] . ' ' . $pol['delivery_time'] . "\n"
        . "Más información para agentes: " . cozy_agent_site_url( '/llms.txt' ) . "\n";

    if ( is_front_page() ) {
        $md  = "# The Cozy Fandom — regalos frikis y decoración kawaii\n\n";
        $md .= '> ' . $b['description'] . "\n\n";
        $md .= "## Productos destacados (precios con IVA, " . $pol['currency'] . ")\n\n";
        foreach ( cozy_agent_home_products() as $p ) {
            $md .= cozy_agent_product_md_line( $p ) . "\n";
        }
        $md .= "\nCatálogo completo: " . $pol['pages']['shop'] . "\n\n";
        $cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'exclude' => [ (int) get_option( 'default_product_cat' ) ] ] );
        if ( $cats && ! is_wp_error( $cats ) ) {
            $md .= "## Categorías\n\n";
            foreach ( $cats as $c ) {
                $md .= sprintf( "- [%s](%s) (%d productos)\n", $c->name, get_term_link( $c ), $c->count );
            }
            $md .= "\n";
        }
        $md .= "## Información de compra\n\n";
        $md .= '- Envíos: ' . $pol['shipping_area'] . '. ' . $pol['shipping_cost'] . "\n";
        $md .= '- Plazo de entrega: ' . $pol['delivery_time'] . "\n";
        $md .= '- Devoluciones: ' . $pol['returns'] . "\n";
        $md .= '- Pago: ' . $pol['payment_methods'] . "\n";
        $md .= '- Autenticidad: ' . $pol['authenticity'] . "\n\n";
        $md .= "## Páginas\n\n";
        $labels = [ 'about' => 'Sobre nosotros', 'contact' => 'Contacto', 'faq' => 'Preguntas frecuentes', 'shipping' => 'Envíos y devoluciones', 'terms' => 'Términos y condiciones', 'legal' => 'Aviso legal' ];
        foreach ( $labels as $key => $label ) {
            if ( ! empty( $pol['pages'][ $key ] ) ) {
                $md .= "- [{$label}](" . $pol['pages'][ $key ] . ")\n";
            }
        }
        return $md . $footer;
    }

    if ( function_exists( 'is_product' ) && is_product() ) {
        $p = wc_get_product( get_queried_object_id() );
        if ( ! $p ) {
            return null;
        }
        $price = html_entity_decode( wp_strip_all_tags( wc_price( wc_get_price_to_display( $p ) ) ), ENT_QUOTES, 'UTF-8' );
        $cats  = wp_get_post_terms( $p->get_id(), 'product_cat', [ 'fields' => 'names' ] );
        $md    = '# ' . $p->get_name() . "\n\n";
        $md   .= "- Precio: {$price} (IVA incluido)\n";
        $md   .= '- Disponibilidad: ' . ( $p->is_in_stock() ? 'en stock' : 'agotado' ) . "\n";
        if ( $p->get_sku() ) {
            $md .= '- SKU: ' . $p->get_sku() . "\n";
        }
        if ( $cats && ! is_wp_error( $cats ) ) {
            $md .= '- Categorías: ' . implode( ', ', $cats ) . "\n";
        }
        $md .= '- Comprar: ' . get_permalink( $p->get_id() ) . "\n\n";
        $short = cozy_agent_html_to_md( $p->get_short_description() );
        $long  = cozy_agent_html_to_md( apply_filters( 'the_content', $p->get_description() ) );
        $md   .= trim( $short . "\n\n" . $long ) . "\n";
        return $md . $footer;
    }

    if ( is_singular() ) {
        $post = get_queried_object();
        if ( ! $post instanceof WP_Post || post_password_required( $post ) ) {
            return null;
        }
        $md = '# ' . get_the_title( $post ) . "\n\n";
        if ( 'post' === $post->post_type ) {
            $md .= '*' . get_the_date( 'j F Y', $post ) . "*\n\n";
        }
        return $md . cozy_agent_html_to_md( apply_filters( 'the_content', $post->post_content ) ) . "\n" . $footer;
    }

    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
        global $wp_query;
        $title = is_shop() ? 'Tienda' : single_term_title( '', false );
        $md    = '# ' . $title . " — The Cozy Fandom\n\n";
        if ( is_product_taxonomy() && term_description() ) {
            $md .= cozy_agent_html_to_md( term_description() ) . "\n\n";
        }
        foreach ( (array) $wp_query->posts as $post ) {
            $p = wc_get_product( $post );
            if ( $p ) {
                $md .= cozy_agent_product_md_line( $p ) . "\n";
            }
        }
        $next = get_next_posts_page_link();
        if ( $next && $wp_query->max_num_pages > max( 1, get_query_var( 'paged' ) ) ) {
            $md .= "\nSiguiente página: {$next}\n";
        }
        return $md . $footer;
    }

    return null;
}

add_action( 'template_redirect', function() {
    if ( is_admin() || is_feed() || is_robots() || wp_doing_ajax() || is_404() ) {
        return;
    }
    if ( ! cozy_agent_wants_markdown() ) {
        // Lets caches that honour Vary keep HTML and Markdown apart.
        header( 'Vary: Accept', false );
        return;
    }
    $md = cozy_agent_markdown_for_request();
    if ( null === $md ) {
        return;
    }
    do_action( 'litespeed_control_set_nocache', 'markdown for agents' );
    status_header( 200 );
    header( 'Content-Type: text/markdown; charset=utf-8' );
    header( 'Vary: Accept' );
    header( 'Cache-Control: private, no-store' );
    header( 'X-LiteSpeed-Cache-Control: no-cache' );
    header( 'X-Markdown-Tokens: ' . (int) ceil( mb_strlen( $md ) / 4 ) );
    header( 'Link: <' . esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . strtok( $_SERVER['REQUEST_URI'], '?' ) ) . '>; rel="canonical"; type="text/html"', false );
    echo $md; // phpcs:ignore WordPress.Security.EscapeOutput -- plain-text Markdown response.
    exit;
}, 20 );

/* LiteSpeed serves cached HTML before PHP runs, so Markdown requests must skip
   the page cache. Prepends a marked block to .htaccess (above the LSCache and
   WordPress blocks, whose [L] rules would stop it from being reached). */
function cozy_agent_htaccess_sync() {
    $file = ABSPATH . '.htaccess';
    if ( ! is_file( $file ) || ! is_readable( $file ) || ! is_writable( $file ) ) {
        update_option( 'cozy_agent_htaccess_status', 'not writable', false );
        return;
    }
    $current = (string) file_get_contents( $file );
    $block   = "# BEGIN CozyAgentMarkdown\n"
        . "<IfModule mod_rewrite.c>\n"
        . "RewriteEngine On\n"
        . "RewriteCond %{HTTP:Accept} text/markdown [NC]\n"
        . "RewriteRule .* - [E=Cache-Control:no-cache]\n"
        . "</IfModule>\n"
        . "# END CozyAgentMarkdown\n";
    $without = preg_replace( "/# BEGIN CozyAgentMarkdown.*?# END CozyAgentMarkdown\n?/s", '', $current );
    if ( false !== strpos( $current, $block ) && 0 === strpos( ltrim( $current ), '# BEGIN CozyAgentMarkdown' ) ) {
        update_option( 'cozy_agent_htaccess_status', 'ok (unchanged)', false );
        return;
    }
    update_option( 'cozy_agent_htaccess_backup', $current, false );
    $ok = false !== file_put_contents( $file, $block . "\n" . ltrim( $without ), LOCK_EX );
    update_option( 'cozy_agent_htaccess_status', $ok ? 'ok (written ' . gmdate( 'c' ) . ')' : 'write failed', false );
}

/* ------------------------------------------------------------------ */
/*  7. MCP server (read-only, Streamable HTTP, JSON responses)          */
/* ------------------------------------------------------------------ */
const COZY_MCP_PROTOCOL = '2025-06-18';

function cozy_agent_mcp_endpoint() {
    return cozy_agent_site_url( '/wp-json/cozy/v1/mcp' );
}

function cozy_agent_server_card() {
    $b = cozy_agent_business();
    return [
        '$schema'          => 'https://static.modelcontextprotocol.io/schemas/mcp-server-card/v1.json',
        'version'          => '1.0',
        'protocolVersion'  => COZY_MCP_PROTOCOL,
        'serverInfo'       => [
            'name'    => 'thecozyfandom-store',
            'title'   => 'The Cozy Fandom — tienda',
            'version' => '1.0.0',
        ],
        'description'      => 'Catálogo de The Cozy Fandom (regalos frikis y decoración kawaii, España): buscar productos, ver precio y stock, categorías y políticas de envío y devolución. Solo lectura.',
        'iconUrl'          => cozy_agent_site_url( '/wp-content/uploads/2026/06/cropped-cropped-TCF_Logo.png' ),
        'documentationUrl' => cozy_agent_site_url( '/llms.txt' ),
        'websiteUrl'       => cozy_agent_site_url( '/' ),
        'transport'        => [
            'type'     => 'streamable-http',
            'endpoint' => cozy_agent_mcp_endpoint(),
        ],
        'remotes'          => [
            [ 'type' => 'streamable-http', 'url' => cozy_agent_mcp_endpoint() ],
        ],
        'capabilities'     => [
            'tools'     => [ 'listChanged' => false ],
            'resources' => new stdClass(),
            'prompts'   => new stdClass(),
        ],
        'authentication'   => [ 'required' => false, 'schemes' => [] ],
        'contact'          => [ 'email' => $b['email'] ],
        'tools'            => array_map( function( $t ) {
            return [ 'name' => $t['name'], 'description' => $t['description'] ];
        }, cozy_agent_mcp_tools() ),
    ];
}

function cozy_agent_mcp_tools() {
    return [
        [
            'name'        => 'search_products',
            'title'       => 'Buscar productos',
            'description' => 'Busca productos en el catálogo por texto (p. ej. "taza snoopy", "book nook") y/o categoría. Devuelve nombre, precio en EUR, stock y URL.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'query'     => [ 'type' => 'string', 'description' => 'Texto a buscar, en español.' ],
                    'category'  => [ 'type' => 'string', 'description' => 'Slug de categoría (ver list_categories).' ],
                    'max_price' => [ 'type' => 'number', 'description' => 'Precio máximo en EUR.' ],
                    'in_stock'  => [ 'type' => 'boolean', 'description' => 'Solo productos disponibles.' ],
                    'limit'     => [ 'type' => 'integer', 'minimum' => 1, 'maximum' => 24, 'default' => 10 ],
                ],
            ],
            'annotations' => [ 'readOnlyHint' => true, 'openWorldHint' => false ],
        ],
        [
            'name'        => 'get_product',
            'title'       => 'Ver producto',
            'description' => 'Detalle de un producto por id, slug o URL: precio, stock, descripción, categorías e imagen.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'id'   => [ 'type' => 'integer' ],
                    'slug' => [ 'type' => 'string', 'description' => 'Slug o URL completa del producto.' ],
                ],
            ],
            'annotations' => [ 'readOnlyHint' => true, 'openWorldHint' => false ],
        ],
        [
            'name'        => 'list_categories',
            'title'       => 'Categorías',
            'description' => 'Lista las categorías de producto con su slug, URL y número de productos.',
            'inputSchema' => [ 'type' => 'object', 'properties' => new stdClass() ],
            'annotations' => [ 'readOnlyHint' => true, 'openWorldHint' => false ],
        ],
        [
            'name'        => 'get_store_policies',
            'title'       => 'Envíos, devoluciones y contacto',
            'description' => 'Costes y zona de envío, plazo de entrega, devoluciones, métodos de pago y datos de contacto de la tienda.',
            'inputSchema' => [ 'type' => 'object', 'properties' => new stdClass() ],
            'annotations' => [ 'readOnlyHint' => true, 'openWorldHint' => false ],
        ],
    ];
}

function cozy_agent_product_data( WC_Product $p, $full = false ) {
    $data = [
        'id'        => $p->get_id(),
        'name'      => $p->get_name(),
        'url'       => get_permalink( $p->get_id() ),
        'price'     => (float) wc_get_price_to_display( $p ),
        'currency'  => get_woocommerce_currency(),
        'in_stock'  => $p->is_in_stock(),
        'on_sale'   => $p->is_on_sale(),
        'image'     => wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_single' ) ?: null,
        'categories'=> wp_get_post_terms( $p->get_id(), 'product_cat', [ 'fields' => 'slugs' ] ),
    ];
    if ( $full ) {
        $data['sku']         = $p->get_sku();
        $data['summary']     = cozy_agent_html_to_md( $p->get_short_description() );
        $data['description'] = wp_html_excerpt( cozy_agent_html_to_md( $p->get_description() ), 2000, '…' );
    }
    return $data;
}

function cozy_agent_visible_product_query( array $args ) {
    $args += [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
        'tax_query'      => [],
    ];
    $args['tax_query'][] = [
        'taxonomy' => 'product_visibility',
        'field'    => 'name',
        'terms'    => [ 'exclude-from-search' ],
        'operator' => 'NOT IN',
    ];
    return new WP_Query( $args );
}

function cozy_agent_mcp_call_tool( $name, array $args ) {
    switch ( $name ) {
        case 'search_products':
            $limit = max( 1, min( 24, (int) ( $args['limit'] ?? 10 ) ) );
            $qargs = [ 'posts_per_page' => $limit * 2 ];
            if ( ! empty( $args['query'] ) ) {
                $qargs['s'] = sanitize_text_field( $args['query'] );
            }
            if ( ! empty( $args['category'] ) ) {
                $qargs['tax_query'] = [ [ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => sanitize_title( $args['category'] ) ] ];
            }
            $out = [];
            foreach ( cozy_agent_visible_product_query( $qargs )->posts as $post ) {
                $p = wc_get_product( $post );
                if ( ! $p || ! $p->is_visible() ) {
                    continue;
                }
                if ( ! empty( $args['in_stock'] ) && ! $p->is_in_stock() ) {
                    continue;
                }
                if ( isset( $args['max_price'] ) && is_numeric( $args['max_price'] ) && (float) wc_get_price_to_display( $p ) > (float) $args['max_price'] ) {
                    continue;
                }
                $out[] = cozy_agent_product_data( $p );
                if ( count( $out ) >= $limit ) {
                    break;
                }
            }
            return [ 'products' => $out, 'count' => count( $out ), 'shop_url' => cozy_agent_site_url( '/es/tienda/' ) ];

        case 'get_product':
            $p = null;
            if ( ! empty( $args['id'] ) ) {
                $p = wc_get_product( absint( $args['id'] ) );
            } elseif ( ! empty( $args['slug'] ) ) {
                $slug = sanitize_title( basename( untrailingslashit( (string) parse_url( $args['slug'], PHP_URL_PATH ) ?: $args['slug'] ) ) );
                $post = get_page_by_path( $slug, OBJECT, 'product' );
                $p    = $post ? wc_get_product( $post ) : null;
            }
            if ( ! $p || 'publish' !== $p->get_status() || ! $p->is_visible() ) {
                return new WP_Error( 'not_found', 'Producto no encontrado.' );
            }
            return cozy_agent_product_data( $p, true );

        case 'list_categories':
            $terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => [ (int) get_option( 'default_product_cat' ) ] ] );
            $out   = [];
            foreach ( is_wp_error( $terms ) ? [] : $terms as $t ) {
                $out[] = [ 'slug' => $t->slug, 'name' => $t->name, 'count' => $t->count, 'url' => get_term_link( $t ) ];
            }
            return [ 'categories' => $out ];

        case 'get_store_policies':
            return cozy_agent_store_policies();
    }
    return new WP_Error( 'unknown_tool', 'Herramienta desconocida: ' . $name );
}

function cozy_agent_mcp_handle( array $msg ) {
    $id     = $msg['id'] ?? null;
    $method = (string) ( $msg['method'] ?? '' );
    $params = isset( $msg['params'] ) && is_array( $msg['params'] ) ? $msg['params'] : [];
    $ok     = function( $result ) use ( $id ) {
        return [ 'jsonrpc' => '2.0', 'id' => $id, 'result' => $result ];
    };
    $err    = function( $code, $message ) use ( $id ) {
        return [ 'jsonrpc' => '2.0', 'id' => $id, 'error' => [ 'code' => $code, 'message' => $message ] ];
    };

    if ( null === $id ) {
        return null; // Notification (e.g. notifications/initialized): no response.
    }
    switch ( $method ) {
        case 'initialize':
            return $ok( [
                'protocolVersion' => is_string( $params['protocolVersion'] ?? null ) ? $params['protocolVersion'] : COZY_MCP_PROTOCOL,
                'capabilities'    => [ 'tools' => [ 'listChanged' => false ] ],
                'serverInfo'      => [ 'name' => 'thecozyfandom-store', 'title' => 'The Cozy Fandom — tienda', 'version' => '1.0.0' ],
                'instructions'    => 'Catálogo de una tienda online española de regalos frikis. Precios en EUR con IVA. Para comprar, enlaza al usuario a la URL del producto.',
            ] );
        case 'ping':
            return $ok( new stdClass() );
        case 'tools/list':
            return $ok( [ 'tools' => cozy_agent_mcp_tools() ] );
        case 'resources/list':
            return $ok( [ 'resources' => [] ] );
        case 'prompts/list':
            return $ok( [ 'prompts' => [] ] );
        case 'tools/call':
            $name = (string) ( $params['name'] ?? '' );
            if ( ! in_array( $name, wp_list_pluck( cozy_agent_mcp_tools(), 'name' ), true ) ) {
                return $err( -32602, 'Unknown tool: ' . $name );
            }
            $result = cozy_agent_mcp_call_tool( $name, isset( $params['arguments'] ) && is_array( $params['arguments'] ) ? $params['arguments'] : [] );
            if ( is_wp_error( $result ) ) {
                return $ok( [ 'isError' => true, 'content' => [ [ 'type' => 'text', 'text' => $result->get_error_message() ] ] ] );
            }
            return $ok( [
                'content'           => [ [ 'type' => 'text', 'text' => wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ] ],
                'structuredContent' => $result,
            ] );
    }
    return $err( -32601, 'Method not found: ' . $method );
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'cozy/v1', '/mcp', [
        [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => function( WP_REST_Request $request ) {
                $body = json_decode( $request->get_body(), true );
                if ( ! is_array( $body ) ) {
                    return new WP_REST_Response( [ 'jsonrpc' => '2.0', 'id' => null, 'error' => [ 'code' => -32700, 'message' => 'Parse error' ] ], 400 );
                }
                $is_batch = array_is_list( $body );
                $replies  = [];
                foreach ( $is_batch ? $body : [ $body ] as $msg ) {
                    $reply = is_array( $msg ) ? cozy_agent_mcp_handle( $msg ) : null;
                    if ( null !== $reply ) {
                        $replies[] = $reply;
                    }
                }
                if ( ! $replies ) {
                    return new WP_REST_Response( null, 202 );
                }
                $response = new WP_REST_Response( $is_batch ? $replies : $replies[0], 200 );
                $response->header( 'Cache-Control', 'no-store' );
                return $response;
            },
        ],
        [
            'methods'             => 'GET',
            'permission_callback' => '__return_true',
            'callback'            => function() {
                // No server-initiated stream; GET describes the endpoint instead.
                $response = new WP_REST_Response( cozy_agent_server_card(), 405 );
                $response->header( 'Allow', 'POST' );
                return $response;
            },
        ],
    ] );
} );

/* ------------------------------------------------------------------ */
/*  8. WebMCP (in-browser agent tools)                                  */
/* ------------------------------------------------------------------ */
add_action( 'wp_footer', function() {
    if ( is_admin() || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        return;
    }
    $config = [
        'storeApi' => cozy_agent_site_url( '/wp-json/wc/store/v1/' ),
        'mcp'      => cozy_agent_mcp_endpoint(),
        'ajaxAdd'  => cozy_agent_site_url( '/?wc-ajax=add_to_cart' ),
        'cartUrl'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : cozy_agent_site_url( '/es/cart/' ),
    ];
    ?>
<script id="cozy-webmcp">
(function () {
    var mc = document.modelContext || navigator.modelContext;
    if (!mc || typeof mc.registerTool !== 'function') return;
    var cfg = <?php echo wp_json_encode( $config, JSON_UNESCAPED_SLASHES ); ?>;

    function text(obj) { return { content: [{ type: 'text', text: JSON.stringify(obj) }] }; }
    function mcpCall(name, args) {
        return fetch(cfg.mcp, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ jsonrpc: '2.0', id: 1, method: 'tools/call', params: { name: name, arguments: args || {} } })
        }).then(function (r) { return r.json(); }).then(function (j) {
            return j.result || text({ error: (j.error && j.error.message) || 'Error' });
        });
    }

    var tools = [
        {
            name: 'search_products',
            description: 'Busca productos de The Cozy Fandom (regalos frikis, tazas, book nooks, blind box…) por texto o categoría. Devuelve nombre, precio en EUR, stock y URL.',
            inputSchema: { type: 'object', properties: {
                query: { type: 'string', description: 'Texto a buscar, en español.' },
                category: { type: 'string', description: 'Slug de categoría.' },
                max_price: { type: 'number', description: 'Precio máximo en EUR.' },
                in_stock: { type: 'boolean', description: 'Solo productos disponibles.' },
                limit: { type: 'integer', minimum: 1, maximum: 24 }
            } },
            annotations: { readOnlyHint: true },
            execute: function (args) { return mcpCall('search_products', args); }
        },
        {
            name: 'get_product',
            description: 'Detalle de un producto (precio, stock, descripción) por id o slug/URL.',
            inputSchema: { type: 'object', properties: { id: { type: 'integer' }, slug: { type: 'string' } } },
            annotations: { readOnlyHint: true },
            execute: function (args) { return mcpCall('get_product', args); }
        },
        {
            name: 'get_store_policies',
            description: 'Envíos (coste, zona, plazo), devoluciones, métodos de pago y contacto de la tienda.',
            inputSchema: { type: 'object', properties: {} },
            annotations: { readOnlyHint: true },
            execute: function () { return mcpCall('get_store_policies', {}); }
        },
        {
            name: 'add_to_cart',
            description: 'Añade un producto simple al carrito del usuario en esta pestaña. Devuelve la URL del carrito para que el usuario revise y pague.',
            inputSchema: { type: 'object', required: ['product_id'], properties: {
                product_id: { type: 'integer', description: 'id devuelto por search_products.' },
                quantity: { type: 'integer', minimum: 1, maximum: 10, default: 1 }
            } },
            execute: function (args) {
                var body = new URLSearchParams();
                body.set('product_id', String(args.product_id));
                body.set('quantity', String(args.quantity || 1));
                return fetch(cfg.ajaxAdd, { method: 'POST', credentials: 'same-origin', body: body })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (!j || j.error) return text({ added: false, message: 'No se pudo añadir (¿producto variable o agotado?). Abre la página del producto.', product_url: j && j.product_url });
                        if (window.jQuery) window.jQuery(document.body).trigger('added_to_cart', [j.fragments, j.cart_hash]);
                        return text({ added: true, cart_url: cfg.cartUrl });
                    });
            }
        }
    ];

    tools.forEach(function (t) {
        try {
            var r = mc.registerTool(t);
            if (r && typeof r.catch === 'function') r.catch(function () {});
        } catch (e) {}
    });
})();
</script>
    <?php
}, 50 );
