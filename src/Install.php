<?php

namespace Vendidero\Germanized\DHL;

use Vendidero\Germanized\Shipments\ShippingProvider\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Install {

    public static function install() {
	    $current_version       = get_option( 'woocommerce_gzd_dhl_version', null );
	    $needs_settings_update = false;

		self::create_db();

	    /**
	     * Older versions did not support custom versioning
	     */
	    if ( is_null( $current_version ) ) {
		    add_option( 'woocommerce_gzd_dhl_version', Package::get_version() );

		    // Legacy settings -> indicate update necessary
		    $needs_settings_update = get_option( 'woocommerce_gzd_dhl_enable' ) || get_option( 'woocommerce_gzd_deutsche_post_enable' );
	    }

	    if ( $needs_settings_update ) {
	    	self::migrate_settings();
	    }
    }

    private static function migrate_settings() {
    	// Make sure that providers are registered
	    add_filter( 'woocommerce_gzd_shipping_provider_class_names', array( '\Vendidero\Germanized\DHL\Package', 'add_shipping_provider_class_name' ), 10, 1 );

	    global $wpdb;

	    $plugin_options   = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'woocommerce_gzd_dhl_%'" );
		$dhl              = wc_gzd_get_shipping_provider( 'dhl' );
	    $deutsche_post    = wc_gzd_get_shipping_provider( 'deutsche_post' );
	    $excluded_options = array( 'woocommerce_gzd_dhl_upload_dir_suffix', 'woocommerce_gzd_dhl_enable', 'woocommerce_gzd_dhl_enable_internetmarke', 'woocommerce_gzd_dhl_internetmarke_enable' );

	    /**
	     * Error while retrieving shipping provider instance
	     */
	    if ( ! is_a( $dhl, '\Vendidero\Germanized\DHL\ShippingProvider\DHL' ) || ! is_a( $deutsche_post, '\Vendidero\Germanized\DHL\ShippingProvider\DeutschePost' ) ) {
	    	return false;
	    }

	    foreach( $plugin_options as $option ) {
	    	$option_name  = $option->option_name;

	    	if ( in_array( $option_name, $excluded_options ) ) {
	    		continue;
		    }

			$option_value = get_option( $option->option_name );
			$is_dp        = strpos( $option_name, '_im_' ) !== false || strpos( $option_name, '_internetmarke_' ) !== false || strpos( $option_name, '_deutsche_post_' ) !== false;

			if ( $option_value ) {
				if ( ! $is_dp ) {
					$option_name_clean = str_replace( 'woocommerce_gzd_dhl_', '', $option_name );

					if ( 'label_default_shipment_weight' === $option_name_clean ) {
						$dhl->set_label_default_shipment_weight( $option_value );
					} elseif ( 'label_minimum_shipment_weight' === $option_name_clean ) {
						$dhl->set_label_minimum_shipment_weight( $option_value );
					} elseif ( strpos( $option_name_clean, '_shipper_' ) !== false || strpos( $option_name_clean, '_return_address_' ) !== false ) {
						continue;
					} else {
						$dhl->update_meta_data( $option_name_clean, $option_value );
					}
				} else {
					$option_name_clean = str_replace( 'woocommerce_gzd_dhl_', '', $option_name );
					$option_name_clean = str_replace( 'deutsche_post_', '', $option_name_clean );
					$option_name_clean = str_replace( 'im_', '', $option_name_clean );

					$deutsche_post->update_meta_data( $option_name_clean, $option_value );
				}
			}
	    }

	    $deutsche_post->set_label_default_shipment_weight( get_option( 'woocommerce_gzd_dhl_label_default_shipment_weight' ) );
	    $deutsche_post->set_label_minimum_shipment_weight( get_option( 'woocommerce_gzd_dhl_label_minimum_shipment_weight' ) );

	    // Update address data
	    $shipper_address = array(
		    'company'       => get_option( 'woocommerce_gzd_dhl_shipper_company' ),
		    'name'          => get_option( 'woocommerce_gzd_dhl_shipper_name' ),
		    'street'        => get_option( 'woocommerce_gzd_dhl_shipper_street' ),
		    'street_number' => get_option( 'woocommerce_gzd_dhl_shipper_street_no' ),
		    'postcode'      => get_option( 'woocommerce_gzd_dhl_shipper_postcode' ),
		    'country'       => get_option( 'woocommerce_gzd_dhl_shipper_country' ),
		    'city'          => get_option( 'woocommerce_gzd_dhl_shipper_city' ),
		    'phone'         => get_option( 'woocommerce_gzd_dhl_shipper_phone' ),
		    'email'         => get_option( 'woocommerce_gzd_dhl_shipper_email' ),
	    );

	    $shipper_address = array_filter( $shipper_address );

	    if ( ! empty( $shipper_address ) ) {
		    $dhl->set_shipper_address( $shipper_address );
		    $deutsche_post->set_shipper_address( $shipper_address );
	    }

	    $return_address = array(
		    'company'       => get_option( 'woocommerce_gzd_dhl_return_address_company' ),
		    'name'          => get_option( 'woocommerce_gzd_dhl_return_address_name' ),
		    'street'        => get_option( 'woocommerce_gzd_dhl_return_address_street' ),
		    'street_number' => get_option( 'woocommerce_gzd_dhl_return_address_street_no' ),
		    'postcode'      => get_option( 'woocommerce_gzd_dhl_return_address_postcode' ),
		    'country'       => get_option( 'woocommerce_gzd_dhl_return_address_country' ),
		    'city'          => get_option( 'woocommerce_gzd_dhl_return_address_city' ),
		    'phone'         => get_option( 'woocommerce_gzd_dhl_return_address_phone' ),
		    'email'         => get_option( 'woocommerce_gzd_dhl_return_address_email' ),
	    );

	    $return_address = array_filter( $return_address );

	    if ( ! empty( $return_address ) ) {
		    $dhl->set_return_address( $return_address );
	    }

	    $dhl->save();
	    $deutsche_post->save();
    }

    private static function create_db() {
	    global $wpdb;
	    $wpdb->hide_errors();
	    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	    dbDelta( self::get_schema() );
    }

    private static function create_upload_dir() {
    	Package::maybe_set_upload_dir();

	    $dir = Package::get_upload_dir();

	    if ( ! @is_dir( $dir['basedir'] ) ) {
	    	@mkdir( $dir['basedir'] );
	    }

	    if ( ! file_exists( trailingslashit( $dir['basedir'] ) . '.htaccess' ) ) {
		    @file_put_contents( trailingslashit( $dir['basedir'] ) . '.htaccess', 'deny from all' );
	    }

	    if ( ! file_exists( trailingslashit( $dir['basedir'] ) . 'index.php' ) ) {
		    @touch( trailingslashit( $dir['basedir'] ) . 'index.php' );
	    }
    }

    private static function get_schema() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $tables = "
CREATE TABLE {$wpdb->prefix}woocommerce_gzd_dhl_labels (
  label_id BIGINT UNSIGNED NOT NULL auto_increment,
  label_date_created datetime NOT NULL default '0000-00-00 00:00:00',
  label_date_created_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  label_shipment_id BIGINT UNSIGNED NOT NULL,
  label_parent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  label_number varchar(200) NOT NULL DEFAULT '',
  label_dhl_product varchar(200) NOT NULL DEFAULT '',
  label_path varchar(200) NOT NULL DEFAULT '',
  label_default_path varchar(200) NOT NULL DEFAULT '',
  label_export_path varchar(200) NOT NULL DEFAULT '',
  label_type varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (label_id),
  KEY label_shipment_id (label_shipment_id),
  KEY label_parent_id (label_parent_id)
) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_gzd_dhl_im_products (
  product_id BIGINT UNSIGNED NOT NULL auto_increment,
  product_im_id BIGINT UNSIGNED NOT NULL,
  product_code INT(16) NOT NULL,
  product_name varchar(150) NOT NULL DEFAULT '',
  product_slug varchar(150) NOT NULL DEFAULT '',
  product_version INT(5) NOT NULL DEFAULT 1,
  product_annotation varchar(500) NOT NULL DEFAULT '',
  product_description varchar(500) NOT NULL DEFAULT '',
  product_information_text TEXT NOT NULL DEFAULT '',
  product_type varchar(50) NOT NULL DEFAULT 'sales',
  product_destination varchar(20) NOT NULL DEFAULT 'national',
  product_price INT(8) NOT NULL,
  product_length_min INT(8) NULL,
  product_length_max INT(8) NULL,
  product_length_unit VARCHAR(8) NULL,
  product_width_min INT(8) NULL,
  product_width_max INT(8) NULL,
  product_width_unit VARCHAR(8) NULL,
  product_height_min INT(8) NULL,
  product_height_max INT(8) NULL,
  product_height_unit VARCHAR(8) NULL,
  product_weight_min INT(8) NULL,
  product_weight_max INT(8) NULL,
  product_weight_unit VARCHAR(8) NULL,
  product_parent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  product_service_count INT(3) NOT NULL DEFAULT 0,
  product_is_wp_int INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (product_id),
  KEY product_im_id (product_im_id),
  KEY product_code (product_code)
) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_gzd_dhl_im_product_services (
  product_service_id BIGINT UNSIGNED NOT NULL auto_increment,
  product_service_product_id BIGINT UNSIGNED NOT NULL,
  product_service_product_parent_id BIGINT UNSIGNED NOT NULL,
  product_service_slug VARCHAR(20) NOT NULL DEFAULT '',
  PRIMARY KEY  (product_service_id),
  KEY product_service_product_id (product_service_product_id),
  KEY product_service_product_parent_id (product_service_product_parent_id)
) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_gzd_dhl_labelmeta (
  meta_id BIGINT UNSIGNED NOT NULL auto_increment,
  gzd_dhl_label_id BIGINT UNSIGNED NOT NULL,
  meta_key varchar(255) default NULL,
  meta_value longtext NULL,
  PRIMARY KEY  (meta_id),
  KEY gzd_dhl_label_id (gzd_dhl_label_id),
  KEY meta_key (meta_key(32))
) $collate;";

        return $tables;
    }
}