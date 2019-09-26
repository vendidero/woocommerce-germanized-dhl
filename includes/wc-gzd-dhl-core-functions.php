<?php
/**
 * WooCommerce Germanized DHL Shipment Functions
 *
 * Functions for shipment specific things.
 *
 * @package WooCommerce_Germanized/DHL/Functions
 * @version 3.4.0
 */

use Vendidero\Germanized\DHL\Label;
use Vendidero\Germanized\DHL\LabelQuery;
use Vendidero\Germanized\DHL\Order;
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\DHL\ParcelLocator;
use Vendidero\Germanized\DHL\ShippingMethod;
use Vendidero\Germanized\DHL\ParcelServices;
use Vendidero\Germanized\DHL\LabelFactory;
use Vendidero\Germanized\DHL\SimpleLabel;
use Vendidero\Germanized\DHL\ReturnLabel;

use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\ShipmentFactory;

defined( 'ABSPATH' ) || exit;

function wc_gzd_dhl_aformat_preferred_api_time( $time ) {
	return str_replace( array( ':', '-' ), '', $time );
}

function wc_gzd_dhl_validate_api_field( $value, $type = 'int', $min_len = 0, $max_len = 0 ) {
    switch ( $type ) {
        case 'string':
            if ( ( strlen( $value ) < $min_len ) || ( strlen( $value ) > $max_len ) ) {
                if ( $min_len === $max_len ) {
                    throw new Exception( sprintf( __( 'The value must be %s characters.', 'woocommerce-germanized-dhl' ), $min_len ) );
                } else {
                    throw new Exception( sprintf( __( 'The value must be between %s and %s characters.', 'woocommerce-germanized-dhl' ), $min_len, $max_len ) );
                }
            }
            break;
        case 'int':
            if ( ! is_numeric( $value ) ) {
                throw new Exception( __( 'The value must be a number', 'woocommerce-germanized-dhl' ) );
            }
            break;
    }
}

function wc_gzd_dhl_get_preferred_times_select_options( $times ) {
	$preferred_times = array( 0 => _x( 'None', 'time context', 'woocommerce-germanized-dhl' ) );

	if ( ! empty( $times ) ) {
		$preferred_times = $times;
	}

	return $preferred_times;
}

function wc_gzd_dhl_get_preferred_days_select_options( $days, $current = '' ) {
	$preferred_days = array( 0 => _x( 'None', 'day context', 'woocommerce-germanized-dhl' ) );

	if ( ! empty( $days ) ) {
		$days = array_keys( $days );

		foreach( $days as $day ) {

			if ( empty( $day ) ) {
				continue;
			}

			$formatted_day  = date_i18n( wc_date_format(), strtotime( $day ) );
			$preferred_days = array_merge( $preferred_days, array( $day => $formatted_day ) );
		}
	}

	if ( ! empty( $current ) ) {
		$preferred_days[ $current ] = date_i18n( wc_date_format(), strtotime( $current ) );
	}

	return $preferred_days;
}

function wc_gzd_dhl_get_duties() {
	$duties = array(
		'DDU' => __( 'Delivery Duty Unpaid', 'woocommerce-germanized-dhl' ),
		'DDP' => __( 'Delivery Duty Paid', 'woocommerce-germanized-dhl' ),
		'DXV' => __( 'Delivery Duty Paid (excl. VAT )', 'woocommerce-germanized-dhl' ),
		'DDX' => __( 'Delivery Duty Paid (excl. Duties, taxes and VAT)', 'woocommerce-germanized-dhl' )
	);

	return $duties;
}

function wc_gzd_dhl_get_visual_min_ages() {
	$visual_age = array(
		'0'   => _x( 'None', 'age context', 'woocommerce-germanized-dhl' ),
		'A16' => __( 'Minimum age of 16', 'woocommerce-germanized-dhl' ),
		'A18' => __( 'Minimum age of 18', 'woocommerce-germanized-dhl' )
	);

	return $visual_age;
}

function wc_gzd_dhl_get_ident_min_ages() {
	return wc_gzd_dhl_get_visual_min_ages();
}

function wc_gzd_dhl_get_label_reference( $reference_type, $placeholders = array() ) {
	$text = $reference_type;

	return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
}

/**
 * Standard way of retrieving shipments based on certain parameters.
 *
 * @since  2.6.0
 * @param  array $args Array of args (above).
 * @return Label[]|stdClass Number of pages and an array of order objects if
 *                             paginate is true, or just an array of values.
 */
