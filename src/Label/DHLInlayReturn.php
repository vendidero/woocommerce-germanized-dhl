<?php

namespace Vendidero\Germanized\DHL\Label;
use DateTimeZone;
use Vendidero\Germanized\Shipments\Shipment;
use WC_Data;
use WC_Data_Store;
use Exception;
use WC_DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * DHL ReturnLabel class.
 */
class DHLInlayReturn extends DHLReturn {

	public function get_type() {
		return 'inlay_return';
	}
}
