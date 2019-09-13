<?php

namespace Vendidero\Germanized\DHL;
use Exception;
use WC_Order_Item;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Automation {

	/**
	 * Init the package - load the REST API Server class.
	 */
	public static function init() {
		if ( 'yes' === Package::get_setting( 'label_auto_enable' ) ) {
			$status = Package::get_setting( 'label_auto_shipment_status' );

			if ( ! empty( $status ) ) {
				$status = str_replace( 'gzd-', '', $status );

				add_action( 'woocommerce_gzd_shipment_status_' . $status, array( __CLASS__, 'maybe_create_label' ), 10, 1 );
			}
		}
	}

	public static function maybe_create_label( $shipment_id ) {
		if ( $shipment = wc_gzd_get_shipment( $shipment_id ) ) {
			if ( ! wc_gzd_dhl_get_shipment_label( $shipment ) ) {
				$label = wc_gzd_dhl_create_label( $shipment );

				if ( ! is_wp_error( $label ) ) {}
			}
		}
	}
}