<?php
/**
 * The comments template.
 *
 * @package oldbook
 */

if (post_password_required()) {
	return;
}
?>

<section id="comments" class="comments-area">
	<?php if (have_comments()) : ?>
		<h2 class="comments-title">
			<?php
			comments_number(
				esc_html__('还没有评论', 'oldbook'),
				esc_html__('1 条评论', 'oldbook'),
				esc_html__('% 条评论', 'oldbook')
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if (! comments_open() && get_comments_number()) : ?>
		<p class="no-comments"><?php esc_html_e('评论已关闭。', 'oldbook'); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
