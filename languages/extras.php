<?php
global $wp_query;
if(is_tax()){
    my_dump($wp_query->query_vars);
}
if (!session_id()) session_start();
global $user; // Use global
$user = wp_get_current_user();
if(strpos(curPageURL(), 'login-press') !== false){
    $_SESSION['url'] = curPageURL();
    if(!is_user_logged_in()){
        wp_redirect('/login', 302);
        exit;
    }
}
if ((strpos(curPageURL(), 'login') === false && strpos(curPageURL(), 'wp-admin') === false && strpos($_SESSION['url'], 'login-press') === false)) {
    $_SESSION['url'] = '/';
}
if ( $user->roles[0] == 'subscriber' && is_admin() ) {
    wp_redirect($_SESSION['url']);
    exit;
}
session_write_close();
/**
 * Clean up the_excerpt()
 */
function roots_excerpt_more() {
    return ' &hellip; ';
}
add_filter('excerpt_more', 'roots_excerpt_more');

/**
 * Manage output of wp_title()
 */
function roots_wp_title($title) {
    if (is_feed()) {
        return $title;
    }

    $title .= get_bloginfo('name');

    return $title;
}
add_filter('wp_title', 'roots_wp_title', 10);


/*
 * this adds shortcode usage to contact form 7 email template*/
add_filter( 'wpcf7_special_mail_tags', 'your_special_mail_tag', 10, 3 );

function your_special_mail_tag( $output, $name, $html ) {
    if ( 'ap24_tax_list' == $name )
        $output = do_shortcode( '[ap24_tax_list]' );

    return $output;
}

function wptricks24_recaptcha_scripts() {
    wp_deregister_script( 'google_recaptcha_script' );

    $url = 'https://www.google.com/recaptcha/api.js';
    $url = add_query_arg( array(
        'onload' => 'contact_form_7_recaptcha_callback',
        'render' => 'explicit',
        'data-size' => 'compact',
        'hl' => explode('_', get_locale())[0]), $url ); // es is the language code for Spanish language

    wp_register_script( 'google_recaptcha_script', $url, array( 'jquery' ), '1.0.0', true );
}

add_action( 'wpcf7_enqueue_scripts', 'wptricks24_recaptcha_scripts', 11 );

/****fix for cf7*********/
add_filter( 'wpcf7_form_elements', 'remove_attr_size' );
function remove_attr_size( $content ) {
    if(strpos($content, 'hidden') !== false ){
        $content = preg_replace('/ size=".*?"/i', ' ', $content);
    }
    return $content;
}

function add_twitter_contactmethod( $contactmethods ) {
    unset($contactmethods['aim']);
    unset($contactmethods['jabber']);
    unset($contactmethods['yim']);
    return $contactmethods;
}
add_filter('user_contactmethods','add_twitter_contactmethod',10,1);

/*

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="billing_vat">VAT number</label></th>
            <td>
                <input type="text" name="vat" id="billing_vat" value="<?php echo esc_attr( get_the_author_meta( 'billing_vat', $user->ID ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
<?php }


add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
function my_save_extra_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;


    update_user_meta( $user_id, 'vat', $_POST['vat'] );
}
*/

function admin_del_color_options() {
    global $_wp_admin_css_colors;
    $_wp_admin_css_colors = 0;
}
add_action('admin_head', 'admin_del_color_options');


/**
 * Retrieve a post given its title.
 *
 * @uses $wpdb
 *
 * @param string $post_title Page title
 * @param string $output Optional. Output type. OBJECT, ARRAY_N, or ARRAY_A.
 * @return mixed
 */
function get_post_by_title($page_title, $output = OBJECT) {
    global $wpdb;
    $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='models'", $page_title ));
    if ( $post )
        return get_post($post, $output);
    return null;
}


add_filter( 'intermediate_image_sizes_advanced', 'prefix_remove_default_images' );
// Remove default image sizes here.
function prefix_remove_default_images( $sizes ) {
    unset( $sizes['small']); // 150px
    unset( $sizes['medium']); // 300px
    unset( $sizes['large']); // 1024px
    unset( $sizes['medium_large']); // 768px
    return $sizes;
}

