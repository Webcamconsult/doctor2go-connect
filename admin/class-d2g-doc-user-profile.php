<?php
/**
 * Doctor profile save Ajax actions. 
 *
 * @package d2g-connect
 */
class D2G_doc_user_profile{
    public static function init() {
      
		////////////////
		//ajax functions
		//update doctor profile post
		add_action( 'wp_ajax_d2g_update_doc', array(__CLASS__, 'd2g_update_doc') );
		add_action( 'wp_ajax_nopriv_d2g_update_doc', array(__CLASS__, 'd2g_update_doc') );

        add_action( 'wp_ajax_d2g_delete_profile_pic', array(__CLASS__, 'd2g_delete_profile_pic') );
		add_action( 'wp_ajax_nopriv_d2g_delete_profile_pic', array(__CLASS__, 'd2g_delete_profile_pic') );

        //adds a doctor profile post when creating a user with a doctor role if the option is checked
		if(get_option('d2g_local_user') == 1){
			add_action( 'user_register', array(__CLASS__, 'd2g_create_doc_profile_locally'), 10, 2 );
		} 
		
		//this redirects the user with a doctor role to his profile edit page
		add_action( 'wp_login', array(__CLASS__, 'd2g_redirect_doctor'), 10 );

        //this resticts the admin access so that (this needs to become optional)
		add_action( 'admin_init', array(__CLASS__, 'd2g_restict_admin'), 12 );

        //adapt edit URL when polylang is used
		/*
		if(is_plugin_active('polylang/polylang.php')){
			self::loader->add_filter( 'pll_translate_post_meta', $plugin_admin, 'd2g_translate_post_meta', 10, 3 );
		}

		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			self::loader->add_filter('wpml_tm_save_translation_cf', $plugin_admin, 'callback_2', 10, 3);

		}*/
	}


