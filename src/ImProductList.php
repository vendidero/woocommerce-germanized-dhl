<?php

namespace Vendidero\Germanized\DHL;

use Vendidero\Germanized\DHL\Api\ImProductsSoap;

defined( 'ABSPATH' ) || exit;

/**
 * DHL Shipment class.
 */
class ImProductList {

	protected $products = null;

	protected $available_products = null;

	public function __construct() {}

	public function get_products( $filters = array() ) {
		if ( is_null( $this->products ) ) {
			$this->load_products();
		}

		$products = $this->products;

		return wp_list_filter( $products, $filters );
	}

	protected function load_products() {
		global $wpdb;

		$products = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->gzd_dhl_im_products}" ) );

		$this->products = $products;
	}

	protected function load_available_products() {
		global $wpdb;

		$available_products = Package::get_setting( 'im_available_products' );

		if ( ! empty( $available_products ) ) {
			$available_products = array_filter( array_map( 'absint', $available_products ) );
		} else {
			$available_products = array();
		}

		$available_products = array_map( function( $p ) {
			return "'" . esc_sql( $p ) . "'";
		}, $available_products );

		$products = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->gzd_dhl_im_products} WHERE im_product_in IN ($available_products)" ) );

		$this->available_products = $products;
	}

	public function get_available_products() {
		if ( is_null( $this->available_products ) ) {
			$this->load_available_products();
		}

		return $this->available_products;
	}

	private function get_information_text( $stamp_type ) {
		$information_text = '';

		foreach ( $stamp_type as $stamp ) {
			if  ($stamp->name == 'Internetmarke' ) {
				foreach ( $stamp->propertyList as $properties ) {
					foreach ( $properties as $property ) {
						if ( $property->name == 'InformationText' ) {
							$information_text = $property->propertyValue->alphanumericValue->fixValue;
						}
					}
				}
			}
		}

		return $information_text;
	}

	protected function get_dimensions( $dimensions, $type = 'width' ) {
		$data = array(
			"product_{$type}_min"  => null,
			"product_{$type}_max"  => null,
			"product_{$type}_unit" => null,
		);

		if ( property_exists( $dimensions, $type ) ) {
			$d = $dimensions->{ $type };

			$data["product_{$type}_min"]  = property_exists( $d, 'minValue' ) ? $d->minValue : null;
			$data["product_{$type}_max"]  = property_exists( $d, 'maxValue' ) ? $d->maxValue : null;
			$data["product_{$type}_unit"] = property_exists( $d, 'unit' ) ? $d->unit : null;
		}

		return $data;
	}

	public function update() {
		global $wpdb;

		$product_soap = new ImProductsSoap();
		$result       = new \WP_Error();

		try {
			$product_list = $product_soap->getProducts();

			$wpdb->query( "TRUNCATE TABLE {$wpdb->gzd_dhl_im_products}" );

			$products = array(
				'sales'      => $product_list->Response->salesProductList->SalesProduct,
				'additional' => $product_list->Response->additionalProductList->AdditionalProduct,
				'basic'      => $product_list->Response->basicProductList->BasicProduct
			);

			foreach( $products as $product_type => $inner_products ) {
				foreach( $inner_products as $product ) {

					$extended_identifier = $product->extendedIdentifier;
					$extern_identifier   = property_exists( $extended_identifier, 'externIdentifier' ) ? $extended_identifier->externIdentifier[0] : new \stdClass();

					$to_insert = array(
						'product_im_id'            => $extended_identifier->{'ProdWS-ID'},
						'product_code'             => property_exists( $extern_identifier, 'id' ) ? $extern_identifier->id : '',
						'product_name'             => property_exists( $extern_identifier, 'name' ) ? $extern_identifier->name : $extended_identifier->name,
						'product_type'             => $product_type,
						'product_annotation'       => property_exists( $extended_identifier, 'annotation' ) ? $extended_identifier->annotation : '',
						'product_description'      => property_exists( $extended_identifier, 'description' ) ? $extended_identifier->description : '',
						'product_destination'      => $extended_identifier->destination,
						'product_price'            => property_exists( $product->priceDefinition, 'price' ) ? Package::eur_to_cents( $product->priceDefinition->price->calculatedGrossPrice->value ) : Package::eur_to_cents( $product->priceDefinition->grossPrice->value ),
						'product_information_text' => property_exists( $product, 'stampTypeList' ) ? $this->get_information_text( (array) $product->stampTypeList->stampType ) : '',
					);

					if ( property_exists( $product, 'dimensionList' ) ) {
						$dimensions = $product->dimensionList;

						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'width' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'height' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'length' ) );
					}

					if ( property_exists( $product, 'weight' ) ) {
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $product, 'weight' ) );
					}

					$to_insert = array_map( 'wc_clean', $to_insert );

					$wpdb->insert( $wpdb->gzd_dhl_im_products, $to_insert );
				}
			}

		} catch( \Exception $e ) {
			$result->add( 'soap', $e->getMessage() );
		}

		return wc_gzd_dhl_wp_error_has_errors( $result ) ? $result : true;
	}
}