/**
 * custom form elements
 */
add_filter( 'wpcf7_form_elements', 'mycustom_wpcf7_form_elements' );
function mycustom_wpcf7_form_elements( $form ) {
    $form = do_shortcode( $form );

    return $form;
}


function ap24_get_search_form( $echo = true ) {
    /**
     * Fires before the search form is retrieved, at the start of get_search_form().
     *
     * @since 2.7.0 as 'get_search_form' action.
     * @since 3.6.0
     *
     * @link https://core.trac.wordpress.org/ticket/19321
     */
    do_action( 'pre_get_search_form' );

    $format = current_theme_supports( 'html5', 'search-form' ) ? 'html5' : 'xhtml';



    /**
     * Filter the HTML format of the search form.
     *
     * @since 3.6.0
     *
     * @param string $format The type of markup to use in the search form.
     *                       Accepts 'html5', 'xhtml'.
     */
    $format = apply_filters( 'search_form_format', $format );

    $search_form_template = locate_template( 'searchform.php' );



    if ( '' != $search_form_template ) {
        ob_start();

        require( $search_form_template );
        $form = ob_get_clean();
    } else {

        if ( 'html5' == $format ) {
            $form = '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
				<label>
					<span class="screen-reader-text">' . _x( 'Search for:', 'label' ) . '</span>
					<input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label' ) . '" />
				</label>
				<input type="submit" class="search-submit" value="'. esc_attr_x( 'Search', 'submit button' ) .'" />
			</form>';
        } else {
            $form = '<form role="search" method="get" id="searchform" class="searchform" action="' . esc_url( home_url( '/' ) ) . '">
				<div>
					<label class="screen-reader-text" for="s">' . _x( 'Search for:', 'label' ) . '</label>
					<input type="text" value="' . get_search_query() . '" name="s" id="s" />
					<input type="submit" id="searchsubmit" value="'. esc_attr_x( 'Search', 'submit button' ) .'" />
				</div>
			</form>';
        }
    }

    /**
     * Filter the HTML output of the search form.
     *
     * @since 2.7.0
     *
     * @param string $form The search form HTML output.
     */

    //echo $form;
    //$result = apply_filters( 'get_search_form', $form );


    if ( null === $result )
        $result = $form;

    if ( $echo )
        echo $result;
    else
        return $result;
}


/**
 * Change Posts Per Page for custom post types
 *
 * @param object $query data
 *
 */
add_action( 'pre_get_posts', 'ap24_change_posts_per_page' );
function ap24_change_posts_per_page( $query ) {

    $active_post_types = get_post_types();
    foreach($active_post_types as $active_post_type){

        if(get_theme_mod('ap24_'.$active_post_type.'_items') != ""){
            if( $query->is_main_query() && !is_admin() && is_post_type_archive( $active_post_type ) ) {
                $query->set( 'posts_per_page', get_theme_mod('ap24_'.$active_post_type.'_items'));
            }
        }
    }
}

/***array sorting with objects*****/
function cmp($a, $b)
{
    return strcmp($a->name, $b->name);
}




add_filter( 'term_link', 'rudr_term_permalink', 10, 3 );
function rudr_term_permalink( $url, $term, $taxonomy ){

    $taxonomy_name = 'services'; // your taxonomy name here
    $taxonomy_slug = 'services'; // the taxonomy slug can be different with the taxonomy name (like 'post_tag' and 'tag' )

    // exit the function if taxonomy slug is not in URL
    if ( strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name ) return $url;

    $url = str_replace('/' . $taxonomy_slug, '', $url);

    return $url;
}


