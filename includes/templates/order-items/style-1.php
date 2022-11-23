<?php

defined( 'ABSPATH' ) || exit;
$text_align          = $direction === 'rtl' ? 'right' : 'left';
$margin_side         = $direction === 'rtl' ? 'left' : 'right';
$item_style          = $props['childStyle']['.viwec-item-style-1'] ? $render->parse_styles( $props['childStyle']['.viwec-item-style-1'] ) : '';
$col_width           = $props['childStyle']['.viwec-text-price'] ? $render->parse_styles( $props['childStyle']['.viwec-text-price'] ) : '';
$trans_product       = $props['content']['product'] ?? esc_html__( 'Product', 'viwec-email-template-customizer' );
$trans_quantity      = $props['content']['quantity'] ?? esc_html__( 'Quantity', 'viwec-email-template-customizer' );
$trans_price         = $props['content']['price'] ?? esc_html__( 'Price', 'viwec-email-template-customizer' );
$show_sku            = ! empty( $props['attrs']['show_sku'] ) && $props['attrs']['show_sku'] == 'true' ? true : false;
$remove_product_link = ! empty( $props['attrs']['remove_product_link'] ) && $props['attrs']['remove_product_link'] == 'true';


$left_align  = $direction === 'rtl' ? 'right' : 'left';
$right_align = $direction === 'rtl' ? 'left' : 'right';

$th_style_left   = "border:1px solid #dddddd; text-align:{$left_align}; padding: 10px;";
$th_style_center = 'border:1px solid #dddddd; text-align:center; padding: 10px;';
$th_style_right  = 'border:1px solid #dddddd; text-align:right; padding: 10px;';

$html = "<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' style='{$item_style}; border-collapse:collapse;line-height: 1'>";
$html .= "<tr><th style='{$th_style_left}'>{$trans_product}</th><th style='{$th_style_center}'>{$trans_quantity}</th><th style='{$th_style_right}{$col_width}'>{$trans_price}</th></tr>";

ob_start();
foreach ( $items as $item_id => $item ) {
	$product = $item->get_product();

	$sku = $purchase_note = $image = $p_url = '';

	if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		continue;
	}

	if ( is_object( $product ) ) {
		$sku           = $product->get_sku();
		$purchase_note = $product->get_purchase_note();
		$p_url         = $remove_product_link ? '#' : $product->get_permalink();
		$image_size    = apply_filters( 'viwec_order_detail_default_image_size', array( 64, 64 ) );
		$image         = $product->get_image( $image_size );
	}
	?>

    <tr>
        <td style='<?php echo esc_attr( $th_style_left ) ?>'>
			<?php

			$show_image = apply_filters( 'viwec_order_detail_default_show_image', false );
			if ( $show_image ) {
				echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
			}

			$name = "<a style='color: inherit' href='{$p_url}'>{$item->get_name()}</a>";
			echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $name, $item, false ) );

			if ( $show_sku && $sku ) {
				echo '<small>' . wp_kses_post( ' (#' . $sku . ')' ) . '</small>';
			}

			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

			wc_display_item_meta(
				$item,
				array(
					'before'       => '<div class=""><div>',
					'after'        => '</div></div>',
					'separator'    => '</div><div>',
					'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr( $text_align ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
				)
			);

			// allow other plugins to add additional product information here.
			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
			do_action( 'viwec_order_item_parts', $item_id, $item, $order, false );
			?>
        </td>

        <td style='<?php echo esc_attr( $th_style_center ) ?>'>
			<?php
			$qty          = $item->get_quantity();
			$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

			if ( $refunded_qty ) {
				$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * - 1 ) ) . '</ins>';
			} else {
				$qty_display = esc_html( $qty );
			}
			echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
			?>
        </td>

        <td style='<?php echo esc_attr( $th_style_right ) ?>'>
			<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
        </td>
    </tr>
	<?php
	if ( $show_purchase_note && $purchase_note ) {
		?>
        <tr>
            <td colspan="3" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; ">
				<?php
				echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) );
				?>
            </td>
        </tr>
		<?php
	}
}

$out = $html . ob_get_clean() . '</table>';
echo wp_kses( $out, viwec_allowed_html() );

