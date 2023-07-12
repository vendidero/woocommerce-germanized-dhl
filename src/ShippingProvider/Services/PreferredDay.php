<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class PreferredDay extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'PreferredDay',
			'label' => _x( 'Delivery day', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Enable delivery day delivery.', 'dhl', 'woocommerce-germanized-dhl' ),
			'long_description' => '<div class="wc-gzd-additional-desc">' . _x( 'Enabling this option will display options for the user to select their delivery day of delivery during the checkout.', 'dhl', 'woocommerce-germanized-dhl' ) . '</div>',
			'setting_id'   => 'PreferredDay_enable',
			'products'    => array( 'V01PAK' ),
			'supported_countries' => array( 'DE' ),
			'supported_zones' => array( 'dom' ),
			'locations' => array( 'label' ),
			'allow_default_booking' => false,
		);

		parent::__construct( $shipping_provider, $args );
	}

	protected function get_additional_label_fields( $shipment ) {
		$preferred_days = array();

		try {
			$preferred_day_options = Package::get_api()->get_preferred_available_days( $shipment->get_postcode() );

			if ( $preferred_day_options ) {
				$preferred_days = $preferred_day_options;
			}
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}

		$label_fields = parent::get_additional_label_fields( $shipment );

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'          => $this->get_label_field_id( 'day' ),
				'label'       => _x( 'Delivery day', 'dhl', 'woocommerce-germanized-dhl' ),
				'description' => '',
				'value'       => '',
				'options'     => wc_gzd_dhl_get_preferred_days_select_options( $preferred_days, '' ),
				'custom_attributes' => array( 'data-show-if-service_PreferredDay' => '' ),
				'type'        => 'select',
			),
		) );

		return $label_fields;
	}
}