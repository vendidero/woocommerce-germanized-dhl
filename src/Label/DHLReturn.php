<?php

namespace Vendidero\Germanized\DHL\Label;
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\Interfaces\ShipmentReturnLabel;

defined( 'ABSPATH' ) || exit;

/**
 * DHL ReturnLabel class.
 */
class DHLReturn extends ReturnLabel {

	/**
	 * Stores product data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'receiver_slug'  => '',
		'sender_address' => array()
	);

	protected function get_hook_prefix() {
		return 'woocommerce_gzd_dhl_return_label_get_';
	}

	public function get_type() {
		return 'return';
	}

	public function get_receiver_id() {
		$slug = $this->get_receiver_slug();
		$id   = '';

		if ( $has_id = Package::get_return_receiver_by_slug( $slug ) ) {
			$id = $has_id['id'];
		}

		/**
		 * Returns the return receiver id for a certain DHL label.
		 *
		 * The dynamic portion of the hook name, `$this->get_hook_prefix()` constructs an individual
		 * hook name which uses `woocommerce_gzd_dhl_return_label_get_` as a prefix.
		 *
		 * Example hook name: `woocommerce_gzd_dhl_return_label_get_receiver_id`
		 *
		 * @param string      $id The receiver id.
		 * @param ReturnLabel $label The return label
		 *
		 * @package Vendidero/Germanized/DHL
		 */
		return apply_filters( "{$this->get_hook_prefix()}receiver_id", $id, $this );
	}

	public function get_receiver_slug( $context = 'view' ) {
		return $this->get_prop( 'receiver_slug', $context );
	}

	public function set_receiver_slug( $receiver_slug ) {
		$this->set_prop( 'receiver_slug', $receiver_slug );
	}
}
