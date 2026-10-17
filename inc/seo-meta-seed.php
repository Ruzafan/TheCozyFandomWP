<?php
/**
 * One-shot Yoast SEO title/meta-description seed for the home, shop,
 * product categories and licences (product_brand).
 *
 * Runs once, the first time an admin loads wp-admin after deploy, and records
 * a version flag so it never runs again. It only fills fields that are still
 * empty — anything edited by hand in Yoast is left alone. To re-run after
 * changing the texts below, bump COZY_SEO_SEED_VERSION.
 *
 * Also replaces Yoast's default "%%term_title%% Archives …" taxonomy title
 * template, which was leaking the English word "Archives" into every
 * category/brand/tag title in Google.
 */

const COZY_SEO_SEED_VERSION = '2026-10-17';

function cozy_seo_seed_data() {
    $site = ' %%sep%% %%sitename%%';

    return [
        'page' => [
            'home' => [ 'Regalos frikis y decoración kawaii' . $site, 'Tazas, book nooks, figuras sorpresa y decoración de Harry Potter, Snoopy, Pusheen, Pokémon y Disney. Envío gratis desde 60 € en península.' ],
            'shop' => [ 'Tienda de regalos frikis y kawaii' . $site, 'Tazas, book nooks, blind box, mochilas Loungefly y decoración de tus fandoms favoritos. Envío en 3-5 días y gratis desde 60 € en península.' ],
        ],
        'product_cat' => [
            'hogar-cocina'        => [ 'Menaje friki: tazas, boles y botellas' . $site, 'Tazas, vasos térmicos, boles de ramen, platos y palillos de Harry Potter, Snoopy, Pusheen, Stitch y anime. Regalos útiles desde 7,99 €.' ],
            'tazas-y-vasos'       => [ 'Tazas frikis y vasos térmicos' . $site, 'Tazas de cerámica y vasos térmicos de Hello Kitty, Pusheen, Snoopy, Super Mario y Disney. El regalo friki que se usa cada día, desde 11,95 €.' ],
            'boles'               => [ 'Boles de ramen frikis con palillos' . $site, 'Boles de ramen de Pusheen, Stitch, Grogu, Demon Slayer y Pokémon, muchos con palillos incluidos. Tu ramen con mucho estilo, desde 15,99 €.' ],
            'botellas'            => [ 'Botellas térmicas y vasos frikis' . $site, 'Botellas térmicas y vasos de 1,2 L de Stitch, Harry Potter, Pusheen y Spy x Family. Tus bebidas frías o calientes durante horas, desde 12,95 €.' ],
            'platos'              => [ 'Platos de Snoopy y Peanuts' . $site, 'Platos y bandejas de cerámica de Snoopy y Woodstock con forma de flor y estrella. Para la mesa o como vaciabolsillos, desde 12,95 €.' ],
            'palillos'            => [ 'Palillos de anime: Totoro, Chihiro y más' . $site, 'Palillos japoneses de Totoro, Sin Cara de El Viaje de Chihiro, Demon Slayer y Kuromi. Un detalle friki para regalar desde 7,99 €.' ],
            'decoracion'          => [ 'Decoración friki y cozy para tu hogar' . $site, 'Book nooks DIY, puzzles, sujetalibros y alfombrillas de escritorio de Harry Potter, Pokémon, Snoopy y anime. Crea tu rincón friki más acogedor.' ],
            'book-nooks'          => [ 'Book Nooks DIY: kits para montar' . $site, 'Kits de book nook para montar y decorar tu estantería: Kioto, sakura, heladería vintage, ramen japonés y más. El regalo ideal para lectores.' ],
            'puzzles'             => [ 'Puzzles de Pokémon, Harry Potter y Japón' . $site, 'Puzzles de 500 y 1000 piezas de Pokémon, Harry Potter, Pesadilla antes de Navidad y paisajes japoneses. Para tardes de manta, desde 11,95 €.' ],
            'sujetalibros'        => [ 'Sujetalibros frikis para tu estantería' . $site, 'Sujetalibros de Harry Potter, One Piece, Toy Story y Los Simpson para ordenar tu colección con mucho estilo. El regalo ideal para lectores.' ],
            'alfombrillas'        => [ 'Alfombrillas de escritorio XL frikis' . $site, 'Alfombrillas de escritorio y gaming XL de Jujutsu Kaisen, Kuromi, Snoopy, Pusheen, Harry Potter y La Gran Ola de Kanagawa. Desde 18,95 €.' ],
            'accesorios'          => [ 'Accesorios frikis: mochilas y bolsas' . $site, 'Mochilas Loungefly, tote bags, bolsas térmicas y neceseres de Harry Potter, Pusheen, Snoopy, Stitch y Pokémon. Accesorios frikis desde 5,99 €.' ],
            'mochilas'            => [ 'Mochilas Loungefly en España' . $site, 'Mochilas Loungefly de Harry Potter (Hogwarts y Hedwig), Lilo y Stitch, Star Wars y Pokémon. Envío en 3-5 días y gratis en península.' ],
            'bolsas'              => [ 'Tote bags y bolsas térmicas frikis' . $site, 'Tote bags y bolsas térmicas de Pusheen, Snoopy y panda para llevar la comida o la compra con estilo. Regalos útiles desde 5,99 €.' ],
            'neceseres'           => [ 'Neceseres de Harry Potter' . $site, 'Neceseres de terciopelo de Harry Potter con diseños de Pociones y Encantamientos. Para maquillaje, viaje o para regalar a fans de Hogwarts.' ],
            'cosmetica-y-belleza' => [ 'Cosmética kawaii: Pusheen, Kuromi, Stitch' . $site, 'Mascarillas faciales, cepillos, limas y toallas de viaje kawaii de Pusheen, Kuromi y Stitch. Detalles monísimos para regalar desde 1,49 €.' ],
            'figuras-sorpresa'    => [ 'Figuras sorpresa y blind box Pokémon' . $site, 'Blind box de Pokémon, Winnie the Pooh, Shin Chan, Star Wars y más. Abre la caja y descubre qué figura te toca. Coleccionables desde 9,95 €.' ],
        ],
        'product_brand' => [
            'pusheen'      => [ 'Regalos de Pusheen: tazas, bolsas y más' . $site, 'Tazas, botellas, bolsas, alfombrillas y accesorios de Pusheen the Cat. Más de 20 regalos para fans del gato más adorable, desde 1,49 €.' ],
            'disney'       => [ 'Regalos Disney: Stitch, Pooh y más' . $site, 'Vasos térmicos de Stitch, tazas de Los Aristogatos, blind box de Winnie the Pooh, sujetalibros de Toy Story y más regalos Disney desde 9,95 €.' ],
            'harry-potter' => [ 'Regalos de Harry Potter originales' . $site, 'Tazas de Hogwarts, mochilas Loungefly, neceseres, puzzles y sujetalibros del Andén 9 ¾. Regalos de Harry Potter para fans de todas las casas.' ],
            'snoopy'       => [ 'Regalos de Snoopy y Peanuts' . $site, 'Tazas, platos, tarros, bolsas y alfombrillas de Snoopy y Woodstock. Regalos de Peanuts para un hogar acogedor, desde 5,95 €.' ],
            'pokemon'      => [ 'Regalos Pokémon: blind box y puzzles' . $site, 'Blind box de Pokémon, puzzles de 500 y 1000 piezas, boles de Pikachu y Eevee y mochilas Loungefly. Regalos Pokémon desde 15,99 €.' ],
            'anime'        => [ 'Regalos de anime: Ghibli y Demon Slayer' . $site, 'Palillos de Totoro y Chihiro, vasos de Demon Slayer, alfombrillas de Jujutsu Kaisen, blind box de Shin Chan y sujetalibros de One Piece.' ],
            'hello-kitty'  => [ 'Regalos de Hello Kitty y Kuromi' . $site, 'Tazas y vasos térmicos de Hello Kitty, alfombrillas de Kuromi y palillos de My Melody. Regalos Sanrio kawaii desde 2,95 €.' ],
            'star-wars'    => [ 'Regalos de Star Wars: Grogu y más' . $site, 'Mochila Loungefly Stormtrooper, botella térmica, bol de ramen de Grogu y figuras sorpresa de Star Wars. Regalos para fans de la saga.' ],
        ],
    ];
}

