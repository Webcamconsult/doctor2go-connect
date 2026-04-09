<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://doctor2go.online
 * @since             1.0.0
 * @package           doctor2go-connect
 *
 * @wordpress-plugin
 * Plugin Name:       Doctor2Go Connect
 * Plugin URI:        https://demo-plugin.doctor2go.online/
 * Description:       With this plugin you can manage doctor profiles on your website, make a connection with the Webcamconsult software. Patients can book you on your profile page and they can create a patient profile for follow up.
 * Version:           1.0.0
 * Author:            Webcamconsult
 * Author URI:        https://www.webcamconsult.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       doctor2go-connect
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'D2GC_PLUGIN_FILE', __FILE__ );
define( 'D2GC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'D2GC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );




/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-d2gConnect-activator.php
 */
function activate_d2gConnect() {
	require_once D2GC_PLUGIN_DIR. 'includes/class-d2g-connect-activator.php';
	D2gConnect_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_d2gConnect' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-d2g-connect-deactivator.php
 */
function deactivate_d2gConnect() {
	require_once D2GC_PLUGIN_DIR. 'includes/class-d2g-connect-deactivator.php';
	D2gConnect_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_d2gConnect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-d2g-connect.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_d2gConnect() {

	$plugin = new D2gConnect();
	$plugin->run();
}
run_d2gConnect();
