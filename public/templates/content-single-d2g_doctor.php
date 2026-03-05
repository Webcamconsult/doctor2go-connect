<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
global $d2g_profile_data;
$location_check = $d2g_profile_data->doctor_meta['locations_to_go'];
?>
<article id="doctor_wrapper" class="doctor_detail_v1 container mt-5">
	<div class="row">
		<!--ACTION HOOK SIDEBAR-->
		<?php do_action( 'd2g_single_sidebar' ); ?>
		<div class="col-sm-9" id="content_wrapper">
			<div class="content_inner_wrapper">
				<div class="entry-wrapper"  id="info">
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
					<div id="info_box">
						<!--ACTION HOOK INFOBOX-->
						<?php do_action( 'd2g_info_box', 'detail', 'col-2' ); ?>
					</div>
					<?php
					if ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) {
							do_action( 'd2g_doctor_walkin_form' );
					}
					?>
					<?php
					if ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ) {
							do_action( 'd2g_doctor_written_con_form' );
					}
					?>
					<div id="bio" class="bio section">
						<h3 class="section_title"><?php echo esc_html__( 'About', 'doctor2go-connect' ); ?></h3>
						<div class="text_wrapper"><?php the_content(); ?></div>
					</div>
					<!--ACTION HOOK LOCATIONS-->
					<?php
					if ( is_array( $location_check ) && count( $location_check ) > 0 ) {
						do_action( 'd2g_doctor_locations' );
					}
					?>
					<!--ACTION HOOK EXTENDED INFO-->
					<?php do_action( 'd2g_doctor_extended_info' ); ?>
					<!--ACTION HOOK BOOKING CALENDAR-->
					<?php do_action( 'd2g_booking_calendar' ); ?>
				</div>
				<?php do_action( 'd2g_back_to_overview' ); ?>
			</div>
		</div>
	</div>
</article>