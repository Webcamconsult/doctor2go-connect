<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Template Functions.
 *
 * @package d2g-connect
 */
// Register AJAX callback + script for saving timezone
add_action( 'wp_ajax_d2g_save_user_timezone', 'd2g_save_user_timezone' );
add_action( 'wp_ajax_nopriv_d2g_save_user_timezone', 'd2g_save_user_timezone' );
add_action( 'wp_enqueue_scripts', 'd2g_enqueue_timezone_script' );

add_action( 'after_setup_theme', 'd2g_load_single_d2g_doctor_hooks' );

add_filter( 'body_class', 'd2g_body_class', 100, 2 );

if ( ! is_admin() || wp_doing_ajax() ) {
	add_action( 'the_post', 'd2g_setup_profile_data' );
}

// Register AJAX handler for loading availibility data in query loop for mulitple doctors
add_action( 'wp_ajax_d2g_load_availability_data', 'd2g_load_availability_data' );
add_action( 'wp_ajax_nopriv_d2g_load_availability_data', 'd2g_load_availability_data' );

add_action( 'd2g_info_box', 'cb_d2g_info_box', 10, 3 );

add_action( 'd2g_like_button', 'cb_d2g_like_button', 10, 1 );

add_action( 'd2g_consult_buttons', 'd2g_show_consult_buttons', 10, 2 );

// hooks for single page
add_action( 'd2g_single_d2g_doctor_main_content', 'd2g_single_d2g_doctor_content' );

// this sets the path to the single template file for doctors
add_filter( 'single_template', 'd2g_redirect_single_template' );

/**
 * Load single dooctor hooks (to extend later with layout options)
 *
 * @since v1.0.0
 */
function d2g_load_single_d2g_doctor_hooks() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	if ( get_option( 'd2g_detail_page_view' ) != 'single-v2' && ! isset( $_GET['view'] ) ) {
		add_action( 'd2g_single_sidebar', 'cb_d2g_single_sidebar', 10, 1 );
	}
	add_action( 'd2g_doctor_locations', 'd2g_show_doctor_locations_by_id', 10, 1 );
	add_action( 'd2g_doctor_extended_info', 'd2g_show_doctor_extended_info' );
	add_action( 'd2g_booking_calendar', 'd2g_show_booking_calendar', 10, 1 );
	add_action( 'd2g_doctor_walkin_form', 'd2g_show_walkin_form' );
	add_action( 'd2g_doctor_written_con_form', 'd2g_show_written_con_form' );
	add_action( 'd2g_doctor_generic_written_con_form', 'd2g_show_generic_written_con_form' );
	add_action( 'd2g_doctor_consultancy_tabs', 'd2g_show_consultancy_tabs' );
	add_action( 'd2g_back_to_overview', 'd2g_show_back_btn' );
}
/*
* sets the path to the corre3ct template file for the view from a single doctor
*/
function d2g_redirect_single_template( $template ) {
	global $post;

	if ( $post->post_type == 'd2g_doctor' && ( 'single.php' == basename( $template ) || 'template-canvas.php' == basename( $template ) ) ) {
		$template = WP_PLUGIN_DIR . '/doctor2go-connect/public/templates/single-d2g_doctor.php';
	}

	return $template;
}

/**
 * Hook call back for single doctor profile (d2g_doctor). This will be extended for a choice of other template parts.
 *
 * @return void
 */
function d2g_single_d2g_doctor_content() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '';
	if ( get_option( 'd2g_detail_page_view' ) !== 'single-v2' && get_option( 'd2g_detail_page_view' ) !== 'single-v3' && ! $view ) {
		include d2g_locate_template( 'content-single-d2g_doctor.php' );
	} elseif ( get_option( 'd2g_detail_page_view' ) == 'single-v2' || $view == 'v2' ) {
		include d2g_locate_template( 'content-single-d2g_doctor-v2.php' );
	} elseif ( get_option( 'd2g_detail_page_view' ) == 'single-v3' || $view == 'v3' ) {
		include d2g_locate_template( 'content-single-d2g_doctor-v3.php' );
	}
}


/**
 * When the_post is called, put profile data into a global.
 *
 * @param mixed $post Post object or post id.
 * @return global $d2g_profile_data
 */
function d2g_setup_profile_data( $post ) {
	global $wp_query;

	if ( $post->post_type == 'd2g_doctor' ) {
		$GLOBALS['d2g_profile_data'] = new D2G_ProfileData( $post );
		return $GLOBALS['d2g_profile_data'];
	}
}


/*
*retrives the template file
*/
function d2g_locate_template( $template_name, $folder = 'templates', $debugMode = false ) {

	static $loadedFiles;

	if ( ! $template_name ) {
		return;
	}
	$located = false;

	$templatePath  = dirname( $template_name );
	$template_name = basename( $template_name );

	if ( isset( $loadedFilex[ $template_name ] ) ) {
		$located = $loadedFiles[ $template_name ];
	} else {
		$arrPath = array(
			get_stylesheet_directory() . "/d2g/$templatePath/",
			get_template_directory() . "/d2g/$templatePath/",
			plugin_dir_path( __FILE__ ) . $folder . "/$templatePath/",
		);
		foreach ( $arrPath as $sPath ) {
			$sPath .= $template_name;
			if ( $debugMode ) {
				echo 'search for ' . esc_html( $sPath );
			}
			if ( file_exists( "$sPath" ) ) {
				$located = $sPath;
				if ( $debugMode ) {
					echo ':found<br/>';
				}
				break;
			} elseif ( $debugMode ) {
					echo ':not found<br/>';
			}
		}
	}
	if ( $located ) {
		$loadedFiles[ $template_name ] = $located;
		return $located;
	} else {
		$loadedFiles[ $template_name ] = false;
	}
	return false;
}

/*
*article (post) css classmanipulation
*/

function d2g_getArticleClass( $class = null, $post_id = null ) {
	return join( ' ', get_post_class( $class, $post_id ) );
}


/*
*creates custom excerpt with possibility to show full content with HTML
*/
function d2g_ttruncat( $text, $numb ) {

	if ( $numb != 'full' ) {
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( "/\r|\n/", '&nbsp;', $text );
		if ( strlen( $text ) > $numb ) {
			$text = substr( $text, 0, $numb );
			$text = substr( $text, 0, strrpos( $text, ' ' ) );
			$etc  = ' ...';
			$text = $text . $etc;
		}
	} else {
		$text = apply_filters( 'the_content', $text );
	}
	return $text;
}

/**
 * Filter Body Class.
 *
 * @param  array  $classes [description].
 * @param  String $class   [description].
 * @return array
 */
function d2g_body_class( $classes, $class ) {
	global $post;
	$currMeta = get_post_meta( $post->ID );
	if ( is_singular( 'd2g_doctor' ) ) {
		$classes[] = 'd2g-single-doctor';
		if ( get_option( 'd2g_detail_page_view' ) != 'single-v2' ) {
			$classes[] = 'sidebar-menu';
		} else {
			$classes[] = 'full-width';
		}
	} else {
		$dashboardPages = array(
			'patient_dashboard',
			'appointments',
			'account_settings',
			'liked_doctors',
			'questionnaires',
			'secure_patient_portal',
		);
		if ( in_array( $currMeta['d2g_page_identifier'][0], $dashboardPages ) ) {
			$classes[] = 'dashboard_pages';
		}
		if ( $currMeta['d2g_page_identifier'][0] == 'doctors' ) {
			$classes[] = 'd2g-doctor-overview';
		}
	}

	// Give me my new, modified $classes.
	return $classes;
}

