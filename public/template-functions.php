<?php
/**
 * Template Functions.
 *
 * @package d2g-connect
 */
//fires hooks
//general hooks for D2G-Connect
add_action('init', 'start_session');
// Register AJAX handler for saving timezone
add_action('wp_ajax_save_user_timezone', 'save_user_timezone');
add_action('wp_ajax_nopriv_save_user_timezone', 'save_user_timezone');
add_action('wp_enqueue_scripts', 'enqueue_timezone_script');

add_action( 'after_setup_theme', 'd2g_load_single_d2g_doctor_hooks' );

add_filter( 'body_class', 'd2g_body_class', 100, 2 );

if(!is_admin() || wp_doing_ajax()){
    add_action( 'the_post', 'd2g_setup_profile_data' );
}

// Register AJAX handler for loading availibility data in query loop for mulitple doctors
add_action('wp_ajax_load_availability_data', 'load_availability_data');
add_action('wp_ajax_nopriv_load_availability_data', 'load_availability_data');

add_action('d2g_info_box', 'cb_d2g_info_box', 10, 3);

add_action('d2g_like_button', 'cb_d2g_like_button', 10, 1);

add_action('d2g_consult_buttons', 'show_consult_buttons', 10, 2);

add_action('d2g_availability_data_ajax', 'd2g_fetch_availability_data', 10, 2);

//hooks for single page
add_action( 'd2g_single_d2g_doctor_main_content', 'd2g_single_d2g_doctor_content' );

//this sets the path to the single template file for doctors
add_filter ('single_template', 'd2g_redirect_single_template');

/**
 * Load single dooctor hooks (to extend later with layout options)
 *
 * @since v1.0.0
 */
function d2g_load_single_d2g_doctor_hooks(){
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    if(get_option('d2g_detail_page_view') != 'single-v2' && !isset($_GET['view'])){
        add_action('d2g_single_sidebar', 'cb_d2g_single_sidebar', 10, 1);  
    } 
    add_action('d2g_doctor_locations', 'show_doctor_locations_by_id', 10, 1);
    add_action('d2g_doctor_extended_info', 'show_doctor_extended_info');
    add_action('d2g_booking_calendar', 'show_booking_calendar', 10, 1 );
    add_action('d2g_doctor_walkin_form', 'show_walkin_form');
    add_action('d2g_doctor_written_con_form', 'show_written_con_form');
    add_action('d2g_back_to_overview', 'show_back_btn');
}
/*
* sets the path to the corre3ct template file for the view from a single doctor
*/
function d2g_redirect_single_template ($template) {
    global $post;
  
    if ($post->post_type == 'd2g_doctor' && ('single.php' == basename ($template) || 'template-canvas.php' == basename ($template))){
        $template = WP_PLUGIN_DIR . '/d2g-connect/public/templates/single-d2g_doctor.php';
    }
 
    return $template;
}

/**
 * Hook call back for single doctor profile (d2g_doctor). This will be extended for a choice of other template parts.
 *
 * @return void
 */
function d2g_single_d2g_doctor_content(){
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    $view = isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : '';
    if ( get_option('d2g_detail_page_view') !== 'single-v2' && ! $view ) {
        include(d2g_locate_template("content-single-d2g_doctor.php"));
    } elseif (get_option('d2g_detail_page_view') == 'single-v2' || $view == 'v2') {
        include(d2g_locate_template("content-single-d2g_doctor-v2.php"));
    }
    
}


/**
 * When the_post is called, put profile data into a global.
 *
 * @param mixed $post Post object or post id.
 * @return global $d2g_profile_data
 */
function d2g_setup_profile_data( $post ) {
    global $wp_query;
    
    if($post->post_type == 'd2g_doctor'){
        $GLOBALS['d2g_profile_data'] = new D2G_ProfileData($post);
        return $GLOBALS['d2g_profile_data'];
    }
}


/*
*retrives the template file
*/
function d2g_locate_template($template_name, $folder = 'templates', $debugMode=false) {

    static $loadedFiles;

    if ( !$template_name ) return;
    $located 		= false;

    $templatePath	= dirname($template_name);
    $template_name	= basename($template_name);


    
    if (isset($loadedFilex[$template_name])) {
        $located = $loadedFiles[$template_name];
    }
    else {
        $arrPath = [
            get_stylesheet_directory()."/d2g/$templatePath/",
            get_template_directory()."/d2g/$templatePath/",
            plugin_dir_path(__FILE__).$folder."/$templatePath/",
        ];
        foreach ($arrPath as $sPath) {
            $sPath .= $template_name;
            if ($debugMode) echo "search for ".esc_html($sPath);
            if (file_exists("$sPath")) {
                $located = $sPath;
                if ($debugMode) echo ":found<br/>";
                break;
            }
            else {
                if ($debugMode) echo ":not found<br/>";
            }
        }
    }
    if ($located) {
        $loadedFiles[$template_name] = $located;
        return $located;
    }
    else {
        $loadedFiles[$template_name] = false;
    }
    return false;
}

/*
*article (post) css classmanipulation 
*/

function d2g_getArticleClass($class=null, $post_id=null) {
    return join(' ',get_post_class($class, $post_id));
}


/*
*creates custom excerpt with possibility to show full content with HTML
*/
function d2g_ttruncat($text,$numb) {

    if($numb != 'full'){
        $text = wp_strip_all_tags($text);
        $text = preg_replace( "/\r|\n/", "&nbsp;", $text );
        if (strlen($text) > $numb) {
            $text = substr($text, 0, $numb);
            $text = substr($text,0,strrpos($text," "));
            $etc = " ...";
            $text = $text.$etc;
        }
    } else {
        $text = apply_filters('the_content', $text);
    }
    return $text;
}

/**
 * Filter Body Class.
 *
 * @param  array  $classes [description].
 * @param  String $class   [description].
 * @return array
 */
function d2g_body_class( $classes, $class ) {
    global $post;
    $currMeta = get_post_meta( $post->ID );
    if( is_singular( 'd2g_doctor' ) ) {
        $classes[]      = 'd2g-single-doctor';
        if(get_option('d2g_detail_page_view') != 'single-v2'){
            $classes[]      = 'sidebar-menu';
        } else {
            $classes[]      = 'full-width';
        }
    } else {
        $dashboardPages = array(
			'patient_dashboard',
			'appointments',
			'account_settings',
			'liked_doctors',
			'questionnaires',
			'secure_patient_portal'
		);
        if(in_array($currMeta['d2g_page_identifier'][0], $dashboardPages)){
            $classes[]      = 'dashboard_pages';
        }
        if($currMeta['d2g_page_identifier'][0] == 'doctors'){
            $classes[]      = 'd2g-doctor-overview';
        }
    }
    

	
	// Give me my new, modified $classes.
	return $classes;
}

function nice_dump($dump){
    echo '<pre>';
    var_dump($dump); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
    echo '</pre>';
}

/*
* return the URL from the current page / post
* this function will sonn be removed as WP functions or other methods will be used, thats why all pcp warnings are surpressed
*/
// phpcs:disable WordPress.Security.ValidatedSanitizedInput
function d2g_curPageURL($returnRequestURI = true) {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
    $pageURL .= "://";
    $requestURI = $_SERVER["REQUEST_URI"];
    if (!$returnRequestURI) {
        $splitURI   = explode("?", $requestURI);
        $requestURI = $splitURI[0];
    }
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $requestURI;
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $requestURI;
    }
    return $pageURL;
}
// phpcs:enable WordPress.Security.ValidatedSanitizedInput


function d2g_fetch_availability_data($post_id, $template = ''){ ?>
    <script>
        jQuery(document).ready(function ($) {

            var wrapper     = '#icon_list_<?php echo esc_js($post_id)?>';
            var postID      = '<?php echo esc_js($post_id)?>';
            var colClass    = '';

            <?php if($template == 'list'){ ?>
                colClass = 'col-sm-6';
            <?php }?>

            /*
            $('#bg_loader').toggleClass('simple_hide');
            $('#loader').toggleClass('simple_hide');
            */
            var ajax_url        = ' <?php echo  esc_js(admin_url('admin-ajax.php')); ?>';
            var data = {
                'action'                    : 'load_availability_data',
                'doc_id'                    : postID
            };
            
            $.post(ajax_url, data, function(response) {
                console.log(response);
                if(response.data.walkin_check == true){
                    var walkin = '<span class="walkin '+ colClass +'"><?php echo esc_html__('walk-in consult', 'doctor2go-connect')?></span>';
                    $('#doc_<?php echo esc_js($post_id)?>').append(walkin);
                    $('.post-<?php echo esc_js($post_id)?>').find('.walk_in_button').removeClass('simple_hide');    
                }

                if(response.data.tariffs != ''){
                    let str = response.data.tariffs
                    let newStr = str;
                    $('.fillup_<?php echo esc_js($post_id)?>').html(newStr);
                    
                    var tariffs = '<li class="icon-cc-mastercard '+ colClass +'">'+ response.data.tariffs +'</li>'
                    $('#icon_list_<?php echo esc_js($post_id)?>').append(tariffs);
                    $('.post-<?php echo esc_js($post_id)?>').find('.booking_con').css('display', 'list-item');
                    
                } else {
                    $('.fillup_<?php echo esc_js($post_id)?>').html('<?php echo esc_html__('not available', 'doctor2go-connect')?>');
                }

                if(response.data.first_availibility != ''){
                    var first_availability = '<li class="icon-clock '+ colClass +'">'+ response.data.first_availibility +'</li>'
                    $('#icon_list_<?php echo esc_js($post_id)?>').append(first_availability);
                }

                //show more info
                $(wrapper).find('.icon-info').bind('click', function(){
                    $(this).next().toggleClass('simple_hide');
                    
                });
            });
            

        });
    </script>
<?php }

//like button
function cb_d2g_like_button($post_id){ ?>
    <?php 
        $liked_posts = get_liked_posts();
        $is_liked = in_array($post_id, $liked_posts);
    ?>
    <button class="like-button <?php echo $is_liked ? 'icon-heart-filled' : 'icon-heart'; ?>" data-post-id="<?php echo esc_html($post_id); ?>">
        <span class="text simple_hide"><?php echo $is_liked ? 'Unlike' : 'Like'; ?></span>
    </button>
<?php }


