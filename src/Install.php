<?php

namespace Vendidero\Germanized\DHL;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Install {

    public static function install() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( self::get_schema() );
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
  label_number varchar(200) NOT NULL DEFAULT '',
  label_dhl_product varchar(200) NOT NULL DEFAULT '',
  label_path varchar(200) NOT NULL DEFAULT '',
  label_export_path varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (label_id),
  KEY label_shipment_id (label_shipment_id)
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