<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }
    //grid view template file
    //retrive all profile data
    global $d2g_profile_data, $cssClass;
    $content            = get_the_content();
    $post_id            = get_the_ID();
    $feat_pic_full      = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), 'full')[0];
    $currLang           = explode('_', get_locale())[0];
    //nice_dump($d2g_profile_data);
?>
<article data-dockey="<?= $d2g_profile_data->doctor_meta['user_key'][0] ?>" data-postid="<?= $post_id?>" data-template='grid' class="d2g_doctor  <?php echo esc_html(d2g_getArticleClass())?> grid2 <?php echo esc_html($cssClass)?>" id="doc_<?= $post_id?>">
    <div class="inner_wrapper card">
        <div class="row">
            <div class="image_wrapper col-sm-4">
                <figure>
                    <?php do_action('d2g_like_button', $post_id);?>
                    <a href="<?php echo esc_html(get_the_permalink())?>">
                        <img style="width:100%" src="<?php echo esc_html($d2g_profile_data->feat_pic_square) ?>" alt="<?php the_title() ?>">
                    </a>
                </figure>
            </div>
            <div class="info_wrapper col-sm-8">
				<div class="inner_wrapper p-3">
					<header>
						<a href="<?php echo esc_html(get_the_permalink())?>">
							<h3 class="entry_title"><?php the_title(); ?></h3>
						</a>
						<?php if($specialties !== false){ ?>
							<p class="specialties">
								<strong>
									<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
										<span><?php echo esc_html( $specialty->name ); ?></span>
									<?php } ?>
								</strong>
							</p>
						<?php } ?>
					</header>
					<div class="inner_content mb-3">
						<?php do_action( 'd2g_info_box', 'overview', 'col-1' ); ?>
					</div>
					<a class="btn btn-outline-primary w-100" href="<?php echo esc_html(get_the_permalink())?>"><?php esc_html_e('start a consult', 'wcc-doclisting')?></a>
				</div>
            </div>
        </div>
    </div>
</article>