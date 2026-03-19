<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function myplugin_get_availabilities( WP_REST_Request $request ) {

	/*
	------------------------------------
	 * 1. Get data from JS request
	 * ------------------------------------ */
	$docKey = sanitize_text_field( $request->get_param( 'docKey' ) );

	if ( empty( $docKey ) ) {
		return new WP_REST_Response(
			array(
				'error' => 'Missing docKey',
			),
			400
		);
	}

	/*
	------------------------------------
	 * 2. Prepare handshake
	 * ------------------------------------ */
	$unixTime = time();
	$myHash   = hash( 'sha256', $unixTime . $docKey );

	$body = array(
		'handshake' => array(
			'time'  => (string) $unixTime,
			'token' => $docKey,
			'hash'  => $myHash,
			'type'  => 'user',
		),
		'calendar'  => 'super',
	);

	/*
	------------------------------------
	 * 3. Call external API
	 * ------------------------------------ */
	$response = wp_remote_request(
		trailingslashit( get_option( 'api_url_short' ) ) . 'doclisting/availabilities',
		array(
			'method'  => 'POST',
			'timeout' => 15,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_REST_Response(
			array(
				'error' => $response->get_error_message(),
			),
			500
		);
	}

	$availabilityDataJson = wp_remote_retrieve_body( $response );
	$availabilityDataObj  = json_decode( $availabilityDataJson );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_REST_Response(
			array(
				'error' => 'Invalid API response',
			),
			500
		);
	}

	/*
	------------------------------------
	 * 4. Your availability logic
	 * ------------------------------------ */
	$walk_in_check     = '';
	$tariffStr         = '';
	$firstAvailibility = '';
	$docSlotsArray     = array();

	// Make sure profile class exists
	global $profileClass;

	if (
		isset( $availabilityDataObj->availabilities ) &&
		! isset( $availabilityDataObj->availabilities->message ) &&
		is_array( $availabilityDataObj->availabilities ) &&
		count( $availabilityDataObj->availabilities ) > 0
	) {

		$docSlotsArray     = $availabilityDataObj->availabilities;
		$firstAvailibility = $profileClass->get_first_avialibility( $docSlotsArray );

		$tariffs   = $profileClass->get_tariffs( $docSlotsArray );
		$tariffStr = d2g_get_tariff_string( $tariffs );

		if (
			! empty( $availabilityDataObj->user_has_inloop ) &&
			! empty( $availabilityDataObj->user_is_active )
		) {
			$walk_in_check = true;
		} else {
			$walk_in_check = false;
		}
	}

	/*
	------------------------------------
	 * 5. Final response
	 * ------------------------------------ */
	$availability_data_set = array(
		'walkin_check'       => $walk_in_check ?: '',
		'tariffs'            => $tariffStr ?: '',
		'first_availibility' => $firstAvailibility ?: '',
		'doc_slots'          => $docSlotsArray,
	);

	return new WP_REST_Response(
		array(
			'success' => true,
			'data'    => $availability_data_set,
		),
		200
	);
}
