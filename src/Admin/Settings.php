<?php

namespace Vendidero\Germanized\DHL\Admin;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin class.
 */
class Settings {

	public static function get_section_description( $section ) {
		return '';
	}

	protected static function get_general_settings() {
		$dhl_products_int = array();
		$dhl_products_dom = array();

		foreach( ( wc_gzd_dhl_get_products_domestic() + wc_gzd_dhl_get_products_international() ) as $product => $title ) {
			$dhl_products_dom[] = array(
				'title'             => $title,
				'type'              => 'text',
				'default'           => '',
				'id'                => 'woocommerce_gzd_dhl_participation_' . $product,
				'custom_attributes'	=> array( 'maxlength' => '2' ),
			);
		}

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_general_options' ),

			array(
				'title' 	        => __( 'Enable', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable DHL integration.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_enable',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Customer Number (EKP)', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your 10 digits DHL customer number, also called "EKP". Find your %s in the DHL business portal.', 'woocommerce-germanized-dhl' ), '<a href="" target="_blank">' . __( 'customer number', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_account_number',
				'default'           => '',
				'placeholder'		=> '1234567890',
				'custom_attributes'	=> array( 'maxlength' => '10' )
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_general_options' ),

			array( 'title' => __( 'API', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_api_options' ),

			array(
				'title' 	=> __( 'Enable Sandbox', 'woocommerce-germanized-dhl' ),
				'desc' 		=> __( 'Activate Sandbox mode for testing purposes.', 'woocommerce-germanized-dhl' ),
				'id' 		=> 'woocommerce_gzd_dhl_sandbox_mode',
				'default'	=> 'no',
				'type' 		=> 'gzd_toggle',
			),

			array(
				'title'             => __( 'Live Username', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your username for the DHL business customer portal. Please note the lower case and test your access data in advance at %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_username',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => 'no', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Live Password', 'woocommerce-germanized-dhl' ),
				'type'              => 'password',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your password for the DHL business customer portal. Please note the new assignment of the password to 3 (Standard User) or 12 (System User) months and test your access data in advance at %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) .'</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_password',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => 'no', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Sandbox Username', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your username for the DHL developer portal. Please note the lower case and test your access data in advance at %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_sandbox_username',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => '', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Sandbox Password', 'woocommerce-germanized-dhl' ),
				'type'              => 'password',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your password for the DHL developer portal. Please test your access data in advance at %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) .'</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_sandbox_password',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => '', 'autocomplete' => 'new-password' )
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_api_options' ),

			array( 'title' => __( 'Products and Participation Numbers', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_product_options', 'desc' => sprintf( __( 'For each DHL product that you would like to use, please enter your participation number here. The participation number consists of the last two characters of the respective accounting number, which you will find in your %s (e.g.: 01).', 'woocommerce-germanized-dhl' ), '<a href="#" target="_blank">' . __( 'contract data', 'woocommerce-germanized-dhl' ) . '</a>' ) ),
		);

		$settings = array_merge( $settings, $dhl_products_dom );

		$settings = array_merge( $settings, $dhl_products_int );

		$settings = array_merge( $settings, array(
			array(
				'title'             => __( 'DHL Retoure', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id'                => 'woocommerce_gzd_dhl_participation_return',
				'placeholder'		=> '',
				'custom_attributes'	=> array( 'maxlength' => '2' )
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_product_options' ),
		) );

		return $settings;
	}

	protected static function get_default_bank_account_data( $data_key = '' ) {
		$bacs = get_option( 'woocommerce_bacs_accounts' );

		if ( ! empty( $bacs ) && is_array( $bacs ) ) {
			$data = $bacs[0];

			if ( isset( $data[ 'account_' . $data_key ] ) ) {
				return $data[ 'account_' . $data_key ];
			} elseif ( isset( $data[ $data_key ] ) ) {
				return $data[ $data_key ];
			}
		}

		return '';
	}

	protected static function get_store_address_country() {
		$default = get_option( 'woocommerce_store_country' );

		return in_array( $default, array( 'DE', 'AT' ) ) ? $default : 'DE';
	}

	protected static function get_store_address_street() {
		$store_address = wc_gzd_split_shipment_street( get_option( 'woocommerce_store_address' ) );

		return $store_address['street'];
	}

	protected static function get_store_address_street_number() {
		$store_address = wc_gzd_split_shipment_street( get_option( 'woocommerce_store_address' ) );

		return $store_address['number'];
	}

	protected static function get_label_settings() {
		$select_dhl_product_dom = wc_gzd_dhl_get_products_domestic();
		$select_dhl_product_int = wc_gzd_dhl_get_products_international();
		$shipment_statuses      = array_diff_key( wc_gzd_get_shipment_statuses(), array_fill_keys( array( 'gzd-draft', 'gzd-delivered', 'gzd-returned' ), '' ) );

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_label_options' ),

			array(
				'title'             => __( 'Domestic Default Service', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'V01PAK',
				'id'                => 'woocommerce_gzd_label_default_product_dom',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Please select your default DHL Paket shipping service for domestic shippments that you want to offer to your customers (you can always change this within each individual order afterwards).', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $select_dhl_product_dom,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title'             => __( 'Int. Default Service', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'V55PAK',
				'id'                => 'woocommerce_gzd_label_default_product_int',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Please select your default DHL Paket shipping service for cross-border shippments that you want to offer to your customers (you can always change this within each individual order afterwards).', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $select_dhl_product_int,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title' 	        => __( 'Codeable', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Generate only if address is codeable.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_address_codeable_only',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
				'desc_tip'          => __( 'Choose this option if you want to make sure that by default labels are only generated for codeable addresses.', 'woocommerce-germanized-dhl' ),
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_label_options' ),

			array( 'title' => __( 'Automation', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_automation_options', 'desc' => __( 'Choose whether and under which conditions labels for your shipments shall be requested and generated automatically.', 'woocommerce-germanized-dhl' ) ),

			array(
				'title' 	        => __( 'Enable', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Automatically create labels for shipments.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_automation_enable',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Status', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'gzd-processing',
				'id'                => 'woocommerce_gzd_label_automation_shipment_status',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Choose a shipment status which should trigger generation of a label.', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $shipment_statuses,
				'class'             => 'wc-enhanced-select',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_label_automation_enable' => '' )
			),

			array(
				'title' 	        => __( 'Age Verification', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Verify ages if shipment contains applicable items.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Germanized offers an %s to be enabled for certain products and/or product categories. By checking this option labels for shipments with applicable items will automatically have the age check service enabled.', 'woocommerce-germanized-dhl' ), '<a href="">' . __( 'age verification checkbox', 'woocommerce-germanized' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_label_automation_age_check_sync',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_label_automation_enable' => '' )
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_automation_options' ),

			array( 'title' => __( 'Shipper Address', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_shipper_address_options' ),

			array(
				'title'             => __( 'Name', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_name',
				'default'           => '',
			),

			array(
				'title'             => __( 'Company', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_company',
				'default'           => get_bloginfo( 'name' ),
			),

			array(
				'title'             => __( 'Street', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_street',
				'default'           => self::get_store_address_street(),
			),

			array(
				'title'             => __( 'Street Number', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_street_no',
				'default'           => self::get_store_address_street_number(),
			),

			array(
				'title'             => __( 'City', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_city',
				'default'           => get_option( 'woocommerce_store_city' ),
			),

			array(
				'title'             => __( 'Postcode', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_postcode',
				'default'           => get_option( 'woocommerce_store_postcode' ),
			),

			array(
				'title'             => __( 'Country', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'class'		        => 'wc-enhanced-select',
				'options'           => array( 'DE' => __( 'Germany', 'woocommerce-germanized-dhl' ), 'AT' => __( 'Austria', 'woocommerce-germanized-dhl' ) ),
				'id' 		        => 'woocommerce_gzd_dhl_shipper_country',
				'default'           => self::get_store_address_country(),
			),

			array(
				'title'             => __( 'Phone', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_phone',
				'default'           => '',
			),

			array(
				'title'             => __( 'Email', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_shipper_email',
				'default'           => get_option( 'admin_email' ),
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_shipper_address_options' ),

			array( 'title' => __( 'Return Address', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_return_address_options' ),

			array(
				'title'             => __( 'Name', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_name',
				'default'           => '',
			),

			array(
				'title'             => __( 'Company', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_company',
				'default'           => get_bloginfo( 'name' ),
			),

			array(
				'title'             => __( 'Street', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_street',
				'default'           => self::get_store_address_street(),
			),

			array(
				'title'             => __( 'Street Number', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_street_no',
				'default'           => self::get_store_address_street_number(),
			),

			array(
				'title'             => __( 'City', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_city',
				'default'           => get_option( 'woocommerce_store_city' ),
			),

			array(
				'title'             => __( 'Postcode', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_postcode',
				'default'           => get_option( 'woocommerce_store_postcode' ),
			),

			array(
				'title'             => __( 'Country', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'class'		        => 'chosen_select',
				'options'           => array( 'DE' => __( 'Germany', 'woocommerce-germanized-dhl' ), 'AT' => __( 'Austria', 'woocommerce-germanized-dhl' ) ),
				'id' 		        => 'woocommerce_gzd_dhl_return_address_country',
				'default'           => self::get_store_address_country(),
			),

			array(
				'title'             => __( 'Phone', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_phone',
				'default'           => '',
			),

			array(
				'title'             => __( 'Email', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_return_address_email',
				'default'           => get_option( 'admin_email' ),
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_return_address_options' ),

			array( 'title' => __( 'Bank Account', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_bank_account_options', 'desc' => __( 'Enter your bank details needed for services that use COD.', 'woocommerce-germanized-dhl' ) ),

			array(
				'title'             => __( 'Holder', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_holder',
				'default'           => self::get_default_bank_account_data( 'name' ),
			),

			array(
				'title'             => __( 'Bank Name', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_name',
				'default'           => self::get_default_bank_account_data( 'bank_name' ),
			),

			array(
				'title'             => __( 'IBAN', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_iban',
				'default'           => self::get_default_bank_account_data( 'iban' ),
			),

			array(
				'title'             => __( 'BIC', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_bic',
				'default'           => self::get_default_bank_account_data( 'bic' ),
			),

			array(
				'title'             => __( 'Payment Reference', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_ref',
				'custom_attributes'	=> array( 'maxlength' => '35' ),
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Use these placeholders to add info to the payment reference: %s. This text is limited to 35 characters.', 'woocommerce-germanized-dhl' ), '<code>{shipment_id}, {order_id}, {email}</code>' ) . '</div>',
				'default'           => '{shipment_id}'
			),

			array(
				'title'             => __( 'Payment Reference 2', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_dhl_bank_ref_2',
				'custom_attributes'	=> array( 'maxlength' => '35' ),
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Use these placeholders to add info to the payment reference: %s. This text is limited to 35 characters.', 'woocommerce-germanized-dhl' ), '<code>{shipment_id}, {order_id}, {email}</code>' ) . '</div>',
				'default'           => '{email}'
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_bank_account_options' ),
		);

		return $settings;
	}

	protected static function get_service_settings() {
		$wc_shipping_methods = WC()->shipping->get_shipping_methods();
		$wc_shipping_titles  = wp_list_pluck( $wc_shipping_methods, 'method_title', 'id' );

		$wc_payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$wc_gateway_titles   = wp_list_pluck( $wc_payment_gateways, 'method_title', 'id' );

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_preferred_options' ),

			array(
				'title'             => __( 'Shipping methods', 'woocommerce-germanized-dhl' ),
				'type'              => 'multiselect',
				'id'                => 'woocommerce_gzd_dhl_preferred_shipping_methods_enabled',
				'desc'              => __( 'Select the shipping methods supporting preferred services.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'options'           => $wc_shipping_titles,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title' 	        => __( 'Preferred day', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred day delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred day of delivery during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for preferred day delivery. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_cost',
				'default'           => '1.2',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'title'             => __( 'Cut-off time', 'woocommerce-germanized-dhl' ),
				'type'              => 'time',
				'id'                => 'woocommerce_gzd_dhl_preferred_cutoff_time',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'The cut-off time is the latest possible order time up to which the minimum preferred day (day of order + 2 working days) can be guaranteed. As soon as the time is exceeded, the earliest preferred day displayed in the frontend will be shifted to one day later (day of order + 3 working days).', 'woocommerce-germanized-dhl' ) . '</div>',
				'default'           => '12:00',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'title' 	        => __( 'Exclude days of transfer', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Monday', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => __( 'Exclude days from transferring shipments to DHL.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_mon',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => 'start',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Tuesday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_tue',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Wednesday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_wed',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Thursday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_thu',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Friday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_fri',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Saturday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_exclusion_sat',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => 'end',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'title' 	        => __( 'Preferred time', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred time delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred time of delivery during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_preferred_time_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for preferred time delivery. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_preferred_time_cost',
				'default'           => '4.8',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_time_enable' => '' )
			),

			array(
				'title'             => __( 'Combined Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for the combination of preferred day and time. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_preferred_day_time_cost',
				'default'           => '4.8',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_preferred_time_enable' => '', 'data-show_if_woocommerce_gzd_dhl_preferred_day_enable' => '' )
			),

			array(
				'title' 	        => __( 'Preferred location', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred location delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred delivery location during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_preferred_location_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title' 	        => __( 'Preferred neighbor', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred neighbor delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to deliver to their preferred neighbor during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_preferred_neighbor_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Exclude gateways', 'woocommerce-germanized-dhl' ),
				'type'              => 'multiselect',
				'desc'              => __( 'Select payment gateways to be excluded from showing preferred services.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id'                => 'woocommerce_gzd_dhl_preferred_payment_gateways_excluded',
				'options'           => $wc_gateway_titles,
				'class'             => 'wc-enhanced-select',
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_preferred_options' ),
		);

		return $settings;
	}

	protected static function get_pickup_settings() {
		$wc_shipping_methods = WC()->shipping->get_shipping_methods();
		$wc_shipping_titles  = wp_list_pluck( $wc_shipping_methods, 'method_title', 'id' );

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_pickup_options' ),

			array(
				'title' 	        => __( 'Packstation', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable delivery to Packstation.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => __( 'Let customers choose a Packstation as delivery address.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_packstation_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title' 	        => __( 'Postoffice', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable delivery to Post Offices.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => __( 'Let customers choose a Post Office as delivery address.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_postoffice_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title' 	        => __( 'Parcel Shop', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable delivery to Parcel Shops.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => __( 'Let customers choose a Parcel Shop as delivery address.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_parcelshop_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title' 	        => __( 'Excluded Methods', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_shipping_methods_excluded',
				'default'	        => array(),
				'class' 	        => 'wc-enhanced-select',
				'type'              => 'multiselect',
				'options'           => $wc_shipping_titles,
				'desc_tip'	        => __( 'Optionally choose shipping methods that do not support pickup delivery.', 'woocommerce-germanized-dhl' ),
			),

			array(
				'title' 	        => __( 'Map', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Let customers find a pickup option on a map.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enable this option to let your customers choose a pickup option from a map within the checkout. If this option is disabled a link to the DHL website is placed instead.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_map_enable',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'API Key', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'id' 		        => 'woocommerce_gzd_parcel_pickup_map_api_key',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_parcel_pickup_map_enable' => '' ),
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'To integrate a map within your checkout you\'ll need a valid API key for Google Maps. You may %s.', 'woocommerce-germanized-dhl' ), '<a href="" target="_blank">' . __( 'retrieve a new one', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'default'           => ''
			),

			array(
				'title'             => __( 'Limit results', 'woocommerce-germanized-dhl' ),
				'type'              => 'number',
				'id' 		        => 'woocommerce_gzd_parcel_pickup_map_max_results',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_parcel_pickup_map_enable' => '' ),
				'desc_tip'          => __( 'Limit the number of pickup stores shown on the map', 'woocommerce-germanized-dhl' ),
				'default'           => 20,
				'css'               => 'max-width: 60px;',
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_pickup_options' ),
		);

		return $settings;
	}

	public static function get_settings( $current_section = '' ) {
		$settings = array();

		if ( '' === $current_section ) {
			$settings = self::get_general_settings();
		} elseif( 'labels' === $current_section ) {
			$settings = self::get_label_settings();
		} elseif( 'services' === $current_section && Package::base_country_supports( 'services' ) ) {
			$settings = self::get_service_settings();
		} elseif( 'pickup' === $current_section ) {
			$settings = self::get_pickup_settings();
		}

		return $settings;
	}

	public static function get_sections() {
		$sections = array(
			''          => __( 'General', 'woocommerce-germanized-dhl' ),
			'labels'    => __( 'Labels', 'woocommerce-germanized-dhl' ),
			'services'  => __( 'Preferred Services', 'woocommerce-germanized-dhl' ),
			'pickup'    => __( 'Parcel Pickup', 'woocommerce-germanized-dhl' ),
		);

		if ( ! Package::base_country_supports( 'services' ) ) {
			unset( $sections['services'] );
		}

		return $sections;
	}
}
