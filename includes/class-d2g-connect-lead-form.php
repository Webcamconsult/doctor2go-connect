<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lead form: [d2gc_lead_form]. The visitor leaves an e-mail address and doctor2go.online sends a link to come back
 * (see D2GC_Leads). Works on any page; other plugins can add their own data:
 *
 * - JavaScript: the event "d2gc:lead-form-submit" on the form, with detail.data (FormData) to append fields to.
 * - PHP: the filter "d2gc_lead_form_args" to change the lead before it is sent, e.g. to store something and set
 *   ai_result_link and source_id. Return a WP_Error there to stop the send with a message for the visitor.
 *
 * Spam protection without other plugins: a hidden honeypot field and limits per IP and per e-mail address.
 * The filter "d2gc_lead_form_check" can add another check (e.g. a captcha plugin).
 *
 * Attributes:
 * source_type (page), intent (send_link), source_id (current post ID), ai_result_link (current page), doctor (doctor
 * post ID, default: the current doctor page), reminder (1 = show the reminder checkbox), dismiss (1 = show a
 * "No thanks" link), newsletter (1 = show the newsletter checkbox when the newsletter setting is on, as in the consult
 * forms), consent_version (v1), consent (text of the consent checkbox; change consent_version with it),
 * badge, title, subtitle, text, button, note, reminder_text, dismiss_text, class.
 *
 * As a small box in the corner instead of a block in the page: popup="idle". It appears after the visitor did nothing
 * (no scrolling, clicking or typing) for "delay" seconds (60), never while a popup window or a form field is in use,
 * and not again for "popup_days" days (30) after it was closed or sent. Of several boxes on a page only the first
 * one that is not switched off (attribute data-popup-off, e.g. set by another script) appears.
 */
class D2GC_Lead_Form {

	const ACTION = 'd2gc_lead';

	/**
	 * Settings of the box on doctor pages (Settings → Advanced → Lead boxes). Off until switched on.
	 * The texts are empty by default (= the translated default text) and can be translated with Polylang / WPML.
	 */
	const OPT_DOCTOR       = 'd2gc_lead_box_doctor';
	const OPT_DOCTOR_DELAY = 'd2gc_lead_box_doctor_delay';
	const OPT_DOCTOR_DAYS  = 'd2gc_lead_box_doctor_days';
	const DOCTOR_TEXTS     = array(
		'title'   => 'd2gc_lead_box_doctor_title',
		'text'    => 'd2gc_lead_box_doctor_text',
		'button'  => 'd2gc_lead_box_doctor_button',
		'consent' => 'd2gc_lead_box_doctor_consent',
	);

