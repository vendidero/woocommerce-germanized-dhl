<?php

namespace Vendidero\Germanized\DHL\Api;

use Exception;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

class ReturnRest extends Rest {

	public function __construct() {}

	public function create_return_label( $args ) {
		$request_args = $args;

		$request_args = array(
			'receiverId' => '22222222220701',
			"customerReference" => "Test",
			"shipmentReference" =>  "test",
			"senderAddress" => array(
				'name1' => 'Dennis',
				'name2' => 'Nissle',
				'streetName' => 'Schillerstraße',
				'houseNumber' => '36',
				'postCode' => '12207',
				'city' => 'Berlin',
				'country' => array(
					'countryISOCode' => 'DE',
				),
			),
			'email' => 'info@vendidero.de',
			'telephoneNumber' => '',
			"weightInGrams" => '1000',
			'value' => '0',
		);

		return $this->post_request( '/returns/', $request_args );
	}

	protected function set_header( $authorization = '' ) {
		parent::set_header();

		if ( ! empty( $authorization ) ) {
			$this->remote_header['Authorization'] = $authorization;
		}

		$this->remote_header['DPDHL-User-Authentication-Token'] = 'MjIyMjIyMjIyMl9DdXN0b21lcjp1QlFiWjYyIVppQmlWVmJoYwo=';
	}
}
