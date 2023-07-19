<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL\ShippingProvider;

use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\ShippingProvider\Auto;
use Vendidero\Germanized\Shipments\ShippingProvider\Product;
use Vendidero\Germanized\Shipments\ShippingProvider\ProductList;
use Vendidero\Germanized\Shipments\ShippingProvider\ServiceList;

defined( 'ABSPATH' ) || exit;

class DeutschePost extends Auto {

	protected function get_default_label_minimum_shipment_weight() {
		return 0.01;
	}

	protected function get_default_label_default_shipment_weight() {
		return 0.5;
	}

	public function supports_customer_return_requests() {
		return true;
	}

	public function get_help_link() {
		return 'https://vendidero.de/dokument/internetmarke-integration-einrichten';
	}

	public function get_signup_link() {
		return 'https://portokasse.deutschepost.de/portokasse/#!/register/';
	}

	public function get_label_classname( $type ) {
		if ( 'return' === $type ) {
			return '\Vendidero\Germanized\DHL\Label\DeutschePostReturn';
		} else {
			return '\Vendidero\Germanized\DHL\Label\DeutschePost';
		}
	}

	/**
	 * @param false|\WC_Order $order
	 *
	 * @return bool
	 */
	public function supports_customer_returns( $order = false ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		/**
		 * Return labels are only supported for DE
		 */
		if ( $order && 'DE' !== $order->get_shipping_country() ) {
			return false;
		}

		return parent::supports_customer_returns( $order );
	}

	public function supports_labels( $label_type, $shipment = false ) {
		$label_types = array( 'simple', 'return' );

		/**
		 * Return labels are only supported for DE
		 */
		if ( 'return' === $label_type && $shipment && 'return' === $shipment->get_type() && 'DE' !== $shipment->get_sender_country() ) {
			return false;
		}

		return in_array( $label_type, $label_types, true );
	}

	public function get_title( $context = 'view' ) {
		return _x( 'Deutsche Post', 'dhl', 'woocommerce-germanized-dhl' );
	}

	public function get_name( $context = 'view' ) {
		return 'deutsche_post';
	}

	public function get_description( $context = 'view' ) {
		return _x( 'Integration for products of the Deutsche Post through Internetmarke.', 'dhl', 'woocommerce-germanized-dhl' );
	}

	public function get_default_tracking_url_placeholder() {
		return 'https://www.deutschepost.de/sendung/simpleQueryResult.html?form.sendungsnummer={tracking_id}&form.einlieferungsdatum_tag={label_date_day}&form.einlieferungsdatum_monat={label_date_month}&form.einlieferungsdatum_jahr={label_date_year}';
	}

	public function get_api_username( $context = 'view' ) {
		return $this->get_meta( 'api_username', true, $context );
	}

	public function set_api_username( $username ) {
		$this->update_meta_data( 'api_username', strtolower( $username ) );
	}

	protected function get_available_base_countries() {
		return Package::get_available_countries();
	}

	protected function get_connection_status_html( $maybe_error ) {
		return '<span class="wc-gzd-shipment-api-connection-status ' . ( is_wp_error( $maybe_error ) ? 'connection-status-error' : 'connection-status-success' ) . '">' . ( sprintf( _x( 'Status: %1$s', 'dhl', 'woocommerce-germanized-dhl' ), ( is_wp_error( $maybe_error ) ? $maybe_error->get_error_message() : _x( 'Connected', 'dhl', 'woocommerce-germanized-dhl' ) ) ) ) . '</span>';
	}