	public static function init() {
		add_shortcode( 'd2gc_lead_form', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( __CLASS__, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, array( __CLASS__, 'ajax' ) );
		add_action( 'wp_footer', array( __CLASS__, 'doctor_popup' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'register_doctor_texts' ) );
	}

	/**
	 * @return bool the box on doctor pages is switched on
	 */
	public static function doctor_popup_enabled() {
		return '1' === (string) get_option( self::OPT_DOCTOR );
	}

	/**
	 * Timing of the box on doctor pages, also used by other boxes added there (d2gc_doctor_lead_popup).
	 *
	 * @return array delay (seconds without activity before it appears), popup_days (days it stays away after it was
	 *               closed or sent, 0 = it can appear on every visit)
	 */
	public static function doctor_popup_timing() {
		$days = get_option( self::OPT_DOCTOR_DAYS, '' );
		return array(
			'delay'      => max( 5, absint( get_option( self::OPT_DOCTOR_DELAY, '' ) ) ?: 60 ),
			'popup_days' => '' === (string) $days ? 30 : absint( $days ),
		);
	}

	/**
	 * Default texts of the box on doctor pages, used when the setting is empty.
	 *
	 * @return array
	 */
	public static function doctor_default_texts() {
		$newsletter = function_exists( 'd2gc_newsletter_enabled' ) && d2gc_newsletter_enabled();
		return array(
			'title'   => __( 'Stay informed', 'doctor2go-connect' ),
			'text'    => $newsletter
				? __( 'We will e-mail you a link to this doctor, so you can easily come back later. Sign up for our newsletter too, to stay informed.', 'doctor2go-connect' )
				: __( 'We will e-mail you a link to this doctor, so you can easily come back later.', 'doctor2go-connect' ),
			'button'  => __( 'Send me the link', 'doctor2go-connect' ),
			'consent' => __( 'I agree that my e-mail address is used to send me the link to this doctor.', 'doctor2go-connect' ),
		);
	}

	/**
	 * (admin_init) Texts entered in the settings can be translated with Polylang / WPML.
	 */
	public static function register_doctor_texts() {
		foreach ( self::DOCTOR_TEXTS as $key => $option ) {
			D2GC_Translations::register_string( $option, (string) get_option( $option, '' ), in_array( $key, array( 'text', 'consent' ), true ) );
		}
	}

	/**
	 * (wp_footer) Doctor pages: a small box in the corner after the visitor did nothing for a while, with a link to this
	 * doctor by e-mail (doctor2go.online sends it: source_type doctor_profile, intent send_doctor).
	 * Other plugins can add their own box first with the action d2gc_doctor_lead_popup; the first box that is not
	 * switched off (data-popup-off) appears, see the popup notes at the top.
	 */
	public static function doctor_popup() {
		if ( ! self::doctor_popup_enabled() || ! is_singular( 'd2g_doctor' ) ) {
			return;
		}
		$doctor = get_queried_object_id();
		$timing = self::doctor_popup_timing();

		/**
		 * Before the box on doctor pages, e.g. a box of another plugin with the same timing.
		 *
		 * @param int   $doctor doctor post ID
		 * @param array $timing delay, popup_days
		 */
		do_action( 'd2gc_doctor_lead_popup', $doctor, $timing );

		$texts = self::doctor_default_texts();
		foreach ( self::DOCTOR_TEXTS as $key => $option ) {
			$value = trim( (string) get_option( $option, '' ) );
			if ( '' !== $value ) {
				$texts[ $key ] = D2GC_Translations::translate_string( $option, $value );
			}
		}

		echo self::shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in shortcode()
			array(
				'popup'           => 'idle',
				'delay'           => (string) $timing['delay'],
				'popup_days'      => (string) $timing['popup_days'],
				'source_type'     => 'doctor_profile',
				'intent'          => 'send_doctor',
				'source_id'       => (string) $doctor,
				'doctor'          => (string) $doctor,
				'ai_result_link'  => get_permalink( $doctor ),
				'consent_version' => 'doctor-v1',
				'title'           => $texts['title'],
				'subtitle'        => '',
				'text'            => $texts['text'],
				'button'          => $texts['button'],
				'consent'         => $texts['consent'],
				'class'           => 'd2gc-lead-popup-doctor',
			)
		);
	}

