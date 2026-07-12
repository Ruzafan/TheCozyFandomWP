<?php
/**
 * Login / Register Form – Cozy Fandom Design
 * Template override: woocommerce/myaccount/form-login.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="cozy-login-page py-16 px-6 md:px-12 max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="text-center max-w-xl mx-auto mb-12">
        <span class="text-xs font-bold text-cozy-mint uppercase tracking-widest block mb-2">Tu espacio personal</span>
        <h1 class="font-serif text-3xl md:text-4xl font-bold text-cozy-coffee">
            Bienvenid@ al Fandom
        </h1>
        <p class="text-sm text-cozy-coffee/70 mt-3 leading-relaxed">
            Accede a tu cuenta para gestionar pedidos, descargas y mucho más desde tu rincón cozy.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">

        <!-- ============================================================ -->
        <!-- LOGIN                                                          -->
        <!-- ============================================================ -->
        <div class="bg-white rounded-[32px] p-8 border border-cozy-sand shadow-sm">

            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 shrink-0 rounded-2xl bg-cozy-mintLight flex items-center justify-center text-cozy-mint">
                    <?php echo cozy_icon( 'key', '16' ); ?>
                </div>
                <h2 class="font-serif text-xl font-bold text-cozy-coffee">Iniciar sesión</h2>
            </div>

            <form class="woocommerce-form woocommerce-form-login login space-y-5" method="post">

                <?php do_action( 'woocommerce_login_form_start' ); ?>

                <div>
                    <label for="username" class="block text-xs font-bold text-cozy-coffee/80 mb-2 uppercase tracking-wider">
                        <?php esc_html_e( 'Usuario o email', 'woocommerce' ); ?> <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        autocomplete="username"
                        required
                        value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                        class="w-full bg-cozy-cream border border-cozy-sand rounded-2xl px-4 py-3 text-sm text-cozy-coffee placeholder-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:ring-2 focus:ring-cozy-mint/20 transition-all"
                    />
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-cozy-coffee/80 mb-2 uppercase tracking-wider">
                        <?php esc_html_e( 'Contraseña', 'woocommerce' ); ?> <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                        class="w-full bg-cozy-cream border border-cozy-sand rounded-2xl px-4 py-3 text-sm text-cozy-coffee placeholder-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:ring-2 focus:ring-cozy-mint/20 transition-all"
                    />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-cozy-coffee/70 cursor-pointer select-none">
                        <input type="checkbox" name="rememberme" value="forever" id="rememberme" class="rounded accent-cozy-mint">
                        <?php esc_html_e( 'Recuérdame', 'woocommerce' ); ?>
                    </label>
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"
                       class="text-xs text-cozy-mint hover:text-cozy-mintDark font-medium transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <?php do_action( 'woocommerce_login_form' ); ?>

                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <input type="hidden" name="redirect" value="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" />

                <button type="submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"
                    class="w-full inline-flex items-center justify-center gap-2 bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-semibold px-8 py-4 rounded-2xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 text-sm">
                    Entrar a mi cuenta <?php echo cozy_icon( 'arrow-right', '12' ); ?>
                </button>

                <?php do_action( 'woocommerce_login_form_end' ); ?>

            </form>
        </div>

        <!-- ============================================================ -->
        <!-- REGISTER                                                       -->
        <!-- ============================================================ -->
        <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

        <div class="bg-cozy-sand/50 rounded-[32px] p-8 border border-cozy-sand shadow-sm relative overflow-hidden">

            <!-- Decorative blob -->
            <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-cozy-mint/10 rounded-full pointer-events-none" aria-hidden="true"></div>

            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 shrink-0 rounded-2xl bg-white flex items-center justify-center text-cozy-accent">
                    <?php echo cozy_icon( 'user-plus', '16' ); ?>
                </div>
                <h2 class="font-serif text-xl font-bold text-cozy-coffee">Crear cuenta</h2>
            </div>

            <form method="post" class="woocommerce-form woocommerce-form-register register space-y-5">

                <?php do_action( 'woocommerce_register_form_start' ); ?>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <div>
                    <label for="reg_username" class="block text-xs font-bold text-cozy-coffee/80 mb-2 uppercase tracking-wider">
                        <?php esc_html_e( 'Usuario', 'woocommerce' ); ?> <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="reg_username"
                        autocomplete="username"
                        placeholder="tu_nombre_cozy"
                        value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                        class="w-full bg-white border border-cozy-sand rounded-2xl px-4 py-3 text-sm text-cozy-coffee placeholder-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:ring-2 focus:ring-cozy-mint/20 transition-all"
                    />
                </div>
                <?php endif; ?>

                <div>
                    <label for="reg_email" class="block text-xs font-bold text-cozy-coffee/80 mb-2 uppercase tracking-wider">
                        <?php esc_html_e( 'Email', 'woocommerce' ); ?> <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="reg_email"
                        autocomplete="email"
                        placeholder="tunombre@email.com"
                        value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
                        class="w-full bg-white border border-cozy-sand rounded-2xl px-4 py-3 text-sm text-cozy-coffee placeholder-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:ring-2 focus:ring-cozy-mint/20 transition-all"
                    />
                </div>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                <div>
                    <label for="reg_password" class="block text-xs font-bold text-cozy-coffee/80 mb-2 uppercase tracking-wider">
                        <?php esc_html_e( 'Contraseña', 'woocommerce' ); ?> <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="reg_password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full bg-white border border-cozy-sand rounded-2xl px-4 py-3 text-sm text-cozy-coffee placeholder-cozy-coffee/40 focus:outline-none focus:border-cozy-mint focus:ring-2 focus:ring-cozy-mint/20 transition-all"
                    />
                </div>
                <?php else : ?>
                <p class="flex items-start gap-2 text-xs text-cozy-coffee/70 bg-cozy-mintLight rounded-2xl px-4 py-3 border border-cozy-mint/20">
                    <?php echo cozy_icon( 'circle-info', '14', 'text-cozy-mint mt-0.5 shrink-0' ); ?>
                    Se generará una contraseña automáticamente y se enviará a tu email.
                </p>
                <?php endif; ?>

                <?php do_action( 'woocommerce_register_form' ); ?>

                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

                <?php if ( get_option( 'wc_privacy_policy_page_id' ) || get_option( 'woocommerce_registration_privacy_policy_text' ) ) : ?>
                <div class="text-xs text-cozy-coffee/60 leading-relaxed">
                    <?php wc_privacy_policy_text( 'registration' ); ?>
                </div>
                <?php endif; ?>

                <button type="submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"
                    class="w-full inline-flex items-center justify-center gap-2 bg-cozy-coffee hover:bg-cozy-coffee/90 text-white font-semibold px-8 py-4 rounded-2xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 text-sm">
                    Unirme al Fandom <?php echo cozy_icon( 'heart', '12' ); ?>
                </button>

                <?php do_action( 'woocommerce_register_form_end' ); ?>

            </form>
        </div>

        <?php else : ?>

        <!-- Decorative panel when registration is disabled -->
        <div class="bg-cozy-sand/50 rounded-[32px] p-8 border border-cozy-sand flex flex-col items-center justify-center text-center relative overflow-hidden min-h-[360px]">
            <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-cozy-mint/10 rounded-full pointer-events-none" aria-hidden="true"></div>
            <div class="w-20 h-20 rounded-[24px] bg-cozy-mintLight flex items-center justify-center text-cozy-mint text-3xl mb-6">
                <?php echo cozy_icon( 'mug-saucer', '32' ); ?>
            </div>
            <h3 class="font-serif text-xl font-bold text-cozy-coffee mb-3">Tu rincón favorito</h3>
            <p class="text-sm text-cozy-coffee/70 leading-relaxed max-w-xs">
                Gestiona tus pedidos, direcciones y datos personales desde un solo lugar acogedor.
            </p>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
               class="mt-6 inline-flex items-center gap-2 bg-white border border-cozy-sand hover:bg-cozy-mintLight hover:border-cozy-mint text-cozy-coffee font-semibold px-6 py-3 rounded-2xl text-sm transition-all">
                <?php echo cozy_icon( 'basket-shopping', '14', 'text-cozy-mint' ); ?> Explorar la tienda
            </a>
        </div>

        <?php endif; ?>

    </div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
