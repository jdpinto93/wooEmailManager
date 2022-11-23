<?php

namespace VIWEC\INC;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

	protected static $instance = null;

	public static $email_ids;

	private function __construct() {
	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function enqueue_admin_script_libs( $enqueue_list = [], $depend = [] ) {
		self::enqueue_admin_scripts( $enqueue_list, $depend, true );
	}

	public static function enqueue_admin_scripts( $enqueue_list = [], $depend = [], $libs = false ) {
		if ( is_array( $enqueue_list ) && ! empty( $enqueue_list ) ) {
			$path   = $libs ? VIWEC_JS . 'libs/' : VIWEC_JS;
			$suffix = $libs ? '.min' : '';
			foreach ( $enqueue_list as $script ) {
				wp_enqueue_script( VIWEC_SLUG . '-' . $script, $path . $script . $suffix . '.js', $depend, VIWEC_VER );
			}
		}
	}

	public static function enqueue_admin_styles_libs( $enqueue_list = [] ) {
		self::enqueue_admin_styles( $enqueue_list, true );
	}

	public static function enqueue_admin_styles( $enqueue_list = [], $libs = false ) {
		if ( is_array( $enqueue_list ) && count( $enqueue_list ) ) {
			$path   = $libs ? VIWEC_CSS . 'libs/' : VIWEC_CSS;
			$suffix = $libs ? '.min' : '';
			foreach ( $enqueue_list as $style ) {
				wp_enqueue_style( VIWEC_SLUG . '-' . $style, $path . $style . $suffix . '.css', [], VIWEC_VER );
			}
		}
	}


	public static function build_tree( $categories, $level = 0 ) {
		$cat_list = [];
		foreach ( $categories as $cat ) {
			$prefix         = str_repeat( '- ', $level );
			$cat_list[]     = [ 'id' => $cat->cat_ID, 'name' => $prefix . $cat->cat_name ];
			$sub_categories = get_term_children( $cat->cat_ID, 'product_cat' );

			if ( count( $sub_categories ) ) {
				$args       = array(
					'taxonomy'     => 'product_cat',
					'orderby'      => 'name',
					'hierarchical' => true,
					'hide_empty'   => false,
					'parent'       => $cat->cat_ID
				);
				$categories = get_categories( $args );
				$cat_list   = array_merge( $cat_list, self::build_tree( $categories, $level + 1 ) );
			}
		}

		return $cat_list;
	}

	public static function get_all_categories() {
		$args       = array(
			'taxonomy'     => 'product_cat',
			'orderby'      => 'name',
			'hierarchical' => true,
			'hide_empty'   => false,
			'parent'       => 0
		);
		$categories = get_categories( $args );

		return self:: build_tree( $categories );
	}

	public static function get_bought_ids( $line_items ) {
		$bought = [];
		foreach ( $line_items as $item ) {
			$item_data = $item->get_data();
			$p_id      = $item_data['product_id'];
			$bought[]  = $p_id;
		}

		return $bought;
	}

	public static function get_categories_from_bought_id( $bought_ids ) {
		$cat_id_filter = [];
		foreach ( $bought_ids as $id ) {
			$cats          = wc_get_product_cat_ids( $id );
			$cat_id_filter = array_merge( $cat_id_filter, $cats );
		}

		return array_unique( $cat_id_filter );
	}

	public static function get_email_ids() {
		if ( ! self::$email_ids ) {
			$emails    = wc()->mailer()->get_emails();
			$email_ids = [];
			if ( is_array( $emails ) && ! empty( $emails ) ) {
				$accept_emails = [ 'woocommerce\/templates' ];
				if ( class_exists( 'WC_Correios' ) ) {
					$accept_emails[] = 'correios_tracking';
				}

				$accept_emails = apply_filters( 'viwec_accept_email_type', $accept_emails );

				$accept_emails = implode( '|', $accept_emails );

				foreach ( $emails as $email ) {
					if ( preg_match( "/({$accept_emails})/", $email->template_base ) ) {
						$email_ids[ $email->id ] = $email->title;
					}
				}

				$email_ids['customer_partially_refunded_order'] = $email_ids['customer_refunded_order'] . ' (' . esc_html__( 'partial', 'viwec-email-template-customizer' ) . ')';
				$email_ids['customer_refunded_order']           = $email_ids['customer_refunded_order'] . ' (' . esc_html__( 'full', 'viwec-email-template-customizer' ) . ')';
				$email_ids['customer_invoice_pending']          = $email_ids['customer_invoice'] . ' (' . esc_html__( 'pending', 'viwec-email-template-customizer' ) . ')';
				$email_ids['customer_invoice']                  = $email_ids['customer_invoice'] . ' (' . esc_html__( 'paid', 'viwec-email-template-customizer' ) . ')';
			}

			asort( $email_ids );

			$email_ids            = array_reverse( $email_ids, true );
			$email_ids['default'] = esc_html__( 'Default template', 'viwec-email-template-customizer' );
			$email_ids            = array_reverse( $email_ids, true );

			self::$email_ids = $email_ids;
		}

		return array_merge( self::$email_ids, self::register_email_type() );
	}

	public static function shortcodes() {
		$date_format = wc_date_format();
		$myaccount_url = wc_get_page_permalink( 'myaccount' );
		return [
			'{admin_email}'          => get_bloginfo( 'admin_email' ),
			'{from_email}'           => sanitize_email( get_option( 'woocommerce_email_from_address' ) ),
			'{checkout_url}'         => wc_get_checkout_url(),
			'{customer_name}'        => esc_html__( 'John Doe', 'viwec-email-template-customizer' ),
			'{customer_note}'        => esc_html__( 'Customer note', 'viwec-email-template-customizer' ),
			'{coupon_expire_date}'   => date_i18n( $date_format, current_time( 'U' ) + MONTH_IN_SECONDS ),
			'{first_name}'           => esc_html__( 'John', 'viwec-email-template-customizer' ),
			'{home_url}'             => home_url(),
			'{last_name}'            => esc_html__( 'Doe', 'viwec-email-template-customizer' ),
			'{myaccount_url}'        => wc_get_page_permalink( 'myaccount' ),
			'{order_date}'           => date_i18n( $date_format, current_time( 'U' ) ),
			'{order_discount}'       => wc_price( 5 ),
			'{order_fully_refund}'   => wc_price( 0 ),
			'{order_note}'           => esc_html__( 'Order note', 'viwec-email-template-customizer' ),
			'{order_number}'         => 2158,
			'{order_partial_refund}' => wc_price( 0 ),
			'{order_received_url}'   => wc_get_endpoint_url( 'order-received', 2158, wc_get_checkout_url() ),
			'{order_shipping}'       => wc_price( 10 ),
			'{order_subtotal}'       => wc_price( 50 ),
			'{order_total}'          => wc_price( 55 ),
			'{order_tax}'            => wc_price( 5 ),
			'{payment_method}'       => esc_html__( 'Paypal', 'viwec-email-template-customizer' ),
			'{payment_url}'          => wc_get_endpoint_url( 'order-pay', 2158, wc_get_checkout_url() ) . '?pay_for_order=true&key=wc_order_6D6P8tQ0N',
			'{set_password_url}'     => wc_get_endpoint_url( 'lost-password', '?action=newaccount&key=N52psnY51Inm0yE3OdxL&login=johndoe', wc_get_page_permalink( 'myaccount' ) ),
			'{reset_password_url}'   => wc_get_endpoint_url( 'lost-password', '?key=N52psnY51Inm0yE3OdxL', wc_get_page_permalink( 'myaccount' ) ),
			'{site_title}'           => get_bloginfo( 'name' ),
			'{shipping_method}'      => esc_html__( 'Flat rate', 'viwec-email-template-customizer' ),
			'{shop_url}'             => wc_get_endpoint_url( 'shop' ),
			'{user_login}'           => 'johndoe',
			'{user_password}'        => 'KG&Q#ToW&kLq0owvLWq4Ck',
			'{user_email}'           => 'johndoe@domain.com',
			'{current_year}'         => date_i18n( 'Y', current_time( 'U' ) ),
			'{dokan_activation_link}' => $myaccount_url ?? '',
		];
	}

	public static function register_email_type() {
		$r                   = [];
		$register_email_type = self::register_3rd_email_type();
		if ( ! empty( $register_email_type ) && is_array( $register_email_type ) ) {
			foreach ( $register_email_type as $id => $data ) {
				if ( empty( $data['name'] ) ) {
					continue;
				}
				$r[ $id ] = $data['name'];
			}
		}

		return $r;
	}

	public static function get_accept_elements_data() {
		$basic_elements = apply_filters( 'viwec_register_element_for_all_email_type', [
			'layout/grid1cols',
			'layout/grid2cols',
			'layout/grid3cols',
			'layout/grid4cols',
			'html/text',
			'html/image',
			'html/button',
			'html/suggest_product',
			'html/post',
			'html/contact',
			'html/menu',
			'html/social',
			'html/divider',
			'html/spacer',
		] );

		$has_order_elements = [
			'html/order_detail',
			'html/order_subtotal',
			'html/order_total',
			'html/shipping_method',
			'html/payment_method',
			'html/order_note',
			'html/billing_address',
			'html/shipping_address',
			'html/wc_hook',
			'html/coupon',
		];

		$emails = [
			'default'                           => [ 'html/coupon', 'html/recover_heading', 'html/recover_content' ],
			'customer_new_account'              => [ 'html/coupon', ],
			'customer_reset_password'           => [ 'html/coupon', ],
			'new_order'                         => $has_order_elements,
			'cancelled_order'                   => $has_order_elements,
			'failed_order'                      => $has_order_elements,
			'customer_completed_order'          => $has_order_elements,
			'customer_invoice'                  => $has_order_elements,
			'customer_invoice_pending'          => $has_order_elements,
			'customer_note'                     => $has_order_elements,
			'customer_on_hold_order'            => $has_order_elements,
			'customer_processing_order'         => $has_order_elements,
			'customer_refunded_order'           => $has_order_elements,
			'customer_partially_refunded_order' => $has_order_elements,
		];

		foreach ( $emails as $type => $el ) {
			$emails[ $type ] = array_merge( $basic_elements, $el );
		}

		$register_email_type = self::register_3rd_email_type();
		if ( ! empty( $register_email_type ) && is_array( $register_email_type ) ) {
			foreach ( $register_email_type as $id => $data ) {
				$accept        = empty( $data['accept_elements'] ) ? [] : $data['accept_elements'];
				$emails[ $id ] = array_merge( $basic_elements, $accept );
			}
		}

		return $emails;
	}

	public static function get_hide_rules_data() {
		$r = [
			'default'                 => [ 'min_order', 'max_order', 'category', 'country' ],
			'customer_new_account'    => [ 'min_order', 'max_order', 'category' ],
			'customer_reset_password' => [ 'min_order', 'max_order', 'category' ]
		];

		$register_email_type = self::register_3rd_email_type();
		if ( ! empty( $register_email_type ) && is_array( $register_email_type ) ) {
			foreach ( $register_email_type as $id => $data ) {
				if ( empty( $data['hide_rules'] ) ) {
					continue;
				}
				$r[ $id ] = $data['hide_rules'];
			}
		}

		return $r;
	}

	public static function register_3rd_email_type() {
		return apply_filters( 'viwec_register_email_type', [] );
	}

	public static function register_shortcode_for_builder() {
		return apply_filters( 'viwec_live_edit_shortcodes', [] );
	}

	public static function get_register_shortcode_for_builder() {
		$result = [];
		$scs    = self::register_shortcode_for_builder();

		if ( ! empty( $scs ) && is_array( $scs ) ) {
			foreach ( $scs as $key => $sc ) {
				if ( ! is_array( $sc ) ) {
					continue;
				}
				$result = array_merge( $result, array_keys( $sc ) );
			}
		}

		return $result;
	}


	public static function get_register_shortcode_for_text_editor() {
		$result = [];

		$email_types = self::register_email_type();
		$scs         = self::register_shortcode_for_builder();

		if ( ! empty( $email_types ) && is_array( $email_types ) ) {
			foreach ( $email_types as $key => $name ) {
				$sc = ! empty( $scs[ $key ] ) ? $scs[ $key ] : '';
				if ( ! $sc || ! is_array( $sc ) ) {
					continue;
				}
				$menu = [];
				foreach ( $sc as $text => $value ) {
					if ( ! $text ) {
						continue;
					}
					$menu[] = [ 'text' => $text, 'value' => $text ];
				}
				$result[ $key ] = [ 'text' => $name, 'menu' => $menu ];
			}
		}

		return $result;
	}

	public static function get_register_shortcode_for_replace() {
		$result = [];

		$scs = self::register_shortcode_for_builder();
		if ( ! empty( $scs ) && is_array( $scs ) ) {
			foreach ( $scs as $key => $sc ) {
				$result = array_merge( $result, $sc );
			}
		}

		return $result;
	}

	public static function admin_email_type() {
		$emails = wc()->mailer()->get_emails();
		$r      = [];
		if ( is_array( $emails ) && ! empty( $emails ) ) {
			foreach ( $emails as $email ) {
				if ( ! empty( $email->recipient ) ) {
					$r[] = $email->id;
				}
			}
		}

		return apply_filters( 'viwec_admin_email_types', $r );
	}

	public static function get_email_ids_grouped() {
		$emails = self::get_email_ids();
		$group  = [ 'admin' => [], 'customer' => [] ];
		if ( ! empty( $emails ) ) {
			foreach ( $emails as $id => $name ) {
				if ( in_array( $id, self::admin_email_type() ) ) {
					$group['admin'][ $id ] = $name;
				} else {
					$group['customer'][ $id ] = $name;
				}
			}
		}

		return $group;
	}

	public static function get_email_recipient() {
		$emails    = wc()->mailer()->get_emails();
		$recipient = wp_list_pluck( $emails, 'recipient', 'id' );

		$recipient['customer_invoice_pending']          = '';
		$recipient['customer_partially_refunded_order'] = '';

		return $recipient;
	}

	public static function get_admin_bar_stt() {
		return get_option( 'viwec_admin_bar_stt' );
	}

	public static function default_shortcode_for_replace() {
		$shop_url      = wc_get_page_permalink( 'shop' );
		$myaccount_url = wc_get_page_permalink( 'myaccount' );
		$checkout_url  = wc_get_checkout_url();

		return [
			'{admin_email}'   => get_option( 'admin_email' ),
			'{from_email}'    => sanitize_email( get_option( 'woocommerce_email_from_address' ) ),
			'{site_title}'    => get_bloginfo( 'name' ),
			'{site_url}'      => site_url(),
			'{home_url}'      => home_url(),
			'{shop_url}'      => $shop_url ? $shop_url : home_url(),
			'{myaccount_url}' => $myaccount_url ? $myaccount_url : home_url(),
			'{checkout_url}'  => $checkout_url ? $checkout_url : home_url(),
			'{dokan_activation_link}' => $myaccount_url ?? '',

			'{order_number}'         => '',
			'{customer_name}'        => '',
			'{first_name}'           => '',
			'{last_name}'            => '',
			'{order_date}'           => '',
			'{order_subtotal}'       => '',
			'{order_total}'          => '',
			'{payment_method}'       => '',
			'{shipping_method}'      => '',
			'{order_note}'           => '',
			'{customer_note}'        => '',
			'{payment_url}'          => '',
			'{order_discount}'       => '',
			'{order_shipping}'       => '',
			'{order_received_url}'   => '',
			'{order_fully_refund}'   => '',
			'{order_partial_refund}' => '',
			'{order_tax}'            => '',

			'{user_login}'         => '',
			'{user_password}'      => '',
			'{user_email}'         => '',
			'{set_password_url}'   => '',
			'{reset_password_url}' => '',
			'{coupon_expire_date}' => '',
			'{current_year}'       => date_i18n( 'Y', current_time( 'U' ) ),
		];
	}

	public static function replace_shortcode( $html, $args, $object = '', $preview = false ) {

		$shortcodes = self::default_shortcode_for_replace();

		if ( $object ) {
			if ( is_a( $object, 'WC_Order' ) ) {

				$date_fm     = get_option( 'date_format' );
				$refunds     = $object ? $object->get_refunds() : '';
				$refund_html = '';

				if ( $refunds ) {
					foreach ( $refunds as $id => $refund ) {
						$refund_html .= '<div>' . wc_price( '-' . $refund->get_amount(), array( 'currency' => $object->get_currency() ) ) . '</div>';
					}
				}

				$payment_method = $object && $object->get_total() > 0 && $object->get_payment_method_title() && 'other' !== $object->get_payment_method_title() ? $object->get_payment_method_title() : '';

				$shortcodes['{order_number}']         = $object->get_order_number();
				$shortcodes['{customer_name}']        = $object->get_formatted_billing_full_name();
				$shortcodes['{first_name}']           = $object->get_billing_first_name();
				$shortcodes['{last_name}']            = $object->get_billing_last_name();
				$shortcodes['{user_email}']           = $object->get_billing_email();
				$shortcodes['{order_date}']           = date_i18n( $date_fm, strtotime( $object->get_date_created() ) );
				$shortcodes['{order_subtotal}']       = $object->get_subtotal_to_display();
				$shortcodes['{order_total}']          = $object->get_formatted_order_total();
				$shortcodes['{payment_method}']       = $payment_method;
				$shortcodes['{shipping_method}']      = $object->get_shipping_method();
				$shortcodes['{customer_note}']        = $object->get_customer_note();
				$shortcodes['{payment_url}']          = $object->get_checkout_payment_url();
				$shortcodes['{order_discount}']       = $object->get_discount_to_display();
				$shortcodes['{order_shipping}']       = $object->get_shipping_to_display();
				$shortcodes['{order_received_url}']   = $object->get_checkout_order_received_url();
				$shortcodes['{order_fully_refund}']   = $refund_html;
				$shortcodes['{order_partial_refund}'] = $refund_html;

				$tax = '';
				if ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) && wc_tax_enabled() ) {
					if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
						$taxes = [];
						foreach ( $object->get_tax_totals() as $code => $tax ) {
							$taxes[] = $tax->label . ' : ' . $tax->formatted_amount;
						}
						$tax = implode( ',', $taxes );
					} else {
						$tax = wc_price( $object->get_total_tax(), array( 'currency' => $object->get_currency() ) );
					}
				}
				$shortcodes['{order_tax}'] = $tax;
			}

			if ( property_exists( $object, 'object' ) && is_a( $object->object, 'WP_User' ) ) {
				$pw    = $object->user_pass ?? '';
				$as_pw = strlen( $pw ) > 3 ? substr_replace( $pw, str_repeat( "*", strlen( $pw ) - 3 ), 2, strlen( $pw ) - 3 ) : $pw;
				$user_id                    = $object->object->ID;
				$shortcodes['{user_login}']             = $object->user_login ?? '';
				$shortcodes['{user_password}']          = $pw;
				$shortcodes['{user_password_asterisk}'] = $as_pw;
				$shortcodes['{user_email}']             = $object->user_email ?? '';
				$shortcodes['{set_password_url}']       = $object->set_password_url ?? '';
				$shortcodes['{reset_password_url}']     = add_query_arg( [ 'key' => $object->reset_key ?? '', 'id' => $object->user_id ?? '' ],
					wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) );
				/*Get Dokan verification_key*/
				$verification_link ='';
				if ( function_exists( 'dokan_get_option' ) ) {
					$verification_key = get_user_meta( $user_id, '_dokan_email_verification_key', true );

					if ( in_array( 'seller', $object->object->roles ) && dokan_get_option( 'disable_welcome_wizard', 'dokan_selling' ) == 'off' ) {
						$verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user_id, 'page' => 'dokan-seller-setup' ), wc_get_page_permalink( 'myaccount' ) );
					} else {
						$verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user_id ), wc_get_page_permalink( 'myaccount' ) );
					}
				}
				$shortcodes['{dokan_activation_link}'] = $verification_link;
			}

			if ( is_a( $object, 'WP_User' ) ) {
				$pw    = $object->register_data['password'] ?? '';
				$as_pw = strlen( $pw ) > 3 ? substr_replace( $pw, str_repeat( "*", strlen( $pw ) - 3 ), 2, strlen( $pw ) - 3 ) : $pw;
				$user_id                    = $object->object->ID;
				$shortcodes['{user_login}']             = $object->user_login ?? '';
				$shortcodes['{customer_name}']          = $object->register_data['user_name'] ?? '';
				$shortcodes['{first_name}']             = $object->register_data['first_name'] ?? '';
				$shortcodes['{last_name}']              = $object->register_data['last_name'] ?? '';
				$shortcodes['{user_email}']             = $object->user_email ?? '';
				$shortcodes['{user_password_asterisk}'] = $as_pw;
				$shortcodes['{user_password}']          = $pw;
				$shortcodes['{reset_password_url}']     = $pw;
				$shortcodes['{set_password_url}']       = $pw;
				/*Get Dokan verification_key*/
				$verification_link = '';
				if ( function_exists( 'dokan_get_option' ) ) {
					$verification_key = get_user_meta( $user_id, '_dokan_email_verification_key', true );

					if ( in_array( 'seller', $object->object->roles ) && dokan_get_option( 'disable_welcome_wizard', 'dokan_selling' ) == 'off' ) {
						$verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user_id, 'page' => 'dokan-seller-setup' ), wc_get_page_permalink( 'myaccount' ) );
					} else {
						$verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user_id ), wc_get_page_permalink( 'myaccount' ) );
					}
				}
				$shortcodes['{dokan_activation_link}'] = $verification_link;
			}
		}

		if ( isset( $args['customer_note'] ) ) {
			$note = wptexturize( make_clickable( $args['customer_note'] ) );
			if ( strstr( $args['customer_note'], PHP_EOL ) ) {
				$shortcodes['{order_note}'] = wpautop( $note );
			} else {
				$shortcodes['{order_note}'] = $note;
			}
		}

		$shortcodes['{coupon_expire_date}'] = $args['coupon_expire_date'] ?? '';

		if ( $preview ) {
			$shortcodes['{user_login}']    = 'johndoe';
			$shortcodes['{user_password}'] = 'KG&Q#ToW&kLq0owvLWq4Ck';
			$shortcodes['{user_email}']    = 'johndoe@domain.com';
		}

		$custom_shortcode = $preview ? apply_filters( 'viwec_register_preview_shortcode', [], $object ) : apply_filters( 'viwec_register_replace_shortcode', [], $object, $args );

		if ( ! empty( $custom_shortcode ) && is_array( $custom_shortcode ) ) {
			foreach ( $custom_shortcode as $sc ) {
				$shortcodes = array_merge( $sc, $shortcodes );
			}
		}

		return str_replace( array_keys( $shortcodes ), array_values( $shortcodes ), $html );
	}


	public static function minify_html( $message ) {
		$replace = [
			'/\>[^\S ]+/s' => '>',     // strip whitespaces after tags, except space
			'/[^\S ]+\</s' => '<',     // strip whitespaces before tags, except space
			'/(\s)+/s'     => '\\1',         // shorten multiple whitespace sequences
//			'/<!--(.|\s)*?-->/' => '' // Remove HTML comments
		];

		return preg_replace( array_keys( $replace ), array_values( $replace ), $message );
	}
}

