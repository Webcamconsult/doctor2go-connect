<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	// list view template file
	// retrive all profile data
	global $d2g_profile_data;
	$content = get_the_content();
	$post_id = get_the_ID();
?>
<article data-dockey="<?php echo esc_html( $d2g_profile_data->doctor_meta['user_key'][0] ); ?>" data-postid="<?php echo esc_html( $post_id ); ?>" data-template="list" class="d2g_doctor  <?php echo esc_html( d2gc_getArticleClass() ); ?> list col-sm-12" id="doc_<?php echo esc_html( $post_id ); ?>">
	<div class="inner_wrapper card p-3 mb-5">
		<div class="row align-items-center">
			<div class="col-sm-4 text-center">
				<a href="<?php echo esc_html( get_the_permalink() ); ?>">
					<figure><img style="width:100%" src="<?php echo esc_html( $d2g_profile_data->feat_pic_square ); ?>" alt="<?php the_title(); ?>"></figure>
				</a>
				<header>
					<a href="<?php echo esc_html( get_the_permalink() ); ?>?>"><h3 class="entry_title"><?php the_title(); ?></h3></a>
					<?php if ( $specialties !== false ) { ?>
						<h4 class="specialties">
							<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
								<span><?php echo esc_html( $specialty->name ); ?></span>
							<?php } ?>
						</h4>
					<?php } ?>
				</header>
			</div>
			<div class="col-sm-8 info_wrapper">
				<div class="inner_wrapper">
					<div class="mb-3"><?php do_action( 'd2g_info_box', 'overview', 'col-1' ); ?></div>
					<a class="btn btn-primary w-100" href="<?php echo esc_html(get_the_permalink())?>"><?php esc_html_e('start a consult', 'doctor2go-connect')?></a>
				</div>
			</div>
		</div>
	</div>
</article>

