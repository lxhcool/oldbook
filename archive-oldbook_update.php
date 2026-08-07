<?php
/**
 * Dynamics archive.
 *
 * @package oldbook
 */

get_header();
?>

<main id="primary" class="oldbook-main oldbook-page oldbook-page--dynamics">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e('动态', 'oldbook'); ?></h1>
		<p class="eyebrow"><?php esc_html_e('记录文字、声音、影像和日常片段', 'oldbook'); ?></p>
	</header>

	<?php if (have_posts()) : ?>
		<div class="oldbook-update-list">
			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('template-parts/content', 'oldbook-update'); ?>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination(array('prev_text' => __('上一页', 'oldbook'), 'next_text' => __('下一页', 'oldbook'))); ?>
	<?php else : ?>
		<section class="oldbook-empty-state">
			<h2><?php esc_html_e('还没有动态。', 'oldbook'); ?></h2>
			<p><?php esc_html_e('这里还没有内容，等你留下第一条记录。', 'oldbook'); ?></p>
		</section>
	<?php endif; ?>
</main>

<?php
get_sidebar();
get_footer();
