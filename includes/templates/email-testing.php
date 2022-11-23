<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div>
    <div>
        <div class="viwec-option-label"><?php esc_html_e( 'Select order', 'viwec-email-template-customizer' ); ?></div>
        <select class="viwec-order-id-test">
			<?php
			if ( ! empty( $orders ) ) {
				foreach ( $orders as $_order ) {
					$name = method_exists( $_order, 'get_formatted_billing_full_name' ) ? $_order->get_formatted_billing_full_name() : '';
					$name = trim( $name ) ? ' - ' . $name : '';
					$stt  = $_order->get_status();
					$stt  = trim( $stt ) ? ' - ' . $stt : '';
					?>
                    <option value='<?php echo esc_attr( $_order->get_id() ); ?>'>
                        #<?php echo esc_html( $_order->get_id() . $name . $stt ) ?>
                    </option>
					<?php
				}
			}
			?>
        </select>
    </div>

    <div>
        <div class="viwec-option-label"><?php esc_html_e( 'Preview', 'viwec-email-template-customizer' ); ?></div>
        <div class="viwec-btn-group vi-ui buttons">
            <button type="button" class="viwec-preview-email-btn desktop vi-ui button mini attached"
                    title="<?php esc_html_e( 'Preview width device screen width > 380px', 'viwec-email-template-customizer' ); ?>">
                <i class="dashicons dashicons-laptop"> </i>
            </button>
            <button type="button" class="viwec-preview-email-btn mobile vi-ui button mini attached"
                    title="<?php esc_html_e( 'Preview width device screen width < 380px', 'viwec-email-template-customizer' ); ?>">
                <i class="dashicons dashicons-smartphone"> </i>
            </button>
        </div>
    </div>

    <div>
        <div class="viwec-option-label"><?php esc_html_e( 'Send to', 'viwec-email-template-customizer' ); ?></div>
        <div class="viwec-flex">
            <input type="text" class="viwec-to-email" value="<?php echo esc_html( get_bloginfo( 'admin_email' ) ) ?>">
            <button type="button" class="viwec-send-test-email-btn vi-ui button mini attached"
                    title="<?php esc_html_e( 'Send test email', 'viwec-email-template-customizer' ); ?>">
                <i class="dashicons dashicons-email"> </i>
            </button>
        </div>
        <div class="viwec-send-test-email-result"></div>
    </div>
</div>

<div class="vi-ui longer modal ">
    <i class="icon close dashicons dashicons-no-alt"></i>

    <div class="header">
		<?php esc_html_e( 'Preview', 'viwec-email-template-customizer' ); ?>
        <div class="viwec-view-btn-group vi-ui buttons">
            <button class="vi-ui button mini viwec-pc-view attached">
                <i class="dashicons dashicons-laptop "
                   title="<?php esc_html_e( 'Desktop & mobile (width >380px)', 'viwec-email-template-customizer' ); ?>"></i>
            </button>
            <button class="vi-ui button mini viwec-mobile-view attached">
                <i class="dashicons dashicons-smartphone"
                   title="<?php esc_html_e( 'View mobile version (width < 380px)', 'viwec-email-template-customizer' ); ?>"></i>
            </button>
            <button class="vi-ui button mini viwec-send-test-email-btn attached">
                <i class="dashicons dashicons-email "
                   title="<?php esc_html_e( 'Send test email', 'viwec-email-template-customizer' ); ?>"></i>
            </button>
        </div>
    </div>

    <div class="content scrolling">
        <div class="viwec-email-preview-content">

        </div>
    </div>

    <div class="actions">

    </div>
</div>
