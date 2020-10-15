<?php

namespace Vendidero\Germanized\DHL;

use baltpeter\Internetmarke\Address;
use baltpeter\Internetmarke\CompanyName;
use baltpeter\Internetmarke\Name;
use baltpeter\Internetmarke\PageFormat;
use baltpeter\Internetmarke\PartnerInformation;
use baltpeter\Internetmarke\PersonName;
use baltpeter\Internetmarke\Service;
use baltpeter\Internetmarke\User;
use Vendidero\Germanized\DHL\Api\ImRefundSoap;

defined( 'ABSPATH' ) || exit;

class Internetmarke {

	/**
	 * @var PartnerInformation|null
	 */
	protected $partner = null;

	/**
	 * @var Service|null
	 */
	protected $api = null;

	/**
	 * @var User|null
	 */
	protected $user = null;

	/**
	 * @var \WP_Error
	 */
	protected $errors = null;

	/**
	 * @var ImProductList|null
	 */
	protected $products = null;

	/**
	 * @var ImRefundSoap|null
	 */
	protected $refund_api = null;

	/**
	 * @var null|PageFormat[]
	 */
	protected $page_formats = null;

	public function __construct() {
		$this->partner = new PartnerInformation( Package::get_internetmarke_partner_id(), Package::get_internetmarke_key_phase(), Package::get_internetmarke_token() );
		$this->errors  = new \WP_Error();

		try {
			$this->api = new Service( $this->partner, array(), Package::get_wsdl_file( 'https://internetmarke.deutschepost.de/OneClickForAppV3?wsdl' ) );
		} catch( \Exception $e ) {
			$this->errors->add( 'startup', _x( 'Error while instantiating main Internetmarke API.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}

		if ( ! Package::is_internetmarke_enabled() ) {
			$this->errors->add( 'startup', _x( 'Internetmarke is disabled. Please enable Internetmarke.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}
	}

	public function auth() {
		if ( Package::get_internetmarke_username() && Package::get_internetmarke_password() ) {
			try {
				$this->user = $this->api->authenticateUser( Package::get_internetmarke_username(), Package::get_internetmarke_password() );
			} catch( \Exception $e ) {
				$this->errors->add( 'authentication', _x( 'Wrong username or password', 'dhl', 'woocommerce-germanized-dhl' ) );
			}
		}

		if ( ! $this->has_authentication_error() ) {
			return true;
		} else {
			return false;
		}
	}

	public function has_authentication_error() {
		$errors = $this->errors->get_error_message( 'authentication' );

		return empty( $errors ) ? false : true;
	}

	public function get_authentication_error() {
		$error = $this->errors->get_error_message( 'authentication' );

		return $error;
	}

	public function has_startup_error() {
		$errors = $this->errors->get_error_message( 'startup' );

		return empty( $errors ) ? false : true;
	}

	public function get_startup_error() {
		$error = $this->errors->get_error_message( 'startup' );

		return $error;
	}

	public function is_available() {
		return ! $this->has_authentication_error() && ! $this->has_startup_error();
	}

	public function get_user() {
		if ( ! $this->user ) {
			$this->auth();
		}

		if ( $this->user ) {
			return $this->user;
		} else {
			return false;
		}
	}

	public function get_balance( $force_refresh = false ) {
		$balance = get_transient( 'wc_gzd_dhl_portokasse_balance' );

		if ( ! $balance || $force_refresh ) {
			if ( $user = $this->get_user() ) {
				$balance = $user->getWalletBalance();

				set_transient( 'wc_gzd_dhl_portokasse_balance', $user->getWalletBalance(), HOUR_IN_SECONDS );
			} else {
				$balance = 0;
			}
		}

		return $balance;
	}

	protected function invalidate_balance() {
		delete_transient( 'wc_gzd_dhl_portokasse_balance' );
	}

	protected function load_products() {
		if ( is_null( $this->products ) ) {
			$this->products = new ImProductList();
		}
	}

	public function get_products( $filters = array() ) {
		$this->load_products();

		return $this->products->get_products( $filters );
	}

	public function get_available_products( $filters = array() ) {
		$this->load_products();

		return $this->products->get_available_products( $filters );
	}

	protected function format_dimensions( $product, $type = 'length' ) {
		$dimension = '';

		if ( ! empty( $product->{"product_{$type}_min"} ) ) {
			$dimension .= $product->{"product_{$type}_min"};

			if ( ! empty( $product->{"product_{$type}_max"} ) ) {
				$dimension .= '-' . $product->{"product_{$type}_max"};
			}
		} elseif( 0 == $product->{"product_{$type}_min"} ) {
			$dimension = sprintf( _x( 'until %s', 'dhl', 'woocommerce-germanized-dhl' ), $product->{"product_{$type}_max"} );
		}

		if ( ! empty( $dimension ) ) {
			$dimension .= ' ' . $product->{"product_{$type}_unit"};
		}

		return $dimension;
	}

	public function get_product_data( $product_code ) {
		$this->load_products();

		return $this->products->get_product_data( $product_code );
	}

	public function get_product_total( $product_code ) {
		$total = 0;

		if ( $data = $this->get_product_data( $product_code ) ) {
			$total = $data->product_price;
		}

		return $total;
	}

	public function get_available_products_printable() {
		$printable = array();

		foreach( $this->get_available_products() as $product ) {
			$dimensions       = array();
			$formatted_length = $this->format_dimensions( $product, 'length' );
			$formatted_width  = $this->format_dimensions( $product, 'width' );
			$formatted_height = $this->format_dimensions( $product, 'height' );
			$formatted_weight = $this->format_dimensions( $product, 'weight' );

			if ( ! empty( $formatted_length ) ) {
				$dimensions[] = sprintf( _x( 'Length: %s', 'dhl', 'woocommerce-germanized-dhl' ), $formatted_length );
			}

			if ( ! empty( $formatted_width ) ) {
				$dimensions[] = sprintf( _x( 'Width: %s', 'dhl', 'woocommerce-germanized-dhl' ), $formatted_width );
			}

			if ( ! empty( $formatted_height ) ) {
				$dimensions[] = sprintf( _x( 'Height: %s', 'dhl', 'woocommerce-germanized-dhl' ), $formatted_height );
			}

			if ( ! empty( $formatted_weight ) ) {
				$dimensions[] = sprintf( _x( 'Weight: %s', 'dhl', 'woocommerce-germanized-dhl' ), $formatted_weight );
			}

			$printable[ $product->product_code ] = array_merge( (array) $product, array(
				'title_formatted'             => wc_gzd_dhl_get_im_product_title( $product->product_name ),
				'price_formatted'             => wc_price( Package::cents_to_eur( $product->product_price ), array( 'currency' => 'EUR' ) ) . ' <span class="price-suffix">' . _x( 'Total', 'dhl', 'woocommerce-germanized-dhl' ) . '</span>',
				'description_formatted'       => ! empty( $product->product_annotation ) ? $product->product_annotation : $product->product_description,
				'information_text_formatted'  => $product->product_information_text,
				'dimensions_formatted'        => implode( '<br/>', $dimensions ),
			) );
		}

		return $printable;
	}

	public function get_page_formats( $force_refresh = false ) {
		if ( is_null( $this->page_formats ) ) {
			$this->page_formats = get_transient( 'wc_gzd_dhl_im_page_formats' );

			if ( ! $this->page_formats || $force_refresh ) {
				$this->page_formats = array();

				try {
					$this->page_formats = $this->api->retrievePageFormats();

					set_transient( 'wc_gzd_dhl_im_page_formats', $this->page_formats, DAY_IN_SECONDS );
				} catch( \Exception $e ) {
					// Log
				}

				$page_formats = $this->page_formats;
			} else {
				$page_formats = $this->page_formats;
			}
		} else {
			$page_formats = $this->page_formats;
		}

		return $page_formats;
	}

	public function get_page_format_list() {
		$formats = $this->get_page_formats();
		$options = array();

		foreach( $formats as $format ) {
			if ( ! $format->isIsAddressPossible() ) {
				continue;
			}

			$options[ $format->getId() ] = $format->getName();
		}

		return $options;
	}

	public function preview_stamp( $product_id, $address_type = 'FrankingZone', $image_id = null ) {
		$preview_url = false;

		try {
			$preview_url = $this->api->retrievePreviewVoucherPng( $product_id, $address_type, $image_id );
		} catch( \Exception $e ) {}

		return $preview_url;
	}

	/**
	 * @param PostLabel $label
	 *
	 * @return mixed
	 */
	public function get_label( &$label ) {
		if ( empty( $label->get_shop_order_id() ) ) {
			return $this->create_label( $label );
		} else {
			if ( ! $this->auth() ) {
				throw new \Exception( $this->get_authentication_error() );
			}

			try {
				$stamp = $this->api->retrieveOrder( $this->get_user()->getUserToken(), $label->get_shop_order_id() );
			} catch( \Exception $e ) {
				return $this->create_label( $label );
			}

			return $this->update_label( $label, $stamp );
		}
	}

	public function get_refund_api() {
		if ( is_null( $this->refund_api ) ) {
			$this->refund_api = new ImRefundSoap( $this->partner, array(), Package::get_wsdl_file( 'https://internetmarke.deutschepost.de/OneClickForRefund?wsdl' ) );
		}

		return $this->refund_api;
	}

	/**
	 * @param PostLabel $label
	 *
	 * @return false|int
	 * @throws \Exception
	 */
	public function refund_label( $label ) {
		try {
			$refund = $this->get_refund_api();

			if ( ! $refund ) {
				throw new \Exception( _x( 'Refund API could not be instantiated', 'dhl', 'woocommerce-germanized-dhl' ) );
			}

			$refund_id = $refund->createRetoureId();

			if ( $refund_id ) {
				$user = $refund->authenticateUser( Package::get_internetmarke_username(), Package::get_internetmarke_password() );

				if ( $user ) {
					$transaction_id = $refund->retoureVouchers( $user->getUserToken(), $refund_id, $label->get_shop_order_id() );
				}

				Package::log( sprintf( 'Refunded DP label %s: %s', $label->get_number(), $transaction_id ) );

				return $transaction_id;
			}
		} catch( \Exception $e ) {
			throw new \Exception( sprintf( _x( 'Could not refund post label: %s', 'dhl', 'woocommerce-germanized-dhl' ), $e->getMessage() ) );
		}

		return false;
	}

	/**
	 * @param PostLabel $label
	 *
	 * @return mixed
	 */
	public function delete_label( &$label ) {
		if ( ! empty( $label->get_shop_order_id() ) ) {
			$transaction_id = $this->refund_label( $label );

			/**
			 * Action fires before deleting a Deutsche Post PDF label through an API call.
			 *
			 * @param PostLabel $label The label object.
			 *
			 * @since 3.2.0
			 * @package Vendidero/Germanized/DHL
			 */
			do_action( 'woocommerce_gzd_dhl_post_label_api_before_delete', $label );

			$label->set_number( '' );

			if ( $file = $label->get_file() ) {
				wp_delete_file( $file );
			}

			$label->set_path( '' );

			if ( $file = $label->get_default_file() ) {
				wp_delete_file( $file );
			}

			/**
			 * Action fires after deleting a Deutsche Post PDF label through an API call.
			 *
			 * @param PostLabel $label The label object.
			 *
			 * @since 3.2.0
			 * @package Vendidero/Germanized/DHL
			 */
			do_action( 'woocommerce_gzd_dhl_post_label_api_deleted', $label );

			return $label;
		}

		return false;
	}

	/**
	 * @param PostLabel $label
	 */
	protected function create_label( &$label ) {
		$shipment = $label->get_shipment();

		if ( ! $shipment ) {
			throw new \Exception( sprintf( _x( 'Could not fetch shipment %d.', 'dhl', 'woocommerce-germanized-dhl' ), $label->get_shipment_id() ) );
		}

		$sender_name       = explode( " ", Package::get_setting( 'shipper_name' ) );
		$sender_name_first = $sender_name;
		$sender_first_name = array_splice( $sender_name_first, 0, ( sizeof( $sender_name ) - 1 ) );
		$sender_last_name  = $sender_name[ sizeof( $sender_name ) - 1 ];

		$person_name       = new PersonName( '', '', implode( ' ', $sender_first_name ), $sender_last_name );
		$sender_country    = Package::get_country_iso_alpha3( Package::get_setting( 'shipper_country' ) );

		if ( Package::get_setting( 'shipper_company' ) ) {
			$name = new Name( null, new CompanyName( Package::get_setting( 'shipper_company' ), $person_name ) );
		} else {
			$name = new Name( $person_name, null );
		}

		$address = new Address( '', Package::get_setting( 'shipper_street' ), Package::get_setting( 'shipper_street_no' ), Package::get_setting( 'shipper_postcode' ), Package::get_setting( 'shipper_city' ), $sender_country );
		$sender  = new \baltpeter\Internetmarke\NamedAddress( $name, $address );

		$receiver_person_name = new PersonName( '', '', $shipment->get_first_name(), $shipment->get_last_name() );

		if ( $shipment->get_company() ) {
			$receiver_name = new Name( null, new CompanyName( $shipment->get_company(), $receiver_person_name ) );
		} else {
			$receiver_name = new Name( $receiver_person_name, null );
		}

		$receiver_address = new Address( '', $shipment->get_address_street(), $shipment->get_address_street_number(), $shipment->get_postcode(), $shipment->get_city(), Package::get_country_iso_alpha3( $shipment->get_country() ) );
		$receiver         = new \baltpeter\Internetmarke\NamedAddress( $receiver_name, $receiver_address );
		$address_binding  = new \baltpeter\Internetmarke\AddressBinding( $sender, $receiver );

		if ( ! $this->auth() ) {
			throw new \Exception( $this->get_authentication_error() );
		}

		try {
			$shop_order_id = $this->api->createShopOrderId( $this->get_user()->getUserToken() );

			if ( ! $shop_order_id ) {
				throw new \Exception( _x( 'Error while generating shop order id.', 'dhl', 'woocommerce-germanized-dhl' ) );
			}

			$label->set_shop_order_id( $shop_order_id );

			$order_item = new \baltpeter\Internetmarke\OrderItem( $label->get_dhl_product(), null, $address_binding, new \baltpeter\Internetmarke\Position( 1, 1, 1 ), 'AddressZone' );
			$stamp      = $this->api->checkoutShoppingCartPdf( $this->get_user()->getUserToken(), $label->get_page_format(), array( $order_item ), $label->get_stamp_total(), $shop_order_id, null, true, 2 );

			return $this->update_label( $label, $stamp );
		} catch( \Exception $e ) {
			throw $e;
		}
	}

	/**
	 * @param PostLabel $label
	 * @param \stdClass $stamp
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function update_label( &$label, $stamp ) {
		if ( isset( $stamp->link ) ) {

			$label->set_original_url( $stamp->link );
			$voucher_list = $stamp->shoppingCart->voucherList;

			if ( ! empty( $voucher_list->voucher ) ) {
				foreach ( $voucher_list->voucher as $i => $voucher ) {

					if ( isset( $voucher->trackId ) ) {
						$label->set_number( $voucher->trackId );
					} else {
						$label->set_number( $voucher->voucherId );
					}

					$label->set_voucher_id( $voucher->voucherId );
				}
			}

			if ( isset( $stamp->manifestLink ) ) {
				$label->set_manifest_url( $stamp->manifestLink );
			}

			$label->save();

			$timeout_seconds = 5;

			// Download file to temp dir.
			$temp_file = download_url( $stamp->link, $timeout_seconds );

			if ( is_wp_error( $temp_file ) ) {
				throw new \Exception( _x( 'Error while downloading the PDF stamp.', 'dhl', 'woocommerce-germanized-dhl' ) );
			}

			$file = [
				'name'     => wc_gzd_dhl_generate_label_filename( $label, 'post-label' ),
				'type'     => 'application/pdf',
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			];

			$overrides = [
				'test_type' => false,
				'test_form' => false,
				'test_size' => true,
			];

			// Move the temporary file into the fonts uploads directory.
			Package::set_upload_dir_filter();
			$results = wp_handle_sideload( $file, $overrides );
			Package::unset_upload_dir_filter();

			if ( empty( $results['error'] ) ) {
				$path = Package::get_relative_upload_dir( $results['file'] );

				$label->set_path( $path );
				$label->set_default_path( $path );
			} else {
				throw new \Exception( _x( 'Error while downloading the PDF stamp.', 'dhl', 'woocommerce-germanized-dhl' ) );
			}

			$label->save();
			$this->invalidate_balance();

			return $label;
		} else {
			throw new \Exception( _x( 'Invalid stamp response.', 'dhl', 'woocommerce-germanized-dhl' ) );
		}
	}

	public function update_products() {
		$this->load_products();

		return $this->products->update();
	}
}