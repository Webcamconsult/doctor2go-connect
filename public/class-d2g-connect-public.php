<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://plugin.doctor2go.online
 * @since      1.0.0
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/public
 * @author     Webcamconsult
 */

class D2gConnect_Public {

	/**
	 * The loader that's responsible for short codes
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      \D2gConnect_Loader    $shortcode_loader    Maintains and registers all shortcode hooks for the plugin.
	 */
	protected $shortcode_loader;

	/**
	 * The loader that's responsible for ajax functions
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      \D2gConnect_Loader    $ajax_loader    Maintains and registers all ajax hooks for the plugin.
	 */
	protected $ajax_loader;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->load_dependencies();
	}


	/**
	 * Load the required dependencies for this class (functions file with all shortcode functions and helpers)
	 *
	 * Create an instance of the shortcode loader which will be used to register the shortcodes
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once D2GC_PLUGIN_DIR . 'public/d2g-connect-shortcodes.php';

		$this->shortcode_loader = new \D2gConnect_Shortcodes( $this->plugin_name, $this->version );
	}



	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function d2gc_enqueue_styles() {
		if ( get_option( 'd2gc_bootstrap_css' ) != '1' ) {
			wp_enqueue_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( $this->plugin_name . '-select', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-fancybox', plugin_dir_url( __FILE__ ) . 'css/jquery.fancybox.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-light', plugin_dir_url( __FILE__ ) . 'css/light.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-fontello', plugin_dir_url( __FILE__ ) . 'fonts/fontello/css/fontello.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-flaticon', plugin_dir_url( __FILE__ ) . 'fonts/flaticon/flaticon_mycollection.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-flaticon-derma', plugin_dir_url( __FILE__ ) . 'fonts/wcc-flaticon2/font/flaticon_derma2go.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-cal', plugin_dir_url( __FILE__ ) . 'css/cal-main.min.css', array(), $this->version, 'all' );

		if ( get_option( 'd2gc_theme_css' ) != 'no-style' ) {
			if ( get_option( 'd2gc_theme_css' ) == 'light' || get_option( 'd2gc_theme_css' ) == '' ) {
				wp_enqueue_style( $this->plugin_name . '-d2g-light', plugin_dir_url( __FILE__ ) . 'css/d2g-light.css', array(), $this->version, 'all' );
			}
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function d2gc_enqueue_scripts() {
		if ( get_option( 'd2gc_bootstrap_js' ) != '1' ) {
			wp_enqueue_script( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
		}
		wp_enqueue_script( $this->plugin_name . '-fancybox', plugin_dir_url( __FILE__ ) . 'js/jquery.fancybox.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-select', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-scrollTo', plugin_dir_url( __FILE__ ) . 'js/jquery.scrollTo.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'moment' );
		wp_enqueue_script( $this->plugin_name . '-moment-timezone', plugin_dir_url( __FILE__ ) . 'js/moment-timezone-with-data.min.js', array( 'jquery', 'moment' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-full-cal-bundle', plugin_dir_url( __FILE__ ) . 'js/fc-index.global.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-connector', plugin_dir_url( __FILE__ ) . 'js/fc-tz-index.global.min.js', array( 'jquery' ), $this->version, true );

		if ( get_option( 'd2gc_recaptcha_site_key' ) != '' && get_option( 'd2gc_deactivate_recapctha_script' ) != 1 ) {
			$recaptcha_site_key = get_option( 'd2gc_recaptcha_site_key' );
			wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=d2gOnloadCallback&render=explicit', array(), $this->version, true );
			wp_enqueue_script('d2g-recaptcha', plugin_dir_url( __FILE__ ) . 'js/d2g-recaptcha.js', array( 'google-recaptcha' ), '1.0', true);
			wp_localize_script(
				'd2g-recaptcha',
				'd2gRecaptchaVars',
				array(
					'siteKey' => esc_attr( $recaptcha_site_key ),
					'elementIdRegistration' => 'captcha_registration',
					'elementIdLogin' 		=> 'captcha_login',
					'elementIdWalkin' 		=> 'captcha_walkin',
					'elementIdEmail' 		=> 'captcha_email',
					'elementIdCalendar' 	=> 'captcha_calendar'
				)
			);
		}

		//load doctors
		wp_enqueue_script( 'd2g-load-doctors', plugin_dir_url( __FILE__ ) . 'js/load-doctors.js', array( 'jquery' ), $this->version, true );

        //edit booking rules doctors
		wp_enqueue_script( 'd2gc-booking-editor', plugin_dir_url( __FILE__ ) . 'js/booking-editor.js', array( 'jquery' ), $this->version, true );
        wp_localize_script(
            'd2gc-booking-editor',
            'd2gBookingRulesData',
            array(

                /* AJAX + security */
                'ajax' => array(
                    'url'                     => admin_url( 'admin-ajax.php' ),
                    'send_booking_rules_nonce'=> wp_create_nonce( 'send_booking_rules_nonce' ),
                ),

                /* user */
                'user' => array(
                    'role'         => ! empty( $current_user->roles ) ? $current_user->roles[0] : null,
                    'is_logged_in' => is_user_logged_in(),
                ),

                /* i18n */
                'i18n' => array(
                    'unavailable'                   => esc_html__( 'Unavailable', 'doctor2go-connect' ),
                    'end_time_after_start'          => esc_html__( 'End time must be after the start time.', 'doctor2go-connect' ),
                    'time_window_overlap'           => esc_html__( 'This time window overlaps with an existing slot on this day.', 'doctor2go-connect' ),
                    'missing_ajax_url'              => esc_html__( 'd2gBookingRulesData.ajax.url is missing', 'doctor2go-connect' ),
                    'request_failed'                => esc_html__( 'Request failed', 'doctor2go-connect' ),
                    'parse_incoming_rules_error'    => esc_html__( 'Could not parse incoming rules data for simple layout:', 'doctor2go-connect' ),
                    'parse_existing_rules_error'    => esc_html__( 'Could not parse existing rules:', 'doctor2go-connect' ),
                    'add_rule_to_list'              => esc_html__( 'Add Rule to List', 'doctor2go-connect' ),
                    'update_rule'                   => esc_html__( 'Update Rule', 'doctor2go-connect' ),
                    'sunday'                        => esc_html__( 'Sunday', 'doctor2go-connect' ),
                    'monday'                        => esc_html__( 'Monday', 'doctor2go-connect' ),
                    'tuesday'                       => esc_html__( 'Tuesday', 'doctor2go-connect' ),
                    'wednesday'                     => esc_html__( 'Wednesday', 'doctor2go-connect' ),
                    'thursday'                      => esc_html__( 'Thursday', 'doctor2go-connect' ),
                    'friday'                        => esc_html__( 'Friday', 'doctor2go-connect' ),
                    'saturday'                      => esc_html__( 'Saturday', 'doctor2go-connect' ),
                    '1st'                           => esc_html__( '1st', 'doctor2go-connect' ),
                    '2nd'                           => esc_html__( '2nd', 'doctor2go-connect' ),
                    '3rd'                           => esc_html__( '3rd', 'doctor2go-connect' ),
                    '4th'                           => esc_html__( '4th', 'doctor2go-connect' ),
                    'last'                          => esc_html__( 'Last', 'doctor2go-connect' ),
                    'between'                       => esc_html__( 'between', 'doctor2go-connect' ),
                    'and'                           => esc_html__( 'and', 'doctor2go-connect' ),
                    'every'                         => esc_html__( 'Every', 'doctor2go-connect' ),
                    'week'                          => esc_html__( 'week', 'doctor2go-connect' ),
                    'weeks'                         => esc_html__( 'weeks', 'doctor2go-connect' ),
                    'on'                            => esc_html__( 'on', 'doctor2go-connect' ),
                    'of_the_month'                  => esc_html__( 'of the month', 'doctor2go-connect' ),
                    'month'                         => esc_html__( 'month', 'doctor2go-connect' ),
                    'months'                        => esc_html__( 'months', 'doctor2go-connect' ),
                    'the'                           => esc_html__( 'the', 'doctor2go-connect' ),
                    'th_day'                        => esc_html__( 'th day', 'doctor2go-connect' ),
                    'custom_rule'                   => esc_html__( 'Custom Rule', 'doctor2go-connect' ),
                    'success_msg'                   => esc_html__( 'Your bookking rules have been saved, it can take up to 24hours to become active.', 'doctor2go-connect' ),
                ),
            )
        );

		//booking via calendar
		wp_enqueue_script( $this->plugin_name . '-booking', plugin_dir_url( __FILE__ ) . 'js/doctor2go-booking.js', array( 'jquery' ), $this->version, true );

		$site_key     = get_option( 'd2gc_recaptcha_site_key' );
		$d2gAdmin     = new D2G_doc_user_profile();
		$currLang     = explode( '_', get_locale() )[0];
		$only_cal     = false; // keep your original logic here if needed
		$patient_meta = get_user_meta( get_current_user_id() );
		$d2g_profile_data = isset( $GLOBALS['d2g_profile_data'] ) ? $GLOBALS['d2g_profile_data'] : null;
		$in_tabs      = false; // keep your original logic here if needed
		$currentDate  = new DateTime();

		$booking_vars = array(
            'ajax_url'              => admin_url( 'admin-ajax.php' ),
            'load_data_nonce'       => wp_create_nonce( 'load_data_nonce' ),
            'current_date'          => $currentDate->format( 'Y-m-d' ),
            'locale'                => $currLang,
            'only_cal'              => (bool) $only_cal,
            'in_tabs'               => (bool) $in_tabs,
            'd2gc_waiting_room_url' => get_option( 'd2gc_waiting_room_url' ),
            'recaptcha_site_key'    => (string) $site_key,
            'd2g_timezone'          => ! empty( $patient_meta['p_timezone'][0] ) ? $patient_meta['p_timezone'][0] : '',
            'is_user_logged_in'     => is_user_logged_in(),
            'appointments_url'      => $d2gAdmin::d2gc_page_url( $currLang, 'appointments', true )['url'],
            'appointment_conf_url'  => $d2gAdmin::d2gc_page_url( $currLang, 'appointment_confirmation', true )['url'],
            'start_holiday'         => ( $d2g_profile_data && ! empty( $d2g_profile_data->doctor_meta['start_holiday'][0] ) ) ? $d2g_profile_data->doctor_meta['start_holiday'][0] : '',
            'end_holiday'           => ( $d2g_profile_data && ! empty( $d2g_profile_data->doctor_meta['end_holiday'][0] ) ) ? $d2g_profile_data->doctor_meta['end_holiday'][0] : '',
            'i18n'                  => array(
                'walk_in_consult'          => esc_html__( 'walk-in consult', 'doctor2go-connect' ),
                'not_available'            => esc_html__( 'not available', 'doctor2go-connect' ),
                'at'                       => esc_html__( 'at', 'doctor2go-connect' ),
                'video'                    => esc_html__( 'Video', 'doctor2go-connect' ),
                'video_consultation_title' => esc_html__( 'Video consultation', 'doctor2go-connect' ),
                'your_appointment'         => esc_html__( 'Your appointment', 'doctor2go-connect' ),
                'fill_required'            => esc_html__( 'Please fill in all marked fields. ', 'doctor2go-connect' ),
                'invalid_email'            => esc_html__( ' You have entered an invalid e-mail. ', 'doctor2go-connect' ),
                'recaptcha_required'       => esc_html__( ' Please verify that you are not a robot. ', 'doctor2go-connect' ),
                'reservation_success'      => esc_html__( 'Your reservation has been completed successfully.', 'doctor2go-connect' ),
                'reservation_payment_info' => esc_html__( 'Online consultation reservations are valid for 24 hours and require payment within this period. Otherwise, they will be canceled.', 'doctor2go-connect' ),
                'reservation_link_text'    => esc_html__( 'Click here to manage your appointment.', 'doctor2go-connect' ),
                'pay_now'                  => esc_html__( 'pay now', 'doctor2go-connect' ),
                'error_general'            => esc_html__( 'There has been an error, please try an other slot or try later. In case of futher issues, please contact the support.', 'doctor2go-connect' ),
                'holiday_attention'        => esc_html__( 'Attention: I am unavailable in the following periode.', 'doctor2go-connect' ),
                'more_slots_text'          => esc_html__( 'available', 'doctor2go-connect' ),
                'legal_check'              => esc_html__( 'You must accept the privacy rules, disclaimer and the terms and conditions.', 'doctor2go-connect' )
            ),
        );

		wp_add_inline_script( $this->plugin_name . '-booking', 'const d2gBookingVars = ' . wp_json_encode( $booking_vars ) . ';', 'before' );



		// like button
		wp_enqueue_script( $this->plugin_name . '-likebtn', plugin_dir_url( __FILE__ ) . 'js/like-button.js', array( 'jquery' ), $this->version, true );
		wp_localize_script($this->plugin_name . '-likebtn','likeButtonData',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'like_nonce' )
			)
		);

        ////////////////////////////////////////////////////
        // do not touch this
        // this is the big file with all small functions
		// custom js needs to come last
        ////////////////////////////////////
		$current_user = wp_get_current_user();
		wp_enqueue_script( 'd2g-public', plugin_dir_url( __FILE__ ) . 'js/d2g-public.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			'd2g-public',
			'd2gPublicData',
			array(

				/* AJAX + security */
				'ajax' => array(
					'url'          => admin_url( 'admin-ajax.php' ),
					'delete_nonce' => wp_create_nonce( 'd2gc_delete_wcc_appointment_nonce' ),
					'mail_nonce'   => wp_create_nonce( 'd2gc_send_ajax_d2g_email' ),
					'delete_pic'   => wp_create_nonce( 'd2g_delete_pic' ),
				),

				/* user */
				'user' => array(
					'role'         => ! empty( $current_user->roles ) ? $current_user->roles[0] : null,
					'is_logged_in' => is_user_logged_in(),
				),

				/* page state */
				'page' => array(
					'edit_id'   => isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'open'      => isset( $_GET['open'] ) ? sanitize_key( wp_unslash( $_GET['open'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'scroll_to' => isset( $_GET['scroll_to'] ) ? sanitize_key( wp_unslash( $_GET['scroll_to'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				),

				/* mail sender */
				'mail' => array(
					'sender_name'  => get_option( 'd2gc_sender_name' ),
					'sender_email' => get_option( 'd2gc_sender_address' ),
				),

				/* recaptcha */
				'recaptcha' => array(
					'enabled' => get_option( 'd2gc_recaptcha_site_key' ) ? 1 : 0,
				),

				/* UI messages */
				'msg' => array(
					'check1'          => esc_html__( 'Kindly review all fields, as some required information is still missing.', 'doctor2go-connect' ),
					'check2'          => esc_html__( 'You need to provide us with information about the languages that you speak.', 'doctor2go-connect' ),
					'check3'          => esc_html__( 'Please make sure to fill in all required fields.', 'doctor2go-connect' ),

					'privacy'         => esc_html__( 'You must accept the privacy rules, disclaimer and the terms and conditions.', 'doctor2go-connect' ),
					'terms'           => esc_html__( 'You must accept the privacy rules, disclaimer and the terms and conditions.', 'doctor2go-connect' ),
					'disclaimer'      => esc_html__( 'You must accept the privacy rules, disclaimer and the terms and conditions.', 'doctor2go-connect' ),
					'robot'           => esc_html__( 'Please verify that you are not a robot.', 'doctor2go-connect' ),

					'cancel_title'    => esc_html__( 'Cancellation request for appointment.', 'doctor2go-connect' ),
					'mail_patient_ok' => esc_html__( 'Mail to patient has successfully been send.', 'doctor2go-connect' ),
					'mail_patient_err'=> esc_html__( 'There has been a problem sending the mail to the patient.', 'doctor2go-connect' ),
					'mail_doc_ok'     => esc_html__( 'Mail to doctor has successfully been send.', 'doctor2go-connect' ),
					'mail_doc_err'    => esc_html__( 'There has been a problem sending the mail to the doctor.', 'doctor2go-connect' ),

					// written consultation
					'invalid_email'   => esc_html__( 'You have entered an invalid e-mail.', 'doctor2go-connect' ),
				),

				/* strings used for dynamic form rows */
				'str' => array(
					'start'             => esc_html__( 'start date', 'doctor2go-connect' ),
					'end'               => esc_html__( 'end date', 'doctor2go-connect' ),
					'study_area'        => esc_html__( 'study area', 'doctor2go-connect' ),
					'degree'            => esc_html__( 'degree', 'doctor2go-connect' ),
					'institution'       => esc_html__( 'institution', 'doctor2go-connect' ),
					'expertise'         => esc_html__( 'expertise', 'doctor2go-connect' ),
					'position'          => esc_html__( 'position', 'doctor2go-connect' ),
					'organisation'      => esc_html__( 'organisation / company', 'doctor2go-connect' ),
					'title'             => esc_html__( 'title', 'doctor2go-connect' ),
					'web_link'          => esc_html__( 'web link', 'doctor2go-connect' ),
					'journal'           => esc_html__( 'journal', 'doctor2go-connect' ),
					'type_publication'  => esc_html__( 'type of publication', 'doctor2go-connect' ),
					'author'            => esc_html__( 'author', 'doctor2go-connect' ),
					'publication_date'  => esc_html__( 'publication date', 'doctor2go-connect' ),
				),

				/* password messages */
				'password' => array(
					'short'  => esc_html__( 'Your password is too short!', 'doctor2go-connect' ),
					'weak'   => esc_html__( 'Your password is weak!', 'doctor2go-connect' ),
					'good'   => esc_html__( 'Your password is good!', 'doctor2go-connect' ),
					'strong' => esc_html__( 'Your password is strong!', 'doctor2go-connect' ),
				),

			)
		);

	}


	/*
	* in this hook all shortcodes are declared
	*/
	public function d2gc_register_shortcodes() {
		add_shortcode( 'd2gc_profile_edit', array( $this->shortcode_loader, 'd2gc_profile_edit' ) );
		add_shortcode( 'd2gc_doctors_listing', array( $this->shortcode_loader, 'd2gc_doctors_listing' ) );
		add_shortcode( 'd2gc_single_doctor_info', array( $this->shortcode_loader, 'd2gc_single_doctor_info' ) );
		add_shortcode( 'd2gc_single_doctor_locations', array( $this->shortcode_loader, 'd2gc_single_doctor_locations' ) );
		add_shortcode( 'd2gc_single_doctor_calendar', array( $this->shortcode_loader, 'd2gc_single_doctor_calendar' ) );
		// custom login and registration shortcodes
		if(get_option('d2gc_activate_custom_login_registration', 0) == 1) {
			add_shortcode( 'd2gc_login_form', array( $this->shortcode_loader, 'd2gc_login_form' ) );
			add_shortcode( 'd2gc_lost_password_form', array( $this->shortcode_loader, 'd2gc_lost_password_form' ) );
			add_shortcode( 'd2gc_reset_password_form', array( $this->shortcode_loader, 'd2gc_reset_password_form' ) );
			add_shortcode( 'd2gc_registration_form', array( $this->shortcode_loader, 'd2gc_registration_form' ) );
		}
		add_shortcode( 'd2gc_account_settings', array( $this->shortcode_loader, 'd2gc_account_settings' ) );
		add_shortcode( 'd2gc_patient_appointments', array( $this->shortcode_loader, 'd2gc_patient_appointments' ) );
		add_shortcode( 'd2gc_patient_dashbaord', array( $this->shortcode_loader, 'd2gc_patient_dashbaord' ) );
		add_shortcode( 'd2gc_patient_menu', array( $this->shortcode_loader, 'd2gc_patient_menu' ) );
		add_shortcode( 'd2gc_search_mask', array( $this->shortcode_loader, 'd2gc_search_mask' ) );
		add_shortcode( 'd2gc_liked_posts', array( $this->shortcode_loader, 'd2gc_liked_posts' ) );
		add_shortcode( 'd2gc_patient_portal', array( $this->shortcode_loader, 'd2gc_patient_portal' ) );
		add_shortcode( 'd2gc_appointment_confirmation', array( $this->shortcode_loader, 'd2gc_appointment_confirmation' ) );
		add_shortcode( 'doctor_consultancy_tabs', array( $this->shortcode_loader, 'd2gc_single_doctor_consultancy_tabs' ) );
	}





	/*
	* this function loads more doctors in the listing
	*/
	public function d2gc_doctor_call() {

		// Verify nonce first
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'doc_call' ) ) {
			wp_die( 'Security failed' );
		}

		global $cssClass;

		// Safely get POST variables with defaults
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( wp_unslash( $_POST['posts_per_page'] ) ) : 10;
		$page           = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
		$orderby        = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '';
		$order          = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '';
		$meta_key       = isset( $_POST['meta_key'] ) ? sanitize_key( wp_unslash( $_POST['meta_key'] ) ) : '';
		$cssClass       = isset( $_POST['cssClass'] ) ? sanitize_html_class( wp_unslash( $_POST['cssClass'] ) ) : '';
		$specialty      = isset( $_POST['specialty'] ) ? absint( wp_unslash( $_POST['specialty'] ) ) : '';
		$doctorLanguage = isset( $_POST['doctor-language'] ) ? absint( wp_unslash( $_POST['doctor-language'] ) ) : '';
		$country        = isset( $_POST['country-origin'] ) ? absint( wp_unslash( $_POST['country-origin'] ) ) : '';
		$intake         = isset( $_POST['intake'] ) ? absint( wp_unslash( $_POST['intake'] ) ) : '';
		$post_id        = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$template       = isset( $_POST['template'] ) ? sanitize_file_name( wp_unslash( $_POST['template'] ) ) : '';
		$consult_type 	= isset( $_POST['consult_type'] ) ? sanitize_text_field( wp_unslash( $_POST['consult_type'] ) ) : '';



		$args = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => $posts_per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
		);


		if ( $orderby !== '' ) {
			$args['orderby'] = $orderby;
		}

		if ( $order !== '' ) {
			$args['order'] = $order;
		}

		if ( $meta_key !== '' ) {
			$args['meta_key'] = $meta_key;
		}

		$checker = 0;

		if ( $specialty !== '' && $specialty !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-specialty',
				'field'    => 'term_id',
				'terms'    => array( $specialty ),
			);
			++$checker;
		}

		if ( $doctorLanguage !== '' && $doctorLanguage !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( $doctorLanguage ),
			);
			++$checker;
		}

		if ( $country !== '' && $country !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( $country ),
			);
			++$checker;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		// prepare meta_query if you already have one
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}

		// email consult: written_con_price not empty
		if ( 'email' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'written_con_price',
				'value'   => '',
				'compare' => '!=',
			);
		}

		// video consult: d2g_availability_check = 1
		if ( 'video' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_availability_check',
				'value'   => '1',
				'compare' => '=',
			);
		}

		if ( 'walkin' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_walk_in',
				'value'   => '1',
				'compare' => '=',
			);
		}
		
		if ( $post_id !== 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,
			);
		}

		$doctor_query = new WP_Query( $args );
		$maxPage      = $doctor_query->max_num_pages;

		if ( $doctor_query->have_posts() ) {
			while ( $doctor_query->have_posts() ) {
				$doctor_query->the_post();
				if ( $template ) {
					include d2gc_locate_template( "overview-content/content-doctor-{$template}.php" );
				}
			}
		} else {
			echo '<div class="error">' . esc_html__( 'We are very sorry but we could not find any doctors for your search query. Please try using less filters to find a suitable doctor.', 'doctor2go-connect' ) . '</div>';
		}

		wp_reset_postdata();
		wp_die();
	}



	/*
	* this function gets the count from doctors
	*/
	public function d2gc_doctor_count_call() {

		// Verify nonce first
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'doc_call' ) ) {
			wp_die( 'Security failed' );
		}

		// Safely get POST variables with defaults
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( wp_unslash( $_POST['posts_per_page'] ) ) : 10;
		$page           = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
		$orderby        = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '';
		$order          = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '';
		$cssClass       = isset( $_POST['cssClass'] ) ? sanitize_html_class( wp_unslash( $_POST['cssClass'] ) ) : '';
		$specialty      = isset( $_POST['specialty'] ) ? absint( wp_unslash( $_POST['specialty'] ) ) : '';
		$doctorLanguage = isset( $_POST['doctor-language'] ) ? absint( wp_unslash( $_POST['doctor-language'] ) ) : '';
		$country        = isset( $_POST['country-origin'] ) ? absint( wp_unslash( $_POST['country-origin'] ) ) : '';
		$intake         = isset( $_POST['intake'] ) ? absint( wp_unslash( $_POST['intake'] ) ) : 0;
		$post_id        = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$consult_type 	= isset( $_POST['consult_type'] ) ? sanitize_text_field( wp_unslash( $_POST['consult_type'] ) ) : '';

		global $cssClass;

		$args = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => $posts_per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
		);

		if ( $orderby !== '' ) {
			$args['orderby'] = $orderby;
		}

		if ( $order !== '' ) {
			$args['order'] = $order;
		}

		$checker = 0;

		if ( $specialty !== '' && $specialty !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-specialty',
				'field'    => 'term_id',
				'terms'    => array( $specialty ),
			);
			++$checker;
		}

		if ( $doctorLanguage !== '' && $doctorLanguage !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( $doctorLanguage ),
			);
			++$checker;
		}

		if ( $country !== '' && $country !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( $country ),
			);
			++$checker;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		

		// email consult: written_con_price not empty
		if ( 'email' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'written_con_price',
				'value'   => '',
				'compare' => '!=',
			);
		}

		// video consult: d2g_availability_check = 1
		if ( 'video' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_availability_check',
				'value'   => '1',
				'compare' => '=',
			);
		}

		if ( 'walkin' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_walk_in',
				'value'   => '1',
				'compare' => '=',
			);
		}
		
		if ( $post_id !== 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,
			);
		}

		if ( $post_id !== 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,
			);
		}

		$doctor_query = new \WP_Query( $args );
		$count        = $doctor_query->found_posts;

		echo esc_html( $count );

		wp_reset_postdata();
		wp_die();
	}



	// deprecated
	public function d2gc_wp_mail_from( $original_email_address ) {
		return 'no-reply@dermatology2go.online'; // Replace with your desired email
	}

	// deprecated
	public function d2gc_wp_mail_from_name( $original_email_from ) {
		return 'Dermatology2Go'; // Replace with desired sender name
	}


	// this overwrites the reset password mail to return the url to the custom password reset form
	public function d2gc_retrieve_password_message( $retrieve_password_message, $key, $user_login, $user_data ) {
		$wp_lang  = determine_locale();
		$currLang = explode( '_', $wp_lang )[0];

		$d2gAdmin = new D2G_doc_user_profile();
		$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'reset_password', true );

		$content  = esc_html__( 'Someone has requested a password reset.', 'doctor2go-connect' ) . "\n\n";
		$content .= esc_html__( 'Website name: ', 'doctor2go-connect' ) . get_option( 'blogname' ) . "\n";
		$content .= esc_html__( 'User name: ', 'doctor2go-connect' ) . $user_data->data->user_email . "\n\n";
		$content .= esc_html__( 'If this was not intended, simply ignore this e-mail. Nothing will happen. ', 'doctor2go-connect' ) . "\n\n";
		$content .= esc_html__( 'To reset your password, visit the following address: ', 'doctor2go-connect' ) . "\n";
		$content .= add_query_arg(
			array(
				'action'  => 'rp',
				'key'     => $key,
				'login'   => $user_login,
				'wp_lang' => $wp_lang,
			),
			$pageData['url']
		);
		$content = wpautop( $content );

		return d2gc_html_email( $content );
	}



	// function for all custom e-mails
	public function d2gc_send_ajax_d2g_email() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'd2gc_send_ajax_d2g_email' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'doctor2go-connect' ) );
			
		}
		
		$type       = isset( $_POST['e-mail'] ) ? sanitize_text_field( wp_unslash( $_POST['e-mail'] ) ) : '';
		$from_email = isset( $_POST['from_email'] ) ? sanitize_email( wp_unslash( $_POST['from_email'] ) ) : '';
		$from_name  = isset( $_POST['from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['from_name'] ) ) : '';
		$to_email   = isset( $_POST['to_email'] ) ? sanitize_email( wp_unslash( $_POST['to_email'] ) ) : '';
		$to_name    = isset( $_POST['to_name'] ) ? sanitize_text_field( wp_unslash( $_POST['to_name'] ) ) : '';
		$link       = isset( $_POST['app_link'] ) ? esc_url_raw( wp_unslash( $_POST['app_link'] ) ) : '';
		$title      = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$iban       = isset( $_POST['iban'] ) ? sanitize_text_field( wp_unslash( $_POST['iban'] ) ) : __( 'use IBAN from where the payment was done', 'doctor2go-connect' );
		$bic        = isset( $_POST['bic'] ) ? sanitize_text_field( wp_unslash( $_POST['bic'] ) ) : __( 'use BIC from where the payment was done', 'doctor2go-connect' );
		$app_date   = isset( $_POST['app_date'] ) ? sanitize_text_field( wp_unslash( $_POST['app_date'] ) ) : '';
		$comment    = isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';

		$args      = array(
			'post_type'  => 'd2g_emails',
			'meta_query' => array(
				array(
					'key'   => 'd2g_email_identifier',
					'value' => $type,
				),
			),
		);
		$emailData = get_posts( $args );


		$content = str_replace( '%to_name%', $to_name, $emailData[0]->post_content );
		$content = str_replace( '%from_name%', $from_name, $content );
		$content = str_replace( '%link%', $link, $content );
		$content = str_replace( '%from_email%', $from_email, $content );
		$content = str_replace( '%to_email%', $to_email, $content );
		$content = str_replace( '%bic%', $bic, $content );
		$content = str_replace( '%iban%', $iban, $content );
		$content = str_replace( '%app_date%', $app_date, $content );
		$content = str_replace( '%comment%', $comment, $content );

		$content = wpautop( $content );

		$msg = d2gc_html_email( $content );

	

		// set header for email and send mail
		$headers = 'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n";
		$resp    = wp_mail( $to_email, $title, $msg, $headers );

		if ( $resp == true ) {
			wp_send_json( array( 'message' => 'mail_send_' . $type ) );
		} else {
			wp_send_json( array( 'message' => 'error' ) );
		}

		wp_die();
	}

	//
	// single sign on login (this is called when user clicks link in WCC software)
	public function d2gc_sso() {
		if ( ! is_admin() ) {
			setcookie( 'wp_lang', get_locale(), time() + 3600, '/' );
		}

		global $post;

		$redirect_url = isset( $_GET['redirect_url'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_url'] ) ) : '';
		$wcc_redirect = isset( $_GET['wcc_redirect'] ) ? sanitize_text_field( wp_unslash( $_GET['wcc_redirect'] ) ) : '';
		$lang         = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : '';
		$redirect_to  = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';
		$user_key     = isset( $_GET['user_key'] ) ? sanitize_text_field( wp_unslash( $_GET['user_key'] ) ) : '';
		$time         = isset( $_GET['time'] ) ? absint( wp_unslash( $_GET['time'] ) ) : 0;
		$hash         = isset( $_GET['hash'] ) ? sanitize_text_field( wp_unslash( $_GET['hash'] ) ) : '';
		$app_id       = isset( $_GET['app'] ) ? absint( wp_unslash( $_GET['app'] ) ) : 0;
		$client_token = isset( $_GET['client_token'] ) ? sanitize_text_field( wp_unslash( $_GET['client_token'] ) ) : '';

		if ( $user_key ) {
			$super_key = (string) get_option( 'd2gc_wcc_token' );
			$unix_time = time();

			if ( ! $time || $unix_time - $time > 300000 ) {
				wp_die( esc_html__( 'The link is not valid anymore', 'doctor2go-connect' ) );
			}

			$hash_checker = hash( 'sha256', $time . '_' . $user_key . '_' . $super_key );

			if ( ! hash_equals( $hash_checker, $hash ) ) {
				wp_die( esc_html__( 'A wrong login hash has been sent', 'doctor2go-connect' ) );
			}

			$my_user = get_users(
				array(
					'meta_key'   => 'user_key',
					'meta_value' => $user_key,
					'number'     => 1,
				)
			);

			if ( ! empty( $my_user[0] ) ) {
				$response = d2gc_programmatic_login( $my_user[0]->user_login );

				if ( true === $response ) {
					$currLang = explode( '_', get_locale() )[0];
					$d2gAdmin = new D2G_doc_user_profile();
					$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'my_profile', true );
					wp_safe_redirect( $pageData['url'] );
					exit;
				}
			}

			wp_die( esc_html__( 'Login failed', 'doctor2go-connect' ) );
		}

		if ( $redirect_url && ! $wcc_redirect ) {
			if ( ! isset( $_GET['load_data_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['load_data_nonce'] ) ), 'load_data_nonce' ) ) {
				wp_die( esc_html__( 'Invalid security token.', 'doctor2go-connect' ), 403 );
			}

			$d2gAdmin  = new D2G_doc_user_profile();
			$pageData  = $d2gAdmin::d2gc_page_url( $lang, 'login', true );
			$pageData2 = $d2gAdmin::d2gc_page_url( $lang, $redirect_to, true );

			if ( $redirect_url === 'appointment_confirmation' ) {
				$url = $d2gAdmin::d2gc_page_url( $lang, $redirect_url, false );
				$url = add_query_arg(
					array(
						'app'          => $app_id,
						'client_token' => $client_token,
					),
					$url
				);
				wp_safe_redirect( $url );
				exit;
			}

			if ( is_user_logged_in() ) {
				wp_safe_redirect( $pageData2['url'] );
			} else {
				wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( $pageData2['url'] ), $pageData['url'] ) );
			}

			exit;
		}

		if ( ! $post || empty( $post->ID ) ) {
			return;
		}

		$post_meta = get_post_meta( $post->ID );

		if ( isset( $post_meta['d2g_page_accessebility'][0] ) && 'protected' === $post_meta['d2g_page_accessebility'][0] && ! is_user_logged_in() ) {
			$currLang = explode( '_', get_locale() )[0];
			$d2gAdmin = new D2G_doc_user_profile();
			$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'login', true );
			wp_safe_redirect( $pageData['url'] );
			exit;
		}

		if ( isset( $post_meta['d2g_page_accessebility'][0] ) && 'protected_uc' === $post_meta['d2g_page_accessebility'][0] && ! is_user_logged_in() && 1 == get_option( 'd2gc_under_construction' ) ) {
			wp_safe_redirect( home_url( '/under-construction' ) );
			exit;
		}
	}

	// redirects are defined for when wp-login.php is triggered
	public function d2gc_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	

		if ( isset( $_REQUEST['interim-login'] ) || isset( $_REQUEST['wp-auth-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- core WP read-only auth-check flags.
			return $redirect_to;
		}

		$currLang = explode( '_', get_locale() )[0];
		$d2gAdmin = new D2G_doc_user_profile();

		if ( is_wp_error( $user ) ) {

			$error_types = array_keys( $user->errors );

			if ( is_array( $error_types ) && count( $error_types ) > 1 ) {
				wp_safe_redirect( home_url( '/login?logout=1' ) );
				exit;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect_to = isset( $_GET['redirect_to'] )
				? wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ), home_url() ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				: home_url();

			$redirect_url = add_query_arg(
				'login',
				'failed',
				$redirect_to
			);

			wp_safe_redirect( $redirect_url );
			exit;

		} else {

			$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'login', true );

			$redirect_to = isset( $_GET['redirect_to'] )// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				? wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ), '' )// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				: '';

			// Valid custom redirect (not login page itself)
			if ( ! empty( $redirect_to ) && $redirect_to !== $pageData['url'] ) {
				wp_safe_redirect( $redirect_to );
				exit;
			}

			if ( in_array( 'patient', $user->roles, true ) ) {

				$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'patient_dashboard', true );

				wp_safe_redirect( $pageData['url'] );
				exit;

			} elseif ( in_array( 'administrator', $user->roles, true ) ) {

				wp_safe_redirect( admin_url() );
				exit;

			} else {

				$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'login', true );

				wp_safe_redirect( $pageData['url'] );
				exit;
			}
		}
	}

	// ajax function for handeling liked posts
	function d2gc_handle_like() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'like_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			wp_die();
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid post ID' ) );
			wp_die();
		}

		if ( is_user_logged_in() ) {
			$user_id     = get_current_user_id();
			$liked_posts = get_user_meta( $user_id, 'liked_posts', true );
			$liked_posts = $liked_posts ? $liked_posts : array();

			if ( in_array( $post_id, $liked_posts, true ) ) {
				$liked_posts = array_diff( $liked_posts, array( $post_id ) );
				$action      = 'unliked';
			} else {
				$liked_posts[] = $post_id;
				$action        = 'liked';
			}

			update_user_meta( $user_id, 'liked_posts', $liked_posts );
			wp_send_json_success(
				array(
					'message' => 'Success',
					'action'  => $action,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'User not logged in' ) );
		}

		wp_die();
	}


    function d2gc_update_booking_rules() {
        check_ajax_referer( 'send_booking_rules_nonce', 'send_booking_rules_nonce' );

        $wpDocID        = isset( $_POST['wp_doc_id'] ) ? absint( wp_unslash( $_POST['wp_doc_id'] ) ) : 0;
        $rules_json     = isset( $_POST['simple_rules_json'] ) ? wp_unslash( $_POST['simple_rules_json'] ) : '';
        $anchor_date    = isset( $_POST['anchor_date'] ) ? sanitize_text_field( wp_unslash( $_POST['anchor_date'] ) ) : '';
        $type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';


        

        if ( ! $wpDocID ) {
            wp_send_json_error(
                array(
                    'message' => 'Missing or invalid doctor ID.',
                )
            );
        }

        $rules_data = json_decode( $rules_json, true );

        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $rules_data ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Invalid rules JSON.',
                    'json_error' => json_last_error_msg(),
                )
            );
        }

        $clean_rules = array();

        if ( ! empty( $rules_data['rules'] ) && is_array( $rules_data['rules'] ) ) {
            foreach ( $rules_data['rules'] as $rule ) {
                $clean_rules[] = array(
                    'anchor_date'   => isset( $rule['anchor_date'] ) && null !== $rule['anchor_date'] ? absint( $rule['anchor_date'] ) : null,
                    'start_time'    => isset( $rule['start_time'] ) ? sanitize_text_field( $rule['start_time'] ) : '',
                    'end_time'      => isset( $rule['end_time'] ) ? sanitize_text_field( $rule['end_time'] ) : '',
                    'wdays'         => isset( $rule['wdays'] ) ? absint( $rule['wdays'] ) : 0,
                    'week_interval' => isset( $rule['week_interval'] ) ? absint( $rule['week_interval'] ) : 1,
                );
            }
        }

        $docKey    = get_post_meta( $wpDocID, 'user_key', true );
        $docOrgKey = get_post_meta( $wpDocID, 'organisation_key', true );
        $docWCC_ID = get_post_meta( $wpDocID, 'wcc_user_id', true );

        update_post_meta($wpDocID, 'simple_booking_rules', $clean_rules);


        if ( empty( $docKey ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Doctor token not found.',
                )
            );
        }

        $myTime   = new DateTime();
        $unixTime = $myTime->format( 'U' );
        $superKey = get_option( 'd2gc_wcc_token' );
        $myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

        $url = trailingslashit( get_option( 'd2gc_api_url_short' ) ) . 'doclisting/update_bookings_rules_for_user';

        if($type == 'adv'){
            $body = array(
                'handshake' => array(
                    'time'  => (string) $unixTime,
                    'token' => $docKey,
                    'hash'  => $myHash,
                    'type'  => 'user',
                ),
                'rules_json'               => $rules_json,
                'user_id'                  => $docWCC_ID,
                'is_simple_form'           => false 
            );
        } else {
            $body = array(
                'handshake' => array(
                    'time'  => (string) $unixTime,
                    'token' => $docKey,
                    'hash'  => $myHash,
                    'type'  => 'user',
                ),
                'simple_rules_json'        => json_encode($clean_rules),
                'user_id'                  => $docWCC_ID,
                'is_simple_form'           => true 
            );
        }

        

        $args = array(
            'method'      => 'POST',
            'timeout'     => 30,
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
                'Pragma'        => 'no-cache',
            ),
            'body'        => wp_json_encode( $body ),
            'data_format' => 'body',
        );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error(
                array(
                    'message' => __('Remote request failed.', 'doctor2go-connect'),
                    'error'   => $response->get_error_message(),
                )
            );
        }

        $status_code   = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $resp          = json_decode( $response_body );

        if ( 200 !== (int) $status_code ) {
            wp_send_json_error(
                array(
                    'message'      => __('API returned an unexpected status code.', 'doctor2go-connect'),
                    'status_code'  => $status_code,
                    'raw_response' => $response_body,
                )
            );
        }

        if ( $resp->message == 'ok' ) {
            wp_send_json_success(
                array(
                    'message'        => __('Booking rules updated successfully.', 'doctor2go-connect'),
                    'rules'          => $clean_rules,
                    'api_response'   => $resp,
                )
            );
        } else {
            wp_send_json_error(
                array(
                    'message'      => __('API returned an unexpected status code.', 'doctor2go-connect'),
                    'rules'          => $clean_rules,
                    'api_response'   => $resp,
                )
            );
        }
    }
}
