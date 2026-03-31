<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webcamconsult.com
 * @since      1.0.0
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 * @author     Webcamconsult
 */
class D2gConnect_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate(){		
 		$protectedPages = array(
			'My profile'            => '[d2g_profile_edit]',
			'Patient dashboard'     => '[d2g_patient_dashbaord]',
			'Appointments'          => '[d2g_patient_appointments]',
			'Account settings'      => '[d2g_account_settings]',
			'Liked doctors'         => '[d2g_liked_posts]',
			'Secure patient portal' => '[d2g_patient_portal]',

		);
		/*

		foreach ( $protectedPages as $title => $content ) {
			// Create post object
			$my_post = array(
				'post_title'   => wp_strip_all_tags( $title ),
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			);

			// Insert the post into the database
			$post_id   = wp_insert_post( $my_post );
			$page_type = strtolower( str_replace( ' ', '_', $title ) );
			update_post_meta( $post_id, 'd2g_page_identifier', $page_type );
			update_post_meta( $post_id, 'd2g_page_accessebility', 'protected' );
		}

		$otherPages = array(
			'Doctors'                   => '[d2g_doctors_listing]',
			'Lost password'             => '[d2g_lost_password_form]',
			'Reset password'            => '[d2g_reset_password_form]',
			'Login'                     => '[d2g_login_form]',
			'Patient registration'      => '[d2g_registration_form]',
			'Appointment confirmation'  => '[d2g_appointment_confirmation]',
			'Email advice confirmation' => 'Your email advice has been successfully submitted. Our dermatologists will get back to you within two working days.',
			'Privacy policy'            => 'To be compliant with GDPR you will need to fill this page in.',
			'Terms and conditions'      => 'Here comes the terms &  conditions from your company.',
			'Disclaimer'                => 'Here comes the disclaimer from your company.',
			'Password reset sent'       => 'We have received your request for a password reset, please check your email and click on the link to reset your password.',

		);

		foreach ( $otherPages as $title => $content ) {
			// Create post object
			$my_post = array(
				'post_title'   => wp_strip_all_tags( $title ),
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'page',
			);

			// Insert the post into the database
			$post_id   = wp_insert_post( $my_post );
			$page_type = strtolower( str_replace( ' ', '_', $title ) );
			update_post_meta( $post_id, 'd2g_page_identifier', $page_type );
		}

		flush_rewrite_rules();
		*/

		/*
		* Schedule doctor availability sync
		*/
		if (!wp_next_scheduled('d2g_sync_doctors')) {
			wp_schedule_event(
				time(),
				'fifteen_minutes',
				'd2g_sync_doctors'
			);
		}

		add_role(
			'doctor',
			__( 'Doctor', 'doctor2go-connect' ),
			array(
				'read'                 => true,
				'view_admin_dashboard' => false,
				'activate_plugins'     => false,
				'deactivate_plugins'   => false,
				'd2g_capability'       => true,
				'edit_posts'           => true,
				'upload_files'         => true,
				'publish_posts'        => true,
			)
		);

		add_role(
			'patient',
			__( 'Patient', 'doctor2go-connect' ),
			array(
				'read'                 => true,
				'view_admin_dashboard' => false,
				'activate_plugins'     => false,
				'deactivate_plugins'   => false,
				'd2g_capability'       => false,
				'edit_posts'           => false,
				'upload_files'         => false,
				'publish_posts'        => false,
			)
		);
	}
}
