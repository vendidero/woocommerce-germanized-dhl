<?php

namespace Vendidero\Germanized\DHL;
use Exception;
use Vendidero\Germanized\Shipments\Shipment;
use WC_Checkout;
use WC_Order;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class ParcelLocator {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
		add_action( 'wp_head', array( __CLASS__, 'add_inline_styles' ), 50 );

		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'add_shipping_fields' ), 10 );
		add_filter( 'woocommerce_admin_shipping_fields', array( __CLASS__, 'add_admin_shipping_fields' ), 10 );

		/**
		 * Checkout Hooks
		 */
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'manipulate_checkout_fields' ), 10 );
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'validate_checkout' ), 20 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'maybe_remove_order_data' ), 10, 2 );
		add_filter( 'woocommerce_get_order_address', array( __CLASS__, 'add_order_address_data' ), 10, 3 );

		// Shipment Pickup
		add_filter( 'woocommerce_gzd_shipment_send_to_external_pickup', array( __CLASS__, 'shipment_has_pickup' ), 10, 3 );

		/**
		 * MyAccount Hooks
		 */
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'manipulate_address_fields' ), 20, 1 );
		add_filter( 'woocommerce_process_myaccount_field_shipping_address_type', array( __CLASS__, 'validate_address_fields' ), 10, 1 );
		add_filter( 'woocommerce_process_myaccount_field_shipping_dhl_postnumber', array( __CLASS__, 'validate_address_postnumber' ), 10, 1 );

		/**
		 * Address Hooks
		 */
		add_filter( 'woocommerce_order_formatted_shipping_address', array( __CLASS__, 'set_formatted_shipping_address' ), 20, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', array( __CLASS__, 'set_formatted_billing_address' ), 20, 2 );
		add_filter( 'woocommerce_formatted_address_replacements', array( __CLASS__, 'set_formatted_address' ), 20, 2 );
		add_filter( 'woocommerce_localisation_address_formats', array( __CLASS__, 'set_address_format' ), 20 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array( __CLASS__, 'set_user_address' ), 10, 3 );

		if ( self::has_map() ) {
			add_action( 'wp_footer', array( __CLASS__, 'add_form' ), 50 );

			add_action( 'wp_ajax_nopriv_woocommerce_gzd_dhl_parcelfinder_search', array( __CLASS__, 'ajax_search' ) );
			add_action( 'wp_ajax_woocommerce_gzd_dhl_parcelfinder_search', array( __CLASS__, 'ajax_search' ) );

			add_action( 'wp_ajax_nopriv_woocommerce_gzd_dhl_parcel_locator_validate_address', array( __CLASS__, 'ajax_validate_address' ) );
			add_action( 'wp_ajax_woocommerce_gzd_dhl_parcel_locator_validate_address', array( __CLASS__, 'ajax_validate_address' ) );
		}
	}

	public static function manipulate_address_fields( $fields ) {
		global $wp;

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return $fields;
		}

		$nonce_key = ( isset( $_REQUEST['woocommerce-edit-address-nonce'] ) ? 'woocommerce-edit-address-nonce' : '_wpnonce' );

		if ( empty( $_POST['action'] ) || 'edit_address' !== $_POST['action'] || empty( $_REQUEST[ $nonce_key ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_key ], 'woocommerce-edit_address' ) ) {
			return $fields;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return $fields;
		}

		if ( isset( $_POST['shipping_address_type'] ) && 'dhl' === $_POST['shipping_address_type'] ) {
			$fields['shipping_address_1']['label'] = self::get_type_text( ' / ' );
		}

		return $fields;
	}

	public static function validate_address_postnumber( $value ) {
		$shipping_country      = isset( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '';
		$shipping_address_type = isset( $_POST['shipping_address_type'] ) ? wc_clean( $_POST['shipping_address_type'] ) : 'regular';

		// Not a supported country
		if ( ! in_array( $shipping_country, self::get_supported_countries() ) ) {
			return '';
		}

		if ( 'dhl' === $shipping_address_type ) {
			$args = array(
				'address_1'  => isset( $_POST['shipping_address_1'] ) ? wc_clean( $_POST['shipping_address_1'] ) : '',
				'postnumber' => isset( $_POST['shipping_dhl_postnumber'] ) ? wc_clean( $_POST['shipping_dhl_postnumber'] ) : '',
				'postcode'   => isset( $_POST['shipping_postcode'] ) ? wc_clean( $_POST['shipping_postcode'] ) : '',
				'city'       => isset( $_POST['shipping_city'] ) ? wc_clean( $_POST['shipping_city'] ) : '',
				'country'    => isset( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '',
			);

			$result = self::validate_address( $args );

			if ( is_wp_error( $result ) ) {
				return '';
			}
		} else {
			return '';
		}

		return $value;
	}

	public static function validate_address_fields( $value ) {
		$shipping_country      = isset( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '';
		$shipping_address_type = isset( $_POST['shipping_address_type'] ) ? wc_clean( $_POST['shipping_address_type'] ) : 'regular';

		// Not a supported country
		if ( ! in_array( $shipping_country, self::get_supported_countries() ) ) {
			return 'regular';
		}

		if ( ! array_key_exists( $shipping_address_type, self::get_address_types() ) ) {
			wc_add_notice( __( 'Invalid address type.', 'woocommerce-germanized-dhl' ), 'error' );
		}

		if ( 'dhl' === $shipping_address_type ) {
			$args = array(
				'address_1'  => isset( $_POST['shipping_address_1'] ) ? wc_clean( $_POST['shipping_address_1'] ) : '',
				'postnumber' => isset( $_POST['shipping_dhl_postnumber'] ) ? wc_clean( $_POST['shipping_dhl_postnumber'] ) : '',
				'postcode'   => isset( $_POST['shipping_postcode'] ) ? wc_clean( $_POST['shipping_postcode'] ) : '',
				'city'       => isset( $_POST['shipping_city'] ) ? wc_clean( $_POST['shipping_city'] ) : '',
				'country'    => isset( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '',
			);

			$result = self::validate_address( $args );

			if ( is_wp_error( $result ) ) {
				foreach( $result->get_error_messages() as $mesage ) {
					wc_add_notice( $mesage, 'error' );
				}
			}
 		}

		return $value;
	}

	/**
	 * @param $send_to_pickup
	 * @param $types
	 * @param Shipment $shipment
	 */
	public static function shipment_has_pickup( $send_to_pickup, $types, $shipment ) {
		$data = $shipment->get_address();

		if ( isset( $data['address_type'] ) && 'dhl' === $data['address_type'] ) {
			foreach( $types as $type ) {
				if ( wc_gzd_dhl_is_pickup_type( $shipment->get_address_1(), $type ) ) {
					return true;
				}
			}
		}

		return $send_to_pickup;
	}

	public static function get_postnumber_by_shipment( $shipment ) {
		if ( is_numeric( $shipment ) ) {
			$shipment = wc_gzd_get_shipment( $shipment );
		}

		$postnumber = '';

		if ( $shipment ) {
			$address = $shipment->get_address();

			if ( isset( $address['dhl_postnumber'] ) ) {
				$postnumber = $address['dhl_postnumber'];
			}
		}

		return $postnumber;
	}

	/**
	 * @param WC_Order $order
	 * @param $data
	 */
	public static function maybe_remove_order_data( $order, $data ) {
		if ( ! self::order_has_pickup( $order ) ) {
			$order->delete_meta_data( '_shipping_dhl_postnumber' );
			$order->update_meta_data( '_shipping_dhl_address_type', 'regular' );
		}
	}

	public static function get_supported_countries() {
		/**
		 * Filter to enable DHL parcel shop delivery for certain countries.
		 *
		 * @since 1.8.5
		 *
		 * @param array $country_codes Array of country codes which support DHL parcel shop delivery.
		 */
		$codes = apply_filters( 'woocommerce_gzd_dhl_parcel_locator_countries', array( 'DE', 'AT' ) );

		return $codes;
	}

	/**
	 * @param $data
	 * @param $type
	 * @param WC_Order $order
	 *
	 * @return mixed
	 */
	public static function add_order_address_data( $data, $type, $order ) {
		if ( 'shipping' === $type ) {
			if ( self::order_has_pickup( $order ) ) {
				$data['dhl_postnumber'] = self::get_postnumber_by_order( $order );
				$data['address_type']   = self::get_shipping_address_type_by_order( $order );
			}
		}

		return $data;
	}

	public static function set_address_format( $formats ) {
		foreach( self::get_supported_countries() as $country ) {

			if ( ! array_key_exists( $country, $formats ) ) {
				continue;
			}

			$format = $formats[ $country ];
			$format = str_replace( "{name}", "{name}\n{dhl_postnumber}", $format );

			$formats[ $country ] = $format;
		}

		return $formats;
	}

	public static function set_formatted_shipping_address( $fields, $order ) {
		if ( ! empty( $fields ) && is_array( $fields ) ) {
			$fields['dhl_postnumber'] = '';

			if ( wc_gzd_dhl_order_has_pickup( $order ) ) {
				$fields['dhl_postnumber'] = self::get_postnumber_by_order( $order );
			}
		}

		return $fields;
	}

	public static function get_postnumber_by_order( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$post_number = '';

		if ( $order ) {
			if ( $order->get_meta( '_shipping_dhl_postnumber' ) ) {
				$post_number = $order->get_meta( '_shipping_dhl_postnumber' );
			}
		}

		return apply_filters( 'woocommerce_gzd_dhl_order_postnumber', $post_number, $order );
	}

	public static function get_shipping_address_type_by_order( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$address_type = 'regular';

		if ( $order ) {
			if ( $type = $order->get_meta( '_shipping_address_type' ) ) {
				if ( array_key_exists( $type, self::get_address_types() ) ) {
					$address_type = $type;
				}
			}
		}

		return $address_type;
	}

	public static function order_has_pickup( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$has_pickup = false;

		if ( $order ) {
			$address_type = self::get_shipping_address_type_by_order( $order );
			$number       = self::get_postnumber_by_order( $order );
			$type         = self::get_pickup_type_by_order( $order );
			$country      = $order->get_shipping_country();

			if ( ! empty( $country ) && in_array( $country, self::get_supported_countries() ) && 'dhl' === $address_type && ! empty( $number ) && ! empty( $type ) ) {
				$has_pickup = true;
			}
		}

		return $has_pickup;
	}

	public static function get_pickup_type_by_order( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$pickup_type = '';

		if ( $order ) {
			if ( $address = $order->get_shipping_address_1() ) {
				$pickup_types = wc_gzd_dhl_get_pickup_types();

				foreach( $pickup_types as $pickup_tmp_type => $label ) {
					if ( wc_gzd_dhl_is_pickup_type( $address, $pickup_tmp_type ) ) {
						$pickup_type = $pickup_tmp_type;
						break;
					}
				}
			}
		}

		return apply_filters( 'woocommerce_gzd_dhl_order_pickup_type', $pickup_type, $order );
	}

	public static function get_postnumber_by_user( $user ) {
		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'ID', $user );
		}

		$post_number = '';

		if ( $user ) {

			if ( get_user_meta( $user->ID, 'shipping_dhl_postnumber', true ) ) {
				$post_number = get_user_meta( $user->ID, 'shipping_dhl_postnumber', true );
			}

			if ( get_user_meta( $user->ID, 'shipping_parcelshop_post_number', true ) ) {
				$post_number = get_user_meta( $user->ID, 'shipping_parcelshop_post_number', true );
			}
		}

		return apply_filters( 'woocommerce_gzd_dhl_user_postnumber', $post_number, $user );
	}

	public static function set_formatted_billing_address( $fields, $order ) {

		if ( ! empty( $fields ) && is_array( $fields ) ) {
			$fields['dhl_postnumber'] = '';
		}

		return $fields;
	}

	public static function set_formatted_address( $placeholder, $args ) {
		if ( isset( $args['dhl_postnumber'] ) ) {
			$placeholder['{dhl_postnumber}']       = $args['dhl_postnumber'];
			$placeholder['{dhl_postnumber_upper}'] = strtoupper( $args['dhl_postnumber'] );
		} else {
			$placeholder['{dhl_postnumber}'] = '';
			$placeholder['{dhl_postnumber}'] = '';
		}
		return $placeholder;
	}

	public static function set_user_address( $address, $customer_id, $name ) {
		if ( 'shipping' === $name ) {
			if ( $post_number = self::get_postnumber_by_user( $customer_id ) ) {
				$address['dhl_postnumber'] = $post_number;
			}
		}
		return $address;
	}

	public static function manipulate_checkout_fields() {
		if ( 'dhl' === WC()->checkout()->get_value( 'shipping_address_type' ) ) {
			add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'switch_street_label' ), 10, 1 );
		} else {
			remove_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'switch_street_label' ), 10 );
		}
	}

	public static function switch_street_label( $fields ) {
		$fields['shipping']['shipping_address_1']['label'] = self::get_type_text( ' / ' );

		return $fields;
	}

	protected static function get_excluded_shipping_methods() {
		return (array) self::get_setting( 'shipping_methods_excluded' );
	}

	protected static function shipping_method_supports_pickup( $method ) {
		$slug             = wc_gzd_dhl_get_shipping_method_slug( $method );
		$methods          = self::get_excluded_shipping_methods();

		if ( ! empty( $methods ) && in_array( $slug, $methods ) ) {
			return false;
		}

		return true;
	}

	public static function validate_checkout() {
		$data   = WC_Checkout::instance()->get_posted_data();
		$errors = new WP_Error();

		// Validate input only if "ship to different address" flag is set
		if ( ! isset( $data['ship_to_different_address'] ) || ! $data['ship_to_different_address'] ) {
			return;
		}

		$shipping_country      = isset( $data['shipping_country'] ) ? $data['shipping_country'] : '';
		$shipping_address_type = isset( $data['shipping_address_type'] ) ? wc_clean( $data['shipping_address_type'] ) : 'regular';

		// Not a supported country
		if ( ! in_array( $shipping_country, self::get_supported_countries() ) ) {
			$data['shipping_dhl_postnumber'] = '';
			return;
		}

		if ( ! array_key_exists( $shipping_address_type, self::get_address_types() ) ) {
			$errors->add( 'validation', __( 'Invalid address type.', 'woocommerce-germanized-dhl' ) );
		}

		if ( 'dhl' === $shipping_address_type ) {

			$methods   = WC()->session->get( 'chosen_shipping_methods' );
			$available = true;

			foreach( $methods as $key => $method ) {
				if ( ! self::shipping_method_supports_pickup( $method ) ) {
					$available = false;

					$errors->add( 'validation', __( 'Sorry but your current shipping method does not supports delivery to pickup locations.', 'woocommerce-germanized-dhl' ) );
					break;
				}
			}

			if ( $available ) {

				$args = array(
					'address_1'  => isset( $data['shipping_address_1'] ) ? wc_clean( $data['shipping_address_1'] ) : '',
					'postnumber' => isset( $data['shipping_dhl_postnumber'] ) ? wc_clean( $data['shipping_dhl_postnumber'] ) : '',
					'postcode'   => isset( $data['shipping_postcode'] ) ? wc_clean( $data['shipping_postcode'] ) : '',
					'city'       => isset( $data['shipping_city'] ) ? wc_clean( $data['shipping_city'] ) : '',
					'country'    => isset( $data['shipping_country'] ) ? wc_clean( $data['shipping_country'] ) : '',
				);

				$result = self::validate_address( $args );

				if ( is_wp_error( $result ) ) {
					foreach( $result->get_error_messages() as $mesage ) {
						$errors->add( 'validation', $mesage );
					}
				}
			}
		}

		if ( $errors->has_errors() ) {
			foreach( $errors->get_error_messages() as $message ) {
				wc_add_notice( $message, 'error' );
			}
		}
	}

	protected static function validate_address( $args ) {
		$args = wp_parse_args( $args, array(
			'address_1'  => '',
			'postnumber' => '',
			'postcode'   => '',
			'city'       => '',
			'country'    => '',
		) );

		$error          = new WP_Error();
		$is_packstation = false;

		if ( wc_gzd_dhl_is_pickup_type( $args['address_1'], 'packstation' ) ) {
			$is_packstation = true;
		} elseif( wc_gzd_dhl_is_pickup_type( $args['address_1'], 'parcelshop' ) ) {

		} elseif( wc_gzd_dhl_is_pickup_type( $args['address_1'], 'postoffice' ) ) {

		} else {
			// Try validation
			$valid = false;

			if ( ! $valid ) {
				$error->add( 'validation', sprintf( __( 'Please indicate shipment to %s by one of the following values: %s.', 'woocommerce-germanized-dhl' ), self::get_type_text( ' / ' ), implode( ', ', array_values( array_unique( wc_gzd_dhl_get_pickup_types() ) ) ) ) );
			}
		}

		if ( $is_packstation ) {
			$post_number_len = strlen( $args['postnumber'] );

			if ( empty( $args['postnumber'] ) ) {
				$error->add( 'validation', __( 'Your DHL customer number (Post number) is needed to ship to a packstation.', 'woocommerce-germanized-dhl' ) );
			} elseif( $post_number_len < 6 || $post_number_len > 12 ) {
				$error->add( 'validation', __( 'Your DHL customer number (Post number) is not valid. Please check your number.', 'woocommerce-germanized-dhl' ) );
			}
		}

		return $error->has_errors() ? $error : true;
	}

	public static function add_inline_styles() {

		// load scripts on checkout page only
		if ( ! is_checkout() && ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		echo '<style type="text/css">#shipping_dhl_postnumber_field, #shipping_address_type_field { display: none; }</style>';
	}

	public static function add_scripts() {

		// load scripts on checkout page only
		if ( ! is_checkout() && ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		$deps   = array( 'jquery' );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( is_checkout() ) {
			array_push( $deps, 'wc-checkout' );
		}

		$pickup_types = wc_gzd_dhl_get_pickup_types();

		wp_register_script( 'wc-gzd-parcel-locator-dhl', Package::get_assets_url() . '/js/parcel-locator' . $suffix . '.js', $deps, Package::get_version(), true );
		wp_register_script( 'wc-gzd-parcel-finder-dhl', Package::get_assets_url() . '/js/parcel-finder' . $suffix . '.js', array( 'jquery-blockui', 'wc-gzd-parcel-locator-dhl' ), Package::get_version(), true );
		wp_register_style( 'wc-gzd-parcel-finder-dhl', Package::get_assets_url() . '/css/parcel-finder' . $suffix . '.css', array(), Package::get_version() );

		wp_localize_script( 'wc-gzd-parcel-locator-dhl', 'wc_gzd_dhl_parcel_locator_params', array(
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'parcel_locator_nonce' => wp_create_nonce('dhl-parcel-locator' ),
			'supported_countries'  => self::get_supported_countries(),
			'excluded_methods'     => self::get_excluded_shipping_methods(),
			'i18n'                 => array_merge( wc_gzd_dhl_get_pickup_types(), array() ),
			'wrapper'              => is_checkout() ? '.woocommerce-checkout' : '.woocommerce-address-fields',
		) );

		if ( self::has_map() ) {
			wp_localize_script( 'wc-gzd-parcel-finder-dhl', 'wc_gzd_dhl_parcel_finder_params', array(
				'parcel_finder_nonce' => wp_create_nonce('dhl-parcel-finder' ),
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'packstation_icon'	  => Package::get_assets_url() . '/img/packstation.png',
				'parcelshop_icon'	  => Package::get_assets_url() . '/img/parcelshop.png',
				'postoffice_icon'	  => Package::get_assets_url() . '/img/post_office.png',
				'api_key'             => self::get_setting( 'map_api_key' ),
				'wrapper'             => is_checkout() ? '.woocommerce-checkout' : '.woocommerce-address-fields',
				'i18n'                => array_merge( wc_gzd_dhl_get_pickup_types(), array(
					'opening_times'     => __( 'Opening Times', 'woocommerce-germanized-dhl' ),
					'monday'		    => __( 'Monday', 'woocommerce-germanized-dhl' ),
					'tueday'		    => __( 'Tuesday', 'woocommerce-germanized-dhl' ),
					'wednesday'		    => __( 'Wednesday', 'woocommerce-germanized-dhl' ),
					'thrusday'		    => __( 'Thursday', 'woocommerce-germanized-dhl' ),
					'friday'			=> __( 'Friday', 'woocommerce-germanized-dhl' ),
					'satuday'			=> __( 'Saturday', 'woocommerce-germanized-dhl' ),
					'sunday'			=> __( 'Sunday', 'woocommerce-germanized-dhl' ),
					'services'			=> __( 'Services', 'woocommerce-germanized-dhl' ),
					'yes'				=> __( 'Yes', 'woocommerce-germanized-dhl' ),
					'no'				=> __( 'No', 'woocommerce-germanized-dhl' ),
					'parking'			=> __( 'Parking', 'woocommerce-germanized-dhl' ),
					'handicap'			=> __( 'Handicap Accessible', 'woocommerce-germanized-dhl' ),
					'branch'			=> __( 'Branch', 'woocommerce-germanized-dhl' ),
					'select'			=> __( 'Select ', 'woocommerce-germanized-dhl' ),
					'post_number'		=> __( 'Post Number ', 'woocommerce-germanized-dhl' ),
				) ),
			) );

			wp_enqueue_script( 'wc-gzd-parcel-finder-dhl' );
			wp_enqueue_style( 'wc-gzd-parcel-finder-dhl' );
		}

		wp_enqueue_script( 'wc-gzd-parcel-locator-dhl' );
	}

	protected static function get_setting( $key ) {
		$setting = Package::get_setting( 'parcel_pickup_' . $key );

		return $setting;
	}

	public static function is_enabled() {
		return self::is_packstation_enabled() || self::is_parcelshop_enabled() || self::is_postoffice_enabled();
	}

	public static function is_postoffice_enabled() {
		return 'yes' === self::get_setting( 'postoffice_enable' );
	}

	public static function is_packstation_enabled() {
		return 'yes' === self::get_setting( 'packstation_enable' );
	}

	public static function is_parcelshop_enabled() {
		return 'yes' === self::get_setting( 'parcelshop_enable' );
	}

	public static function has_map() {
		$api_key = self::get_setting( 'map_api_key' );

		return ( 'yes' === self::get_setting( 'map_enable' ) && ! empty( $api_key ) );
	}

	public static function get_max_results() {
		return self::get_setting( 'map_max_results' );
	}

	protected static function get_type_text( $sep = '&amp;', $plural = true ) {
		$search_types = '';

		if ( self::is_packstation_enabled() ) {
			$search_types .= __( 'Packstation', 'woocommerce-germanized-dhl' );
		}

		if ( self::is_parcelshop_enabled() || self::is_postoffice_enabled() ) {
			$branch_type   = ( $plural ) ? __( 'Branches', 'woocommerce-germanized-dhl' ) : __( 'Branch', 'woocommerce-germanized-dhl' );
			$search_types .= ( ! empty( $search_types ) ? ' ' . $sep . ' ' . $branch_type : $branch_type );
		}

		return $search_types;
	}

	public static function add_admin_shipping_fields( $fields ) {
		$fields['address_type'] = array(
			'label'        => __( 'Address Type', 'woocommerce-germanized-dhl' ),
			'type'         => 'select',
			'show'         => false,
			'options'	   => self::get_address_types()
		);

		$fields['dhl_postnumber'] = array(
			'label'        => __( 'DHL customer number (Post number)', 'woocommerce-germanized-dhl' ),
			'show'         => false,
			'type'         => 'text',
		);

		return $fields;
	}

	public static function get_address_types() {
		return array(
			'regular' => __( 'Regular Address', 'woocommerce-germanized-dhl' ),
			'dhl'     => self::get_type_text( ' / ' )
		);
	}

	public static function add_shipping_fields( $fields ) {
		$fields['shipping_address_type'] = array(
			'label'        => __( 'Address Type', 'woocommerce-germanized-dhl' ),
			'required'     => true,
			'type'         => 'select',
			'class'        => array( 'shipping-dhl-address-type' ),
			'clear'        => true,
			'priority'     => 5,
			'options'	   => self::get_address_types(),
		);

		$fields['shipping_dhl_postnumber'] = array(
			'label'        => __( 'DHL customer number (Post number)', 'woocommerce-germanized-dhl' ),
			'required'     => false,
			'type'         => 'text',
			'class'        => array( 'shipping-dhl-postnumber' ),
			'description'  => __( 'Not yet a DHL customer?', 'woocommerce-germanized-dhl' ) . '<br/><a href="">' . __( 'Register now', 'woocommerce-germanized-dhl' ) . '</a>',
			'clear'        => true,
			'priority'     => 45,
		);

		$fields['shipping_address_1']['custom_attributes']                             = ( isset( $fields['shipping_address_1']['custom_attributes'] ) ? $fields['shipping_address_1']['custom_attributes'] : array() );
		$fields['shipping_address_1']['custom_attributes']['data-label-dhl']           = apply_filters( 'woocommerce_gzd_dhl_pickup_type_label', self::get_type_text( ' / ', false ) );
		$fields['shipping_address_1']['custom_attributes']['data-label-regular']       = $fields['shipping_address_1']['label'];
		$fields['shipping_address_1']['custom_attributes']['data-placeholder-dhl']     = apply_filters( 'woocommerce_gzd_dhl_pickup_type_placeholder', sprintf( __( 'e.g. %s 256' ), wc_gzd_dhl_get_pickup_type('packstation' ) ) );
		$fields['shipping_address_1']['custom_attributes']['data-placeholder-regular'] = isset( $fields['shipping_address_1']['placeholder'] ) ? $fields['shipping_address_1']['placeholder'] : '';
		$fields['shipping_address_1']['custom_attributes']['data-desc-dhl']            = self::get_button();

		return $fields;
	}

	protected static function get_icon( $type = 'packstation' ) {
		return Package::get_assets_url() . '/img/' . $type . '.png';
	}

	protected static function get_button() {
		$text = sprintf( __( 'Search %s', 'woocommerce-germanized-dhl' ), self::get_type_text() );

		if ( self::has_map() ) {
			return '<a class="gzd-dhl-parcel-shop-modal" href="javascript:;">' . $text . '</a>';
		} else {
			return '<a href="" class="dhl-parcel-finder-plain-link">' . $text . '</a>';
		}
	}

	public static function add_button() {
		echo self::get_button();
	}

	public static function add_form() {

		if ( ! is_checkout() && ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		$args = array(
			'img_packstation'        => self::get_icon( 'packstation' ),
			'img_postoffice'         => self::get_icon( 'post_office' ),
			'img_parcelshop'         => self::get_icon( 'parcelshop' ),
			'is_packstation_enabled' => self::is_packstation_enabled(),
			'is_postoffice_enabled'  => self::is_postoffice_enabled(),
			'is_parcelshop_enabled'  => self::is_parcelshop_enabled(),
		);

		wc_get_template( 'checkout/dhl/parcel-finder.php', $args, Package::get_template_path(), Package::get_path() . '/templates/' );
	}

	public static function ajax_validate_address() {

		check_ajax_referer( 'dhl-parcel-locator', 'security' );

		$country	 = isset( $_POST['country'] ) ? wc_clean( $_POST['country'] ) : Package::get_base_country();
		$postcode	 = isset( $_POST['postcode'] ) ? wc_clean( $_POST['postcode'] ) : '';
		$city	     = isset( $_POST['city'] ) ? wc_clean( $_POST['city'] ) : '';
		$address	 = isset( $_POST['address'] ) ? wc_clean( $_POST['address'] ) : '';

		wp_send_json( array(
			'valid'        => true,
			'address'      => $address,
			'success'      => true,
		) );
	}

	public static function ajax_search() {

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

					if ( ( 'packStation' === $value->shopType && self::is_packstation_enabled() && $packstation_filter ) ||
					     ( 'parcelShop' === $value->shopType && self::is_parcelshop_enabled() && $branch_filter ) ||
					     ( 'postOffice' === $value->shopType && self::is_postoffice_enabled() && $branch_filter )
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

					if ( $res_count >= self::get_max_results() ) {
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
			$error = sprintf( __( 'There was an error while communicating with DHL. Please manually find a %s or %s.', 'woocommerce-germanized-dhl' ), '<a href="">' . __( 'parcel shop', 'woocommerce-germanized-dhl' ) . '</a>', '<a class="dhl-retry-search" href="#">' . __( 'retry', 'woocommerce-germanized-dhl' ) . '</a>' );

			wp_send_json( array(
				'success' => false,
				'message' => Package::is_debug_mode() ? $e->getMessage() : $error,
			) );
		}

		wp_die();
	}
}