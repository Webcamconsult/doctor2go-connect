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
			settings_fields( 'd2g-option-group' );
			do_settings_sections( 'd2g-option-group' );
			?>
			<h3 class="section_title opener"><?php echo esc_html__( 'API connection', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<p>
					<label><strong><?php esc_html_e( 'Organisation API', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="password" name="wcc_token" placeholder="wcc_token" value="<?php echo esc_html( get_option( 'wcc_token' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'URL used for API', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="api_url_short" placeholder="api_url_short" value="<?php echo esc_html( get_option( 'api_url_short' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'URL used for waiting room from the Doctor2go software', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="waiting_room_url" placeholder="waiting_room_url" value="<?php echo esc_html( get_option( 'waiting_room_url' ) ); ?>">
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Base URL from the Doctor2go software', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="wcc_base_url" placeholder="base_url" value="<?php echo esc_html( get_option( 'wcc_base_url' ) ); ?>">
				</p>
				<!--
				<p>
					<label><strong>admin_mail</strong></label><br>
					<input style="min-width: 400px;" type="text" name="admin_mail" placeholder="admin_mail" value="<?php echo esc_html( get_option( 'admin_mail' ) ); ?>">
				</p>-->    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'CSS & JS', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<select id="d2g_theme_css" name="d2g_theme_css">
					<option <?php echo ( get_option( 'd2g_theme_css' ) == 'light' || get_option( 'd2g_theme_css' ) == '' ) ? 'selected' : ''; ?> value="light"><?php echo esc_html__( 'light (default)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2g_theme_css' ) == 'dark' ) ? 'selected' : ''; ?> value="dark"><?php echo esc_html__( 'dark', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2g_theme_css' ) == 'no-style' ) ? 'selected' : ''; ?> value="no-style"><?php echo esc_html__( 'no styling', 'doctor2go-connect' ); ?></option>   
				</select>
				<p><?php echo esc_html__( 'You can deactivate the following css file if your theme already contains a bootstrap css.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="d2g_bootstrap_css" id="d2g_bootstrap_css" value="1"  <?php echo ( get_option( 'd2g_bootstrap_css' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_bootstrap_css"><?php echo esc_html__( 'Deactivate bootstrap css', 'doctor2go-connect' ); ?></label>    
				<p><?php echo esc_html__( 'You can deactivate the following JS file if your theme already contains a bootstrap5 JS file.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="d2g_bootstrap_js" id="d2g_bootstrap_js" value="1"  <?php echo ( get_option( 'd2g_bootstrap_js' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_bootstrap_js"><?php echo esc_html__( 'Deactivate bootstrap JS', 'doctor2go-connect' ); ?></label>    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Overview page settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<h4><?php echo esc_html__( 'Load availability information', 'doctor2go-connect' ); ?></h4>
				<input type="checkbox" name="d2g_load_availability_info" id="d2g_load_availability_info" value="1"  <?php echo ( get_option( 'd2g_load_availability_info' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_load_availability_info"><?php echo esc_html__( 'Load availability information on overview page', 'doctor2go-connect' ); ?></label>
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Detail page settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<h4><?php echo esc_html__( 'Layout', 'doctor2go-connect' ); ?></h4>
				<select id="d2g_detail_page_view" name="d2g_detail_page_view">
					<option <?php echo ( get_option( 'd2g_detail_page_view' ) == 'single-v1' || get_option( 'd2g_detail_page_view' ) == '' ) ? 'selected' : ''; ?> value="single-v1"><?php echo esc_html__( 'With sidebar (anchor links)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2g_detail_page_view' ) == 'single-v2' ) ? 'selected' : ''; ?> value="single-v2"><?php echo esc_html__( 'Full width (no sidebar)', 'doctor2go-connect' ); ?></option>
					<option <?php echo ( get_option( 'd2g_detail_page_view' ) == 'single-v3' ) ? 'selected' : ''; ?> value="single-v3"><?php echo esc_html__( 'Custom layout', 'doctor2go-connect' ); ?></option>
				</select>
				<h4><?php echo esc_html__( 'Header & footer', 'doctor2go-connect' ); ?></h4>
				<p><?php echo esc_html__( 'If you are using a base file in your theme, where you load the footer and header, than you can deactivate loading those in youre single-d2g_doctor.php file.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2g_single_header_footer" id="d2g_single_header_footer" value="1"  <?php echo ( get_option( 'd2g_single_header_footer' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_single_header_footer"><?php echo esc_html__( 'Deactivate header and footer in single-d2g_doctor.php', 'doctor2go-connect' ); ?></label>
			</div>
			
			<h3 class="section_title opener"><?php echo esc_html__( 'Placeholder image for doctors', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<?php
				if ( get_option( 'd2g_placeholder' ) != '' ) {
					$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_placeholder' ), 'd2g-doc-pic' )[0]
					?>
					<img src="<?php echo esc_html( $feat_pic ); ?>" style="max-width:300px; width:100%;">
				<?php } ?>
				<input readonly class="hide" type="text" name="d2g_placeholder" value="<?php echo esc_html( get_option( 'd2g_placeholder' ) ); ?>"/>
				<br>
				<p><button class="button wpse-228085-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'E-mail settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<p>
					<label><strong><?php echo esc_html__( 'sender address', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2g_sender_address" placeholder="<?php echo esc_html__( 'sender address', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2g_sender_address' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'recipient address', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2g_recipient_address" placeholder="<?php echo esc_html__( 'recipient address', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2g_recipient_address' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'sender name', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2g_sender_name" placeholder="<?php echo esc_html__( 'sender name', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2g_sender_name' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'logo used in e-mail', 'doctor2go-connect' ); ?></strong></label><br>
				</p>
				<?php
				if ( get_option( 'd2g_logo' ) != '' ) {
					$feat_pic = wp_get_attachment_image_src( get_option( 'd2g_logo' ), 'full' )[0];
					?>
					<img src="<?php echo esc_html( $feat_pic ); ?>" style="max-width:300px; width:100%;">
				<?php } ?>
				<input readonly class="hide" type="text" name="d2g_logo" value="<?php echo esc_html( get_option( 'd2g_logo' ) ); ?>"/>
				<br>
				<p><button class="button wpse-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>

			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'User / profile settings for doctors', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<input type="checkbox" name="d2g_local_user" id="d2g_local_user" value="1"  <?php echo ( get_option( 'd2g_local_user' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_local_user"><?php echo esc_html__( 'Create doctor user and profile locally', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'If you create the user account and profile locally you will not need to set the API connection', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2g_admin_access" id="d2g_admin_access" value="1"  <?php echo ( get_option( 'd2g_admin_access' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_admin_access"><?php echo esc_html__( 'Give doctor access to wp-admin', 'doctor2go-connect' ); ?></label>
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Security (Google Recaptcha V2 / Solid Security Basic) ', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">

				<p><strong><?php echo esc_html__( 'We advise you following:', 'doctor2go-connect' ); ?></strong></p>
				<p><?php echo esc_html__( '1. Install the "Solid Security Basic" plugin and configure two-factor authentication (2FA) for all individuals with access to the user accounts. This is can be crucial to ensure the highest level of security for sensitive patient data.', 'doctor2go-connect' ); ?></p>
				<p><input type="checkbox" name="activate_2fa_link" id="activate_2fa_link" value="1"  <?php echo ( get_option( 'activate_2fa_link' ) == '1' ) ? 'checked' : ''; ?>><label for="activate_2fa_link"><?php echo esc_html__( 'I have installed "Solid Security Basic" and I have activated 2FA', 'doctor2go-connect' ); ?></label></p>
				<p><?php echo esc_html__( '2. Create a Google reCAPTCHA account specifically for your website and enter the generated keys into the appropriate settings within your site’s configuration.', 'doctor2go-connect' ); ?></p>
				<p>
					<label><strong><?php echo esc_html__( 'site key', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2g_recaptcha_site_key" placeholder="<?php echo esc_html__( 'site key', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2g_recaptcha_site_key' ) ); ?>">
				</p>
				<p>
					<label><strong><?php echo esc_html__( 'secret key', 'doctor2go-connect' ); ?></strong></label><br>
					<input style="min-width: 400px;" type="text" name="d2g_recaptcha_secret_key" placeholder="<?php echo esc_html__( 'secret key', 'doctor2go-connect' ); ?>" value="<?php echo esc_html( get_option( 'd2g_recaptcha_secret_key' ) ); ?>">
				</p>
				<p><?php echo esc_html__( 'You can deactivate the loading from the Google recaptcha script  if necessary, particularly if it is already being loaded from another source.', 'doctor2go-connect' ); ?></p>        
				<input type="checkbox" name="deactivate_recapctha_script" id="deactivate_recapctha_script" value="1"  <?php echo ( get_option( 'deactivate_recapctha_script' ) == '1' ) ? 'checked' : ''; ?>><label for="deactivate_recapctha_script"><?php echo esc_html__( 'Deactivate loading google recaptcha script', 'doctor2go-connect' ); ?></label>    
			</div>
			<h3 class="section_title opener"><?php echo esc_html__( 'Special settings', 'doctor2go-connect' ); ?></h3>
			<div class="section_wrapper simple_hide">
				<input type="checkbox" name="under_construction" id="under_construction" value="1"  <?php echo ( get_option( 'under_construction' ) == '1' ) ? 'checked' : ''; ?>><label for="under_construction"><?php echo esc_html__( 'Activate under construction', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Block all pages for not logged in users and redirect to the under construction page', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2g_use_imgix" id="d2g_use_imgix" value="1"  <?php echo ( get_option( 'd2g_use_imgix' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_use_imgix"><?php echo esc_html__( 'Use imgix image processing', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Please find more info\'s about imigix on their website.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2g_pseudo_translations" id="d2g_pseudo_translations" value="1"  <?php echo ( get_option( 'd2g_pseudo_translations' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_pseudo_translations"><?php echo esc_html__( 'Use pseudo translations.', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'Doctors will be translated via meta values in the post and in the meta values in the connected taxonomies.', 'doctor2go-connect' ); ?></p>
				<input type="checkbox" name="d2g_use_default_questionnaire" id="d2g_use_default_questionnaire" value="1"  <?php echo ( get_option( 'd2g_use_default_questionnaire' ) == '1' ) ? 'checked' : ''; ?>><label for="d2g_use_default_questionnaire"><?php echo esc_html__( 'Use default questionnaire as intake form for appointments.', 'doctor2go-connect' ); ?></label>
				<p><?php echo esc_html__( 'The questinonaire will be fetched in the WCC software based on langauge and on type default.', 'doctor2go-connect' ); ?></p>
			</div>
			<p><?php submit_button(); ?></p>
		</form>
	</div>
	
</div>