	/**
	 * @param array|string $atts
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$post       = get_post();
		$newsletter = function_exists( 'd2gc_newsletter_enabled' ) && d2gc_newsletter_enabled();
		$a          = shortcode_atts(
			array(
				'source_type'     => 'page',
				'intent'          => 'send_link',
				'source_id'       => $post ? (string) $post->ID : '',
				'ai_result_link'  => $post ? get_permalink( $post ) : home_url( '/' ),
				'doctor'          => $post && 'd2g_doctor' === $post->post_type ? (string) $post->ID : '',
				'reminder'        => '1',
				'dismiss'         => '0',
				'consent_version' => 'v1',
				'consent'         => __( 'I agree that my e-mail address is used to send me this link.', 'doctor2go-connect' ),
				'badge'           => __( 'For later', 'doctor2go-connect' ),
				'title'           => __( 'Not ready yet?', 'doctor2go-connect' ),
				'subtitle'        => __( 'Save this for later.', 'doctor2go-connect' ),
				'text'            => __( 'We will send you a secure link by e-mail, so you can come back to it later.', 'doctor2go-connect' ),
				'button'          => __( 'Send me the link', 'doctor2go-connect' ),
				'note'            => $newsletter
					? __( 'We only use your e-mail address for this, unless you sign up for the newsletter.', 'doctor2go-connect' )
					: __( 'We only use your e-mail address for this. No newsletter.', 'doctor2go-connect' ),
				'newsletter'      => '1',
				'reminder_text'   => __( 'Also send me one reminder to come back to it.', 'doctor2go-connect' ),
				'dismiss_text'    => __( 'No thanks, I will look at it now', 'doctor2go-connect' ),
				'popup'           => '',
				'delay'           => '60',
				'popup_days'      => '30',
				'class'           => '',
			),
			$atts,
			'd2gc_lead_form'
		);

		wp_enqueue_script( 'd2gc-lead-form', D2GC_PLUGIN_URL . 'public/js/d2g-lead-form.js', array(), (string) filemtime( D2GC_PLUGIN_DIR . 'public/js/d2g-lead-form.js' ), true );
		wp_localize_script(
			'd2gc-lead-form',
			'd2gcLeadForm',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => self::ACTION,
				'i18n'    => array(
					'error'   => __( 'Something went wrong. Please try again.', 'doctor2go-connect' ),
					'sending' => __( 'Sending…', 'doctor2go-connect' ),
				),
			)
		);

		$id              = 'd2gc-lead-' . wp_unique_id();
		$show_newsletter = $newsletter && '1' === $a['newsletter'];
		$privacy         = get_privacy_policy_url();
		$popup           = 'idle' === $a['popup'];
		$check_class     = $popup ? 'form-check small' : 'form-check';

		ob_start();
		if ( $popup ) {
			// small box in the corner, shown by d2g-lead-form.js after the visitor did nothing for "delay" seconds
			?>
			<div class="d2gc-lead d2gc-lead-popup card bg-white shadow-lg position-fixed bottom-0 end-0 m-3 d-none <?php echo esc_attr( $a['class'] ); ?>" id="<?php echo esc_attr( $id ); ?>" style="z-index:1040;width:min(400px,calc(100% - 2rem));max-height:calc(100% - 2rem);overflow-y:auto" data-popup-delay="<?php echo esc_attr( absint( $a['delay'] ) ); ?>" data-popup-days="<?php echo esc_attr( absint( $a['popup_days'] ) ); ?>" data-popup-key="<?php echo esc_attr( $a['source_type'] ); ?>" role="dialog" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
				<button type="button" class="btn-close position-absolute top-0 end-0 m-3 d2gc-lead-dismiss" aria-label="<?php echo esc_attr__( 'Close' ); ?>"></button>
				<div class="card-body p-3">
					<div class="d-flex align-items-center gap-2 mb-2 pe-4">
						<span class="d2gc-lead-icon d-inline-flex flex-shrink-0 align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:36px;height:36px" aria-hidden="true"><span class="icon d2cif-mail"></span></span>
						<?php if ( '' !== $a['title'] ) { ?>
							<p class="fs-6 fw-bold text-primary mb-0" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $a['title'] ); ?></p>
						<?php } ?>
					</div>
					<?php if ( '' !== $a['subtitle'] ) { ?>
						<p class="fw-semibold mb-1"><?php echo esc_html( $a['subtitle'] ); ?></p>
					<?php } ?>
					<?php if ( '' !== $a['text'] ) { ?>
						<p class="small mb-3"><?php echo esc_html( $a['text'] ); ?></p>
					<?php } ?>
			<?php
		} else {
			?>
			<div class="d2gc-lead card mb-4 <?php echo esc_attr( $a['class'] ); ?>" id="<?php echo esc_attr( $id ); ?>">
				<div class="card-body p-4">
					<div class="d-flex align-items-center gap-3 mb-3">
						<span class="d2gc-lead-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:56px;height:56px" aria-hidden="true"><span class="icon d2cif-mail fs-3"></span></span>
						<?php if ( '' !== $a['badge'] ) { ?>
							<span class="badge rounded-pill bg-primary-subtle text-primary fs-6 fw-semibold px-3 py-2"><?php echo esc_html( $a['badge'] ); ?></span>
						<?php } ?>
					</div>
					<?php if ( '' !== $a['title'] ) { ?>
						<h2 class="h3 text-primary mb-2"><?php echo esc_html( $a['title'] ); ?></h2>
					<?php } ?>
					<?php if ( '' !== $a['subtitle'] ) { ?>
						<p class="h5 mb-3"><?php echo esc_html( $a['subtitle'] ); ?></p>
					<?php } ?>
					<?php if ( '' !== $a['text'] ) { ?>
						<p class="mb-4"><?php echo esc_html( $a['text'] ); ?></p>
					<?php } ?>
			<?php
		}
		?>
				<form class="d2gc-lead-form" novalidate>
					<input type="hidden" name="source_type" value="<?php echo esc_attr( $a['source_type'] ); ?>">
					<input type="hidden" name="intent" value="<?php echo esc_attr( $a['intent'] ); ?>">
					<input type="hidden" name="source_id" value="<?php echo esc_attr( $a['source_id'] ); ?>">
					<input type="hidden" name="ai_result_link" value="<?php echo esc_url( $a['ai_result_link'] ); ?>">
					<input type="hidden" name="doctor" value="<?php echo esc_attr( $a['doctor'] ); ?>">
					<input type="hidden" name="consent_version" value="<?php echo esc_attr( $a['consent_version'] ); ?>">
					<?php // "lang": Polylang then also answers the AJAX call in the language of the page ?>
					<input type="hidden" name="lang" value="<?php echo esc_attr( function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : explode( '_', get_locale() )[0] ); ?>">
					<?php // honeypot: hidden from people, bots fill it in ?>
					<div style="position:absolute;left:-9999px" aria-hidden="true">
						<label for="<?php echo esc_attr( $id ); ?>-website">Website</label>
						<input type="text" name="website" id="<?php echo esc_attr( $id ); ?>-website" tabindex="-1" autocomplete="off">
					</div>

					<label class="<?php echo $popup ? 'visually-hidden' : 'form-label'; ?>" for="<?php echo esc_attr( $id ); ?>-email"><?php echo esc_html__( 'Your e-mail address', 'doctor2go-connect' ); ?></label>
					<div class="row g-2 mb-3">
						<div class="<?php echo $popup ? 'col' : 'col-md'; ?>">
							<input type="email" class="form-control<?php echo $popup ? '' : ' form-control-lg'; ?>" name="email" id="<?php echo esc_attr( $id ); ?>-email" placeholder="<?php echo esc_attr__( 'name@example.com', 'doctor2go-connect' ); ?>" autocomplete="email" required>
						</div>
						<div class="<?php echo $popup ? 'col-auto' : 'col-md-auto'; ?>">
							<button type="submit" class="btn btn-primary w-100 <?php echo $popup ? 'px-3' : 'px-4 btn-lg'; ?>"><?php echo esc_html( $a['button'] ); ?></button>
						</div>
					</div>

					<div class="<?php echo esc_attr( $check_class ); ?> mb-2">
						<input class="form-check-input" type="checkbox" name="consent" value="1" id="<?php echo esc_attr( $id ); ?>-consent" required>
						<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>-consent">
							<?php echo esc_html( $a['consent'] ); ?>
							<?php if ( $privacy ) { ?>
								<a href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener"><?php echo esc_html__( 'Privacy statement', 'doctor2go-connect' ); ?></a>
							<?php } ?>
						</label>
					</div>
					<?php if ( '1' === $a['reminder'] ) { ?>
						<div class="<?php echo esc_attr( $check_class ); ?> <?php echo $show_newsletter ? 'mb-2' : 'mb-3'; ?>">
							<input class="form-check-input" type="checkbox" name="reminder" value="1" id="<?php echo esc_attr( $id ); ?>-reminder">
							<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>-reminder"><?php echo esc_html( $a['reminder_text'] ); ?></label>
						</div>
					<?php } ?>
					<?php if ( $show_newsletter ) { ?>
						<div class="<?php echo esc_attr( $check_class ); ?> mb-3 d2gc-newsletter-signup">
							<input class="form-check-input" type="checkbox" name="newsletter" value="1" id="<?php echo esc_attr( $id ); ?>-newsletter">
							<label class="form-check-label" for="<?php echo esc_attr( $id ); ?>-newsletter"><?php echo esc_html__( 'I want to sign up for the newsletter', 'doctor2go-connect' ); ?></label>
						</div>
					<?php } ?>
					<?php if ( '' !== $a['note'] ) { ?>
						<p class="small text-muted d-flex align-items-center gap-2 mb-0"><span class="icon d2cif-protected" aria-hidden="true"></span><?php echo esc_html( $a['note'] ); ?></p>
					<?php } ?>

					<div class="d2gc-lead-message alert mt-3 mb-0 d-none<?php echo $popup ? ' small p-2' : ''; ?>" role="status"></div>
				</form>

				<?php if ( ! $popup && '1' === $a['dismiss'] ) { ?>
					<hr class="my-4">
					<p class="text-center mb-0"><a href="#" class="d2gc-lead-dismiss text-muted"><?php echo esc_html( $a['dismiss_text'] ); ?></a></p>
				<?php } ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * (AJAX) Checks the form and sends the lead.
	 */
	public static function ajax() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; protected by honeypot, limits and the d2gc_lead_form_check filter
		$field = function ( $key ) {
			return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		};

