<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-mail verification of patient accounts.
 *
 * - A new registration is unverified (user meta d2gc_email_verified = '0'). The registration mail carries a one-time
 *   confirmation link (placeholder %link% in the mail text, otherwise added at the end).
 * - Accounts without that meta (all accounts that existed before) count as verified.
 * - Until the address is confirmed, the pages with data from doctor2go.online (appointments, electronic medical
 *   records) show a notice with a "send again" button instead. Booking works as before.
 * - On confirmation, and on every login of a verified account, the guest bookings of that e-mail address are linked to
 *   the account (D2GC_Client_Refs::link_to_user()). This is why the address has to be proven first: otherwise anyone
 *   could register with someone else's e-mail address and get their bookings.
 *
 * Only a hash of the link key is stored; a link works once and for KEY_DAYS days.
 */
class D2GC_Email_Verification {

	const META_VERIFIED = 'd2gc_email_verified';
	const META_KEY      = 'd2gc_email_verify_key';
	const QUERY_KEY     = 'd2gc_verify';
	const QUERY_USER    = 'd2gc_user';
	const RESEND_FIELD  = 'd2gc_resend_verification';
	const KEY_DAYS      = 7;

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'handle_link' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_resend' ) );
		add_action( 'wp_login', array( __CLASS__, 'on_login' ), 10, 2 );
	}

	/** Accounts without the meta existed before the verification and count as verified. */
	public static function is_verified( $user_id ) {
		return '0' !== (string) get_user_meta( (int) $user_id, self::META_VERIFIED, true );
	}

	/**
	 * Marks a new account as unverified.
	 *
	 * @return string the confirmation link for the registration mail
	 */
	public static function start( $user_id ) {
		update_user_meta( $user_id, self::META_VERIFIED, '0' );
		return self::new_link( $user_id );
	}

	/** A new one-time link (an older one stops working). It opens the login page in the current language. */
	private static function new_link( $user_id ) {
		$key = bin2hex( random_bytes( 16 ) );
		update_user_meta( $user_id, self::META_KEY, hash( 'sha256', $key ) . '|' . ( time() + self::KEY_DAYS * DAY_IN_SECONDS ) );
		return add_query_arg(
			array(
				self::QUERY_KEY  => $key,
				self::QUERY_USER => (int) $user_id,
			),
			self::page_url( 'login' )
		);
	}

	private static function page_url( $page ) {
		$lang = explode( '_', get_locale() )[0];
		$data = D2G_doc_user_profile::d2gc_page_url( $lang, $page, true );
		return ! empty( $data['url'] ) ? $data['url'] : home_url( '/' );
	}

	/**
	 * (template_redirect) The link from the mail: confirms the address, links the guest bookings and sends the patient
	 * to the dashboard (logged in) or the login page, with a message (email_verified=1|0).
	 */
	public static function handle_link() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the key in the link is the check
		if ( empty( $_GET[ self::QUERY_KEY ] ) ) {
			return;
		}
		$key     = sanitize_key( wp_unslash( $_GET[ self::QUERY_KEY ] ) );
		$user_id = isset( $_GET[ self::QUERY_USER ] ) ? absint( $_GET[ self::QUERY_USER ] ) : 0;
		// phpcs:enable

		$ok     = false;
		$stored = $user_id ? (string) get_user_meta( $user_id, self::META_KEY, true ) : '';
		if ( '' !== $stored ) {
			list( $hash, $expires ) = array_pad( explode( '|', $stored ), 2, 0 );
			$ok = hash_equals( $hash, hash( 'sha256', $key ) ) && time() <= (int) $expires;
		} elseif ( $user_id && get_userdata( $user_id ) && self::is_verified( $user_id ) ) {
			$ok = true; // clicked a second time: already confirmed
		}

		if ( $ok && '' !== $stored ) {
			update_user_meta( $user_id, self::META_VERIFIED, '1' );
			delete_user_meta( $user_id, self::META_KEY );
			D2GC_Client_Refs::link_to_user( $user_id, D2GC_Client_Refs::LINKED_BY_REGISTRATION );
		}

		$target = is_user_logged_in() && get_current_user_id() === $user_id ? self::page_url( 'patient_dashboard' ) : self::page_url( 'login' );
		wp_safe_redirect( add_query_arg( 'email_verified', $ok ? '1' : '0', $target ) );
		exit;
	}

	/** (template_redirect) "Send the confirmation mail again" from the notice. */
	public static function handle_resend() {
		if ( empty( $_POST[ self::RESEND_FIELD ] ) || ! is_user_logged_in() ) {
			return;
		}
		$user = wp_get_current_user();
		$sent = 0;
		if ( wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::RESEND_FIELD ] ) ), self::RESEND_FIELD ) && ! self::is_verified( $user->ID ) ) {
			$sent = self::send_mail( $user, self::new_link( $user->ID ) ) ? 1 : 0;
		}
		wp_safe_redirect( add_query_arg( 'verification_sent', $sent, remove_query_arg( array( 'verification_sent', 'email_verified' ) ) ) );
		exit;
	}

	/** (wp_login) A verified account gets the guest bookings of its e-mail address. */
	public static function on_login( $user_login, $user ) {
		if ( $user instanceof WP_User && self::is_verified( $user->ID ) ) {
			D2GC_Client_Refs::link_to_user( $user->ID, D2GC_Client_Refs::LINKED_BY_LOGIN );
		}
	}

	/** The mail of the "send again" button (the first link comes with the registration mail). */
	private static function send_mail( $user, $link ) {
		$name    = trim( $user->first_name . ' ' . $user->last_name );
		$content = '<p>' . esc_html( sprintf( /* translators: %s: name */ __( 'Hello %s,', 'doctor2go-connect' ), '' !== $name ? $name : $user->display_name ) ) . '</p>'
			. '<p>' . esc_html__( 'Please confirm your e-mail address with the link below. After that you can see your appointments and medical records in your account.', 'doctor2go-connect' ) . '</p>'
			. '<p><a href="' . esc_url( $link ) . '">' . esc_html__( 'Confirm my e-mail address', 'doctor2go-connect' ) . '</a></p>'
			/* translators: %d: number of days */
			. '<p>' . esc_html( sprintf( __( 'The link works once and for %d days.', 'doctor2go-connect' ), self::KEY_DAYS ) ) . '</p>';

		$headers = 'From: ' . get_option( 'd2gc_sender_name' ) . ' <' . get_option( 'd2gc_sender_address' ) . '>' . "\r\n";
		return wp_mail( $user->user_email, __( 'Please confirm your e-mail address', 'doctor2go-connect' ), d2gc_html_email( $content ), $headers );
	}

	/**
	 * The notice for unverified accounts, with the "send again" button.
	 *
	 * @param string $intro the sentence above the button
	 */
	public static function notice_html( $intro = '' ) {
		$intro = '' !== $intro ? $intro : __( 'Please confirm your e-mail address first. We have sent you an e-mail with a confirmation link. After that you will see your appointments and medical records here.', 'doctor2go-connect' );
		ob_start();
		?>
		<div class="alert alert-warning mb-5">
			<p><?php echo esc_html( $intro ); ?></p>
			<form method="post" class="mb-0">
				<input type="hidden" name="<?php echo esc_attr( self::RESEND_FIELD ); ?>" value="<?php echo esc_attr( wp_create_nonce( self::RESEND_FIELD ) ); ?>">
				<button type="submit" class="btn btn-primary"><?php echo esc_html__( 'Send the confirmation e-mail again', 'doctor2go-connect' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/** Messages after the link or the "send again" button (email_verified, verification_sent in the URL). */
	public static function messages_html() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only
		$verified = isset( $_GET['email_verified'] ) ? sanitize_key( wp_unslash( $_GET['email_verified'] ) ) : '';
		$sent     = isset( $_GET['verification_sent'] ) ? sanitize_key( wp_unslash( $_GET['verification_sent'] ) ) : '';
		// phpcs:enable
		$html = '';
		if ( '1' === $verified ) {
			$html .= '<div class="alert alert-success mb-5" role="alert">' . esc_html__( 'Thank you, your e-mail address is confirmed.', 'doctor2go-connect' ) . '</div>';
		} elseif ( '0' === $verified ) {
			$html .= '<div class="alert alert-danger mb-5" role="alert">' . esc_html__( 'This confirmation link is no longer valid. Please log in and request a new one.', 'doctor2go-connect' ) . '</div>';
		}
		if ( '1' === $sent ) {
			$html .= '<div class="alert alert-success mb-5" role="alert">' . esc_html__( 'We have sent you a new confirmation e-mail.', 'doctor2go-connect' ) . '</div>';
		} elseif ( '0' === $sent ) {
			$html .= '<div class="alert alert-danger mb-5" role="alert">' . esc_html__( 'The confirmation e-mail could not be sent. Please try again later.', 'doctor2go-connect' ) . '</div>';
		}
		return $html;
	}
}
