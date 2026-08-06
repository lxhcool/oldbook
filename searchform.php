<?php
/**
 * Search form.
 *
 * @package oldbook
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
	<div class="search-form__field">
		<label class="screen-reader-text" for="oldbook-search">
			<?php esc_html_e('搜索内容：', 'oldbook'); ?>
		</label>
		<input
			type="search"
			id="oldbook-search"
			class="search-field"
			placeholder="<?php echo esc_attr_x('搜索站内内容', 'placeholder', 'oldbook'); ?>"
			value="<?php echo esc_attr(get_search_query()); ?>"
			name="s"
		>
	</div>
	<button type="submit" class="search-submit">
		<?php echo oldbook_icon('search'); ?>
		<span><?php esc_html_e('搜索', 'oldbook'); ?></span>
	</button>
</form>