	protected function get_general_settings( $for_shipping_method = false ) {
		$settings = array(
			array(
				'title' => '',
				'type'  => 'title',
				'id'    => 'deutsche_post_general_options',
			),

			array(
				'title'             => _x( 'Username', 'dhl', 'woocommerce-germanized-dhl' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Your credentials to the <a href="%s" target="_blank">Portokasse</a>. Please test your credentials before connecting.', 'dhl', 'woocommerce-germanized-dhl' ), 'https://portokasse.deutschepost.de/portokasse/#!/' ) . '</div>',
				'id'                => 'api_username',
				'default'           => '',
				'value'             => $this->get_setting( 'api_username', '' ),
				'custom_attributes' => array( 'autocomplete' => 'new-password' ),
			),

			array(
				'title'             => _x( 'Password', 'dhl', 'woocommerce-germanized-dhl' ),
				'type'              => 'password',
				'id'                => 'api_password',
				'default'           => '',
				'value'             => $this->get_setting( 'api_password', '' ),
				'custom_attributes' => array( 'autocomplete' => 'new-password' ),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'deutsche_post_general_options',
			),
		);

		if ( $im = Package::get_internetmarke_api() ) {
			$im->reload_products();

			$screen              = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$settings_url        = $this->get_edit_link( '' );
			$page_format_options = $im->get_page_format_list();

			if ( is_admin() && $screen && in_array( $screen->id, array( 'woocommerce_page_wc-settings' ), true ) ) {
				if ( $im->is_configured() && $im->auth() && $im->is_available() ) {
					if ( isset( $_GET['provider'] ) && 'deutsche_post' === $_GET['provider'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$balance = $im->get_balance( true );

						$settings = array_merge(
							$settings,
							array(
								array(
									'title' => _x( 'Portokasse', 'dhl', 'woocommerce-germanized-dhl' ),
									'type'  => 'title',
									'id'    => 'deutsche_post_portokasse_options',
									'desc'  => $this->get_connection_status_html( true ),
								),
								array(
									'title' => _x( 'Balance', 'dhl', 'woocommerce-germanized-dhl' ),
									'type'  => 'html',
									'html'  => wc_price( Package::cents_to_eur( $balance ), array( 'currency' => 'EUR' ) ),
								),

								array(
									'title' => _x( 'Charge (€)', 'dhl', 'woocommerce-germanized-dhl' ),
									'type'  => 'dp_charge',
								),

								array(
									'type' => 'sectionend',
									'id'   => 'deutsche_post_portokasse_options',
								),
							)
						);
					}
				} elseif ( $im && $im->has_errors() ) {
					$settings = array_merge(
						$settings,
						array(
							array(
								'title' => _x( 'Portokasse', 'dhl', 'woocommerce-germanized-dhl' ),
								'type'  => 'title',
								'id'    => 'deutsche_post_api_error',
								'desc'  => $this->get_connection_status_html( $im->get_errors() ),
							),
							array(
								'type' => 'sectionend',
								'id'   => 'deutsche_post_api_error',
							),
						)
					);
				}
			}

			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => _x( 'Printing', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'  => 'title',
						'id'    => 'deutsche_post_print_options',
					),

					array(
						'title'   => _x( 'Default Format', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'      => 'label_default_page_format',
						'class'   => 'wc-enhanced-select',
						'desc'    => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Choose a print format which will be selected by default when creating labels. Manually <a href="%s">refresh</a> available print formats to make sure the list is up-to-date.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'wc-gzd-dhl-im-page-formats-refresh' ), $settings_url ), 'wc-gzd-dhl-refresh-im-page-formats' ) ) ) . '</div>',
						'type'    => 'select',
						'value'   => $this->get_setting( 'label_default_page_format', 1 ),
						'options' => $page_format_options,
						'default' => 1,
					),
					array(
						'title'             => _x( 'Print X-axis column', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'                => 'label_position_x',
						'desc_tip'          => _x( 'Adjust the print X-axis start column for the label.', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'              => 'number',
						'value'             => $this->get_setting( 'label_position_x', 1 ),
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'css'               => 'max-width: 100px;',
						'default'           => 1,
					),
					array(
						'title'             => _x( 'Print Y-axis column', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'                => 'label_position_y',
						'desc_tip'          => _x( 'Adjust the print Y-axis start column for the label.', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'              => 'number',
						'value'             => $this->get_setting( 'label_position_y', 1 ),
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'css'               => 'max-width: 100px;',
						'default'           => 1,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'deutsche_post_print_options',
					),
				)
			);
		}

		$settings = array_merge(
			$settings,
			array(
				array(
					'title' => _x( 'Tracking', 'dhl', 'woocommerce-germanized-dhl' ),
					'type'  => 'title',
					'id'    => 'tracking_options',
				),
			)
		);

		$general_settings = parent::get_general_settings( $for_shipping_method );

		return array_merge( $settings, $general_settings );
	}

