<?php
/**
 * Cozy Fandom – "Coming soon" / private store mode
 *
 * When enabled (Ajustes > Generales > "Modo tienda privada"), every front-end
 * request from anyone who isn't an administrator is replaced by a single
 * email-capture page — no shop, listing, cart or account pages are reachable.
 * Entering an administrator's email quietly redirects to wp-login.php instead
 * of collecting the address, so the store keeps a normal-looking "notify me"
 * form with a hidden door in for admins. This is obscurity, not access
 * control — wp-login.php still requires the real password.
 *
 * @package cozy-fandom-child
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/*  SETTING — toggle in Ajustes > Generales                            */
/* ------------------------------------------------------------------ */
add_action( 'admin_init', function () {
    register_setting( 'general', 'cozy_coming_soon_mode', [
        'type'              => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default'           => false,
    ] );
    add_settings_field(
        'cozy_coming_soon_mode',
        'Modo tienda privada ("Próximamente")',
        function () {
            $val = get_option( 'cozy_coming_soon_mode' );
            echo '<label><input type="checkbox" name="cozy_coming_soon_mode" value="1" ' . checked( $val, true, false ) . '> Mostrar solo un formulario de aviso por email; oculta toda la tienda a quien no sea administrador.</label>';
        },
        'general'
    );
} );

/* ------------------------------------------------------------------ */
/*  FRONT-END GATE                                                      */
/* ------------------------------------------------------------------ */
add_action( 'template_redirect', function () {
    if ( ! get_option( 'cozy_coming_soon_mode' ) ) {
        return;
    }
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }
    cozy_render_coming_soon_page();
    exit;
} );

