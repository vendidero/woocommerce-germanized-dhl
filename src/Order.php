<?php

namespace Vendidero\Germanized\DHL;
use Exception;
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

	protected function set_dhl_date_prop( $prop, $value, $format = 'Y-m-d' ) {
		try {
			if ( ! empty( $value ) ) {

				if ( is_a( $value, 'WC_DateTime' ) ) {
					$datetime = $value;
				} elseif ( is_numeric( $value ) ) {
					$datetime = new WC_DateTime( "@{$value}" );
				} else {
					$timestamp = wc_string_to_timestamp( $value );
					$datetime  = new WC_DateTime( "@{$timestamp}" );
				}

				if ( $datetime ) {
					$value = strtotime( date( $format, $datetime->getOffsetTimestamp() ) );
				}
			}
		} catch ( Exception $e ) {} // @codingStandardsIgnoreLine.

		$this->set_dhl_prop( $prop, $value );
	}

	protected function set_dhl_time_prop( $prop, $value ) {
		$this->set_dhl_date_prop( $prop, $value, 'H:i:s' );
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
		$data      = $this->get_dhl_props();
		$prop_data = array_key_exists( $prop, $data ) ? $data[ $prop ] : null;

		// Legacy DHL plugin support
		if ( is_null( $prop_data ) ) {
			$meta = $this->get_order()->get_meta( '_pr_shipment_dhl_label_items' );

			if ( ! empty( $meta ) ) {

				if ( 'preferred_day' === $prop ) {
					$preferred_day = isset( $meta['pr_dhl_preferred_day'] ) ? $meta['pr_dhl_preferred_day'] : false;

					if ( $preferred_day ) {
						return strtotime( $preferred_day );
					}
				} elseif( 'preferred_time_start' === $prop || 'preferred_time_end' === $prop ) {
					$preferred_time = isset( $meta['pr_dhl_preferred_time'] ) ? $meta['pr_dhl_preferred_time'] : false;

					if ( $preferred_time ) {
						$preferred_time_start_part = substr( $preferred_time, 0, 4 );
						$preferred_time_start      = implode( ':', str_split( $preferred_time_start_part, 2 ) );

						$preferred_time_end_part   = substr( $preferred_time, 4, 8 );
						$preferred_time_end        = implode( ':', str_split( $preferred_time_end_part, 2 ) );

						if ( 'preferred_time_start' === $prop ) {
							return strtotime( $preferred_time_start );
						} elseif( 'preferred_time_end' === $prop ) {
							return strtotime( $preferred_time_end );
						}
					}
				} elseif( 'preferred_neighbor' === $prop ) {
					$has_neighbor = ( isset( $meta['pr_dhl_preferred_location_neighbor'] ) && 'preferred_neighbor' === $meta['pr_dhl_preferred_location_neighbor'] ) ? true : false;

					if ( $has_neighbor ) {
						return ( isset( $meta['pr_dhl_preferred_neighbour_name'] ) ? $meta['pr_dhl_preferred_neighbour_name'] : '' );
					}
				} elseif( 'preferred_neighbor_address' === $prop ) {
					$has_neighbor = ( isset( $meta['pr_dhl_preferred_location_neighbor'] ) && 'preferred_neighbor' === $meta['pr_dhl_preferred_location_neighbor'] ) ? true : false;

					if ( $has_neighbor ) {
						return ( isset( $meta['pr_dhl_preferred_neighbour_address'] ) ? $meta['pr_dhl_preferred_neighbour_address'] : '' );
					}
				} elseif( 'preferred_location' === $prop ) {
					$has_location = ( isset( $meta['pr_dhl_preferred_location_neighbor'] ) && 'preferred_location' === $meta['pr_dhl_preferred_location_neighbor'] ) ? true : false;

					if ( $has_location ) {
						return ( isset( $meta['pr_dhl_preferred_location'] ) ? $meta['pr_dhl_preferred_location'] : '' );
					}
				}
			}
		}

		return $prop_data;
	}

	public function has_cod_payment() {
		$result = false;

		if ( 'cod' === $this->get_order()->get_payment_method() ) {
			$result = true;
		}

		return apply_filters( 'woocommerce_gzd_dhl_order_has_cod_payment', $result, $this->get_order(), $this );
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

	public function has_preferred_day() {
		return $this->get_preferred_day() !== null;
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

	public function has_preferred_time() {
		return $this->get_preferred_time_start() !== null && $this->get_preferred_time_end() !== null;
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

	public function has_preferred_location() {
		$location = $this->get_preferred_location();

		return ! empty( $location ) ? true : false;
	}

	public function get_preferred_neighbor() {
		return $this->get_dhl_prop( 'preferred_neighbor' );
	}

	public function get_preferred_neighbor_address() {
		return $this->get_dhl_prop( 'preferred_neighbor_address' );
	}

	public function has_preferred_neighbor() {
		$address = $this->get_preferred_neighbor_formatted_address();

		return ! empty( $address ) ? true : false;
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