function wc_gzd_dhl_get_labels( $args ) {
    $query = new LabelQuery( $args );
    return $query->get_labels();
}

function wc_gzd_dhl_get_current_shipping_method() {
	$chosen_shipping_methods = WC()->session ? WC()->session->get( 'chosen_shipping_methods' ) : array();

	if ( ! empty( $chosen_shipping_methods ) ) {
		$method = wc_gzd_dhl_get_shipping_method( $chosen_shipping_methods[0] );

		return $method;
	}

	return false;
}

function wc_gzd_dhl_get_services() {
    return array(
        'PreferredTime',
        'PreferredLocation',
        'PreferredNeighbour',
        'PreferredDay',
	    'VisualCheckOfAge',
        'Personally',
        'NoNeighbourDelivery',
        'NamedPersonOnly',
        'Premium',
        'AdditionalInsurance',
        'BulkyGoods',
        'IdentCheck',
        'CashOnDelivery',
	    'ParcelOutletRouting'
    );
}

function wc_gzd_dhl_get_shipping_method( $instance_id ) {

	if ( ! is_numeric( $instance_id ) ) {
		$expl        = explode( ':', $instance_id );
		$instance_id = ( ( ! empty( $expl ) && sizeof( $expl ) > 1 ) ? (int) $expl[1] : $instance_id );
	}

	if ( empty( $instance_id ) ) {
		return false;
	}

	// Make sure shipping zones are loaded
	include_once WC_ABSPATH . 'includes/class-wc-shipping-zones.php';

	if ( $method = WC_Shipping_Zones::get_shipping_method( $instance_id ) ) {
		return new ShippingMethod( $method );
	}

	return false;
}

function wc_gzd_dhl_get_preferred_services() {
	return array(
		'PreferredTime',
		'PreferredLocation',
		'PreferredNeighbour',
		'PreferredDay',
	);
}

function wc_gzd_dhl_get_return_products() {
	return array(
		'V01PAK',
		'V01PRIO',
		'V86PARCEL',
		'V55PAK'
	);
}

function wc_gzd_dhl_get_pickup_types() {
    return array(
    	'packstation' => __( 'Packstation', 'woocommerce-germanized-dhl' ),
	    'postoffice'  => __( 'Postfiliale', 'woocommerce-germanized-dhl' ),
	    'parcelshop'  => __( 'Postfiliale', 'woocommerce-germanized-dhl' )
    );
}

function wc_gzd_dhl_get_working_days() {
	return array(
		'Mon' => __( 'mon', 'woocommerce-germanized-dhl' ),
		'Tue' => __( 'tue', 'woocommerce-germanized-dhl' ),
		'Wed' => __( 'wed', 'woocommerce-germanized-dhl' ),
		'Thu' => __( 'thu', 'woocommerce-germanized-dhl' ),
		'Fri' => __( 'fri', 'woocommerce-germanized-dhl' ),
		'Sat' => __( 'sat', 'woocommerce-germanized-dhl' )
	);
}

function wc_gzd_dhl_is_pickup_type( $maybe_type, $type = 'packstation' ) {
	$label = wc_gzd_dhl_get_pickup_type( $type );

	if ( ! $label ) {
		return false;
	}

	$label      = strtolower( trim( $label ) );
	$maybe_type = strtolower( trim( $maybe_type ) );

	if ( strpos( $maybe_type, $label ) !== false ) {
		return true;
	}

	return false;
}

function wc_gzd_dhl_get_excluded_working_days() {
	$work_days = array(
		'mon',
		'tue',
		'wed',
		'thu',
		'fri',
		'sat'
	);

	$excluded = array();

	foreach ( $work_days as $value ) {
		if ( ParcelServices::is_preferred_day_excluded( $value ) ) {
			$excluded[] = $value;
		}
	}

	return $excluded;
}

function wc_gzd_dhl_order_has_pickup( $order ) {
	return ParcelLocator::order_has_pickup( $order );
}

function wc_gzd_dhl_get_pickup_type( $type ) {
	$types = wc_gzd_dhl_get_pickup_types();

	if ( array_key_exists( $type, $types ) ) {
		return $types[ $type ];
	} elseif( in_array( $type, $types ) ) {
		return $type;
	} else {
		return false;
	}
}

