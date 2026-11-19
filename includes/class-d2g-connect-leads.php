<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Leads for doctor2go.online: a visitor leaves an e-mail address (e.g. to get a result or a page by e-mail) and
 * doctor2go.online sends the e-mails. One entry point for every lead source: d2gc_send_lead().
 *
 * Every lead is stored with its status. When doctor2go.online can not be reached or has a server error, the send is
 * retried by WP-Cron with growing intervals; a refusal (4xx) fails at once. Retries always use the same lead_id,
 * so doctor2go.online can recognise a repeat. Stored leads are deleted after a retention period.
 *
 * Nothing medical is sent or stored here: the source (e.g. a stored AI check) stays with the plugin that made it.
 */
class D2GC_Leads {

	const DB_VERSION        = '1';
	const DB_VERSION_OPTION = 'd2gc_leads_db_version';
	const CRON_HOOK         = 'd2gc_retry_leads';
	const MAX_ATTEMPTS      = 5;

	/** Wait before attempt 2, 3, 4 and 5 (seconds). */
	const RETRY_DELAYS = array( 900, 3600, 21600, 86400 );

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'init', array( __CLASS__, 'ensure_scheduled' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'process_queue' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'add_schedule' ) ); // phpcs:ignore WordPress.WP.CronInterval -- 15 minutes
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'd2gc_leads';
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
				lead_id char(36) NOT NULL,
				email varchar(190) NOT NULL,
				source_type varchar(40) NOT NULL,
				intent varchar(40) NOT NULL,
				doctor_post_id bigint(20) unsigned NOT NULL DEFAULT 0,
				payload longtext NOT NULL,
				status varchar(20) NOT NULL,
				attempts smallint(5) unsigned NOT NULL DEFAULT 0,
				next_attempt datetime DEFAULT NULL,
				last_error text NULL,
				wcc_lead_id varchar(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY lead_id (lead_id),
				KEY status_next (status, next_attempt)
			) " . $wpdb->get_charset_collate() . ';'
		);
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	public static function add_schedule( $schedules ) {
		$schedules['fifteen_minutes'] = array(
			'interval' => 900,
			'display'  => 'Every 15 minutes',
		);
		return $schedules;
	}

	/**
	 * (init) The retry and cleanup job; scheduled again when it is missing.
	 */
	public static function ensure_scheduled() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 300, 'fifteen_minutes', self::CRON_HOOK );
		}
	}

	/**
	 * Stores a lead and sends it to doctor2go.online.
	 *
	 * @param array $args {
	 *     @type string $email          required
	 *     @type string $source_type    required, e.g. quick_ai, skin_condition, doctor_profile
	 *     @type string $intent         required, e.g. save_result, send_article, request_contact
	 *     @type string $source_id      reference of the source, e.g. a check reference or page ID (never a secret token)
	 *     @type string $ai_result_link the link in the e-mail
	 *     @type int    $doctor_post_id doctor post (optional); its WCC user ID is sent as doctor_id
	 *     @type string $language       language of the e-mail (default: the site language)
	 *     @type bool   $reminder       the visitor wants a reminder
	 *     @type array  $consent        given (bool), text_version, date (ISO 8601, default: now)
	 *     @type bool   $confirmation   WordPress also sends a confirmation e-mail (default false: doctor2go.online sends it)
	 * }
	 * @return array|WP_Error lead_id, status (sent|pending|failed), error; WP_Error for invalid input
	 */
	public static function send( $args ) {
		self::maybe_install();

		$email = sanitize_email( (string) ( $args['email'] ?? '' ) );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'd2gc_lead_email', __( 'Please enter a valid e-mail address.', 'doctor2go-connect' ) );
		}
		$source_type = sanitize_key( (string) ( $args['source_type'] ?? '' ) );
		$intent      = sanitize_key( (string) ( $args['intent'] ?? '' ) );
		if ( '' === $source_type || '' === $intent ) {
			return new WP_Error( 'd2gc_lead_source', 'source_type and intent are required.' );
		}

		$doctor_post_id = absint( $args['doctor_post_id'] ?? 0 );
		$consent        = is_array( $args['consent'] ?? null ) ? $args['consent'] : array();
		$language       = sanitize_key( (string) ( $args['language'] ?? '' ) );

		$lead_id = wp_generate_uuid4();
		$payload = array(
			'lead_id'     => $lead_id,
			'email'       => $email,
			'source_type' => $source_type,
			'source_id'   => sanitize_text_field( (string) ( $args['source_id'] ?? '' ) ),
			'intent'      => $intent,
			'ai_result_link' => esc_url_raw( (string) ( $args['ai_result_link'] ?? '' ) ),
			'doctor_id'   => $doctor_post_id ? (string) get_post_meta( $doctor_post_id, 'wcc_user_id', true ) : '',
			'language'    => '' !== $language ? $language : explode( '_', get_locale() )[0],
			'reminder'    => ! empty( $args['reminder'] ),
			'consent'     => array(
				'given'        => ! empty( $consent['given'] ),
				'text_version' => sanitize_text_field( (string) ( $consent['text_version'] ?? '' ) ),
				'date'         => sanitize_text_field( (string) ( $consent['date'] ?? wp_date( 'c' ) ) ),
			),
		);

		/**
		 * Lets themes and plugins add fields to a lead, e.g. campaign or consent data. Runs once, when the lead is
		 * made (retries run in the background, without the visitor's cookies), and can not replace the fields above.
		 *
		 * @param array $extra   fields to add (empty)
		 * @param array $payload the lead as the plugin built it (read only)
		 */
		$extra   = apply_filters( 'd2gc_lead_payload', array(), $payload );
		$payload = $payload + ( is_array( $extra ) ? $extra : array() );

		$now = current_time( 'mysql' );
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::table(),
			array(
				'lead_id'        => $lead_id,
				'email'          => $email,
				'source_type'    => $source_type,
				'intent'         => $intent,
				'doctor_post_id' => $doctor_post_id,
				'payload'        => wp_json_encode( $payload ),
				'status'         => 'pending',
				'attempts'       => 0,
				'next_attempt'   => $now,
				'created_at'     => $now,
				'updated_at'     => $now,
			)
		);

		$result = self::attempt( $lead_id );

		/**
		 * Whether WordPress also sends a confirmation e-mail (e-mail template of the type lead_confirmation).
		 * Off by default: doctor2go.online sends the confirmation itself. For sites where it doesn't, return true
		 * here or pass confirmation => true. Never sent when doctor2go.online refused the lead.
		 *
		 * @param bool  $send    the confirmation argument of the lead, default false
		 * @param array $payload the lead
		 */
		$send = apply_filters( 'd2gc_lead_send_confirmation', ! empty( $args['confirmation'] ), $payload );
		if ( $send && 'failed' !== $result['status'] ) {
			self::send_confirmation( $payload );
		}

		return $result;
	}

	/**
	 * Sends the confirmation e-mail with the e-mail template of the type lead_confirmation, in the language of the lead
	 * (other language or none as fallback). Placeholders: %email%, %link% (the return link), %site_name%.
	 *
	 * @param array $payload the lead
	 * @return bool sent
	 */
	public static function send_confirmation( $payload ) {
		$template = self::email_template( 'lead_confirmation', (string) ( $payload['language'] ?? '' ) );
		if ( ! $template || ! is_email( $payload['email'] ?? '' ) ) {
			return false;
		}

		$url  = esc_url( (string) ( $payload['ai_result_link'] ?? '' ) );
		$link = '' !== $url ? '<a href="' . $url . '">' . $url . '</a>' : '';
		$site = (string) ( get_option( 'd2gc_sender_name' ) ?: get_bloginfo( 'name' ) );
		$map  = array(
			'%email%'     => esc_html( $payload['email'] ),
			'%link%'      => $link,
			'%site_name%' => esc_html( $site ),
		);

		$title   = wp_strip_all_tags( strtr( $template->post_title, $map ) );
		$content = d2gc_html_email( wpautop( strtr( $template->post_content, $map ) ) );
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site . ' <' . get_option( 'd2gc_sender_address' ) . '>',
		);

		return (bool) wp_mail( $payload['email'], $title, $content, $headers );
	}

	/**
	 * The published e-mail template of a type, in the given language when there is one.
	 *
	 * @param string $type     d2g_email_identifier
	 * @param string $language e.g. nl
	 * @return WP_Post|null
	 */
	public static function email_template( $type, $language = '' ) {
		$args = array(
			'post_type'        => 'd2g_emails',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'meta_key'         => 'd2g_email_identifier', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $type, // phpcs:ignore WordPress.DB.SlowDBQuery
			'suppress_filters' => false,
		);
		if ( '' !== $language && function_exists( 'pll_languages_list' ) && in_array( $language, (array) pll_languages_list(), true ) ) {
			$found = get_posts( $args + array( 'lang' => $language ) );
			if ( $found ) {
				return $found[0];
			}
		}
		$found = get_posts( $args + array( 'lang' => '' ) );
		return $found ? $found[0] : null;
	}

	/**
	 * One send attempt for a stored lead; updates its status.
	 *
	 * @param string $lead_id
	 * @return array lead_id, status, error
	 */
	public static function attempt( $lead_id ) {
		global $wpdb;
		$lead = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE lead_id = %s', $lead_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $lead || 'sent' === $lead->status ) {
			return array( 'lead_id' => $lead_id, 'status' => $lead ? $lead->status : 'missing', 'error' => '' );
		}

		$payload   = json_decode( $lead->payload, true );
		$handshake = self::handshake( (int) $lead->doctor_post_id );
		$error     = '';
		$wcc_id    = '';
		$final     = false; // a refusal: retrying the same lead will not help

		if ( is_wp_error( $handshake ) ) {
			$error = $handshake->get_error_message();
		} else {
			$payload['handshake'] = $handshake;
			$response             = wp_remote_post(
				self::endpoint(),
				array(
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 15,
				)
			);
			if ( is_wp_error( $response ) ) {
				$error = $response->get_error_message();
			} else {
				$code = (int) wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				// success = a 2xx answer without an "errors" key (the lead API answers 201, the other calls 200)
				if ( $code < 200 || $code > 299 || ! is_array( $body ) || isset( $body['errors'] ) ) {
					$error = 'HTTP ' . $code . ': ' . substr( wp_strip_all_tags( wp_remote_retrieve_body( $response ) ), 0, 300 );
					// 4xx = doctor2go.online refused the lead; only "timeout" (408) and "too many requests" (429) are worth a retry
					$final = $code >= 400 && $code < 500 && ! in_array( $code, array( 408, 429 ), true );
				} else {
					$wcc_id = (string) ( $body['lead']['id'] ?? $body['id'] ?? '' );
				}
			}
		}

		$attempts = (int) $lead->attempts + 1;
		if ( '' === $error ) {
			$status = 'sent';
			$next   = null;
		} elseif ( $final || $attempts >= self::MAX_ATTEMPTS ) {
			$status = 'failed';
			$next   = null;
		} else {
			$status = 'pending';
			$next   = wp_date( 'Y-m-d H:i:s', time() + self::RETRY_DELAYS[ min( $attempts, count( self::RETRY_DELAYS ) ) - 1 ] );
		}

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::table(),
			array(
				'status'       => $status,
				'attempts'     => $attempts,
				'next_attempt' => $next,
				'last_error'   => $error,
				'wcc_lead_id'  => $wcc_id,
				'updated_at'   => current_time( 'mysql' ),
			),
			array( 'lead_id' => $lead_id )
		);

		if ( '' !== $error && 1 === (int) get_option( 'd2g_debug' ) ) {
			error_log( 'D2GC lead ' . $lead_id . ' attempt ' . $attempts . ': ' . $error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return array( 'lead_id' => $lead_id, 'status' => $status, 'error' => $error );
	}

	/**
	 * (cron) Retries pending leads that are due and deletes leads past the retention period.
	 */
	public static function process_queue() {
		self::maybe_install();
		global $wpdb;
		$due = $wpdb->get_col( $wpdb->prepare( 'SELECT lead_id FROM ' . self::table() . " WHERE status = 'pending' AND next_attempt <= %s ORDER BY id LIMIT 20", current_time( 'mysql' ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $due as $lead_id ) {
			self::attempt( $lead_id );
		}

		/**
		 * Days a lead (with its e-mail address) is kept in WordPress.
		 *
		 * @param int $days
		 */
		$days = max( 1, (int) apply_filters( 'd2gc_lead_retention_days', 30 ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table() . ' WHERE created_at < %s', wp_date( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * The lead API on doctor2go.online.
	 *
	 * @return string
	 */
	public static function endpoint() {
		/**
		 * @param string $url full URL of the lead API
		 */
		return (string) apply_filters( 'd2gc_lead_endpoint', get_option( 'd2gc_api_url_short' ) . 'doclisting/lead' );
	}

	/**
	 * Signs the request like the other doctor2go.online calls, with the key of the lead's doctor.
	 * A lead without a doctor needs another handshake, to be agreed with doctor2go.online (filter d2gc_lead_handshake).
	 *
	 * @param int $doctor_post_id
	 * @return array|WP_Error
	 */
	public static function handshake( $doctor_post_id ) {
		$handshake = null;
		$doc_key   = $doctor_post_id ? (string) get_post_meta( $doctor_post_id, 'user_key', true ) : '';
		if ( '' !== $doc_key ) {
			$time      = (string) time();
			$handshake = array(
				'time'  => $time,
				'token' => $doc_key,
				'hash'  => hash( 'sha256', $time . '_' . $doc_key . '_' . get_option( 'd2gc_wcc_token' ) ),
				'type'  => 'user',
			);
		}

		/**
		 * @param array|null $handshake      null when the lead has no doctor linked to doctor2go.online
		 * @param int        $doctor_post_id
		 */
		$handshake = apply_filters( 'd2gc_lead_handshake', $handshake, $doctor_post_id );

		return is_array( $handshake ) ? $handshake : new WP_Error( 'd2gc_lead_handshake', 'No handshake: the lead has no doctor linked to doctor2go.online.' );
	}

	/**
	 * Newest leads for the settings page. The e-mail address is shortened.
	 *
	 * @param int $limit
	 * @return array
	 */
	public static function get_recent( $limit = 50 ) {
		self::maybe_install();
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT lead_id, email, source_type, intent, status, attempts, next_attempt, last_error, wcc_lead_id, created_at FROM ' . self::table() . ' ORDER BY id DESC LIMIT %d', $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $rows as &$row ) {
			$parts        = explode( '@', $row['email'] );
			$row['email'] = substr( $parts[0], 0, 2 ) . '…@' . ( $parts[1] ?? '' );
		}
		return $rows;
	}
}

/**
 * Stores a lead and sends it to doctor2go.online. See D2GC_Leads::send().
 *
 * @param array $args
 * @return array|WP_Error
 */
function d2gc_send_lead( $args ) {
	return D2GC_Leads::send( $args );
}
