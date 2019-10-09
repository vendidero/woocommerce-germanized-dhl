<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

use Vendidero\Germanized\DHL\Package;

$default_args = wc_gzd_dhl_get_return_label_default_args( $dhl_order, $shipment );
?>

<form action="" method="post" class="wc-gzd-dhl-create-label-form">

	<?php woocommerce_wp_select( array(
		'id'          		=> 'dhl_label_receiver_id',
		'label'       		=> __( 'Receiver Id', 'woocommerce-germanized-dhl' ),
		'description'		=> '',
		'options'			=> wc_gzd_dhl_get_return_receiver_ids(),
		'value'             => isset( $default_args['receiver_id'] ) ? wc_gzd_dhl_get_return_receiver_id_key( $default_args['receiver_id'] ) : '',
	) ); ?>

</form>
