<?php

namespace Vendidero\Germanized\DHL;

use Exception;
use Vendidero\Germanized\DHL\Api\Paket;

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

	// These are all considered domestic by DHL
	protected static $us_territories = array( 'US', 'GU', 'AS', 'PR', 'UM', 'VI' );

	protected static $holidays = array();

	protected static $api = null;

    /**
     * Init the package - load the REST API Server class.
     */
    public static function init() {
        self::define_tables();
        self::maybe_set_upload_dir();
        self::init_hooks();
        self::includes();
    }

	public static function get_holidays( $country = 'DE' ) {
		if ( empty( self::$holidays ) ) {
			self::$holidays = include self::get_path() . '/i18n/holidays.php';
		}

		$holidays = self::$holidays;

		if ( ! empty( $country ) ) {
			$holidays = array_key_exists( $country, self::$holidays ) ? self::$holidays[ $country ] : array();
		}

		return apply_filters( 'woocommerce_gzd_dhl_holidays', $holidays, $country );
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

	    if ( is_admin() ) {
		    Admin\Admin::init();
	    }

	    if ( ParcelLocator::is_enabled() ) {
		    ParcelLocator::init();
	    }

	    Ajax::init();
	    LabelWatcher::init();
    }

    public static function init_hooks() {
        add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        // add_action( 'init', array( '\Vendidero\Germanized\DHL\Install', 'install' ), 15 );
	    // add_action( 'init', array( __CLASS__, 'test' ), 120 );

        add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ), 10, 1 );
        add_action( 'woocommerce_shipping_init', array( __CLASS__, 'shipping_includes' ) );
        add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'add_shipping_method' ) );
    }

	public static function test() {
    	// $label = new Label();
    	// $label->set_dhl_product( 123 );
    	// var_dump($label->get_changes());

		$label = wc_gzd_dhl_get_label( 14 );
    	$label->set_has_return( true );

    	/*$shipment = wc_gzd_get_shipment( $label->get_shipment_id() );
    	$address = $shipment->get_address();
    	$address['postcode'] = '12059';
    	$address['city'] = 'Berlin';
    	$address['address_1'] = 'Sonnenallee 181';
    	$address['country'] = 'DE';
    	$shipment->set_address( $address );
    	$shipment->save();
    	*/

    	$api = self::get_api();
    	try {
		    $api->get_label_api()->delete_label( $label );
	    } catch( Exception $e ) {
    		var_dump($e);
	    }

		try {
			$api->get_label_api()->get_label( $label );
		} catch( Exception $e ) {
			var_dump($e);
		}
        // $api->get_label_api()->delete_label( $label );
		// $api->get_label_api()->delete_label( $label );

    	exit();

    	/*$label = wc_gzd_dhl_get_label( 5 );
    	$label->save();
    	var_dump($label);
    	exit();
    	*/

    	/*$validate = wc_gzd_dhl_validate_label_args( array(
    		'services' => array(
    			'IdentCheck'
		    ),
		    'preferred_day' => '1989-10-10',
		    'preferred_time_start' => '10:00',
		    'preferred_time_end' => '12:00',
		    'ident_date_of_birth' => '1997-10-10'
	    ) );

    	if ( ! is_wp_error( $validate ) ) {
    		$label = new Label();
    		$label->set_props( $validate );

			var_dump($label->get_services());
			var_dump($label->get_ident_date_of_birth()->date('Y-m-d'));
			var_dump($label->get_preferred_day()->date('Y-m-d'));
			var_dump($label->get_preferred_time());
    		var_dump($label);
    		exit();
	    }

    	var_dump($validate);
    	exit();
    	*/

    	/*
		$api    = self::get_api();
		$times = $api->get_preferred_day_time( '12207' );
		var_dump($times);

		var_dump(wc_gzd_dhl_get_preferred_times_select_options( $times['preferred_time'] ));

		exit();
    	*/

		/*$result = $api->get_parcel_api()->get_services( array(
			'postcode'    => '53225',
			'start_date'  => '2019-08-20',
			'account_num' => '0',
		) );
		*/
	}

	public static function register_data_stores( $stores ) {
        $stores['dhl-label'] = 'Vendidero\Germanized\DHL\DataStores\Label';

        return $stores;
    }

    public static function get_api() {
		if ( is_null( self::$api ) ) {
			self::$api = new Paket( self::get_base_country() );
		}

		return self::$api;
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

	public static function get_assets_url() {
		return self::get_url() . '/assets';
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
    	$debug_user = defined( 'WC_GZD_DHL_SANDBOX_USER' ) ? WC_GZD_DHL_SANDBOX_USER : '';

        return self::is_debug_mode() ? $debug_user : self::get_app_id();
    }

    public static function get_cig_password() {
	    $debug_pwd = defined( 'WC_GZD_DHL_SANDBOX_PASSWORD' ) ? WC_GZD_DHL_SANDBOX_PASSWORD : '';

        return self::is_debug_mode() ? $debug_pwd : self::get_app_token();
    }

    public static function get_gk_api_user() {
	    return self::is_debug_mode() ? '2222222222_01' : self::get_setting( 'gk_api_user' );
    }

	public static function get_gk_api_signature() {
		return self::is_debug_mode() ? 'pass' : self::get_setting( 'gk_api_signature' );
	}

    public static function get_cig_url() {
        return self::is_debug_mode() ? 'https://cig.dhl.de/services/sandbox/soap' : 'https://cig.dhl.de/services/production/soap';
    }

    public static function get_rest_url() {
        return self::is_debug_mode() ? 'https://cig.dhl.de/services/sandbox/rest' : 'https://cig.dhl.de/services/production/rest';
    }

    public static function get_gk_api_url() {
	    return self::is_debug_mode() ? 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/3.0/geschaeftskundenversand-api-3.0.wsdl' : 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/3.0/geschaeftskundenversand-api-3.0.wsdl';
    }

	public static function get_parcel_finder_api_url() {
		return self::is_debug_mode() ? 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/parcelshopfinder/1.0/parcelshopfinder-1.0-sandbox.wsdl' : 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/parcelshopfinder/1.0/parcelshopfinder-1.0-production.wsdl';
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

    public static function get_participation_number( $product ) {
    	return self::get_setting( 'participation_' . $product );
    }

    public static function get_setting( $name ) {

    	if ( self::is_debug_mode() && 'account_num' === $name ) {
    		return '2222222222';
	    } elseif( 'cutoff_time' === $name ) {
    		return '12:00';
	    } elseif( 'shipper_company' === $name ) {
    		return 'Company';
	    } elseif( 'shipper_full_name' === $name ) {
		    return 'Test';
	    } elseif( 'shipper_street' === $name ) {
		    return 'Schillerstraße';
	    } elseif( 'shipper_street_no' === $name ) {
		    return '36';
	    } elseif( 'shipper_postcode' === $name ) {
		    return '12207';
	    } elseif( 'shipper_city' === $name ) {
		    return 'Berlin';
	    } elseif( 'shipper_country' === $name ) {
		    return 'DE';
	    } elseif( 'shipper_email' === $name ) {
		    return 'info@vendidero.de';
	    } elseif( 'return_address_country' === $name ) {
		    return 'DE';
	    } elseif( 'participation_V01PAK' === $name ) {
    		return '04';
	    } elseif( 'participation_return' === $name ) {
			return '01';
	    }

    	return '';
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

    public static function get_base_country() {
	    $base_location = wc_get_base_location();

	    return $base_location['country'];
    }

    public static function get_us_territories() {
    	return self::$us_territories;
    }

	/**
	 * Function return whether the sender and receiver country is the same territory
	 */
	public static function is_shipping_domestic( $country_receiver ) {
		// If base is US territory
		if ( in_array( self::get_base_country(), self::get_us_territories() ) ) {
			// ...and destination is US territory, then it is "domestic"
			if( in_array( $country_receiver, self::get_us_territories() ) ) {
				return true;
			} else {
				return false;
			}
		} elseif( $country_receiver == self::get_base_country() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function return whether the sender and receiver country is "crossborder" i.e. needs CUSTOMS declarations (outside EU)
	 */
	public static function is_crossborder_shipment( $country_receiver ) {
		if ( self::is_shipping_domestic( $country_receiver ) ) {
			return false;
		}

		// Is sender country in EU...
		if ( in_array( self::get_base_country(), WC()->countries->get_european_union_countries() ) ) {
			// ... and receiver country is in EU means NOT crossborder!
			if ( in_array( $country_receiver, WC()->countries->get_european_union_countries() ) ) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
}