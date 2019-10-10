<?php

namespace Vendidero\Germanized\DHL;
use Exception;
use Vendidero\Germanized\Shipments\Shipment;
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
		add_action( 'woocommerce_gzd_shipment_before_status_change', array( __CLASS__, 'set_automation' ), 10, 2 );
	}

	/**
	 * @param $shipment_id
	 * @param Shipment $shipment
	 */
	public static function set_automation( $shipment_id, $shipment ) {

		$disable = false;

		if ( ! wc_gzd_dhl_shipment_needs_label( $shipment, false ) ) {
			$disable = true;
		}

		/**
		 * Filter that allows to disable automatically creating DHL labels for a certain shipment.
		 *
		 * @param boolean  $disable True if you want to disable automation.
		 * @param Shipment $shipment The shipment object.
		 *
		 * @since 3.0.0
		 */
		$disable = apply_filters( 'woocommerce_gzd_dhl_disable_label_auto_generate', $disable, $shipment );

		if ( $disable ) {
			return;
		}

		$shipping_method = wc_gzd_dhl_get_shipping_method( $shipment->get_shipping_method() );
		$setting         = 'return' === $shipment->get_type() ? 'label_return_auto_enable' : 'label_auto_enable';
		$setting_status  = 'return' === $shipment->get_type() ? 'label_return_auto_shipment_status' : 'label_auto_shipment_status';
		$hook_prefix     = 'woocommerce_gzd_' . ( 'return' === $shipment->get_type() ? 'return_' : '' ) . 'shipment_status_';

		if ( 'yes' === Package::get_setting( $setting, $shipping_method ) ) {
			$status = Package::get_setting( $setting_status, $shipping_method );

			if ( ! empty( $status ) ) {
				$status = str_replace( 'gzd-', '', $status );

				add_action( $hook_prefix . $status, array( __CLASS__, 'maybe_create_label' ), 10, 1 );
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