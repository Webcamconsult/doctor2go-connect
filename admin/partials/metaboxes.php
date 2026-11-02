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

	// one nonce for all doctor boxes (verified in D2gConnect_Admin::d2gc_meta_box_save())
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
	<?php
}

/**
 * Button to the doctor2go.online dashboard ("URL used for waiting room from the Doctor2go software" on the settings
 * page). Nothing when that URL is not set.
 */
function d2gc_wcc_dashboard_button() {
	$url = get_option( 'd2gc_waiting_room_url' );
	if ( ! $url ) {
		return;
	}
	?>
	<p><a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html__( 'Open doctor2go.online dashboard', 'doctor2go-connect' ); ?></a></p>
	<?php
}

/**
 * Prices box: price and currency per consultation type.
 *
 * @param WP_Post $post
 */
function d2gc_meta_box_prices_cb( $post ) {
	$values     = get_post_meta( $post->ID );
	$currencies = array( 'EUR', 'USD', 'GBP', 'ALL', 'MXN', 'AUD', 'INR', 'AZN', 'BYN', 'BGN', 'HRK', 'CZK', 'DKK', 'GEL', 'HUF', 'ISK', 'CHF', 'MKD', 'MDL', 'NOK', 'PLN', 'RON', 'RUB', 'RSD', 'SEK', 'TRY', 'UAH', 'CAD', 'NZD', 'BRL', 'ZAR' );
	$prices     = array(
		'walk_in'     => __( 'Walk-in consultation', 'doctor2go-connect' ),
		'written_con' => __( 'E-mail advice', 'doctor2go-connect' ),
	);
	// editor off: all prices come from doctor2go.online (doctor sync), here read-only
	if ( ! d2gc_booking_rules_editor_enabled() ) {
		$prices['video_con'] = __( 'Video consultation (standard price)', 'doctor2go-connect' );
		?>
		<p><?php echo esc_html__( 'The prices of a doctor are set in the doctor2go.online software and sent to this website automatically.', 'doctor2go-connect' ); ?></p>
		<?php d2gc_wcc_dashboard_button(); ?>
		<div class="d2gc-box-fields">
			<?php foreach ( $prices as $prefix => $label ) { ?>
				<div class="d2gc-box-field">
					<span class="d2gc-box-field__label"><?php echo esc_html( $label ); ?></span>
					<div class="d2gc-price-input d2gc-price-input--readonly">
						<input type="text" readonly value="<?php echo esc_attr( $values[ $prefix . '_price' ][0] ?? '' ); ?>" aria-label="<?php echo esc_attr__( 'Price', 'doctor2go-connect' ); ?>">
						<input type="text" readonly value="<?php echo esc_attr( $values[ $prefix . '_currency' ][0] ?? '' ); ?>" aria-label="<?php echo esc_attr__( 'Currency', 'doctor2go-connect' ); ?>" class="d2gc-currency">
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
		return;
	}
	?>
	<div class="d2gc-box-fields">
		<?php foreach ( $prices as $prefix => $label ) { ?>
			<div class="d2gc-box-field">
				<label for="<?php echo esc_attr( $prefix ); ?>_price"><?php echo esc_html( $label ); ?></label>
				<div class="d2gc-price-input">
					<input type="text" inputmode="decimal" id="<?php echo esc_attr( $prefix ); ?>_price" name="meta[<?php echo esc_attr( $prefix ); ?>_price]" value="<?php echo esc_attr( $values[ $prefix . '_price' ][0] ?? '' ); ?>" placeholder="<?php echo esc_attr__( 'Price', 'doctor2go-connect' ); ?>">
					<select name="meta[<?php echo esc_attr( $prefix ); ?>_currency]" id="<?php echo esc_attr( $prefix ); ?>_currency" aria-label="<?php echo esc_attr__( 'Currency', 'doctor2go-connect' ); ?>">
						<?php foreach ( $currencies as $currency ) { ?>
							<option <?php selected( $currency, $values[ $prefix . '_currency' ][0] ?? '' ); ?> value="<?php echo esc_attr( $currency ); ?>"><?php echo esc_html( $currency ); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>
		<?php // video consultation: read-only, it belongs to the booking rules (availabilities) in the profile editor ?>
		<div class="d2gc-box-field">
			<span class="d2gc-box-field__label"><?php echo esc_html__( 'Video consultation (standard price)', 'doctor2go-connect' ); ?></span>
			<div class="d2gc-price-input d2gc-price-input--readonly">
				<input type="text" readonly value="<?php echo esc_attr( $values['video_con_price'][0] ?? '' ); ?>" aria-label="<?php echo esc_attr__( 'Price', 'doctor2go-connect' ); ?>">
				<input type="text" readonly value="<?php echo esc_attr( $values['video_con_currency'][0] ?? '' ); ?>" aria-label="<?php echo esc_attr__( 'Currency', 'doctor2go-connect' ); ?>" class="d2gc-currency">
			</div>
			<p class="description"><?php echo esc_html__( 'Set in the profile editor together with the booking rules (availabilities), a different price can be set per booking rule.', 'doctor2go-connect' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Questionnaires box: e-mail advice questionnaire and intake for video consultations.
 *
 * @param WP_Post $post
 */
function d2gc_meta_box_questionnaires_cb( $post ) {
	$values = get_post_meta( $post->ID );
	?>
	<div class="d2gc-box-fields d2gc-box-fields--2">
		<div class="d2gc-box-field">
			<label for="written_con_type"><?php echo esc_html__( 'E-mail consult questionnaire', 'doctor2go-connect' ); ?></label>
			<select name="meta[written_con_type]" id="written_con_type">
				<?php foreach ( d2gc_get_questionnaire_types() as $type => $label ) { ?>
					<option <?php selected( $type, $values['written_con_type'][0] ?? '' ); ?> value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php } ?>
			</select>
			<p class="description"><?php echo esc_html__( 'The form patients fill in when they request e-mail advice.', 'doctor2go-connect' ); ?></p>
		</div>
		<div class="d2gc-box-field">
			<label for="intake_type"><?php echo esc_html__( 'Intake for video consultations', 'doctor2go-connect' ); ?></label>
			<select name="meta[intake_type]" id="intake_type">
				<?php $current_intake = d2gc_get_doctor_intake_type( $values ); ?>
				<?php foreach ( d2gc_get_questionnaire_types( true ) as $type => $label ) { ?>
					<option <?php selected( $type, $current_intake ); ?> value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php } ?>
			</select>
			<p class="description"><?php echo esc_html__( 'The questions in the booking form of a video consultation.', 'doctor2go-connect' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Filters box: the doctor taxonomies (country, languages, fields of study, body parts) as searchable multi-selects
 * (select2). Saved by WordPress itself via tax_input; the hidden 0 makes sure an emptied box removes all terms.
 * Required taxonomies (D2gConnect_Admin::d2gc_get_required_taxonomies()) are checked in d2g-connect-admin.js.
 *
 * @param WP_Post $post
 */
function d2gc_meta_box_filters_cb( $post ) {
	$required = D2gConnect_Admin::d2gc_get_required_taxonomies();
	// translated taxonomies (Polylang): the terms in the language of this post
	$lang = D2GC_Translations::get_post_language( $post->ID );
	wp_nonce_field( 'd2gc_filters', 'd2gc_filters_nonce' );
	?>
	<div class="notice notice-error inline d2gc-filters-error" hidden><p></p></div>
	<div class="d2gc-box-fields d2gc-box-fields--2">
		<?php
		foreach ( get_object_taxonomies( 'd2g_doctor', 'objects' ) as $taxonomy ) {
			if ( ! $taxonomy->show_ui || false !== $taxonomy->meta_box_cb ) {
				continue;
			}
			$args = array(
				'taxonomy'   => $taxonomy->name,
				'hide_empty' => false,
				'orderby'    => 'name',
			);
			if ( $lang ) {
				$args['lang'] = $lang;
			}
			$terms       = get_terms( $args );
			$selected    = wp_get_object_terms( $post->ID, $taxonomy->name, array( 'fields' => 'ids' ) );
			$is_required = in_array( $taxonomy->name, $required, true );
			$field_id    = 'd2gc-tax-' . $taxonomy->name;
			?>
			<div class="d2gc-box-field">
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $taxonomy->labels->name . ( $is_required ? ' *' : '' ) ); ?></label>
				<input type="hidden" name="tax_input[<?php echo esc_attr( $taxonomy->name ); ?>][]" value="0">
				<select id="<?php echo esc_attr( $field_id ); ?>" name="tax_input[<?php echo esc_attr( $taxonomy->name ); ?>][]" multiple class="d2gc-select2" data-label="<?php echo esc_attr( $taxonomy->labels->name ); ?>"<?php echo $is_required ? ' data-required="1"' : ''; ?> data-placeholder="<?php echo esc_attr( $taxonomy->labels->search_items ); ?>">
					<?php foreach ( is_array( $terms ) ? $terms : array() as $term ) { ?>
						<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( in_array( $term->term_id, $selected, true ) ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php } ?>
				</select>
			</div>
		<?php } ?>
	</div>
	<p class="description"><?php echo esc_html__( 'Fields marked with * need at least one value before the profile can be published.', 'doctor2go-connect' ); ?></p>
	<?php
}

/**
 * Availability & holiday box: booking rules and vacation periods are edited in the front-end profile editor
 * (they are sent to WCC there); here only an explanation, a link and the vacation periods read-only.
 *
 * @param WP_Post $post
 */
function d2gc_meta_box_availability_cb( $post ) {
	$periods = array();
	foreach ( D2GC_Translations::get_post_translation_ids( $post->ID ) as $id ) {
		$periods = get_post_meta( $id, 'd2g_vacation_periods', true );
		if ( is_array( $periods ) && $periods ) {
			break;
		}
	}
	$periods = is_array( $periods ) ? $periods : array();
	$today   = wp_date( 'Y-m-d' );
	$format  = get_option( 'date_format' );
	$tz      = wp_timezone();
	$edit    = D2G_doc_user_profile::d2gc_page_url() . '?edit=' . $post->ID;
	?>
	<?php if ( d2gc_booking_rules_editor_enabled() ) { ?>
		<p>
			<?php echo esc_html__( 'The availabilities (booking rules, incl. the video consultation prices) and the vacation periods of a doctor are set in the profile editor on the website, because they are sent to doctor2go.online when they are saved.', 'doctor2go-connect' ); ?>
		</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $edit ); ?>" target="_blank"><?php echo esc_html__( 'Open profile editor', 'doctor2go-connect' ); ?></a></p>
	<?php } else { ?>
		<p>
			<?php echo esc_html__( 'The availabilities (booking rules, incl. the video consultation prices) and the vacation periods of a doctor are set in the doctor2go.online software (see Doctor2Go Connect settings, Doctors tab).', 'doctor2go-connect' ); ?>
		</p>
		<?php d2gc_wcc_dashboard_button(); ?>
	<?php } ?>
	<h4><?php echo esc_html__( 'Vacation periods', 'doctor2go-connect' ); ?></h4>
	<?php if ( empty( $periods ) ) { ?>
		<p><?php echo esc_html__( 'No vacation periods.', 'doctor2go-connect' ); ?></p>
	<?php } else { ?>
		<ul class="d2gc-vacation-list">
			<?php
			foreach ( $periods as $period ) {
				$start = ( new DateTimeImmutable( $period['start_date'], $tz ) )->getTimestamp();
				$end   = ( new DateTimeImmutable( $period['end_date'], $tz ) )->getTimestamp();
				if ( $period['end_date'] < $today ) {
					$state = __( 'past', 'doctor2go-connect' );
				} elseif ( $period['start_date'] <= $today ) {
					$state = __( 'active now', 'doctor2go-connect' );
				} else {
					$state = __( 'upcoming', 'doctor2go-connect' );
				}
				?>
				<li><?php echo esc_html( wp_date( $format, $start ) . ' – ' . wp_date( $format, $end ) ); ?> <em>(<?php echo esc_html( $state ); ?>)</em></li>
			<?php } ?>
		</ul>
	<?php } ?>
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
		'Secure patient portal',
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
