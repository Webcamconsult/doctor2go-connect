<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Booking Ajax actions.
 *
 * @package d2g-connect
 */
class D2G_booking_wcc_user {
	public static function init() {

		// create appointment
		add_action( 'wp_ajax_d2g_create_wcc_appointment', array( __CLASS__, 'd2g_create_wcc_appointment' ) );
		add_action( 'wp_ajax_nopriv_d2g_create_wcc_appointment', array( __CLASS__, 'd2g_create_wcc_appointment' ) );

		// delete appointment
		add_action( 'wp_ajax_d2g_delete_wcc_appointment', array( __CLASS__, 'd2g_delete_wcc_appointment' ) );
		add_action( 'wp_ajax_nopriv_d2g_delete_wcc_appointment', array( __CLASS__, 'd2g_delete_wcc_appointment' ) );

		// walk in appointment
		add_action( 'wp_ajax_d2g_create_wcc_walkin', array( __CLASS__, 'd2g_create_wcc_walkin' ) );
		add_action( 'wp_ajax_nopriv_d2g_create_wcc_walkin', array( __CLASS__, 'd2g_create_wcc_walkin' ) );

		// written consult
		add_action( 'wp_ajax_d2g_create_wcc_written_cosnsult', array( __CLASS__, 'd2g_create_wcc_written_cosnsult' ) );
		add_action( 'wp_ajax_nopriv_d2g_create_wcc_written_cosnsult', array( __CLASS__, 'd2g_create_wcc_written_cosnsult' ) );
	}

