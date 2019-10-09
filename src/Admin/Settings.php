<?php

namespace Vendidero\Germanized\DHL\Admin;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin class.
 */
class Settings {

	public static function get_section_description( $section ) {
		if ( '' === $section ) {
			// return __( 'Adjust general settings. Learn more about Germanized & DHL' );
		}

		return '';
	}

	public static function get_setup_settings() {
		return array(
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
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your username for the DHL business customer portal. Please note the lower case and test your access data in advance %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_username',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => 'no', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Live Password', 'woocommerce-germanized-dhl' ),
				'type'              => 'password',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your password for the DHL business customer portal. Please note the new assignment of the password to 3 (Standard User) or 12 (System User) months and test your access data in advance %s.', 'woocommerce-germanized-dhl' ), '<a href="" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) .'</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_password',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => 'no', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Sandbox Username', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your username for the DHL developer portal. Please note the lower case and test your access data in advance %s.', 'woocommerce-germanized-dhl' ), '<a href="https://entwickler.dhl.de" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_sandbox_username',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => '', 'autocomplete' => 'new-password' )
			),

			array(
				'title'             => __( 'Sandbox Password', 'woocommerce-germanized-dhl' ),
				'type'              => 'password',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Your password for the DHL developer portal. Please test your access data in advance %s.', 'woocommerce-germanized-dhl' ), '<a href="https://entwickler.dhl.de" target = "_blank">' . __( 'here', 'woocommerce-germanized-dhl' ) .'</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_api_sandbox_password',
				'default'           => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_sandbox_mode' => '', 'autocomplete' => 'new-password' )
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_api_options' ),
		);
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

		$settings = self::get_setup_settings();

		$settings = array_merge( $settings, array(
			array( 'title' => __( 'Products and Participation Numbers', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_product_options', 'desc' => sprintf( __( 'For each DHL product that you would like to use, please enter your participation number here. The participation number consists of the last two characters of the respective accounting number, which you will find in your %s (e.g.: 01).', 'woocommerce-germanized-dhl' ), '<a href="#" target="_blank">' . __( 'contract data', 'woocommerce-germanized-dhl' ) . '</a>' ) ),
		) );

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

		return in_array( $default, Package::get_available_countries() ) ? $default : 'DE';
	}

	protected static function get_store_address_street() {
		$store_address = wc_gzd_split_shipment_street( get_option( 'woocommerce_store_address' ) );

		return $store_address['street'];
	}

	protected static function get_store_address_street_number() {
		$store_address = wc_gzd_split_shipment_street( get_option( 'woocommerce_store_address' ) );

		return $store_address['number'];
	}

	public static function get_label_default_settings( $for_shipping_method = false ) {

		$select_dhl_product_dom = wc_gzd_dhl_get_products_domestic();
		$select_dhl_product_int = wc_gzd_dhl_get_products_international();
		$duties                 = wc_gzd_dhl_get_duties();

		$settings = array(
			array(
				'title'             => __( 'Domestic Default Service', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'V01PAK',
				'id'                => 'woocommerce_gzd_dhl_label_default_product_dom',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Please select your default DHL shipping service for domestic shippments that you want to offer to your customers (you can always change this within each individual order afterwards).', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $select_dhl_product_dom,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title'             => __( 'Int. Default Service', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'V55PAK',
				'id'                => 'woocommerce_gzd_dhl_label_default_product_int',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Please select your default DHL shipping service for cross-border shippments that you want to offer to your customers (you can always change this within each individual order afterwards).', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $select_dhl_product_int,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title'             => __( 'Default Duty', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'DDP',
				'id'                => 'woocommerce_gzd_dhl_label_default_duty',
				'desc'              => __( 'Please select a default duty type.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'options'           => $duties,
				'class'             => 'wc-enhanced-select',
			),

			array(
				'title' 	        => __( 'Codeable', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Generate label only if address is codeable.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_address_codeable_only',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
				'desc_tip'          => __( 'Choose this option if you want to make sure that by default labels are only generated for codeable addresses.', 'woocommerce-germanized-dhl' ),
			),

			array(
				'title'             => __( 'Default weight', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Choose a default shipment weight to be used for labels if no weight has been applied to the shipment.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_label_default_shipment_weight',
				'default'           => '2',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
			),
		);

		if ( Package::base_country_supports( 'returns' ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title' 	        => __( 'Return label', 'woocommerce-germanized-dhl' ),
					'desc' 		        => __( 'Additionally create return labels for shipments that support returns.', 'woocommerce-germanized-dhl' ),
					'id' 		        => 'woocommerce_gzd_dhl_label_auto_direct_return_label',
					'default'	        => 'no',
					'type' 		        => 'gzd_toggle',
					'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_label_auto_enable' => '' )
				),
			) );
		}

		if ( $for_shipping_method ) {
			$settings = self::convert_for_shipping_method( $settings );
		}

		return $settings;
	}

	public static function get_parcel_pickup_type_settings( $for_shipping_method = false ) {
		$settings = array(
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
		);

		if ( $for_shipping_method ) {
			$settings = self::convert_for_shipping_method( $settings );
		}

		return $settings;
	}

	public static function get_label_default_services_settings( $for_shipping_method = false ) {

		$settings = array(
			array(
				'title' 	        => __( 'Visual minimum age', 'woocommerce-germanized-dhl' ),
				'id'          		=> 'woocommerce_gzd_dhl_label_visual_min_age',
				'type' 		        => 'select',
				'default'           => 'none',
				'options'			=> wc_gzd_dhl_get_visual_min_ages(),
				'desc_tip'          => __( 'Choose this option if you want to let DHL check your customer\'s age.', 'woocommerce-germanized-dhl' ),
			),
			array(
				'title' 	        => __( 'Additional Insurance', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Add an additional insurance to labels.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_AdditionalInsurance',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'Retail Outlet Routing', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Send undeliverable items to nearest retail outlet instead of immediate return.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_ParcelOutletRouting',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'No Neighbor', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Do not deliver to neighbors.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_NoNeighbourDelivery',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'Named person only', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Do only delivery to named person.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_NamedPersonOnly',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'Premium', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Premium delivery.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_Premium',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'Bulky Goods', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Deliver as bulky goods.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_service_BulkyGoods',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),
			array(
				'title' 	        => __( 'Age Verification', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Verify ages if shipment contains applicable items.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . sprintf( __( 'Germanized offers an %s to be enabled for certain products and/or product categories. By checking this option labels for shipments with applicable items will automatically have the age check service enabled.', 'woocommerce-germanized-dhl' ), '<a href="">' . __( 'age verification checkbox', 'woocommerce-germanized' ) . '</a>' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_label_auto_age_check_sync',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),
		);

		if ( $for_shipping_method ) {
			$settings = self::convert_for_shipping_method( $settings );
		}

		return $settings;
	}

	public static function get_automation_settings( $for_shipping_method = false ) {
		$shipment_statuses = array_diff_key( wc_gzd_get_shipment_statuses(), array_fill_keys( array( 'gzd-draft', 'gzd-delivered', 'gzd-returned' ), '' ) );

		$settings = array(
			array(
				'title' 	        => __( 'Automation', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Automatically create labels for shipments.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_label_auto_enable',
				'default'	        => 'no',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Status', 'woocommerce-germanized-dhl' ),
				'type'              => 'select',
				'default'           => 'gzd-processing',
				'id'                => 'woocommerce_gzd_dhl_label_auto_shipment_status',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'Choose a shipment status which should trigger generation of a label.', 'woocommerce-germanized-dhl' ) . '</div>',
				'options'           => $shipment_statuses,
				'class'             => 'wc-enhanced-select',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_label_auto_enable' => '' )
			),
		);

		if ( $for_shipping_method ) {
			$settings = self::convert_for_shipping_method( $settings );
		}

		return $settings;
	}

	protected static function convert_for_shipping_method( $settings ) {
		$new_settings = array();

		foreach( $settings as $setting ) {
			$new_setting            = array();
			$new_setting['id']      = str_replace( 'woocommerce_gzd_dhl_', 'dhl_', $setting['id'] );
			$new_setting['type']    = str_replace( 'gzd_toggle', 'checkbox', $setting['type'] );
			$new_setting['default'] = Package::get_setting( $new_setting['id'] );

			if ( 'checkbox' === $new_setting['type'] ) {
				$new_setting['label'] = $setting['desc'];
			} elseif ( isset( $setting['desc'] ) ) {
				$new_setting['description'] = $setting['desc'];
			}

			$copy = array( 'options', 'title', 'desc_tip' );

			foreach ( $copy as $cp ) {
				if ( isset( $setting[ $cp ] ) ) {
					$new_setting[ $cp ] = $setting[ $cp ];
				}
			}

			$new_settings[ $new_setting['id'] ] = $new_setting;
		}

		return $new_settings;
	}

	protected static function get_label_settings() {

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_label_options', 'desc' => sprintf( __( 'Adjust options for label creation. Settings may be overridden by more specific %s settings.', 'woocommerce-germanized-dhl' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '" target="_blank">' . __( 'shipping method', 'woocommerce-germanized-dhl' ) . '</a>' ) ),
		);

		$settings = array_merge( $settings, self::get_label_default_settings() );

		$settings = array_merge( $settings, array(
			array( 'type' => 'sectionend', 'id' => 'dhl_label_options' ),
		) );

		if ( Package::base_country_supports( 'services' ) ) {

			$settings = array_merge( $settings, array(
				array( 'title' => __( 'Default Services', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_label_default_services_options', 'desc' => sprintf( __( 'Adjust services to be added to your labels by default. Find out more about these %s.', 'woocommerce-germanized-dhl' ), '<a href="https://www.dhl.de/de/geschaeftskunden/paket/leistungen-und-services/paket.html" target="_blank">' . __( 'nationwide services', 'woocommerce-germanized-dhl' ) . '</a>' ) ),
			) );

			$settings = array_merge( $settings, self::get_label_default_services_settings() );

			$settings = array_merge( $settings, array(
				array( 'type' => 'sectionend', 'id' => 'dhl_label_default_services_options' ),
			) );

		}

		$settings = array_merge( $settings, array(
			array( 'title' => __( 'Automation', 'woocommerce-germanized-dhl' ), 'type' => 'title', 'id' => 'dhl_automation_options', 'desc' => __( 'Choose whether and under which conditions labels for your shipments shall be requested and generated automatically.', 'woocommerce-germanized-dhl' ) ),
		) );

		$settings = array_merge( $settings, self::get_automation_settings() );

		$settings = array_merge( $settings, array(

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
				'options'           => Package::get_available_countries(),
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
				'options'           => Package::get_available_countries(),
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
		) );

		return $settings;
	}

	public static function get_preferred_services_settings( $for_shipping_method = false ) {
		$settings = array(
			array(
				'title' 	        => __( 'Preferred day', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred day delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred day of delivery during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for preferred day delivery. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_cost',
				'default'           => '1.2',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'title' 	        => __( 'Preferred Time', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred time delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred time of delivery during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_PreferredTime_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title'             => __( 'Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for preferred time delivery. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_PreferredTime_cost',
				'default'           => '4.8',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredTime_enable' => '' )
			),

			array(
				'title'             => __( 'Combined Fee', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => __( 'Insert gross value as surcharge for the combination of preferred day and time. Insert 0 to offer service for free.', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => true,
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_combined_cost',
				'default'           => '4.8',
				'css'               => 'max-width: 60px;',
				'class'             => 'wc_input_decimal',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredTime_enable' => '', 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'title' 	        => __( 'Preferred Location', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred location delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to select their preferred delivery location during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_PreferredLocation_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),

			array(
				'title' 	        => __( 'Preferred Neighbor', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Enable preferred neighbor delivery.', 'woocommerce-germanized-dhl' ) . '<div class="wc-gzd-additional-desc">' . __( 'Enabling this option will display options for the user to deliver to their preferred neighbor during the checkout.', 'woocommerce-germanized-dhl' ) . '</div>',
				'id' 		        => 'woocommerce_gzd_dhl_PreferredNeighbour_enable',
				'default'	        => 'yes',
				'type' 		        => 'gzd_toggle',
			),
		);

		if ( $for_shipping_method ) {
			$settings = self::convert_for_shipping_method( $settings );
		}

		return $settings;
	}

	protected static function get_service_settings() {
		$wc_payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$wc_gateway_titles   = wp_list_pluck( $wc_payment_gateways, 'method_title', 'id' );
		$settings            = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_preferred_options' ),
		);

		$settings = array_merge( $settings, self::get_preferred_services_settings() );

		$settings = array_merge( $settings, array(

			array(
				'title'             => __( 'Cut-off time', 'woocommerce-germanized-dhl' ),
				'type'              => 'time',
				'id'                => 'woocommerce_gzd_dhl_PreferredDay_cutoff_time',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'The cut-off time is the latest possible order time up to which the minimum preferred day (day of order + 2 working days) can be guaranteed. As soon as the time is exceeded, the earliest preferred day displayed in the frontend will be shifted to one day later (day of order + 3 working days).', 'woocommerce-germanized-dhl' ) . '</div>',
				'default'           => '12:00',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'title'             => __( 'Preparation days', 'woocommerce-germanized-dhl' ),
				'type'              => 'number',
				'id'                => 'woocommerce_gzd_dhl_PreferredDay_preparation_days',
				'desc'              => '<div class="wc-gzd-additional-desc">' . __( 'If you need more time to prepare your shipments you might want to add a static preparation time to the possible starting date for preferred day delivery.', 'woocommerce-germanized-dhl' ) . '</div>',
				'default'           => '0',
				'css'               => 'max-width: 60px',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '', 'min' => 0, 'max' => 3 )
			),

			array(
				'title' 	        => __( 'Exclude days of transfer', 'woocommerce-germanized-dhl' ),
				'desc' 		        => __( 'Monday', 'woocommerce-germanized-dhl' ),
				'desc_tip'          => __( 'Exclude days from transferring shipments to DHL.', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_mon',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => 'start',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Tuesday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_tue',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Wednesday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_wed',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Thursday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_thu',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Friday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_fri',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => '',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
			),

			array(
				'desc' 		        => __( 'Saturday', 'woocommerce-germanized-dhl' ),
				'id' 		        => 'woocommerce_gzd_dhl_PreferredDay_exclusion_sat',
				'type' 		        => 'gzd_toggle',
				'default'	        => 'no',
				'checkboxgroup'	    => 'end',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_PreferredDay_enable' => '' )
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
		) );

		return $settings;
	}

	protected static function get_pickup_settings() {

		$settings = array(
			array( 'title' => '', 'type' => 'title', 'id' => 'dhl_pickup_options' ),
		);

		$settings = array_merge( $settings, self::get_parcel_pickup_type_settings() );

		$settings = array_merge( $settings, array(

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
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_map_api_key',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_parcel_pickup_map_enable' => '' ),
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( __( 'To integrate a map within your checkout you\'ll need a valid API key for Google Maps. You may %s.', 'woocommerce-germanized-dhl' ), '<a href="" target="_blank">' . __( 'retrieve a new one', 'woocommerce-germanized-dhl' ) . '</a>' ) . '</div>',
				'default'           => ''
			),

			array(
				'title'             => __( 'Limit results', 'woocommerce-germanized-dhl' ),
				'type'              => 'number',
				'id' 		        => 'woocommerce_gzd_dhl_parcel_pickup_map_max_results',
				'custom_attributes'	=> array( 'data-show_if_woocommerce_gzd_dhl_parcel_pickup_map_enable' => '' ),
				'desc_tip'          => __( 'Limit the number of pickup stores shown on the map', 'woocommerce-germanized-dhl' ),
				'default'           => 20,
				'css'               => 'max-width: 60px;',
			),

			array( 'type' => 'sectionend', 'id' => 'dhl_pickup_options' ),
		) );

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
		} elseif( 'pickup' === $current_section && Package::base_country_supports( 'pickup' ) ) {
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

		if ( ! Package::base_country_supports( 'pickup' ) ) {
			unset( $sections['pickup'] );
		}

		return $sections;
	}
}
