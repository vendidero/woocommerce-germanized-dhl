<?php

namespace Vendidero\Germanized\DHL;

use Exception;
use Vendidero\Germanized\DHL\Package;
use WP_Error;

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
		$ajax_events = array(
			'create_dhl_label',
			'remove_dhl_label',
			'dhl_create_label_form',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	public static function dhl_create_label_form() {
		check_ajax_referer( 'create-dhl-label-form', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['shipment_id'] ) ) {
			wp_die( -1 );
		}

		$shipment_id    = absint( $_POST['shipment_id'] );
		$response       = array();
		$response_error = array(
			'success'  => false,
			'messages' => array(
				__( 'There was an error deleting the label.', 'woocommerce-germanized-dhl' )
			),
		);

		if ( ! $shipment = wc_gzd_get_shipment( $shipment_id ) ) {
			wp_send_json( $response_error );
		}

		if ( ! $dhl_order = wc_gzd_dhl_get_order( $shipment->get_order() ) ) {
			wp_send_json( $response_error );
		}

		ob_start();
		include( Package::get_path() . '/includes/admin/views/html-shipment-label-backbone-form.php' );
		$html = ob_get_clean();

		$response = array(
			'fragments' => array(
				'.wc-gzd-dhl-create-label' => '<div class="wc-gzd-dhl-create-label">' . $html . '</div>',
			),
			'shipment_id' => $shipment_id,
			'success'     => true,
		);

		wp_send_json( $response );
	}

	public static function remove_dhl_label() {
		check_ajax_referer( 'remove-dhl-label', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['shipment_id'] ) || ! isset( $_POST['label_id'] ) ) {
			wp_die( -1 );
		}

		$response       = array();
		$response_error = array(
			'success'  => false,
			'messages' => array(
				__( 'There was an error deleting the label.', 'woocommerce-germanized-dhl' )
			),
		);

		$shipment_id = absint( $_POST['shipment_id'] );
		$label_id    = absint( $_POST['label_id'] );

		if ( ! $shipment = wc_gzd_get_shipment( $shipment_id ) ) {
			wp_send_json( $response_error );
		}

		if ( ! $label = wc_gzd_dhl_get_label( $label_id ) ) {
			wp_send_json( $response_error );
		}

		if ( (int) $label->get_shipment_id() !== $shipment_id ) {
			wp_send_json( $response_error );
		}

		if ( $label->delete( true ) ) {
			$response = array(
				'success'   => true,
				'label_id'  => $label->get_id(),
				'fragments' => array(
					'#shipment-' . $shipment_id . ' .wc-gzd-shipment-dhl-label' => self::refresh_label_html( $shipment )
				),
			);
		} else {
			wp_send_json( $response_error );
		}

		wp_send_json( $response );
	}

	public static function create_dhl_label() {
		check_ajax_referer( 'create-dhl-label', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['shipment_id'] ) ) {
			wp_die( -1 );
		}

		$response       = array();
		$response_error = array(
			'success'  => false,
			'messages' => array(
				__( 'There was an error processing the label.', 'woocommerce-germanized-dhl' )
			),
		);

		$shipment_id = absint( $_POST['shipment_id'] );

		if ( ! $shipment = wc_gzd_get_shipment( $shipment_id ) ) {
			wp_send_json( $response_error );
		}

		$services = array();
		$props    = array();

		foreach( $_POST as $key => $value ) {
			if ( substr( $key, 0, strlen( 'dhl_label_service_' ) ) === 'dhl_label_service_' ) {
				$new_key              = substr( $key, ( strlen( 'dhl_label_service_' ) ) );

				if ( 'yes' === $value && in_array( $new_key, wc_gzd_dhl_get_services() ) ) {
					$services[] = $new_key;
				}
			} elseif ( substr( $key, 0, strlen( 'dhl_label_' ) ) === 'dhl_label_' ) {
				$new_key           = substr( $key, ( strlen( 'dhl_label_' ) ) );
				$props[ $new_key ] = wc_clean( wp_unslash( $value ) );
			}
		}

		if ( isset( $props['preferred_time'] ) && ! empty( $props['preferred_time'] ) ) {
			$preferred_time = explode( '-', wc_clean( wp_unslash( $props['preferred_time'] ) ) );

			if ( sizeof( $preferred_time ) === 2 ) {
				$props['preferred_time_start'] = $preferred_time[0];
				$props['preferred_time_end']   = $preferred_time[1];
			}

			unset( $props['preferred_time'] );
		}

		$props['services'] = $services;

		if ( $label = wc_gzd_dhl_get_shipment_label( $shipment ) ) {
			$label = wc_gzd_dhl_update_label( $label, $props );
		} else {
			$label = wc_gzd_dhl_create_label( $shipment, $props );
		}

		if ( is_wp_error( $label ) ) {
			$response = array(
				'success'  => false,
				'messages' => $label->get_error_messages(),
			);
		} else {
			$response = array(
				'success'   => true,
				'label_id'  => $label->get_id(),
				'fragments' => array(
					'#shipment-' . $shipment_id . ' .wc-gzd-shipment-dhl-label'                                     => self::refresh_label_html( $shipment, $label ),
					'tr#shipment-' . $shipment_id . ' td.actions .wc-gzd-shipment-action-button-generate-dhl-label' => self::label_download_button_html( $label ),
				),
			);
		}

		wp_send_json( $response );
	}

	/**
	 * @param Label $label
	 *
	 * @return string
	 */
	protected static function label_download_button_html( $label ) {
		return '<a class="button wc-gzd-shipment-action-button wc-gzd-shipment-action-button-download-dhl-label download" href="' . $label->get_download_url() .'" target="_blank" title="' . __( 'Download DHL label', 'woocommerce-germanized-dhl' ) . '">' . __( 'Download label', 'woocommerce-germanized-dhl' ) . '</a>';
	}

	protected static function refresh_label_html( $p_shipment, $p_label = false ) {
		$shipment       = $p_shipment;
		$dhl_order      = wc_gzd_dhl_get_order( $shipment->get_order() );

		if ( $p_label ) {
			$dhl_label      = $p_label;
		}

		ob_start();
		include( Package::get_path() . '/includes/admin/views/html-shipment-label.php' );
		$html = ob_get_clean();

		return $html;
	}
}