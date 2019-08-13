<?php

namespace Vendidero\Germanized\DHL;
use WC_Order;
use WC_Customer;
use WC_DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * Shipment Order
 *
 * @class 		WC_GZD_Shipment_Order
 * @version		1.0.0
 * @author 		Vendidero
 */
class Order {

	/**
	 * The actual order item object
	 *
	 * @var object
	 */
	protected $order;

	/**
	 * @param WC_Customer $customer
	 */
	public function __construct( $order ) {
		$this->order = $order;
	}

	/**
	 * Returns the Woo WC_Order original object
	 *
	 * @return object|WC_Order
	 */
	public function get_order() {
		return $this->order;
	}

	protected function get_dhl_props() {
		$data = (array) $this->get_order()->get_meta( '_dhl_services' );

		return $data;
	}

	protected function set_dhl_date_prop( $prop, $value ) {
		$value = $value ? strtotime( date("Y-m-d", $value->getOffsetTimestamp() ) ) : '';
		$this->set_dhl_prop( $prop, $value );
	}

	protected function set_dhl_time_prop( $prop, $value ) {
		$value = $value ? strtotime( date("H:i:s", $value->getOffsetTimestamp() ) ) : '';
		$this->set_dhl_prop( $prop, $value );
	}

	protected function set_dhl_boolean_prop( $prop, $value ) {
		$value = wc_bool_to_string( $value );
		$this->set_dhl_prop( $prop, $value );
	}

	protected function set_dhl_prop( $prop, $value ) {
		$data = $this->get_dhl_props();

		if ( empty( $value ) ) {
			$this->delete_dhl_prop( $prop );
		} else {
			$data[ $prop ] = $value;
			$this->get_order()->update_meta_data( '_dhl_services', $data );
		}
	}

	protected function delete_dhl_prop( $prop ) {
		$data = $this->get_dhl_props();

		if ( array_key_exists( $prop, $data ) ) {
			unset( $data[ $prop ] );
		}

		$this->get_order()->update_meta_data( '_dhl_services', $data );
	}

	protected function get_dhl_prop( $prop ) {
		$data = $this->get_dhl_props();

		return array_key_exists( $prop, $data ) ? $data[ $prop ] : null;
	}

	public function get_post_number() {
		$fallback = $this->get_order()->get_meta( '_shipping_dhl_postnum' );

		return $fallback;
	}

	public function has_cod_payment() {
		return true;
	}

	public function get_date_of_birth() {
		if ( $timestamp = $this->get_dhl_prop( 'date_of_birth' ) ) {
			$date = new WC_DateTime( "@{$timestamp}" );
			return $date;
		}

		return null;
	}

	public function get_preferred_day() {
		if ( $timestamp = $this->get_dhl_prop( 'preferred_day' ) ) {
			$date = new WC_DateTime( "@{$timestamp}" );
			return $date;
		}

		return null;
	}

	public function get_preferred_time_start() {
		if ( $timestamp = $this->get_dhl_prop( 'preferred_time_start' ) ) {
			$date = new WC_DateTime( "@{$timestamp}" );
			return $date;
		}

		return null;
	}

	public function get_preferred_time_end() {
		if ( $timestamp = $this->get_dhl_prop( 'preferred_time_end' ) ) {
			$date = new WC_DateTime( "@{$timestamp}" );
			return $date;
		}

		return null;
	}

	public function get_preferred_time() {
		$start = $this->get_preferred_time_start();
		$end   = $this->get_preferred_time_end();

		if ( $start && $end ) {
			return $start->date( 'H:i' ) . '-' . $end->date( 'H:i' );
		}

		return null;
	}

	public function get_preferred_formatted_time() {
		$start = $this->get_preferred_time_start();
		$end   = $this->get_preferred_time_end();

		if ( $start && $end ) {
			return sprintf( _x( '%s-%s', 'time-span', 'woocommerce-germanized-dhl' ), $start->date( 'H' ), $end->date( 'H' ) );
		}

		return null;
	}

	public function get_preferred_location() {
		return $this->get_dhl_prop( 'preferred_location' );
	}

	public function get_preferred_neighbor() {
		return $this->get_dhl_prop( 'preferred_neighbor' );
	}

	public function get_preferred_neighbor_address() {
		return $this->get_dhl_prop( 'preferred_neighbor_address' );
	}

	public function get_preferred_neighbor_formatted_address() {
		if ( ! empty( $this->get_preferred_neighbor() ) && ! empty( $this->get_preferred_neighbor_address() ) ) {
			return $this->get_preferred_neighbor() . ', ' . $this->get_preferred_neighbor_address();
		}

		return '';
	}

	public function set_preferred_day( $date ) {
		$this->set_dhl_date_prop( 'preferred_day', $date );
	}

	public function set_preferred_time_start( $time ) {
		$this->set_dhl_time_prop( 'preferred_time_start', $time );
	}

	public function set_preferred_time_end( $time ) {
		$this->set_dhl_time_prop( 'preferred_time_end', $time );
	}

	public function set_preferred_location( $location ) {
		$this->set_dhl_prop( 'preferred_location', $location );
	}

	public function set_preferred_neighbor( $neighbor ) {
		$this->set_dhl_prop( 'preferred_neighbor', $neighbor );
	}

	public function set_preferred_neighbor_address( $address ) {
		$this->set_dhl_prop( 'preferred_neighbor_address', $address );
	}

	/**
	 * Call child methods if the method does not exist.
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return bool|mixed
	 */
	public function __call( $method, $args ) {

		if ( method_exists( $this->order, $method ) ) {
			return call_user_func_array( array( $this->order, $method ), $args );
		}

		return false;
	}
}