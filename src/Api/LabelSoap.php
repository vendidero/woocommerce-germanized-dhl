<?php

namespace Vendidero\Germanized\DHL\Api;

use Exception;
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\DHL\Label;

defined( 'ABSPATH' ) || exit;

class LabelSoap extends Soap {

    const DHL_MAX_ITEMS = '6';

    const DHL_RETURN_PRODUCT = '07';

    public function __construct( ) {
        try {
            parent::__construct( Package::get_gk_api_url() );
        } catch ( Exception $e ) {
            throw $e;
        }
    }

    public function get_access_token() {
        return $this->get_auth_api()->get_access_token( Package::get_gk_api_user(), Package::get_gk_api_signature() );
    }

    public function test_connection() {
        try {
        	$soap_client = $this->get_access_token();
        	$version     = $soap_client->getVersion();
        	return true;
        } catch( Exception $e ) {
        	return false;
        }
    }

    protected function validate_field( $key, $value ) {
        try {
            switch ( $key ) {
                case 'weight':
                    wc_gzd_dhl_validate_api_field( $value );
                    break;
                case 'hs_code':
                    wc_gzd_dhl_validate_api_field( $value, 'string', 4, 11 );
                    break;
                default:
                    parent::validate_field( $key, $value );
                    break;
            }
        } catch ( Exception $e ) {
            throw $e;
        }
    }

    /**
     * @param Label $label
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function get_label( $label ) {
        $soap_request = $this->get_request( $label );

        try {
            $soap_client = $this->get_access_token();
            Package::log( '"createShipmentOrder" called with: ' . print_r( $soap_request, true ) );

            $response_body = $soap_client->createShipmentOrder( $soap_request );
            Package::log( 'Response: Successful' );

        } catch ( Exception $e ) {
            Package::log( 'Response Error: ' . $e->getMessage() );
            throw $e;
        }

        if ( $response_body->Status->statusCode != 0 ) {
            throw new Exception( sprintf( __( 'Could not create label - %s', 'woocommerce-germanized-dhl' ), $response_body->Status->statusMessage ) );
        } else {
            // Give the server 1 second to create the PDF before downloading it
            // sleep( 1 );
            try {

                if ( isset( $response_body->CreationState->LabelData->shipmentNumber ) ) {
                    $label->set_number( $response_body->CreationState->LabelData->shipmentNumber );
                }

                $filename_label = wc_gzd_dhl_generate_label_filename( $label, 'label' );

                if ( $path = wc_gzd_dhl_upload_file( $filename_label, $response_body->CreationState->LabelData->labelData ) ) {
                    $label->set_path( $path );
                }

                if ( isset( $response_body->CreationState->LabelData->exportLabelData ) ) {
                    $filename_export = wc_gzd_dhl_generate_label_filename( $label, 'export' );

                    if ( $path = wc_gzd_dhl_upload_file( $filename_export, $response_body->CreationState->LabelData->exportLabelData ) ) {
                        $label->set_export_path( $path );
                    }
                }

                $label->save();

            } catch( Exception $e ) {
                throw new Exception( __( 'Error while creating and uploading the label', 'woocommerce-germanized-dhl' ) );
            }

            return $label;
        }
    }

    /**
     * @param Label $label
     *
     * @throws Exception
     */
    protected function delete_label_call( $label ) {
        $soap_request =	array(
            'Version'          => array(
                'majorRelease' => '3',
                'minorRelease' => '0'
            ),
            'shipmentNumber'   => $label->get_number()
        );

        try {
            Package::log( '"deleteShipmentOrder" called with: ' . print_r( $soap_request, true ) );

            $soap_client   = $this->get_access_token();
            $response_body = $soap_client->deleteShipmentOrder( $soap_request );

            Package::log( 'Response Body: ' . print_r( $response_body, true ) );
        } catch (Exception $e) {
            throw $e;
        }

        if ( $response_body->Status->statusCode != 0 ) {
            throw new Exception( sprintf( __( 'Could not delete label - %s', 'woocommerce-germanized-dhl' ), $response_body->Status->statusMessage ) );
        }
    }

    /**
     * @param Label $label
     *
     * @throws Exception
     */
    public function delete_label( $label ) {
        try {
            $this->delete_label_call( $label );
        } catch (Exception $e) {
            throw $e;
        }

        $label->delete();
    }