function nice_dump( $dump ) {
	echo '<pre>';
	var_dump( $dump ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
	echo '</pre>';
}



// like button
function cb_d2g_like_button( $post_id ) {
	?>
	<?php
		$liked_posts = d2g_get_liked_posts();
		$is_liked    = in_array( $post_id, $liked_posts );
	?>
	<button class="like-button <?php echo $is_liked ? 'icon-heart-filled' : 'icon-heart'; ?>" data-post-id="<?php echo esc_html( $post_id ); ?>">
		<span class="text simple_hide"><?php echo $is_liked ? 'Unlike' : 'Like'; ?></span>
	</button>
	<?php
}


/*
*displays the info box from a doctor profile
*/
function cb_d2g_info_box( $temp_file, $version, $post = '' ) {
	if ( $post == '' ) {
		global $d2g_profile_data;
	} else {
		$d2g_profile_data = new D2G_ProfileData( $post, true );
	}

	$rowClass = '';
	$liClass  = '';
	if ( $temp_file == 'detail' || $version == 'col-2' ) {
		$rowClass = 'row';
		$liClass  = 'col-sm-6';
	}
	$currUser  = wp_get_current_user();
	$user_meta = get_user_meta( $currUser->data->ID );
	?>
	
	<ul class="icon_list specs <?php echo ($temp_file != 'detail')?'list-group mb-3':''?>  <?php echo esc_html( $rowClass ); ?>" id="icon_list_<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>">
		<li class="icon-home list-group-item <?php echo esc_html( $liClass ); ?>">
			<span>
				<?php echo esc_html( $d2g_profile_data->doctor_meta['d2g_zip'][0] ); ?> <?php echo esc_html( $d2g_profile_data->doctor_meta['d2g_city'][0] ); ?> 
				<?php if ( $d2g_profile_data->doctor_meta['d2g_zip'][0] . $d2g_profile_data->doctor_meta['d2g_city'][0] != '' ) { ?>
					-
				<?php } ?>
				<?php if ( $d2g_profile_data->countries !== false ) { ?>
					<?php foreach ( $d2g_profile_data->countries as $country ) { ?>
						<?php echo esc_html( $country->name ); ?>
					<?php } ?>
				<?php } ?>
			</span>
		</li>
        <?php if ( $d2g_profile_data->languages !== false ) { ?>
            <li class="icon-globe list-group-item <?php echo esc_html( $liClass ); ?>">
                <?php foreach ( $d2g_profile_data->languages as $language ) { ?>
                    <span><?php echo esc_html( $language->name ); ?></span>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if ( $d2g_profile_data->doctor_meta['reg_nr'][0] != '' && is_single() ) { ?>
            <li class="icon-sort-numeric-outline list-group-item <?php echo esc_html( $liClass ); ?>">
                <?php echo esc_html__( 'Reg. Nr.', 'doctor2go-connect' ); ?> <?php echo esc_html( $d2g_profile_data->doctor_meta['reg_nr'][0] ); ?>
            </li>
        <?php } ?>
        <?php if ( $d2g_profile_data->doctor_meta['reg_country'][0] != '' && is_single() ) { ?>
            <li class="icon-doc list-group-item <?php echo esc_html( $liClass ); ?>">
                <?php echo esc_html__( 'Reg. country', 'doctor2go-connect' ); ?>: <?php echo esc_html( $d2g_profile_data->doctor_meta['reg_country'][0] ); ?>
            </li>
        <?php } ?>
	</ul>   
	<?php 
	if($temp_file != 'detail'){
		if($d2g_profile_data->doctor_meta['d2g_first_availability'][0] != '' && $d2g_profile_data->doctor_meta['d2g_first_availability'][0] != 0){
			$datetimeObj = DateTime::createFromFormat( 'Y-m-d\TH:i:s+', $d2g_profile_data->doctor_meta['d2g_first_availability'][0] );
			$timezone    = ( get_user_timezone() != '' ) ? get_user_timezone() : 'Europe/Amsterdam';

			if ( $user_meta['p_timezone'][0] ) {
				$timezone = $user_meta['p_timezone'][0];
			}

			$timeZoneChange = new DateTimeZone( $timezone );
			$datetimeObj->setTimezone( $timeZoneChange );
			$firstAvailibility = $datetimeObj->format( 'd/m/Y' ) . ' ' . esc_html__( ' - ', 'doctor2go-connect' ) . ' ' . $datetimeObj->format( 'H:i' ) . '  (' . explode( '/', $timezone )[1] . ')';

		} else {
			$firstAvailibility = '';
		}
		$error_code 	= '<span class="text-danger">&#10060;</span>';
		$success_code 	= '<span class="text-success">&#10004;</span>';
		?>
		<ul class="icon_list specs consult_list <?php echo ($temp_file != 'detail')?'list-group':''?>  <?php echo esc_html( $rowClass ); ?>" id="icon_list_<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>">
			<li class="bg-light list-group-item d-flex justify-content-between <?php echo esc_html( $liClass ); ?>">
				<strong><?php echo esc_html__('Consultation offers');?></strong>
				<a href="#info_content" class="fancybox info_link">
					<span class="icon-info"></span>
				</a>
			</li>
			<?php 
			if( $d2g_profile_data->doctor_meta['d2g_tariffs'][0] != '' && $d2g_profile_data->doctor_meta['d2g_tariffs'][0] != 0 ){
				$video = true;
			} else {
				$video = false;
			}
			?>
			<li class="<?php echo ($video == true)?esc_html__('available', 'doctor2go-connect'):esc_html__('not_available', 'doctor2go-connect')  ?> flaticon-wcc flaticon-meeting-schedule list-group-item d-flex justify-content-between align-items-center <?php echo esc_html( $liClass ); ?>">
				<div class="ms-2 me-auto">
					<div class="fw-bold"><?php echo esc_html__('Video consult on appointment', 'doctor-2go-connect')?> <?php echo ($video == true)?wp_kses_post($success_code):wp_kses_post($error_code)?></div>
						<?php echo  ($video == true)?esc_html__('first availability: ', 'doctor-2go-connect').wp_kses_post( $firstAvailibility ):esc_html__('not available', 'doctor2go-connect')?>
				</div>
				<span class="badge text-bg-primary rounded-pill">
					<?php echo ( $video == true) ? wp_kses_post( $d2g_profile_data->doctor_meta['d2g_tariffs'][0] ):esc_html__('n/a')?>
				</span>
				
			</li>
			<?php 
			if( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ){
				$email = true;
			} else {
				$email = false;
			}
			?>
			<li class="<?php echo ($email == true)?esc_html__('available', 'doctor2go-connect'):esc_html__('not_available', 'doctor2go-connect')  ?> icon-mail-1 list-group-item d-flex justify-content-between align-items-center <?php echo esc_html( $liClass ); ?>">
				<div class="ms-2 me-auto">
					<div class="fw-bold"><?php echo esc_html__('E-mail advice', 'doctor-2go-connect')?> <?php echo ($email == true)?wp_kses_post($success_code):wp_kses_post($error_code)?></div>
					<?php echo ($email == true)?esc_html__('available at any time', 'doctor2go-connect'):esc_html__('not available', 'doctor2go-connect')  ?>
				</div>
				<span class="badge text-bg-primary rounded-pill">
					<?php echo ($email == true) ? esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] ).' '. esc_html( $d2g_profile_data->doctor_meta['written_con_price'][0] ):esc_html__('n/a')?>
				</span>
			</li>
			<?php 
			if( $d2g_profile_data->doctor_meta['d2g_walk_in'][0] != '' && $d2g_profile_data->doctor_meta['d2g_walk_in'][0] != 0 && $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ){
				$walkin = true;
			} else {
				$walkin = false;
			}
			?>
			<li class="<?php echo ($walkin == true)?esc_html__('available', 'doctor2go-connect'):esc_html__('not_available', 'doctor2go-connect')  ?> flaticon-online-meeting flaticon-wcc list-group-item d-flex justify-content-between align-items-center <?php echo esc_html( $liClass ); ?>">
				<div class="ms-2 me-auto">
					<div class="fw-bold"><?php echo esc_html__('Walkin video consult', 'doctor-2go-connect')?> <?php echo ($walkin == true)?wp_kses_post($success_code):wp_kses_post($error_code)?></div>
					<?php echo ($walkin == true)?esc_html__('now available', 'doctor2go-connect'):esc_html__('not available', 'doctor2go-connect')  ?>
				</div>
				<span class="badge text-bg-primary rounded-pill">
					<?php echo ($walkin == true) ? esc_html( $d2g_profile_data->doctor_meta['walk_in_currency'][0] ).' '. esc_html( $d2g_profile_data->doctor_meta['walk_in_price'][0] ):esc_html__('n/a')?>
				</span>
			</li>
		</ul>
	<?php }
}


function cb_d2g_single_sidebar( $d2g_profile_data = '' ) {
	if ( $d2g_profile_data == '' ) {
		global $d2g_profile_data;
	}
	$post_id = get_the_ID();
	if ( get_option( 'd2g_use_imgix' ) == 1 ) {
		$doc_pic = $d2g_profile_data->feat_pic_full . '&w=300&h=300&fit=crop&crop=faces&auto=format,compress';
	} else {
		$doc_pic = $d2g_profile_data->feat_pic_square;
	}
	?>
	<div class="col-sm-3 sidebar not_mobile">
		<div class="sidebar_inner">
			
			<?php if ( $d2g_profile_data->walk_in_check == true ) { ?>
				<span class="css_shape_doctor"></span>
			<?php } ?>
			<figure class="card p-5 mb-3">
				<img style="width:100%; border-radius:100%;" src="<?php echo esc_html( $doc_pic ); ?>" alt="<?php echo esc_html( get_the_title() ); ?>">
				<?php do_action( 'd2g_like_button', $post_id ); ?>
			</figure>
			<?php do_action( 'd2g_consult_buttons', 'detail', 'small' ); ?>
			<ul class="margin-bottom-standard anchor_links list-group">
				<li class="list-group-item icon-right-open"><a class="scroll_to" href="#info"><?php echo esc_html__( 'Short info', 'doctor2go-connect' ); ?></a></li>
				<li class="list-group-item icon-right-open"><a class="scroll_to" href="#bio"><?php echo esc_html__( 'About', 'doctor2go-connect' ); ?></a></li>
				<?php if ( $d2g_profile_data->locations ) { ?>
					<li class="list-group-item icon-right-open"><a class="scroll_to" href="#location_wrapper"><?php echo esc_html__( 'Location(s)', 'doctor2go-connect' ); ?></a></li>
				<?php } ?>
				<?php if ( $d2g_profile_data->exps ) { ?>
					<li class="list-group-item icon-right-open"><a class="scroll_to" href="#exp"><?php echo esc_html__( 'Experience', 'doctor2go-connect' ); ?></a></li>
				<?php } ?>                
				<?php if ( $d2g_profile_data->edus ) { ?>
					<li class="list-group-item icon-right-open"><a class="scroll_to" href="#edu"><?php echo esc_html__( 'Education', 'doctor2go-connect' ); ?></a></li>
				<?php } ?>
				<?php if ( $d2g_profile_data->pubs ) { ?>
					<li class="list-group-item icon-right-open"><a class="scroll_to" href="#pub"><?php echo esc_html__( 'Publications', 'doctor2go-connect' ); ?></a></li>
				<?php } ?>
				<?php if ( $d2g_profile_data->walk_in_check == true ) { ?>
					<li class="list-group-item icon-right-open"><a class="highlight scroll_to" href="#inloop"><?php echo esc_html__( 'Walk in now', 'doctor2go-connect' ); ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<?php
}


function d2g_show_doctor_locations_by_id( $docID = '', $show_title = true ) {
	if ( $docID == '' ) {
		global $d2g_profile_data;
		$locations = $d2g_profile_data->locations;
	} else {
		$locations = get_post_meta( $docID, 'locations_to_go' )[0];
	}

	$checker = 0;

	if ( $locations != '' ) {
		?>
		<div id="location_wrapper" class="locations section">
			<?php if ( $show_title == true ) { ?>
				<h3 class="section_title"><?php echo esc_html__( 'Location(s)', 'doctor2go-connect' ); ?></h3>
			<?php } ?>
			<?php if ( count( $locations ) > 0 ) { ?>
				<ul class="location_tabs">
					<?php foreach ( $locations as $location ) { ?>
						<li class="tab_link" ref-loc="#<?php echo esc_html( $location['_id'] ); ?>"><?php echo esc_html( $location['name'] . ' (' . $location['city'] . ' - ' . $location['country'] . ')' ); ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
			
			<?php foreach ( $locations as $location ) { ?>
				<div class="d2g_tab_content_wrapper <?php echo ( count( $locations ) > 0 && $checker != 0 ) ? 'hide' : ''; ?>" id="<?php echo esc_html( $location['_id'] ); ?>">
					<div class="d2g_tab_content">
						<div class="row">
							<div class="col-sm-8 no_pad_right">
								<iframe style="width:100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo esc_html( $location['street'] ); ?> <?php echo esc_html( $location['number'] ); ?>,<?php echo esc_html( $location['postal_code'] ); ?> <?php echo esc_html( $location['city'] ); ?>&z=10&output=embed&hl=<?php echo esc_html( explode( '_', get_locale() )[0] ); ?>"></iframe>
							</div>
							<div class="col-sm-4 ">
								<div class="inner_wrapper">
									<h3><?php echo esc_html( $location['name'] ); ?></h3>
									<?php if ( $location['description'] != '' ) { ?>
										<p class="lightGrey"><?php echo esc_html( $location['description'] ); ?></p>
									<?php } ?>
									<div class="address_wrapper">
										<h4><?php echo esc_html__( 'Address', 'doctor2go-connect' ); ?></h4>
										<p><?php echo esc_html( $location['country'] ); ?></p>
										<p><?php echo esc_html( $location['street'] ); ?> <?php echo esc_html( $location['number'] ); ?></p>
										<p><?php echo esc_html( $location['postal_code'] ); ?> <?php echo esc_html( $location['city'] ); ?></p>
										<p><?php echo esc_html( $location['country'] ); ?></p>
									</div>
									<?php if ( $location['how_to_get_there'] != '' ) { ?>
										<div class="extra_info">
											<h4><?php echo esc_html__( 'How to get there', 'doctor2go-connect' ); ?></h4>
											<p><?php echo esc_html( $location['how_to_get_there'] ); ?></p>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php ++$checker; ?>
			<?php } ?>
		</div>
		<?php
	}
}


function d2g_show_doctor_extended_info() {
	global $d2g_profile_data;
	?>
	<?php if ( $d2g_profile_data->exps ) { ?>
		<div id="exp" class="exp section mb-5">
			<h3 class="section_title"><?php echo esc_html__( 'Working experience', 'doctor2go-connect' ); ?></h3>
			<div class="row exp_exp">
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Date', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Expertise', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Position', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Organisation / Hospital', 'doctor2go-connect' ); ?></strong>
				</div>
			</div>
			<?php foreach ( $d2g_profile_data->exps as $exp ) { ?>
				<div class="row exp_exp">
					<div class="col-sm-3">
						<?php echo esc_html( $exp['d2g_exp_edu_start_date'] ); ?> - <?php echo esc_html( $exp['d2g_exp_edu_end_date'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $exp['d2g_exp_edu_expertise'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $exp['d2g_exp_edu_title'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $exp['d2g_exp_edu_org'] ); ?>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<?php if ( $d2g_profile_data->edus ) { ?>
		<div id="edu" class="edu section mb-5">
			<h3 class="section_title"><?php echo esc_html__( 'Education', 'doctor2go-connect' ); ?></h3>
			<div class="row exp_edu">
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Date', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Study area', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Degree', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'Institution', 'doctor2go-connect' ); ?></strong>
				</div>
			</div>
			<?php foreach ( $d2g_profile_data->edus as $edu ) { ?>
				<div class="row exp_edu">
					<div class="col-sm-3">
						<?php echo esc_html( $edu['d2g_exp_edu_start_date'] ); ?> - <?php echo esc_html( $edu['d2g_exp_edu_end_date'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $edu['d2g_exp_edu_study'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $edu['d2g_exp_edu_title'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $edu['d2g_exp_edu_org'] ); ?>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<?php if ( $d2g_profile_data->pubs ) { ?>
		<div id="pub" class="pub section mb-5">
			<h3 class="section_title"><?php echo esc_html__( 'Publications', 'doctor2go-connect' ); ?></h3>
			<div class="row exp_edu">
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'title', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'journal / website', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'type of publication', 'doctor2go-connect' ); ?></strong>
				</div>
				<div class="col-sm-3">
					<strong><?php echo esc_html__( 'publication Date', 'doctor2go-connect' ); ?></strong>
				</div>
			</div>
			<?php
			$temp_counter = 1;
			foreach ( $d2g_profile_data->pubs as $pub ) {
				?>
				<div class="row exp_edu pub">
					<div class="col-sm-3">
						<p><?php echo esc_html( $pub['d2g_pub_title'] ); ?></p>
						<?php if ( $exp['d2g_pub_link'] ) { ?>
							<p><a target="_blank" href="<?php echo esc_html( $pub['d2g_pub_link'] ); ?>"><?php echo esc_html__( 'read online', 'doctor2go-connect' ); ?> <span style="font-size: 12px;" class="icon-right-open"></span></a></p>
						<?php } ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $pub['d2g_pub_journal'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $pub['d2g_pub_type'] ); ?>
					</div>
					<div class="col-sm-3">
						<?php echo esc_html( $pub['d2g_pub_date'] ); ?>
					</div>
				</div>
				<?php
				++$temp_counter;
			}
			?>
		</div>
		<?php
	}
}


function d2g_show_booking_calendar( $post = '', $only_cal = false, $in_tabs = false ) {

	if ( $post == '' ) {
		global $d2g_profile_data;
	} else {
		$d2g_profile_data = new D2G_ProfileData( $post, true );
	}
	$post_id = $d2g_profile_data->doctor_profile_ID;
	// patient data
	$patient      = wp_get_current_user();
	$patient_meta = get_user_meta( $patient->data->ID );
	$site_key     = get_option( 'd2g_recaptcha_site_key' );
	$redirectURL  = get_the_permalink( $post_id ) . '?book=1';

	$d2gAdmin  = new D2G_doc_user_profile();
	$currLang  = explode( '_', get_locale() )[0];
	$pageLogin = $d2gAdmin::d2g_page_url( $currLang, 'login', true )['url'] . '?redirect_to=' . urlencode( $redirectURL );
	$pageRegis = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true )['url'] . '?redirect_to=' . urlencode( $redirectURL );
	?>
	<!--booking calendar-->
	<div class="row">
			
		<div id="calendar_wrapper" class="calendar section mb-5 col-sm-6">
			<h3 class="section_title"><?php echo esc_html__('Booking calendar', 'doctor2go-connect')?></h3>
			<div class="mb-5"  id="booking_intro">
				<h4 class="opener"><?php echo esc_html__('Need help? Click here for the booking instructions.', 'doctor2go-connect')?> <span class="icon-angle-down"></span></h4>
				<ol class="list-group list-group-numbered simple_hide">
					<li class="list-group-item">
						<?php echo esc_html__( 'In the calendar, select a day that has available appointments. The available days are marked with a button showing the number of free slots (for example, "3 slots").', 'doctor2go-connect' ); ?>
					</li>
					<li class="list-group-item">
						<?php echo esc_html__( 'Click the button for your preferred day to choose a time.', 'doctor2go-connect' ); ?>
					</li>
					<li class="list-group-item">
						<?php echo esc_html__( 'After selecting your time, fill in the booking form with your details and submit it to complete your reservation.', 'doctor2go-connect' ); ?>
					</li>
					<li class="list-group-item">
						<?php echo esc_html__( 'You will than receive an email with futher instructions.', 'doctor2go-connect' ); ?>
					</li>
				</ol>
			</div>
			<div id="calendar"></div>
		</div>
		<!--booking form-->
		<div class="col-sm-6 cal_form_wrapper">
			
			<div  class=" p-4 border rounded bg-light simple_hide" id="booking_form_wrapper">
				<div id="error" class="alert alert-danger simple_hide"></div>
				<p id="app_msg_success" class="alert alert-success simple_hide"></p>
				<form name="booking_form" id="booking_form">
					<h3 class="mb-4"><?php echo esc_html__( 'Booking details', 'doctor2go-connect' ); ?></h3>
					<div id="app_details" class="row g-3">
						<div class="col-12">
							<label class="form-label fw-bold"><?php echo esc_html__( 'Doctor', 'doctor2go-connect' ); ?></label>
							<div id="doctor" class="form-control-plaintext"><?php echo esc_html( get_the_title( $d2g_profile_data->doctor_profile_ID ) ); ?></div>
						</div>
						<div class="col-12">
							<label class="form-label fw-bold"><?php echo esc_html__( 'Costs', 'doctor2go-connect' ); ?></label>
							<div class="form-control-plaintext icon-cc-mastercard">
								&nbsp;&nbsp;<span id="pay_price"></span> <span id="pay_cur"></span> / <?php echo esc_html__( 'consultation', 'doctor2go-connect' ); ?><br>
								<small class="text-muted"><?php echo esc_html__( 'Prices are excl. VAT', 'doctor2go-connect' ); ?></small>
							</div>
						</div>
						<div class="col-md-6">
							<label class="form-label fw-bold"><?php echo esc_html__( 'Start', 'doctor2go-connect' ); ?></label>
							<div id="start" class="form-control-plaintext"></div>
						</div>
						<div class="col-md-6">
							<label class="form-label fw-bold"><?php echo esc_html__( 'End', 'doctor2go-connect' ); ?></label>
							<div id="end" class="form-control-plaintext"></div>
						</div>
						<div class="col-12">
							<label class="form-label fw-bold"><?php echo esc_html__( 'Location', 'doctor2go-connect' ); ?></label>
							<div id="location" class="form-control-plaintext"></div>
						</div>
						<div class="col-12">
							<label class="form-label fw-bold"><?php echo esc_html__( 'Your info', 'doctor2go-connect' ); ?></label>
							<div id="patient" class="row g-3">
								<?php if ( is_user_logged_in() ) {
									$first_name = !empty( $patient_meta['first_name'][0] ) ? $patient_meta['first_name'][0] : '';
									$last_name  = !empty( $patient_meta['last_name'][0] ) ? $patient_meta['last_name'][0] : '';
									$email      = !empty( $patient->data->user_email ) ? $patient->data->user_email : '';

									// Show warning only if one of the required fields is empty
									if ( empty($first_name) || empty($last_name) || empty($email) ) { ?>
										<div class="col-12">
											<div class="alert alert-warning mb-2">
												<?php echo esc_html__( 'Your account data is not complete yet. Please fill in all required fields.', 'doctor2go-connect' ); ?>
											</div>
											<input type="hidden" value="update_user" name="user_action" id="user_action">
										</div>
									<?php } else { ?>
										<input type="hidden" value="none" name="user_action" id="user_action">
									<?php }
								} else { ?>
									<input type="hidden" value="none" name="user_action" id="user_action">
								<?php } ?>
								<div class="col-md-6">
									<input autocomplete="off" type="text" class="form-control myrequired noMargBot" id="patient_fname"
										value="<?php echo esc_html( $patient_meta['first_name'][0] ); ?>"
										placeholder="<?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?> *">
								</div>
								<div class="col-md-6">
									<input autocomplete="off" type="text" class="form-control myrequired noMargBot" id="patient_lname"
										value="<?php echo esc_html( $patient_meta['last_name'][0] ); ?>"
										placeholder="<?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?> *">
								</div>
								<div class="col-md-6">
									<input autocomplete="off" type="text" class="form-control myrequired" id="patient_email"
										value="<?php echo esc_html( $patient->data->user_email ); ?>"
										placeholder="<?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?> *">
								</div>
								<div class="col-md-6">
									<input autocomplete="off" type="text" class="form-control" id="p_tel"
										value="<?php echo esc_html( $patient_meta['p_tel'][0] ); ?>"
										placeholder="<?php echo esc_html__( 'Tel', 'doctor2go-connect' ); ?>">
								</div>
							</div>
						</div>
					</div>
					<p class="text-muted mt-3"><?php echo esc_html__( '* These are mandatory fields', 'doctor2go-connect' ); ?></p>
					<div class="simple_hide bg-secondary bg-opacity-25 p-3 rounded mt-3">
						<input readonly type="text" id="wp_doc_id" class="form-control mb-2" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>">
						<input readonly type="text" id="wp_user_id" class="form-control mb-2" value="<?php echo esc_html( $patient->data->ID ); ?>">
						<input readonly type="text" id="location_id" class="form-control mb-2" value="">
						<input readonly type="text" id="start_str" class="form-control mb-2">
						<input readonly type="text" id="end_str" class="form-control mb-2">
						<input readonly type="text" id="hourly_price" class="form-control mb-2" value="">
						<input readonly type="text" id="vat" class="form-control mb-2" value="">
						<input readonly type="text" id="currency" class="form-control mb-2" value="">
						<input readonly type="text" id="questionnaire" class="form-control mb-2" value="">
					</div>
					<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
						<div class="g-recaptcha my-3" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
						<div id="captcha_calendar"></div>
					<?php } ?>
					<?php wp_nonce_field( 'booking' ); ?>
					<input name='user[tel_number]' id="tel_number" type="checkbox" value="1" tabindex="-1" style="display:none" autocomplete="false"/>
					<div class="mt-3">
						<input id="submit_booking" type="submit" class="btn btn-primary" value="<?php esc_html_e( 'submit', 'doctor2go-connect' ); ?>">
						<div id="loader_booking" class="spinner-border text-primary ms-2" role="status" style="display:none;">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<a id="logged_out_cal_link" href="#logged_out_cal" class="fancybox simple_hide"></a>
	<div id="logged_out_cal" class="simple_hide">
		<h3 class="mb-m error"><?php echo esc_html__( 'To start your choosen consult, you first need to login or register an account.', 'doctor2go-connect' ); ?></h3>
		<div class="btn_wrapper center">
			<a class="btn btn-default button" href="<?php echo esc_html( $pageLogin ); ?>"><?php echo esc_html__( 'login', 'doctor2go-connect' ); ?></a>&nbsp;&nbsp;&nbsp;
			<a class="btn btn-default button" href="<?php echo esc_html( $pageRegis ); ?>"><?php echo esc_html__( 'register', 'doctor2go-connect' ); ?></a>
		</div>
	</div>
<?php }

// show confirmation boxes on register page + booking page
function d2g_confirmation_checkboxes( $form = '' ) {
	$currLang      = explode( '_', get_locale() )[0];
	$d2gAdmin      = new D2G_doc_user_profile();
	$pageDataPriv  = $d2gAdmin::d2g_page_url( $currLang, 'privacy_policy', true );
	$pageDataTerms = $d2gAdmin::d2g_page_url( $currLang, 'terms_and_conditions', true );
	$pageDataDiscl = $d2gAdmin::d2g_page_url( $currLang, 'disclaimer', true );
	?>
	<div id="conf_boxes">
	<p>
		<label for="conf_terms>"><input id="conf_terms<?php echo esc_html( $form ); ?>" name="meta[conf_terms]" type="checkbox" value="yes"> <?php echo esc_html__( 'I accept the terms and conditions.', 'doctor2go-connect' ); ?></label>&nbsp;&nbsp;&nbsp;&nbsp; 
		<a target="_blank" href="<?php echo esc_html( $pageDataTerms['url'] ); ?>"><?php echo esc_html__( 'view the terms & conditions', 'doctor2go-connect' ); ?></a>
	</p>
	<p>
		<label for="conf_privacy>"><input id="conf_privacy<?php echo esc_html( $form ); ?>" name="meta[conf_privacy]" type="checkbox" value="yes"> <?php echo esc_html__( 'I accept the privacy rules.', 'doctor2go-connect' ); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
		<a target="_blank" href="<?php echo esc_html( $pageDataPriv['url'] ); ?>"><?php echo esc_html__( 'view the privacy rules', 'doctor2go-connect' ); ?></a>
	</p>
	<p>
		<label for="conf_disclaimer>"><input id="conf_disclaimer<?php echo esc_html( $form ); ?>" name="meta[conf_disclaimer]" type="checkbox" value="yes"> <?php echo esc_html__( 'I accept the disclaimer.', 'doctor2go-connect' ); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
		<a target="_blank" href="<?php echo esc_html( $pageDataDiscl['url'] ); ?>"><?php echo esc_html__( 'view the disclaimer', 'doctor2go-connect' ); ?></a>
	</p>
	</div>
	<?php
}

// this creates the back to overview button for on the doctor detail pages
function d2g_show_back_btn() {
	$currLang   = explode( '_', get_locale() )[0];
	$d2gAdmin   = new D2G_doc_user_profile();
	$doctorsURL = $d2gAdmin::d2g_page_url( $currLang, 'doctors', false );
	?>
	<div class="btn_wrapper center mb-l mt-l">    
		<a id="backLink" class="btn btn-secondary wp-block-button__link" href="<?php echo esc_url( $doctorsURL ); ?>"><?php echo esc_html__( 'back to overview', 'doctor2go-connect' ); ?></a>
	</div>
	
	<?php
}

/**
 * @param $objects
 * @return array from taxonmy objects in only key value pairs
 */
function prepMyArray( $objects ) {
	$prepArray = array();
	foreach ( $objects as $object ) {
		$prepArray[ $object->slug ] = $object->name;
	}
	return $prepArray;
}

// this will create the walkin form
function d2g_show_walkin_form() {
	global $d2g_profile_data;

	// countries

	$allCountries      = get_terms(
		array(
			'taxonomy'   => 'country-origin',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	$allCountriesArray = ( $allCountries !== false ) ? prepMyArray( $allCountries ) : '';

	$site_key = get_option( 'd2g_recaptcha_site_key' );
	if ( is_user_logged_in() ) {
		$currUser   = wp_get_current_user();
		$currUserID = $currUser->ID;
		$userMeta   = get_user_meta( $currUserID );
		// nice_dump($currUser);
	} else {
		$detail_link    = get_the_permalink();
		$d2gAdmin       = new D2G_doc_user_profile();
		$currLang       = explode( '_', get_locale() )[0];
		$pageLogin      = $d2gAdmin::d2g_page_url( $currLang, 'login', true );
		$pageRegis      = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true );
		$action_buttons = array(
			'link_login' => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=walk_in_link' ),
			'link_regis' => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=walk_in_link' ),
		);
	}

	// nice_dump($d2g_profile_data);
	?>
	<div id="inloop" class="walkin_form_wrapper">
		<h3 class="section_title"><?php echo esc_html__( 'Walk-in consultation', 'doctor2go-connect' ); ?></h3>
		<span class="price_wrapper">
			<p style="margin-bottom: 2px;"><?php echo esc_html__( 'Consultation fee:', 'doctor2go-connect' ); ?></p>
			<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['walk_in_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['walk_in_price'][0] ); ?></strong>
		</span>
		<div class="alert alert-light info_notes mb-3">
		<?php
		echo esc_html__(
			'This doctor is currently available for a walk-in consultation.
			Please complete the form and click “Pay and Continue” to proceed.
			After your payment is confirmed, you’ll be placed in the waiting room. If others are ahead of you, a short wait may apply.','doctor2go-connect')?>
		</div>
		
		<div class="error simple_hide" id="walkin_error"></div>
		<div class="walkin_form_inner_wrapper mb-s">
			<form id="walkin_form" method="post" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'walkin_form_action', 'walkin_form_nonce' ); ?>
				<input type="hidden" name="wp_doc_id" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>"> 
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="client_name"><?php echo esc_html__( 'Patient name', 'doctor2go-connect' ); ?> *</label>
							<input class="required_walk form-control" type="text" value="<?php echo esc_html( $userMeta['first_name'][0] . ' ' . $userMeta['last_name'][0] ); ?>" name="client_name" id="client_name">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="client_email"><?php echo esc_html__( 'Patient email', 'doctor2go-connect' ); ?> *</label>
							<input class="required_walk form-control" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="optie_telefoonnummer"><?php echo esc_html__( 'Patient phone', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="text" value="<?php echo esc_html( $userMeta['p_tel'][0] ); ?>" name="optie_telefoonnummer" id="optie_telefoonnummer">
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="optie_geboortedatum"><?php echo esc_html__( 'Date of Birth: day/month/year  ', 'doctor2go-connect' ); ?> *</label>
							<input class="required_walk form-control" type="date"  name="optie_geboortedatum" id="optie_geboortedatum">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="optie_aanhef"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
							<select name="optie_aanhef form-select" id="optie_aanhef">
								<option value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
								<option value="male"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
								<option value="female"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
								<option value="other"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label"><?php echo esc_html__( 'Country', 'doctor2go-connect' ); ?></label>
							<select name="optie_land">
								<option value="0"><?php echo esc_html__( 'Country', 'doctor2go-connect' ); ?></option>
								<?php
								$current_theme = wp_get_theme();
								$theme_id      = $current_theme->get( 'Template' );
								if ( $theme_id == 'wcc-doclisting' ) {
									foreach ( $allCountries as $country ) {
										$selected = '';
										if ( isset( $countriesArray[ $country->slug ] ) ) {
											$selected = 'selected';
										}
										?>
										<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $country->slug ); ?>"><?php echo ( pll_current_language() == 'en' ) ? esc_html( $country->name ) : esc_html( get_term_meta( $country->term_id, 'rudr_text_' . pll_current_language(), true ) ); ?></option>
										<?php
									}
								} else {
									foreach ( $allCountriesArray as $slug => $name ) {
										$selected = '';
										if ( isset( $countriesArray[ $slug ] ) ) {
											$selected = 'selected';
										}
										?>
										<option <?php echo esc_html( $selected ); ?> value="<?php echo esc_html( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>  
				</div>
				<div class="mb-3">
					<label for="optie_reason" class="form-label"><?php echo esc_html__( 'Reason for consult', 'doctor2go-connect' ); ?>*</label>
					<textarea class="required_walk form-control w-100" name="optie_reason" rows="3" id="optie_reason"></textarea>
				</div>
				<div class="mb-3">
					<?php if ( ! is_user_logged_in() ) { ?>
						<?php d2g_confirmation_checkboxes( '_wf' ); ?>
					<?php } ?>
					<!-- reCAPTCHA Widget -->
					<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
						<div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
						<div id="captcha_walkin"></div>
					<?php } ?>
				</div>
				<p class="mb-3"><button class="btn btn-primary wp-block-button__link request_walkin button" tabindex="6" id="save"><?php esc_html_e( 'pay and continue', 'doctor2go-connect' ); ?></button></p>
				<p><?php esc_html_e( 'After your payment goes through, you’ll enter the waiting room. The doctor will begin the consultation shortly', 'doctor2go-connect' ); ?></p>
			</form>
		</div>
		<p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
	</div>
<?php }


// this will create the walkin form
function d2g_show_written_con_form() {
	global $d2g_profile_data;

	if ( get_option( 'd2g_use_imgix' ) == 1 ) {
		$doc_pic = $d2g_profile_data->feat_pic_full . '&w=120&h=120&fit=crop&crop=faces&auto=format,compress';
	} else {
		$doc_pic = $d2g_profile_data->feat_pic_square;
	}

	$site_key = get_option( 'd2g_recaptcha_site_key' );
	if ( is_user_logged_in() ) {
		$currUser   = wp_get_current_user();
		$currUserID = $currUser->ID;
		$userMeta   = get_user_meta( $currUserID );
	} else {
		$detail_link    = get_the_permalink();
		$d2gAdmin       = new D2G_doc_user_profile();
		$currLang       = explode( '_', get_locale() )[0];
		$pageLogin      = $d2gAdmin::d2g_page_url( $currLang, 'login', true );
		$pageRegis      = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true );
		$action_buttons = array(
			'link_login' => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
			'link_regis' => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
		);
	}
	?>
	
	<div id="written_consult" class="walkin_form_wrapper">
		<h3 class="section_title"><?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?></h3>
		<span class="price_wrapper">
			<p style="margin-bottom: 2px;"><?php echo esc_html__( 'Consultation fee:', 'doctor2go-connect' ); ?></p>
			<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong>
		</span>
		<div class="alert alert-light info_notes mb-3">
			<p><strong><?php echo esc_html__( 'Obtain a professional assessment from a certified dermatologist by email within two working days through a straightforward three-step process.', 'doctor2go-connect' ); ?></strong></p>
			<div><span class="flaticon-personal-information icon"></span><span><strong>1. </strong><?php echo esc_html__( 'Enter your personal information and describe your complaint', 'doctor2go-connect' ); ?></span></div>
			<div><span class="flaticon-credit-card icon"></span><span><strong>2. </strong><?php echo esc_html__( 'Click pay and continue, you will be redirected to the payment page.', 'doctor2go-connect' ); ?></span></div>
			<div><span class="icon-mail-1 icon"></span><span><strong>3. </strong><?php echo esc_html__( 'After payment, you will receive your assessment within 2 working days.', 'doctor2go-connect' ); ?></span></div>
		</div>
		<div class="alert alert-danger simple_hide" id="written_con_error"></div>
		<div class="walkin_form_inner_wrapper mb-s">
			<form id="written_con_form" method="post" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'email_advice_form_action', 'email_advice_form_nonce' ); ?>
				<input type="hidden" name="wp_doc_id" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>"> 
				<div class="row mb-3 simple_hide">
					<div class="col-sm-12">
						<div>
							<input id="type_small" class="required_wc form-control" type="radio"  value="short" name="type" checked>
							<label for="type_small"><?php echo esc_html__( 'Short Questionnaire – for simple or minor skin issues', 'doctor2go-connect' ); ?></label>
						</div>
					</div>
					<div class="col-sm-12">
						<div>
<input id="type_default" class="required_wc form-control" type="radio"  value="default" name="type">
						<label class="form-label" for="type_default"><?php echo esc_html__( 'Extended Questionnaire – for complex or multiple skin concerns', 'doctor2go-connect' ); ?></label>
							
						</div>	
					</div>
				</div>
				<legend class="fs-5 mb-3">
					<strong><?php echo esc_html__('Personal information', 'doctor2go-connect')?></strong>
				</legend>
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['first_name'][0] ); ?>" name="first_name" id="first_name">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['last_name'][0] ); ?>" name="last_name" id="last_name">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label for="client_email"><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email_ec">
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="option_bday"><?php echo esc_html__( 'Date of Birth: day/month/year  ', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="date"  name="option_bday" id="option_bday">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="optie_aanhef"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
							<select name="optie_aanhef form-select" id="optie_aanhef">
								<option value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
								<option value="male"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
								<option value="female"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
								<option value="other"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						
					</div>  
				</div>
				<!-- Over de huidaandoening -->
                <fieldset class="mb-4">
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('About the skin condition', 'doctor2go-connect')?></strong>
                    </legend>
					<div class="row mb-3">
						<div class="col-md-4">
							<label for="image_1" class="form-label"><?php echo esc_html__( 'Upload image 1', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_1" id="image_1" accept="image/*">
						</div>
						<div class="col-md-4">
							<label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_2" id="image_2" accept="image/*">
						</div>
						<div class="col-md-4">
							<label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_3" id="image_3" accept="image/*">
						</div>
					</div>
                    <!-- Beschrijving / eerste opgemerkt -->
                    <div class="mb-3">
                        <label for="beschrijf_de_klacht" class="form-label">
                            <?php echo esc_html__('Describe the complaint', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="opgemerkt" class="form-label">
                            <?php echo esc_html__('When did you first notice this spot?', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="opgemerkt" name="first_noticed" class="form-control required_wc" rows="2" placeholder="<?php echo esc_attr__('For example: 2 weeks ago...', 'doctor2go-connect')?>"></textarea>
                    </div>

                    <!-- Locatie / veranderd -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="locatie" class="form-label">
                                <?php echo esc_html__('Location on the body', 'doctor2go-connect')?> *
                            </label>
                            <input type="text" id="locatie" name="location" class="form-control required_wc" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                        </div>
                        <div class="col-md-6">
                            <label for="veranderd" class="form-label">
                                <?php echo esc_html__('Has the spot changed?', 'doctor2go-connect')?>
                            </label>
                            <select id="veranderd" name="has_changed" class="form-select">
                                <option value="<?php echo esc_html__('no', 'doctor2go-connect')?>"><?php echo esc_html__('No', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('yes, in size', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in size', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('Yes, in color', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in color', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('Yes, in shape', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in shape', 'doctor2go-connect')?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Symptomen switches -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="jeuk" name="itch_check" value="1" role="switch">
                                <label class="form-check-label" for="jeuk">
                                    <?php echo esc_html__('Does the skin condition itch?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bloed" name="blood_check" value="1" role="switch">
                                <label class="form-check-label" for="bloed">
                                    <?php echo esc_html__('Does the skin condition bleed?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                    </div>
					<div class="mb-3">
                        <label for="history" class="form-label">
                            <?php echo esc_html__('Medical history (skin cancer)', 'ai-derma-plugin')?>
                        </label>
                        <textarea id="history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                    </div>
                </fieldset>
				<div class="mb-3">
					<!-- reCAPTCHA Widget -->
					<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
						<div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
						<div id="captcha_email"></div>
					<?php } ?>
				</div>
				<?php if ( ! is_user_logged_in() ) { ?>
					<?php d2g_confirmation_checkboxes( '_ea' ); ?>
				<?php } ?>
				<div class="mb-4 d-flex align-items-center">
					<button class="btn btn-primary wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e( 'continue and pay', 'doctor2go-connect' ); ?></button>
					<div id="loader" class="spinner-border text-primary ms-2" role="status" style="display:none;">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
			</form>
		</div>
		<p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
	</div>
<?php }


// this will create the walkin form
function d2g_show_generic_written_con_form() {
	global $d2g_profile_data;

	if ( get_option( 'd2g_use_imgix' ) == 1 ) {
		$doc_pic = $d2g_profile_data->feat_pic_full . '&w=120&h=120&fit=crop&crop=faces&auto=format,compress';
	} else {
		$doc_pic = $d2g_profile_data->feat_pic_square;
	}

	$site_key = get_option( 'd2g_recaptcha_site_key' );
	if ( is_user_logged_in() ) {
		$currUser   = wp_get_current_user();
		$currUserID = $currUser->ID;
		$userMeta   = get_user_meta( $currUserID );
	} else {
		$detail_link    = get_the_permalink();
		$d2gAdmin       = new D2G_doc_user_profile();
		$currLang       = explode( '_', get_locale() )[0];
		$pageLogin      = $d2gAdmin::d2g_page_url( $currLang, 'login', true );
		$pageRegis      = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true );
		$action_buttons = array(
			'link_login' => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
			'link_regis' => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
		);
	}
	?>
	
	<div id="written_consult" class="walkin_form_wrapper">
		<h3 class="section_title"><?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?></h3>
		<span class="price_wrapper">
			<p style="margin-bottom: 2px;"><?php echo esc_html__( 'Consultation fee:', 'doctor2go-connect' ); ?></p>
			<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong>
		</span>
		<div class="alert alert-light info_notes mb-3">
			<p><strong><?php echo esc_html__( 'Obtain a professional assessment from a certified dermatologist by email within two working days through a straightforward three-step process.', 'doctor2go-connect' ); ?></strong></p>
			<div><span class="flaticon-personal-information icon"></span><span><strong>1. </strong><?php echo esc_html__( 'Enter your personal information and describe your complaint', 'doctor2go-connect' ); ?></span></div>
			<div><span class="flaticon-credit-card icon"></span><span><strong>2. </strong><?php echo esc_html__( 'Click pay and continue, you will be redirected to the payment page.', 'doctor2go-connect' ); ?></span></div>
			<div><span class="icon-mail-1 icon"></span><span><strong>3. </strong><?php echo esc_html__( 'After payment, you will receive your assessment within 2 working days.', 'doctor2go-connect' ); ?></span></div>
		</div>
		<div class="alert alert-danger simple_hide" id="written_con_error"></div>
		<div class="walkin_form_inner_wrapper mb-s">
			<form id="written_con_form" method="post" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'email_advice_form_action', 'email_advice_form_nonce' ); ?>
				<input type="hidden" name="wp_doc_id" value="<?php echo esc_html( $d2g_profile_data->doctor_profile_ID ); ?>"> 
				<div class="row mb-3 simple_hide">
					<div class="col-sm-12">
						<div>
							<input id="type_small" class="required_wc form-control" type="radio"  value="short" name="type" checked>
							<label for="type_small"><?php echo esc_html__( 'Short Questionnaire – for simple or minor skin issues', 'doctor2go-connect' ); ?></label>
						</div>
					</div>
					<div class="col-sm-12">
						<div>
<input id="type_default" class="required_wc form-control" type="radio"  value="default" name="type">
						<label class="form-label" for="type_default"><?php echo esc_html__( 'Extended Questionnaire – for complex or multiple skin concerns', 'doctor2go-connect' ); ?></label>
							
						</div>	
					</div>
				</div>
				<legend class="fs-5 mb-3">
					<strong><?php echo esc_html__('Personal information', 'doctor2go-connect')?></strong>
				</legend>
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label for="first_name"><?php echo esc_html__( 'First name', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['first_name'][0] ); ?>" name="first_name" id="first_name">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label for="last_name"><?php echo esc_html__( 'Last name', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $userMeta['last_name'][0] ); ?>" name="last_name" id="last_name">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label for="client_email"><?php echo esc_html__( 'E-mail', 'doctor2go-connect' ); ?> *</label>
							<input class="required_wc form-control" type="text" value="<?php echo esc_html( $currUser->data->user_email ); ?>" name="client_email" id="client_email_ec">
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="option_bday"><?php echo esc_html__( 'Date of Birth: day/month/year  ', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="date"  name="option_bday" id="option_bday">
						</div>
					</div>
					<div class="col-sm-4">
						<div>
							<label class="form-label" for="optie_aanhef"><?php echo esc_html__( 'Gender', 'doctor2go-connect' ); ?></label>
							<select name="optie_aanhef form-select" id="optie_aanhef">
								<option value="0"><?php echo esc_html__( 'make a choice', 'doctor2go-connect' ); ?></option>
								<option value="male"><?php echo esc_html__( 'male', 'doctor2go-connect' ); ?></option>
								<option value="female"><?php echo esc_html__( 'female', 'doctor2go-connect' ); ?></option>
								<option value="other"><?php echo esc_html__( 'other', 'doctor2go-connect' ); ?></option>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						
					</div>  
				</div>
				<!-- Over de huidaandoening -->
                <fieldset class="mb-4">
                    <legend class="fs-5 mb-3">
                        <strong><?php echo esc_html__('About the skin condition', 'doctor2go-connect')?></strong>
                    </legend>
					<div class="row mb-3">
						<div class="col-md-4">
							<label for="image_1" class="form-label"><?php echo esc_html__( 'Upload image 1', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_1" id="image_1" accept="image/*">
						</div>
						<div class="col-md-4">
							<label for="image_2" class="form-label"><?php echo esc_html__( 'Upload image 2', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_2" id="image_2" accept="image/*">
						</div>
						<div class="col-md-4">
							<label for="image_3" class="form-label"><?php echo esc_html__( 'Upload image 3', 'doctor2go-connect' ); ?></label>
							<input class="form-control" type="file" name="image_3" id="image_3" accept="image/*">
						</div>
					</div>
                    <!-- Beschrijving / eerste opgemerkt -->
                    <div class="mb-3">
                        <label for="beschrijf_de_klacht" class="form-label">
                            <?php echo esc_html__('Describe the complaint', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="beschrijf_de_klacht" name="complaint_description" class="form-control required_wc" rows="3" placeholder="<?php echo esc_attr__('For example: itchy red spots or bumps...', 'doctor2go-connect')?>"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="opgemerkt" class="form-label">
                            <?php echo esc_html__('When did you first notice this spot?', 'doctor2go-connect')?> *
                        </label>
                        <textarea id="opgemerkt" name="first_noticed" class="form-control required_wc" rows="2" placeholder="<?php echo esc_attr__('For example: 2 weeks ago...', 'doctor2go-connect')?>"></textarea>
                    </div>

                    <!-- Locatie / veranderd -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="locatie" class="form-label">
                                <?php echo esc_html__('Location on the body', 'doctor2go-connect')?> *
                            </label>
                            <input type="text" id="locatie" name="location" class="form-control required_wc" placeholder="<?php echo esc_attr__('For example: left forearm', 'doctor2go-connect')?>">
                        </div>
                        <div class="col-md-6">
                            <label for="veranderd" class="form-label">
                                <?php echo esc_html__('Has the spot changed?', 'doctor2go-connect')?>
                            </label>
                            <select id="veranderd" name="has_changed" class="form-select">
                                <option value="<?php echo esc_html__('no', 'doctor2go-connect')?>"><?php echo esc_html__('No', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('yes, in size', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in size', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('Yes, in color', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in color', 'doctor2go-connect')?></option>
                                <option value="<?php echo esc_html__('Yes, in shape', 'doctor2go-connect')?>"><?php echo esc_html__('Yes, in shape', 'doctor2go-connect')?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Symptomen switches -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="jeuk" name="itch_check" value="1" role="switch">
                                <label class="form-check-label" for="jeuk">
                                    <?php echo esc_html__('Does the skin condition itch?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bloed" name="blood_check" value="1" role="switch">
                                <label class="form-check-label" for="bloed">
                                    <?php echo esc_html__('Does the skin condition bleed?', 'doctor2go-connect')?>
                                </label>
                            </div>
                        </div>
                    </div>
					<div class="mb-3">
                        <label for="history" class="form-label">
                            <?php echo esc_html__('Medical history (skin cancer)', 'ai-derma-plugin')?>
                        </label>
                        <textarea id="history" name="medical_history" class="form-control" rows="3" placeholder="<?php echo esc_attr__('If applicable...', 'ai-derma-plugin')?>"></textarea>
                    </div>
                </fieldset>
				<div class="mb-3">
					<!-- reCAPTCHA Widget -->
					<?php if ( get_option( 'd2g_recaptcha_site_key' ) ) { ?>
						<div class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
						<div id="captcha_email"></div>
					<?php } ?>
				</div>
				<?php if ( ! is_user_logged_in() ) { ?>
					<?php d2g_confirmation_checkboxes( '_ea' ); ?>
				<?php } ?>
				<div class="mb-4 d-flex align-items-center">
					<button class="btn btn-primary wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e( 'continue and pay', 'doctor2go-connect' ); ?></button>
					<div id="loader" class="spinner-border text-primary ms-2" role="status" style="display:none;">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
			</form>
		</div>
		<p><?php echo esc_html__( '* required fields.', 'doctor2go-connect' ); ?></p>
	</div>
<?php }


function d2g_show_consultancy_tabs($post = '', $stand_alone = false){ 
	if ( $post == '' ) {
		global $d2g_profile_data;
	} else {
		global $d2g_profile_data;
		$d2g_profile_data = new D2G_ProfileData( $post, true );
	}
	$post_ID		 = $d2g_profile_data->doctor_profile_ID;
	?>
	<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
		<?php if ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ) {?>
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-tab-pane" type="button" role="tab" aria-controls="email-tab-pane" aria-selected="true">
					<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong><br>
					<?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?>
				</button>
			</li>
		<?php } ?>
		<li class="nav-item" role="presentation">
			<button class="nav-link calendar_button simple_hide" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-tab-pane" type="button" role="tab" aria-controls="calendar-tab-pane" aria-selected="false">
				<strong class="fillup_<?php echo esc_html( $post_ID ); ?>"><?php echo  $d2g_profile_data->doctor_meta['d2g_tariffs'][0] ; ?></strong><br><?php echo esc_html__( 'Video consult', 'doctor2go-connect' ); ?>
			</button>
		</li>
		<?php if ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) { ?>
			<li class="nav-item  walk_in_button simple_hide" role="presentation">
				<button class="nav-link" id="walkin-tab" data-bs-toggle="tab" data-bs-target="#walkin-tab-pane" type="button" role="tab" aria-controls="walkin-tab-pane" aria-selected="false">
					<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['walk_in_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['walk_in_price'][0] ); ?></strong><br>
					<?php echo esc_html__( 'Walk-in', 'doctor2go-connect' ); ?>
				</button>
			</li>
		<?php } ?>
	</ul>

	<div class="tab-content mb-5" id="myTabContent">
		<?php if ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ) {?>
			<div class="tab-pane fade show active" id="email-tab-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
				<?php do_action( 'd2g_doctor_written_con_form' ); ?>
			</div>
		<?php } ?>
		<div class="tab-pane fade" id="calendar-tab-pane" role="tabpanel" aria-labelledby="calendar-tab" tabindex="0">
			<?php if($stand_alone === true){
				d2g_show_booking_calendar( $post, true, true );
			} else {
				do_action( 'd2g_booking_calendar' );
			} ?>
		</div>
		<?php if ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) {?>
			<div class="tab-pane fade" id="walkin-tab-pane" role="tabpanel" aria-labelledby="walkin-tab" tabindex="0">
				<?php do_action( 'd2g_doctor_walkin_form' );?>
			</div>
		<?php } ?>
	</div>
<?php }


// this will create the buttons on the doctor detail page and hide them when they are not needed
function d2g_show_consult_buttons( $template = '', $size = '' ) {
	global $d2g_profile_data;
	$detail_link    = get_the_permalink();
	$post_id        = get_the_ID();
	$d2gAdmin       = new D2G_doc_user_profile();
	$currLang       = explode( '_', get_locale() )[0];
	$pageLogin      = $d2gAdmin::d2g_page_url( $currLang, 'login', true );
	$pageRegis      = $d2gAdmin::d2g_page_url( $currLang, 'patient_registration', true );
	$location_check = array();
	$holiday        = false;

	if ( $d2g_profile_data->doctor_meta['locations_to_go'][0] ) {
		$location_check = unserialize( $d2g_profile_data->doctor_meta['locations_to_go'][0] );
	}

	if ( $d2g_profile_data->doctor_meta['end_holiday'][0] != '' && $d2g_profile_data->doctor_meta['start_holiday'][0] != '' ) {
		$start = new DateTime( $d2g_profile_data->doctor_meta['start_holiday'][0] );
		$end   = new DateTime( $d2g_profile_data->doctor_meta['end_holiday'][0] );
		$check = new DateTime();

		// Only compare the date part (ignore time)
		$check->setTime( 0, 0, 0 );
		$start->setTime( 0, 0, 0 );
		$end->setTime( 0, 0, 0 );

		if ( $check >= $start && $check <= $end ) {
			$holiday = true;
		}
	}

	$consult_buttons = array(
		
		'walk_in'      => array(
			'image'       => ( $size == 'small' ) ? 'walk-in-small.png' : 'walk-in.png',
			'name'        => __( 'Walk-in Consult', 'doctor2go-connect' ),
			'show'        => ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) ? true : false,
			'li_class'    => 'simple_hide',
			'a_class'     => ( $template == 'detail' ) ? 'fancybox variant' : 'variant',
			'a_class_out' => 'fancybox',
			'link'        => ( $template == 'detail' ) ? '#inloop' : $detail_link . '?open=walk_in_link',
			'price'       => $d2g_profile_data->doctor_meta['walk_in_price'][0],
			'currency'    => $d2g_profile_data->doctor_meta['walk_in_currency'][0],
			'link_login'  => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=walk_in_link' ),
			'link_regis'  => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=walk_in_link' ),
			'price_class' => '',
		),
		'written_con'  => array(
			'image'       => ( $size == 'small' ) ? 'written-consult-small.png' : 'written-consult.png',
			'name'        => __( 'E-mail advice', 'doctor2go-connect' ),
			'show'        => ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' && $holiday == false ) ? true : false,
			'li_class'    => '',
			'a_class'     => ( $template == 'detail' ) ? 'fancybox' : '',
			'a_class_out' => 'fancybox',
			'link'        => ( $template == 'detail' ) ? '#written_consult' : $detail_link . '?open=written_con_link',
			'price'       => $d2g_profile_data->doctor_meta['written_con_price'][0],
			'currency'    => $d2g_profile_data->doctor_meta['written_con_currency'][0],
			'link_login'  => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
			'link_regis'  => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?open=written_con_link' ),
			'price_class' => '',
		),
		
		'physical_con' => array(
			'image'       => ( $size == 'small' ) ? 'physical-consult-small.png' : 'physical-consult.png',
			'name'        => __( 'Physical Consult', 'doctor2go-connect' ),
			'show'        => ( count( $location_check ) > 0 ) ? true : false,
			'li_class'    => 'simple_hide booking_con',
			'a_class'     => ( $template == 'detail' ) ? 'scroll_to variant' : 'variant',
			'a_class_out' => 'fancybox variant',
			'link'        => ( $template == 'detail' ) ? '#calendar_wrapper' : $detail_link . '?scroll_to=calendar_wrapper',
			'price'       => '0',
			'currency'    => '',
			'link_login'  => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?scroll_to=calendar_wrapper' ),
			'link_regis'  => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?scroll_to=calendar_wrapper' ),
			'price_class' => 'fillup',
		),
		'video_con'    => array(
			'image'       => ( $size == 'small' ) ? 'video-consult-small.png' : 'video-consult.png',
			'name'        => __( 'Video Consult', 'doctor2go-connect' ),
			'show'        => true,
			'li_class'    => 'simple_hide booking_con',
			'a_class'     => ( $template == 'detail' ) ? 'scroll_to variant' : 'variant',
			'a_class_out' => 'scroll_to variant',
			'link'        => ( $template == 'detail' ) ? '#calendar_wrapper' : $detail_link . '#calendar_wrapper',
			'price'       => '0',
			'currency'    => '',
			'link_login'  => $pageLogin['url'] . '?redirect_to=' . urlencode( $detail_link . '?scroll_to=calendar_wrapper' ),
			'link_regis'  => $pageRegis['url'] . '?redirect_to=' . urlencode( $detail_link . '?scroll_to=calendar_wrapper' ),
			'price_class' => 'fillup',
		),

	);
	?>
	
	<ul class="consult_buttons d-flex list-unstyled">
		<?php
		foreach ( $consult_buttons as $id => $button ) {
			if ( ( ! is_user_logged_in() ) && $id != 'walk_in' && $id != 'show_doc' && $id != 'written_con' && $id != 'video_con' ) {
				$myLink = '#' . $id . '_message_' . $post_id;
			} else {
				$myLink = $button['link'];
			}
			?>
			<?php if ( $button['show'] == true ) { ?> 
				<li id="<?php echo esc_html( $id ); ?>_button" class="<?php echo esc_html( $id ); ?>_button <?php echo esc_html( $button['li_class'] ); ?>">
					<a id="<?php echo esc_html( $id . '_link' ); ?>" class="<?php echo esc_html( $id . '_link' ); ?> btn btn-primary button <?php echo ( is_user_logged_in() ) ? esc_html( $button['a_class'] ) : esc_html( $button['a_class_out'] ); ?>" href="<?php echo esc_url( $myLink ); ?>">
						<div class="image"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/' . $button['image'] ); ?>"></div>
						<div class="name"><?php echo esc_html( $button['name'] ); ?></div>
						<div class="price <?php echo esc_html( $button['price_class'] . '_' . get_the_ID() ); ?>" id="price_<?php echo esc_html( $id ); ?>"><?php echo esc_html( $button['currency'] . '&nbsp;' . $button['price'] ); ?></div>
					</a>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
	<div class="consult_buttons info_btn_wrapper">
		<a href="#info_content" class="fancybox link">
			<span class="icon-info"></span>
			<span class="link_name"><?php echo esc_html__( 'More info about the consultation types', 'doctor2go-connect' ); ?></span>
		</a>
	</div>
	<?php foreach ( $consult_buttons as $id => $button ) { ?>
		<?php if ( $id != 'show_doc' && $id != 'walk_in' ) { ?>
			<div class="simple_hide" id="<?php echo esc_html( $id . '_message_' . $post_id ); ?>" style="max-width:400px;">
				<h3 class="mb-m error center"><?php echo esc_html__( 'To start your choosen consult, you first need to login or register an account.', 'doctor2go-connect' ); ?></h3>
				<div class="btn_wrapper center">
					<a class="btn btn-default button" href="<?php echo esc_url( $button['link_login'] ); ?>"><?php echo esc_html__( 'login', 'doctor2go-connect' ); ?></a>&nbsp;&nbsp;&nbsp;
					<a class="btn btn-default button" href="<?php echo esc_url( $button['link_regis'] ); ?>"><?php echo esc_html__( 'register', 'doctor2go-connect' ); ?></a>
				</div>
			</div>
		<?php } ?>
		<?php
	}
}


function d2g_single_appointment($appointment, $docObj, $client_token, $timezone, $currLang, $d2gAdmin, $show_intake, $doc_email = ''){
	// doctor image
	if(get_option('d2g_use_imgix') == 1){
		$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( $docObj->ID ), 'full' )[0].'&w=200&h=200&fit=crop&crop=faces';
	} else {
		$feat_pic = wp_get_attachment_image_src( get_post_thumbnail_id( $docObj->ID ), 'thumbnail' )[0];
	}
	
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
	$delBtn             = '';
	$payment_link		= '';
	$payment_info		= '';
	$pageAppConf 		= $d2gAdmin::d2g_page_url( $currLang, 'appointment_confirmation', false );
	$termsPageURL 		= $d2gAdmin::d2g_page_url( $currLang, 'd2g_policies', false );
	$termsLink			= '<a href=\"'.$termsPageURL.'\">'. esc_html__( 'View terms & conditions.', 'doctor2go-connect' ) . '</a>';
	if ( isset( $appointment->answer_set_id ) && $show_intake == true ) {
		$questionnaireLink = '<a class="btn btn-outline-primary payment_btn w-100 mb-2" target="_blank" href="'.$pageAppConf.'?app='.$appointment->_id.'&client_token='.$client_token.'"><span class="flaticon-medical-information"></span> '.esc_html__( 'intake quesionnaire', 'doctor2go-connect' ).'</a>';
	}
	$consultLink 		= '<a class="button btn-primary btn invert mb-2 w-100" target="_blank" href="' . get_option( 'waiting_room_url' ) . 'wachtkamer/' . $appointment->token . '?locale=' . explode( '_', get_locale() )[0] . '"><span class=" icon-videocam-outline"></span> ' . esc_html__( 'go to consultation', 'doctor2go-connect' ) . '</a>';
	$contactBtn      	= '<a class="prep_cancellation_email btn-outline-secondary btn scroll_to w-100 fancybox_spec " href="#cancellation_form_wrapper" data-app-date="'.$date->format("d/m/Y").' '. esc_html__(' at ', 'd2g-connect').' ' .$date->format("H:i").'  ('.$timezone.')" data-app-link="'.get_option('waiting_room_url').'admin/appointments/'.$appointment->_id.'" data-doc-email="'.$doc_email.'" data-doc-name="'.$docObj->post_title.'"><span class=" icon-mail"></span> '. esc_html__('contact doctor', 'd2g-connect').'</a>';
	
	if ( $diffInSeconds <= 0 || $diffInSeconds > 86400 ) {
		$delBtn 		= '<a class="del_app button btn-danger btn w-100 mb-2" href="#" data-app-id="' . $appointment->_id . '" data-user-id="' . $appointment->user_id . '"><span class=" icon-cancel-circled"></span> ' . esc_html__( 'cancel appointment', 'doctor2go-connect' ) . '<span class="btn-spinner spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span></a>';
	} else {
		$delBtn 		= '<div class="icon-info-outline alert alert-danger w-100 mb-2 p-btn text-center" 
		data-bs-toggle="tooltip" 
		data-bs-custom-class="custom-tooltip" 
		data-bs-placement="top" 
		data-bs-title="' . esc_attr__( 'Cancellations are generally permitted up to 24 hours before the scheduled appointment. Please review our cancellation and refund policy on the Terms and Conditions page.
		If the cancellation option is no longer available, you may still contact the doctor using the Contact Doctor button.', 'doctor2go-connect' ) . '">
		' . esc_html__( 'cancellation deactivated', 'doctor2go-connect' ) . '<br>' .$termsLink . '</div>';
	}

	if ( $appointment->payment_has_paid == true ) {
		$payment_info 	= '<div class="alert alert-success m-3 mb-0 icon-info-outline"><strong>' . esc_html__( 'Your appointment has been paid.', 'doctor2go-connect' ) . '</strong></div>';
		$delBtn 		= '<div class="icon-info-outline alert alert-danger w-100 mb-2 p-btn text-center" 
		data-bs-toggle="tooltip" 
		data-bs-custom-class="custom-tooltip" 
		data-bs-placement="top" 
		data-bs-title="' . esc_attr__( 'Your appointment has already been paid for; therefore, the cancellation option has been deactivated. Please refer to our Terms and Conditions page for details.
		If cancellation is deactivated, you can still contact the doctor using the Contact Doctor button to request a cancellation and refund.', 'doctor2go-connect' ) . '">
		' . esc_html__( 'cancellation deactivated', 'doctor2go-connect' ) . '<br>' . $termsLink . '</div>';
	} else {
		
		$redirectURL 	= '&redirect_url=' . urlencode( $pageAppConf . '?app=' . $appointment->_id . '&client_token=' . $client_token ) ;
		$payment_info 	= '<p class="payment_needed alert alert-warning m-3 mb-0 icon-info-outline" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="top" data-bs-title="' . esc_attr__( 'Payment may be made in advance or upon arrival at your appointment. Please note that appointments paid upfront cannot be cancelled.', 'doctor2go-connect' ) . '"><strong>' . esc_html__( 'A payment is required for this appointment.', 'doctor2go-connect' ) . '</strong></p>';
		$payment_link 	= '<a class="icon-cc-mastercard btn btn-secondary payment_btn w-100 mb-2" target="_blank" href="' . get_option( 'waiting_room_url' ) . 'payment/' . $appointment->_id . '?locale=' . explode( '_', get_locale() )[0] . $redirectURL . '"> ' . esc_html__( 'pay now', 'doctor2go-connect' ) . '</a>';
	}

	// create the appointment rows and save in array to sort them later
	if ( $appointment->location_to_go != null ) {
		$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper card mb-5"><div id="app-' . $appointment->_id . '" class="app_row d-flex align-items-center justify-content-between">
			<div class="feat_pic p-3"><img src="' . $feat_pic . '"></div>
			<div class="content_outer p-3">
				<div class="content">
					<p class="consult_type"><strong>' . esc_html__( 'Physical consultation', 'doctor2go-connect' ) . '</strong></p>
					<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
					<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
					<p class="address">' . $appointment->location_to_go->location_name . ': ' . $appointment->location_to_go->location_full_adress_url . '</p>
				</div> 
			</div>
			<div class="btn_wrap p-3">'. $payment_link . $delBtn . $questionnaireLink . $contactBtn . '</div>
			</div></div>';
	} else {
		$structuredAppointments[ $appointment->date ] = '<div class="outer_app_wrapper card mb-5">'.$payment_info.'<div id="app-' . $appointment->_id . '" class="app_row align-items-center d-flex justify-content-between">
			<div class="feat_pic p-3"><img src="' . $feat_pic . '"></div>
			<div class="content_outer p-3">
				<div class="content">
					<p class="consult_type"><strong>' . esc_html__( 'Online consultation', 'doctor2go-connect' ) . '</strong></p>
					<h3>' . $date->format( 'd/m/Y' ) . ' ' . esc_html__( ' at ', 'doctor2go-connect' ) . ' ' . $date->format( 'H:i' ) . '  <span class="small">(' . $timezone . ')</span></h3>
					<a href="' . get_the_permalink( $docObj->ID ) . '"><h4>' . $docObj->post_title . '</h4></a>
				</div> 	
			</div>
			<div class="btn_wrap p-3">'. $payment_link  . $consultLink  . $delBtn . $questionnaireLink . $contactBtn .'</div>
			</div></div>';
	}

	return $structuredAppointments;

	
}



function d2g_cancelation_request_form( $currUser, $user_meta ) {

    $first_name = isset( $user_meta['first_name'][0] ) && $user_meta['first_name'][0] !== '' ? $user_meta['first_name'][0] : '';
    $last_name  = isset( $user_meta['last_name'][0] ) && $user_meta['last_name'][0] !== '' ? $user_meta['last_name'][0] : '';
    $full_name  = trim( $first_name . ' ' . $last_name );
    $user_email = isset( $currUser->data->user_email ) && $currUser->data->user_email !== '' ? $currUser->data->user_email : '';

    ?>

<div id="cancellation_form_wrapper" class="simple_hide list_app walkin_form_wrapper mb-xl" style="max-width:500px">
	<div class="inner_wrapper">
		<h2 class="mb-3"><?php echo esc_html__( 'Contact:', 'doctor2go-connect' ); ?> <span id="doc_name_visible"></span></h2>
		<p class="alert alert-warning"><?php echo esc_html__( 'Please use this form only for appointment-related requests (for example, if your appointment has already been paid for but you wish to cancel or reschedule it).
		Kindly note that medical questions or requests for medical advice cannot be submitted through this form.', 'doctor2go-connect' ); ?></p>
		<div id="return1" class="simple_hide mb-m center"></div>
		<div id="return2" class="simple_hide mb-m center"></div>
		<form id="cancellation_form" method="post" action="">
			<div class="row mb-m">
				<div class="col-sm-6 mb-2">
					<div>
						<label for="client_name" class="form-label"><?php echo esc_html__( 'Patient name', 'doctor2go-connect' ); ?> *</label>
						<input class="form-control required" type="text" value="<?php echo esc_attr( $full_name ); ?>" name="client_name" id="client_name">
					</div>
				</div>
				<div class="col-sm-6 mb-2">
					<div>
						<label for="client_email" class="form-label"><?php echo esc_html__( 'Patient email', 'doctor2go-connect' ); ?> *</label>
						<input class="form-control required" type="email" value="<?php echo esc_attr( sanitize_email( $user_email ) ); ?>" name="client_email" id="client_email">
					</div>
				</div>
				<div class="col-sm-6 simple_hide">
					<div>
						<label for="doc_name" class="form-label"><?php echo esc_html__( 'Doctor name', 'doctor2go-connect' ); ?> *</label>
						<input readonly class="form-control required" type="text" value="" name="doc_name" id="doc_name">
					</div>
				</div>
				<div class="col-sm-6  simple_hide">
					<div>
						<label for="doc_email" class="form-label"><?php echo esc_html__( 'Doctor email', 'doctor2go-connect' ); ?> *</label>
						<input readonly class="form-control required" type="email" value="" name="doc_email" id="doc_email">
					</div>
				</div>
				<div class="col-sm-12 mb-2">
					<div>
						<label for="app_date" class="form-label"><?php echo esc_html__( 'Appointment date and time', 'doctor2go-connect' ); ?> *</label>
						<input readonly class="form-control required" type="text" value="" name="app_date" id="app_date">
					</div>
				</div>
				<div class="col-sm-12 simple_hide">
					<div>
						<label for="app_link" class="form-label"><?php echo esc_html__( 'Appointment link for doctor', 'doctor2go-connect' ); ?> *</label>
						<input readonly class="form-control required" type="text" value="" name="app_link" id="app_link">
					</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-sm-12">
					<div>
						<label for="comment" class="form-label"><?php echo esc_html__( 'Comment (optional)', 'doctor2go-connect' ); ?></label>
						<textarea class="form-control" id="comment" name="comment"></textarea>
					</div>
				</div>
			</div>
			<button type="button" class="btn btn-primary wp-block-button__link request_cancellation button" id="request_cancellation">
				<?php esc_html_e( 'send', 'doctor2go-connect' ); ?>
				<span class="btn-spinner spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
			</button>
		</form>
	</div>
</div>
<?php
}




function d2g_footer_html() {
	?>
	<div class="simple_hide" id="info_content" style="max-width: 800px;">
		<h2><?php echo esc_html__( 'Consultation types', 'doctor2go-connect' ); ?></h2>
		<div class="consult_info_wrapper">
			<div class="consult_info">
				<h3><?php echo esc_html__( 'Walk-in Consultation', 'doctor2go-connect' ); ?></h3>
				<p><?php echo esc_html__( 'A walk-in consultation allows you to have a real-time video consultation with the doctor without a prior appointment. You will enter a virtual waiting room and the doctor will attend to you as soon as they are available.', 'doctor2go-connect' ); ?></p>
			</div>
			<div class="consult_info">
				<h3><?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?></h3>
				<p><?php echo esc_html__( 'An email advice allows you to receive a professional assessment from a certified dermatologist via email within two working days. You will complete a questionnaire describing your skin concern, and the doctor will provide their evaluation and recommendations in writing.', 'doctor2go-connect' ); ?></p>
			</div>
			<div class="consult_info">
				<h3><?php echo esc_html__( 'Physical Consultation', 'doctor2go-connect' ); ?></h3>
				<p><?php echo esc_html__( 'A physical consultation involves an in-person visit to the doctor\'s clinic or designated location. You will have the opportunity to discuss your skin concerns face-to-face and receive a thorough examination and treatment plan.', 'doctor2go-connect' ); ?></p>
			</div>
			<div class="consult_info">
				<h3><?php echo esc_html__( 'Video Consultation', 'doctor2go-connect' ); ?></h3>
				<p><?php echo esc_html__( 'A video consultation enables you to have a remote appointment with the doctor via a secure video platform. This option provides convenience and flexibility, allowing you to discuss your skin concerns from the comfort of your own home.', 'doctor2go-connect' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'd2g_footer_html' );

/**
 * @param $type
 * @param $user_email
 * @param $user_name
 * @param $admin_email
 * @param string $code
 * @param string $link
 * @param array  $extraData
 * @return string
 */
function d2g_user_email( $type, $user_email, $user_name ) {
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

	// placeholder replacements general + DE
	$title   = str_replace( '%to_name%', $user_name, $emailData[0]->post_title );
	$content = str_replace( '%to_name%', $user_name, $emailData[0]->post_content );
	$content = str_replace( '%email%', $user_email, $content );

	$msg = d2g_html_email( $content );

	// set header for confirmation mail (visitor / patient) and send mail
	$headers = 'From: ' . get_option( 'd2g_sender_name' ) . ' <' . get_option( 'd2g_sender_address' ) . '>' . "\r\n";
	wp_mail( $user_email, $title, $msg, $headers );

	// set headers for admin notification mail and send mail
	$headers = 'From: ' . $user_name . ' <' . $user_email . '>' . "\r\n";
	wp_mail( get_option( 'd2g_recipient_address' ), $title, $msg, $headers );

	return 'mail send';
}

function d2g_html_email( $content ) {
	$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_logo' ), 'full' )[0];
	$msg      = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html;UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            </head>
            <body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
            <table class="container" width="100%" style="max-width: 550px;" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                <tbody>
                <tr>
                    <td style="padding: 15px;"><center>
                    <table width="100%" style="max-width: 550px;" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                        <tbody>
                        <tr>
                            <td align="left">
                            <div style="border: solid 1px #d9d9d9;">
                                <table id="header" style="line-height: 1.6; font-size: 12px; font-family: Helvetica, Arial, sans-serif; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                    <tr>
                                        <td style="color: #ffffff;" colspan="2" valign="bottom" height="30">.</td>
                                    </tr>
                                    <tr>
                                        <td style="line-height: 32px;  text-align: center; padding-left: 30px;" colspan="2" valign="baseline">
                                            <span style="font-size: 32px;">
                                                <a style="text-decoration: none;" href="' . str_replace( '/wp', '', get_site_url() ) . '" target="_blank" rel="noopener">
                                                    <img style="max-width:250px;" src="' . $feat_pic . '">
                                                </a>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                                <table id="content" style="margin-top: 15px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                    <tr>
                                    <td style="border-top: solid 1px #d9d9d9;" colspan="2">
                                        <div style="padding: 15px 0;">' . $content . '</div>
                                    </td>
                                    </tr>
                                </tbody>
                                </table>
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </center></td>
                </tr>
                </tbody>
            </table>
            </body>
        </html>';

		return $msg;
}


/**
 * Programmatically logs a user in
 *
 * @param string $username
 * @return bool True if the login was successful; false if it wasn't
 */
function d2g_programmatic_login( $username ) {

	if ( is_user_logged_in() ) {
		wp_logout();
	}

	add_filter( 'authenticate', 'd2g_allow_programmatic_login', 1, 3 );    // hook in earlier than other callbacks to short-circuit them
	add_filter( 'wordfence_ls_require_captcha', '__return_false' );
	$user = wp_signon( array( 'user_login' => $username ) );

	remove_filter( 'authenticate', 'd2g_allow_programmatic_login', 1, 3 );

	if ( is_a( $user, 'WP_User' ) ) {
		wp_set_current_user( $user->ID, $user->user_login );

		if ( is_user_logged_in() ) {
			return true;
		}
	}

	return false;
}

// ✅ reCAPTCHA validation for login form
if ( get_option( 'd2g_recaptcha_site_key' ) ) {
	add_filter(
		'wp_authenticate_user',
		function ( $user ) {

        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

			// Only run for POST requests
			if ( $request_method === 'POST' ) {

				$recaptcha_secret_key = get_option( 'd2g_recaptcha_secret_key' );

				// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

				// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

				// Verify reCAPTCHA response with Google
				$response = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					array(
						'body' => array(
							'secret'   => $recaptcha_secret_key,
							'response' => $recaptcha_response,
							'remoteip' => $remote_ip,
						),
					)
				);

				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( empty( $response_body['success'] ) || ! $response_body['success'] ) {
					return new WP_Error(
						'recaptcha_failed',
						__( 'reCAPTCHA verification failed. Please try again.', 'doctor2go-connect' )
					);
				}
			}

			return $user;
		}
	);
}




/**
 * An 'authenticate' filter callback that authenticates the user using only     the username.
 *
 * To avoid potential security vulnerabilities, this should only be used in     the context of a programmatic login,
 * and unhooked immediately after it fires.
 *
 * @param WP_User $user
 * @param string  $username
 * @param string  $password
 * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
 */
function d2g_allow_programmatic_login( $user, $username, $password ) {
	return get_user_by( 'login', $username );
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 *
 * @param Array   $cats     taxonomy term objects to sort
 * @param integer $parentId the current parent ID to put them in
 */
function d2g_sort_terms_hierarchicaly( array $cats, $parentId = 0 ) {
	$into = array();
	foreach ( $cats as $i => $cat ) {
		if ( $cat->parent == $parentId ) {
			$cat->children         = d2g_sort_terms_hierarchicaly( $cats, $cat->term_id );
			$into[ $cat->term_id ] = $cat;
		}
	}
	return $into;
}


function d2g_save_user_timezone() {

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$timezone = isset( $_POST['timezone'] )
		? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		: '';

	if ( $timezone !== '' ) {

		// Store in cookie for 30 days
		setcookie(
			'd2g_user_timezone',
			$timezone,
			time() + MONTH_IN_SECONDS,
			COOKIEPATH,
			COOKIE_DOMAIN
		);

		wp_send_json_success(
			array(
				'message'  => 'Timezone saved successfully!',
				'timezone' => $timezone,
			)
		);
	}

	wp_send_json_error( array( 'message' => 'Timezone not provided.' ) );
}


function d2g_enqueue_timezone_script() {
	wp_enqueue_script( 'timezone-script', D2G_PLUGIN_URL . '/public/js/timezone.js', array(), null, true );

	// Pass the AJAX URL to the script
	wp_localize_script( 'timezone-script', 'ajaxurl', array( admin_url( 'admin-ajax.php' ) ) );
}


// retrive user timezone in php
function get_user_timezone() {
	if ( isset( $_COOKIE['d2g_user_timezone'] ) ) {
		return sanitize_text_field( wp_unslash( $_COOKIE['d2g_user_timezone'] ) );
	}

	return '';
}

// creates an array for timezones
function d2g_timezones() {
	// time zones list from PHP
	$cont                 = '';
	$timezone_identifiers = ( $cont == null ) ? DateTimeZone::listIdentifiers() : DateTimeZone::listIdentifiers();
	$continent            = '';
	$i                    = '';
	$timezones            = array();
	$phpTime              = gmdate( 'Y-m-d H:i:s' );

	foreach ( $timezone_identifiers as $key => $value ) {
		if ( preg_match( '/^(Europe|America|Asia|Antarctica|Arctic|Atlantic|Indian|Pacific)\//', $value ) ) {
			$ex = explode( '/', $value ); // obtain continent, city
			if ( $continent != $ex[0] ) {
				$i = $ex[0];
			}

			$timezone     = new DateTimeZone( $value ); // Get default system timezone to create a new DateTimeZone object
			$offset       = $timezone->getOffset( new \DateTime( $phpTime ) );
			$offsetHours  = round( abs( $offset ) / 3600 );
			$offsetString = ( $offset < 0 ? '-' : '+' );
			if ( $offsetHours == 1 or $offsetHours == -1 ) {
				$label = 'Hour';
			} else {
				$label = 'Hours';
			}

			$city                      = $ex[1];
			$continent                 = $ex[0];
			$c[ $i ][ $value ]         = isset( $ex[2] ) ? $ex[1] . ' - ' . $ex[2] : $ex[1];
			$timezones[ $i ][ $value ] = $c[ $i ][ $value ] . ' (' . $offsetString . $offsetHours . ' ' . $label . ')';
		}
	}

	return $timezones;
}

// Function to show liked posts for logged-in users
function d2g_get_liked_posts() {
	if ( is_user_logged_in() ) {
		$user_id     = get_current_user_id();
		$liked_posts = get_user_meta( $user_id, 'liked_posts', true );
		return $liked_posts ? $liked_posts : array();
	}

	return array();
}


// Enable shortcode processing in menu items
add_filter( 'wp_nav_menu_items', 'd2g_run_shortcodes_in_menu', 10, 2 );
function d2g_run_shortcodes_in_menu( $items, $args ) {
	return do_shortcode( $items );
}

// shortcode for user name
function d2g_user_name_shortcode() {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		// get user meta or other data as needed
		$meta = get_user_meta( $current_user->ID );
		return esc_html( $meta['first_name'][0] . ' ' . $meta['last_name'][0] );
	} else {
		return '';
	}
}
add_shortcode( 'd2g_user_name', 'd2g_user_name_shortcode' );


// ajax callback funcrtion for loading the availability data in the calendar on the detail page
function d2g_load_availability_data() {
	// Get doctor ID safely, suppress nonce warning
	$doc_id = isset( $_POST['doc_id'] ) ? absint( wp_unslash( $_POST['doc_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( ! $doc_id ) {
		wp_send_json_error( array( 'message' => 'Invalid doctor ID' ) );
		wp_die();
	}

	$profileClass         = new D2G_ProfileData( $doc_id );
	$doctor_meta          = get_post_meta( $doc_id );
	$availabilityDataJson = $profileClass->d2g_get_availability_data( $doctor_meta['user_key'][0] );
	$availabilityDataObj  = json_decode( $availabilityDataJson );
	$walk_in_check     = '';
	$tariffStr         = '';
	$firstAvailibility = '';
	$docSlotsArray     = array();

	if ( isset( $availabilityDataObj->availabilities ) || isset( $availabilityDataObj->user_has_inloop ) ) {
		if ( ! isset( $availabilityDataObj->availabilities->message ) && count( $availabilityDataObj->availabilities ) > 0 ) {
			update_post_meta( $doc_id, 'd2g_availability_check', 1 );
			$docSlotsArray     		= $availabilityDataObj->availabilities;
			$firstAvailibilityStr 	= $profileClass->get_first_avialibility( $docSlotsArray );
			$firstAvailibility 		= $profileClass->get_first_avialibility( $docSlotsArray, 'date' );
			update_post_meta( $doc_id, 'd2g_first_availability', $firstAvailibility );
			$tariffs           		= $profileClass->get_tariffs( $docSlotsArray );
			$tariffStr         		= d2g_get_tariff_string( $tariffs );
			update_post_meta( $doc_id, 'd2g_tariffs', $tariffStr );
			
		}
		 if ( isset( $availabilityDataObj->user_has_inloop ) && $availabilityDataObj->user_has_inloop == true && isset( $availabilityDataObj->user_is_active ) && $availabilityDataObj->user_is_active == true ) {
			$walk_in_check     = true;
			update_post_meta( $doc_id, 'd2g_walk_in', 1 );
		} else {
			update_post_meta( $doc_id, 'd2g_walk_in', 0 );
			$walk_in_check     = false;
		}
		update_post_meta( $doc_id, 'd2g_last_synced', date('Y-m-d H:i:s') );
		update_post_meta( $doc_id, 'd2g_timecode', time() );
	} else {
		update_post_meta( $doc_id, 'd2g_availability_check', 0 );
		update_post_meta( $doc_id, 'd2g_first_availability', 0 );
		update_post_meta( $doc_id, 'd2g_walk_in', 0 ); 
		update_post_meta( $doc_id, 'd2g_tariffs', 0 );
		update_post_meta( $doc_id, 'd2g_last_synced', 0 );	
	}

	$availibily_data_set = array(
		'walkin_check'       => $walk_in_check ?: '',
		'tariffs'            => $tariffStr ?: '',
		'first_availibility' => $firstAvailibilityStr ?: '',
		'doc_slots'          => $docSlotsArray,
	);

	wp_send_json_success( $availibily_data_set );
	wp_die();
}


// Function to generate tariff string
function d2g_get_tariff_string( $tariffs ) {

	$tariffStr = '';
	foreach ( $tariffs as $tariff => $currency ) {
		$tariffStr .= '<span class="tariff">' . $currency['payment_currency'] . ' ' . $tariff . '</span>';
	}
	return $tariffStr;
}

//sanitize and return POST data
function d2g_get_post_text( $key ) {
    return isset( $_POST[ $key ] )
        ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) )
        : '';
}