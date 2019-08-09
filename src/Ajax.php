<?php

namespace Vendidero\Germanized\DHL;

/**
 * WC_Ajax class.
 */
class Ajax {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events_nopriv = array();

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}

		$ajax_events = array(
			'create_dhl_label',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	public static function remove_shipment() {
		check_ajax_referer( 'edit-shipments', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['shipment_id'] ) ) {
			wp_die( -1 );
		}

		$response_error = array(
			'success' => false,
			'message' => _x( 'There was an error processing the shipment', 'shipments', 'woocommerce-germanized-shipments' ),
		);

		$response = array(
			'success' => true,
			'message' => '',
		);

		$shipment_id = absint( $_POST['shipment_id'] );

		if ( ! $shipment = wc_gzd_get_shipment( $shipment_id ) ) {
			wp_send_json( $response_error );
		}

		if ( ! $order_shipment = wc_gzd_get_shipment_order( $shipment->get_order() ) ) {
			wp_send_json( $response_error );
		}

		if ( $shipment->delete( true ) ) {
			$order_shipment->remove_shipment( $shipment_id );

			$response['shipment_id'] = $shipment_id;
			$response['fragments']   = array(
				'.order-shipping-status' => self::get_order_status_html( $order_shipment ),
			);

			self::send_json_success( $response, $order_shipment );
		} else {
			wp_send_json( $response_error );
		}
	}
}