function cozy_render_coming_soon_page() {
    status_header( 503 );
    header( 'Retry-After: 3600' );
    nocache_headers();
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php bloginfo( 'name' ); ?> &mdash; Muy pronto</title>
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/style.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/style.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/css/main.css' ); ?>">
</head>
<body class="bg-cozy-cream">
    <?php
    $logo_id       = get_theme_mod( 'custom_logo' );
    $instagram_url = get_option( 'cozy_instagram_url', '' );
    $tiktok_url    = get_option( 'cozy_tiktok_url', '' );

    $teasers = [
        [ 'img' => 'snoopy-heart.png',  'label' => 'Snoopy' ],
        [ 'img' => 'harry-potter.png',  'label' => 'Harry Potter' ],
        [ 'img' => 'disney.png',        'label' => 'Disney' ],
    ];
    ?>

    <!-- Full-bleed banner (same pattern as front-page.php's hero), email box overlaid on top -->
    <section class="relative" style="overflow:hidden;">
        <div class="relative w-full h-full md:h-[80vh]">

            <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/banner.jpeg' ); ?>"
                 alt="" aria-hidden="true" loading="eager"
                 class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none select-none">

            <div class="absolute inset-x-0 bottom-0 h-1/3 pointer-events-none"
                 style="background:linear-gradient(180deg, rgba(252,249,245,0) 0%, #FCF9F5 100%);"></div>

            <div class="relative z-10 h-full flex items-center justify-center px-6">
                <div class="w-full max-w-md rounded-[28px] p-8 sm:p-10 text-center shadow-sm my-5 mx-auto"
                     style="background:rgba(255,255,255,0.85); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);">

                    <?php if ( $logo_id ) : ?>
                        <?php echo wp_get_attachment_image( $logo_id, [ 72, 72 ], false, [
                            'class' => 'mx-auto mb-3 rounded-2xl object-cover',
                            'alt'   => get_bloginfo( 'name' ),
                        ] ); ?>
                    <?php else : ?>
                        <span class="text-4xl block mb-2" aria-hidden="true">🌿</span>
                    <?php endif; ?>

                    <span class="font-serif text-xl font-bold text-cozy-coffee block mb-3"><?php bloginfo( 'name' ); ?></span>

                    <h1 class="font-bold text-lg text-cozy-coffee mb-2 m-0">Estamos preparando algo bonito</h1>
                    <p class="text-sm text-cozy-coffee/70 mt-2 mb-6">Muy pronto abrimos la tienda. Déjanos tu email y te avisamos en cuanto esté lista.</p>

                    <form id="cozy-coming-soon-form" class="space-y-3" novalidate>
                        <input type="email" name="email" required placeholder="tú@email.com"
                               class="w-full text-sm px-4 py-3 rounded-full border border-cozy-sand focus:outline-none focus:border-cozy-mint bg-white/80 text-cozy-coffee">
                        <button type="submit" class="w-full bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold text-sm px-4 py-3 rounded-full transition-colors">Avísame</button>
                    </form>
                    <p id="cozy-coming-soon-message" class="text-xs mt-4 min-h-[1em]"></p>

                    <?php if ( ( $instagram_url && $instagram_url !== '#' ) || ( $tiktok_url && $tiktok_url !== '#' ) ) : ?>
                    <div class="flex items-center justify-center gap-3 pt-6 mt-6 border-t border-cozy-sand/70">
                        <?php if ( $instagram_url && $instagram_url !== '#' ) : ?>
                        <a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
                           class="w-10 h-10 rounded-full bg-cozy-cream hover:bg-cozy-mint flex items-center justify-center text-cozy-coffee/60 hover:text-cozy-coffee transition-colors">
                            <?php echo cozy_icon( 'instagram', '16' ); ?>
                        </a>
                        <?php endif; ?>
                        <?php if ( $tiktok_url && $tiktok_url !== '#' ) : ?>
                        <a href="<?php echo esc_url( $tiktok_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok"
                           class="w-10 h-10 rounded-full bg-cozy-cream hover:bg-cozy-mint flex items-center justify-center text-cozy-coffee/60 hover:text-cozy-coffee transition-colors">
                            <?php echo cozy_icon( 'tiktok', '16' ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Sneak peek of what's coming + trust badges -->
    <section class="px-6 pt-10 md:pt-14 pb-14">
        <div class="max-w-3xl mx-auto space-y-10 md:space-y-12 flex flex-col gap-y-2.5 mt-[15px]">
            <div class="text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-cozy-mintDark mb-6">Muy pronto en la tienda</p>
                <div class="flex items-center justify-center gap-8 sm:gap-12">
                    <?php foreach ( $teasers as $teaser ) : ?>
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white border border-cozy-sand shadow-sm flex items-center justify-center overflow-hidden">
                            <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/' . $teaser['img'] ); ?>"
                                 alt="" aria-hidden="true" loading="lazy" class="w-full h-full object-contain p-1.5 opacity-90">
                        </div>
                        <span class="text-[10px] font-bold text-cozy-coffee/50 uppercase tracking-wide"><?php echo esc_html( $teaser['label'] ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <p class="text-center text-[11px] text-cozy-coffee/40">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Todos los derechos reservados.</p>
        </div>
    </section>

    <script>
    (function () {
        var form = document.getElementById( 'cozy-coming-soon-form' );
        var msg  = document.getElementById( 'cozy-coming-soon-message' );
        form.addEventListener( 'submit', function ( e ) {
            e.preventDefault();
            var email = form.email.value.trim();
            var btn   = form.querySelector( 'button' );
            btn.disabled = true;
            msg.textContent = '';

            var body = new URLSearchParams();
            body.set( 'action', 'cozy_coming_soon_notify' );
            body.set( 'nonce', <?php echo wp_json_encode( wp_create_nonce( 'cozy_coming_soon' ) ); ?> );
            body.set( 'email', email );

            fetch( <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( res ) {
                if ( res.success && res.data && res.data.redirect ) {
                    window.location.href = res.data.redirect;
                    return;
                }
                btn.disabled = false;
                msg.textContent = ( res.data && res.data.message ) || '';
                msg.className = 'text-xs mt-4 min-h-[1em] ' + ( res.success ? 'text-cozy-mintDark' : 'text-red-400' );
                if ( res.success ) { form.reset(); }
            } )
            .catch( function () {
                btn.disabled = false;
                msg.textContent = 'Error de conexión. Inténtalo de nuevo.';
                msg.className = 'text-xs mt-4 min-h-[1em] text-red-400';
            } );
        } );
    })();
    </script>
</body>
</html>
    <?php
}

/* ------------------------------------------------------------------ */
/*  AJAX — email capture + hidden admin door                           */
/* ------------------------------------------------------------------ */
add_action( 'wp_ajax_cozy_coming_soon_notify', 'cozy_coming_soon_notify' );
add_action( 'wp_ajax_nopriv_cozy_coming_soon_notify', 'cozy_coming_soon_notify' );

function cozy_coming_soon_notify() {
    check_ajax_referer( 'cozy_coming_soon', 'nonce' );

    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Introduce un email válido.' ] );
    }

    $user = get_user_by( 'email', $email );
    if ( $user && user_can( $user, 'manage_options' ) ) {
        wp_send_json_success( [ 'redirect' => wp_login_url() ] );
    }

    // Keep a local record so no signup is lost even if Mailchimp isn't configured
    $emails = get_option( 'cozy_coming_soon_emails', [] );
    if ( ! is_array( $emails ) ) {
        $emails = [];
    }
    $key = strtolower( $email );
    if ( ! isset( $emails[ $key ] ) ) {
        $emails[ $key ] = current_time( 'mysql' );
        update_option( 'cozy_coming_soon_emails', $emails, false );
    }

    // Best-effort: also push to Mailchimp when configured. Failure here shouldn't
    // block the visitor's confirmation — the local record above already has them.
    cozy_mailchimp_upsert_subscriber( $email );

    wp_send_json_success( [ 'message' => '¡Gracias! Te avisaremos en cuanto abramos. 🌿' ] );
}

/* ------------------------------------------------------------------ */
/*  LOGIN PAGE — match the store's look, no way back into the shop     */
/* ------------------------------------------------------------------ */
add_action( 'login_enqueue_scripts', function () {
    ?>
    <style>
        body.login { background: #FCF9F5; }
        body.login #login h1 a {
            background-image: none;
            width: auto;
            height: auto;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #3A3128;
            text-indent: 0;
        }
        body.login #login h1 a::before { content: "🌿 "; }
        body.login form#loginform,
        body.login form#lostpasswordform,
        body.login form#registerform {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(58,49,40,.08);
            border: 1px solid #F2E6D5;
        }
        body.login .button-primary {
            background: #88C4B5 !important;
            border-color: #88C4B5 !important;
            color: #3A3128 !important;
            text-shadow: none !important;
            box-shadow: none !important;
            border-radius: 999px !important;
            font-weight: 700 !important;
        }
        body.login .button-primary:hover,
        body.login .button-primary:focus {
            background: #72b0a2 !important;
            border-color: #72b0a2 !important;
        }
        body.login #nav a,
        body.login #backtoblog a { color: #3A3128 !important; }
    </style>
    <?php
} );

add_filter( 'login_headerurl', function () {
    return home_url( '/' );
} );

add_filter( 'login_headertext', function () {
    return get_bloginfo( 'name' );
} );