    protected function set_arguments( $args ) {

        // Order details
        if ( empty( $args['order_details']['dhl_product'] )) {
            throw new Exception( __('DHL "Product" is empty!', 'pr-shipping-dhl') );
        }

        // return receiver
        if ( isset( $args['order_details']['return_address'] ) && ( $args['order_details']['return_address'] == 'yes' ) ) {

            if ( ( $args['order_details']['dhl_product'] != 'V01PAK' ) && ( $args['order_details']['dhl_product'] != 'V01PRIO' ) && ( $args['order_details']['dhl_product'] != 'V86PARCEL' ) && ( $args['order_details']['dhl_product'] != 'V55PAK' ) ){

                throw new Exception( __('Returns are not supported by this DHL Service.', 'pr-shipping-dhl') );
            }

            if ( empty( $args['dhl_settings']['return_name'] )) {
                throw new Exception( __('Please, provide a return name in the DHL shipping settings', 'pr-shipping-dhl') );
            }

            if ( empty( $args['dhl_settings']['return_address'] )) {
                throw new Exception( __('Please, provide a return address in the DHL shipping settings', 'pr-shipping-dhl') );
            }

            if ( empty( $args['dhl_settings']['return_address_no'] )) {
                throw new Exception( __('Please, provide a return address number in the DHL shipping settings', 'pr-shipping-dhl') );
            }

            if ( empty( $args['dhl_settings']['return_address_city'] )) {
                throw new Exception( __('Please, provide a return city in the DHL shipping settings', 'pr-shipping-dhl') );
            }

            if ( empty( $args['dhl_settings']['return_address_zip'] )) {
                throw new Exception( __('Please, provide a return postcode in the DHL shipping settings', 'pr-shipping-dhl') );
            }
        }

        if ( empty( $args['order_details']['order_id'] )) {
            throw new Exception( __('Shop "Order ID" is empty!', 'pr-shipping-dhl') );
        }

        if ( empty( $args['order_details']['weightUom'] )) {
            throw new Exception( __('Shop "Weight Units of Measure" is empty!', 'pr-shipping-dhl') );
        }

        if ( empty( $args['order_details']['weight'] )) {
            throw new Exception( __('Order "Weight" is empty!', 'pr-shipping-dhl') );
        }

        if ( isset( $args['order_details']['identcheck'] ) && ( $args['order_details']['identcheck'] == 'yes' ) ) {
            if ( empty( $args['shipping_address']['first_name'] ) || empty( $args['shipping_address']['last_name'] ) ) {
                throw new Exception( __('First name and last name must be passed for "Identity Check".', 'pr-shipping-dhl') );
            }

            if ( empty( $args['order_details']['identcheck_dob'] ) && empty( $args['order_details']['identcheck_age'] ) ) {
                throw new Exception( __('Either a "Date of Birth" or "Minimum Age" must be eneted for "Ident-Check".', 'pr-shipping-dhl') );
            }
        }

        // Validate weight
        try {
            $this->validate_field( 'weight', $args['order_details']['weight'] );
        } catch (Exception $e) {
            throw new Exception( 'Weight - ' . $e->getMessage() );
        }

        // if ( empty( $args['order_details']['duties'] )) {
        // 	throw new Exception( __('DHL "Duties" is empty!', 'pr-shipping-dhl') );
        // }

        if ( empty( $args['order_details']['currency'] )) {
            throw new Exception( __('Shop "Currency" is empty!', 'pr-shipping-dhl') );
        }

        if ( empty( $args['shipping_address']['city'] )) {
            throw new Exception( __('Shipping "City" is empty!', 'pr-shipping-dhl') );
        }

        if ( empty( $args['shipping_address']['country'] )) {
            throw new Exception( __('Shipping "Country" is empty!', 'pr-shipping-dhl') );
        }

        // Validate shipping address
        if ( empty( $args['shipping_address']['address_1'] )) {
            throw new Exception( __('Shipping "Address 1" is empty!', 'pr-shipping-dhl') );
        }


        $this->pos_ps = PR_DHL()->is_packstation( $args['shipping_address']['address_1'] );
        $this->pos_rs = PR_DHL()->is_parcelshop( $args['shipping_address']['address_1'] );
        $this->pos_po = PR_DHL()->is_post_office( $args['shipping_address']['address_1'] );

        // If Packstation, post number is mandatory
        if ( $this->pos_ps && empty( $args['shipping_address']['dhl_postnum'] ) ) {
            throw new Exception( __('Post Number is missing, it is mandatory for "Packstation" delivery.', 'pr-shipping-dhl') );
        }

        // Check address 2 if no parcel shop is being selected
        if ( ! $this->pos_ps && ! $this->pos_rs && ! $this->pos_po ) {
            // If address 2 missing, set last piece of an address to be address 2
            if ( empty( $args['shipping_address']['address_2'] )) {
                // Break address into pieces
                $address_exploded = explode(' ', $args['shipping_address']['address_1']);

                // Loop through address and set number value only...
                // ...last found number will be 'address_2'
                foreach ($address_exploded as $address_key => $address_value) {
                    if (is_numeric($address_value)) {
                        // Set last index as street number
                        $args['shipping_address']['address_2'] = $address_value;
                        $set_key = $address_key;
                    }
                }

                // If no number was found, then take last part of address no matter what it is
                if ( empty( $args['shipping_address']['address_2'] )) {
                    $args['shipping_address']['address_2'] = $address_exploded[ $address_key ];
                    $set_key = $address_key;
                }

                // Unset it in address 1
                unset( $address_exploded[ $set_key ] );
                // Set address 1 without street number or last part of address
                $args['shipping_address']['address_1'] = implode(' ', $address_exploded );
            }
        }

        // Add default values for required fields that might not be passed e.g. phone
        $default_args = array( 'shipping_address' =>
            array( 'name' => '',
                'company' => '',
                'address_2' => '',
                'email' => '',
                'postcode' => '',
                'state' => '',
                'phone' => ' '
            ),
        );

        $args['shipping_address'] = wp_parse_args( $args['shipping_address'], $default_args['shipping_address'] );

        $default_args_item = array(
            'item_description' => '',
            'sku' => '',
            'line_total' => 0,
            'country_origin' => '',
            'hs_code' => '',
            'qty' => 1
        );

        foreach ($args['items'] as $key => $item) {

            if ( ! empty( $item['hs_code'] ) ) {
                try {
                    $this->validate_field( 'hs_code', $item['hs_code'] );
                } catch (Exception $e) {
                    throw new Exception( 'HS Code - ' . $e->getMessage() );
                }
            }

            $args['items'][$key] = wp_parse_args( $item, $default_args_item );
        }

        $this->args = $args;
    }