function ap24_get_search_form_sc( $echo = true ) {
    /**
     * Fires before the search form is retrieved, at the start of get_search_form().
     *
     * @since 2.7.0 as 'get_search_form' action.
     * @since 3.6.0
     *
     * @link https://core.trac.wordpress.org/ticket/19321
     */
    do_action( 'pre_get_search_form' );

    $format = current_theme_supports( 'html5', 'search-form' ) ? 'html5' : 'xhtml';



    /**
     * Filter the HTML format of the search form.
     *
     * @since 3.6.0
     *
     * @param string $format The type of markup to use in the search form.
     *                       Accepts 'html5', 'xhtml'.
     */
    $format = apply_filters( 'search_form_format', $format );

    $search_form_template = locate_template( 'searchform.php' );



    if ( '' != $search_form_template ) {
        ob_start();

        require( $search_form_template );
        $form = ob_get_clean();
    } else {

        if ( 'html5' == $format ) {
            $form = '
				<label>
					<span class="screen-reader-text">' . _x( 'Search for:', 'label' ) . '</span>
					<input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label' ) . '" />
				</label>
				<input type="submit" class="search-submit" value="'. esc_attr_x( 'Search', 'submit button' ) .'" />
			';
        } else {
            $form = '
				<div>
					<label class="screen-reader-text" for="s">' . _x( 'Search for:', 'label' ) . '</label>
					<input type="text" value="' . get_search_query() . '" name="s" id="s" />
					<input type="submit" id="searchsubmit" value="'. esc_attr_x( 'Search', 'submit button' ) .'" />
				</div>
			';
        }
    }

    /**
     * Filter the HTML output of the search form.
     *
     * @since 2.7.0
     *
     * @param string $form The search form HTML output.
     */

    //echo $form;
    //$result = apply_filters( 'get_search_form', $form );


    if ( null === $result )
        $result = $form;

    if ( $echo )
        echo $result;
    else
        return $result;
}
add_shortcode('ap24_get_search_form', 'ap24_get_search_form_sc');

//RTP events

function event_list_incl_filter($atts){
    $a = shortcode_atts(array(
        'default_city'      => 'Berlin'
    ), $atts);

    if(!is_admin()){
        ob_start();


        ?>
        <div id="event_main_wrapper">
            <div id="sub_menu_wrapper" class="fixed_sub_nav">
                <div id="event_filter_wrapper" class="container">
                    <?= create_filters($a['default_city'])?>
                </div>
            </div>
            <div id="sub_menu_anchor"></div>
            <div id="event_list_wrapper_outer">
                <div id="event_list_wrapper">
                    <?= create_event_list($a['default_city'])?>
                </div>
            </div>
        </div>

        <?php
        /* Get the buffered content into a var */
        $sc = ob_get_contents();

        /* Clean buffer */
        ob_end_clean();

        /* Return the content as usual */
        return $sc;
        /* Restore original Post Data */
    }



}
add_shortcode('event_list_incl_filter', 'event_list_incl_filter');

