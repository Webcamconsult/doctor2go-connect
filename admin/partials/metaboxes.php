<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The template for displaying the doctor meta boxes in the admin.
 *
 * @link       https://www.webcamconsult.com
 * @since      1.0.0
 *
 * @package    doctor2go-connect
 * @subpackage doctor2go-connect/admin/partials
 */
function d2gc_meta_box_personal_cb( $post ) {
	$values = get_post_meta( $post->ID );

	$personalFields = array(
		'd2g_first_name'   => esc_html__( 'First name', 'doctor2go-connect' ),
		'd2g_last_name'    => esc_html__( 'Last name', 'doctor2go-connect' ),
		'd2g_emp_title'    => esc_html__( 'Title', 'doctor2go-connect' ),
		'd2g_address'      => esc_html__( 'Address', 'doctor2go-connect' ),
		'd2g_zip'          => esc_html__( 'Zip code', 'doctor2go-connect' ),
		'd2g_city'         => esc_html__( 'city', 'doctor2go-connect' ),
		'tel'              => esc_html__( 'Phone', 'doctor2go-connect' ),
		'd2g_mobile'       => esc_html__( 'Mobile', 'doctor2go-connect' ),
		'd2g_main_email'   => esc_html__( 'E-mail', 'doctor2go-connect' ),
		'd2g_organisation' => esc_html__( 'Organisation', 'doctor2go-connect' ),
		'reg_nr'           => esc_html__( 'Registration number', 'doctor2go-connect' ),
		'reg_country'      => esc_html__( 'Country of registration', 'doctor2go-connect' )

	);
	$currencies = array( 'EUR', 'USD', 'GBP', 'ALL', 'MXN', 'AUD', 'INR', 'AZN', 'BYN', 'BGN', 'HRK', 'CZK', 'DKK', 'GEL', 'HUF', 'ISK', 'CHF', 'MKD', 'MDL', 'NOK', 'PLN', 'RON', 'RUB', 'RSD', 'SEK', 'CHF', 'TRY', 'UAH', 'CAD', 'NZD', 'BRL', 'ZAR' );
	$types	  = array( 'short' => 'Short generic Email Advice', 'derma_email_advice' => 'Dermatology Email Advice' );

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	?>
	<div class="row">
		<?php
		foreach ( $personalFields as $key => $name ) {
			?>
			<div class="col-sm-4">
				<div>
					<label for="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $name ); ?></label><br>
					<input type="text" name="<?php echo esc_html( $key ); ?>" id="<?php echo esc_html( $key ); ?>" value="<?php echo ( isset( $values[ $key ][0] ) ? esc_html( $values[ $key ][0] ) : '' ); ?>"/>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<div class="row">
		<div class="col-sm-4 no-flex">
			<label class="small"><?php echo esc_html__( 'Walk-in price', 'doctor2go-connect' ); ?>*</label>
			<input class="required" type="text" class="" id="walk_in_price" value="<?php echo esc_html( $values['walk_in_price'][0] ); ?>" tabindex="1" size="40" name="meta[walk_in_price]"  placeholder="<?php echo esc_html__( 'Walk-In price', 'doctor2go-connect' ); ?>*"/> 
		</div>
		<div class="col-sm-4 no-flex">
			<label class="small"><?php echo esc_html__( 'Walk-in currency', 'doctor2go-connect' ); ?>*</label>
			<select class="form-control" name="meta[walk_in_currency]" id="avg_price_currency">
				<?php foreach ( $currencies as $currency ) { ?>
					<option <?php echo ( $currency == $values['walk_in_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>"><?php echo esc_html( $currency ); ?></option>    
				<?php } ?>
			</select>
		</div>
		<div class="col-sm-4">
		   
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4 no-flex">
			<label class="small"><?php echo esc_html__( 'E-mail consult price', 'doctor2go-connect' ); ?>*</label>
			<input class="required" type="text" class="" id="written_con_price" value="<?php echo esc_html( $values['written_con_price'][0] ); ?>" tabindex="1" size="40" name="meta[written_con_price]"  placeholder="<?php echo esc_html__( 'E-mail consult price', 'doctor2go-connect' ); ?>*"/> 
		</div>
		<div class="col-sm-4 no-flex">
			<label class="small"><?php echo esc_html__( 'E-mail consult currency', 'doctor2go-connect' ); ?>*</label>
			<select class="form-control" name="meta[written_con_currency]" id="written_con_currency">
				<?php foreach ( $currencies as $currency ) { ?>
					<option <?php echo ( $currency == $values['written_con_currency'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $currency ); ?>"><?php echo esc_html( $currency ); ?></option>    
				<?php } ?>
			</select>
		</div>
		<div class="col-sm-4 no-flex">
			<label class="small"><?php echo esc_html__( 'E-mail consult questionnaire', 'doctor2go-connect' ); ?>*</label>
			<select class="form-control" name="meta[written_con_type]" id="written_con_type">
				<?php foreach ( $types as $type => $label ) { ?>
					<option <?php echo ( $type == $values['written_con_type'][0] ) ? 'selected' : ''; ?> value="<?php echo esc_html( $type ); ?>"><?php echo esc_html( $label ); ?></option>    
				<?php } ?>
			</select>
		</div>
	</div>
	<h3><?php echo esc_html__( 'Holiday settings', 'doctor2go-connect' ); ?></h3>
	<div class="row holiday">
		<div class="col-sm-4">
			<label class="small">Start date</label>
			<input type="date" name="meta[start_holiday]" value="">
		</div>
		<div class="col-sm-4">
			<label class="small">End date</label>
			<input type="date" name="meta[end_holiday]" value="">
		</div>
		<div class="col-sm-4">
		   
		</div>
	</div>
	<?php
}


function d2gc_meta_box_work_cb( $post ) {
	$doctor_meta = get_post_meta( $post->ID );

	if ( isset( $doctor_meta['exps'] ) ) {
		$doctor_meta['exps'] = unserialize( $doctor_meta['exps'][0] );
	}

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	?>
	<div class="form-table exp_wrapper">
		<?php $counter = 0; ?>
		<div class="row exp_edu">
			<div class="col-sm-3">
				<div class="row">
					<div class="col-sm-6">
						<strong><?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?></strong>
					</div>
					<div class="col-sm-6">
						<strong><?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?></strong>
					</div>
				</div> 
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
	<div class="btn_wrapper mb-l"><a class="button button-primary button-large add_exp" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#">add an extra working experience</a></div>
	
	<?php
}

function d2gc_meta_box_education_cb( $post ) {
	$doctor_meta = get_post_meta( $post->ID );

	if ( isset( $doctor_meta['edus'] ) ) {
		$doctor_meta['edus'] = unserialize( $doctor_meta['edus'][0] );
	}

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	?>
	<div class="form-table edu_wrapper">
		<?php $counter = 0; ?>
		<div class="row exp_edu">
			<div class="col-sm-3">
				<div class="row">
					<div class="col-sm-6">
						<strong><?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?></strong>
					</div>
					<div class="col-sm-6">
						<strong><?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?></strong>
					</div>
				</div> 
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
					<div class="row">
						<div class="col-sm-6">
							<input type="text" class="" id="d2g_exp_edu_date" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_start_date]" placeholder="<?php echo esc_html__( 'start date', 'doctor2go-connect' ); ?>"/>
						</div>
						<div class="col-sm-6">
							<input type="text" class="" id="d2g_exp_edu_study" tabindex="1" size="40" name="meta[edus][0][d2g_exp_edu_end_date]" placeholder="<?php echo esc_html__( 'end date', 'doctor2go-connect' ); ?>"/>
						</div>
					</div>
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
	<div class="btn_wrapper mb-l"><a class="button button-primary button-large add_edu" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#">add an extra education</a></div>
	
	<?php
}

function d2gc_meta_box_publications_cb( $post ) {
	$doctor_meta = get_post_meta( $post->ID );

	if ( isset( $doctor_meta['pubs'] ) ) {
		$doctor_meta['pubs'] = unserialize( $doctor_meta['pubs'][0] );
	}

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	?>
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
	<div class="btn_wrapper mb-l"><a class="button button-primary button-large add_pub" data-entry-id="<?php echo esc_html( $counter ) - 1; ?>" href="#"><?php echo esc_html__( 'add an extra publication', 'doctor2go-connect' ); ?></a></div>
	
	<?php
}




function d2gc_meta_box_page_type_cb( $post ) {

	$page_meta = get_post_meta( $post->ID );

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	$pages = array(
		'My profile',
		'Doctors',
		'Lost password',
		'Reset password',
		'Login',
		'Patient registration',
		'Privacy policy',
		'Terms and conditions',
		'Password reset sent',
		'Account settings',
		'Appointments',
		'Patient dashboard',
		'Liked doctors',
		'Questionnaires',
		'Disclaimer',
		'Appointment confirmation',
		'Email advice confirmation',

	);
	?>
	<h4 style="color: red"><?php esc_html_e( 'All pages used by the D2G connect plugin need a page type! Otherwise links in the front end might be broken!', 'doctor2go-connect' ); ?></h4>
	<select name="meta[d2g_page_identifier]">
		<option value="0"><?php esc_html_e( 'make a selection', 'doctor2go-connect' ); ?></option>
		<?php
		foreach ( $pages as $page ) {
			$value = strtolower( str_replace( ' ', '_', $page ) );
			?>
			<option <?php echo ( $page_meta['d2g_page_identifier'][0] == $value ) ? 'selected' : ''; ?> value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $page ); ?></option>
		<?php } ?>
		
	</select>
	<?php
}



function d2gc_meta_box_email_type_cb( $post ) {

	$page_meta = get_post_meta( $post->ID );

	wp_nonce_field( 'd2g_meta_box_nonce', 'meta_box_nonce' );
	$emailTypes = array(
		'cancellation_doctor'  => __( 'Cancellation for doctor', 'doctor2go-connect' ),
		'cancellation_patient' => __( 'Cancellation for patient', 'doctor2go-connect' ),
		'registration'         => __( 'Confirmation for patient registration', 'doctor2go-connect' ),
	);
	?>
	<h4 style="color: red"><?php esc_html_e( 'Define which email this template is for.', 'doctor2go-connect' ); ?></h4>
	<select name="meta[d2g_email_identifier]">
		<option value="0"><?php esc_html_e( 'make a selection', 'doctor2go-connect' ); ?></option>
		<?php
		foreach ( $emailTypes as $key => $label ) {
			$value = strtolower( str_replace( ' ', '_', $key ) );
			?>
			<option <?php echo ( $page_meta['d2g_email_identifier'][0] == $value ) ? 'selected' : ''; ?> value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $label ); ?></option>
		<?php } ?>
		
	</select>
	<?php
}
