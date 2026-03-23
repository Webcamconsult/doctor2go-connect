<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }
    //grid view template file
    //retrive all profile data
    global $d2g_profile_data, $cssClass;
    $content            = get_the_content();
    $post_id            = get_the_ID();
?>
<article data-dockey="<?= $d2g_profile_data->doctor_meta['user_key'][0] ?>" data-postid="<?= $post_id?>" data-template='grid' class="d2g_doctor grid <?php echo esc_html(d2g_getArticleClass())?> <?php echo esc_html($cssClass)?> loading" id="doc_<?= $post_id?>">
    <div class="inner_wrapper card border-0 shadow pt-5 my-5 position-relative">
        <div class="card-body p4">
            <figure class="member-profile position-absolute w-100 text-center">
                <a href="<?php echo esc_html(get_the_permalink())?>">
                    <img class="rounded-circle mx-auto d-inline-block shadow-sm" src="<?php echo esc_html($d2g_profile_data->feat_pic_square) ?>" alt="<?php the_title() ?>">
                </a>
            </figure>
            <div class="entry_content card-text pt-1">
                <header>
                    <a href="<?php echo esc_html(get_the_permalink())?>?>">
                        <h3 class="member-name mb-2 text-center text-primary font-weight-bold"><?php the_title(); ?></h3>
                    </a>
                    <?php if($specialties !== false){ ?>
                        <h4 class="mb-4 text-center">
                            <?php foreach ($d2g_profile_data->specialties as $specialty){ ?>
                                <span><?php echo esc_html($specialty->name)?></span>
                            <?php } ?>
                        </h4>
                    <?php } ?>
                </header>
                <div class="inner_content mb-3">
                    <?php do_action( 'd2g_info_box', 'overview', 'col-1' ); ?>
                </div>
                <a class="btn btn-primary w-100" href="<?php echo esc_html(get_the_permalink())?>"><?php esc_html_e('start a consult', 'wcc-doclisting')?></a>
            </div>
        </div>
        <div class="card-footer theme-bg-primary border-0 text-center">
            <ul class="social-list list-inline mb-0 mx-auto">
                <li class="list-inline-item"><?php do_action('d2g_like_button', $post_id);?></li> 
            </ul><!--//social-list-->
        </div><!--//card-footer-->
    </div>
</article>