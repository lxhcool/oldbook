<?php
/**
 * The sidebar containing widget areas.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! is_active_sidebar('sidebar-1')) {
	return;
}
?>
<aside id="secondary" class="widget-area" aria-label="<?php esc_attr_e('Sidebar', 'oldbook'); ?>">
	<?php dynamic_sidebar('sidebar-1'); ?>
</aside>
