<?php

namespace Vendidero\Germanized\DHL\Admin;

use Vendidero\Germanized\DHL\Package;

defined( 'ABSPATH' ) || exit;

class Status {

	public static function init() {
		add_filter( 'woocommerce_admin_status_tabs', array( __CLASS__, 'register_tab' ), 10 );
		add_action( 'woocommerce_admin_status_content_dhl', array( __CLASS__, 'render' ) );
	}

	public static function render() {
		?>
		<table class="wc_status_table widefat" cellspacing="0" id="status">
			<thead>
				<tr>
					<th colspan="3" data-export-label="Post & DHL Ping status" style="">
                        <h2><?php echo esc_html_x( 'Ping Check', 'dhl', 'woocommerce-germanized-dhl' ); ?></h2>
                    </th>
				</tr>
			</thead>
			<tbody>
			<?php foreach( self::get_urls_to_ping() as $url ) :
				$result = self::test_url( $url );
				?>
				<tr>
					<td style="width: 50%" data-export-label="<?php echo esc_attr( $url ); ?>"><code style=""><?php echo $url; ?></code></td>
					<td>
						<?php
							if ( $result ) {
								echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
							} else {
								echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html_x( 'Unable to connect to the URL. Please make sure that your webhost allows outgoing connections to that specific URL.', 'dhl', 'woocommerce-germanized-dhl' ) . '</mark>';
							}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public static function register_tab( $tabs ) {
		$tabs['dhl'] = _x( 'DHL & Post', 'dhl', 'woocommerce-germanized-dhl' );

		return $tabs;
	}

	protected static function test_url( $url ) {
		$transient_name    = 'woocommerce_gzd_dhl_test_remote_get_' . $url;
		$get_response_code = get_transient( $transient_name );

		if ( false === $get_response_code || is_wp_error( $get_response_code ) ) {
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {
				$get_response_code = $response['response']['code'];
			}

			set_transient( $transient_name, $get_response_code, HOUR_IN_SECONDS );
		}

		$get_response_successful = ! is_wp_error( $get_response_code ) && ! empty( $get_response_code );

		return $get_response_successful;
	}

	public static function get_urls_to_ping() {
		$urls = array();

		if ( Package::is_dhl_enabled() ) {
			$urls = array_merge( $urls, array(
				Package::get_gk_api_url(),
				Package::get_rest_url(),
				Package::get_cig_url(),
			) );
		}

		if ( Package::is_internetmarke_enabled() ) {
			$urls = array_merge( $urls, array(
				Package::get_warenpost_international_rest_url(),
				Package::get_internetmarke_main_url(),
				Package::get_internetmarke_refund_url(),
				Package::get_internetmarke_products_url()
			) );
		}

		return $urls;
	}
}