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
 * @subpackage d2g-connect/shortcodes
 */

/**
 * Just some shotcodes
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/shortcodes
 * @author     Webcamconsult
 */
class D2gConnect_Shortcodes {


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
	}


	/*
	* shortcode to show the doctor profile edit form
	*/
	public function d2g_profile_edit( $atts ) {
		$a = shortcode_atts(
			array(),
			$atts
		);

		$d2gAdmin = new \D2G_doc_user_profile();

		$currLang  = explode( '_', get_locale() )[0];
		$currUser  = wp_get_current_user();
		$permalink = esc_url( get_permalink() . '?edit=' . ( isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( is_user_logged_in() && ( in_array( 'editor', (array) $currUser->roles ) || in_array( 'administrator', (array) $currUser->roles ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce not required for this internal action.
			$pubProfileID = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- load-only for logged in users, no form processing.
			$pubProfile   = get_post( $pubProfileID );
		} else {

			$currUserID   = $currUser->data->ID;
			$pubProfile   = $d2gAdmin::d2g_get_pub_profile( $currUserID )[0];
			$pubProfileID = (int) $pubProfile->ID;
		}

		$profileStatus = $pubProfile->post_status;

		$doctor_meta = get_post_meta( $pubProfileID );

		// specialties
		$specialties   = get_the_terms( $pubProfileID, 'doctor-specialty' );
		$specArray     = ( $specialties !== false ) ? $this->prepArray( $specialties ) : '';
		$argsSpecialty = array(
			'taxonomy'   => 'doctor-specialty', // empty string(''), false, 0 don't work, and return empty array
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsSpecialty = array(
				'taxonomy'   => 'doctor-specialty',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
				'hide_empty' => false,
				'parent'     => 0, // can be 0, '0', '' too
			);
		}
		$allSpecialities = get_terms( $argsSpecialty );

		// languages
		$languages    = get_the_terms( $pubProfileID, 'doctor-language' );
		$langArray    = ( $languages !== false ) ? $this->prepArray( $languages ) : '';
		$argsLanguage = array(
			'taxonomy'   => 'doctor-language', // empty string(''), false, 0 don't work, and return empty array
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsLanguage = array(
				'taxonomy'   => 'doctor-language',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
		}
		$allLanguages = get_terms( $argsLanguage );

		// countries
		$countries      = get_the_terms( $pubProfileID, 'country-origin' );
		$countriesArray = ( $countries !== false ) ? $this->prepArray( $countries ) : '';
		$argsCountry    = array(
			'taxonomy'   => 'country-origin', // empty string(''), false, 0 don't work, and return empty array
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsCountry = array(
				'taxonomy'   => 'country-origin',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
		}
		$allCountries = get_terms( $argsCountry );

		// featured image
		$img_ID        = get_post_thumbnail_id( $pubProfileID );
		$feat_pic_full = wp_get_attachment_image_src( get_post_thumbnail_id( $pubProfileID ), 'd2g-doc-pic' )[0];

		if ( isset( $doctor_meta['edus'] ) ) {
			$doctor_meta['edus'] = unserialize( $doctor_meta['edus'][0] );
		}
		if ( isset( $doctor_meta['exps'] ) ) {
			$doctor_meta['exps'] = unserialize( $doctor_meta['exps'][0] );
		}
		if ( isset( $doctor_meta['pubs'] ) ) {
			$doctor_meta['pubs'] = unserialize( $doctor_meta['pubs'][0] );
		}

		ob_start();
		?>
		<div class="d2g_doctor-form alignwide"><!-- content-wrap start-->
			<div class="loader simple_hide"><?php esc_html_e( 'Your profile is beeing saved.', 'doctor2go-connect' ); ?></div>
			<div class="row">
				<div class="col-sm-12 outer_form_wrapper">
					<form id="doctor_post" name="new_post" method="post" action="<?php echo esc_html( $permalink ); ?>" enctype="multipart/form-data">
						<div class="row margin-bottom-big">
							<div class="col-sm-12">
								<p id="submitwrap">
									<?php if ( $profileStatus == 'draft' ) { ?>
										<button class="btn btn-default wp-block-button__link save_doctor button" tabindex="6" id="save"><?php esc_html_e( 'save as draft', 'doctor2go-connect' ); ?></button>
										<button class="btn btn-default wp-block-button__link publish_doctor button" tabindex="6" id="submit"><?php esc_html_e( 'publish profile', 'doctor2go-connect' ); ?></button>
										
									<?php } else { ?>
										<button class="btn btn-default wp-block-button__link save_doctor button" tabindex="6" id="save"><?php esc_html_e( 'save profile', 'doctor2go-connect' ); ?></button>
										<button class="btn btn-default wp-block-button__link unpublish_doctor button" tabindex="6" id="unpublish"><?php esc_html_e( 'unpublish profile', 'doctor2go-connect' ); ?></button>   
									<?php } ?>
									<a target="_blank" class="btn-default btn button wp-block-button__link" href="/?post_type=d2g_doctor&p=<?php echo esc_html( $pubProfileID ); ?>&preview=true"><?php esc_html_e( 'preview profile', 'doctor2go-connect' ); ?></a>
								</p>
							</div>
						</div>
						<ul class="pm_tabs row">
							<?php
							$tab = isset( $_GET['tab'] ) ? absint( wp_unslash( $_GET['tab'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only tab state, no form processing.
							?>
							<li class="<?php echo ( $tab != 2 && $tab != 3 ) ? 'active' : ''; ?> tab col-sm-6" data-tab-id="1" data-ref="basic_data">
								<span>1</span><span><?php echo esc_html( __( 'Basics', 'doctor2go-connect' ) ); ?></span>
							</li>
							<li class="<?php echo ( $tab == 2 ) ? 'active' : ''; ?> tab col-sm-6" data-ref="edu" data-tab-id="2">
								<span>2</span><span><?php echo esc_html( __( 'Education & working experience', 'doctor2go-connect' ) ); ?></span>
							</li>
						</ul>
						<div class="error_msg_sales"></div>
						<div class="basic_data pm_d2g_tab_content first <?php echo ( $tab != 2 && $tab != 3 ) ? '' : 'simple_hide'; ?>">
							<div class="row margin-bottom-standard">
								<div class="col-sm-6">
									<h3><?php echo esc_html__( 'Personal information', 'doctor2go-connect' ); ?></h3>
									<div  class="form-table">
										<div class="margin-bottom-standard">
											<label class="small"><?php echo esc_html__( 'Organisation', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="d2g_organisation" value="<?php echo esc_html( $doctor_meta['d2g_organisation'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_organisation]" placeholder="<?php echo esc_html__( 'Organisation', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'First name *', 'doctor2go-connect' ); ?></label>
											<input type="text" class="required" id="d2g_first_name" value="<?php echo esc_html( $doctor_meta['d2g_first_name'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_first_name]" placeholder="<?php echo esc_html__( 'First name *', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Last name *', 'doctor2go-connect' ); ?></label>
											<input type="text" class="required" id="d2g_last_name" value="<?php echo esc_html( $doctor_meta['d2g_last_name'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_last_name]" placeholder="<?php echo esc_html__( 'Last name *', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Title *', 'doctor2go-connect' ); ?></label>
											<input type="text" id="d2g_emp_title" value="<?php echo esc_html( $doctor_meta['d2g_emp_title'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_emp_title]" placeholder="<?php echo esc_html__( 'Title *', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Display name', 'doctor2go-connect' ); ?></label>
											<input type="text" id="d2g_post_title" value="<?php echo esc_html( $pubProfile->post_title ); ?>" tabindex="1" size="40" name="post_title" placeholder="<?php echo esc_html__( 'Display name', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Address', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="address" value="<?php echo esc_html( $doctor_meta['d2g_address'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_address]" placeholder="<?php echo esc_html__( 'Address', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Zip', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="zip" value="<?php echo esc_html( $doctor_meta['d2g_zip'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_zip]" placeholder="<?php echo esc_html__( 'Zip code', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'City', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="city" value="<?php echo esc_html( $doctor_meta['d2g_city'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_city]"  placeholder="<?php echo esc_html__( 'City *', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Country *', 'doctor2go-connect' ); ?></label>
											<select name="tax[country-origin]">
												<option value=""><?php echo esc_html__( 'Country', 'doctor2go-connect' ); ?></option>
												<?php
												foreach ( $allCountries as $country ) {
													$selected = '';
													if ( isset( $countriesArray[ $country->slug ] ) ) {
														$selected = 'selected';
													}
													?>
													<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $country->slug ); ?>">
														<?php
														if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
															echo ( $currLang == 'en' ) ? esc_html( $country->name ) : esc_html( get_term_meta( $country->term_id, 'rudr_text_' . $currLang, true ) );
														} else {
															echo esc_html( $country->name );
														}
														?>
													</option>
												<?php } ?>
											</select>
											<label class="small"><?php echo esc_html__( 'Practice phone number (optional but recommended for use with a reception)', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="tel" value="<?php echo esc_html( $doctor_meta['tel'][0] ); ?>" tabindex="1" size="40" name="meta[tel]"  placeholder="<?php echo esc_html__( 'Tel ', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'Mobile phone number  (optional)', 'doctor2go-connect' ); ?></label>
											<input type="text" class="" id="mobile" value="<?php echo esc_html( $doctor_meta['d2g_mobile'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_mobile]"  placeholder="<?php echo esc_html__( 'Mobile ', 'doctor2go-connect' ); ?>"/>
											<label class="small"><?php echo esc_html__( 'E-mail *', 'doctor2go-connect' ); ?></label>
											<input type="text" class="required" id="email" value="<?php echo esc_html( $doctor_meta['d2g_main_email'][0] ); ?>" tabindex="1" size="40" name="meta[d2g_main_email]"  placeholder="<?php echo esc_html__( 'E-mail *', 'doctor2go-connect' ); ?>"/>
										</div>
										<h3><?php echo esc_html__( 'Registration information', 'doctor2go-connect' ); ?></h3>
										<label class="small"><?php echo esc_html__( 'Registration number', 'doctor2go-connect' ); ?></label>
										<input type="text" class="" id="reg_nr" value="<?php echo esc_html( $doctor_meta['reg_nr'][0] ); ?>" tabindex="1" size="40" name="meta[reg_nr]"  placeholder="<?php echo esc_html__( 'Registration number ', 'doctor2go-connect' ); ?>"/>
										<label class="small"><?php echo esc_html__( 'Country of registration', 'doctor2go-connect' ); ?></label>
										<input type="text" class="" id="reg_country" value="<?php echo esc_html( $doctor_meta['reg_country'][0] ); ?>" tabindex="1" size="40" name="meta[reg_country]"  placeholder="<?php echo esc_html__( 'Country of registration', 'doctor2go-connect' ); ?>"/>   <br />
									</div>
									<?php if ( get_option( 'd2g_local_user' ) == 1 ) { ?>
										<h3><?php echo esc_html__( 'Code for booking calendar', 'doctor2go-connect' ); ?></h3>
										<div class="form-table">
											<input type="text" class="" id="d2g_cal_code" value='<?php echo esc_html( $doctor_meta['d2g_cal_code'][0] ); ?>' tabindex="1" size="40" name="meta[d2g_cal_code]"  placeholder="<?php echo esc_html__( 'Shortcode or iframe for booking calendar', 'doctor2go-connect' ); ?>"/>   <br />
										</div>
										<?php
									} else {
										$currencies = array( 'EUR', 'USD', 'GBP', 'ALL', 'MXN', 'AUD', 'INR', 'AZN', 'BYN', 'BGN', 'HRK', 'CZK', 'DKK', 'GEL', 'HUF', 'ISK', 'CHF', 'MKD', 'MDL', 'NOK', 'PLN', 'RON', 'RUB', 'RSD', 'SEK', 'CHF', 'TRY', 'UAH', 'CAD', 'NZD', 'BRL', 'ZAR' );
										?>
										<h3><?php echo esc_html__( 'payment settings', 'doctor2go-connect' ); ?></h3> 
										<div class="form-table payment_settings mb-m">
											<p><strong><?php echo esc_html__( 'Tariffs for the booking calendar are configured in your Webcamconsult / Doctor2Go dashboard, as outlined in the Getting Started guide. However, prices for email and walk-in consultations must be set separately here, as they are special consults.', 'doctor2go-connect' ); ?></strong></p>
											<label class="small"><?php echo esc_html__( 'Walk-in price & currency', 'doctor2go-connect' ); ?>*</label>
											<input type="text" class="price_input" id="walk_in_price" value="<?php echo esc_html( $doctor_meta['walk_in_price'][0] ); ?>" tabindex="1" size="40" name="meta[walk_in_price]"  placeholder="<?php echo esc_html__( 'Walk-In price', 'doctor2go-connect' ); ?>*"/> 
											<select class="form-control" name="meta[walk_in_currency]" id="walk_in_currency">
												<?php foreach ( $currencies as $currency ) { ?>
														<option <?php echo ( $currency == $doctor_meta['walk_in_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>"><?php echo esc_html( $currency ); ?></option>    
													<?php } ?>
											</select>
											<label class="small"><?php echo esc_html__( 'Written consult price & currency', 'doctor2go-connect' ); ?>*</label>
											<input type="text" class="price_input" id="written_con_price" value="<?php echo esc_html( $doctor_meta['written_con_price'][0] ); ?>" tabindex="1" size="40" name="meta[written_con_price]"  placeholder="<?php echo esc_html__( 'Written consult price', 'doctor2go-connect' ); ?>"/> 
											<select class="form-control" name="meta[written_con_currency]" id="written_con_currency">
												<?php foreach ( $currencies as $currency ) { ?>
														<option <?php echo ( $currency == $doctor_meta['written_con_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>"><?php echo esc_html( $currency ); ?></option>    
													<?php } ?>
											</select>
											<p class="mb-xl simple_hide"><label><input <?php echo ( $doctor_meta['d2g_intake_call'][0] == 1 ) ? 'checked' : ''; ?> name="meta[d2g_intake_call]" type="checkbox" value="1">&nbsp;<span><?php echo esc_html__( 'I offer a free intake call', 'doctor2go-connect' ); ?></span></label></p>
										</div>
									<?php } ?>
									<h3><?php echo esc_html__( 'Holiday settings', 'doctor2go-connect' ); ?></h3>
									<p><strong><?php echo esc_html__( 'Enter your next holiday here to block e-mail consults and to show a notice on your detail page during your absence.', 'doctor2go-connect' ); ?></strong></p>
									<div class="row">
										<div class="col-sm-6">
											<label class="small"><?php echo esc_html__( 'Start date', 'doctor2go-connect' ); ?></label>
											<input type="date" name="meta[start_holiday]" value="<?php echo esc_html( $doctor_meta['start_holiday'][0] ); ?>">
										</div>
										<div class="col-sm-6">
											<label class="small"><?php echo esc_html__( 'End date', 'doctor2go-connect' ); ?></label>
											<input type="date" name="meta[end_holiday]" value="<?php echo esc_html( $doctor_meta['end_holiday'][0] ); ?>">
										</div>
									</div>
								</div>
								<div class="col-sm-6 lists">
									<h3><?php echo esc_html__( 'Profile image', 'doctor2go-connect' ); ?></h3>
									<div class="form-table pic_upload_wrapper margin-bottom-standard">
										<?php if ( ! $feat_pic_full ) { ?>
											<p>
												<input type="file" name="picture_1"/><br />
											</p>
											<p><?php esc_html_e( 'To upload your image you first need to choose one and than save your profile. The image will be displayed after the pagereload.', 'doctor2go-connect' ); ?></p>
										<?php } else { ?>
											<p class="mb-l"><?php esc_html_e( 'Before you can upload a new image, please first delete the old one.', 'doctor2go-connect' ); ?></p>
											<div style="max-width: 400px" class="profile_pic_wrapper">
												<a class="del_img_link button flaticon-dustbin" data-doc-id="<?php echo esc_html( $pubProfileID ); ?>" data-image-id="<?php echo esc_html( $img_ID ); ?>" style="display: inline-block; margin: 15px 0" href="#"></a>
												<img style="width:100%" src="<?php echo esc_html( $feat_pic_full ); ?>">
											</div>
										<?php } ?>
									</div>
									<h3><?php echo esc_html__( 'Languages', 'doctor2go-connect' ); ?>  *</h3>
									<select name="tax[doctor-language][]" multiple="multiple">
										<?php
										foreach ( $allLanguages as $language ) {
											$selected = '';
											if ( isset( $langArray[ $language->slug ] ) ) {
												$selected = 'selected';
											}
											?>
											<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $language->slug ); ?>">
												<?php
												if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
													echo ( $currLang == 'en' ) ? esc_html( $language->name ) : esc_html( get_term_meta( $language->term_id, 'rudr_text_' . $currLang, true ) );
												} else {
													echo esc_html( $language->name );
												}
												?>
											</option>
										<?php } ?>
									</select>
									<div class="extra margin-bottom-standard">
										<label for="sub_title"><input name="meta[sub_title]" id="sub_title" type="checkbox" checked value="y"> <?php echo esc_html__( 'I offer subtitles (this is standard function with in webcamconsult)', 'doctor2go-connect' ); ?></label>
									</div>
									<div id="specialty_wrapper">
										<h3><?php echo esc_html__( 'Fields of study', 'doctor2go-connect' ); ?>  *</h3>
										<select name="tax[doctor-specialty][]" multiple="multiple">
											<?php
											foreach ( $allSpecialities as $speciality ) {
												$selected = '';
												if ( isset( $specArray[ $speciality->slug ] ) ) {
													$selected = 'selected';
												}
												?>
												<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $speciality->slug ); ?>">
													<?php
													if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
														echo ( $currLang == 'en' ) ? esc_html( $speciality->name ) : esc_html( get_term_meta( $speciality->term_id, 'rudr_text_' . $currLang, true ) );
													} else {
														echo esc_html( $speciality->name );
													}
													?>
												</option>
											<?php } ?>
										</select>
									</div>
									<h3><?php echo esc_html__( 'About your self', 'doctor2go-connect' ); ?></h3>
									<p><strong><?php echo esc_html__( 'Please provide a brief biography highlighting your background and strengths for visitors.', 'doctor2go-connect' ); ?></strong></p>
									<div class="form-table mb-l">
										<?php wp_editor( $pubProfile->post_content, 'docdesc' ); ?>
									</div>
								</div>
							</div>
						</div>

						<div class="<?php echo ( $tab == 2 ) ? '' : 'simple_hide'; ?> pm_d2g_tab_content edu exp_edu">
							<h3><?php echo esc_html__( 'education', 'doctor2go-connect' ); ?></h3>
							<div class="form-table edu_wrapper">
								<?php $counter = 0; ?>
								<div class="row exp_edu">
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'start & end date', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?></strong>
									</div>
								</div>
								<?php if ( isset( $doctor_meta['edus'] ) ) { ?>
									<?php foreach ( $doctor_meta['edus'] as $edu ) { ?>
										<div class="row exp_edu edu_<?php echo esc_html( $counter ); ?>">
											<?php if ( $counter > 0 ) { ?>
												<a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>
											<?php } ?>
											<div class="col-sm-3">
												<div class="row">
													<div class="col-sm-6">
														<input type="text" class="" id="d2g_exp_edu_date" value="<?php echo esc_html( $edu['d2g_exp_edu_start_date'] ); ?>" tabindex="1" size="40" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
													</div>
													<div class="col-sm-6">
														<input type="text" class="" id="d2g_exp_edu_study" value="<?php echo esc_html( $edu['d2g_exp_edu_end_date'] ); ?>" tabindex="1" size="40" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>		
													</div>
												</div>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_study" value="<?php echo esc_html( $edu['d2g_exp_edu_study'] ); ?>" tabindex="1" size="40" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_study]" placeholder="<?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_title" value="<?php echo esc_html( $edu['d2g_exp_edu_title'] ); ?>" tabindex="1" size="40" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_org" value="<?php echo esc_html( $edu['d2g_exp_edu_org'] ); ?>" tabindex="1" size="40" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
										<?php ++$counter; ?>
									<?php } ?>
								<?php } else { ?>
									<div class="row exp_edu edu_0">
										<div class="col-sm-3">
											<input style="width:50%; float:left" type="text" class="" id="d2g_exp_edu_date" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
											<input style="width:50%; display:inline-block" type="text" class="" id="d2g_exp_edu_study" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_study" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_study]" placeholder="<?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_title" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_org" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?>"/>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="btn_wrapper mb-l"><a class="btn btn-default wp-block-button__link add_edu invert" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#"><?php echo esc_html__( 'add an extra education', 'doctor2go-connect' ); ?></a></div>
							<h3><?php echo esc_html__( 'working experience', 'doctor2go-connect' ); ?></h3>
							<div class="form-table exp_wrapper">
								<?php $counter = 0; ?>
								<div class="row exp_edu">
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'start & end date', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'expertise', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'position', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-3">
										<strong><?php echo esc_html__( 'company', 'doctor2go-connect' ); ?></strong>
									</div>
								</div>
								<?php if ( isset( $doctor_meta['exps'] ) ) { ?>
									<?php foreach ( $doctor_meta['exps'] as $exp ) { ?>
										<div class="row exp_edu exp_<?php echo esc_html( $counter ); ?>">
											<?php if ( $counter > 0 ) { ?>
												<a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>
											<?php } ?>
											<div class="col-sm-3">
												<div class="row">
													<div class="col-sm-6">
														<input type="text" class="" id="d2g_exp_edu_date" value="<?php echo esc_html( $exp['d2g_exp_edu_start_date'] ); ?>" tabindex="1" size="40" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
													</div>
													<div class="col-sm-6">
														<input type="text" class="" id="d2g_exp_edu_study" value="<?php echo esc_html( $exp['d2g_exp_edu_end_date'] ); ?>" tabindex="1" size="40" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
													</div>
												</div>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_expertise" value="<?php echo esc_html( $exp['d2g_exp_edu_expertise'] ); ?>" tabindex="1" size="40" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_expertise]" placeholder="<?php echo esc_html__( 'exptertise', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_title" value="<?php echo esc_html( $exp['d2g_exp_edu_title'] ); ?>" tabindex="1" size="40" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'position', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-3">
												<input type="text" class="" id="d2g_exp_edu_org" value="<?php echo esc_html( $exp['d2g_exp_edu_org'] ); ?>" tabindex="1" size="40" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'company', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
										<?php ++$counter; ?>
									<?php } ?>
								<?php } else { ?>
									<div class="row exp_edu exp_0">
										<div class="col-sm-3">
											<div class="row">
												<div class="col-sm-6">
													<input type="text" class="" id="d2g_exp_edu_date" tabindex="1" size="40" name="meta[exps][0][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
												</div>
												<div class="col-sm-6">
													<input type="text" class="" id="d2g_exp_edu_study" tabindex="1" size="40" name="meta[exps][0][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
												</div>
											</div>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_expertise" tabindex="1" size="40" name="meta[exps][0][d2g_exp_edu_expertise]" placeholder="<?php echo esc_html__( 'exptertise', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_title"  tabindex="1" size="40" name="meta[exps][0][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-3">
											<input type="text" class="" id="d2g_exp_edu_org"  tabindex="1" size="40" name="meta[exps][0][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'company', 'doctor2go-connect' ); ?>"/>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="btn_wrapper mb-l"><a class="btn btn-default wp-block-button__link add_exp invert" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#"><?php echo esc_html__( 'add an extra working experience', 'doctor2go-connect' ); ?></a></div>
							<h3><?php echo esc_html__( 'publications', 'doctor2go-connect' ); ?></h3>
							<div class="form-table pub_wrapper">
								<?php $counter = 0; ?>
								<div class="row exp_edu">
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'title', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'author', 'doctor2go-connect' ); ?></strong>
									</div>
									<div class="col-sm-2">
										<strong><?php echo esc_html__( 'publication Date', 'doctor2go-connect' ); ?></strong>
									</div>
								</div>
								<?php if ( isset( $doctor_meta['pubs'] ) ) { ?>
									<?php foreach ( $doctor_meta['pubs'] as $exp ) { ?>
										<div class="row exp_edu exp_<?php echo esc_html( $counter ); ?>">
											<?php if ( $counter > 0 ) { ?>
												<a class="remove_btn btn-add" href="#"><span class="icon-minus-circled"></span> </a>
											<?php } ?>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_title" value="<?php echo esc_html( $exp['d2g_pub_title'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_link" value="<?php echo esc_html( $exp['d2g_pub_link'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_link]" placeholder="<?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_journal" value="<?php echo esc_html( $exp['d2g_pub_journal'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_journal]" placeholder="<?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_type" value="<?php echo esc_html( $exp['d2g_pub_type'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_type]" placeholder="<?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_author" value="<?php echo esc_html( $exp['d2g_pub_author'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_author]" placeholder="<?php echo esc_html__( 'author', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_date" value="<?php echo esc_html( $exp['d2g_pub_date'] ); ?>" tabindex="1" size="40" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_date]" placeholder="<?php echo esc_html__( 'publication date', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
										<?php ++$counter; ?>
									<?php } ?>
								<?php } else { ?>
									<div class="row exp_edu exp_0">
										<div class="col-sm-2">
											<input type="text" class="" id="d2g_pub_title"  tabindex="1" size="40" name="meta[pubs][0][d2g_pub_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-2">
											<input type="text" class="" id="d2g_pub_link"  tabindex="1" size="40" name="meta[pubs][0][d2g_pub_link]" placeholder="<?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-2">
											<input type="text" class="" id="d2g_pub_journal" tabindex="1" size="40" name="meta[pubs][0][d2g_pub_journal]" placeholder="<?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?>"/>
										</div>
										<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_type" tabindex="1" size="40" name="meta[pubs][0][d2g_pub_type]" placeholder="<?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?>"/>
											</div>
											<div class="col-sm-2">
												<input type="text" class="" id="d2g_pub_author" tabindex="1" size="40" name="meta[pubs][0][d2g_pub_author]" placeholder="<?php echo esc_html__( 'author', 'doctor2go-connect' ); ?>"/>
											</div>
										<div class="col-sm-2">
											<input type="text" class="" id="d2g_pub_date"  tabindex="1" size="40" name="meta[pubs][0][d2g_pub_date]" placeholder="<?php echo esc_html__( 'publication Date', 'doctor2go-connect' ); ?>"/>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="btn_wrapper mb-l"><a class="btn btn-default wp-block-button__link add_pub invert" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#"><?php echo esc_html__( 'add an extra publication', 'doctor2go-connect' ); ?></a></div>
						</div>
						<input type="hidden" name="doc_action" value="doc_update" />
						<input type="hidden" name="d2g_lang" value="<?php echo esc_html( $currLang ); ?>" />
						<input type="hidden" name="update_id" value="<?php echo esc_html( $pubProfileID ); ?>" />
						<input type="hidden" id="post_status" name="post_status" value="<?php echo esc_html( $profileStatus ); ?>" />
						<div class="row margin-bottom-big">
							<h3 class="simple_hide check_url"><?php esc_html_e( 'We are checking your data, this might take a while', 'doctor2go-connect' ); ?></h3>
							<div class="col-sm-12">
								<p id="submitwrap">
									<?php if ( $profileStatus == 'draft' ) { ?>
										<button class="btn btn-default wp-block-button__link save_doctor button" tabindex="6" id="save"><?php esc_html_e( 'save as draft', 'doctor2go-connect' ); ?></button>
										<button class="btn btn-default wp-block-button__link publish_doctor button" tabindex="6" id="submit"><?php esc_html_e( 'publish profile', 'doctor2go-connect' ); ?></button>
										
									<?php } else { ?>
										<button class="btn btn-default wp-block-button__link save_doctor button" tabindex="6" id="save"><?php esc_html_e( 'save profile', 'doctor2go-connect' ); ?></button>
										<button class="btn btn-default wp-block-button__link unpublish_doctor button" tabindex="6" id="unpublish"><?php esc_html_e( 'unpublish profile', 'doctor2go-connect' ); ?></button>   
									<?php } ?>
									<a target="_blank" class="btn-default btn button wp-block-button__link" href="/?post_type=d2g_doctor&p=<?php echo esc_html( $pubProfileID ); ?>&preview=true"><?php esc_html_e( 'preview profile', 'doctor2go-connect' ); ?></a>
								</p>
								<?php wp_nonce_field( 'doc-update' ); ?>
							</div>
						</div>
					</form>
				
				</div>
			</div>
		</div>
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();


		/* Return the content as usual */
		return $sc;
	}


	/*
	* shortcode to show the doctor listing
	*/
	public function d2g_doctors_listing( $atts ) {

		global $cssClass;

		$a = shortcode_atts(
			array(
				'posts_per_page' => 6,
				'template'       => 'grid',
				'columns'        => '3',
				'wrapper_class'  => '',
				'orderby'        => '',
				'order'          => '',
				'meta_key'       => '',
			),
			$atts
		);

		if ( $a['columns'] == '4' ) {
			$cssClass = 'col-sm-3';
		} elseif ( $a['columns'] == '3' ) {
			$cssClass = 'col-sm-4';
		} elseif ( $a['columns'] == '2' ) {
			$cssClass = 'col-sm-6';
		} else {
			$cssClass = 'col-sm-12';
		}

		$args = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => $a['posts_per_page'],
		);

		if ( $a['orderby'] != '' ) {
			$args['orderby'] = $a['orderby'];
		}

		if ( $a['order'] != '' ) {
			$args['order'] = $a['order'];
		}

		if ( $a['meta_key'] != '' ) {
			$args['meta_key'] = $a['meta_key'];
		}

		$specialty      = isset( $_GET['doctor-specialty'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor-specialty'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$doctorLanguage = isset( $_GET['doctor-language'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor-language'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$country        = isset( $_GET['country-origin'] ) ? sanitize_text_field( wp_unslash( $_GET['country-origin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$intake         = isset( $_GET['intake'] ) ? sanitize_text_field( wp_unslash( $_GET['intake'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.

		$checker = 0;

		if ( $specialty != '' && $specialty != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-specialty',
				'field'    => 'term_id',
				'terms'    => array( (int) $specialty ),
			);
			++$checker;
		}

		if ( $doctorLanguage != '' && $doctorLanguage != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( (int) $doctorLanguage ),
			);
			++$checker;
		}

		if ( $country != '' && $country != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( (int) $country ),
			);
			++$checker;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		if ( $intake == 1 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'd2g_intake_call',
					'value' => $intake,
				),
			);
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Single post view by ID.
		if ( $post_id && $post_id != 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,  // Sanitized ID
			);
		}

		$the_query = new WP_Query( $args );
		ob_start();

		if ( $the_query->have_posts() ) {
			$maxPage = $the_query->max_num_pages;
			?>
			<div id="doctor_wrapper_outer" class=" <?php echo esc_html( $a['wrapper_class'] ); ?>">
				<div id="doctor_wrapper" class="row <?php echo esc_html( $a['template'] ); ?> ">
					<?php
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						include d2g_locate_template( 'content-doctor-' . $a['template'] . '.php' );
					}
					?>
				</div>
			</div>
			<?php if ( $maxPage > 1 ) { ?>
				<div class="center load_more_btn_wrapper"><a class="more_doctors button btn btn-default wp-block-button__link" data-page="2"><?php echo esc_html__( 'load more', 'doctor2go-connect' ); ?></a></div>
			<?php } ?>
			<input id="cssClass" type="hidden" value="<?php echo esc_html( $cssClass ); ?>">
			<input id="posts_per_page" type="hidden" value="<?php echo esc_html( $a['posts_per_page'] ); ?>">
			<input id="orderby" type="hidden" value="<?php echo esc_html( $a['orderby'] ); ?>">
			<input id="order" type="hidden" value="<?php echo esc_html( $a['order'] ); ?>">
			<input id="template" type="hidden" value="<?php echo esc_html( $a['template'] ); ?>">
			<input id="meta_key" type="hidden" value="<?php echo esc_html( $a['meta_key'] ); ?>">
			<input id="newPageNr" type="hidden" value="">
			<div id="end"></div>
			<?php

			wp_localize_script(
				'd2g-load-doctors',
				'myShortcodeData',
				array(
					'template' 	=> $a['template'],
					'maxPage'  	=> $maxPage,
					'_wpnonce' 	=> wp_create_nonce( 'doc_call' ),
					'ajax_url' 	=> admin_url( 'admin-ajax.php' ),
					'posts_per_page' => $a['posts_per_page'],
					'loading_checker' => get_option( 'd2g_load_availability_info' ) == 1 ? 1 : 0
				)
			);
			

		} else {
			// no posts found
		}
		/* Restore original Post Data */
		wp_reset_postdata();

		?>
	
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();

		/* Return the content as usual */
		return $sc;
	}


	//
	// SC displays the search mask, the SC needs to be in the same page as the doc list SC
	public function d2g_search_mask( $atts ) {

		$a = shortcode_atts(
			array(
				'view'          => '',
				'stand_alone'   => 'false',
				'ul_class'      => '',
				'wrapper_class' => '',

			),
			$atts
		);
		$currLang = explode( '_', get_locale() )[0];
		$d2gAdmin = new D2G_doc_user_profile();
		$pageDoc  = $d2gAdmin::d2g_page_url( $currLang, 'doctors', false );
		// cities
		$argsSpecialty = array(
			'taxonomy' => 'doctor-specialty', // empty string(''), false, 0 don't work, and return empty array
			// 'parent' => 0, //can be 0, '0', '' too
			'orderby'  => 'name',
			'order'    => 'ASC',
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsSpecialty = array(
				'taxonomy'   => 'doctor-specialty',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
				// 'parent' => 0, //can be 0, '0', '' too
			);
		}
		$specialties = get_terms( $argsSpecialty );

		foreach ( $specialties as $term ) {
			$specialties_by_parent[ $term->parent ][] = $term;
		}

		// venues
		$argsLanguage = array(
			'taxonomy' => 'doctor-language', // empty string(''), false, 0 don't work, and return empty array
			'orderby'  => 'name',
			'order'    => 'ASC',
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsLanguage = array(
				'taxonomy'   => 'doctor-language',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
			);
		}
		$languages = get_terms( $argsLanguage );

		// genres
		$argsCountry = array(
			'taxonomy' => 'country-origin', // empty string(''), false, 0 don't work, and return empty array
			'orderby'  => 'name',
			'order'    => 'ASC',
		);
		if ( get_option( 'd2g_pseudo_translations' ) == 1 && $currLang != 'en' ) {
			$argsCountry = array(
				'taxonomy'   => 'country-origin',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
			);
		}
		$countries = get_terms( $argsCountry );

		// search params from url
		$args = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$args = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$args2 = array(
			'post_type'      => 'd2g_doctor',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$specialty      = isset( $_GET['doctor-specialty'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor-specialty'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$doctorLanguage = isset( $_GET['doctor-language'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor-language'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$country        = isset( $_GET['country-origin'] ) ? sanitize_text_field( wp_unslash( $_GET['country-origin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.
		$intake         = isset( $_GET['intake'] ) ? sanitize_text_field( wp_unslash( $_GET['intake'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.

		$checker = 0;

		if ( $specialty != '' && $specialty != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-specialty',
				'field'    => 'term_id',
				'terms'    => array( (int) $specialty ),
			);
			++$checker;
		}

		if ( $doctorLanguage != '' && $doctorLanguage != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( (int) $doctorLanguage ),
			);
			++$checker;
		}

		if ( $country != '' && $country != '0' ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( (int) $country ),
			);
			++$checker;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		if ( $intake == 1 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'd2g_intake_call',
					'value' => $intake,
				),
			);
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Single post view by ID.
		if ( $post_id && $post_id != 0 ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,  // Sanitized ID
			);
		}

		$doctor_query  = new WP_Query( $args );
		$doctor_query2 = new WP_Query( $args2 );
		$count         = $doctor_query->found_posts;

		$current_theme = wp_get_theme();
		$theme_id      = $current_theme->get( 'Template' );

		ob_start();
		?>
		<?php if ( $a['wrapper_class'] != '' ) { ?>
			<div class="<?php echo esc_html( $a['wrapper_class'] ); ?>">
		<?php } ?>
		<?php
		// Sanitized filter params (view-only GET filters).
		$doctor_specialty = isset( $_GET['doctor-specialty'] ) ? absint( wp_unslash( $_GET['doctor-specialty'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET filters only (view/search), no state change.
		$country_origin   = isset( $_GET['country-origin'] ) ? absint( wp_unslash( $_GET['country-origin'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET filters only (view/search), no state change.
		$doctor_language  = isset( $_GET['doctor-language'] ) ? absint( wp_unslash( $_GET['doctor-language'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET filters only (view/search), no state change.
		$post_id_filter   = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET filters only (view/search), no state change.
		$intake_filter    = isset( $_GET['intake'] ) ? absint( wp_unslash( $_GET['intake'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET filters only (view/search), no state change.
		?>

		<form method="GET" action="<?php echo esc_url( $pageDoc ); ?>">
			<h3 class="opener special">Filters <span class="icon-angle-down"></span></h3>
			<div class="doctor_filters_outer">
				<ul id="doctor_filters" class="<?php echo esc_attr( $a['ul_class'] ); ?>">
					<li class="filter_wrap">
						<select name="doctor-specialty" id="specialty_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all specialties', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $specialties as $specialty ) { ?>
								<option <?php selected( $doctor_specialty, $specialty->term_id ); ?> value="<?php echo esc_attr( $specialty->term_id ); ?>">
									<?php
									if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
										echo ( $currLang == 'en' )
											? esc_html( $specialty->name )
											: esc_html( get_term_meta( $specialty->term_id, 'rudr_text_' . $currLang, true ) );
									} else {
										echo esc_html( $specialty->name );
									}
									?>
								</option>
							<?php } ?>
						</select>
					</li>

					<li class="filter_wrap">
						<select name="country-origin" id="country_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all countries', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $countries as $country ) { ?>
								<option <?php selected( $country_origin, $country->term_id ); ?> value="<?php echo esc_attr( $country->term_id ); ?>">
									<?php
									if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
										echo ( $currLang == 'en' )
											? esc_html( $country->name )
											: esc_html( get_term_meta( $country->term_id, 'rudr_text_' . $currLang, true ) );
									} else {
										echo esc_html( $country->name );
									}
									?>
								</option>
							<?php } ?>
						</select>
					</li>

					<li class="filter_wrap">
						<select name="doctor-language" id="language_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all languages', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $languages as $language ) { ?>
								<option <?php selected( $doctor_language, $language->term_id ); ?> value="<?php echo esc_attr( $language->term_id ); ?>">
									<?php
									if ( get_option( 'd2g_pseudo_translations' ) == 1 ) {
										echo ( $currLang == 'en' )
											? esc_html( $language->name )
											: esc_html( get_term_meta( $language->term_id, 'rudr_text_' . $currLang, true ) );
									} else {
										echo esc_html( $language->name );
									}
									?>
								</option>
							<?php } ?>
						</select>
					</li>

					<!--
					//this is not sure if ever is gonna be used
					<li id="hourly_price">
						<p><label><?php echo esc_html__( 'hourly price', 'doctor2go-connect' ); ?></label></p>
						<div class="range_slider_wrapper">
							<div id="slider-range-price"></div>
							<input type="hidden" value="1" id="amount1-price" name="price[min]">
							<input type="hidden" value="500" id="amount2-price" name="price[max]">
							<div class="stretch">
								<p class="alignleft" id="amount_1-price"></p>
								<p class="alignright" id="amount_2-price"></p>
								<div class="clearfix"></div>
							</div>
						</div>
					</li>
					-->

					<li class="filter_wrap">
						<select name="post_id" id="post_id" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'Doctor name', 'doctor2go-connect' ); ?></option>
							<?php
							while ( $doctor_query2->have_posts() ) {
								$doctor_query2->the_post();
								?>
								<option <?php selected( $post_id_filter, get_the_ID() ); ?> value="<?php echo esc_attr( get_the_ID() ); ?>">
									<?php echo esc_html( get_the_title() ); ?>
								</option>
							<?php } ?>
						</select>
					</li>

					<li class="filter_check_wrapper simple_hide">
						<label>
							<input <?php checked( $intake_filter, 1 ); ?> class="doctor_filter" type="checkbox" id="intake" value="1">
							<?php echo esc_html__( 'Free intake interview', 'doctor2go-connect' ); ?>
						</label>
					</li>

					<!--<li class="filter_check_wrapper">
						<label><input class="doctor_filter" type="checkbox" id="sub_title" value="1"> <?php echo esc_html__( 'Subtitles for translation', 'doctor2go-connect' ); ?></label>
					</li>-->

					<?php if ( $a['stand_alone'] == 'false' ) { ?>
						<li>
							<a class="btn btn-default" href="<?php echo esc_url( $pageDoc ); ?>"><?php esc_html_e( 'Reset search', 'doctor2go-connect' ); ?></a>
						</li>
					<?php } ?>
				</ul>

				<p><?php echo esc_html__( 'Found doctors:', 'doctor2go-connect' ); ?> <span id="doc_count"><?php echo esc_html( $count ); ?></span></p>

				<?php if ( $a['stand_alone'] == 'true' ) { ?>
					<input id="search_submit" type="submit" class="search_submit" value="<?php echo esc_attr__( 'Search', 'doctor2go-connect' ); ?>">
					<div class="error" id="search_error"></div>
					<div class="loader simple_hide"></div>
				<?php } ?>
			</div>
		</form>

		<?php if ( $a['wrapper_class'] != '' ) { ?>
		</div>
		<?php } ?>
	
		<?php
		wp_localize_script(
				'd2g-load-doctors',
				'myShortcodeDataFilters',
				array(
					'_wpnonce' 				=> wp_create_nonce( 'doc_call' ),
					'ajax_url' 				=> admin_url( 'admin-ajax.php' ),
					'posts_per_page' 		=> $a['posts_per_page'],
					'standalone_checker' 	=> $a['stand_alone'],
					'page_url'				=> $pageDoc,
					'loading_checker' 		=> get_option( 'd2g_load_availability_info' ) == 1 ? 1 : 0,
					'str_no_doctors_found'	=> esc_html__( 'We are sorry, but we could not find any doctors for your search criteria, please refine your search.', 'doctor2go-connect' )
				)
			);

		/* Restore original Post Data */
		wp_reset_postdata();

		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}


	/*
	* shortcode to show the doctor info box
	*/
	public function d2g_single_doctor_info( $atts ) {

		$a                = shortcode_atts(
			array(
				'doc_id' => '',
			),
			$atts
		);
		$post             = get_post( $a['doc_id'] );
		$d2g_profile_data = new D2G_ProfileData( $post, true );
		if ( get_post_thumbnail_id( $a['doc_id'] ) ) {
			$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( $a['doc_id'] ), 'd2g-doc-pic' )[0];
		} elseif ( get_option( 'd2g_placeholder' ) != '' ) {
				$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'd2g-doc-pic' )[0];
		} else {
			$feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';
		}

		ob_start();
		?>
		<article class="type-d2g_doctor single">
			<?php if ( ! empty( $feat_pic ) ) : ?>
				<img src="<?php echo esc_url( $feat_pic ); ?>" alt="<?php echo esc_html( $post->post_title ); ?>">
			<?php endif; ?>
			<div class="inner bg_white">
				<header>
					<h3><?php echo esc_html( $post->post_title ); ?></h3>
					<?php if ( $d2g_profile_data->specialties !== false ) { ?>
						<h4 class="specialties">
							<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
								<span><?php echo esc_html( $specialty->name ); ?></span>
							<?php } ?>
						</h4>
					<?php } ?>
				</header>
				<?php
				if ( $a['doc_id'] != '' ) {
					cb_d2g_info_box( 'single', 'grid', $post );
				} else {
					echo esc_html__( 'You must pass a doctor profile ID', 'doctor2go-connect' );
				}
				?>
			</div>
			<a class="button btn btn-default wp-block-button__link" href="<?php echo esc_html( get_the_permalink() ); ?>"><?php echo esc_html__( 'view doctor', 'doctor2go-connect' ); ?></a>
		</article>
		<?php
		/* Restore original Post Data */
		wp_reset_postdata();
		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}

	/*
	* shortcode to show the doctor locations
	*/
	public function d2g_single_doctor_locations( $atts ) {

		$a    = shortcode_atts(
			array(
				'doc_id'        => '',
				'wrapper_class' => '',
			),
			$atts
		);
		$post = get_post( $a['doc_id'] );

		ob_start();
		?>
		<div class="<?php echo esc_html( $a['wrapper_class'] ); ?>">
			<div class="type-d2g_doctor single">
				<div class="inner_wrapper">
					
					<?php
					if ( $a['doc_id'] != '' ) {
						show_doctor_locations_by_id( $a['doc_id'], false );
					} else {
						echo esc_html__( 'You must pass a doctor profile ID', 'doctor2go-connect' );
					}
					?>
				</div>
			</div>
		</div>
		<?php
		/* Restore original Post Data */
		wp_reset_postdata();

		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}


	/*
	* shortcode to show the doctor info box`
	*/
	public function d2g_single_doctor_calendar( $atts ) {

		$a    = shortcode_atts(
			array(
				'doc_id'        => '',
				'wrapper_class' => '',
			),
			$atts
		);
		$post = get_post( $a['doc_id'] );

		ob_start();
		?>
		<div class="<?php echo esc_html( $a['wrapper_class'] ); ?>">
			<div class="type-d2g_doctor single-calendar">
				<div class="inner_wrapper">
					<?php
					if ( $a['doc_id'] != '' ) {
						show_booking_calendar( $post, true );
					} else {
						echo esc_html__( 'You must pass a doctor profile ID', 'doctor2go-connect' );
					}
					?>
				</div>
			</div>
		</div>
		<?php
		/* Restore original Post Data */
		wp_reset_postdata();

		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}

	//
	// custom login form shortcode
	//
	public function d2g_login_form() {
		ob_start();

		// Check if the user is already logged in.
		if ( is_user_logged_in() ) {
			echo '<p>' . esc_html__( 'You are logged in', 'doctor2go-connect' ) . '</p>';
			return ob_get_clean();
		}

		// Messages from GET (view-only).
		$login_status = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
		$logout_flag  = isset( $_GET['logout'] ) ? absint( wp_unslash( $_GET['logout'] ) ) : 0;

		if ( 'failed' === $login_status ) {
			echo '<p>' . esc_html__( 'You either have entered a wrong username or password, please try again or click on lost password to reset your password.', 'doctor2go-connect' ) . '</p>';
		} elseif ( 1 === $logout_flag ) {
			echo '<p>' . esc_html__( 'You have logged out from this website.', 'doctor2go-connect' ) . '</p>';
		}

		// Determine the default redirect URL after login.
		$locale_parts    = explode( '_', get_locale() );
		$currLang        = isset( $locale_parts[0] ) ? sanitize_key( $locale_parts[0] ) : 'en';
		$d2gAdmin        = new D2G_doc_user_profile();
		$defaultRedirect = $d2gAdmin::d2g_page_url( $currLang, 'dashboard', true );

		$redirect_to_req = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : ''; // raw for redirects/requests. [web:197]
		$redirect_to     = ! empty( $redirect_to_req ) ? $redirect_to_req : ( isset( $defaultRedirect['url'] ) ? esc_url_raw( $defaultRedirect['url'] ) : '' );

		// Verify nonce on POST (this is your “processing form data” part).
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- $_SERVER['REQUEST_METHOD'] always exists in PHP web context.
			$nonce = isset( $_POST['d2g_login_nonce'] ) ? sanitize_key( wp_unslash( $_POST['d2g_login_nonce'] ) ) : '';
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'd2g_login_action' ) ) {
				echo '<p>' . esc_html__( 'Security check failed. Please reload the page and try again.', 'doctor2go-connect' ) . '</p>';
				return ob_get_clean();
			}
		}

		// Your reCAPTCHA site key.
		$recaptcha_site_key = get_option( 'd2g_recaptcha_site_key' );
		?>
	<div class="d2g_form_wrapper">
		<form method="post" action="<?php echo esc_url( wp_login_url( $redirect_to ) ); ?>" id="custom-loginform">
			<?php
			// Add nonce field to the form.
			wp_nonce_field( 'd2g_login_action', 'd2g_login_nonce' ); // [web:189]
			?>

			<p>
				<label for="user_login"><?php esc_html_e( 'Email', 'doctor2go-connect' ); ?></label>
				<input type="text" name="log" id="user_login" class="input" required>
			</p>

			<p>
				<label for="user_pass"><?php esc_html_e( 'Password', 'doctor2go-connect' ); ?></label>
				<input type="password" name="pwd" id="user_pass" class="input" required>
			</p>

			<!-- reCAPTCHA Widget -->
			<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"></div>
				<div id="captcha_login"></div>
			<?php } ?>

			<!-- altacha Widget check if shortcode is active -->
			<?php if ( shortcode_exists( 'altcha' ) ) : ?>
				<?php echo do_shortcode( '[altcha]' ); ?>
			<?php endif; ?>

			<p style="margin-top: 10px;">
				<label>
					<input type="checkbox" name="rememberme" value="forever">
					<?php esc_html_e( 'Remember Me', 'doctor2go-connect' ); ?>
				</label>
			</p>

			<input type="submit" value="<?php echo esc_attr__( 'Login', 'doctor2go-connect' ); ?>" class="button button-primary">
		</form>

		<?php $pageData = $d2gAdmin::d2g_page_url( $currLang, 'lost_password', true ); ?>
		<a href="<?php echo esc_url( $pageData['url'] ); ?>"><?php esc_html_e( 'Lost password?', 'doctor2go-connect' ); ?></a>&nbsp;&nbsp;

		<?php $pageData = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true ); ?>
		<a href="<?php echo esc_url( $pageData['url'] ); ?>"><?php esc_html_e( 'Register as patient', 'doctor2go-connect' ); ?></a>&nbsp;&nbsp;
	</div>

		<?php
		// Include the Google reCAPTCHA script (your existing logic).
		

		return ob_get_clean();
	}



	//
	// custom lost password form
	public function d2g_lost_password_form() {
		ob_start();

		// Already logged in → bail early.
		if ( is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You are already logged in.', 'doctor2go-connect' ) . '</p>';
		}

		// View-only message from GET.
		$login_status = isset( $_GET['login'] )
			? sanitize_text_field( wp_unslash( $_GET['login'] ) )
			: '';

		if ( 'failed' === $login_status ) {
			echo '<p style="color:red;">' .
				esc_html__( 'Invalid username or email address', 'doctor2go-connect' ) .
			'</p>';
		}

		// Nonce check ONLY on POST.
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';

		if ( 'POST' === $request_method ) {
			$nonce = isset( $_POST['d2g_lost_password_nonce'] )
				? sanitize_key( wp_unslash( $_POST['d2g_lost_password_nonce'] ) )
				: '';

			if ( ! wp_verify_nonce( $nonce, 'd2g_lost_password_action' ) ) {
				return '<p>' . esc_html__( 'Security check failed. Please reload the page and try again.', 'doctor2go-connect' ) . '</p>';
			}
		}

		// Locale + redirect URL.
		$locale_parts = explode( '_', get_locale() );
		$currLang     = sanitize_key( $locale_parts[0] ?? 'en' );

		$d2gAdmin = new D2G_doc_user_profile();
		$pageData = $d2gAdmin::d2g_page_url( $currLang, 'password_reset_sent', true );
		$redirect = isset( $pageData['url'] ) ? esc_url( $pageData['url'] ) : '';

		?>

		<div class="d2g_form_wrapper">
			<form id="lostpasswordform"
				action="<?php echo esc_url( site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>"
				method="post">

				<?php wp_nonce_field( 'd2g_lost_password_action', 'd2g_lost_password_nonce' ); ?>

				<p>
					<label for="user_login">
						<?php esc_html_e( 'Username or Email Address', 'doctor2go-connect' ); ?>
					</label>
					<input type="text" name="user_login" id="user_login" required>
				</p>

				<p>
					<input type="submit"
						name="wp-submit"
						id="wp-submit"
						value="<?php esc_attr_e( 'Get New Password', 'doctor2go-connect' ); ?>">
				</p>

				<input type="hidden" name="redirect_to" value="<?php echo esc_html( $redirect ); ?>">
			</form>
		</div>

		<?php

		return ob_get_clean();
	}



	//
	// custom reset password form logic
	public function d2g_reset_password_form() {

		if ( is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You are already logged in.', 'doctor2go-connect' ) . '</p>';
		}

		// Validate + sanitize required GET params.
		if ( ! isset( $_GET['key'], $_GET['login'] ) ) {
			return '<p class="error">' . esc_html__( 'Invalid password reset request. Please check your email for the correct link.', 'doctor2go-connect' ) . '</p>';
		}

		$reset_key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
		$login     = sanitize_user( wp_unslash( $_GET['login'] ), true );

		// If POST: verify nonce + validate + sanitize password fields.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {

			$nonce = isset( $_POST['d2g_reset_password_nonce'] ) ? sanitize_key( wp_unslash( $_POST['d2g_reset_password_nonce'] ) ) : '';
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'd2g_reset_password_action' ) ) {
				return '<p class="error">' . esc_html__( 'Security check failed. Please reload the page and try again.', 'doctor2go-connect' ) . '</p>';
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$new_password = isset( $_POST['new_password'] ) ? wp_unslash( $_POST['new_password'] ) : '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$confirm_password = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] ) : '';

			if ( '' === $new_password || '' === $confirm_password ) {
				return '<p class="error">' . esc_html__( 'Please enter your new password twice.', 'doctor2go-connect' ) . '</p>' .
					$this->custom_reset_password_form_html( $login, $reset_key );
			}

			if ( $new_password !== $confirm_password ) {
				return '<p class="error">' . esc_html__( 'Passwords do not match. Please try again.', 'doctor2go-connect' ) . '</p>' .
					$this->custom_reset_password_form_html( $login, $reset_key );
			}

			$user = check_password_reset_key( $reset_key, $login );
			if ( is_wp_error( $user ) ) {
				return '<p class="error">' . esc_html__( 'Invalid password reset link. Please request a new one.', 'doctor2go-connect' ) . '</p>';
			}

			reset_password( $user, $new_password );

			$locale_parts = explode( '_', get_locale() );
			$currLang     = isset( $locale_parts[0] ) ? sanitize_key( $locale_parts[0] ) : 'en';
			$d2gAdmin     = new D2G_doc_user_profile();
			$pageData     = $d2gAdmin::d2g_page_url( $currLang, 'login', true );

			$login_url   = isset( $pageData['url'] ) ? esc_url( $pageData['url'] ) : '';
			$login_title = isset( $pageData['title'] ) ? esc_html( $pageData['title'] ) : esc_html__( 'Login', 'doctor2go-connect' );

			return '<p class="success">' .
				esc_html__( 'Your password has been reset successfully. Click on the following link to login', 'doctor2go-connect' ) .
				'<br><a href="' . $login_url . '">' . $login_title . '</a>.</p>';
		}

		return $this->custom_reset_password_form_html( $login, $reset_key );
	}

	//
	// custom password reset form HTML
	private function custom_reset_password_form_html( $login, $reset_key ) {
		ob_start();
		?>
		<div class="d2g_form_wrapper">
			<form id="resetpasswordform" method="post">
				<?php
				// Nonce field.
				wp_nonce_field( 'd2g_reset_password_action', 'd2g_reset_password_nonce' ); // [web:189]
				?>

				<!-- Keep these so POST has the same values; avoids relying on GET on submit -->
				<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
				<input type="hidden" name="key" value="<?php echo esc_attr( $reset_key ); ?>">

				<p>
					<label for="new_password"><?php echo esc_html__( 'New Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="new_password" id="new_password" required>
				</p>
				<p>
					<label for="confirm_password"><?php echo esc_html__( 'Confirm New Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="confirm_password" id="confirm_password" required>
				</p>
				<p>
					<input type="submit" value="<?php echo esc_attr__( 'Reset Password', 'doctor2go-connect' ); ?>">
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}


	//
	// custom registration form
	function d2g_registration_form() {

		if ( is_user_logged_in() ) {
			return '<p>You are already registered and logged in.</p>';
		}

		$recaptcha_site_key = get_option( 'd2g_recaptcha_site_key' );
		$secret_key         = get_option( 'd2g_recaptcha_secret_key' );
		$timezones          = d2g_timezones();

		// Process form submission
		if (
			isset( $_SERVER['REQUEST_METHOD'] ) &&
			$_SERVER['REQUEST_METHOD'] === 'POST' &&
			isset( $_POST['custom_registration'] )
		) {

			$errors = array();

			// Nonce verification
			if ( ! isset( $_POST['d2g_reg_nonce'] ) || ! wp_verify_nonce( isset( $_POST['d2g_reg_nonce'] ) ? sanitize_key( wp_unslash( $_POST['d2g_reg_nonce'] ) ) : '', 'd2g_registration_action' ) ) {
				$errors[] = __( 'Security check failed. Please refresh the page.', 'doctor2go-connect' );
			} else {

				$email = isset( $_POST['email'] )
					? sanitize_email( wp_unslash( $_POST['email'] ) )
					: '';

				$username = '';
				if ( ! empty( $email ) && is_email( $email ) ) {
					$username = explode( '@', $email )[0] . time();
				}

				$password = isset( $_POST['password'] )
					? sanitize_text_field( wp_unslash( $_POST['password'] ) )
					: '';

				$confirm_password = isset( $_POST['confirm_password'] )
					? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) )
					: '';

				$recaptcha_response = isset( $_POST['g-recaptcha-response'] )
					? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
					: '';

				if ( get_option( 'd2g_recaptcha_site_key' ) != '' ) {

					$recaptcha_verify = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'body' => array(
								'secret'   => $secret_key,
								'response' => $recaptcha_response,
								'remoteip' => isset( $_SERVER['REMOTE_ADDR'] )
									? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
									: '',
							),
						)
					);

					$recaptcha_result = json_decode( wp_remote_retrieve_body( $recaptcha_verify ) );

					if ( empty( $recaptcha_result ) || ! $recaptcha_result->success ) {
						$errors[] = __( 'CAPTCHA verification failed. Please try again.', 'doctor2go-connect' );
					}
				}

				if ( empty( $username ) || empty( $email ) || empty( $password ) || empty( $confirm_password ) ) {
					$errors[] = __( 'All fields are required.', 'doctor2go-connect' );
				}

				if ( ! is_email( $email ) ) {
					$errors[] = __( 'Please provide a valid email address.', 'doctor2go-connect' );
				}

				if ( $password !== $confirm_password ) {
					$errors[] = __( 'Passwords do not match.', 'doctor2go-connect' );
				}

				if ( username_exists( $username ) || email_exists( $email ) ) {
					$errors[] = __( 'The username or email is already registered.', 'doctor2go-connect' );
				}

				if ( empty( $errors ) ) {

					$user_input = array(
						'user_login' => $username,
						'user_pass'  => $password,
						'user_email' => $email,
						'first_name' => isset( $_POST['meta']['first_name'] )
							? sanitize_text_field( wp_unslash( $_POST['meta']['first_name'] ) )
							: '',
						'last_name'  => isset( $_POST['meta']['last_name'] )
							? sanitize_text_field( wp_unslash( $_POST['meta']['last_name'] ) )
							: '',
						'role'       => 'patient',
					);

					$user_id = wp_insert_user( $user_input );

					if ( ! is_wp_error( $user_id ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
						$meta = wp_unslash( $_POST['meta'] ); // ✅ unslash once for all values

						if ( isset( $meta ) && is_array( $meta ) ) {
							foreach ( $meta as $key => $value ) {
								update_user_meta(
									$user_id,
									sanitize_key( $key ),
									sanitize_text_field( $value )
								);
							}
						}

						d2g_user_email(
							'registration',
							$email,
							(
								isset( $_POST['meta']['first_name'] )
									? sanitize_text_field( wp_unslash( $_POST['meta']['first_name'] ) )
									: ''
							) . ' ' . (
								isset( $_POST['meta']['last_name'] )
									? sanitize_text_field( wp_unslash( $_POST['meta']['last_name'] ) )
									: ''
							),
							get_option( 'd2g_sender_address' )
						);

						$currLang = explode( '_', get_locale() )[0];
						$d2gAdmin = new D2G_doc_user_profile();
						$pageData = $d2gAdmin::d2g_page_url( $currLang, 'patient_dashboard', true );

						programmatic_login( $username );

						if ( isset( $_GET['redirect_to'] ) ) {

							// Unslash once
							$redirect_to = wp_unslash( $_GET['redirect_to'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated

							// Validate URL (ensure it's safe)
							$redirect_to = wp_validate_redirect( $redirect_to, home_url() );

							// Append parameter safely
							$redirect_to = add_query_arg( 'signup', 'completed', $redirect_to );

							wp_safe_redirect( $redirect_to );
							exit;

						} else {

							$redirect_url = add_query_arg(
								'signup',
								'completed',
								$pageData['url']
							);

							wp_safe_redirect( $redirect_url );
							exit;
						}

						exit;

					} else {
						$errors[] = $user_id->get_error_message();
					}
				}
			}

			if ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					echo '<p class="error">' . esc_html( $error ) . '</p>';
				}
			}
		}

		ob_start();
		?>
		<div class="d2g_form_wrapper">
			<?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash?>
			<form id="custom-registration-form" method="post" action="?create_account=1<?php echo ( isset( $_GET['redirect_to'] ) ) ? '&redirect_to=' . urlencode( wp_unslash( $_GET['redirect_to'] ) ) : ''; ?>">
				<?php wp_nonce_field( 'd2g_registration_action', 'd2g_reg_nonce' ); ?>
				<div id="error" class="error"></div>

				<p>
					<label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="text" name="meta[first_name]" id="first_name" required>
				</p>
				<p>
					<label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="text" name="meta[last_name]" id="last_name" required>
				</p>
				<p>
					<label for="email"><?php echo esc_html__( 'Email', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="email" name="email" id="patient_email" required>
				</p>
				<p>
					<label for="p_tel"><?php echo esc_html__( 'Phone', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="text" name="meta[p_tel]" id="p_tel" required>
				</p>
				<p id="time_zone_wrapper">
					<label><?php echo esc_html__( 'Timezone (Your browser detects time zones automatically, but you can set a different one here if needed.)', 'doctor2go-connect' ); ?></label>
					<select name="meta[p_timezone]">
						<option value="0"><?php echo esc_html__( 'make a selection', 'doctor2go-connect' ); ?></option>
						<?php foreach ( $timezones as $group => $zones ) { ?>
							<optgroup label="<?php echo esc_html( $group ); ?>">
								<?php foreach ( $zones as $key => $name ) { ?>
									<option value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</p>
				<p>
					<label><?php echo esc_html__( 'Password (your password needs to be minimum 8 characters long and it must contain minimum one special character.)', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="password" name="password" id="pass1" required>
				</p>
				<div class="info error" id="result" style="width:100%!important;display:none;"></div>
				<p>
					<label><?php echo esc_html__( 'Confirm Password', 'doctor2go-connect' ); ?>*</label>
					<input class="myrequired" type="password" name="confirm_password" id="pass2" required>
				</p>

				<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
					<p>
						<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"></div>
						<div id="captcha_registration"></div>
					</p>
				<?php } ?>

				<?php if ( shortcode_exists( 'altcha' ) ) : ?>
					<?php echo do_shortcode( '[altcha]' ); ?>
				<?php endif; ?>

				<?php confirmation_checkboxes(); ?>

				<p>
					<input type="hidden" name="custom_registration" value="1">
					<input id="submit_registration" type="submit" value="<?php echo esc_html__( 'Register', 'doctor2go-connect' ); ?>">
				</p>
			</form>
		</div>
		<?php
		wp_localize_script(
			'd2g-public',
			'd2gRegistrationVars',
			array(
				'msg_required'     => esc_html__( 'Please fill in all marked fields. ', 'doctor2go-connect' ),
				'msg_pass_short'   => esc_html__( 'Your password is too short. ', 'doctor2go-connect' ),
				'msg_pass_match'   => esc_html__( 'Your passwords do not match. ', 'doctor2go-connect' ),
				'msg_email_invalid'=> esc_html__( 'You have entered an invalid e-mail. ', 'doctor2go-connect' ),
				'msg_privacy'      => esc_html__( 'You must accept the privacy rules. ', 'doctor2go-connect' ),
				'msg_terms'        => esc_html__( 'You must accept the terms and conditions. ', 'doctor2go-connect' ),
				'msg_disclaimer'   => esc_html__( 'You must accept the disclaimer. ', 'doctor2go-connect' ),
			)
		);

		

		return ob_get_clean();
	}



	//
	// shortcode patient dashbaord
	public function d2g_patient_dashbaord() {
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pages    = array(
			'account_settings' => 'account.jpg',
			'appointments'     => 'appointments.jpg',
			'liked_doctors'    => 'heart.jpg',
			'questionnaires'   => 'questionnaire.jpg',

		);

		// patient / user data
		$currUser  = wp_get_current_user();
		$user_meta = get_user_meta( $currUser->data->ID );

		$tokensCheck     = $user_meta['tokens'][0];
		$tokensAssArray  = unserialize( $user_meta['tokens'][0] );
		$tokensSimpleArr = array();
		foreach ( $tokensAssArray as $token ) {
			$tokensSimpleArr[] = $token;
		}

		$client_info = json_decode( $this->get_wcc_client_info( $tokensSimpleArr ) );

		ob_start();

		?>
		<div class="alignwide p_dashboard">
			<div class="row">
				<?php
				foreach ( $pages as $page => $image ) {
					$pageData = $d2gAdmin::d2g_page_url( $currLang, $page, true );
					?>
					<div class="col-sm-3">
						<a href="<?php echo esc_url( $pageData['url'] ); ?>">
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/' . $image ); ?>">
							<h3><?php echo esc_html( $pageData['title'] ); ?></h3>
						</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();

		/* Return the content as usual */
		return $sc;
	}

	//
	// shortcode patient dashbaord
	public function d2g_questionnaires() {

		// patient / user data
		$currUser  = wp_get_current_user();
		$user_meta = get_user_meta( $currUser->data->ID );

		$tokensCheck     = $user_meta['tokens'][0];
		$tokensAssArray  = unserialize( $user_meta['tokens'][0] );
		$tokensSimpleArr = array();
		foreach ( $tokensAssArray as $token ) {
			$tokensSimpleArr[] = $token;
		}

		$client_infos     = json_decode( $this->get_wcc_client_info( $tokensSimpleArr ) );
		$written_consults = array();
		$simple_consults  = array();

		// sort answer sets in written and simple consults based on apt_is_written and assoiated to a token (client has multiple tokens when he has multiple practitioners from different organizations)
		foreach ( $client_infos as $info ) {
			if ( is_array( $info->answer_set_info ) ) {
				$answer_sets[ $info->authentication_token ] = $info->answer_set_info;
				foreach ( $info->answer_set_info as $answer_set ) {
					if ( $answer_set->apt_is_written == true ) {
						$written_consults[ $info->authentication_token ][] = $answer_set;
					} else {
						$simple_consults[ $info->authentication_token ][] = $answer_set;
					}
				}
			}
		}

		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pageData = $d2gAdmin::d2g_page_url( $currLang, 'questionnaires', true );

		ob_start();

		?>
		<div class="alignwide">
		<div class="row with_right_sidebar">
			<div class="col-sm-8">
				<?php if ( isset( $_GET['url'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?> 
					<h2>
						<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$title = isset( $_GET['title'] ) ? sanitize_text_field( wp_unslash( $_GET['title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						echo esc_html( urldecode( $title ) );
						?>
					</h2>
					<h4 style="color: #d20a10"><?php echo esc_html__( 'Please fill in the following questionnaire. Your answers will be stored in a highly secure database.', 'doctor2go-connect' ); ?></h4>
					<?php
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : '';
					?>
					<iframe src="<?php echo esc_url( urldecode( $url ) ); ?>" style="width:100%; border:none; height:1000px"></iframe>
				<?php } else { ?>
					<p class="error"><strong><?php esc_html_e( 'No questionnaire was selected, click on one of the questionnaires in your questionnaires list.', 'doctor2go-connect' ); ?></strong></p>
				<?php } ?>
			</div>
			<div class="col-sm-4">
				<h2><?php echo esc_html__( 'Your questionnaires', 'doctor2go-connect' ); ?></h2>
				<h3 class="simple"><?php echo esc_html__( 'Online and pysical consults', 'doctor2go-connect' ); ?></h3>
				<h3 class="opener"><?php echo esc_html__( 'Online and pysical consults', 'doctor2go-connect' ); ?> <span class="icon-down-open"></span></h3>
				<?php if ( count( $simple_consults ) > 0 ) { ?> 
					<ul class="mb-xl  questionnaires_list"  id="questionnaires" style="list-style: none; padding:0;">
						<?php
						foreach ( $simple_consults as $client_token => $answer_sets ) {
							foreach ( $answer_sets as $answer_set ) {
								$date     = new DateTime( $answer_set->apt_date );
								$timezone = $user_meta['p_timezone'][0] ?: get_user_timezone();
								$date->setTimezone( new DateTimeZone( $timezone ) );

								$title = urlencode( esc_html( $answer_set->name ) . ' ' . esc_html__( 'for your appointment on', 'doctor2go-connect' ) . ' ' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  (' . $timezone . ') ' . esc_html__( ' with ', 'doctor2go-connect' ) . ' ' . esc_html( $answer_set->apt_user ) );
								?>
								<li>
									<a href="<?php esc_url( $pageData['url'] ); ?>?url=<?php echo urlencode( get_option( 'waiting_room_url' ) . 'answer_set/' . $answer_set->id . '?client_auth=' . $client_token ); ?>&title=<?php echo esc_html( $title ); ?>">
										<span style="font-size:16px" class="demo-icon icon-right-open"></span>
										<ul>
											<li><strong><?php echo esc_html__( 'Consult preparation:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $answer_set->name ); ?></li>
											<li><strong><?php echo esc_html__( 'Date:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $date->format( 'd/m/Y' ) ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . esc_html( $date->format( 'H:i' ) ) . '  (' . esc_html( $timezone ) . ') '; ?></li>
											<li><strong><?php echo esc_html__( 'Practitioner:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $answer_set->apt_user ); ?></li>
										</ul>
									</a>
								</li>
							<?php } ?>
						<?php } ?>
					</ul>
					<?php
				} else {
					echo '<p class="error">' . esc_html__( 'There are no questionnaires to be filled in.', 'doctor2go-connect' ) . '</p>';
				}
				?>
				<h3 class="simple"><?php echo esc_html__( 'Written consults', 'doctor2go-connect' ); ?></h3>
				<h3 class="opener"><?php echo esc_html__( 'Written consults', 'doctor2go-connect' ); ?>  <span class="icon-down-open"></span></h3>
				<?php if ( count( $written_consults ) > 0 ) { ?> 
					<ul class="mb-xl questionnaires_list"  id="questionnaire_written" style="list-style: none; padding:0;">
						<?php
						foreach ( $written_consults as $client_token => $answer_sets ) {
							foreach ( $answer_sets as $answer_set ) {
								$baseUrl  = get_option( 'wcc_base_url' );
								$date     = new DateTime( $answer_set->apt_date );
								$timezone = $user_meta['p_timezone'][0] ?: get_user_timezone();
								$date->setTimezone( new DateTimeZone( $timezone ) );
								$title = urlencode( __( 'Written consult:', 'doctor2go-connect' ) . ' ' . esc_html( $answer_set->name ) . ' ' . esc_html__( 'started on', 'doctor2go-connect' ) . ' ' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  (' . $timezone . ') ' . esc_html__( ' with ', 'doctor2go-connect' ) . ' ' . esc_html( $answer_set->apt_user ) );

								?>
									<?php
									if ( $answer_set->apt_use_payment == true ) {
										if ( $answer_set->apt_was_paid == true ) {
											$payment_info  = __( 'done', 'doctor2go-connect' );
											$payment_check = 'success';

											$link = $pageData['url'] . '?url=' . urlencode( get_option( 'waiting_room_url' ) . 'answer_set/' . $answer_set->id . '?client_auth=' . $client_token ) . '&title=' . $title;

										} else {
											$payment_info  = __( 'outstanding', 'doctor2go-connect' );
											$payment_check = 'error';

											$questionnaire_url = urlencode( 'https://app.' . $baseUrl . '/written_appointment/' . $answer_set->apt_token );
											$payment_url_full  = 'https://app.' . $baseUrl . '/payment/' . $answer_set->apt_token;
											$redirect_url      = urlencode( $pageData['url'] . '?url=' . $questionnaire_url . '&title=' . $title . '&skip_cookie_wall=true' );
											$link              = $payment_url_full . '?redirect_url=' . $redirect_url;
										}
									} else {
										$payment_info = __( 'free consult', 'doctor2go-connect' );
										$link         = $pageData['url'] . '?url=' . urlencode( get_option( 'waiting_room_url' ) . 'answer_set/' . $answer_set->id . '?client_auth=' . $client_token ) . '&title=' . $title;
									}

									?>
									<li>
										<a href="<?php echo esc_url( $link ); ?>">
											<span style="font-size:16px" class="demo-icon icon-right-open"></span>
											<ul>
												<li><strong><?php echo esc_html__( 'Written consult:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $answer_set->name ); ?></li>
												<li><strong><?php echo esc_html__( 'Started on:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $date->format( 'd/m/Y' ) ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . esc_html( $date->format( 'H:i' ) ) . '  (' . esc_html( $timezone ) . ') '; ?></li>
												<li><strong><?php echo esc_html__( 'Practitioner:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $answer_set->apt_user ); ?></li>
												<li class="<?php echo esc_url( $payment_check ); ?>"><strong><?php echo esc_html__( 'Payment:', 'doctor2go-connect' ); ?></strong> <?php echo esc_html( $payment_info ); ?></li>
											</ul>
										</a>
									</li>
							<?php } ?>
						<?php } ?>
					</ul>
					<?php
				} else {
					echo '<p class="error">' . esc_html__( 'You have not started any written consults.', 'doctor2go-connect' ) . '</p>';
				}
				?>
				
			</div>
		</div>
		</div>
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();

		/* Return the content as usual */
		return $sc;
	}


	//
	// shortcode patient dashbaord
	public function d2g_public_questionnaire() {

		ob_start();

		?>
		<div class="alignwide">
			<div class="row">
				<div class="col-sm-12">
					<?php
					// Properly unslash and ignore PCP warnings on this line
					$iframe_url = isset( $_GET['url'] ) ? wp_unslash( $_GET['url'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					if ( $iframe_url ) {
						?>
						<iframe src="<?php echo esc_url( $iframe_url ); ?>" style="width:100%; border:none; height:1500px"></iframe>
					<?php } else { ?>
						<p class="error"><strong><?php esc_html_e( 'No questionnaire was found.', 'doctor2go-connect' ); ?></strong></p>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();

		/* Return the content as usual */
		return $sc;
	}

	//
	// shortcode patient dashbaord
	public function d2g_patient_menu() {
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pages    = array(

			'appointments'          => 'appointments-small.jpg',
			'liked_doctors'         => 'heart-small.jpg',
			'questionnaires'        => 'questionnaire-small.jpg',
			'secure_patient_portal' => 'patient-small.jpg',
			'account_settings'      => 'account-small.jpg',
		);

		ob_start();

		?>
		<ul class="user_menu">
			<?php
			foreach ( $pages as $page => $image ) {
				$pageData = $d2gAdmin::d2g_page_url( $currLang, $page, true );
				?>
				<li>
					<a href="<?php echo esc_html( $pageData['url'] ); ?>">
						<img style="width:50px; display:inline-block; margin-right:10px;" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/' . $image ); ?>">
						<span><?php echo esc_html( $pageData['title'] ); ?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
		<?php
		/* Get the buffered content into a var */
		$sc = ob_get_contents();

		/* Clean buffer */
		ob_end_clean();

		/* Return the content as usual */
		return $sc;
	}

	//
	// shortcode patient appointments
	public function d2g_patient_appointments() {
		// initialize class to get dynamic links
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		// get client info from WP DB
		$currUser        = wp_get_current_user();
		$user_meta       = get_user_meta( $currUser->data->ID );
		$timezone        = $user_meta['p_timezone'][0] ?: get_user_timezone();
		$tokensCheck     = $user_meta['tokens'][0];
		$tokensAssArray  = unserialize( $user_meta['tokens'][0] );
		$tokensSimpleArr = array();
		foreach ( $tokensAssArray as $token ) {
			$tokensSimpleArr[] = $token;
		}

		// get clinet appointments from WCC
		$appointments           = json_decode( $this->get_patient_appointments_simple( $tokensSimpleArr ) );
		$structuredAppointments = array();

		ob_start();

		if ( $tokensCheck == '' ) {
			?>
			<p><?php echo esc_html__( 'You haven\'t booked any consultations yet. When you do, the details will appear in this section.', 'doctor2go-connect' ); ?></p>
		<?php } else { ?>
			<div class="alignwide">
				<div class="list_app">
					<?php
					if ( $appointments == null ) {
						?>
						<h3 class="error"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
						<?php
						$sc = ob_get_contents();
						ob_end_clean();
						return $sc;
					} elseif ( is_array( $appointments ) ) {
						if ( count( $appointments ) < 1 ) {
							?>
							<h3 class="error"><?php echo esc_html__( 'You don\’t have any upcoming consultations. Book a new appointment when you\'re ready.', 'doctor2go-connect' ); ?></h3>
						<?php } else { ?>
							<?php
							foreach ( $appointments as $appointment ) {
								// get doc info for appointment
								$docObj       = $this->get_doctor_by_wcc_id( $appointment->user_id )[0];
								$doc_email    = get_post_meta( $docObj->ID, 'd2g_main_email', true );
								$orgKey       = get_post_meta( $docObj->ID, 'organisation_key', true );
								$client_token = $tokensAssArray[ $orgKey ];

								// doctor image
								$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( $docObj->ID ), 'thumbnail' )[0];
								if ( $feat_pic == '' ) {
									if ( get_option( 'd2g_placeholder' ) != '' ) {
										$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'thumbnail' )[0];
									} else {
										$feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';

									}
								}
								// doctor specialties
								$specialties   = get_the_terms( $docObj->ID, 'specialty' );
								$specialty_str = '';
								if ( $specialties !== false ) {
									foreach ( $specialties as $specialty ) {
										$specialty_str .= '<span>' . $specialty->name . '</span>';
									}
								}

								// appointment date
								$date = new DateTime( $appointment->date );

								$date_now = new DateTime();
								// Calculate difference in seconds
								$diffInSeconds = $date->getTimestamp() - $date_now->getTimestamp();

								$date->setTimezone( new DateTimeZone( $timezone ) );

								// create the links
								// html link for questionnaires
								$questionnaire     = '';
								$questionnaireLink = '<div class="questionnaire"></div>';
								if ( isset( $appointment->answer_set_id ) && $appointment->answer_set_id != null ) {
									$pageData = $d2gAdmin::d2g_page_url( $currLang, 'questionnaires', true );
									// url to load in iframe
									$questionnaireURL  = urlencode( get_option( 'waiting_room_url' ) . 'answer_set/' . $appointment->answer_set_id . '?client_auth=' . $client_token );
									$title             = urlencode( esc_html__( 'Questionnaire for your appointment on', 'doctor2go-connect' ) . ' ' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  (' . $timezone . ') ' . esc_html__( ' with ', 'doctor2go-connect' ) . ' ' . esc_html( $docObj->post_title ) );
									$questionnaireLink = '<div class="questionnaire">
										<a class="btn btn-default" href="' . $pageData['url'] . '?url=' . $questionnaireURL . '&title=' . $title . '"><img decoding="async" src="' . plugin_dir_url( __FILE__ ) . 'images/questionnaire-small.jpg">' . esc_html__( ' fill in the questionnnaire ', 'doctor2go-connect' ) . '</a>
									</div>';

								}
								// other links
								$consultLink = '<a target="_blank" href="' . get_option( 'waiting_room_url' ) . 'wachtkamer/' . $appointment->token . '?locale=' . explode( '_', get_locale() )[0] . '"><span class=" icon-videocam-outline"></span> ' . esc_html__( 'go to consultation', 'doctor2go-connect' ) . '</a>';
								$docLink     = '<a href="/doctor/' . $docObj->post_name . '"><span class=" icon-eye"></span> ' . esc_html__( 'view doctor page', 'doctor2go-connect' ) . '</a>';
								// cancellation button or locked message
								if ( $diffInSeconds <= 0 || $diffInSeconds > 86400 ) {
									$delBtn = '<a class="del_app" href="#" data-app-id="' . $appointment->_id . '" data-user-id="' . $appointment->user_id . '"><span class=" icon-cancel-circled"></span> ' . esc_html__( 'cancel appointment', 'doctor2go-connect' ) . '</a>';
								} else {
									$delBtn = '<span class="icon-lock"></span> ' . esc_html__( 'cancellation locked (less than 24h before appointment)', 'doctor2go-connect' );
								}
								$cancelBtn = '<a class="prep_cancellation_email scroll_to" href="#cancellation_form_wrapper" data-app-date="' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  (' . $timezone . ')" data-app-link="' . get_option( 'waiting_room_url' ) . 'admin/appointments/' . $appointment->_id . '" data-doc-email="' . $doc_email . '" data-doc-name="' . $docObj->post_title . '"><span class=" icon-cancel-circled"></span> ' . esc_html__( 'request cancellation', 'doctor2go-connect' ) . '</a>';

								if ( $appointment->payment_has_paid == true ) {
									$payment_info = '<div class="paid"><strong>' . esc_html__( 'paid', 'doctor2go-connect' ) . '</strong></div>';
								} else {
									$pageData    = $d2gAdmin::d2g_page_url( $currLang, 'appointments', true );
									$redirectURL = '&redirect_url=' . urlencode( $pageData['url'] . '?app=' ) . $appointment->_id;

									$payment_info = '<div class="payment_needed error">
									<div class=""><strong>' . esc_html__( 'A payment is required for this appointment. You can either do it upofront or when entering the waiting room', 'doctor2go-connect' ) . '</strong></div>
									<div class="btn_wrap"><a class="btn btn-default payment_btn" target="_blank" href="' . get_option( 'waiting_room_url' ) . 'payment/' . $appointment->_id . '?locale=' . explode( '_', get_locale() )[0] . $redirectURL . '">' . esc_html__( 'pay now', 'doctor2go-connect' ) . '</a></div>
									</div>';
								}

								// create the appointment rows and save in array to sort them later
								if ( $appointment->location_to_go != null ) {
									$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper card mb-3"><div id="app-' . $appointment->_id . '" class="app_row">
										<div class="feat_pic"><img src="' . $feat_pic . '"></div>
										<div class="content_outer">
											<div class="content">
												<p class="consult_type"><strong>' . esc_html__( 'Physical consultation', 'doctor2go-connect' ) . '</strong></p>
												<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
												<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
												<p class="address">' . $appointment->location_to_go->location_name . ': ' . $appointment->location_to_go->location_full_adress_url . '</p>
											</div> 
											<div class="btn_wrap">' . $docLink . ' ' . $delBtn . '</div>
										</div>' . $questionnaireLink . '</div></div>';
								} else {
									$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper card mb-3">' . $payment_info . '<div id="app-' . $appointment->_id . '" class="app_row">
										<div class="feat_pic"><img src="' . $feat_pic . '"></div>
										<div class="content_outer">
											<div class="content">
												<p class="consult_type"><strong>' . esc_html__( 'Online consultation', 'doctor2go-connect' ) . '</strong></p>
												<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
												<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
											</div> 
											<div class="btn_wrap">' . $consultLink . ' ' . $docLink . '<br>' . $delBtn . '</div>
										</div>' . $questionnaireLink . '</div></div>';
								}

								// Properly get 'app' from GET with unslash and isset check
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
								$app_id = isset( $_GET['app'] ) ? sanitize_text_field( wp_unslash( $_GET['app'] ) ) : '';
								if ( $appointment->_id == $app_id ) {
									?>
									<div class="walkin_form_wrapper mb-xl">
										<?php
										echo '<h2>' . esc_html__( 'You have successfully booked following appointment.', 'doctor2go-connect' ) . '</h2>';
										if ( $questionnaire != '' ) {
											echo '<h4 class="error">' . esc_html__( 'Please note: A questionnaire needs to be completed.', 'doctor2go-connect' ) . '</h4>';
										}
										echo wp_kses_post( $structuredAppointments[ $appointment->date ] );
										?>
									</div>
								<?php } ?>
								<?php
							}
							// display the appointments with sorting on date
							?>
							<h2><?php echo esc_html__( 'Your appointments', 'doctor2go-connect' ); ?></h2>
							<?php
							ksort( $structuredAppointments );
							foreach ( $structuredAppointments as $appointment ) {
								?>
								<?php echo wp_kses_post( $appointment ); ?>
							<?php } ?>
						<?php } ?>
					<?php } else { ?>
						<h3 class="error"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
		
		<div id="bg_loader" class="simple_hide"></div>
		<div id="loader" class="simple_hide"><?php echo esc_html__( 'Your request is beeing handled.', 'doctor2go-connect' ); ?></div>
		<div id="return1" class="simple_hide mb-m center"></div>
		<div id="return2" class="simple_hide mb-m center"></div>
		<div id="cancellation_form_wrapper" class="simple_hide list_app walkin_form_wrapper mb-xl">
			<h2><?php echo esc_html__( 'Cancellation request', 'doctor2go-connect' ); ?></h2>
			
			<form id="cancellation_form" method="post" action="">
				<div class="row mb-m">
					<div class="col-sm-6">
						<div>
							<label for="client_name"><?php echo esc_html__( 'Patient name', 'doctor2go-connect' ); ?> *</label>
							<input class="required" type="text" value="<?php echo esc_html( $user_meta['first_name'][0] ); ?> <?php echo esc_html( $user_meta['last_name'][0] ); ?>" name="client_name" id="client_name">
						</div>
					</div>
					<div class="col-sm-6">
						<div>
							<label for="client_email"><?php echo esc_html__( 'Patient email', 'doctor2go-connect' ); ?> *</label>
							<input class="required" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email">
						</div>
					</div>
					<div class="col-sm-6">
						<div>
							<label for="doc_name"><?php echo esc_html__( 'Doctor name', 'doctor2go-connect' ); ?> *</label>
							<input readonly class="required" type="text" value="" name="doc_name" id="doc_name">
						</div>
					</div>
					<div class="col-sm-6">
						<div>
							<label for="doc_email"><?php echo esc_html__( 'Doctor email', 'doctor2go-connect' ); ?> *</label>
							<input readonly class="required" type="text" value="" name="client_email" id="doc_email">
						</div>
					</div>
					<div class="col-sm-12">
						<div>
							<label for="app_date"><?php echo esc_html__( 'Appointment date and time', 'doctor2go-connect' ); ?> *</label>
							<input readonly class="required" type="text" value="" name="doc_name" id="app_date">
						</div>
					</div>
					<div class="col-sm-12 simple_hide">
						<div>
							<label for="app_link"><?php echo esc_html__( 'Appointment link for doctor', 'doctor2go-connect' ); ?> *</label>
							<input readonly class="required" type="text" value="" name="doc_name" id="app_link">
						</div>
					</div>
				</div>
				<div class="row mb-m">
					<div class="col-sm-12">
						<div>
						<label for="app_date"><?php echo esc_html__( 'Comment (optional)', 'doctor2go-connect' ); ?></label>
							<textarea id="comment" name="comment"></textarea>
						</div>
					</div>
				</div>
				<a href="#" class="btn btn-default wp-block-button__link request_cancellation button" id="request_cancellation"><?php esc_html_e( 'send', 'doctor2go-connect' ); ?></a>
			</form>
		</div>
		<?php

		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}


	//
	// shortcode patient appointments
	public function d2g_appointment_confirmation() {
		// initialize class to get dynamic links
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$client_token      = isset( $_GET['client_token'] ) ? sanitize_text_field( wp_unslash( $_GET['client_token'] ) ) : '';
		$tokensSimpleArr[] = $client_token;
		$timezone          = get_user_timezone() ?: 'Europe/Amsterdam';

		// get clinet appointments from WCC
		$appointments           = json_decode( $this->get_patient_appointments_simple( $tokensSimpleArr ) );
		$structuredAppointments = array();

		ob_start();

		if ( $client_token ) {
			?>
			<p><?php echo esc_html__( 'You haven\'t booked any consultations yet. When you do, the details will appear in this section.', 'doctor2go-connect' ); ?></p>
		<?php } else { ?>
			<div class="row">
				<div class="list_app left single_app_wrapper col-sm-8">
					<?php
					if ( $appointments == null ) {
						?>
						<h3 class="error"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
						<?php
						$sc = ob_get_contents();
						ob_end_clean();
						return $sc;
					} elseif ( is_array( $appointments ) ) {
						if ( count( $appointments ) < 1 ) {
							?>
							<h3 class="error"><?php echo esc_html__( 'You don\’t have any upcoming consultations. Book a new appointment when you\'re ready.', 'doctor2go-connect' ); ?></h3>
						<?php } else { ?>
							<?php
							foreach ( $appointments as $appointment ) {
								// get doc info for appointment
								$docObj = $this->get_doctor_by_wcc_id( $appointment->user_id )[0];

								// doctor image
								$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( $docObj->ID ), 'thumbnail' )[0];
								if ( $feat_pic == '' ) {
									if ( get_option( 'd2g_placeholder' ) != '' ) {
										$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'thumbnail' )[0];
									} else {
										$feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';

									}
								}
								// doctor specialties
								$specialties   = get_the_terms( $docObj->ID, 'specialty' );
								$specialty_str = '';
								if ( $specialties !== false ) {
									foreach ( $specialties as $specialty ) {
										$specialty_str .= '<span>' . $specialty->name . '</span>';
									}
								}

								// appointment date
								$date     = new DateTime( $appointment->date );
								$date_now = new DateTime();
								// Calculate difference in seconds
								$diffInSeconds = $date->getTimestamp() - $date_now->getTimestamp();

								$date->setTimezone( new DateTimeZone( $timezone ) );

								// create the links
								// html link for questionnaires
								$delBtn                 = '';
								$questionnaireURLSimple = '';
								if ( isset( $appointment->answer_set_id ) ) {

									$pageData = $d2gAdmin::d2g_page_url( $currLang, 'intake_questionnaire', true );
									// url to load in iframe
									$questionnaireURLSimple = get_option( 'waiting_room_url' ) . 'answer_set/' . $appointment->answer_set_id . '?client_auth=' . $client_token;
								}
								// other links
								$consultLink = '<a class="button btn-default btn invert" target="_blank" href="' . get_option( 'waiting_room_url' ) . 'wachtkamer/' . $appointment->token . '?locale=' . explode( '_', get_locale() )[0] . '"><span class=" icon-videocam-outline"></span> ' . esc_html__( 'go to consultation', 'doctor2go-connect' ) . '</a>';
								$docLink     = '<a class="button btn-default btn" href="/doctor/' . $docObj->post_name . '"><span class=" icon-eye"></span> ' . esc_html__( 'view doctor page', 'doctor2go-connect' ) . '</a>';
								if ( $diffInSeconds <= 0 || $diffInSeconds > 86400 ) {
									$delBtn = '<a class="del_app button btn-default btn" href="#" data-app-id="' . $appointment->_id . '" data-user-id="' . $appointment->user_id . '"><span class=" icon-cancel-circled"></span> ' . esc_html__( 'cancel appointment', 'doctor2go-connect' ) . '</a>';
								}

								if ( $appointment->payment_has_paid == true ) {
									$payment_info = '<div class="paid"><strong>' . esc_html__( 'paid', 'doctor2go-connect' ) . '</strong></div>';
								} else {
									$pageData    = $d2gAdmin::d2g_page_url( $currLang, 'appointments', true );
									$redirectURL = '&redirect_url=' . urlencode( $pageData['url'] . '?app=' ) . $appointment->_id;

									$payment_info = '<div class="payment_needed error">
									<div class=""><strong>' . esc_html__( 'A payment is required for this appointment. You can either do it upofront or when entering the waiting room', 'doctor2go-connect' ) . '</strong></div>
									<div class="btn_wrap"><a class="btn btn-default payment_btn" target="_blank" href="' . get_option( 'waiting_room_url' ) . 'payment/' . $appointment->_id . '?locale=' . explode( '_', get_locale() )[0] . $redirectURL . '">' . esc_html__( 'pay now', 'doctor2go-connect' ) . '</a></div>
									</div>';
								}

								// create the appointment rows and save in array to sort them later
								if ( $appointment->location_to_go != null ) {
									$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper"><div id="app-' . $appointment->_id . '" class="app_row">
										<div class="feat_pic"><img src="' . $feat_pic . '"></div>
										<div class="content_outer">
											<div class="content">
												<p class="consult_type"><strong>' . esc_html__( 'Physical consultation', 'doctor2go-connect' ) . '</strong></p>
												<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
												<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
												<p class="address">' . $appointment->location_to_go->location_name . ': ' . $appointment->location_to_go->location_full_adress_url . '</p>
											</div> 
										</div>
										<div class="btn_wrap">' . $delBtn . '</div>
										</div></div>';
								} else {
									$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper">' . $payment_info . '<div id="app-' . $appointment->_id . '" class="app_row">
										<div class="feat_pic"><img src="' . $feat_pic . '"></div>
										<div class="content_outer">
											<div class="content">
												<p class="consult_type"><strong>' . esc_html__( 'Online consultation', 'doctor2go-connect' ) . '</strong></p>
												<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
												<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
											</div> 	
										</div>
										<div class="btn_wrap">' . $consultLink . ' ' . $delBtn . '</div>
										</div></div>';
								}

								// phpcs:ignore WordPress.Security.NonceVerification.Recommended
								$app_id = isset( $_GET['app'] ) ? sanitize_text_field( wp_unslash( $_GET['app'] ) ) : '';

								if ( $appointment->_id == $app_id ) {
									?>
								
									<?php if ( $questionnaireURLSimple != '' ) { ?>
										<h4 class="error only_mobile mb-s"><?php echo esc_html__( 'For this appointment you are requiered to fill in an intake questionnaire.', 'doctor2go-connect' ); ?></h4>
										<p class="mb-m only_mobile"><a class="scroll_to btn button btn-default" href="#questionnaire"><?php echo esc_html__( 'Go to questionnaire', 'doctor2go-connect' ); ?></a></p>
									<?php } ?>
									<div class="mb-xl">
										<?php echo wp_kses_post( $structuredAppointments[ $appointment->date ] ); ?>
									</div>
									<div class="mb-l only_mobile help_menu">
										<h2><?php echo esc_html__( 'Need help?', 'doctor2go-connect' ); ?></h2>
										<?php
											wp_nav_menu(
												array(
													'theme_location' => 'd2g-help-menu',
													'container' => 'nav',
													'container_class' => 'd2g-help-menu',
													'fallback_cb' => false,
													'echo' => true, // So we can return it instead of echoing
												)
											);
										?>
									</div>
									<?php if ( $questionnaireURLSimple != '' ) { ?>
										<h2 class="not_mobile"><?php echo esc_html__( 'For this appointment you are requiered to fill in an intake questionnaire.', 'doctor2go-connect' ); ?></h2>
										
										<iframe id="questionnaire" src="<?php echo esc_url( $questionnaireURLSimple ); ?>" style="width:100%; border:none; height:2500px"></iframe>
									<?php } ?>
								<?php } ?>
							<?php } ?>  
						<?php } ?>
					<?php } else { ?>
						<h3 class="error"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
					<?php } ?>
				</div>
				<div class="col-sm-4 not_mobile">
					<h2><?php echo esc_html__( 'Need help?', 'doctor2go-connect' ); ?></h2>
					<?php
						wp_nav_menu(
							array(
								'theme_location'  => 'd2g-help-menu',
								'container'       => 'nav',
								'container_class' => 'd2g-help-menu',
								'fallback_cb'     => false,
								'echo'            => true, // So we can return it instead of echoing
							)
						);
					?>
				</div>
			</div>
		<?php } ?>
		<div id="bg_loader" class="simple_hide"></div>
		<div id="loader" class="simple_hide"><?php echo esc_html__( 'Your request is beeing handled.', 'doctor2go-connect' ); ?></div>
		<?php

		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}

	//
	// shortcode account settings
	public function d2g_account_settings() {

		// Verify nonce
		$nonce = isset( $_POST['d2g_account_nonce'] ) ? sanitize_key( wp_unslash( $_POST['d2g_account_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'd2g_account_action' ) ) {
			echo '<p class="error">' . esc_html__( 'Security check failed. Please refresh the page and try again.', 'doctor2go-connect' ) . '</p>';
			return;
		}

		$current_user = wp_get_current_user();
		$user_id      = $current_user->data->ID;
		$timezones    = d2g_timezones();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === wp_unslash( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['custom_registration'] ) ) {
			$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$password         = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
			$confirm_password = isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : '';
			$errors           = array();

			// Validate inputs
			if ( empty( $email ) ) {
				$errors[] = __( 'All fields are required.', 'doctor2go-connect' );
			}

			if ( ! empty( $email ) && ! is_email( $email ) ) {
				$errors[] = __( 'Please provide a valid email address.', 'doctor2go-connect' );
			}

			if ( ! empty( $password ) && $password === $confirm_password ) {
				wp_set_password( $password, $user_id );
			}

			// Update meta if exists
			if ( isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$meta = wp_unslash( $_POST['meta'] );
				foreach ( $meta as $key => $value ) {
					update_user_meta(
						$user_id,
						sanitize_key( $key ),
						sanitize_text_field( $value )
					);
				}
			}

			// Display messages
			if ( empty( $errors ) && ! is_wp_error( $user_id ) ) {
				echo '<p class="success">' . esc_html__( 'Update was successful.', 'doctor2go-connect' ) . ' </p>';
			} elseif ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					echo '<p class="error">' . esc_html( $error ) . '</p>';
				}
			} elseif ( is_wp_error( $user_id ) ) {
				echo '<p class="error">' . esc_html( $user_id->get_error_message() ) . '</p>';
			}
		}

		$user_meta = get_user_meta( $user_id );

		// Display the form for the account settings
		ob_start();
		?>
		<div class="d2g_form_wrapper">
			<form id="custom-registration-form" method="post">
				<?php wp_nonce_field( 'd2g_account_action', 'd2g_account_nonce' ); ?>
				<p>
					<label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[first_name]" id="first_name" required value="<?php echo esc_html( $user_meta['first_name'][0] ); ?>">
				</p>
				<p>
					<label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[last_name]" id="last_name" required value="<?php echo esc_html( $user_meta['last_name'][0] ); ?>">
				</p>
				<p>
					<label for="email"><?php echo esc_html__( 'Email (can not be changed)', 'doctor2go-connect' ); ?></label>
					<input readonly type="email" name="email" id="email" required value="<?php echo esc_html( $current_user->data->user_email ); ?>">
				</p>
				<p>
					<label for="p_tel"><?php echo esc_html__( 'Phone', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[p_tel]" id="p_tel" required value="<?php echo esc_html( $user_meta['p_tel'][0] ); ?>">
				</p>
				<p id="time_zone_wrapper">
				<label for="p_tel"><?php echo esc_html__( 'Timezone', 'doctor2go-connect' ); ?></label>
					<select name="meta[p_timezone]">
						<option value="0"><?php echo esc_html__( 'make a selection', 'doctor2go-connect' ); ?></option>
						<?php foreach ( $timezones as $group => $zones ) { ?>
							<optgroup label="<?php echo esc_html( $group ); ?>">
								<?php foreach ( $zones as $key => $name ) { ?>
									<option <?php echo ( $key == $user_meta['p_timezone'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</p>
				<p class="attention"><?php echo esc_html__( 'Only fill in the password fields, if you want to change your password. ', 'doctor2go-connect' ); ?></p>
				<p>
					<label for="password"><?php echo esc_html__( 'Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="password" id="password" >
				</p>
				<p>
					<label for="confirm_password"><?php echo esc_html__( 'Confirm Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="confirm_password" id="confirm_password" >
				</p>
				<p>
					<input type="hidden" name="custom_registration" value="1">
					<input type="submit" value="<?php echo esc_html__( 'save', 'doctor2go-connect' ); ?>">
				</p>
			</form>
		</div>
		<?php if ( ( get_option( 'activate_2fa_link' ) == '1' ) ) { ?>
			<div class="btn_wrapper">
				<a class="btn btn-default" href="/wp/wp-login.php?itsec_after_interstitial=2fa-on-board"><?php esc_html_e( 'configure 2FA', 'doctor2go-connect' ); ?></a>
			</div>
		<?php } ?>
		
		<?php
		return ob_get_clean();
	}

	// shortcode to show liked posts
	public function d2g_liked_posts() {
		global $cssClass;
		$cssClass    = 'col-sm-6 col-md-4';
		$liked_posts = get_liked_posts();

		if ( empty( $liked_posts ) ) {
			return '<p class="error">' . esc_html__( 'No liked doctors yet.', 'doctor2go-connect' ) . '</p>';
		}

		// WP_Query arguments
		$args = array(
			'post_type'      => 'd2g_doctor', // Custom post type
			'post__in'       => $liked_posts, // Include only specific post IDs
			'orderby'        => 'post__in', // Maintain the order of IDs
			'posts_per_page' => -1, // Retrieve all specified posts
		);

		// Custom query
		$query = new WP_Query( $args );

		ob_start();

		// Check if the query returns posts
		if ( $query->have_posts() ) {
			echo '<div class="outer_wrapper"><div class="d2g-doctor-grid row">'; // Wrapper div for styling
			while ( $query->have_posts() ) {
				$query->the_post();
				include d2g_locate_template( 'content-doctor-grid.php' );
			}
			echo '</div></div>';
		} else {
			echo '<p class="error">' . esc_html__( 'No liked doctors yet.', 'doctor2go-connect' ) . '</p>';
		}

		// Restore original post data
		wp_reset_postdata();
		return ob_get_clean();
	}


	public function show_wcc_clinet_info() {

		ob_start();

		// Restore original post data
		wp_reset_postdata();
		return ob_get_clean();
	}


	public function d2g_patient_portal() {
		// initialize class to get dynamic links
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		// get client info from WP DB
		$currUser       = wp_get_current_user();
		$user_meta      = get_user_meta( $currUser->data->ID );
		$tokensCheck    = $user_meta['tokens'][0];
		$tokensAssArray = unserialize( $user_meta['tokens'][0] );

		foreach ( $tokensAssArray as $org => $token ) {
			$orgsArray[] = $org;
		}

		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pageData = $d2gAdmin::d2g_page_url( $currLang, 'secure_patient_portal', true );

		ob_start();
		?>
		<?php
		if ( $tokensCheck == '' ) {
			echo esc_html__( 'You don\'t have access to a patient portal at this time. This usually means you haven\'t booked a consultation yet. Once you\'ve booked and paid for a consultation with a doctor, your patient portal will become available.', 'doctor2go-connect' );
		} else {
			?>
		<div class="alignwide">
			<div class="row with_right_sidebar">
				<div class="col-sm-9">
					<?php
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					if ( isset( $_GET['url'] ) ) {
						// Sanitize GET parameters
						$iframe_url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
						$title      = isset( $_GET['title'] ) ? sanitize_text_field( wp_unslash( $_GET['title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash

						?>
						<h2>
							<?php
							echo esc_html__( 'Patient portal:', 'doctor2go-connect' ) . ' ' . esc_html( $title );
							?>
						</h2>
						<p>
							<?php
							echo esc_html__( "This secure portal lets you send and receive messages from your doctor and access any documents they've shared with you.", 'doctor2go-connect' );
							?>
						</p>
						<iframe 
							id="patient_portal" 
							style="width:100%; border:none; height:1200px; overflow-y:scroll;" 
							src="<?php echo esc_url( $iframe_url ); ?>">
						</iframe>
					<?php } else { ?>
						<h2 class="error">
							<?php esc_html_e( 'No doctor has been selected yet. Please choose one to proceed.', 'doctor2go-connect' ); ?>
						</h2>
					<?php } ?>



				</div>
				<div class="col-sm-3">
					<h2><?php echo esc_html__( 'Your doctor\'s', 'doctor2go-connect' ); ?></h2>
				
					<?php
					$args = array(
						'post_type'      => 'd2g_doctor',
						'posts_per_page' => -1, // or any limit you want
						'meta_query'     => array(
							array(
								'key'   => 'organisation_key',
								'value' => $orgsArray, // Replace with your actual keys

							),
						),
					);

					$query = new WP_Query( $args );

					if ( $query->have_posts() ) :
						?>
						<ul class="mb-xl  questionnaires_list"  id="questionnaires" style="list-style: none; padding:0;">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$orgKey = get_post_meta( get_the_ID(), 'organisation_key', true );
							$title  = get_the_title();
							// doctor image
							$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' )[0];
							if ( $feat_pic == '' ) {
								if ( get_option( 'd2g_placeholder' ) != '' ) {
									$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'thumbnail' )[0];
								} else {
									$feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';

								}
							}
							?>
							<li>
								<a class="center" href="<?php echo esc_url( $pageData['url'] ); ?>?url=<?php echo urlencode( get_option( 'waiting_room_url' ) . 'portal/' . $tokensAssArray[ $orgKey ] . '?skip_cookie_wall=true&locale=' . $currLang ); ?>&title=<?php echo esc_html( $title ); ?>">
									<span style="font-size:16px" class="demo-icon icon-right-open"></span>
									<div class="feat_pic"><img src="<?php echo esc_url( $feat_pic ); ?>"></div>
									<strong>
										<?php echo esc_html( $title ); ?>
									</strong>
								</a>
							</li>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
						</ul>
						<?php
					else :
						echo '<p class="error">No doctors found for the selected organisation.</p>';
					endif;
					?>
					
				</div>
			</div>
		</div>
			<?php
		}

		return ob_get_clean();
	}


	public function d2g_public_patient_portal() {
		ob_start();
		?>
		<iframe id="patient_portal" style="width: 100%; border:none; height:1200px; overflow-y:scroll;" src="<?php echo esc_url( get_option( 'waiting_room_url' ) . 'portal/' ); ?>"></iframe>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param $objects
	 * @return array from taxonmy objects in only key value pairs
	 */
	private function prepArray( $objects ) {
		$prepArray = array();
		foreach ( $objects as $object ) {
			$prepArray[ $object->slug ] = $object->name;
		}
		return $prepArray;
	}


	/**
	 * @param array $tokens
	 * @return mixed
	 * API call to get appointments from client
	 */
	private function get_patient_appointments_simple( $tokens = array() ) {

		$myTime   = new DateTime();
		$unixTime = $myTime->format( 'U' );
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $tokens[0] . '_' . $superKey );

		$response = wp_remote_request(
			get_option( 'api_url_short' ) . 'doclisting/appointments/client',
			array(
				'method'  => 'POST',
				'timeout' => 20,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'time'   => $unixTime,
						'token'  => $tokens[0],
						'hash'   => $myHash,
						'type'   => 'client',
						'tokens' => $tokens,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * @param array $tokens
	 * @return mixed
	 * API call to get appointments from client
	 */
	private function get_wcc_client_info( $tokens = array() ) {

		if ( empty( $tokens ) || empty( $tokens[0] ) ) {
			return false;
		}

		$unixTime = time();
		$superKey = get_option( 'wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $tokens[0] . '_' . $superKey );

		$payload = array(
			'time'   => (string) $unixTime,
			'token'  => $tokens[0],
			'hash'   => $myHash,
			'type'   => 'client',
			'tokens' => $tokens,
		);

		$response = wp_remote_request(
			get_option( 'api_url_short' ) . 'doclisting/client',
			array(
				'method'  => 'POST',
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

		return wp_remote_retrieve_body( $response );
	}



	/**
	 * @param $wcc_user_id
	 * @return int[]|WP_Post[]
	 */
	private function get_doctor_by_wcc_id( $wcc_user_id ) {
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


