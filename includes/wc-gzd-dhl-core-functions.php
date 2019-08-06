<?php
/**
 * WooCommerce Germanized DHL Shipment Functions
 *
 * Functions for shipment specific things.
 *
 * @package WooCommerce_Germanized/DHL/Functions
 * @version 3.4.0
 */

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