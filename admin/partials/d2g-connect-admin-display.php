<?php
/**
 * Settings page of the plugin.
 *
 * All settings are in ONE form (options.php saves every option of the group on each save, an option that is
 * missing in the form would be emptied), the tabs only show/hide panels of that form (see d2g-connect-admin.js).
 * The translations tool is outside the form, it works via AJAX (D2GC_Doctor_Sync::ajax_check / ajax_align).
 *
 * @link       https://plugin.doctor2go.online
 * @since      1.0.0
 *
 * @package    doctor2go-connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A toggle switch row (the input stays a normal checkbox with value 1, so the saved values do not change).
 *
 * @param string $name        option name
 * @param string $title
 * @param string $description
 */
$d2gc_toggle = function ( $name, $title, $description = '' ) {
	?>
	<div class="d2gc-setting d2gc-setting--toggle">
		<label class="d2gc-toggle" for="<?php echo esc_attr( $name ); ?>">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( get_option( $name ), '1' ); ?>>
			<span class="d2gc-toggle__slider" aria-hidden="true"></span>
		</label>
		<div class="d2gc-setting__text">
			<label for="<?php echo esc_attr( $name ); ?>" class="d2gc-setting__title"><?php echo esc_html( $title ); ?></label>
			<?php if ( $description ) { ?>
				<p class="d2gc-setting__description"><?php echo esc_html( $description ); ?></p>
			<?php } ?>
		</div>
	</div>
	<?php
};

/**
 * A text field row.
 *
 * @param string $name
 * @param string $title
 * @param string $description
 * @param string $type text|password|email|url
 */
