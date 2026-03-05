<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * This template can be overridden by copying it to yourtheme/d2-gconnect/single-d2g_doctor.php.
 *
 * HOWEVER, on occasion d2g-connect will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.doctor2go.online/template-structure/
 * @author  Webcamconsult
 * @package d2g-connetc
 * @since   1.0.0
 */



if ( get_option( 'd2g_single_header_footer' ) != 1 ) {
	get_header();
}

while ( have_posts() ) :
	the_post();

	if ( ! apply_filters( 'd2g_enable_profile_content', false ) ) {
		do_action( 'd2g_single_d2g_doctor_main_content' );
	}

	if ( apply_filters( 'd2g_enable_profile_content', false ) ) {
		the_content();
	}



endwhile; // end of the loop.

if ( get_option( 'd2g_single_header_footer' ) != 1 ) {
	get_footer();
}
