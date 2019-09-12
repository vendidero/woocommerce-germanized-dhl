<?php

namespace Vendidero\Germanized\DHL;
use Exception;
use WC_DateTime;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class ParcelServices {

	public static function init() {
		if ( self::is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
			add_action( 'woocommerce_review_order_after_shipping', array( __CLASS__, 'add_fields' ), 100 );
			add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'add_fees' ) );
			add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate' ), 10, 2 );
			add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'create_order' ), 10 );
			add_filter( 'woocommerce_get_order_item_totals', array( __CLASS__, 'order_totals' ), 10, 2 );
		}
	}

	public static function order_totals( $total_rows, $order ) {
		$new_rows = array();

		if ( $dhl_order = wc_gzd_dhl_get_order( $order ) ) {
			if ( $dhl_order->has_preferred_day() ) {
				$new_rows['preferred_day'] = array(
					'label' => __( 'Preferred Day', 'woocommerce-germanized-dhl' ),
					'value' => wc_format_datetime( $dhl_order->get_preferred_day(), wc_date_format() ),
				);
			}

			if ( $dhl_order->has_preferred_time() ) {
				$new_rows['preferred_time'] = array(
					'label' => __( 'Preferred Time', 'woocommerce-germanized-dhl' ),
					'value' => $dhl_order->get_preferred_time(),
				);
			}

			if ( $dhl_order->has_preferred_location() ) {
				$new_rows['preferred_location'] = array(
					'label' => __( 'Preferred Location', 'woocommerce-germanized-dhl' ),
					'value' => $dhl_order->get_preferred_location(),
				);
			} elseif( $dhl_order->has_preferred_neighbor() ) {
				$new_rows['preferred_neighbor'] = array(
					'label' => __( 'Preferred Neighbor', 'woocommerce-germanized-dhl' ),
					'value' => $dhl_order->get_preferred_neighbor_formatted_address(),
				);
			}
		}

		if ( ! empty( $new_rows ) ) {

			// Instert before payment method
			$insert_before = array_search( 'payment_method', array_keys( $total_rows ) );

			// If no payment method, insert before order total
			if ( empty( $insert_before ) ) {
				$insert_before = array_search( 'order_total', array_keys( $total_rows ) );
			}

			if ( empty( $insert_before ) ) {
				$total_rows += $new_rows;
			} else {
				$first_array = array_splice( $total_rows, 0, $insert_before );
				$total_rows  = array_merge( $first_array, $new_rows, $total_rows );
			}
		}

		return $total_rows;
	}

	public static function test() {
		/*$order = new Order( wc_get_order( 23804 ) );

		$order->set_preferred_day( '2019-10-10' );
		$order->set_preferred_time_start( '10:00' );
		$order->set_preferred_time_end( '12:00' );

		var_dump($order->get_preferred_day());
		var_dump($order->get_preferred_time_start());
		var_dump($order->get_preferred_time_end());
		exit();
		*/
	}

	public static function create_order( $order ) {
		$posted_data = $_POST;

		if ( self::is_available() ) {
			$data = self::get_data( $posted_data );

			if ( ! $data['errors']->has_errors() ) {
				$dhl_order = new Order( $order );

				if ( ! empty( $data['preferred_day'] ) ) {
					$dhl_order->set_preferred_day( $data['preferred_day'] );
				}

				if ( ! empty( $data['preferred_time'] ) ) {
					$dhl_order->set_preferred_time_start( $data['preferred_time_start'] );
					$dhl_order->set_preferred_time_end( $data['preferred_time_end'] );
				}

				if ( 'place' === $data['preferred_location_type'] && ! empty( $data['preferred_location'] ) ) {
					$dhl_order->set_preferred_location( $data['preferred_location'] );
				} elseif ( 'neighbor' === $data['preferred_location_type'] && ! empty( $data['preferred_location_neighbor_name'] ) && ! empty( $data['preferred_location_neighbor_address'] ) ) {
					$dhl_order->set_preferred_neighbor( $data['preferred_location_neighbor_name'] );
					$dhl_order->set_preferred_neighbor_address( $data['preferred_location_neighbor_address'] );
				}
			}
		}
	}

	public static function validate( $data, $errors ) {
		$posted_data = $_POST;

		if ( self::is_available() ) {
			$data = self::get_data( $posted_data );

			if ( $data['errors']->has_errors() ) {
				foreach( $data['errors']->get_error_messages() as $message ) {
					$errors->add( 'validation', $message );
				}
			}
		}
	}

	public static function add_fees( $cart ) {
		if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {
			return;
		}

		// POST information is either in a query string-like variable called 'post_data'...
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
		} else {
			$post_data = $_POST;
		}

		if ( self::is_available( true ) ) {
			$data = self::get_data( $post_data );

			try {
				if ( ! empty( $data['preferred_time'] ) && ! empty( $data['preferred_day'] ) ) {
					if ( ! empty( $data['preferred_day_time_cost'] ) ) {
						$cart->add_fee( __( 'DHL Preferred Day & Time', 'woocommerce-germanized-dhl' ), $data['preferred_day_time_cost'] );
					}
				} elseif ( ! empty( $data['preferred_time'] ) ) {
					if ( ! empty( $data['preferred_time_cost'] ) ) {
						$cart->add_fee( __( 'DHL Preferred Time', 'woocommerce-germanized-dhl' ), $data['preferred_time_cost'] );
					}
				} elseif ( ! empty( $data['preferred_day'] ) ) {
					if ( ! empty( $data['preferred_day_cost'] ) ) {
						$cart->add_fee( __( 'DHL Preferred Day', 'woocommerce-germanized-dhl' ), $data['preferred_day_cost'] );
					}
				}
			} catch ( Exception $e ) {}
		}
	}

	public static function add_scripts() {

		// load scripts on checkout page only
		if ( ! is_checkout() ) {
			return;
		}

		$deps   = array( 'jquery', 'wc-checkout', 'jquery-tiptip' );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
		wp_register_script( 'wc-gzd-preferred-services-dhl', Package::get_assets_url() . '/js/preferred-services' . $suffix . '.js', $deps, Package::get_version(), true );
		wp_register_style( 'wc-gzd-preferred-services-dhl', Package::get_assets_url() . '/css/preferred-services' . $suffix . '.css', array(), Package::get_version() );

		$excluded_gateways = self::get_excluded_payment_gateways();

		wp_localize_script( 'wc-gzd-preferred-services-dhl', 'wc_gzd_dhl_preferred_services_params', array(
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'payment_gateways_excluded' => empty( $excluded_gateways ) ? false : true,
		) );

		wp_enqueue_script( 'wc-gzd-preferred-services-dhl' );
		wp_enqueue_style( 'wc-gzd-preferred-services-dhl' );
	}

	protected static function get_excluded_payment_gateways() {
		return (array) self::get_setting( 'payment_gateways_excluded' );
	}

	protected static function payment_gateway_supports_services( $method ) {
		$methods = self::get_excluded_payment_gateways();

		if ( ! empty( $methods ) && in_array( $method, $methods ) ) {
			return false;
		}

		return true;
	}

	protected static function get_enabled_shipping_methods() {
		return (array) self::get_setting( 'shipping_methods_enabled' );
	}

	protected static function shipping_method_supports_services( $method ) {
		$slug             = wc_gzd_dhl_get_shipping_method_slug( $method );
		$methods          = self::get_enabled_shipping_methods();

		if ( ! empty( $methods ) && in_array( $slug, $methods ) ) {
			return true;
		}

		return false;
	}

	protected static function get_preferred_day_cost() {
		$cost = self::get_setting( 'day_cost' );

		if ( empty( $cost ) ) {
			$cost = 0;
		}

		$cost = 1.2;

		return $cost;
	}

	protected static function get_preferred_time_cost() {
		$cost = self::get_setting( 'time_cost' );

		if ( empty( $cost ) ) {
			$cost = 0;
		}

		$cost = 5;

		return $cost;
	}

	protected static function get_preferred_day_time_cost() {
		$cost = self::get_setting( 'day_time_cost' );

		if ( empty( $cost ) ) {
			$cost = 0;
		}

		$cost = 5;

		return $cost;
	}

	protected static function is_available( $check_day_transfer = false ) {
		$chosen_shipping_methods = (array) WC()->session->get( 'chosen_shipping_methods' );
		$chosen_payment_method   = (array) WC()->session->get( 'chosen_payment_method' );
		$customer_country        = WC()->customer->get_shipping_country();
		$display_preferred       = false;

		// Preferred options are only for Germany customers
		if ( Package::base_country_supports( 'services' ) && 'DE' === $customer_country ) {

			if ( $check_day_transfer ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					// Check products
				}
			}

			foreach ( $chosen_shipping_methods as $key => $value ) {
				if ( self::shipping_method_supports_services( $value ) ) {
					$display_preferred = true;
					break;
				}
			}

			foreach( $chosen_payment_method as $key => $method ) {
				if ( ! self::payment_gateway_supports_services( $method ) ) {
					$display_preferred = false;
					break;
				}
			}
		}

		return $display_preferred;
	}

	protected static function get_data( $post_data ) {
		$data = array(
			'preferred_day_enabled'               => false,
			'preferred_time_enabled'              => false,
			'preferred_location_enabled'          => false,
			'preferred_neighbor_enabled'          => false,
			'preferred_day'                       => '',
			'preferred_time'                      => '',
			'preferred_location_type'             => 'none',
			'preferred_location'                  => '',
			'preferred_location_neighbor_name'    => '',
			'preferred_location_neighbor_address' => '',
			'preferred_day_time_options'          => WC()->session->get( 'dhl_preferred_day_time_options' ),
			'preferred_day_cost'                  => self::get_preferred_day_cost(),
			'preferred_time_cost'                 => self::get_preferred_time_cost(),
			'preferred_day_time_cost'             => self::get_preferred_day_time_cost(),
		);

		$data['errors'] = new WP_Error();

		if ( is_null( $data['preferred_day_time_options'] ) ) {
			self::refresh_day_time_session();
			$data['preferred_day_time_options'] = WC()->session->get( 'dhl_preferred_day_time_options' );
		}

		try {
			if ( self::is_preferred_day_enabled() ) {
				$data['preferred_day_enabled'] = true;

				if ( isset( $post_data['dhl_preferred_day'] ) && ! empty( $post_data['dhl_preferred_day'] ) ) {
					$day = wc_clean( $post_data['dhl_preferred_day'] );

					if ( wc_gzd_dhl_is_valid_datetime( $day, 'Y-m-d' ) ) {
						if ( ! empty( $data['preferred_day_time_options']['preferred_day'] ) ) {
							if ( array_key_exists( $day, $data['preferred_day_time_options']['preferred_day'] ) ) {
								$data['preferred_day'] = $day;
							}
						}
					}

					if ( empty( $data['preferred_day'] ) ) {
						$data['errors']->add( 'validation', __( 'Sorry, but the preferred day you have chosen is no longer available.', 'woocommerce-germanized-dhl' ) );
					}
				}
			}

			if ( self::is_preferred_time_enabled() ) {
				$data['preferred_time_enabled'] = true;

				if ( isset( $post_data['dhl_preferred_time'] ) && ! empty( $post_data['dhl_preferred_time'] ) ) {
					$preferred_org_time = wc_clean( $post_data['dhl_preferred_time'] );
					$preferred_time     = explode( '-', $preferred_org_time );

					if ( sizeof( $preferred_time ) === 2 ) {
						$time_start = $preferred_time[0];
						$time_end   = $preferred_time[1];

						if ( wc_gzd_dhl_is_valid_datetime( $time_start, 'H:i' ) && wc_gzd_dhl_is_valid_datetime( $time_end, 'H:i' ) ) {
							if ( ! empty( $data['preferred_day_time_options']['preferred_time'] ) ) {
								if ( array_key_exists( $preferred_org_time, $data['preferred_day_time_options']['preferred_time'] ) ) {
									$data['preferred_time']       = $preferred_org_time;
									$data['preferred_time_start'] = $time_start;
									$data['preferred_time_end']   = $time_end;
								}
							}
						}
					}

					if ( empty( $data['preferred_time'] ) ) {
						$data['errors']->add( 'validation', __( 'Sorry, but the preferred time you have chosen is no longer available.', 'woocommerce-germanized-dhl' ) );
					}
				}
			}

			if ( self::is_preferred_neighbor_enabled() ) {
				$data['preferred_neighbor_enabled'] = true;
			}

			if ( self::is_preferred_location_enabled() ) {
				$data['preferred_location_enabled'] = true;
			}

			if ( self::is_preferred_location_enabled() || self::is_preferred_neighbor_enabled() ) {
				if ( isset( $post_data['dhl_preferred_location_type'] ) && 'none' !== $post_data['dhl_preferred_location_type'] ) {
					if ( self::is_preferred_location_enabled() && 'place' === $post_data['dhl_preferred_location_type'] ) {
						$location = isset( $post_data['dhl_preferred_location'] ) ? wc_clean( $post_data['dhl_preferred_location'] ) : '';

						$data['preferred_location_type'] = 'place';

						if ( ! empty( $location ) ) {
							$data['preferred_location'] = $location;
						} else {
							$data['errors']->add( 'validation', __( 'Please choose a preferred location.', 'woocommerce-germanized-dhl' ) );
						}
					} elseif( self::is_preferred_neighbor_enabled() && 'neighbor' === $post_data['dhl_preferred_location_type'] ) {
						$name    = isset( $post_data['dhl_preferred_location_neighbor_name'] ) ? wc_clean( $post_data['dhl_preferred_location_neighbor_name'] ) : '';
						$address = isset( $post_data['dhl_preferred_location_neighbor_address'] ) ?  wc_clean( $post_data['dhl_preferred_location_neighbor_address'] ) : '';

						$data['preferred_location_type'] = 'neighbor';

						if ( ! empty( $name ) && ! empty( $address ) ) {
							$data['preferred_location_neighbor_name']    = $name;
							$data['preferred_location_neighbor_address'] = $address;
						} else {
							$data['errors']->add( 'validation', __( 'Please choose name and address of your preferred neighbor.', 'woocommerce-germanized-dhl' ) );
						}
					}
				}
			}

			return $data;
		} catch( Exception $e ) {
			return $data;
		}
	}

	protected static function refresh_day_time_session() {
		WC()->session->set( 'dhl_preferred_day_time_options', array() );

		if ( self::is_preferred_day_enabled() || self::is_preferred_time_enabled() ) {
			$shipping_postcode = WC()->customer->get_shipping_postcode();

			try {
				if ( ! empty( $shipping_postcode ) ) {
					WC()->session->set( 'dhl_preferred_day_time_options', Package::get_api()->get_preferred_day_time( $shipping_postcode ) );
				}

			} catch( Exception $e ) {}
		}
	}

	public static function add_fields() {
		$post_data = array();

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
		}

		if ( self::is_available( true ) ) {
			$data = self::get_data( $post_data );

			self::refresh_day_time_session();

			$data['preferred_day_time_options'] = WC()->session->get( 'dhl_preferred_day_time_options' );
			$data['logo_url']                   = Package::get_assets_url() . '/img/dhl-official.png';

			wc_get_template( 'checkout/dhl/preferred-services.php', $data, '', Package::get_path() . '/templates/' );
		}
	}

	public static function is_enabled() {
		return Package::base_country_supports( 'services' ) && ( self::is_preferred_day_enabled() || self::is_preferred_time_enabled() || self::is_preferred_location_enabled() || self::is_preferred_neighbor_enabled() );
	}

	public static function is_preferred_day_enabled() {
		return 'yes' === self::get_setting( 'day_enable' );
	}

	public static function is_preferred_time_enabled() {
		return 'yes' === self::get_setting( 'time_enable' );
	}

	public static function is_preferred_location_enabled() {
		return 'yes' === self::get_setting( 'location_enable' );
	}

	public static function is_preferred_neighbor_enabled() {
		return 'yes' === self::get_setting( 'neighbor_enable' );
	}

	protected static function get_setting( $key ) {
		$setting = Package::get_setting( "preferred_{$key}" );

		return $setting;
	}
}