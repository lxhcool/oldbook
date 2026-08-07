<?php
/**
 * Template Name: 文章列表
 * Template Post Type: page
 *
 * @package oldbook
 */

get_header();

$articles = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'paged'          => max(1, get_query_var('paged')),
	)
);

$articles_title = get_query_var('oldbook_articles') ? __('文章', 'oldbook') : get_the_title();
$articles_title = $articles_title ? $articles_title : __('文章', 'oldbook');
?>

<main id="primary" class="oldbook-main oldbook-page oldbook-page--articles">
	<header class="page-header">
		<h1 class="page-title"><?php echo esc_html($articles_title); ?></h1>
		<p class="eyebrow"><?php esc_html_e('文章', 'oldbook'); ?></p>
	</header>

	<?php if ($articles->have_posts()) : ?>
		<div class="content-list content-list--grid">
			<?php while ($articles->have_posts()) : $articles->the_post(); ?>
				<?php get_template_part('template-parts/content', get_post_type()); ?>
			<?php endwhile; ?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'total'     => $articles->max_num_pages,
				'current'   => max(1, get_query_var('paged')),
				'prev_text' => __('上一页', 'oldbook'),
				'next_text' => __('下一页', 'oldbook'),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</main>

<?php
get_sidebar();
get_footer();
