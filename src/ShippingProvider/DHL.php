<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL\ShippingProvider;

use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\ShippingProvider;

defined( 'ABSPATH' ) || exit;

class DHL extends ShippingProvider {

	public function is_manual_integration() {
		return false;
	}

	public function get_label_classname( $type ) {
		if ( 'return' === $type ) {
			return '\Vendidero\Germanized\DHL\Label\DHLReturn';
		} else {
			return '\Vendidero\Germanized\DHL\Label\DHL';
		}
	}

	public function supports_labels( $label_type ) {
		$label_types = array( 'simple' );

		if ( 'yes' === Package::get_setting( 'dhl_label_retoure_enable' ) ) {
			$label_types[] = 'return';
		}

		return in_array( $label_type, $label_types );
	}

	public function supports_customer_return_requests() {
		return ( 'yes' === Package::get_setting( 'dhl_label_retoure_enable' ) ? true : false );
	}

	public function is_activated() {
		return Package::is_dhl_enabled();
	}

	public function get_title( $context = 'view' ) {
		return _x( 'DHL', 'dhl', 'woocommerce-germanized-dhl' );
	}

	public function get_name( $context = 'view' ) {
		return 'dhl';
	}

	public function get_description( $context = 'view' ) {
		return _x( 'Complete DHL integration supporting labels, preferred services and packstation delivery.', 'dhl', 'woocommerce-germanized-dhl' );
	}

