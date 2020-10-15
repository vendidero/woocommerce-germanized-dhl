<?php

namespace Vendidero\Germanized\DHL;

defined( 'ABSPATH' ) || exit;

/**
 * Deutsche Post Label class.
 */
class PostLabel extends Label {

	/**
	 * Stores product data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'page_format'   => '',
		'shop_order_id' => '',
		'stamp_total'   => 0,
		'voucher_id'    => '',
		'original_url'  => '',
		'manifest_url'  => ''
	);

	public function get_type() {
		return 'post';
	}

	public function get_number( $context = 'view' ) {
		$number = parent::get_number( $context );

		return $number;
	}

	public function get_page_format( $context = 'view' ) {
		return $this->get_prop( 'page_format', $context );
	}

	public function set_page_format( $value ) {
		$this->set_prop( 'page_format', $value );
	}

	public function get_stamp_total( $context = 'view' ) {
		return $this->get_prop( 'stamp_total', $context );
	}

	public function set_stamp_total( $value ) {
		$this->set_prop( 'stamp_total', absint( $value ) );
	}

	public function get_shop_order_id( $context = 'view' ) {
		return $this->get_prop( 'shop_order_id', $context );
	}

	public function set_shop_order_id( $value ) {
		$this->set_prop( 'shop_order_id', $value );
	}

	public function set_dhl_product( $product ) {
		$this->set_prop( 'dhl_product', $product );
	}

	public function get_voucher_id( $context = 'view' ) {
		return $this->get_prop( 'voucher_id', $context );
	}

	public function set_voucher_id( $value ) {
		$this->set_prop( 'voucher_id', $value );
	}

	public function get_original_url( $context = 'view' ) {
		return $this->get_prop( 'original_url', $context );
	}

	public function set_original_url( $value ) {
		$this->set_prop( 'original_url', $value );
	}

	public function get_manifest_url( $context = 'view' ) {
		return $this->get_prop( 'manifest_url', $context );
	}

	public function set_manifest_url( $value ) {
		$this->set_prop( 'manifest_url', $value );
	}

	public function is_trackable() {
		$voucher_id = $this->get_voucher_id();

		if ( ! empty( $voucher_id ) && $voucher_id !== $this->get_number() ) {
			return true;
		} elseif( in_array( $this->get_dhl_product(), [ 232, 233, 234, 238, 1007, 195, 1017, 196, 1027, 197, 1037, 198, 1047, 199, 1057, 200 ] ) ) {
			return true;
		}

		return false;
	}
}
