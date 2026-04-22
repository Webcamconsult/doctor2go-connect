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
	public function d2gc_profile_edit( $atts ) {
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
			$pubProfile   = $d2gAdmin::d2gc_get_pub_profile( $currUserID )[0];
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
		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && $currLang != 'en' ) {
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
		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && $currLang != 'en' ) {
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
		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && $currLang != 'en' ) {
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
		<div class="d2g_doctor-form container d2g_wrapper">
			<div class="row">
				<div class="col-12 outer_form_wrapper">
					<form id="doctor_post" name="new_post" method="post" action="<?php echo esc_html( $permalink ); ?>" enctype="multipart/form-data">
						<div class="row mb-4">
							<div class="col-12">
								<p id="submitwrap" class="d-flex flex-wrap gap-2 justify-content-center mt-5">
									<?php if ( $profileStatus == 'draft' ) { ?>
										<button class="btn btn-primary wp-block-button__link save_doctor" tabindex="6" id="save">
											<?php esc_html_e( 'save as draft', 'doctor2go-connect' ); ?>
										</button>
										<button class="btn btn-success wp-block-button__link publish_doctor" tabindex="6" id="submit">
											<?php esc_html_e( 'publish profile', 'doctor2go-connect' ); ?>
										</button>
									<?php } else { ?>
										<button class="btn btn-primary wp-block-button__link save_doctor" tabindex="6" id="save">
											<?php esc_html_e( 'save profile', 'doctor2go-connect' ); ?>
										</button>
										<button class="btn btn-warning wp-block-button__link unpublish_doctor" tabindex="6" id="unpublish">
											<?php esc_html_e( 'unpublish profile', 'doctor2go-connect' ); ?>
										</button>
									<?php } ?>
									<a target="_blank" class="btn btn-outline-primary wp-block-button__link" href="/?post_type=d2g_doctor&p=<?php echo esc_html( $pubProfileID ); ?>&preview=true">
										<?php esc_html_e( 'preview profile', 'doctor2go-connect' ); ?>
									</a>
								</p>
							</div>
						</div>

						<ul class="nav nav-tabs mb-3" id="docTab" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane" type="button" role="tab" aria-controls="basic-tab-pane" aria-selected="true">
									<strong><?php echo esc_html( __( 'Basic personal information', 'doctor2go-connect' ) ); ?></strong>
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link exp_edu_button" id="exp_edu-tab" data-bs-toggle="tab" data-bs-target="#exp_edu-tab-pane" type="button" role="tab" aria-controls="exp_edu-tab-pane" aria-selected="false">
									<strong><?php echo esc_html( __( 'Education & working experience', 'doctor2go-connect' ) ); ?></strong>
								</button>
							</li>
						</ul>

						<div class="alert alert-danger mt-3 mb-3 simple_hide"></div>

						<div class="tab-content mb-5" id="myTabContent">
							<div class="basic_data pm_d2g_tab_content first tab-pane fade show active" id="basic-tab-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
								<div class="row mb-3">
									<div class="col-12 col-lg-6">
										<h3><?php echo esc_html__( 'Personal information', 'doctor2go-connect' ); ?></h3>
										<div class="form-table">
											<div class="mb-3">
												<label class="form-label small">
													<?php echo esc_html__( 'Organisation', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="d2g_organisation" value="<?php echo esc_html( $doctor_meta['d2g_organisation'][0] ); ?>" tabindex="1" name="meta[d2g_organisation]" placeholder="<?php echo esc_html__( 'Organisation', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'First name *', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control required" id="d2g_first_name" value="<?php echo esc_html( $doctor_meta['d2g_first_name'][0] ); ?>" tabindex="1" name="meta[d2g_first_name]" placeholder="<?php echo esc_html__( 'First name *', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Last name *', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control required" id="d2g_last_name" value="<?php echo esc_html( $doctor_meta['d2g_last_name'][0] ); ?>" tabindex="1" name="meta[d2g_last_name]" placeholder="<?php echo esc_html__( 'Last name *', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Title *', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="d2g_emp_title" value="<?php echo esc_html( $doctor_meta['d2g_emp_title'][0] ); ?>" tabindex="1" name="meta[d2g_emp_title]" placeholder="<?php echo esc_html__( 'Title *', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Display name', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="d2g_post_title" value="<?php echo esc_html( $pubProfile->post_title ); ?>" tabindex="1" name="post_title" placeholder="<?php echo esc_html__( 'Display name', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Address', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="address" value="<?php echo esc_html( $doctor_meta['d2g_address'][0] ); ?>" tabindex="1" name="meta[d2g_address]" placeholder="<?php echo esc_html__( 'Address', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Zip', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="zip" value="<?php echo esc_html( $doctor_meta['d2g_zip'][0] ); ?>" tabindex="1" name="meta[d2g_zip]" placeholder="<?php echo esc_html__( 'Zip code', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'City', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="city" value="<?php echo esc_html( $doctor_meta['d2g_city'][0] ); ?>" tabindex="1" name="meta[d2g_city]" placeholder="<?php echo esc_html__( 'City *', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Country *', 'doctor2go-connect' ); ?>
												</label>
												<select name="tax[country-origin]" class="form-select">
													<option value="">
														<?php echo esc_html__( 'Country', 'doctor2go-connect' ); ?>
													</option>
													<?php
													foreach ( $allCountries as $country ) {
														$selected = '';
														if ( isset( $countriesArray[ $country->slug ] ) ) {
															$selected = 'selected';
														}
														?>
														<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $country->slug ); ?>">
															<?php
															if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
																echo ( $currLang == 'en' ) ? esc_html( $country->name ) : esc_html( get_term_meta( $country->term_id, 'rudr_text_' . $currLang, true ) );
															} else {
																echo esc_html( $country->name );
															}
															?>
														</option>
														<?php
													}
													?>
												</select>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Practice phone number (optional but recommended for use with a reception)', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="tel" value="<?php echo esc_html( $doctor_meta['tel'][0] ); ?>" tabindex="1" name="meta[tel]" placeholder="<?php echo esc_html__( 'Tel ', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'Mobile phone number  (optional)', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control" id="mobile" value="<?php echo esc_html( $doctor_meta['d2g_mobile'][0] ); ?>" tabindex="1" name="meta[d2g_mobile]" placeholder="<?php echo esc_html__( 'Mobile ', 'doctor2go-connect' ); ?>"/>

												<label class="form-label small mt-2">
													<?php echo esc_html__( 'E-mail *', 'doctor2go-connect' ); ?>
												</label>
												<input type="text" class="form-control required" id="email" value="<?php echo esc_html( $doctor_meta['d2g_main_email'][0] ); ?>" tabindex="1" name="meta[d2g_main_email]" placeholder="<?php echo esc_html__( 'E-mail *', 'doctor2go-connect' ); ?>"/>
											</div>

											<h3 class="mt-5">
												<?php echo esc_html__( 'Registration information', 'doctor2go-connect' ); ?>
											</h3>

											<label class="form-label small">
												<?php echo esc_html__( 'Registration number', 'doctor2go-connect' ); ?>
											</label>
											<input type="text" class="form-control" id="reg_nr" value="<?php echo esc_html( $doctor_meta['reg_nr'][0] ); ?>" tabindex="1" name="meta[reg_nr]" placeholder="<?php echo esc_html__( 'Registration number ', 'doctor2go-connect' ); ?>"/>

											<label class="form-label small mt-2">
												<?php echo esc_html__( 'Country of registration', 'doctor2go-connect' ); ?>
											</label>
											<input type="text" class="form-control" id="reg_country" value="<?php echo esc_html( $doctor_meta['reg_country'][0] ); ?>" tabindex="1" name="meta[reg_country]" placeholder="<?php echo esc_html__( 'Country of registration', 'doctor2go-connect' ); ?>"/>
										</div>

										<?php if ( get_option( 'd2gc_local_user' ) == 1 ) { ?>
											<h3 class="mt-5">
												<?php echo esc_html__( 'Code for booking calendar', 'doctor2go-connect' ); ?>
											</h3>
											<div class="form-table">
												<input type="text" class="form-control" id="d2g_cal_code" value="<?php echo esc_html( $doctor_meta['d2g_cal_code'][0] ); ?>" tabindex="1" name="meta[d2g_cal_code]" placeholder="<?php echo esc_html__( 'Shortcode or iframe for booking calendar', 'doctor2go-connect' ); ?>"/>
											</div>
										<?php } else { ?>
											<?php
											$currencies = array( 'EUR', 'USD', 'GBP', 'ALL', 'MXN', 'AUD', 'INR', 'AZN', 'BYN', 'BGN', 'HRK', 'CZK', 'DKK', 'GEL', 'HUF', 'ISK', 'CHF', 'MKD', 'MDL', 'NOK', 'PLN', 'RON', 'RUB', 'RSD', 'SEK', 'CHF', 'TRY', 'UAH', 'CAD', 'NZD', 'BRL', 'ZAR' );
											?>
											<h3 class="mt-5">
												<?php echo esc_html__( 'Payment & extra consult settings', 'doctor2go-connect' ); ?>
											</h3>
											<div class="form-table payment_settings mb-3">
												<p class="alert alert-light">
													<strong>
														<?php echo esc_html__( 'Tariffs for the booking calendar are configured in your Webcamconsult / Doctor2Go dashboard, as outlined in the Getting Started guide. However, prices for email and walk-in consultations must be set separately here, as they are special consults.', 'doctor2go-connect' ); ?>
													</strong>
												</p>

												<label class="form-label small">
													<?php echo esc_html__( 'Walk-in price & currency', 'doctor2go-connect' ); ?>*
												</label>
												<div class="row g-2 align-items-center">
													<div class="col-8">
														<input type="text" class="form-control price_input" id="walk_in_price" value="<?php echo esc_html( $doctor_meta['walk_in_price'][0] ); ?>" tabindex="1" name="meta[walk_in_price]" placeholder="<?php echo esc_html__( 'Walk-In price', 'doctor2go-connect' ); ?>*"/>
													</div>
													<div class="col-4">
														<select class="form-select" name="meta[walk_in_currency]" id="walk_in_currency">
															<?php foreach ( $currencies as $currency ) { ?>
																<option <?php echo ( $currency == $doctor_meta['walk_in_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>">
																	<?php echo esc_html( $currency ); ?>
																</option>
															<?php } ?>
														</select>
													</div>
												</div>

												<label class="form-label small mt-3">
													<?php echo esc_html__( 'Email advice price & currency', 'doctor2go-connect' ); ?>*
												</label>
												<div class="row g-2 align-items-center">
													<div class="col-8">
														<input type="text" class="form-control price_input" id="written_con_price" value="<?php echo esc_html( $doctor_meta['written_con_price'][0] ); ?>" tabindex="1" name="meta[written_con_price]" placeholder="<?php echo esc_html__( 'Written consult price', 'doctor2go-connect' ); ?>"/>
													</div>
													<div class="col-4">
														<select class="form-select" name="meta[written_con_currency]" id="written_con_currency">
															<?php foreach ( $currencies as $currency ) { ?>
																<option <?php echo ( $currency == $doctor_meta['written_con_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>">
																	<?php echo esc_html( $currency ); ?>
																</option>
															<?php } ?>
														</select>
													</div>
												</div>

												<div class="mt-3">
													<label class="small"><?php echo esc_html__( 'E-mail consult questionnaire', 'doctor2go-connect' ); ?>*</label>
													<select class="form-control" name="meta[written_con_type]" id="written_con_type">
														<?php 
														$types	  = array( 'short' => 'Short generic Email Advice', 'derma_email_advice' => 'Dermatology Email Advice' );
														foreach ( $types as $type => $label ) { ?>
															<option <?php echo ( $type == $doctor_meta['written_con_type'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $type ); ?>"><?php echo esc_html( $label ); ?></option>    
														<?php } ?>
													</select>
												</div>

												<p class="mt-3 mb-4 simple_hide">
													<label class="form-check-label">
														<input <?php echo ( $doctor_meta['d2g_intake_call'][0] == 1 ) ? 'checked' : ''; ?> class="form-check-input" name="meta[d2g_intake_call]" type="checkbox" value="1">
														<span>
															<?php echo esc_html__( 'I offer a free intake call', 'doctor2go-connect' ); ?>
														</span>
													</label>
												</p>
											</div>
										<?php } ?>

										<h3 class="mt-5">
											<?php echo esc_html__( 'Holiday settings', 'doctor2go-connect' ); ?>
										</h3>
										<p class="alert alert-light">
											<strong>
												<?php echo esc_html__( 'Enter your next holiday here to block e-mail consults and to show a notice on your detail page during your absence.', 'doctor2go-connect' ); ?>
											</strong>
										</p>
										<div class="row g-3">
											<div class="col-12 col-md-6">
												<label class="form-label small">
													<?php echo esc_html__( 'Start date', 'doctor2go-connect' ); ?>
												</label>
												<input type="date" class="form-control" name="meta[start_holiday]" value="<?php echo esc_html( $doctor_meta['start_holiday'][0] ); ?>">
											</div>
											<div class="col-12 col-md-6">
												<label class="form-label small">
													<?php echo esc_html__( 'End date', 'doctor2go-connect' ); ?>
												</label>
												<input type="date" class="form-control" name="meta[end_holiday]" value="<?php echo esc_html( $doctor_meta['end_holiday'][0] ); ?>">
											</div>
										</div>
									</div>

									<div class="col-12 col-lg-6 lists">
										<h3><?php echo esc_html__( 'Profile image', 'doctor2go-connect' ); ?></h3>
										<div class="form-table pic_upload_wrapper mb-3">
											<?php if ( ! $feat_pic_full ) { ?>
												<p class="mb-2">
													<input type="file" class="form-control" name="picture_1"/>
												</p>
												<p class="small text-muted">
													<?php esc_html_e( 'To upload your image you first need to choose one and than save your profile. The image will be displayed after the pagereload.', 'doctor2go-connect' ); ?>
												</p>
											<?php } else { ?>
												<p class="mb-3">
													<?php esc_html_e( 'Before you can upload a new image, please first delete the old one.', 'doctor2go-connect' ); ?>
												</p>
												<div class="profile_pic_wrapper" style="max-width:400px">
													<a class="del_img_link btn btn-outline-danger flaticon-dustbin mb-2" data-doc-id="<?php echo esc_html( $pubProfileID ); ?>" data-image-id="<?php echo esc_html( $img_ID ); ?>" href="#"></a>
													<img class="img-fluid" src="<?php echo esc_html( $feat_pic_full ); ?>">
												</div>
											<?php } ?>
										</div>
										<div class="mb-3">
											<h3>
												<?php echo esc_html__( 'Languages', 'doctor2go-connect' ); ?> *
											</h3>
											<select name="tax[doctor-language][]" multiple="multiple" class="form-select mb-3">
												<?php
												foreach ( $allLanguages as $language ) {
													$selected = '';
													if ( isset( $langArray[ $language->slug ] ) ) {
														$selected = 'selected';
													}
													?>
													<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $language->slug ); ?>">
														<?php
														if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
															echo ( $currLang == 'en' ) ? esc_html( $language->name ) : esc_html( get_term_meta( $language->term_id, 'rudr_text_' . $currLang, true ) );
														} else {
															echo esc_html( $language->name );
														}
														?>
													</option>
													<?php
												}
												?>
											</select>
										</div>

										<div class="extra mb-5">
											<label class="form-check-label" for="sub_title">
												<input name="meta[sub_title]" id="sub_title" type="checkbox" checked value="y" class="form-check-input">
												<?php echo esc_html__( 'I offer subtitles (this is standard function with in webcamconsult)', 'doctor2go-connect' ); ?>
											</label>
										</div>

										<div id="specialty_wrapper" class="mb-5">
											<h3>
												<?php echo esc_html__( 'Fields of study', 'doctor2go-connect' ); ?> *
											</h3>
											<select name="tax[doctor-specialty][]" multiple="multiple" class="form-select mb-3">
												<?php
												foreach ( $allSpecialities as $speciality ) {
													$selected = '';
													if ( isset( $specArray[ $speciality->slug ] ) ) {
														$selected = 'selected';
													}
													?>
													<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $speciality->slug ); ?>">
														<?php
														if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
															echo ( $currLang == 'en' ) ? esc_html( $speciality->name ) : esc_html( get_term_meta( $speciality->term_id, 'rudr_text_' . $currLang, true ) );
														} else {
															echo esc_html( $speciality->name );
														}
														?>
													</option>
													<?php
												}
												?>
											</select>
										</div>

										<h3>
											<?php echo esc_html__( 'About your self', 'doctor2go-connect' ); ?>
										</h3>
										<p>
											<strong>
												<?php echo esc_html__( 'Please provide a brief biography highlighting your background and strengths for visitors.', 'doctor2go-connect' ); ?>
											</strong>
										</p>
										<div class="form-table mb-3">
											<?php wp_editor( $pubProfile->post_content, 'docdesc' ); ?>
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane fade pm_d2g_tab_content edu exp_edu" id="exp_edu-tab-pane" role="tabpanel" aria-labelledby="exp_edu-tab" tabindex="0">
								<h3><?php echo esc_html__( 'education', 'doctor2go-connect' ); ?></h3>

								<div class="form-table edu_wrapper mb-4">
									<?php $counter = 0; ?>

									<div class="row exp_edu fw-bold mb-2">
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'start & end date', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?>
										</div>
									</div>

									<?php if ( isset( $doctor_meta['edus'] ) ) { ?>
										<?php foreach ( $doctor_meta['edus'] as $edu ) { ?>
											<div class="row exp_edu edu_<?php echo esc_html( $counter ); ?> align-items-start mb-2">
												<?php if ( $counter > 0 ) { ?>
													<div class="col-12 text-end mb-1">
														<a class="remove_btn btn btn-sm btn-outline-danger btn-add" href="#">
															<span class="icon-minus-circled"></span>
														</a>
													</div>
												<?php } ?>

												<div class="col-12 col-md-3">
													<div class="row g-2">
														<div class="col-6">
															<input type="text" class="form-control" id="d2g_exp_edu_date" value="<?php echo esc_html( $edu['d2g_exp_edu_start_date'] ); ?>" tabindex="1" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
														</div>
														<div class="col-6">
															<input type="text" class="form-control" id="d2g_exp_edu_study" value="<?php echo esc_html( $edu['d2g_exp_edu_end_date'] ); ?>" tabindex="1" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
														</div>
													</div>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_study" value="<?php echo esc_html( $edu['d2g_exp_edu_study'] ); ?>" tabindex="1" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_study]" placeholder="<?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_title" value="<?php echo esc_html( $edu['d2g_exp_edu_title'] ); ?>" tabindex="1" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_org" value="<?php echo esc_html( $edu['d2g_exp_edu_org'] ); ?>" tabindex="1" name="meta[edus][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?>"/>
												</div>
											</div>
											<?php ++$counter; ?>
										<?php } ?>
									<?php } else { ?>
										<div class="row exp_edu edu_0 mb-2">
											<div class="col-12 col-md-3">
												<div class="row g-2">
													<div class="col-6">
														<input type="text" class="form-control" id="d2g_exp_edu_date" tabindex="1" name="meta[edus][0][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
													</div>
													<div class="col-6">
														<input type="text" class="form-control" id="d2g_exp_edu_study" tabindex="1" name="meta[edus][0][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
													</div>
												</div>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_study" tabindex="1" name="meta[edus][0][d2g_exp_edu_study]" placeholder="<?php echo esc_html__( 'study area', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_title" tabindex="1" name="meta[edus][0][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'degree', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_org" tabindex="1" name="meta[edus][0][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'institution', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
									<?php } ?>
								</div>

								<div class="btn_wrapper mb-4">
									<a class="btn btn-outline-primary wp-block-button__link add_edu invert" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#">
										<?php echo esc_html__( 'add an extra education', 'doctor2go-connect' ); ?>
									</a>
								</div>

								<h3><?php echo esc_html__( 'working experience', 'doctor2go-connect' ); ?></h3>

								<div class="form-table exp_wrapper mb-4">
									<?php $counter = 0; ?>

									<div class="row exp_edu fw-bold mb-2">
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'start & end date', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'expertise', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'position', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-3">
											<?php echo esc_html__( 'company', 'doctor2go-connect' ); ?>
										</div>
									</div>

									<?php if ( isset( $doctor_meta['exps'] ) ) { ?>
										<?php foreach ( $doctor_meta['exps'] as $exp ) { ?>
											<div class="row exp_edu exp_<?php echo esc_html( $counter ); ?> align-items-start mb-2">
												<?php if ( $counter > 0 ) { ?>
													<div class="col-12 text-end mb-1">
														<a class="remove_btn btn btn-sm btn-outline-danger btn-add" href="#">
															<span class="icon-minus-circled"></span>
														</a>
													</div>
												<?php } ?>

												<div class="col-12 col-md-3">
													<div class="row g-2">
														<div class="col-6">
															<input type="text" class="form-control" id="d2g_exp_edu_date" value="<?php echo esc_html( $exp['d2g_exp_edu_start_date'] ); ?>" tabindex="1" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
														</div>
														<div class="col-6">
															<input type="text" class="form-control" id="d2g_exp_edu_study" value="<?php echo esc_html( $exp['d2g_exp_edu_end_date'] ); ?>" tabindex="1" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
														</div>
													</div>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_expertise" value="<?php echo esc_html( $exp['d2g_exp_edu_expertise'] ); ?>" tabindex="1" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_expertise]" placeholder="<?php echo esc_html__( 'exptertise', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_title" value="<?php echo esc_html( $exp['d2g_exp_edu_title'] ); ?>" tabindex="1" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'position', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-3 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_exp_edu_org" value="<?php echo esc_html( $exp['d2g_exp_edu_org'] ); ?>" tabindex="1" name="meta[exps][<?php echo esc_html( $counter ); ?>][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'company', 'doctor2go-connect' ); ?>"/>
												</div>
											</div>
											<?php ++$counter; ?>
										<?php } ?>
									<?php } else { ?>
										<div class="row exp_edu exp_0 mb-2">
											<div class="col-12 col-md-3">
												<div class="row g-2">
													<div class="col-6">
														<input type="text" class="form-control" id="d2g_exp_edu_date" tabindex="1" name="meta[exps][0][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
													</div>
													<div class="col-6">
														<input type="text" class="form-control" id="d2g_exp_edu_study" tabindex="1" name="meta[exps][0][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
													</div>
												</div>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_expertise" tabindex="1" name="meta[exps][0][d2g_exp_edu_expertise]" placeholder="<?php echo esc_html__( 'exptertise', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_title" tabindex="1" name="meta[exps][0][d2g_exp_edu_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-3 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_exp_edu_org" tabindex="1" name="meta[exps][0][d2g_exp_edu_org]" placeholder="<?php echo esc_html__( 'company', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
									<?php } ?>
								</div>

								<div class="btn_wrapper mb-4">
									<a class="btn btn-outline-primary wp-block-button__link add_exp invert" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#">
										<?php echo esc_html__( 'add an extra working experience', 'doctor2go-connect' ); ?>
									</a>
								</div>

								<h3><?php echo esc_html__( 'publications', 'doctor2go-connect' ); ?></h3>

								<div class="form-table pub_wrapper mb-4">
									<?php $counter = 0; ?>

									<div class="row exp_edu fw-bold mb-2">
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'author', 'doctor2go-connect' ); ?>
										</div>
										<div class="col-12 col-md-2">
											<?php echo esc_html__( 'publication Date', 'doctor2go-connect' ); ?>
										</div>
									</div>

									<?php if ( isset( $doctor_meta['pubs'] ) ) { ?>
										<?php foreach ( $doctor_meta['pubs'] as $exp ) { ?>
											<div class="row exp_edu exp_<?php echo esc_html( $counter ); ?> align-items-start mb-2">
												<?php if ( $counter > 0 ) { ?>
													<div class="col-12 text-end mb-1">
														<a class="remove_btn btn btn-sm btn-outline-danger btn-add" href="#">
															<span class="icon-minus-circled"></span>
														</a>
													</div>
												<?php } ?>

												<div class="col-12 col-md-2">
													<input type="text" class="form-control" id="d2g_pub_title" value="<?php echo esc_html( $exp['d2g_pub_title'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-2 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_pub_link" value="<?php echo esc_html( $exp['d2g_pub_link'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_link]" placeholder="<?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-2 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_pub_journal" value="<?php echo esc_html( $exp['d2g_pub_journal'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_journal]" placeholder="<?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-2 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_pub_type" value="<?php echo esc_html( $exp['d2g_pub_type'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_type]" placeholder="<?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-2 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_pub_author" value="<?php echo esc_html( $exp['d2g_pub_author'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_author]" placeholder="<?php echo esc_html__( 'author', 'doctor2go-connect' ); ?>"/>
												</div>

												<div class="col-12 col-md-2 mt-2 mt-md-0">
													<input type="text" class="form-control" id="d2g_pub_date" value="<?php echo esc_html( $exp['d2g_pub_date'] ); ?>" tabindex="1" name="meta[pubs][<?php echo esc_html( $counter ); ?>][d2g_pub_date]" placeholder="<?php echo esc_html__( 'publication date', 'doctor2go-connect' ); ?>"/>
												</div>
											</div>
											<?php ++$counter; ?>
										<?php } ?>
									<?php } else { ?>
										<div class="row exp_edu exp_0 mb-2">
											<div class="col-12 col-md-2">
												<input type="text" class="form-control" id="d2g_pub_title" tabindex="1" name="meta[pubs][0][d2g_pub_title]" placeholder="<?php echo esc_html__( 'title', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-2 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_pub_link" tabindex="1" name="meta[pubs][0][d2g_pub_link]" placeholder="<?php echo esc_html__( 'web link', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-2 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_pub_journal" tabindex="1" name="meta[pubs][0][d2g_pub_journal]" placeholder="<?php echo esc_html__( 'journal', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-2 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_pub_type" tabindex="1" name="meta[pubs][0][d2g_pub_type]" placeholder="<?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-2 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_pub_author" tabindex="1" name="meta[pubs][0][d2g_pub_author]" placeholder="<?php echo esc_html__( 'author', 'doctor2go-connect' ); ?>"/>
											</div>

											<div class="col-12 col-md-2 mt-2 mt-md-0">
												<input type="text" class="form-control" id="d2g_pub_date" tabindex="1" name="meta[pubs][0][d2g_pub_date]" placeholder="<?php echo esc_html__( 'publication Date', 'doctor2go-connect' ); ?>"/>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>

						<input type="hidden" name="doc_action" value="doc_update" />
						<input type="hidden" name="d2g_lang" value="<?php echo esc_html( $currLang ); ?>" />
						<input type="hidden" name="update_id" value="<?php echo esc_html( $pubProfileID ); ?>" />
						<input type="hidden" id="post_status" name="post_status" value="<?php echo esc_html( $profileStatus ); ?>" />

						<div class="row mb-4">
							<div class="col-12">
								<h3 class="simple_hide check_url mb-3">
									<?php esc_html_e( 'We are checking your data, this might take a while', 'doctor2go-connect' ); ?>
								</h3>
								<p id="submitwrap" class="d-flex flex-wrap gap-2 justify-content-center">
									<?php if ( $profileStatus == 'draft' ) { ?>
										<button class="btn btn-primary wp-block-button__link save_doctor" tabindex="6" id="save">
											<?php esc_html_e( 'save as draft', 'doctor2go-connect' ); ?>
										</button>
										<button class="btn btn-success wp-block-button__link publish_doctor" tabindex="6" id="submit">
											<?php esc_html_e( 'publish profile', 'doctor2go-connect' ); ?>
										</button>
									<?php } else { ?>
										<button class="btn btn-primary wp-block-button__link save_doctor" tabindex="6" id="save">
											<?php esc_html_e( 'save profile', 'doctor2go-connect' ); ?>
										</button>
										<button class="btn btn-warning wp-block-button__link unpublish_doctor" tabindex="6" id="unpublish">
											<?php esc_html_e( 'unpublish profile', 'doctor2go-connect' ); ?>
										</button>
									<?php } ?>
									<a target="_blank" class="btn btn-outline-primary wp-block-button__link" href="/?post_type=d2g_doctor&p=<?php echo esc_html( $pubProfileID ); ?>&preview=true">
										<?php esc_html_e( 'preview profile', 'doctor2go-connect' ); ?>
									</a>
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
	public function d2gc_doctors_listing( $atts ) {

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
		$consult_type 	= isset( $_GET['consult_type'] ) ? sanitize_text_field( wp_unslash( $_GET['consult_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- URL filtering params, view-only.

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
						include d2gc_locate_template( 'content-doctor-' . $a['template'] . '.php' );
					}
					?>
				</div>
			</div>
			<?php if ( $maxPage > 1 ) { ?>
				<div class="center load_more_btn_wrapper"><a class="more_doctors button btn btn-primary wp-block-button__link" data-page="2"><?php echo esc_html__( 'load more', 'doctor2go-connect' ); ?></a></div>
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
					'loading_checker' => get_option( 'd2gc_load_availability_info' ) == 1 ? 1 : 0
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
	public function d2gc_search_mask( $atts ) {

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
		$pageDoc  = $d2gAdmin::d2gc_page_url( $currLang, 'doctors', false );

		// Specialties.
		$argsSpecialty = array(
			'taxonomy' => 'doctor-specialty',
			'orderby'  => 'name',
			'order'    => 'ASC',
		);

		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && 'en' !== $currLang ) {
			$argsSpecialty = array(
				'taxonomy'   => 'doctor-specialty',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
			);
		}

		$specialties = get_terms( $argsSpecialty );

		$specialties_by_parent = array();
		foreach ( $specialties as $term ) {
			$specialties_by_parent[ $term->parent ][] = $term;
		}

		// Languages.
		$argsLanguage = array(
			'taxonomy' => 'doctor-language',
			'orderby'  => 'name',
			'order'    => 'ASC',
		);

		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && 'en' !== $currLang ) {
			$argsLanguage = array(
				'taxonomy'   => 'doctor-language',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
			);
		}

		$languages = get_terms( $argsLanguage );

		// Countries.
		$argsCountry = array(
			'taxonomy' => 'country-origin',
			'orderby'  => 'name',
			'order'    => 'ASC',
		);

		if ( get_option( 'd2gc_pseudo_translations' ) == 1 && 'en' !== $currLang ) {
			$argsCountry = array(
				'taxonomy'   => 'country-origin',
				'hide_empty' => true,
				'meta_key'   => 'rudr_text_' . $currLang,
				'orderby'    => 'meta_value',
				'order'      => 'ASC',
			);
		}

		$countries = get_terms( $argsCountry );

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

		// View-only GET filters (no state changes).
		$doctor_specialty = isset( $_GET['doctor-specialty'] )? sanitize_text_field( wp_unslash( $_GET['doctor-specialty'] ) ): '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter, affects query args only.
		$doctor_language = isset( $_GET['doctor-language'] )? sanitize_text_field( wp_unslash( $_GET['doctor-language'] ) ): '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$country_origin = isset( $_GET['country-origin'] )? sanitize_text_field( wp_unslash( $_GET['country-origin'] ) ): '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$consult_type = isset( $_GET['consult_type'] )? sanitize_text_field( wp_unslash( $_GET['consult_type'] ) ): '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$post_id_filter = isset( $_GET['post_id'] )? absint( wp_unslash( $_GET['post_id'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter for single post.

		// Prepare meta_query if needed.
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}

		// Email consult: written_con_price not empty.
		if ( 'email' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'written_con_price',
				'value'   => '',
				'compare' => '!=',
			);
		}

		// Video consult: d2g_availability_check = 1.
		if ( 'video' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_availability_check',
				'value'   => '1',
				'compare' => '=',
			);
		}

		// Walk-in consult: d2g_walk_in = 1.
		if ( 'walkin' === $consult_type ) {
			$args['meta_query'][] = array(
				'key'     => 'd2g_walk_in',
				'value'   => '1',
				'compare' => '=',
			);
		}

		$checker = 0;

		if ( '' !== $doctor_specialty && '0' !== $doctor_specialty ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-specialty',
				'field'    => 'term_id',
				'terms'    => array( (int) $doctor_specialty ),
			);
			++$checker;
		}

		if ( '' !== $doctor_language && '0' !== $doctor_language ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'doctor-language',
				'field'    => 'term_id',
				'terms'    => array( (int) $doctor_language ),
			);
			++$checker;
		}

		if ( '' !== $country_origin && '0' !== $country_origin ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'country-origin',
				'field'    => 'term_id',
				'terms'    => array( (int) $country_origin ),
			);
			++$checker;
		}

		if ( $checker > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}

		$post_id = isset( $_GET['post_id'] )? absint( wp_unslash( $_GET['post_id'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter for single post.

		if ( $post_id && 0 !== $post_id ) {
			$args = array(
				'post_type' => 'd2g_doctor',
				'p'         => $post_id,
			);
		}

		$doctor_query  = new WP_Query( $args );
		$doctor_query2 = new WP_Query( $args2 );
		$count         = $doctor_query->found_posts;

		ob_start();

		if ( '' !== $a['wrapper_class'] ) {
			?>
			<div class="<?php echo esc_attr( $a['wrapper_class'] ); ?>">
			<?php
		}

		// Sanitized filter params for form defaults (view-only GET filters).
		$doctor_specialty = isset( $_GET['doctor-specialty'] )? absint( wp_unslash( $_GET['doctor-specialty'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$country_origin = isset( $_GET['country-origin'] )? absint( wp_unslash( $_GET['country-origin'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$doctor_language = isset( $_GET['doctor-language'] )? absint( wp_unslash( $_GET['doctor-language'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$post_id_filter = isset( $_GET['post_id'] )? absint( wp_unslash( $_GET['post_id'] ) ): 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.
		$consult_type_raw = isset( $_GET['consult_type'] )? sanitize_text_field( wp_unslash( $_GET['consult_type'] ) ): '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View-only GET filter.

		?>

		<form method="GET" action="<?php echo esc_url( $pageDoc ); ?>">
			<h3 class="opener special">
				<?php echo esc_html__( 'Filters', 'doctor2go-connect' ); ?>
				<span class="icon-angle-down"></span>
			</h3>
			<div class="doctor_filters_outer">
				<ul id="doctor_filters" class="<?php echo esc_attr( $a['ul_class'] ); ?>">
					<li class="filter_wrap flex-fill">
						<select name="doctor-specialty" id="specialty_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all specialties', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $specialties as $specialty ) { ?>
								<option <?php selected( $doctor_specialty, $specialty->term_id ); ?> value="<?php echo esc_attr( $specialty->term_id ); ?>">
									<?php
									if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
										echo ( 'en' === $currLang )
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

					<li class="filter_wrap flex-fill">
						<select name="country-origin" id="country_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all countries', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $countries as $country ) { ?>
								<option <?php selected( $country_origin, $country->term_id ); ?> value="<?php echo esc_attr( $country->term_id ); ?>">
									<?php
									if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
										echo ( 'en' === $currLang )
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

					<li class="filter_wrap flex-fill">
						<select name="doctor-language" id="language_filter" class="doctor_filter">
							<option value="0"><?php echo esc_html__( 'all languages', 'doctor2go-connect' ); ?></option>
							<?php foreach ( $languages as $language ) { ?>
								<option <?php selected( $doctor_language, $language->term_id ); ?> value="<?php echo esc_attr( $language->term_id ); ?>">
									<?php
									if ( get_option( 'd2gc_pseudo_translations' ) == 1 ) {
										echo ( 'en' === $currLang )
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

					<li class="filter_wrap flex-fill">
						<select name="consult_type" id="consult_type" class="doctor_filter">
							<option value=""><?php echo esc_html__( 'all consult types', 'doctor2go-connect' ); ?></option>
							<option value="email" <?php selected( $consult_type_raw, 'email' ); ?>>
								<?php echo esc_html__( 'email consult', 'doctor2go-connect' ); ?>
							</option>
							<option value="video" <?php selected( $consult_type_raw, 'video' ); ?>>
								<?php echo esc_html__( 'video consult', 'doctor2go-connect' ); ?>
							</option>
							<option value="walkin" <?php selected( $consult_type_raw, 'walkin' ); ?>>
								<?php echo esc_html__( 'walkin video consult', 'doctor2go-connect' ); ?>
							</option>
						</select>
					</li>

					<li class="filter_wrap flex-fill">
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

					<?php if ( 'false' === $a['stand_alone'] ) { ?>
						<li>
							<a class="btn btn-primary" href="<?php echo esc_url( $pageDoc ); ?>">
								<?php esc_html_e( 'Reset search', 'doctor2go-connect' ); ?>
							</a>
						</li>
					<?php } ?>
				</ul>

				<p>
					<?php echo esc_html__( 'Found doctors:', 'doctor2go-connect' ); ?>
					<span id="doc_count"><?php echo esc_html( $count ); ?></span>
				</p>

				<?php if ( 'true' === $a['stand_alone'] ) { ?>
					<input id="search_submit" type="submit" class="search_submit" value="<?php echo esc_attr__( 'Search', 'doctor2go-connect' ); ?>">
					<div class="alert alert-danger" id="search_error"></div>
					<div class="loader simple_hide"></div>
				<?php } ?>

				<?php wp_nonce_field( 'doctor_filter_action', 'doctor_filter_nonce' ); ?>
			</div>
		</form>

		<?php
		if ( '' !== $a['wrapper_class'] ) {
			?>
			</div>
			<?php
		}

		wp_localize_script(
			'd2g-load-doctors',
			'myShortcodeDataFilters',
			array(
				'_wpnonce'             => wp_create_nonce( 'doc_call' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'posts_per_page'       => $a['posts_per_page'],
				'standalone_checker'   => $a['stand_alone'],
				'page_url'             => $pageDoc,
				'loading_checker'      => get_option( 'd2gc_load_availability_info' ) == 1 ? 1 : 0,
				'str_no_doctors_found' => esc_html__( 'We are sorry, but we could not find any doctors for your search criteria, please refine your search.', 'doctor2go-connect' ),
			)
		);

		wp_reset_postdata();

		$sc = ob_get_contents();
		ob_end_clean();

		return $sc;
	}


	/*
	* shortcode to show the doctor info box
	*/
	public function d2gc_single_doctor_info( $atts ) {

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
		} elseif ( get_option( 'd2gc_placeholder' ) != '' ) {
				$feat_pic = wp_get_attachment_image_src( get_option( 'd2gc_placeholder' ), 'd2g-doc-pic' )[0];
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
					d2gc_cb_d2g_info_box( 'single', 'grid', $post );
				} else {
					echo esc_html__( 'You must pass a doctor profile ID', 'doctor2go-connect' );
				}
				?>
			</div>
			<a class="button btn btn-primary wp-block-button__link" href="<?php echo esc_html( get_the_permalink() ); ?>"><?php echo esc_html__( 'view doctor', 'doctor2go-connect' ); ?></a>
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
	public function d2gc_single_doctor_locations( $atts ) {

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
						d2gc_show_doctor_locations_by_id( $a['doc_id'], false );
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
	public function d2gc_single_doctor_calendar( $atts ) {

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
						d2gc_show_booking_calendar( $post, true );
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
	public function d2gc_single_doctor_consultancy_tabs( $atts ) {

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
			<div class="type-d2g_doctor tab_wrapper">
				<div class="inner_wrapper">
					<?php
					if ( $a['doc_id'] != '' ) {
						d2gc_show_consultancy_tabs( $post, true );
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
	public function d2gc_login_form() {
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
		$defaultRedirect = $d2gAdmin::d2gc_page_url( $currLang, 'dashboard', true );

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
		$recaptcha_site_key = get_option( 'd2gc_recaptcha_site_key' );
		?>
	<div class="d2g_form_wrapper  mt-4">
		<form method="post" class="w-100 w-md-50 mx-auto border rounded-3 p-4 bg-light shadow-sm" action="<?php echo esc_url( wp_login_url( $redirect_to ) ); ?>" id="custom-loginform">
			<?php
			// Add nonce field to the form.
			wp_nonce_field( 'd2g_login_action', 'd2g_login_nonce' );
			?>

			<div class="mb-3">
				<label for="user_login" class="form-label"><?php esc_html_e( 'Email', 'doctor2go-connect' ); ?></label>
				<input type="email" name="log" id="user_login" class="form-control" required>
			</div>

			<div class="mb-3">
				<label for="user_pass" class="form-label"><?php esc_html_e( 'Password', 'doctor2go-connect' ); ?></label>
				<input type="password" name="pwd" id="user_pass" class="form-control" required>
			</div>

			<!-- reCAPTCHA Widget -->
			<?php if ( get_option( 'd2gc_recaptcha_site_key' ) ) { ?>
				<div class="mb-3">
					<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"></div>
					<div id="captcha_login"></div>
				</div>
			<?php } ?>

			<!-- altacha Widget check if shortcode is active -->
			<?php if ( shortcode_exists( 'altcha' ) ) : ?>
				<div class="mb-3">
					<?php echo do_shortcode( '[altcha]' ); ?>
				</div>
			<?php endif; ?>

			<div class="mb-4">
				<div class="form-check">
					<input type="checkbox" name="rememberme" value="forever" class="form-check-input" id="rememberme">
					<label class="form-check-label" for="rememberme">
						<?php esc_html_e( 'Remember Me', 'doctor2go-connect' ); ?>
					</label>
				</div>
			</div>

			<button type="submit" class="btn btn-primary w-100 mb-3"><?php echo esc_attr__( 'Login', 'doctor2go-connect' ); ?></button>
		</form>

		<div class="text-center">
			<?php $pageData = $d2gAdmin::d2gc_page_url( $currLang, 'lost_password', true ); ?>
			<a href="<?php echo esc_url( $pageData['url'] ); ?>" class="btn btn-link p-0"><?php esc_html_e( 'Lost password?', 'doctor2go-connect' ); ?></a>

			<?php $pageData = $d2gAdmin::d2gc_page_url( $currLang, 'patient_registration', true ); ?>
			<a href="<?php echo esc_url( $pageData['url'] ); ?>" class="btn btn-link p-0"><?php esc_html_e( 'Register as patient', 'doctor2go-connect' ); ?></a>
		</div>
	</div>


		<?php
		// Include the Google reCAPTCHA script (your existing logic).
		

		return ob_get_clean();
	}



	//
	// custom lost password form
	public function d2gc_lost_password_form() {
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
		$locale 		= get_locale();  // e.g., 'ro_RO'
		$locale_parts 	= explode( '_', get_locale() );
		$currLang     	= sanitize_key( $locale_parts[0] ?? 'en' );
		$action_url 	= add_query_arg( 'wp_lang', $locale, site_url( 'wp-login.php?action=lostpassword', 'login_post' ) );

		$d2gAdmin = new D2G_doc_user_profile();
		$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'password_reset_sent', true );
		$redirect = isset( $pageData['url'] ) ? esc_url( $pageData['url'] ) : '';

		?>

		<div class="d2g_form_wrapper  py-4">
			<form id="lostpasswordform" action="<?php echo esc_url( $action_url ); ?>" method="post" class="w-100 w-md-50 mx-auto border rounded-3 p-4 bg-light shadow-sm">
				<?php wp_nonce_field( 'd2g_lost_password_action', 'd2g_lost_password_nonce' ); ?>

				<div class="mb-3">
					<label for="user_login" class="form-label">
						<?php esc_html_e( 'E-mail', 'doctor2go-connect' ); ?>
					</label>
					<input type="text" name="user_login" id="user_login" class="form-control" required>
				</div>

				<div class="d-grid mb-3">
					<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Get New Password', 'doctor2go-connect' ); ?>" class="btn btn-primary">
				</div>

				<input type="hidden" name="redirect_to" value="<?php echo esc_html( $redirect ); ?>">
			</form>
		</div>

		<?php
		return ob_get_clean();
	}



	//
	// custom reset password form logic
	public function d2gc_reset_password_form() {

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
			$pageData     = $d2gAdmin::d2gc_page_url( $currLang, 'login', true );

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
		<div class="d2g_form_wrapper  py-4">
			<form id="resetpasswordform" method="post" class="w-100 w-md-50 mx-auto border rounded-3 p-4 bg-light shadow-sm">
				<?php
				// Nonce field.
				wp_nonce_field( 'd2g_reset_password_action', 'd2g_reset_password_nonce' );
				?>

				<!-- Keep these so POST has the same values; avoids relying on GET on submit -->
				<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
				<input type="hidden" name="key" value="<?php echo esc_attr( $reset_key ); ?>">

				<div class="mb-3">
					<label for="new_password" class="form-label">
						<?php echo esc_html__( 'New Password', 'doctor2go-connect' ); ?>
					</label>
					<input type="password" name="new_password" id="new_password" class="form-control" required>
				</div>

				<div class="mb-3">
					<label for="confirm_password" class="form-label">
						<?php echo esc_html__( 'Confirm New Password', 'doctor2go-connect' ); ?>
					</label>
					<input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
				</div>

				<div class="d-grid">
					<input type="submit" value="<?php echo esc_attr__( 'Reset Password', 'doctor2go-connect' ); ?>" class="btn btn-primary">
				</div>
			</form>
		</div>

		<?php
		return ob_get_clean();
	}


	//
	// custom registration form
	function d2gc_registration_form() {

		if ( is_user_logged_in() ) {
			return '<p>You are already registered and logged in.</p>';
		}

		$recaptcha_site_key = get_option( 'd2gc_recaptcha_site_key' );
		$secret_key         = get_option( 'd2gc_recaptcha_secret_key' );
		$timezones          = d2gc_timezones();

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['custom_registration'] ) ) {

			$errors = array();

			if ( ! isset( $_POST['d2g_reg_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['d2g_reg_nonce'] ) ), 'd2g_registration_action' ) ) {
				$errors[] = __( 'Security check failed. Please refresh the page.', 'doctor2go-connect' );
			} else {

				$email              = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
				$username           = ( ! empty( $email ) && is_email( $email ) ) ? explode( '@', $email )[0] . time() : '';
				$password           = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
				$confirm_password   = isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : '';
				$recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

				if ( get_option( 'd2gc_recaptcha_site_key' ) ) {
					$recaptcha_verify = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'body' => array(
								'secret'   => $secret_key,
								'response' => $recaptcha_response,
								'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
							),
						)
					);

					$recaptcha_result = json_decode( wp_remote_retrieve_body( $recaptcha_verify ) );
					if ( empty( $recaptcha_result ) || empty( $recaptcha_result->success ) ) {
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
						'first_name' => isset( $_POST['meta']['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['meta']['first_name'] ) ) : '',
						'last_name'  => isset( $_POST['meta']['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['meta']['last_name'] ) ) : '',
						'role'       => 'patient',
					);

					$user_id = wp_insert_user( $user_input );

					if ( ! is_wp_error( $user_id ) ) {

						$meta = isset( $_POST['meta'] ) ? wp_unslash( $_POST['meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						if ( is_array( $meta ) ) {
							foreach ( $meta as $key => $value ) {
								update_user_meta( $user_id, sanitize_key( $key ), sanitize_text_field( $value ) );
							}
						}

						d2gc_user_email(
							'registration',
							$email,
							( isset( $_POST['meta']['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['meta']['first_name'] ) ) : '' ) . ' ' . ( isset( $_POST['meta']['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['meta']['last_name'] ) ) : '' ),
							get_option( 'd2gc_sender_address' )
						);

						$currLang = explode( '_', get_locale() )[0];
						$d2gAdmin = new D2G_doc_user_profile();
						$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'patient_dashboard', true );

						d2gc_programmatic_login( $username );

						$redirect_url = add_query_arg( 'signup', 'completed', $pageData['url'] );

						if (
							isset( $_GET['redirect_to'], $_GET['redirect_to_nonce'] ) &&
							wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['redirect_to_nonce'] ) ), 'd2g_redirect_to' )
						) {
							$raw_redirect = wp_unslash( $_GET['redirect_to'] );
							$safe_redirect = wp_validate_redirect( $raw_redirect, $redirect_url );
							$safe_redirect = add_query_arg( 'signup', 'completed', $safe_redirect );
							wp_safe_redirect( $safe_redirect );
							exit;
						}

						wp_safe_redirect( $redirect_url );
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

		$action_url = add_query_arg( 'create_account', '1', '' );

		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect_to = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
			if ( $redirect_to ) {
				$action_url = add_query_arg(
					array(
						'redirect_to'       => $redirect_to,
						'redirect_to_nonce' => wp_create_nonce( 'd2g_redirect_to' ),
					),
					$action_url
				);
			}
		}

		ob_start();
		?>
		<div class="d2g_form_wrapper py-5">
			<form id="custom-registration-form" method="post" action="<?php echo esc_url( $action_url ); ?>" class="w-100 w-md-75 w-lg-50 mx-auto border rounded-3 p-4 bg-light shadow-sm needs-validation" novalidate>
				<?php wp_nonce_field( 'd2g_registration_action', 'd2g_reg_nonce' ); ?>

				<div id="error" class="alert alert-danger d-none"></div>

				<div class="mb-3">
					<label for="first_name" class="form-label"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?>*</label>
					<input class="form-control myrequired" type="text" name="meta[first_name]" id="first_name" required>
				</div>

				<div class="mb-3">
					<label for="last_name" class="form-label"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?>*</label>
					<input class="form-control myrequired" type="text" name="meta[last_name]" id="last_name" required>
				</div>

				<div class="mb-3">
					<label for="patient_email" class="form-label"><?php echo esc_html__( 'Email', 'doctor2go-connect' ); ?>*</label>
					<input class="form-control myrequired" type="email" name="email" id="patient_email" required>
				</div>

				<div class="mb-3">
					<label for="p_tel" class="form-label"><?php echo esc_html__( 'Phone', 'doctor2go-connect' ); ?>*</label>
					<input class="form-control myrequired" type="text" name="meta[p_tel]" id="p_tel" required>
				</div>

				<div class="mb-3">
					<label class="form-label" for="p_bday"><?php echo esc_html__( 'Date of Birth', 'doctor2go-connect' ); ?></label>
					<input class="form-control" type="date" name="p_bday" id="p_bday">
				</div>

				<div class="mb-3">
					<label class="form-label" for="p_gender"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
					<select name="meta[p_gender]" id="p_gender" class="form-select">
						<option value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
						<option value="male"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
						<option value="female"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
						<option value="other"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
					</select>
				</div>

				<div class="mb-3">
					<label for="p_timezone" class="form-label">
						<?php echo esc_html__( 'Timezone (Your browser detects time zones automatically, but you can set a different one here if needed.)', 'doctor2go-connect' ); ?>
					</label>
					<select name="meta[p_timezone]" id="p_timezone" class="form-select">
						<option value="0"><?php echo esc_html__( 'Make a selection', 'doctor2go-connect' ); ?></option>
						<?php foreach ( $timezones as $group => $zones ) { ?>
							<optgroup label="<?php echo esc_html( $group ); ?>">
								<?php foreach ( $zones as $key => $name ) { ?>
									<option value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</div>

				<div class="mb-3">
					<label for="pass1" class="form-label">
						<?php echo esc_html__( 'Password (your password needs to be minimum 8 characters long and it must contain minimum one special character.)', 'doctor2go-connect' ); ?>*
					</label>
					<input class="form-control myrequired" type="password" name="password" id="pass1" required>
				</div>

				<div id="result" class="info alert alert-danger d-none" style="width:100%!important;"></div>

				<div class="mb-3">
					<label for="pass2" class="form-label"><?php echo esc_html__( 'Confirm Password', 'doctor2go-connect' ); ?>*</label>
					<input class="form-control myrequired" type="password" name="confirm_password" id="pass2" required>
				</div>

				<?php if ( get_option( 'd2gc_recaptcha_site_key' ) ) { ?>
					<div class="mb-3">
						<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"></div>
						<div id="captcha_registration" class="form-text text-danger"></div>
					</div>
				<?php } ?>

				<?php if ( shortcode_exists( 'altcha' ) ) : ?>
					<div class="mb-3">
						<?php echo do_shortcode( '[altcha]' ); ?>
					</div>
				<?php endif; ?>

				<div class="mb-3">
					<?php d2gc_confirmation_checkboxes(); ?>
				</div>

				<input type="hidden" name="custom_registration" value="1">

				<div class="d-grid">
					<input id="submit_registration" type="submit"
						value="<?php echo esc_html__( 'Register', 'doctor2go-connect' ); ?>"
						class="btn btn-primary">
				</div>
			</form>
		</div>
		<?php

		wp_localize_script(
			'd2g-public',
			'd2gRegistrationVars',
			array(
				'msg_required'      => esc_html__( 'Please fill in all marked fields. ', 'doctor2go-connect' ),
				'msg_pass_short'    => esc_html__( 'Your password is too short. ', 'doctor2go-connect' ),
				'msg_pass_match'    => esc_html__( 'Your passwords do not match. ', 'doctor2go-connect' ),
				'msg_email_invalid' => esc_html__( 'You have entered an invalid e-mail. ', 'doctor2go-connect' ),
				'msg_privacy'       => esc_html__( 'You must accept the privacy rules. ', 'doctor2go-connect' ),
				'msg_terms'         => esc_html__( 'You must accept the terms and conditions. ', 'doctor2go-connect' ),
				'msg_disclaimer'    => esc_html__( 'You must accept the disclaimer. ', 'doctor2go-connect' ),
			)
		);

		return ob_get_clean();
	}



	
	// shortcode patient dashbaord
	public function d2gc_patient_dashbaord() {
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pages    = array(
			'account_settings' => 'account.jpg',
			'appointments'     => 'appointments.jpg',
			'liked_doctors'    => 'heart.jpg',
			'secure_patient_portal'   => 'patient.jpg',

		);

		// patient / user data
		$currUser  = wp_get_current_user();
		$user_meta = get_user_meta( $currUser->data->ID );
		$tokensAssArray  = unserialize( $user_meta['tokens'][0] );
		$tokensSimpleArr = array();
		foreach ( $tokensAssArray as $token ) {
			$tokensSimpleArr[] = $token;
		}

		ob_start();

		?>
		<div class="alignwide p_dashboard mt-5">
			<div class="row">
				<?php
				foreach ( $pages as $page => $image ) {
					$pageData = $d2gAdmin::d2gc_page_url( $currLang, $page, true );
					?>
					<div class="col-sm-3">
						<div class="card p-5 text-center h-100">
							<a href="<?php echo esc_url( $pageData['url'] ); ?>">
								<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/' . $image ); ?>">
								<h3><?php echo esc_html( $pageData['title'] ); ?></h3>
							</a>
						</div>
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


	// shortcode patient menu
	public function d2gc_patient_menu() {
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		$pages    = array(

			'appointments'          => 'appointments-small.jpg',
			'liked_doctors'         => 'heart-small.jpg',
			'secure_patient_portal' => 'patient-small.jpg',
			'account_settings'      => 'account-small.jpg',
		);

		ob_start();

		?>
		<ul class="user_menu row justify-content-between w-100 mt-5 mb-5">
			<?php
			foreach ( $pages as $page => $image ) {
				$pageData = $d2gAdmin::d2gc_page_url( $currLang, $page, true );
				?>
				<li class="col-sm-3">
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
	public function d2gc_patient_appointments() {
		// initialize class to get dynamic links
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		// get client info from WP DB
		$currUser        = wp_get_current_user();
		$user_meta       = get_user_meta( $currUser->data->ID );
		$timezone        = $user_meta['p_timezone'][0] ?: d2gc_get_user_timezone();
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
						<h3 class="alert alert-danger"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
						<?php
						$sc = ob_get_contents();
						ob_end_clean();
						return $sc;
					} elseif ( is_array( $appointments ) ) {
						if ( count( $appointments ) < 1 ) {
							?>
							<h3 class="alert alert-danger"><?php echo esc_html__( 'You don\’t have any upcoming consultations. Book a new appointment when you\'re ready.', 'doctor2go-connect' ); ?></h3>
						<?php } else { ?>
							<?php
							foreach ( $appointments as $appointment ) {
								// get doc info for appointment
								$docObj       = $this->d2gc_get_doctor_by_wcc_id( $appointment->user_id )[0];
								$doc_email    = get_post_meta( $docObj->ID, 'd2g_main_email', true );
								$orgKey       = get_post_meta( $docObj->ID, 'organisation_key', true );
								$client_token = $tokensAssArray[ $orgKey ];

								if ( isset( $appointment->answer_set_id ) ) {
									// url to load in iframe
									$questionnaireURLSimple = get_option( 'd2gc_waiting_room_url' ) . 'answer_set/' . $appointment->answer_set_id . '?client_auth=' . $client_token;
								}

								$d2gc_single_appointment = d2gc_single_appointment($appointment, $docObj, $client_token, $timezone, $currLang, $d2gAdmin, true, $doc_email);

								$structuredAppointments += $d2gc_single_appointment;
							}
							// display the appointments with sorting on date
							?>
							<?php
							ksort( $structuredAppointments );
							foreach ( $structuredAppointments as $appointment ) {
								?>
								<?php echo wp_kses_post( $appointment ); ?>
							<?php } ?>
						<?php } ?>
					<?php } else { ?>
						<h3 class="alert alert-danger"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
					<?php } ?>
				</div>
			</div>
		<?php } 
		d2gc_cancelation_request_form( $currUser, $user_meta );
		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}


	//
	// shortcode patient appointments
	public function d2gc_appointment_confirmation() {
		// initialize class to get dynamic links
		$d2gAdmin = new D2G_doc_user_profile();
		$currLang = explode( '_', get_locale() )[0];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$client_token      = isset( $_GET['client_token'] ) ? sanitize_text_field( wp_unslash( $_GET['client_token'] ) ) : '';
		$tokensSimpleArr[] = $client_token;
		$timezone          = d2gc_get_user_timezone() ?: 'Europe/Amsterdam';

		// get clinet appointments from WCC
		$appointments           = json_decode( $this->get_patient_appointments_simple( $tokensSimpleArr ) );
		$structuredAppointments = array();

		ob_start();
		?>
		
		<?php if ( !$client_token ) { ?>
			<p class="alert alert-danger mb-5 mt-5"><?php echo esc_html__( 'Something went wrong, as it seems you do nbot have a valid client, therefor we can not show you your appointment. Please contact the website administrator.', 'doctor2go-connect' ); ?></p>
		<?php } else { ?>
			
				<div class="list_app left single_app_wrapper">
					<?php
					if ( $appointments == null ) {
						?>
						<h3 class="alert alert-danger"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
						<?php
						$sc = ob_get_contents();
						ob_end_clean();
						return $sc;
					} elseif ( is_array( $appointments ) ) {
						if ( count( $appointments ) < 1 ) {
							?>
							<h3 class="alert alert-danger"><?php echo esc_html__( 'You don\’t have any upcoming consultations. Book a new appointment when you\'re ready.', 'doctor2go-connect' ); ?></h3>
						<?php } else { ?>
							<?php foreach ( $appointments as $appointment ) {
								$docObj = $this->d2gc_get_doctor_by_wcc_id( $appointment->user_id )[0];
								$doc_email    = get_post_meta( $docObj->ID, 'd2g_main_email', true );
								$d2gc_single_appointment = d2gc_single_appointment($appointment, $docObj, $client_token, $timezone, $currLang, $d2gAdmin, false, $doc_email);
								$structuredAppointments += $d2gc_single_appointment;

								if ( isset( $appointment->answer_set_id ) ) {
									// url to load in iframe
									$questionnaireURLSimple = get_option( 'd2gc_waiting_room_url' ) . 'answer_set/' . $appointment->answer_set_id . '?client_auth=' . $client_token;
								}

								$app_id = isset( $_GET['app'] ) ? sanitize_text_field( wp_unslash( $_GET['app'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only query param, no state change.
								
								if ( $appointment->_id == $app_id ) {
									if ( $questionnaireURLSimple != '' ) { ?>
										<div class="alert d-flex alert-warning mb-5 mt-5 align-items-center justify-content-between">
											<strong><?php echo esc_html__( 'For this appointment you are requiered to fill in an intake questionnaire.', 'doctor2go-connect' ); ?></strong>
											<a class="scroll_to btn button btn-primary" href="#questionnaire"><?php echo esc_html__( 'Go to questionnaire', 'doctor2go-connect' ); ?></a>
										</div>
									<?php } ?>
									
									<?php echo wp_kses_post( $structuredAppointments[ $appointment->date ] ); ?>
										
									<?php if ( $questionnaireURLSimple != '' ) { ?>
										<iframe id="questionnaire" src="<?php echo esc_url( $questionnaireURLSimple ); ?>" style="width:100%; border:none; height:2500px"></iframe>
									<?php } ?>
								<?php } ?>
								
							<?php } ?>  
						<?php } ?>
					<?php } else { ?>
						<h3 class="alert alert-danger"><?php echo esc_html__( 'Something went wrong while retrieving your appointments. Please try again later, or refresh the page.', 'doctor2go-connect' ); ?></h3>
					<?php } ?>
				</div>
		<?php } ?>
		<?php
		d2gc_cancelation_request_form('', '');
		$sc = ob_get_contents();
		ob_end_clean();
		return $sc;
	}

	//
	// shortcode account settings
	public function d2gc_account_settings() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id   = get_current_user_id();
		$timezones = d2gc_timezones();
		$errors    = array();

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === wp_unslash( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['custom_registration'] )) {
			if ( ! isset( $_POST['d2g_account_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['d2g_account_nonce'] ) ), 'd2g_account_action') ) {
				$errors[] = __( 'Security check failed. Please refresh the page and try again.', 'doctor2go-connect' );
			}

			$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$password         = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
			$confirm_password = isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : '';

			if ( empty( $email ) ) {
				$errors[] = __( 'All fields are required.', 'doctor2go-connect' );
			} elseif ( ! is_email( $email ) ) {
				$errors[] = __( 'Please provide a valid email address.', 'doctor2go-connect' );
			}

			if ( ! empty( $password ) || ! empty( $confirm_password ) ) {
				if ( empty( $password ) || empty( $confirm_password ) ) {
					$errors[] = __( 'Please fill in both password fields.', 'doctor2go-connect' );
				} elseif ( $password !== $confirm_password ) {
					$errors[] = __( 'Passwords do not match.', 'doctor2go-connect' );
				}
			}

			if ( empty( $errors ) && isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) ) {
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

			if ( empty( $errors ) && ! empty( $password ) ) {
				wp_set_password( $password, $user_id );
			}
		}

		$current_user = wp_get_current_user();
		$user_meta    = get_user_meta( $user_id );

		ob_start();
		?>
		<div class="d2g_form_wrapper">
			<?php
			if ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					echo '<p class="alert alert-danger">' . esc_html( $error ) . '</p>';
				}
			} elseif (
				isset( $_SERVER['REQUEST_METHOD'] ) &&
				'POST' === wp_unslash( $_SERVER['REQUEST_METHOD'] ) &&
				isset( $_POST['custom_registration'] )
			) {
				echo '<p class="success">' . esc_html__( 'Update was successful.', 'doctor2go-connect' ) . '</p>';
			}
			?>

			<form id="custom-registration-form" method="post">
				<?php wp_nonce_field( 'd2g_account_action', 'd2g_account_nonce' ); ?>

				<div class="mb-3">
					<label for="first_name" class="form-label"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[first_name]" id="first_name" class="form-control" required value="<?php echo isset( $user_meta['first_name'][0] ) ? esc_attr( $user_meta['first_name'][0] ) : ''; ?>">
				</div>

				<div class="mb-3">
					<label for="last_name" class="form-label"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[last_name]" id="last_name" class="form-control" required value="<?php echo isset( $user_meta['last_name'][0] ) ? esc_attr( $user_meta['last_name'][0] ) : ''; ?>">
				</div>

				<div class="mb-3 alert alert-warning">
					<label for="email" class="form-label"><?php echo esc_html__( 'Email (can not be changed)', 'doctor2go-connect' ); ?></label>
					<input type="email" readonly class="form-control-plaintext" name="email" id="email" required value="<?php echo esc_attr( $current_user->user_email ); ?>">
				</div>

				<div class="mb-3">
					<label for="p_tel" class="form-label"><?php echo esc_html__( 'Phone', 'doctor2go-connect' ); ?></label>
					<input type="text" name="meta[p_tel]" id="p_tel" class="form-control" required value="<?php echo isset( $user_meta['p_tel'][0] ) ? esc_attr( $user_meta['p_tel'][0] ) : ''; ?>">
				</div>

				<div class="mb-3">
					<label class="form-label" for="p_bday"><?php echo esc_html__( 'Date of Birth', 'doctor2go-connect' ); ?></label>
					<input class="form-control" type="date" name="meta[p_bday]" id="p_bday" value="<?php echo isset( $user_meta['p_bday'][0] ) ? esc_attr( $user_meta['p_bday'][0] ) : ''; ?>">
				</div>

				<div class="mb-3">
					<label class="form-label" for="p_gender"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
					<select name="meta[p_gender]" id="p_gender" class="form-select">
						<option <?php selected( isset( $user_meta['p_gender'][0] ) ? $user_meta['p_gender'][0] : '', '0' ); ?> value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
						<option <?php selected( isset( $user_meta['p_gender'][0] ) ? $user_meta['p_gender'][0] : '', 'male' ); ?> value="male"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
						<option <?php selected( isset( $user_meta['p_gender'][0] ) ? $user_meta['p_gender'][0] : '', 'female' ); ?> value="female"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
						<option <?php selected( isset( $user_meta['p_gender'][0] ) ? $user_meta['p_gender'][0] : '', 'other' ); ?> value="other"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
					</select>
				</div>

				<div class="mb-3" id="time_zone_wrapper">
					<label for="p_timezone" class="form-label"><?php echo esc_html__( 'Timezone', 'doctor2go-connect' ); ?></label>
					<select name="meta[p_timezone]" class="form-select" id="p_timezone">
						<option value="0"><?php echo esc_html__( 'make a selection', 'doctor2go-connect' ); ?></option>
						<?php foreach ( $timezones as $group => $zones ) { ?>
							<optgroup label="<?php echo esc_attr( $group ); ?>">
								<?php foreach ( $zones as $key => $name ) { ?>
									<option <?php selected( isset( $user_meta['p_timezone'][0] ) ? $user_meta['p_timezone'][0] : '', $key ); ?> value="<?php echo esc_attr( $key ); ?>">
										<?php echo esc_html( $name ); ?>
									</option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</div>

				<p class="mb-3 text-warning"><?php echo esc_html__( 'Only fill in the password fields, if you want to change your password.', 'doctor2go-connect' ); ?></p>

				<div class="mb-3">
					<label for="password" class="form-label"><?php echo esc_html__( 'Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="password" id="password" class="form-control">
				</div>

				<div class="mb-3">
					<label for="confirm_password" class="form-label"><?php echo esc_html__( 'Confirm Password', 'doctor2go-connect' ); ?></label>
					<input type="password" name="confirm_password" id="confirm_password" class="form-control">
				</div>

				<div class="mb-3">
					<input type="hidden" name="custom_registration" value="1">
					<input type="submit" class="btn btn-primary" value="<?php echo esc_attr__( 'save', 'doctor2go-connect' ); ?>">
				</div>
			</form>
		</div>

		<?php if ( '1' === get_option( 'd2gc_activate_2fa_link' ) ) { ?>
			<div class="btn_wrapper">
				<a class="btn btn-outline-primary" href="/wp/wp-login.php?itsec_after_interstitial=2fa-on-board"><?php esc_html_e( 'configure 2FA', 'doctor2go-connect' ); ?></a>
			</div>
		<?php } ?>

		<?php
		return ob_get_clean();
	}

	// shortcode to show liked posts
	public function d2gc_liked_posts() {
		global $cssClass;
		$cssClass    = 'col-sm-6 col-md-4';
		$liked_posts = d2gc_get_liked_posts();

		if ( empty( $liked_posts ) ) {
			return '<p class="alert alert-danger">' . esc_html__( 'No liked doctors yet.', 'doctor2go-connect' ) . '</p>';
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
			echo '<div id="doctor_wrapper" class="outer_wrapper"><div class="d2g-doctor-grid row">'; // Wrapper div for styling
			while ( $query->have_posts() ) {
				$query->the_post();
				include d2gc_locate_template( 'content-doctor-grid.php' );
			}
			echo '</div></div>';
		} else {
			echo '<p class="alert alert-danger">' . esc_html__( 'No liked doctors yet.', 'doctor2go-connect' ) . '</p>';
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


	public function d2gc_patient_portal() {
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
		$pageData = $d2gAdmin::d2gc_page_url( $currLang, 'secure_patient_portal', true );

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
						<h2><?php echo esc_html__( 'Patient portal:', 'doctor2go-connect' ) . ' ' . esc_html( $title );?></h2>
						<p><?php echo esc_html__( "This secure portal lets you send and receive messages from your doctor and access any documents they've shared with you.", 'doctor2go-connect' );?></p>
						<iframe 
							id="patient_portal" 
							style="width:100%; border:none; height:1200px; overflow-y:scroll;" 
							src="<?php echo esc_url( $iframe_url ); ?>">
						</iframe>
					<?php } else { ?>
						<h2 class="alert alert-danger">
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

					if ( $query->have_posts() ) {
						?>
						<ul class="list-group mb-5  doctors_list"  id="doctors_list">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$orgKey = get_post_meta( get_the_ID(), 'organisation_key', true );
							$title  = get_the_title();
							// doctor image
							$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' )[0];
							if ( $feat_pic == '' ) {
								if ( get_option( 'd2gc_placeholder' ) != '' ) {
									$feat_pic = wp_get_attachment_image_src( get_option( 'd2gc_placeholder' ), 'thumbnail' )[0];
								} else {
									$feat_pic = plugin_dir_url( __FILE__ ) . 'images/doctor-placeholder.jpg';

								}
							}
							?>
							<li class="list-group-item text-center p-0">
								<a class="p-3 doc_portal_link d-block" href="<?php echo esc_url( $pageData['url'] ); ?>?url=<?php echo urlencode( get_option( 'd2gc_waiting_room_url' ) . 'portal/' . $tokensAssArray[ $orgKey ] . '?skip_cookie_wall=true&locale=' . $currLang ); ?>&title=<?php echo esc_html( $title ); ?>">
									<div class="feat_pic"><img class="doc_portal_image rounded-circle" src="<?php echo esc_url( $feat_pic ); ?>"></div>
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
					} else {
						echo '<p class="alert alert-danger">No doctors found for the selected organisation.</p>';
					} ?>
				</div>
			</div>
		</div>
	<?php }
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
		$superKey = get_option( 'd2gc_wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $tokens[0] . '_' . $superKey );

		$response = wp_remote_request(
			get_option( 'd2gc_api_url_short' ) . 'doclisting/appointments/client',
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
		$superKey = get_option( 'd2gc_wcc_token' );
		$myHash   = hash( 'sha256', $unixTime . '_' . $tokens[0] . '_' . $superKey );

		$payload = array(
			'time'   => (string) $unixTime,
			'token'  => $tokens[0],
			'hash'   => $myHash,
			'type'   => 'client',
			'tokens' => $tokens,
		);

		$response = wp_remote_request(
			get_option( 'd2gc_api_url_short' ) . 'doclisting/client',
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
	private function d2gc_get_doctor_by_wcc_id( $wcc_user_id ) {
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
