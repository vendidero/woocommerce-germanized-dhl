<?php

namespace Vendidero\Germanized\DHL;
use Exception;
use Vendidero\Germanized\Shipments\ShipmentItem;
use WC_Order_Item;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class LabelWatcher {

	/**
	 * Init the package - load the REST API Server class.
	 */
	public static function init() {

		// Create labels if they do not yet exist
		add_action( 'woocommerce_gzd_dhl_before_create_label', array( __CLASS__, 'create_label' ), 10, 1 );
		add_action( 'woocommerce_gzd_dhl_before_update_label', array( __CLASS__, 'update_label' ), 10, 1 );

		// Delete label
		add_action( 'woocommerce_gzd_dhl_label_deleted', array( __CLASS__, 'delete_label' ), 10, 2 );

		// Delete the label if parent shipment has been deleted
		add_action( 'woocommerce_gzd_shipment_deleted', array( __CLASS__, 'deleted_shipment' ), 10, 2 );

		add_action( 'woocommerce_gzd_shipment_item_synced', array( __CLASS__, 'sync_item_meta' ), 10, 3 );
	}

	/**
	 * @param ShipmentItem $item
	 * @param WC_Order_Item $order_item
	 * @param $args
	 */
	public static function sync_item_meta( $item, $order_item, $args ) {
		if ( $product = $item->get_product() ) {
			$item->update_meta_data( '_dhl_hs_code', $product->get_meta( '_dhl_hs_code' ) );
			$item->update_meta_data( '_dhl_manufacture_country', $product->get_meta( '_dhl_manufacture_country' ) );
		}
	}

	public static function create_label( $label ) {
		try {
			Package::get_api()->get_label( $label );
		} catch( Exception $e ) {
			throw new Exception( nl2br( $e->getMessage() ) );
		}
	}

	public static function update_label( $label ) {
		try {
			Package::get_api()->get_label( $label );
		} catch( Exception $e ) {
			throw new Exception( nl2br( $e->getMessage() ) );
		}
	}

	public static function delete_label( $label_id, $label ) {
		try {
			Package::get_api()->delete_label( $label );
		} catch( Exception $e ) {}
	}

	public static function deleted_shipment( $shipment_id, $shipment ) {
		if ( $label = wc_gzd_dhl_get_shipment_label( $shipment_id ) ) {
			$label->delete( true );
		}
	}
}