function create_filters($filterCity){
    //cities
    $argsCity = array (
        'taxonomy' => 'event-location', //empty string(''), false, 0 don't work, and return empty array
        'parent' => 0, //can be 0, '0', '' too
    );
    $cities = get_terms($argsCity);

    //venues
    $argsVenue = array (
        'taxonomy' => 'event-location', //empty string(''), false, 0 don't work, and return empty array
        'parent' => (int)$filterCity, //can be 0, '0', '' too
    );
    $venues = get_terms($argsVenue);

    //genres
    $argsGenre = array (
        'taxonomy' => 'event-genres', //empty string(''), false, 0 don't work, and return empty array
    );
    $genres = get_terms($argsGenre);

    //categories
    $argsCategory = array (
        'taxonomy' => 'event-category', //empty string(''), false, 0 don't work, and return empty array
    );
    $categories = get_terms($argsCategory);
    /*
        my_dump($cities);
        my_dump($venues);
        my_dump($genres);
        my_dump($categories);
    */
    ob_start();


    ?>

    <h3 class="opener special">Filters <span class="icon-angle-down"></span></h3>
    <div class="event_filters_outer <?= (wpmd_is_phone())?'simple_hide':''?>">
        <ul id="event_filters">
            <li class="date_filter_wrapper">
                <span class="cal alignleft"></span><input class="event_filter  alignleft" id="event_date" type="text" placeholder="all future dates"> <a class="clear_field  alignleft" href="#">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="clearfix"></div>
            </li>
            <li class="filter_wrap">
                <select name="city" id="city_filter" class="event_filter_special">
                    <option value="0"><?php _e('all cities', 'rtp')?></option>
                    <?php foreach($cities as $city){?>
                        <option <?= ((int)$filterCity == $city->term_id)?'selected="selected"':''?> value="<?= $city->term_id?>"><?= $city->name?></option>
                    <?php } ?>
                </select>
            </li>
            <li class="filter_wrap">
                <select name="venue" id="venue_filter" class="event_filter">

                    <option value="0"><?= $filterCity?__('all venues', 'rtp'):__('first choose a city', 'rtp')?></option>
                    <?php foreach($venues as $venue){?>
                        <option value="<?= $venue->term_id?>"><?= $venue->name?></option>
                    <?php } ?>
                </select>
            </li>
            <li class="filter_wrap">
                <select name="category" id="category_filter" class="event_filter">
                    <label><?php _e('category', 'rtp')?></label><br>
                    <option value="0"><?php _e('all categories', 'rtp')?></option>
                    <?php foreach($categories as $category){?>
                        <option value="<?= $category->term_id?>"><?= $category->name?></option>
                    <?php } ?>
                </select>
            </li>
            <li class="filter_wrap">
                <select name="genre" id="genre_filter" class="event_filter">
                    <label><?php _e('genre', 'rtp')?></label><br>
                    <option value="0"><?php _e('all genres', 'rtp')?></option>
                    <?php foreach($genres as $genre){?>
                        <option value="<?= $genre->term_id?>"><?= $genre->name?></option>
                    <?php } ?>
                </select>
            </li>
        </ul>
        <?php if(wpmd_is_phone()){?>
            <a href="#" class="rtp_btn btn btn-default" id="submit_event_search"><?= _e('submit', 'rtp')?></a>
        <?php }?>
    </div>


    <?php


    /* Get the buffered content into a var */
    $sc = ob_get_contents();

    /* Clean buffer */
    ob_end_clean();

    /* Return the content as usual */
    return $sc;
    /* Restore original Post Data */
}