function wc_gzd_dhl_validate_label_args( $shipment, $args = array() ) {

	$args = wp_parse_args( $args, array(
		'preferred_day'         => '',
		'preferred_time_start'  => '',
		'preferred_time_end'    => '',
		'preferred_location'    => '',
		'preferred_neighbor'    => '',
		'ident_date_of_birth'   => '',
		'ident_min_age'         => '',
		'visual_min_age'        => '',
		'email_notification'    => 'no',
		'has_return'            => 'no',
		'codeable_address_only' => 'no',
		'cod_total'             => 0,
		'duties'                => '',
		'services'              => array(),
		'return_address'        => array(),
	) );

	$error = new WP_Error();

	if ( ! $shipment_order = $shipment->get_order() ) {
		$error->add( 500, sprintf( __( 'Shipment order #%s does not exist', 'woocommerce-germanized-dhl' ), $shipment->get_order_id() ) );
	}

	$dhl_order = wc_gzd_dhl_get_order( $shipment_order );

	// Do only allow valid services
	if ( ! empty( $args['services'] ) ) {
		$args['services'] = array_intersect( $args['services'], wc_gzd_dhl_get_services() );
	}

	// Add default return address fields
	if ( empty( $args['return_address'] ) && 'yes' === Package::get_setting( 'generate_return_label' ) ) {
		$args['has_return']     = 'yes';
		$args['return_address'] = wp_parse_args( $args['return_address'], array(
			'name'          => Package::get_setting( 'return_address_name' ),
			'company'       => Package::get_setting( 'return_address_company' ),
			'street'        => Package::get_setting( 'return_address_street' ),
			'street_number' => Package::get_setting( 'return_address_street_no' ),
			'postcode'      => Package::get_setting( 'return_address_postcode' ),
			'city'          => Package::get_setting( 'return_address_city' ),
			'state'         => Package::get_setting( 'return_address_state' ),
			'country'       => Package::get_setting( 'return_address_country' ),
		) );
	}

	// Check if return address has empty mandatory fields
	if ( 'yes' === $args['has_return'] ) {
		$args['return_address'] = wp_parse_args( $args['return_address'], array(
			'name'          => '',
			'company'       => '',
			'street'        => '',
			'street_number' => '',
			'postcode'      => '',
			'city'          => '',
			'state'         => '',
			'country'       => Package::get_setting( 'return_address_country' ),
		) );

		$mandatory = array(
			'street'     => __( 'Street', 'woocommerce-germanized-dhl' ),
			'postcode'   => __( 'Postcode', 'woocommerce-germanized-dhl' ),
			'city'       => __( 'City', 'woocommerce-germanized-dhl' ),
		);

		foreach( $mandatory as $mand => $title ) {
			if ( empty( $args['return_address'][ $mand ] ) ) {
				$error->add( 500, sprintf( __( '%s of the return address is a mandatory field.', 'woocommerce-germanized-dhl' ), $title ) );
			}
		}

		if ( empty( $args['return_address']['name'] ) && empty( $args['return_address']['company'] ) ) {
			$error->add( 500, __( 'Please either add a return company or name.', 'woocommerce-germanized-dhl' ) );
		}
	} else {
		$args['return_address'] = array();
	}

	// No cash on delivery available
	if ( ! empty( $args['cod_total'] ) && ! $dhl_order->has_cod_payment() ) {
		$args['cod_total'] = 0;
	}

	if ( ! empty( $args['cod_total'] ) && $dhl_order->has_cod_payment() ) {
		$args['services'] = array_merge( $args['services'], array( 'CashOnDelivery' ) );
	}

	if ( ! empty( $args['preferred_day'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_day'], 'Y-m-d' ) ) {
		$args['services'] = array_merge( $args['services'], array( 'PreferredDay' ) );
	} else {
		if ( ! empty( $args['preferred_day'] ) && ! wc_gzd_dhl_is_valid_datetime( $args['preferred_day'], 'Y-m-d' ) ) {
			$error->add( 500, __( 'Error while parsing preferred day.', 'woocommerce-germanized-dhl' ) );
		}

		$args['services']      = array_diff( $args['services'], array( 'PreferredDay' ) );
		$args['preferred_day'] = '';
	}

	if ( ( ! empty( $args['preferred_time_start'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_time_start'], 'H:i' ) ) && ( ! empty( $args['preferred_time_end'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_time_end'], 'H:i' ) ) ) {
		$args['services'] = array_merge( $args['services'], array( 'PreferredTime' ) );
	} else {
		if ( ( ! empty( $args['preferred_time_start'] ) && ! wc_gzd_dhl_is_valid_datetime( $args['preferred_time_start'], 'H:i' ) ) || ( ! empty( $args['preferred_time_end'] ) && ! wc_gzd_dhl_is_valid_datetime( $args['preferred_time_end'], 'H:i' ) ) ) {
			$error->add( 500, __( 'Error while parsing preferred time.', 'woocommerce-germanized-dhl' ) );
		}

		$args['services']             = array_diff( $args['services'], array( 'PreferredTime' ) );
		$args['preferred_time_start'] = '';
		$args['preferred_time_end']   = '';
 	}

	if ( ! empty( $args['preferred_location'] ) ) {
		$args['services'] = array_merge( $args['services'], array( 'PreferredLocation' ) );
	} else {
		$args['services'] = array_diff( $args['services'], array( 'PreferredLocation' ) );
	}

	if ( ! empty( $args['preferred_neighbor'] ) ) {
		$args['services'] = array_merge( $args['services'], array( 'PreferredNeighbour' ) );
	} else {
		$args['services'] = array_diff( $args['services'], array( 'PreferredNeighbour' ) );
	}

	if ( ! empty( $args['visual_min_age'] ) && array_key_exists( $args['visual_min_age'], wc_gzd_dhl_get_visual_min_ages() ) ) {
		$args['services']       = array_merge( $args['services'], array( 'VisualCheckOfAge' ) );
	} else {
		if ( ! empty( $args['visual_min_age'] ) && ! array_key_exists( $args['visual_min_age'], wc_gzd_dhl_get_visual_min_ages() ) ) {
			$error->add( 500, __( 'The visual min age check is invalid.', 'woocommerce-germanized-dhl' ) );
		}

		$args['services']       = array_diff( $args['services'], array( 'VisualCheckOfAge' ) );
		$args['visual_min_age'] = '';
	}

	// In case order does not support email notification - remove parcel outlet routing
	if ( in_array( 'ParcelOutletRouting', $args['services'] ) ) {
		if ( ! $dhl_order->supports_email_notification() ) {
			$args['services'] = array_diff( $args['services'], array( 'ParcelOutletRouting' ) );
		}
	}

	if ( in_array( 'IdentCheck', $args['services'] ) ) {
		if ( ! empty( $args['ident_min_age'] ) && ! array_key_exists( $args['ident_min_age'], wc_gzd_dhl_get_ident_min_ages() ) ) {
			$error->add( 500, __( 'The ident min age check is invalid.', 'woocommerce-germanized-dhl' ) );

			$args['ident_min_age'] = '';
		}

		if ( ! empty( $args['ident_date_of_birth'] ) ) {
			if ( ! wc_gzd_dhl_is_valid_datetime( $args['ident_date_of_birth'], 'Y-m-d' ) ) {
				$error->add( 500, __( 'There was an error parsing the date of birth for the identity check.', 'woocommerce-germanized-dhl' ) );
			}
		}

		if ( empty( $args['ident_date_of_birth'] ) && empty( $args['ident_min_age'] ) ) {
			$error->add( 500, __( 'Either a minimum age or a date of birth must be added to the ident check.', 'woocommerce-germanized-dhl' ) );
		}
	} else {
		$args['ident_min_age']       = '';
		$args['ident_date_of_birth'] = '';
	}

	// We don't need duties for non-crossborder shipments
	if ( ! empty( $args['duties'] ) && ! Package::is_crossborder_shipment( $shipment->get_country() ) ) {
		unset( $args['duties'] );
	}

	if ( ! empty( $args['duties'] ) && ! array_key_exists( $args['duties'], wc_gzd_dhl_get_duties() ) ) {
		$error->add( 500, sprintf( __( '%s duties element does not exist.', 'woocommerce-germanized-dhl' ), $args['duties'] ) );
	}

	if ( $error->has_errors() ) {
		return $error;
	}

	return $args;
}

function wc_gzd_dhl_is_valid_datetime( $maybe_datetime, $format = 'Y-m-d' ) {
	if ( ! is_a( $maybe_datetime, 'DateTime' && ! is_numeric( $maybe_datetime ) ) ) {
		if ( ! DateTime::createFromFormat( $format, $maybe_datetime ) ) {
			return false;
		}
	}

	return true;
}

function wc_gzd_dhl_format_label_state( $state, $country ) {
	// If not USA or Australia, then change state from ISO code to name
	if ( ! in_array( $country, array( 'US', 'AU' ) ) ) {

		// Get all states for a country
		$states = WC()->countries->get_states( $country );

		// If the state is empty, it was entered as free text
		if ( ! empty( $states ) && ! empty( $state ) ) {
			// Change the state to be the name and not the code
			$state = $states[ $state ];

			// Remove anything in parentheses (e.g. TH)
			$ind = strpos( $state, " (" );

			if ( false !== $ind ) {
				$state = substr( $state, 0, $ind );
			}
		}
	}

	return $state;
}

/**
 * @param Shipment $shipment
 */
function wc_gzd_dhl_shipment_has_dhl( $shipment ) {
	$supports_dhl = false;

	if ( is_numeric( $shipment ) ) {
		$shipment = wc_gzd_get_shipment( $shipment );
	}

	if ( $shipment ) {
		$supports_dhl = ( 'dhl' === $shipment->get_shipping_provider() );
	}

	/**
	 * Filter to determine whether a shipment supports DHL shipment or not.
	 *
	 * @param boolean                                  $supports_dhl Whether the shipment supports DHL or not.
	 * @param Shipment $shipment The shipment object.
	 *
	 * @since 3.0.0
	 */
	return apply_filters( 'woocommerce_gzd_dhl_shipment_has_dhl', $supports_dhl, $shipment );
}

function wc_gzd_dhl_update_label( $label, $args = array() ) {
	try {
		$shipment = $label->get_shipment();

		if ( ! $shipment || ! is_a( $shipment, 'Vendidero\Germanized\Shipments\Shipment' ) ) {
			throw new Exception( __( 'Invalid shipment', 'woocommerce-germanized-dhl' ) );
		}

		if ( ! $order = $shipment->get_order() ) {
			throw new Exception( __( 'Order does not exist', 'woocommerce-germanized-dhl' ) );
		}

		$dhl_order = wc_gzd_dhl_get_order( $order );
		$args      = wp_parse_args( $args, wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment ) );

		// Add COD service if payment method matches
		$args = wc_gzd_dhl_validate_label_args( $shipment, $args );

		if ( is_wp_error( $args ) ) {
			return $args;
		}

		$label->set_props( $args );
		$label->set_shipment_id( $shipment->get_id() );

		/**
		 * Action fires before updating a DHL label.
		 *
		 * @param Label $label The label object.
		 *
		 * @since 3.0.0
		 *
		 */
		do_action( 'woocommerce_gzd_dhl_before_update_label', $label );

		$label->save();

		/**
		 * Action fires after updating a DHL label.
		 *
		 * @param Label $label The label object.
		 *
		 * @since 3.0.0
		 *
		 */
		do_action( 'woocommerce_gzd_dhl_after_update_label', $label );

	} catch ( Exception $e ) {
		return new WP_Error( 'error', $e->getMessage() );
	}

	return $label;
}

/**
 * @param SimpleLabel $parent_label
 */
function wc_gzd_dhl_get_return_label_default_args( $parent_label ) {
	$dhl_shipping_method = false;
	$defaults            = array(
		'shipment_id' => $parent_label->get_shipment_id(),
	);

	if ( $shipment = $parent_label->get_shipment() ) {
		$shipping_method     = $shipment->get_shipping_method();
		$dhl_shipping_method = wc_gzd_dhl_get_shipping_method( $shipping_method );

		$defaults['sender_address'] = $shipment->get_address();
	}

	return $defaults;
}

function wc_gzd_dhl_validate_return_label_args( $parent_label, $args = array() ) {
	return $args;
}

/**
 * @param Order $dhl_order
 * @param Shipment $shipment
 */
function wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment ) {

	$shipping_method     = $shipment->get_shipping_method();
	$dhl_shipping_method = wc_gzd_dhl_get_shipping_method( $shipping_method );
	$shipment_weight     = $shipment->get_weight();

	$defaults = array(
		'dhl_product'           => wc_gzd_dhl_get_default_product( $shipment->get_country(), $dhl_shipping_method ),
		'services'              => array(),
		'codeable_address_only' => Package::get_setting( 'label_address_codeable_only', $dhl_shipping_method ),
		'weight'                => empty( $shipment_weight ) ? Package::get_setting( 'label_default_shipment_weight', $dhl_shipping_method ) : wc_get_weight( $shipment_weight, 'kg' ),
	);

	if ( $dhl_order->supports_email_notification() ) {
		$defaults['email_notification'] = 'yes';
	}

	if ( $dhl_order->has_cod_payment() ) {
		$defaults['cod_total'] = $shipment->get_total();
	}

	if ( Package::is_crossborder_shipment( $shipment->get_country() ) ) {

		$defaults['duties'] = Package::get_setting( 'label_default_duty', $dhl_shipping_method );

	} elseif ( Package::is_shipping_domestic( $shipment->get_country() ) ) {

		if ( Package::base_country_supports( 'services' ) ) {

			if ( $dhl_order->has_preferred_day() ) {
				$defaults['preferred_day'] = $dhl_order->get_preferred_day()->format( 'Y-m-d' );
			}

			if ( $dhl_order->has_preferred_time() ) {
				$defaults['preferred_time']       = $dhl_order->get_preferred_time();
				$defaults['preferred_time_start'] = $dhl_order->get_preferred_time_start()->format( 'H:i' );
				$defaults['preferred_time_end']   = $dhl_order->get_preferred_time_end()->format( 'H:i' );
			}

			if ( $dhl_order->has_preferred_location() ) {
				$defaults['preferred_location'] = $dhl_order->get_preferred_location();
			}

			if ( $dhl_order->has_preferred_neighbor() ) {
				$defaults['preferred_neighbor'] = $dhl_order->get_preferred_neighbor_formatted_address();
			}

			if ( 'none' !== Package::get_setting( 'label_visual_min_age', $dhl_shipping_method ) ) {
				$defaults['services'][]     = 'VisualCheckOfAge';
				$defaults['visual_min_age'] = Package::get_setting( 'label_visual_min_age', $dhl_shipping_method );
			}

			if ( $dhl_order->needs_age_verification() && 'yes' === Package::get_setting( 'label_auto_age_check_sync', $dhl_shipping_method ) ) {
				$defaults['services'][]     = 'VisualCheckOfAge';
				$defaults['visual_min_age'] = $dhl_order->get_min_age();
			}

			foreach( wc_gzd_dhl_get_services() as $service ) {

				// Combination is not available
				if ( $defaults['visual_min_age'] !== 'none' && 'NamedPersonOnly' === $service ) {
					continue;
				}

				if ( 'yes' === Package::get_setting( 'label_service_' . $service, $dhl_shipping_method ) ) {
					$defaults['services'][] = $service;
				}
			}

			// Demove duplicates
			$defaults['services'] = array_unique( $defaults['services'] );
		}

		if ( Package::base_country_supports( 'returns' ) ) {

			if ( 'yes' === Package::get_setting( 'label_auto_return_label', $dhl_shipping_method ) ) {
				$defaults['has_return'] = 'yes';

				$defaults['return_address'] = array(
					'name'          => Package::get_setting( 'return_address_name' ),
					'company'       => Package::get_setting( 'return_address_company' ),
					'street'        => Package::get_setting( 'return_address_street' ),
					'street_number' => Package::get_setting( 'return_address_street_no' ),
					'postcode'      => Package::get_setting( 'return_address_postcode' ),
					'city'          => Package::get_setting( 'return_address_city' ),
					'phone'         => Package::get_setting( 'return_address_phone' ),
					'email'         => Package::get_setting( 'return_address_email' ),
				);
			}
		}
	}

	return $defaults;
}

