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
<div id="content" class="site-content <?= esc_attr(apply_filters('bootscore/class/container', 'container', 'single')); ?> <?= esc_attr(apply_filters('bootscore/class/content/spacer', 'pt-3 pb-5', 'single')); ?>">
	<article id="doctor_wrapper_v2" class="doctor_detail_v2 type-d2g_doctor">
		<?php if(wpmd_is_phone()){ ?>
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
		<?php } ?>
		<div id="content_wrapper">
			<div class="top mb-<?php echo (wpmd_is_notphone()) ? '5' : '3'; ?>">
				<div class="<?php echo (wpmd_is_phone()) ? 'd-flex flex-row' : ''; ?><?php echo (wpmd_is_notphone()) ? 'row' : ''; ?>">
					<div class="col-sm-3">
						<figure><img class="profile_pic" src="<?php echo esc_html( $d2g_profile_data->feat_pic_full ); ?>&w=120&h=120&fit=crop&crop=faces" alt="<?php the_title(); ?>"></figure>
					</div>
					<div class="col-sm-9" id="intro">
						<?php if(wpmd_is_notphone()){ ?>
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
							<div id="bio">
								<?php the_content(); ?>
							</div>
						<?php } ?>
						<div id="short_info">
							<?php do_action( 'd2g_info_box', 'detail', 'col-2' ); ?>
							<!--ACTION HOOK walkin consult form-->
						</div>
						
					</div>
				</div>
			</div>
			<div class="entry-wrapper"> 
				<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
							<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['written_con_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['written_con_price'][0] ); ?></strong><br>
							<?php echo esc_html__( 'E-mail advice', 'doctor2go-connect' ); ?>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#calendar-tab" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">
							<strong><?php echo  $d2g_profile_data->doctor_meta['d2g_tariffs'][0] ; ?></strong><br><?php echo esc_html__( 'Video consult', 'doctor2go-connect' ); ?>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">
							<strong><?php echo esc_html( $d2g_profile_data->doctor_meta['walk_in_currency'][0] . ' ' . $d2g_profile_data->doctor_meta['walk_in_price'][0] ); ?></strong><br>
							<?php echo esc_html__( 'Walk-in', 'doctor2go-connect' ); ?>
						</button>
					</li>
				</ul>
				<div class="tab-content mb-5" id="myTabContent">
					<div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
						<?php if ( $d2g_profile_data->doctor_meta['written_con_price'][0] != '' ) {
								do_action( 'd2g_doctor_written_con_form' );
						} ?>
					</div>
					<div class="tab-pane fade" id="calendar-tab" role="tabpanel" aria-labelledby="calendar-tab" tabindex="0">
						<?php do_action( 'd2g_booking_calendar' ); ?>
					</div>
					<div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
						<?php if ( $d2g_profile_data->doctor_meta['walk_in_price'][0] != '' ) {
								do_action( 'd2g_doctor_walkin_form' );
						} ?>
					</div>
				</div>
				<!--ACTION HOOK BOOKING CALENDAR-->
				
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
</div>

