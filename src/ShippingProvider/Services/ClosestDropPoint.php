<?php

namespace Vendidero\Germanized\DHL\ShippingProvider\Services;

use Vendidero\Germanized\DHL\ParcelServices;
use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class ClosestDropPoint extends Service {

	public function __construct( $shipping_provider, $args = array() ) {
		$args = array(
			'id'          => 'ClosestDropPoint',
			'label'       => _x( 'Closest Droppoint (CDP)', 'dhl', 'woocommerce-germanized-dhl' ),
			'description' => _x( 'Ship to a parcel shop or parcel locker in the vicinity of your customer’s home address.', 'dhl', 'woocommerce-germanized-dhl' ),
			'products'    => array( 'V53WPAK' ),
			'countries'   => ParcelServices::get_cdp_countries(),
			'zones'       => array( 'eu' ),
		);

		parent::__construct( $shipping_provider, $args );
	}

	public function book_as_default( $shipment ) {
		$book_as_default = parent::book_as_default( $shipment );

		if ( false === $book_as_default ) {
			$dhl_order = wc_gzd_dhl_get_order( $shipment->get_order() );

			if ( $dhl_order && $dhl_order->has_cdp_delivery() ) {
				$book_as_default = true;
			}
		}

		return $book_as_default;
	}
}
