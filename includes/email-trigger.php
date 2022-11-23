<?php

namespace VIWEC\INC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Trigger {

	protected static $instance = null;
	protected $template_id;
	protected $object;
	protected $use_default_temp = false;
	protected $class_email;
	protected $heading;
	protected $unique = [];
	protected $clear_css;
	protected $disable_email_template;
	public $plain_search = array(
		"/\r/",                                                  // Non-legal carriage return.
		'/&(nbsp|#0*160);/i',                                    // Non-breaking space.
		'/&(quot|rdquo|ldquo|#0*8220|#0*8221|#0*147|#0*148);/i', // Double quotes.
		'/&(apos|rsquo|lsquo|#0*8216|#0*8217);/i',               // Single quotes.
		'/&gt;/i',                                               // Greater-than.
		'/&lt;/i',                                               // Less-than.
		'/&#0*38;/i',                                            // Ampersand.
		'/&amp;/i',                                              // Ampersand.
		'/&(copy|#0*169);/i',                                    // Copyright.
		'/&(trade|#0*8482|#0*153);/i',                           // Trademark.
		'/&(reg|#0*174);/i',                                     // Registered.
		'/&(mdash|#0*151|#0*8212);/i',                           // mdash.
		'/&(ndash|minus|#0*8211|#0*8722);/i',                    // ndash.
		'/&(bull|#0*149|#0*8226);/i',                            // Bullet.
		'/&(pound|#0*163);/i',                                   // Pound sign.
		'/&(euro|#0*8364);/i',                                   // Euro sign.
		'/&(dollar|#0*36);/i',                                   // Dollar sign.
		'/&[^&\s;]+;/i',                                         // Unknown/unhandled entities.
		'/[ ]{2,}/',                                             // Runs of spaces, post-handling.
	);

	public $plain_replace = array( '', ' ', '"', "'", '>', '<', '&', '&', '(c)', '(tm)', '(R)', '--', '-', '*', '£', 'EUR', '$', '', ' ', );
	protected $fix_default_thumbnail;

