<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class PreferredLocation extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'PreferredLocation',
			'label' => _x( 'Drop-off location', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Enable drop-off location delivery.', 'dhl', 'woocommerce-germanized-dhl' ),
			'long_description' => '<div class="wc-gzd-additional-desc">' . _x( 'Enabling this option will display options for the user to select their preferred delivery location during the checkout.', 'dhl', 'woocommerce-germanized-dhl' ) . '</div>',
			'setting_id'   => 'PreferredLocation_enable',
			'products'    => array( 'V01PAK', 'V62WP' ),
			'supported_countries' => array( 'DE' ),
			'supported_zones' => array( 'dom' ),
			'locations' => array( 'label' ),
			'allow_default_booking' => false,
		);

		parent::__construct( $shipping_provider, $args );
	}

	protected function get_additional_label_fields( $shipment ) {
		$label_fields = parent::get_additional_label_fields( $shipment );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'                => $this->get_label_field_id( 'location' ),
				'label'             => _x( 'Drop-off location', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder'       => '',
				'description'       => '',
				'value'             => '',
				'custom_attributes' => array( 'maxlength' => '80', 'data-show-if-service_PreferredLocation' => '' ),
				'type'              => 'text',
			),
		) );

		return $label_fields;
	}
}