    /**
     * @param $currUserID
     * @return int[]|WP_Post[]
     */
    public static function d2g_get_pub_profile($currUserID){
        $args = array(
            'post_type'  => 'd2g_doctor',
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key'     => 'd2g_user_id',
                    'value'   => $currUserID
                ),
            ),
        );
        $pubProfile = get_posts($args);
        return $pubProfile;
    }
   
    /*
    *retrieve the URL from the doc profile edit page in the front-end
    */
    public static function d2g_page_url($lang = '', $d2g_page_identifier = '', $title = false){
        $args            = array(
            'post_type'     => 'page',
            'meta_query'    => array(
                array(
                    'key'       => 'd2g_page_identifier',
                    'value'     => ($d2g_page_identifier)?:'my_profile'
                ),
            ),
        );
        if($lang != ''){
            $args['lang'] = $lang;
        }
        $page_id           = get_posts($args)[0]->ID;
        if($title == false){
            $profile_edit_url               = get_permalink($page_id);
            return $profile_edit_url;
        } else {
            $page['title'] = get_the_title( $page_id );
            $page['url'] = get_permalink( $page_id );
            return $page;
        }
        
    }

    /*
    * This function is called when setting is checked that profile is created locally when user is created localy, this function is not really used and my be removed in future
    * There for no nonce check, because this is an internal function and not called via ajax  
    */
    public static function d2g_create_doc_profile_locally($user_id, $userdata){
        
        
        if(isset($_POST['role']) && $_POST['role'] == 'doctor'){// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce not required for this internal action.
            //get user object
            $u = new WP_User( $user_id );

            // Remove standard role and add new role
            $u->remove_role( 'subscriber' );
            $u->add_role( 'doctor' );

            //creates the doctor profile post and sets some meta data (user_id and email)
            $post = array(
                'post_content'      => '',
                'post_status'       => 'draft',
                'post_title'        =>  $userdata['user_email'],
                'post_type'         => 'd2g_doctor',
                'post_author'       => $user_id
            );
            
            $doctor_id = (int)wp_insert_post( $post );
            update_post_meta( $doctor_id, 'd2g_main_email', $userdata['user_email'] );
            update_post_meta( $doctor_id, 'd2g_user_id', $user_id );
        }
        
        
        
    }

    /*
    * create user via API from WCC
    */
    public static function d2g_create_doc_user($user_data){
     
        $user_login = self::d2g_clean_name($user_data['user_title'].$user_data['user_initials'].$user_data['user_last_name'].time());
    
        $user_input = array(
            'user_login'    => $user_login,
            'user_pass'     => $user_data['pass']?:'wcc_'.$user_login,
            'user_email'    => $user_data['user_email'],
            'first_name'    => $user_data['user_first_name'],
            'last_name'     => $user_data['user_last_name'],
            'display_name'  => $user_data['user_full_name'],
            'role'          => 'doctor'
        );
    
        $user = wp_insert_user( $user_input );
    
    
    
        return $user;
    }
    
    /**
     * @param $string
     * @return mixed
     */
    protected static function d2g_clean_name($string) {
        $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = strtolower($string);

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

    

    /**
     * @param array $doc_data
     * @param array $doc_data_meta
     * @param string $action
     * @param string $source
     * @return int
     * this function crates a doc profile and is called via the WCC api
     */
    public static function d2g_create_doc_profile($doc_data = array(), $doc_data_meta = array(), $user_id) {

        $doc_id         = '';
        $error_check    = false;

        if($error_check != true){
            /********************db insert***************************/
            $post = array(
                'post_content'      => '',
                'post_status'       => 'draft',
                'post_title'        =>  $doc_data['user_title'].' '.$doc_data['user_first_name'].' '.$doc_data['user_last_name'],
                'post_type'         => 'd2g_doctor',
                'post_author'       => $user_id
            );
            //my_dump($post);
            $doc_id = (int)wp_insert_post( $post );

            if(count($doc_data_meta) > 0){
                foreach($doc_data_meta as $meta_key => $meta_value) {
                    add_post_meta($doc_id, $meta_key, $meta_value, true);
                }
            }

            
            $new_url        = self::d2g_page_url();
			update_post_meta($doc_id, 'd2g_edit_url', '<a target="_blank" href="'.$new_url.'?edit='.$doc_id.'">edit</a>');
            
            do_action('breeze_clear_all_cache');

        }

        return $doc_id;
    }

    /**
     * this sets the meta fields for the doctor profile, values received from WCC
     * @param $user
     * @param $user_data
     * @return array
     */
    public static function d2g_set_doc_meta($user_id, $user_data){
        $doc_meta = array(
            'd2g_user_id'               => (string)$user_id,
            'tariffs'                   => $user_data['tariffs'],
            'vat_rules'                 => $user_data['vat_rules'],
            'user_key'                  => $user_data['user_key'],
            'wcc_user_id'               => $user_data['user_id'],
            'organisation_key'          => $user_data['organisation_key'],
            'd2g_emp_title'             => $user_data['user_title'],
            'd2g_first_name'            => $user_data['user_first_name'],
            'd2g_last_name'             => $user_data['user_last_name'],
            'd2g_organisation'          => $user_data['organisation_name'],
            'd2g_main_email'            => $user_data['user_email'],
            'organisation_subdomain'    => $user_data['organisation_subdomain'],
            'locations_to_go'           => $user_data['locations']

        );
        return $doc_meta;
    }

    /**
     * update from a doctor profile post
     */
    public static function d2g_update_doc(){
		
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'doc-update' ) ) {
            return false;
        }
		
		
        global $success;

        $doc_data_meta = isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['meta'] ) ) : [];
        $doc_tax       = isset( $_POST['tax'] ) && is_array( $_POST['tax'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['tax'] ) ) : [];
        $update_id     = isset( $_POST['update_id'] ) ? absint( wp_unslash( $_POST['update_id'] ) ) : 0;
        $currLang      = isset( $_POST['d2g_lang'] ) ? sanitize_key( wp_unslash( $_POST['d2g_lang'] ) ) : '';


        /*************essential info*********/
        $my_update = array(
            'post_title'   => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
            'post_content' => isset( $_POST['docdesc'] ) ? wp_kses_post( wp_unslash( $_POST['docdesc'] ) ) : '',
            'post_status'  => isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'draft',
            'ID'           => $update_id,
        );

        /** update post */
        $insertID = wp_update_post($my_update);

		if(is_int($insertID)){
			/** update post meta */
			foreach ($doc_data_meta as $meta_key => $meta_value) {
				update_post_meta($update_id, $meta_key, $meta_value);
			}
	
            $new_url        = self::d2g_page_url($currLang);
			update_post_meta($update_id, 'd2g_edit_url', '<a target="_blank" href="'.$new_url.'?edit='.$update_id.'">edit</a>');
				
			/** update post taxonomies used for filtering */
			if(isset($_POST['tax'])){
				self::updateDocTerms($doc_tax, $update_id);
			}
	
			/** sets the profile image */
			if(isset($_FILES)){
				global $_FILES;
				
				self::set_doc_images($update_id);
			}
	
			$success = true;
            do_action('breeze_clear_all_cache');
			echo 'updated';
		} else {
            echo 'error';
			//var_dump($insertID);
		}
  
		wp_die();
    }

    /**
     * @param $taxonomies
     * @param $post_id
     */
    public static function updateDocTerms($taxonomies, $post_id){
        foreach ($taxonomies as $taxonomy => $value){
            wp_set_object_terms( $post_id, is_array($value)?$value:(array)$value, $taxonomy);
        }
    }


    /**
     * @param $user_id
     */
    public static function set_doc_images($post_ID) {
        global $_FILES; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce not required for this internal action.

        foreach ($_FILES as $field_name => $file_data) {// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce not required for this internal action.
    
            if(is_array($file_data['tmp_name'])){
                $count = count($file_data['tmp_name']);
                for ($x = 0; $x <= $count; $x++) {
                    if ($file_data['error'][$x] == 0 && $file_data['size'][$x] > 0) {
                        self::add_user_image($post_ID, $file_data['tmp_name'][$x], $file_data['name'][$x], $field_name);
                    }
                }
            } else {
                if ($file_data['error'] == 0 && $file_data['size'] > 0) {
                    self::add_user_image($post_ID, $file_data['tmp_name'], $file_data['name'], $field_name);
                }
            }
    
        }
    }
    

    /**
     * @param $post_id
     * @param $source_path
     * @param $original_name
     * @param string $is_featured
     * @return bool
     */
    protected static function add_user_image ($post_id, $source_path, $original_name, $field = '') {

        $original_name = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $original_name);
        $original_name = str_replace(' ', '-', $original_name);
        $original_name = strtolower($original_name);
        $filename = wp_upload_dir()['path'] . '/' . basename ($original_name);
    
        file_put_contents ($filename, file_get_contents ($source_path));

        if (file_exists($filename)) {
            //self::messages[] 	= date('d.m.Y H:i:s')." add image ".$http_filename." into ".$filename;
            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype( basename( $filename ), null );
    
            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();
    
            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename )),
                'post_content'   => '',
                'post_author'    => get_current_user_id(),
                'post_status'    => 'inherit'
            );
    
            // Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
    
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH . 'wp-admin/includes/image.php');
    
            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
    
            //set featured image
            if ($field == 'picture_1') {
                update_post_meta($post_id, '_thumbnail_id', $attach_id);
            }
    
            return true;
        } else {
            return false;
        }
    }


    public static function d2g_delete_profile_pic() {
        if (! isset( $_POST['_wpnonce'] ) ||! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'd2g_delete_pic' )) {
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
    public static function d2g_translate_post_meta( $value, $key, $lang ) {
        
        if ( 'd2g_edit_url' === $key ) {
            
            $new_url        = self::d2g_page_url($lang);
            $part2          = explode('?edit=', $value)[1];
            $value          = '<a target="_blank" href="'.$new_url.'?edit='.$part2;
        }
        return $value;
    }


    /*
    * redirect doctor when logging in 
    */
    public static function d2g_redirect_doctor(){
        
        $currUser       = wp_get_current_user();    
        
        if(in_array('doctor', $currUser->roles)){
            
            $new_url        = self::d2g_page_url();
            header("Location:".$new_url);
            exit;
        } else {
            return;
        }

    }

    //this needs to become optional
    public static function d2g_restict_admin() {
        
        $new_url        = self::d2g_page_url();
        
        if ( wp_doing_ajax() || current_user_can( 'delete_pages' ) || current_user_can( 'delete_others_posts' ) ) {
            return;
        } else {
            
            $currUser       = wp_get_current_user();    

            if(in_array('doctor', $currUser->roles)){
                if(get_option('d2g_admin_access') == 1){
                    return;
                }
                $new_url        = self::d2g_page_url();
            } else {
                $new_url        =  home_url();
            }
            header( 'Refresh: 2; ' . esc_url( $new_url ) );
            
            wp_die( 
                esc_html('You will now be redirected to your profile edit page', 'doctor2go-connect'), 
               
            );
        };
    }


    
}

D2G_doc_user_profile::init();