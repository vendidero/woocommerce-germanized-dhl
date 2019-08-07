<?php

namespace Vendidero\Germanized\DHL;
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
        'date_created'          => null,
        'shipment_id'           => 0,
        'number'                => '',
        'path'                  => '',
        'export_path'           => '',
        'dhl_product'           => '',
        'preferred_day'         => '',
        'services'              => array(),
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

    public function get_path( $context = 'view' ) {
        return $this->get_prop( 'path', $context );
    }

    public function get_export_path( $context = 'view' ) {
        return $this->get_prop( 'export_path', $context );
    }

    public function get_preferred_day( $context = 'view' ) {
        return $this->get_prop( 'preferred_day', $context );
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

    public function set_dhl_product( $product ) {
        $this->set_prop( 'product', $product );
    }

    public function set_path( $path ) {
        $this->set_prop( 'path', $path );
    }

    public function set_export_path( $path ) {
        $this->set_prop( 'export_path', $path );
    }

    public function set_services( $services ) {
        $this->set_prop( 'services', (array) $services );
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

    public function set_preferred_day( $day ) {
        $this->set_date_prop( 'preferred_day', $day );
    }

    public function get_file() {
        if ( ! $path = $this->get_path() ) {
            return false;
        }

        return $this->get_file_by_path( $path );
    }

    protected function get_file_by_path( $file ) {
        // If the file is relative, prepend upload dir.
        if ( $file && 0 !== strpos( $file, '/' ) && ! preg_match( '|^.:\|', $file ) && ( ( $uploads = Package::get_upload_dir() ) && false === $uploads['error'] ) ) {
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

    public function set_shipment_id( $shipment_id ) {
        // Reset order object
        $this->shipment = null;

        $this->set_prop( 'shipment_id', absint( $shipment_id ) );
    }
}
