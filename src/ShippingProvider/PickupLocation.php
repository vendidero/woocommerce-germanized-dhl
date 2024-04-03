<?php

namespace Vendidero\Germanized\DHL\ShippingProvider;

defined( 'ABSPATH' ) || exit;

class PickupLocation extends \Vendidero\Germanized\Shipments\ShippingProvider\PickupLocation {

	/**
	 * @param $customer_number
	 *
	 * @return bool|\WP_Error
	 */
	public function customer_number_is_valid( $customer_number ) {
		$customer_number = preg_replace( '/[^0-9]/', '', $customer_number );
		$is_valid        = parent::customer_number_is_valid( $customer_number );

		if ( $is_valid ) {
			$customer_number_len = strlen( $customer_number );

			if ( $customer_number_len < 6 || $customer_number_len > 12 ) {
				$is_valid = false;
			}
		}

		return $is_valid;
	}

	public function get_customer_number_field_label() {
		return _x( 'Customer Number (Post Number)', 'dhl', 'woocommerce-germanized-dhl' );
	}
}
