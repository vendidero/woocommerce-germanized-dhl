<?php

namespace Vendidero\Germanized\DHL\Label;

use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\Interfaces\ShipmentLabel;
use WC_Data;
use WC_Data_Store;
use Exception;
use WC_DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * DHL Shipment class.
 */
abstract class Label extends \Vendidero\Germanized\DHL\Legacy\Label {

	protected $legacy = false;

    protected $extra_data = array(
	    'default_path' => '',
	    'export_path'  => '',
    );

	/**
     * Prefix for action and filter hooks on data.
     *
     * @since  3.0.0
     * @return string
     */
    protected function get_hook_prefix() {
        return 'woocommerce_gzd_dhl_label_get_';
    }

	public function get_additional_file_types() {
		return array(
			'default',
			'export'
		);
	}

	public function get_filename( $file_type = '' ) {
		if ( 'default' === $file_type ) {
			return $this->get_default_filename();
		} elseif( 'export' === $file_type ) {
			return $this->get_export_filename();
		} else {
			return parent::get_filename( $file_type );
		}
	}

	public function get_file( $file_type = '' ) {
    	if ( 'default' === $file_type ) {
    		return $this->get_default_file();
	    } elseif( 'export' === $file_type ) {
    		return $this->get_export_file();
	    } else {
		    return parent::get_file( $file_type );
	    }
	}

	public function get_default_path( $context = 'view' ) {
		return $this->get_prop( 'default_path', $context );
	}

    public function get_export_path( $context = 'view' ) {
        return $this->get_prop( 'export_path', $context );
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
	protected function get_address_prop( $prop, $address = 'sender_address', $context = 'view' ) {
		$value = null;

		if ( isset( $this->changes[ $address ][ $prop ] ) || isset( $this->data[ $address ][ $prop ] ) ) {
			$value = isset( $this->changes[ $address ][ $prop ] ) ? $this->changes[ $address ][ $prop ] : $this->data[ $address ][ $prop ];

			if ( 'view' === $context ) {
				/**
				 * Filter to adjust a specific address property for a DHL label.
				 *
				 * The dynamic portion of the hook name, `$this->get_hook_prefix()` constructs an individual
				 * hook name which uses `woocommerce_gzd_dhl_label_get_` as a prefix. Additionally
				 * `$address` contains the current address type e.g. sender_address and `$prop` contains the actual
				 * property e.g. street.
				 *
				 * Example hook name: `woocommerce_gzd_dhl_return_label_get_sender_address_street`
				 *
				 * @param string                          $value The address property value.
				 * @param Label $label The label object.
				 *
				 * @since 3.0.0
				 * @package Vendidero/Germanized/DHL
				 */
				$value = apply_filters( "{$this->get_hook_prefix()}{$address}_{$prop}", $value, $this );
			}
		}

		return $value;
	}

	/**
	 * Returns linked children labels.
	 *
	 * @return ShipmentLabel[]
	 */
	public function get_children() {
		if ( ! $this->legacy ) {
			return parent::get_children();
		} else {
			return wc_gzd_dhl_get_labels( array(
				'parent_id' => $this->get_id(),
			) );
		}
	}

    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

	public function set_default_path( $path ) {
		$this->set_prop( 'default_path', $path );
	}

    public function set_export_path( $path ) {
        $this->set_prop( 'export_path', $path );
    }

	protected function set_time_prop( $prop, $value ) {
		try {

			if ( empty( $value ) ) {
				$this->set_prop( $prop, null );
				return;
			}

			if ( is_a( $value, 'WC_DateTime' ) ) {
				$datetime = $value;
			} elseif ( is_numeric( $value ) ) {
				$datetime = new WC_DateTime( "@{$value}" );
			} else {
				$timestamp = wc_string_to_timestamp( $value );
				$datetime  = new WC_DateTime( "@{$timestamp}" );
			}

			$this->set_prop( $prop, $datetime );
		} catch ( Exception $e ) {} // @codingStandardsIgnoreLine.
	}

    public function add_service( $service ) {
        $services = (array) $this->get_services();

        if ( ! in_array( $service, $services ) && in_array( $service, wc_gzd_dhl_get_services() ) ) {
            $services[] = $service;

            $this->set_services( $services );
            return true;
        }

        return false;
    }

	public function get_default_file() {
		if ( ! $path = $this->get_default_path() ) {
			return false;
		}

		return $this->get_file_by_path( $path );
	}

	public function get_default_filename() {
		if ( ! $path = $this->get_default_path() ) {
			return false;
		}

		return basename( $path );
	}

    public function get_export_file() {
        if ( ! $path = $this->get_export_path() ) {
            return false;
        }

        return $this->get_file_by_path( $path );
    }

	public function get_export_filename() {
		if ( ! $path = $this->get_export_path() ) {
			return false;
		}

		return basename( $path );
	}
}
