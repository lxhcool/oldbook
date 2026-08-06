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
			<?php esc_html_e('Search for:', 'oldbook'); ?>
		</label>
		<input
			type="search"
			id="oldbook-search"
			class="search-field"
			placeholder="<?php echo esc_attr_x('Search the site', 'placeholder', 'oldbook'); ?>"
			value="<?php echo esc_attr(get_search_query()); ?>"
			name="s"
		>
	</div>
	<button type="submit" class="search-submit">
		<?php esc_html_e('Search', 'oldbook'); ?>
	</button>
</form>
