<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Doctor profile save Ajax actions.
 *
 * @package d2g-connect
 */
class D2G_doc_user_profile {
	public static function init() {

		//
		// ajax functions
		// update doctor profile post
		add_action( 'wp_ajax_d2gc_update_doc', array( __CLASS__, 'd2gc_update_doc' ) );
		add_action( 'wp_ajax_nopriv_d2gc_update_doc', array( __CLASS__, 'd2gc_update_doc' ) );

		add_action( 'wp_ajax_d2gc_delete_profile_pic', array( __CLASS__, 'd2gc_delete_profile_pic' ) );
		add_action( 'wp_ajax_nopriv_d2gc_delete_profile_pic', array( __CLASS__, 'd2gc_delete_profile_pic' ) );

		// adds a doctor profile post when creating a user with a doctor role if the option is checked
		if ( get_option( 'd2gc_local_user' ) == 1 ) {
			add_action( 'user_register', array( __CLASS__, 'd2gc_create_doc_profile_locally' ), 10, 2 );
		}

		// this redirects the user with a doctor role to his profile edit page
		add_action( 'wp_login', array( __CLASS__, 'd2gc_redirect_doctor' ), 10 );

		// this resticts the admin access so that (this needs to become optional)
		add_action( 'admin_init', array( __CLASS__, 'd2gc_restict_admin' ), 12 );

		// adapt edit URL when polylang is used
		/*
		if(is_plugin_active('polylang/polylang.php')){
			self::loader->add_filter( 'pll_translate_post_meta', $plugin_admin, 'd2gc_translate_post_meta', 10, 3 );
		}

		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			self::loader->add_filter('wpml_tm_save_translation_cf', $plugin_admin, 'callback_2', 10, 3);

		}*/
	}


	/**
	 * @param $currUserID
	 * @return int[]|WP_Post[]
	 */
	public static function d2gc_get_pub_profile( $currUserID ) {
		$args       = array(
			'post_type'   => 'd2g_doctor',
			'post_status' => 'any',
			'meta_query'  => array(
				array(
					'key'   => 'd2g_user_id',
					'value' => $currUserID,
				),
			),
		);
		$pubProfile = get_posts( $args );
		return $pubProfile;
	}

	/*
	*retrieve the URL from the doc profile edit page in the front-end
	*/
	public static function d2gc_page_url( $lang = '', $d2g_page_identifier = '', $title = false ) {
		$args = array(
			'post_type'  => 'page',
			'meta_query' => array(
				array(
					'key'   => 'd2g_page_identifier',
					'value' => ( $d2g_page_identifier ) ?: 'my_profile',
				),
			),
		);
		if ( $lang != '' ) {
			$args['lang'] = $lang;
		}
		$page_id = get_posts( $args )[0]->ID;
		if ( $title == false ) {
			$profile_edit_url = get_permalink( $page_id );
			return $profile_edit_url;
		} else {
			$page['title'] = get_the_title( $page_id );
			$page['url']   = get_permalink( $page_id );
			return $page;
		}
	}

	/*
	* This function is called when setting is checked that profile is created locally when user is created localy, this function is not really used and my be removed in future
	* There for no nonce check, because this is an internal function and not called via ajax
	*/
	public static function d2gc_create_doc_profile_locally( $user_id, $userdata ) {

		if ( isset( $_POST['role'] ) && $_POST['role'] == 'doctor' ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce not required for this internal action.
			// get user object
			$u = new WP_User( $user_id );

			// Remove standard role and add new role
			$u->remove_role( 'subscriber' );
			$u->add_role( 'doctor' );

			// creates the doctor profile post and sets some meta data (user_id and email)
			$post = array(
				'post_content' => '',
				'post_status'  => 'draft',
				'post_title'   => $userdata['user_email'],
				'post_type'    => 'd2g_doctor',
				'post_author'  => $user_id,
			);

			$doctor_id = (int) wp_insert_post( $post );
			update_post_meta( $doctor_id, 'd2g_main_email', $userdata['user_email'] );
			update_post_meta( $doctor_id, 'd2g_user_id', $user_id );
		}
	}

