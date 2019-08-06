<?php

namespace Vendidero\Germanized\DHL;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {

    /**
     * Version.
     *
     * @var string
     */
    const VERSION = '0.0.1-dev';

    /**
     * Init the package - load the REST API Server class.
     */
    public static function init() {
        self::init_hooks();
    }

    public static function init_hooks() {
        add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        add_action( 'woocommerce_shipping_init', array( __CLASS__, 'includes' ) );
        add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'add_shipping_method' ) );
    }

    public static function includes() {

    }

    public static function add_shipping_method( $methods ) {
        return $methods;
    }

    public static function load_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'woocommerce-germanized-dhl' );

        unload_textdomain( 'woocommerce-germanized-dhl' );
        load_textdomain( 'woocommerce-germanized-dhl', WP_LANG_DIR . '/woocommerce-germanized-dhl/woocommerce-germanized-dhl-' . $locale . '.mo' );
        load_plugin_textdomain( 'woocommerce-germanized-dhl', false, self::get_path() . '/i18n/languages' );
    }

    /**
     * Return the version of the package.
     *
     * @return string
     */
    public static function get_version() {
        return self::VERSION;
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_path() {
        return dirname( __DIR__ );
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_url() {
        return plugins_url( '', __DIR__ );
    }

    public static function is_debug_mode() {
        return defined( 'WC_GZD_DHL_DEBUG' ) && WC_GZD_DHL_DEBUG;
    }

    private static function define_constant( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    public static function get_app_id() {
        return 'dhl_woocommerce_plugin_2_1';
    }

    public static function get_app_token() {
        return 'Iw4zil3jFJTOXHA6AuWP4ykGkXKLee';
    }

    public static function get_cig_user() {
        return self::is_debug_mode() ? 'shadmin' : self::get_app_id();
    }

    public static function get_cig_password() {
        return self::is_debug_mode() ? 'm6jvtj{U)zH;\']' : self::get_app_token();
    }

    public static function get_cig_url() {
        return self::is_debug_mode() ? 'https://cig.dhl.de/services/sandbox/soap' : 'https://cig.dhl.de/services/production/soap';
    }

    public static function get_rest_url() {
        return self::is_debug_mode() ? 'https://cig.dhl.de/services/sandbox/soap' : 'https://cig.dhl.de/services/production/soap';
    }

    public static function get_business_portal_url() {
        return 'https://www.dhl-geschaeftskundenportal.de';
    }

    public static function log( $message, $type = 'info' ) {
        $logger = wc_get_logger();

        if ( ! $logger ) {
            return false;
        }

        if ( ! is_callable( array( $logger, $type ) ) ) {
            $type = 'info';
        }

        $logger->{$type}( $message, array( 'source' => 'woocommerce-germanized-dhl' ) );
    }
}