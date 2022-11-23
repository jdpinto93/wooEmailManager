<?php

namespace VIWEC\INC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Auto load class
spl_autoload_register( function ( $class ) {
	$prefix   = __NAMESPACE__;
	$base_dir = __DIR__;
	$len      = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = strtolower( substr( $class, $len ) );
	$relative_class = strtolower( str_replace( '_', '-', $relative_class ) );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	} else {
		return;
	}
} );


/*
 * Initialize Plugin
 */

class Init {
	protected static $instance = null;
	public static $img_map;
	protected $cache_products = [];
	protected $cache_posts = [];

	private function __construct() {
		$this->define_params();
		$this->class_init();

		add_action( 'init', array( $this, 'plugin_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_run_file' ), 9999 );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function define_params() {
		self::$img_map = apply_filters( 'viwec_image_map', [
			'infor_icons' => [
				'home'     => [
					'home-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'home-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'home-white-border' => esc_html__( 'White/Border', 'viwec-email-template-customizer' ),
					'home-black-border' => esc_html__( 'Black/Border', 'viwec-email-template-customizer' ),
					'home-black-white'  => esc_html__( 'Black/White', 'viwec-email-template-customizer' ),
					'home-white-black'  => esc_html__( 'White/Black', 'viwec-email-template-customizer' ),
				],
				'email'    => [
					'email-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'email-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'email-white-border' => esc_html__( 'White/Border', 'viwec-email-template-customizer' ),
					'email-black-border' => esc_html__( 'Black/Border', 'viwec-email-template-customizer' ),
					'email-black-white'  => esc_html__( 'Black/White', 'viwec-email-template-customizer' ),
					'email-white-black'  => esc_html__( 'White/Black', 'viwec-email-template-customizer' ),
				],
				'phone'    => [
					'phone-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'phone-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'phone-white-border' => esc_html__( 'White/Border', 'viwec-email-template-customizer' ),
					'phone-black-border' => esc_html__( 'Black/Border', 'viwec-email-template-customizer' ),
					'phone-black-white'  => esc_html__( 'Black/White', 'viwec-email-template-customizer' ),
					'phone-white-black'  => esc_html__( 'White/Black', 'viwec-email-template-customizer' ),
				],
				'location' => [
					'location-white'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'location-black'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'location-white-border' => esc_html__( 'White/Border', 'viwec-email-template-customizer' ),
					'location-black-border' => esc_html__( 'Black/Border', 'viwec-email-template-customizer' ),
					'location-black-white'  => esc_html__( 'Black/White', 'viwec-email-template-customizer' ),
					'location-white-black'  => esc_html__( 'White/Black', 'viwec-email-template-customizer' ),
				],
			],

			'social_icons' => [
				'facebook' => [
					''                => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'fb-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'fb-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'fb-blue'         => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'fb-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'fb-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'fb-blue-border'  => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'fb-blue-white'   => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'fb-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'fb-white-blue'   => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'twitter' => [
					''                 => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'twi-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'twi-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'twi-cyan'         => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'twi-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'twi-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'twi-cyan-border'  => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'twi-cyan-white'   => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'twi-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'twi-white-cyan'   => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'instagram' => [
					''                 => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'ins-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'ins-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'ins-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'ins-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'ins-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'ins-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'ins-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'ins-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'ins-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'youtube' => [
					''                => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'yt-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'yt-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'yt-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'yt-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'yt-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'yt-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'yt-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'yt-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'yt-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'linkedin' => [
					''                => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'li-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'li-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'li-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'li-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'li-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'li-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'li-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'li-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'li-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'whatsapp' => [
					''                => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'wa-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'wa-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'wa-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'wa-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'wa-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'wa-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'wa-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'wa-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'wa-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],
				'telegram' => [
					''                  => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'tele-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'tele-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'tele-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'tele-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'tele-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'tele-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'tele-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'tele-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'tele-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],

				'tiktok' => [
					''                    => esc_html__( 'Disable', 'viwec-email-template-customizer' ),
					'tiktok-black'        => esc_html__( 'Black', 'viwec-email-template-customizer' ),
					'tiktok-white'        => esc_html__( 'White', 'viwec-email-template-customizer' ),
					'tiktok-color'        => esc_html__( 'Color', 'viwec-email-template-customizer' ),
					'tiktok-white-border' => esc_html__( 'White border', 'viwec-email-template-customizer' ),
					'tiktok-black-border' => esc_html__( 'Black border', 'viwec-email-template-customizer' ),
					'tiktok-color-border' => esc_html__( 'Color border', 'viwec-email-template-customizer' ),
					'tiktok-color-white'  => esc_html__( 'Color - White', 'viwec-email-template-customizer' ),
					'tiktok-white-black'  => esc_html__( 'Black - White', 'viwec-email-template-customizer' ),
					'tiktok-white-color'  => esc_html__( 'White - Color', 'viwec-email-template-customizer' ),
				],
			]
		] );
	}

	public function class_init() {
		Utils::init();
		Email_Builder::init();
		Email_Trigger::init();
		Compatible::init();
		include_once VIWEC_DIR . 'compatible' . DIRECTORY_SEPARATOR . 'email-template-customizer.php';
		include_once VIWEC_SUPPORT . 'support.php';
		include_once VIWEC_INCLUDES . 'functions.php';

		if ( class_exists( 'VillaTheme_Support' ) ) {
			new \VillaTheme_Support(
				array(
					'support'    => 'https://wordpress.org/support/plugin/',
					//'docs'       => 'http://docs.villatheme.com/?item=woocommerce-email-template-customizer',
					//'review'     => 'https://wordpress.org/support/plugin/email-template-customizer-for-woo/reviews/?rate=5#rate-response',
					//'pro_url'    => 'https://1.envato.market/BZZv1',
					'css'        => VIWEC_CSS,
					'image'      => VIWEC_IMAGES,
					'slug'       => 'email-template-customizer',
					'menu_slug'  => 'edit.php?post_type=viwec_template',
					'version'    => VIWEC_VER,
					//'survey_url' => 'https://script.google.com/macros/s/AKfycbxkQO1eTmttYm1uNwN_pxenA9JEYbDo8PWumGZPvk29G2VIDi59/exec'
				)
			);
		}
	}

	public function plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		// Global + Frontend Locale
		load_textdomain( 'viwec-email-template-customizer', VIWEC_LANGUAGES . "viwec-email-template-customizer-$locale.mo" );
		load_plugin_textdomain( 'viwec-email-template-customizer', false, VIWEC_LANGUAGES );
	}

	public function admin_enqueue() {
		global $post;
		switch ( get_current_screen()->id ) {
			case 'viwec_template':
				$scripts_lib = [ 'tab', 'accordion', 'select2', 'dimmer', 'transition', 'modal' ];
				$scripts     = [ 'inputs', 'email-builder', 'properties', 'components' ];
				$styles_lib  = [ 'tab', 'menu', 'accordion', 'select2', 'dimmer', 'transition', 'modal', 'button' ];
				$styles      = [ 'email-builder' ];

				wp_enqueue_editor();
				wp_enqueue_media();
				wp_enqueue_script( 'wc-enhanced-select' );
				wp_enqueue_script( 'iris' );

				Utils::enqueue_admin_script_libs( $scripts_lib, [ 'jquery' ] );
				Utils::enqueue_admin_scripts( $scripts, [ 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker' ] );

				Utils::enqueue_admin_styles_libs( $styles_lib );
				Utils::enqueue_admin_styles( $styles );

				$samples         = Email_Samples::sample_templates();
				$hide_rule       = Utils::get_hide_rules_data();
				$accept_elements = Utils::get_accept_elements_data();

				$params = [
					'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
					'nonce'               => wp_create_nonce( 'viwec_nonce' ),
					'product'             => VIWEC_IMAGES . 'product.png',
					'post'                => VIWEC_IMAGES . 'post.png',
					'placeholder'         => VIWEC_IMAGES . 'placeholder.jpg',
					'emailTypes'          => Utils::get_email_ids_grouped(),
					'samples'             => $samples,
					'subjects'            => Email_Samples::default_subject(),
					'adminBarStt'         => Utils::get_admin_bar_stt(),
					'suggestProductPrice' => wc_price( 20 ),
					'homeUrl'             => home_url(),
					'siteUrl'             => site_url(),
					'shopUrl'             => wc_get_endpoint_url( 'shop' ),
					'adminEmail'          => get_bloginfo( 'admin_email' ),
					'adminPhone'          => get_user_meta( get_current_user_id(), 'billing_phone', true ) ?? '202-000-0000',
					'hide_rule'           => $hide_rule,
					'accept_elements'     => $accept_elements,
				];

				foreach ( self::$img_map['social_icons'] as $type => $data ) {
					foreach ( $data as $key => $text ) {
						$url = $key ? VIWEC_IMAGES . $key . '.png' : '';

						$params['social_icons'][ $type ][] = [ 'id' => $url, 'text' => $text, 'slug' => $key ];
					}
				}
				foreach ( self::$img_map['infor_icons'] as $type => $data ) {
					foreach ( $data as $key => $text ) {
						$params['infor_icons'][ $type ][] = [ 'id' => VIWEC_IMAGES . $key . '.png', 'text' => $text, 'slug' => $key ];
					}
				}

				$params['shortcode']             = array_keys( Utils::shortcodes() );
				$params['shortcode_for_replace'] = array_merge( Utils::shortcodes(), Utils::get_register_shortcode_for_replace() );

				$params['sc_3rd_party']                 = Utils::get_register_shortcode_for_builder();
				$params['sc_3rd_party_for_text_editor'] = Utils::get_register_shortcode_for_text_editor();

				$params['post_categories']    = $this->get_categories( 'category' );
				$params['product_categories'] = $this->get_categories( 'product_cat' );

				$email_structure = ( get_post_meta( $post->ID, 'viwec_email_structure', true ) );
				if ( $email_structure ) {
					$email_structure             = html_entity_decode( $email_structure );
					$json_decode_email_structure = json_decode( $email_structure, true );

					array_walk_recursive( $json_decode_email_structure, function ( $value, $key ) {
						if ( in_array( $key, [ 'data-coupon-include-product', 'data-coupon-exclude-product' ], true ) ) {
							$value                = explode( ',', $value );
							$this->cache_products = array_merge( $this->cache_products, $value );
						}

						if ( in_array( $key, [ 'data-include-post-id', 'data-exclude-post-id' ], true ) ) {
							$value             = explode( ',', $value );
							$this->cache_posts = array_merge( $this->cache_posts, $value );
						}
					} );

					$products_temp = [ [ 'id' => '', 'text' => '' ] ];
					$posts_temp    = [];

					if ( ! empty( $this->cache_products ) ) {
						$this->cache_products = array_values( array_unique( $this->cache_products ) );

						$products = wc_get_products( [ 'limit' => - 1, 'include' => $this->cache_products ] );
						if ( ! empty( $products ) ) {
							foreach ( $products as $p ) {
								$products_temp[] = [ 'id' => (string) $p->get_id(), 'text' => $p->get_name() ];
							}
						}
					}

					if ( ! empty( $this->cache_posts ) ) {
						$this->cache_posts = array_values( array_unique( $this->cache_posts ) );

						$posts = get_posts( [ 'numberposts' => 5, 'include' => $this->cache_posts ] );
						if ( ! empty( $posts ) ) {
							foreach ( $posts as $p ) {
								$posts_temp[] = [ 'id' => $p->ID, 'text' => $p->post_title, 'content' => do_shortcode( $p->post_content ) ];
							}
						}
					}

					wp_localize_script( VIWEC_SLUG . '-email-builder', 'viWecCachePosts', $posts_temp );
					wp_localize_script( VIWEC_SLUG . '-email-builder', 'viWecCacheProducts', $products_temp );
					wp_localize_script( VIWEC_SLUG . '-email-builder', 'viWecLoadTemplate', [ $email_structure ] );
				}

				$params['i18n'] = I18n::init();

				if ( ! empty( $_GET['sample'] ) ) {
					if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
						$style            = ! empty( $_GET['style'] ) ? sanitize_text_field( wp_unslash( $_GET['style'] ) ) : 'basic';
						$params['addNew'] = [ 'type' => sanitize_text_field( wp_unslash( $_GET['sample'] ) ), 'style' => $style ];
					}
				}

				global $viwec_params;
				$viwec_params = $params;

				wp_localize_script( VIWEC_SLUG . '-inputs', 'viWecParams', $params );
				break;

			case 'edit-viwec_template':
				$styles     = [ 'manage-template' ];
				$styles_lib = [ 'form', 'segment', 'button', 'icon' ];
				Utils::enqueue_admin_styles( $styles );
				Utils::enqueue_admin_styles_libs( $styles_lib );
				break;

			//Premium
			case 'viwec_template_page_viwec-auto-update':
				$scripts = [ 'get-key' ];
				$styles  = [ 'manage-template' ];
				Utils::enqueue_admin_scripts( $scripts, [ 'jquery' ] );
				Utils::enqueue_admin_styles( $styles );
				$styles_lib = [ 'form', 'segment', 'button', 'icon' ];
				Utils::enqueue_admin_styles_libs( $styles_lib );
				break;
		}
	}

	public function get_categories( $type ) {
		$cats       = [];
		$categories = get_terms( $type, 'orderby=name&hide_empty=0' );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cats[] = [ 'id' => $cat->term_id, 'text' => $cat->name ];
			}
		}

		return $cats;
	}

	public function enqueue_run_file() {
		if ( get_current_screen()->id === 'viwec_template' ) {
			Utils::enqueue_admin_scripts( [ 'run' ], [ 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker' ] );
		}
	}

	public function admin_body_class( $class ) {
		$admin_bar = Utils::get_admin_bar_stt();
		$class     = $admin_bar ? $class : $class . ' viwec-admin-bar-hidden';

		return $class;
	}

	public function admin_footer() {
		if ( get_current_screen()->id === 'edit-viwec_template' ) {
			?>
            <div id="viwec-in-all-email-page">
				<?php do_action( 'villatheme_support_email-template-customizer-for-woo' ); ?>
            </div>
		<?php }
	}

}

Init::init();

