<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client references: the WCC clients (id + authentication token) that bookings get back, kept on the server.
 *
 * - The browser never gets a token. Pages without login (the appointment confirmation after a video booking) get a
 *   random reference instead, which opens only that one appointment.
 * - Guest bookings wait here until the owner of the e-mail address has a confirmed account, then the token is linked
 *   to that account.
 * - A row always has a token, or it does not exist: rows are deleted when linked, when their reference expired
 *   (logged-in bookings, the token is already in the account) or after the retention period (guests).
 *
 * The token is encrypted with a key from the WordPress salts in .env (or the constant D2GC_CLIENT_REF_KEY), the
 * reference is stored as a hash only. Same approach as the saved AI checks of the ai-derma-plugin.
 *
 * Next to the rows there is a log without personal data (no e-mail, name, user id or token): what happened, when (the
 * day), which booking type, who booked and with which doctor. Kept one year, see the admin page Event Log.
 */
class D2GC_Client_Refs {

	const DB_VERSION        = '3';
	const DB_VERSION_OPTION = 'd2gc_client_refs_db_version';
	const CRON_HOOK         = 'd2gc_delete_expired_client_refs';

	/** Who booked: a guest, a logged-in patient with the account's e-mail, a logged-in patient with another e-mail. */
	const BOOKED_BY_GUEST = 'guest';
	const BOOKED_BY_OWN   = 'own';
	const BOOKED_BY_OTHER = 'other';

	/** Log events. */
	const EVENT_BOOKED          = 'booked';
	const EVENT_LINKED          = 'linked';
	const EVENT_EXPIRED         = 'expired';
	const EVENT_DELETED_ACCOUNT = 'deleted_account';
	const EVENT_DELETED_ADMIN   = 'deleted_admin';
	const EVENT_ERASED          = 'erased'; // personal data erasure request

	/** Days the log is kept. */
	const LOG_DAYS = 365;

	/** Request field with the reference: POST from the buttons on the confirmation page, GET in the page link. */
	const REQUEST_KEY = 'client_ref';
	const URL_KEY     = 'ref';

	/** Longest a reference may live, whatever a caller passes (seconds). */
	const MAX_REF_LIFETIME = 400 * DAY_IN_SECONDS;