    protected function get_account_number( $dhl_product ) {
        // create account number
        $product_number = preg_match('!\d+!', $dhl_product, $matches );

        if ( $product_number ) {
            $account_number = Package::get_setting( 'account_num' ) . $matches[0] . Package::get_setting( 'participation' );
            return $account_number;
        } else {
            throw new Exception( __('Could not create account number - no product number.', 'pr-shipping-dhl') );
        }
    }

    /**
     * @param Label $label
     * @return array
     *
     * @throws Exception
     */
    protected function get_request( $label ) {
        $shipment = $label->get_shipment();

        if ( ! $shipment ) {
            throw new Exception( sprintf( __( 'Could not fetch shipment %d.', 'woocommerce-germanized-dhl' ), $label->get_shipment_id() ) );
        }

        $services  = array();
        $bank_data = array();

        foreach( $label->get_services() as $service ) {

            $services[ $service ] = array(
                'active' => 1
            );

            switch ( $service ) {
                case 'AdditionalInsurance':
                    $services[ $service ]['insuranceAmount'] = $shipment->get_total();
                    break;
                case 'IdentCheck':
                    $services[ $service ]['Ident']['surname']     = $shipment->get_first_name();
                    $services[ $service ]['Ident']['givenName']   = $shipment->get_last_name();
                    $services[ $service ]['Ident']['dateOfBirth'] = $label->get_ident_date_of_birth() ? $label->get_ident_date_of_birth()->get_date() : '';
                    $services[ $service ]['Ident']['minimumAge']  = $label->get_ident_min_age();
                    break;
                case 'CashOnDelivery':
                    $services[ $service ]['codAmount'] = $shipment->get_total();

                    $bank_data_map = array(
                        'bank_holder' => 'accountOwner',
                        'bank_name'   => 'bankName',
                        'bank_iban'   => 'iban',
                        'bank_ref'    => 'note1',
                        'bank_ref_2'  => 'note2',
                        'bank_bic'    => 'bic'
                    );

                    foreach ( $bank_data_map as $key => $value ) {
                        if ( $setting_value = Package::get_setting( $key ) ) {
                            $bank_data[ $value ] = $setting_value;
                        }
                    }
                    break;
                case 'PreferredDay':
                    $services[ $service ]['details'] = $label->get_preferred_day() ? $label->get_preferred_day()->get_date() : '';
                    break;
                case 'PreferredTime':
                    $services[ $service ]['type'] = wc_gzd_dhl_aformat_preferred_api_time( $label->get_preferred_time() );
                    break;
                case 'VisualCheckOfAge':
                    $services[ $service ]['type'] = $label->get_visual_min_age();
                    break;
                case 'PreferredLocation':
                    $services[ $service ]['details'] = $label->get_preferred_location();
                    break;
                case 'PreferredNeighbour':
                    $services[ $service ]['details'] = $label->get_preferred_neighbor();
                    break;
            }
        }

        $dhl_label_body = array(
            'Version'            => array(
                'majorRelease'   => '3',
                'minorRelease'   => '0'
            ),
            'ShipmentOrder'      => array (
                'sequenceNumber' => $label->get_shipment_id(),
                'Shipment'       => array(
                    'ShipmentDetails' => array(
                        'product'        => $label->get_dhl_product(),
                        'accountNumber'  => self::get_account_number( $label->get_dhl_product() ),
                        'shipmentDate'   => date('Y-m-d' ),
                        'ShipmentItem'   => array(
                            'weightInKG' => wc_get_weight( $label->get_shipment()->get_weight(), 'kg' )
                        ),
                        'Service'        => $services,
                        'Notification'   => $label->has_email_notification() ? array( 'recipientEmailAddress' => $shipment->get_email() ) : array(),
                        'BankData'       => array(),
                    ),
                    'Shipper'       => array(
                        'Name'      => array(
                            'name1' => Package::get_setting( 'shipper_company' ) ? Package::get_setting( 'shipper_company' ) : Package::get_setting( 'shipper_name' ),
                            'name2' => Package::get_setting( 'shipper_company' ) ? Package::get_setting( 'shipper_name' ) : '',
                        ),
                        'Address'   => array(
                            'streetName'   => Package::get_setting( 'shipper_address' ),
                            'streetNumber' => Package::get_setting( 'shipper_address_no' ),
                            'zip'          => Package::get_setting( 'shipper_address_zip' ),
                            'city'         => Package::get_setting( 'shipper_address_city' ),
                            'Origin'       => array(
                                'countryISOCode' => Package::get_setting( 'shipper_country' ),
                                'state'          => Package::get_setting( 'shipper_address_state' ),
                            )
                        ),
                        'Communication' => array(
                            'phone' => Package::get_setting( 'shipper_phone' ),
                            'email' => Package::get_setting( 'shipper_email' )
                        )
                    ),
                    'Receiver'             => array(
                        'name1'            => $shipment->get_company() ? $shipment->get_company() : $shipment->get_formatted_full_name(),
                        'Address'          => array(
                            'name2'        => $shipment->get_company() ? $shipment->get_formatted_full_name() : '',
                            'streetName'   => $shipment->get_address_street(),
                            'streetNumber' => $shipment->get_address_street_number(),
                            'zip'          => $shipment->get_postcode(),
                            'city'         => $shipment->get_city(),
                            'Origin'       => array(
                                'countryISOCode' => $shipment->get_country(),
                                'state'          => $shipment->get_state()
                            )
                        ),
                        'Communication' => array(
                            'phone' => $shipment->get_phone(),
                            'email' => $shipment->get_email()
                        )
                    )
                ),
                'labelResponseType' => 'B64'
            )
        );

        if ( $shipment->send_to_external_pickup( array_values( wc_gzd_dhl_get_pickup_types() ) ) ) {

            // Address is NOT needed if using a parcel shop
            unset( $dhl_label_body['ShipmentOrder']['Shipment']['Receiver']['Address'] );

            $parcel_shop = array(
                'zip'    => $shipment->get_postcode(),
                'city'   => $shipment->get_city(),
                'Origin' => array(
                    'countryISOCode' => $shipment->get_country(),
                    'state'          => $shipment->get_state()
                )
            );

            $address_number = filter_var( $shipment->get_address_1(), FILTER_SANITIZE_NUMBER_INT );

            if ( $shipment->send_to_external_pickup( wc_gzd_dhl_get_pickup_type( 'packstation' ) ) ) {
                $parcel_shop['postNumber']        = $shipment->get_meta( '_dhl_postnum' );
                $parcel_shop['packstationNumber'] = $address_number;

                $dhl_label_body['ShipmentOrder']['Shipment']['Receiver']['Packstation'] = $parcel_shop;
            }

            if ( $shipment->send_to_external_pickup( wc_gzd_dhl_get_pickup_type( 'postoffice' ) ) || $shipment->send_to_external_pickup( wc_gzd_dhl_get_pickup_type( 'parcelshop' ) ) ) {

                if ( $post_number = $shipment->get_meta( '_dhl_postnum' ) ) {
                    $parcel_shop['postNumber'] = $post_number;

                    unset( $dhl_label_body['ShipmentOrder']['Shipment']['Receiver']['Communication']['email'] );
                }

                $parcel_shop['postfilialNumber'] = $address_number;
                $dhl_label_body['ShipmentOrder']['Shipment']['Receiver']['Postfiliale'] = $parcel_shop;
            }
        }

        if ( $label->has_return() ) {
            $dhl_label_body['ShipmentOrder']['Shipment']['ShipmentDetails']['returnShipmentAccountNumber'] = Package::get_setting( 'account_num' ) . self::DHL_RETURN_PRODUCT . Package::get_setting( 'participation_return' );

            $dhl_label_body['ShipmentOrder']['Shipment']['ReturnReceiver'] = array(
                'Name' => array(
                    'name1' => $label->get_return_company() ? $label->get_return_company() : $label->get_return_formatted_full_name(),
                    'name2' => $label->get_return_company() ? $label->get_return_formatted_full_name() : ''
                ),
                'Address' => array(
                    'streetName'   => $label->get_return_street(),
                    'streetNumber' => $label->get_return_street_number(),
                    'zip'          => $label->get_return_postcode(),
                    'city'         => $label->get_return_city(),
                    'Origin'       => array(
                        'countryISOCode' => $label->get_return_country(),
                        'state'          => $label->get_return_state(),
                    )
                ),
                'Communication' => array(
                    'phone' => $label->get_return_phone(),
                    'email' => $label->get_return_email()
                )
            );
        }

        if ( $label->codeable_address_only() ) {
            $dhl_label_body['ShipmentOrder']['PrintOnlyIfCodeable'] = array( 'active' => 1 );
        }

        if ( Package::is_crossborder_shipment( $shipment->get_country() ) ) {

            if ( sizeof( $shipment->get_items() ) > self::DHL_MAX_ITEMS ) {
                throw new Exception( sprintf( __( 'Only %s shipment items can be processed, your shipment has %s', 'woocommerce-germanized-dhl' ), self::DHL_MAX_ITEMS, sizeof( $shipment->get_items() ) ) );
            }

            $customsDetails   = array();
            $item_description = '';

            foreach ( $shipment->get_items() as $key => $item ) {

                $item_description .= ! empty( $item_description ) ? ', ' : '';
                $item_description .= $item['item_description'];

                $json_item = array(
                    'description'         => substr( $item['item_description'], 0, 255 ),
                    'countryCodeOrigin'   => $item->get_meta( '_country_origin' ),
                    'customsTariffNumber' => $item->get_meta( '_hs_code' ),
                    'amount'              => intval( $item->get_quantity() ),
                    'netWeightInKG'       => round( floatval( wc_get_weight( $item->get_weight(), 'kg' ) ), 2 ),
                    'customsValue'        => round( floatval( $item->get_total() ), 2 ),
                );

                array_push($customsDetails, $json_item );
            }

            $item_description = substr( $item_description, 0, 255 );

            $dhl_label_body['ShipmentOrder']['Shipment']['ExportDocument'] = array(
                'invoiceNumber'         => $shipment->get_id(),
                'exportType'            => 'OTHER',
                'exportTypeDescription' => $item_description,
                'termsOfTrade'          => $this->args['order_details']['duties'],
                'placeOfCommital'       => $shipment->get_country(),
                'ExportDocPosition'     => $customsDetails
            );
        }

        // Unset/remove any items that are empty strings or 0, even if required!
        $this->body_request = $this->walk_recursive_remove( $dhl_label_body );

        // Ensure Export Document is set before adding additional fee
        if ( isset( $this->body_request['ShipmentOrder']['Shipment']['ExportDocument'] ) ) {
            // Additional fees, required and 0 so place after check
            $this->body_request['ShipmentOrder']['Shipment']['ExportDocument']['additionalFee'] = 0;
        }

        // If "Ident-Check" enabled, then ensure both fields are passed even if empty
        if ( $label->has_service( 'IdentCheck' ) ) {
            if ( ! isset( $this->body_request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service']['IdentCheck']['Ident']['minimumAge'] ) ) {
                $this->body_request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service']['IdentCheck']['Ident']['minimumAge'] = '';
            }
            if ( ! isset( $this->body_request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service']['IdentCheck']['Ident']['dateOfBirth'] ) ) {
                $this->body_request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service']['IdentCheck']['Ident']['dateOfBirth'] = '';
            }
        }

        // Ensure 'postNumber' is passed with 'Postfiliale' even if empty
        if ( ! isset( $this->body_request['ShipmentOrder']['Shipment']['Receiver']['Postfiliale']['postNumber'] ) ) {
            // Additional fees, required and 0 so place after check
            $this->body_request['ShipmentOrder']['Shipment']['Receiver']['Postfiliale']['postNumber'] = '';
        }

        return $this->body_request;
    }
}
