<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.webcamconsult.com
 * @since      1.0.0
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    d2g-connect
 * @subpackage d2g-connect/includes
 * @author     Webcamconsult
 */
class D2gConnect {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      \D2gConnect_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'doctor2go-connect';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - \D2gConnect_Loader. Orchestrates the hooks of the plugin.
	 * - \D2gConnect_Admin. Defines all hooks for the admin area.
	 * - \D2gConnect_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-d2g-connect-loader.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-d2g-connect-admin.php';

		// booking functions & patient functions
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-d2g-booking-patient.php';

		// create update doc user and doc profile functions
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-d2g-doc-user-profile.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once plugin_dir_path( __DIR__ ) . 'public/class-d2g-connect-public.php';

		// functions needed for the view
		require_once plugin_dir_path( __DIR__ ) . 'public/template-functions.php';

		// block functions
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/d2g-blocks.php';

		// creates a full doctor profile data obj.
		require_once plugin_dir_path( __DIR__ ) . 'public/class-d2g-profile-data.php';

		$this->loader = new \D2gConnect_Loader();
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// this might becomde dynamic from a settings page
		$taxonomies = array(
			'country-operation',
			'country-origin',
			'doctor-language',
			'disease',
			'doctor-degree',
			'doctor-specialty',
		);

		$plugin_admin = new \D2gConnect_Admin( $this->get_plugin_name(), $this->get_version() );

		// functions to add scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// adds the admin settings pages menu item & register settings function
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_d2g_settings' );

		// adds custom post type + custom taxonmies
		$this->loader->add_action( 'init', $plugin_admin, 'activate_myplugin_cpt' );
		$this->loader->add_action( 'init', $plugin_admin, 'activate_myplugin_tax' );

		// adds image sizes
		$this->loader->add_action( 'init', $plugin_admin, 'd2g_image_sizes' );
		$this->loader->add_action( 'init', $plugin_admin, 'd2g_hide_admin_bar' );

		// this changes the admin columns for doctors
		$this->loader->add_filter( 'manage_d2g_doctor_posts_columns', $plugin_admin, 'd2g_filter_posts_columns' );
		$this->loader->add_filter( 'manage_pages_columns', $plugin_admin, 'd2g_filter_page_columns' );
		$this->loader->add_filter( 'manage_d2g_emails_posts_columns', $plugin_admin, 'd2g_filter_email_columns' );
		$this->loader->add_action( 'manage_d2g_doctor_posts_custom_column', $plugin_admin, 'd2g_doctor_column', 10, 2 );
		$this->loader->add_action( 'manage_pages_custom_column', $plugin_admin, 'd2g_page_column', 10, 2 );
		$this->loader->add_action( 'manage_d2g_emails_posts_custom_column', $plugin_admin, 'd2g_email_column', 10, 2 );

		$this->loader->add_filter( 'manage_edit-d2g_doctor_sortable_columns', $plugin_admin, 'd2g_doctor_sortable_columns' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'd2g_posts_orderby' );

		// this changes the admin columns for users
		$this->loader->add_filter( 'manage_users_columns', $plugin_admin, 'd2g_modify_user_table' );
		$this->loader->add_filter( 'manage_users_custom_column', $plugin_admin, 'new_modify_user_table_row', 10, 3 );

		// metaboxes
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'd2g_meta_box_add' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'd2g_meta_box_save' );

		add_action(
			'admin_enqueue_scripts',
			function () {
				// if possible try not to queue this all over the admin by adding your settings GET page val into next
				$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin page conditional, no action performed
				if ( empty( $page ) || 'd2g-connect-admin-display' !== $page ) {
					return; }

				wp_enqueue_media();
			}
		);

		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new \D2gConnect_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'd2g_register_shortcodes' );

		// load more doctors
		$this->loader->add_action( 'wp_ajax_doctor_call', $plugin_public, 'doctor_call' );
		$this->loader->add_action( 'wp_ajax_nopriv_doctor_call', $plugin_public, 'doctor_call' );
		$this->loader->add_action( 'wp_ajax_doctor_count_call', $plugin_public, 'doctor_count_call' );
		$this->loader->add_action( 'wp_ajax_nopriv_doctor_count_call', $plugin_public, 'doctor_count_call' );

		// single sign on hook for doctors coming from the D2G software
		$this->loader->add_action( 'template_redirect', $plugin_public, 'd2g_sso' );

		// function to override the standard lost password mail
		$this->loader->add_filter( 'retrieve_password_message', $plugin_public, 'd2g_retrieve_password_message', 10, 4 );
		$this->loader->add_filter( 'wp_mail_from', $plugin_public, 'd2g_wp_mail_from', 10, 1 );
		$this->loader->add_filter( 'wp_mail_from_name', $plugin_public, 'd2g_wp_mail_from_name', 10, 1 );

		// reirect users after login
		$this->loader->add_filter( 'login_redirect', $plugin_public, 'd2g_login_redirect', 10, 3 );

		// ajax function for handling liked posts
		$this->loader->add_action( 'wp_ajax_handle_like', $plugin_public, 'd2g_handle_like' );
		$this->loader->add_action( 'wp_ajax_nopriv_handle_like', $plugin_public, 'd2g_handle_like' );

		// send d2gc email
		$this->loader->add_action( 'wp_ajax_send_ajax_d2g_email', $plugin_public, 'send_ajax_d2g_email' );
		$this->loader->add_action( 'wp_ajax_nopriv_send_ajax_d2g_email', $plugin_public, 'send_ajax_d2g_email' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    \D2gConnect_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