/**
 * @param Shipment $shipment the shipment
 * @param array $args
 */
function wc_gzd_dhl_create_label( $shipment, $args = array() ) {
	try {
		if ( ! $shipment || ! is_a( $shipment, 'Vendidero\Germanized\Shipments\Shipment' ) ) {
			throw new Exception( __( 'Invalid shipment', 'woocommerce-germanized-dhl' ) );
		}

		if ( ! $order = $shipment->get_order() ) {
			throw new Exception( __( 'Order does not exist', 'woocommerce-germanized-dhl' ) );
		}

		$dhl_order = wc_gzd_dhl_get_order( $order );
		$args      = wp_parse_args( $args, wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment ) );

		// Add COD service if payment method matches
		$args      = wc_gzd_dhl_validate_label_args( $shipment, $args );

		if ( is_wp_error( $args ) ) {
			return $args;
		}

		$label = LabelFactory::get_label( false, 'simple' );

		if ( ! $label ) {
			throw new Exception( _x( 'Error while creating the label instance', 'woocommerce-germanized-dhl' ) );
		}

		$label->set_props( $args );
		$label->set_shipment( $shipment );

		/**
		 * Action fires before creating a DHL label.
		 *
		 * @param Label $label The label object.
		 *
		 * @since 3.0.0
		 *
		 */
		do_action( 'woocommerce_gzd_dhl_before_create_label', $label );

		$label->save();

		/**
		 * Action fires after creating a DHL label.
		 *
		 * @param Label $label The label object.
		 *
		 * @since 3.0.0
		 *
		 */
		do_action( 'woocommerce_gzd_dhl_after_create_label', $label );

	} catch ( Exception $e ) {
		return new WP_Error( 'error', $e->getMessage() );
	}

	return $label;
}