	/*
	* create user via API from WCC
	*/
	public static function d2gc_create_doc_user( $user_data ) {

		$user_login = self::d2gc_clean_name( $user_data['user_title'] . $user_data['user_initials'] . $user_data['user_last_name'] . time() );

		$user_input = array(
			'user_login'   => $user_login,
			'user_pass'    => $user_data['pass'] ?: 'wcc_' . $user_login,
			'user_email'   => $user_data['user_email'],
			'first_name'   => $user_data['user_first_name'],
			'last_name'    => $user_data['user_last_name'],
			'display_name' => $user_data['user_full_name'],
			'role'         => 'doctor',
		);

		$user = wp_insert_user( $user_input );

		return $user;
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	protected static function d2gc_clean_name( $string ) {
		$string = str_replace( ' ', '', $string ); // Replaces all spaces with hyphens.
		$string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Removes special chars.
		$string = strtolower( $string );

		return preg_replace( '/-+/', '-', $string ); // Replaces multiple hyphens with single one.
	}



	/**
	 * @param array  $doc_data
	 * @param array  $doc_data_meta
	 * @param string $action
	 * @param string $source
	 * @return int
	 * this function crates a doc profile and is called via the WCC api
	 */
	public static function d2gc_create_doc_profile( $doc_data = array(), $doc_data_meta = array(), $user_id ) {

		$doc_id      = '';
		$error_check = false;

		if ( $error_check != true ) {
			/********************db insert*/
			$post = array(
				'post_content' => '',
				'post_status'  => 'draft',
				'post_title'   => $doc_data['user_title'] . ' ' . $doc_data['user_first_name'] . ' ' . $doc_data['user_last_name'],
				'post_type'    => 'd2g_doctor',
				'post_author'  => $user_id,
			);
			// my_dump($post);
			$doc_id = (int) wp_insert_post( $post );

			if ( count( $doc_data_meta ) > 0 ) {
				foreach ( $doc_data_meta as $meta_key => $meta_value ) {
					add_post_meta( $doc_id, $meta_key, $meta_value, true );
				}
			}

			$new_url = self::d2gc_page_url();
			update_post_meta( $doc_id, 'd2g_edit_url', '<a target="_blank" href="' . $new_url . '?edit=' . $doc_id . '">edit</a>' );

			do_action( 'breeze_clear_all_cache' );

		}

		return $doc_id;
	}

	/**
	 * this sets the meta fields for the doctor profile, values received from WCC
	 *
	 * @param $user
	 * @param $user_data
	 * @return array
	 */
	public static function d2gc_set_doc_meta( $user_id, $user_data ) {
		$doc_meta = array(
			'd2g_user_id'            => (string) $user_id,
			'tariffs'                => $user_data['tariffs'],
			'vat_rules'              => $user_data['vat_rules'],
			'user_key'               => $user_data['user_key'],
			'wcc_user_id'            => $user_data['user_id'],
			'organisation_key'       => $user_data['organisation_key'],
			'd2g_emp_title'          => $user_data['user_title'],
			'd2g_first_name'         => $user_data['user_first_name'],
			'd2g_last_name'          => $user_data['user_last_name'],
			'd2g_organisation'       => $user_data['organisation_name'],
			'd2g_main_email'         => $user_data['user_email'],
			'organisation_subdomain' => $user_data['organisation_subdomain'],
			'locations_to_go'        => $user_data['locations'],

		);
		return $doc_meta;
	}

	/**
	 * Update a doctor profile post.
	 */
	public static function d2gc_update_doc() {
		$nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : ''; if (!$nonce || !wp_verify_nonce($nonce, 'doc-update')) { wp_die('Invalid nonce'); }
		
		global $success;
		$post_data = wp_unslash($_POST);
		$update_id = isset($post_data['update_id']) ? absint($post_data['update_id']) : 0;
		if (!$update_id) wp_die('Invalid ID');
		$currLang = isset($post_data['d2g_lang']) ? sanitize_key($post_data['d2g_lang']) : '';

		// META (unchanged)
		$doc_data_meta = [];
		if (isset($post_data['meta']) && is_array($post_data['meta'])) {
			foreach ($post_data['meta'] as $meta_key => $meta_value) {
				$meta_key = sanitize_key($meta_key);
				if ('edus' === $meta_key && is_array($meta_value)) {
					$clean_items = [];
					foreach ($meta_value as $item) if (is_array($item)) $clean_items[] = [
						'd2g_exp_edu_start_date' => sanitize_text_field($item['d2g_exp_edu_start_date'] ?? ''),
						'd2g_exp_edu_end_date'   => sanitize_text_field($item['d2g_exp_edu_end_date'] ?? ''),
						'd2g_exp_edu_study'      => sanitize_text_field($item['d2g_exp_edu_study'] ?? ''),
						'd2g_exp_edu_title'      => sanitize_text_field($item['d2g_exp_edu_title'] ?? ''),
						'd2g_exp_edu_org'        => sanitize_text_field($item['d2g_exp_edu_org'] ?? ''),
					];
					$doc_data_meta[$meta_key] = $clean_items;
				} elseif ('exps' === $meta_key && is_array($meta_value)) {
					$clean_items = [];
					foreach ($meta_value as $item) if (is_array($item)) $clean_items[] = [
						'd2g_exp_edu_start_date' => sanitize_text_field($item['d2g_exp_edu_start_date'] ?? ''),
						'd2g_exp_edu_end_date'   => sanitize_text_field($item['d2g_exp_edu_end_date'] ?? ''),
						'd2g_exp_edu_expertise'  => sanitize_text_field($item['d2g_exp_edu_expertise'] ?? ''),
						'd2g_exp_edu_title'      => sanitize_text_field($item['d2g_exp_edu_title'] ?? ''),
						'd2g_exp_edu_org'        => sanitize_text_field($item['d2g_exp_edu_org'] ?? ''),
					];
					$doc_data_meta[$meta_key] = $clean_items;
				} elseif ('pubs' === $meta_key && is_array($meta_value)) {
					$clean_items = [];
					foreach ($meta_value as $item) if (is_array($item)) $clean_items[] = [
						'd2g_pub_title'   => sanitize_text_field($item['d2g_pub_title'] ?? ''),
						'd2g_pub_link'    => esc_url_raw($item['d2g_pub_link'] ?? ''),
						'd2g_pub_journal' => sanitize_text_field($item['d2g_pub_journal'] ?? ''),
						'd2g_pub_type'    => sanitize_text_field($item['d2g_pub_type'] ?? ''),
						'd2g_pub_author'  => sanitize_text_field($item['d2g_pub_author'] ?? ''),
						'd2g_pub_date'    => sanitize_text_field($item['d2g_pub_date'] ?? ''),
					];
					$doc_data_meta[$meta_key] = $clean_items;
				} else {
					$doc_data_meta[$meta_key] = is_array($meta_value) ? array_map('sanitize_text_field', $meta_value) : sanitize_text_field($meta_value);
				}
			}
		}

		// TAX (FIXED)
		$doc_tax = [];
		if (isset($post_data['tax']) && is_array($post_data['tax'])) {
			foreach ($post_data['tax'] as $tax_key => $tax_value) {
				$tax_key = sanitize_key($tax_key);
				if (is_array($tax_value)) $doc_tax[$tax_key] = array_map('sanitize_text_field', $tax_value);
				else $doc_tax[$tax_key] = sanitize_text_field($tax_value);
			}
		}

		// Prepare Post Update
		$my_update = [
			'ID'           => $update_id,
			'post_title'   => sanitize_text_field($post_data['post_title'] ?? ''),
			'post_content' => wp_kses_post($post_data['docdesc'] ?? ''),
			'post_status'  => sanitize_key($post_data['post_status'] ?? 'draft'),
		];
		$insertID = wp_update_post($my_update, true);
		if (is_wp_error($insertID)) { echo 'error'; wp_die(); }

		// Update Meta
		foreach ($doc_data_meta as $meta_key => $meta_value) update_post_meta($update_id, $meta_key, $meta_value);

		// Update Edit URL
		update_post_meta($update_id,'d2g_edit_url',sprintf('<a target="_blank" href="%s?edit=%d">%s</a>',esc_url(self::d2gc_page_url($currLang)),absint($update_id),esc_html__('edit','doctor2go-connect')));

		// Update Taxonomies
		if (!empty($doc_tax)) self::d2gc_updateDocTerms($doc_tax, $update_id);

		// Handle File Uploads
		if (!empty($_FILES)) self::d2gc_set_doc_images($update_id, $nonce);

		$success = true;
		do_action('breeze_clear_all_cache');
		echo 'updated'; wp_die();
	}

	/**
	 * Update object terms for the post.
	 */
	public static function d2gc_updateDocTerms($taxonomies,$post_id) {
		foreach ($taxonomies as $taxonomy => $value) wp_set_object_terms($post_id,is_array($value) ? $value : [$value],$taxonomy);
	}

	/**
	 * Securely handle doctor image uploads.
	 *
	 * @param int $post_id
	 * @param string $nonce
	 */
	private static function d2gc_set_doc_images( $post_id, $nonce ) {

		if ( ! isset( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'doc-update' ) ) {
			wp_die( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) || empty( $_FILES ) || ! is_array( $_FILES ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			return;
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		foreach ( $_FILES as $field_name => $file_data ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( ! is_array( $file_data ) || ! isset( $file_data['name'], $file_data['type'], $file_data['tmp_name'], $file_data['error'], $file_data['size'] ) ) {
				continue;
			}

			if ( is_array( $file_data['name'] ) ) {

				if ( ! is_array( $file_data['type'] ) || ! is_array( $file_data['tmp_name'] ) || ! is_array( $file_data['error'] ) || ! is_array( $file_data['size'] ) ) {
					continue;
				}

				foreach ( $file_data['name'] as $index => $name ) {

					if ( empty( $name ) || ! isset( $file_data['error'][ $index ], $file_data['type'][ $index ], $file_data['tmp_name'][ $index ], $file_data['size'][ $index ] ) || UPLOAD_ERR_OK !== (int) $file_data['error'][ $index ] ) {
						continue;
					}

					$single_file = array(
						'name'     => sanitize_file_name( $name ),
						'type'     => sanitize_text_field( wp_unslash( $file_data['type'][ $index ] ) ),
						'tmp_name' => sanitize_text_field( wp_unslash( $file_data['tmp_name'][ $index ] ) ),
						'error'    => (int) $file_data['error'][ $index ],
						'size'     => (int) $file_data['size'][ $index ],
					);

					self::d2gc_handle_single_upload( $single_file, $post_id, $field_name );
				}
			} else {

				if ( empty( $file_data['name'] ) || ! isset( $file_data['error'], $file_data['type'], $file_data['tmp_name'], $file_data['size'] ) || UPLOAD_ERR_OK !== (int) $file_data['error'] ) {
					continue;
				}

				$single_file = array(
					'name'     => sanitize_file_name( $file_data['name'] ),
					'type'     => sanitize_text_field( wp_unslash( $file_data['type'] ) ),
					'tmp_name' => sanitize_text_field( wp_unslash( $file_data['tmp_name'] ) ),
					'error'    => (int) $file_data['error'],
					'size'     => (int) $file_data['size'],
				);

				self::d2gc_handle_single_upload( $single_file, $post_id, $field_name );
			}
		}
	}


	/**
	 * Process a single uploaded file securely using WordPress API.
	 *
	 * @param array  $file
	 * @param int    $post_id
	 * @param string $field_name
	 */
	private static function d2gc_handle_single_upload( $file, $post_id, $field_name ) {
		// Restrict to images only
		$allowed_mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
		);

		add_filter(
			'upload_mimes',
			function () use ( $allowed_mimes ) {
				return $allowed_mimes;
			}
		);

		// Use WordPress core upload handling
		$attachment_id = media_handle_sideload( $file, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return;
		}

		// If this field is meant to be featured image
		if ( $field_name === 'picture_1' ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}


	public static function d2gc_delete_profile_pic() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'd2g_delete_pic' ) ) {
			wp_die( 'Security failed' );
		}

		$image_id = absint( wp_unslash( $_POST['image'] ?? 0 ) );
		$doc_id   = absint( wp_unslash( $_POST['doc_id'] ?? 0 ) );

		if ( $image_id && $doc_id ) {
			wp_delete_attachment( $image_id );
			update_post_meta( $doc_id, '_thumbnail_id', '' );
			wp_send_json_success();
		}
		wp_send_json_error();
	}


