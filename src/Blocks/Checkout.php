<?php
namespace Vendidero\Germanized\DHL\Blocks;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\DHL\ParcelServices;

final class Checkout {

	public function __construct() {
		$this->register_endpoint_data();
		$this->register_integrations();

		add_filter( 'woocommerce_gzd_dhl_checkout_parcel_services_data', function( $data ) {
			if ( WC()->session ) {
				$customer = WC()->customer;

				$data['shipping_country'] = $customer->get_shipping_country();

				if ( WC()->session->get( 'dhl_preferred_day' ) ) {
					$data['dhl_preferred_day'] = WC()->session->get( 'dhl_preferred_day' );
				}

				if ( WC()->session->get( 'dhl_preferred_delivery_type' ) ) {
					$data['dhl_preferred_delivery_type'] = WC()->session->get( 'dhl_preferred_delivery_type' );
				}
			}

			return $data;
		} );
	}

	private function register_integrations() {
		add_action( 'woocommerce_blocks_checkout_block_registration', function( $integration_registry ) {
			$integration_registry->register( Package::container()->get( \Vendidero\Germanized\DHL\Blocks\Integrations\Checkout::class ) );
		} );
	}

	private function register_endpoint_data() {
		woocommerce_store_api_register_endpoint_data( array(
			'endpoint'        => CartSchema::IDENTIFIER,
			'namespace'       => 'woocommerce-germanized-dhl',
			'data_callback'   => function() {
				return $this->get_cart_data();
			},
			'schema_callback' => function () {
				return $this->get_cart_schema();
			},
		) );

		woocommerce_store_api_register_endpoint_data( array(
			'endpoint'        => CheckoutSchema::IDENTIFIER,
			'namespace'       => 'woocommerce-germanized-dhl',
			'schema_callback' => function () {
				return $this->get_checkout_schema();
			},
		) );

		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'woocommerce-germanized-dhl-checkout-fees',
				'callback'  => function( $data ) {
					$dhl = wp_parse_args( wc_clean( wp_unslash( $data ) ), array(
						'preferred_day'           => '',
						'preferred_delivery_type' => '',
					) );

					WC()->session->set( "dhl_preferred_day", $dhl['preferred_day'] );
					WC()->session->set( "dhl_preferred_delivery_type", $dhl['preferred_delivery_type'] );
				},
			)
		);
	}

	private function get_cart_schema() {
		return [
			'preferred_day_enabled' => [
				'description' => __( 'Preferred day enabled', 'woocommerce-germanized' ),
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_day_cost' => [
				'description' => __( 'Preferred day costs', 'woocommerce-germanized' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_day' => [
				'description' => __( 'Preferred day', 'woocommerce-germanized' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_home_delivery_cost' => [
				'description' => __( 'Preferred delivery costs', 'woocommerce-germanized' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_delivery_type_enabled' => [
				'description' => __( 'Preferred delivery type enabled', 'woocommerce-germanized' ),
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_delivery_type' => [
				'description' => __( 'Preferred delivery type', 'woocommerce-germanized' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_location_enabled' => [
				'description' => __( 'Preferred location enabled', 'woocommerce-germanized' ),
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_neighbor_enabled' => [
				'description' => __( 'Preferred neighbor enabled', 'woocommerce-germanized' ),
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'preferred_days' => [
				'description' => __( 'Preferred neighbor enabled', 'woocommerce-germanized' ),
				'type'        => 'array',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
				'items'       => [
					'type'       => 'object',
					'properties' => array(
						'day'     => [
							'description' => __( 'The preferred day.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
						],
						'week_day'     => [
							'description' => __( 'The formatted week day.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
						],
						'date'     => [
							'description' => __( 'The preferred day.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
						],
					)
				]
			],
		];
	}

	private function get_cart_data() {
		$customer       = wc()->customer;
		$preferred_days = array();

		if ( ParcelServices::is_preferred_day_enabled() && 'DE' === $customer->get_shipping_country() ) {
			$api_preferred_days = \Vendidero\Germanized\DHL\Package::get_api()->get_preferred_available_days( WC()->customer->get_shipping_postcode() );

			foreach( $api_preferred_days as $key => $preferred_day ) {
				$key          = empty( $key ) ? '' : $key;
				$week_day_num = empty( $key ) ? '-' : esc_html( date( 'j', strtotime( $key ) ) );

				$preferred_days[] = array(
					'day'      => $week_day_num,
					'week_day' => $preferred_day,
					'date'     => $key,
				);
			}
		}

		$money_formatter = \Automattic\WooCommerce\Blocks\Package::container()->get( \Automattic\WooCommerce\StoreApi\StoreApi::class )->container()->get( ExtendSchema::class )->get_formatter( 'money' );

		return array(
			'preferred_day_enabled'        => ParcelServices::is_preferred_day_enabled(),
			'preferred_day'                => WC()->session->get( 'dhl_preferred_day' ) ? WC()->session->get( 'dhl_preferred_day' ) : '',
			'preferred_location_enabled'   => ParcelServices::is_preferred_location_enabled(),
			'preferred_neighbor_enabled'   => ParcelServices::is_preferred_neighbor_enabled(),
			'preferred_delivery_type_enabled' => ParcelServices::is_preferred_delivery_type_enabled(),
			'preferred_delivery_type'      => WC()->session->get( 'dhl_preferred_delivery_type' ) ? WC()->session->get( 'dhl_preferred_delivery_type' ) : ParcelServices::get_default_preferred_delivery_type(),
			'preferred_days'               => $preferred_days,
			'preferred_day_cost'           => $money_formatter->format( ParcelServices::get_preferred_day_cost() ),
			'preferred_home_delivery_cost' => $money_formatter->format( ParcelServices::get_preferred_home_delivery_cost() )
		);
	}

	private function get_checkout_schema() {
		return [
			'preferred_day' => [
				'description' => __( 'Preferred day', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
			'preferred_location_type' => [
				'description' => __( 'Preferred location type', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
			'preferred_location' => [
				'description' => __( 'Preferred location', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
			'preferred_neighbor_name' => [
				'description' => __( 'Preferred neighbor name', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
			'preferred_neighbor_address' => [
				'description' => __( 'Preferred neighbor name', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
			'preferred_delivery_type' => [
				'description' => __( 'Preferred delivery type', 'woocommerce-germanized' ),
				'type'        => array( 'string', 'null' ),
				'context'     => [ 'view', 'edit' ],
				'default'     => '',
			],
		];
	}

	private function has_checkout_data( $param, $request ) {
		return isset( $request[ $param ] ) && null !== $request[ $param ];
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	private function get_checkout_data_from_request( $request ) {
		$data = array_filter( (array) wc_clean( $request['extensions']['woocommerce-germanized-dhl'] ) );

		$data = wp_parse_args( $data, array(
			'preferred_day' => '',
			'preferred_location_type' => '',
			'preferred_location' => '',
			'preferred_neighbor_name' => '',
			'preferred_neighbor_address' => '',
			'preferred_delivery_type' => ''
		) );

		return $data;
	}
}