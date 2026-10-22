<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps the language independent data of a doctor the same in all its translations (Polylang / WPML, see D2GC_Translations).
 * Whichever translation was edited is copied to the others, there is no fixed source language.
 *
 * Not synced here: the availability fields (d2g_walk_in, d2g_availability_check, d2g_first_availability, d2g_tariffs,
 * d2g_last_synced, d2g_timecode), those are written to all translations by D2gConnect_Worker (cron).
 *
 * @since 1.0.2
 */
class D2GC_Doctor_Sync {

	/**
	 * True while a sync is running, so save hooks triggered by the sync itself do nothing (no loops, see is_syncing()).
	 *
	 * @var bool
	 */
	private static $syncing = false;

	/**
	 * Meta keys that link the doctor to WCC. Never copied when empty: an incomplete translation must not
	 * remove the WCC connection of the others.
	 *
	 * @var string[]
	 */
	private static $identity_keys = array( 'd2g_user_id', 'wcc_user_id', 'user_key', 'organisation_key', 'organisation_subdomain' );

	/**
	 * Post statuses that are synced (trash, auto-draft etc. are not).
	 *
	 * @var string[]
	 */
	private static $synced_statuses = array( 'publish', 'draft', 'pending', 'private' );

	/**
	 * Registers the sync on every save of a doctor post (wp-admin, quick edit, new translations, ...).
	 */
	public static function init() {
		// wp_after_insert_post, not save_post: it fires after the meta boxes (d2gc_meta_box_save on save_post) and the
		// terms are saved, so the new values are synced and not the old ones (classic and block editor)
		add_action( 'wp_after_insert_post', array( __CLASS__, 'on_save_post' ), 100, 2 );

		// "Translations" section on the settings page: check and align existing doctors
		add_action( 'wp_ajax_d2gc_translations_check', array( __CLASS__, 'ajax_check' ) );
		add_action( 'wp_ajax_d2gc_translations_align', array( __CLASS__, 'ajax_align' ) );
	}

	/**
	 * All doctors that have translations, with the most recently edited translation as source (used to align them).
	 *
	 * @return array[] list of array( 'source' => int, 'ids' => int[] )
	 */
	public static function get_doctor_groups() {
		$posts = get_posts(
			array(
				'post_type'        => 'd2g_doctor',
				'post_status'      => self::$synced_statuses,
				'numberposts'      => -1,
				'fields'           => 'ids',
				'lang'             => '', // Polylang: all languages
				'suppress_filters' => true, // WPML: all languages
			)
		);

		$seen   = array();
		$groups = array();
		foreach ( $posts as $post_id ) {
			if ( isset( $seen[ $post_id ] ) ) {
				continue;
			}
			$ids = D2GC_Translations::get_post_translation_ids( $post_id );
			foreach ( $ids as $id ) {
				$seen[ $id ] = true;
			}
			if ( count( $ids ) < 2 ) {
				continue;
			}

			$modified = array();
			foreach ( $ids as $id ) {
				$modified[ $id ] = get_post_modified_time( 'U', true, $id );
			}
			arsort( $modified );

			$groups[] = array(
				'source' => (int) key( $modified ),
				'ids'    => $ids,
			);
		}

		return $groups;
	}

