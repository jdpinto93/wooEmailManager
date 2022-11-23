<?php

namespace VIWEC\INC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Render {

	protected static $instance = null;
	public $preview;
	public $demo;
	public $sent_to_admin;
	public $render_data = [];
	public $plain_text;
	public $template_args;
	public $order;
	public $other_message_content;
	public $class_email;
	public $use_default_template;
	public $recover_heading;
	public $custom_css;
	public $check_rendered;
	protected $direction;
	protected $template_id;
	protected $props;
	protected $order_currency;
	protected $user;
	protected $email_id;
	protected $font_family_default = "Roboto, RobotoDraft, Helvetica, Arial, sans-serif";

	public function __construct( $data ) {
		$data = wp_parse_args( $data, [ 'template_id' => '' ] );

		$this->template_id = $data['template_id'];

		add_action( 'viwec_render_content', [ $this, 'render_content' ], 10, 2 );
		add_filter( 'gettext', [ $this, 'recover_text' ], 10, 3 );
		add_action( 'viwec_order_item_parts', [ $this, 'order_download' ], 10, 3 );
		add_filter( 'woocommerce_order_shipping_to_display_shipped_via', [ $this, 'remove_shipping_method' ] );

		add_filter( 'woocommerce_email_styles', array( $this, 'custom_style' ), 9999 );
	}

