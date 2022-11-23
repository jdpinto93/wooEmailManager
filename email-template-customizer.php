<?php
 /**
 * Plugin Name:       Email Editor
 * Plugin URI:        http://www.webmasteryagency.com
 * Description:       Edita los correos electronicos de woocommerce con la facilidad de un builder Drag and Drop
 * Version:           1.1.3
 * Requires at least: 5.2
 * Requires PHP:      7.2.2
 * Author:            Jose Pinto
 * Author URI:        http://www.webmasteryagency.com
 * License:           GPL v3 or later
 * Domain Path: /lang
 * Text Domain _JPinto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


define( 'VIWEC_VER', '1.1.3' );
define( 'VIWEC_NAME', 'Editor de Correos' );

if ( ! class_exists( 'Woo_Email_Template_Customizer' ) ) {
	class Woo_Email_Template_Customizer {
		public $err_message;
		public $wp_version_require = '5.0';
		public $wc_version_require = '5.0';
		public $php_version_require = '7.0';

		public function __construct() {
			$this->condition_init();
			add_action( 'admin_notices', array( $this, 'admin_notices_condition' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_actions_link' ), 99 );
		}

		public function condition_init() {
			global $wp_version;
			if ( version_compare( $this->wp_version_require, $wp_version, '>' ) ) {
				$this->err_message = __( 'Please upgrade WordPress version to', 'viwec-email-template-customizer' ) . ' ' . $this->wp_version_require;

				return;
			}

			if ( version_compare( $this->php_version_require, phpversion(), '>' ) ) {
				$this->err_message = __( 'Please upgrade php version to', 'viwec-email-template-customizer' ) . ' ' . $this->php_version_require;

				return;
			}

			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$this->err_message = __( 'Please install and activate WooCommerce to use', 'viwec-email-template-customizer' );
				unset( $_GET['activate'] );  // phpcs:ignore WordPress.Security.NonceVerification
				deactivate_plugins( plugin_basename( __FILE__ ) );

				return;
			}

			$wc_version = get_option( 'woocommerce_version' );
			if ( version_compare( $this->wc_version_require, $wc_version, '>' ) ) {
				$this->err_message = __( 'Please upgrade WooCommerce version to', 'viwec-email-template-customizer' ) . ' ' . $this->wc_version_require;

				return;
			}

			$this->define();

			if ( is_file( VIWEC_INCLUDES . 'init.php' ) ) {
				require_once VIWEC_INCLUDES . 'init.php';
				register_activation_hook( __FILE__, array( $this, 'viwec_activate' ) );
			}
		}

		public function define() {
			$plugin_url = plugin_dir_url( __FILE__ );

			define( 'VIWEC_SLUG', 'woo-email-template-customizer' );
			define( 'VIWEC_DIR', plugin_dir_path( __FILE__ ) );
			define( 'VIWEC_INCLUDES', VIWEC_DIR . "includes" . DIRECTORY_SEPARATOR );
			define( 'VIWEC_SUPPORT', VIWEC_INCLUDES . "support" . DIRECTORY_SEPARATOR );
			define( 'VIWEC_TEMPLATES', VIWEC_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
			define( 'VIWEC_LANGUAGES', VIWEC_DIR . "languages" . DIRECTORY_SEPARATOR );

			define( 'VIWEC_CSS', $plugin_url . "assets/css/" );
			define( 'VIWEC_JS', $plugin_url . "assets/js/" );
			define( 'VIWEC_IMAGES', $plugin_url . "assets/img/" );
		}

		public function viwec_activate() {
			$check_exist = get_posts( [ 'post_type' => 'viwec_template', 'numberposts' => 1 ] );

			if ( empty( $check_exist ) ) {
				$default_subject = \VIWEC\INC\Email_Samples::default_subject();
				$templates       = \VIWEC\INC\Email_Samples::sample_templates();
				$site_title      = get_option( 'blogname' );
				foreach ( $templates as $key => $template ) {
					$args     = [
						'post_title'  => $default_subject[ $key ] ? str_replace( '{site_title}', $site_title, $default_subject[ $key ] ) : '',
						'post_status' => 'publish',
						'post_type'   => 'viwec_template',
					];
					$post_id  = wp_insert_post( $args );
					$template = $template['basic']['data'];
					$template = str_replace( '\\', '\\\\', $template );
					update_post_meta( $post_id, 'viwec_settings_type', $key );
					update_post_meta( $post_id, 'viwec_email_structure', $template );
				}
				update_option( 'viwec_email_update_button', true, 'no' );
			}
		}

		public function admin_notices_condition() {
			if ( $this->err_message ) {
				?>
                <div id="message" class="error">
                    <p><?php echo esc_html( $this->err_message . ' ' . __( 'to use', 'viwec-email-template-customizer' ) . ' ' . VIWEC_NAME ); ?></p>
                </div>
				<?php
			}
		}

		public function plugin_actions_link( $links ) {
			if ( ! $this->err_message ) {
				$settings_link = '<a href="' . admin_url( 'edit.php?post_type=viwec_template' ) . '">' . __( 'Settings', 'viwec-email-template-customizer' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}
	}

	new Woo_Email_Template_Customizer();
}