	private function is_save_settings_request() {
		$is_settings_save             = ( isset( $_POST['available_products'] ) && isset( $_GET['provider'] ) && 'deutsche_post' === wc_clean( wp_unslash( $_GET['provider'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$is_ajax_shipping_method_save = wp_doing_ajax() && isset( $_GET['action'] ) && 'woocommerce_shipping_zone_methods_save_settings' === wc_clean( wp_unslash( $_GET['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$is_packaging_save            = ( isset( $_POST['action'] ) && 'woocommerce_gzd_save_packaging_settings' === wc_clean( wp_unslash( $_POST['action'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

		return $is_ajax_shipping_method_save || $is_settings_save || $is_packaging_save;
	}

	protected function get_label_settings( $for_shipping_method = false ) {
		$im                  = Package::get_internetmarke_api();
		$settings            = parent::get_label_settings( $for_shipping_method );
		$settings_url        = $this->get_edit_link( 'label' );
		$screen              = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		$page_format_options = array();
		$product_options     = array(
			'available'         => array(),
			'default_available' => $im ? $im->get_default_available_products() : array(),
			'dom'               => array(),
			'eu'                => array(),
			'int'               => array(),
		);

		/**
		 * Do only allow calling IM API during admin setting (save) requests.
		 */
		if ( is_admin() && ( ( $screen && ( in_array( $screen->id, array( 'woocommerce_page_wc-settings', 'woocommerce_page_shipment-packaging' ), true ) ) ) || $this->is_save_settings_request() ) ) {
			if ( $im && $im->is_configured() && $im->auth() && $im->is_available() ) {
				$im->reload_products();

				$page_format_options = $im->get_page_format_list();

				$product_options = array(
					'available'         => $this->get_product_select_options(),
					'default_available' => $im ? $im->get_default_available_products() : array(),
					'dom'               => wc_gzd_dhl_get_deutsche_post_products_domestic( false, false ),
					'eu'                => wc_gzd_dhl_get_deutsche_post_products_eu( false, false ),
					'int'               => wc_gzd_dhl_get_deutsche_post_products_international( false, false ),
				);

				if ( isset( $_GET['provider'] ) && 'deutsche_post' === $_GET['provider'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$balance = $im->get_balance( true );

					$settings = array_merge(
						$settings,
						array(
							array(
								'title' => _x( 'Portokasse', 'dhl', 'woocommerce-germanized-dhl' ),
								'type'  => 'title',
								'id'    => 'deutsche_post_portokasse_options',
							),

							array(
								'title' => _x( 'Balance', 'dhl', 'woocommerce-germanized-dhl' ),
								'type'  => 'html',
								'html'  => wc_price( Package::cents_to_eur( $balance ), array( 'currency' => 'EUR' ) ),
							),

							array(
								'title' => _x( 'Charge (€)', 'dhl', 'woocommerce-germanized-dhl' ),
								'type'  => 'dp_charge',
							),

							array(
								'type' => 'sectionend',
								'id'   => 'deutsche_post_portokasse_options',
							),
						)
					);
				}
			} elseif ( $im && $im->has_errors() ) {
				$settings = array_merge(
					$settings,
					array(
						array(
							'title' => _x( 'API Error', 'dhl', 'woocommerce-germanized-dhl' ),
							'type'  => 'title',
							'id'    => 'deutsche_post_api_error',
							'desc'  => '<div class="notice inline notice-error"><p>' . implode( ', ', $im->get_errors()->get_error_messages() ) . '</p></div>',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'deutsche_post_api_error',
						),
					)
				);

				return $settings;
			}
		}

		if ( $im && $im->is_configured() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title'          => _x( 'Products', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'           => 'title',
						'id'             => 'deutsche_post_product_options',
						'allow_override' => true,
					),

					array(
						'title'          => _x( 'Available Products', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'             => 'available_products',
						'class'          => 'wc-enhanced-select',
						'desc'           => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Choose the products you want to be available for your shipments from the list above. Manually <a href="%s">refresh</a> the product list to make sure it is up-to-date.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'wc-gzd-dhl-im-product-refresh' ), $settings_url ), 'wc-gzd-dhl-refresh-im-products' ) ) ) . '</div>',
						'type'           => 'multiselect',
						'value'          => $this->get_setting( 'available_products', $product_options['default_available'] ),
						'options'        => $product_options['available'],
						'default'        => $product_options['default_available'],
						'allow_override' => false,
					),

					array(
						'title'   => _x( 'Domestic Default Service', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'    => 'select',
						'default' => '',
						'value'   => $this->get_setting( 'label_default_product_dom', '' ),
						'id'      => 'label_default_product_dom',
						'desc'    => '<div class="wc-gzd-additional-desc">' . _x( 'Please select your default shipping service for domestic shipments that you want to offer to your customers (you can always change this within each individual shipment afterwards).', 'dhl', 'woocommerce-germanized-dhl' ) . '</div>',
						'options' => $product_options['dom'],
						'class'   => 'wc-enhanced-select',
					),

					array(
						'title'   => _x( 'EU Default Service', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'    => 'select',
						'default' => '',
						'value'   => $this->get_setting( 'label_default_product_eu', '' ),
						'id'      => 'label_default_product_eu',
						'desc'    => '<div class="wc-gzd-additional-desc">' . _x( 'Please select your default shipping service for EU shipments that you want to offer to your customers.', 'dhl', 'woocommerce-germanized-dhl' ) . '</div>',
						'options' => $product_options['eu'],
						'class'   => 'wc-enhanced-select',
					),

					array(
						'title'   => _x( 'Int. Default Service', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'    => 'select',
						'default' => '',
						'value'   => $this->get_setting( 'label_default_product_int', '' ),
						'id'      => 'label_default_product_int',
						'desc'    => '<div class="wc-gzd-additional-desc">' . _x( 'Please select your default shipping service for cross-border shipments that you want to offer to your customers.', 'dhl', 'woocommerce-germanized-dhl' ) . '</div>',
						'options' => $product_options['int'],
						'class'   => 'wc-enhanced-select',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'deutsche_post_product_options',
					),
				)
			);

			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => _x( 'Printing', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'  => 'title',
						'id'    => 'deutsche_post_print_options',
					),

					array(
						'title'   => _x( 'Default Format', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'      => 'label_default_page_format',
						'class'   => 'wc-enhanced-select',
						'desc'    => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Choose a print format which will be selected by default when creating labels. Manually <a href="%s">refresh</a> available print formats to make sure the list is up-to-date.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'wc-gzd-dhl-im-page-formats-refresh' ), $settings_url ), 'wc-gzd-dhl-refresh-im-page-formats' ) ) ) . '</div>',
						'type'    => 'select',
						'value'   => $this->get_setting( 'label_default_page_format', 1 ),
						'options' => $page_format_options,
						'default' => 1,
					),
					array(
						'title'             => _x( 'Print X-axis column', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'                => 'label_position_x',
						'desc_tip'          => _x( 'Adjust the print X-axis start column for the label.', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'              => 'number',
						'value'             => $this->get_setting( 'label_position_x', 1 ),
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'css'               => 'max-width: 100px;',
						'default'           => 1,
					),
					array(
						'title'             => _x( 'Print Y-axis column', 'dhl', 'woocommerce-germanized-dhl' ),
						'id'                => 'label_position_y',
						'desc_tip'          => _x( 'Adjust the print Y-axis start column for the label.', 'dhl', 'woocommerce-germanized-dhl' ),
						'type'              => 'number',
						'value'             => $this->get_setting( 'label_position_y', 1 ),
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'css'               => 'max-width: 100px;',
						'default'           => 1,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'deutsche_post_print_options',
					),
				)
			);
		}

		return $settings;
	}

