<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.webcamconsult.com
 * @since      1.0.0
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 * @author     Webcamconsult
 */
class D2gConnect_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		unregister_post_type( 'd2g_doctor' );
	}
}
