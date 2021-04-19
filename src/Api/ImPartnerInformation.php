<?php

namespace Vendidero\Germanized\DHL\Api;

class ImPartnerInformation extends \baltpeter\Internetmarke\PartnerInformation {

	/**
	 * @return array An array of SOAP headers to authenticate the request with the Internetmarke server. Valid for four minutes from `REQUEST_TIMESTAMP`
	 */
	public function soapHeaderArray() {
		$date = new \DateTime( "now", new \DateTimeZone( 'Europe/Berlin' ) );

		return array(
			new \SoapHeader('https://internetmarke.deutschepost.de', 'PARTNER_ID', $this->partnerId ),
			new \SoapHeader('https://internetmarke.deutschepost.de', 'REQUEST_TIMESTAMP', $date->format( 'dmY-His' ) ),
			new \SoapHeader('https://internetmarke.deutschepost.de', 'KEY_PHASE', $this->keyPhase ),
			new \SoapHeader('https://internetmarke.deutschepost.de', 'PARTNER_SIGNATURE', $this->calculateSignature() )
		);
	}
}