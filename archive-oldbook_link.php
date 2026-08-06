<?php
/**
 * Bookmark archive.
 *
 * @package oldbook
 */

get_header();
?>

<main id="primary" class="oldbook-main oldbook-page oldbook-page--links">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e('目录', 'oldbook'); ?></p>
		<h1 class="page-title"><?php esc_html_e('书签', 'oldbook'); ?></h1>
		<p class="archive-description"><?php esc_html_e('值得再次访问的网页，也收录想一直关注的人。', 'oldbook'); ?></p>
	</header>

	<?php foreach (oldbook_get_link_groups() as $group => $label) : ?>
		<?php
		$links = get_posts(
			array(
				'post_type'      => 'oldbook_link',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_key'       => '_oldbook_link_group',
				'meta_value'     => $group,
			)
		);
		?>
		<section class="oldbook-links-group">
			<header class="oldbook-section-heading">
				<h2><?php echo esc_html($label); ?></h2>
				<span><?php echo esc_html(number_format_i18n(count($links))); ?></span>
			</header>
			<?php if ($links) : ?>
				<div class="oldbook-links-list">
					<?php foreach ($links as $link) : ?>
						<?php
						global $post;
						$post = $link;
						setup_postdata($post);
						get_template_part('template-parts/content', 'oldbook-link');
						?>
					<?php endforeach; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="oldbook-empty-inline"><?php esc_html_e('这里还没有链接。', 'oldbook'); ?></p>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
</main>

<?php
get_sidebar();
get_footer();
