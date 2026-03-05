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
<article id="doctor_wrapper_v2" class="doctor_detail_v2 type-d2g_doctor">
	<div id="content_wrapper">
		<div class="top">
			<div class="row">
				<div class="col-sm-3">
					<figure><img style="width:100%" src="<?php echo esc_html( $d2g_profile_data->feat_pic ); ?>" alt="<?php the_title(); ?>"></figure>
				</div>
				<div class="col-sm-9" id="info">
					<header>
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<?php if ( $d2g_profile_data->specialties !== false ) { ?>
							<h2 class="specialties">
								<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
									<span><?php echo esc_html( $specialty->name ); ?></span>
								<?php } ?>
							</h2>
						<?php } ?>
					</header>
					<div class="entry_content">
						<div id="bio">
							<?php the_content(); ?>
						</div>
						<?php do_action( 'd2g_info_box', 'detail', 'col-2' ); ?>
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
			<!--ACTION HOOK consult buttons-->
			<?php do_action( 'd2g_consult_buttons', 'detail', 'small' ); ?>
		</div>
		<div class="entry-wrapper"> 
			<!--ACTION HOOK BOOKING CALENDAR-->
			<?php do_action( 'd2g_booking_calendar' ); ?>
			<!--ACTION HOOK LOCATIONS-->
			<?php
			if ( is_array( $location_check ) && count( $location_check ) > 0 ) {
					do_action( 'd2g_doctor_locations' );
			}
			?>
			<!--ACTION HOOK EXTENDED INFO-->
			<?php do_action( 'd2g_doctor_extended_info' ); ?>
			
		</div>  
		<?php do_action( 'd2g_back_to_overview' ); ?>  
	</div>
</article>