	/** @var array|null|false reference row of this request (false = not looked up yet) */
	private static $request_row = false;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'init', array( __CLASS__, 'ensure_scheduled' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'delete_expired' ) );
		add_action( 'deleted_user', array( __CLASS__, 'delete_for_user' ) );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'd2gc_client_refs';
	}

	public static function log_table() {
		global $wpdb;
		return $wpdb->prefix . 'd2gc_client_ref_log';
	}

	/** Days an unlinked guest row is kept (filter d2gc_client_ref_retention_days, 365). */
	public static function retention_days() {
		return max( 1, (int) apply_filters( 'd2gc_client_ref_retention_days', 365 ) );
	}

	/**
	 * (init) Creates or updates the table. Not only on activation: Composer deploys never run the activation hook.
	 */
	public static function maybe_install() {
		if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			'CREATE TABLE ' . self::table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				ref_hash char(64) DEFAULT NULL,
				email varchar(190) NOT NULL,
				org_key varchar(64) NOT NULL,
				client_id varchar(64) NOT NULL,
				token longblob NOT NULL,
				appointment_id varchar(64) NOT NULL DEFAULT '',
				type varchar(20) NOT NULL,
				booked_by varchar(10) NOT NULL DEFAULT '',
				doctor_post_id bigint(20) unsigned NOT NULL DEFAULT 0,
				booking_ref varchar(12) NOT NULL DEFAULT '',
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				ref_expires datetime DEFAULT NULL,
				expires_at datetime NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY ref_hash (ref_hash),
				KEY email (email),
				KEY user_id (user_id),
				KEY expires_at (expires_at)
			) " . $wpdb->get_charset_collate() . ';'
		);
		dbDelta(
			'CREATE TABLE ' . self::log_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				event varchar(20) NOT NULL,
				type varchar(20) NOT NULL,
				booked_by varchar(10) NOT NULL,
				doctor_post_id bigint(20) unsigned NOT NULL DEFAULT 0,
				booking_ref varchar(12) NOT NULL DEFAULT '',
				days_to_link int(10) unsigned DEFAULT NULL,
				created_on date NOT NULL,
				PRIMARY KEY  (id),
				KEY created_on (created_on),
				KEY event (event),
				KEY booking_ref (booking_ref)
			) " . $wpdb->get_charset_collate() . ';'
		);
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	public static function ensure_scheduled() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * (cron) Deletes the rows whose time is up, and the log entries older than a year. A guest row that expires was
	 * never linked: that is logged; a logged-in row only lived for its reference, the token is in the account.
	 */
	public static function delete_expired() {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE expires_at < %s', gmdate( 'Y-m-d H:i:s' ) ), ARRAY_A );
		foreach ( $rows as $row ) {
			if ( 0 === (int) $row['user_id'] ) {
				self::log( self::EVENT_EXPIRED, $row );
			}
			$wpdb->delete( self::table(), array( 'id' => (int) $row['id'] ) );
		}
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::log_table() . ' WHERE created_on < %s', gmdate( 'Y-m-d', time() - self::LOG_DAYS * DAY_IN_SECONDS ) ) );
		// phpcs:enable
	}

	/** (deleted_user) A deleted account takes its rows with it. */
	public static function delete_for_user( $user_id ) {
		self::delete_where( array( 'user_id' => (int) $user_id ), self::EVENT_DELETED_ACCOUNT );
	}

	/** (admin page) Deletes one row. @return int rows deleted */
	public static function delete_row( $id ) {
		return self::delete_where( array( 'id' => (int) $id ), self::EVENT_DELETED_ADMIN );
	}

	/**
	 * Deletes all rows of an e-mail address (admin page, or an erasure request with EVENT_ERASED).
	 *
	 * @return int rows deleted
	 */
	public static function delete_email( $email, $event = self::EVENT_DELETED_ADMIN ) {
		return self::delete_where( array( 'email' => strtolower( trim( (string) $email ) ) ), $event );
	}

	/** @return int rows deleted */
	private static function delete_where( $where, $event ) {
		global $wpdb;
		$column = key( $where );
		$rows   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . " WHERE $column = %s", current( $where ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table and column name
		foreach ( $rows as $row ) {
			self::log( $event, $row );
			$wpdb->delete( self::table(), array( 'id' => (int) $row['id'] ) );
		}
		return count( $rows );
	}

	/**
	 * Writes a log entry, without personal data.
	 *
	 * @param string $event one of the EVENT_ constants
	 * @param array  $row   type, booked_by, doctor_post_id (+ created_at for linked)
	 */
	public static function log( $event, $row ) {
		global $wpdb;
		$days = null;
		if ( self::EVENT_LINKED === $event && ! empty( $row['created_at'] ) ) {
			$days = max( 0, (int) floor( ( time() - strtotime( $row['created_at'] . ' UTC' ) ) / DAY_IN_SECONDS ) );
		}
		$wpdb->insert(
			self::log_table(),
			array(
				'event'          => $event,
				'type'           => sanitize_key( (string) ( $row['type'] ?? '' ) ),
				'booked_by'      => sanitize_key( (string) ( $row['booked_by'] ?? '' ) ),
				'doctor_post_id' => (int) ( $row['doctor_post_id'] ?? 0 ),
				'booking_ref'    => substr( (string) ( $row['booking_ref'] ?? '' ), 0, 12 ),
				'days_to_link'   => $days,
				'created_on'     => gmdate( 'Y-m-d' ),
			)
		);
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Storing (after a booking)
	 * ------------------------------------------------------------------------------------------------------ */

	/**
	 * Keeps the WCC client a booking got back.
	 *
	 * Logged in and the booking e-mail is the account's e-mail: the token goes into the account (user meta ids/tokens)
	 * and the row only lives as long as its reference. Otherwise (guest, or logged in with another e-mail) the token
	 * only goes into the row, for the owner of that e-mail address to link later.
	 *
	 * @param array $args {
	 *     @type string $email          e-mail of the booking form
	 *     @type string $org_key        organisation key of the doctor
	 *     @type string $client_id      WCC client id
	 *     @type string $token          WCC authentication token
	 *     @type string $type           video|email_advice|walk_in
	 *     @type int    $doctor_post_id doctor post id
	 *     @type string $appointment_id WCC appointment id ('' when none)
	 *     @type int    $ref_until      timestamp until which the reference works, 0 = no reference
	 * }
	 * @return string the reference for the confirmation page, '' when there is none
	 */
	public static function store( $args ) {
		$email     = strtolower( trim( (string) ( $args['email'] ?? '' ) ) );
		$org_key   = (string) ( $args['org_key'] ?? '' );
		$client_id = (string) ( $args['client_id'] ?? '' );
		$token     = (string) ( $args['token'] ?? '' );
		if ( ! is_email( $email ) || '' === $org_key || '' === $client_id || '' === $token ) {
			return '';
		}

		$user      = wp_get_current_user();
		$own       = $user->ID > 0 && strtolower( $user->user_email ) === $email;
		$booked_by = $own ? self::BOOKED_BY_OWN : ( $user->ID > 0 ? self::BOOKED_BY_OTHER : self::BOOKED_BY_GUEST );
		$type      = sanitize_key( (string) ( $args['type'] ?? '' ) );
		$doctor    = (int) ( $args['doctor_post_id'] ?? 0 );
		$number    = self::new_booking_ref();
		self::log(
			self::EVENT_BOOKED,
			array(
				'type'           => $type,
				'booked_by'      => $booked_by,
				'doctor_post_id' => $doctor,
				'booking_ref'    => $number,
			)
		);
		if ( $own ) {
			self::add_to_user( $user->ID, $org_key, $client_id, $token );
		}

		$now       = time();
		$ref_until = min( (int) ( $args['ref_until'] ?? 0 ), $now + self::MAX_REF_LIFETIME );
		$ref       = $ref_until > $now ? strtolower( wp_generate_password( 32, false ) ) : '';

		if ( $own ) {
			if ( '' === $ref ) {
				return ''; // the token is in the account, nothing to keep
			}
			$expires = $ref_until;
		} else {
			$expires = max( $now + self::retention_days() * DAY_IN_SECONDS, $ref_until );
		}

		global $wpdb;
		$saved = $wpdb->insert(
			self::table(),
			array(
				'ref_hash'       => '' !== $ref ? hash( 'sha256', $ref ) : null,
				'email'          => $email,
				'org_key'        => $org_key,
				'client_id'      => $client_id,
				'token'          => self::encrypt( $token, self::aad( $email, $org_key, $client_id ) ),
				'appointment_id' => (string) ( $args['appointment_id'] ?? '' ),
				'type'           => $type,
				'booked_by'      => $booked_by,
				'doctor_post_id' => $doctor,
				'booking_ref'    => $number,
				'user_id'        => $own ? $user->ID : 0,
				'ref_expires'    => '' !== $ref ? gmdate( 'Y-m-d H:i:s', $ref_until ) : null,
				'expires_at'     => gmdate( 'Y-m-d H:i:s', $expires ),
				'created_at'     => gmdate( 'Y-m-d H:i:s', $now ),
			)
		);
		if ( ! $saved && get_option( 'd2g_debug' ) == 1 ) {
			error_log( 'D2GC client refs: row could not be saved: ' . $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging.
		}

		return $saved ? $ref : '';
	}

	/** What made a guest booking link to an account (the log type of a linked event). */
	const LINKED_BY_REGISTRATION = 'registration'; // the confirmation link of the registration mail
	const LINKED_BY_LOGIN        = 'login';

	/**
	 * Links the guest bookings of an account's e-mail address to the account and deletes their rows. Only call this
	 * for an account whose e-mail address is confirmed (D2GC_Email_Verification).
	 *
	 * @param int    $user_id
	 * @param string $trigger LINKED_BY_REGISTRATION or LINKED_BY_LOGIN, logged as the type of the event
	 * @return int number of bookings linked
	 */
	public static function link_to_user( $user_id, $trigger = self::LINKED_BY_LOGIN ) {
		$user = get_userdata( (int) $user_id );
		if ( ! $user ) {
			return 0;
		}
		global $wpdb;
		$rows   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE email = %s AND user_id = 0', strtolower( $user->user_email ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$linked = 0;
		foreach ( $rows as $row ) {
			$token = self::decrypt( $row['token'], self::aad( $row['email'], $row['org_key'], $row['client_id'] ) );
			if ( false !== $token && '' !== $token ) {
				self::add_to_user( $user->ID, $row['org_key'], $row['client_id'], $token );
				self::log( self::EVENT_LINKED, array( 'type' => $trigger ) + $row );
				self::add_guest_history( $user->ID, $row, $trigger );
				$linked++;
			}
			$wpdb->delete( self::table(), array( 'id' => (int) $row['id'] ) );
		}
		return $linked;
	}

	/** User meta with the guest bookings that were linked to the account (shown on the user's profile in wp-admin). */
	const META_GUEST_HISTORY = 'd2gc_guest_bookings';

	/**
	 * Notes on the account that it was a guest before: booking number, type, doctor, booked on, linked on, by what.
	 * Personal data, so it lives with the account (deleted with it), not in the log.
	 */
	private static function add_guest_history( $user_id, $row, $trigger ) {
		$history   = get_user_meta( $user_id, self::META_GUEST_HISTORY, true );
		$history   = is_array( $history ) ? $history : array();
		$history[] = array(
			'booking_ref'    => (string) ( $row['booking_ref'] ?? '' ),
			'type'           => (string) $row['type'],
			'doctor_post_id' => (int) $row['doctor_post_id'],
			'booked_at'      => (string) $row['created_at'],
			'linked_at'      => gmdate( 'Y-m-d H:i:s' ),
			'linked_by'      => $trigger,
		);
		update_user_meta( $user_id, self::META_GUEST_HISTORY, $history );
	}

	/** Booking number shown in the log and on the account, e.g. B-7F3K9Q (no personal data). */
	private static function new_booking_ref() {
		$chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ'; // no 0/O, 1/I/L: easy to read out and type
		$ref   = 'B-';
		for ( $i = 0; $i < 6; $i++ ) {
			$ref .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		return $ref;
	}

	/**
	 * Adds a WCC client to an account (user meta ids/tokens, one per organisation). An organisation that already has a
	 * token in the account keeps it.
	 */
	public static function add_to_user( $user_id, $org_key, $client_id, $token ) {
		$ids    = get_user_meta( $user_id, 'ids', true );
		$tokens = get_user_meta( $user_id, 'tokens', true );
		$ids    = is_array( $ids ) ? $ids : array();
		$tokens = is_array( $tokens ) ? $tokens : array();

		if ( isset( $tokens[ $org_key ] ) ) {
			return;
		}
		$ids[ $org_key ]    = $client_id;
		$tokens[ $org_key ] = $token;
		update_user_meta( $user_id, 'ids', $ids );
		update_user_meta( $user_id, 'tokens', $tokens );
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Reading (confirmation page and its buttons)
	 * ------------------------------------------------------------------------------------------------------ */

	/**
	 * The row of a reference, with the token decrypted.
	 *
	 * @param string $ref
	 * @return array|null row with "token", null when the reference is unknown or expired
	 */
	public static function find( $ref ) {
		$ref = sanitize_key( (string) $ref );
		if ( 32 !== strlen( $ref ) ) {
			return null;
		}
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE ref_hash = %s', hash( 'sha256', $ref ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $row || empty( $row['ref_expires'] ) || strtotime( $row['ref_expires'] . ' UTC' ) < time() ) {
			return null;
		}
		$token = self::decrypt( $row['token'], self::aad( $row['email'], $row['org_key'], $row['client_id'] ) );
		if ( false === $token || '' === $token ) {
			return null;
		}
		$row['token'] = $token;
		$row['ref']   = $ref;
		return $row;
	}

	/**
	 * The row of the reference sent with this request (POST client_ref from the buttons, or GET ref in the page link).
	 *
	 * @return array|null
	 */
	public static function find_from_request() {
		if ( false === self::$request_row ) {
			// phpcs:disable WordPress.Security.NonceVerification -- the reference itself is the access check
			$ref = '';
			if ( isset( $_POST[ self::REQUEST_KEY ] ) && is_string( $_POST[ self::REQUEST_KEY ] ) ) {
				$ref = wp_unslash( $_POST[ self::REQUEST_KEY ] );
			} elseif ( isset( $_GET[ self::URL_KEY ] ) && is_string( $_GET[ self::URL_KEY ] ) ) {
				$ref = wp_unslash( $_GET[ self::URL_KEY ] );
			}
			// phpcs:enable
			self::$request_row = '' !== $ref ? self::find( $ref ) : null;
		}
		return self::$request_row;
	}

	/** True when the request carries a reference, valid or not. */
	public static function request_has_ref() {
		// phpcs:ignore WordPress.Security.NonceVerification -- read-only check
		return ! empty( $_POST[ self::REQUEST_KEY ] ) || ! empty( $_GET[ self::URL_KEY ] );
	}

	/* ---------------------------------------------------------------------------------------------------------
	 * Encryption (libsodium, XChaCha20-Poly1305; e-mail, organisation and client are bound to the token, so rows
	 * can't be swapped)
	 * ------------------------------------------------------------------------------------------------------ */

	private static function aad( $email, $org_key, $client_id ) {
		return $email . '|' . $org_key . '|' . $client_id;
	}

	private static function key() {
		$secret = defined( 'D2GC_CLIENT_REF_KEY' ) ? D2GC_CLIENT_REF_KEY : wp_salt( 'secure_auth' );
		return sodium_crypto_generichash( 'd2gc-client-ref|' . $secret, '', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES );
	}

	private static function encrypt( $plain, $aad ) {
		$nonce = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
		return $nonce . sodium_crypto_aead_xchacha20poly1305_ietf_encrypt( $plain, $aad, $nonce, self::key() );
	}

	/** @return string|false */
	private static function decrypt( $data, $aad ) {
		$size = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
		if ( strlen( (string) $data ) <= $size ) {
			return false;
		}
		return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt( substr( $data, $size ), $aad, substr( $data, 0, $size ), self::key() );
	}
}
