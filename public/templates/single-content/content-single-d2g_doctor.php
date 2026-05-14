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
<article id="doctor_wrapper_v1" class="doctor_detail_v1 container  doc_details">
	<div class="mb-3 only_mobile">
		<div class="top_wrapper_mobile">
			<header>
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
					<div class="entry_content">
						<div class="mb-3">
							<?php do_action( 'd2g_info_box', 'detail', 'col-2' ); ?>
						</div>
						<!--ACTION HOOK consult buttons-->
						
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'd2g_consult_buttons', 'detail', 'small' ); ?>
	</div>
	<div class="row">
		<!--ACTION HOOK SIDEBAR-->
		<?php do_action( 'd2g_single_sidebar' ); ?>
		<div class="col-sm-9" id="content_wrapper">
			<div class="content_inner_wrapper">
				<div class="entry-wrapper"  id="info">
					<header class="not_mobile">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<?php if ( $d2g_profile_data->specialties !== false ) { ?>
							<h2 class="specialties">
								<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
									<span><?php echo esc_html( $specialty->name ); ?></span>
								<?php } ?>
							</h2>
						<?php } ?>
					</header>
					<div id="info_box" class="not_mobile">
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
					
					<div id="bio" class="bio section mb-5">
						<h3 class="section_title"><?php echo esc_html__( 'About', 'doctor2go-connect' ); ?></h3>
						<div class="text_wrapper"><?php the_content(); ?></div>
					</div>
					<!--ACTION HOOK BOOKING CALENDAR-->
					<?php do_action( 'd2g_booking_calendar' ); ?>
					<div class="mb-5">
						<!--ACTION HOOK LOCATIONS-->
						<?php
						if ( is_array( $location_check ) && count( $location_check ) > 0 ) {
							do_action( 'd2g_doctor_locations' );
						}
						?>
					</div>
					<div class="mb-5">
						<!--ACTION HOOK EXTENDED INFO-->
						<?php do_action( 'd2g_doctor_extended_info' ); ?>
					</div>			
				</div>
				<div class="mb-5">
					<?php do_action( 'd2g_back_to_overview' ); ?>
				</div>
			</div>
		</div>
	</div>
</article>