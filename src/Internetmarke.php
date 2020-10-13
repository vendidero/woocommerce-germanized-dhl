<?php

namespace Vendidero\Germanized\DHL;

use baltpeter\Internetmarke\PageFormat;
use baltpeter\Internetmarke\PartnerInformation;
use baltpeter\Internetmarke\Service;
use baltpeter\Internetmarke\User;

defined( 'ABSPATH' ) || exit;

class Internetmarke {

	/**
	 * @var PartnerInformation|null
	 */
	protected $partner = null;

	/**
	 * @var Service|null
	 */
	protected $api = null;

	/**
	 * @var User|null
	 */
	protected $user = null;

	/**
	 * @var \WP_Error
	 */
	protected $errors = null;

	/**
	 * @var ImProductList|null
	 */
	protected $products = null;

	/**
	 * @var null|PageFormat[]
	 */
	protected $page_formats = null;

	public function __construct() {
		$this->partner  = new PartnerInformation( Package::get_internetmarke_partner_id(), Package::get_internetmarke_key_phase(), Package::get_internetmarke_token() );
		$this->api      = new Service( $this->partner );
		$this->errors   = new \WP_Error();

		if ( ! Package::is_internetmarke_enabled() ) {
			$this->errors->add( 'startup', _x( 'Internetmarke is disabled. Please enable Internetmarke.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}
	}

	public function auth() {
		if ( Package::get_internetmarke_username() && Package::get_internetmarke_password() ) {
			try {
				$this->user = $this->api->authenticateUser( Package::get_internetmarke_username(), Package::get_internetmarke_password() );
			} catch( \Exception $e ) {
				$this->errors->add( 'authentication', _x( 'Wrong username or password', 'dhl', 'woocommerce-germanized-dhl' ) );
			}
		}

		if ( ! $this->has_authentication_error() ) {
			return true;
		} else {
			return false;
		}
	}

	public function has_authentication_error() {
		$errors = $this->errors->get_error_message( 'authentication' );

		return empty( $errors ) ? false : true;
	}

	public function get_authentication_error() {
		$error = $this->errors->get_error_message( 'authentication' );

		return $error;
	}

	public function has_startup_error() {
		$errors = $this->errors->get_error_message( 'startup' );

		return empty( $errors ) ? false : true;
	}

	public function get_startup_error() {
		$error = $this->errors->get_error_message( 'startup' );

		return $error;
	}

	public function is_available() {
		return ! $this->has_authentication_error() && ! $this->has_startup_error();
	}

	public function get_user() {
		if ( ! $this->user ) {
			$this->auth();
		}

		if ( $this->user ) {
			return $this->user;
		} else {
			return false;
		}
	}

	public function get_balance( $force_refresh = false ) {
		$balance = get_transient( 'wc_gzd_dhl_portokasse_balance' );

		if ( ! $balance || $force_refresh ) {
			if ( $user = $this->get_user() ) {
				$balance = $user->getWalletBalance();

				set_transient( 'wc_gzd_dhl_portokasse_balance', $user->getWalletBalance(), HOUR_IN_SECONDS );
			} else {
				$balance = 0;
			}
		}

		return $balance;
	}

	protected function load_products() {
		if ( is_null( $this->products ) ) {
			$this->products = new ImProductList();
		}
	}

	public function get_products( $filters = array() ) {
		$this->load_products();

		return $this->products->get_products( $filters );
	}

	public function get_available_products() {
		$this->load_products();

		return $this->products->get_available_products();
	}

	public function get_product_list( $products ) {
		$list = array();

		foreach( $products as $product ) {
			$list[ $product->product_im_id ] = $product->product_name;
		}

		return $list;
	}

	public function get_page_formats( $force_refresh = false ) {
		if ( is_null( $this->page_formats ) ) {
			$this->page_formats = get_transient( 'wc_gzd_dhl_im_page_formats' );

			if ( ! $this->page_formats || $force_refresh ) {
				$this->page_formats = array();
				$this->page_formats = $this->api->retrievePageFormats();

				set_transient( 'wc_gzd_dhl_im_page_formats', $this->page_formats, DAY_IN_SECONDS );

				$page_formats = $this->page_formats;
			} else {
				$page_formats = $this->page_formats;
			}
		} else {
			$page_formats = $this->page_formats;
		}

		return $page_formats;
	}

	public function get_page_format_list() {
		$formats = $this->get_page_formats();
		$options = array();

		foreach( $formats as $format ) {
			if ( ! $format->isIsAddressPossible() ) {
				continue;
			}

			$options[ $format->getId() ] = $format->getName();
		}

		return $options;
	}
}