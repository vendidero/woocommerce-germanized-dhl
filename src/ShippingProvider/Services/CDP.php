<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\DHL\ParcelServices;
use Vendidero\Germanized\Shipments\ShipmentError;
use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class CDP extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id' => 'CDP',
			'label' => _x( 'Delivery Type (CDP)', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Allow your international customers to choose between home and closest droppoint delivery. ', 'dhl', 'woocommerce-germanized-dhl' ),
			'long_description' => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Display options for the user to select their preferred delivery type during checkout. Currently available for <a href="%s">certain countries only</a>.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( 'https://www.dhl.de/de/geschaeftskunden/paket/leistungen-und-services/internationaler-versand/paket-international.html' ) ) . '</div>',
			'setting_id'   => 'PreferredDeliveryType_enable',
			'products'    => array( 'V53WPAK' ),
			'supported_countries' => ParcelServices::get_cdp_countries(),
			'supported_zones' => array( 'eu' ),
		);

		parent::__construct( $shipping_provider, $args );
	}

	public function get_default_value( $suffix = '' ) {
		$default_value = parent::get_default_value( $suffix );

		if ( 'delivery_type' === $suffix ) {
			$default_value = 'home';
		}

		return $default_value;
	}

	protected function get_additional_setting_fields( $args ) {
		$base_setting_id = $this->get_setting_id( $args );
		$args['suffix']  = 'delivery_type';
		$setting_id      = $this->get_setting_id( $args );
		$value           = $this->get_shipping_provider() ? $this->get_shipping_provider()->get_setting( $setting_id, 'home' ) : 'home';

		return array(
			array(
				'title'    => _x( 'Delivery Type', 'dhl', 'woocommerce-germanized-dhl' ),
				'id'       => $setting_id,
				'type'     => 'select',
				'default'  => 'home',
				'value'    => $value,
				'options'  => ParcelServices::get_preferred_delivery_types(),
				'custom_attributes' => array( "data-show_if_{$base_setting_id}" => '' ),
			),
		);
	}

	protected function get_additional_label_fields( $shipment ) {
		$label_fields  = parent::get_additional_label_fields( $shipment );
		$dhl_order     = wc_gzd_dhl_get_order( $shipment->get_order() );
		$delivery_type = $this->get_shipment_setting( $shipment, 'delivery_type' );

		if ( $dhl_order && $dhl_order->has_preferred_delivery_type() ) {
			$delivery_type = $dhl_order->get_preferred_delivery_type();
		}

		$label_fields = array_merge( $label_fields, array(
			array(
				'id'                => $this->get_label_field_id( 'delivery_type' ),
				'label'             => _x( 'Delivery type', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder'       => '',
				'description'       => '',
				'value'             => $delivery_type,
				'options'           => ParcelServices::get_preferred_delivery_types(),
				'custom_attributes' => array( 'data-show-if-service_CDP' => '' ),
				'type'              => 'select',
			),
		) );

		return $label_fields;
	}

	public function book_as_default( $shipment ) {
		$book_as_default = parent::book_as_default( $shipment );

		if ( false === $book_as_default ) {
			$dhl_order = wc_gzd_dhl_get_order( $shipment->get_order() );

			if ( $dhl_order && $dhl_order->has_preferred_delivery_type() ) {
				$book_as_default = true;
			}
		}

		return $book_as_default;
	}

	public function validate_label_request( $props, $shipment ) {
		$error    = new ShipmentError();
		$location = isset( $props[ $this->get_label_field_id( 'location' ) ] ) ? $props[ $this->get_label_field_id( 'location' ) ] : '';

		if ( empty( $location ) ) {
			$error->add( 500, _x( 'Please choose a valid preferred location.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}

		return wc_gzd_shipment_wp_error_has_errors( $error ) ? $error : true;
	}
}