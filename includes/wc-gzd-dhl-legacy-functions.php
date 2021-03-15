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

add_filter( 'woocommerce_gzd_shipment_label', '_wc_gzd_dhl_legacy_label', 10, 4 );

function _wc_gzd_dhl_legacy_label( $label, $the_label, $shipping_provider, $type ) {
	if ( ! $label ) {
		$label_id = \Vendidero\Germanized\Shipments\LabelFactory::get_label_id( $the_label );

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