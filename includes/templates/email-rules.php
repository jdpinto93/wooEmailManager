<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency       = get_woocommerce_currency_symbol( get_woocommerce_currency() );
$currency_label = esc_html__( 'Subtotal', 'viwec-email-template-customizer' ) . " ({$currency})";

?>

