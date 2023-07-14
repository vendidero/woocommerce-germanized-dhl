<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShipmentError;
use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class CashOnDelivery extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'CashOnDelivery',
			'label' => _x( 'Cash on Delivery', 'dhl', 'woocommerce-germanized-dhl' ),
			'products' => array( 'V01PAK', 'V53WPAK' ),
			'excluded_locations' => array( 'settings' ),
		);

		parent::__construct( $shipping_provider, $args );
	}

	public function get_default_value( $suffix = '' ) {
		$default_value = parent::get_default_value( $suffix );

		if ( 'cod_total' === $suffix ) {
			$default_value = '';
		}

		return $default_value;
	}

	protected function get_additional_label_fields( $shipment ) {
		$label_fields = parent::get_additional_label_fields( $shipment );
		$value        = $shipment->get_total() + round( $shipment->get_additional_total(), wc_get_price_decimals() );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'          => $this->get_label_field_id( 'cod_total' ),
				'class'       => 'wc_input_decimal',
				'label'       => _x( 'COD Amount', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder' => '',
				'description' => '',
				'value'       => wc_format_localized_decimal( $value ),
				'type'        => 'text',
				'custom_attributes' => array( 'data-show-if-service_CashOnDelivery' => '' ),
			),
		) );

		return $label_fields;
	}

	public function validate_label_request( $props, $shipment ) {
		$error     = new ShipmentError();
		$field_id  = $this->get_label_field_id( 'cod_total' );
		$cod_total = isset( $props[ $field_id ] ) ? (float) wc_format_decimal( $props[ $field_id ] ) : 0.0;

		if ( empty( $cod_total ) ) {
			$error->add( 500, _x( 'Please choose a valid cash on delivery total amount.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}

		return wc_gzd_shipment_wp_error_has_errors( $error ) ? $error : true;
	}
}