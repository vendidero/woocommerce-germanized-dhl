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
			$this->data['dhl_product_id'] = '';
			$this->data['default_path']   = '';
			$this->data['export_path']    = '';
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
		return $this->get_prop( 'dhl_product', $context );
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
		}

		parent::download( $args );
	}

	public function get_tracking_url() {
		if ( $shipment = $this->get_shipment() ) {
			return $shipment->get_tracking_url();
		}

		return '';
	}
}