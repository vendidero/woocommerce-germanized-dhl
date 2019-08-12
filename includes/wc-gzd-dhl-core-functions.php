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
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\Shipment;

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

	return $times;
}

function wc_gzd_dhl_get_preferred_days_select_options( $days ) {
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

function wc_gzd_dhl_get_services() {
    return array(
        'PreferredTime',
        'VisualCheckOfAge',
        'PreferredLocation',
        'PreferredNeighbour',
        'PreferredDay',
        'Personally',
        'NoNeighbourDelivery',
        'NamedPersonOnly',
        'Premium',
        'AdditionalInsurance',
        'BulkyGoods',
        'IdentCheck',
        'CashOnDelivery'
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

function wc_gzd_dhl_get_excluded_working_days() {
	return array();
}

function wc_gzd_dhl_get_pickup_type( $type ) {
	$types = wc_gzd_dhl_get_pickup_types();

	return array_key_exists( $type, $types ) ? $types[ $type ] : false;
}

function wc_gzd_dhl_validate_label_args( $args = array() ) {

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
		'services'              => array(),
		'return_address'        => array(),
	) );

	$error = new WP_Error();

	// Do only allow valid services
	if ( ! empty( $args['services'] ) ) {
		$args['services'] = array_intersect( $args['services'], wc_gzd_dhl_get_services() );
	}

	// Add default return address fields
	if ( empty( $args['return_address'] ) && 'yes' === Package::get_setting( 'generate_return_label' ) ) {
		$args['has_return']     = 'yes';
		$args['return_address'] = wp_parse_args( $args['return_address'], array(
			'first_name'    => Package::get_setting( 'generate_return_address_first_name' ),
			'last_name'     => Package::get_setting( 'generate_return_address_last_name' ),
			'company'       => Package::get_setting( 'generate_return_address_company' ),
			'street'        => Package::get_setting( 'generate_return_address_street' ),
			'street_number' => Package::get_setting( 'generate_return_address_street_no' ),
			'postcode'      => Package::get_setting( 'generate_return_address_postcode' ),
			'city'          => Package::get_setting( 'generate_return_address_city' ),
			'state'         => Package::get_setting( 'generate_return_address_state' ),
		) );
	}

	// Check if return address has empty mandatory fields
	if ( 'yes' === $args['has_return'] ) {
		$args['return_address'] = wp_parse_args( $args['return_address'], array(
			'first_name'    => '',
			'last_name'     => '',
			'company'       => '',
			'street'        => '',
			'street_number' => '',
			'postcode'      => '',
			'city'          => '',
			'state'         => '',
		) );

		$mandatory = array(
			'first_name' => __( 'First name', 'woocommerce-germanized-dhl' ),
			'last_name'  => __( 'Last name', 'woocommerce-germanized-dhl' ),
			'street'     => __( 'Street', 'woocommerce-germanized-dhl' ),
			'postcode'   => __( 'Postcode', 'woocommerce-germanized-dhl' ),
			'city'       => __( 'City', 'woocommerce-germanized-dhl' ),
		);

		foreach( $mandatory as $mand => $title ) {
			if ( empty( $args['return_address'][ $mand ] ) ) {
				$error->add( 500, sprintf( __( '%s of the return address is a mandatory field.', 'woocommerce-germanized-dhl' ), $title ) );
			}
		}
	} else {
		$args['return_address'] = array();
	}

	if ( ! empty( $args['preferred_day'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_day'], 'Y-m-d' ) ) {
		$args['services']      = array_merge( $args['services'], array( 'PreferredDay' ) );
	} else {
		if ( ! empty( $args['preferred_day'] ) && ! wc_gzd_dhl_is_valid_datetime( $args['preferred_day'], 'Y-m-d' ) ) {
			$error->add( 500, __( 'Error while parsing preferred day.', 'woocommerce-germanized-dhl' ) );
		}

		$args['services']      = array_diff( $args['services'], array( 'PreferredDay' ) );
		$args['preferred_day'] = '';
	}

	if ( ( ! empty( $args['preferred_time_start'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_time_start'], 'H:i' ) ) && ( ! empty( $args['preferred_time_end'] ) && wc_gzd_dhl_is_valid_datetime( $args['preferred_time_end'], 'H:i' ) ) ) {
		$args['services']             = array_merge( $args['services'], array( 'PreferredTime' ) );
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
	} else {
		$args['ident_min_age']       = '';
		$args['ident_date_of_birth'] = '';
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
		$args      = wp_parse_args( $args, array(
			'preferred_day'              => $dhl_order->get_preferred_day(),
			'preferred_time_start'       => $dhl_order->get_preferred_time_start(),
			'preferred_time_end'         => $dhl_order->get_preferred_time_end(),
			'preferred_location'         => $dhl_order->get_preferred_location(),
			'preferred_neighbor'         => $dhl_order->get_preferred_neighbor(),
			'preferred_neighbor_address' => $dhl_order->get_preferred_neighbor_address(),
			'services'                   => array(),
		) );

		// Add COD service if payment method matches

		$args = wc_gzd_dhl_validate_label_args( $args );

		if ( is_wp_error( $args ) ) {
			return $args;
		}

		$label = new Vendidero\Germanized\DHL\Label();

		$label->set_props( $args );
		$label->set_shipment_id( $shipment->get_id() );
		$label->save();

	} catch ( Exception $e ) {
		return new WP_Error( 'error', $e->getMessage() );
	}

	return $label;
}

/**
 * Main function for returning label.
 *
 * @param  mixed $the_label Object or label id.
 *
 * @return bool|Label
 *@since  2.2
 *
 */
function wc_gzd_dhl_get_label( $the_label = false ) {
    $label_id = wc_gzd_dhl_get_label_id( $the_label );

    if ( ! $label_id ) {
        return false;
    }

    // Filter classname so that the class can be overridden if extended.
    $classname = apply_filters( 'woocommerce_gzd_dhl_label_class', 'Vendidero\Germanized\DHL\Label', $label_id );

    if ( ! class_exists( $classname ) ) {
        return false;
    }

    try {
        return new $classname( $label_id );
    } catch ( Exception $e ) {
        wc_caught_exception( $e, __FUNCTION__, func_get_args() );
        return false;
    }
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

function wc_gzd_dhl_get_shipment_label( $the_shipment ) {
	$shipment_id = wc_gzd_get_shipment_id( $the_shipment );

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

function wc_gzd_dhl_upload_file( $filename, $file ) {
    try {
        Package::set_upload_dir_filter();
        // Make sure that WP overrides file if it does already exist
        add_filter( 'wp_unique_filename', '_wc_gzd_label_force_keep_filename', 10, 3 );

        $tmp = wp_upload_bits( $filename,null, $file );

        Package::unset_upload_dir_filter();
        remove_filter( 'wp_unique_filename', '_wc_gzd_label_force_keep_filename', 10 );

        if ( isset( $tmp['file'] ) ) {
            return $tmp['file'];
        } else {
            throw new Exception( __( 'Error while uploading label.', 'woocommerce-germanized-dhl' ) );
        }
    } catch ( Exception $e ) {
        return false;
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