<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	// grid view template file
	// retrive all profile data
	global $d2g_profile_data, $cssClass;
	$post_id = get_the_ID();
?>
<article data-dockey="<?php echo esc_html( $d2g_profile_data->doctor_meta['user_key'][0] ); ?>" data-postid="<?php echo esc_html( $post_id ); ?>" data-template="grid" class="d2g_doctor <?php echo esc_html( d2g_getArticleClass() ); ?> grid <?php echo esc_html( $cssClass ); ?>" id="doc_<?php echo esc_html( $post_id ); ?>">
	<div class="inner_wrapper">
		<?php do_action( 'd2g_like_button', $post_id ); ?>
		<a href="<?php echo esc_html( get_the_permalink() ); ?>">
			<figure><img style="width:100%" src="<?php echo esc_html( $d2g_profile_data->feat_pic_square ); ?>" alt="<?php the_title(); ?>"></figure>
		</a>
		<div class="entry_content">
			<header>
				<a href="<?php echo esc_html( get_the_permalink() ); ?>?>">
					<h3 class="entry_title"><?php the_title(); ?></h3>
				</a>
				<?php if ( $d2g_profile_data->specialties !== false ) { ?>
					<h4 class="specialties">
						<?php foreach ( $d2g_profile_data->specialties as $specialty ) { ?>
							<span><?php echo esc_html( $specialty->name ); ?></span>
						<?php } ?>
					</h4>
				<?php } ?>
			</header>
			<div class="inner_content">
				<?php do_action( 'd2g_info_box', 'overview', 'col-1' ); ?>
				<?php do_action( 'd2g_consult_buttons', 'overview', 'small' ); ?>
			</div>
		</div>
	</div>
</article>

