<?php

namespace Vendidero\Germanized\DHL\Api;

use Exception;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

class ParcelRest extends Rest {

    protected $account_num = '';

    public function __construct() {}

    public function get_services( $args ) {

        $args = wp_parse_args( $args, array(
            'postcode'    => '',
            'account_num' => Package::get_setting( 'account_num' ),
            'start_date'  => '',
        ) );

        if ( empty( $args['postcode'] ) ) {
            throw new Exception( __( 'Please provide the receiver postnumber.', 'woocommerce-germanized-dhl' ) );
        }

        if ( empty( $args['account_num'] ) && ! Package::is_debug_mode() ) {
            throw new Exception( __( 'Please set an account in the DHL shipping settings.', 'woocommerce-germanized-dhl' ) );
        }

        if ( empty( $args['start_date'] ) ) {
            throw new Exception( __( 'Please provide the shipment start date.', 'woocommerce-germanized-dhl' ) );
        }

        $this->account_num = $args['account_num'];

        // curl -X GET --header 'Accept: application/json' --header 'X-EKP: 2222222222' 'https://cig.dhl.de/services/sandbox/rest/checkout/28757/availableServices?startDate=2018-08-17'
        return $this->get_request( '/checkout/' . $args['postcode'] . '/availableServices', array( 'startDate' => $args['start_date'] ) );
    }

    protected function set_header( $authorization = '' ) {
        parent::set_header();

        if ( ! empty( $authorization ) ) {
        	$this->remote_header['Authorization'] = $authorization;
        }

        $this->remote_header['X-EKP'] = $this->account_num;
    }
}