$d2gc_field = function ( $name, $title, $description = '', $type = 'text' ) {
	?>
	<div class="d2gc-setting">
		<label for="<?php echo esc_attr( $name ); ?>" class="d2gc-setting__title"><?php echo esc_html( $title ); ?></label>
		<input class="regular-text d2gc-input" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( get_option( $name ) ); ?>" autocomplete="off">
		<?php if ( $description ) { ?>
			<p class="d2gc-setting__description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
	</div>
	<?php
};

$d2gc_has_translations = '' !== D2GC_Translations::provider();

$d2gc_tabs = array(
	'connection' => __( 'Connection', 'doctor2go-connect' ),
	'display'    => __( 'Display', 'doctor2go-connect' ),
	'doctors'    => __( 'Doctors', 'doctor2go-connect' ),
	'patients'   => __( 'Patients', 'doctor2go-connect' ),
	'email'      => __( 'E-mail', 'doctor2go-connect' ),
	'security'   => __( 'Security', 'doctor2go-connect' ),
	'advanced'   => __( 'Advanced', 'doctor2go-connect' ),
);
if ( $d2gc_has_translations ) {
	$d2gc_tabs['translations'] = __( 'Translations', 'doctor2go-connect' );
}
?>
<div class="wrap d2g-settings-wrap d2gc-settings">
	<h1 class="d2gc-settings__title"><?php echo esc_html__( 'Doctor2Go Connect', 'doctor2go-connect' ); ?></h1>
	<p class="d2gc-settings__subtitle"><?php echo esc_html__( 'Connects your website to the doctor2go.online online practice software.', 'doctor2go-connect' ); ?></p>

	<nav class="nav-tab-wrapper d2gc-tabs" role="tablist">
		<?php foreach ( $d2gc_tabs as $d2gc_tab => $d2gc_label ) { ?>
			<a href="#<?php echo esc_attr( $d2gc_tab ); ?>" class="nav-tab" role="tab" data-tab="<?php echo esc_attr( $d2gc_tab ); ?>" aria-controls="d2gc-panel-<?php echo esc_attr( $d2gc_tab ); ?>"><?php echo esc_html( $d2gc_label ); ?></a>
		<?php } ?>
	</nav>

	<form method="post" action="options.php" id="ac_settings">
		<?php
		settings_fields( 'd2gc-option-group' );
		do_settings_sections( 'd2gc-option-group' );
		?>

		<?php /* ##########  CONNECTION  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-connection" role="tabpanel" data-tab="connection">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'doctor2go.online API', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'The connection to your doctor2go.online account. You receive these details from doctor2go.online.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_field( 'd2gc_wcc_token', __( 'Organisation API', 'doctor2go-connect' ), '', 'password' );
						$d2gc_field( 'd2gc_api_url_short', __( 'URL used for API', 'doctor2go-connect' ), '', 'url' );
						$d2gc_field( 'd2gc_waiting_room_url', __( 'URL used for waiting room from the Doctor2go software', 'doctor2go-connect' ), '', 'url' );
						$d2gc_field( 'd2gc_wcc_base_url', __( 'Base URL from the Doctor2go software', 'doctor2go-connect' ) );
						?>
						<div class="d2gc-setting">
							<label for="d2gc_doctor_sync_url" class="d2gc-setting__title"><?php echo esc_html__( 'Doctor sync URL', 'doctor2go-connect' ); ?></label>
							<input class="regular-text d2gc-input" type="text" id="d2gc_doctor_sync_url" value="<?php echo esc_attr( D2GC_Rest::get_doctor_sync_url() ); ?>" readonly onclick="this.select();">
							<p class="d2gc-setting__description"><?php echo esc_html__( 'doctor2go.online sends new and changed doctors to this address.', 'doctor2go-connect' ); ?></p>
						</div>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  DISPLAY  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-display" role="tabpanel" data-tab="display">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'CSS & JS', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'You can Choose to load the custom doctor styling or not, it is an extension to the bootstarp 5 styles.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<div class="d2gc-setting">
							<label for="d2gc_theme_css" class="d2gc-setting__title"><?php echo esc_html__( 'Doctor styling', 'doctor2go-connect' ); ?></label>
							<select id="d2gc_theme_css" name="d2gc_theme_css">
								<option <?php selected( in_array( get_option( 'd2gc_theme_css' ), array( 'light', '' ), true ) ); ?> value="light"><?php echo esc_html__( 'load extra custom styles (light)', 'doctor2go-connect' ); ?></option>
								<option <?php selected( get_option( 'd2gc_theme_css' ), 'no-style' ); ?> value="no-style"><?php echo esc_html__( 'no extra styling', 'doctor2go-connect' ); ?></option>
							</select>
						</div>
						<?php
						$d2gc_toggle( 'd2gc_bootstrap_css', __( 'Deactivate bootstrap css', 'doctor2go-connect' ), __( 'You can deactivate the following css file if your theme already contains a bootstrap css.', 'doctor2go-connect' ) );
						$d2gc_toggle( 'd2gc_bootstrap_js', __( 'Deactivate bootstrap JS', 'doctor2go-connect' ), __( 'You can deactivate the following JS file if your theme already contains a bootstrap5 JS file.', 'doctor2go-connect' ) );
						?>
					</div>
				</section>

				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Detail page settings', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Layout of the doctor profile pages.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<div class="d2gc-setting">
							<label for="d2gc_detail_page_view" class="d2gc-setting__title"><?php echo esc_html__( 'Layout', 'doctor2go-connect' ); ?></label>
							<select id="d2gc_detail_page_view" name="d2gc_detail_page_view">
								<?php // single-v1 (sidebar layout) is no longer offered, existing v1 settings fall back to v2, see d2gc_get_detail_page_view() ?>
								<option <?php selected( d2gc_get_detail_page_view(), 'single-v2' ); ?> value="single-v2"><?php echo esc_html__( 'Full width (no sidebar, hidden forms in a popup)', 'doctor2go-connect' ); ?></option>
								<option <?php selected( d2gc_get_detail_page_view(), 'single-v3' ); ?> value="single-v3"><?php echo esc_html__( 'Full width (no sidebar, consultation tabs)', 'doctor2go-connect' ); ?></option>
							</select>
						</div>
						<?php $d2gc_toggle( 'd2gc_single_header_footer', __( 'Deactivate header and footer in single-d2g_doctor.php', 'doctor2go-connect' ), __( 'If you are using a base file in your theme, where you load the footer and header, than you can deactivate loading those in youre single-d2g_doctor.php file.', 'doctor2go-connect' ) ); ?>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  DOCTORS  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-doctors" role="tabpanel" data-tab="doctors">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Doctor accounts', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'How doctor users and their public profiles are created and how doctors log in.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_toggle( 'd2gc_local_user', __( 'Create doctor user and profile locally', 'doctor2go-connect' ), __( 'If you create the user account and profile locally you will not need to set the API connection', 'doctor2go-connect' ) );
						$d2gc_toggle( 'd2gc_admin_access', __( 'Give doctor access to wp-admin', 'doctor2go-connect' ) );
						$d2gc_toggle( 'd2gc_activate_sso', __( 'Activate Single Sign On Login for doctors', 'doctor2go-connect' ), __( 'Doctors can log in to your site directly from doctor2go.online.', 'doctor2go-connect' ) );
						?>
					</div>
				</section>

				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Profile editor', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'What doctors can edit in the profile editor on the website.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_toggle(
							'd2gc_disable_booking_rules_editor',
							__( 'Deactivate the "Booking rules (availabilities & prices)" tab', 'doctor2go-connect' ),
							__( 'Doctors can also set their availabilities, prices and vacation periods in the doctor2go.online software. Turn this on if they should do that there: the tab is hidden in the profile editor and nothing is sent from the website. The questionnaires are on the first tab of the profile editor and stay editable.', 'doctor2go-connect' )
						);
						?>
					</div>
				</section>
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Placeholder image for doctors', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Shown for doctors without a profile picture.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<div class="d2gc-setting d2gc-media">
							<?php if ( get_option( 'd2gc_placeholder' ) ) { ?>
								<img class="d2gc-media__preview" src="<?php echo esc_url( wp_get_attachment_image_src( get_option( 'd2gc_placeholder' ), 'd2g-doc-pic' )[0] ?? '' ); ?>" alt="">
							<?php } ?>
							<input readonly class="hide" type="text" name="d2gc_placeholder" value="<?php echo esc_attr( get_option( 'd2gc_placeholder' ) ); ?>"/>
							<p><button class="button wpse-228085-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>
						</div>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  PATIENTS  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-patients" role="tabpanel" data-tab="patients">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Patient accounts', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Registration and login for patients.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_toggle( 'd2gc_activate_custom_login_registration', __( 'Activate custom login and registration for patients', 'doctor2go-connect' ) );
						?>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  E-MAIL  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-email" role="tabpanel" data-tab="email">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'E-mail settings', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Sender and layout of the e-mails the plugin sends.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_field( 'd2gc_sender_address', __( 'sender address', 'doctor2go-connect' ), '', 'email' );
						$d2gc_field( 'd2gc_sender_name', __( 'sender name', 'doctor2go-connect' ) );
						$d2gc_field( 'd2gc_recipient_address', __( 'recipient address', 'doctor2go-connect' ), '', 'email' );
						$d2gc_toggle( 'd2gc_activate_custom_password_mail', __( 'Activate custom password reset email', 'doctor2go-connect' ) );
						?>
						<div class="d2gc-setting d2gc-media">
							<span class="d2gc-setting__title"><?php echo esc_html__( 'logo used in e-mail', 'doctor2go-connect' ); ?></span>
							<?php if ( get_option( 'd2gc_logo' ) ) { ?>
								<img class="d2gc-media__preview" src="<?php echo esc_url( wp_get_attachment_image_src( get_option( 'd2gc_logo' ), 'full' )[0] ?? '' ); ?>" alt="">
							<?php } ?>
							<input readonly class="hide" type="text" name="d2gc_logo" value="<?php echo esc_attr( get_option( 'd2gc_logo' ) ); ?>"/>
							<p><button class="button wpse-upload"><?php echo esc_html__( 'Upload', 'doctor2go-connect' ); ?></button></p>
						</div>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  SECURITY  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-security" role="tabpanel" data-tab="security">
			<div class="d2gc-grid">
				<section class="d2gc-card d2gc-card--wide">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'ALTCHA spam protection', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'This plugin is compatible with ALTCHA, a privacy-friendly alternative to Google reCAPTCHA.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<div class="d2gc-info">
							<p>
								<strong><?php echo esc_html__( 'Status:', 'doctor2go-connect' ); ?></strong>
								<?php
								echo shortcode_exists( 'altcha' )
									? esc_html__( 'ALTCHA is active.', 'doctor2go-connect' )
									: esc_html__( 'ALTCHA is not installed or not active.', 'doctor2go-connect' );
								?>
							</p>
							<ul>
								<li><?php echo esc_html__( 'You only need the settings of the ALTCHA plugin itself, nothing has to be set here. The login, registration and password reset forms of this plugin show the ALTCHA widget automatically.', 'doctor2go-connect' ); ?></li>
								<li><?php echo esc_html__( 'Depending on your theme, different ALTCHA settings might be needed (for example which forms or pages are protected).', 'doctor2go-connect' ); ?></li>
								<li><strong><?php echo esc_html__( 'Recommended: always protect the doctor detail pages with ALTCHA, they contain many forms (booking, e-mail advice, walk-in).', 'doctor2go-connect' ); ?></strong></li>
							</ul>
						</div>
					</div>
				</section>

				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Google reCAPTCHA', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Create a Google reCAPTCHA account specifically for your website and enter the generated keys into the appropriate settings within your site’s configuration.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_field( 'd2gc_recaptcha_site_key', __( 'site key', 'doctor2go-connect' ) );
						$d2gc_field( 'd2gc_recaptcha_secret_key', __( 'secret key', 'doctor2go-connect' ), '', 'password' );
						$d2gc_toggle( 'd2gc_deactivate_recapctha_script', __( 'Deactivate loading google recaptcha script', 'doctor2go-connect' ), __( 'You can deactivate the loading from the Google recaptcha script  if necessary, particularly if it is already being loaded from another source.', 'doctor2go-connect' ) );
						?>
					</div>
				</section>

				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Two-factor authentication', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Install the "Solid Security Basic" plugin and configure two-factor authentication (2FA) for all individuals with access to the user accounts. This is can be crucial to ensure the highest level of security for sensitive patient data.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php $d2gc_toggle( 'd2gc_activate_2fa_link', __( 'I have installed "Solid Security Basic" and I have activated 2FA', 'doctor2go-connect' ) ); ?>
					</div>
				</section>
			</div>
		</div>

		<?php /* ##########  ADVANCED  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-advanced" role="tabpanel" data-tab="advanced">
			<div class="d2gc-grid">
				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Special settings', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Site wide options, use with care.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php
						$d2gc_toggle( 'd2gc_under_construction', __( 'Activate under construction', 'doctor2go-connect' ), __( 'Block all pages for not logged in users and redirect to the under construction page', 'doctor2go-connect' ) );
						$d2gc_toggle( 'd2gc_use_imgix', __( 'Use imgix image processing', 'doctor2go-connect' ), __( 'Please find more info\'s about imigix on their website.', 'doctor2go-connect' ) );
						$d2gc_toggle( 'd2gc_pseudo_translations', __( 'Use pseudo translations.', 'doctor2go-connect' ), __( 'Doctors will be translated via meta values in the post and in the meta values in the connected taxonomies.', 'doctor2go-connect' ) );
						?>
					</div>
				</section>

				<section class="d2gc-card">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Troubleshooting', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'Only enable while investigating an issue.', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php $d2gc_toggle( 'd2g_debug', __( 'Activate debug logging', 'doctor2go-connect' ), __( 'Writes extra details to the PHP error log when a request fails (e.g. the written consultation form). Only enable temporarily while investigating an issue.', 'doctor2go-connect' ) ); ?>
					</div>
				</section>

				<section class="d2gc-card d2gc-card--wide">
					<header class="d2gc-card__header">
						<h2><?php echo esc_html__( 'Doctor sync log', 'doctor2go-connect' ); ?></h2>
						<p><?php echo esc_html__( 'The last 50 requests from doctor2go.online to the doctor sync URL, newest first. With "Activate debug logging" on, the full request is stored too (hash, keys and passwords shortened).', 'doctor2go-connect' ); ?></p>
					</header>
					<div class="d2gc-card__body">
						<?php $d2gc_sync_log = D2GC_Rest::get_log(); ?>
						<?php if ( empty( $d2gc_sync_log ) ) { ?>
							<p><?php echo esc_html__( 'No requests yet.', 'doctor2go-connect' ); ?></p>
						<?php } else { ?>
							<div class="d2gc-log">
								<table class="widefat striped">
									<thead>
										<tr>
											<th><?php echo esc_html__( 'Time', 'doctor2go-connect' ); ?></th>
											<th><?php echo esc_html__( 'Status', 'doctor2go-connect' ); ?></th>
											<th><?php echo esc_html__( 'Result', 'doctor2go-connect' ); ?></th>
											<th><?php echo esc_html__( 'Doctor', 'doctor2go-connect' ); ?></th>
											<th><?php echo esc_html__( 'Request', 'doctor2go-connect' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $d2gc_sync_log as $d2gc_entry ) { ?>
											<tr>
												<td><?php echo esc_html( $d2gc_entry['time'] ?? '' ); ?></td>
												<td><strong><?php echo esc_html( $d2gc_entry['status'] ?? '' ); ?></strong><br><code><?php echo esc_html( $d2gc_entry['code'] ?? '' ); ?></code></td>
												<td><?php echo esc_html( $d2gc_entry['result'] ?? '' ); ?></td>
												<td><?php echo esc_html( $d2gc_entry['user_email'] ?? '' ); ?><br><code><?php echo esc_html( $d2gc_entry['wcc_user_id'] ?? '' ); ?></code></td>
												<td>
													<?php echo esc_html( ( $d2gc_entry['method'] ?? '' ) . ' · ' . ( $d2gc_entry['content_type'] ?: '-' ) . ' · ' . ( $d2gc_entry['body_bytes'] ?? 0 ) . ' bytes · IP ' . ( $d2gc_entry['ip'] ?? '' ) ); ?><br>
													<span class="description"><?php echo esc_html( $d2gc_entry['keys'] ?? '' ); ?></span>
													<?php if ( isset( $d2gc_entry['payload'] ) ) { ?>
														<details>
															<summary><?php echo esc_html__( 'Request and response', 'doctor2go-connect' ); ?></summary>
															<pre><?php echo esc_html( wp_json_encode( array( 'request' => $d2gc_entry['payload'], 'response' => $d2gc_entry['response'] ?? null ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></pre>
														</details>
													<?php } ?>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						<?php } ?>
					</div>
				</section>
			</div>
		</div>

		<div class="d2gc-savebar">
			<?php submit_button( null, 'primary', 'submit', false ); ?>
		</div>
	</form>

	<?php if ( $d2gc_has_translations ) { ?>
		<?php /* ##########  TRANSLATIONS (outside the form, AJAX)  ########## */ ?>
		<div class="d2gc-panel" id="d2gc-panel-translations" role="tabpanel" data-tab="translations">
			<section class="d2gc-card" id="d2gc-translations" data-nonce="<?php echo esc_attr( wp_create_nonce( 'd2gc_translations' ) ); ?>">
				<header class="d2gc-card__header">
					<h2><?php echo esc_html__( 'Translations of doctor profiles', 'doctor2go-connect' ); ?></h2>
					<p>
						<?php echo esc_html__( 'Doctor data that is the same in every language (contact details, prices, booking rules, vacation periods, profile picture, specialties, languages and country) is kept the same in all translations when a profile is saved.', 'doctor2go-connect' ); ?>
						<?php echo esc_html__( 'Here you can check existing doctors and align their translations. The most recently edited translation of a doctor is used as the source. The publication status is not changed.', 'doctor2go-connect' ); ?>
					</p>
				</header>
				<div class="d2gc-card__body">
					<p class="d2gc-actions">
						<button type="button" class="button" id="d2gc-translations-check"><?php echo esc_html__( 'Check translations', 'doctor2go-connect' ); ?></button>
						<button type="button" class="button button-primary" id="d2gc-translations-align" disabled><?php echo esc_html__( 'Align all', 'doctor2go-connect' ); ?></button>
						<span class="spinner" id="d2gc-translations-spinner"></span>
					</p>
					<div id="d2gc-translations-result" aria-live="polite"></div>
				</div>
			</section>
		</div>
	<?php } ?>
</div>
