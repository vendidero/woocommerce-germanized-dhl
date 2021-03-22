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
}
