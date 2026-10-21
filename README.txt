=== Doctor2Go Connect ===
Contributors: doctor2go, playfield-media
Tags: online practice, doctors, appointments, telemedicine, booking
Requires at least: 5.6
Tested up to: 7.1
Requires PHP: 7.3
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress site to doctor2go.online: doctor profiles, online booking, e-mail advice and a patient dashboard.

== Description ==

Doctor2Go Connect turns your WordPress site into the public front end of an online medical practice. It connects your site to [doctor2go.online](https://doctor2go.online), the online practice software by Webcamconsult BV, where appointments, payments, video consultations and patient files are managed.

Patients find a doctor on your site, book a video, physical or walk-in consultation, request written e-mail advice and manage their appointments in a personal dashboard. Doctors keep their public profile up to date directly from the front end.

A doctor2go.online subscription is required. Patient and appointment data is processed and stored by doctor2go.online, see the External Services section below.

= Features =

* **Doctor profiles:** unlimited public doctor profiles with specialty, languages, locations, prices, education, experience, publications and a booking calendar.
* **Doctor overview with search:** filter by specialty, language, country and name.
* **Online booking:** video, physical and walk-in consultations based on the availability in doctor2go.online, with different prices per time slot.
* **E-mail advice:** patients request a written assessment, including photos and medical history.
* **Payments:** handled securely by doctor2go.online (including Stripe and PayPal), with optional VAT on consultation prices.
* **E-mail notifications:** doctors and patients are notified about bookings. All e-mail templates can be customised.
* **Patient area:** registration, login, password reset, appointment overview, favourite doctors, intake questionnaires and access to the secure doctor2go.online patient portal with chat and medical records.
* **Front-end profile editor:** doctors edit their public profile and availability without access to wp-admin.
* **Shortcodes:** place every part (doctor listing, search, calendar, forms, patient pages) on any page.
* **Multilingual:** compatible with Polylang and WPML.

= Doctor accounts =

Doctor users and their public profiles are created and updated automatically by doctor2go.online, so the connection details of each doctor are always set correctly. Doctors can log in to your site directly from doctor2go.online (single sign-on), if this option is enabled.

= Patient accounts =

The plugin includes its own registration, login and password reset forms that can be placed on any page with a shortcode. They include the required consent checkboxes linked to your terms and conditions, privacy policy and disclaimer pages, and can optionally be protected with Google reCAPTCHA or ALTCHA.

You can also use the standard WordPress registration and login. In that case, enable registration and set the **New User Default Role** (Settings → General) to **Patient**.

Some features are optional and need to be enabled on the plugin settings page.

= Works well with =

* Yoast SEO
* Polylang and WPML
* Solid Security, recommended for medical and privacy-sensitive websites
* Google reCAPTCHA v2 and ALTCHA

== Installation ==

1. In wp-admin, go to Plugins → Add New and search for "Doctor2Go Connect".
2. Click **Install Now** and then **Activate**.
3. Go to **Doctor2Go Connect** in the admin menu and enter the API details of your doctor2go.online account.
4. Place the shortcodes on your pages. An overview of all shortcodes is available under **Doctor2Go Connect → Shortcodes info**.

To install manually, upload the `doctor2go-connect` folder to `/wp-content/plugins/` and activate the plugin on the Plugins screen.

== External Services ==

This plugin connects to the external services listed below. For each service you will find what it is used for, which data is sent and when, and links to its terms of service and privacy policy.

= doctor2go.online (Webcamconsult) API =

This plugin is the WordPress front end of the doctor2go.online online practice software by Webcamconsult BV, and a doctor2go.online subscription is required. Doctors, appointments, payments, video consultations and patient files are managed in doctor2go.online. The plugin communicates with the API URL that you enter in the plugin settings.

Data is exchanged in the following situations:

* Doctor profiles: when a doctor account is created or changed in doctor2go.online, doctor2go.online sends the doctor's name, e-mail address, organisation, locations and tariffs to your site, where the WordPress user and the public doctor profile are created or updated.
* Doctor availability: when a doctor profile, doctor listing or booking calendar is shown, the plugin requests the doctor's available time slots. Only the doctor's key is sent, no visitor data.
* Booking an appointment (video, physical or walk-in consultation): the patient's name, e-mail address, phone number, date of birth, salutation, country, language, time zone and reason for the consultation are sent, together with the chosen doctor, date, location and price. The patient is looked up by e-mail address in doctor2go.online and a patient record is created there if it does not exist yet.
* E-mail advice (written consultation): the patient's details listed above, plus the health information entered in the form: description of the complaint, location on the body, when it was first noticed, medical and treatment history, an optional AI assessment and the uploaded photos.
* Patient dashboard: when a logged-in patient opens the dashboard, the patient's appointments and portal links are requested. Registering an account on your site only creates a WordPress user and sends nothing to doctor2go.online.
* Cancelling an appointment: the appointment ID is sent.
* Doctors editing their availability in the front end: the booking rules are sent.
* Patients are taken to doctor2go.online for payment, the video waiting room, intake questionnaires and the patient portal.

Terms of service: [https://doctor2go.online/Algemene%20voorwaarden-Webcamconsult-EN.pdf](https://doctor2go.online/Algemene%20voorwaarden-Webcamconsult-EN.pdf)
Privacy policy: [https://doctor2go.online/en/privacy-policy](https://doctor2go.online/en/privacy-policy)

= Google reCAPTCHA (optional) =

Only active when you enter your own reCAPTCHA keys in the plugin settings. It protects the registration, login, booking, walk-in and e-mail advice forms against spam. Without keys, nothing is loaded from or sent to Google.

* Data sent: the reCAPTCHA script is loaded from Google on pages with these forms, which sends the visitor's IP address and browser data to Google. When a form is submitted, the reCAPTCHA token is sent to Google for verification.
* Terms of service: [https://policies.google.com/terms](https://policies.google.com/terms)
* Privacy policy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

= Google Maps (embedded map) =

On doctor profile pages with practice locations, each location is shown on an embedded Google Maps map.

* Data sent: the address of the location is sent to Google to show the map. When the map loads, Google also receives the visitor's IP address and browser data.
* Terms of service: [https://maps.google.com/help/terms_maps/](https://maps.google.com/help/terms_maps/)
* Privacy policy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

= OpenAI API (optional, admin only) =

Used by the translate box on the doctor edit screen in wp-admin, to translate a doctor profile into another language. Only available when the constant `D2GC_AI_API_KEY` with your own OpenAI API key is defined in wp-config.php, and only used when an editor starts a translation.

* Data sent: the texts of the doctor profile that is being translated: job title, subtitle, city, organisation, country of registration, education, work experience and publications, and optionally the profile title and description. No patient data is sent.
* Terms of use: [https://openai.com/policies/terms-of-use](https://openai.com/policies/terms-of-use)
* Privacy policy: [https://openai.com/policies/privacy-policy](https://openai.com/policies/privacy-policy)

= ALTCHA (optional, via the ALTCHA plugin) =

If the separate ALTCHA plugin is active, its widget is shown on the login, registration and lost password forms. Verification is handled entirely by the ALTCHA plugin. Doctor2Go Connect does not send any data to ALTCHA itself.

== Third-party libraries ==

The following libraries are bundled in their distributed form (no build step), all under GPL-compatible licenses. They run in the browser and do not send data anywhere.

* Bootstrap 5.3.8 (MIT): [https://getbootstrap.com](https://getbootstrap.com)
* FullCalendar 6.1.15 with the Moment Timezone plugin (MIT): [https://fullcalendar.io](https://fullcalendar.io)
* Moment Timezone (MIT): [https://momentjs.com/timezone/](https://momentjs.com/timezone/)
* Select2 4.1.0-rc.0 (MIT): [https://select2.org](https://select2.org)
* jQuery ScrollTo 2.1.3 (MIT): [https://github.com/flesler/jquery.scrollTo](https://github.com/flesler/jquery.scrollTo)

== Frequently Asked Questions ==

= Do I need a doctor2go.online account? =

Yes. Doctor2Go Connect is the WordPress front end of doctor2go.online. Appointments, payments, video consultations and patient files are handled there. Visit [doctor2go.online](https://doctor2go.online) for more information.

= Where do I find the available shortcodes? =

In wp-admin under **Doctor2Go Connect → Shortcodes info** you find all shortcodes with a short description and their options.

= Does it work with my theme? =

Doctor2Go Connect works with any theme. The plugin ships with its own Bootstrap based styling, which you can switch off in the settings if your theme already loads Bootstrap or if you prefer your own styles. Templates can be overridden in your theme.

= Can I use it together with WooCommerce or other booking plugins? =

Yes. Consultations are paid through doctor2go.online, so there is no conflict with the cart or checkout of other plugins.

= Which payment methods are supported? =

Payments are processed by doctor2go.online, which supports among others Stripe and PayPal.

= Where can I get support? =

Please use the support forum of this plugin on WordPress.org.

== Screenshots ==

1. Doctor overview page with search filters
2. Doctor profile page with booking options
3. Patient appointments overview

== Changelog ==

= 1.0.1 =
* Security: profile, profile picture, booking rule and vacation updates are only possible for the doctor who owns the profile (or an editor/administrator).
* Security: appointments can only be cancelled, and cancellation e-mails only be sent, by the patient of that appointment.
* Security: reCAPTCHA on the plugin login form no longer blocks logins via wp-login.php.
* New: price and currency per booking rule (simple and advanced editor), loader and messages when saving.
* New: vacation periods in the profile editor, synchronised with doctor2go.online; e-mail advice is paused and a notice is shown during a vacation.
* Replaced fancyBox with Bootstrap modals (licence), removed unused libraries and fonts.
* Fixed: walk-in form data, unclosed HTML tags in the booking rule editor, option names in the walk-in flow, price display with several prices.
* The sidebar detail layout (v1) is no longer offered, existing sites fall back to the full width layout (v2).

= 1.0.0 =
* Initial release.
