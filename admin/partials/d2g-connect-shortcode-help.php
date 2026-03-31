<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
		<h2>Basic shortcodes</h2>
		<p class="mb-l"><?php echo esc_html__( 'Following shortcodes are included in the free version and can be used in pages from your website.', 'doctor2go-connect' ); ?></p>
		<h3>[d2g_doctors_listing]</h3>
		<p><?php echo esc_html__( 'Use this shortcode to display a list of doctors on a page. It includes a "Load More Doctors" button that loads additional doctors based on the "posts_per_page" setting.', 'doctor2go-connect' ); ?></p>
		<h4><?php echo esc_html__( 'Following parameters can be used to change the view from the listing', 'doctor2go-connect' ); ?></h4>
		<table class="info mb-l">
			<tr>
				<td>
					<strong><?php echo esc_html__( 'Parameter', 'doctor2go-connect' ); ?></strong>
				</td>
				<td>
					<strong><?php echo esc_html__( 'Possible values', 'doctor2go-connect' ); ?></strong>
				</td>
			</tr>
			<tr>
				<td>
					<p>template</p>
				</td>
				<td>
					grid (<?php echo esc_html__( 'default', 'doctor2go-connect' ); ?>) / list
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">columns</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'Amount of columns (only applicable with the grid template.)', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					2 / 3 (<?php echo esc_html__( 'default', 'doctor2go-connect' ); ?>) / 4 
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">posts_per_page</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'Amount of doctors shown on a page.', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'Any number larger than 0 (6 = default)', 'doctor2go-connect' ); ?></p>
					<p class="mt-xs">Or -1 (<?php echo esc_html__( 'All doctors will be shown.', 'doctor2go-connect' ); ?>)</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">wrapper_class</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'css class used for the outer wrapper (optional)', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'Any string', 'doctor2go-connect' ); ?></p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">orderby</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'orderby (optional) this follows the general WordPress ordering rules', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'default orderby is "title", you can use any orderby as described in the WordPress codex, when you want use orderby for a meta field you have to use orderby="meta_value" and you will have to set for instance the meta_key="written_con_price"', 'doctor2go-connect' ); ?></p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">order</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'order (optional) this follows the general WordPress order rules', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'ASC (ascending) or DESC (descending)', 'doctor2go-connect' ); ?></p>
				</td>
			</tr>
		</table>
		
		<h3>[d2g_profile_edit]</h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the form doctors use to create or update their profiles. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<hr>

		<h2>Pro shortcodes <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h2>
		<p class="mb-l">
			<?php echo esc_html__( 'Following shortcodes are included only included in the paid version and can be used in pages from your website. Click', 'doctor2go-connect' ); ?>&nbsp;<a href="d2g-connect.doctor2go.online/shop"><?php echo esc_html__( 'here', 'doctor2go-connect' ); ?></a>&nbsp;<?php echo esc_html__( 'to get your paid license now.', 'doctor2go-connect' ); ?>
		</p>

		<h3>[d2g_search_mask] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the search form on the doctor overview page. It must be used with the listing shortcode. No parameters are required.', 'doctor2go-connect' ); ?></p>

		

		<h3>[d2g_single_doctor_info] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p><?php echo esc_html__( 'Use this shortcode to display a single doctor in a compact format, similar to the grid overview on the front end. It can be placed anywhere on your website, such as the sidebar.', 'doctor2go-connect' ); ?></p>
		<table class="info mb-l">
			<tr>
				<td>
					<strong><?php echo esc_html__( 'Parameter', 'doctor2go-connect' ); ?></strong>
				</td>
				<td>
					<strong><?php echo esc_html__( 'Possible values', 'doctor2go-connect' ); ?></strong>
				</td>
			</tr>
			<tr>
				<td>
					<p>doc_id</p>
				</td>
				<td>
					<?php echo esc_html__( 'ID from doctor post, can be found in the backend overview from the doctors', 'doctor2go-connect' ); ?>
				</td>
			</tr>
		</table>
		<h3>[d2g_single_doctor_locations] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p><?php echo esc_html__( 'Use this shortcode to display a doctor\'s locations, similar to the locations section on the doctor detail page. Ideal for a contact page or website footer.', 'doctor2go-connect' ); ?></p>
		<table class="info mb-l">
			<tr>
				<td>
					<strong><?php echo esc_html__( 'Parameter', 'doctor2go-connect' ); ?></strong>
				</td>
				<td>
					<strong><?php echo esc_html__( 'Possible values', 'doctor2go-connect' ); ?></strong>
				</td>
			</tr>
			<tr>
				<td>
					<p>doc_id</p>
				</td>
				<td>
					<?php echo esc_html__( 'ID from doctor post, can be found in the backend overview from the doctors', 'doctor2go-connect' ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">wrapper_class</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'css class used for the outer wrapper (optional)', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'Any string', 'doctor2go-connect' ); ?></p>
				</td>
			</tr>
		</table>
		<h3>[d2g_single_doctor_calendar] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p><?php echo esc_html__( 'Use this shortcode to display a doctor\'s calendar, similar to the calendar section on the doctor detail page. Ideal for a front page or website sidebar.', 'doctor2go-connect' ); ?></p>
		<table class="info mb-l">
			<tr>
				<td>
					<strong><?php echo esc_html__( 'Parameter', 'doctor2go-connect' ); ?></strong>
				</td>
				<td>
					<strong><?php echo esc_html__( 'Possible values', 'doctor2go-connect' ); ?></strong>
				</td>
			</tr>
			<tr>
				<td>
					<p>doc_id</p>
				</td>
				<td>
					<?php echo esc_html__( 'ID from doctor post, can be found in the backend overview from the doctors', 'doctor2go-connect' ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<p class="mb-xs">wrapper_class</p>
					<p class="mt-xs"><i><?php echo esc_html__( 'css class used for the outer wrapper (optional)', 'doctor2go-connect' ); ?></i></p>
				</td>
				<td>
					<p class="mb-xs"><?php echo esc_html__( 'Any string', 'doctor2go-connect' ); ?></p>
				</td>
			</tr>
		</table>
		
		<h3>[d2g_registration_form] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the registration form for patients. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_login_form] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the login form. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_lost_password_form] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the lost password form. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_reset_password_form] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the reset password form. No parameters are required.', 'doctor2go-connect' ); ?></p>
		
		<h3>[d2g_patient_menu] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display a menu form the patient pages. Ideal it is used with in combination with the following shortcodes: d2g_account_settings / d2g_patient_appointments / d2g_liked_posts / d2g_patient_dashbaord . No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_patient_dashbaord] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the patient dashboard, the page patients are redirected to after logging in. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_account_settings] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the search form on the doctor overview page. It must be used with the listing shortcode. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_patient_appointments] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the account settings form, allowing patients to update their email, phone number, and password. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_liked_posts] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the liked doctor\'s from a patient. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_questionnaires] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the questionnaires for an intake or from a written consultation. No parameters are required.', 'doctor2go-connect' ); ?></p>

		<h3>[d2g_patient_portal] <span class="red simple_hide"><?php echo esc_html__( '(PRO version only*)', 'doctor2go-connect' ); ?></span></h3>
		<p class="mb-l"><?php echo esc_html__( 'Use this shortcode to display the patient portal where the patient can chat with doctors and receive medical documents from, where he/she has a connection with. No parameters are required.', 'doctor2go-connect' ); ?></p>
	</div>
</div>