/**
 * @param SimpleLabel $parent_label
 * @param array $args
 *
 * @return bool|ReturnLabel|WP_Error
 */
function wc_gzd_dhl_create_return_label( $parent_label, $args = array() ) {
	try {
		if ( ! $parent_label || ! is_a( $parent_label, 'Vendidero\Germanized\DHL\Label' ) ) {
			throw new Exception( __( 'Invalid label', 'woocommerce-germanized-dhl' ) );
		}

		$args      = wp_parse_args( $args, wc_gzd_dhl_get_return_label_default_args( $parent_label ) );
		$args      = wc_gzd_dhl_validate_return_label_args( $parent_label, $args );

		if ( is_wp_error( $args ) ) {
			return $args;
		}

		$label = LabelFactory::get_label( false, 'return' );

		$label->set_props( $args );
		$label->set_parent_id( $parent_label->get_id() );
		$label->set_shipment_id( $parent_label->get_shipment_id() );

		/**
		 * Action fires before creating a DHL return label.
		 *
		 * @param ReturnLabel $label The label object.
		 * @param SimpleLabel $label The parent label object.
		 *
		 * @since 3.0.0
		 */
		do_action( 'woocommerce_gzd_dhl_before_create_return_label', $label, $parent_label );

		$label->save();

	} catch ( Exception $e ) {
		return new WP_Error( 'error', $e->getMessage() );
	}

	return $label;
}