	/*
	* this function creates an appointment in the D2G-application
	*/
	public static function d2g_create_wcc_appointment() {

		$nonce = isset( $_POST['_wpnonce'] )
			? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'booking' ) ) {
			return false;
		}

		// reCAPTCHA: validate, unslash, sanitize, and also REMOTE_ADDR.
		if ( get_option( 'd2g_recaptcha_site_key' ) !== '' ) {
			$secret_key = get_option( 'd2g_recaptcha_secret_key' ); // Your reCAPTCHA secret key.

			$recaptcha_response = isset( $_POST['g-recaptcha-response'] )
				? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
				: '';

			$remote_addr = isset( $_SERVER['REMOTE_ADDR'] )
				? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
				: '';

			$recaptcha_verify = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'body' => array(
						'secret'   => $secret_key,
						'response' => $recaptcha_response,
						'remoteip' => $remote_addr,
					),
				)
			);

			$recaptcha_result = json_decode( wp_remote_retrieve_body( $recaptcha_verify ) );

			if ( empty( $recaptcha_result ) || empty( $recaptcha_result->success ) ) {
				$errors[] = __( 'CAPTCHA verification failed. Please try again.', 'doctor2go-connect' );
				return false;
			}
		}

		// Document ID: usually an integer.
		$wpDocID = isset( $_POST['wp_doc_id'] )
			? absint( wp_unslash( $_POST['wp_doc_id'] ) )
			: 0;

		$docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
		$docKey    = get_post_meta( $wpDocID, 'user_key', true );
		$docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );

		// Text fields.
		$message          = d2g_get_post_text( 'comment' );
		$appointment_date = d2g_get_post_text( 'start' );
		$endDate          = d2g_get_post_text( 'end' );
		$patientEmail     = d2g_get_post_text( 'email' );
		$patientTel       = d2g_get_post_text( 'p_tel' );
		$patient_fname    = d2g_get_post_text( 'patient_fname' );
		$patient_lname    = d2g_get_post_text( 'patient_lname' );
		$location_id      = d2g_get_post_text( 'location_id' );
		$docPrice         = d2g_get_post_text( 'docPrice' );
		$currency         = d2g_get_post_text( 'currency' );
		$vat              = d2g_get_post_text( 'vat' );
		$questionnaire_id = d2g_get_post_text( 'questionnaire_id' );

		// Language from locale (not from user input).
		$language = sanitize_text_field( wp_unslash( explode( '_', get_locale() )[0] ) );
		$currLang = explode( '_', get_locale() )[0];

		$userAction = '';
		$payCheck   = 'true';

		// get current user
		$currUser = wp_get_current_user();

		if ( $currUser->ID != 0 ) {

			// saves the name when account data is incomplete
			$user_action = isset( $_POST['user_action'] ) ? sanitize_text_field( wp_unslash( $_POST['user_action'] ) ) : '';
			$wp_user_id  = isset( $_POST['wp_user_id'] ) ? absint( wp_unslash( $_POST['wp_user_id'] ) ) : 0;

			if ( 'update_user' === $user_action && $wp_user_id > 0 ) {
				update_user_meta( $wp_user_id, 'first_name', $patient_fname );
				update_user_meta( $wp_user_id, 'last_name', $patient_lname );
				update_user_meta( $wp_user_id, 'p_tel', $patientTel );
			}

			$userMeta = get_user_meta( $wp_user_id );

			// get client tokens
			$ids    = unserialize( $userMeta['ids'][0] );
			$tokens = unserialize( $userMeta['tokens'][0] );

			// check if user has necessary tokesn and id's
			if ( ! isset( $ids[ $docOrgKey ] ) ) {
				// check if user excists in the organisation
				$client = json_decode( self::d2g_get_wcc_client_by_mail( $patientEmail, $docOrgKey ) );
				if ( ! isset( $client->authentication_token ) ) {
					// user was not found in WCC or has no auth_token and needs to be created
					$userMeta['first_name'][0] = $patient_fname;
					$userMeta['last_name'][0]  = $patient_lname;
					$client                    = self::d2g_create_wcc_client_new( $currUser, $userMeta, $docKey, $patientEmail, $patientTel, $docOrgKey );
				}

				$wcc_client_id = $client->_id;
				$client_token  = $client->authentication_token;

				// update the list of client id's based on organisation wcc
				$ids               = unserialize( get_user_meta( $currUser->ID )['ids'][0] );
				$ids[ $docOrgKey ] = $client->_id;
				update_user_meta( $currUser->ID, 'ids', $ids );
				// update the list of client tokens based on organisation from wcc
				$tokens               = unserialize( get_user_meta( $currUser->ID )['tokens'][0] );
				$tokens[ $docOrgKey ] = $client->authentication_token;
				update_user_meta( $currUser->ID, 'tokens', $tokens );

			} else {
				// user is found with tokens and id's in wp database
				$wcc_client_id = $ids[ $docOrgKey ];
				$client_token  = $tokens[ $docOrgKey ];
			}
		} else {

			// check if user excists in the organisation
			$client = json_decode( self::d2g_get_wcc_client_by_mail( $patientEmail, $docOrgKey ) );

			if ( ! isset( $client->authentication_token ) ) {
				// user was not found in WCC or has no auth_token and needs to be created
				$userMeta['first_name'][0] = $patient_fname;
				$userMeta['last_name'][0]  = $patient_lname;
				$client                    = self::d2g_create_wcc_client_new( $currUser, $userMeta, $docKey, $patientEmail, $patientTel, $docOrgKey );
			}

			$wcc_client_id = $client->_id;
			$client_token  = $client->authentication_token;

		}

		$myTime   = new DateTime();
		$unixTime = $myTime->format( 'U' );
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$postfields = array(
			'appointment' => array(
				'date'             => $appointment_date,
				'client_id'        => $wcc_client_id,
				'end_date'         => $endDate,
				'payment_price'    => $docPrice,
				'payment_vat'      => $vat,
				'language'         => $currLang,
				'payment_currency' => $currency,
				'use_payment'      => $payCheck,
				'user_id'          => $docWCC_ID,
				'location_id'      => $location_id,

				'custom_message'   => $message,
			),
			'handshake'   => array(
				'time'  => $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
		);

		if ( $questionnaire_id == '' && get_option( 'd2g_use_default_questionnaire' ) != 1 ) {
			$postfields['appointment']['questionnaire_id'] = 'false';
		} elseif ( $questionnaire_id != '' && get_option( 'd2g_use_default_questionnaire' ) != 1 ) {
			$postfields['appointment']['questionnaire_id'] = $questionnaire_id;
		}

		$response = wp_remote_post(
			get_option( 'api_url_short' ) . 'doclisting/appointments/',
			array(
				'method'      	=> 'POST',
				'headers'     	=> array(
				'Content-Type' 	=> 'application/json',
				),
				'body'        => wp_json_encode( $postfields ),
				'timeout'     => 30,
				'redirection' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'API Error: ' . $response->get_error_message() );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Import error logging.
			wp_send_json( 'error' );
			wp_die();
		}

		$body        = wp_remote_retrieve_body( $response );
		$appointment = json_decode( $body, true ); // <-- JSON decoded here

		if ( isset( $appointment['_id'] ) ) {

			$booking_data = array(
				'appointment_id'   => $appointment['_id'],
				'questionnaire_id' => $appointment['questionnaire_id'],
				'user_action'      => $userAction,
				'client_token'     => $client_token,
			);

			/*
			make this configurable later
			if($appointment->location_id == NULL){
				$booking_data['send_to_payment'] = true;
			}*/

			wp_send_json( $booking_data );

		} else {
			wp_send_json( 'error' );
		}

		wp_die();
	}

	/*
	* this function is used to delete an appointment in the D2G software
	*/
	public static function d2g_delete_wcc_appointment() {

		// Nonce check
		check_ajax_referer( 'd2g_delete_wcc_appointment_nonce', 'security' );

		if ( ! isset( $_POST['wcc_user_id'], $_POST['app_id'] ) ) {
			return;
		}

		$wcc_user_id = sanitize_text_field( wp_unslash( $_POST['wcc_user_id'] ) );
		$app_id      = sanitize_text_field( wp_unslash( $_POST['app_id'] ) );

		$docObj = self::d2g_get_doctor_by_wcc_id( $wcc_user_id )[0];

		$orgKey = get_post_meta( $docObj->ID, 'organisation_key', true );

		$response = wp_remote_request(
			get_option( 'api_url_short' ) . 'appointments/' . $app_id . '.json',
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'Authorization' => 'Token token=' . sanitize_text_field( $orgKey ),
				),
				'timeout' => 10,
			)
		);
		if ( is_wp_error( $response ) ) {
			echo esc_html__( 'Your appointment cloud not be canceled. Please contact your doctor.', 'doctor2go-connect' );
			wp_die();
		}

		$body     = wp_remote_retrieve_body( $response );
		$response = json_decode( $body );

		if ( isset( $response->message ) && $response->message === 'Your appointment was destroyed.' ) {
			$message = __( 'Your appointment has been canceled.', 'doctor2go-connect' );
		} else {
			$message = __( 'Your appointment cloud not be canceled. Please contact your doctor.', 'doctor2go-connect' );
		}

		echo esc_html( $message );
		wp_die();
	}



	// this retrieves a URL for the walk-in appointment
	// if success user gets redirected to the doctor waiting room
	public static function d2g_create_wcc_walkin() {

		if ( ! isset( $_POST['walkin_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['walkin_form_nonce'] ) ), 'walkin_form_action' ) ) {
			return false; // stop processing immediately
		}

		// Validate CAPTCHA
		$secret_key = get_option( 'd2g_recaptcha_secret_key' );
		if ( $secret_key !== '' ) {

			$recaptcha_response = '';
			if ( ! empty( $_POST['g-recaptcha-response'] ) ) {
				$recaptcha_response = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
			}

			$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
				? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
				: '';

			$recaptcha_verify = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'body'    => array(
						'secret'   => $secret_key,
						'response' => $recaptcha_response,
						'remoteip' => $remote_ip,
					),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $recaptcha_verify ) ) {
				return false;
			}

			$recaptcha_result = json_decode( wp_remote_retrieve_body( $recaptcha_verify ) );

			if ( empty( $recaptcha_result ) || ! $recaptcha_result->success ) {
				return false;
			}
		}

		$wpDocID        = isset( $_POST['wp_doc_id'] ) ? absint( wp_unslash( $_POST['wp_doc_id'] ) ) : 0;
		$client_name    = isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '';
		$client_email   = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$client_tel     = isset( $_POST['optie_telefoonnummer'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_telefoonnummer'] ) ) : '';
		$client_bday    = isset( $_POST['optie_geboortedatum'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_geboortedatum'] ) ) : '';
		$client_gender  = isset( $_POST['optie_aanhef'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_aanhef'] ) ) : '';
		$client_country = isset( $_POST['optie_land'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_land'] ) ) : '';
		$client_reason  = isset( $_POST['optie_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_reason'] ) ) : '';

		$currLang = explode( '_', get_locale() )[0];

		$docKey    = get_post_meta( $wpDocID, 'user_key', true );
		$docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
		$docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );
		$orgSlug   = get_post_meta( $wpDocID, 'organisation_subdomain', true ) . '.';
		$price     = get_post_meta( $wpDocID, 'walk_in_price', true );
		$currency  = get_post_meta( $wpDocID, 'walk_in_currency', true );
		$baseUrl   = get_option( 'wcc_base_url' );

		$unixTime = time();
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$payload = array(
			'consultant_id'        => (string) $docWCC_ID,
			'requires_payment'     => 'true',
			'payment_price'        => (string) $price,
			'payment_currency'     => (string) $currency,
			'client_email'         => $client_email,
			'client_name'          => $client_name,
			'language'             => $currLang,
			'optie_telefoonnummer' => $client_tel,
			'optie_geboortedatum'  => $client_bday,
			'optie_aanhef'         => $client_gender,
			'optie_land'           => $client_country,
			'optie_reason'         => $client_reason,
			'handshake'            => array(
				'time'  => (string) $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
		);

		$response = wp_remote_post(
			get_option( 'api_url_short' ) . 'doclisting/inloopspreekuur',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_die( esc_html__( 'There has been an error.', 'doctor2go-connect' ) );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		// get current user
		$currUser = wp_get_current_user();

		if ( isset( $response_body->client->id ) && $currUser->ID !== 0 ) {
			$client = $response_body->client;

			$ids    = (array) get_user_meta( $currUser->ID, 'ids', true );
			$tokens = (array) get_user_meta( $currUser->ID, 'tokens', true );

			if ( ! isset( $tokens[ $docOrgKey ] ) ) {
				$ids[ $docOrgKey ]    = $client->id;
				$tokens[ $docOrgKey ] = $client->authentication_token;

				update_user_meta( $currUser->ID, 'ids', $ids );
				update_user_meta( $currUser->ID, 'tokens', $tokens );
			}
		}

		if ( isset( $response_body->url ) ) {
			$waiting_room_full_url = 'https://' . $orgSlug . $baseUrl . $response_body->url;
			wp_send_json_success( array( 'redirect_url' => $waiting_room_full_url ) );
		}

		wp_die( esc_html__( 'There has been an error.', 'doctor2go-connect' ) );
	}




	// this retrieves a URL for the walk-in appointment
	// if success user gets redirected to the doctor waiting room
	public static function d2g_create_wcc_written_cosnsult() {

		if ( ! isset( $_POST['email_advice_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['email_advice_form_nonce'] ) ), 'email_advice_form_action' ) ) {
			return false; // stop processing immediately
		}

		// Validate CAPTCHA
		$secret_key = get_option( 'd2g_recaptcha_secret_key' );
		if ( $secret_key !== '' ) {
			$recaptcha_response = isset( $_POST['g-recaptcha-response'] )
				? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
				: '';

			$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
				? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
				: '';

			$recaptcha_verify = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'body'    => array(
						'secret'   => $secret_key,
						'response' => $recaptcha_response,
						'remoteip' => $remote_ip,
					),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $recaptcha_verify ) ) {
				return false;
			}

			$recaptcha_result = json_decode( wp_remote_retrieve_body( $recaptcha_verify ) );

			if ( empty( $recaptcha_result ) || ! $recaptcha_result->success ) {
				return false;
			}
		}

		$wpDocID      	= isset( $_POST['wp_doc_id'] ) ? absint( wp_unslash( $_POST['wp_doc_id'] ) ) : 0;
		$first_name   	= isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name    	= isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$client_email 	= isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$client_gender  = isset( $_POST['client_gender'] ) ? sanitize_text_field( wp_unslash( $_POST['client_gender'] ) ) : '';
		$complaint		= isset( $_POST['complaint_description'] ) ? sanitize_text_field( wp_unslash( $_POST['complaint_description'] ) ) : '';
		$client_bday 	= isset($_POST['option_bday']) ? sanitize_text_field(wp_unslash($_POST['option_bday'])) : '';
		if ($client_bday && ($dt = DateTime::createFromFormat('d/m/Y', $client_bday))) $client_bday = $dt->format('Y-m-d'); else $client_bday = '';

		//images
		$image_1 = $image_2 = $image_3 = '';
		$allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];

		foreach (['image_1','image_2','image_3'] as $img) {
			if (!empty($_FILES[$img]['tmp_name']) && $_FILES[$img]['error'] === UPLOAD_ERR_OK) {
				$file_type = wp_check_filetype_and_ext($_FILES[$img]['tmp_name'], $_FILES[$img]['name']);
				if (!empty($file_type['type']) && in_array($file_type['type'], $allowed_mimes, true)) {
					$imgType = mime_content_type($_FILES[$img]['tmp_name']); // bv. image/png
					${$img} = 'data:' . $imgType . ';base64,' . base64_encode(file_get_contents($_FILES[$img]['tmp_name']));
				}
			}
		}

		/*maybe for later
		$type         = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		*/

		$docKey    = get_post_meta( $wpDocID, 'user_key', true );
		$docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
		$docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );
		$orgSlug   = get_post_meta( $wpDocID, 'organisation_subdomain', true ) . '.';
		$baseUrl   = get_option( 'wcc_base_url' );
		$price     = get_post_meta( $wpDocID, 'written_con_price', true );
		$currency  = get_post_meta( $wpDocID, 'written_con_currency', true );

		$unixTime = time();
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$d2gAdmin     = new D2G_doc_user_profile();
		$currLang     = explode( '_', get_locale() )[0];
		$confirmation_url = $d2gAdmin::d2g_page_url( $currLang, 'email_advice_confirmation', false );

		$currUser        = wp_get_current_user();
		$require_payment = (float) $price > 0;

		$payload = array(
			'consultant_id'      	=> (string) $docWCC_ID,
			'requires_payment'   	=> (string) $require_payment,
			'payment_price'      	=> (string) $price,
			'payment_currency'   	=> (string) $currency,
			'client_email'       	=> $client_email,
			'optie_naam'         	=> $last_name,
			'optie_first_name'   	=> $first_name,
			'optie_aanhef'       	=> $client_gender,
			'optie_geboortedatum'	=> $client_bday,
			'language'           	=> $currLang,
			'complaint_desc'        => $complaint,
			'image_1' 				=> $image_1,
			'image_2' 				=> $image_2,
			'image_3' 				=> $image_3,
			'handshake'          => array(
				'time'  => (string) $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
		);

		$response = wp_remote_post(
			get_option( 'api_url_short' ) . 'doclisting/written_consult_complete',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			$response_body = json_decode( $response  );
			nice_dump($response_body);
			wp_die( esc_html__( 'There has been an error.', 'doctor2go-connect' ) );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $response_body->client->id ) && is_user_logged_in() ) {
			$client = $response_body->client;

			$ids    = (array) get_user_meta( $currUser->ID, 'ids', true );
			$tokens = (array) get_user_meta( $currUser->ID, 'tokens', true );

			if ( ! isset( $tokens[ $docOrgKey ] ) ) {
				$ids[ $docOrgKey ]    = $client->id;
				$tokens[ $docOrgKey ] = $client->authentication_token;

				update_user_meta( $currUser->ID, 'ids', $ids );
				update_user_meta( $currUser->ID, 'tokens', $tokens );
			}
		}

		if ( isset( $response_body->url ) ) {
			$questionnaire_url = 'https://' . $orgSlug . $baseUrl . $response_body->url;
			$redirect_url = $questionnaire_url . '?redirect_url=' . urlencode($confirmation_url) . '&booked_consult=email&skip_cookie_wall=true';
			wp_send_json_success( array( 'redirect_url' => $redirect_url ) );
		}

		wp_die( esc_html__( 'There has been an error.', 'doctor2go-connect' ) );
	}



	/**
	 * @param $currUser
	 * @param $userMeta
	 * @param $docOrgKey
	 * @param $email
	 * @return mixed
	 */
	protected static function d2g_create_wcc_client_new(
		$currUser,
		$userMeta,
		$docKey,
		$email,
		$mobile_number,
		$docOrgKey
	) {

		if ( $currUser->ID == 0 ) {
			$d1           = new DateTime();
			$currUser->ID = 'anonymous-' . $d1->format( 'U' );
		}

		$timeZone = ! empty( $userMeta['p_timezone'][0] )
			? $userMeta['p_timezone'][0]
			: get_user_timezone();

		$language = explode( '_', get_locale() )[0];

		$unixTime = time();
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$payload = array(
			'client'    => array(
				'email'          => sanitize_email( $email ),
				'mobile_number'  => sanitize_text_field( $mobile_number ),
				'first_name'     => sanitize_text_field( $userMeta['first_name'][0] ?? '' ),
				'last_name'      => sanitize_text_field( $userMeta['last_name'][0] ?? '' ),
				'time_zone'      => $timeZone,
				'language'       => $language,
				'date_of_birth'  => '2000-01-01T00:00:00.000+01:00',
				'reference_code' => 'DL-' . $currUser->ID,
			),
			'handshake' => array(
				'time'  => (string) $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
		);

		$response = wp_remote_post(
			get_option( 'api_url_short' ) . 'doclisting/create_client',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$client = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! $client ) {
			return false;
		}

		if ( $currUser->ID != 0 ) {
			// update the list of client tokens from organisations
			$ids               = unserialize( get_user_meta( $currUser->ID )['ids'][0] );
			$ids[ $docOrgKey ] = $client->_id;
			update_user_meta( $currUser->ID, 'ids', $ids );

			$tokens               = unserialize( get_user_meta( $currUser->ID )['tokens'][0] );
			$tokens[ $docOrgKey ] = $client->authentication_token;
			update_user_meta( $currUser->ID, 'tokens', $tokens );
		}

		return $client;
	}


	/**
	 * @param $email
	 * @param $token
	 * @return mixed
	 */
	protected static function d2g_get_wcc_client_by_mail( $email, $token ) {

		$response = wp_remote_post(
			get_option( 'api_url_short' ) . 'clients/get_client_by_email',
			array(
				'headers' => array(
					'Authorization' => 'Token token=' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'email' => $email,
					)
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}



	/*
	* create patient user during booking process
	*/
	protected static function d2g_create_patient_user( $user_data ) {

		$user_login = 'test-patient';

		$user_input = array(
			'user_login'   => $user_login,
			'user_pass'    => $user_data['pass'],
			'user_email'   => $user_data['user_email'],
			'first_name'   => $user_data['user_first_name'],
			'last_name'    => $user_data['user_last_name'],
			'display_name' => $user_data['user_full_name'],
			'role'         => 'patient',
		);

		$user = wp_insert_user( $user_input );

		update_user_meta( $user, 'p_tel', $user_data['p_tel'] );

		// $response = programmatic_login( $user_login );

		wp_send_json_success( $user );
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	protected static function d2g_clean_name( $string ) {
		$string = str_replace( ' ', '', $string ); // Replaces all spaces with hyphens.
		$string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Removes special chars.
		$string = strtolower( $string );

		return preg_replace( '/-+/', '-', $string ); // Replaces multiple hyphens with single one.
	}


	/**
	 * @param $wcc_user_id
	 * @return int[]|WP_Post[]
	 */
	public static function d2g_get_doctor_by_wcc_id( $wcc_user_id ) {
		$args   = array(
			'post_type'  => 'd2g_doctor',
			'meta_query' => array(
				array(
					'key'   => 'wcc_user_id',
					'value' => $wcc_user_id,
				),
			),
		);
		$doctor = get_posts( $args );
		return $doctor;
	}
}

D2G_booking_wcc_user::init();
