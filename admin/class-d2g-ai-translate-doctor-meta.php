<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class D2GC_Doctor_AI_Translator {

    public function __construct() {
        add_action( 'add_meta_boxes_d2g_doctor', array( $this, 'add_translate_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_d2gc_translate_doctor_fields_into_current_post', array( $this, 'ajax_translate_into_current_post' ) );
    }

    public function add_translate_metabox() {
        add_meta_box(
            'd2gc_ai_translate_into_current',
            'AI Translate Custom Fields',
            array( $this, 'render_translate_metabox' ),
            'd2g_doctor',
            'side',
            'high'
        );
    }

    public function render_translate_metabox( $post ) {
        if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_default_language' ) ) {
            echo '<p>Polylang is required.</p>';
            return;
        }

        $current_lang = pll_get_post_language( $post->ID );
        $source_lang  = pll_default_language();

        wp_nonce_field( 'd2gc_translate_doctor_fields_into_current_post', 'd2gc_translate_nonce' );

        echo '<p><strong>Current post language:</strong> ' . esc_html( strtoupper( (string) $current_lang ) ) . '</p>';
        echo '<p><strong>Source language:</strong> ' . esc_html( strtoupper( (string) $source_lang ) ) . '</p>';

        if ( empty( $current_lang ) || empty( $source_lang ) ) {
            echo '<p>Could not determine languages.</p>';
            return;
        }

        if ( $current_lang === $source_lang ) {
            echo '<p>This is the source-language post. Open a translated doctor post to use this button.</p>';
            return;
        }

        echo '<p>This will translate the linked source doctor custom meta into the current post. You can optionally include title, content, and excerpt.</p>';

        echo '<p style="margin:10px 0;">';
        echo '<label for="d2gc-translate-post-fields">';
        echo '<input type="checkbox" id="d2gc-translate-post-fields" value="1" style="margin-right:6px;" />';
        echo 'Also translate title, content, and excerpt';
        echo '</label>';
        echo '</p>';

        echo '<p><button type="button" class="button button-primary" id="d2gc-translate-current-btn" data-post-id="' . esc_attr( $post->ID ) . '">Translate into this post</button></p>';
        echo '<div id="d2gc-translate-result" style="margin-top:10px;"></div>';
    }

    public function enqueue_assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        $screen = get_current_screen();

        if ( ! $screen || 'd2g_doctor' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_script(
            'd2gc-ai-translate-current',
            plugin_dir_url( __FILE__ ) . '../admin/js/d2gc-ai-translate-current.js',
            array( 'jquery' ),
            '1.1.0',
            true
        );

        wp_localize_script(
            'd2gc-ai-translate-current',
            'd2gcAiTranslateCurrent',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'd2gc_translate_doctor_fields_into_current_post' ),
            )
        );
    }

    public function ajax_translate_into_current_post() {
        check_ajax_referer( 'd2gc_translate_doctor_fields_into_current_post', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
        }

        if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_get_post' ) ) {
            wp_send_json_error( array( 'message' => 'Polylang is required.' ), 500 );
        }

        $current_post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $translate_post_fields = ! empty( $_POST['translate_post_fields'] ) && '1' === (string) $_POST['translate_post_fields'];

        if ( ! $current_post_id || 'd2g_doctor' !== get_post_type( $current_post_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid doctor post.' ), 400 );
        }

        if ( ! current_user_can( 'edit_post', $current_post_id ) ) {
            wp_send_json_error( array( 'message' => 'You are not allowed to edit this post.' ), 403 );
        }

        $current_lang = pll_get_post_language( $current_post_id );
        $source_lang  = pll_default_language();

        if ( empty( $current_lang ) || empty( $source_lang ) ) {
            wp_send_json_error( array( 'message' => 'Could not determine source/current language.' ), 400 );
        }

        if ( $current_lang === $source_lang ) {
            wp_send_json_error( array( 'message' => 'This button is only for translated posts, not the source-language post.' ), 400 );
        }

        $source_post_id = pll_get_post( $current_post_id, $source_lang );

        if ( empty( $source_post_id ) || (int) $source_post_id === (int) $current_post_id ) {
            wp_send_json_error( array( 'message' => 'No linked source doctor found.' ), 400 );
        }

        $meta_fields = $this->get_translatable_fields();

        $source_post_fields = array();
        if ( $translate_post_fields ) {
            $source_post_fields = array(
                'post_title'   => get_post_field( 'post_title', $source_post_id ),
                'post_content' => get_post_field( 'post_content', $source_post_id ),
                'post_excerpt' => get_post_field( 'post_excerpt', $source_post_id ),
            );
        }

        $source_meta_fields = array();

        foreach ( $meta_fields as $field_key ) {
            $source_meta_fields[ $field_key ] = get_post_meta( $source_post_id, $field_key, true );
        }

        $translated = $this->translate_fields_with_ai(
            array(
                'translate_post_fields' => $translate_post_fields,
                'post_fields'           => $source_post_fields,
                'meta_fields'           => $source_meta_fields,
            ),
            $current_lang
        );

        if ( is_wp_error( $translated ) ) {
            wp_send_json_error( array( 'message' => $translated->get_error_message() ), 500 );
        }

        $updated_post_fields = array();
        $saved_meta_keys     = array();

        if ( $translate_post_fields && isset( $translated['post_fields'] ) && is_array( $translated['post_fields'] ) ) {
            $post_update = array(
                'ID' => $current_post_id,
            );

            if ( array_key_exists( 'post_title', $translated['post_fields'] ) ) {
                $post_update['post_title'] = $translated['post_fields']['post_title'];
                $updated_post_fields[] = 'post_title';
            }

            if ( array_key_exists( 'post_content', $translated['post_fields'] ) ) {
                $post_update['post_content'] = $translated['post_fields']['post_content'];
                $updated_post_fields[] = 'post_content';
            }

            if ( array_key_exists( 'post_excerpt', $translated['post_fields'] ) ) {
                $post_update['post_excerpt'] = $translated['post_fields']['post_excerpt'];
                $updated_post_fields[] = 'post_excerpt';
            }

            if ( count( $post_update ) > 1 ) {
                $result = wp_update_post( wp_slash( $post_update ), true );

                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
                }
            }
        }

        if ( isset( $translated['meta_fields'] ) && is_array( $translated['meta_fields'] ) ) {
            foreach ( $meta_fields as $field_key ) {
                if ( array_key_exists( $field_key, $translated['meta_fields'] ) ) {
                    update_post_meta( $current_post_id, $field_key, $translated['meta_fields'][ $field_key ] );
                    $saved_meta_keys[] = $field_key;
                }
            }
        }

        $message = $translate_post_fields
            ? 'Translated selected post fields and custom fields saved into current post.'
            : 'Translated custom fields saved into current post.';

        wp_send_json_success(
            array(
                'message'               => $message,
                'source_post_id'        => (int) $source_post_id,
                'current_post_id'       => (int) $current_post_id,
                'current_lang'          => $current_lang,
                'translate_post_fields' => $translate_post_fields,
                'updated_post_fields'   => $updated_post_fields,
                'saved_keys'            => $saved_meta_keys,
            )
        );
    }

    private function get_translatable_fields() {
        return array(
            'd2g_emp_title',
            'd2g_city',
            'd2g_organisation',
            'reg_country',
            'sub_title',
            'edus',
            'exps',
            'pubs',
        );
    }

    private function translate_fields_with_ai( $source_data, $target_lang ) {
        $api_key = defined( 'D2GC_AI_API_KEY' ) && D2GC_AI_API_KEY ? D2GC_AI_API_KEY : '';

        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_api_key', 'D2GC_AI_API_KEY is not defined.' );
        }

        $translate_post_fields = ! empty( $source_data['translate_post_fields'] );
        $post_fields           = isset( $source_data['post_fields'] ) && is_array( $source_data['post_fields'] ) ? $source_data['post_fields'] : array();
        $meta_fields           = isset( $source_data['meta_fields'] ) && is_array( $source_data['meta_fields'] ) ? $source_data['meta_fields'] : array();

        $system_prompt = 'You translate WordPress doctor post data. Return valid JSON only with exactly two top-level keys: post_fields and meta_fields. Preserve all keys and structure exactly. Translate human-readable text into the target language. Preserve HTML formatting in post_content and any HTML-bearing meta fields. For array meta fields such as edus, exps, and pubs, preserve the exact nested structure, indexes, and subkeys, but translate only the human-readable text values inside them. Do not invent, remove, reorder, or rename keys. Leave names, phone numbers, emails, URLs, years, dates, IDs, registration numbers, and proper nouns unchanged unless clearly translatable.';

        if ( ! $translate_post_fields ) {
            $system_prompt .= ' The post_fields object may be empty. In that case, return post_fields as an empty object and translate only meta_fields.';
        }

        $payload = array(
            'model'       => 'gpt-4.1-mini',
            'temperature' => 0.2,
            'response_format' => array(
                'type' => 'json_object',
            ),
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => wp_json_encode(
                        array(
                            'target_language'       => $target_lang,
                            'translate_post_fields' => $translate_post_fields,
                            'post_fields'           => $post_fields,
                            'meta_fields'           => $meta_fields,
                        ),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                ),
            ),
        );

        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            array(
                'timeout' => 90,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode( $payload ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );

        if ( 200 !== (int) $status_code ) {
            return new WP_Error( 'api_error', 'AI API error: ' . $body );
        }

        $data    = json_decode( $body, true );
        $content = $data['choices'][0]['message']['content'] ?? '';

        if ( empty( $content ) ) {
            return new WP_Error( 'empty_response', 'AI returned an empty response.' );
        }

        $decoded = json_decode( $content, true );

        if ( ! is_array( $decoded ) ) {
            return new WP_Error( 'invalid_json', 'AI response was not valid JSON.' );
        }

        if ( ! isset( $decoded['post_fields'] ) || ! is_array( $decoded['post_fields'] ) ) {
            $decoded['post_fields'] = array();
        }

        if ( ! isset( $decoded['meta_fields'] ) || ! is_array( $decoded['meta_fields'] ) ) {
            $decoded['meta_fields'] = array();
        }

        return $decoded;
    }
}

new D2GC_Doctor_AI_Translator();