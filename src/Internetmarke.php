<?php

namespace Vendidero\Germanized\DHL;

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

	public function __construct() {
		$this->partner  = new PartnerInformation( Package::get_internetmarke_partner_id(), Package::get_internetmarke_key_phase(), Package::get_internetmarke_token() );
		$this->api      = new Service( $this->partner );
		$this->errors   = new \WP_Error();

		if ( Package::get_internetmarke_username() ) {
			try {
				$this->user = $this->api->authenticateUser( Package::get_internetmarke_username(), Package::get_internetmarke_password() );
			} catch( \Exception $e ) {
				$this->errors->add( 'authentication', _x( 'Wrong username or password', 'woocommerce-germanized-dhl', 'woocommerce-germanized-dhl' ) );
			}
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

	public function get_products() {

	}
}