<?php

namespace Vendidero\Germanized\DHL\Admin;
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\Shipment;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Meta_Box_Order_Items Class.
 */
class MetaBox {

	/**
	 * Output the metabox.
	 *
	 * @param Shipment $shipment
	 */
	public static function output( $the_shipment ) {
		global $post, $thepostid, $theorder;

		if ( ! is_int( $thepostid ) ) {
			$thepostid = $post->ID;
		}

		if ( ! is_object( $theorder ) ) {
			$theorder = wc_get_order( $thepostid );
		}

		$order     = $theorder;
		$shipment  = $the_shipment;
		$dhl_label = wc_gzd_dhl_get_shipment_label( $the_shipment );
		$dhl_order = wc_gzd_dhl_get_order( $order );

		include( Package::get_path() . '/includes/admin/views/html-shipment-label.php' );
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 */
	public static function save( $order_id ) {
		// Get order object.
		$order_shipment = wc_gzd_get_shipment_order( $order_id );

		self::refresh_shipments( $order_shipment );

		$order_shipment->validate_shipments( array( 'save' => false ) );

		// Refresh status just before saving
		self::refresh_status( $order_shipment );

		$order_shipment->save();
	}
}
