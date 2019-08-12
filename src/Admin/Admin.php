<?php

namespace Vendidero\Germanized\DHL\Admin;
use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin class.
 */
class Admin {

	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		add_action( 'admin_init', array( __CLASS__, 'download_label' ) );

		add_action( 'woocommerce_gzd_shipments_meta_box_shipment_after_right_column', array( 'Vendidero\Germanized\DHL\Admin\MetaBox', 'output' ), 10, 1 );
	}

	public static function download_label() {
		if ( isset( $_GET['action'] ) && 'wc-gzd-dhl-download-label' === $_GET['action'] ) {
			if ( isset( $_GET['label_id'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'dhl-download-label' ) ) {

				$label_id = absint( $_GET['label_id'] );
				$args     = wp_parse_args( $_GET, array(
					'force'  => 'no',
					'export' => 'no',
				) );

				DownloadHandler::download_label( $label_id, wc_string_to_bool( $args['force'] ) );
			}
		}
	}

	public static function admin_styles() {
		global $wp_scripts;

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register admin styles.
		wp_register_style( 'woocommerce_gzd_dhl_admin', Package::get_assets_url() . '/css/admin' . $suffix . '.css', array( 'woocommerce_admin_styles' ), Package::get_version() );

		// Admin styles for WC pages only.
		if ( in_array( $screen_id, self::get_screen_ids() ) ) {
			wp_enqueue_style( 'woocommerce_gzd_dhl_admin' );
		}
	}

	public static function admin_scripts() {
		global $post;

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-gzd-admin-dhl', Package::get_assets_url() . '/js/admin-dhl' . $suffix . '.js', array( 'wc-gzd-admin-shipments' ), Package::get_version() );

		// Orders.
		if ( in_array( str_replace( 'edit-', '', $screen_id ), wc_get_order_types( 'order-meta-boxes' ) ) ) {
			wp_enqueue_script( 'wc-gzd-admin-dhl' );

			wp_localize_script(
				'wc-gzd-admin-dhl',
				'wc_gzd_admin_dhl_params',
				array(
					'ajax_url'                 => admin_url( 'admin-ajax.php' ),
					'create_label_nonce'       => wp_create_nonce( 'create-dhl-label' ),
					'remove_label_nonce'       => wp_create_nonce( 'remove-dhl-label' ),
					'edit_label_nonce'         => wp_create_nonce( 'edit-dhl-label' ),
					'i18n_remove_label_notice' => __( 'Do you really want to delete the label?', 'woocommerce-germanized-dhl' ),
				)
			);
		}
	}

	public static function get_screen_ids() {
		$screen_ids = array(
			'woocommerce_page_wc-gzd-shipments'
		);

		foreach ( wc_get_order_types() as $type ) {
			$screen_ids[] = $type;
			$screen_ids[] = 'edit-' . $type;
		}

		return $screen_ids;
	}
}