	public function get_additional_options_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=germanized-dhl' );
	}

	public function get_default_tracking_url_placeholder() {
		return 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?lang=de&idc={tracking_id}&rfn=&extendedSearch=true';
	}

	public function get_tracking_url_placeholder( $context = 'view' ) {
		$data = parent::get_tracking_url_placeholder( $context );

		// In case the option value is not stored in DB yet
		if ( 'view' === $context && empty( $data ) ) {
			$data = $this->get_default_tracking_url_placeholder();
		}

		return $data;
	}

	public function get_tracking_desc_placeholder( $context = 'view' ) {
		$data = parent::get_tracking_desc_placeholder( $context );

		// In case the option value is not stored in DB yet
		if ( 'view' === $context && empty( $data ) ) {
			$data = $this->get_default_tracking_desc_placeholder();
		}

		return $data;
	}

	public function deactivate() {
		update_option( 'woocommerce_gzd_dhl_enable', 'no' );

		/**
		 * This action is documented in woocommerce-germanized-shipments/src/ShippingProvider.php
		 */
		do_action( 'woocommerce_gzd_shipping_provider_activated', $this );
	}

	public function activate() {
		update_option( 'woocommerce_gzd_dhl_enable', 'yes' );

		/**
		 * This action is documented in woocommerce-germanized-shipments/src/ShippingProvider.php
		 */
		do_action( 'woocommerce_gzd_shipping_provider_deactivated', $this );
	}

	public function get_setting_sections() {
		$sections = parent::get_setting_sections();

		return $sections;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return array
	 */
	public function get_label_settings( $shipment ) {
		$settings     = parent::get_label_settings( $shipment );
		$dhl_order    = wc_gzd_dhl_get_order( $shipment->get_order() );
		$default_args = wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment );

		if ( $dhl_order->has_cod_payment() ) {
			$settings = array_merge( $settings, array( array(
				'id'          => 'cod_total',
				'class'       => 'wc_input_decimal',
				'label'       => _x( 'COD Amount', 'dhl', 'woocommerce-germanized-dhl' ),
				'placeholder' => '',
				'description' => '',
				'value'       => isset( $default_args['cod_total'] ) ? $default_args['cod_total'] : '',
				'type'        => 'text'
			) ) );
		}

		if ( Package::is_crossborder_shipment( $shipment->get_country() ) ) {
			$settings = array_merge( $settings, array( array(
				'id'          => 'duties',
				'label'       => _x( 'Duties', 'dhl', 'woocommerce-germanized-dhl' ),
				'description' => '',
				'value'       => isset( $default_args['duties'] ) ? $default_args['duties'] : '',
				'options'     => wc_gzd_dhl_get_duties(),
				'type'        => 'select'
			) ) );
		}

		$services = array(
			array(
				'id'          		=> 'service_GoGreen',
				'label'       		=> _x( 'GoGreen', 'dhl', 'woocommerce-germanized-dhl' ),
				'description'		=> '',
				'type'              => 'checkbox',
				'value'       		=> in_array( 'GoGreen', $default_args['services'] ) ? 'yes' : 'no',
				'wrapper_class'     => 'form-field-checkbox',
				'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'GoGreen' )
			),
			array(
				'id'          		=> 'service_AdditionalInsurance',
				'label'       		=> _x( 'Additional insurance', 'dhl', 'woocommerce-germanized-dhl' ),
				'description'       => '',
				'type'              => 'checkbox',
				'value'		        => in_array( 'AdditionalInsurance', $default_args['services'] ) ? 'yes' : 'no',
				'wrapper_class'     => 'form-field-checkbox',
				'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'AdditionalInsurance' )
			)
		);

		if ( Package::base_country_supports( 'services' ) && Package::is_shipping_domestic( $shipment->get_country() ) ) {
			$preferred_days = array();

			try {
				$preferred_day_options = Package::get_api()->get_preferred_available_days( $shipment->get_postcode() );

				if ( $preferred_day_options ) {
					$preferred_days  = $preferred_day_options;
				}
			} catch( \Exception $e ) {}

			$settings = array_merge( $settings, array( array(
				'id'          => 'preferred_day',
				'label'       => _x( 'Preferred Day', 'dhl', 'woocommerce-germanized-dhl' ),
				'description' => '',
				'value'       => isset( $default_args['preferred_day'] ) ? $default_args['preferred_day'] : '',
				'options'     => wc_gzd_dhl_get_preferred_days_select_options( $preferred_days, ( isset( $default_args['preferred_day'] ) ? $default_args['preferred_day'] : '' ) ),
				'type'        => 'select'
			) ) );

			if ( $dhl_order->has_preferred_location() ) {
				$settings = array_merge( $settings, array( array(
					'id'          		=> 'preferred_location',
					'label'       		=> _x( 'Preferred Location', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'       		=> isset( $default_args['preferred_location'] ) ? $default_args['preferred_location'] : '',
					'custom_attributes'	=> array( 'maxlength' => '80' ),
					'type'              => 'text'
				) ) );
			}

			if ( $dhl_order->has_preferred_neighbor() ) {
				$settings = array_merge( $settings, array( array(
					'id'          		=> 'preferred_neighbor',
					'label'       		=> _x( 'Preferred Neighbor', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'       		=> isset( $default_args['preferred_neighbor'] ) ? $default_args['preferred_neighbor'] : '',
					'custom_attributes'	=> array( 'maxlength' => '80' ),
					'type'              => 'text'
				) ) );
			}

			$settings = array_merge( $settings, array(
				array(
					'id'          	=> 'codeable_address_only',
					'label'       	=> _x( 'Valid address only', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'checkbox',
					'value'       	=> isset( $default_args['codeable_address_only'] ) ? wc_bool_to_string( $default_args['codeable_address_only'] ) : 'no',
					'wrapper_class' => 'form-field-checkbox'
				),
				array(
					'id'          		=> 'has_inlay_return',
					'label'       		=> _x( 'Create inlay return label', 'dhl', 'woocommerce-germanized-dhl' ),
					'class'             => 'checkbox show-if-trigger',
					'custom_attributes' => array( 'data-show-if' => '.show-if-has-return' ),
					'desc_tip'          => true,
					'value'             => isset( $default_args['has_inlay_return'] ) ? wc_bool_to_string( $default_args['has_inlay_return'] ) : 'no',
					'wrapper_class'     => 'form-field-checkbox',
					'type'              => 'checkbox'
				),
				array(
					'id'            => 'return_address[name]',
					'label'         => _x( 'Name', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder'   => '',
					'description'   => '',
					'value'         => isset( $default_args['return_address']['name'] ) ? $default_args['return_address']['name'] : '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return',
				),
				array(
					'id'          	=> 'return_address[company]',
					'label'       	=> _x( 'Company', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'   => '',
					'wrapper_class' => 'show-if-has-return',
					'type'          => 'text',
					'value'         => isset( $default_args['return_address']['company'] ) ? $default_args['return_address']['company'] : '',
				),
				array(
					'id'          	=> '',
					'type'          => 'columns',
				),
				array(
					'id'          	=> 'return_address[street]',
					'label'       	=> _x( 'Street', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-9',
					'value'         => isset( $default_args['return_address']['street'] ) ? $default_args['return_address']['street'] : '',
				),
				array(
					'id'          	=> 'return_address[street_number]',
					'label'       	=> _x( 'Street No', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'   => '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-3',
					'value'         => isset( $default_args['return_address']['street_number'] ) ? $default_args['return_address']['street_number'] : '',
				),
				array(
					'id'          	=> '',
					'type'          => 'columns',
				),
				array(
					'id'          	=> 'return_address[postcode]',
					'label'       	=> _x( 'Postcode', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-6',
					'value'         => isset( $default_args['return_address']['postcode'] ) ? $default_args['return_address']['postcode'] : '',
				),
				array(
					'id'          	=> 'return_address[city]',
					'label'       	=> _x( 'City', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-6',
					'value'         => isset( $default_args['return_address']['city'] ) ? $default_args['return_address']['city'] : '',
				),
				array(
					'id'          	=> '',
					'type'          => 'columns',
				),
				array(
					'id'          	=> 'return_address[phone]',
					'label'       	=> _x( 'Phone', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-6',
					'value'         => isset( $default_args['return_address']['phone'] ) ? $default_args['return_address']['phone'] : '',
				),
				array(
					'id'          	=> 'return_address[email]',
					'label'       	=> _x( 'Email', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 	=> '',
					'description'	=> '',
					'type'          => 'text',
					'wrapper_class' => 'show-if-has-return column col-6',
					'value'         => isset( $default_args['return_address']['email'] ) ? $default_args['return_address']['email'] : '',
				),
				array(
					'id'          	=> '',
					'type'          => 'columns_end',
				),
			) );

			$services = array_merge( $services, array(
				array(
					'id'          		=> 'visual_min_age',
					'label'       		=> _x( 'Age check', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'type'              => 'select',
					'value'       		=> isset( $default_args['visual_min_age'] ) ? $default_args['visual_min_age'] : '',
					'options'			=> wc_gzd_dhl_get_visual_min_ages(),
					'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'VisualCheckOfAge' )
				),
			) );

			if ( $dhl_order->supports_email_notification() ) {
				$services = array_merge( $services, array(
					array(
						'id'          		=> 'service_ParcelOutletRouting',
						'label'       		=> _x( 'Retail outlet routing', 'dhl', 'woocommerce-germanized-dhl' ),
						'description'       => '',
						'type'              => 'checkbox',
						'value'		        => in_array( 'ParcelOutletRouting', $default_args['services'] ) ? 'yes' : 'no',
						'wrapper_class'     => 'form-field-checkbox',
						'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'ParcelOutletRouting' )
					)
				) );
			}

			if ( ! $dhl_order->has_preferred_neighbor() ) {
				$services = array_merge( $services, array(
					array(
						'id'          		=> 'service_NoNeighbourDelivery',
						'label'       		=> _x( 'No neighbor', 'dhl', 'woocommerce-germanized-dhl' ),
						'description'       => '',
						'type'              => 'checkbox',
						'value'		        => in_array( 'NoNeighbourDelivery', $default_args['services'] ) ? 'yes' : 'no',
						'wrapper_class'     => 'form-field-checkbox',
						'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'NoNeighbourDelivery' )
					)
				) );
			}

			$services = array_merge( $services, array(
				array(
					'id'          		=> 'service_NamedPersonOnly',
					'label'       		=> _x( 'Named person only', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'type'              => 'checkbox',
					'value'		        => in_array( 'NamedPersonOnly', $default_args['services'] ) ? 'yes' : 'no',
					'wrapper_class'     => 'form-field-checkbox',
					'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'NamedPersonOnly' )
				),
				array(
					'id'          		=> 'service_BulkyGoods',
					'label'       		=> _x( 'Bulky goods', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'type'              => 'checkbox',
					'value'		        => in_array( 'BulkyGoods', $default_args['services'] ) ? 'yes' : 'no',
					'wrapper_class'     => 'form-field-checkbox',
					'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'BulkyGoods' )
				),
				array(
					'id'          		=> 'service_IdentCheck',
					'label'       		=> _x( 'Identity check', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'type'              => 'checkbox',
					'class'             => 'checkbox show-if-trigger',
					'value'		        => in_array( 'IdentCheck', $default_args['services'] ) ? 'yes' : 'no',
					'custom_attributes' => array_merge( array( 'data-show-if' => '.show-if-ident-check' ), wc_gzd_dhl_get_service_product_attributes( 'IdentCheck' ) ),
					'wrapper_class'     => 'form-field-checkbox',
				),
				array(
					'id'          	=> '',
					'type'          => 'columns',
				),
				array(
					'id'          		=> 'ident_date_of_birth',
					'label'       		=> _x( 'Date of Birth', 'dhl', 'woocommerce-germanized-dhl' ),
					'placeholder' 		=> '',
					'description'		=> '',
					'value'       		=> isset( $default_args['ident_date_of_birth'] ) ? $default_args['ident_date_of_birth'] : '',
					'custom_attributes' => array( 'pattern' => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])', 'maxlength' => 10 ),
					'class'				=> 'short date-picker',
					'wrapper_class'     => 'show-if-ident-check column col-6',
					'type'              => 'text',
				),
				array(
					'id'          		=> 'ident_min_age',
					'label'       		=> _x( 'Minimum age', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'wrapper_class'     => 'show-if-ident-check column col-6',
					'type'              => 'select',
					'value'       		=> isset( $default_args['ident_min_age'] ) ? $default_args['ident_min_age'] : '',
					'options'			=> wc_gzd_dhl_get_ident_min_ages(),
				),
				array(
					'id'          	=> '',
					'type'          => 'columns_end',
				),
			) );
		} elseif( Package::is_crossborder_shipment( $shipment->get_country() ) ) {
			$services = array_merge( $services, array(
				array(
					'id'          		=> 'service_Premium',
					'label'       		=> _x( 'Premium', 'dhl', 'woocommerce-germanized-dhl' ),
					'description'		=> '',
					'value'		        => in_array( 'Premium', $default_args['services'] ) ? 'yes' : 'no',
					'wrapper_class'     => 'form-field-checkbox',
					'type'              => 'checkbox',
					'custom_attributes' => wc_gzd_dhl_get_service_product_attributes( 'Premium' )
				)
			) );
		}

		$settings[] = array(
			'type' => 'services_start',
			'id'   => '',
		);

		$settings = array_merge( $settings, $services );

		return $settings;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_available_label_products( $shipment ) {
		return wc_gzd_dhl_get_products( $shipment->get_country() );
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_default_label_product( $shipment ) {
		$dhl_order    = wc_gzd_dhl_get_order( $shipment->get_order() );
		$default_args = wc_gzd_dhl_get_label_default_args( $dhl_order, $shipment );

		return isset( $default_args['product_id'] ) ? $default_args['product_id'] : false;
	}
}