function wc_gzd_dhl_get_shipping_method_slug( $method ) {
	if ( empty( $method ) ) {
		return $method;
	}

	// Assumes format 'name:id'
	$new_ship_method = explode(':', $method );
	$new_ship_method = isset( $new_ship_method[0] ) ? $new_ship_method[0] : $method;

	return $new_ship_method;
}

/**
 * Main function for returning label.
 *
 * @param  mixed $the_label Object or label id.
 *
 * @return bool|SimpleLabel|ReturnLabel
 *
 */
function wc_gzd_dhl_get_label( $the_label = false ) {
	return LabelFactory::get_label( $the_label );
}

function wc_gzd_dhl_get_order( $order ) {
	if ( is_numeric( $order ) ) {
		$order = wc_get_order( $order);
	}

	if ( is_a( $order, 'WC_Order' ) ) {
		try {
			return new Vendidero\Germanized\DHL\Order( $order );
		} catch ( Exception $e ) {
			wc_caught_exception( $e, __FUNCTION__, func_get_args() );
			return false;
		}
	}

	return false;
}

function wc_gzd_dhl_get_products_international() {

	$country = Package::get_base_country();

	$germany_int =  array(
		'V55PAK'  => __( 'DHL Paket Connect', 'woocommerce-germanized-dhl' ),
		'V54EPAK' => __( 'DHL Europaket (B2B)', 'woocommerce-germanized-dhl' ),
		'V53WPAK' => __( 'DHL Paket International', 'woocommerce-germanized-dhl' ),
	);

	$austria_int = array(
		'V87PARCEL' => __( 'DHL Paket Connect', 'woocommerce-germanized-dhl' ),
		'V82PARCEL' => __( 'DHL Paket International', 'woocommerce-germanized-dhl' )
	);

	$dhl_prod_int = array();

	switch ( $country ) {
		case 'DE':
			$dhl_prod_int = $germany_int;
			break;
		case 'AT':
			$dhl_prod_int = $austria_int;
			break;
		default:
			break;
	}

	return $dhl_prod_int;
}

