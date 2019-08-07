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

defined( 'ABSPATH' ) || exit;

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

function wc_gzd_dhl_get_pickup_types() {
    return array();
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