function create_event_list($filterCity){
    $args = array(
        'post_type'         => 'events',
        'posts_per_page'    => -1,
        'orderby'           => 'meta_value',
        'order'             => 'DESC',
        'meta_key'          => 'event_start_date',
        //'meta_value'        => date( "Y-m-d" ), // change to how "event date" is stored
        //'meta_compare'      => '>',


    );

    if($filterCity != '' && $filterCity != '0'){
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event-location',
                'field'    => 'term_id',
                'terms'    => array( (int)$filterCity ),
            ),
        );
    }

    $the_query = new WP_Query( $args );
    ob_start();

    if ( $the_query->have_posts() ) {
        ?>
        <div class="row blog super_grid">
            <?php
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                include(ap24_locate_template("templates/content-events-grid1.php"));
            }
            ?>
        </div>

        <?php  add_action('wp_footer', function() use ($filterCity) { ?>
            <script>
                jQuery(document).ready(function(){

                    $( "#event_date" ).datepicker({
                        dateFormat: 'yy-mm-dd'
                    });

                    $( "#event_date" ).click(function () {
                        $(this).parent().addClass('active');
                    });

                    localStorage.setItem('prevCity', <?= (int)$filterCity?>);

                    <?php if(wpmd_is_notphone()){?>
                        $('.clear_field').click(function () {
                            $(this).parent().removeClass('active');
                            var valCheck = $(this).parent().find('input').val();
                            if(valCheck != ''){
                                $(this).parent().find('input').val('');
                                $('#event_filter_wrapper').css('opacity', '0.5');
                                var prevCity = localStorage.getItem('prevCity');

                                $('#event_list_wrapper').fadeOut();
                                $('#event_list_wrapper_outer').addClass('loading_events');

                                var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                                var data = {
                                    'action'                    : 'event_call',
                                    'filterCity'                : $('#city_filter').val(),
                                    'filterVenue'               : $('#venue_filter').val(),
                                    'filterGenre'               : $('#genre_filter').val(),
                                    'filterCategory'            : $('#category_filter').val(),
                                    'filterDate'                : $('#event_date').val()


                                };
                                //console.log(data);
                                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                                jQuery.post(ajax_url, data, function(response) {
                                    $('#event_filter_wrapper').css('opacity', '1');
                                    console.log('res:' + response);
                                    if(response == 0){
                                        response = '<h2><?php _e('We are sorry, but we could not find any events for your search criteria, please refine your search.')?></h2>'
                                    }
                                    $('#event_list_wrapper_outer').removeClass('loading_events');
                                    $('#event_list_wrapper').html(response).fadeIn();
                                });
                            }

                        });

                        $('.event_filter').on('change', function(){
                            $('#event_filter_wrapper').css('opacity', '0.5');
                            var prevCity = localStorage.getItem('prevCity');

                            $('#event_list_wrapper').fadeOut();
                            $('#event_list_wrapper_outer').addClass('loading_events');

                            var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                            var data = {
                                'action'                    : 'event_call',
                                'filterCity'                : $('#city_filter').val(),
                                'filterVenue'               : $('#venue_filter').val(),
                                'filterGenre'               : $('#genre_filter').val(),
                                'filterCategory'            : $('#category_filter').val(),
                                'filterDate'                : $('#event_date').val()


                            };
                            //console.log(data);
                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_url, data, function(response) {
                                $('#event_filter_wrapper').css('opacity', '1');
                                console.log('res:' + response);
                                if(response == 0){
                                    response = '<h2><?php _e('We are sorry, but we could not find any events for your search criteria, please refine your search.')?></h2>'
                                }
                                $('#event_list_wrapper_outer').removeClass('loading_events');
                                $('#event_list_wrapper').html(response).fadeIn();
                            });
                        });

                        $('#city_filter').on('change', function(){
                            $('#event_filter_wrapper').css('opacity', '0.5');

                            var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                            var data = {
                                'action'                    : 'load_venues',
                                'filterCity'                : $('#city_filter').val()


                            };
                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_url, data, function(response) {
                                //console.log(response);
                                $('#event_filter_wrapper').css('opacity', '1');
                                $('#venue_filter').html(response);
                                $('#venue_filter').dropdown('destroy');
                                $('#venue_filter').dropdown();
                            });


                            $('#event_list_wrapper').fadeOut();
                            $('#event_list_wrapper_outer').addClass('loading_events');

                            var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                            var data = {
                                'action'                    : 'event_call',
                                'filterCity'                : $('#city_filter').val(),
                                'filterVenue'               : '0',
                                'filterGenre'               : $('#genre_filter').val(),
                                'filterCategory'            : $('#category_filter').val(),
                                'filterDate'                : $('#event_date').val()


                            };
                            //console.log(data);
                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_url, data, function(response) {
                                if(response == 0){
                                    response = '<h2><?php _e('We are sorry, but we could not find any events for your search criteria, please refine your search.')?></h2>'
                                }
                                $('#event_list_wrapper_outer').removeClass('loading_events');
                                $('#event_list_wrapper').html(response).fadeIn();
                            });
                        });
                    <?php } else { ?>
                        $('.clear_field').click(function () {
                            $(this).parent().removeClass('active');
                            $(this).parent().find('input').val('');
                        });
                        $('#city_filter').on('change', function(){
                            $('#event_filter_wrapper').css('opacity', '0.5');
                            var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                            var data = {
                                'action'                    : 'load_venues',
                                'filterCity'                : $('#city_filter').val()


                            };
                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_url, data, function(response) {
                                //console.log(response);
                                $('#event_filter_wrapper').css('opacity', '1');
                                $('#venue_filter').html(response);
                                $('#venue_filter').dropdown('destroy');
                                $('#venue_filter').dropdown();
                            });

                        });
                        $('#submit_event_search').on('click', function(){
                            $('#menu_closer').css('display', 'none').css('background', 'transparent');
                            $('.opener').find('span').toggleClass('icon-angle-up');
                            $('.opener').find('span').toggleClass('icon-angle-down');
                            $('body').toggleClass('search_open');
                            $('.event_filters_outer').slideToggle();
                            $('#event_filter_wrapper').css('opacity', '0.5');
                            $('#event_list_wrapper').fadeOut();
                            $('#event_list_wrapper_outer').addClass('loading_events');

                            var ajax_url = '<?= admin_url('admin-ajax.php'); ?>';
                            var data = {
                                'action'                    : 'event_call',
                                'filterCity'                : $('#city_filter').val(),
                                'filterVenue'               : $('#venue_filter').val(),
                                'filterGenre'               : $('#genre_filter').val(),
                                'filterCategory'            : $('#category_filter').val(),
                                'filterDate'                : $('#event_date').val()


                            };
                            //console.log(data);
                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_url, data, function(response) {
                                $('#event_filter_wrapper').css('opacity', '1');
                                console.log('res:' + response);
                                if(response == 0){
                                    response = '<h2><?php _e('We are sorry, but we could not find any events for your search criteria, please refine your search.')?></h2>'
                                }
                                $('#event_list_wrapper_outer').removeClass('loading_events');
                                $('#event_list_wrapper').html(response).fadeIn();

                            });

                            return false;
                        });

                    <?php } ?>



                });
            </script>
        <?php });
    } else {
        // no posts found
    }
    /* Restore original Post Data */
    wp_reset_postdata();

    ?>

    <?php
    /* Get the buffered content into a var */
    $sc = ob_get_contents();

    /* Clean buffer */
    ob_end_clean();

    /* Return the content as usual */
    return $sc;
    /* Restore original Post Data */
}


