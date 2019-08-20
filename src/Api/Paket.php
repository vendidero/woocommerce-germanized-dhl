<?php
/**
 * Initializes blocks in WordPress.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL\Api;

use \Vendidero\Germanized\DHL\Package;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

defined( 'ABSPATH' ) || exit;

class Paket {

    protected $label_api = null;

    protected $finder_api = null;

    protected $parcel_api = null;

    protected $country_code = '';

    public function __construct( $country_code ) {
        $this->country_code = $country_code;
    }

    public function get_label_api() {
        if ( is_null( $this->label_api ) ) {
            try {
                $this->label_api = new LabelSoap();
            } catch( Exception $e ) {
                $this->label_api = null;
            }
        }

        if ( is_null( $this->label_api ) ) {
            throw new Exception( __( 'Label API not available', 'woocommerce-germanized-dhl' ) );
        }

        return $this->label_api;
    }

    public function get_finder_api() {
        if ( is_null( $this->finder_api ) ) {
            try {
                $this->finder_api = new FinderSoap();
            } catch( Exception $e ) {
                $this->finder_api = null;
            }
        }

        if ( is_null( $this->finder_api ) ) {
            throw new Exception( __( 'Parcel Finder API not available', 'woocommerce-germanized-dhl' ) );
        }

        return $this->finder_api;
    }

    public function get_parcel_api() {
        if ( is_null( $this->parcel_api ) ) {
            try {
                $this->parcel_api = new ParcelRest();
            } catch( Exception $e ) {
                $this->parcel_api = null;
            }
        }

        if ( is_null( $this->parcel_api ) ) {
            throw new Exception( __( 'Parcel API not available', 'woocommerce-germanized-dhl' ) );
        }

        return $this->parcel_api;
    }

    public function get_country_code() {
        return $this->country_code;
    }

    public function test_connection() {
        return $this->get_label_api()->test_connection();
    }

    public function get_parcel_location( $args ) {
        return $this->get_finder_api()->get_parcel_location( $args );
    }

    public function get_content_indicator( ) {
        return array();
    }

    public function get_label( &$label ) {
        return $this->get_label_api()->get_label( $label );
    }

    public function delete_label( &$label ) {
        return $this->get_label_api()->delete_label( $label );
    }

    public function reset_connection( ) {
        return;
    }

    public function get_preferred_day_time( $postcode, $cutoff_time = '' ) {
	    $exclude_working_days  = wc_gzd_dhl_get_excluded_working_days();
	    $cutoff_time           = empty( $cutoff_time ) ? Package::get_setting( 'cutoff_time' ) : $cutoff_time;
	    $account_num           = Package::get_setting( 'account_num' );

        // Always exclude Sunday
        $exclude_working_days  = array_merge( $exclude_working_days, array( 'Sun' => __( 'sun', 'woocommerce-germanized-dhl' ) ) );
        $day_counter           = 0;

        // Get existing timezone to reset afterwards
        $current_timzone = date_default_timezone_get();
        // Always set and get DE timezone and check against it.
        date_default_timezone_set( 'Europe/Berlin' );

        $tz_obj             = new DateTimeZone(  'Europe/Berlin' );
        $today              = new DateTime( "now", $tz_obj );

        $today_de_timestamp = $today->getTimestamp();
        $week_day           = $today->format('D' );
        $week_date          = $today->format('Y-m-d' );
        $week_time          = $today->format('H:i' );

        // Compare week day with key since key includes capital letter in beginning and will work for English AND German!
        // Check if today is a working day...
        if ( ( ! array_key_exists( $week_day, $exclude_working_days ) ) && ( ! in_array( $week_date, Package::get_holidays( 'DE' ) ) ) ) {

            // ... and check if after cutoff time if today is a transfer day
            if( $today_de_timestamp >= strtotime( $cutoff_time ) ) {
                // If the cut off time has been passed, then add a day
                $today->add( new DateInterval('P1D' ) ); // Add 1 day

                $week_day  = $today->format('D' );
                $week_date = $today->format('Y-m-d' );

                $day_counter++;
            }
        }

        // Make sure the next transfer days are working days
        while ( array_key_exists( $week_day, $exclude_working_days ) || in_array( $week_date, Package::get_holidays( 'DE' ) ) ) {

            $today->add( new DateInterval( 'P1D' ) ); // Add 1 day
            $week_day  = $today->format( 'D' );
            $week_date = $today->format( 'Y-m-d' );

            $day_counter++;
        }

        $args['postcode']    = $postcode;
        $args['account_num'] = $account_num;
        $args['start_date']  = $week_date;
        $preferred_day_time  = array();

        try {
            $preferred_services                   = $this->get_parcel_api()->get_services( $args );
            $preferred_day_time['preferred_day']  = $this->get_preferred_day( $preferred_services );
            $preferred_day_time['preferred_time'] = $this->get_preferred_time( $preferred_services );
        } catch( Exception $e ) {
        	throw $e;
        }

        // Reset time locael
        // setlocale(LC_TIME, $current_locale);
        // Reset timezone to not affect any other plugins
        date_default_timezone_set( $current_timzone );

        return $preferred_day_time;
    }

    protected function get_preferred_day( $preferred_services ) {

        $day_of_week_arr = array(
            '1' => __( 'Mon', 'pr-shipping-dhl' ),
            '2' => __( 'Tue', 'pr-shipping-dhl' ),
            '3' => __( 'Wed', 'pr-shipping-dhl' ),
            '4' => __( 'Thu', 'pr-shipping-dhl' ),
            '5' => __( 'Fri', 'pr-shipping-dhl' ),
            '6' => __( 'Sat', 'pr-shipping-dhl' ),
            '7' => __( 'Sun', 'pr-shipping-dhl' )
        );

        $preferred_days = array();

        if ( isset( $preferred_services->preferredDay->available ) && $preferred_services->preferredDay->available && isset( $preferred_services->preferredDay->validDays ) ) {

        	foreach ( $preferred_services->preferredDay->validDays as $days_key => $days_value ) {
                $temp_day_time = strtotime( $days_value->start );
                $day_of_week   = date('N', $temp_day_time );
                $week_date     = date('Y-m-d', $temp_day_time );

                $preferred_days[ $week_date ] = $day_of_week_arr[ $day_of_week ];
            }

            // Add none option
            array_unshift( $preferred_days, _x( 'none', 'day context', 'woocommerce-germanized-dhl' ) );
        }

        return $preferred_days;
    }

    protected function get_preferred_time( $preferred_services ) {
        $preferred_times = array();

        if ( isset( $preferred_services->preferredTime->available ) && $preferred_services->preferredTime->available && isset( $preferred_services->preferredTime->timeframes ) ) {

            // Add none option
            $preferred_times[0] = _x( 'None', 'time context', 'woocommerce-germanized-dhl' );

            foreach ( $preferred_services->preferredTime->timeframes as $time_key => $time_value ) {
                $temp_day_time      = str_replace( ':00', '', $time_value->start );
                $temp_day_time     .= '-';
                $temp_day_time     .= str_replace( ':00', '', $time_value->end );
                $temp_day_time_key  = $time_value->start . '-' . $time_value->end;

                $preferred_times[ $temp_day_time_key ] = $temp_day_time;
            }
        }

        return $preferred_times;
    }
}
