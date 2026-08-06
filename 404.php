<?php
/**
 * The 404 template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<section class="not-found">
		<p class="eyebrow"><?php esc_html_e('页面不存在', 'oldbook'); ?></p>
		<h1 class="page-title"><?php esc_html_e('这一页暂时找不到。', 'oldbook'); ?></h1>
		<p><?php esc_html_e('可以搜索站内内容，或返回首页。', 'oldbook'); ?></p>
		<?php get_search_form(); ?>
		<a class="button-link" href="<?php echo esc_url(home_url('/')); ?>">
			<?php esc_html_e('返回首页', 'oldbook'); ?>
		</a>
	</section>
</div>

<?php
get_sidebar();
get_footer();
