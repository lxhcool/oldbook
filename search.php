<?php
/**
 * The search results template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<header class="page-header">
		<h1 class="page-title">
			<?php
			printf(
				esc_html__('搜索“%s”的结果', 'oldbook'),
				'<span>' . esc_html(get_search_query()) . '</span>'
			);
			?>
		</h1>
		<p class="eyebrow"><?php esc_html_e('搜索', 'oldbook'); ?></p>
	</header>

	<?php if (have_posts()) : ?>
		<div class="content-list">
			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('template-parts/content', get_post_type()); ?>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_navigation(
			array(
				'prev_text' => __('更早内容', 'oldbook'),
				'next_text' => __('更新内容', 'oldbook'),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
</div>

<?php
get_sidebar();
get_footer();
