<?php

namespace Vendidero\Germanized\DHL\Legacy;

use Vendidero\Germanized\DHL\Package;
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
abstract class Label extends \Vendidero\Germanized\Shipments\Labels\Label {

	protected $legacy = false;

	public function __construct( $data = 0, $legacy = false ) {
		$label_id     = false;
		$this->legacy = $legacy;

		if ( $this->legacy ) {
			$this->data['dhl_product']  = '';
			$this->data['default_path'] = '';
			$this->data['export_path']  = '';
		}

		if ( $data instanceof Label ) {
			$label_id = $data->get_id();
		} elseif ( is_numeric( $data ) ) {
			$label_id = $data;
		}

		parent::__construct( $data );

		/**
		 * Legacy object support
		 */
		if ( $this->legacy && $this->get_id() <= 0 and $label_id > 0 ) {
			$data_store = WC_Data_Store::load( 'dhl-legacy-label' );

			// If we have an ID, load the user from the DB.
			try {
				$this->set_id( $label_id );
				$data_store->read( $this );

				$this->data_store_name = 'dhl-legacy-label';
				$this->data_store      = $data_store;
				$this->object_type     = 'dhl_label';
				$this->cache_group     = 'dhl-labels';
			} catch ( Exception $e ) {
				$this->set_id( 0 );
				$this->set_object_read( true );
			}
		}
	}

	public function set_props( $props, $context = 'set' ) {
		return parent::set_props( $props, $context );
	}

	public function get_additional_file_types() {
		return array(
			'default',
			'export'
		);
	}

	public function is_legacy() {
		return $this->legacy;
	}

	public function get_product_id( $context = 'view' ) {
		if ( $this->legacy ) {
			return $this->get_dhl_product();
		}

		return parent::get_product_id();
	}

	public function get_dhl_product( $context = 'view' ) {
		if ( $this->legacy ) {
			return $this->get_prop( 'dhl_product', $context );
		}

		return $this->get_product_id( $context );
	}

	public function set_dhl_product( $product ) {
		$this->set_prop( 'dhl_product', $product );
	}

	protected function get_file_by_path( $file ) {
		if ( $this->legacy ) {
			// If the file is relative, prepend upload dir.
			if ( $file && 0 !== strpos( $file, '/' ) && ( ( $uploads = Package::get_upload_dir() ) && false === $uploads['error'] ) ) {
				$file = $uploads['basedir'] . "/$file";

				return $file;
			} else {
				return false;
			}
		} else {
			return parent::get_file_by_path( $file );
		}
	}

	public function download( $args = array() ) {
		if ( $this->legacy ) {
			DownloadHandler::download_label( $this->get_id(), $args );
		} else {
			parent::download( $args );
		}
	}

	public function get_tracking_url() {
		if ( $shipment = $this->get_shipment() ) {
			return $shipment->get_tracking_url();
		}

		return '';
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

	public function get_path( $context = 'view', $file_type = '' ) {
		if ( 'default' === $file_type ) {
			return $this->get_default_path( $context );
		} elseif( 'export' === $file_type ) {
			return $this->get_export_path( $context );
		} else {
			return parent::get_path( $context, $file_type );
		}
	}

	public function set_path( $path, $file_type = '' ) {
		if ( 'default' === $file_type ) {
			$this->set_default_path( $path );
		} elseif( 'export' === $file_type ) {
			$this->set_export_path( $path );
		} else {
			parent::set_path( $path, $file_type );
		}
	}

	public function get_default_file() {
		if ( ! $path = $this->get_default_path() ) {
			return false;
		}

		return $this->get_file_by_path( $path );
	}

	public function get_default_filename() {
		if ( ! $path = $this->get_default_path() ) {
			return $this->get_new_filename( 'default' );
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
			return $this->get_new_filename( 'export' );
		}

		return basename( $path );
	}

	public function set_default_path( $path ) {
		$this->set_prop( 'default_path', $path );
	}

	public function set_export_path( $path ) {
		$this->set_prop( 'export_path', $path );
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
				 * @param \Vendidero\Germanized\DHL\Label\Label $label The label object.
				 *
				 * @since 3.0.0
				 * @package Vendidero/Germanized/DHL
				 */
				$value = apply_filters( "{$this->get_hook_prefix()}{$address}_{$prop}", $value, $this );
			}
		}

		return $value;
	}
}