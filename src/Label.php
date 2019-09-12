<?php

namespace Vendidero\Germanized\DHL;
use DateTimeZone;
use Vendidero\Germanized\Shipments\Shipment;
use WC_Data;
use WC_Data_Store;
use Exception;
use WC_DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * DHL Shipment class.
 */
class Label extends WC_Data {

    /**
     * This is the name of this object type.
     *
     * @since 3.0.0
     * @var string
     */
    protected $object_type = 'dhl_label';

    /**
     * Contains a reference to the data store for this class.
     *
     * @since 3.0.0
     * @var object
     */
    protected $data_store = 'dhl-label';

    /**
     * Stores meta in cache for future reads.
     * A group must be set to to enable caching.
     *
     * @since 3.0.0
     * @var string
     */
    protected $cache_group = 'dhl-labels';

    /**
     * @var Shipment
     */
    private $shipment = null;

    /**
     * Stores shipment data.
     *
     * @var array
     */
    protected $data = array(
        'date_created'               => null,
        'shipment_id'                => 0,
        'number'                     => '',
        'return_number'              => '',
        'path'                       => '',
        'default_path'               => '',
        'export_path'                => '',
        'dhl_product'                => '',
        'preferred_day'              => '',
        'preferred_time_start'       => '',
        'preferred_time_end'         => '',
        'preferred_location'         => '',
        'preferred_neighbor'         => '',
        'ident_date_of_birth'        => '',
        'ident_min_age'              => '',
        'visual_min_age'             => '',
        'email_notification'         => 'no',
        'has_return'                 => 'no',
        'codeable_address_only'      => 'no',
        'duties'                     => '',
        'cod_total'                  => 0,
        'return_address'             => array(),
        'services'                   => array(),
    );

    public function __construct( $data = 0 ) {
        parent::__construct( $data );

        if ( $data instanceof Label ) {
            $this->set_id( absint( $data->get_id() ) );
        } elseif ( is_numeric( $data ) ) {
            $this->set_id( $data );
        }

        $this->data_store = WC_Data_Store::load( 'dhl-label' );

        // If we have an ID, load the user from the DB.
        if ( $this->get_id() ) {
            try {
                $this->data_store->read( $this );
            } catch ( Exception $e ) {
                $this->set_id( 0 );
                $this->set_object_read( true );
            }
        } else {
            $this->set_object_read( true );
        }
    }

