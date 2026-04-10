<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://plugin.doctor2go.online
 * @since      1.0.0
 *
 * @package    Webcamconsult
 * @subpackage Webcamconsult/admin/partials
 */

$locale = explode( '_', get_locale() );

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap d2g-settings-wrap">
	<div class="inner">
		<h2 style="font-size: 30px;">Doctor2Go Connect Settings</h2>
		<form method="post" action="options.php" id="ac_settings">
			<?php
			settings_fields( 'd2gc-option-group' );
			do_settings_sections( 'd2gc-option-group' );
			?>
			<h3 class="section_title opener"><?php echo esc_html__( 'API connection', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<p>
					<label><strong><?php esc_html_e( 'Organisation API', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="password" name="d2gc_wcc_token" placeholder="d2gc_wcc_token" value="<?php echo esc_html( get_option( 'd2gc_wcc_token' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'URL used for API', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_api_url_short" placeholder="d2gc_api_url_short" value="<?php echo esc_html( get_option( 'd2gc_api_url_short' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'URL used for waiting room from the Doctor2go software', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_waiting_room_url" placeholder="d2gc_waiting_room_url" value="<?php echo esc_html( get_option( 'd2gc_waiting_room_url' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Base URL from the Doctor2go software', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_wcc_base_url" placeholder="base_url" value="<?php echo esc_html( get_option( 'd2gc_wcc_base_url' ) ); ?>">
				</p>
				<!--
				<p>
					<label><strong>d2gc_admin_mail</strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_admin_mail" placeholder="d2gc_admin_mail" value="<?php echo esc_html( get_option( 'd2gc_admin_mail' ) ); ?>">
				</p>-->    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'CSS & JS', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<p><?php echo esc_html__( 'You can Choose to load the custom doctor styling or not, it is an extension to the bootstarp 5 styles.', 'doctor2go-connect' ); ?></p>
				<select id="d2gc_theme_css" name="d2gc_theme_css">
					<option <?php echo ( get_option( 'd2gc_theme_css' ) == 'light' || get_option( 'd2gc_theme_css' ) == '' ) ? 'selected' : ''; ?> value="light"><?php echo esc_html__( 'load extra custom styles (light)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2gc_theme_css' ) == 'no-style' ) ? 'selected' : ''; ?> value="no-style"><?php echo esc_html__( 'no extra styling', 'doctor2go-connect' ); ?></option>   
				</select>
				<p><?php echo esc_html__( 'You can deactivate the following css file if your theme already contains a bootstrap css.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="d2gc_bootstrap_css" id="d2gc_bootstrap_css" value="1"  <?php echo ( get_option( 'd2gc_bootstrap_css' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_bootstrap_css"><?php echo esc_html__( 'Deactivate bootstrap css', 'doctor2go-connect' ); ?></label>    
				<p><?php echo esc_html__( 'You can deactivate the following JS file if your theme already contains a bootstrap5 JS file.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="d2gc_bootstrap_js" id="d2gc_bootstrap_js" value="1"  <?php echo ( get_option( 'd2gc_bootstrap_js' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_bootstrap_js"><?php echo esc_html__( 'Deactivate bootstrap JS', 'doctor2go-connect' ); ?></label>    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Detail page settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<h4><?php echo esc_html__( 'Layout', 'doctor2go-connect' ); ?></h4>
				<select id="d2gc_detail_page_view" name="d2gc_detail_page_view">
					<option <?php echo ( get_option( 'd2gc_detail_page_view' ) == 'single-v1' || get_option( 'd2gc_detail_page_view' ) == '' ) ? 'selected' : ''; ?> value="single-v1"><?php echo esc_html__( 'With sidebar (anchor links, hidden forms in fancybox)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2gc_detail_page_view' ) == 'single-v2' ) ? 'selected' : ''; ?> value="single-v2"><?php echo esc_html__( 'Full width (no sidebar, hidden forms in fancybox)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2gc_detail_page_view' ) == 'single-v3' ) ? 'selected' : ''; ?> value="single-v3"><?php echo esc_html__( 'Full width (no sidebar, consultation tabs, for this template you will need to install WP Mobile Detect)', 'doctor2go-connect' ); ?></option>
				</select>
				<h4><?php echo esc_html__( 'Header & footer', 'doctor2go-connect' ); ?></h4>
				<p><?php echo esc_html__( 'If you are using a base file in your theme, where you load the footer and header, than you can deactivate loading those in youre single-d2g_doctor.php file.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2gc_single_header_footer" id="d2gc_single_header_footer" value="1"  <?php echo ( get_option( 'd2gc_single_header_footer' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_single_header_footer"><?php echo esc_html__( 'Deactivate header and footer in single-d2g_doctor.php', 'doctor2go-connect' ); ?></label>
			</div>
			
			<h3 class="section_title opener"><?php echo esc_html__( 'Placeholder image for doctors', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<?php
				if ( get_option( 'd2gc_placeholder' ) != '' ) {
					$feat_pic = wp_get_attachment_image_src( get_option( 'd2gc_placeholder' ), 'd2g-doc-pic' )[0]
					?>
					<img src="<?php echo esc_html( $feat_pic ); ?>" style="max-width:300px; width:100%;">
				<?php } ?>
				<input readonly class="hide" type="text" name="d2gc_placeholder" value="<?php echo esc_html( get_option( 'd2gc_placeholder' ) ); ?>"/>
				<br>
				<p><button class="button wpse-228085-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'E-mail settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<p>
					<label><strong><?php echo esc_html__( 'sender address', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_sender_address" placeholder="<?php echo esc_html__( 'sender address', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2gc_sender_address' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'recipient address', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_recipient_address" placeholder="<?php echo esc_html__( 'recipient address', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2gc_recipient_address' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'sender name', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_sender_name" placeholder="<?php echo esc_html__( 'sender name', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2gc_sender_name' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'logo used in e-mail', 'doctor2go-connect' ); ?></strong></label><br>
				</p>
				<?php
				if ( get_option( 'd2gc_logo' ) != '' ) {
					$feat_pic = wp_get_attachment_image_src( get_option( 'd2gc_logo' ), 'full' )[0];
					?>
					<img src="<?php echo esc_html( $feat_pic ); ?>" style="max-width:300px; width:100%;">
				<?php } ?>
				<input readonly class="hide" type="text" name="d2gc_logo" value="<?php echo esc_html( get_option( 'd2gc_logo' ) ); ?>"/>
				<br>
				<p><button class="button wpse-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>

			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'User / profile settings for doctors', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<input type="checkbox" name="d2gc_local_user" id="d2gc_local_user" value="1"  <?php echo ( get_option( 'd2gc_local_user' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_local_user"><?php echo esc_html__( 'Create doctor user and profile locally', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'If you create the user account and profile locally you will not need to set the API connection', 'doctor2go-connect' ); ?></p>
				<p><input type="checkbox" name="d2gc_admin_access" id="d2gc_admin_access" value="1"  <?php echo ( get_option( 'd2gc_admin_access' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_admin_access"><?php echo esc_html__( 'Give doctor access to wp-admin', 'doctor2go-connect' ); ?></label></p>
				<p><input type="checkbox" name="d2gc_activate_sso" id="d2gc_activate_sso" value="1"  <?php echo ( get_option( 'd2gc_activate_sso' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_activate_sso"><?php echo esc_html__( 'Activate Single Sign On Login for doctors', 'doctor2go-connect' ); ?></label></p>
				<p><input type="checkbox" name="d2gc_activate_custom_login_registration" id="d2gc_activate_custom_login_registration" value="1"  <?php echo ( get_option( 'd2gc_activate_custom_login_registration' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_activate_custom_login_registration"><?php echo esc_html__( 'Activate custom login and registration for patients', 'doctor2go-connect' ); ?></label></p>
				<p><input type="checkbox" name="d2gc_activate_custom_password_mail" id="d2gc_activate_custom_password_mail" value="1"  <?php echo ( get_option( 'd2gc_activate_custom_password_mail' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_activate_custom_password_mail"><?php echo esc_html__( 'Activate custom password reset email', 'doctor2go-connect' ); ?></label></p>
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Security (Google Recaptcha V2 / Solid Security Basic) ', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">

				<p><strong><?php echo esc_html__( 'We advise you following:', 'doctor2go-connect' ); ?></strong></p>
				<p><?php echo esc_html__( '1. Install the "Solid Security Basic" plugin and configure two-factor authentication (2FA) for all individuals with access to the user accounts. This is can be crucial to ensure the highest level of security for sensitive patient data.', 'doctor2go-connect' ); ?></p>
				<p><input type="checkbox" name="d2gc_activate_2fa_link" id="d2gc_activate_2fa_link" value="1"  <?php echo ( get_option( 'd2gc_activate_2fa_link' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_activate_2fa_link"><?php echo esc_html__( 'I have installed "Solid Security Basic" and I have activated 2FA', 'doctor2go-connect' ); ?></label></p>
				<p><?php echo esc_html__( '2. Create a Google reCAPTCHA account specifically for your website and enter the generated keys into the appropriate settings within your site’s configuration.', 'doctor2go-connect' ); ?></p>
				<p>
					<label><strong><?php echo esc_html__( 'site key', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_recaptcha_site_key" placeholder="<?php echo esc_html__( 'site key', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2gc_recaptcha_site_key' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'secret key', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2gc_recaptcha_secret_key" placeholder="<?php echo esc_html__( 'secret key', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2gc_recaptcha_secret_key' ) ); ?>">
				</p>
				<p><?php echo esc_html__( 'You can deactivate the loading from the Google recaptcha script  if necessary, particularly if it is already being loaded from another source.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="d2gc_deactivate_recapctha_script" id="d2gc_deactivate_recapctha_script" value="1"  <?php echo ( get_option( 'd2gc_deactivate_recapctha_script' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_deactivate_recapctha_script"><?php echo esc_html__( 'Deactivate loading google recaptcha script', 'doctor2go-connect' ); ?></label>    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Special settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<input type="checkbox" name="d2gc_under_construction" id="d2gc_under_construction" value="1"  <?php echo ( get_option( 'd2gc_under_construction' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_under_construction"><?php echo esc_html__( 'Activate under construction', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Block all pages for not logged in users and redirect to the under construction page', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2gc_use_imgix" id="d2gc_use_imgix" value="1"  <?php echo ( get_option( 'd2gc_use_imgix' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_use_imgix"><?php echo esc_html__( 'Use imgix image processing', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Please find more info\'s about imigix on their website.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2gc_pseudo_translations" id="d2gc_pseudo_translations" value="1"  <?php echo ( get_option( 'd2gc_pseudo_translations' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_pseudo_translations"><?php echo esc_html__( 'Use pseudo translations.', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Doctors will be translated via meta values in the post and in the meta values in the connected taxonomies.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2gc_use_default_questionnaire" id="d2gc_use_default_questionnaire" value="1"  <?php echo ( get_option( 'd2gc_use_default_questionnaire' ) == '1' ) ? 'checked' : ''; ?>><label for="d2gc_use_default_questionnaire"><?php echo esc_html__( 'Use default questionnaire as intake form for appointments.', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'The questinonaire will be fetched in the WCC software based on langauge and on type default.', 'doctor2go-connect' ); ?></p>
			</div>
			<p><?php submit_button(); ?></p>
		</form>
	</div>
	
</div>