	/*
	* autotranslate function for edit link from doctors with polylang
	*/
	public static function d2gc_translate_post_meta( $value, $key, $lang ) {

		if ( 'd2g_edit_url' === $key ) {

			$new_url = self::d2gc_page_url( $lang );
			$part2   = explode( '?edit=', $value )[1];
			$value   = '<a target="_blank" href="' . $new_url . '?edit=' . $part2;
		}
		return $value;
	}


	/*
	* redirect doctor when logging in
	*/
	public static function d2gc_redirect_doctor() {
		$currUser = wp_get_current_user();

		if ( in_array( 'doctor', $currUser->roles, true ) ) {
			wp_safe_redirect( self::d2gc_page_url() );
			exit;
		}
	}

	// this needs to become optional
	public static function d2gc_restict_admin() {

		$new_url = self::d2gc_page_url();

		if ( wp_doing_ajax() || current_user_can( 'delete_pages' ) || current_user_can( 'delete_others_posts' ) ) {
			return;
		} else {

			$currUser = wp_get_current_user();

			if ( in_array( 'doctor', $currUser->roles ) ) {
				if ( get_option( 'd2gc_admin_access' ) == 1 ) {
					return;
				}
				$new_url = self::d2gc_page_url();
			} else {
				$new_url = home_url();
			}
			header( 'Refresh: 2; ' . esc_url( $new_url ) );

			wp_die(
				esc_html( 'You will now be redirected to your profile edit page', 'doctor2go-connect' ),
			);
		}
	}
}

D2G_doc_user_profile::init();
