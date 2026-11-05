<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://plugin.doctor2go.online
 * @since      1.0.0
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    d2g-connect
 * @subpackage d2g-connect/admin
 * @author     Webcamconsult
 */
class D2gConnect_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function d2gc_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in D2gConnect_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The D2gConnect_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/d2g-connect-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-grid', plugin_dir_url( __FILE__ ) . 'css/d2g-grid.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-fontello', plugin_dir_url( __FILE__ ) . 'fonts/fontello/css/fontello.css', array(), $this->version, 'all' );

		// select2 for the Filters box on the doctor edit screen (the copy the public side uses)
		if ( $this->d2gc_is_doctor_edit_screen() ) {
			wp_enqueue_style( $this->plugin_name . '-select', plugins_url( 'public/css/select2.min.css', __DIR__ ), array(), $this->version, 'all' );
		}
	}

	/**
	 * @return bool whether the current admin screen is the add / edit screen of a doctor
	 */
	private function d2gc_is_doctor_edit_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && 'post' === $screen->base && 'd2g_doctor' === $screen->post_type;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function d2gc_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webcamconsult_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webcamconsult_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$deps = array( 'jquery' );
		if ( $this->d2gc_is_doctor_edit_screen() ) {
			wp_register_script( $this->plugin_name . '-select', plugins_url( 'public/js/select2.full.min.js', __DIR__ ), array( 'jquery' ), $this->version, false );
			$deps[] = $this->plugin_name . '-select';
		}
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/d2g-connect-admin.js', $deps, $this->version, false );
		wp_localize_script( $this->plugin_name, 'myStrData', [
			'str_start' 				=> esc_html__( 'start date', 'doctor2go-connect' ),
			'str_end' 					=> esc_html__( 'end date', 'doctor2go-connect' ),
			'str_study_area' 			=> esc_html__( 'study area', 'doctor2go-connect' ),
			'str_degree' 				=> esc_html__( 'degree', 'doctor2go-connect' ),
			'str_institution' 			=> esc_html__( 'institution', 'doctor2go-connect' ),
			'str_expertise' 			=> esc_html__( 'expertise', 'doctor2go-connect' ),
			'str_position' 				=> esc_html__( 'position', 'doctor2go-connect' ),
			'str_organisation' 			=> esc_html__( 'organisation / company', 'doctor2go-connect' ),
			'str_title' 				=> esc_html__( 'title', 'doctor2go-connect' ),
			'str_web_link' 				=> esc_html__( 'web link', 'doctor2go-connect' ),
			'str_journal' 				=> esc_html__( 'journal', 'doctor2go-connect' ),
			'str_type_of_publication' 	=> esc_html__( 'type of publication', 'doctor2go-connect' ),
			'str_author' 				=> esc_html__( 'author', 'doctor2go-connect' ),
			'str_publication_date' 		=> esc_html__( 'publication date', 'doctor2go-connect' ),

			// settings page: translations of doctor profiles
			'tr_none'          => esc_html__( 'All translations are aligned.', 'doctor2go-connect' ),
			/* translators: 1: doctors with differences, 2: doctors with translations */
			'tr_summary'       => esc_html__( '%1$d of %2$d doctors with translations have differences.', 'doctor2go-connect' ),
			/* translators: %d: number of languages */
			'tr_languages'     => esc_html__( '%d languages', 'doctor2go-connect' ),
			'tr_differs_in'    => esc_html__( 'differs in:', 'doctor2go-connect' ),
			'tr_align_one'     => esc_html__( 'align', 'doctor2go-connect' ),
			/* translators: 1: aligned doctors, 2: total */
			'tr_progress'      => esc_html__( 'Aligning… %1$d of %2$d', 'doctor2go-connect' ),
			'tr_error'         => esc_html__( 'Something went wrong, please try again.', 'doctor2go-connect' ),
			// manual availability sync (Doctors tab)
			'as_progress'      => esc_html__( 'Syncing… %1$d of %2$d doctors', 'doctor2go-connect' ),
			'as_done'          => esc_html__( '%d doctors synced.', 'doctor2go-connect' ),
			'as_failed'        => esc_html__( 'Not synced (no answer from doctor2go.online or no doctor key):', 'doctor2go-connect' ),
			'as_none'          => esc_html__( 'No doctors linked to doctor2go.online found.', 'doctor2go-connect' ),
			// Filters tab on the doctor edit screen
			/* translators: %s: list of filters, e.g. "Countries, Languages" */
			'filters_missing'  => esc_html__( 'Please choose at least one value for: %s', 'doctor2go-connect' ),
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
		]);
	}

	/**
	 * Add menu options to the WordPress admin
	 */
	public function d2gc_register_menu() {
		add_menu_page(
			'Doctor2Go Connect',
			'Doctor2Go Connect',
			'manage_options',
			'd2g-connect-admin-display',
			function () {
				require_once 'partials/d2g-connect-admin-display.php';
			}
		);

		add_submenu_page(
			'd2g-connect-admin-display',
			'D2GC shortcodes info',
			'Shortcodes info',
			'manage_options',
			'd2g-connect-inline-widgets',
			function () {
				require_once 'partials/d2g-connect-shortcode-help.php';
			}
		);
	}

	/**
	 * register the widget
	 */
	public function register_widget() {
		// register_widget('D2gConnect_Widget');
		// this is for the connect version
	}

	public function get_widgets() {
		// this is for the connect version
	}

	public function set_client_id() {
		// this is for the connect version
	}

	/**
	 * Register the custom posttype for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function d2gc_activate_myplugin_cpt() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webcamconsult_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webcamconsult_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$args = array(
			'label'           => esc_html__( 'Doctors', 'doctor2go-connect' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'menu_icon'       => 'dashicons-businessman',
			'rewrite'         => array(
				'slug'       => 'doctor',
				'with_front' => false,
			),
			'query_var'       => true,
			'supports'        => array(
				'title',
				'editor',
				'excerpt',
				'custom-fields',
				'revisions',
				'thumbnail',
				'author',
			),
		);
		register_post_type( 'd2g_doctor', $args );

		$args = array(
			'label'           => esc_html__( 'D2GC E-mail', 'doctor2go-connect' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'menu_icon'       => 'dashicons-email',
			'rewrite'         => array(
				'slug'       => 'email',
				'with_front' => false,
			),
			'query_var'       => true,
			'supports'        => array(
				'title',
				'editor',
				'excerpt',
				'custom-fields',
				'revisions',
				'thumbnail',
				'author',
			),
		);
		register_post_type( 'd2g_emails', $args );
	}

	/*
	* adds custom taxonomies
	*/
	public function d2gc_activate_myplugin_tax() {
		$taxonomies = $this->d2gc_get_d2g_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$labels = $this->d2gc_get_tax_label( $taxonomy );
			$args   = array(
				'hierarchical'          => true,
				'labels'                => $labels,
				'public'                => true,
				'show_ui'               => true,
				'show_in_rest'          => true,
				'show_admin_column'     => true,
				'meta_box_cb'           => false, // edited as select boxes in the "Filters" box (d2gc_meta_box_filters_cb)
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array(
					'slug'         => $taxonomy,
					'with_front'   => false, // Don't display the category base before
					'hierarchical' => true,
				),
			);

			register_taxonomy( $taxonomy, array( 'd2g_doctor' ), $args );

		}
	}

	/**
	 * custom taxonimies for the website are declared here
	 */
	public function d2gc_get_d2g_taxonomies() {
		return array(
			// 'country-operation',
			'country-origin',
			'doctor-language',
			// 'disease',
			// 'doctor-degree',
			'doctor-specialty',
			'body-part',
		);
	}

	/**
	 * The doctor taxonomies that need at least one term before a profile can be published in wp-admin.
	 *
	 * @return string[]
	 */
	public static function d2gc_get_required_taxonomies() {
		/**
		 * Filters the doctor taxonomies that need at least one term (wp-admin, "Filters" tab).
		 *
		 * @param string[] $taxonomies
		 */
		return (array) apply_filters( 'd2gc_required_doctor_taxonomies', array( 'country-origin', 'doctor-language', 'doctor-specialty' ) );
	}

	/**
	 * The doctor meta fields marked as required (*) in the front-end profile editor.
	 *
	 * @return string[] meta keys
	 */
	public static function d2gc_get_required_fields() {
		$fields = array(
			'd2g_first_name',
			'd2g_last_name',
			'd2g_emp_title',
			'd2g_main_email',
			'walk_in_price',
			'walk_in_currency',
			'written_con_price',
			'written_con_currency',
			'video_con_price',
			'video_con_currency',
		);
		// the questionnaire is only shown in the editor when the doctors are WCC users
		if ( get_option( 'd2gc_local_user' ) != 1 ) {
			$fields[] = 'written_con_type';
		}

		/**
		 * Filters the doctor meta fields that need a value (reported by the doctor-status REST route).
		 *
		 * @param string[] $fields meta keys
		 */
		return (array) apply_filters( 'd2gc_required_doctor_fields', $fields );
	}

	/**
	 * (wp_insert_post_data) Second line of defence behind the check in d2g-connect-admin.js: a doctor saved from the
	 * wp-admin form without the required filters is not published (stays / becomes draft), a notice says why.
	 * Profiles that are already online stay online.
	 *
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public function d2gc_check_required_filters( $data, $postarr ) {
		if ( 'd2g_doctor' !== $data['post_type'] || 'publish' !== $data['post_status'] || empty( $postarr['ID'] ) ) {
			return $data;
		}
		// only the wp-admin form (the Filters box sends its own nonce); WCC, the profile editor, quick edit: unchanged
		$nonce = isset( $_POST['d2gc_filters_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['d2gc_filters_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'd2gc_filters' ) || 'publish' === get_post_status( $postarr['ID'] ) ) {
			return $data;
		}

		$missing = array();
		foreach ( self::d2gc_get_required_taxonomies() as $taxonomy ) {
			$terms = isset( $postarr['tax_input'][ $taxonomy ] ) ? array_filter( (array) $postarr['tax_input'][ $taxonomy ] ) : array();
			if ( ! $terms && taxonomy_exists( $taxonomy ) ) {
				$missing[] = get_taxonomy( $taxonomy )->labels->name;
			}
		}
		if ( $missing ) {
			$data['post_status'] = 'draft';
			set_transient( 'd2gc_filters_missing_' . get_current_user_id(), $missing, MINUTE_IN_SECONDS );
		}
		return $data;
	}

	/**
	 * (admin_notices) Why a doctor was not published, see d2gc_check_required_filters().
	 */
	public function d2gc_required_filters_notice() {
		$key     = 'd2gc_filters_missing_' . get_current_user_id();
		$missing = get_transient( $key );
		if ( ! $missing ) {
			return;
		}
		delete_transient( $key );
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				/* translators: %s: list of filters, e.g. "Countries, Languages" */
				echo esc_html( sprintf( __( 'The profile was saved as draft and not published: please choose at least one value for %s (Filters tab).', 'doctor2go-connect' ), implode( ', ', $missing ) ) );
				?>
			</p>
		</div>
		<?php
	}

	private function d2gc_get_tax_label( $taxonomy ) {
		/***************doctor taxonomies*/

		$labels['country-operation'] = array(
			'name'              => _x( 'Counytries of operations', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Counytry of operations', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Counytry of operations', 'doctor2go-connect' ),
		);

		$labels['country-origin'] = array(
			'name'              => _x( 'Country of origin', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Country of origin', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Country of origin', 'doctor2go-connect' ),
		);

		$labels['doctor-language'] = array(
			'name'              => _x( 'Languages', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Language', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Languages', 'doctor2go-connect' ),
		);

		$labels['disease'] = array(
			'name'              => _x( 'Diseases', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Disease', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Diseases', 'doctor2go-connect' ),
		);

		$labels['doctor-degree'] = array(
			'name'              => _x( 'Degrees', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Degree', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Degrees', 'doctor2go-connect' ),
		);

		$labels['doctor-specialty'] = array(
			'name'              => _x( 'Specialties', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Specialty', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Specialties', 'doctor2go-connect' ),
		);

		$labels['body-part'] = array(
			'name'              => _x( 'Body parts', 'taxonomy general name', 'doctor2go-connect' ),
			'singular_name'     => _x( 'Body part', 'taxonomy singular name', 'doctor2go-connect' ),
			'search_items'      => __( 'Search', 'doctor2go-connect' ),
			'all_items'         => __( 'All', 'doctor2go-connect' ),
			'parent_item'       => __( 'Parent', 'doctor2go-connect' ),
			'parent_item_colon' => __( 'Parent:', 'doctor2go-connect' ),
			'edit_item'         => __( 'Edit', 'doctor2go-connect' ),
			'update_item'       => __( 'Update', 'doctor2go-connect' ),
			'add_new_item'      => __( 'Add New', 'doctor2go-connect' ),
			'new_item_name'     => __( 'New', 'doctor2go-connect' ),
			'menu_name'         => __( 'Body parts', 'doctor2go-connect' ),
		);

		return $labels[ $taxonomy ];
	}


	/*
	* here the custom image sizes are declared
	*/
	public function d2gc_d2g_image_sizes() {
		add_image_size( 'd2g-doc-pic', 400, 500, true );
		add_image_size( 'd2g-doc-pic-square', 300, 300, true );
	}

	/*
	* admin columns doctors are created
	*/
	public function d2gc_filter_posts_columns( $columns ) {

		$columns = array(
			'cb'                        => $columns['cb'],
			'id'                        => __( 'Profile ID', 'doctor2go-connect' ),
			'd2g_user_id'               => __( 'WP user ID', 'doctor2go-connect' ),
			'image'                     => __( 'Image', 'doctor2go-connect' ),
			'title'                     => __( 'Title', 'doctor2go-connect' ),
			'd2g_first_name'            => __( 'Fist name', 'doctor2go-connect' ),
			'd2g_last_name'             => __( 'Last name', 'doctor2go-connect' ),
			'd2g_main_email'            => __( 'E-mail', 'doctor2go-connect' ),
			'taxonomy-country-origin'   => __( 'Country', 'doctor2go-connect' ),
			'taxonomy-doctor-language'  => __( 'Language', 'doctor2go-connect' ),
			'taxonomy-doctor-specialty' => __( 'Specialisation', 'doctor2go-connect' ),
			'organisation_subdomain'    => __( 'D2G domain', 'doctor2go-connect' ),
			'organisation_key'          => __( 'D2G org. key', 'doctor2go-connect' ),
			'wcc_user_id'               => __( 'D2G user ID', 'doctor2go-connect' ),
			'user_key'                  => __( 'D2G user key', 'doctor2go-connect' ),
			'author'                    => __( 'Author', 'doctor2go-connect' ),
			'd2g_edit_url'              => __( 'Action', 'doctor2go-connect' ),
		);

		return $columns;
	}




	/*
	* admin columns doctors values are retrived
	*/
	public function d2gc_doctor_column( $column, $post_id ) {

		if ( 'id' === $column ) {
			echo esc_html( $post_id );
		}

		if ( 'd2g_user_id' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_user_id', true ) );
		}

		if ( 'image' === $column ) {
			echo get_the_post_thumbnail( $post_id, array( 80, 80 ) );
		}

		if ( 'd2g_first_name' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_first_name', true ) );
		}

		if ( 'd2g_last_name' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_last_name', true ) );
		}

		if ( 'd2g_main_email' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_main_email', true ) );
		}

		if ( 'organisation_key' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'organisation_key', true ) );
		}

		if ( 'wcc_user_id' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'wcc_user_id', true ) );
		}

		if ( 'user_key' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'user_key', true ) );
		}

		if ( 'd2g_edit_url' === $column ) {
			echo wp_kses_post( get_post_meta( $post_id, 'd2g_edit_url', true ) );
		}

		if ( 'organisation_subdomain' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'organisation_subdomain', true ) );
		}
	}




	/*
	* what admin columns doctors are sortable
	*/
	public function d2gc_doctor_sortable_columns( $columns ) {
		$columns['d2g_first_name'] = 'd2g_first_name';
		$columns['d2g_last_name']  = 'd2g_last_name';
		return $columns;
	}

	/*
	* create sort-query for sortabel custom columns
	*/
	public function d2gc_posts_orderby( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'price_per_month' === $query->get( 'orderby' ) ) {
			$query->set( 'd2g_first_name', 'meta_value' );
			$query->set( 'meta_key', 'd2g_first_name' );

		}

		if ( 'd2g_last_name' === $query->get( 'orderby' ) ) {
			$query->set( 'd2g_last_name', 'meta_value' );
			$query->set( 'meta_key', 'd2g_last_name' );

		}
	}

	/*
	* admin columns page are created
	*/
	public function d2gc_filter_page_columns( $columns ) {

		$columns = array(
			'cb'                     => $columns['cb'],
			'id'                     => __( 'Page ID', 'doctor2go-connect' ),
			'title'                  => __( 'Title', 'doctor2go-connect' ),
			'd2g_page_accessebility' => __( 'Access', 'doctor2go-connect' ),
			'd2g_page_identifier'    => __( 'Identifier', 'doctor2go-connect' ),
			'author'                 => __( 'Author', 'doctor2go-connect' ),
		);

		return $columns;
	}

	/*
	* admin columns pages values are retrived
	*/
	public function d2gc_page_column( $column, $post_id ) {
		if ( 'id' === $column ) {
			echo esc_html( $post_id );
		}
		if ( 'd2g_page_accessebility' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_page_accessebility', true ) );
		}
		if ( 'd2g_page_identifier' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_page_identifier', true ) );
		}
	}

	/*
	* admin columns email are created
	*/
	public function d2gc_filter_email_columns( $columns ) {

		$columns = array(
			'cb'                   => $columns['cb'],
			'title'                => __( 'Title', 'doctor2go-connect' ),
			'd2g_email_identifier' => __( 'E-mail', 'doctor2go-connect' ),

		);

		return $columns;
	}


	/*
	* admin columns pages values are retrived
	*/
	public function d2gc_email_column( $column, $post_id ) {
		if ( 'd2g_email_identifier' === $column ) {
			echo esc_html( get_post_meta( $post_id, 'd2g_email_identifier', true ) );
		}
	}


	// extendes the user tabel
	public function d2gc_modify_user_table( $column ) {
		$column['wcc_user_id']      = 'D2G user id';
		$column['user_key']         = 'D2G user key';
		$column['organisation_key'] = 'D2G org. key';
		return $column;
	}

	/*
	* what admin columns doctors are sortable
	*/
	public function d2gc_new_modify_user_table_row( $val, $column_name, $user_id ) {

		$user_meta = get_user_meta( $user_id );

		switch ( $column_name ) {
			case 'wcc_user_id':
				return $user_meta['wcc_user_id'][0];
			case 'user_key':
				return $user_meta['user_key'][0];
			case 'organisation_key':
				return $user_meta['organisation_key'][0];
			default:
		}
		return $val;
	}


	/*
	* register options for the D2G settings
	*/
	public function d2gc_register_d2g_settings() {
		$settings_with_callbacks = array(
			'd2gc_detail_page_view' => array( $this, 'd2gc_sanitize_detail_page_view' ),
			'd2gc_theme_css' => array( $this, 'd2gc_sanitize_theme_css' ),
			'd2gc_bootstrap_css' => 'absint',
			'd2gc_wcc_token' => array( $this, 'd2gc_validate_secret' ),
			'd2gc_api_url_short' => 'esc_url_raw',
			'd2gc_waiting_room_url' => 'esc_url_raw',
			'd2gc_wcc_base_url' => 'sanitize_text_field',
			'd2gc_admin_mail' => 'sanitize_email',
			'd2gc_local_user' => 'absint',
			'd2gc_placeholder' => 'absint',
			'd2gc_recaptcha_site_key' => array( $this, 'd2gc_validate_secret' ),
			'd2gc_recaptcha_secret_key' => array( $this, 'd2gc_validate_secret' ),
			'd2gc_admin_access' => 'absint',
			'd2gc_single_header_footer' => 'absint',
			'd2gc_deactivate_recapctha_script' => 'absint',
			'd2gc_activate_2fa_link' => 'absint',
			'd2gc_logo' => 'absint',
			'd2gc_sender_address' => 'sanitize_email',
			'd2gc_recipient_address' => 'sanitize_email',
			'd2gc_sender_name' => 'sanitize_text_field',
			'd2gc_use_imgix' => 'absint',
			'd2gc_load_availability_info' => 'absint',
			'd2gc_bootstrap_js' => 'absint',
			'd2gc_activate_sso' => 'absint',
			'd2gc_activate_custom_password_mail' => 'absint',
			'd2gc_activate_custom_login_registration' => 'absint',
			'd2gc_disable_booking_rules_editor' => 'absint',
			'd2g_debug' => 'absint',
			'd2gc_newsletter_signup' => 'absint',
		);

		foreach ( $settings_with_callbacks as $setting => $callback ) {
			register_setting( 'd2gc-option-group', $setting, array( 'sanitize_callback' => $callback ) );
		}
	}

	public function d2gc_sanitize_token( $value ) {
		$value = trim( wp_unslash( $value ) ); // remove slashes + spaces
		if ( '' === $value ) {
			add_settings_error(
				'd2gc_wcc_token',
				'empty_token',
				__( 'API token cannot be empty.', 'doctor2go-connect' ),
				'error'
			);
			return '';
		}
		return sanitize_textarea_field( $value ); // preserves all characters safely
	}

	public function d2gc_validate_secret( $value ) {
		$value = is_string( $value ) ? trim( wp_unslash( $value ) ) : '';

		if ( '' === $value ) {
			add_settings_error( 'd2gc-option-group', 'empty_secret', __( 'This field cannot be empty.', 'doctor2go-connect' ), 'error' );
			return '';
		}

		return $value;
	}

	/**
	 * Sanitize theme CSS dropdown value.
	 */
	public function d2gc_sanitize_theme_css( $value ) {

		$allowed = array(
			'light',
			'dark',
			'no-style',
		);

		if ( in_array( $value, $allowed, true ) ) {
			return $value;
		}

		// Default fallback
		return 'light';
	}

	/**
	 * Sanitize detail page template dropdown.
	 */
	public function d2gc_sanitize_detail_page_view( $value ) {

		// single-v1 (sidebar layout) is no longer offered
		$allowed = array(
			'single-v2',
			'single-v3'
		);

		if ( in_array( $value, $allowed, true ) ) {
			return $value;
		}

		// Safe fallback
		return 'single-v2';
	}

	// function adds metaboxes with callback function where the html is defined
	public function d2gc_meta_box_add() {
		require_once 'partials/metaboxes.php';
		add_meta_box( 'personal-info', 'Personal information', 'd2gc_meta_box_personal_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'd2gc-prices', __( 'Prices', 'doctor2go-connect' ), 'd2gc_meta_box_prices_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'd2gc-questionnaires', __( 'Questionnaires', 'doctor2go-connect' ), 'd2gc_meta_box_questionnaires_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'education', 'Education', 'd2gc_meta_box_education_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'work', 'Work experience', 'd2gc_meta_box_work_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'publication', 'Publications', 'd2gc_meta_box_publications_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'd2gc-availability', __( 'Availability & holiday', 'doctor2go-connect' ), 'd2gc_meta_box_availability_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'd2gc-filters', __( 'Filters', 'doctor2go-connect' ), 'd2gc_meta_box_filters_cb', 'd2g_doctor', 'normal', 'high' );
		add_meta_box( 'page_type', 'Page type', 'd2gc_meta_box_page_type_cb', 'page', 'normal', 'high' );
		add_meta_box( 'email_type', 'E-mail', 'd2gc_meta_box_email_type_cb', 'd2g_emails', 'normal', 'high' );
	}

	/**
	 * Boxes on the doctor edit screen that are shown/hidden by the tabs (see d2gc_doctor_edit_tabs()).
	 *
	 * @var string[]
	 */
	private $doctor_tab_boxes = array( 'personal-info', 'd2gc-prices', 'd2gc-questionnaires', 'education', 'work', 'publication', 'd2gc-availability', 'd2gc-filters', 'postexcerpt' );

	/**
	 * The tab boxes are never "hidden" via Screen Options: WordPress saves every box that is invisible at that moment
	 * as hidden (postboxes.save_state), which would hide the boxes of the other tabs for good. The tabs decide alone.
	 *
	 * @param mixed $hidden metaboxhidden_d2g_doctor user option
	 * @return mixed
	 */
	public function d2gc_doctor_never_hide_tab_boxes( $hidden ) {
		return is_array( $hidden ) ? array_values( array_diff( $hidden, $this->doctor_tab_boxes ) ) : $hidden;
	}

	/**
	 * (get_user_option_meta-box-order_d2g_doctor) The same box order on the doctor edit screen for every user, whatever
	 * was dragged before. Boxes that do not exist on a site are skipped by WordPress, boxes not listed (other plugins)
	 * follow below in their own order. Dragging is switched off in d2g-connect-admin.js.
	 *
	 * @return array context => comma separated box ids
	 */
	public function d2gc_doctor_meta_box_order() {
		/**
		 * Filters the fixed order of the boxes on the doctor edit screen.
		 *
		 * @param array $order context (side, normal, advanced) => comma separated box ids
		 */
		return apply_filters(
			'd2gc_doctor_meta_box_order',
			array(
				'side'     => 'submitdiv,ml_box,d2gc_ai_translate_into_current,postimagediv,tagsdiv-post_tag,dynwid,authordiv',
				'normal'   => 'd2gc-prices,d2gc-questionnaires,d2gc-availability,d2gc-filters,personal-info,education,work,publication,postexcerpt,slugdiv,revisionsdiv,postcustom',
				'advanced' => '',
			)
		);
	}

	/**
	 * Tab bar on the doctor edit screen (below the title). The tabs only show/hide the boxes and the editor of the
	 * one post form, so "Update" always saves everything (see "DOCTOR EDIT SCREEN: TABS" in d2g-connect-admin.js).
	 * Boxes of other plugins stay visible below the tabs.
	 *
	 * @param WP_Post $post
	 */
	public function d2gc_doctor_edit_tabs( $post ) {
		if ( 'd2g_doctor' !== $post->post_type ) {
			return;
		}
		$tabs = array(
			'personal'       => __( 'Personal information', 'doctor2go-connect' ),
			'bio'            => __( 'Bio', 'doctor2go-connect' ),
			'experience'     => __( 'Education & experience', 'doctor2go-connect' ),
			'filters'        => __( 'Filters', 'doctor2go-connect' ),
			'prices'         => __( 'Prices', 'doctor2go-connect' ),
			'questionnaires' => __( 'Questionnaires', 'doctor2go-connect' ),
			'availability'   => __( 'Availability & holiday', 'doctor2go-connect' ),
		);
		?>
		<nav class="nav-tab-wrapper d2gc-doctor-tabs" role="tablist">
			<?php foreach ( $tabs as $tab => $label ) { ?>
				<a href="#<?php echo esc_attr( $tab ); ?>" class="nav-tab" role="tab" data-tab="<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php } ?>
		</nav>
		<?php
	}

	// function saves the metaboxes
	public function d2gc_meta_box_save( $post_id ) {

		// Bail if we're doing an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail for the translations the doctor sync updates itself: the submitted form belongs to the edited post only
		if ( D2GC_Doctor_Sync::is_syncing() ) {
			return;
		}

		$included_cpts = array( 'd2g_doctor', 'page', 'd2g_emails' );

		if ( in_array( get_post_type( $post_id ), $included_cpts ) ) {

			// the profile editor in the language of this post (not of the current request, e.g. a REST call from WCC)
			$new_url = D2G_doc_user_profile::d2gc_page_url( (string) D2GC_Translations::get_post_language( $post_id ) );

			update_post_meta( $post_id, 'd2g_edit_url', '<a target="_blank" href="' . $new_url . '?edit=' . $post_id . '">edit</a>' );

			// if our nonce isn't there, or we can't verify it, bail
			$nonce = isset( $_POST['meta_box_nonce'] )
				? sanitize_text_field( wp_unslash( $_POST['meta_box_nonce'] ) )
				: '';

			if ( ! wp_verify_nonce( $nonce, 'd2g_meta_box_nonce' ) ) {
				return;
			}

			$personalFields = array(
				'd2g_first_name'   => esc_html__( 'First name', 'doctor2go-connect' ),
				'd2g_last_name'    => esc_html__( 'Last name', 'doctor2go-connect' ),
				'd2g_emp_title'    => esc_html__( 'Title', 'doctor2go-connect' ),
				'd2g_address'      => esc_html__( 'Address', 'doctor2go-connect' ),
				'd2g_zip'          => esc_html__( 'Zip code', 'doctor2go-connect' ),
				'd2g_city'         => esc_html__( 'city', 'doctor2go-connect' ),
				'tel'              => esc_html__( 'Phone', 'doctor2go-connect' ),
				'd2g_mobile'       => esc_html__( 'Mobile', 'doctor2go-connect' ),
				'd2g_main_email'   => esc_html__( 'E-mail', 'doctor2go-connect' ),
				'd2g_organisation' => esc_html__( 'Organisation', 'doctor2go-connect' ),
				'reg_nr'           => esc_html__( 'Registration number', 'doctor2go-connect' ),
				'reg_country'      => esc_html__( 'Country of registration', 'doctor2go-connect' ),
				'reg_link'         => esc_html__( 'Registration link', 'doctor2go-connect' ),
				'avg_price'        => esc_html__( 'Average price', 'doctor2go-connect' ),

			);

			// Update personal fields. Saved whenever the field was submitted, also when empty, so a value can be
			// cleared in wp-admin. Not submitted (pages, e-mail templates use the same save) = left untouched.
			foreach ( $personalFields as $key => $name ) {
				if ( isset( $_POST[ $key ] ) ) {
					// a URL: sanitize_text_field() would remove the %xx parts and break the link
					$value = 'reg_link' === $key
						? esc_url_raw( wp_unslash( $_POST[ $key ] ) )
						: d2gc_get_post_text( $key );
					update_post_meta( $post_id, $key, $value );
				}
			}

			// registration check (date and PDF), same rules as the front-end profile editor
			if ( 'd2g_doctor' === get_post_type( $post_id ) && isset( $_POST['reg_check_date'] ) ) {
				update_post_meta( $post_id, 'reg_check_date', D2G_doc_user_profile::d2gc_sanitize_date( wp_unslash( $_POST['reg_check_date'] ) ) );
				D2G_doc_user_profile::d2gc_save_reg_check_pdf( $post_id );
			}

			$postMetaArr = isset( $_POST['meta'] ) ? map_deep( wp_unslash( $_POST['meta'] ), 'sanitize_text_field' ) : array();

			// Only the plugin's own fields from the meta boxes (admin/partials/metaboxes.php).
			// WordPress' "Custom Fields" box posts under the same name (meta[<meta_id>][key|value]); saving
			// everything stored those as junk meta with numeric keys ("20936" => array( key, value )) on every save.
			$allowed_meta = array(
				'edus',
				'exps',
				'pubs',
				'walk_in_price',
				'walk_in_currency',
				'written_con_price',
				'written_con_currency',
				'written_con_type',
				'intake_type',
				'd2g_page_identifier',
				'd2g_email_identifier',
			);
			// editor off: the prices come from doctor2go.online, the box shows them read-only
			if ( ! d2gc_booking_rules_editor_enabled() ) {
				$allowed_meta = array_diff( $allowed_meta, array( 'walk_in_price', 'walk_in_currency', 'written_con_price', 'written_con_currency' ) );
			}

			foreach ( $postMetaArr as $meta_key => $meta_value ) {
				if ( in_array( (string) $meta_key, $allowed_meta, true ) ) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}
	}


	public function d2gc_hide_admin_bar() {
		/*@ Get current logged-in user data */
		$user = wp_get_current_user();
		/*@ Fetch only roles */
		$currentUserRoles = $user->roles;
		if ( in_array( 'doctor', $currentUserRoles ) || in_array( 'patient', $currentUserRoles ) || in_array( 'subscriber', $currentUserRoles ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}
}
