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
<article data-dockey="<?php echo esc_html( $d2g_profile_data->doctor_meta['user_key'][0] ); ?>" data-postid="<?php echo esc_html( $post_id ); ?>" data-template="list" class="d2g_doctor  <?php echo esc_html( d2g_getArticleClass() ); ?> list col-sm-12" id="doc_<?php echo esc_html( $post_id ); ?>">
	<div class="inner_wrapper">
		<div class="row">
			<div class="col-sm-4">
				<a href="<?php echo esc_html( get_the_permalink() ); ?>">
					<figure><img style="width:100%" src="<?php echo esc_html( $d2g_profile_data->feat_pic ); ?>" alt="<?php the_title(); ?>"></figure>
				</a>
			</div>
			<div class="col-sm-8">
				<div class="entry_content">
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
					<div class="inner_content">
						<div class="promo"><?php echo esc_html( d2g_ttruncat( $content, 400 ) ); ?></div>
						<?php do_action( 'd2g_info_box', 'overview', 'col-2' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'd2g_consult_buttons', 'overview', 'small' ); ?>
	</div>
</article>

