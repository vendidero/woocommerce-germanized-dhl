<?php

namespace Vendidero\Germanized\DHL;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class ParcelFinder {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'add_shipping_fields' ), 10 );

		if ( self::has_map() ) {
			add_action( 'wp_footer', array( __CLASS__, 'add_form' ), 50 );
			add_action( 'woocommerce_before_checkout_shipping_form', array( __CLASS__, 'add_button' ) );
		}
	}

	public static function add_scripts() {

		// load scripts on checkout page only
		if ( ! is_checkout() && ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		$deps   = array( 'jquery', 'jquery-blockui' );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( is_checkout() ) {
			array_push( $deps, 'wc-checkout' );
		}

		wp_register_script( 'wc-gzd-parcel-finder-dhl', Package::get_assets_url() . '/js/parcel-finder' . $suffix . '.js', $deps, Package::get_version(), true );
		wp_register_style( 'wc-gzd-parcel-finder-dhl', Package::get_assets_url() . '/css/parcel-finder' . $suffix . '.css', array(), Package::get_version() );

		if ( self::has_map() ) {

			wp_localize_script( 'wc-gzd-parcel-finder-dhl', 'wc_gzd_dhl_parcel_finder_params', array(
				'parcel_finder_nonce' => wp_create_nonce('dhl-parcel-finder' ),
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
			) );

			wp_enqueue_script( 'wc-gzd-parcel-finder-dhl' );
			wp_enqueue_style( 'wc-gzd-parcel-finder-dhl' );
		}
	}

	protected static function get_setting( $key ) {
		$settings = Package::get_setting( 'parcel_finder' );

		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	public static function is_enabled() {
		return self::is_packstation_enabled() || self::is_parcelshop_enabled() || self::is_post_office_enabled();
	}

	public static function is_post_office_enabled() {
		return true;
		return 'yes' === self::get_setting( 'display_post_office' );
	}

	public static function is_packstation_enabled() {
		return true;
		return 'yes' === self::get_setting( 'display_packstation' );
	}

	public static function is_parcelshop_enabled() {
		return true;
		return 'yes' === self::get_setting( 'display_parcelshop' );
	}

	public static function has_map() {
		return true;
	}

	public static function get_max_results() {
		return 10;
	}

	protected static function get_type_text( $sep = '&amp;', $plural = true ) {
		$search_types = '';

		if ( self::is_packstation_enabled() ) {
			$search_types .= __( 'Packstation', 'woocommerce-germanized-dhl' );
		}

		if ( self::is_parcelshop_enabled() || self::is_post_office_enabled() ) {
			$branch_type   = ( $plural ) ? __( 'Branches', 'woocommerce-germanized-dhl' ) : __( 'Branch', 'woocommerce-germanized-dhl' );
			$search_types .= ( ! empty( $search_types ) ? ' ' . $sep . ' ' . $branch_type : $branch_type );
		}

		return $search_types;
	}

	public static function add_shipping_fields( $fields ) {
		$fields['shipping_address_type'] = array(
			'label'        => __( 'Address Type', 'woocommerce-germanized-dhl' ),
			'required'     => true,
			'type'         => 'select',
			'class'        => array( 'shipping-dhl-address-type' ),
			'clear'        => true,
			'priority'     => 5,
			'options'	   => array( 'regular' => __( 'Regular Address', 'woocommerce-germanized-dhl' ), 'dhl' => self::get_type_text( ' / ' ) )
		);

		$fields['shipping_dhl_postnumber'] = array(
			'label'        => __( 'DHL customer number (Post number)', 'woocommerce-germanized-dhl' ),
			'required'     => false,
			'type'         => 'text',
			'class'        => array( 'shipping-dhl-postnumber' ),
			'description'  => __( 'Not yet a DHL customer?', 'woocommerce-germanized-dhl' ) . '<br/><a href="">' . __( 'Register now', 'woocommerce-germanized-dhl' ) . '</a>',
			'clear'        => true,
			'priority'     => 45,
		);

		$fields['shipping_address_1']['custom_attributes']                       = ( isset( $fields['shipping_address_1']['custom_attributes'] ) ? $fields['shipping_address_1']['custom_attributes'] : array() );
		$fields['shipping_address_1']['custom_attributes']['data-label-dhl']     = self::get_type_text( ' / ', false );
		$fields['shipping_address_1']['custom_attributes']['data-label-regular'] = $fields['shipping_address_1']['label'];
		$fields['shipping_address_1']['custom_attributes']['data-desc-dhl']      = self::get_button();

		return $fields;
	}

	protected static function get_icon( $type = 'packstation' ) {
		return Package::get_assets_url() . '/img/' . $type . '.png';
	}

	protected static function get_button() {
		$text     = sprintf( __( 'Search %s', 'woocommerce-germanized-dhl' ), self::get_type_text() );
		$dhl_logo = self::get_icon( 'dhl-official' );

		if ( self::has_map() ) {
			return '<a class="button gzd-dhl-parcel-shop-modal" href="javascript:;">' . $text . '<img src="' . $dhl_logo .'" class="dhl-co-logo"></a>';
		} else {
			return '<a href="" class="dhl-parcel-finder-plain-link">' . $text . '</a>';
		}
	}

	public static function add_button() {
		echo self::get_button();
	}

	public static function add_form() {

		if ( ! is_checkout()&& ! is_wc_endpoint_url( 'edit-address' ) ) {
			return;
		}

		$args = array(
			'img_packstation'        => self::get_icon( 'packstation' ),
			'img_post_office'        => self::get_icon( 'post_office' ),
			'img_parcelshop'         => self::get_icon( 'parcelshop' ),
			'is_packstation_enabled' => self::is_packstation_enabled(),
			'is_post_office_enabled' => self::is_post_office_enabled(),
			'is_parcelshop_enabled'  => self::is_parcelshop_enabled(),
		);

		wc_get_template( 'checkout/dhl/parcel-finder.php', $args, '', Package::get_path() . '/templates/' );
	}
}