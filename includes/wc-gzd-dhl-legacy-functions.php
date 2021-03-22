<?php
/**
 * WooCommerce Germanized DHL Shipment Functions
 *
 * Functions for shipment specific things.
 *
 * @package WooCommerce_Germanized/DHL/Functions
 * @version 3.4.0
 */

use Vendidero\Germanized\DHL\Legacy\LabelQuery;

/**
 * Standard way of retrieving shipments based on certain parameters.
 *
 * @since  2.6.0
 * @param  array $args Array of args (above).
 * @return \Vendidero\Germanized\DHL\Label\Label[]
 */
function wc_gzd_dhl_get_labels( $args ) {
	$query = new LabelQuery( $args );

	return $query->get_labels();
}

function wc_gzd_dhl_get_return_label_by_shipment( $the_shipment ) {
	return wc_gzd_dhl_get_shipment_label( $the_shipment, 'return' );
}

/**
 * Main function for returning label.
 *
 * @param  mixed $the_label Object or label id.
 *
 * @return bool|\Vendidero\Germanized\DHL\Label\Label
 *
 */
function wc_gzd_dhl_get_label( $the_label = false ) {
	return wc_gzd_get_shipment_label( $the_label );
}

function wc_gzd_dhl_get_shipment_label( $the_shipment, $type = '' ) {
	$shipment_id = \Vendidero\Germanized\Shipments\ShipmentFactory::get_shipment_id( $the_shipment );

	if ( $shipment_id ) {

		$args = array(
			'shipment_id' => $shipment_id,
		);

		if ( ! empty( $type ) ) {
			$args['type'] = $type;
		}

		$labels = wc_gzd_dhl_get_labels( $args );

		if ( ! empty( $labels ) ) {
			return $labels[0];
		}
	}

	return false;
}

add_filter( 'woocommerce_gzd_shipping_provider_dhl_get_label', '_wc_gzd_dhl_legacy_shipment_label_dhl', 10, 3 );
add_filter( 'woocommerce_gzd_shipping_provider_deutsche_post_get_label', '_wc_gzd_dhl_legacy_shipment_label_deutsche_post', 10, 3 );

/**
 * @param $label
 * @param \Vendidero\Germanized\Shipments\Shipment $the_shipment
 * @param \Vendidero\Germanized\Shipments\Interfaces\ShippingProvider $provider
 *
 * @return false|\Vendidero\Germanized\DHL\Label\Label
 */
function _wc_gzd_dhl_legacy_shipment_label_dhl( $label, $the_shipment, $provider ) {
	if ( ! $label && '' === $the_shipment->get_version() ) {
		$label_type = $the_shipment->get_type();

		return wc_gzd_dhl_get_shipment_label( $the_shipment, $label_type );
	}

	return $label;
}

function _wc_gzd_dhl_legacy_shipment_label_deutsche_post( $label, $the_shipment, $provider ) {
	if ( ! $label && '' === $the_shipment->get_version() ) {
		$label_type = $the_shipment->get_type();
		$label_type = 'return' === $label_type ? 'deutsche_post_return' : 'deutsche_post';

		return wc_gzd_dhl_get_shipment_label( $the_shipment, $label_type );
	}

	return $label;
}

add_filter( 'woocommerce_gzd_shipment_label', '_wc_gzd_dhl_legacy_label', 10, 4 );

function _wc_gzd_dhl_legacy_label( $label, $the_label, $shipping_provider, $type ) {
	if ( ! $label ) {
		$label_id = \Vendidero\Germanized\Shipments\Labels\Factory::get_label_id( $the_label );

		if ( $label_id ) {
			$type = WC_Data_Store::load( 'dhl-legacy-label' )->get_label_type( $label_id );

			if ( $type ) {
				$mappings = array(
					'simple'               => '\Vendidero\Germanized\DHL\Label\DHL',
					'return'               => '\Vendidero\Germanized\DHL\Label\DHLReturn',
					'deutsche_post'        => '\Vendidero\Germanized\DHL\Label\DeutschePost',
					'deutsche_post_return' => '\Vendidero\Germanized\DHL\Label\DeutschePostReturn',
				);

				$classname = isset( $mappings[ $type ] ) ? $mappings[ $type ] : '\Vendidero\Germanized\DHL\Label\DHL';

				try {
					$label = new $classname( $label_id, true );
				} catch( Exception $e ) {
					wc_caught_exception( $e, __FUNCTION__, func_get_args() );
					$label = false;
				}
			}
		}
	}

	return $label;
}