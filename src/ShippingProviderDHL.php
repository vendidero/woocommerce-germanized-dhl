<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL;

use Vendidero\Germanized\Shipments\ShippingProvider;

defined( 'ABSPATH' ) || exit;

class ShippingProviderDHL extends ShippingProvider {

	public function is_manual_integration() {
		return false;
	}

	public function supports_labels( $label_type ) {
		$label_types = array( 'simple' );

		if ( 'yes' === Package::get_setting( 'dhl_label_retoure_enable' ) ) {
			$label_types[] = 'return';
		}

		return in_array( $label_type, $label_types );
	}

	public function is_activated() {
		return Package::is_enabled();
	}

	public function get_title( $context = 'view' ) {
		return __( 'DHL', 'dhl', 'woocommerce-germanized-dhl' );
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

	public function get_tracking_url_placeholder( $context = 'view' ) {
		$data = parent::get_tracking_url_placeholder( $context );

		// In case the option value is not stored in DB yet
		if ( 'view' === $context && empty( $data ) ) {
			$data = 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?lang=de&idc={tracking_id}&rfn=&extendedSearch=true';
		}

		return $data;
	}

	public function get_tracking_desc_placeholder( $context = 'view' ) {
		$data = parent::get_tracking_desc_placeholder( $context );

		// In case the option value is not stored in DB yet
		if ( 'view' === $context && empty( $data ) ) {
			$data = _x( 'Your shipment is being processed by {shipping_provider}. If you want to track the shipment, please use the following tracking number: {tracking_id}. Depending on the chosen shipping method it is possible that the tracking data does not reflect the current status when receiving this email.', 'dhl', 'woocommerce-germanized-dhl' );
		}

		return $data;
	}

	public function get_supports_customer_returns( $context = 'view' ) {
		return Package::get_setting( 'label_retoure_enable' ) === 'yes' && Package::get_setting( 'label_supports_customer_returns' ) === 'yes';
	}

	public function get_return_manual_confirmation( $context = 'view' ) {
		return Package::get_setting( 'label_return_manual_confirmation' ) === 'yes';
	}

	public function get_return_instructions( $context = 'view' ) {
		return Package::get_setting( 'label_return_instructions' );
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
}