	private function __construct() {
		add_filter( 'wc_get_template', array( $this, 'replace_template_path' ), 10, 5 );
		add_action( 'viwec_email_template', array( $this, 'load_template' ), 10 );
		add_action( 'woocommerce_email', array( $this, 'get_email_ids' ) );
		add_filter( 'wp_new_user_notification_email', array( $this, 'replace_wp_new_user_email' ), 1, 3 );

		add_filter( 'woocommerce_email_styles', array( $this, 'remove_style' ), 99 );
		add_filter( 'woocommerce_email_styles', array( $this, 'custom_css' ), 99999 );

		add_filter( 'woocommerce_order_item_thumbnail', [ $this, 'item_thumbnail_start' ], PHP_INT_MAX );
		add_action( 'woocommerce_order_item_meta_end', [ $this, 'item_thumbnail_end' ], PHP_INT_MAX );

		add_filter( 'woocommerce_mail_callback_params', array( $this, 'use_default_template_email' ), 999, 2 );
		add_filter( 'woocommerce_mail_callback_params', array( $this, 'reset_template_id' ), 99999 );

//		Email with wc_mail
		add_action( 'woocommerce_email_header', array( $this, 'send_email_via_wc_mailer' ), 1 );
		add_filter( 'woocommerce_email_get_option', [ $this, 'add_padding_for_addition_content' ], 10, 4 );

		add_filter( 'wp_mail', [ $this, 'minify_email_content' ] );

	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function remove_header_footer_hook( $bool ) {
		$remove = apply_filters( 'viwec_remove_origin_email_header_footer', $bool );
		if ( $remove ) {
			remove_all_actions( 'woocommerce_email_header' );
			remove_all_actions( 'woocommerce_email_footer' );
		}
	}

	public function get_email_ids( $email_object ) {
		if ( is_array( $email_object->emails ) ) {
			foreach ( $email_object->emails as $email ) {
				add_filter( 'woocommerce_email_recipient_' . $email->id, array( $this, 'trigger_recipient' ), 10, 3 );
				add_filter( 'woocommerce_email_subject_' . $email->id, array( $this, 'replace_subject' ), 10, 2 );
			}
		}

		add_filter( 'woocommerce_email_recipient_customer_partially_refunded_order', array( $this, 'trigger_recipient' ), 10, 3 );
		add_filter( 'woocommerce_email_recipient_customer_invoice_pending', array( $this, 'trigger_recipient' ), 10, 3 );
		add_filter( 'woocommerce_email_subject_customer_invoice_paid', array( $this, 'replace_subject' ), 10, 3 );
	}

	public function trigger_recipient( $recipient, $object, $class_email ) {
		$this->template_id = '';

		if ( ! $object ) {
			return $recipient;
		}

		$status_options = get_option( 'viwec_emails_status', [] );
		if ( ! empty( $status_options[ $class_email->id ] ) && $status_options[ $class_email->id ] == 'disable' ) {
			$this->disable_email_template = true;

			return $recipient;
		}

		if ( is_a( $object, 'WC_Order' ) ) {
			$this->template_id = $this->get_template_id_has_order( $class_email->id, $object );
		} else {
			$this->template_id = $this->get_template_id_no_order( $class_email->id );
		}

		if ( ! $this->template_id ) {
			$this->use_default_temp = $this->get_default_template();
			$this->remove_header_footer_hook( $this->use_default_temp );
			add_filter( 'woocommerce_email_order_items_args', [ $this, 'show_image' ] );
		}

		$this->clear_css   = $this->template_id || $this->use_default_temp ? true : false;
		$this->object      = $object;
		$this->class_email = $class_email;

		return $recipient;
	}

	public function show_image( $args ) {
		if ( $this->use_default_temp ) {
			$show_image         = get_post_meta( $this->use_default_temp, 'viwec_enable_img_for_default_template', true );
			$args['show_image'] = $show_image ? true : false;

			$size               = get_post_meta( $this->use_default_temp, 'viwec_img_size_for_default_template', true );
			$args['image_size'] = $size ? [ (int) $size, 300 ] : [ 80, 80 ];
		}

		return $args;
	}

	public function replace_subject( $subject, $object ) {
		if ( $this->template_id ) {
			$_subject = get_post( $this->template_id )->post_title;
			$subject  = $_subject ? $_subject : $subject;
			$subject  = Utils::replace_shortcode( $subject, [], $object );
			$subject  = preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $subject ) );
			$subject  = htmlspecialchars_decode( $subject );
		}