/*
*displays the info box from a doctor profile
*/
function cb_d2g_info_box($temp_file, $version, $post = ''){
    if($post == ''){
        global $d2g_profile_data;
    } else {
        $d2g_profile_data   = new D2G_ProfileData($post, true);
    }
    
    $rowClass   = '';
    $liClass    = '';
    if($temp_file == 'detail' || $version == 'col-2'){
        $rowClass   = 'row';
        $liClass    = 'col-sm-6';
    }
    
    ?>
    
    <ul class="icon_list specs <?php echo esc_html($rowClass)?>" id="icon_list_<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>">
        <?php if ($d2g_profile_data->doctor_meta['d2g_zip'][0].$d2g_profile_data->doctor_meta['d2g_city'][0] != "") { ?>
            <li class="icon-home <?php echo esc_html($liClass)?>">
                <span>
                    <?php echo esc_html($d2g_profile_data->doctor_meta['d2g_zip'][0]) ?> <?php echo esc_html($d2g_profile_data->doctor_meta['d2g_city'][0]) ?> -
                    <?php if($d2g_profile_data->countries !== false){ ?>
                        <?php foreach ($d2g_profile_data->countries as $country){ ?>
                            <?php echo esc_html($country->name)?>
                        <?php } ?>
                    <?php } ?>
                </span>
            </li>
        <?php } ?>
        <?php if ($d2g_profile_data->languages !== false) { ?>
            <li class="icon-globe <?php echo esc_html($liClass)?>">
                <?php foreach ($d2g_profile_data->languages as $language){ ?>
                    <span><?php echo esc_html($language->name)?></span>
                <?php } ?>
            </li>
        <?php } ?>
        <?php if ($d2g_profile_data->doctor_meta['reg_nr'][0] != "" && is_single()) { ?>
            <li class="icon-sort-numeric-outline <?php echo esc_html($liClass)?>">
                <?php echo esc_html__('Reg. Nr.', 'doctor2go-connect')?> <?php echo esc_html($d2g_profile_data->doctor_meta['reg_nr'][0]) ?>
            </li>
        <?php } ?>
        <?php if ($d2g_profile_data->doctor_meta['reg_country'][0] != "" && is_single()) { ?>
            <li class="icon-doc <?php echo esc_html($liClass)?>">
                <?php echo esc_html__('Reg. country', 'doctor2go-connect')?>: <?php echo esc_html($d2g_profile_data->doctor_meta['reg_country'][0]) ?>
            </li>
        <?php } ?>
        <?php if ($d2g_profile_data->doctor_meta['avg_price'][0]) { ?>
            <li class="icon-cc-mastercard <?php echo esc_html($liClass)?>">
                <?php echo esc_html__('Average price:', 'doctor2go-connect')?> <?php echo esc_html($d2g_profile_data->doctor_meta['avg_price'][0])?>
            </li>
        <?php } ?>
        <?php if ($d2g_profile_data->firstAvailibility != "") { ?>
            <li class="icon-clock <?php echo esc_html($liClass)?>"><?php echo ($d2g_profile_data->firstAvailibility != '01/01/1970 @ 01:00')?esc_html($d2g_profile_data->firstAvailibility).'<p class="small">'.esc_html__('next best availibilty', 'doctor2go-connect'):esc_html__('no availibilities', 'doctor2go-connect') ?> </p></li>
        <?php } ?>
        <?php if(is_array($d2g_profile_data->locations)) {?>
            <?php if(count($d2g_profile_data->locations) > 0 && $temp_file != 'detail'){ ?>
                <li class="icon-shop <?php echo esc_html($liClass)?>">
                    <a href="#locations-<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>" class="fancybox"><?php echo esc_html('show all locations', 'doctor2go-connect')?></a>
                    <div id="locations-<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>" class="simple_hide locations_popup">
                        <h4><?php echo esc_html('All locations from: ', 'doctor2go-connect')?> <?php the_title()?></h4>
                        <ul>
                            <?php foreach($d2g_profile_data->locations as $location){ ?>
                                <li><?php echo esc_html($location['name'] .' ('.$location['city'] .' - '. $location['country'].')')?></li>
                            <?php } ?>
                        </ul>
                    </div>   
                </li>
            <?php } ?>
        <?php } ?>
        
    </ul>
<?php
    
}

/*
*
*/
function cb_d2g_single_sidebar($d2g_profile_data = ''){ 
    if($d2g_profile_data == ''){
        global $d2g_profile_data;
    }
    $post_id = get_the_ID();
    if(get_option('d2g_use_imgix') == 1){
        $doc_pic = $d2g_profile_data->feat_pic_full.'&w=300&h=300&fit=crop&crop=faces&auto=format,compress';
    } else {
        $doc_pic = $d2g_profile_data->feat_pic_square;
    }
    ?>
    <div class="col-sm-3 sidebar">
        <div class="sidebar_inner">
            
            <?php if($d2g_profile_data->walk_in_check == true){ ?>
                <span class="css_shape_doctor"></span>
            <?php } ?>
            <figure>
                <img style="width:100%" src="<?php echo  esc_html($doc_pic)?>" alt="<?php echo esc_html(get_the_title()) ?>">
                <?php do_action('d2g_like_button', $post_id);?>
            </figure>
            <?php do_action('d2g_consult_buttons', 'detail', 'small');?>
            <ul class="margin-bottom-standard anchor_links">
                <li class=" icon-right-open"><a class="scroll_to" href="#info"><?php echo esc_html__('Short info', 'doctor2go-connect')?></a></li>
                <li class=" icon-right-open"><a class="scroll_to" href="#bio"><?php echo esc_html__('About', 'doctor2go-connect')?></a></li>
                <?php if($d2g_profile_data->locations) { ?>
                    <li class=" icon-right-open"><a class="scroll_to" href="#location_wrapper"><?php echo esc_html__('Location(s)', 'doctor2go-connect')?></a></li>
                <?php } ?>
                <?php if($d2g_profile_data->exps) { ?>
                    <li class=" icon-right-open"><a class="scroll_to" href="#exp"><?php echo esc_html__('Experience', 'doctor2go-connect')?></a></li>
                <?php } ?>                
                <?php if($d2g_profile_data->edus) { ?>
                    <li class=" icon-right-open"><a class="scroll_to" href="#edu"><?php echo esc_html__('Education', 'doctor2go-connect')?></a></li>
                <?php } ?>
                <?php if($d2g_profile_data->pubs) { ?>
                    <li class=" icon-right-open"><a class="scroll_to" href="#pub"><?php echo esc_html__('Publications', 'doctor2go-connect')?></a></li>
                <?php } ?>
                <?php if($d2g_profile_data->walk_in_check == true){?>
                    <li class=" icon-right-open"><a class="highlight scroll_to" href="#inloop"><?php echo esc_html__('Walk in now', 'doctor2go-connect')?></a></li>
                <?php }?>
            </ul>
        </div>
    </div>
    <?php
    
}