	/**
	 * What would change in the translations when the source is synced (same rules as sync()).
	 *
	 * @param int   $source
	 * @param int[] $ids all translations including the source
	 * @return string[] meta keys and taxonomy names that differ in at least one translation
	 */
	public static function get_differences( $source, $ids ) {
		$differences = array();
		$targets     = array_diff( $ids, array( $source ) );

		foreach ( self::get_synced_meta_keys() as $key ) {
			if ( ! metadata_exists( 'post', $source, $key ) ) {
				continue;
			}
			$value = get_post_meta( $source, $key, true );
			if ( ( '' === $value || null === $value ) && in_array( $key, self::$identity_keys, true ) ) {
				continue;
			}
			foreach ( $targets as $target_id ) {
				if ( maybe_serialize( $value ) !== maybe_serialize( get_post_meta( $target_id, $key, true ) ) ) {
					$differences[] = $key;
					break;
				}
			}
		}

		foreach ( self::get_synced_taxonomies() as $taxonomy ) {
			$term_ids = taxonomy_exists( $taxonomy ) ? wp_get_object_terms( $source, $taxonomy, array( 'fields' => 'ids' ) ) : array();
			if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
				continue;
			}
			$translated = D2GC_Translations::is_translated_taxonomy( $taxonomy );
			foreach ( $targets as $target_id ) {
				$language = D2GC_Translations::get_post_language( $target_id );
				$want     = $translated ? array_filter(
					array_map(
						function ( $term_id ) use ( $taxonomy, $language ) {
							return D2GC_Translations::get_term_translation( $term_id, $taxonomy, $language );
						},
						$term_ids
					)
				) : $term_ids;
				$have = wp_get_object_terms( $target_id, $taxonomy, array( 'fields' => 'ids' ) );
				$want = array_map( 'intval', $want );
				$have = is_wp_error( $have ) ? array() : array_map( 'intval', $have );
				sort( $want );
				sort( $have );
				if ( $want !== $have ) {
					$differences[] = $taxonomy;
					break;
				}
			}
		}