		return $subject;
	}

	public function replace_template_path( $located, $template_name, $args, $template_path, $default_path ) {
		if ( ! $this->template_id ) {
			return $located;
		}

		if ( $template_name == 'emails/email-addresses.php' ) {
			return VIWEC_TEMPLATES . 'empty-file.php';
		}

		if ( $template_name == 'emails/email-styles.php' ) {
			return VIWEC_TEMPLATES . 'empty-file.php';
		}

		if ( ! empty( $args['email']->id ) && in_array( $args['email']->id, $this->unique ) ) {
			return $located;
		}

		if ( ! isset( $args['email'] ) && isset( $args['order'] ) && isset( $args['email_heading'] ) ) {
			$WC_mailer = WC()->mailer();
			if ( isset( $WC_mailer->emails ) ) {
				foreach ( $WC_mailer->emails as $mailer ) {
					if ( ! empty( $mailer->object ) && $args['email_heading'] == $mailer->heading ) {
						$args['email'] = $mailer;
						break;
					} else if ( isset( $mailer->settings ) && ! empty( $mailer->settings ) ) {
						$settings = $mailer->settings;
						if ( isset( $settings['heading'] ) && $args['email_heading'] == $settings['heading'] ) {
							$args['email'] = $mailer;
							break;
						}
					}
				}
			}
		}

		if ( isset( $args['email'] ) && ! empty( $args['email']->id ) ) {
			if ( $args['plain_text'] ) {
				return $located;
			}
			if ( $this->template_id ) {
				$this->unique[] = $args['email']->id;
				$located        = VIWEC_TEMPLATES . 'email-template.php';
			}
		}

		return $located;
	}

	public function load_template( $args ) {
		if ( ! $this->template_id ) {
			return;
		}

		$email_render = Email_Render::init( [ 'template_id' => $this->template_id ] );
		$email_render->set_object( $args['email'] );
		$email_render->template_args = $args;

		$data = get_post_meta( $this->template_id, 'viwec_email_structure', true );
		$data = json_decode( html_entity_decode( $data ), true );
		$email_render->render( $data );
	}

	public function get_template_id_has_order( $type, $order ) {
		$country_code   = function_exists( 'icl_get_languages' ) ? get_post_meta( $order->get_id(), 'wpml_language', true ) : $order->get_billing_country();
		$line_items     = $order->get_items( 'line_item' );
		$order_subtotal = $order->get_subtotal();
		$bought_ids     = Utils::get_bought_ids( $line_items );
		$categories     = Utils::get_categories_from_bought_id( $bought_ids );

		if ( $type == 'customer_invoice' && $order->get_status() == 'pending' ) {
			$type = 'customer_invoice_pending';
		}

		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => $type,
		];

		$posts = get_posts( $args );

		$filter_ids = [];

		foreach ( $posts as $post ) {
			$rules           = get_post_meta( $post->ID, 'viwec_setting_rules', true );
			$rule_countries  = $rules['countries'] ?? '';
			$rule_categories = $rules['categories'] ?? '';
			$rule_languages  = $rules['languages'] ?? '';
			$min_subtotal    = $rules['min_subtotal'] ?? '';
			$max_subtotal    = $rules['max_subtotal'] ?? '';

			$rule_countries = function_exists( 'icl_get_languages' ) ? $rule_languages : $rule_countries;

			if ( empty( $rule_countries ) || ( ! empty( $rule_countries ) && is_array( $rule_countries ) && in_array( $country_code, $rule_countries ) ) ) {
				if ( empty( $rule_categories ) || count( array_intersect( $categories, $rule_categories ) ) ) {
					if ( ! $max_subtotal ) {
						if ( $order_subtotal >= (float) $min_subtotal ) {
							$filter_ids[] = $post->ID;
						}
					} else {
						if ( $order_subtotal >= (float) $min_subtotal && $order_subtotal < (float) $max_subtotal ) {
							$filter_ids[] = $post->ID;
						}
					}
				}
			}
		}

		return current( $filter_ids );
	}

	public function get_template_id_no_order( $type ) {

		if ( ! empty( $_POST['billing_country'] ) ) {
			$country_code = sanitize_text_field( wp_unslash( $_POST['billing_country'] ) );
		} else {
			$locate       = \WC_Geolocation::geolocate_ip();
			$country_code = $locate['country'];
		}

		$temp_id = '';

		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => $type,
		];

		$posts = get_posts( $args );

		if ( $country_code ) {
			foreach ( $posts as $post ) {
				$list_countries = get_post_meta( $post->ID, 'viwec_settings_countries', true );
				if ( is_array( $list_countries ) && in_array( $country_code, $list_countries ) ) {
					$temp_id = $post->ID;
					break;
				} elseif ( empty( $list_countries ) ) {
					$temp_id = $post->ID;
					break;
				}
			}
		} else {
			$post    = current( $posts );
			$temp_id = $post->ID;
		}

		return $temp_id;
	}

	public function get_default_template() {
		$id   = '';
		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => 'default',
		];

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$ids = wp_list_pluck( $posts, 'ID' );
			$id  = current( $ids );
		}

		return $id;
	}

	public function replace_wp_new_user_email( $wp_new_user_notification_email, $user, $blogname ) {
		$this->template_id = $this->get_template_id_no_order( 'customer_new_account' );

		if ( $this->template_id ) {

			$register_data = [];
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'uael_register_user' && isset( $_POST['data'] ) ) {
				$data = wc_clean( wp_unslash( $_POST['data'] ) );
			} else {
				$data = wc_clean( $_POST );
			}

			if ( isset( $data['user_name'] ) ) {
				$register_data['user_name'] = wp_unslash( sanitize_text_field( $data['user_name'] ) );
			}

			if ( ! empty( $data['first_name'] ) ) {
				$register_data['first_name'] = sanitize_text_field( $data['first_name'] );
			}

			if ( ! empty( $data['last_name'] ) ) {
				$register_data['last_name'] = sanitize_text_field( $data['last_name'] );
			}

			if ( ! empty( $data['password'] ) ) {
				$register_data['password'] = wp_unslash( sanitize_text_field( $data['password'] ) );
			} else {
				$key                       = get_password_reset_key( $user );
				$register_data['password'] = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' );
			}

			$user->register_data = $register_data;

			$subject = get_post( $this->template_id )->post_title;
			if ( $subject ) {
				$wp_new_user_notification_email['subject'] = Utils::replace_shortcode( $subject, '', $user );
			}

			$email_render = Email_Render::init();
//			$email_render = new Email_Render();
			$email_render->set_user( $user );

			$data = get_post_meta( $this->template_id, 'viwec_email_structure', true );
			$data = json_decode( html_entity_decode( $data ), true );
			ob_start();
			$email_render->render( $data );
			$email_body = ob_get_clean();

			if ( $email_body ) {
				$wp_new_user_notification_email['message'] = $email_body;
			}

			$wp_new_user_notification_email['headers'] = [ "Content-Type: text/html" ];
		}

		return $wp_new_user_notification_email;
	}

	public function remove_style( $style ) {
		return $this->clear_css ? '' : $style;
	}

	public function custom_css( $style ) {
		if ( $this->use_default_temp || $this->template_id ) {
			$id    = $this->template_id ? $this->template_id : $this->use_default_temp;
			$style .= get_post_meta( $id, 'viwec_custom_css', true );
		}

		return $style;
	}

	public function use_default_template_email( $args, $class_email ) {

		if ( $this->use_default_temp && ! $this->template_id ) {
			$email_render = Email_Render::init();
			if ( ! $email_render->check_rendered ) {
				$email_render->set_object( $class_email );
				$email_render->other_message_content = $args[2];
				$email_render->class_email           = $this->class_email;
				$email_render->use_default_template  = true;
				$email_render->recover_heading       = $this->heading;
				$data                                = get_post_meta( $this->use_default_temp, 'viwec_email_structure', true );
				$data                                = json_decode( html_entity_decode( $data ), true );

				ob_start();
				$email_render->render( $data );
				$message = ob_get_clean();
				$message = $class_email->style_inline( $message );
				$args[2] = $message;
				remove_filter( 'woocommerce_order_item_thumbnail', [ $this, 'item_thumbnail_start' ] );
				remove_action( 'woocommerce_order_item_name', [ $this, 'item_thumbnail_end' ] );
			}
		}

		return $args;
	}

	public function item_thumbnail_start( $image ) {
		$this->fix_default_thumbnail = true;

		return '<table><tr><td valign="middle" style="vertical-align: middle;border: none;"> ' . $image . '</td><td valign="middle" style="vertical-align: middle;border: none;">';
	}

	public function item_thumbnail_end() {
		if ( $this->fix_default_thumbnail ) {
			?>
            </td>
            </tr>
            </table>
			<?php
			$this->fix_default_thumbnail = false;
		}
	}

	public function reset_template_id( $wp_mail ) {
		$this->use_default_temp       = '';
		$this->template_id            = '';
		$this->disable_email_template = '';
		$this->unique                 = [];
		$email_render                 = Email_Render::init();
		$email_render->check_rendered = false;

		return $wp_mail;
	}

	public function send_email_via_wc_mailer( $heading ) {
		if ( $this->disable_email_template ) {
			return;
		}
		$this->heading          = $heading;
		$this->use_default_temp = $this->get_default_template();
		$this->remove_header_footer_hook( $this->use_default_temp );
	}

	public function add_padding_for_addition_content( $value, $_this, $_value, $key ) {
		if ( $this->use_default_temp ) {
			$value = $key == 'additional_content' ? "<div style='padding-top: 20px;'>{$value}</div>" : $value;
		}

		return $value;
	}

	public function minify_email_content( $args ) {
		$message         = $args['message'];
		$args['message'] = Utils::minify_html( $message );

		return $args;
	}
}