function show_doctor_locations_by_id($docID = '', $show_title = true){
    if($docID == ''){
        global $d2g_profile_data;
        $locations = $d2g_profile_data->locations;
    } else {
        $locations = get_post_meta($docID, 'locations_to_go')[0];
    }
    
    $checker = 0;
    
    if($locations != ''){ ?>
        <div id="location_wrapper" class="locations section">
            <?php if($show_title == true){ ?>
                <h3 class="section_title"><?php echo esc_html__('Location(s)', 'doctor2go-connect')?></h3>
            <?php } ?>
            <?php if(count($locations) > 0){ ?>
                <ul class="location_tabs">
                    <?php foreach($locations as $location){ ?>
                        <li class="tab_link" ref-loc="#<?php echo esc_html($location['_id'])?>"><?php echo esc_html($location['name'] .' ('.$location['city'] .' - '. $location['country'].')')?></li>
                    <?php } ?>
                </ul>
            <?php } ?>
            
            <?php foreach($locations as $location){ ?>
                <div class="d2g_tab_content_wrapper <?php echo (count($locations) > 0 && $checker != 0)?'hide':''?>" id="<?php echo esc_html($location['_id'])?>">
                    <div class="d2g_tab_content">
                        <div class="row">
                            <div class="col-sm-8 no_pad_right">
                                <iframe style="width:100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo esc_html($location['street']); ?> <?php echo esc_html($location['number']); ?>,<?php echo esc_html($location['postal_code']); ?> <?php echo esc_html($location['city']); ?>&z=10&output=embed&hl=<?php echo esc_html(explode('_', get_locale())[0])?>"></iframe>
                            </div>
                            <div class="col-sm-4 ">
                                <div class="inner_wrapper">
                                    <h3><?php echo esc_html($location['name'])?></h3>
                                    <?php if($location['description'] != ''){ ?>
                                        <p class="lightGrey"><?php echo esc_html($location['description'])?></p>
                                    <?php } ?>
                                    <div class="address_wrapper">
                                        <h4><?php echo esc_html__('Address', 'doctor2go-connect')?></h4>
                                        <p><?php echo esc_html($location['country'])?></p>
                                        <p><?php echo esc_html($location['street'])?> <?php echo esc_html($location['number'])?></p>
                                        <p><?php echo esc_html($location['postal_code'])?> <?php echo esc_html($location['city'])?></p>
                                        <p><?php echo esc_html($location['country'])?></p>
                                    </div>
                                    <?php if($location['how_to_get_there'] != ''){ ?>
                                        <div class="extra_info">
                                            <h4><?php echo esc_html__('How to get there', 'doctor2go-connect')?></h4>
                                            <p><?php echo esc_html($location['how_to_get_there'])?></p>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $checker++?>
            <?php } ?>
        </div>
    <?php } 
   
}


function show_doctor_extended_info(){ 
    global $d2g_profile_data;
    ?>
    <?php if($d2g_profile_data->exps){ ?>
        <div id="exp" class="exp section">
            <h3 class="section_title"><?php echo esc_html__('Working experience', 'doctor2go-connect')?></h3>
            <div class="row exp_exp">
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Date', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Expertise', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Position', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Organisation / Hospital', 'doctor2go-connect')?></strong>
                </div>
            </div>
            <?php foreach($d2g_profile_data->exps as $exp){ ?>
                <div class="row exp_exp">
                    <div class="col-sm-3">
                        <?php echo esc_html($exp['d2g_exp_edu_start_date'])?> - <?php echo esc_html($exp['d2g_exp_edu_end_date'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($exp['d2g_exp_edu_expertise'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($exp['d2g_exp_edu_title'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($exp['d2g_exp_edu_org'])?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <?php if($d2g_profile_data->edus){ ?>
        <div id="edu" class="edu section">
            <h3 class="section_title"><?php echo esc_html__('Education', 'doctor2go-connect')?></h3>
            <div class="row exp_edu">
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Date', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Study area', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Degree', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-3">
                    <strong><?php echo esc_html__('Institution', 'doctor2go-connect')?></strong>
                </div>
            </div>
            <?php foreach($d2g_profile_data->edus as $edu){ ?>
                <div class="row exp_edu">
                    <div class="col-sm-3">
                        <?php echo esc_html($edu['d2g_exp_edu_start_date'])?> - <?php echo esc_html($edu['d2g_exp_edu_end_date'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($edu['d2g_exp_edu_study'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($edu['d2g_exp_edu_title'])?>
                    </div>
                    <div class="col-sm-3">
                        <?php echo esc_html($edu['d2g_exp_edu_org'])?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php }?>
    <?php if($d2g_profile_data->pubs){?>
        <div id="pub" class="pub section">
            <h3 class="section_title"><?php echo esc_html__('Publications', 'doctor2go-connect')?></h3>
            <div class="row exp_edu">
                <div class="col-sm-4">
                    <strong><?php echo esc_html__('title', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-2">
                    <strong><?php echo esc_html__('journal', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-2">
                    <strong><?php echo esc_html__('type of publication', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-2">
                    <strong><?php echo esc_html__('author', 'doctor2go-connect')?></strong>
                </div>
                <div class="col-sm-2">
                    <strong><?php echo esc_html__('publication Date', 'doctor2go-connect')?></strong>
                </div>
            </div>
            <?php 
            $temp_counter = 1;
            foreach($d2g_profile_data->pubs as $pub){ ?>
                <div class="row exp_edu pub">
                    <div class="col-sm-4">
                        <p><?php echo esc_html($pub['d2g_pub_title'])?></p>
                        <?php if($exp['d2g_pub_link']){?>
                            <p><a target="_blank" href="<?php echo esc_html($pub['d2g_pub_link'])?>"><?php echo esc_html__('read online', 'doctor2go-connect')?> <span style="font-size: 12px;" class="icon-right-open"></span></a></p>
                        <?php } ?>
                    </div>
                    <div class="col-sm-2">
                        <?php echo esc_html($pub['d2g_pub_journal'])?>
                    </div>
                    <div class="col-sm-2">
                        <?php echo esc_html($pub['d2g_pub_type'])?>
                    </div>
                    <div class="col-sm-2">
                        <?php 
                            echo esc_html(d2g_ttruncat($pub['d2g_pub_author'], 80));
                            if (strlen($pub['d2g_pub_author']) > 80) { ?>
                                <p style="margin-top: 10px;">
                                    <a class="fancybox" href="#authors_<?php echo esc_html($temp_counter)?>"><?php echo esc_html__('show more', 'doctor2go-connect')?></a>
                                </p>
                                <div id="authors_<?php echo esc_html($temp_counter)?>" style="max-width: 500px" class="simple_hide">
                                    <?php echo esc_html($pub['d2g_pub_author']);?>
                                </div>
                            <?php }
                        
                        ?>
                        
                    </div>
                    <div class="col-sm-2">
                        <?php echo esc_html($pub['d2g_pub_date'])?>
                    </div>
                </div>
            <?php 
            $temp_counter ++;
            } ?>
        </div>
    <?php } 
    
}


function show_booking_calendar($post = '', $only_cal = false){
    
    if($post == ''){
        global $d2g_profile_data;
    } else {
        $d2g_profile_data   = new D2G_ProfileData($post, true);
    }
    $post_id            = $d2g_profile_data->doctor_profile_ID;
    //patient data
    $patient            = wp_get_current_user();    
    $patient_meta       = get_user_meta( $patient->data->ID);
    $site_key           = get_option('d2g_recaptcha_site_key'); 
    $redirectURL        = get_the_permalink($post_id).'?book=1';

    $currLang 		    = explode('_', get_locale())[0];
    $d2gAdmin 	        = new D2G_doc_user_profile();
    $currLang 	        = explode('_', get_locale())[0];
    $pageLogin 	        = $d2gAdmin::d2g_page_url($currLang, 'login', true)['url'].'?redirect_to='.urlencode($redirectURL);
    $pageRegis 	        = $d2gAdmin::d2g_page_url($currLang, 'patient_registration', true)['url'].'?redirect_to='.urlencode($redirectURL);
    ?>
    <!--booking calendar-->
    <div id="calendar_wrapper" class="calendar section">
        <h3 class="section_title"><?php echo esc_html__('Schedule a consultation with this dermatologist at your convenience.', 'doctor2go-connect')?></h3>
        <div id="calendar"></div>
    </div>
    <div id="page_loader"></div>
    <!--booking form-->
    <div class="simple_hide" id="booking_form_wrapper">
        <div id="error"></div>
        <p id="app_msg_success" class="success simple_hide"></p>
        <?php if(is_user_logged_in()){ ?>
            <?php if($patient_meta['first_name'][0].' '.$patient_meta['last_name'][0] == ''){
                $missingName = true;
            }?>
            <p id="app_msg"><?php echo esc_html__('Before making the appointment, please check if your data is correct.', 'doctor2go-connect')?> </p>
        <?php } ?>
        <form name="booking_form" id="booking_form">
            <h3><?php echo esc_html__('Booking details', 'doctor2go-connect')?></h3>
            <ul id="app_details">
                <li class="label"><?php echo esc_html__('Doctor', 'doctor2go-connect')?></li>
                <li id="doctor"><?php echo esc_html(get_the_title($d2g_profile_data->doctor_profile_ID))?></li>
                <li class="label"><?php echo esc_html__('Costs', 'doctor2go-connect')?></li>
                <li class="icon-cc-mastercard">
                    &nbsp;&nbsp;<span id="pay_price"></span> <span id="pay_cur"></span> / <?php echo esc_html__('consultation', 'doctor2go-connect')?><br><?php echo esc_html__('Prices are excl. VAT', 'doctor2go-connect')?>
                </li>
                <li class="label"><?php echo esc_html__('Start', 'doctor2go-connect')?></li>
                <li id="start"></li>
                <li class="label"><?php echo esc_html__('End', 'doctor2go-connect')?></li>
                <li id="end"></li>
                <li class="label"><?php echo esc_html__('Location', 'doctor2go-connect')?></li>
                <li id="location"></li>
                <li class="label"><?php echo esc_html__('Your info', 'doctor2go-connect')?></li>
                <li id="patient">
                    <?php if(is_user_logged_in()){ ?>
                        <p><?php echo esc_html__('Your account data is not complete yet. Please fill in all required fields.', 'doctor2go-connect')?></p>
                        <input type="hidden" value="update_user" name="user_action" id="user_action">
                    <?php } else { ?>
                        <input type="hidden" value="none" name="user_action" id="user_action">
                    <?php } ?>
                    <input autocomplete="off" type="text" class="myrequired" id="patient_fname" class="noMargBot"  value="<?php echo esc_html($patient_meta['first_name'][0])?>" placeholder="<?php echo esc_html__('First name', 'doctor2go-connect')?> *">
                    <input autocomplete="off" type="text" class="myrequired" id="patient_lname" class="noMargBot"  value="<?php echo esc_html($patient_meta['last_name'][0])?>" placeholder="<?php echo esc_html__('Last name', 'doctor2go-connect')?> *">
                    <input autocomplete="off" type="text" class="myrequired" id="patient_email" value="<?php echo esc_html($patient->data->user_email)?>" placeholder="<?php echo esc_html__('E-mail', 'doctor2go-connect')?> *">
                    <input autocomplete="off" type="text" id="p_tel" value="<?php echo esc_html($patient_meta['p_tel'][0])?>" placeholder="<?php echo esc_html__('Tel', 'doctor2go-connect')?>">
                </li>
                
            </ul>
            <p class="hinweis"><?php echo esc_html__('* These are mandatory fields', 'doctor2go-connect')?></p>
            <div class="simple_hide" style="background: #c2c2c2; padding: 10px">
                <input readonly type="text" id="wp_doc_id" value="<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>">
                <input readonly type="text" id="wp_user_id" value="<?php echo esc_html($patient->data->ID)?>">
                <input readonly type="text" id="location_id" value="">
                <input readonly type="text" id="start_str">
                <input readonly type="text" id="end_str">
                <input readonly type="text" id="hourly_price" value="">
                <input readonly type="text" id="vat" value="">
                <input readonly type="text" id="currency" value="">
                <input readonly type="text" id="questionnaire" value="">
            </div>
            <div>
                <?php if(get_option('d2g_recaptcha_site_key') != '' && get_option('deactivate_recapctha_script') != 1){ ?>
                    <div id="recaptcha1" class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <div id="recaptchaDiv1"></div>
                <?php } ?>
            </div>
            <?php //honeypot?>
            <?php wp_nonce_field( 'booking' ); ?>
            <input name='user[tel_number]' id="tel_number" type="checkbox" value="1" tabindex="-1" style="display:none" autocomplete="false"/>
            <input id="submit_booking" type="submit" value="<?php esc_html_e('submit', 'doctor2go-connect')?>">
        </form>
    </div>
    <a id="logged_out_cal_link" href="#logged_out_cal" class="fancybox simple_hide"></a>
    <div id="logged_out_cal" class="simple_hide">
        <h3 class="mb-m error"><?php echo esc_html__('To start your choosen consult, you first need to login or register an account.', 'doctor2go-connect')?></h3>
        <div class="btn_wrapper center">
            <a class="btn btn-default button" href="<?php echo esc_html($pageLogin)?>"><?php echo esc_html__('login', 'doctor2go-connect')?></a>&nbsp;&nbsp;&nbsp;
            <a class="btn btn-default button" href="<?php echo esc_html($pageRegis)?>"><?php echo esc_html__('register', 'doctor2go-connect')?></a>
        </div>
    </div>
   
    <?php add_action('wp_footer', function () use ($post_id, $site_key, $d2gAdmin, $currLang, $only_cal, $patient_meta, $d2g_profile_data){
    $currentDate = new DateTime();
    ?>
    <script>
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        <?php if(isset($_GET['book']) && $_GET['book'] == 1){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            jQuery(document).ready(function($){
                    jQuery('#start').html(localStorage.getItem("start")); 
                    jQuery('#end').html(localStorage.getItem("end")); 
                    jQuery('#start_str').val(localStorage.getItem("start_str")); 
                    jQuery('#end_str').val(localStorage.getItem("end_str")); 
                    jQuery('#hourly_price').val(localStorage.getItem("payment_price"));
                    jQuery('#pay_price').html(localStorage.getItem("payment_price"));
                    jQuery('#vat').val(localStorage.getItem("payment_vat"));
                    jQuery('#currency').val(localStorage.getItem("payment_currency"));
                    jQuery('#pay_cur').html(localStorage.getItem("payment_currency"));
                    jQuery('#location').html(localStorage.getItem("location"));
                    jQuery('#location_id').val(localStorage.getItem("doc_location_id"));
                    jQuery('#questionnaire').val(localStorage.getItem("questionnaire"));
            });
        <?php } ?>

        <?php 
        if((isset($_GET['book']) && $_GET['book'] == 1) || (isset($_GET['create_account']) && $_GET['create_account'] == 1 )){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ?>
            jQuery(document).ready(function($){
                jQuery('#booking_form_wrapper').removeClass('simple_hide'); 
                var goal = '#booking_form_wrapper';

                setTimeout(function(){
                    jQuery('body').scrollTo(goal,{duration:'slow', offset : -260});
                }, 300)
            });
        <?php } ?>

        var captchaCode1 = '';
        var captchaCode2 = '';
        var captchaCode3 = '';
        <?php if(get_option('d2g_recaptcha_site_key') != ''){ ?>
            
            var recaptchaWidgets;
            var onloadCallback = function() {
                grecaptcha.render('recaptchaDiv1', {
                    'sitekey' : '<?php echo esc_attr($site_key); ?>',
                    'callback' : correctCaptcha1
                });
                grecaptcha.render('recaptchaDiv2', {
                    'sitekey' : '<?php echo esc_attr($site_key); ?>',
                    'callback' : correctCaptcha2
                });
                grecaptcha.render('recaptchaDiv3', {
                    'sitekey' : '<?php echo esc_attr($site_key); ?>',
                    'callback' : correctCaptcha3
                });
            };
            var correctCaptcha1 = function(response) {
                captchaCode1 = response;
            };
            var correctCaptcha2 = function(response) {
                captchaCode2 = response;
            };
            var correctCaptcha3 = function(response) {
                captchaCode3 = response;
            };
        <?php } ?>
        
        // calendar code    
        document.addEventListener('DOMContentLoaded', function($) {
            // Get the user's timezone
            <?php if($patient_meta['p_timezone'][0]){ ?>
                var timezone = '<?php echo esc_js($patient_meta['p_timezone'][0])?>';
            <?php } else { ?>
                var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            <?php } ?>
            
            let docSlots = '';
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    right: 'prev,next',
                    left: 'title',
                    center: '' 
                },
                initialDate: '<?php echo esc_js($currentDate->format('Y-m-d'))?>',
                navLinks: true, // can click day/week names to navigate views
                nowIndicator: true,
                weekNumbers: true,
                weekNumberCalculation: 'ISO',
                editable: false,
                selectable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                events: function(fetchInfo, successCallback, failureCallback) {
                    //function retrives availibility data per ajax call from WCC and than sets first availibility + tariffs + when enabled at doctor walkin form 
                    <?php if($only_cal == false){ ?>
                        //this is only triggerd on doc detail pages not when the calendar shortcode is used
                        const parentElement = document.getElementById('icon_list_<?php echo esc_js($post_id)?>'); // Replace with your parent element selector
                        const hasChildWithClass = parentElement.querySelector('.icon-clock') !== null;
                    <?php } ?>
                    

                    if (docSlots != '') {
                        //this prevents the calendar from doing an extra ajax call when changing month
                        successCallback(docSlots);
                    } else {
                        var wrapper     = '#icon_list_<?php echo esc_js($post_id)?>';
                        var postID      = '<?php echo esc_js($post_id)?>';
                        var ajax_url        = ' <?php echo  esc_js(admin_url('admin-ajax.php')); ?>';
                        var data = {
                            'action'                    : 'load_availability_data',
                            'doc_id'                    : postID
                        };

                        jQuery.ajax({
                            url: ajax_url,
                            type: 'POST',
                            data: {
                                'action'                    : 'load_availability_data',
                                'doc_id'                    : postID
                            },
                            success: function(response) {
                                console.log(response);
                                <?php if($only_cal == false){ ?>
                                    //this is only triggerd on doc detail pages not when the calendar shortcode is used
                                    if(response.data.walkin_check == true){
                                        var walkin = '<span class="walkin"><?php echo esc_html__('walk-in consult', 'doctor2go-connect')?></span>';
                                        jQuery('.walk_in_button').toggleClass('simple_hide');
                                        //jQuery('#doctor_wrapper').append(walkin);    
                                    }

                                    if(response.data.tariffs != ''){
                                        var tariffs = '<li class="icon-cc-mastercard">'+ response.data.tariffs +'</li>'
                                        jQuery('#icon_list_<?php echo esc_js($post_id)?>').append(tariffs);
                                        jQuery('.fillup_<?php echo esc_js($post_id)?>').each(function(){
                                            jQuery(this).html(response.data.tariffs);
                                        });
                                        jQuery('body.postid-<?php echo esc_js($post_id)?>').find('.booking_con').css('display', 'list-item');
                                    } else {
                                        jQuery('.fillup_<?php echo esc_js($post_id)?>').each(function(){
                                            jQuery(this).html('<?php echo esc_html__('not available', 'doctor2go-connect')?>');
                                        });
                                    }

                                    if(response.data.first_availibility != ''){
                                        var first_availability = '<li class="icon-clock">'+ response.data.first_availibility +'</li>'
                                        jQuery('#icon_list_<?php echo esc_js($post_id)?>').append(first_availability);
                                    } else {
                                        jQuery('#calendar_wrapper').css('display', 'none');
                                    }
                                <?php } ?>
                                jQuery('#page_loader').css('display', 'none');
                                //doc slots are saved in var for later use
                                docSlots = response.data.doc_slots;
                                if(response.data.first_availibility != ''){
                                    successCallback(response.data.doc_slots); // Pass the events to FullCalendar
                                } 
                                
                                
                            },
                            error: function() {
                                failureCallback(console.log('there was an error'));
                            }
                        });
                    }                    
                },
                locale: '<?php echo esc_js(explode('_',get_locale())[0])?>',
                timeZone: timezone,
                slotDuration: "00:15:00",
                scrollTime:'09:00:00',
                eventClick: function(info) { 

                    var payment_price       = info.event._def.extendedProps.payment_price;
                    var payment_currency    = info.event._def.extendedProps.payment_currency;
                    var payment_vat         = info.event._def.extendedProps.payment_vat;
                    var questionnaire       = info.event._def.extendedProps.questionnaire;

                    console.log(info.event._def.extendedProps);

                    //start date time
                    var part1 = info.event.startStr;
                    part1 = part1.split('T');
                    var stDate = part1[0];
                    stDate = stDate.split('-');
                    stDate = stDate[2] + '/' + stDate[1] + '/' + stDate[0];
                    if(part1[1].indexOf('-') != -1){
                        part1 = part1[1].split('-')[0];
                    } else {
                        part1 = part1[1].split('+')[0];
                    }
                    part1 = part1.slice(0, -3);

                    var niceStart = stDate + ' <?php echo esc_html__('at', 'doctor2go-connect')?> ' + part1 + ' (' + timezone + ')'; 

                    //end date time
                    var part2 = info.event.endStr;
                    part2 = part2.split('T');
                    var endDate = part2[0]; 
                    endDate = endDate.split('-');
                    endDate = endDate[2] + '/' + endDate[1] + '/' + endDate[0];
                    if(part2[1].indexOf('-') != -1){
                        part2 = part2[1].split('-')[0];
                    } else {
                        part2 = part2[1].split('+')[0];
                    }
                    part2 = part2.slice(0, -3);

                    var niceEnd = endDate + ' <?php echo esc_html__('at', 'doctor2go-connect')?> ' + part2 + ' (' + timezone + ')';

                    //location handeling
                    var doc_location        = '';
                    var doc_location_id     = '';
                    if(info.event._def.extendedProps.location != null){
                        //console.log(info.event._def.extendedProps.location);
                        doc_location        = info.event._def.extendedProps.location.location_name + ': ' + info.event._def.extendedProps.location.location_full_adress_url;
                        doc_location_id     = info.event._def.extendedProps.location.location_id;
                    } else {
                        doc_location        = '<?php echo esc_html__('Video', 'doctor2go-connect')?>';
                    }


                    jQuery('#start').html(niceStart); 
                    localStorage.setItem("start", niceStart);

                    jQuery('#end').html(niceEnd); 
                    localStorage.setItem("end", niceEnd);

                    jQuery('#start_str').val(info.event.startStr); 
                    localStorage.setItem("start_str", info.event.startStr);

                    jQuery('#end_str').val(info.event.endStr); 
                    localStorage.setItem("end_str", info.event.endStr);

                    jQuery('#hourly_price').val(payment_price);
                    jQuery('#pay_price').html(payment_price);
                    localStorage.setItem("payment_price", payment_price);

                    jQuery('#vat').val(payment_vat);
                    localStorage.setItem("payment_vat", payment_vat);

                    jQuery('#currency').val(payment_currency);
                    jQuery('#pay_cur').html(payment_currency);
                    localStorage.setItem("payment_currency", payment_currency);

                    jQuery('#location').html(doc_location);
                    localStorage.setItem("location", doc_location);

                    jQuery('#location_id').val(doc_location_id);
                    localStorage.setItem("doc_location_id", doc_location_id);

                    jQuery('#questionnaire').val(questionnaire);
                    localStorage.setItem("questionnaire", questionnaire);

                    jQuery('#booking_form_wrapper').removeClass('simple_hide'); 
                    var goal = '#booking_form_wrapper';
                    setTimeout(function(){
                        jQuery('body').scrollTo('#booking_form_wrapper',{duration:'slow', offset : -160});
                    }, 200)

                }, 
                eventContent: function( info ) {
                    var location_name   = '';
                    var partStart       = '';
                    if(info.event._def.extendedProps.location != null){
                        //console.log(info.event._def.extendedProps.location);
                        location_name   = info.event._def.extendedProps.location.location_name;
                        partStart       = '<span class="physical">';
                        
                    } else {
                        location_name = '<?php echo esc_html__('Video', 'doctor2go-connect')?>'
                        partStart       = '<span class="online">';
                    }
                    var partEnd         = '</span>';
                    var part1           = info.event.startStr;
                    part1               = part1.split('T');
                    
                    var theDay = part1[0];
                    if(part1[1].indexOf('-') != -1){
                        part1 = part1[1].split('-')[0];
                    } else {
                        part1 = part1[1].split('+')[0];
                    }
                    part1 = part1.slice(0, -3);
                    var part2 = info.event.endStr;
                    part2 = part2.split('T');
                    if(part2[1].indexOf('-') != -1){
                        part2 = part2[1].split('-')[0];
                    } else {
                        part2 = part2[1].split('+')[0];
                    }
                    part2 = part2.slice(0, -3);
                    return {html: partStart + part1 + '-' + part2 + ' / ' + info.event._def.extendedProps.payment_price + info.event._def.extendedProps.payment_currency  + '<br>' + location_name +  partEnd}
                    
                }

            });

            calendar.render();
            //calendar.changeView('timeGridWeek');

            
        });

        jQuery(document).ready(function($){
            $('.myrequired').focus(function(){
                $(this).css('border', 'none');
            });

            //validates and submits the booking form
            $('#submit_booking').click(function(e){
                e.preventDefault();

                //honeypot check
                if($('#tel_number').is(':checked')){
                    return false;
                }
                
                var checker         = false;
                var ajax_url        = '<?php echo  esc_js(admin_url('admin-ajax.php')); ?>';
                var email           = $('#patient_email').val();   
                var uname           = $('#uname').val();
                var pass            = $('#pass1').val();
                var rpass           = $('#pass2').val();
                var user_action     = $('#user_action').val();
                var user_tel        = $('#p_tel').val();
                var location_id     = $('#location_id').val();
                var checker_message = '';
                //checks required fields for booking
                $('.myrequired').each(function(){
                    if($(this).val() === ""){
                        $(this).css('border', '1px solid #ff5000');
                        checker = true;
                        checker_message = '<?php echo esc_html__('Please fill in all marked fields. ', 'doctor2go-connect')?>';
                    }
                    jQuery('body').scrollTo('#booking_form_wrapper',{duration:'slow', offset : -200});
                });
                
   
                var data = {
                    'action'                    : 'create_wcc_appointment',
                    'start'                     : $('#start_str').val(),
                    'end'                       : $('#end_str').val(),
                    'vat'                       : $('#vat').val(),
                    'email'                     : $('#patient_email').val(),
                    'patient_fname'             : $('#patient_fname').val(),
                    'patient_lname'             : $('#patient_lname').val(),
                    'wp_doc_id'                 : $('#wp_doc_id').val(),
                    'wcc_user_id'               : $('#wcc_user_id').val(),
                    'wp_user_id'                : $('#wp_user_id').val(),
                    'docPrice'                  : $('#hourly_price').val(),
                    'comment'                   : $('#patient_comment').val(),
                    'user_action'               : user_action,
                    'pass'                      : pass,
                    'location_id'               : location_id,
                    'token'                     : $('#token').val(),
                    'p_tel'                     : $('#p_tel').val(),
                    'currency'                  : $('#currency').val(),
                    'questionnaire_id'          : $('#questionnaire').val(),
                    'g-recaptcha-response'      : captchaCode1,
                    '_wpnonce'                  : $('#_wpnonce').val()
                };

                if(isEmail(email) == 'notOK'){
                    $('#email').css('border-color', '#ff5000');
                    checker = true;
                    checker_message = checker_message + '<?php echo esc_html__(' You have entered an invalid e-mail. ', 'doctor2go-connect')?>';
                }

                <?php if(get_option('d2g_recaptcha_site_key') != ''){ ?>
                    // ✅ reCAPTCHA validation for this form
                    if(typeof captchaCode1 === 'undefined' || captchaCode1.length === 0){
                        checker = true;
                        checker_message += '<?php echo esc_html__(' Please verify that you are not a robot. ', 'doctor2go-connect')?>';
                    }
                <?php } ?>

                
               

                if(checker == false){
                    $('#booking_form').addClass('loading');
                    $('#error').addClass('simple_hide');
                    $('#app_msg').addClass('simple_hide');
                    $('#app_msg_success').addClass('simple_hide');
                    $.post(ajax_url, data, function(response) {
                        console.log(response);
                        if(response != 'error'){
                            <?php 
                            if(is_user_logged_in()){
                                $pageData 		= $d2gAdmin::d2g_page_url($currLang, 'appointments', true);
                            } else {
                                $pageData 		= $d2gAdmin::d2g_page_url($currLang, 'appointment_confirmation', true);
                            }
                            

                            $redirectURL    = urlencode($pageData['url'].'?booked_consult=video&app=');
                            ?>

                            var answer = '<p class="success"><?php echo esc_html__('Your reservation has been successfully. You will now be redirected to your appointment manager. You might need to fill in an intake questionnaire.', 'doctor2go-connect')?></p>';
                            
                            if(response.send_to_payment == true){
                                answer += '<p><?php echo esc_html__('Online consultation reservations are valid for 24 hours and require payment within this period. Otherwise, they will be canceled.', 'doctor2go-connect')?></p>';
                                answer += '<p><?php echo esc_html__('If you were not redirected automatically than please click on the button.', 'doctor2go-connect')?></p>';
                                answer += '<p><a target="_blank" class="btn btn-default" href="<?php echo esc_js(get_option('waiting_room_url'))?>payment/' + response.appointment_id + '?locale=<?php echo esc_html(explode('_',get_locale())[0])?>&redirect_url=<?php echo esc_html($redirectURL)?>' + response.appointment_id + '">';
                                answer += '<?php echo esc_html__('pay now', 'doctor2go-connect')?></a></p>';
                            } 

                            $('#app_msg_success').html(answer).removeClass('simple_hide');

                            if(response.send_to_payment == true){
                                setTimeout(function(){
                                    window.location.href = '<?php echo esc_js(get_option('waiting_room_url'))?>payment/' + response.appointment_id + '?locale=<?php echo esc_js(explode('_',get_locale())[0])?>&redirect_url=<?php echo esc_js($redirectURL)?>' + response.appointment_id;
                                }, 1500);
                            } else {
                                setTimeout(function(){
                                    window.location.href = "<?php echo esc_js($pageData['url'])?>?app=" + response.appointment_id + "&client_token=" + response.client_token;
                                }, 1500);
                            }

                        } else {
                            var answer = '<p><?php echo esc_html__('There has been an error, please try an other slot or try later. In case of futher issues, please contact the support.', 'doctor2go-connect')?></p>';
                            $('#error').html(answer).removeClass('simple_hide');
                        }
                        
                        $('#booking_form').toggleClass('loading');
                        $('#booking_form').toggleClass('simple_hide');
                        var goal = '#booking_form_wrapper';
                        jQuery('body').scrollTo(goal,{duration:'slow', offset : -260});
                        
                    });
                } else {
                    $('#error').css('display', 'block').html(checker_message);
                }

                return false;
            });

            setTimeout(() => {
                <?php 
            if($d2g_profile_data->doctor_meta['end_holiday'][0] != '' && $d2g_profile_data->doctor_meta['start_holiday'][0] != ''){
                $start  = new DateTime($d2g_profile_data->doctor_meta['start_holiday'][0]);
                $formattedStart = (new DateTime($d2g_profile_data->doctor_meta['start_holiday'][0]))->format('d/m/Y');
                $end    = new DateTime($d2g_profile_data->doctor_meta['end_holiday'][0]);
                $formattedEnd = (new DateTime($d2g_profile_data->doctor_meta['end_holiday'][0]))->format('d/m/Y');
                $check  = new DateTime();

                // Only compare the date part (ignore time)
                $check->setTime(0, 0, 0);
                $start->setTime(0, 0, 0);
                $end->setTime(0, 0, 0);

                if($check >= $start && $check <= $end ){ ?>
                    alert('<?php echo esc_html__('Attention: I am unavailable in the following periode.', 'doctor2go-connect')?>').'\n'.$formattedStart.' - '.$formattedEnd;      
                <?php }
            } ?>
            }, 200);
            
        })

    </script>
    <style>

        .fc-timegrid-event .fc-event-main{
            padding: 0!important;
            margin-top: -2px;
        }

    </style>
<?php });
}

//show confirmation boxes on register page + booking page
function confirmation_checkboxes($form = ''){
    $currLang 		= explode('_', get_locale())[0];
    $d2gAdmin 		= new D2G_doc_user_profile();
    $pageDataPriv 	= $d2gAdmin::d2g_page_url($currLang, 'privacy_policy', true);
    $pageDataTerms 	= $d2gAdmin::d2g_page_url($currLang, 'terms_and_conditions', true);
    $pageDataDiscl 	= $d2gAdmin::d2g_page_url($currLang, 'disclaimer', true);
    ?>
    <div id="conf_boxes">
    <p>
        <label for="conf_terms>"><input id="conf_terms<?php echo esc_html($form)?>" name="meta[conf_terms]" type="checkbox" value="yes"> <?php echo esc_html__('I accept the terms and conditions.', 'doctor2go-connect')?></label>&nbsp;&nbsp;&nbsp;&nbsp; 
        <a target="_blank" href="<?php echo esc_html($pageDataTerms['url'])?>"><?php echo esc_html__('view the terms & conditions', 'doctor2go-connect')?></a>
    </p>
    <p>
        <label for="conf_privacy>"><input id="conf_privacy<?php echo esc_html($form)?>" name="meta[conf_privacy]" type="checkbox" value="yes"> <?php echo esc_html__('I accept the privacy rules.', 'doctor2go-connect')?></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <a target="_blank" href="<?php echo esc_html($pageDataPriv['url'])?>"><?php echo esc_html__('view the privacy rules', 'doctor2go-connect')?></a>
    </p>
    <p>
        <label for="conf_disclaimer>"><input id="conf_disclaimer<?php echo esc_html($form)?>" name="meta[conf_disclaimer]" type="checkbox" value="yes"> <?php echo esc_html__('I accept the disclaimer.', 'doctor2go-connect')?></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <a target="_blank" href="<?php echo esc_html($pageDataDiscl['url'])?>"><?php echo esc_html__('view the disclaimer', 'doctor2go-connect')?></a>
    </p>
    </div>
<?php }

//this creates the back to overview button for on the doctor detail pages
function show_back_btn(){
    ?>
    <div class="btn_wrapper center mb-l mt-l">    
        <a id="backLink" class="btn btn-default wp-block-button__link" href="<?php echo esc_url(d2g_curPageURL())?>"><?php echo esc_html__('back to overview', 'doctor2go-connect')?></a>
    </div>
    
    <?php
}

/**
 * @param $objects
 * @return array from taxonmy objects in only key value pairs 
 */
function prepMyArray($objects){
    $prepArray = array();
    foreach ($objects as $object){
        $prepArray[$object->slug] = $object->name;
    }
    return $prepArray;
}

//this will create the walkin form
function show_walkin_form(){
    global $d2g_profile_data;
    
    
    
    //countries
    
    
    $allCountries       = get_terms(array('taxonomy' => 'country-origin', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
    $allCountriesArray  = ($allCountries !== false)?prepMyArray($allCountries):'';
    
    $site_key = get_option('d2g_recaptcha_site_key'); 
    if(is_user_logged_in(  )){
        $currUser       = wp_get_current_user();
        $currUserID     = $currUser->ID;
        $userMeta       = get_user_meta( $currUserID );
        //nice_dump($currUser);
    } else {
        $detail_link = get_the_permalink( );
        $d2gAdmin 	= new D2G_doc_user_profile();
        $currLang 	= explode('_', get_locale())[0];
        $pageLogin 	= $d2gAdmin::d2g_page_url($currLang, 'login', true);
        $pageRegis 	= $d2gAdmin::d2g_page_url($currLang, 'patient_registration', true);
        $action_buttons = array(
                'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?open=walk_in_link'),
                'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?open=walk_in_link'),
        );
    }
    
    
    //nice_dump($d2g_profile_data);
    ?>
    <div id="inloop" class="walkin_form_wrapper simple_hide" style="max-width: 1000px;">
        <h3 class="section_title"><?php echo esc_html__('Walk-in consultation', 'doctor2go-connect')?></h3>
        <span class="price_wrapper">
            <p style="margin-bottom: 2px;"><?php echo esc_html__('Consultation fee:', 'doctor2go-connect')?></p>
            <strong><?php echo esc_html($d2g_profile_data->doctor_meta['walk_in_currency'][0].' '.$d2g_profile_data->doctor_meta['walk_in_price'][0]);?></strong>
        </span>
        <p class="mb-s"><?php echo esc_html__('This doctor is currently available for a walk-in consultation.
Please complete the form and click “Pay and Continue” to proceed.
After your payment is confirmed, you’ll be placed in the waiting room. If others are ahead of you, a short wait may apply.', 'doctor2go-connect')?></p>
        <?php if(!is_user_logged_in()) { ?>
            <div class="note mb-s">
                                        <p><strong><?php echo esc_html__('Register for the walk-in consultation with this doctor.', 'doctor2go-connect')?></strong></p>
                        <p><?php echo esc_html__('Don’t have an account yet? You can create one now. 
Prefer to continue without an account? That’s also possible. 
Please note: without an account, you will only have one-time access to the secure patient portal after your consultation to download any documents the doctor may share with you. 
With an account, you’ll have ongoing access and can also message the doctor after your appointment.', 'doctor2go-connect')?></p>
                <div class="btn_wrapper center">
                    <a class="btn btn-default button" href="<?php echo esc_url($action_buttons['link_login'])?>"><?php echo esc_html__('login', 'doctor2go-connect')?></a>&nbsp;&nbsp;&nbsp;
                    <a class="btn btn-default button" href="<?php echo esc_url($action_buttons['link_regis'])?>"><?php echo esc_html__('register', 'doctor2go-connect')?></a>
                </div>
            </div>
            
        <?php } ?>
        <div class="error simple_hide" id="walkin_error"></div>
        <div class="walkin_form_inner_wrapper mb-s">
            <form id="walkin_form" method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field( 'walkin_form_action', 'walkin_form_nonce' ); ?>
                <input type="hidden" name="wp_doc_id" value="<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>"> 
                <div class="row mb-s">
                    <div class="col-sm-4">
                        <div>
                            <label for="client_name"><?php echo esc_html__('Patient name', 'doctor2go-connect')?> *</label>
                            <input class="required_walk" type="text" value="<?php echo esc_html($userMeta['first_name'][0].' '.$userMeta['last_name'][0])?>" name="client_name" id="client_name">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label for="client_email"><?php echo esc_html__('Patient email', 'doctor2go-connect')?> *</label>
                            <input class="required_walk" type="text" value="<?php echo esc_html($currUser->data->user_email)?>" name="client_email" id="client_email">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label for="optie_telefoonnummer"><?php echo esc_html__('Patient phone', 'doctor2go-connect')?></label>
                            <input class="" type="text" value="<?php echo esc_html($userMeta['p_tel'][0])?>" name="optie_telefoonnummer" id="optie_telefoonnummer">
                        </div>
                    </div>
                </div>
                <div class="row mb-s">
                    <div class="col-sm-4">
                        <div>
                            <label for="optie_geboortedatum"><?php echo esc_html__('Date of Birth: day/month/year  ', 'doctor2go-connect')?> *</label>
                            <input class="required_walk" type="date"  name="optie_geboortedatum" id="optie_geboortedatum">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label for="optie_aanhef"><?php echo esc_html__('Gender', 'doctor2go-connect')?></label>
                            <select name="optie_aanhef" id="optie_aanhef">
                                <option value="0"><?php echo esc_html__('make a choice', 'doctor2go-connect')?></option>
                                <option value="male"><?php echo esc_html__('male', 'doctor2go-connect')?></option>
                                <option value="female"><?php echo esc_html__('female', 'doctor2go-connect')?></option>
                                <option value="other"><?php echo esc_html__('other', 'doctor2go-connect')?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label class=""><?php echo esc_html__('Country', 'doctor2go-connect')?></label>
                            <select name="optie_land">
                                <option value="0"><?php echo esc_html__('Country', 'doctor2go-connect')?></option>
                                <?php 
                                $current_theme = wp_get_theme();
                                $theme_id = $current_theme->get( 'Template' );
                                if($theme_id == 'wcc-doclisting'){
                                    foreach ($allCountries as $country){
                                        $selected = '';
                                        if(isset($countriesArray[$country->slug])){
                                            $selected = 'selected';
                                        } ?>
                                        <option <?php echo esc_html($selected)?> value="<?php echo esc_html($country->slug)?>"><?php echo (pll_current_language() == 'en')?esc_html($country->name):esc_html(get_term_meta($country->term_id, 'rudr_text_'.pll_current_language(), true))?></option>
                                    <?php }
                                } else {
                                    foreach ($allCountriesArray as $slug => $name){
                                        $selected = '';
                                        if(isset($countriesArray[$slug])){
                                            $selected = 'selected';
                                        } ?>
                                        <option <?php echo esc_html($selected)?> value="<?php echo esc_html($slug)?>"><?php echo esc_html($name)?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>  
                </div>
                <label class="small"><?php echo esc_html__('Reason for consult', 'doctor2go-connect')?>*</label>
                <textarea class="required_walk" name="optie_reason" id="optie_reason"></textarea>
                <div class="mb-m">
                    <?php if(!is_user_logged_in(  )){ ?>
                        <?php confirmation_checkboxes('_wf')?>
                    <?php } ?>
                    <div id="recaptcha2" class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <div id="recaptchaDiv2"></div>
                </div>
                <p class="mb-s"><button class="btn btn-default wp-block-button__link request_walkin button" tabindex="6" id="save"><?php esc_html_e('pay and continue', 'doctor2go-connect')?></button></p>
                <p><?php esc_html_e("After your payment goes through, you’ll enter the waiting room. The doctor will begin the consultation shortly", 'doctor2go-connect')?></p>
            </form>
        </div>
        <p><?php echo esc_html__('* required fields.', 'doctor2go-connect')?></p>
    </div>
    <?php 
    // add js functions to footer
    add_action('wp_footer', function () use ($site_key) { ?>
        <script>
            
            jQuery(document).ready(function($){
                $('.request_walkin').click(function(event){
                    event.preventDefault();

                    var checker_message     = '';
                    var checker             = false;
                    $('.required_walk').each(function(){
                        if($(this).val() === ""){
                            $(this).css('border-color', '#970808');
                            checker = true;
                            checker_message = '<?php echo esc_html__('Kindly review all fields, as some required information is still missing. ', 'doctor2go-connect')?>';
                        }
                    });

                    <?php if(!is_user_logged_in(  )){ ?>
                         if($('#conf_privacy_wf').is(':not(:checked)')){
                            checker = true;
                            checker_message = checker_message + '<?php echo esc_html__(' You must accept the privacy rules. ', 'doctor2go-connect')?>';
                        }

                        if($('#conf_terms_wf').is(':not(:checked)')){
                            checker = true;
                            checker_message = checker_message + '<?php echo esc_html__(' You must accept the terms and conditions. ', 'doctor2go-connect')?>';
                        }

                        if($('#conf_disclaimer_wf').is(':not(:checked)')){
                            checker = true;
                            checker_message = checker_message + '<?php echo esc_html__(' You must accept the disclaimer. ', 'doctor2go-connect')?>';
                        }
                    <?php } ?>

                    <?php if(get_option('d2g_recaptcha_site_key') != ''){ ?>
                        // ✅ reCAPTCHA validation for this form
                        if(typeof captchaCode2 === 'undefined' || captchaCode2.length === 0){
                            checker = true;
                            checker_message += '<?php echo esc_html__(' Please verify that you are not a robot. ', 'doctor2go-connect')?>';
                        }
                    <?php } ?>

                    if(checker == false){
                        $("#inloop").toggleClass('loading');
                        var myformData = new FormData($("#walkin_form")[0]);
                        myformData.append('action', 'create_wcc_walkin');
                        
                        
                        var ajax_url = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

                        $.ajax({
                            type: "POST",
                            data: myformData,
                            url: ajax_url,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                console.log(response);
                                // handle success
                                $("#inloop").toggleClass('loading');
                                //console.log(response);
                                window.location.href = response.data.redirect_url;
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                $(".walkin_form_inner_wrapper").toggleClass('loading');
                                // handle error
                                console.log(errorThrown);
                            }
                        });
                    } else {
                        $('#walkin_error').html(checker_message).toggleClass('simple_hide');
                        return false;
                    }

                    return false;

                });

            
            });
        
            
        
        
        </script>
        
    <?php }, 100);

}


//this will create the walkin form
function show_written_con_form(){
    global $d2g_profile_data;

    if(get_option('d2g_use_imgix') == 1){
        $doc_pic = $d2g_profile_data->feat_pic_full.'&w=120&h=120&fit=crop&crop=faces&auto=format,compress';
    } else {
        $doc_pic = $d2g_profile_data->feat_pic_square;
    }    
    
    $site_key = get_option('d2g_recaptcha_site_key'); 
    if(is_user_logged_in(  )){
        $currUser       = wp_get_current_user();
        $currUserID     = $currUser->ID;
        $userMeta       = get_user_meta( $currUserID );
    } else {
        $detail_link = get_the_permalink( );
        $d2gAdmin 	= new D2G_doc_user_profile();
        $currLang 	= explode('_', get_locale())[0];
        $pageLogin 	= $d2gAdmin::d2g_page_url($currLang, 'login', true);
        $pageRegis 	= $d2gAdmin::d2g_page_url($currLang, 'patient_registration', true);
        $action_buttons = array(
                'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?open=written_con_link'),
                'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?open=written_con_link'),
        );
    }
    ?>
    
    <div id="written_consult" class="walkin_form_wrapper simple_hide" style="max-width:1000px">
        <header class="section_header mb-m">
            <img src="<?php echo  esc_html($doc_pic)?>" alt="<?php echo esc_html($d2g_profile_data->doctor->post_title); ?>">
            <h3 class="section_title"><?php echo esc_html__('Written consultation', 'doctor2go-connect')?>:<br><?php echo esc_html($d2g_profile_data->doctor->post_title); ?></h3>
        </header>
        <span class="price_wrapper">
            <p style="margin-bottom: 2px;"><?php echo esc_html__('Consultation fee:', 'doctor2go-connect')?></p>
            <strong><?php echo esc_html($d2g_profile_data->doctor_meta['written_con_currency'][0].' '.$d2g_profile_data->doctor_meta['written_con_price'][0]);?></strong>
        </span>
        <div class="info_notes mb-s">
            <h4><?php echo esc_html__('Obtain a professional assessment from a certified dermatologist by email within two working days through a straightforward three-step process.', 'doctor2go-connect')?></h4>
            <div class="flex_it">
                <div><span class="flaticon-personal-information icon"></span><span><strong>1. </strong><?php echo esc_html__('Enter personal information', 'doctor2go-connect')?></span></div>
                <div><span class="flaticon-allergy-test icon"></span><span><strong>2. </strong><?php echo esc_html__('Describe your complaint', 'doctor2go-connect')?></span></div>
                <div><span class="flaticon-credit-card icon"></span><span><strong>3. </strong><?php echo esc_html__('Complete with payment', 'doctor2go-connect')?></span></div>
            </div>
        </div>
        <h3 class="section_title"><?php echo esc_html__('Step 1 from 3: Enter personal information', 'doctor2go-connect')?></h3>
        <div class="error simple_hide" id="written_con_error"></div>
        <div class="walkin_form_inner_wrapper mb-s">
            <form id="written_con_form" method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field( 'email_advice_form_action', 'email_advice_form_nonce' ); ?>
                <input type="hidden" name="wp_doc_id" value="<?php echo esc_html($d2g_profile_data->doctor_profile_ID)?>"> 
                <div class="row mb-s simple_hide">
                    <div class="col-sm-12">
                        <div>
                            <input id="type_small" class="required_wc" type="radio"  value="short" name="type" checked>
                            <label for="type_small"><?php echo esc_html__('Short Questionnaire – for simple or minor skin issues', 'doctor2go-connect')?></label>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div>
                            <input id="type_default" class="required_wc" type="radio"  value="default" name="type">
                            <label for="type_default"><?php echo esc_html__('Extended Questionnaire – for complex or multiple skin concerns', 'doctor2go-connect')?></label>
                            
                        </div>
                    </div>
                </div>
                <div class="row mb-s">
                    <div class="col-sm-4">
                        <div>
                            <label for="first_name"><?php echo esc_html__('First name', 'doctor2go-connect')?> *</label>
                            <input class="required_wc" type="text" value="<?php echo esc_html($userMeta['first_name'][0])?>" name="first_name" id="first_name">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label for="last_name"><?php echo esc_html__('Last name', 'doctor2go-connect')?> *</label>
                            <input class="required_wc" type="text" value="<?php echo esc_html($userMeta['last_name'][0])?>" name="last_name" id="last_name">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div>
                            <label for="client_email"><?php echo esc_html__('Patient email', 'doctor2go-connect')?> *</label>
                            <input class="required_wc" type="text" value="<?php echo esc_html($currUser->data->user_email)?>" name="client_email" id="client_email_ec">
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div>
                            <div id="recaptcha3" class="g-recaptcha mb-s" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                            <div id="recaptchaDiv3"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-m">
                    <button class="btn btn-default wp-block-button__link start_written_con button" tabindex="6" id="save"><?php esc_html_e('pay and continue', 'doctor2go-connect')?></button>
                </div>
            </form>
        </div>
        <p><?php echo esc_html__('* required fields.', 'doctor2go-connect')?></p>
    </div>
    <?php 
    // add js functions to footer
    add_action('wp_footer', function () use ($site_key) { ?>
        <script>
            
            jQuery(document).ready(function($){
                $('.start_written_con').click(function(event){
                    event.preventDefault();

                    var checker_message     = '';
                    var checker             = false;
                    $('.required_wc').each(function(){
                        if($(this).val() === ""){
                            $(this).css('border-color', '#970808');
                            checker = true;
                            checker_message = '<?php echo esc_html__('Kindly review all fields, as some required information is still missing. ', 'doctor2go-connect')?>';
                        }
                    });


                    if(isEmail($('#client_email_ec').val()) == 'notOK'){
                        $('#client_email_ec').css('border-color', '#ff5000');
                        checker = true;
                        checker_message = checker_message + '<?php echo esc_html__(' You have entered an invalid e-mail. ', 'doctor2go-connect')?>';
                    }

                    <?php if(get_option('d2g_recaptcha_site_key') != ''){ ?>
                        // ✅ reCAPTCHA validation for this form
                        if(typeof captchaCode3 === 'undefined' || captchaCode3.length === 0){
                            checker = true;
                            checker_message += '<?php echo esc_html__(' Please verify that you are not a robot. ', 'doctor2go-connect')?>';
                        }
                    <?php } ?>

                    if(checker == false){
                        $("#written_consult").toggleClass('loading');
                        var myformData = new FormData($("#written_con_form")[0]);
                        myformData.append('action', 'create_wcc_written_cosnsult');
                        
                        
                        var ajax_url = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

                        $.ajax({
                            type: "POST",
                            data: myformData,
                            url: ajax_url,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                console.log(response);
                                $("#written_consult").toggleClass('loading');
                                // handle success
                                window.location.href = response.data.redirect_url;
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                $("#written_consult").toggleClass('loading');
                                // handle error
                                console.log(errorThrown);
                            }
                        });
                    } else {
                        $('#written_con_error').html(checker_message).toggleClass('simple_hide');
                        return false;
                    }

                    return false;

                });
            });
        </script>
        
    <?php }, 100);

}


function show_consult_buttons($template = '', $size = ''){
    global $d2g_profile_data;
    $detail_link = get_the_permalink( );
    $post_id    = get_the_ID();
    $d2gAdmin 	= new D2G_doc_user_profile();
    $currLang 	= explode('_', get_locale())[0];
    $pageLogin 	= $d2gAdmin::d2g_page_url($currLang, 'login', true);
    $pageRegis 	= $d2gAdmin::d2g_page_url($currLang, 'patient_registration', true);
    $location_check = array();
    $holiday = false;
    //nice_dump($d2g_profile_data->doctor_meta);
    if($d2g_profile_data->doctor_meta['locations_to_go'][0]){
        $location_check = unserialize($d2g_profile_data->doctor_meta['locations_to_go'][0]);
    }

    if($d2g_profile_data->doctor_meta['end_holiday'][0] != '' && $d2g_profile_data->doctor_meta['start_holiday'][0] != ''){
        $start  = new DateTime($d2g_profile_data->doctor_meta['start_holiday'][0]);
        $end    = new DateTime($d2g_profile_data->doctor_meta['end_holiday'][0]);
        $check  = new DateTime();

        // Only compare the date part (ignore time)
        $check->setTime(0, 0, 0);
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        if($check >= $start && $check <= $end ){
            $holiday = true;
        }
    }
    
    
    
    $consult_buttons = array(
        'show_doc'       => array(
            'image'         => ($size == 'small')?'view-doctor-small.png':'view-doctor.png',
            'name'          => __('View doctor', 'doctor2go-connect'),
            'show'          => ($template == 'detail')?false:true,
            'li_class'      => '',
            'a_class'       => '',
            'a_class_out'   => '',
            'link'          => $detail_link,
            'price'         => '',
            'currency'      => '',
            'link_login'    => '',
            'link_regis'    => '',
            'price_class'   => ''
        ),
        'walk_in'       => array(
            'image'         => ($size == 'small')?'walk-in-small.png':'walk-in.png',
            'name'          => __('Walk-in Consult', 'doctor2go-connect'),
            'show'          => ($d2g_profile_data->doctor_meta['walk_in_price'][0] != '')?true:false,
            'li_class'      => 'simple_hide',
            'a_class'       => ($template == 'detail')?'fancybox variant':'variant',
            'a_class_out'   => 'fancybox',
            'link'          => ($template == 'detail')?'#inloop':$detail_link.'?open=walk_in_link',
            'price'         => $d2g_profile_data->doctor_meta['walk_in_price'][0],
            'currency'      => $d2g_profile_data->doctor_meta['walk_in_currency'][0],
            'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?open=walk_in_link'),
            'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?open=walk_in_link'),
            'price_class'   => ''
        ),
        'written_con'   => array(
            'image'         => ($size == 'small')?'written-consult-small.png':'written-consult.png',
            'name'          => __('Written Consult', 'doctor2go-connect'),
            'show'          => ($d2g_profile_data->doctor_meta['written_con_price'][0] != '' && $holiday == false)?true:false,
            'li_class'      => '',
            'a_class'       => ($template == 'detail')?'fancybox':'',
            'a_class_out'   => 'fancybox',
            'link'          => ($template == 'detail')?'#written_consult':$detail_link.'?open=written_con_link',
            'price'         => $d2g_profile_data->doctor_meta['written_con_price'][0],
            'currency'      => $d2g_profile_data->doctor_meta['written_con_currency'][0],
            'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?open=written_con_link'),
            'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?open=written_con_link'),
            'price_class'   => ''
        ),
        /*'free_intake'  => array(
            'image'         => ($size == 'small')?'free-intake-small.png':'free-intake.png',
            'name'          => __('Free Online Intake', 'doctor2go-connect'),
            'show'          => ($d2g_profile_data->doctor_meta['d2g_intake_call'][0] != '')?true:false,
            'li_class'      => '',
            'a_class'       => ($template == 'detail')?'scroll_to variant':'variant',
            'a_class_out'   => 'fancybox variant',
            'link'          => ($template == 'detail')?'#calendar_wrapper':$detail_link.'?scroll_to=calendar_wrapper',
            'price'         => '0',
            'currency'      => $d2g_profile_data->doctor_meta['written_con_currency'][0],
            'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'price_class'   => ''
        ),*/
        'physical_con'  => array(
            'image'         => ($size == 'small')?'physical-consult-small.png':'physical-consult.png',
            'name'          => __('Physical Consult', 'doctor2go-connect'),
            'show'          => (count($location_check) > 0)?true:false,
            'li_class'      => 'simple_hide booking_con',
            'a_class'       => ($template == 'detail')?'scroll_to variant':'variant',
            'a_class_out'   => 'fancybox variant',
            'link'          => ($template == 'detail')?'#calendar_wrapper':$detail_link.'?scroll_to=calendar_wrapper',
            'price'         => '0',
            'currency'      => '',
            'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'price_class'   => 'fillup'
        ),
        'video_con'  => array(
            'image'         => ($size == 'small')?'video-consult-small.png':'video-consult.png',
            'name'          => __('Video Consult', 'doctor2go-connect'),
            'show'          => true,
            'li_class'      => 'simple_hide booking_con',
            'a_class'       => ($template == 'detail')?'scroll_to variant':'variant',
            'a_class_out'   => 'scroll_to variant',
            'link'          => ($template == 'detail')?'#calendar_wrapper':$detail_link.'#calendar_wrapper',
            'price'         => '0',
            'currency'      => '',
            'link_login'    => $pageLogin['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'link_regis'    => $pageRegis['url'].'?redirect_to='.urlencode($detail_link.'?scroll_to=calendar_wrapper'),
            'price_class'   => 'fillup'
        ),
        
    );
    ?>
    
    <ul class="consult_buttons">
        <?php foreach ($consult_buttons as $id => $button){ 
            if((!is_user_logged_in()) && $id != 'walk_in' && $id != 'show_doc' && $id != 'written_con' && $id != 'video_con'){
                $myLink = '#'.$id.'_message_'.$post_id;
            } else{
                $myLink = $button['link'];
            }
            ?>
            <?php if($button['show'] == true){ ?> 
                <li id="<?php echo esc_html($id)?>_button" class="<?php echo esc_html($id)?>_button <?php echo esc_html($button['li_class'])?>">
                    <a id="<?php echo esc_html($id.'_link')?>" class="<?php echo esc_html($id.'_link')?> btn btn-default button <?php echo (is_user_logged_in())?esc_html($button['a_class']):esc_html($button['a_class_out'])?>" href="<?php echo esc_url($myLink)?>">
                        <div class="image"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'images/'.$button['image']);?>"></div>
                        <div class="name"><?php echo esc_html($button['name'])?></div>
                        <div class="price <?php echo esc_html($button['price_class'].'_'.get_the_ID())?>" id="price_<?php echo esc_html($id)?>"><?php echo esc_html($button['currency'].'&nbsp;'.$button['price']); ?></div>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
    <div class="consult_buttons info_btn_wrapper">
        <a href="#info_content" class="fancybox link">
            <span class="icon-info"></span>
            <span class="link_name"><?php echo esc_html__('More info about the consultation types', 'doctor2go-connect')?></span>
        </a>
    </div>
    <?php foreach ($consult_buttons as $id => $button){ ?>
        <?php if($id != 'show_doc' && $id != 'walk_in'){?>
            <div class="simple_hide" id="<?php echo esc_html($id.'_message_'.$post_id)?>" style="max-width:400px;">
                <h3 class="mb-m error center"><?php echo esc_html__('To start your choosen consult, you first need to login or register an account.', 'doctor2go-connect')?></h3>
                <div class="btn_wrapper center">
                    <a class="btn btn-default button" href="<?php echo esc_url($button['link_login'])?>"><?php echo esc_html__('login', 'doctor2go-connect')?></a>&nbsp;&nbsp;&nbsp;
                    <a class="btn btn-default button" href="<?php echo esc_url($button['link_regis'])?>"><?php echo esc_html__('register', 'doctor2go-connect')?></a>
                </div>
            </div>
        <?php } ?>
    <?php }  
}

function d2g_footer_html() {
    ?>
    <div class="simple_hide" id="info_content" style="max-width: 800px;">
        <h2><?php echo esc_html__('Consultation types', 'doctor2go-connect')?></h2>
        <div class="consult_info_wrapper">
            <div class="consult_info">
                <h3><?php echo esc_html__('Walk-in Consultation', 'doctor2go-connect')?></h3>
                <p><?php echo esc_html__('A walk-in consultation allows you to have a real-time video consultation with the doctor without a prior appointment. You will enter a virtual waiting room and the doctor will attend to you as soon as they are available.', 'doctor2go-connect')?></p>
            </div>
            <div class="consult_info">
                <h3><?php echo esc_html__('E-mail advice', 'doctor2go-connect')?></h3>
                <p><?php echo esc_html__('An email advice allows you to receive a professional assessment from a certified dermatologist via email within two working days. You will complete a questionnaire describing your skin concern, and the doctor will provide their evaluation and recommendations in writing.', 'doctor2go-connect')?></p>
            </div>
            <div class="consult_info">
                <h3><?php echo esc_html__('Physical Consultation', 'doctor2go-connect')?></h3>
                <p><?php echo esc_html__('A physical consultation involves an in-person visit to the doctor\'s clinic or designated location. You will have the opportunity to discuss your skin concerns face-to-face and receive a thorough examination and treatment plan.', 'doctor2go-connect')?></p>
            </div>
            <div class="consult_info">
                <h3><?php echo esc_html__('Video Consultation', 'doctor2go-connect')?></h3>
                <p><?php echo esc_html__('A video consultation enables you to have a remote appointment with the doctor via a secure video platform. This option provides convenience and flexibility, allowing you to discuss your skin concerns from the comfort of your own home.', 'doctor2go-connect')?></p>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'd2g_footer_html' );

/**
 * @param $type
 * @param $user_email
 * @param $user_name
 * @param $admin_email
 * @param string $code
 * @param string $link
 * @param array $extraData
 * @return string
 */
function d2g_user_email($type, $user_email, $user_name){
    $args = array(
        'post_type'     => 'd2g_emails',
        'meta_query'    => array(
            array(
                'key'      => 'd2g_email_identifier',
                'value'    => $type
            ),
        ),
    );
    $emailData = get_posts($args);

    //placeholder replacements general + DE
    $title          = str_replace('%to_name%', $user_name, $emailData[0]->post_title);
    $content        = str_replace('%to_name%', $user_name, $emailData[0]->post_content);
    $content        = str_replace('%email%', $user_email, $content);

    $msg =  d2g_html_email($content);

    
    //set header for confirmation mail (visitor / patient) and send mail
    $headers = 'From: '.get_option('d2g_sender_name').' <'.get_option('d2g_sender_address').'>' . "\r\n";
    wp_mail($user_email, $title, $msg, $headers);
    
    //set headers for admin notification mail and send mail
    $headers = 'From: '. $user_name .' <'.$user_email.'>' . "\r\n";
    wp_mail(get_option('d2g_recipient_address'), $title, $msg, $headers);



    return 'mail send';

}

function d2g_html_email($content){
    $feat_pic 		= wp_get_attachment_image_src(get_option('d2g_logo'), 'full')[0];
    $msg = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html;UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            </head>
            <body style="margin: 0px; background-color: #F4F3F4; font-family: Helvetica, Arial, sans-serif; font-size:12px;" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" topmargin="0" marginwidth="0" leftmargin="0">
            <table class="container" width="100%" style="max-width: 550px;" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                <tbody>
                <tr>
                    <td style="padding: 15px;"><center>
                    <table width="100%" style="max-width: 550px;" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                        <tbody>
                        <tr>
                            <td align="left">
                            <div style="border: solid 1px #d9d9d9;">
                                <table id="header" style="line-height: 1.6; font-size: 12px; font-family: Helvetica, Arial, sans-serif; color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                    <tr>
                                        <td style="color: #ffffff;" colspan="2" valign="bottom" height="30">.</td>
                                    </tr>
                                    <tr>
                                        <td style="line-height: 32px;  text-align: center; padding-left: 30px;" colspan="2" valign="baseline">
                                            <span style="font-size: 32px;">
                                                <a style="text-decoration: none;" href="'.str_replace('/wp', '', get_site_url()).'" target="_blank" rel="noopener">
                                                    <img style="max-width:250px;" src="'.$feat_pic.'">
                                                </a>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                                <table id="content" style="margin-top: 15px; margin-right: 30px; margin-left: 30px; color: #444; line-height: 1.6; font-size: 12px; font-family: Arial, sans-serif;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                <tbody>
                                    <tr>
                                    <td style="border-top: solid 1px #d9d9d9;" colspan="2">
                                        <div style="padding: 15px 0;">'.$content.'</div>
                                    </td>
                                    </tr>
                                </tbody>
                                </table>
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </center></td>
                </tr>
                </tbody>
            </table>
            </body>
        </html>';

        return $msg;
}


/**
 * Programmatically logs a user in
 *
 * @param string $username
 * @return bool True if the login was successful; false if it wasn't
 */
function programmatic_login( $username ) {
    
    if ( is_user_logged_in() ) {
        wp_logout();
    }

    add_filter( 'authenticate', 'allow_programmatic_login', 1, 3 );    // hook in earlier than other callbacks to short-circuit them
    add_filter( 'wordfence_ls_require_captcha', '__return_false' );
    $user = wp_signon( array( 'user_login' => $username ) );
    
    remove_filter( 'authenticate', 'allow_programmatic_login', 1, 3 );

    if ( is_a( $user, 'WP_User' ) ) {
        wp_set_current_user( $user->ID, $user->user_login );

        if ( is_user_logged_in() ) {
            return true;
        }
    }

    return false;
}

// ✅ reCAPTCHA validation for login form   
if ( get_option('d2g_recaptcha_site_key') ) {
    add_filter('wp_authenticate_user', function ( $user ) {

        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

        // Only run for POST requests
        if ( $request_method === 'POST' ) {

            $recaptcha_secret_key = get_option('d2g_recaptcha_secret_key');

            // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

            // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

            // Verify reCAPTCHA response with Google
            $response = wp_remote_post(
                'https://www.google.com/recaptcha/api/siteverify',
                array(
                    'body' => array(
                        'secret'   => $recaptcha_secret_key,
                        'response' => $recaptcha_response,
                        'remoteip' => $remote_ip,
                    ),
                )
            );

            $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( empty( $response_body['success'] ) || ! $response_body['success'] ) {
                return new WP_Error(
                    'recaptcha_failed',
                    __( 'reCAPTCHA verification failed. Please try again.', 'doctor2go-connect' )
                );
            }
        }

        return $user;
    } );
}




/**
 * An 'authenticate' filter callback that authenticates the user using only     the username.
 *
 * To avoid potential security vulnerabilities, this should only be used in     the context of a programmatic login,
 * and unhooked immediately after it fires.
 *
 * @param WP_User $user
 * @param string $username
 * @param string $password
 * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
 */
function allow_programmatic_login( $user, $username, $password ) {
    return get_user_by( 'login', $username );
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 * @param Array   $cats     taxonomy term objects to sort
 * @param integer $parentId the current parent ID to put them in
 */
function sort_terms_hierarchicaly(Array $cats, $parentId = 0)
{
    $into = [];
    foreach ($cats as $i => $cat) {
        if ($cat->parent == $parentId) {
            $cat->children = sort_terms_hierarchicaly($cats, $cat->term_id);
            $into[$cat->term_id] = $cat;
        }
    }
    return $into;
}

// Start session in WordPress
function start_session() {
    if (!session_id()) {
        session_start();
    }
  
}


function save_user_timezone() {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    $timezone = isset($_POST['timezone']) ? sanitize_text_field(wp_unslash($_POST['timezone'])) : '';

    if ($timezone !== '') {
        // Save the timezone to the session
        $_SESSION['user_timezone'] = $timezone;

        // Return a success response
        wp_send_json_success([
            'message'  => 'Timezone saved successfully!',
            'timezone' => $timezone,
        ]);
    } else {
        wp_send_json_error(['message' => 'Timezone not provided.']);
    }

    wp_die(); // Terminate the AJAX request properly
}


function enqueue_timezone_script() {
    wp_enqueue_script('timezone-script', D2G_PLUGIN_URL . '/public/js/timezone.js', [], null, true);

    // Pass the AJAX URL to the script
    wp_localize_script('timezone-script', 'ajaxurl', array(admin_url('admin-ajax.php')));
}


// retrive user timezone in php
function get_user_timezone() {
    if (!session_id()) {
        session_start();
    }

    if (!empty($_SESSION['user_timezone'])) {
        return sanitize_text_field($_SESSION['user_timezone']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    }

    return '';
}

// creates an array for timezones
function d2g_timezones(){
    // time zones list from PHP
    $cont = '';
    $timezone_identifiers  = ($cont == NULL) ? DateTimeZone::listIdentifiers() : DateTimeZone::listIdentifiers();
    $continent = "";
    $i = "";
    $timezones = array();
    $phpTime = gmdate("Y-m-d H:i:s");

    

    foreach ($timezone_identifiers as $key => $value) {
        if (preg_match('/^(Europe|America|Asia|Antarctica|Arctic|Atlantic|Indian|Pacific)\//', $value)) {
            $ex = explode("/", $value); //obtain continent, city
            if ($continent != $ex[0]) {
                $i = $ex[0];
            }

            $timezone = new DateTimeZone($value); // Get default system timezone to create a new DateTimeZone object
            $offset = $timezone->getOffset(new \DateTime($phpTime));
            $offsetHours = round(abs($offset) / 3600);
            $offsetString = ($offset < 0 ? '-' : '+');
            if ($offsetHours == 1 or $offsetHours == -1) {
                $label = "Hour";
            } else {
                $label = "Hours";
            }

            $city = $ex[1];
            $continent = $ex[0];
            $c[$i][$value] = isset($ex[2]) ? $ex[1] . ' - ' . $ex[2] : $ex[1];
            $timezones[$i][$value] = $c[$i][$value] . " (" . $offsetString . $offsetHours . " " . $label . ")";
        }
    }

    return $timezones;
}

// Function to show liked posts for logged-in users
function get_liked_posts() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $liked_posts = get_user_meta($user_id, 'liked_posts', true);
        return $liked_posts ? $liked_posts : [];
    }

    return [];
}

//this adds some js based on some GET var in the URL
add_action('wp_footer', 'add_custom_js_to_footer');
function add_custom_js_to_footer() {
    $open      = isset($_GET['open']) ? sanitize_text_field( wp_unslash($_GET['open']) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $scroll_to = isset($_GET['scroll_to']) ? sanitize_text_field( wp_unslash($_GET['scroll_to']) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    ?>
    <script>
        jQuery(document).ready(function($){
            <?php if($open !== ''){ ?>
                setTimeout(function(){
                    $('#<?php echo esc_js($open); ?>').click();
                }, 800);
            <?php } ?>
            <?php if($scroll_to !== ''){ ?>
                setTimeout(function(){
                    $('body').scrollTo('#<?php echo esc_js($scroll_to); ?>', {duration: 'slow', offset: -120});
                }, 800);
            <?php } ?>
        });
    </script>
    <?php
}


// Hook into 'init' to register a new menu location
function myplugin_register_extra_menu_location() {
    register_nav_menus( array(
        'd2g-help-menu' => __( 'D2G patient menu (used on the appointments page for patients without an account).', 'doctor2go-connect' ),
    ) );
}
add_action( 'init', 'myplugin_register_extra_menu_location' );

// Enable shortcode processing in menu items
add_filter( 'wp_nav_menu_items', 'run_shortcodes_in_menu', 10, 2 );
function run_shortcodes_in_menu( $items, $args ) {
    return do_shortcode( $items );
}

//shortcode for user name
function d2g_user_name_shortcode() {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        //get user meta or other data as needed
        $meta = get_user_meta( $current_user->ID );
        return esc_html( $meta['first_name'][0].' '.$meta['last_name'][0] );
    } else {
        return '';
    }
}
add_shortcode( 'd2g_user_name', 'd2g_user_name_shortcode' );


//old ajax call in calendar loads the availability data from the D2G-software
function load_availability_data() {
    // Get doctor ID safely, suppress nonce warning
    $doc_id = isset($_POST['doc_id']) ? absint( wp_unslash( $_POST['doc_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

    if (!$doc_id) {
        wp_send_json_error(['message' => 'Invalid doctor ID']);
        wp_die();
    }

    $profileClass         = new D2G_ProfileData($doc_id);
    $doctor_meta          = get_post_meta($doc_id);
    $availabilityDataJson = $profileClass->d2g_get_availability_data($doctor_meta['user_key'][0]);
    $availabilityDataObj  = json_decode($availabilityDataJson);

    $walk_in_check        = '';
    $tariffStr            = '';
    $firstAvailibility    = '';
    $docSlotsArray        = array();

    if (isset($availabilityDataObj->availabilities)) {
        if (!isset($availabilityDataObj->availabilities->message) && count($availabilityDataObj->availabilities) > 0) {
            $docSlotsArray       = $availabilityDataObj->availabilities;
            $firstAvailibility   = $profileClass->get_first_avialibility($docSlotsArray);
            $docSlotsJson        = json_encode($docSlotsArray);
            $tariffs             = $profileClass->get_tariffs($docSlotsArray);
            $tariffStr           = get_tariff_string($tariffs);

            $walk_in_check       = ($availabilityDataObj->user_has_inloop == true && $availabilityDataObj->user_is_active) ? true : false;
        }
    }

    $availibily_data_set = array(
        'walkin_check'        => $walk_in_check ?: '',
        'tariffs'             => $tariffStr ?: '',
        'first_availibility'  => $firstAvailibility ?: '',
        'doc_slots'           => $docSlotsArray
    );

    wp_send_json_success($availibily_data_set);
    wp_die();
}


// Function to generate tariff string
function get_tariff_string($tariffs){
    
    $tariffStr = '';
    foreach($tariffs as $tariff => $currency){
        $tariffStr .= '<span class="tariff">'.$currency['payment_currency'].' '.$tariff.'</span>';
    }
     return $tariffStr;
}


// REST API endpoint to get doctor availabilities
add_action('rest_api_init', function () {
    register_rest_route('d2g-connect/v1', '/availabilities', [
        'methods'  => 'POST',
        'callback' => 'd2g_get_availabilities',
        'permission_callback' => '__return_true', // lock this down if needed
    ]);
});

// Callback function for the REST API endpoint (this is used in the doc overview page and other loops to load availability data)
//Important note: This function is similar to the load_availability_data() function used in the old ajax call in the calendar, but adapted for REST API usage.
// A wrapper ID #doctor_wrapper is needed around the doctor loop for this to work properly.
function d2g_get_availabilities( WP_REST_Request $request ) {

    // Get params from JS
    $docKey = sanitize_text_field( $request->get_param( 'doc_key' ) );
    $docId  = absint( $request->get_param( 'doc_id' ) );

    if ( empty( $docKey ) || empty( $docId ) ) {
        return new WP_REST_Response( [
            'error' => 'Missing doc_key or doc_id'
        ], 400 );
    }

    // Instantiate profile class
    $profileClass = new D2G_ProfileData( $docId );

    // Prepare handshake
    $unixTime = time();
    $superKey = get_option( 'wcc_token' );
    $myHash   = hash( 'sha256', $unixTime . '_' . $docKey . '_' . $superKey );

    $url = get_option( 'api_url_short' ) . 'doclisting/availabilities';

    $body = [
        'handshake' => [
            'time'  => (string) $unixTime,
            'token' => $docKey,
            'hash'  => $myHash,
            'type'  => 'user',
        ],
        'calendar' => 'super',
    ];

    $args = [
        'method'      => 'POST',
        'timeout'     => 30,
        'headers'     => [
            'Content-Type' => 'application/json',
        ],
        'body'        => wp_json_encode( $body ),
        'data_format' => 'body',
    ];

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        return new WP_REST_Response( [
            'error'   => 'API request failed',
            'message' => $response->get_error_message(),
        ], 500 );
    }

    $response_body = wp_remote_retrieve_body( $response );
    $availabilityDataObj = json_decode( $response_body );

    // -----------------------------
    // BUSINESS LOGIC FOR AVAILABILITY DATA
    // -----------------------------
    $walk_in_check     = '';
    $tariffStr         = '';
    $firstAvailibility = '';
    $docSlotsArray     = [];

    if ( isset( $availabilityDataObj->availabilities ) ) {

        if (
            ! isset( $availabilityDataObj->availabilities->message ) &&
            is_array( $availabilityDataObj->availabilities ) &&
            count( $availabilityDataObj->availabilities ) > 0
        ) {

            $docSlotsArray     = $availabilityDataObj->availabilities;
            $firstAvailibility = $profileClass->get_first_avialibility( $docSlotsArray );

            $tariffs   = $profileClass->get_tariffs( $docSlotsArray );
            $tariffStr = get_tariff_string( $tariffs );

            if (
                ! empty( $availabilityDataObj->user_has_inloop ) &&
                ! empty( $availabilityDataObj->user_is_active )
            ) {
                $walk_in_check = true;
            } else {
                $walk_in_check = false;
            }
        }
    }

    $availability_data_set = [
        'walkin_check'       => $walk_in_check ?: '',
        'tariffs'            => $tariffStr ?: '',
        'first_availibility' => $firstAvailibility ?: '',
        'doc_slots'          => $docSlotsArray,
    ];

    return new WP_REST_Response( [
        'success' => true,
        'data'    => $availability_data_set,
    ], 200 );
}

//sanitize and return POST data
function d2g_get_post_text( $key ) {
    return isset( $_POST[ $key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        : '';
}