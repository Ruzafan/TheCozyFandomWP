<?php
/**
 * Edit Account Form – Cozy Fandom Design with Collapsible Password Section
 * Template override: woocommerce/myaccount/form-edit-account.php
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' ); ?>

<div class="cozy-account-edit-wrap bg-white rounded-[24px] p-6 sm:p-10 border border-cozy-sand shadow-sm max-w-2xl mx-auto my-6">

    <h2 class="font-serif text-2xl font-bold text-cozy-coffee mb-6">Detalles de la cuenta</h2>

    <form class="woocommerce-EditAccountForm edit-account space-y-6" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

        <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <p class="woocommerce-form-row woocommerce-form-row--first form-row m-0">
                <label for="account_first_name" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Nombre', 'woocommerce' ); ?>&nbsp;<span class="required text-cozy-mint">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 bg-cozy-cream rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" required />
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--last form-row m-0">
                <label for="account_last_name" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Apellidos', 'woocommerce' ); ?>&nbsp;<span class="required text-cozy-mint">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 bg-cozy-cream rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" required />
            </p>
        </div>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide m-0">
            <label for="account_display_name" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Nombre público en la web', 'woocommerce' ); ?>&nbsp;<span class="required text-cozy-mint">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 bg-cozy-cream rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required />
            <span class="block text-[11px] text-cozy-coffee/60 mt-1">Este será el nombre que se mostrará en tu cuenta y en las opiniones de productos.</span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide m-0">
            <label for="account_email" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Correo electrónico', 'woocommerce' ); ?>&nbsp;<span class="required text-cozy-mint">*</span></label>
            <input type="email" class="woocommerce-Input woocommerce-Input--email input-text w-full px-4 py-3 bg-cozy-cream rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
        </p>

        <!-- PASSWORD TOGGLE & COLLAPSIBLE FIELDSET -->
        <div class="pt-4 border-t border-cozy-sand">
            <label for="cozy_toggle_password" class="inline-flex items-center gap-3 cursor-pointer select-none p-3.5 rounded-xl bg-cozy-cream hover:bg-cozy-sand/50 transition-colors border border-cozy-sand">
                <input type="checkbox" id="cozy_toggle_password" name="cozy_toggle_password" class="w-4 h-4 accent-cozy-mint rounded cursor-pointer" />
                <span class="text-xs font-bold text-cozy-coffee flex items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cozy-coffee/70"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Quiero cambiar mi contraseña
                </span>
            </label>

            <fieldset id="cozy_password_fieldset" class="hidden mt-4 p-5 bg-cozy-cream/50 rounded-2xl border border-cozy-sand/80 space-y-4">
                <legend class="sr-only"><?php esc_html_e( 'Cambio de contraseña', 'woocommerce' ); ?></legend>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide m-0">
                    <label for="password_current" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Contraseña actual', 'woocommerce' ); ?></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 bg-white rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="password_current" id="password_current" autocomplete="new-password" disabled />
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide m-0">
                    <label for="password_1" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Nueva contraseña', 'woocommerce' ); ?></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 bg-white rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="password_1" id="password_1" autocomplete="new-password" disabled />
                </p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide m-0">
                    <label for="password_2" class="block text-xs font-bold uppercase tracking-wider text-cozy-coffee/80 mb-1.5"><?php esc_html_e( 'Confirmar nueva contraseña', 'woocommerce' ); ?></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 bg-white rounded-xl border border-cozy-sand focus:border-cozy-mint focus:outline-none text-sm text-cozy-coffee" name="password_2" id="password_2" autocomplete="new-password" disabled />
                </p>
            </fieldset>
        </div>

        <?php do_action( 'woocommerce_edit_account_form' ); ?>

        <div class="pt-4">
            <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
            <button type="submit" class="woocommerce-Button button bg-cozy-mint hover:bg-cozy-mintDark text-cozy-coffee font-bold px-8 py-3.5 rounded-2xl shadow-sm hover:shadow-md transition-all text-sm cursor-pointer border-none" name="save_account_details" value="<?php esc_attr_e( 'Guardar cambios', 'woocommerce' ); ?>"><?php esc_html_e( 'Guardar cambios', 'woocommerce' ); ?></button>
            <input type="hidden" name="action" value="save_account_details" />
        </div>

        <?php do_action( 'woocommerce_edit_account_form_end' ); ?>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('cozy_toggle_password');
    var fieldset = document.getElementById('cozy_password_fieldset');
    if (!toggle || !fieldset) return;

    var inputs = fieldset.querySelectorAll('input[type="password"]');

    function updateState() {
        if (toggle.checked) {
            fieldset.classList.remove('hidden');
            inputs.forEach(function(input) {
                input.removeAttribute('disabled');
            });
        } else {
            fieldset.classList.add('hidden');
            inputs.forEach(function(input) {
                input.setAttribute('disabled', 'disabled');
                input.value = '';
            });
        }
    }

    toggle.addEventListener('change', updateState);
    updateState();
});
</script>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
