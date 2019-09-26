<?php

namespace Vendidero\Germanized\DHL;
use DateTimeZone;
use Vendidero\Germanized\Shipments\Shipment;
use WC_Data;
use WC_Data_Store;
use Exception;
use WC_DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * DHL ReturnLabel class.
 */
class ReturnLabel extends Label {

	/**
	 * Stores product data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'parent_id'      => 0,
		'sender_address' => array(),
	);

	protected function get_hook_prefix() {
		return 'woocommerce_gzd_dhl_return_label_get_';
	}

	public function get_type() {
		return 'return';
	}

	public function get_parent_id( $context = 'view' ) {
		return $this->get_prop( 'parent_id', $context );
	}

	public function get_sender_address( $context = 'view' ) {
		return $this->get_prop( 'sender_address', $context );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @since  3.0.0
	 * @param  string $prop Name of prop to get.
	 * @param  string $address billing or shipping.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_sender_address_prop( $prop, $context = 'view' ) {
		$value = $this->get_address_prop( $prop, 'sender_address', $context );

		return $value;
	}

	public function get_sender_street( $context = 'view' ) {
		return $this->get_sender_address_prop( 'street', $context );
	}

	public function get_sender_street_number( $context = 'view' ) {
		return $this->get_sender_address_prop( 'street_number', $context );
	}

	public function get_sender_company( $context = 'view' ) {
		return $this->get_sender_address_prop( 'company', $context );
	}

	public function get_sender_name( $context = 'view' ) {
		return $this->get_sender_address_prop( 'name', $context );
	}

	public function get_sender_formatted_full_name() {
		return sprintf( _x( '%1$s', 'full name', 'woocommerce-germanized-dhl' ), $this->get_sender_name() );
	}

	public function get_sender_postcode( $context = 'view' ) {
		return $this->get_sender_address_prop( 'postcode', $context );
	}

	public function get_sender_city( $context = 'view' ) {
		return $this->get_sender_address_prop( 'city', $context );
	}

	public function get_sender_state( $context = 'view' ) {
		return $this->get_sender_address_prop( 'state', $context );
	}

	public function get_sender_country( $context = 'view' ) {
		return $this->get_sender_address_prop( 'country', $context );
	}

	public function get_sender_phone( $context = 'view' ) {
		return $this->get_sender_address_prop( 'phone', $context );
	}

	public function get_sender_email( $context = 'view' ) {
		return $this->get_sender_address_prop( 'email', $context );
	}

	public function set_parent_id( $parent_id ) {
		$this->set_prop( 'parent_id', absint( $parent_id ) );
	}

	public function set_sender_address( $value ) {
		$this->set_prop( 'sender_address', empty( $value ) ? array() : (array) $value );
	}
}
