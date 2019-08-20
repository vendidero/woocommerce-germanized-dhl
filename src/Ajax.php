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
		$ajax_events_nopriv = array(
			'dhl_parcelfinder_search'
		);

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_nopriv_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			add_action( 'wp_ajax_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}

		$ajax_events = array(
			'create_dhl_label',
			'remove_dhl_label'
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_woocommerce_gzd_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	public static function dhl_parcelfinder_search() {

		if ( ! ParcelFinder::is_enabled() || ! ParcelFinder::has_map() ) {
			wp_die();
		}

		check_ajax_referer( 'dhl-parcel-finder', 'security' );

		$parcelfinder_country	 = isset( $_POST['dhl_parcelfinder_country'] ) ? wc_clean( $_POST['dhl_parcelfinder_country'] ) : Package::get_base_country();
		$parcelfinder_postcode	 = isset( $_POST['dhl_parcelfinder_postcode'] ) ? wc_clean( $_POST['dhl_parcelfinder_postcode'] ) : '';
		$parcelfinder_city	 	 = isset( $_POST['dhl_parcelfinder_city'] ) ? wc_clean( $_POST['dhl_parcelfinder_city'] ) : '';
		$parcelfinder_address	 = isset( $_POST['dhl_parcelfinder_address'] ) ? wc_clean( $_POST['dhl_parcelfinder_address'] ) : '';
		$packstation_filter	 	 = wc_string_to_bool( isset( $_POST['dhl_parcelinder_packstation_filter'] ) ? wc_clean( $_POST['dhl_parcelinder_packstation_filter'] ) : 'yes' );
		$branch_filter	 		 = wc_string_to_bool( isset( $_POST['dhl_parcelinder_branch_filter'] ) ? wc_clean( $_POST['dhl_parcelinder_branch_filter'] ) : 'yes' );

		try {
			$args = array(
				'address'  => $parcelfinder_address,
				'postcode' => $parcelfinder_postcode,
				'city'     => $parcelfinder_city,
				'country'  => empty( $parcelfinder_country ) ? Package::get_base_country() : $parcelfinder_country,
			);

			$error                = new WP_Error();
			$parcel_res          = Package::get_api()->get_parcel_location( $args );
			$parcel_res_filtered = array();

			if ( ! isset( $parcel_res->parcelLocation ) ) {
				$error->add( 404, __( 'No parcel shops found', 'woocommerce-germanized-dhl' ) );
			} else {
				$res_count = 0;

				foreach ( $parcel_res->parcelLocation as $key => $value ) {

					if ( ( 'packStation' === $value->shopType && ParcelFinder::is_packstation_enabled() && $packstation_filter ) ||
					     ( 'parcelShop' === $value->shopType && ParcelFinder::is_parcelshop_enabled() && $branch_filter ) ||
					     ( 'postOffice' === $value->shopType && ParcelFinder::is_post_office_enabled() && $branch_filter )
					) {
						if ( $value->psfServicetypes ) {
							if ( is_array( $value->psfServicetypes ) ) {
								if ( in_array( 'parcelpickup', $value->psfServicetypes ) ) {
									array_push($parcel_res_filtered, $value );
									$res_count++;
								}
							} else {
								if ( 'parcelpickup' === $value->psfServicetypes ) {
									array_push($parcel_res_filtered, $value );
									$res_count++;
								}
							}
						}
					}

					if ( $res_count >= ParcelFinder::get_max_results() ) {
						break;
					}
				}
			}

			if ( empty( $parcel_res_filtered ) ) {
				$error->add( 404, __( 'No Parcel Shops found. Ensure "Packstation" or Branch" filter is checked.', 'woocommerce-germanized-dhl' ) );
			}

			if ( ! $error->has_errors() ) {
				wp_send_json( array(
					'parcel_shops' => $parcel_res_filtered,
					'success'      => true,
				) );
			} else {
				wp_send_json( array(
					'success'    => false,
					'messages'   => $error->get_error_messages(),
				) );
			}
		} catch ( Exception $e ) {
			$error = sprintf( __( 'There was an error while communicating with DHL. Please manually find a %s.', 'woocommerce-germanized-dhl' ), '<a href="">' . __( 'parcel shop', 'woocommerce-germanized-dhl' ) . '</a>' );

			wp_send_json( array(
				'success' => false,
				'message' => Package::is_debug_mode() ? $e->getMessage() : $error,
			) );
		}

		wp_die();
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
		check_ajax_referer( 'edit-dhl-label', 'security' );

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
					'#shipment-' . $shipment_id . ' .wc-gzd-shipment-dhl-label' => self::refresh_label_html( $shipment, $label )
				),
			);
		}

		wp_send_json( $response );
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