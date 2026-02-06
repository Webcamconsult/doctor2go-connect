<?php

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

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
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/d2g-connect-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name.'-grid', plugin_dir_url(__FILE__) . 'css/d2g-grid.css', array(), $this->version, 'all');
        wp_enqueue_style( $this->plugin_name.'-fontello', plugin_dir_url( __FILE__ ) . 'fonts/fontello/css/fontello.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/d2g-connect-admin.js', array('jquery'), $this->version, false);
        
    }

    /**
     * Add menu options to the Wordpress admin
     */
    public function register_menu() {
        add_menu_page('Doctor2Go Connect', 'Doctor2Go Connect', 'manage_options', 'd2g-connect-admin-display', function() {
            require_once('partials/d2g-connect-admin-display.php');
        });
    
		add_submenu_page('d2g-connect-admin-display', 'D2GC shortcodes info', 'Shortcodes info', 'manage_options', 'd2g-connect-inline-widgets', function() {
            require_once('partials/d2g-connect-shortcode-help.php');
		});
    }

    /**
     * register the widget
     */
    public function register_widget() {
        //register_widget('D2gConnect_Widget');
        //this is for the connect version
    }

    public function get_widgets() {
        //this is for the connect version
    }

    public function set_client_id() {
       //this is for the connect version
    }

    /**
	 * Register the custom posttype for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function activate_myplugin_cpt() {

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
            'label'                 => esc_html__('Doctors', 'doctor2go-connect'),
            'public'                => true,
            'show_ui'               => true,
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_icon'             => 'dashicons-businessman',
            'rewrite'               => array(
                'slug'                  => 'doctor',
                'with_front'            => false
            ),
            'query_var'             => true,
            'supports'              => array(
                'title',
                'editor',
                'excerpt',
                'custom-fields',
                'revisions',
                'thumbnail',
                'author'
            )
        ); 
        register_post_type( 'd2g_doctor', $args );


        $args = array(
            'label'                 => esc_html__('D2GC E-mail', 'doctor2go-connect'),
            'public'                => true,
            'show_ui'               => true,
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_icon'             => 'dashicons-email',
            'rewrite'               => array(
                'slug'                  => 'email',
                'with_front'            => false
            ),
            'query_var'             => true,
            'supports'              => array(
                'title',
                'editor',
                'excerpt',
                'custom-fields',
                'revisions',
                'thumbnail',
                'author'
            )
        ); 
        register_post_type( 'd2g_emails', $args );
	}

    /*
    * adds custom taxonomies 
    */
    public function activate_myplugin_tax(){
        $taxonomies = $this->get_d2g_taxonomies();

        foreach($taxonomies as $taxonomy){
            $labels = $this->get_tax_label($taxonomy);
            $args = array(
                'hierarchical'          => true,
                'labels'                => $labels,
                'public'                => true,
                'show_ui'               => true,
                'show_in_rest'          => true,
                'show_admin_column'     => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
                'rewrite'               => array(
                    'slug' => $taxonomy,
                    'with_front' => false, // Don't display the category base before
                    'hierarchical' => true
                ),
            );

            register_taxonomy ( $taxonomy, array('d2g_doctor'), $args );

        }
    }

    /**
     * custom taxonimies for the website are declared here
     */
    public function get_d2g_taxonomies(){
        return array(
            //'country-operation',
            'country-origin',
            'doctor-language',
            //'disease',
            //'doctor-degree',
            'doctor-specialty',
            'body-part'
        );
    }

    private function get_tax_label($taxonomy){
        /***************doctor taxonomies***************/

        $labels['country-operation'] = array(
            'name'              => _x( 'Counytries of operations', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Counytry of operations', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Counytry of operations', 'doctor2go-connect'),
        );

        $labels['country-origin'] = array(
            'name'              => _x( 'Country of origin', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Country of origin', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Country of origin', 'doctor2go-connect'),
        );

        $labels['doctor-language'] = array(
            'name'              => _x( 'Languages', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Language', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Languages', 'doctor2go-connect'),
        );

        $labels['disease'] = array(
            'name'              => _x( 'Diseases', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Disease', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Diseases', 'doctor2go-connect'),
        );

        $labels['doctor-degree'] = array(
            'name'              => _x( 'Degrees', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Degree', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Degrees', 'doctor2go-connect'),
        );


        $labels['doctor-specialty'] = array(
            'name'              => _x( 'Specialties', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Specialty', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Specialties', 'doctor2go-connect'),
        );

        $labels['body-part'] = array(
            'name'              => _x( 'Body parts', 'taxonomy general name', 'doctor2go-connect'),
            'singular_name'     => _x( 'Body part', 'taxonomy singular name', 'doctor2go-connect'),
            'search_items'      => __( 'Search', 'doctor2go-connect'),
            'all_items'         => __( 'All', 'doctor2go-connect'),
            'parent_item'       => __( 'Parent', 'doctor2go-connect'),
            'parent_item_colon' => __( 'Parent:', 'doctor2go-connect'),
            'edit_item'         => __( 'Edit', 'doctor2go-connect'),
            'update_item'       => __( 'Update', 'doctor2go-connect'),
            'add_new_item'      => __( 'Add New', 'doctor2go-connect'),
            'new_item_name'     => __( 'New', 'doctor2go-connect'),
            'menu_name'         => __( 'Body parts', 'doctor2go-connect'),
        );

        return $labels[$taxonomy];
    }


    /*
    * here the custom image sizes are declared 
    */
    public function d2g_image_sizes(){
        add_image_size( 'd2g-doc-pic', 400, 500, true );
        add_image_size( 'd2g-doc-pic-square', 300, 300, true );
    }

    /*
    * admin columns doctors are created
    */
    public function d2g_filter_posts_columns($columns){
      
        $columns = array(
            'cb'                        => $columns['cb'],
            'id'                        => __( 'Profile ID', 'doctor2go-connect'),
            'd2g_user_id'               => __( 'WP user ID', 'doctor2go-connect'),   
            'image'                     => __( 'Image', 'doctor2go-connect'),
            'title'                     => __( 'Title', 'doctor2go-connect'),
            'd2g_first_name'            => __( 'Fist name', 'doctor2go-connect'),
            'd2g_last_name'             => __( 'Last name', 'doctor2go-connect'),
            'd2g_main_email'            => __( 'E-mail', 'doctor2go-connect'),
            'taxonomy-country-origin'   => __( 'Country', 'doctor2go-connect'  ),
            'taxonomy-doctor-language'  => __( 'Language', 'doctor2go-connect'  ),
            'taxonomy-doctor-specialty' => __( 'Specialisation', 'doctor2go-connect'  ),
            'organisation_subdomain'    => __( 'D2G domain', 'doctor2go-connect'),
            'organisation_key'          => __( 'D2G org. key', 'doctor2go-connect'),
            'wcc_user_id'               => __( 'D2G user ID', 'doctor2go-connect'),
            'user_key'                  => __( 'D2G user key', 'doctor2go-connect'),
            'author'                    => __( 'Author', 'doctor2go-connect'),
            'd2g_edit_url'              => __( 'Action', 'doctor2go-connect'),
          );

        return $columns;
    }

    
    

    /*
    * admin columns doctors values are retrived
    */
    public function d2g_doctor_column($column, $post_id){

        if ( 'id' === $column ) {
            echo esc_html($post_id);
        }

        if ( 'd2g_user_id' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_user_id', true ));
        }
        
        if ( 'image' === $column ) {
            echo get_the_post_thumbnail( $post_id, array(80, 80) );
        }

        if ( 'd2g_first_name' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_first_name', true ));
        }

        if ( 'd2g_last_name' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_last_name', true ));
        }

        if ( 'd2g_main_email' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_main_email', true ));
        }

        if ( 'organisation_key' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'organisation_key', true ));
        }

        if ( 'wcc_user_id' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'wcc_user_id', true ));
        }

        if ( 'user_key' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'user_key', true ));
        }

        if ( 'd2g_edit_url' === $column ) {
            echo wp_kses_post(get_post_meta( $post_id, 'd2g_edit_url', true ));
        }

        if ( 'organisation_subdomain' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'organisation_subdomain', true ));
        }
    }




    /*
    * what admin columns doctors are sortable
    */
    public function d2g_doctor_sortable_columns($columns){
        $columns['d2g_first_name'] = 'd2g_first_name';
        $columns['d2g_last_name'] = 'd2g_last_name';
        return $columns;
    }

    /*
    * create sort-query for sortabel custom columns
    */
    public function d2g_posts_orderby( $query ) {
        if( ! is_admin() || ! $query->is_main_query() ) {
          return;
        }
      
        if ( 'price_per_month' === $query->get( 'orderby') ) {
          $query->set( 'd2g_first_name', 'meta_value' );
          $query->set( 'meta_key', 'd2g_first_name' );
          
        }

        if ( 'd2g_last_name' === $query->get( 'orderby') ) {
            $query->set( 'd2g_last_name', 'meta_value' );
            $query->set( 'meta_key', 'd2g_last_name' );
            
          }
    }

    /*
    * admin columns page are created
    */
    public function d2g_filter_page_columns($columns){
      
        $columns = array(
            'cb'                        => $columns['cb'],
            'id'                        => __( 'Page ID', 'doctor2go-connect'),
            'title'                     => __( 'Title', 'doctor2go-connect'),
            'd2g_page_accessebility'    => __( 'Access', 'doctor2go-connect'),   
            'd2g_page_identifier'       => __( 'Identifier', 'doctor2go-connect'),
            'author'                    => __( 'Author', 'doctor2go-connect')
          );

          

        return $columns;
    }

    /*
    * admin columns pages values are retrived
    */
    public function d2g_page_column($column, $post_id){
        if ( 'id' === $column ) {
            echo esc_html($post_id);
        }
        if ( 'd2g_page_accessebility' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_page_accessebility', true ));
        }
        if ( 'd2g_page_identifier' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_page_identifier', true ));
        }  
    }

    /*
    * admin columns email are created
    */
    public function d2g_filter_email_columns($columns){
      
        $columns = array(
            'cb'                        => $columns['cb'],
            'title'                     => __( 'Title', 'doctor2go-connect'),
            'd2g_email_identifier'    => __( 'E-mail', 'doctor2go-connect'),   
            
          );


        return $columns;
    }


    /*
    * admin columns pages values are retrived
    */
    public function d2g_email_column($column, $post_id){
        if ( 'd2g_email_identifier' === $column ) {
            echo esc_html(get_post_meta( $post_id, 'd2g_email_identifier', true ));
        }
        
    }


    // extendes the user tabel
    public function d2g_modify_user_table( $column ) {
        $column['wcc_user_id'] = 'D2G user id';
        $column['user_key'] = 'D2G user key';
        $column['organisation_key'] = 'D2G org. key';
        return $column;
    }

    /*
    * what admin columns doctors are sortable
    */
    public function new_modify_user_table_row( $val, $column_name, $user_id ) {
        
       $user_meta   = get_user_meta($user_id); 
    
        switch ($column_name) {
            case 'wcc_user_id' :
                
                return $user_meta['wcc_user_id'][0];
            case 'user_key' :
                return $user_meta['user_key'][0];
            case 'organisation_key' :
                return $user_meta['organisation_key'][0];
            default:
        }
        return $val;
    }


    /*
    * register options for the D2G settings
    */
    public function register_d2g_settings() {
        $settings_with_callbacks = array(
            'd2g_overview_template'     => 'sanitize_text_field',
            'd2g_single_template'       => 'sanitize_text_field',
            'd2g_theme_css'             => 'sanitize_text_field', // maybe allow full CSS, no sanitization
            'd2g_css_grid'              => 'absint',
            'wcc_token'                 => 'sanitize_text_field',
            'api_url_short'             => 'esc_url_raw',
            'waiting_room_url'          => 'esc_url_raw',
            'wcc_base_url'              => 'sanitize_text_field',
            'admin_mail'                => 'sanitize_email',
            'd2g_local_user'            => 'absint',
            'd2g_placeholder'           => 'sanitize_text_field',
            'd2g_detail_page_view'      => 'sanitize_text_field',
            'd2g_privacy_url'           => 'esc_url_raw',
            'd2g_terms_url'             => 'esc_url_raw',
            'd2g_recaptcha_site_key'    => 'sanitize_text_field',
            'd2g_recaptcha_secret_key'  => 'sanitize_text_field',
            'd2g_admin_access'          => 'absint',
            'd2g_single_header_footer'  => 'absint',
            'deactivate_recapctha_script' => 'absint',
            'activate_2fa_link'         => 'absint',
            'under_construction'        => 'absint',
            'd2g_logo'                  => 'sanitize_text_field',
            'd2g_sender_address'        => 'sanitize_email',
            'd2g_recipient_address'     => 'sanitize_email',
            'd2g_sender_name'           => 'sanitize_text_field',
            'd2g_pseudo_translations'   => 'absint',
            'd2g_use_imgix'             => 'absint',
            'd2g_use_default_questionnaire'             => 'absint'
        );
        //registration for the short code activation options
        foreach ( $settings_with_callbacks as $setting => $callback ) {
            register_setting( 'd2g-option-group', $setting, array(
                'sanitize_callback' => $callback,
            ));
        }
    
    } 

    //function adds metaboxes with callback function where the html is defined
    public function d2g_meta_box_add(){
        require_once('partials/metaboxes.php');
        add_meta_box( 'personal-info', 'Personal information', 'd2g_meta_box_personal_cb', 'd2g_doctor', 'normal', 'high' );
        add_meta_box( 'education', 'Education', 'd2g_meta_box_education_cb', 'd2g_doctor', 'normal', 'high' );
        add_meta_box( 'work', 'Work experience', 'd2g_meta_box_work_cb', 'd2g_doctor', 'normal', 'high' );
        add_meta_box( 'publication', 'Publications', 'd2g_meta_box_publications_cb', 'd2g_doctor', 'normal', 'high' );
        add_meta_box( 'page_type', 'Page type', 'd2g_meta_box_page_type_cb', 'page', 'normal', 'high' );
        add_meta_box( 'email_type', 'E-mail', 'd2g_meta_box_email_type_cb', 'd2g_emails', 'normal', 'high' );
    }

    //function saves the metaboxes
    public function d2g_meta_box_save( $post_id ){
        
        // Bail if we're doing an auto save 
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        $included_cpts = array('d2g_doctor', 'page', 'd2g_emails');

        if (in_array(get_post_type($post_id), $included_cpts)  ) {

            $currLang           = get_locale();
            $d2gAdmin           = new D2G_doc_user_profile();
		    $new_url            = $d2gAdmin::d2g_page_url();

            update_post_meta($post_id, 'd2g_edit_url', '<a target="_blank" href="'.$new_url.'?edit='.$post_id.'">edit</a>');
        
            // if our nonce isn't there, or we can't verify it, bail 
            $nonce = isset( $_POST['meta_box_nonce'] )
                ? sanitize_text_field( wp_unslash( $_POST['meta_box_nonce'] ) )
                : '';

            if ( ! wp_verify_nonce( $nonce, 'd2g_meta_box_nonce' ) ) {
                return;
            }


            $personalFields = array(
                'd2g_first_name'            => esc_html__('First name', 'doctor2go-connect'),
                'd2g_last_name'             => esc_html__('Last name', 'doctor2go-connect'),
                'd2g_emp_title'             => esc_html__('Title', 'doctor2go-connect'),
                'd2g_address'               => esc_html__('Address', 'doctor2go-connect'),
                'd2g_zip'                   => esc_html__('Zip code', 'doctor2go-connect'),
                'd2g_city'                  => esc_html__('city', 'doctor2go-connect'),
                'tel'                       => esc_html__('Phone', 'doctor2go-connect'),
                'd2g_mobile'                => esc_html__('Mobile', 'doctor2go-connect'),
                'd2g_main_email'            => esc_html__('E-mail', 'doctor2go-connect'),
                'd2g_organisation'          => esc_html__('Organisation', 'doctor2go-connect'),
                'reg_nr'                    => esc_html__('Registration number', 'doctor2go-connect'),
                'reg_country'               => esc_html__('Country of registration', 'doctor2go-connect'),
                'avg_price'                 => esc_html__('Average price', 'doctor2go-connect')
        
            );

            // Update personal fields (fixes lines 632 & 653).
            foreach ( $personalFields as $key => $name ) {
                $value = d2g_get_post_text( $key );
                if ( ! empty( $value ) ) {
                    update_post_meta( $post_id, $key, $value );
                }
            }
            
            $postMetaArr = isset( $_POST['meta'] ) ? map_deep( wp_unslash( $_POST['meta'] ), 'sanitize_text_field' ) : array();

            foreach ( $postMetaArr as $meta_key => $meta_value ) {
                update_post_meta( $post_id, $meta_key, $meta_value );
            }

        }
        
		
    }

    
    public function d2g_hide_admin_bar(){
        /*@ Get current logged-in user data */
        $user = wp_get_current_user();
        /*@ Fetch only roles */
        $currentUserRoles = $user->roles;
        if(in_array('doctor', $currentUserRoles) || in_array('patient', $currentUserRoles) || in_array('subscriber', $currentUserRoles)){
            add_filter('show_admin_bar', '__return_false');
        }
    }

    

}
