<?php
/**
 * REST route that doctor2go.online (WCC) calls to create or update a doctor.
 *
 * POST /wp-json/d2gc/v1/doctor-sync
 * Body (JSON): timestamp, user_key, hash = sha256( timestamp . '_' . user_key . '_' . organisation API token ), and the
 * doctor data (user_email, user_id, user_first_name, ...). It replaces the old endpoints/doctor.php, the body is the same.
 *
 * Response (JSON): { "message": "..." } on success, { "errors": [ "..." ] } on failure, with a matching HTTP status.
 *
 * @package d2g-connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class D2GC_Rest {

	const REST_NAMESPACE = 'd2gc/v1';

	/** Option with the last requests to the doctor sync route (shown on the settings page, Advanced tab). */
	const LOG_OPTION = 'd2gc_doctor_sync_log';
	const LOG_MAX    = 50;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'format_errors' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'log_request' ), 20, 3 );
	}

	/**
	 * The JSON body, also when it is not sent with Content-Type: application/json (the old endpoints/doctor.php read
	 * php://input as JSON regardless of the header). Form fields as a last resort.
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public static function get_payload( $request ) {
		$data = $request->get_json_params();
		if ( ! is_array( $data ) || ! $data ) {
			$data = json_decode( (string) $request->get_body(), true );
		}
		if ( ! is_array( $data ) || ! $data ) {
			$data = $request->get_body_params();
		}
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Logs every request to the doctor sync route: always a summary (time, IP, Content-Type, status, error), with
	 * "Activate debug logging" (d2g_debug) also the request body (secrets masked) and a line in the PHP error log.
	 *
	 * @param WP_HTTP_Response $response
	 * @param WP_REST_Server   $server
	 * @param WP_REST_Request  $request
	 * @return WP_HTTP_Response
	 */
	public static function log_request( $response, $server, $request ) {
		if ( 0 !== strpos( $request->get_route(), '/' . self::REST_NAMESPACE . '/doctor-sync' ) ) {
			return $response;
		}

		$payload = self::get_payload( $request );
		$data    = $response->get_data();
		$debug   = 1 === (int) get_option( 'd2g_debug' );

		$entry = array(
			'time'         => current_time( 'mysql' ),
			'method'       => $request->get_method(),
			'ip'           => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'content_type' => (string) $request->get_header( 'content_type' ),
			'body_bytes'   => strlen( (string) $request->get_body() ),
			'status'       => $response->get_status(),
			'code'         => is_array( $data ) && isset( $data['code'] ) ? (string) $data['code'] : '',
			'result'       => is_array( $data ) ? ( isset( $data['errors'] ) ? implode( ' | ', (array) $data['errors'] ) : (string) ( $data['message'] ?? '' ) ) : '',
			'user_email'   => isset( $payload['user_email'] ) && is_scalar( $payload['user_email'] ) ? (string) $payload['user_email'] : '',
			'wcc_user_id'  => isset( $payload['user_id'] ) && is_scalar( $payload['user_id'] ) ? (string) $payload['user_id'] : '',
			'keys'         => implode( ', ', array_keys( $payload ) ),
		);
		if ( $debug ) {
			$entry['payload']  = self::mask_secrets( $payload );
			$entry['response'] = $data;
			error_log( 'D2GC doctor-sync: ' . wp_json_encode( $entry ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- only with "Activate debug logging"
		}

		$log = get_option( self::LOG_OPTION, array() );
		$log = is_array( $log ) ? $log : array();
		array_unshift( $log, $entry );
		update_option( self::LOG_OPTION, array_slice( $log, 0, self::LOG_MAX ), false );

		return $response;
	}

	/**
	 * @param array $payload
	 * @return array the payload with hash, password and keys shortened
	 */
	private static function mask_secrets( $payload ) {
		foreach ( $payload as $key => $value ) {
			if ( is_array( $value ) ) {
				$payload[ $key ] = self::mask_secrets( $value );
			} elseif ( is_scalar( $value ) && preg_match( '/hash|pass|token|secret|key$/i', (string) $key ) && '' !== (string) $value ) {
				$value           = (string) $value;
				$payload[ $key ] = strlen( $value ) > 8 ? substr( $value, 0, 4 ) . '…' . substr( $value, -2 ) . ' (' . strlen( $value ) . ')' : '***';
			}
		}
		return $payload;
	}

	/**
	 * @return array the logged requests, newest first
	 */
	public static function get_log() {
		$log = get_option( self::LOG_OPTION, array() );
		return is_array( $log ) ? $log : array();
	}

	/**
	 * Errors of this namespace (also the ones from the permission check) in the same format: { code, errors: [] }.
	 *
	 * @param WP_HTTP_Response $response
	 * @param WP_REST_Server   $server
	 * @param WP_REST_Request  $request
	 * @return WP_HTTP_Response
	 */
	public static function format_errors( $response, $server, $request ) {
		if ( 0 !== strpos( $request->get_route(), '/' . self::REST_NAMESPACE . '/' ) || $response->get_status() < 400 ) {
			return $response;
		}
		$data = $response->get_data();
		if ( is_array( $data ) && isset( $data['message'] ) && ! isset( $data['errors'] ) ) {
			$response->set_data(
				array(
					'code'   => isset( $data['code'] ) ? $data['code'] : 'd2gc_error',
					'errors' => array( $data['message'] ),
				)
			);
		}
		return $response;
	}

	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/doctor-sync',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'doctor_sync' ),
				'permission_callback' => array( __CLASS__, 'check_doctor_sync_request' ),
			)
		);
	}

	/**
	 * The URL WCC has to call (shown on the settings page).
	 *
	 * @return string
	 */
	public static function get_doctor_sync_url() {
		return rest_url( self::REST_NAMESPACE . '/doctor-sync' );
	}

	/**
	 * Only requests signed with the organisation API token and with a recent timestamp.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public static function check_doctor_sync_request( $request ) {
		$data      = self::get_payload( $request );
		$super_key = (string) get_option( 'd2gc_wcc_token' );

		if ( '' === $super_key ) {
			return new WP_Error( 'd2gc_not_configured', 'The organisation API token is not set on this website.', array( 'status' => 503 ) );
		}
		if ( empty( $data['timestamp'] ) || empty( $data['user_key'] ) || empty( $data['hash'] ) || ! is_string( $data['hash'] ) ) {
			return new WP_Error( 'd2gc_missing_auth', 'timestamp, user_key and hash are required.', array( 'status' => 400 ) );
		}

		$expected = hash( 'sha256', $data['timestamp'] . '_' . $data['user_key'] . '_' . $super_key );
		if ( ! hash_equals( $expected, $data['hash'] ) ) {
			return new WP_Error( 'd2gc_invalid_hash', 'The hash is incorrect for the doctor sync request.', array( 'status' => 403 ) );
		}

		/**
		 * Filters how old (in seconds, both directions for clock differences) the timestamp of a doctor sync request may
		 * be. 0 switches the check off.
		 *
		 * @param int $max_age
		 */
		$max_age = (int) apply_filters( 'd2gc_doctor_sync_max_age', 15 * MINUTE_IN_SECONDS );
		if ( $max_age > 0 ) {
			$time = self::parse_timestamp( $data['timestamp'] );
			if ( null === $time ) {
				return new WP_Error( 'd2gc_invalid_timestamp', 'The timestamp could not be read.', array( 'status' => 400 ) );
			}
			if ( abs( time() - $time ) > $max_age ) {
				return new WP_Error( 'd2gc_expired', 'The request is too old (or the clocks differ too much).', array( 'status' => 403 ) );
			}
		}

		return true;
	}

	/**
	 * Unix seconds, unix milliseconds or a date string (e.g. ISO 8601).
	 *
	 * @param mixed $timestamp
	 * @return int|null
	 */
	private static function parse_timestamp( $timestamp ) {
		if ( is_numeric( $timestamp ) ) {
			$time = (int) $timestamp;
			return $time > 100000000000 ? (int) floor( $time / 1000 ) : $time;
		}
		if ( is_string( $timestamp ) ) {
			$time = strtotime( $timestamp );
			return false === $time ? null : $time;
		}
		return null;
	}

	/**
	 * Creates or updates the doctor (see D2G_doc_user_profile::d2gc_sync_doctor_from_wcc()).
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public static function doctor_sync( $request ) {
		try {
			$result = D2G_doc_user_profile::d2gc_sync_doctor_from_wcc( self::get_payload( $request ) );
		} catch ( Throwable $e ) {
			$result = new WP_Error( 'd2gc_exception', $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ')', array( 'status' => 500 ) );
		}

		if ( is_wp_error( $result ) ) {
			$data = $result->get_error_data();
			return new WP_REST_Response(
				array(
					'code'   => $result->get_error_code(),
					'errors' => $result->get_error_messages(),
				),
				is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 500
			);
		}

		return new WP_REST_Response( $result, 200 );
	}
}
