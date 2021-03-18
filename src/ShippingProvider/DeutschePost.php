<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL\ShippingProvider;

use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\Shipments\ShippingProvider\Auto;

defined( 'ABSPATH' ) || exit;

class DeutschePost extends Auto {

	public function get_label_classname( $type ) {
		if ( 'return' === $type ) {
			return '\Vendidero\Germanized\DHL\Label\DeutschePostReturn';
		} else {
			return '\Vendidero\Germanized\DHL\Label\DeutschePost';
		}
	}

	public function is_manual_integration() {
		return false;
	}

	public function supports_labels( $label_type ) {
		$label_types = array( 'simple', 'return' );

		return in_array( $label_type, $label_types );
	}

	public function is_activated() {
		return Package::is_internetmarke_enabled();
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

	public function get_additional_options_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=germanized-dhl&section=internetmarke' );
	}

	public function get_default_tracking_url_placeholder() {
		return 'https://www.deutschepost.de/sendung/simpleQueryResult.html?form.sendungsnummer={tracking_id}&form.einlieferungsdatum_tag={label_date_day}&form.einlieferungsdatum_monat={label_date_month}&form.einlieferungsdatum_jahr={label_date_year}';
	}

	public function deactivate() {
		update_option( 'woocommerce_gzd_dhl_internetmarke_enable', 'no' );

		/**
		 * This action is documented in woocommerce-germanized-shipments/src/ShippingProvider.php
		 */
		do_action( 'woocommerce_gzd_shipping_provider_activated', $this );
	}

	public function activate() {
		update_option( 'woocommerce_gzd_dhl_internetmarke_enable', 'yes' );

		/**
		 * This action is documented in woocommerce-germanized-shipments/src/ShippingProvider.php
		 */
		do_action( 'woocommerce_gzd_shipping_provider_deactivated', $this );
	}

	public function get_default_label_product( $shipment ) {
		// TODO: Implement get_default_label_product() method.
	}

	public function get_available_label_products( $shipment ) {
		// TODO: Implement get_available_label_products() method.
	}
}