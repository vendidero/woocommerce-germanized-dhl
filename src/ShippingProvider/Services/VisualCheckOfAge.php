<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class VisualCheckOfAge extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'VisualCheckOfAge',
			'label' => _x( 'Visual Age check', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Let DHL handle the age check for you at the point of delivery.', 'dhl', 'woocommerce-germanized-dhl' ),
			'products'    => array( 'V01PAK' ),
			'supported_countries' => array( 'DE' ),
			'supported_zones' => array( 'dom' ),
		);

		parent::__construct( $shipping_provider, $args );
	}

	protected function get_additional_setting_fields( $args ) {
		$base_setting_id = $this->get_setting_id( $args );
		$setting_id      = $base_setting_id . '_min_age';
		$value           = $this->get_shipping_provider() ? $this->get_shipping_provider()->get_setting( $setting_id, '0' ) : '0';

		return array(
			array(
				'title'    => _x( 'Minimum age', 'dhl', 'woocommerce-germanized-dhl' ),
				'id'       => $setting_id,
				'type'     => 'select',
				'default'  => '0',
				'value'    => $value,
				'options'  => wc_gzd_dhl_get_ident_min_ages(),
				'custom_attributes' => array( "data-show_if_{$base_setting_id}" => '' ),
				'desc_tip' => _x( 'Choose this option if you want to let DHL check your customer\'s identity and age.', 'dhl', 'woocommerce-germanized-dhl' ),
			),
		);
	}

	public function get_default_value( $suffix = '' ) {
		$default_value = parent::get_default_value( $suffix );

		if ( 'min_age' === $suffix ) {
			$default_value = '';
		}

		return $default_value;
	}

	protected function get_additional_label_fields( $shipment ) {
		$label_fields = parent::get_additional_label_fields( $shipment );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'                => $this->get_label_field_id( 'min_age' ),
				'label'             => _x( 'Minimum Age', 'dhl', 'woocommerce-germanized-dhl' ),
				'description'       => '',
				'type'              => 'select',
				'value'             => $this->get_shipment_setting( $shipment, 'min_age' ),
				'options'           => wc_gzd_dhl_get_visual_min_ages(),
				'custom_attributes' => array( 'data-show-if-service_VisualCheckOfAge' => '' ),
			),
		) );

		return $label_fields;
	}
}