function cozy_seo_seed_run() {
    $log  = [];
    $data = cozy_seo_seed_data();

    // Pages: Yoast's post-meta watcher rebuilds the indexable on update_post_meta.
    $page_ids = [
        'home' => (int) get_option( 'page_on_front' ),
        'shop' => function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0,
    ];
    foreach ( $data['page'] as $key => $meta ) {
        $id = $page_ids[ $key ];
        if ( $id <= 0 ) {
            $log[] = "page:$key no encontrada";
            continue;
        }
        if ( '' === (string) get_post_meta( $id, '_yoast_wpseo_title', true ) ) {
            update_post_meta( $id, '_yoast_wpseo_title', $meta[0] );
        }
        if ( '' === (string) get_post_meta( $id, '_yoast_wpseo_metadesc', true ) ) {
            update_post_meta( $id, '_yoast_wpseo_metadesc', $meta[1] );
        }
        $log[] = "page:$key ok";
    }

    // Terms: Yoast keeps these in the wpseo_taxonomy_meta option; its indexable
    // is only refreshed on edited_term, hence the no-op wp_update_term().
    foreach ( [ 'product_cat', 'product_brand' ] as $taxonomy ) {
        foreach ( $data[ $taxonomy ] as $slug => $meta ) {
            $term = get_term_by( 'slug', $slug, $taxonomy );
            if ( ! $term ) {
                $log[] = "$taxonomy:$slug no encontrado";
                continue;
            }
            $values = [];
            if ( '' === (string) WPSEO_Taxonomy_Meta::get_term_meta( $term, $taxonomy, 'title' ) ) {
                $values['wpseo_title'] = $meta[0];
            }
            if ( '' === (string) WPSEO_Taxonomy_Meta::get_term_meta( $term, $taxonomy, 'desc' ) ) {
                $values['wpseo_desc'] = $meta[1];
            }
            if ( $values ) {
                WPSEO_Taxonomy_Meta::set_values( $term->term_id, $taxonomy, $values );
                wp_update_term( $term->term_id, $taxonomy );
            }
            $log[] = "$taxonomy:$slug ok";
        }
    }

    // Fallback template for every other term (incl. the 339 product tags):
    // drop the English "Archives" Yoast puts in by default.
    foreach ( [ 'product_cat', 'product_brand', 'product_tag', 'category', 'post_tag' ] as $taxonomy ) {
        WPSEO_Options::set( 'title-tax-' . $taxonomy, '%%term_title%% %%page%% %%sep%% %%sitename%%' );
    }

    return $log;
}

add_action( 'admin_init', function() {
    if ( get_option( 'cozy_seo_seed_version' ) === COZY_SEO_SEED_VERSION ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'WPSEO_Taxonomy_Meta' ) || ! class_exists( 'WPSEO_Options' ) ) {
        return;
    }
    $log = cozy_seo_seed_run();
    update_option( 'cozy_seo_seed_version', COZY_SEO_SEED_VERSION, false );
    update_option( 'cozy_seo_seed_log', $log, false );

    // LiteSpeed serves cached HTML, so the new <title>s only show after a purge.
    do_action( 'litespeed_purge_all' );
} );
