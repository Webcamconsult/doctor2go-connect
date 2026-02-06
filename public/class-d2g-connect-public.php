<?php

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
	 * @var      D2gConnect_Loader    $shortcode_loader    Maintains and registers all shortcode hooks for the plugin.
	 */
	protected $shortcode_loader;

	/**
	 * The loader that's responsible for ajax functions
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      D2gConnect_Loader    $ajax_loader    Maintains and registers all ajax hooks for the plugin.
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/d2g-connect-shortcodes.php';

		$this->shortcode_loader = new D2gConnect_Shortcodes($this->plugin_name, $this->version);
	}
	


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if(get_option('d2g_css_grid') != '1') {
			wp_enqueue_style( $this->plugin_name.'-grid', plugin_dir_url( __FILE__ ) . 'css/grid.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( $this->plugin_name.'-select', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-fancybox', plugin_dir_url( __FILE__ ) . 'css/jquery.fancybox.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-light', plugin_dir_url( __FILE__ ) . 'css/light.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-fontello', plugin_dir_url( __FILE__ ) . 'fonts/fontello/css/fontello.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-flaticon', plugin_dir_url( __FILE__ ) . 'fonts/flaticon/flaticon_mycollection.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-flaticon-derma', plugin_dir_url( __FILE__ ) . 'fonts/wcc-flaticon2/font/flaticon_derma2go.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-cal', plugin_dir_url( __FILE__ ) . 'css/cal-main.min.css', array(), $this->version, 'all' );

		

		if(get_option('d2g_theme_css') != 'no-style'){
			wp_enqueue_style( $this->plugin_name.'-public', plugin_dir_url( __FILE__ ) . 'css/d2g-public.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name.'-overview', plugin_dir_url( __FILE__ ) . 'css/d2g-overview-doctor.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name.'-single', plugin_dir_url( __FILE__ ) . 'css/d2g-single-doctor.css', array(), $this->version, 'all' );
			if(get_option('d2g_theme_css') == 'light' || get_option('d2g_theme_css') == ''){
				wp_enqueue_style( $this->plugin_name.'-light', plugin_dir_url( __FILE__ ) . 'css/d2g-light.css', array(), $this->version, 'all' );
			} elseif(get_option('d2g_theme_css') == 'dark') {
				wp_enqueue_style( $this->plugin_name.'-dark', plugin_dir_url( __FILE__ ) . 'css/d2g-dark.css', array(), $this->version, 'all' );
			}
		}
		
		

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name.'-fancybox', plugin_dir_url( __FILE__ ) . 'js/jquery.fancybox.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'-select', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'-scrollTo', plugin_dir_url( __FILE__ ) . 'js/jquery.scrollTo.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'moment' );
		wp_enqueue_script( $this->plugin_name.'-moment-timezone', plugin_dir_url( __FILE__ ) . 'js/moment-timezone-with-data.min.js', array( 'jquery', 'moment' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'-full-cal-bundle', plugin_dir_url( __FILE__ ) . 'js/fc-index.global.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'-connector', plugin_dir_url( __FILE__ ) . 'js/fc-tz-index.global.min.js', array( 'jquery' ), $this->version, true );


		if(get_option('d2g_recaptcha_site_key') != '' && get_option('deactivate_recapctha_script') != 1){
			wp_enqueue_script( $this->plugin_name.'-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit', array(  ), $this->version, true );
		}

		//like button
		wp_enqueue_script( $this->plugin_name.'-likebtn', plugin_dir_url( __FILE__ ) . 'js/like-button.js', array( 'jquery' ), $this->version, true );
		wp_localize_script($this->plugin_name.'-likebtn', 'likeButtonData', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('like_nonce'),
		]);

		//like button
		wp_enqueue_script( $this->plugin_name.'-availibility', plugin_dir_url( __FILE__ ) . 'js/availibility.js', array( 'jquery' ), $this->version, array('in_footer' => true, 'strategy'  => 'defer') );
		wp_localize_script($this->plugin_name.'-availibility', 'availibilityData', [
			'restUrl' => esc_url_raw( rest_url( 'd2g-connect/v1/' ) ),
    		'nonce'   => wp_create_nonce( 'wp_rest' ),
			'string1' => esc_html__('walk-in consult', 'doctor2go-connect'),
			'string2' => esc_html__('not available', 'doctor2go-connect')		
		]);

		//custom js needs tom come last
		wp_enqueue_script( $this->plugin_name.'-public', plugin_dir_url( __FILE__ ) . 'js/d2g-public.js', array( 'jquery' ), $this->version, true );
		
	}


	/*
    * in this hook all shortcodes are declared 
    */
    public function d2g_register_shortcodes(){
		add_shortcode('d2g_profile_edit', array($this->shortcode_loader, 'd2g_profile_edit'));
		add_shortcode('d2g_doctors_listing', array($this->shortcode_loader, 'd2g_doctors_listing'));
		add_shortcode('d2g_single_doctor_info', array($this->shortcode_loader, 'd2g_single_doctor_info'));
		add_shortcode('d2g_single_doctor_locations', array($this->shortcode_loader, 'd2g_single_doctor_locations'));
		add_shortcode('d2g_single_doctor_calendar', array($this->shortcode_loader, 'd2g_single_doctor_calendar'));
		add_shortcode('d2g_login_form', array($this->shortcode_loader, 'd2g_login_form'));
		add_shortcode('d2g_lost_password_form', array($this->shortcode_loader, 'd2g_lost_password_form'));
		add_shortcode('d2g_reset_password_form', array($this->shortcode_loader, 'd2g_reset_password_form'));
		add_shortcode('d2g_registration_form', array($this->shortcode_loader, 'd2g_registration_form'));
		add_shortcode('d2g_account_settings', array($this->shortcode_loader, 'd2g_account_settings'));
		add_shortcode('d2g_patient_appointments', array($this->shortcode_loader, 'd2g_patient_appointments'));
		add_shortcode('d2g_patient_dashbaord', array($this->shortcode_loader, 'd2g_patient_dashbaord'));
		add_shortcode('d2g_patient_menu', array($this->shortcode_loader, 'd2g_patient_menu'));
		add_shortcode('d2g_search_mask', array($this->shortcode_loader, 'd2g_search_mask'));
		add_shortcode('d2g_liked_posts', array($this->shortcode_loader, 'd2g_liked_posts'));
		add_shortcode('d2g_questionnaires', array($this->shortcode_loader, 'd2g_questionnaires'));
		add_shortcode('d2g_patient_portal', array($this->shortcode_loader, 'd2g_patient_portal'));
		add_shortcode('d2g_public_questionnaire', array($this->shortcode_loader, 'd2g_public_questionnaire'));
		add_shortcode('d2g_public_patient_portal', array($this->shortcode_loader, 'd2g_public_patient_portal'));
		add_shortcode('d2g_appointment_confirmation', array($this->shortcode_loader, 'd2g_appointment_confirmation'));
		
    }



	

	/*
	* this function loads more doctors in the listing
	*/
	public function doctor_call() {

		// Verify nonce first
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'doc_call' ) ) {
			wp_die( 'Security failed' );
		}

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

		global $cssClass;

		$args = array(
			'post_type'   => 'd2g_doctor',
			'posts_per_page' => $posts_per_page,
			'paged'       => $page,
			'post_status' => 'publish',
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
			$checker++;
		}

		if ( $doctorLanguage !== '' && $doctorLanguage !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( $doctorLanguage ),
			);
			$checker++;
		}

		if ( $country !== '' && $country !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( $country ),
			);
			$checker++;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		if ( $intake === 1 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'd2g_intake_call',
					'value' => $intake,
				),
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
					include d2g_locate_template( "content-doctor-{$template}.php" );
				}
			}
		} else {
			echo '<div class="error">' . esc_html__( 'We are very sorry but we could not find any doctors for your search query. Please try using less filters to find a suitable doctor.', 'doctor2go-connect' ) . '</div>';
		}

		?>
		<script>
		jQuery(document).ready(function($){
			setTimeout(function(){
				var newPageNr = !isNaN(parseInt($('#newPageNr').val(),10)) ? parseInt($('#newPageNr').val(),10) : parseInt($('.more_doctors').attr('data-page'));
				var maxPage = <?php echo esc_js($maxPage); ?>;
				if(newPageNr > maxPage){
					$('.more_doctors').hide();
				} else {
					$('.more_doctors').show();
				}
			}, 100);
		});
		</script>
		<?php

		wp_reset_postdata();
		wp_die();
	}



	/*
	* this function gets the count from doctors
	*/
	public function doctor_count_call() {

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
		$specialty      = isset( $_POST['doctor-specialty'] ) ? absint( wp_unslash( $_POST['doctor-specialty'] ) ) : '';
		$doctorLanguage = isset( $_POST['doctor-language'] ) ? absint( wp_unslash( $_POST['doctor-language'] ) ) : '';
		$country        = isset( $_POST['country-origin'] ) ? absint( wp_unslash( $_POST['country-origin'] ) ) : '';
		$intake         = isset( $_POST['intake'] ) ? absint( wp_unslash( $_POST['intake'] ) ) : 0;
		$post_id        = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;

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
			$checker++;
		}

		if ( $doctorLanguage !== '' && $doctorLanguage !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( $doctorLanguage ),
			);
			$checker++;
		}

		if ( $country !== '' && $country !== 0 ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( $country ),
			);
			$checker++;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		if ( $intake === 1 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'd2g_intake_call',
					'value' => $intake,
				),
			);
		}

		if ( $post_id !== 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,
			);
		}

		$doctor_query = new WP_Query( $args );
		$count        = $doctor_query->found_posts;

		echo esc_html( $count );

		wp_reset_postdata();
		wp_die();
	}



	//deprecated
	public function d2g_wp_mail_from($original_email_address) {
		return 'no-reply@dermatology2go.online'; // Replace with your desired email
	}
	
	//deprecated
	public function d2g_wp_mail_from_name($original_email_from) {
		return 'Dermatology2Go'; // Replace with desired sender name
	}


	//this overwrites the reset password mail to return the url to the custom password reset form
	public function d2g_retrieve_password_message( $retrieve_password_message, $key, $user_login, $user_data ) {
		$currLang 		= explode('_', get_locale())[0];
		$d2gAdmin 		= new D2G_doc_user_profile();
		$pageData 		= $d2gAdmin::d2g_page_url($currLang, 'reset_password', true);

		

   		$content = esc_html__( 'Someone has requested a password reset.', 'doctor2go-connect')."\n\n";
		$content .= esc_html__( 'Website name: ', 'doctor2go-connect').get_option('blogname')."\n";
		$content .= esc_html__( 'User name: ', 'doctor2go-connect').$user_data->data->user_email."\n\n";
		$content .= esc_html__( 'If this was not intended, simply ignore this e-mail. Nothing will happen. ', 'doctor2go-connect')."\n\n";
		$content .= esc_html__( 'To reset your password, visit the following address: ', 'doctor2go-connect')."\n";
		$content .= $pageData['url'].'?action=rp&key='.$key.'&login='.$user_login.'&wp_lang='.get_locale();
		$content  = wpautop($content);
   
		$msg =  d2g_html_email($content);
    
		return $msg;

	}

	

	//function for all custom e-mails
    public function send_ajax_d2g_email(){

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'send_ajax_d2g_email' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed.', 'doctor2go-connect' ) );
		}
		$type       = isset($_POST['e-mail'])        ? sanitize_email(wp_unslash($_POST['e-mail']))         : '';
		$from_email = isset($_POST['from_email'])    ? sanitize_email(wp_unslash($_POST['from_email']))     : '';
		$from_name  = isset($_POST['from_name'])     ? sanitize_text_field(wp_unslash($_POST['from_name'])) : '';
		$to_email   = isset($_POST['to_email'])      ? sanitize_email(wp_unslash($_POST['to_email']))       : '';
		$to_name    = isset($_POST['to_name'])       ? sanitize_text_field(wp_unslash($_POST['to_name']))   : '';
		$link       = isset($_POST['app_link'])      ? esc_url_raw(wp_unslash($_POST['app_link']))          : '';
		$title      = isset($_POST['title'])         ? sanitize_text_field(wp_unslash($_POST['title']))     : '';
		$iban       = isset($_POST['iban'])          ? sanitize_text_field(wp_unslash($_POST['iban']))      : __('use IBAN from where the payment was done', 'doctor2go-connect');
		$bic        = isset($_POST['bic'])           ? sanitize_text_field(wp_unslash($_POST['bic']))       : __('use BIC from where the payment was done', 'doctor2go-connect');
		$app_date   = isset($_POST['app_date'])      ? sanitize_text_field(wp_unslash($_POST['app_date']))  : '';
		$comment    = isset($_POST['comment'])       ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';



        $args = array(
            'post_type'     => 'd2g_emails',
            'meta_query'    => array(
                array(
                    'key'      => 'd2g_email_identifier',
                    'value'    => $type
                ),
            ),
        );
        $emailData = get_posts($args);
        
        $content        = str_replace('%to_name%', $to_name, $emailData[0]->post_content);
        $content        = str_replace('%from_name%', $from_name, $content);
        $content        = str_replace('%link%', $link, $content);
        $content        = str_replace('%from_email%', $from_email, $content);
        $content        = str_replace('%to_email%', $to_email, $content);
        $content        = str_replace('%bic%', $bic, $content);
        $content        = str_replace('%iban%', $iban, $content);
        $content        = str_replace('%app_date%', $app_date, $content);
        $content        = str_replace('%comment%', $comment, $content);

        $content        = wpautop($content);

        $msg =  d2g_html_email($content);

        //set header for email and send mail
        $headers = 'From: '.$from_name.' <'.$from_email.'>' . "\r\n";
        $resp = wp_mail($to_email, $title, $msg, $headers);
        
        if($resp == true){
            wp_send_json(['message' => 'mail_send_'.$type]);
        } else {
            wp_send_json(['message' => 'error']);
        }

        
        wp_die();
    }

	////////////////////////////////////////////////////////////////////////////
	//single sign on login (this is called when user clicks link in WCC software)
	public function d2g_sso() {
		// Set cookie for WP language
		if ( ! is_admin() ) {
			setcookie( "wp_lang", get_locale(), time() + 3600, "/" );
		}

		global $post;

		
		$redirect_url  = isset( $_GET['redirect_url'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$wcc_redirect  = isset( $_GET['wcc_redirect'] ) ? sanitize_text_field( wp_unslash( $_GET['wcc_redirect'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$lang          = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect_to   = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$user_key      = isset( $_GET['user_key'] ) ? wp_unslash( $_GET['user_key'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$time          = isset( $_GET['time'] ) ? absint( wp_unslash( $_GET['time'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$hash          = isset( $_GET['hash'] ) ? wp_unslash( $_GET['hash'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$app_id        = isset( $_GET['app'] ) ? absint( wp_unslash( $_GET['app'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$client_token  = isset( $_GET['client_token'] ) ? sanitize_text_field( wp_unslash( $_GET['client_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		

		/*** programmatic login ***/
		if ( $user_key ) {
			$superKey = get_option( 'wcc_token' );
			$unixTime = ( new DateTime() )->format( 'U' );

			if ( $unixTime - $time <= 300000 ) {
				$hashChecker = hash( 'sha256', $time . "_" . $user_key . "_" . $superKey );

				if ( $hashChecker === $hash ) {
					$myUser = get_users( array(
						'meta_key'   => 'user_key',
						'meta_value' => $user_key
					) );

					if ( isset( $myUser[0] ) ) {
						$userName = $myUser[0]->data->user_login;
						$response = programmatic_login( $userName );

						if ( $response === true ) {
							$currLang  = explode( '_', get_locale() )[0];
							$d2gAdmin  = new D2G_doc_user_profile();
							$pageData  = $d2gAdmin::d2g_page_url( $currLang, 'my_profile', true );
							header( "Location:" . $pageData['url'] );
							exit;
						}
					}
				} else {
					echo esc_html__( 'A wrong login hash has been sent', 'doctor2go-connect' );
				}
			} else {
				echo esc_html__( 'The link is not valid anymore', 'doctor2go-connect' );
			}
		}

		// Handle redirects to protected pages or specific pages (confirmation after booking)
		if ( $redirect_url && ! $wcc_redirect ) {
			$d2gAdmin = new D2G_doc_user_profile();
			$pageData = $d2gAdmin::d2g_page_url( $lang, 'login', true );
			$pageData2 = $d2gAdmin::d2g_page_url( $lang, $redirect_to, true );

			// Special case for appointment confirmation
			if ( $redirect_url === 'appointment_confirmation' ) {
				$url = $d2gAdmin::d2g_page_url( $lang, $redirect_url, false );
				header( "Location:" . $url . '?app=' . $app_id . '&client_token=' . $client_token );
				exit;
			}

			if ( is_user_logged_in() ) {
				header( "Location:" . $pageData2['url'] );
			} else {
				header( "Location:" . $pageData['url'] . '?redirect_to=' . urlencode( $pageData2['url'] ) );
			}

			exit;
		}

		$post_meta = get_post_meta( $post->ID );

		if ( isset( $post_meta['d2g_page_accessebility'][0] ) && $post_meta['d2g_page_accessebility'][0] === 'protected' && ! is_user_logged_in() ) {
			$currLang  = explode( '_', get_locale() )[0];
			$d2gAdmin  = new D2G_doc_user_profile();
			$pageData  = $d2gAdmin::d2g_page_url( $currLang, 'login', true );
			header( "Location:" . $pageData['url'] );
			exit;
		}

		if ( isset( $post_meta['d2g_page_accessebility'][0] ) && $post_meta['d2g_page_accessebility'][0] === 'protected_uc' && ! is_user_logged_in() && get_option( 'under_construction' ) == 1 ) {
			$new_url = '/under-construction';
			header( "Location:" . $new_url );
			exit;
		}
		
	}
	
	//redirects are defined for when wp-login.php is triggered 
	public function d2g_login_redirect($redirect_to, $requested_redirect_to, $user) {
		$currLang 		= explode('_', get_locale())[0];
		$d2gAdmin 		= new D2G_doc_user_profile();
		
		
		if (is_wp_error($user)) {
			//Login failed, find out why...
			$error_types = array_keys($user->errors);
			//Error type seems to be empty if none of the fields are filled out
			$error_type = 'both_empty';
			//Otherwise just get the first error (as far as I know there
			//will only ever be one)
			if (is_array($error_types) && !empty($error_types)) {
				
				if(count($error_types) > 1){
					wp_redirect( home_url('/login').'?logout=1'); 
					exit;
				}
				
			}

			$message = "?login=failed";
			wp_redirect( $redirect_to . $message); 
		} else {
			$pageData 		= $d2gAdmin::d2g_page_url($currLang, 'login', true);
			
			
			$redirect_to = isset($_GET['redirect_to']) ? wp_unslash($_GET['redirect_to']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ($redirect_to != '' && urldecode($redirect_to) != $pageData['url']) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_redirect(urldecode($redirect_to)); 
				exit;
			}

			if (in_array('patient', $user->roles)) {
				$pageData = $d2gAdmin::d2g_page_url($currLang, 'patient_dashboard', true);
				wp_redirect($pageData['url']); 
				exit;
			} elseif (in_array('administrator', $user->roles)) {
				wp_redirect(get_site_url().'/wp-admin/'); 
				exit;
			} else {
				$pageData = $d2gAdmin::d2g_page_url($currLang, 'login', true);
				wp_redirect($pageData['url']); 
				exit;
			}

			if (strpos($redirect_to, 'doctor') !== false) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_redirect($redirect_to.'?book=1'); 
				exit;
			}
			
			
		}

		
		exit;
	}

	//ajax function for handeling liked posts
	function d2g_handle_like() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';

		if (!$nonce || !wp_verify_nonce($nonce, 'like_nonce')) {
			wp_send_json_error(['message' => 'Invalid nonce']);
			wp_die();
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
		if (!$post_id || !get_post($post_id)) {
			wp_send_json_error(['message' => 'Invalid post ID']);
			wp_die();
		}

		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$liked_posts = get_user_meta($user_id, 'liked_posts', true);
			$liked_posts = $liked_posts ? $liked_posts : [];

			if (in_array($post_id, $liked_posts, true)) {
				$liked_posts = array_diff($liked_posts, [$post_id]);
				$action = 'unliked';
			} else {
				$liked_posts[] = $post_id;
				$action = 'liked';
			}

			update_user_meta($user_id, 'liked_posts', $liked_posts);
			wp_send_json_success(['message' => 'Success', 'action' => $action]);
		} else {
			wp_send_json_error(['message' => 'User not logged in']);
		}

		wp_die();
	}

	
}