add_action( 'wp_ajax_nopriv_event_call', 'event_call' );
add_action( 'wp_ajax_event_call', 'event_call' );
function event_call(){
    $args = array(
        'post_type'         => 'events',
        'posts_per_page'    => -1,
        'orderby'           => 'meta_value',
        'order'             => 'DESC',
        'meta_key'          => 'event_start_date',
        //'meta_value'        => date( "Y-m-d" ), // change to how "event date" is stored
        //'meta_compare'      => '>',


    );

    //my_dump($_POST);

    $filterCity         = $_POST['filterCity'];
    $filterVenue        = $_POST['filterVenue'];
    $filterGenre        = $_POST['filterGenre'];
    $filterCategory     = $_POST['filterCategory'];
    $filterDate         = $_POST['filterDate'];

    if($filterDate != ''){
        $args['meta_value']         = $filterDate;
        $args['meta_compare']       = '=';
    }


    $checker = 0;

    if($filterCity != '' && $filterCity != '0'){
        $args['tax_query'][] = array(
            'taxonomy' => 'event-location',
            'field'    => 'term_id',
            'terms'    => array( (int)$filterCity ),
        );
        $checker ++;
    }

    if($filterVenue != '' && $filterVenue != '0'){
        $args['tax_query'][] = array(
            'taxonomy' => 'event-location',
            'field'    => 'term_id',
            'terms'    => array( (int)$filterVenue ),
        );
        $checker ++;
    }

    if($filterCategory != '' && $filterCategory != '0'){
        $args['tax_query'][] = array(
            'taxonomy' => 'event-category',
            'field'    => 'term_id',
            'terms'    => array( (int)$filterCategory ),
        );
        $checker ++;
    }

    if($filterGenre != '' && $filterGenre != '0'){
        $args['tax_query'][] = array(
            'taxonomy' => 'event-genres',
            'field'    => 'term_id',
            'terms'    => array( (int)$filterGenre ),
        );
        $checker ++;
    }

    if($checker > 1){
        $args['tax_query']['relation'] = 'AND';
    }



    //my_dump($args);
    $the_query = new WP_Query( $args );


    if ( $the_query->have_posts() ) {
        ?>
        <div class="row blog super_grid">
            <?php
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                include(ap24_locate_template("templates/content-events-grid1.php"));
            }
            ?>
        </div>
        <script>
            jQuery(document).ready(function(){
                setTimeout(function(){
                    $('.super_grid').masonry();
                }, 500);

            });
        </script>
        <?php
    } else {
        // no posts found
    }
    /* Restore original Post Data */
    wp_reset_postdata();

    ?>

    <?php
    /* Restore original Post Data */
    wp_die();
}

