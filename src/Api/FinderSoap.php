<?php

namespace Vendidero\Germanized\DHL\Api;

use Exception;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

class FinderSoap extends Soap {

    public function __construct( ) {
        try {
            parent::__construct( Package::get_parcel_finder_api_url() );
        } catch ( Exception $e ) {
            throw $e;
        }
    }

	public function get_access_token() {
		return $this->get_auth_api()->get_access_token();
	}

    public function test_connection() {
    	try {
		    $soap_client  = $this->get_access_token();
		    $soap_request = $this->get_request( array(
		    	'country'  => 'DE',
			    'postcode' => '12207'
		    ) );

		    $response_body = $soap_client->getParcellocationByAddress( $soap_request );
		    return true;
	    } catch( Exception $e ) {
		    return false;
	    }
    }

    public function validate_parcel_location( $args ) {
	    $args = wp_parse_args( $args, array(
		    'city'     => '',
		    'postcode' => '',
		    'country'  => Package::get_base_country(),
	    ) );


    }

	public function get_parcel_location( $args ) {
    	$args = wp_parse_args( $args, array(
	        'city'     => '',
	        'postcode' => '',
	        'country'  => Package::get_base_country(),
		    'address'  => '',
        ) );

		if ( empty( $args['city'] ) && empty( $args['postcode'] ) ) {
			throw new Exception( _x( 'At least shipping city or postcode is required.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}

		if ( empty( $args['country'] ) ) {
			throw new Exception( _x( 'Shipping country is required.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}

		$shipping_address = implode(' ', $args );
		$dhl_label_body   = array(
			'Version' => array(
				'majorRelease' => '2',
				'minorRelease' => '2'
			),
			'address'     => $shipping_address,
			'countrycode' => $args['country']
		);

		// Unset/remove any items that are empty strings or 0, even if required!
		$soap_request = $this->walk_recursive_remove( $dhl_label_body );

        try {
            $soap_client = $this->get_access_token();
            Package::log( '"getParcellocationByAddress" called with: ' . print_r( $soap_request, true ) );

            $response_body = $soap_client->getParcellocationByAddress( $soap_request );

            Package::log( 'Response: Successful' );

            return $response_body;
        } catch ( Exception $e ) {
            Package::log( 'Response Error: ' . $e->getMessage(), 'error' );
            throw $e;
        }
    }

    protected function get_request( $args ) {

    }
}