function wc_gzd_dhl_get_products( $shipping_country ) {
	if ( Package::is_shipping_domestic( $shipping_country ) ) {
		return wc_gzd_dhl_get_products_domestic();
	} else {
		return wc_gzd_dhl_get_products_international();
	}
}

function wc_gzd_dhl_get_default_product( $country, $method = false ) {
	if ( Package::is_crossborder_shipment( $country ) ) {
		return Package::get_setting( 'label_default_product_int', $method );
	} else {
		return Package::get_setting( 'label_default_product_dom', $method );
	}
}

function wc_gzd_dhl_get_products_domestic() {

	$country = Package::get_base_country();

	$germany_dom = array(
		'V01PAK'  => __( 'DHL Paket', 'woocommerce-germanized-dhl' ),
		'V01PRIO' => __( 'DHL Paket PRIO', 'woocommerce-germanized-dhl' ),
		'V06PAK'  => __( 'DHL Paket Taggleich', 'woocommerce-germanized-dhl' ),
	);

	$austria_dom = array(
		'V86PARCEL' => __( 'DHL Paket Austria', 'woocommerce-germanized-dhl' )
	);

	$dhl_prod_dom = array();

	switch ( $country ) {
		case 'DE':
			$dhl_prod_dom = $germany_dom;
			break;
		case 'AT':
			$dhl_prod_dom = $austria_dom;
			break;
		default:
			break;
	}

	return $dhl_prod_dom;
}

