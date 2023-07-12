<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class CashOnDelivery extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'CashOnDelivery',
			'label' => _x( 'Cash on Delivery', 'dhl', 'woocommerce-germanized-dhl' ),
			'products' => array( 'V01PAK', 'V53WPAK' ),
			'locations' => array( 'label' ),
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
		$value        = $this->get_shipment_setting( $shipment, 'cod_total' );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'          => $this->get_label_field_id( 'cod_total' ),
				'class'       => 'wc_input_decimal',
				'label'       => _x( 'COD Amount', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder' => '',
				'description' => '',
				'value'       => $value,
				'type'        => 'text',
				'custom_attributes' => array( 'data-show-if-service_CashOnDelivery' => '' ),
			),
		) );

		return $label_fields;
	}
}