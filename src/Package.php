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

    protected static $upload_dir_suffix = '';

    /**
     * Init the package - load the REST API Server class.
     */
    public static function init() {
        self::define_tables();
        self::maybe_set_upload_dir();
        self::init_hooks();
        self::includes();
    }

    /**
     * Register custom tables within $wpdb object.
     */
    private static function define_tables() {
        global $wpdb;

        // List of tables without prefixes.
        $tables = array(
            'gzd_dhl_labelmeta'     => 'woocommerce_gzd_dhl_labelmeta',
            'gzd_dhl_labels'        => 'woocommerce_gzd_dhl_labels',
        );

        foreach ( $tables as $name => $table ) {
            $wpdb->$name    = $wpdb->prefix . $table;
            $wpdb->tables[] = $table;
        }
    }

    private static function maybe_set_upload_dir() {
        // Create a dir suffix
        if ( ! get_option( 'woocommerce_gzd_dhl_upload_dir_suffix', false ) ) {
            self::$upload_dir_suffix = substr( self::generate_key(), 0, 10 );
            update_option( 'woocommerce_gzd_dhl_upload_dir_suffix', self::$upload_dir_suffix );
        } else {
            self::$upload_dir_suffix = get_option( 'woocommerce_gzd_dhl_upload_dir_suffix' );
        }
    }

    private static function includes() {
        include_once self::get_path() . '/includes/wc-gzd-dhl-core-functions.php';
    }

    public static function init_hooks() {
        add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        // add_action( 'init', array( '\Vendidero\Germanized\DHL\Install', 'install' ), 15 );
        // add_action( 'init', array( __CLASS__, 'test' ), 120 );

        add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ), 10, 1 );
        add_action( 'woocommerce_shipping_init', array( __CLASS__, 'shipping_includes' ) );
        add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'add_shipping_method' ) );
    }

    public static function register_data_stores( $stores ) {
        $stores['dhl-label'] = 'Vendidero\Germanized\DHL\DataStores\Label';

        return $stores;
    }

    public static function test() {

    }

    public static function shipping_includes() {

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

    /**
     * Generate a unique key.
     *
     * @return string
     */
    protected static function generate_key() {
        $key       = array( ABSPATH, time() );
        $constants = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT', 'SECRET_KEY' );

        foreach ( $constants as $constant ) {
            if ( defined( $constant ) ) {
                $key[] = constant( $constant );
            }
        }

        shuffle( $key );

        return md5( serialize( $key ) );
    }

    public static function get_upload_dir_suffix() {
        return self::$upload_dir_suffix;
    }

    public static function get_upload_dir() {

        self::set_upload_dir_filter();
        $upload_dir = wp_upload_dir();
        self::unset_upload_dir_filter();

        return apply_filters( 'woocommerce_gzd_dhl_upload_dir', $upload_dir );
    }

    public static function get_relative_upload_dir( $path ) {

        self::set_upload_dir_filter();
        $path = _wp_relative_upload_path( $path );
        self::unset_upload_dir_filter();

        return apply_filters( 'woocommerce_gzd_dhl_relative_upload_dir', $path );
    }

    public static function set_upload_dir_filter() {
        add_filter( 'upload_dir', array( __CLASS__, "filter_upload_dir" ), 150, 1 );
    }

    public static function unset_upload_dir_filter() {
        remove_filter( 'upload_dir', array( __CLASS__, "filter_upload_dir" ), 150 );
    }

    public static function create_upload_folder() {
        $dir = self::get_upload_dir();

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

    public static function filter_upload_dir( $args ) {
        $upload_base = trailingslashit( $args['basedir'] );
        $upload_url  = trailingslashit( $args['baseurl'] );

        $args['basedir'] = apply_filters( 'woocommerce_gzd_dhl_upload_path', $upload_base . 'wc-gzd-dhl-' . self::get_upload_dir_suffix() );
        $args['baseurl'] = apply_filters( 'woocommerce_gzd_dhl_upload_url', $upload_url . 'wc-gzd-dhl-' . self::get_upload_dir_suffix() );

        $args['path'] = $args['basedir'] . $args['subdir'];
        $args['url']  = $args['baseurl'] . $args['subdir'];

        return $args;
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