		if ( '' !== $field( 'website' ) ) {
			wp_send_json_success( array( 'message' => self::success_message() ) ); // a bot: pretend it worked
		}

		$email = sanitize_email( $field( 'email' ) );
		if ( ! is_email( $email ) ) {
			self::fail( __( 'Please enter a valid e-mail address.', 'doctor2go-connect' ) );
		}
		if ( '1' !== $field( 'consent' ) ) {
			self::fail( __( 'Please agree that we may use your e-mail address for this.', 'doctor2go-connect' ) );
		}

		/**
		 * Extra check before a lead is accepted, e.g. a captcha. Return a WP_Error to refuse it.
		 *
		 * @param true|WP_Error $ok
		 */
		$ok = apply_filters( 'd2gc_lead_form_check', true );
		if ( is_wp_error( $ok ) ) {
			self::fail( $ok->get_error_message() );
		}

		if ( ! self::within_limits( $email ) ) {
			self::fail( __( 'Too many requests. Please try again later.', 'doctor2go-connect' ) );
		}

		$ai_result_link = esc_url_raw( isset( $_POST['ai_result_link'] ) && is_string( $_POST['ai_result_link'] ) ? wp_unslash( $_POST['ai_result_link'] ) : '' );
		// only links to this site, so the form can not be used to send other links in our name
		if ( '' !== $ai_result_link && wp_parse_url( $ai_result_link, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			$ai_result_link = '';
		}
		$doctor = absint( $field( 'doctor' ) );

		$args = array(
			'email'          => $email,
			'source_type'    => sanitize_key( $field( 'source_type' ) ) ?: 'page',
			'intent'         => sanitize_key( $field( 'intent' ) ) ?: 'send_link',
			'source_id'      => $field( 'source_id' ),
			'ai_result_link' => $ai_result_link,
			'doctor_post_id' => $doctor && 'd2g_doctor' === get_post_type( $doctor ) ? $doctor : 0,
			'reminder'       => '1' === $field( 'reminder' ),
			'language'       => sanitize_key( $field( 'lang' ) ), // of the page: an AJAX call does not know it
			'consent'        => array(
				'given'        => true,
				'text_version' => $field( 'consent_version' ),
				'date'         => wp_date( 'c' ),
			),
		);

		/**
		 * Changes a lead from the form before it is sent, e.g. to store data and set ai_result_link and source_id.
		 * Return a WP_Error to stop it; its message is shown to the visitor.
		 *
		 * @param array $args lead, see D2GC_Leads::send()
		 */
		$args = apply_filters( 'd2gc_lead_form_args', $args );
		if ( is_wp_error( $args ) ) {
			self::fail( $args->get_error_message() );
		}

		$result = D2GC_Leads::send( $args );
		if ( is_wp_error( $result ) ) {
			self::fail( $result->get_error_message() );
		}
		if ( 'failed' === $result['status'] ) {
			self::fail( __( 'Something went wrong. Please try again.', 'doctor2go-connect' ) );
		}

		// newsletter checkbox ticked: same sign up as the consult forms (Newsletter plugin, its own opt-in settings)
		d2gc_newsletter_subscribe( $email );

		// pending = doctor2go.online was not reached yet, the retry sends it: the visitor still gets the e-mail
		wp_send_json_success( array( 'message' => self::success_message() ) );
		// phpcs:enable
	}

	private static function success_message() {
		return __( 'Thank you! You will receive the link by e-mail shortly.', 'doctor2go-connect' );
	}

	private static function fail( $message ) {
		wp_send_json_error( array( 'message' => $message ), 400 );
	}

	/**
	 * At most 5 leads per IP address per hour and 3 per e-mail address per day.
	 *
	 * @param string $email
	 * @return bool
	 */
	private static function within_limits( $email ) {
		$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$limits = array(
			'd2gc_lead_ip_' . md5( $ip )                 => array( (int) apply_filters( 'd2gc_lead_limit_ip', 5 ), HOUR_IN_SECONDS ),
			'd2gc_lead_em_' . md5( strtolower( $email ) ) => array( (int) apply_filters( 'd2gc_lead_limit_email', 3 ), DAY_IN_SECONDS ),
		);
		foreach ( $limits as $key => $limit ) {
			if ( (int) get_transient( $key ) >= $limit[0] ) {
				return false;
			}
		}
		foreach ( $limits as $key => $limit ) {
			set_transient( $key, (int) get_transient( $key ) + 1, $limit[1] );
		}
		return true;
	}
}
