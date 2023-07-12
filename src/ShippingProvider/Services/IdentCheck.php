<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class IdentCheck extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'IdentCheck',
			'label' => _x( 'Ident-Check', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Use the DHL Ident-Check service to make sure your parcels are only released to the recipient in person.', 'dhl', 'woocommerce-germanized-dhl' ),
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

		if ( 'date_of_birth' === $suffix ) {
			$default_value = '';
		} elseif ( 'min_age' === $suffix ) {
			$default_value = '';
		}

		return $default_value;
	}

	protected function get_additional_label_fields( $shipment ) {
		$label_fields = parent::get_additional_label_fields( $shipment );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'    => '',
				'type'  => 'columns',
			),
			array(
				'id'                => $this->get_label_field_id( 'date_of_birth' ),
				'label'             => _x( 'Date of Birth', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder'       => '',
				'description'       => '',
				'value'             => $this->get_shipment_setting( $shipment, 'date_of_birth' ),
				'custom_attributes' => array(
					'pattern'   => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
					'maxlength' => 10,
					'data-show-if-service_IdentCheck' => ''
				),
				'class'             => 'short date-picker',
				'wrapper_class'     => 'column col-6',
				'type'              => 'text',
			),
			array(
				'id'            => $this->get_label_field_id( 'min_age' ),
				'label'         => _x( 'Minimum age', 'dhl', 'woocommerce-germanized-dhl' ),
				'description'   => '',
				'type'          => 'select',
				'value'         => $this->get_shipment_setting( $shipment, 'min_age' ),
				'options'       => wc_gzd_dhl_get_ident_min_ages(),
				'custom_attributes' => array( 'data-show-if-service_IdentCheck' => '' ),
				'wrapper_class'     => 'column col-6',
			),
			array(
				'id'   => '',
				'type' => 'columns_end',
			),
		) );

		return $label_fields;
	}
}