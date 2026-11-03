<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small adapter for multilingual plugins that store every translation as its own post (Polylang, WPML).
 * Used to keep the language independent data of a doctor the same in all its translations.
 *
 * Plugins that translate on the fly (TranslatePress, Weglot, ...) use one post for all languages and need nothing here.
 * Other plugins can be supported via the d2gc_doctor_translation_ids / d2gc_term_translation_ids filters.
 *
 * @since 1.0.2
 */
class D2GC_Translations {

	/**
	 * The active multilingual plugin.
	 *
	 * @return string 'polylang', 'wpml' or '' (none)
	 */
	public static function provider() {
		if ( function_exists( 'pll_get_post_translations' ) ) {
			return 'polylang';
		}
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return 'wpml';
		}
		return '';
	}

	/**
	 * All translations of a post, including the post itself.
	 *
	 * @param int $post_id
	 * @return int[] post ids, the given post first
	 */
	public static function get_post_translation_ids( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return array();
		}

		$ids = array( $post_id );

		switch ( self::provider() ) {
			case 'polylang':
				$ids = array_merge( $ids, array_values( (array) pll_get_post_translations( $post_id ) ) );
				break;

			case 'wpml':
				$element_type = 'post_' . get_post_type( $post_id );
				$trid         = apply_filters( 'wpml_element_trid', null, $post_id, $element_type );
				if ( $trid ) {
					$translations = (array) apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );
					foreach ( $translations as $translation ) {
						if ( ! empty( $translation->element_id ) ) {
							$ids[] = $translation->element_id;
						}
					}
				}
				break;
		}

		/**
		 * Filters the translations of a doctor post (including the post itself).
		 *
		 * @param int[] $ids     post ids
		 * @param int   $post_id the post the translations were requested for
		 */
		$ids = apply_filters( 'd2gc_doctor_translation_ids', $ids, $post_id );

		return array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	}

	/**
	 * Language code of a post ('en', 'de', ...), empty when unknown or no multilingual plugin is active.
	 *
	 * @param int $post_id
	 * @return string
	 */
	public static function get_post_language( $post_id ) {
		switch ( self::provider() ) {
			case 'polylang':
				return (string) pll_get_post_language( $post_id, 'slug' );

			case 'wpml':
				$details = apply_filters( 'wpml_post_language_details', null, $post_id );
				return is_array( $details ) && ! empty( $details['language_code'] ) ? (string) $details['language_code'] : '';
		}
		return '';
	}

	/**
	 * Language codes of all languages of the site ('en', 'de', ...), empty when no multilingual plugin is active.
	 *
	 * @return string[]
	 */
	public static function get_languages() {
		$languages = array();

		switch ( self::provider() ) {
			case 'polylang':
				$languages = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list( array( 'fields' => 'slug' ) ) : array();
				break;

			case 'wpml':
				$languages = array_keys( (array) apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) ) );
				break;
		}

		/**
		 * Filters the languages a doctor profile should be translated into.
		 *
		 * @param string[] $languages language codes
		 */
		return array_values( array_filter( array_map( 'strval', (array) apply_filters( 'd2gc_languages', $languages ) ) ) );
	}

	/**
	 * Whether the terms of a taxonomy are translated (a separate term per language). Not translated = the same terms in all languages.
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	public static function is_translated_taxonomy( $taxonomy ) {
		switch ( self::provider() ) {
			case 'polylang':
				return function_exists( 'pll_is_translated_taxonomy' ) && pll_is_translated_taxonomy( $taxonomy );

			case 'wpml':
				return (bool) apply_filters( 'wpml_is_translated_taxonomy', false, $taxonomy );
		}
		return false;
	}

	/**
	 * The translation of a term in a given language (for syncing taxonomies between translated posts).
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @param string $language language code of the target post
	 * @return int term id in that language; 0 when the term has no translation in that language (then it must not be
	 *             assigned, it would be a term of another language); the given term when no multilingual plugin is active
	 */
	public static function get_term_translation( $term_id, $taxonomy, $language ) {
		$provider = self::provider();
		if ( '' === $provider || '' === (string) $language ) {
			return (int) $term_id;
		}

		$translated = 0;

		switch ( $provider ) {
			case 'polylang':
				$translated = function_exists( 'pll_get_term' ) ? (int) pll_get_term( $term_id, $language ) : 0;
				break;

			case 'wpml':
				$translated = (int) apply_filters( 'wpml_object_id', $term_id, $taxonomy, false, $language );
				break;
		}

		/**
		 * Filters the translation of a term.
		 *
		 * @param int    $translated term id in $language, 0 when there is none
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @param string $language
		 */
		return (int) apply_filters( 'd2gc_term_translation', $translated, $term_id, $taxonomy, $language );
	}
}