    /**
     * Merge changes with data and clear.
     * Overrides WC_Data::apply_changes.
     * array_replace_recursive does not work well for license because it merges domains registered instead
     * of replacing them.
     *
     * @since 3.2.0
     */
    public function apply_changes() {
        if ( function_exists( 'array_replace' ) ) {
            $this->data = array_replace( $this->data, $this->changes ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_replaceFound
        } else { // PHP 5.2 compatibility.
            foreach ( $this->changes as $key => $change ) {
                $this->data[ $key ] = $change;
            }
        }
        $this->changes = array();
    }

    /**
     * Prefix for action and filter hooks on data.
     *
     * @since  3.0.0
     * @return string
     */
    protected function get_hook_prefix() {
        return 'woocommerce_gzd_dhl_label_get_';
    }

    /**
     * Return the date this license was created.
     *
     * @since  3.0.0
     * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return WC_DateTime|null object if the date is set or null if there is no date.
     */
    public function get_date_created( $context = 'view' ) {
        return $this->get_prop( 'date_created', $context );
    }

    public function get_shipment_id( $context = 'view' ) {
        return $this->get_prop( 'shipment_id', $context );
    }

    public function get_dhl_product( $context = 'view' ) {
        return $this->get_prop( 'dhl_product', $context );
    }

    public function get_number( $context = 'view' ) {
        return $this->get_prop( 'number', $context );
    }

	public function get_return_number( $context = 'view' ) {
		return $this->get_prop( 'return_number', $context );
	}

	public function get_cod_total( $context = 'view' ) {
		return $this->get_prop( 'cod_total', $context );
	}

	public function get_duties( $context = 'view' ) {
		return $this->get_prop( 'duties', $context );
	}

    public function get_path( $context = 'view' ) {
        return $this->get_prop( 'path', $context );
    }

	public function get_default_path( $context = 'view' ) {
		return $this->get_prop( 'default_path', $context );
	}

	/**
	 * @param bool   $force Whether to force file download or show stream in browser
	 * @param string $path E.g. default or export
	 *
	 * @return string
	 */
    public function get_download_url( $force = false, $path = '' ) {
    	return add_query_arg( array( 'action' => 'wc-gzd-dhl-download-label', 'label_id' => $this->get_id(), 'path' => $path, 'force' => wc_bool_to_string( $force ) ), wp_nonce_url( admin_url(), 'dhl-download-label' ) );
    }

    public function get_export_path( $context = 'view' ) {
        return $this->get_prop( 'export_path', $context );
    }

    public function get_preferred_day( $context = 'view' ) {
        return $this->get_prop( 'preferred_day', $context );
    }

    public function get_preferred_time() {
	    $start = $this->get_preferred_time_start();
	    $end   = $this->get_preferred_time_end();

	    if ( $start && $end ) {
		    return $start->date( 'H:i' ) . '-' . $end->date( 'H:i' );
	    }

	    return null;
    }

	public function get_preferred_time_start( $context = 'view' ) {
		return $this->get_prop( 'preferred_time_start', $context );
	}

	public function get_preferred_time_end( $context = 'view' ) {
		return $this->get_prop( 'preferred_time_end', $context );
	}

	public function get_preferred_formatted_time() {
		$start = $this->get_preferred_time_start();
		$end   = $this->get_preferred_time_end();

		if ( $start && $end ) {
			return sprintf( _x( '%s-%s', 'time-span', 'woocommerce-germanized-dhl' ), $start->date( 'H' ), $end->date( 'H' ) );
		}

		return null;
	}

	public function get_preferred_location( $context = 'view' ) {
		return $this->get_prop( 'preferred_location', $context );
	}

	public function get_preferred_neighbor( $context = 'view' ) {
		return $this->get_prop( 'preferred_neighbor', $context );
	}

	public function get_ident_date_of_birth( $context = 'view' ) {
		return $this->get_prop( 'ident_date_of_birth', $context );
	}

	public function get_ident_min_age( $context = 'view' ) {
		return $this->get_prop( 'ident_min_age', $context );
	}

	public function get_visual_min_age( $context = 'view' ) {
		return $this->get_prop( 'visual_min_age', $context );
	}

	public function get_email_notification( $context = 'view' ) {
		return $this->get_prop( 'email_notification', $context );
	}

	public function has_email_notification() {
    	return ( true === $this->get_email_notification() );
	}

	public function get_has_return( $context = 'view' ) {
		return $this->get_prop( 'has_return', $context );
	}

	public function has_return() {
    	$products = wc_gzd_dhl_get_return_products();

		return ( true === $this->get_has_return() && in_array( $this->get_dhl_product(), $products ) );
	}

	public function get_codeable_address_only( $context = 'view' ) {
		return $this->get_prop( 'codeable_address_only', $context );
	}

	public function codeable_address_only() {
		return ( true === $this->get_codeable_address_only() );
	}

	public function get_return_address( $context = 'view' ) {
		return $this->get_prop( 'return_address', $context );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @since  3.0.0
	 * @param  string $prop Name of prop to get.
	 * @param  string $address billing or shipping.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_address_prop( $prop, $address = 'return_address', $context = 'view' ) {
		$value = null;

		if ( isset( $this->changes[ $address ][ $prop ] ) || isset( $this->data[ $address ][ $prop ] ) ) {
			$value = isset( $this->changes[ $address ][ $prop ] ) ? $this->changes[ $address ][ $prop ] : $this->data[ $address ][ $prop ];

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $address . '_' . $prop, $value, $this );
			}
		}

		return $value;
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @since  3.0.0
	 * @param  string $prop Name of prop to get.
	 * @param  string $address billing or shipping.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_return_address_prop( $prop, $context = 'view' ) {
		$value = $this->get_address_prop( $prop, 'return_address', $context );

		// Load from settings
		if ( is_null( $value ) ) {
			$value = Package::get_setting( 'return_' . $prop );
		}

		return $value;
	}

	public function get_return_street( $context = 'view' ) {
		return $this->get_return_address_prop( 'street', $context );
	}

	public function get_return_street_number( $context = 'view' ) {
		return $this->get_return_address_prop( 'street_number', $context );
	}

	public function get_return_company( $context = 'view' ) {
		return $this->get_return_address_prop( 'company', $context );
	}

	public function get_return_name( $context = 'view' ) {
		return $this->get_return_address_prop( 'name', $context );
	}

	public function get_return_formatted_full_name() {
		return sprintf( _x( '%1$s', 'full name', 'woocommerce-germanized-dhl' ), $this->get_return_name() );
	}

	public function get_return_postcode( $context = 'view' ) {
		return $this->get_return_address_prop( 'postcode', $context );
	}

	public function get_return_city( $context = 'view' ) {
		return $this->get_return_address_prop( 'city', $context );
	}

	public function get_return_state( $context = 'view' ) {
		return $this->get_return_address_prop( 'state', $context );
	}

	public function get_return_country( $context = 'view' ) {
		return $this->get_return_address_prop( 'country', $context );
	}

	public function get_return_phone( $context = 'view' ) {
		return $this->get_return_address_prop( 'phone', $context );
	}

	public function get_return_email( $context = 'view' ) {
		return $this->get_return_address_prop( 'email', $context );
	}

    public function get_services( $context = 'view' ) {
        return $this->get_prop( 'services', $context );
    }

    public function has_service( $service ) {
        return ( in_array( $service, $this->get_services() ) );
    }

    public function get_shipment() {
        if ( is_null( $this->shipment ) ) {
            $this->shipment = ( $this->get_shipment_id() > 0 ? wc_gzd_get_shipment( $this->get_shipment_id() ) : false );
        }

        return $this->shipment;
    }

    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

    /**
     * Set the date this license was last updated.
     *
     * @since  1.0.0
     * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
     */
    public function set_date_created( $date = null ) {
        $this->set_date_prop( 'date_created', $date );
    }

    public function set_number( $number ) {
        $this->set_prop( 'number', $number );
    }

	public function set_return_number( $number ) {
		$this->set_prop( 'return_number', $number );
	}

	public function set_cod_total( $value ) {
		$value = wc_format_decimal( $value );

		if ( ! is_numeric( $value ) ) {
			$value = 0;
		}

		$this->set_prop( 'cod_total', $value );
	}

	public function set_duties( $duties ) {
		$this->set_prop( 'duties', $duties );
	}

    public function set_dhl_product( $product ) {
        $this->set_prop( 'dhl_product', $product );
    }

    public function set_path( $path ) {
        $this->set_prop( 'path', $path );
    }

	public function set_default_path( $path ) {
		$this->set_prop( 'default_path', $path );
	}

    public function set_export_path( $path ) {
        $this->set_prop( 'export_path', $path );
    }

    public function set_services( $services ) {
        $this->set_prop( 'services', empty( $services ) ? array() : (array) $services );
    }

	public function set_preferred_day( $day ) {
		$this->set_date_prop( 'preferred_day', $day );
	}

	public function set_preferred_time_start( $time ) {
		$this->set_time_prop( 'preferred_time_start', $time );
	}

	public function set_preferred_time_end( $time ) {
		$this->set_time_prop( 'preferred_time_end', $time );
	}

	public function set_preferred_location( $location ) {
		$this->set_prop( 'preferred_location', $location );
	}

	public function set_preferred_neighbor( $neighbor ) {
		$this->set_prop( 'preferred_neighbor', $neighbor );
	}

	public function set_email_notification( $value ) {
    	$this->set_prop( 'email_notification', wc_string_to_bool( $value ) );
	}

	public function set_has_return( $value ) {
		$this->set_prop( 'has_return', wc_string_to_bool( $value ) );
	}

	public function set_return_address( $value ) {
    	$this->set_prop( 'return_address', empty( $value ) ? array() : (array) $value );
	}

	public function set_codeable_address_only( $value ) {
		$this->set_prop( 'codeable_address_only', wc_string_to_bool( $value ) );
	}

	protected function set_time_prop( $prop, $value ) {
		try {

			if ( empty( $value ) ) {
				$this->set_prop( $prop, null );
				return;
			}

			if ( is_a( $value, 'WC_DateTime' ) ) {
				$datetime = $value;
			} elseif ( is_numeric( $value ) ) {
				$datetime = new WC_DateTime( "@{$value}" );
			} else {
				$timestamp = wc_string_to_timestamp( $value );
				$datetime  = new WC_DateTime( "@{$timestamp}" );
			}

			$this->set_prop( $prop, $datetime );
		} catch ( Exception $e ) {} // @codingStandardsIgnoreLine.
	}

	public function set_ident_date_of_birth( $date ) {
		$this->set_date_prop( 'ident_date_of_birth', $date );
	}

	public function set_ident_min_age( $age ) {
    	$this->set_prop( 'ident_min_age', $age );
	}

	public function set_visual_min_age( $age ) {
		$this->set_prop( 'visual_min_age', $age );
	}

    public function add_service( $service ) {
        $services = (array) $this->get_services();

        if ( ! in_array( $service, $services ) && in_array( $service, wc_gzd_dhl_get_services() ) ) {
            $services[] = $service;

            $this->set_services( $services );
            return true;
        }

        return false;
    }

    public function remove_service( $service ) {
	    $services = (array) $this->get_services();

	    if ( in_array( $service, $services ) ) {
		    $services = array_diff( $services, array( $service ) );

		    $this->set_services( $services );
		    return true;
	    }

	    return false;
    }

    public function get_file() {
        if ( ! $path = $this->get_path() ) {
            return false;
        }

        return $this->get_file_by_path( $path );
    }

    public function get_filename() {
	    if ( ! $path = $this->get_path() ) {
		    return false;
	    }

	    return basename( $path );
    }

	public function get_default_file() {
		if ( ! $path = $this->get_default_path() ) {
			return false;
		}

		return $this->get_file_by_path( $path );
	}

	public function get_default_filename() {
		if ( ! $path = $this->get_default_path() ) {
			return false;
		}

		return basename( $path );
	}

    protected function get_file_by_path( $file ) {
        // If the file is relative, prepend upload dir.
        if ( $file && 0 !== strpos( $file, '/' ) && ( ( $uploads = Package::get_upload_dir() ) && false === $uploads['error'] ) ) {
            $file = $uploads['basedir'] . "/$file";

            return $file;
        } else {
            return false;
        }
    }

    public function get_export_file() {
        if ( ! $path = $this->get_export_path() ) {
            return false;
        }

        return $this->get_file_by_path( $path );
    }

	public function get_export_filename() {
		if ( ! $path = $this->get_export_path() ) {
			return false;
		}

		return basename( $path );
	}

    public function set_shipment_id( $shipment_id ) {
        // Reset order object
        $this->shipment = null;

        $this->set_prop( 'shipment_id', absint( $shipment_id ) );
    }
}