function wc_gzd_dhl_get_return_label( $label_parent_id ) {
	$labels = wc_gzd_dhl_get_labels( array(
		'parent_id' => $label_parent_id,
		'type'      => 'return',
	) );

	if ( ! empty( $labels ) ) {
		return $labels[0];
	}

	return false;
}

function wc_gzd_dhl_get_shipment_label( $the_shipment ) {
	$shipment_id = ShipmentFactory::get_shipment_id( $the_shipment );

	if ( $shipment_id ) {

		$labels = wc_gzd_dhl_get_labels( array(
			'shipment_id' => $shipment_id,
		) );

		if ( ! empty( $labels ) ) {
			return $labels[0];
		}
	}

	return false;
}

function wc_gzd_dhl_generate_label_filename( $label, $prefix = 'label' ) {
    $filename = 'dhl-' . $prefix . '-' . $label->get_shipment_id() . '.pdf';

    return $filename;
}

function _wc_gzd_dhl_keep_force_filename( $new_filename ) {
	return isset( $GLOBALS['gzd_dhl_unique_filename'] ) ? $GLOBALS['gzd_dhl_unique_filename'] : $new_filename;
}

function wc_gzd_dhl_upload_data( $filename, $bits, $relative = true ) {
    try {
        Package::set_upload_dir_filter();
        $GLOBALS['gzd_dhl_unique_filename'] = $filename;
	    add_filter( 'wp_unique_filename', '_wc_gzd_dhl_keep_force_filename', 10, 1 );

	    $tmp = wp_upload_bits( $filename,null, $bits );

	    unset( $GLOBALS['gzd_dhl_unique_filename'] );
	    remove_filter( 'wp_unique_filename', '_wc_gzd_dhl_keep_force_filename', 10 );
	    Package::unset_upload_dir_filter();

        if ( isset( $tmp['file'] ) ) {
            $path = $tmp['file'];

            if ( $relative ) {
	            $path = Package::get_relative_upload_dir( $path );
            }

            return $path;
        } else {
            throw new Exception( __( 'Error while uploading label.', 'woocommerce-germanized-dhl' ) );
        }
    } catch ( Exception $e ) {
        return false;
    }
}

/**
 * Get label type data by type.
 *
 * @param  string $type type name.
 * @return bool|array Details about the label type.
 */
function wc_gzd_dhl_get_label_type_data( $type ) {
	$types = array(
		'simple' => array(
			'class_name' => '\Vendidero\Germanized\DHL\SimpleLabel'
		),
		'return' => array(
			'class_name' => '\Vendidero\Germanized\DHL\ReturnLabel'
		),
	);

	if ( $type && array_key_exists( $type, $types ) ) {
		return $types[ $type ];
	} else {
		return $types['simple'];
	}
}

/**
 * Get the order ID depending on what was passed.
 *
 * @since 3.0.0
 * @param  mixed $order Order data to convert to an ID.
 * @return int|bool false on failure
 */
function wc_gzd_dhl_get_label_id( $label ) {
    if ( is_numeric( $label ) ) {
        return $label;
    } elseif ( $label instanceof Vendidero\Germanized\DHL\Label ) {
        return $label->get_id();
    } elseif ( ! empty( $label->label_id ) ) {
        return $label->label_id;
    } else {
        return false;
    }
}