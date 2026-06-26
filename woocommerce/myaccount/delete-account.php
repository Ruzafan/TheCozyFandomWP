<?php
/**
 * Delete Account – Cozy Fandom Design
 * Template override: woocommerce/myaccount/delete-account.php
 */
defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
?>

<div class="cozy-delete-account max-w-lg mx-auto py-4">

    <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-2xl bg-red-50 flex items-center justify-center shrink-0">
            <?php echo cozy_icon( 'triangle-exclamation', '16', 'text-red-400' ); ?>
        </div>
        <div>
            <h2 class="font-serif text-xl font-bold text-cozy-coffee m-0 p-0 border-0">Cerrar cuenta</h2>
            <p class="text-xs text-cozy-coffee/60 m-0 mt-0.5">Esta acción es permanente e irreversible</p>
        </div>
    </div>

    <div class="bg-red-50 border border-red-100 rounded-2xl p-5 mb-6 text-sm text-red-700 space-y-2">
        <p class="font-bold m-0">¿Seguro que quieres eliminar tu cuenta?</p>
        <ul class="list-disc list-inside space-y-1 m-0 text-red-600/80">
            <li>Se eliminará tu cuenta (<strong><?php echo esc_html( $current_user->user_email ); ?></strong>)</li>
            <li>Perderás acceso a tu historial de pedidos</li>
            <li>Tus datos personales serán borrados</li>
        </ul>
        <p class="m-0 text-xs text-red-500">Los pedidos ya realizados se conservan por obligación legal (período fiscal), pero quedarán anonimizados.</p>
    </div>

    <?php if ( isset( $_GET['error'] ) && $_GET['error'] === 'password' ) : ?>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 text-sm text-red-800 font-medium">
            La contraseña introducida es incorrecta. Por favor, inténtalo de nuevo.
        </div>
    <?php endif; ?>

    <form method="POST">
        <?php wp_nonce_field( 'cozy_delete_account' ); ?>
        
        <div class="mb-6">
            <label for="cozy_confirm_password" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/60 mb-2">
                Confirma tu contraseña actual
            </label>
            <input type="password" name="cozy_confirm_password" id="cozy_confirm_password" required
                   class="w-full px-4 py-3 rounded-2xl border border-red-100 focus:outline-none focus:border-red-300 focus:ring-1 focus:ring-red-300 bg-white text-sm text-cozy-coffee transition-all shadow-inner"
                   placeholder="Introduce tu contraseña para confirmar">
        </div>

        <button type="submit" name="cozy_delete_account" value="1"
                class="w-full py-3 rounded-2xl bg-red-500 hover:bg-red-600 text-white font-bold text-sm transition-colors cursor-pointer border-0 shadow-sm">
            <?php echo cozy_icon( 'user-xmark', '14' ); ?>
            Sí, eliminar mi cuenta permanentemente
        </button>
    </form>

    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>"
       class="block text-center mt-4 text-sm font-semibold text-cozy-mint hover:text-cozy-mintDark transition-colors no-underline">
        No, volver a mi cuenta
    </a>

</div>
