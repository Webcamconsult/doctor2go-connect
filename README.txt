Here is your updated readme with Bootstrap 5 added to the “External Services” section:

=== Doctor2Go Connect ===
Contributors: doctor2go (Webcamconsult BV), playfield-media (BV Playfield-Media)
Tags: online practice, medical websites, doctors, bookings
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)

Online practice: Doctor2Go Connect links your WordPress site to doctor2go.online, giving patients a dashboard to book and manage appointments.

== Description ==
Turn your WordPress site into an online medical practice by connecting it to doctor2go.online. Patients can book and manage appointments, access medical files, and receive paid advice or online consultations. 
All data is securely handled via doctor2go.online’s API and databases. A doctor2go.online subscription is required—click the link to learn more.

With Doctor2Go Connect you get:

* Smooth booking process for video and physical consultations
* E-mail advice
* Patient dashboard
* SEO-friendly & compatible with popular plugins like Yoast SEO and Polylang
* Hassle-free payments through secure and popular payment gateways

=== Core Features ===

* Unlimited doctor profiles: Create an unlimited number of doctor profiles (that your server can store). Show all relevant information about the doctor, like specialty, languages, location, prices, booking calendar, and more.
* Search filters on the overview page: specialty / language / country / doctor name
* Payment system: Stripe and PayPal
* Multiple pricing and availabilities: Add different pricing for weekdays vs weekends.
* Tax options: Add tax to consultation prices.
* Email notifications: Doctors and patients receive notifications for bookings. All email templates are customizable.
* Patient area: Registration, login, lost password, appointments listing, liked doctors, questionnaires (intake forms), and access to the secure patient portal from doctor2go.online with chat and EMR.
* Shortcodes: Flexible shortcodes to configure your pages.

=== Integrations ===

* Yoast SEO: Enhance website SEO with seamless integration.
* Polylang: Translate your website into multiple languages for a global audience.

== Installation ==

=== Using the WordPress Dashboard ===
Navigate to the 'Add New' in the Plugins dashboard
Search for "Doctor2Go Connect"
Click Install Now
Activate the plugin on the Plugin dashboard

=== Uploading in WordPress Dashboard ===
Navigate to the 'Add New' in the Plugins dashboard
Go to the 'Upload' area
Select Doctor2Go Connect.zip from your computer
Click 'Install Now'
Activate the plugin on the Plugin dashboard

=== Using FTP ===
Download Doctor2Go Connect.zip
Extract the Doctor2Go Connect directory to your computer
Upload the Doctor2Go Connect directory to the /wp-content/plugins/ directory
Activate the plugin in the Plugin dashboard

== External Services ==
This plugin relies on some third-party/external services and libraries for certain functionality. Users should be aware of what data is sent and why.

All third-party JavaScript and CSS libraries (such as Bootstrap, FullCalendar, Moment.js, Moment Timezone, Datejs, Fancybox, Select2, and related 
locale/extension files) are included as downloaded vendor files from their official sources, without any custom build process or transpilation. 
The plugin uses these libraries in their distributed form (no bundlers or build tools like webpack, Babel, or npm are used).



=== Google reCAPTCHA ===
- Service: Google reCAPTCHA v2 / v3
- Purpose: Prevents spam and automated bots on forms (e.g., doctor registration or booking forms).
- Data sent: User's reCAPTCHA token and metadata (such as IP address) are sent to Google when a form is submitted.
- Terms of Service: [https://policies.google.com/terms](https://policies.google.com/terms)
- Privacy Policy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

=== FullCalendar ===
- Service: FullCalendar JavaScript library
- Purpose: Provides a dynamic calendar UI for displaying doctor availability and appointments.
- Data sent: None directly; only used to render calendar events within the plugin.
- License & Documentation: [https://fullcalendar.io/docs](https://fullcalendar.io/docs) and [https://fullcalendar.io/license](https://fullcalendar.io/license)

=== Google Calendar API (optional) ===
- Service: Google Calendar API v3
- Purpose: If calendar sync is enabled, retrieves doctor appointments from Google Calendar.
- Data sent: Calendar IDs and event metadata may be sent to Google servers.
- Terms of Service: [https://developers.google.com/terms](https://developers.google.com/terms)
- Privacy Policy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

=== Fancybox JS ===
- Service: Fancybox JavaScript library
- Purpose: Used to display modal popups/lightboxes for images and other content in the plugin.
- Data sent: None; purely client-side library.
- License & Documentation: [https://fancyapps.com/docs/ui/fancybox/](https://fancyapps.com/docs/ui/fancybox/)

=== jQuery ScrollTo JS ===
- Service: jQuery ScrollTo library
- Purpose: Smooth scrolling to anchors or elements in the plugin UI.
- Data sent: None; purely client-side library.
- License & Documentation: [https://github.com/flesler/jquery.scrollTo](https://github.com/flesler/jquery.scrollTo)

=== Select2 JS ===
- Service: Select2 JavaScript library
- Purpose: Enhances select boxes with search and better UX for dropdowns in plugin forms.
- Data sent: None; purely client-side library.
- License & Documentation: [https://select2.org/](https://select2.org/)

=== Bootstrap 5 ===
- Service: Bootstrap 5 CSS and JavaScript framework
- Purpose: Provides responsive layout, grid system, and UI components used in the plugin’s frontend output.
- Data sent: None by itself; Bootstrap is a front-end CSS/JS framework and does not send data to external servers.
- License & Documentation: [https://getbootstrap.com/docs/5.3/getting-started/introduction/](https://getbootstrap.com/docs/5.3/getting-started/introduction/) and [https://github.com/twbs/bootstrap/blob/main/LICENSE](https://github.com/twbs/bootstrap/blob/main/LICENSE)

== Frequently Asked Questions ==

=== Where can I find Doctor2Go Connect documentation and user guides? ===
In the plugin folder you will find the guide.

=== There is something cool you could add... ===
This feature is still to come.

=== Where can I get support? ===
This feature is still to come.

=== Will Doctor2Go Connect work with my theme? ===
Yes, Doctor2Go Connect will work with any theme, but may require some styling adjustments to match nicely. Works perfectly with the default WordPress theme.

=== Can I run WooCommerce or Easy Digital Downloads and WP Travel at the same time? ===
Yes, there are no technological conflicts. Each plugin handles its own cart system; Doctor2Go Connect bookings and consultations use their own payment gateways.

=== Does the plugin have payment gateways? ===
Doctor2Go Connect uses the payment gateways from the doctor2go.online software.

== Screenshots ==
1. Overview page from doctors
2. Detail page from doctor
3. Patient appointments page

== Changelog ==
No changelog yet

== Upgrade Notice ==
N/A