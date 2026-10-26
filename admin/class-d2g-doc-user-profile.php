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
		// logged-in only: ownership is checked in the handlers via d2gc_current_user_can_edit_doctor()
		add_action( 'wp_ajax_d2gc_update_doc', array( __CLASS__, 'd2gc_update_doc' ) );

		add_action( 'wp_ajax_d2gc_delete_profile_pic', array( __CLASS__, 'd2gc_delete_profile_pic' ) );

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
	 * Meta keys that are managed by the WCC sync and may not be changed via the front-end profile form.
	 */
	const D2GC_PROTECTED_DOC_META = array(
		'd2g_user_id',
		'user_key',
		'wcc_user_id',
		'wcc_status',
		'organisation_key',
		'organisation_subdomain',
		'tariffs',
		'vat_rules',
		'locations_to_go',
		'simple_booking_rules',
		'd2g_vacation_periods',
		'd2g_edit_url',
	);

	/**
	 * Whether the current user may edit the given doctor profile.
	 * Editors and admins may edit every profile, doctors only the profile linked to them via d2g_user_id.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function d2gc_current_user_can_edit_doctor( $post_id ) {
		$post_id = absint( $post_id );

		if ( ! $post_id || ! is_user_logged_in() || 'd2g_doctor' !== get_post_type( $post_id ) ) {
			return false;
		}

		if ( current_user_can( 'edit_others_posts' ) ) {
			return true;
		}

		$owner_id = absint( get_post_meta( $post_id, 'd2g_user_id', true ) );

		return $owner_id && get_current_user_id() === $owner_id;
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

		$user_login = self::d2gc_clean_name( ( $user_data['user_title'] ?? '' ) . ( $user_data['user_initials'] ?? '' ) . ( $user_data['user_last_name'] ?? '' ) . time() );

		$user_input = array(
			'user_login'   => $user_login,
			'user_pass'    => ! empty( $user_data['pass'] ) ? $user_data['pass'] : wp_generate_password( 24 ),
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
	public static function d2gc_create_doc_profile( $doc_data, $doc_data_meta, $user_id ) {

		$doc_id      = '';
		$error_check = false;

		if ( $error_check != true ) {
			/********************db insert*/
			$post = array(
				'post_content' => '',
				'post_status'  => 'draft',
				'post_title'   => self::d2gc_get_wcc_title( $doc_data ),
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

			$new_url = self::d2gc_page_url( (string) D2GC_Translations::get_post_language( $doc_id ) );
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
			'tariffs'                => $user_data['tariffs'] ?? '',
			'vat_rules'              => $user_data['vat_rules'] ?? '',
			'user_key'               => $user_data['user_key'] ?? '',
			'wcc_user_id'            => $user_data['user_id'] ?? '',
			'organisation_key'       => $user_data['organisation_key'] ?? '',
			'd2g_emp_title'          => $user_data['user_title'] ?? '',
			'd2g_first_name'         => $user_data['user_first_name'] ?? '',
			'd2g_last_name'          => $user_data['user_last_name'] ?? '',
			'd2g_organisation'       => $user_data['organisation_name'] ?? '',
			'd2g_main_email'         => $user_data['user_email'] ?? '',
			'organisation_subdomain' => $user_data['organisation_subdomain'] ?? '',
			'locations_to_go'        => $user_data['locations'] ?? '',

		);
		return $doc_meta;
	}

	/**
	 * Creates or updates a doctor with the data WCC sends to the REST route (D2GC_Rest, the request is verified there).
	 *
	 * The doctor is found by the WCC user_id (all languages). Unknown user_id + unknown e-mail address: a WordPress user
	 * and a doctor profile (draft) are created. Known doctor: profile data, booking rules, vacation periods and prices
	 * are updated in all translations; organisation_state inactive/active takes the profile offline/online.
	 * A block that is not sent is left as it is, an empty list removes everything (see d2gc_get_wcc_doctor_meta()).
	 *
	 * @param array $user_data the JSON body
	 * @return array|WP_Error { message, user_id?, doctor_id }
	 */
	public static function d2gc_sync_doctor_from_wcc( $user_data ) {
		if ( empty( $user_data['user_id'] ) || ! is_scalar( $user_data['user_id'] ) ) {
			return new WP_Error( 'd2gc_missing_user_id', 'user_id (the WCC user id) is required.', array( 'status' => 400 ) );
		}
		if ( empty( $user_data['user_email'] ) || ! is_email( $user_data['user_email'] ) ) {
			return new WP_Error( 'd2gc_missing_email', 'A valid user_email is required.', array( 'status' => 400 ) );
		}

		// validate everything before anything is written
		$meta = self::d2gc_get_wcc_doctor_meta( $user_data );
		if ( is_wp_error( $meta ) ) {
			return $meta;
		}

		$docs = self::d2gc_get_doctors_by_wcc_id( $user_data['user_id'] );

		/* ---------- create ---------- */
		if ( empty( $docs ) ) {
			if ( email_exists( $user_data['user_email'] ) ) {
				return new WP_Error( 'd2gc_email_in_use', 'No doctor with this user_id exists, but the e-mail address belongs to an existing WordPress user. Remove or change that user first.', array( 'status' => 409 ) );
			}

			$user_id = self::d2gc_create_doc_user( $user_data );
			if ( is_wp_error( $user_id ) ) {
				$user_id->add_data( array( 'status' => 400 ) );
				return $user_id;
			}

			update_user_meta( $user_id, 'wcc_user_id', $user_data['user_id'] );
			update_user_meta( $user_id, 'user_key', $user_data['user_key'] ?? '' );
			update_user_meta( $user_id, 'organisation_key', $user_data['organisation_key'] ?? '' );

			$doc_id = self::d2gc_create_doc_profile( $user_data, self::d2gc_set_doc_meta( $user_id, $user_data ), $user_id );
			foreach ( $meta as $key => $value ) {
				update_post_meta( $doc_id, $key, $value );
			}

			return array(
				'message'   => 'The user and the public profile have been created.',
				'user_id'   => (int) $user_id,
				'doctor_id' => (int) $doc_id,
			);
		}

		/* ---------- update ---------- */
		$doc_id     = $docs[0]->ID;
		$wcc_status = get_post_meta( $doc_id, 'wcc_status', true );
		$state      = $user_data['organisation_state'] ?? '';
		$status     = $docs[0]->post_status;
		if ( 'inactive' === $state ) {
			$status     = 'draft';
			$wcc_status = 'inactive';
		} elseif ( 'active' === $state && 'inactive' === $wcc_status ) {
			$status     = 'publish';
			$wcc_status = 'active';
		}
		$title = self::d2gc_get_wcc_title( $user_data );

		// the same data for every translation (title, status and wcc_status are not part of the meta sync)
		foreach ( $docs as $doc ) {
			foreach ( $meta as $key => $value ) {
				update_post_meta( $doc->ID, $key, $value );
			}
			if ( $wcc_status ) {
				update_post_meta( $doc->ID, 'wcc_status', $wcc_status );
			}
			$update = array(
				'ID'          => $doc->ID,
				'post_status' => $status,
			);
			if ( '' !== $title ) {
				$update['post_title'] = $title;
			}
			wp_update_post( $update );
		}

		self::d2gc_update_wp_user_from_wcc( (int) get_post_meta( $doc_id, 'd2g_user_id', true ), $user_data );

		do_action( 'breeze_clear_all_cache' );

		return array(
			'message'   => 'The doctor has been updated.',
			'doctor_id' => (int) $doc_id,
		);
	}

	/**
	 * All doctor posts (every language and status) linked to a WCC user id.
	 *
	 * @param string $wcc_user_id
	 * @return WP_Post[]
	 */
	public static function d2gc_get_doctors_by_wcc_id( $wcc_user_id ) {
		return get_posts(
			array(
				'post_type'      => 'd2g_doctor',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'lang'           => '', // all languages (Polylang)
				'meta_query'     => array(
					array(
						'key'   => 'wcc_user_id',
						'value' => (string) $wcc_user_id,
					),
				),
			)
		);
	}

	/**
	 * Post title from the WCC data: user_full_name without double spaces, otherwise title + first + last name.
	 *
	 * @param array $user_data
	 * @return string
	 */
	public static function d2gc_get_wcc_title( $user_data ) {
		$squash = function ( $text ) {
			return trim( preg_replace( '/\s+/', ' ', (string) $text ) );
		};
		$title = $squash( $user_data['user_full_name'] ?? '' );
		if ( '' === $title ) {
			$title = $squash( ( $user_data['user_title'] ?? '' ) . ' ' . ( $user_data['user_first_name'] ?? '' ) . ' ' . ( $user_data['user_last_name'] ?? '' ) );
		}
		return sanitize_text_field( $title );
	}

	/**
	 * The doctor meta from the WCC data, validated. Only what WCC sent: a missing field or block is not changed,
	 * an empty list (booking_rules, vacation_periods) removes everything.
	 *
	 * - booking_rules {"rules":[...]} (or advanced_booking_rules, or a plain list) -> simple_booking_rules, the list
	 *   both booking rule editors and the profile read
	 * - vacation_periods {"periods":[...]} (or a plain list) -> d2g_vacation_periods (vacation notice, consult buttons)
	 * - prices video / walkin / email -> video_con_*, walk_in_*, written_con_* (consult buttons, payment requests)
	 *
	 * @param array $user_data
	 * @return array|WP_Error meta key => value
	 */
	public static function d2gc_get_wcc_doctor_meta( $user_data ) {
		$fields = array(
			'user_key'               => 'user_key',
			'organisation_key'       => 'organisation_key',
			'organisation_subdomain' => 'organisation_subdomain',
			'organisation_name'      => 'd2g_organisation',
			'user_title'             => 'd2g_emp_title',
			'user_first_name'        => 'd2g_first_name',
			'user_last_name'         => 'd2g_last_name',
			'user_email'             => 'd2g_main_email',
			'tariffs'                => 'tariffs',
			'vat_rules'              => 'vat_rules',
			'locations'              => 'locations_to_go',
		);
		$meta = array();
		foreach ( $fields as $field => $key ) {
			if ( ! array_key_exists( $field, $user_data ) || null === $user_data[ $field ] ) {
				continue;
			}
			$meta[ $key ] = is_scalar( $user_data[ $field ] ) ? sanitize_text_field( (string) $user_data[ $field ] ) : $user_data[ $field ];
		}

		/* booking rules */
		$rules = null;
		foreach ( array( 'booking_rules', 'advanced_booking_rules' ) as $field ) {
			if ( isset( $user_data[ $field ] ) && is_array( $user_data[ $field ] ) ) {
				$rules = $user_data[ $field ];
				break;
			}
		}
		if ( null !== $rules ) {
			$list = isset( $rules['rules'] ) ? $rules['rules'] : $rules;
			if ( ! is_array( $list ) ) {
				return new WP_Error( 'd2gc_invalid_booking_rules', 'booking_rules.rules must be a list.', array( 'status' => 400 ) );
			}
			$clean = array();
			foreach ( $list as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}
				$currency    = strtoupper( (string) ( $rule['currency'] ?? '' ) );
				$clean_rule  = array(
					'anchor_date'   => isset( $rule['anchor_date'] ) && null !== $rule['anchor_date'] ? absint( $rule['anchor_date'] ) : null,
					'start_time'    => sanitize_text_field( (string) ( $rule['start_time'] ?? '' ) ),
					'end_time'      => sanitize_text_field( (string) ( $rule['end_time'] ?? '' ) ),
					'wdays'         => absint( $rule['wdays'] ?? 0 ),
					'week_interval' => isset( $rule['week_interval'] ) ? absint( $rule['week_interval'] ) : 1,
					'price'         => D2gConnect_Public::d2gc_clean_rule_price( $rule['price'] ?? null ),
					'currency'      => preg_match( '/^[A-Z]{3}$/', $currency ) ? $currency : null,
				);
				// kept as WCC sends it (questionnaire of the rule)
				if ( isset( $rule['questionnaire'] ) && is_scalar( $rule['questionnaire'] ) && '' !== (string) $rule['questionnaire'] ) {
					$clean_rule['questionnaire'] = sanitize_text_field( (string) $rule['questionnaire'] );
				}
				$clean[] = $clean_rule;
			}
			$meta['simple_booking_rules'] = $clean;
		}

		/* vacation periods */
		if ( isset( $user_data['vacation_periods'] ) && is_array( $user_data['vacation_periods'] ) ) {
			$periods = isset( $user_data['vacation_periods']['periods'] ) ? $user_data['vacation_periods']['periods'] : $user_data['vacation_periods'];
			$periods = D2gConnect_Public::d2gc_clean_vacation_periods( $periods );
			if ( is_wp_error( $periods ) ) {
				return new WP_Error( 'd2gc_invalid_vacation_periods', 'vacation_periods: every period needs start_date and end_date as YYYY-MM-DD, end not before start.', array( 'status' => 400 ) );
			}
			$meta['d2g_vacation_periods'] = $periods;
		}

		/* prices */
		if ( isset( $user_data['prices'] ) && is_array( $user_data['prices'] ) ) {
			$targets = array(
				'video'  => 'video_con',
				'walkin' => 'walk_in',
				'email'  => 'written_con',
			);
			foreach ( $targets as $type => $prefix ) {
				if ( ! isset( $user_data['prices'][ $type ] ) || ! is_array( $user_data['prices'][ $type ] ) ) {
					continue;
				}
				$entry = $user_data['prices'][ $type ];
				if ( array_key_exists( 'price', $entry ) && null !== $entry['price'] && '' !== $entry['price'] ) {
					$price = str_replace( ',', '.', trim( (string) $entry['price'] ) );
					if ( ! preg_match( '/^\d+(\.\d{1,2})?$/', $price ) ) {
						return new WP_Error( 'd2gc_invalid_price', 'prices.' . $type . '.price must be a number (0 or more, max. 2 decimals).', array( 'status' => 400 ) );
					}
					$meta[ $prefix . '_price' ] = $price;
				}
				if ( ! empty( $entry['currency'] ) ) {
					$currency = strtoupper( (string) $entry['currency'] );
					if ( ! preg_match( '/^[A-Z]{3}$/', $currency ) ) {
						return new WP_Error( 'd2gc_invalid_currency', 'prices.' . $type . '.currency must be a 3 letter code (e.g. EUR).', array( 'status' => 400 ) );
					}
					$meta[ $prefix . '_currency' ] = $currency;
				}
			}
		}

		return $meta;
	}

	/**
	 * Name (and e-mail, when it is not used by another user) of the doctor's WordPress user from the WCC data.
	 *
	 * @param int   $user_id
	 * @param array $user_data
	 */
	private static function d2gc_update_wp_user_from_wcc( $user_id, $user_data ) {
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return;
		}
		$update = array( 'ID' => $user_id );
		if ( isset( $user_data['user_first_name'] ) ) {
			$update['first_name'] = sanitize_text_field( (string) $user_data['user_first_name'] );
		}
		if ( isset( $user_data['user_last_name'] ) ) {
			$update['last_name'] = sanitize_text_field( (string) $user_data['user_last_name'] );
		}
		$title = self::d2gc_get_wcc_title( $user_data );
		if ( '' !== $title ) {
			$update['display_name'] = $title;
		}
		$owner = email_exists( $user_data['user_email'] );
		if ( ! $owner ) {
			$update['user_email'] = $user_data['user_email'];
		}
		if ( count( $update ) > 1 ) {
			wp_update_user( $update );
		}
		update_user_meta( $user_id, 'user_key', $user_data['user_key'] ?? get_user_meta( $user_id, 'user_key', true ) );
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
		if (!self::d2gc_current_user_can_edit_doctor($update_id)) wp_die(esc_html__('You are not allowed to edit this profile.', 'doctor2go-connect'), 403);
		$currLang = isset($post_data['d2g_lang']) ? sanitize_key($post_data['d2g_lang']) : '';

		// META (sync-managed and private keys can not be changed from the front-end)
		$doc_data_meta = [];
		if (isset($post_data['meta']) && is_array($post_data['meta'])) {
			foreach ($post_data['meta'] as $meta_key => $meta_value) {
				$meta_key = sanitize_key($meta_key);
				if ('' === $meta_key || '_' === $meta_key[0] || in_array($meta_key, self::D2GC_PROTECTED_DOC_META, true)) continue;
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

		// TAX (only the doctor taxonomies shown in the UI, so e.g. the Polylang language can not be changed)
		$doc_tax     = [];
		$allowed_tax = array_keys(wp_filter_object_list(get_object_taxonomies('d2g_doctor', 'objects'), ['show_ui' => true]));
		if (isset($post_data['tax']) && is_array($post_data['tax'])) {
			foreach ($post_data['tax'] as $tax_key => $tax_value) {
				$tax_key = sanitize_key($tax_key);
				if (!in_array($tax_key, $allowed_tax, true)) continue;
				if (is_array($tax_value)) $doc_tax[$tax_key] = array_map('sanitize_text_field', $tax_value);
				else $doc_tax[$tax_key] = sanitize_text_field($tax_value);
			}
		}

		// STATUS (doctors can not publish a profile that has been deactivated in WCC)
		$current_status = get_post_status($update_id);
		$post_status    = sanitize_key($post_data['post_status'] ?? $current_status);
		if (!in_array($post_status, ['draft', 'pending', 'publish'], true)) $post_status = $current_status;
		if ('publish' === $post_status && 'publish' !== $current_status && 'inactive' === get_post_meta($update_id, 'wcc_status', true) && !current_user_can('edit_others_posts')) $post_status = $current_status;

		// Prepare Post Update
		$my_update = [
			'ID'           => $update_id,
			'post_title'   => sanitize_text_field($post_data['post_title'] ?? ''),
			'post_content' => wp_kses_post($post_data['docdesc'] ?? ''),
			'post_status'  => $post_status,
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

		// copy the language independent fields, the profile picture and the status to the other languages
		D2GC_Doctor_Sync::sync($update_id);

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

		if ( ! self::d2gc_current_user_can_edit_doctor( $post_id ) || empty( $_FILES ) || ! is_array( $_FILES ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
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

		if ( ! self::d2gc_current_user_can_edit_doctor( $doc_id ) ) {
			wp_send_json_error( null, 403 );
		}

		// only the profile picture of this doctor may be deleted
		if ( $image_id && get_post_thumbnail_id( $doc_id ) === $image_id ) {
			// remove the picture in all languages (the sync only copies fields that exist, not removed ones)
			foreach ( D2GC_Translations::get_post_translation_ids( $doc_id ) as $translation_id ) {
				if ( get_post_thumbnail_id( $translation_id ) === $image_id ) {
					delete_post_thumbnail( $translation_id );
				}
			}
			wp_delete_attachment( $image_id );
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