add_action( 'wp_ajax_nopriv_load_venues', 'load_venues' );
add_action( 'wp_ajax_load_venues', 'load_venues' );
function load_venues(){

    $filterCity = $_POST['filterCity'];
    //venues
    $argsVenue = array (
        'taxonomy' => 'event-location', //empty string(''), false, 0 don't work, and return empty array
        'parent' => (int)$filterCity, //can be 0, '0', '' too
    );
    $venues = get_terms($argsVenue);

    ?>

    <option value="0"><?= $filterCity?__('all venues', 'rtp'):__('first choose a city', 'rtp')?></option>
    <?php if($filterCity != 0){ ?>
        <?php foreach($venues as $venue){?>
            <option value="<?= $venue->term_id?>"><?= $venue->name?></option>
        <?php } ?>
    <?php } ?>
    <?php

    wp_die();
}


add_action( 'wp_ajax_nopriv_do_search', 'do_search' );
add_action( 'wp_ajax_do_search', 'do_search' );
function do_search(){



    $args = array(
        'post_type'     => $_POST['search_filter'],
        's'             => $_POST['search_term']
    );

    if($_POST['search_filter'] == 'any'){
        $args['post_type'] = array('post', 'page', 'faq', 'events');
    }

    $the_query = new WP_Query( $args );
    //my_dump($the_query);

// The Loop
    if ( $the_query->have_posts() ) {
        echo '<ul>';
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            ?> <li><a href="<?php the_permalink()?>"><?= get_the_title()?></a></li><?php
        }
        echo '</ul>';
        /* Restore original Post Data */
        wp_reset_postdata();
    } else {
        echo '<div style="padding:5px; color:#000;">'.__('we are sorry we could not find any thing for: ', 'rtp').$_POST['search_term'].'</div>';
    }
    wp_die();
}


add_shortcode( 'rtp_languages_list', 'rtp_languages_list');
function rtp_languages_list(){
    $languages = apply_filters( 'wpml_active_languages', '', '' );
    if(!empty($languages)){
        ob_start();
        global $wpdb;
        $currBlogID = get_current_blog_id();
        if(get_option('ap24_https') == 1){
            $preUrl = 'https://';
        } else {
            $preUrl = 'http://';
        }
        $domains = $wpdb->get_results( "SELECT * FROM apwp_domain_mapping WHERE blog_id = ".$currBlogID." AND active = 1" );
        $primary_domain = $preUrl.$domains[0]->domain;
        $test_domain = 'https://rave-the-planet.ravetheplanet.net';

        echo '<div class="ap24_language_list"><ul>';
        foreach($languages as $l){
            $url = str_replace($test_domain, $primary_domain, $l['url']);
            $myClass = 'nonactive';
            if($l['active']){
                $myClass = 'active';
            }
            echo '<li class="'.$myClass.' '.$l['language_code'].'">';
            if($l['country_flag_url']){
                if(!$l['active']) echo '<a href="'.$url.'">';
                echo '&nbsp;';
                if(!$l['active']) echo '</a>';
            }
            echo '</li>';
        }
        echo '</ul></div>';
        /* Get the buffered content into a var */
        $sc = ob_get_contents();

        /* Clean buffer */
        ob_end_clean();

        /* Return the content as usual */
        return $sc;
        /* Restore original Post Data */
    }
}


add_shortcode( 'barometer', 'barometer');
function barometer(){

        $content = file_get_contents('https://shop.ravetheplanet.com/OnePager/barometer');
        $contentParts = explode('<body>', $content);
        $mainPart = str_replace('</html>', '', $contentParts[1]);
        $mainPart = str_replace('</body>', '', $mainPart);
        ob_start();

        echo $mainPart;

        $sc = ob_get_contents();
        ob_end_clean();
        return $sc;

}


add_action( 'admin_init', 'restrict_wpadmin_access' );
if ( ! function_exists( 'restrict_wpadmin_access' ) ) {
    function restrict_wpadmin_access() {
        if ( wp_doing_ajax() || current_user_can( 'delete_pages' ) || current_user_can( 'delete_others_posts' ) ) {
            return;
        } else {
            header( 'Refresh: 2; ' . esc_url( home_url() ) );
            $args = array(
                'back_link' => true,
            );
            wp_die( 'Restricted access.', 'Error', $args );
        };
    };
};

/**
 * Disable User Notification of Password Change Confirmation
 */
add_filter( 'send_password_change_email', '__return_false' );