	public static function init( $data = [] ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $data );
		}

		return self::$instance;
	}

	public function recover_text( $translation, $text, $domain ) {
		if ( in_array( $text, [ 'Order fully refunded.', 'Refund' ] ) ) {
			$translation = $text;
		}

		return $translation;
	}

	public function set_object( $email ) {
		$this->email_id = $email->id;
		$object         = $email->object;
		if ( is_a( $object, 'WC_Order' ) ) {
			$this->order          = $object;
			$this->order_currency = $this->order->get_currency();
		} elseif ( is_a( $object, 'WP_User' ) ) {
			$this->user = $email;
		}
	}

	public function set_user( $user ) {
		$this->user = $user;
	}

	public function order( $order_id ) {
		if ( $order_id ) {
			$this->order = wc_get_order( $order_id );
			if ( $this->order ) {
				$this->order_currency = $this->order->get_currency();
			}
		}
	}

	public function demo_order() {
		$this->demo = true;

		$order = new \WC_Order();
		$order->set_id( 123456 );
		$order->set_billing_first_name( 'John' );
		$order->set_billing_last_name( 'Doe' );
		$order->set_billing_email( 'johndoe@domain.com' );
		$order->set_billing_country( 'US' );
		$order->set_billing_city( 'Azusa' );
		$order->set_billing_state( 'NY' );
		$order->set_payment_method( 'paypal' );
		$order->set_payment_method_title( 'Paypal' );
		$order->set_billing_postcode( 10001 );
		$order->set_billing_phone( '0123456789' );
		$order->set_billing_address_1( 'Ap #867-859 Sit Rd.' );
		$order->set_shipping_total( 10 );
		$order->set_total( 60 );
		$this->order = $order;
	}

	public function demo_new_user() {
		$user             = new \WP_User();
		$user->user_login = 'johndoe';
		$user->user_pass  = '$P$BKpFUPNogZw6kAv/dMrk6CjSmlFI8l0';
		$this->user       = $user;
	}

	public function parse_styles( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $data;
		}

		$style      = '';
		$ignore_arr = [
			'border-top-width',
			'border-right-width',
			'border-bottom-width',
			'border-left-width',
			'border-style',
			'border-color',
			'border-top-color',
			'border-width',
		];
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $ignore_arr ) ) {
				continue;
			}

			$style .= "{$key}:{$value};";
		}

		$border_style = $data['border-style'] ?? 'solid';
		$border_color = $data['border-color'] ?? ( $data['border-top-color'] ?? 'transparent' );

		if ( isset( $data['border-top-width'] ) ) {
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $pos ) {
				$style .= ! isset( $data["border-{$pos}-width"] ) || $data["border-{$pos}-width"] === '0px'
					? "border-{$pos}:0 hidden;"
					: "border-{$pos}:{$data["border-{$pos}-width"]} {$border_style} {$border_color};";
			}
		} elseif ( isset( $data['border-width'] ) ) {
			$border_width = $data['border-width'];
			if ( $border_width !== '0px' ) {
				$style .= " border-width: {$border_width}; ";
				$style .= " border-style: {$border_style}; ";
				$style .= " border-color: {$border_color}; ";
			} else {
				$style .= " border:0 hidden; ";
			}
		}

		return esc_attr( $style );
	}

	public function replace_template( $located, $template_name ) {
		if ( $template_name == 'emails/email-styles.php' ) {
			$located = VIWEC_TEMPLATES . 'email-style.php';
		}

		return $located;
	}

	public function render( $data ) {
		$this->check_rendered = true;

		$bg_style   = '';
		$width      = 600;
		$responsive = 380;

		if ( isset( $data['style_container'] ) ) {
			$width      = ! empty( $data['style_container']['width'] ) ? $data['style_container']['width'] : $width;
			$responsive = ! empty( $data['style_container']['responsive'] ) ? $data['style_container']['responsive'] : $responsive;
			unset( $data['style_container']['width'] );
			unset( $data['style_container']['responsive'] );
			$bg_style = isset( $data['style_container'] ) ? $this->parse_styles( $data['style_container'] ) : '';
		}

		$this->direction = get_post_meta( $this->template_id, 'viwec_settings_direction', true );

		if ( $this->preview ) {
			$this->direction = isset( $_POST['direction'] ) ? sanitize_text_field( wp_unslash( $_POST['direction'] ) ) : 'ltr';
		}

		$this->email_header( $bg_style, $width, $responsive );

		$left_align = $this->direction == 'rtl' ? 'right' : 'left';
		?>
        <table align='center' width='600' border='0' cellpadding='0' cellspacing='0'>
			<?php
			if ( ! empty( $data['rows'] ) && is_array( $data['rows'] ) ) {
				foreach ( $data['rows'] as $row ) {
					if ( ! empty( $row ) && is_array( $row ) ) {
						$row_outer_style = ! empty( $row['props']['style_outer'] ) ? $this->parse_styles( $row['props']['style_outer'] ) : '';
						?>
                        <tr>
                            <td class="viwec-row" valign='top' width='100%' style='<?php echo esc_attr( $row_outer_style ) ?>'>
                                <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse;margin: 0; padding:0'>
                                    <tr>
                                        <td valign='top' width='100%' class='viwec-responsive-padding viwec-inline-block' border='0' cellpadding='0' cellspacing='0'
                                            style='width: 100%; font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif;font-size: 0 !important;border-collapse: collapse;margin: 0; padding:0; '>

											<?php
											$end_array = array_keys( $row );
											$end_array = end( $end_array );

											if ( ! empty( $row['cols'] && is_array( $row['cols'] ) ) ) {
												$arr_key    = array_keys( $row['cols'] );
												$start      = current( $arr_key );
												$end        = end( $arr_key );
												$col_number = count( $row['cols'] );

												$width = ( 100 / $col_number ) . '%';

												foreach ( $row['cols'] as $key => $col ) {
													$col_style = ! empty( $col['props']['style'] ) ? $this->parse_styles( $col['props']['style'] ) : '';

													if ( $start == $key ) { ?>
                                                        <!--[if mso | IE]>
                                                        <table width="100%" role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td valign='top' class="" style="vertical-align:top;width:<?php echo esc_attr( $width ) ?>;"><![endif]-->
													<?php } ?>

                                                    <table align="<?php echo esc_attr( $left_align ) ?>" width="<?php echo esc_attr( $width ) ?>" class='viwec-responsive' border="0" cellpadding="0" cellspacing="0"
                                                           style='margin:0; padding:0;border-collapse: collapse;'>
                                                        <tr>
                                                            <td>
                                                                <table width='100%' align='left' border='0' cellpadding='0' cellspacing='0'
                                                                       style='margin:0; padding:0;border-collapse: collapse;width: 100%'>
                                                                    <tr>
                                                                        <td class="viwec-column" valign='top' width='100%' style='line-height: 1.5;<?php echo esc_attr( $col_style ) ?>'>
																			<?php
																			if ( ! empty( $col['elements'] && is_array( $col['elements'] ) ) ) {
																				foreach ( $col['elements'] as $el ) {
																					$type = isset( $el['type'] ) ? str_replace( '/', '_', $el['type'] ) : '';

																					if ( $type == 'html_recover_heading' && $this->use_default_template && ! $this->recover_heading && ! $this->class_email ) {
																						continue;
																					}

																					$content_style = isset( $el['style'] ) ? $this->parse_styles( str_replace( "'", '', $el['style'] ) ) : '';
																					$el_style      = ! empty( $el['props']['style'] ) ? $this->parse_styles( str_replace( "'", '', $el['props']['style'] ) ) : '';

																					?>
                                                                                    <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0'
                                                                                           style='border-collapse: separate;'>
                                                                                        <tr>
                                                                                            <td valign='top' style='<?php echo esc_attr( $el_style ); ?>'>
                                                                                                <table class="<?php echo esc_attr( $type ) ?>" align='center' width='100%' border='0' cellpadding='0'
                                                                                                       cellspacing='0' style='border-collapse: separate;'>
                                                                                                    <tr>
                                                                                                        <td valign='top' dir="<?php echo esc_attr( $this->direction ) ?>"
                                                                                                            style='font-size: 15px;<?php echo esc_attr( $content_style ) ?>'>
																											<?php
																											$this->props = $el;
																											do_action( 'viwec_render_content', $type, $el, $this ); ?>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            </td>
                                                                                        </tr>
                                                                                    </table>
																					<?php
																				}
																			}
																			?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
													<?php
													if ( $end == $key ) {
														?>
                                                        <!--[if mso | IE]></td></tr></table><![endif]-->
														<?php
													} else {
														?>
                                                        <!--[if mso | IE]></td>
                                                        <td valign='top' style="vertical-align:top;width:<?php echo esc_attr( $width ) ?>;">
                                                        <![endif]-->
														<?php
													}
												}
											} ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
					<?php }
				}
			} ?>
        </table>
		<?php
		$this->email_footer();
	}

	public function email_header( $bg_style, $width, $responsive ) {
		wc_get_template( 'email-header.php',
			[ 'bg_style' => $bg_style, 'width' => $width, 'responsive' => $responsive, 'direction' => $this->direction ],
			'',
			VIWEC_TEMPLATES );
	}

	public function email_footer() {
		?>
        </td></tr></tbody></table></td></tr></tbody></table></div></body></html>
		<?php
	}

	public function render_content( $type, $props ) {
		$func = 'render_' . $type;
		if ( method_exists( $this, $func ) ) {
			$this->$func( $props );
		}
	}

	public function custom_style() {
		return $this->custom_css ? $this->custom_css : '';
	}

	public function replace_shortcode( $text ) {
		$object = '';

		if ( $this->order ) {
			$object = $this->order;
		} elseif ( $this->user ) {
			$object = $this->user;
		}

		$text = Utils::replace_shortcode( $text, $this->template_args, $object, $this->preview );

		return $text;
	}

	public function render_html_image( $props ) {
		$src      = isset( $props['attrs']['src'] ) ? $props['attrs']['src'] : '';
		$width    = isset( $props['childStyle']['img'] ) ? $this->parse_styles( $props['childStyle']['img'] ) : '';
		$ol_width = ! empty( $props['childStyle']['img']['width'] ) ? str_replace( 'px', '', $props['childStyle']['img']['width'] ) : '100%';
		$href     = ! empty( $props['attrs']['data-href'] ) ? $props['attrs']['data-href'] : '#';
		$alt      = ! empty( $props['attrs']['data-alt'] ) ? $props['attrs']['data-alt'] : '';
		?>
        <a href="<?php echo esc_attr( $href ) ?>" target="_blank">
            <img alt="<?php echo esc_attr( $alt ); ?>"
                 width="<?php echo esc_attr( $ol_width ) ?>"
                 src='<?php echo esc_url( $src ) ?>' max-width='100%'
                 style='max-width: 100%;vertical-align: middle;<?php echo esc_attr( $width ) ?>'/>
        </a>
		<?php
	}

	public function render_html_text( $props ) {
		$content = isset( $props['content']['text'] ) ? $props['content']['text'] : '';
		$content = $this->replace_shortcode( $content );
		echo wp_kses( do_shortcode( $content ), viwec_allowed_html() );
	}

	public function render_html_order_detail( $props ) {
		if ( $this->order ) {
			$temp    = ! empty( $props['attrs']['data-template'] ) ? $props['attrs']['data-template'] : 1;
			$preview = $this->demo ? 'pre-' : '';

			if ( $preview ) {
				$this->direction = isset( $_POST['direction'] ) ? sanitize_text_field( wp_unslash( $_POST['direction'] ) ) : 'ltr';
			}

			if ( is_file( VIWEC_TEMPLATES . "order-items/{$preview}style-{$temp}.php" ) ) {
				?>
                <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
                    <tr>
                        <td valign='top'>
							<?php
							$sent_to_admin = $this->template_args['sent_to_admin'] ?? '';
							wc_get_template( "order-items/{$preview}style-{$temp}.php", [
								'order'               => $this->order,
								'items'               => $this->order->get_items(),
								'show_sku'            => $sent_to_admin,
								'show_download_links' => $this->order->is_download_permitted() && ! $sent_to_admin,
								'show_purchase_note'  => $this->order->is_paid() && ! $sent_to_admin,
								'props'               => $props,
								'render'              => $this,
								'direction'           => $this->direction
							], 'email-template-customizer', VIWEC_TEMPLATES );
							?>
                        </td>
                    </tr>
                </table>
				<?php
			}
		}
	}

	public function get_p_inherit_style( $props ) {
		$inherit_style = ! empty( $props['style'] ) ? $props['style'] : [];
		$font_weight   = $inherit_style['font-weight'] ?? 'inherit';
		$font_size     = $inherit_style['font-size'] ?? 'inherit';
		$line_height   = $inherit_style['line-height'] ?? 'inherit';
		$color         = $inherit_style['color'] ?? 'inherit';

		$p_style = [
			"font-weight:{$font_weight}",
			"font-size:{$font_size}",
			"line-height:{$line_height}",
			"color:{$color}",
		];

		return implode( ';', $p_style );
	}

	public function render_html_order_subtotal( $props ) {
		$html = '';
		if ( $this->order ) {

			$left_style  = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style    = isset( $props['childStyle']['.viwec-order-subtotal-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-subtotal-style'] ) : '';

			$p_style = $this->get_p_inherit_style( $props );

			$item_totals = $this->order->get_order_item_totals();
			if ( ! empty( $item_totals ) && is_array( $item_totals ) ) {
				foreach ( $item_totals as $id => $item ) {
					switch ( $id ) {
						case 'cart_subtotal':
							$id = 'subtotal';
							break;
						default:
							break;
					}

					$label = str_replace( ':', '', $item['label'] );

					if ( $label == 'Order fully refunded.' && ! empty( $props['content']['refund-full'] ) ) {
						$label = $props['content']['refund-full'];
					}

					if ( $label == 'Refund' && ! empty( $props['content']['refund-part'] ) ) {
						$label = $props['content']['refund-part'];
					}

					if ( in_array( $id, [ 'order_total', 'payment_method' ] ) ) {
						continue;
					}

					$text = $props['content'][ $id ] ?? $label;

					$html .= "<tr>";
					$html .= "<td valign='top'  class='viwec-mobile-50' style='{$el_style}{$left_style}'><p style='{$p_style}'>{$text}</p></td>";
					$html .= "<td valign='top'  class='viwec-mobile-50' style='{$el_style}{$right_style}'><p style='{$p_style}'>{$item['value']}</p></td>";
					$html .= "</tr>";
				}
			}
		}

		$this->table( $html );
	}

	public function render_html_order_total( $props ) {
		$html = '';
		if ( $this->order ) {
			$left_style  = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style    = isset( $props['childStyle']['.viwec-order-total-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-total-style'] ) : '';

			$trans_total = $props['content']['order_total'] ?? esc_html__( 'Total', 'viwec-email-template-customizer' );

			$p_style = $this->get_p_inherit_style( $props );

			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			$total_html  = "<tr><td valign='top' class='viwec-mobile-50' style='{$el_style}{$left_style}{$p_style}'><p style='{$p_style}'>{$trans_total}</p></td>";
			$total_html  .= "<td valign='top' class='viwec-mobile-50' style='{$el_style}{$right_style}'><p style='{$p_style}'>{$this->order->get_formatted_order_total($tax_display)}</p></td></tr>";

			$html .= $total_html;
		}
		$this->table( $html );
	}

	public function render_html_order_note( $props ) {
		if ( $this->order && $this->order->get_customer_note() ) {
			$html        = '';
			$left_style  = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style    = isset( $props['childStyle']['.viwec-order-total-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-total-style'] ) : '';

			$p_style = $this->get_p_inherit_style( $props );

			$trans_note = $props['content']['order_note'] ?? 'Note';
			$note_html  = "<tr><td valign='top' class='viwec-mobile-50' style='{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_note}</p></td>";
			$note_html  .= "<td valign='top' class='viwec-mobile-50' style='{$el_style}{$right_style}'><p style='{$p_style}'>{$this->order->get_customer_note()}</p></td></tr>";

			$html .= $note_html;
			$this->table( $html );
		}
	}

	public function render_html_shipping_method( $props ) {
		if ( $this->order ) {
			if ( $shipping_method = $this->order->get_shipping_method() ?? '' ) {
				$shipping_method_html = '';
				$left_style           = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
				$right_style          = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
				$el_style             = isset( $props['childStyle']['.viwec-shipping-method-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-shipping-method-style'] ) : '';

				$p_style = $this->get_p_inherit_style( $props );

				$trans_shipping_method = $props['content']['shipping_method'] ?? esc_html__( 'Shipping method', 'viwec-email-template-customizer' );
				$shipping_method_html  .= "<tr><td  valign='top' class='viwec-mobile-50' style='{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_shipping_method}</p></td>";
				$shipping_method_html  .= "<td  valign='top' class='viwec-mobile-50' style='{$el_style}{$right_style}'><p style='{$p_style}'>{$shipping_method}</p></td></tr>";
				$this->table( $shipping_method_html );
			}
		}
	}

	public function render_html_payment_method( $props ) {
		$html = '';
		if ( $this->order ) {
			$payment_method_html = '';
			$left_style          = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style         = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style            = isset( $props['childStyle']['.viwec-payment-method-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-payment-method-style'] ) : '';

			$payment_method = $this->order->get_total() > 0 && $this->order->get_payment_method_title() && 'other' !== $this->order->get_payment_method_title() ? $this->order->get_payment_method_title() : '';

			$p_style = $this->get_p_inherit_style( $props );

			$trans_payment_method = $props['content']['payment_method'] ?? esc_html__( 'Payment method', 'viwec-email-template-customizer' );
			if ( $payment_method ) {
				$payment_method_html = "<tr><td  valign='top' class='viwec-mobile-50' style='{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_payment_method}</p></td>";
				$payment_method_html .= "<td  valign='top' class='viwec-mobile-50' style='{$el_style}{$right_style}'><p style='{$p_style}'>{$payment_method}</p></td></tr>";
			}
			$html .= $payment_method_html;
		}
		$this->table( $html );
	}

	public function render_html_billing_address( $props ) {

		if ( ! $this->order ) {
			return;
		}

		if ( $this->preview ) {
			$this->render_html_billing_address_via_hook( '', '', '', '', $props );
		} else {
			add_action( 'woocommerce_email_customer_details', [ $this, 'render_html_billing_address_via_hook' ], 20, 5 );
			$args = $this->template_args;
			do_action( 'woocommerce_email_customer_details', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'], $props );
			remove_action( 'woocommerce_email_customer_details', [ $this, 'render_html_billing_address_via_hook' ], 20 );
		}

	}

	public function render_html_billing_address_via_hook( $order, $sent_to_admin, $plain_text, $email, $props ) {
		if ( $props['type'] !== 'html/billing_address' ) {
			return;
		}
		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';

		$billing_address = $this->order->get_formatted_billing_address();
		$billing_address = str_replace( '<br/>', "</td></tr><tr><td  valign='top' style='color: {$color}; font-weight: {$font_weight};'>", $billing_address );
		$billing_email   = $billing_phone = '';
		if ( $phone = $this->order->get_billing_phone() ) {
			$billing_phone = "<tr><td valign='top' ><a href='tel:$phone' style='color: {$color}; font-weight: {$font_weight};'>$phone</td></tr>";
		}

		if ( $this->order->get_billing_email() ) {
			$billing_email = "<tr><td valign='top' ><a style='color:{$color}; font-weight: {$font_weight};' href='mailto:{$this->order->get_billing_email()}'>{$this->order->get_billing_email()}</a></td></tr>";
		}

		$html = "<tr><td  valign='top'  style='color: {$color}; font-weight: {$font_weight};'>{$billing_address}</td></tr>{$billing_phone}{$billing_email}";
		$this->table( $html );
	}

	public function render_html_shipping_address( $props ) {
		if ( ! $this->order ) {
			return;
		}

		if ( $this->preview ) {
			$this->render_html_shipping_address_via_hook( '', '', '', '', $props );
		} else {
			add_action( 'woocommerce_email_customer_details', [ $this, 'render_html_shipping_address_via_hook' ], 20, 5 );
			$args = $this->template_args;
			do_action( 'woocommerce_email_customer_details', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'], $props );
			remove_action( 'woocommerce_email_customer_details', [ $this, 'render_html_shipping_address_via_hook' ], 20 );
		}
	}

	public function render_html_shipping_address_via_hook( $order, $sent_to_admin, $plain_text, $email, $props ) {
		if ( $props['type'] !== 'html/shipping_address' ) {
			return;
		}
		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';

		$shipping_address = $this->order->get_formatted_shipping_address();
		$shipping_address = empty( $shipping_address ) ? $this->order->get_formatted_billing_address() : $shipping_address;
		$shipping_address = str_replace( '<br/>', "</td></tr><tr><td valign='top' style='color: {$color}; font-weight: {$font_weight};'>", $shipping_address );

		$html = "<tr><td valign='top' style='color: {$color}; font-weight: {$font_weight};'>{$shipping_address}</td></tr>";
		$this->table( $html );
	}

	public function render_html_social( $props ) {
		$align   = $props['style']['text-align'] ?? 'left';
		$socials = [ 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'whatsapp' ];
		$html    = '';
		$width   = ! empty( $props['attrs']['data-width'] ) ? $props['attrs']['data-width'] : 32;

		if ( isset( $props['attrs']['direction'] ) && $props['attrs']['direction'] === 'vertical' ) {
			foreach ( $socials as $social ) {
				$link = isset( $props['attrs'][ $social . '_url' ] ) ? esc_url( $props['attrs'][ $social . '_url' ] ) : '';
				$img  = isset( $props['attrs'][ $social ] ) ? esc_url( $props['attrs'][ $social ] ) : '';
				if ( ! empty( $img ) && ! empty( $link ) ) {
					$html .= "<tr><td valign='top' ><a href='{$link}'><img style='vertical-align: middle' src='{$img}' width='{$width}'></a></td></tr>";
				}
			}
		} else {
			$html = '<tr>';
			foreach ( $socials as $social ) {
				$link = isset( $props['attrs'][ $social . '_url' ] ) ? esc_url( $props['attrs'][ $social . '_url' ] ) : '';
				$img  = isset( $props['attrs'][ $social ] ) ? esc_url( $props['attrs'][ $social ] ) : '';
				if ( ! empty( $img ) && ! empty( $link ) ) {
					$html .= "<td valign='top' style='padding: 0;'><a href='{$link}'><img src='{$img}' width='{$width}'></a></td>";
				}
			}
			$html .= '</tr>';
		}

		$html = "<table align='{$align}' border='0' cellpadding='0' cellspacing='0' >$html</table>";
		echo wp_kses( $html, viwec_allowed_html() );
	}

	public function render_html_button( $props ) {
		$url           = isset( $props['attrs']['href'] ) ? $this->replace_shortcode( $props['attrs']['href'] ) : '';
		$text          = isset( $props['content']['text'] ) ? $this->replace_shortcode( $props['content']['text'] ) : '';
		$text          = str_replace( [ '<p>', '</p>' ], [ '', '' ], $text );
		$align         = $props['style']['text-align'] ?? 'left';
		$style         = isset( $props['childStyle']['a'] ) ? $props['childStyle']['a'] : [];
		$padding       = ! empty( $style['padding'] ) ? $style['padding'] : '';
		$border_radius = ! empty( $style['border-radius'] ) ? $style['border-radius'] : '';
		$bg_color      = ! empty( $style['background-color'] ) ? $style['background-color'] : 'inherit';
		unset( $style['padding'] );

		$style       = $this->parse_styles( $style );
		$text_color  = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'normal';
		$width       = $props['childStyle']['a']['width'] ?? '';

		$line_height = $props['style']['line-height'] ?? 1;
		$height      = str_replace( 'px', '', $line_height );

		$a_style = [
			"color:{$text_color} !important",
			"font-weight:{$font_weight}",
			"display:block;text-decoration:none;text-transform:none;margin:0;text-align: center;max-width: 100%",
			"background-color:{$bg_color}",
			"line-height:{$line_height}",
			"height:{$line_height}",
		];

		?>
        <table align='<?php echo esc_attr( $align ) ?>' width='<?php echo esc_attr( $width ) ?>' height="<?php echo esc_attr( $height ) ?>"
               class='viwec-button-responsive' border='0' cellpadding='0' cellspacing='0' role='presentation'
               style='border-collapse:separate;width: <?php echo esc_attr( $width ) ?>;
                       background-color: <?php echo esc_attr( $bg_color ) ?>;
                       border-radius: <?php echo esc_attr( $border_radius ) ?>;'>
            <tr>
                <td class='viwec-mobile-button-padding' align='center' valign='middle' role='presentation'
                    height="<?php echo esc_attr( $height ) ?>" style='<?php echo esc_attr( $style ) ?>;'>
                    <a href='<?php echo esc_url( do_shortcode( $url ) ) ?>' target='_blank'
                       style='<?php echo esc_attr( implode( ';', $a_style ) ) ?>'>
                          <span style='color: <?php echo esc_attr( $text_color ) ?>'>
                              <?php echo wp_kses( do_shortcode( $text ), viwec_allowed_html() ) ?>
                          </span>
                    </a>
                </td>
            </tr>
        </table>
		<?php
	}

	public function render_html_menu( $props ) {
		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		?>
        <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate;margin: 0; padding:0'>
			<?php
			if ( isset( $props['content'] ) && is_array( $props['content'] ) ) {
				$count_text = count( array_filter( $props['content'] ) );
				$count_link = count( array_filter( $props['attrs'] ) );
				$col        = min( $count_text, $count_link ) ? 100 / min( $count_text, $count_link ) . '%' : '';

				if ( isset( $props['attrs']['direction'] ) && $props['attrs']['direction'] === 'vertical' ) {
					foreach ( $props['content'] as $key => $value ) {

						$link = isset( $props['attrs'][ $key ] ) ? $this->replace_shortcode( $props['attrs'][ $key ] ) : '';

						if ( empty( $value ) || ! $link ) {
							continue;
						} ?>
                        <tr>
                            <td valign='top'>
                                <a href='<?php echo esc_url( $link ) ?>'
                                   style='color: <?php echo esc_attr( $color ) ?>; font-weight: <?php echo esc_attr( $font_weight ) ?>;font-style:inherit;'>
									<?php echo wp_kses( $value, viwec_allowed_html() ) ?>
                                </a>
                            </td>
                        </tr>
					<?php }
				} else { ?>
                    <tr>
						<?php
						foreach ( $props['content'] as $key => $value ) {

							$link = isset( $props['attrs'][ $key ] ) ? $this->replace_shortcode( $props['attrs'][ $key ] ) : '';

							if ( empty( $value ) || ! $link ) {
								continue;
							}
							?>
                            <td valign='top' width='<?php echo esc_attr( $col ) ?>'>
                                <a href='<?php echo esc_url( $link ) ?>'
                                   style='color: <?php echo esc_attr( $color ) ?>; font-weight: <?php echo esc_attr( $font_weight ) ?>; font-style: inherit'>
									<?php echo wp_kses( $value, viwec_allowed_html() ) ?>
                                </a>
                            </td>
						<?php } ?>
                    </tr>
				<?php }
			} ?>
        </table>
		<?php
	}

	public function render_html_divider( $props ) {
		$style = isset( $props['childStyle']['hr'] ) ? $this->parse_styles( $props['childStyle']['hr'] ) : '';
		?>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' style="margin: 10px 0;">
            <tr>
                <td valign='top' style="border-width: 0;<?php echo esc_attr( $style ) ?>"></td>
            </tr>
        </table>
		<?php
	}

	public function render_html_spacer( $props ) {
		$style         = isset( $props['childStyle']['.viwec-spacer'] ) ? $this->parse_styles( $props['childStyle']['.viwec-spacer'] ) : '';
		$mobile_hidden = ! empty( $props['attrs']['mobile-hidden'] ) && $props['attrs']['mobile-hidden'] == 'true' ? 'viwec-mobile-hidden' : '';
		?>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-size:0 !important;margin:0;' class='<?php echo esc_attr( $mobile_hidden ) ?>'>
            <tr>
                <td valign='top' style='<?php echo esc_attr( $style ) ?>'></td>
            </tr>
        </table>
		<?php
	}

	public function render_html_contact( $props ) {
		$align       = $props['style']['text-align'] ?? 'left';
		$color       = $props['style']['color'] ?? 'inherit';
		$font_size   = $props['style']['font-size'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		$style       = "color: {$color};font-size: {$font_size};font-weight: $font_weight;vertical-align:sub;";
		?>
        <table align='<?php echo esc_attr( $align ) ?>'>
			<?php
			if ( ! empty( $props['attrs']['home'] ) && ! empty( $props['content']['home_text'] ) ) {
				$url  = isset( $props['attrs']['home_link'] ) ? $this->replace_shortcode( $props['attrs']['home_link'] ) : '';
				$text = isset( $props['content']['home_text'] ) ? $this->replace_shortcode( $props['content']['home_text'] ) : '';
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['home'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'><a style='<?php echo esc_attr( $style ) ?>' href='<?php echo esc_url( $url ) ?>'>
							<?php echo wp_kses( $text, viwec_allowed_html() ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}

			if ( ! empty( $props['attrs']['email'] ) && ! empty( $props['attrs']['email_link'] ) ) {
				$email_url = $this->replace_shortcode( $props['attrs']['email_link'] );
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['email'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'>
                        <a style='<?php echo esc_attr( $style ) ?>' href='mailto:<?php echo esc_attr( $email_url ) ?>'>
							<?php echo esc_html( $email_url ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}

			if ( ! empty( $props['attrs']['phone'] ) && ! empty( $props['content']['phone_text'] ) ) {
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['phone'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'><a style='<?php echo esc_attr( $style ) ?>' href='tel:<?php echo esc_attr( $props['content']['phone_text'] ) ?>'>
							<?php echo wp_kses( $props['content']['phone_text'], viwec_allowed_html() ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
		<?php
	}

	public function render_html_wc_hook( $props ) {
		$hook = ! empty( $props['attrs']['data-wc-hook'] ) ? $props['attrs']['data-wc-hook'] : 'woocommerce_email_before_order_table';

		$h2         = ! empty( $props['childStyle']['h2'] ) ? $this->parse_styles( $props['childStyle']['h2'] ) . 'margin:0;' : '';
		$class_td   = ! empty( $props['childStyle']['.td'] ) ? $this->parse_styles( $props['childStyle']['.td'] ) : '';
		$table_head = ! empty( $props['childStyle']['.head.td'] ) ? $this->parse_styles( $props['childStyle']['.head.td'] ) : '';
		$table_body = ! empty( $props['childStyle']['.body.td'] ) ? $this->parse_styles( $props['childStyle']['.body.td'] ) : '';

		$this->custom_css .= $h2 ? ".{$hook} h1,.{$hook} h2,.{$hook} h3,.{$hook} h4,.{$hook} h5,.{$hook} h6{{$h2}}" : '';
		$this->custom_css .= $h2 ? ".{$hook} h1 a,.{$hook} h2 a,.{$hook} h3 a,.{$hook} h4 a,.{$hook} h5 a,.{$hook} h6 a {{$h2}}" : '';
		$this->custom_css .= $class_td ? ".{$hook} th.td,.{$hook} td.td{padding:8px;{$class_td}}" : '';
		$this->custom_css .= $table_body ? ".{$hook} tbody .td{{$table_body}}" : '';
		$this->custom_css .= $table_body ? ".{$hook} tfoot .td {{$table_body}}" : '';
		$this->custom_css .= $table_head ? ".{$hook} thead .td{{$table_head}}" : '';
		$this->custom_css .= $this->custom_css ? ".{$hook} table{border-collapse:collapse; border:none !important;} blockquote{margin:5px 20px;} .{$hook} img{padding-right:8px;} ul, li{margin:0;} div{margin-bottom:0 !important;}" : '';


		if ( $this->preview ) {
			wc()->mailer();

			$class_email = new \WC_Email();

			ob_start();
			switch ( $hook ) {
				case '':
				case 'woocommerce_email_before_order_table':
					do_action( 'woocommerce_email_before_order_table', $this->order, false, false, '' );
					break;
				case 'woocommerce_email_after_order_table':
					do_action( 'woocommerce_email_after_order_table', $this->order, false, false, '' );
					break;
				case 'woocommerce_email_order_meta':
					do_action( 'woocommerce_email_order_meta', $this->order, false, false, '' );
					break;
			}

			$content = ob_get_clean();

			if ( ! $content ) {
				ob_start();
				?>
                <div style="margin-bottom: 20px;">
                    <h2><?php esc_html_e( 'Other plugin information', 'viwec-email-customizer' ); ?></h2>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <thead>
                        <tr>
                            <th class="td" align="left">ID</th>
                            <th class="td" align="left">Items</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="td">1</td>
                            <td class="td">Item</td>
                        </tr>
                        <tr>
                            <td class="td">2</td>
                            <td class="td">Item</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
				<?php
				$content = ob_get_clean();
			}

			$content = $class_email->style_inline( "<div class='{$hook}'>" . $content . '</div>' );
			echo str_replace( [ 'margin-bottom: 40px;' ], [ '' ], $content );

		} else {
			$args = $this->template_args;

			printf( "<div class='%s'>", esc_attr( $hook ) );
			switch ( $hook ) {
				case '':
				case 'woocommerce_email_before_order_table':
					do_action( 'woocommerce_email_before_order_table', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'] );
					break;
				case 'woocommerce_email_after_order_table':
//					remove_action( 'woocommerce_email_after_order_table', [ \WC_Subscriptions_Order::class, 'add_sub_info_email' ], 15 );
					do_action( 'woocommerce_email_after_order_table', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'] );
					break;
				case 'woocommerce_email_order_meta':
					do_action( 'woocommerce_email_order_meta', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'] );
					break;
			}
			echo "<div>";
		}
	}

	public function render_html_recover_heading( $props ) {
		if ( $this->preview ) {
			echo esc_html__( 'Thank you for your order', 'viwec-email-template-customizer' );
		}

		if ( ! $this->use_default_template ) {
			return;
		}

		if ( $this->class_email ) {
			echo wp_kses( $this->class_email->get_heading(), viwec_allowed_html() );
		}

		if ( $this->recover_heading ) {
			echo esc_html( $this->recover_heading );
		}
	}

	public function render_html_recover_content( $props ) {
		$link_color  = $props['childStyle']['p']['color'] ?? '#444444';
		$p           = ! empty( $props['childStyle']['p'] ) ? $this->parse_styles( $props['childStyle']['p'] ) : '';
		$h2          = ! empty( $props['childStyle']['h2'] ) ? $this->parse_styles( $props['childStyle']['h2'] ) : '';
		$h2          = str_replace( 'padding', 'margin', $h2 );
		$class_td    = ! empty( $props['childStyle']['.td'] ) ? $this->parse_styles( $props['childStyle']['.td'] ) : '';
		$table_head  = ! empty( $props['childStyle']['.head.td'] ) ? $this->parse_styles( $props['childStyle']['.head.td'] ) : '';
		$table_body  = ! empty( $props['childStyle']['.body.td'] ) ? $this->parse_styles( $props['childStyle']['.body.td'] ) : '';
		$label_items = ! empty( $props['childStyle']['th.body.td'] ) ? $this->parse_styles( $props['childStyle']['th.body.td'] ) : '';
		$value_items = ! empty( $props['childStyle']['td.body.td'] ) ? $this->parse_styles( $props['childStyle']['td.body.td'] ) : '';

		$this->custom_css .= "#viwec-transferred-content address{padding:0 !important; border:none !important;}";
		$this->custom_css .= $p ? "#viwec-transferred-content p, #viwec-transferred-content #addresses td{{$p}}" : '';
		$this->custom_css .= $h2 ? "#viwec-transferred-content h1,#viwec-transferred-content h2,#viwec-transferred-content h3,
		                            #viwec-transferred-content h4,#viwec-transferred-content h5,#viwec-transferred-content h6{{$h2}}" : '';
		$this->custom_css .= $h2 ? "#viwec-transferred-content h1 a,#viwec-transferred-content h2 a,#viwec-transferred-content h3 a,
                                    #viwec-transferred-content h4 a,#viwec-transferred-content h5 a,#viwec-transferred-content h6 a {{$h2}}" : '';
		$this->custom_css .= $class_td ? "#viwec-transferred-content th.td,#viwec-transferred-content td.td{padding:8px;{$class_td}}" : '';
		$this->custom_css .= $table_body ? "#viwec-transferred-content tbody td, #viwec-transferred-content tbody .td{{$table_body}}" : '';
		$this->custom_css .= $table_body ? "#viwec-transferred-content tfoot td, #viwec-transferred-content tfoot .td {{$table_body}}" : '';
		$this->custom_css .= $table_head ? "#viwec-transferred-content thead td, #viwec-transferred-content thead .td{{$table_head}}" : '';
		$this->custom_css .= $label_items ? "#viwec-transferred-content tfoot th, #viwec-transferred-content tfoot th.td{{$label_items}}" : '';
		$this->custom_css .= $value_items ? "#viwec-transferred-content tbody td, #viwec-transferred-content tfoot td,
		                                    #viwec-transferred-content tbody td.td, #viwec-transferred-content tfoot td.td{{$value_items}}" : '';
		$this->custom_css .= $this->custom_css ? '#viwec-transferred-content table{ border:none !important;} blockquote{margin:5px 20px;} 
                                                  #viwec-transferred-content img{padding-right:8px;} ul, li{margin:0;} ul.wc-item-meta{padding: 10px 0;}' : '';
		$this->custom_css .= "a{color:{$link_color} !important;}";

		if ( $this->preview ) {
			add_filter( 'woocommerce_email_order_items_args', [ $this, 'show_image' ] );
			wc()->mailer();
			$class_email = new \WC_Email();

			ob_start();
			printf( "<p>Hi %s, Just to let you know — we've received your order #%s, and it is now being processed:</p>",
				esc_html( $this->order->get_billing_first_name() ),
				absint( $this->order->get_id() ) );

			do_action( 'woocommerce_email_order_details', $this->order, false, false, '' );
			do_action( 'woocommerce_email_order_meta', $this->order, false, false, '' );
			do_action( 'woocommerce_email_customer_details', $this->order, false, false, '' );

			$content = ob_get_clean();
			$content = preg_replace( '/border=[\'\"]\d+[\'\"]/', 'border="0"', $content );
			$content = '<div id="viwec-transferred-content">' . wp_kses_post( $content ) . '</div>';
			$content = $class_email->style_inline( $content );
			$content = str_replace( [ 'margin-bottom: 40px;', 'border-top-width: 4px;' ], '', $content );
			echo wp_kses_post( $content );
		}

		if ( ! $this->use_default_template ) {
			return;
		}

		if ( $this->other_message_content ) {
			$content = str_replace( [ 'margin-bottom: 40px;', 'border-top-width: 4px;' ], '', $this->other_message_content );
			$content = preg_replace( '/border=[\'"]\d+[\'"]/', 'border="0"', $content );
			echo '<div id="viwec-transferred-content">' . wp_kses( $content, viwec_allowed_html() ) . '</div>';
		}
	}

	/*For 3rd compatible*/
	public function render_html_coupon( $props ) {

		$type        = $props['attrs']['data-coupon-type'] ?? '';
		$coupon_code = $props['content']['data-coupon-code'] ?? '';
		$align       = $props['style']['text-align'] ?? 'left';
		$text_color  = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'normal';
		$width       = $props['childStyle']['.viwec-coupon']['width'] ?? '';
		$style       = isset( $props['childStyle']['.viwec-coupon'] ) ? $this->parse_styles( $props['childStyle']['.viwec-coupon'] ) : '';

		if ( $type == 2 ) {
			$coupon_code = $this->preview ? 's0kvk4kp' : $this->generate_coupon( $props );
		}

		if ( ! $coupon_code || $coupon_code === 'COUPONCODE' ) {
			return;
		}

		$coupon_code = strtoupper( $coupon_code );

		$this->render_data['coupon'] = $coupon_code;

		$coupon_obj = new \WC_Coupon( $coupon_code );

		$coupon_ex_date = $coupon_obj->get_date_expires();
		if ( $coupon_ex_date ) {
			$coupon_ex_date = $coupon_ex_date->date_i18n( wc_date_format() );
		}

		$this->template_args['coupon_expire_date'] = $coupon_ex_date;

		?>
        <table align='<?php echo esc_attr( $align ) ?>' width='<?php echo esc_attr( $width ) ?>'
               class='viwec-responsive' border='0' cellpadding='0' cellspacing='0' role='presentation'
               style='border-collapse:collapse;width:<?php echo esc_attr( $width ) ?>;'>
            <tr>
                <td class='viwec-mobile-button-padding' align='center' valign='middle' role='presentation' style='<?php echo esc_attr( $style ) ?>'>
                    <div style='color:<?php echo esc_attr( $text_color ) ?> !important;font-weight:<?php echo esc_attr( $font_weight ) ?>;
                            display:inline-block;margin:0;text-align: center; max-width:100%;'>
						<?php echo esc_html( $coupon_code ) ?>
                    </div>
                </td>
            </tr>
        </table>
		<?php
	}

	public function show_image( $args ) {
		$args['show_image'] = isset( $this->props['attrs']['show_img'] ) && $this->props['attrs']['show_img'] == 'true' ? true : false;
		$args['image_size'] = ! empty( $this->props['childStyle']['img']['width'] ) ? [ (int) $this->props['childStyle']['img']['width'], 300 ] : [ 32, 32 ];

		return $args;
	}

	public function table( $content, $style = '', $width = '100%', $attr = [] ) {
		?>
        <table width='<?php echo esc_attr( $width ) ?>' border='0' cellpadding='0' cellspacing='0' align='left'
               style='border-collapse: collapse;<?php echo esc_attr( $style ) ?>'>
			<?php echo wp_kses( $content, viwec_allowed_html() ) ?>
        </table>
		<?php
	}

	public function generate_coupon( $props ) {
		$option = $props['attrs'];
		$amount = isset( $option['data-coupon-amount'] ) && floatval( $option['data-coupon-amount'] ) >= 0 ? $option['data-coupon-amount'] : 0;
		$type   = ! empty( $option['data-discount-type'] ) ? $option['data-discount-type'] : 'percent';
		$prefix = ! empty( $option['data-discount-prefix'] ) ? $option['data-discount-prefix'] : '';

		if ( ! in_array( $type, array_keys( wc_get_coupon_types() ) ) || ( $type == 'percent' && $amount > 100 ) ) {
			return '';
		}

		$code   = $this->generate_coupon_code( $prefix );
		$coupon = new \WC_Coupon( $code );

		$coupon->set_discount_type( $type );

		$coupon->set_amount( $amount );

		if ( ! empty( $option['data-coupon-expiry-date'] ) ) {
			$coupon->set_date_expires( current_time( 'U' ) + $option['data-coupon-expiry-date'] * DAY_IN_SECONDS );
		}

		if ( ! empty( $option['data-coupon-min-spend'] ) ) {
			$coupon->set_minimum_amount( $option['data-coupon-min-spend'] );
		}

		if ( ! empty( $option['data-coupon-max-spend'] ) ) {
			$coupon->set_maximum_amount( $option['data-coupon-max-spend'] );
		}

		if ( ! empty( $option['data-coupon-individual'] ) ) {
			$coupon->set_individual_use( $option['data-coupon-individual'] );
		}

		if ( ! empty( $option['data-coupon-exclude-sale'] ) ) {
			$coupon->set_exclude_sale_items( $option['data-coupon-exclude-sale'] );
		}

		if ( ! empty( $option['data-coupon-allow-free-shipping'] ) ) {
			$coupon->set_free_shipping( $option['data-coupon-allow-free-shipping'] );
		}

		if ( ! empty( $option['data-coupon-include-product'] ) ) {
			$coupon->set_product_ids( explode( ',', $option['data-coupon-include-product'] ) );
		}

		if ( ! empty( $option['data-coupon-exclude-product'] ) ) {
			$coupon->set_excluded_product_ids( explode( ',', $option['data-coupon-exclude-product'] ) );
		}

		if ( ! empty( $option['data-coupon-include-categories'] ) ) {
			$coupon->set_product_categories( explode( ',', $option['data-coupon-include-categories'] ) );
		}

		if ( ! empty( $option['data-coupon-exclude-categories'] ) ) {
			$coupon->set_excluded_product_categories( explode( ',', $option['data-coupon-exclude-categories'] ) );
		}

		if ( ! empty( $option['data-coupon-limit-quantity'] ) ) {
			$coupon->set_usage_limit( $option['data-coupon-limit-quantity'] );
		}

		if ( ! empty( $option['data-coupon-limit-items'] ) ) {
			$coupon->set_limit_usage_to_x_items( $option['data-coupon-limit-items'] );
		}

		if ( ! empty( $option['data-coupon-limit-users'] ) ) {
			$coupon->set_usage_limit_per_user( $option['data-coupon-limit-users'] );
		}

		$coupon_id = $coupon->save();
		if ( $coupon_id && class_exists( 'Dokan_Pro' ) ) {
			update_post_meta( $coupon_id, 'admin_coupons_enabled_for_vendor', 'yes' );
		}

		return $code;
	}

	public function generate_coupon_code( $prefix = '' ) {
		$code          = $prefix;
		$character_arr = array_merge( range( 'a', 'z' ), range( 0, 9 ) );

		for ( $i = 0; $i < 8; $i ++ ) {
			$rand = rand( 0, count( $character_arr ) - 1 );
			$code .= $character_arr[ $rand ];
		}

		$args = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'title'          => $code
		);

		$the_query = new \WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$code = $this->generate_coupon_code( $prefix );
		}

		wp_reset_postdata();

		return $code;
	}

	public function order_download( $item_id, $item, $order ) {
		$show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();

		if ( ! $show_downloads ) {
			return;
		}

		$pid       = $item->get_data()['product_id'];
		$downloads = $order->get_downloadable_items();

		foreach ( $downloads as $download ) {
			if ( $pid == $download['product_id'] ) {
				$href    = esc_url( $download['download_url'] );
				$display = esc_html( $download['download_name'] );
				$expires = '';
				if ( ! empty( $download['access_expires'] ) ) {
					$datetime     = esc_attr( date_i18n( 'Y-m-d', strtotime( $download['access_expires'] ) ) );
					$title        = esc_attr( strtotime( $download['access_expires'] ) );
					$display_time = esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) );
					$expires      = "- <time datetime='$datetime' title='$title'>$display_time</time>";
				}
				printf( "<p><a href='%s'>%s</a> %s</p>", esc_url( $href ), esc_html( $display ), esc_html( $expires ) );
			}
		}
	}

	public function remove_shipping_method( $shipping_display ) {
		if ( $this->order ) {
			return '';
		}

		return $shipping_display;
	}

	public function get_style( $props, $layer1, $layer2 = '' ) {
		if ( ! $props || ! $layer1 ) {
			return '';
		}

		if ( $layer2 ) {
			$data = $props[ $layer1 ][ $layer2 ] ?? '';
		} else {
			$data = $props[ $layer1 ] ?? '';
		}

		return $this->parse_styles( $data );
	}
}

