<?php
/**
 * Doctor Single Content Template
 *
 * This template can be overridden by copying it to yourtheme/d2g-connect/content-single-d2g_doctor.php.
 *
 * HOWEVER, on occasion d2g-connect will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     https://plugin.doctor2go.online/docs/template-structure/
 * @author  Webcamconsult
 * @package d2g-connect
 * @since   1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $d2g_profile_data;
//
// patient data
//
$patient        = wp_get_current_user();
$patient_meta   = get_user_meta( $patient->data->ID );
$location_check = $d2g_profile_data->doctor_meta['locations_to_go'];
$post_ID		 = $d2g_profile_data->doctor_profile_ID;

?>

<article id="doctor_wrapper_v3" class="doctor_detail_v3 type-d2g_doctor  doc_details">
	<div id="content_wrapper">
		<div class="top mb-<?php echo (wpmd_is_notphone()) ? '5' : '2'; ?> <?= esc_attr(apply_filters('bootscore/class/container', 'container', 'single')); ?>">
            <div id="top_content" class="site-content card ">
                <div class="card-body">
                    <?php if(wpmd_is_phone()){ ?>
                        <header>
                            <h1 class="entry-title text-primary"><?php the_title(); ?> <?php do_action('d2g_like_button', $post_ID);?></h1>
                            <?php if ( $d2g_profile_data->specialties !== false ) { ?>
                                <h2 class="specialties text-primary">
                                    <?php foreach ($d2g_profile_data->specialties as $specialty){ ?>
                                        <span><?php echo esc_html($specialty->name); ?></span>
                                    <?php } ?>
                                </h2>
                            <?php } ?>
                        </header>
                    <?php } ?>
                    <div class="<?php echo (wpmd_is_phone()) ? 'd-flex flex-row' : ''; ?><?php echo (wpmd_is_notphone()) ? 'row' : ''; ?>">
                        <div class="col-sm-3">
                            <figure><img class="profile_pic" src="<?php echo esc_html( $d2g_profile_data->feat_pic_full ); ?><?php echo (wpmd_is_notphone()) ? '&w=230&h=230&fit=crop&crop=faces' : '&w=120&h=120&fit=crop&crop=faces'; ?>" alt="<?php the_title(); ?>"></figure>
                        </div>
                        <div class="col-sm-9" id="intro">
                            <div class="inner_wrapper">
                                <?php do_action('d2g_like_button', $post_ID);?>
                                <?php if(wpmd_is_notphone()){ ?>
                                    <header>
                                        <h1 class="entry-title text-primary"><?php the_title(); ?></h1>
                                        <?php if ( $d2g_profile_data->specialties !== false ) { ?>
                                            <h2 class="specialties text-primary">
                                                <?php foreach ($d2g_profile_data->specialties as $specialty){ ?>
                                                    <span><?php echo esc_html($specialty->name); ?></span>
                                                <?php } ?>
                                            </h2>
                                        <?php } ?>
                                    </header>
                                    
                                <?php } ?>
                                <div id="short_info">
                                    <?php do_action( 'd2g_info_box', 'detail', 'col-2', '', 'top' ); ?>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <h3 class="mt-4 mb-3 opener text-primary"><?php echo esc_html__( 'About this doctor', 'doctor2go-connect' ); ?><span class="icon d2cif-Arrow-small-down"></span></h3>
                    <div class="simple_hide">
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div id="bio" class="mb-5">
                                    <?php the_content() ?>
                                </div>
                                <div id="exp_edu"><?php do_action( 'd2g_doctor_extended_info' ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="entry-wrapper"> 
			<div id="main_content" class="site-content <?php echo esc_attr(apply_filters('bootscore/class/container', 'container', 'single')); ?> <?php echo esc_attr(apply_filters('bootscore/class/content/spacer', 'pt-3 pb-5', 'single')); ?>">
				<!--ACTION HOOK CONSULTANCY TABS-->
				<?php do_action( 'd2g_doctor_consultancy_tabs' ); ?>  
			</div>
		</div>  
	</div>
</article>
<?php
$d2g_physician_schema = function_exists( 'd2gc_get_doctor_physician_schema' ) ? d2gc_get_doctor_physician_schema( $d2g_profile_data ) : false;
if ( $d2g_physician_schema ) :
    ?>
    <script type="application/ld+json">
        <?php
        echo wp_json_encode(
            array_merge( array( '@context' => 'https://schema.org' ), $d2g_physician_schema ),
            JSON_UNESCAPED_SLASHES
        );
        ?>
    </script>
<?php endif; ?>