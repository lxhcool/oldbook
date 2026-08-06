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
				esc_html__('No responses yet', 'oldbook'),
				esc_html__('One response', 'oldbook'),
				esc_html__('% responses', 'oldbook')
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
		<p class="no-comments"><?php esc_html_e('Comments are closed.', 'oldbook'); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
