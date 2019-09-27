<?php

namespace Vendidero\Germanized\DHL\Api;

use Exception;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

class ReturnRest extends Rest {

	public function __construct() {}

	public function create_return_label( $args ) {
		$request_args = array(
			'receiverId' => 'DE',
			'billingNumber' => '22222222220701',
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
					'countryISOCode' => 'DEU',
					'country' => 'Germany'
				),
			),
			'email' => 'info@vendidero.de',
			'telephoneNumber' => '',
			"weightInGrams" => '5000',
			'value' => '60',
			'returnDocumentType' => 'SHIPMENT_LABEL'
		);

		return $this->post_request( '/returns/', json_encode( $request_args ) );
	}

	protected function get_retoure_auth() {
		return base64_encode( Package::get_retoure_api_user() . ':' . Package::get_retoure_api_signature() );
	}

	protected function set_header( $authorization = '' ) {
		parent::set_header();

		if ( ! empty( $authorization ) ) {
			$this->remote_header['Authorization'] = $authorization;
		}

		$this->remote_header['DPDHL-User-Authentication-Token'] = $this->get_retoure_auth();
	}
}
