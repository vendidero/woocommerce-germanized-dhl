<?php
/**
 * Initializes blocks in WordPress.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DHL\Api;

use Vendidero\Germanized\DHL\Package;
use Exception;

defined( 'ABSPATH' ) || exit;

class AuthRest {

    /**
     * define Auth API endpoint
     */
    const PR_DHL_REST_AUTH_END_POINT = '/account/v1/auth/accesstoken';

    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @var PR_DHL_API_Auth_REST
     */
    private static $_instance; //The single instance

    /**
     * constructor.
     */
    private function __construct() {}

    // Magic method clone is empty to prevent duplication of connection
    private function __clone() {}

    // Stopping unserialize of object
    private function __wakeup() {}

    public static function get_instance( ) {
        if ( ! self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return string
     */
    public function get_access_token( $client_id, $client_secret ) {
        if ( empty( $this->access_token ) ) {
            try {
                $this->request_access_token( $client_id, $client_secret );
            } catch (Exception $e) {
                throw $e;
            }
        }

        return $this->access_token;
    }

    public function delete_access_token() {
        Package::log( 'Delete Transient - Access Token' );
        delete_transient( '_dhl_auth_token_rest' );
    }

    /**
     * @param string $access_token
     */
    protected function set_access_token( $access_token, $expires_in = 0, $token_type = '', $token_scope = '' ) {

        if ( ! empty( $expires_in ) ) {
            set_transient( '_dhl_auth_token_rest', $access_token, $expires_in );
            Package::log( 'Set Transient - Access Token' );
        }

        $this->access_token = $access_token;
    }

    /**
     * Get the signed URL.
     * The signed URL is fetched by doing an OAuth request.
     *
     * @throws Exception
     *
     * @return String
     */
    protected function request_access_token( $client_id, $client_secret ) {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;

        Package::log( 'Authorize User - Client ID: ' . $this->client_id );

        $wp_request_headers = array( 'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ) );
        $wp_request_url     = Package::get_rest_url() . self::PR_DHL_REST_AUTH_END_POINT;

        Package::log( 'Authorization URL: ' . $wp_request_url );

        $wp_auth_response   = wp_remote_get( $wp_request_url, array( 'headers' => $wp_request_headers ) );
        $response_code      = wp_remote_retrieve_response_code( $wp_auth_response );
        $response_body      = json_decode( wp_remote_retrieve_body( $wp_auth_response ) );

        Package::log( 'Authorization Response: ' . $response_code );

        switch ( $response_code ) {
            case '200':
                $this->set_access_token( $response_body->access_token, $response_body->expires_in, $response_body->token_type, $response_body->scope );
                break;
            case '401':
            default:
                throw new Exception( __( 'Authentication failed: Please check client ID and secret in the DHL shipping settings', 'woocommerce-germanized-dhl' ) );
                break;
        }

        return true;
    }

    public function is_key_match( $client_id, $client_secret ) {
        if ( ( (string) $this->client_id === (string) $client_id ) && ( (string) $this->client_secret === (string) $client_secret ) ) {
            return true;
        } else {
            return false;
        }
    }
}
