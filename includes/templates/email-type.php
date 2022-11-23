<?php

use VIWEC\INC\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_types      = Utils::get_email_ids();
$email_types1     = Utils::get_email_ids_grouped();
$admin_email_type = Utils::admin_email_type()

?>
<div>
    <div class="viwec-setting-row">
        <select class="viwec-input viwec-set-email-type" name="viwec_settings_type" required>
			<?php
			printf( "<option value='default' %1s>%3s</option>", esc_attr( $type_selected == 'default' ? 'selected' : '' ), esc_html( $email_types['default'] ) );
			?>
            <optgroup label="<?php esc_html_e( 'Admin', 'viwec-email-template-customizer' ); ?>">
				<?php
				foreach ( $email_types as $id => $title ) {
					if ( $id == 'default' || ! in_array( $id, $admin_email_type ) ) {
						continue;
					}
					$selected = $type_selected == $id ? 'selected' : '';

					printf( "<option value='%1s' %2s>%3s</option>", esc_attr( $id ), esc_attr( $selected ), esc_html( $title ) );
				}
				?>
            </optgroup>
            <optgroup label="<?php esc_html_e( 'Customer', 'viwec-email-template-customizer' ); ?>">
				<?php
				foreach ( $email_types as $id => $title ) {
					if ( $id == 'default' || in_array( $id, $admin_email_type ) ) {
						continue;
					}
					$selected = $type_selected == $id ? 'selected' : '';

					printf( "<option value='%1s' %2s>%3s</option>", esc_attr( $id ), esc_attr( $selected ), esc_html( $title ) );
				}
				?>
            </optgroup>
        </select>
    </div>
	<?php do_action( 'viwec_setting_options', $type_selected ); ?>
    <div>
        <div class="viwec-option-label">
			<?php esc_html_e( 'Direction', 'viwec-email-template-customizer' ); ?>
        </div>

		<?php
		$directions = [
			'ltr' => esc_html__( 'Left to right', 'viwec-email-template-customizer' ),
			'rtl' => esc_html__( 'Right to left', 'viwec-email-template-customizer' )
		];
		?>
        <select class="viwec-settings-direction" name="viwec_settings_direction">
			<?php
			foreach ( $directions as $value => $text ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $direction_selected, $value, false ), esc_html( $text ) );
			}
			?>
        </select>
    </div>
</div>
