<?php
if ( defined( 'ABSPATH' ) ) {
	return;
}

$currDir = dirname( __DIR__ );

$rootDir = '';
if ( isset( $_SERVER['DOCUMENT_ROOT'] ) && ! empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
	$rootDir = rtrim( stripslashes( filter_var( $_SERVER['DOCUMENT_ROOT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
}

require_once $rootDir ?: $currDir . '/wp-config.php';

if ( ! function_exists( 'get_option' ) ) {
	wp_die( esc_html( 'WordPress not loaded.' ) );
}

require_once D2GC_PLUGIN_DIR . 'admin/class-d2g-doc-user-profile.php';
$d2gAdmin = new D2G_doc_user_profile();

$error     = '';
$user_data = json_decode( file_get_contents( 'php://input' ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

$superKey = get_option( 'd2gc_wcc_token' );
if ( empty( $superKey ) ) {
	wp_die( 'Server configuration error.', 500 );
}

$timestamp = isset( $user_data['timestamp'] ) ? absint( $user_data['timestamp'] ) : 0;
if ( abs( time() - $timestamp ) > 300 ) {
	wp_die( 'Request expired.', 403 );
}

$user_key_safe = isset( $user_data['user_key'] ) ? sanitize_text_field( $user_data['user_key'] ) : '';
$payload       = $timestamp . '_' . $user_key_safe;
$expected_hash = hash_hmac( 'sha256', $payload, $superKey );

if ( ! isset( $user_data['hash'] ) || ! hash_equals( $expected_hash, $user_data['hash'] ) ) {
	error_log( 'IMPORT ERROR: Invalid hash.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	wp_die( 'Invalid signature.', 403 );
}

// Sanitize all incoming user fields
$user_id_safe            = isset( $user_data['user_id'] ) ? sanitize_text_field( $user_data['user_id'] ) : 0;
$user_email_safe         = isset( $user_data['user_email'] ) ? sanitize_email( $user_data['user_email'] ) : '';
$user_first_name_safe    = isset( $user_data['user_first_name'] ) ? sanitize_text_field( $user_data['user_first_name'] ) : '';
$user_last_name_safe     = isset( $user_data['user_last_name'] ) ? sanitize_text_field( $user_data['user_last_name'] ) : '';
$user_full_name_safe     = isset( $user_data['user_full_name'] ) ? sanitize_text_field( $user_data['user_full_name'] ) : '';
$organisation_key_safe   = isset( $user_data['organisation_key'] ) ? sanitize_text_field( $user_data['organisation_key'] ) : '';
$organisation_state_safe = isset( $user_data['organisation_state'] ) ? sanitize_text_field( $user_data['organisation_state'] ) : '';
$locations_safe          = isset( $user_data['locations'] ) && is_array( $user_data['locations'] )? array_map( 'sanitize_text_field', $user_data['locations'] ): array();
$tariffs_safe            = isset( $user_data['tariffs'] ) && is_array( $user_data['tariffs'] )? array_map( 'sanitize_text_field', $user_data['tariffs'] ): array();

$user_data_save = array(
    'timestamp'          => $timestamp,
    'user_id'            => $user_id_safe,
    'user_key'           => $user_key_safe,
    'user_email'         => $user_email_safe,
    'user_first_name'    => $user_first_name_safe,
    'user_last_name'     => $user_last_name_safe,
    'user_full_name'     => $user_full_name_safe,
    'organisation_key'   => $organisation_key_safe,
    'organisation_state' => $organisation_state_safe,
    'locations'          => $locations_safe,
    'tariffs'            => $tariffs_safe,
    'hash'               => $hash_safe,
);

if ( $user_email_safe ) {

	if ( ! email_exists( $user_email_safe ) ) {

		$user = $d2gAdmin::d2gc_create_doc_user( $user_data_save ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( is_wp_error( $user ) ) {
			$error = $user->errors['existing_user_email'][0] ?? esc_html__( 'An unknown error has occurred', 'doctor2go-connect' );
			error_log( 'IMPORT ERROR: ' . print_r( $error, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'IMPORT ERROR USER: ' . print_r( $user, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		if ( empty( $error ) ) {
			update_user_meta( $user, 'wcc_user_id', $user_id_safe );
			update_user_meta( $user, 'user_key', $user_key_safe );
			update_user_meta( $user, 'organisation_key', $organisation_key_safe );

			$doc_meta = $d2gAdmin::d2gc_set_doc_meta( $user, $user_data_save ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$doc      = $d2gAdmin::d2gc_create_doc_profile( $user_data_save, $doc_meta, $user ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			echo esc_html__( 'The user + public profile have been created. User ID: ', 'doctor2go-connect' ) . esc_html( $user );
		}
	} elseif ( $user_id_safe ) {


			$args = array(
				'post_type'   => 'd2g_doctor',
				'post_status' => 'any',
				'meta_query'  => array(
					array(
						'key'   => 'wcc_user_id',
						'value' => $user_id_safe,
					),
				),
			);

			$docs = get_posts( $args );

			if ( count( $docs ) > 0 ) {

				$docID      = $docs[0]->ID;
				$wcc_status = get_post_meta( $docID, 'wcc_status', true );

				if ( $organisation_state_safe === 'inactive' ) {
					$status = 'draft';
					update_post_meta( $docID, 'wcc_status', 'inactive' );
				} elseif ( $organisation_state_safe === 'active' && $wcc_status === 'inactive' ) {
					$status = 'publish';
					update_post_meta( $docID, 'wcc_status', 'active' );
				} else {
					$status = $docs[0]->post_status;
				}

				$my_update = array(
					'post_title'   => $user_full_name_safe,
					'post_content' => wp_kses_post( $docs[0]->post_content ),
					'post_status'  => $status,
					'ID'           => $docID,
				);
				wp_update_post( $my_update );

				update_post_meta( $docID, 'tariffs', $tariffs_safe );
				update_post_meta( $docID, 'locations_to_go', $locations_safe );
				update_post_meta( $docID, 'd2g_first_name', $user_first_name_safe );
				update_post_meta( $docID, 'd2g_last_name', $user_last_name_safe );
				update_post_meta( $docID, 'd2g_main_email', $user_email_safe );

				echo esc_html__( 'Update was a success', 'doctor2go-connect' );

			} else {
				echo esc_html__( 'User does not exist', 'doctor2go-connect' );
			}
	} else {
		echo esc_html__( 'No user ID has been sent', 'doctor2go-connect' );
	}
}

wp_die();
