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
		add_action( 'wp_ajax_d2gc_create_wcc_appointment', array( __CLASS__, 'd2gc_create_wcc_appointment' ) );
		add_action( 'wp_ajax_nopriv_d2gc_create_wcc_appointment', array( __CLASS__, 'd2gc_create_wcc_appointment' ) );

		// delete appointment
		add_action( 'wp_ajax_d2gc_delete_wcc_appointment', array( __CLASS__, 'd2gc_delete_wcc_appointment' ) );
		add_action( 'wp_ajax_nopriv_d2gc_delete_wcc_appointment', array( __CLASS__, 'd2gc_delete_wcc_appointment' ) );

		// walk in appointment
		add_action( 'wp_ajax_d2gc_create_wcc_walkin', array( __CLASS__, 'd2gc_create_wcc_walkin' ) );
		add_action( 'wp_ajax_nopriv_d2gc_create_wcc_walkin', array( __CLASS__, 'd2gc_create_wcc_walkin' ) );

		// written consult
		add_action( 'wp_ajax_d2gc_create_wcc_written_cosnsult', array( __CLASS__, 'd2gc_create_wcc_written_cosnsult' ) );
		add_action( 'wp_ajax_nopriv_d2gc_create_wcc_written_cosnsult', array( __CLASS__, 'd2gc_create_wcc_written_cosnsult' ) );
	}

	/*
	* this function creates an appointment in the D2G-application
	*/
	public static function d2gc_create_wcc_appointment() {

        $nonce = isset( $_POST['_wpnonce'] )
            ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) )
            : '';

        if ( ! wp_verify_nonce( $nonce, 'booking' ) ) {
            return false;
        }

        if ( get_option( 'd2gc_recaptcha_site_key' ) !== '' ) {
            $secret_key = get_option( 'd2gc_recaptcha_secret_key' );

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

        $wpDocID = isset( $_POST['wp_doc_id'] )
            ? absint( wp_unslash( $_POST['wp_doc_id'] ) )
            : 0;

        $docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
        $docKey    = get_post_meta( $wpDocID, 'user_key', true );
        $docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );

        $message            = d2gc_get_post_text( 'comment' );
        $appointment_date   = d2gc_get_post_text( 'start' );
        $endDate            = d2gc_get_post_text( 'end' );
        $patientEmail       = d2gc_get_post_text( 'email' );
        $patientTel         = d2gc_get_post_text( 'p_tel' );
        $patient_fname      = d2gc_get_post_text( 'patient_fname' );
        $patient_lname      = d2gc_get_post_text( 'patient_lname' );
        $location_id        = d2gc_get_post_text( 'location_id' );
        $docPrice           = d2gc_get_post_text( 'docPrice' );
        $currency           = d2gc_get_post_text( 'currency' );
        $vat                = d2gc_get_post_text( 'vat' );
        $questionnaire_id   = d2gc_get_post_text( 'questionnaire_id' );
        $client_gender      = isset( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '';
        $client_bday        = isset( $_POST['bday'] ) ? sanitize_text_field( wp_unslash( $_POST['bday'] ) ) : '';

        $complaint_description = isset( $_POST['complaint_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['complaint_description'] ) ) : '';
        $first_noticed         = isset( $_POST['first_noticed'] ) ? sanitize_textarea_field( wp_unslash( $_POST['first_noticed'] ) ) : '';
        $medical_history       = isset( $_POST['medical_history'] ) ? sanitize_textarea_field( wp_unslash( $_POST['medical_history'] ) ) : '';
        $treatment_history     = isset( $_POST['treatment_history'] ) ? sanitize_textarea_field( wp_unslash( $_POST['treatment_history'] ) ) : '';
        $complaint_location    = isset( $_POST['complaint_location'] ) ? sanitize_text_field( wp_unslash( $_POST['complaint_location'] ) ) : '';
        $booking_ai_info       = isset( $_POST['booking_ai_info'] ) ? sanitize_textarea_field( wp_unslash( $_POST['booking_ai_info'] ) ) : '';

        // Images: uploaded file wins; if not present, fall back to hidden base64.
        $image_1 = $image_2 = $image_3 = '';
        $allowed_mimes = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );

        foreach ( array(
            'booking_image_1' => 'derma_pic_1',
            'booking_image_2' => 'derma_pic_2',
            'booking_image_3' => 'derma_pic_3',
        ) as $file_key => $hidden_key ) {

            $value = '';

            if (
                isset( $_FILES[ $file_key ] )
                && isset( $_FILES[ $file_key ]['tmp_name'], $_FILES[ $file_key ]['name'], $_FILES[ $file_key ]['error'] )
                && UPLOAD_ERR_OK === (int) $_FILES[ $file_key ]['error']
                && ! empty( $_FILES[ $file_key ]['tmp_name'] )
            ) {
                $tmp_name  = $_FILES[ $file_key ]['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $orig_name = sanitize_file_name( wp_unslash( $_FILES[ $file_key ]['name'] ) );

                $file_type = wp_check_filetype_and_ext( $tmp_name, $orig_name );

                if ( ! empty( $file_type['type'] ) && in_array( $file_type['type'], $allowed_mimes, true ) ) {
                    $img_type = $file_type['type'];

                    // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContents
                    $file_contents = file_get_contents( $tmp_name );
                    if ( false !== $file_contents ) {
                        $value = 'data:' . $img_type . ';base64,' . base64_encode( $file_contents );
                    }
                }
            }

            if ( '' === $value && ! empty( $_POST[ $hidden_key ] ) ) {
                $value = wp_unslash( $_POST[ $hidden_key ] );
            }

            if ( 'booking_image_1' === $file_key ) {
                $image_1 = $value;
            } elseif ( 'booking_image_2' === $file_key ) {
                $image_2 = $value;
            } elseif ( 'booking_image_3' === $file_key ) {
                $image_3 = $value;
            }
        }

        $language = sanitize_text_field( wp_unslash( explode( '_', get_locale() )[0] ) );
        $currLang = explode( '_', get_locale() )[0];

        $userAction = '';
        $payCheck   = 'true';

        $currUser = wp_get_current_user();

        if ( $currUser->ID != 0 ) {

            $user_action = isset( $_POST['user_action'] ) ? sanitize_text_field( wp_unslash( $_POST['user_action'] ) ) : '';
            $wp_user_id  = isset( $_POST['wp_user_id'] ) ? absint( wp_unslash( $_POST['wp_user_id'] ) ) : 0;

            if ( 'update_user' === $user_action && $wp_user_id > 0 ) {
                update_user_meta( $wp_user_id, 'first_name', $patient_fname );
                update_user_meta( $wp_user_id, 'last_name', $patient_lname );
                update_user_meta( $wp_user_id, 'p_tel', $patientTel );
            }

        } 

        $myTime   = new DateTime();
        $unixTime = $myTime->format( 'U' );
        $superKey = get_option( 'd2gc_wcc_token' );
        $myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

        $postfields = array(
            'client_email'          => $patientEmail,
            'optie_naam'            => $patient_lname,
			'optie_first_name'      => $patient_fname,
			'optie_aanhef'          => $client_gender,
			'optie_geboortedatum'   => $client_bday,
            'date'                  => $appointment_date,
            'end_date'              => $endDate,
            'payment_price'         => $docPrice,
            'payment_vat'           => $vat,
            'language'              => $currLang,
            'payment_currency'      => $currency,
            'requires_payment'      => $payCheck,
            'consultant_id'         => $docWCC_ID,
            'location_id'           => $location_id,
            'custom_message'        => $message,
            'complaint_desc'        => $complaint_description,
            'first_noticed'         => $first_noticed,
            'medical_history'       => $medical_history,
            'treatment_history'     => $treatment_history,
            'complaint_location'    => $complaint_location,
            'ai_assessment'         => $booking_ai_info, // placeholder for future AI assessment field
            'image_1'               => $image_1,
            'image_2'               => $image_2,
            'image_3'               => $image_3,
            'handshake'   => array(
                'time'  => $unixTime,
                'token' => $docKey,
                'hash'  => $myHash,
                'type'  => 'user',
            ),
        );


        if ( $questionnaire_id == '' && get_option( 'd2gc_use_default_questionnaire' ) != 1 ) {
            $postfields['appointment']['questionnaire_id'] = 'false';
        } elseif ( $questionnaire_id != '' && get_option( 'd2gc_use_default_questionnaire' ) != 1 ) {
            $postfields['appointment']['questionnaire_id'] = $questionnaire_id;
        }

        $response = wp_remote_post(
            get_option( 'd2gc_api_url_short' ) . 'doclisting/video_consult_complete/',
            array(
                'method'      => 'POST',
                'headers'     => array(
                    'Content-Type' => 'application/json',
                ),
                'body'        => wp_json_encode( $postfields ),
                'timeout'     => 30,
                'redirection' => 10,
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'API Error: ' . $response->get_error_message() );
            wp_send_json( 'error' );
            wp_die();
        }

        $body        = wp_remote_retrieve_body( $response );
        $appointment = json_decode( $body, true );

        

        if ( isset( $appointment['client']['id'] ) && is_user_logged_in() ) {
			$client = $appointment['client'];

			$ids    = (array) get_user_meta( $currUser->ID, 'ids', true );
			$tokens = (array) get_user_meta( $currUser->ID, 'tokens', true );

			if ( ! isset( $tokens[ $docOrgKey ] ) ) {
				$ids[ $docOrgKey ]    = $client['id'];
				$tokens[ $docOrgKey ] = $client['authentication_token'];

				update_user_meta( $currUser->ID, 'ids', $ids );
				update_user_meta( $currUser->ID, 'tokens', $tokens );
			}
		}


        if ( isset( $appointment['url'] ) ) {

            $booking_data = array(
                'appointment_id'   => $appointment['appointment_id'],
                'client_token'     => $appointment['client']['authentication_token'],
            );

            wp_send_json( $booking_data );

        } else {
            wp_send_json( 'error' );
        }

        wp_die();
    }

	/*
	* this function is used to delete an appointment in the D2G software
	*/
	public static function d2gc_delete_wcc_appointment() {

		// Nonce check
		check_ajax_referer( 'd2gc_delete_wcc_appointment_nonce', 'security' );

		if ( ! isset( $_POST['wcc_user_id'], $_POST['app_id'] ) ) {
			return;
		}

		$wcc_user_id = sanitize_text_field( wp_unslash( $_POST['wcc_user_id'] ) );
		$app_id      = sanitize_text_field( wp_unslash( $_POST['app_id'] ) );

		$docObj = self::d2gc_get_doctor_by_wcc_id( $wcc_user_id )[0];

		$orgKey = get_post_meta( $docObj->ID, 'organisation_key', true );

		$response = wp_remote_request(
			get_option( 'd2gc_api_url_short' ) . 'appointments/' . $app_id . '.json',
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
	public static function d2gc_create_wcc_written_cosnsult() {

		// Verify nonce early and bail on failure.
		if ( ! isset( $_POST['email_advice_form_nonce'] )|| ! wp_verify_nonce(sanitize_text_field( wp_unslash( $_POST['email_advice_form_nonce'] ) ), 'email_advice_form_action')) {
			return false; // stop processing immediately
		}

		// Validate CAPTCHA.
		$secret_key = get_option( 'd2gc_recaptcha_secret_key' );
		if ( '' !== $secret_key ) {
			$recaptcha_response = isset( $_POST['g-recaptcha-response'] )? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ): '';
			$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ): '';

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

			if ( empty( $recaptcha_result ) || empty( $recaptcha_result->success ) ) {
				return false;
			}
		}

		// POST fields (nonce already verified).
		$wpDocID         = isset( $_POST['wp_doc_id'] ) ? absint( wp_unslash( $_POST['wp_doc_id'] ) ) : 0;
		$first_name      = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name       = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$client_email    = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$client_gender   = isset( $_POST['optie_aanhef'] ) ? sanitize_text_field( wp_unslash( $_POST['optie_aanhef'] ) ) : '';
        $client_bday     = isset( $_POST['option_bday'] ) ? sanitize_text_field( wp_unslash( $_POST['option_bday'] ) ) : '';
		$complaint       = isset( $_POST['complaint_description'] ) ? sanitize_text_field( wp_unslash( $_POST['complaint_description'] ) ) : '';
		$medical_history = isset( $_POST['medical_history'] ) ? sanitize_text_field( wp_unslash( $_POST['medical_history'] ) ) : '';
        $treatment_history = isset( $_POST['treatment_history'] ) ? sanitize_text_field( wp_unslash( $_POST['treatment_history'] ) ) : '';
		$first_noticed   = isset( $_POST['first_noticed'] ) ? sanitize_text_field( wp_unslash( $_POST['first_noticed'] ) ) : '';
		$location        = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
		$type            = isset( $_POST['written_con_type'] ) ? sanitize_text_field( wp_unslash( $_POST['written_con_type'] ) ) : '';
        $email_ai_info   = isset( $_POST['email_ai_info'] ) ? sanitize_textarea_field( wp_unslash( $_POST['email_ai_info'] ) ) : '';

		// Images: uploaded file wins; if not present, fall back to hidden base64.
		$image_1 = $image_2 = $image_3 = '';
		$allowed_mimes = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );

		foreach ( array( 'image_1', 'image_2', 'image_3' ) as $index => $img ) {

			$value = ''; // final data URI for this slot

			// 1) Prefer a newly uploaded file in $_FILES.
			if (
				isset( $_FILES[ $img ] )
				&& isset( $_FILES[ $img ]['tmp_name'], $_FILES[ $img ]['name'], $_FILES[ $img ]['error'] )
				&& UPLOAD_ERR_OK === (int) $_FILES[ $img ]['error']
				&& ! empty( $_FILES[ $img ]['tmp_name'] )
			) {
				$tmp_name  = $_FILES[ $img ]['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$orig_name = sanitize_file_name( wp_unslash( $_FILES[ $img ]['name'] ) );

				$file_type = wp_check_filetype_and_ext( $tmp_name, $orig_name );

				if ( ! empty( $file_type['type'] ) && in_array( $file_type['type'], $allowed_mimes, true ) ) {
					$img_type = $file_type['type'];

					// phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContents
					$file_contents = file_get_contents( $tmp_name );
					if ( false !== $file_contents ) {
						$value = 'data:' . $img_type . ';base64,' . base64_encode( $file_contents );
					}
				}
			}

			// 2) If no uploaded file was used, fall back to hidden field derma_pic_X (base64 already).
			if ( '' === $value ) {
				$hidden_key = 'derma_pic_' . ( $index + 1 );
				if ( ! empty( $_POST[ $hidden_key ] ) ) {
					$value = sanitize_text_field( wp_unslash( $_POST[ $hidden_key ] ) );
				}
			}

			// 3) Assign into the correct variable $image_1, $image_2, $image_3.
			if ( 'image_1' === $img ) {
				$image_1 = $value;
			} elseif ( 'image_2' === $img ) {
				$image_2 = $value;
			} elseif ( 'image_3' === $img ) {
				$image_3 = $value;
			}
		}

		// NEW: PDFs already base64 from JS (file_X_base64)
		$file_1_base64 = isset( $_POST['file_1_base64'] ) ? wp_kses_post( wp_unslash( $_POST['file_1_base64'] ) ) : '';
		$file_2_base64 = isset( $_POST['file_2_base64'] ) ? wp_kses_post( wp_unslash( $_POST['file_2_base64'] ) ) : '';
		$file_3_base64 = isset( $_POST['file_3_base64'] ) ? wp_kses_post( wp_unslash( $_POST['file_3_base64'] ) ) : '';

		$docKey    = get_post_meta( $wpDocID, 'user_key', true );
		$docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
		$docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );
		$orgSlug   = get_post_meta( $wpDocID, 'organisation_subdomain', true ) . '.';
		$baseUrl   = get_option( 'd2gc_wcc_base_url' );
		$price     = get_post_meta( $wpDocID, 'written_con_price', true );
		$currency  = get_post_meta( $wpDocID, 'written_con_currency', true );

		$unixTime = time();
		$superKey = get_option( 'd2gc_wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

		$d2gAdmin         = new D2G_doc_user_profile();
		$currLang         = explode( '_', get_locale() )[0];
		$confirmation_url = $d2gAdmin::d2gc_page_url( $currLang, 'email_advice_confirmation', false );

		$currUser        = wp_get_current_user();
		$require_payment = (float) $price > 0;

		$payload = array(
			'consultant_id'       => (string) $docWCC_ID,
			'requires_payment'    => (string) $require_payment,
			'payment_price'       => (string) $price,
			'payment_currency'    => (string) $currency,
			'type'                => $type,
			'client_email'        => $client_email,
			'optie_naam'          => $last_name,
			'optie_first_name'    => $first_name,
			'optie_aanhef'        => $client_gender,
			'optie_geboortedatum' => $client_bday,
			'language'            => $currLang,
			'complaint_desc'      => $complaint,
			'medical_history'     => $medical_history,
            'treatment_history'   => $treatment_history,
			'first_noticed'       => $first_noticed,
            'ai_assessment'       => $email_ai_info, // placeholder for future AI assessment field
			'complaint_location'  => $location,
			'image_1'             => $image_1,
			'image_2'             => $image_2,
			'image_3'             => $image_3,
			// NEW: PDFs
			'file_1'              => $file_1_base64,
			'file_2'              => $file_2_base64,
			'file_3'              => $file_3_base64,
			'handshake'           => array(
				'time'  => (string) $unixTime,
				'token' => $docKey,
				'hash'  => $myHash,
				'type'  => 'user',
			),
		);


		$response = wp_remote_post(
			get_option( 'd2gc_api_url_short' ) . 'doclisting/written_consult_complete',
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
			$redirect_url      = $questionnaire_url . '?redirect_url=' . urlencode( $confirmation_url ) . '&booked_consult=email&skip_cookie_wall=true';

			wp_send_json_success(
				array(
					'redirect_url' => $redirect_url,
				)
			);
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
			: d2gc_get_user_timezone();

		$language = explode( '_', get_locale() )[0];

		$unixTime = time();
		$superKey = get_option( 'd2gc_wcc_token' );
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
			get_option( 'd2gc_api_url_short' ) . 'doclisting/create_client',
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
			get_option( 'd2gc_api_url_short' ) . 'clients/get_client_by_email',
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
	protected static function d2gc_clean_name( $string ) {
		$string = str_replace( ' ', '', $string ); // Replaces all spaces with hyphens.
		$string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Removes special chars.
		$string = strtolower( $string );

		return preg_replace( '/-+/', '-', $string ); // Replaces multiple hyphens with single one.
	}


	/**
	 * @param $wcc_user_id
	 * @return int[]|WP_Post[]
	 */
	public static function d2gc_get_doctor_by_wcc_id( $wcc_user_id ) {
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
