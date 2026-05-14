<?php
/**
 * Doctor Single Content Template
 *
 * This template can be overridden by copying it to yourtheme/d2g-connect/content-single-d2g_doctor.php.
 *
 * HOWEVER, on occasion d2g-connect will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     https://plugin.doctor2go.online/docs/template-structure/
 * @author  Webcamconsult
 * @package d2g-connect
 * @since   1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $d2g_profile_data;
//
// patient data
//
$patient        = wp_get_current_user();
$patient_meta   = get_user_meta( $patient->data->ID );
$location_check = $d2g_profile_data->doctor_meta['locations_to_go'];

?>
<article id="doctor_wrapper_v2" class="doctor_detail_v2 type-d2g_doctor doc_details">
	<div id="content_wrapper">
		<div class="top mb-5 pt-5 pb-5">
			<div class="container">
				<header class="only_mobile">
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php if ( $d2g_profile_data->specialties !== false ) { ?>
						<h2 class="specialties mb-3">
							<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
								<span><?php echo esc_html( $specialty->name ); ?></span>
							<?php } ?>
						</h2>
					<?php } ?>
				</header>
				<div class="row mb-3 top_row">
					<div class="col-sm-3">
						<figure><img class="feat_pic card" style="width:100%" src="<?php echo esc_html( $d2g_profile_data->feat_pic_square ); ?>" alt="<?php the_title(); ?>"></figure>
					</div>
					<div class="col-sm-9" id="info_top">
						<header class="not_mobile">
							<h1 class="entry-title"><?php the_title(); ?></h1>
							<?php if ( $d2g_profile_data->specialties !== false ) { ?>
								<h2 class="specialties mb-3">
									<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
										<span><?php echo esc_html( $specialty->name ); ?></span>
									<?php } ?>
								</h2>
							<?php } ?>
						</header>
						<div class="entry_content">
							<div class="mb-5">
								<?php do_action( 'd2g_info_box', 'detail', 'col-2' ); ?>
							</div>
							<!--ACTION HOOK consult buttons-->
							<?php do_action( 'd2g_consult_buttons', 'detail', 'small' ); ?>
							
							<!--ACTION HOOK walkin consult form-->
							<?php
							if ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) {
									do_action( 'd2g_doctor_walkin_form' );
							}
							?>
							<!--ACTION HOOK email consult form-->
							<?php
							if ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ) {
									do_action( 'd2g_doctor_written_con_form' );
							}
							?>
						</div>
						
					</div>
				</div>
				
			</div>
		</div>
		<div class="info_calendar_locations"> 
			<div class="container mb-5">
				<div id="bio" class=" mb-5">
					<?php the_content(); ?>
				</div>
				<!--ACTION HOOK BOOKING CALENDAR-->
				<?php do_action( 'd2g_booking_calendar' ); ?>
				<!--ACTION HOOK LOCATIONS-->
				<?php
				if ( is_array( $location_check ) && count( $location_check ) > 0 ) {
						do_action( 'd2g_doctor_locations' );
				}
				?>
			</div>
			<div id="extra_info" class=" pt-5 pb-5">
				<div class="container">
					<!--ACTION HOOK EXTENDED INFO-->
					<?php do_action( 'd2g_doctor_extended_info' ); ?>
					<?php do_action( 'd2g_back_to_overview' ); ?>  
				</div>
			</div>
		</div>  
	</div>
</article>
