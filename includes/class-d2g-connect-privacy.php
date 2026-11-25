<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal data export and erasure (Tools → Export / Erase Personal Data), by e-mail address.
 *
 * One erase request removes everything about a person within the site: the client references (guest bookings and
 * active appointment links), the leads and, as the very last step, the account if it is a patient account (with its
 * WCC tokens, guest booking notes and e-mail verification data). Doctor and admin accounts are never deleted here:
 * their data is cleared and the account kept. WordPress asks the person to confirm the request by e-mail first.
 *
 * Not covered: the anonymous client reference log (no personal data), and the data at doctor2go.online / WCC, which
 * has its own deletion process. Other plugins add their own erasers (e.g. the saved AI checks of ai-derma-plugin,
 * which run before the leads because they are found through them).
 */
class D2GC_Privacy {

	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'erasers' ) );
		// late priority: the account is deleted after every other eraser has run
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'account_eraser' ), 999 );
	}

	public static function exporters( $exporters ) {
		$exporters['doctor2go-connect-bookings'] = array(
			'exporter_friendly_name' => __( 'Doctor2Go bookings', 'doctor2go-connect' ),
			'callback'               => array( __CLASS__, 'export_bookings' ),
		);
		$exporters['doctor2go-connect-leads']    = array(
			'exporter_friendly_name' => __( 'Doctor2Go leads', 'doctor2go-connect' ),
			'callback'               => array( __CLASS__, 'export_leads' ),
		);
		$exporters['doctor2go-connect-account']  = array(
			'exporter_friendly_name' => __( 'Doctor2Go patient account', 'doctor2go-connect' ),
			'callback'               => array( __CLASS__, 'export_account' ),
		);
		return $exporters;
	}

	public static function erasers( $erasers ) {
		$erasers['doctor2go-connect-bookings'] = array(
			'eraser_friendly_name' => __( 'Doctor2Go bookings', 'doctor2go-connect' ),
			'callback'             => array( __CLASS__, 'erase_bookings' ),
		);
		$erasers['doctor2go-connect-leads']    = array(
			'eraser_friendly_name' => __( 'Doctor2Go leads', 'doctor2go-connect' ),
			'callback'             => array( __CLASS__, 'erase_leads' ),
		);
		return $erasers;
	}

	public static function account_eraser( $erasers ) {
		$erasers['doctor2go-connect-account'] = array(
			'eraser_friendly_name' => __( 'Doctor2Go patient account', 'doctor2go-connect' ),
			'callback'             => array( __CLASS__, 'erase_account' ),
		);
		return $erasers;
	}

	private static function normalize( $email ) {
		return strtolower( trim( (string) $email ) );
	}

	private static function done( $removed = false, $retained = false, $messages = array() ) {
		return array(
			'items_removed'  => (bool) $removed,
			'items_retained' => (bool) $retained,
			'messages'       => $messages,
			'done'           => true,
		);
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Client references (bookings waiting for an account, active appointment links)
	 * ------------------------------------------------------------------------------------------------------ */

	public static function export_bookings( $email ) {
		global $wpdb;
		$rows  = $wpdb->get_results( $wpdb->prepare( 'SELECT id, type, doctor_post_id, booking_ref, created_at, expires_at FROM ' . D2GC_Client_Refs::table() . ' WHERE email = %s', self::normalize( $email ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = array();
		foreach ( $rows as $row ) {
			$items[] = array(
				'group_id'    => 'd2gc-bookings',
				'group_label' => __( 'Bookings kept by Doctor2Go', 'doctor2go-connect' ),
				'item_id'     => 'd2gc-booking-' . $row->id,
				'data'        => array(
					array( 'name' => __( 'Booking', 'doctor2go-connect' ), 'value' => $row->booking_ref ),
					array( 'name' => __( 'Type', 'doctor2go-connect' ), 'value' => $row->type ),
					array( 'name' => __( 'Doctor', 'doctor2go-connect' ), 'value' => $row->doctor_post_id ? get_the_title( $row->doctor_post_id ) : '' ),
					array( 'name' => __( 'Booked on', 'doctor2go-connect' ), 'value' => $row->created_at . ' UTC' ),
					array( 'name' => __( 'Deleted on', 'doctor2go-connect' ), 'value' => $row->expires_at . ' UTC' ),
				),
			);
		}
		return array( 'data' => $items, 'done' => true );
	}

	public static function erase_bookings( $email ) {
		$deleted = D2GC_Client_Refs::delete_email( $email, D2GC_Client_Refs::EVENT_ERASED );
		return self::done( $deleted > 0 );
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Leads
	 * ------------------------------------------------------------------------------------------------------ */

	public static function export_leads( $email ) {
		global $wpdb;
		$rows  = $wpdb->get_results( $wpdb->prepare( 'SELECT id, source_type, intent, status, created_at FROM ' . D2GC_Leads::table() . ' WHERE LOWER(email) = %s', self::normalize( $email ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = array();
		foreach ( $rows as $row ) {
			$items[] = array(
				'group_id'    => 'd2gc-leads',
				'group_label' => __( 'E-mail sign-ups (leads)', 'doctor2go-connect' ),
				'item_id'     => 'd2gc-lead-' . $row->id,
				'data'        => array(
					array( 'name' => __( 'Date', 'doctor2go-connect' ), 'value' => $row->created_at ),
					array( 'name' => __( 'Source', 'doctor2go-connect' ), 'value' => $row->source_type . ' / ' . $row->intent ),
					array( 'name' => __( 'Status', 'doctor2go-connect' ), 'value' => $row->status ),
				),
			);
		}
		return array( 'data' => $items, 'done' => true );
	}

	public static function erase_leads( $email ) {
		global $wpdb;
		$deleted = (int) $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . D2GC_Leads::table() . ' WHERE LOWER(email) = %s', self::normalize( $email ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return self::done(
			$deleted > 0,
			false,
			$deleted > 0 ? array( __( 'Leads already sent to doctor2go.online are not deleted there; ask doctor2go.online for that.', 'doctor2go-connect' ) ) : array()
		);
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * The account (last)
	 * ------------------------------------------------------------------------------------------------------ */

	/** A patient account: role patient, and nothing that makes it a doctor or an admin. */
	private static function is_patient_account( $user ) {
		$roles = (array) $user->roles;
		return in_array( 'patient', $roles, true ) && ! in_array( 'doctor', $roles, true ) && ! user_can( $user, 'edit_posts' );
	}

	public static function export_account( $email ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return array( 'data' => array(), 'done' => true );
		}
		$tokens = get_user_meta( $user->ID, 'tokens', true );
		$data   = array(
			array(
				'name'  => __( 'E-mail address confirmed', 'doctor2go-connect' ),
				'value' => D2GC_Email_Verification::is_verified( $user->ID ) ? __( 'Yes', 'doctor2go-connect' ) : __( 'No', 'doctor2go-connect' ),
			),
			array(
				'name'  => __( 'Connected doctor organisations', 'doctor2go-connect' ),
				'value' => (string) ( is_array( $tokens ) ? count( $tokens ) : 0 ),
			),
		);
		$history = get_user_meta( $user->ID, D2GC_Client_Refs::META_GUEST_HISTORY, true );
		foreach ( is_array( $history ) ? $history : array() as $entry ) {
			$data[] = array(
				'name'  => __( 'Guest booking before the account', 'doctor2go-connect' ),
				'value' => sprintf( '%s, %s, %s (%s)', $entry['booking_ref'] ?? '', $entry['type'] ?? '', ! empty( $entry['doctor_post_id'] ) ? get_the_title( $entry['doctor_post_id'] ) : '', $entry['booked_at'] ?? '' ),
			);
		}
		return array(
			'data' => array(
				array(
					'group_id'    => 'd2gc-account',
					'group_label' => __( 'Doctor2Go patient account', 'doctor2go-connect' ),
					'item_id'     => 'd2gc-account-' . $user->ID,
					'data'        => $data,
				),
			),
			'done' => true,
		);
	}

	/**
	 * Deletes the account when it is a patient account. Its tokens, guest booking notes, e-mail verification data and
	 * remaining client reference rows go with it (user meta, and the deleted_user hook of D2GC_Client_Refs).
	 */
	public static function erase_account( $email ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return self::done();
		}
		if ( ! self::is_patient_account( $user ) ) {
			return self::done(
				false,
				true,
				array( sprintf( /* translators: %s: roles */ __( 'The account was kept because it is not a patient account (role: %s). Delete it under Users if needed.', 'doctor2go-connect' ), implode( ', ', (array) $user->roles ) ) )
			);
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$deleted = wp_delete_user( $user->ID );
		return self::done( $deleted, ! $deleted, $deleted ? array( __( 'The patient account has been deleted.', 'doctor2go-connect' ) ) : array( __( 'The patient account could not be deleted.', 'doctor2go-connect' ) ) );
	}
}
