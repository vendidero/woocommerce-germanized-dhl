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
		$shipment  = $the_shipment;
		$dhl_label = wc_gzd_dhl_get_shipment_label( $the_shipment );
		$dhl_order = wc_gzd_dhl_get_order( $shipment->get_order() );

		include( Package::get_path() . '/includes/admin/views/html-shipment-label.php' );
	}
}