	protected function register_services() {
		foreach( Package::get_internetmarke_api()->get_product_list()->get_additional_services() as $service => $label ) {
			$this->register_service( $service, array(
				'label' => $label,
				'shipment_types' => array( 'return', 'simple' ),
				'excluded_locations' => wc_gzd_get_shipping_provider_service_locations(),
			) );
		}
	}

	protected function register_products() {
		global $wpdb;

		if ( ! get_transient( 'wc_gzd_dhl_im_products_expire' ) ) {
			$result = Package::get_internetmarke_api()->get_product_list()->update();

			if ( is_wp_error( $result ) ) {
				Package::log( 'Error while refreshing Internetmarke product data: ' . $result->get_error_message() );
			}

			/**
			 * Refresh product data once per day.
			 */
			set_transient( 'wc_gzd_dhl_im_products_expire', 'yes', DAY_IN_SECONDS );
		}

		$products = $wpdb->get_results( "SELECT * FROM {$wpdb->gzd_dhl_im_products}" );

		foreach( $products as $product ) {
			$this->register_product( $product->product_code, array(
				'id' => $product->product_code,
				'label' => wc_gzd_dhl_get_im_product_title( $product->product_name ),
				'description' => $product->product_description,
				'supported_shipment_types' => array( 'simple', 'return' ),
				'internal_id' => $product->product_id,
				'parent_id' => $product->product_parent_id,
				'supported_zones' => 'national' === $product->product_destination ? array( 'dom' ) : array( 'eu', 'int' ),
				'price' => $product->product_price,
				'length' => array( 'min' => $product->product_length_min, 'max' => $product->product_length_max ),
				'width' => array( 'min' => $product->product_width_min, 'max' => $product->product_width_max ),
				'height' => array( 'min' => $product->product_height_min, 'max' => $product->product_height_max ),
				'weight' => array( 'min' => $product->product_weight_min, 'max' => $product->product_weight_max ),
				'weight_unit' => 'g',
				'dimension_unit' => 'mm',
				'meta' => array(
					'is_wp_int' => 0 !== absint( $product->product_is_wp_int ) ? true : false,
					'information_text' => $product->product_information_text,
					'annotation' => $product->product_annotation,
					'destination' => $product->product_destination,
				),
			) );
		}
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_label_fields( $shipment ) {
		if ( ! Package::get_internetmarke_api()->is_available() ) {
			return Package::get_internetmarke_api()->get_errors();
		}

		return parent::get_label_fields( $shipment );
	}

	protected function get_portokasse_charge_button() {
		if ( ! Package::get_internetmarke_api()->get_user() ) {
			return '';
		}

		$balance      = Package::get_internetmarke_api()->get_balance();
		$user_token   = Package::get_internetmarke_api()->get_user()->getUserToken();
		$settings_url = $this->get_edit_link();

		$html = '
			<input type="text" placeholder="10.00" style="max-width: 150px; margin-right: 10px;" class="wc-input-price short" name="woocommerce_gzd_dhl_im_portokasse_charge_amount" id="woocommerce_gzd_dhl_im_portokasse_charge_amount" />
			<a id="woocommerce_gzd_dhl_im_portokasse_charge" class="button button-secondary" data-url="https://portokasse.deutschepost.de/portokasse/marketplace/enter-app-payment" data-success_url="' . esc_url( add_query_arg( array( 'wallet-charge-success' => 'yes' ), $settings_url ) ) . '" data-cancel_url="' . esc_url( add_query_arg( array( 'wallet-charge-success' => 'no' ), $settings_url ) ) . '" data-partner_id="' . esc_attr( Package::get_internetmarke_partner_id() ) . '" data-key_phase="' . esc_attr( Package::get_internetmarke_key_phase() ) . '" data-user_token="' . esc_attr( $user_token ) . '" data-schluessel_dpwn_partner="' . esc_attr( Package::get_internetmarke_token() ) . '" data-wallet="' . esc_attr( $balance ) . '">' . _x( 'Charge Portokasse', 'dhl', 'woocommerce-germanized-dhl' ) . '</a>
			<p class="description">' . sprintf( _x( 'The minimum amount is %s', 'dhl', 'woocommerce-germanized-dhl' ), wc_price( 10, array( 'currency' => 'EUR' ) ) ) . '</p>
		';

		return $html;
	}

	public function get_label_fields_html( $shipment ) {
		$html  = parent::get_label_fields_html( $shipment );
		$html .= '
			<div class="columns preview-columns wc-gzd-dhl-im-product-data">
		        <div class="column col-4">
		            <p class="wc-gzd-dhl-im-product-price wc-price data-placeholder hide-default" data-replace="price_formatted"></p>
		        </div>
		        <div class="column col-3 col-dimensions">
		            <p class="wc-gzd-dhl-im-product-dimensions data-placeholder hide-default" data-replace="dimensions_formatted"></p>
		        </div>
		        <div class="column col-5 col-preview">
		            <div class="image-preview"></div>
		        </div>
		        <div class="column col-12">
		            <p class="wc-gzd-dhl-im-product-description data-placeholder hide-default" data-replace="description_formatted"></p>
		            <p class="wc-gzd-dhl-im-product-information-text data-placeholder hide-default" data-replace="information_text_formatted"></p>
		        </div>
		    </div>
		';

		return $html;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_return_label_fields( $shipment ) {
		return $this->get_simple_label_fields( $shipment );
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return array|\WP_Error
	 */
	protected function get_simple_label_fields( $shipment ) {
		$props     = $this->get_default_label_props( $shipment );
		$products  = $this->get_products( array( 'shipment' => $shipment, 'parent_id' => 0 ) );
		$settings  = parent::get_simple_label_fields( $shipment );
		$is_wp_int = false;

		/**
		 * When retrieving the label fields make sure to only include parent products
		 */
		$settings[0]['options'] = $products->as_options();

		if ( ! empty( $props['product_id'] ) ) {
			$is_wp_int = Package::get_internetmarke_api()->is_warenpost_international( $props['product_id'] );
		}

		if ( $products->empty() ) {
			return new \WP_Error( 'dp-label-missing-products', sprintf( _x( 'Sorry but none of your selected <a href="%s">Deutsche Post Products</a> is available for this shipment. Please verify your shipment data (e.g. weight) and try again.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( $this->get_edit_link( 'label' ) ) ) );
		}

		$settings = array_merge( $settings, $this->get_available_additional_services( $props['product_id'], $props['services'] ) );

		if ( ! $is_wp_int ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'id'          => 'page_format',
						'label'       => _x( 'Page Format', 'dhl', 'woocommerce-germanized-dhl' ),
						'description' => '',
						'type'        => 'select',
						'options'     => Package::get_internetmarke_api()->get_page_format_list(),
						'value'       => isset( $default_args['page_format'] ) ? $default_args['page_format'] : '',
					),
					array(
						'id'   => '',
						'type' => 'columns',
					),
					array(
						'id'                => 'position_x',
						'label'             => _x( 'Print X-Position', 'dhl', 'woocommerce-germanized-dhl' ),
						'description'       => '',
						'type'              => 'number',
						'wrapper_class'     => 'column col-6',
						'style'             => 'width: 100%;',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'value'             => isset( $default_args['position_x'] ) ? $default_args['position_x'] : 1,
					),
					array(
						'id'                => 'position_y',
						'label'             => _x( 'Print Y-Position', 'dhl', 'woocommerce-germanized-dhl' ),
						'description'       => '',
						'type'              => 'number',
						'wrapper_class'     => 'column col-6',
						'style'             => 'width: 100%;',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'value'             => isset( $default_args['position_y'] ) ? $default_args['position_y'] : 1,
					),
				)
			);
		}

		return $settings;
	}

	public function get_available_additional_services( $product_id, $selected_services = array() ) {
		$im_product_id = $this->get_product( $product_id )->get_internal_id();
		$services      = \Vendidero\Germanized\DHL\Package::get_internetmarke_api()->get_product_list()->get_services_for_product( $im_product_id, $selected_services );
		$settings      = array(
			array(
				'id'   => 'additional-services',
				'type' => 'wrapper',
			),
		);

		foreach ( $services as $service ) {
			$settings[] = array(
				'id'            => 'service_' . $service,
				'wrapper_class' => 'form-field-checkbox',
				'type'          => 'checkbox',
				'label'         => \Vendidero\Germanized\DHL\Package::get_internetmarke_api()->get_product_list()->get_additional_service_title( $service ),
				'value'         => in_array( $service, $selected_services, true ) ? 'yes' : 'no',
			);
		}

		$settings[] = array(
			'type' => 'wrapper_end',
		);

		return $settings;
	}

	protected function get_default_label_props( $shipment ) {
		$dp_defaults = $this->get_default_simple_label_props( $shipment );
		$defaults    = parent::get_default_label_props( $shipment );
		$defaults    = array_replace_recursive( $defaults, $dp_defaults );

		if ( ! empty( $defaults['product_id'] ) ) {
			if ( $product = $this->get_product( $defaults['product_id'] ) ) {
				$defaults['stamp_total'] = Package::get_internetmarke_api()->get_product_total( $defaults['product_id'] );

				if ( $product->get_parent_id() > 0 ) {
					$defaults['services']   = Package::get_internetmarke_api()->get_product_services( $product->get_id() );
					$defaults['product_id'] = Package::get_internetmarke_api()->get_product_parent_code( $product->get_id() );
				} else {
					/**
					 * Get current services from the selected product.
					 */
					$defaults['services'] = Package::get_internetmarke_api()->get_product_services( $defaults['product_id'] );
				}
			}
		}

		return $defaults;
	}

	protected function get_default_simple_label_props( $shipment ) {
		$defaults = array(
			'page_format' => $this->get_shipment_setting( $shipment, 'label_default_page_format' ),
			'position_x'  => $this->get_shipment_setting( $shipment, 'label_position_x' ),
			'position_y'  => $this->get_shipment_setting( $shipment, 'label_position_y' ),
			'stamp_total' => 0,
			'services'    => array(),
		);

		return $defaults;
	}

	/**
	 * @param Shipment $shipment
	 * @param $props
	 *
	 * @return \WP_Error|mixed
	 */
	protected function validate_label_request( $shipment, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'page_format' => '',
				'product_id'  => '',
				'services'    => array(),
			)
		);

		$error = new \WP_Error();

		if ( ! empty( $args['services'] ) ) {
			/**
			 * Additional services are requested. Let's check whether the actual product exists and
			 * refresh the product code (to the child product code).
			 */
			$im_product_code = Package::get_internetmarke_api()->get_product_code( $args['product_id'], $args['services'] );

			if ( false === $im_product_code ) {
				$error->add( 500, _x( 'The services chosen are not available for the current product.', 'dhl', 'woocommerce-germanized-dhl' ) );
			} else {
				$args['product_id'] = $im_product_code;
			}
		}

		$available_products = $this->get_products( array( 'shipment' => $shipment ) );

		/**
		 * Check whether the product might not be available for the current shipment
		 */
		if ( ! $available_products->get( $args['product_id'] ) ) {
			/**
			 * In case no other products are available or this is a manual request - return error
			 */
			if ( empty( $available_products ) || ( is_admin() && current_user_can( 'manage_woocommerce' ) ) ) {
				$error->add( 500, sprintf( _x( 'Sorry but none of your selected <a href="%s">Deutsche Post Products</a> is available for this shipment. Please verify your shipment data (e.g. weight) and try again.', 'dhl', 'woocommerce-germanized-dhl' ), esc_url( $this->get_edit_link( 'label' ) ) ) );
			} else {
				/**
				 * In case the chosen product is not available - use the first product available instead
				 * to prevent errors during automation (connected with the default product option which might not fit).
				 */
				$im_product_code = Package::get_internetmarke_api()->get_product_parent_code( $available_products->get_by_index( 0 )->get_id() );

				if ( ! empty( $args['services'] ) ) {
					$im_product_code_additional = Package::get_internetmarke_api()->get_product_code( $im_product_code, $args['services'] );

					if ( false !== $im_product_code_additional ) {
						$im_product_code = $im_product_code_additional;
					}
				}

				$args['product_id'] = $im_product_code;
			}
		}

		/**
		 * Refresh stamp total based on actual product.
		 */
		if ( ! empty( $args['product_id'] ) ) {
			$args['stamp_total'] = Package::get_internetmarke_api()->get_product_total( $args['product_id'] );
		} else {
			$error->add( 500, sprintf( _x( 'Deutsche Post product is missing for %s.', 'dhl', 'woocommerce-germanized-dhl' ), $shipment->get_id() ) );
		}

		if ( wc_gzd_dhl_wp_error_has_errors( $error ) ) {
			return $error;
		}

		return $args;
	}
}
