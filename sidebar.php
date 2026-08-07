<?php
/**
 * The right rail: WordPress widget area.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

?>

		<?php if (is_active_sidebar('sidebar-1')) : ?>
			<aside class="oldbook-sidebar" aria-label="<?php esc_attr_e('侧栏', 'oldbook'); ?>">
				<?php dynamic_sidebar('sidebar-1'); ?>
			</aside>
		<?php endif; ?>