		return $differences;
	}

	/**
	 * Readable name of a synced field or taxonomy for the overview on the settings page.
	 *
	 * @param string $key meta key or taxonomy name
	 * @return string
	 */
	private static function get_field_label( $key ) {
		if ( taxonomy_exists( $key ) ) {
			return get_taxonomy( $key )->labels->name;
		}
		$labels = array(
			'd2g_first_name'    => __( 'First name', 'doctor2go-connect' ),
			'd2g_last_name'     => __( 'Last name', 'doctor2go-connect' ),
			'd2g_emp_title'     => __( 'Title', 'doctor2go-connect' ),
			'd2g_organisation'  => __( 'Organisation', 'doctor2go-connect' ),
			'd2g_address'       => __( 'Address', 'doctor2go-connect' ),
			'd2g_zip'           => __( 'Zip code', 'doctor2go-connect' ),
			'd2g_city'          => __( 'City', 'doctor2go-connect' ),
			'tel'               => __( 'Phone', 'doctor2go-connect' ),
			'd2g_mobile'        => __( 'Mobile', 'doctor2go-connect' ),
			'd2g_main_email'    => __( 'E-mail', 'doctor2go-connect' ),
			'reg_nr'            => __( 'Registration number', 'doctor2go-connect' ),
			'reg_country'       => __( 'Country of registration', 'doctor2go-connect' ),
			'walk_in_price'     => __( 'Walk-in price', 'doctor2go-connect' ),
			'written_con_price' => __( 'E-mail advice price', 'doctor2go-connect' ),
			'video_con_price'   => __( 'Video consultation price', 'doctor2go-connect' ),
			'simple_booking_rules' => __( 'Booking rules', 'doctor2go-connect' ),
			'd2g_vacation_periods' => __( 'Vacation periods', 'doctor2go-connect' ),
			'_thumbnail_id'     => __( 'Profile picture', 'doctor2go-connect' ),
		);
		return isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
	}

	/**
	 * Checks the AJAX request of the settings page: admins only, valid nonce.
	 */
	private static function check_admin_request() {
		check_ajax_referer( 'd2gc_translations', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'doctor2go-connect' ) ), 403 );
		}
	}

	/**
	 * AJAX (settings page): list of the doctors whose translations differ. Read-only.
	 */
	public static function ajax_check() {
		self::check_admin_request();

		$groups  = self::get_doctor_groups();
		$doctors = array();
		foreach ( $groups as $group ) {
			$differences = self::get_differences( $group['source'], $group['ids'] );
			if ( empty( $differences ) ) {
				continue;
			}
			$doctors[] = array(
				'source'      => $group['source'],
				'title'       => get_the_title( $group['source'] ),
				'edit_link'   => get_edit_post_link( $group['source'], 'raw' ),
				'language'    => D2GC_Translations::get_post_language( $group['source'] ),
				'languages'   => count( $group['ids'] ),
				'differences' => array_map( array( __CLASS__, 'get_field_label' ), $differences ),
			);
		}

		wp_send_json_success(
			array(
				'total'   => count( $groups ),
				'doctors' => $doctors,
			)
		);
	}

	/**
	 * AJAX (settings page): align the translations of the given doctors (a batch of max. 10), the post status is not changed.
	 */
	public static function ajax_align() {
		self::check_admin_request();

		$sources = isset( $_POST['sources'] ) ? array_slice( array_map( 'absint', (array) wp_unslash( $_POST['sources'] ) ), 0, 10 ) : array();
		$done    = 0;
		foreach ( $sources as $source ) {
			if ( 'd2g_doctor' === get_post_type( $source ) && self::sync( $source, null, false ) ) {
				$done++;
			}
		}

		wp_send_json_success( array( 'done' => $done ) );
	}

	/**
	 * Save of a doctor post: a new translation takes over the data of the existing translations, an existing
	 * translation passes its data on to the others. The post status is not synced here (a translator saving a draft
	 * must not take the other languages offline); that only happens in the profile editor and the WCC sync.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function on_save_post( $post_id, $post ) {
		if ( ! $post instanceof WP_Post || 'd2g_doctor' !== $post->post_type ) {
			return;
		}
		if ( self::$syncing || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( in_array( $post->post_status, array( 'auto-draft', 'trash', 'inherit' ), true ) ) {
			return;
		}
		// the profile editor syncs itself (incl. status) once all its fields are saved
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- only reads which AJAX action is running, the handler verifies its nonce
		if ( wp_doing_ajax() && isset( $_POST['action'] ) && 'd2gc_update_doc' === $_POST['action'] ) {
			return;
		}

		$source = self::get_pull_source( $post_id );
		if ( $source ) {
			// new translation: take over everything from the existing translations, nothing changes for them
			self::sync( $source, null, false );
			return;
		}

		self::sync( $post_id, null, false );
	}

	/**
	 * For a new translation (no link to WCC yet): the most recently edited translation that has one, to copy from.
	 *
	 * @param int $post_id
	 * @return int 0 when the post is not a new translation
	 */
	private static function get_pull_source( $post_id ) {
		if ( metadata_exists( 'post', $post_id, 'user_key' ) || metadata_exists( 'post', $post_id, 'd2g_user_id' ) ) {
			return 0;
		}

		$candidates = array();
		foreach ( D2GC_Translations::get_post_translation_ids( $post_id ) as $translation_id ) {
			if ( $translation_id !== (int) $post_id && get_post_meta( $translation_id, 'user_key', true ) ) {
				$candidates[ $translation_id ] = get_post_modified_time( 'U', true, $translation_id );
			}
		}
		if ( empty( $candidates ) ) {
			return 0;
		}
		arsort( $candidates );
		return (int) key( $candidates );
	}

	/**
	 * Whether a sync is running right now. Save handlers use this to ignore the saves the sync triggers itself.
	 *
	 * @return bool
	 */
	public static function is_syncing() {
		return self::$syncing;
	}

	/**
	 * The meta keys that are the same in every language.
	 *
	 * @return string[]
	 */
	public static function get_synced_meta_keys() {
		$keys = array(
			// link to WCC and the WordPress user
			'd2g_user_id',
			'wcc_user_id',
			'user_key',
			'organisation_key',
			'organisation_subdomain',
			'd2g_main_email',
			// name, title, address, contact, registration
			'd2g_first_name',
			'd2g_last_name',
			'd2g_emp_title',
			'd2g_organisation',
			'd2g_address',
			'd2g_zip',
			'd2g_city',
			'tel',
			'd2g_mobile',
			'reg_nr',
			'reg_country',
			'd2g_cal_code',
			// prices and consultation settings
			'written_con_price',
			'written_con_currency',
			'written_con_type',
			'intake_type',
			'walk_in_price',
			'walk_in_currency',
			'video_con_price',
			'video_con_currency',
			'avg_price',
			'avg_price_currency',
			'd2g_intake_call',
			'd2g_service_price',
			'd2g_custom_service_price',
			'tariffs',
			'vat_rules',
			'locations_to_go',
			// availability and absence
			'simple_booking_rules',
			'd2g_vacation_periods',
			// profile picture
			'_thumbnail_id',
		);

		/**
		 * Filters the doctor meta keys that are kept the same in all translations.
		 *
		 * @param string[] $keys
		 */
		return (array) apply_filters( 'd2gc_synced_doctor_meta', $keys );
	}

	/**
	 * The taxonomies whose terms are kept the same in all translations (translated taxonomies via the term translations).
	 *
	 * @return string[]
	 */
	public static function get_synced_taxonomies() {
		/**
		 * Filters the doctor taxonomies that are kept the same in all translations.
		 *
		 * @param string[] $taxonomies
		 */
		return (array) apply_filters( 'd2gc_synced_doctor_taxonomies', array( 'doctor-specialty', 'doctor-language', 'country-origin', 'body-part' ) );
	}

	/**
	 * Gives the translations the same terms as the edited doctor: for translated taxonomies the term in the language
	 * of the translation (a term without translation in that language is left out, it would be of another language),
	 * otherwise the same terms.
	 *
	 * @param int   $doctor_id
	 * @param int[] $targets
	 */
	private static function sync_terms( $doctor_id, $targets ) {
		foreach ( self::get_synced_taxonomies() as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$term_ids = wp_get_object_terms( $doctor_id, $taxonomy, array( 'fields' => 'ids' ) );
			// an empty term list is never copied (like meta: only what is set), so an incomplete translation can not
			// remove the terms of the others
			if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
				continue;
			}
			$translated = D2GC_Translations::is_translated_taxonomy( $taxonomy );

			foreach ( $targets as $target_id ) {
				$target_terms = $term_ids;
				if ( $translated ) {
					$language     = D2GC_Translations::get_post_language( $target_id );
					$target_terms = array_filter(
						array_map(
							function ( $term_id ) use ( $taxonomy, $language ) {
								return D2GC_Translations::get_term_translation( $term_id, $taxonomy, $language );
							},
							$term_ids
						)
					);
				}
				wp_set_object_terms( $target_id, array_values( array_map( 'intval', $target_terms ) ), $taxonomy );
			}
		}
	}

	/**
	 * Copies the language independent data of a doctor to all its translations.
	 *
	 * @param int           $doctor_id   the translation that was edited
	 * @param string[]|null $keys        meta keys to sync, null = everything: all synced keys (get_synced_meta_keys())
	 *                                   and the taxonomies (get_synced_taxonomies())
	 * @param bool          $sync_status also apply the post status (published / draft / ...) to the translations
	 * @return int number of translations that were updated
	 */
	public static function sync( $doctor_id, $keys = null, $sync_status = true ) {
		$doctor_id = absint( $doctor_id );
		if ( self::$syncing || ! $doctor_id || 'd2g_doctor' !== get_post_type( $doctor_id ) ) {
			return 0;
		}

		$targets = array_diff( D2GC_Translations::get_post_translation_ids( $doctor_id ), array( $doctor_id ) );
		if ( empty( $targets ) ) {
			return 0;
		}

		$full_sync = null === $keys;
		$keys      = $full_sync ? self::get_synced_meta_keys() : array_intersect( (array) $keys, self::get_synced_meta_keys() );
		$status    = get_post_status( $doctor_id );

		self::$syncing = true;
		try {
			foreach ( $targets as $target_id ) {
				foreach ( $keys as $key ) {
					// only fields the edited translation has; a field it never had stays as it is elsewhere
					if ( ! metadata_exists( 'post', $doctor_id, $key ) ) {
						continue;
					}
					$value = get_post_meta( $doctor_id, $key, true );

					if ( ( '' === $value || null === $value ) && in_array( $key, self::$identity_keys, true ) ) {
						continue;
					}
					update_post_meta( $target_id, $key, $value );
				}

				if ( $sync_status && in_array( $status, self::$synced_statuses, true ) && get_post_status( $target_id ) !== $status
					&& in_array( get_post_status( $target_id ), self::$synced_statuses, true ) ) {
					wp_update_post(
						array(
							'ID'          => $target_id,
							'post_status' => $status,
						)
					);
				}
			}

			if ( $full_sync ) {
				self::sync_terms( $doctor_id, $targets );
			}
		} finally {
			self::$syncing = false;
		}

		/**
		 * Fires after the language independent data of a doctor was copied to its translations.
		 *
		 * @param int   $doctor_id the edited translation
		 * @param int[] $targets   the updated translations
		 */
		do_action( 'd2gc_doctor_synced', $doctor_id, $targets );

		return count( $targets );
	}
}
