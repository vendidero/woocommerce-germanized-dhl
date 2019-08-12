<?php

namespace Vendidero\Germanized\DHL\Admin;
use Vendidero\Germanized\DHL\Package;
use WC_Download_Handler;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin class.
 */
class DownloadHandler {

	public static function download_label( $label_id, $force = false ) {
		if ( current_user_can( 'edit_shop_orders' ) ) {
			if ( $label = wc_gzd_dhl_get_label( $label_id ) ) {
				if ( file_exists( $label->get_file() ) ) {
					if ( $force ) {
						WC_Download_Handler::download_file_force( $label->get_file(), $label->get_filename() );
					} else {
						self::embed( $label->get_file(), $label->get_filename() );
					}
				}
			}
		}
	}

	private static function embed( $file_path, $filename ) {
		if ( ob_get_level() ) {
			$levels = ob_get_level();
			for ( $i = 0; $i < $levels; $i++ ) {
				@ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			}
		} else {
			@ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		}

		wc_nocache_headers();

		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Content-type: application/pdf' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: inline; filename="' . $filename . '";' );
		header( 'Content-Transfer-Encoding: binary' );

		$file_size = @filesize( $file_path ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

		if ( ! $file_size ) {
			return;
		}

		header( 'Content-Length: ' . $file_size );

		@readfile( $file_path );
		exit();
	}
}
