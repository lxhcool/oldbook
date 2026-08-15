<?php
/**
 * Independent oldbook management console.
 *
 * Theme settings, updates, and links are registered as sibling WordPress
 * admin pages while sharing the same console shell and visual language.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_register_admin_pages() {
	add_menu_page(
		__('浮光主题设置', 'oldbook'),
		__('浮光主题设置', 'oldbook'),
		'manage_options',
		'oldbook',
		'oldbook_render_settings_page',
		'dashicons-admin-customizer',
		25
	);
	add_menu_page(
		__('动态', 'oldbook'),
		__('动态', 'oldbook'),
		'edit_posts',
		'oldbook-updates',
		'oldbook_render_updates_page',
		'dashicons-format-status',
		26
	);
	add_menu_page(
		__('链接', 'oldbook'),
		__('链接', 'oldbook'),
		'edit_posts',
		'oldbook-links',
		'oldbook_render_links_page',
		'dashicons-admin-links',
		27
	);
}
add_action('admin_menu', 'oldbook_register_admin_pages');

function oldbook_is_admin_console_page($page = '') {
	if (! $page && isset($_GET['page'])) {
		$page = sanitize_key(wp_unslash($_GET['page']));
	}

	return in_array($page, array('oldbook', 'oldbook-updates', 'oldbook-links'), true);
}

function oldbook_enqueue_admin_assets($hook_suffix) {
	$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if (! oldbook_is_admin_console_page($page)) {
		return;
	}

	$theme = wp_get_theme();
	$tab   = oldbook_get_admin_tab();
	$admin_css_path = get_template_directory() . '/assets/css/admin.css';
	$admin_js_path  = get_template_directory() . '/assets/js/admin.js';
	$admin_css_ver  = file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : $theme->get('Version');
	$admin_js_ver   = file_exists($admin_js_path) ? (string) filemtime($admin_js_path) : $theme->get('Version');

	$iconfont_path = get_template_directory() . '/assets/iconfont/iconfont.js';
	wp_enqueue_script(
		'oldbook-iconfont',
		get_template_directory_uri() . '/assets/iconfont/iconfont.js',
		array(),
		file_exists($iconfont_path) ? (string) filemtime($iconfont_path) : $theme->get('Version'),
		true
	);

	$iconfont_fix_path = get_template_directory() . '/assets/js/oldbook-iconfont.js';
	wp_enqueue_script(
		'oldbook-iconfont-fix',
		get_template_directory_uri() . '/assets/js/oldbook-iconfont.js',
		array('oldbook-iconfont'),
		file_exists($iconfont_fix_path) ? (string) filemtime($iconfont_fix_path) : $theme->get('Version'),
		true
	);

	wp_enqueue_style(
		'oldbook-admin',
		get_template_directory_uri() . '/assets/css/admin.css',
		array(),
		$admin_css_ver
	);
	wp_enqueue_script(
		'oldbook-admin',
		get_template_directory_uri() . '/assets/js/admin.js',
		array(),
		$admin_js_ver,
		true
	);

	if (in_array($tab, array('publish', 'add-link'), true)) {
		wp_enqueue_media();
	}
}
add_action('admin_enqueue_scripts', 'oldbook_enqueue_admin_assets');

function oldbook_admin_body_class($classes) {
	$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if (oldbook_is_admin_console_page($page)) {
		$classes .= ' oldbook-admin-body';
	}

	return $classes;
}
add_filter('admin_body_class', 'oldbook_admin_body_class');

function oldbook_admin_tabs() {
	return array(
		'settings' => array(
			'label' => __('站点设置', 'oldbook'),
			'icon'  => 'settings',
			'cap'   => 'manage_options',
		),
		'updates' => array(
			'label' => __('动态', 'oldbook'),
			'icon'  => 'activity',
			'cap'   => 'edit_posts',
		),
		'publish' => array(
			'label' => __('发布动态', 'oldbook'),
			'icon'  => 'edit',
			'cap'   => 'edit_posts',
		),
		'links' => array(
			'label' => __('链接', 'oldbook'),
			'icon'  => 'link',
			'cap'   => 'edit_posts',
		),
		'add-link' => array(
			'label' => __('添加链接', 'oldbook'),
			'icon'  => 'plus',
			'cap'   => 'edit_posts',
		),
	);
}

function oldbook_admin_page_for_tab($tab) {
	$page_map = array(
		'settings' => 'oldbook',
		'updates'  => 'oldbook-updates',
		'publish'  => 'oldbook-updates',
		'links'    => 'oldbook-links',
		'add-link' => 'oldbook-links',
	);

	return isset($page_map[$tab]) ? $page_map[$tab] : 'oldbook';
}

function oldbook_get_admin_tab() {
	$tabs        = oldbook_admin_tabs();
	$page        = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'oldbook';
	$defaults    = array(
		'oldbook'         => current_user_can('manage_options') ? 'settings' : 'updates',
		'oldbook-updates' => 'updates',
		'oldbook-links'   => 'links',
	);
	$allowed_tabs = array(
		'oldbook'         => array('settings', 'updates', 'publish', 'links', 'add-link'),
		'oldbook-updates' => array('updates', 'publish'),
		'oldbook-links'   => array('links', 'add-link'),
	);
	$default_tab = isset($defaults[$page]) ? $defaults[$page] : 'settings';
	$tab         = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : $default_tab;

	return isset($tabs[$tab]) && isset($allowed_tabs[$page]) && in_array($tab, $allowed_tabs[$page], true) ? $tab : $default_tab;
}

function oldbook_admin_page_url($tab, $args = array()) {
	$tabs = oldbook_admin_tabs();

	if (! isset($tabs[$tab])) {
		$tab = 'settings';
	}

	$args['page'] = oldbook_admin_page_for_tab($tab);

	if (in_array($tab, array('settings', 'updates', 'links'), true)) {
		unset($args['tab']);
	} else {
		$args['tab'] = $tab;
	}

	return add_query_arg($args, admin_url('admin.php'));
}

function oldbook_admin_redirect($tab, $args = array()) {
	wp_safe_redirect(oldbook_admin_page_url($tab, $args));
	exit;
}

function oldbook_admin_notice() {
	$notice = isset($_GET['oldbook_notice']) ? sanitize_key(wp_unslash($_GET['oldbook_notice'])) : '';
	$error  = isset($_GET['oldbook_error']) ? sanitize_text_field(wp_unslash($_GET['oldbook_error'])) : '';

	$messages = array(
		'saved'   => __('已保存。', 'oldbook'),
		'deleted' => __('已删除。', 'oldbook'),
	);

	if ($notice && isset($messages[$notice])) {
		printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html($messages[$notice]));
	}

	if ($error) {
		printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($error));
	}
}

function oldbook_render_admin_heading($title, $description = '', $action = '', $class = '') {
	?>
	<header class="oldbook-console__heading<?php echo $class ? ' ' . esc_attr($class) : ''; ?>">
		<div class="oldbook-console__heading-text">
			<h2><?php echo esc_html($title); ?></h2>
			<?php if ($description) : ?>
				<p><?php echo esc_html($description); ?></p>
			<?php endif; ?>
		</div>
		<?php if ($action) : ?>
			<div class="oldbook-console__heading-action"><?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</header>
	<?php
}

function oldbook_render_settings_page() {
	oldbook_render_admin_page();
}

function oldbook_render_updates_page() {
	oldbook_render_admin_page();
}

function oldbook_render_links_page() {
	oldbook_render_admin_page();
}

function oldbook_render_settings_nav($current) {
	$tabs = oldbook_admin_tabs();
	?>
	<nav class="oldbook-console__nav" aria-label="<?php esc_attr_e('设置导航', 'oldbook'); ?>">
		<ul>
			<?php foreach ($tabs as $slug => $item) : ?>
				<?php
				if ('oldbook' !== oldbook_admin_page_for_tab($slug) || ! current_user_can($item['cap'])) {
					continue;
				}
				?>
				<li class="<?php echo $current === $slug ? 'is-current' : ''; ?>">
					<a href="<?php echo esc_url(oldbook_admin_page_url($slug)); ?>"<?php echo $current === $slug ? ' aria-current="page"' : ''; ?>>
						<span class="oldbook-console__nav-icon" aria-hidden="true"><?php echo oldbook_icon($item['icon']); ?></span>
						<span><?php echo esc_html($item['label']); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

function oldbook_render_admin_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理主题内容的权限。', 'oldbook'));
	}

	$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'oldbook';
	$tab = oldbook_get_admin_tab();
	$tabs = oldbook_admin_tabs();
	$is_settings_page = 'oldbook' === $page;

	if ('oldbook' === $page && 'settings' !== $tab) {
		oldbook_admin_redirect($tab);
	}

	if (! current_user_can($tabs[$tab]['cap'])) {
		wp_die(esc_html__('你没有访问这个页面的权限。', 'oldbook'));
	}

	$save_form = '';

	if ($is_settings_page) {
		if ('settings' === $tab) {
			$save_form = 'oldbook-settings-form';
		}
	}

	$site_title = oldbook_get_site_title();
	$logo_url   = oldbook_get_site_logo_url('full');
	$logo_height = oldbook_get_logo_height();
	?>
	<div class="oldbook-console">
		<header class="oldbook-console__header">
			<div class="oldbook-console__header-left">
				<div class="oldbook-console__brand" style="<?php echo esc_attr('--oldbook-admin-logo-height:' . $logo_height . 'px;'); ?>">
					<?php if ($logo_url) : ?>
						<img class="oldbook-console__brand-logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_title); ?>">
					<?php else : ?>
						<span class="oldbook-console__brand-fallback" aria-hidden="true"><?php echo oldbook_icon('system'); ?></span>
					<?php endif; ?>
				</div>
				<div class="oldbook-console__header-title">
					<?php echo esc_html($tabs[$tab]['label']); ?>
				</div>
			</div>
			<div class="oldbook-console__header-actions">
				<?php if ($save_form) : ?>
					<button class="oldbook-console__save button button-primary" type="submit" form="<?php echo esc_attr($save_form); ?>">
						<?php esc_html_e('保存设置', 'oldbook'); ?>
					</button>
				<?php endif; ?>
				<a class="oldbook-console__site-link" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e('查看站点', 'oldbook'); ?></span>
					<span class="oldbook-console__site-link-icon" aria-hidden="true"><?php echo oldbook_icon('external'); ?></span>
				</a>
			</div>
		</header>

		<div class="oldbook-console__layout<?php echo $is_settings_page ? '' : ' oldbook-console__layout--independent'; ?>">
			<?php if ($is_settings_page) : ?>
				<div class="oldbook-console__rail">
					<?php oldbook_render_settings_nav($tab); ?>
				</div>
			<?php endif; ?>

			<div class="oldbook-console__body">
				<?php oldbook_admin_notice(); ?>

					<?php
					switch ($tab) {
						case 'publish':
							oldbook_render_publish_tab();
							break;
						case 'links':
							oldbook_render_links_tab();
							break;
						case 'add-link':
							oldbook_render_add_link_tab();
							break;
						case 'settings':
							oldbook_render_settings_tab();
							break;
						case 'updates':
						default:
							oldbook_render_updates_tab();
							break;
					}
					?>
			</div>
		</div>
	</div>
	<?php
}

function oldbook_render_updates_tab() {
	$updates = get_posts(
		array(
			'post_type'      => 'oldbook_update',
			'post_status'    => array('publish', 'draft', 'pending', 'private'),
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	oldbook_render_admin_heading(
		__('动态', 'oldbook'),
		__('在一个专注的页面中发布文字、音乐、视频和图片动态。', 'oldbook'),
		sprintf(
			'<a class="button button-primary" href="%s">%s%s</a>',
			esc_url(oldbook_admin_page_url('publish')),
			oldbook_icon('plus'),
			esc_html__('发布动态', 'oldbook')
		)
	);
	?>
	<?php if ($updates) : ?>
		<table class="widefat fixed striped oldbook-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e('标题', 'oldbook'); ?></th>
					<th><?php esc_html_e('类型', 'oldbook'); ?></th>
					<th><?php esc_html_e('状态', 'oldbook'); ?></th>
					<th><?php esc_html_e('日期', 'oldbook'); ?></th>
					<th class="oldbook-admin-table__actions"><?php esc_html_e('操作', 'oldbook'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($updates as $update) : ?>
					<?php $type = oldbook_get_update_type($update->ID); ?>
					<tr>
						<td>
							<strong><?php echo esc_html(get_the_title($update)); ?></strong>
							<p class="oldbook-admin-table__preview"><?php echo esc_html(oldbook_get_update_preview($update->ID)); ?></p>
						</td>
						<td><span class="oldbook-admin-type"><span class="oldbook-admin-type__icon"><?php echo oldbook_icon(oldbook_get_update_types()[$type]['icon']); ?></span><?php echo esc_html(oldbook_get_update_type_label($type)); ?></span></td>
						<td><span class="oldbook-admin-status<?php echo 'publish' === $update->post_status ? ' is-published' : ''; ?>"><span class="oldbook-admin-status__dot" aria-hidden="true"></span><?php echo esc_html(get_post_status_object($update->post_status)->label); ?></span></td>
						<td><?php echo esc_html(get_the_date('Y-m-d H:i', $update)); ?></td>
						<td class="oldbook-admin-table__actions">
							<a class="button button-small" href="<?php echo esc_url(oldbook_admin_page_url('publish', array('post_id' => $update->ID))); ?>">
								<?php echo oldbook_icon('edit'); ?>
								<?php esc_html_e('编辑', 'oldbook'); ?>
							</a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<input type="hidden" name="action" value="oldbook_delete_content">
								<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($update->ID); ?>">
								<?php wp_nonce_field('oldbook_delete_content_' . $update->ID); ?>
								<button class="button button-small oldbook-admin-danger" type="submit">
									<?php echo oldbook_icon('trash'); ?>
									<?php esc_html_e('删除', 'oldbook'); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="oldbook-admin-empty">
			<span class="oldbook-admin-empty__icon" aria-hidden="true"><?php echo oldbook_icon('activity'); ?></span>
			<p><?php esc_html_e('还没有动态，发布第一条动态吧。', 'oldbook'); ?></p>
			<a class="button button-primary" href="<?php echo esc_url(oldbook_admin_page_url('publish')); ?>">
				<?php echo oldbook_icon('plus'); ?>
				<?php esc_html_e('发布动态', 'oldbook'); ?>
			</a>
		</div>
	<?php endif; ?>
	<?php
}

function oldbook_render_links_tab() {
	$links = get_posts(
		array(
			'post_type'      => 'oldbook_link',
			'post_status'    => array('publish', 'draft', 'pending', 'private'),
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	oldbook_render_admin_heading(
		__('链接', 'oldbook'),
		__('在一个轻量的目录中管理个人收藏和友链。', 'oldbook'),
		sprintf(
			'<a class="button button-primary" href="%s">%s%s</a>',
			esc_url(oldbook_admin_page_url('add-link')),
			oldbook_icon('plus'),
			esc_html__('添加链接', 'oldbook')
		)
	);
	?>
	<?php if ($links) : ?>
		<table class="widefat fixed striped oldbook-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e('网站', 'oldbook'); ?></th>
					<th><?php esc_html_e('分组', 'oldbook'); ?></th>
					<th><?php esc_html_e('网址', 'oldbook'); ?></th>
					<th><?php esc_html_e('日期', 'oldbook'); ?></th>
					<th class="oldbook-admin-table__actions"><?php esc_html_e('操作', 'oldbook'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($links as $link) : ?>
					<?php $group = oldbook_get_link_group($link->ID); ?>
					<tr>
						<td>
							<strong><?php echo esc_html(get_the_title($link)); ?></strong>
							<p class="oldbook-admin-table__preview"><?php echo esc_html(oldbook_get_link_description($link->ID)); ?></p>
						</td>
						<td><span class="oldbook-admin-type"><span class="oldbook-admin-type__icon"><?php echo oldbook_icon('link'); ?></span><?php echo esc_html(oldbook_get_link_groups()[$group]); ?></span></td>
						<td><a class="oldbook-admin-table__url" href="<?php echo esc_url(oldbook_get_link_url($link->ID)); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(oldbook_get_link_url($link->ID)); ?></a></td>
						<td><?php echo esc_html(get_the_date('Y-m-d H:i', $link)); ?></td>
						<td class="oldbook-admin-table__actions">
							<a class="button button-small" href="<?php echo esc_url(oldbook_admin_page_url('add-link', array('post_id' => $link->ID))); ?>">
								<?php echo oldbook_icon('edit'); ?>
								<?php esc_html_e('编辑', 'oldbook'); ?>
							</a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<input type="hidden" name="action" value="oldbook_delete_content">
								<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($link->ID); ?>">
								<?php wp_nonce_field('oldbook_delete_content_' . $link->ID); ?>
								<button class="button button-small oldbook-admin-danger" type="submit">
									<?php echo oldbook_icon('trash'); ?>
									<?php esc_html_e('删除', 'oldbook'); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="oldbook-admin-empty">
			<span class="oldbook-admin-empty__icon" aria-hidden="true"><?php echo oldbook_icon('link'); ?></span>
			<p><?php esc_html_e('还没有链接，添加第一个收藏吧。', 'oldbook'); ?></p>
			<a class="button button-primary" href="<?php echo esc_url(oldbook_admin_page_url('add-link')); ?>">
				<?php echo oldbook_icon('plus'); ?>
				<?php esc_html_e('添加链接', 'oldbook'); ?>
			</a>
		</div>
	<?php endif; ?>
	<?php
}

function oldbook_render_settings_tab() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('你没有管理站点设置的权限。', 'oldbook'));
	}

	$cover_settings  = oldbook_get_cover_settings();
	$logo_url        = oldbook_get_site_logo_url('full');
	$logo_height     = oldbook_get_logo_height();
	$cover_url       = oldbook_get_cover_image_url();
	$site_title      = oldbook_get_site_title();
	$signature       = (string) get_theme_mod('oldbook_profile_signature', '');
	$layout_settings = oldbook_get_layout_settings();
	$layout_key      = $layout_settings['show_left_sidebar']
		? ($layout_settings['show_right_sidebar']
			? ($layout_settings['mini_left_sidebar'] ? 'mini-left' : 'three')
			: 'left')
		: ($layout_settings['show_right_sidebar'] ? 'right' : 'single');
	$home_content    = oldbook_get_home_content();

	$settings_sections = array(
		'basic'     => array('label' => __('基本信息', 'oldbook'), 'href' => '#oldbook-section-basic'),
		'layout'    => array('label' => __('页面布局', 'oldbook'), 'href' => '#oldbook-section-layout'),
		'logo'      => array('label' => __('Logo 与标识', 'oldbook'), 'href' => '#oldbook-section-logo'),
		'cover'     => array('label' => __('封面与蒙层', 'oldbook'), 'href' => '#oldbook-section-cover'),
		'signature' => array('label' => __('个性签名', 'oldbook'), 'href' => '#oldbook-section-signature'),
	);

	oldbook_render_admin_heading(
		__('站点设置', 'oldbook'),
		__('管理站点的基本信息、页面布局、Logo、封面与个性签名。', 'oldbook'),
		'',
		'oldbook-console__heading--settings'
	);
	?>
	<nav class="oldbook-settings-tabs" aria-label="<?php esc_attr_e('站点设置分区', 'oldbook'); ?>">
		<ul>
			<?php foreach ($settings_sections as $slug => $section) : ?>
				<li>
					<a href="<?php echo esc_url($section['href']); ?>"<?php echo 'basic' === $slug ? ' class="is-current" aria-current="page"' : ''; ?>>
						<?php echo esc_html($section['label']); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<form id="oldbook-settings-form" class="oldbook-admin-form oldbook-settings-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="oldbook_save_settings">
		<?php wp_nonce_field('oldbook_save_settings'); ?>

		<section id="oldbook-section-basic" class="oldbook-settings-section">
			<header class="oldbook-settings-section__head">
				<h2><?php esc_html_e('基本信息', 'oldbook'); ?></h2>
				<p><?php esc_html_e('站点名称会显示在浏览器标签页和站点标识中。', 'oldbook'); ?></p>
			</header>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-site-title"><?php esc_html_e('站点标题', 'oldbook'); ?></label>
				</div>
				<div class="oldbook-settings-field__control">
					<input type="text" id="oldbook-site-title" name="oldbook_site_title" value="<?php echo esc_attr($site_title); ?>" maxlength="120" required>
				</div>
			</div>
		</section>

		<section id="oldbook-section-layout" class="oldbook-settings-section">
			<header class="oldbook-settings-section__head">
				<h2><?php esc_html_e('页面布局', 'oldbook'); ?></h2>
				<p><?php esc_html_e('决定站点内容区显示为几栏，更改后立即在站点中生效。', 'oldbook'); ?></p>
			</header>
			<input type="hidden" name="oldbook_show_left_sidebar" value="<?php echo $layout_settings['show_left_sidebar'] ? '1' : '0'; ?>" data-oldbook-picker-input="show-left-sidebar">
			<input type="hidden" name="oldbook_show_right_sidebar" value="<?php echo $layout_settings['show_right_sidebar'] ? '1' : '0'; ?>" data-oldbook-picker-input="show-right-sidebar">
			<input type="hidden" name="oldbook_mini_left_sidebar" value="<?php echo $layout_settings['mini_left_sidebar'] ? '1' : '0'; ?>" data-oldbook-picker-input="mini-left-sidebar">
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label><?php esc_html_e('布局样式', 'oldbook'); ?></label>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-layout-picker" role="radiogroup" aria-label="<?php esc_attr_e('页面布局', 'oldbook'); ?>">
						<?php
						$layout_options = array(
							'three'     => array('label' => __('三栏布局', 'oldbook'), 'left' => '1', 'right' => '1', 'mini' => '0'),
							'mini-left' => array('label' => __('迷你左栏', 'oldbook'), 'left' => '1', 'right' => '1', 'mini' => '1'),
							'left'      => array('label' => __('左侧布局', 'oldbook'), 'left' => '1', 'right' => '0', 'mini' => '0'),
							'right'     => array('label' => __('右侧布局', 'oldbook'), 'left' => '0', 'right' => '1', 'mini' => '0'),
							'single'    => array('label' => __('单栏布局', 'oldbook'), 'left' => '0', 'right' => '0', 'mini' => '0'),
						);
						foreach ($layout_options as $key => $option) :
							?>
							<button type="button" class="oldbook-layout-option<?php echo $layout_key === $key ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $layout_key === $key ? 'true' : 'false'; ?>" data-oldbook-layout-choice="<?php echo esc_attr($key); ?>" data-oldbook-layout-left="<?php echo esc_attr($option['left']); ?>" data-oldbook-layout-right="<?php echo esc_attr($option['right']); ?>" data-oldbook-layout-mini="<?php echo esc_attr($option['mini']); ?>">
								<span class="oldbook-layout-option__preview oldbook-layout-option__preview--<?php echo esc_attr($key); ?>" aria-hidden="true"><i></i><i></i><i></i></span>
								<strong class="oldbook-layout-option__copy"><?php echo esc_html($option['label']); ?></strong>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-home-content"><?php esc_html_e('首页内容', 'oldbook'); ?></label>
					<p><?php esc_html_e('首页内容流默认显示文章列表还是动态列表。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<select id="oldbook-home-content" name="oldbook_home_content">
						<option value="articles"<?php selected($home_content, 'articles'); ?>><?php esc_html_e('文章列表', 'oldbook'); ?></option>
						<option value="updates"<?php selected($home_content, 'updates'); ?>><?php esc_html_e('动态列表', 'oldbook'); ?></option>
					</select>
				</div>
			</div>
		</section>

		<section id="oldbook-section-logo" class="oldbook-settings-section">
			<header class="oldbook-settings-section__head">
				<h2><?php esc_html_e('Logo 与标识', 'oldbook'); ?></h2>
				<p><?php esc_html_e('Logo 显示在站点顶部标识和后台顶栏中。', 'oldbook'); ?></p>
			</header>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-site-logo-file"><?php esc_html_e('Logo 图片', 'oldbook'); ?></label>
					<p><?php esc_html_e('支持常见图片格式，透明背景效果最佳。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-logo-upload">
						<label class="oldbook-logo-upload__preview" for="oldbook-site-logo-file" data-oldbook-logo-preview data-oldbook-logo-label="<?php echo $logo_url ? esc_attr__('更换 Logo', 'oldbook') : esc_attr__('上传 Logo', 'oldbook'); ?>" style="<?php echo esc_attr('--oldbook-admin-logo-height:' . $logo_height . 'px;'); ?>">
							<?php if ($logo_url) : ?>
								<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_title); ?>">
							<?php else : ?>
								<span class="oldbook-logo-upload__empty">
									<span class="oldbook-logo-upload__icon" aria-hidden="true"><?php echo oldbook_icon('upload'); ?></span>
								</span>
							<?php endif; ?>
						</label>
						<input class="oldbook-settings-logo__input" type="file" id="oldbook-site-logo-file" name="oldbook_site_logo_file" accept="image/*">
						<?php if ($logo_url) : ?>
							<div class="oldbook-logo-upload__actions">
								<button type="button" class="oldbook-logo-upload__remove" data-oldbook-remove-logo>
									<?php echo oldbook_icon('trash'); ?>
									<?php esc_html_e('移除', 'oldbook'); ?>
								</button>
								<input type="hidden" name="oldbook_remove_logo" value="0" data-oldbook-remove-logo-input>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-site-logo-height"><?php esc_html_e('Logo 高度', 'oldbook'); ?></label>
					<p><?php esc_html_e('控制 Logo 在站点中的显示大小。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-settings-range">
						<input type="range" id="oldbook-site-logo-height" name="oldbook_site_logo_height" value="<?php echo esc_attr($logo_height); ?>" min="16" max="72" step="1" data-oldbook-range>
						<output data-oldbook-range-output="oldbook-site-logo-height" data-oldbook-range-suffix="px"><?php echo esc_html($logo_height); ?>px</output>
					</div>
				</div>
			</div>
		</section>

		<section id="oldbook-section-cover" class="oldbook-settings-section">
			<header class="oldbook-settings-section__head">
				<h2><?php esc_html_e('封面与蒙层', 'oldbook'); ?></h2>
				<p><?php esc_html_e('封面显示在站点顶部，蒙层用于保证标题文字可读。', 'oldbook'); ?></p>
			</header>
			<div class="oldbook-admin-cover-preview" data-oldbook-cover-preview style="<?php echo esc_attr('--oldbook-cover-height:' . $cover_settings['height'] . 'px;--oldbook-cover-overlay:' . $cover_settings['overlay'] . ';'); ?>">
				<img src="<?php echo esc_url($cover_url); ?>" alt="">
				<span class="oldbook-admin-cover-preview__overlay" aria-hidden="true"></span>
				<span class="oldbook-admin-cover-preview__label"><?php esc_html_e('当前封面预览', 'oldbook'); ?></span>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-cover-file"><?php esc_html_e('封面图片', 'oldbook'); ?></label>
					<p><?php esc_html_e('建议使用横向大图，未上传时使用默认封面。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<input type="file" id="oldbook-cover-file" name="oldbook_cover_file" accept="image/*">
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-cover-height"><?php esc_html_e('封面高度', 'oldbook'); ?></label>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-settings-range">
						<input type="range" id="oldbook-cover-height" name="oldbook_cover_height" value="<?php echo esc_attr($cover_settings['height']); ?>" min="200" max="520" step="1" data-oldbook-range>
						<output data-oldbook-range-output="oldbook-cover-height" data-oldbook-range-suffix="px"><?php echo esc_html($cover_settings['height']); ?>px</output>
					</div>
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-cover-overlay-color"><?php esc_html_e('蒙层颜色', 'oldbook'); ?></label>
					<p><?php esc_html_e('蒙层叠加在封面上，帮助文字更清晰。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-settings-color-control">
						<input type="color" id="oldbook-cover-overlay-color" name="oldbook_cover_overlay_color" value="<?php echo esc_attr($cover_settings['overlay_color']); ?>" data-oldbook-cover-overlay-color>
						<code data-oldbook-cover-overlay-output><?php echo esc_html($cover_settings['overlay_color']); ?></code>
					</div>
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-cover-overlay-opacity"><?php esc_html_e('蒙层不透明度', 'oldbook'); ?></label>
				</div>
				<div class="oldbook-settings-field__control">
					<div class="oldbook-settings-range">
						<input type="range" id="oldbook-cover-overlay-opacity" name="oldbook_cover_overlay_opacity" value="<?php echo esc_attr($cover_settings['overlay_opacity']); ?>" min="0" max="100" step="1" data-oldbook-range data-oldbook-cover-overlay-opacity>
						<output data-oldbook-range-output="oldbook-cover-overlay-opacity"><?php echo esc_html($cover_settings['overlay_opacity']); ?>%</output>
					</div>
				</div>
			</div>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label><?php esc_html_e('蒙层样式', 'oldbook'); ?></label>
					<p><?php esc_html_e('渐变模式使用两种颜色混合出蒙层。', 'oldbook'); ?></p>
				</div>
				<div class="oldbook-settings-field__control">
					<input type="hidden" name="oldbook_cover_gradient_enabled" value="<?php echo $cover_settings['gradient_enabled'] ? '1' : '0'; ?>" data-oldbook-picker-input="cover-gradient">
					<div class="oldbook-picker oldbook-picker--compact" role="radiogroup" aria-label="<?php esc_attr_e('蒙层样式', 'oldbook'); ?>">
						<button type="button" class="oldbook-picker__option<?php echo $cover_settings['gradient_enabled'] ? '' : ' is-selected'; ?>" role="radio" aria-checked="<?php echo $cover_settings['gradient_enabled'] ? 'false' : 'true'; ?>" data-oldbook-picker="cover-gradient" data-value="0"><?php esc_html_e('纯色', 'oldbook'); ?></button>
						<button type="button" class="oldbook-picker__option<?php echo $cover_settings['gradient_enabled'] ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $cover_settings['gradient_enabled'] ? 'true' : 'false'; ?>" data-oldbook-picker="cover-gradient" data-value="1"><?php esc_html_e('渐变', 'oldbook'); ?></button>
					</div>
					<div class="oldbook-settings-gradient-fields" data-oldbook-gradient-fields aria-hidden="<?php echo $cover_settings['gradient_enabled'] ? 'false' : 'true'; ?>">
						<div class="oldbook-settings-gradient-fields__colors">
							<div class="oldbook-settings-color-control">
								<label class="oldbook-admin-label" for="oldbook-cover-gradient-start"><?php esc_html_e('起始色', 'oldbook'); ?></label>
								<div class="oldbook-settings-color-control__input">
									<input type="color" id="oldbook-cover-gradient-start" name="oldbook_cover_gradient_start" value="<?php echo esc_attr($cover_settings['gradient_start']); ?>" data-oldbook-cover-gradient-start <?php disabled(! $cover_settings['gradient_enabled']); ?>>
									<code data-oldbook-cover-gradient-start-output><?php echo esc_html($cover_settings['gradient_start']); ?></code>
								</div>
							</div>
							<div class="oldbook-settings-color-control">
								<label class="oldbook-admin-label" for="oldbook-cover-gradient-end"><?php esc_html_e('结束色', 'oldbook'); ?></label>
								<div class="oldbook-settings-color-control__input">
									<input type="color" id="oldbook-cover-gradient-end" name="oldbook_cover_gradient_end" value="<?php echo esc_attr($cover_settings['gradient_end']); ?>" data-oldbook-cover-gradient-end <?php disabled(! $cover_settings['gradient_enabled']); ?>>
									<code data-oldbook-cover-gradient-end-output><?php echo esc_html($cover_settings['gradient_end']); ?></code>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section id="oldbook-section-signature" class="oldbook-settings-section">
			<header class="oldbook-settings-section__head">
				<h2><?php esc_html_e('个性签名', 'oldbook'); ?></h2>
				<p><?php esc_html_e('签名展示在站点的个人介绍区域。', 'oldbook'); ?></p>
			</header>
			<div class="oldbook-settings-field">
				<div class="oldbook-settings-field__label">
					<label for="oldbook-profile-signature"><?php esc_html_e('签名内容', 'oldbook'); ?></label>
				</div>
				<div class="oldbook-settings-field__control">
					<textarea id="oldbook-profile-signature" name="oldbook_profile_signature" rows="3" maxlength="120"><?php echo esc_textarea($signature); ?></textarea>
				</div>
			</div>
		</section>

		<div class="oldbook-settings-footer">
			<button type="submit" class="button button-primary button-large"><?php esc_html_e('保存设置', 'oldbook'); ?></button>
		</div>
	</form>
	<?php
}

function oldbook_render_publish_tab() {
	$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
	$post    = $post_id ? get_post($post_id) : null;

	if ($post_id && (! $post || 'oldbook_update' !== $post->post_type)) {
		oldbook_admin_redirect('publish', array('oldbook_error' => __('找不到这条动态。', 'oldbook')));
	}

	if ($post_id && ! current_user_can('edit_post', $post_id)) {
		wp_die(esc_html__('你没有编辑这条动态的权限。', 'oldbook'));
	}

	$type         = $post ? oldbook_get_update_type($post_id) : 'text';
	$title        = $post ? $post->post_title : '';
	$content      = $post ? $post->post_content : '';
	$media_source = $post ? sanitize_key((string) get_post_meta($post_id, '_oldbook_media_source', true)) : 'external';
	$media_source = in_array($media_source, array('local', 'external'), true) ? $media_source : 'external';
	$media_url    = $post ? get_post_meta($post_id, '_oldbook_media_url', true) : '';
	$photo_ids    = $post ? oldbook_get_photo_ids($post_id) : array();
	$types        = oldbook_get_update_types();

	oldbook_render_admin_heading(
		$post ? __('编辑动态', 'oldbook') : __('发布动态', 'oldbook'),
		__('选择一种类型，填写内容，无需打开默认文章编辑器即可发布。', 'oldbook')
	);
	?>
	<form class="oldbook-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="oldbook_save_update">
		<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($post_id); ?>">
		<input type="hidden" name="oldbook_title" value="<?php echo esc_attr($title); ?>">
		<input type="hidden" name="oldbook_type" value="<?php echo esc_attr($type); ?>" data-oldbook-picker-input="update-type">
		<?php wp_nonce_field('oldbook_save_update'); ?>

		<div class="oldbook-admin-form__section">
			<h2><?php esc_html_e('动态类型', 'oldbook'); ?></h2>
			<div class="oldbook-picker" role="radiogroup" aria-label="<?php esc_attr_e('动态类型', 'oldbook'); ?>">
				<?php foreach ($types as $value => $item) : ?>
					<button type="button" class="oldbook-picker__option<?php echo $type === $value ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $type === $value ? 'true' : 'false'; ?>" data-oldbook-picker="update-type" data-value="<?php echo esc_attr($value); ?>">
						<span class="oldbook-picker__icon"><?php echo oldbook_icon($item['icon']); ?></span>
						<span><?php echo esc_html($item['label']); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="oldbook-admin-form__section">
			<label class="oldbook-admin-label" for="oldbook-content"><?php esc_html_e('文字内容', 'oldbook'); ?><span><?php esc_html_e('支持换行，也可以为媒体动态添加简短说明。', 'oldbook'); ?></span></label>
			<textarea id="oldbook-content" name="oldbook_content" rows="8"><?php echo esc_textarea($content); ?></textarea>
		</div>

		<div class="oldbook-admin-form__section oldbook-admin-conditional" data-oldbook-type="music video">
			<h2><?php esc_html_e('媒体来源', 'oldbook'); ?></h2>
			<input type="hidden" name="oldbook_media_source" value="<?php echo esc_attr($media_source); ?>" data-oldbook-picker-input="media-source">
			<div class="oldbook-picker oldbook-picker--compact" role="radiogroup" aria-label="<?php esc_attr_e('媒体来源', 'oldbook'); ?>">
				<button type="button" class="oldbook-picker__option<?php echo 'local' === $media_source ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo 'local' === $media_source ? 'true' : 'false'; ?>" data-oldbook-picker="media-source" data-value="local"><?php esc_html_e('上传文件', 'oldbook'); ?></button>
				<button type="button" class="oldbook-picker__option<?php echo 'external' === $media_source ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo 'external' === $media_source ? 'true' : 'false'; ?>" data-oldbook-picker="media-source" data-value="external"><?php esc_html_e('外部网址', 'oldbook'); ?></button>
			</div>
			<div class="oldbook-admin-media-source" data-oldbook-source="local">
				<label class="oldbook-admin-label" for="oldbook-media-file"><?php esc_html_e('音频或视频文件', 'oldbook'); ?></label>
				<input type="file" id="oldbook-media-file" name="oldbook_media_file" accept="audio/*,video/*">
				<?php if ($post_id && oldbook_get_update_media_url($post_id, $type) && 'local' === $media_source) : ?>
					<p class="description"><?php esc_html_e('当前已有文件，重新上传可以替换它。', 'oldbook'); ?></p>
				<?php endif; ?>
			</div>
			<div class="oldbook-admin-media-source" data-oldbook-source="external">
				<label class="oldbook-admin-label" for="oldbook-media-url"><?php esc_html_e('媒体网址', 'oldbook'); ?></label>
				<input type="url" id="oldbook-media-url" name="oldbook_media_url" value="<?php echo esc_attr($media_url); ?>" placeholder="https://">
			</div>
		</div>

		<div class="oldbook-admin-form__section oldbook-admin-conditional" data-oldbook-type="photo">
			<label class="oldbook-admin-label" for="oldbook-photos"><?php esc_html_e('图片', 'oldbook'); ?><span><?php esc_html_e('最多选择 9 张本地图片。', 'oldbook'); ?></span></label>
			<input type="file" id="oldbook-photos" name="oldbook_photos[]" accept="image/*" multiple>
			<?php if ($photo_ids) : ?>
				<div class="oldbook-admin-photo-list">
					<?php foreach ($photo_ids as $photo_id) : ?>
						<?php echo wp_get_attachment_image($photo_id, 'thumbnail', false, array('loading' => 'lazy', 'alt' => '')); ?>
						<input type="hidden" name="oldbook_existing_photo_ids[]" value="<?php echo esc_attr($photo_id); ?>">
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="oldbook-admin-form__actions">
			<button type="submit" class="button button-primary button-large"><?php echo $post ? esc_html__('更新动态', 'oldbook') : esc_html__('发布动态', 'oldbook'); ?></button>
			<a class="button button-large" href="<?php echo esc_url(oldbook_admin_page_url('updates')); ?>"><?php esc_html_e('取消', 'oldbook'); ?></a>
		</div>
	</form>
	<?php
}

function oldbook_render_add_link_tab() {
	$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
	$post    = $post_id ? get_post($post_id) : null;

	if ($post_id && (! $post || 'oldbook_link' !== $post->post_type)) {
		oldbook_admin_redirect('add-link', array('oldbook_error' => __('找不到这个链接。', 'oldbook')));
	}

	if ($post_id && ! current_user_can('edit_post', $post_id)) {
		wp_die(esc_html__('你没有编辑这个链接的权限。', 'oldbook'));
	}

	$group       = $post ? oldbook_get_link_group($post_id) : 'bookmark';
	$url         = $post ? oldbook_get_link_url($post_id) : '';
	$description = $post ? oldbook_get_link_description($post_id) : '';
	$icon_url    = $post ? get_post_meta($post_id, '_oldbook_link_icon_url', true) : '';
	$title       = $post ? $post->post_title : '';

	oldbook_render_admin_heading(
		$post ? __('编辑链接', 'oldbook') : __('添加链接', 'oldbook'),
		__('填写网址、简短介绍和可选的自定义图标。', 'oldbook')
	);
	?>
	<form class="oldbook-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="oldbook_save_link">
		<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($post_id); ?>">
		<input type="hidden" name="oldbook_link_group" value="<?php echo esc_attr($group); ?>" data-oldbook-picker-input="link-group">
		<?php wp_nonce_field('oldbook_save_link'); ?>

		<div class="oldbook-admin-form__section">
			<h2><?php esc_html_e('分组', 'oldbook'); ?></h2>
			<div class="oldbook-picker oldbook-picker--compact" role="radiogroup" aria-label="<?php esc_attr_e('链接分组', 'oldbook'); ?>">
				<?php foreach (oldbook_get_link_groups() as $value => $label) : ?>
					<button type="button" class="oldbook-picker__option<?php echo $group === $value ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $group === $value ? 'true' : 'false'; ?>" data-oldbook-picker="link-group" data-value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="oldbook-admin-form__section">
			<label class="oldbook-admin-label" for="oldbook-link-title"><?php esc_html_e('标题', 'oldbook'); ?></label>
			<input type="text" id="oldbook-link-title" name="oldbook_link_title" value="<?php echo esc_attr($title); ?>" maxlength="120" required>
		</div>

		<div class="oldbook-admin-form__section">
			<label class="oldbook-admin-label" for="oldbook-link-url"><?php esc_html_e('网址', 'oldbook'); ?></label>
			<input type="url" id="oldbook-link-url" name="oldbook_link_url" value="<?php echo esc_attr($url); ?>" placeholder="https://" required>
		</div>

		<div class="oldbook-admin-form__section">
			<label class="oldbook-admin-label" for="oldbook-link-description"><?php esc_html_e('描述', 'oldbook'); ?></label>
			<textarea id="oldbook-link-description" name="oldbook_link_description" rows="4"><?php echo esc_textarea($description); ?></textarea>
		</div>

		<div class="oldbook-admin-form__section">
			<h2><?php esc_html_e('图标', 'oldbook'); ?></h2>
			<p class="description"><?php esc_html_e('两个字段都留空时，将使用网站图标。', 'oldbook'); ?></p>
			<label class="oldbook-admin-label" for="oldbook-link-icon-file"><?php esc_html_e('上传自定义图标', 'oldbook'); ?></label>
			<input type="file" id="oldbook-link-icon-file" name="oldbook_link_icon_file" accept="image/*">
			<label class="oldbook-admin-label" for="oldbook-link-icon-url"><?php esc_html_e('自定义图标网址', 'oldbook'); ?></label>
			<input type="url" id="oldbook-link-icon-url" name="oldbook_link_icon_url" value="<?php echo esc_attr($icon_url); ?>" placeholder="https://">
		</div>

		<div class="oldbook-admin-form__actions">
			<button type="submit" class="button button-primary button-large"><?php echo $post ? esc_html__('更新链接', 'oldbook') : esc_html__('保存链接', 'oldbook'); ?></button>
			<a class="button button-large" href="<?php echo esc_url(oldbook_admin_page_url('links')); ?>"><?php esc_html_e('取消', 'oldbook'); ?></a>
		</div>
	</form>
	<?php
}

function oldbook_get_upload_mimes($kind) {
	$mimes = array(
		'image' => array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
		),
		'audio' => array(
			'mp3'  => 'audio/mpeg',
			'm4a'  => 'audio/mp4',
			'ogg'  => 'audio/ogg',
			'wav'  => 'audio/wav',
			'flac' => 'audio/flac',
		),
		'video' => array(
			'mp4|m4v' => 'video/mp4',
			'webm'    => 'video/webm',
			'ogv'     => 'video/ogg',
			'mov'     => 'video/quicktime',
		),
	);

	return isset($mimes[$kind]) ? $mimes[$kind] : $mimes['image'];
}

function oldbook_handle_upload($file_key, $post_id, $kind) {
	if (empty($_FILES[$file_key]) || empty($_FILES[$file_key]['name'])) {
		return 0;
	}

	$file  = $_FILES[$file_key];
	$mimes = oldbook_get_upload_mimes($kind);

	if (! empty($file['error']) && UPLOAD_ERR_OK !== (int) $file['error']) {
		return new WP_Error('oldbook_upload_error', __('文件上传没有完成，请重试。', 'oldbook'));
	}

	$type = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $mimes);

	if (empty($type['type'])) {
		return new WP_Error('oldbook_invalid_upload', __('不允许上传这种文件类型。', 'oldbook'));
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	return media_handle_upload(
		$file_key,
		$post_id,
		array(),
		array(
			'test_form' => false,
			'mimes'    => $mimes,
		)
	);
}

function oldbook_handle_photo_uploads($post_id, $existing_ids) {
	if (empty($_FILES['oldbook_photos']) || ! is_array($_FILES['oldbook_photos']['name'])) {
		return $existing_ids;
	}

	$files = $_FILES['oldbook_photos'];
	$count = 0;

	foreach ($files['name'] as $name) {
		if ($name) {
			$count++;
		}
	}

	if (count($existing_ids) + $count > 9) {
		return new WP_Error('oldbook_photo_limit', __('图片动态最多只能包含 9 张图片。', 'oldbook'));
	}

	foreach ($files['name'] as $index => $name) {
		if (! $name) {
			continue;
		}

		$_FILES['oldbook_photo'] = array(
			'name'     => $files['name'][$index],
			'type'     => $files['type'][$index],
			'tmp_name' => $files['tmp_name'][$index],
			'error'    => $files['error'][$index],
			'size'     => $files['size'][$index],
		);

		$attachment_id = oldbook_handle_upload('oldbook_photo', $post_id, 'image');
		unset($_FILES['oldbook_photo']);

		if (is_wp_error($attachment_id)) {
			return $attachment_id;
		}

		$existing_ids[] = absint($attachment_id);
	}

	return $existing_ids;
}

function oldbook_redirect_save_error($page, $message, $post_id = 0) {
	$args = array('oldbook_error' => wp_strip_all_tags($message));

	if ($post_id) {
		$args['post_id'] = absint($post_id);
	}

	oldbook_admin_redirect($page, $args);
}

function oldbook_handle_save_update() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理主题内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_save_update');

	$post_id = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;
	$type    = isset($_POST['oldbook_type']) ? sanitize_key(wp_unslash($_POST['oldbook_type'])) : 'text';
	$types   = oldbook_get_update_types();

	if (! isset($types[$type])) {
		oldbook_redirect_save_error('publish', __('请选择有效的动态类型。', 'oldbook'), $post_id);
	}

	if ($post_id && ('oldbook_update' !== get_post_type($post_id) || ! current_user_can('edit_post', $post_id))) {
		oldbook_redirect_save_error('publish', __('找不到这条动态。', 'oldbook'));
	}

	$title   = isset($_POST['oldbook_title']) ? sanitize_text_field(wp_unslash($_POST['oldbook_title'])) : '';
	$content = isset($_POST['oldbook_content']) ? sanitize_textarea_field(wp_unslash($_POST['oldbook_content'])) : '';
	$title   = $title ? $title : oldbook_default_update_title($type);

	if ('text' === $type && ! trim($content)) {
		oldbook_redirect_save_error('publish', __('文字动态需要填写内容。', 'oldbook'), $post_id);
	}

	$media_source = isset($_POST['oldbook_media_source']) ? sanitize_key(wp_unslash($_POST['oldbook_media_source'])) : 'external';
	$media_source = in_array($media_source, array('local', 'external'), true) ? $media_source : 'external';
	$media_url    = isset($_POST['oldbook_media_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_media_url'])) : '';
	$old_media_id = $post_id ? absint(get_post_meta($post_id, '_oldbook_media_attachment_id', true)) : 0;
	$old_media_url = $post_id ? oldbook_get_update_media_url($post_id, $type) : '';

	if (in_array($type, array('music', 'video'), true) && 'external' === $media_source && ! $media_url) {
		oldbook_redirect_save_error('publish', __('请填写有效的外部媒体网址。', 'oldbook'), $post_id);
	}

	if (in_array($type, array('music', 'video'), true) && 'local' === $media_source && empty($_FILES['oldbook_media_file']['name']) && ! $old_media_id) {
		oldbook_redirect_save_error('publish', __('请上传媒体文件，或选择外部媒体网址。', 'oldbook'), $post_id);
	}

	$existing_photo_ids = isset($_POST['oldbook_existing_photo_ids']) ? array_values(array_filter(array_map('absint', (array) wp_unslash($_POST['oldbook_existing_photo_ids'])))) : array();
	if ('photo' === $type && ! $existing_photo_ids && empty($_FILES['oldbook_photos']['name'][0])) {
		oldbook_redirect_save_error('publish', __('图片动态至少需要选择一张图片。', 'oldbook'), $post_id);
	}

	$post_data = array(
		'post_type'    => 'oldbook_update',
		'post_status'  => $post_id ? get_post_status($post_id) : 'draft',
		'post_title'   => $title,
		'post_content' => $content,
		'post_author'  => get_current_user_id(),
	);

	if ($post_id) {
		$post_data['ID'] = $post_id;
		$saved_id       = wp_update_post($post_data, true);
	} else {
		$saved_id = wp_insert_post($post_data, true);
	}

	if (is_wp_error($saved_id)) {
		oldbook_redirect_save_error('publish', $saved_id->get_error_message(), $post_id);
	}

	$saved_id = absint($saved_id);
	update_post_meta($saved_id, '_oldbook_update_type', $type);

	$update_category = isset($_POST['oldbook_update_category']) ? absint($_POST['oldbook_update_category']) : 0;
	wp_set_object_terms($saved_id, $update_category ? array($update_category) : array(), 'update_category');

	if ('text' === $type) {
		delete_post_meta($saved_id, '_oldbook_media_source');
		delete_post_meta($saved_id, '_oldbook_media_url');
		delete_post_meta($saved_id, '_oldbook_media_attachment_id');
		delete_post_meta($saved_id, '_oldbook_photo_ids');
	}

	if (in_array($type, array('music', 'video'), true)) {
		if ('external' === $media_source) {
			update_post_meta($saved_id, '_oldbook_media_source', 'external');
			update_post_meta($saved_id, '_oldbook_media_url', $media_url);
			delete_post_meta($saved_id, '_oldbook_media_attachment_id');
		} else {
			$attachment_id = oldbook_handle_upload('oldbook_media_file', $saved_id, $type === 'music' ? 'audio' : 'video');

			if (is_wp_error($attachment_id)) {
				oldbook_redirect_save_error('publish', $attachment_id->get_error_message(), $post_id);
			}

			if ($attachment_id) {
				update_post_meta($saved_id, '_oldbook_media_attachment_id', absint($attachment_id));
				delete_post_meta($saved_id, '_oldbook_media_url');
			}

			update_post_meta($saved_id, '_oldbook_media_source', 'local');
		}

		delete_post_meta($saved_id, '_oldbook_photo_ids');
	}

	if ('photo' === $type) {
		$photo_ids = oldbook_handle_photo_uploads($saved_id, $existing_photo_ids);

		if (is_wp_error($photo_ids)) {
			oldbook_redirect_save_error('publish', $photo_ids->get_error_message(), $post_id);
		}

		update_post_meta($saved_id, '_oldbook_photo_ids', $photo_ids);
		delete_post_meta($saved_id, '_oldbook_media_source');
		delete_post_meta($saved_id, '_oldbook_media_url');
		delete_post_meta($saved_id, '_oldbook_media_attachment_id');
	}

	wp_update_post(
		array(
			'ID'          => $saved_id,
			'post_status' => 'publish',
		)
	);

	oldbook_admin_redirect('updates', array('oldbook_notice' => 'saved'));
}
add_action('admin_post_oldbook_save_update', 'oldbook_handle_save_update');

function oldbook_handle_save_link() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理主题内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_save_link');

	$post_id     = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;
	$title       = isset($_POST['oldbook_link_title']) ? sanitize_text_field(wp_unslash($_POST['oldbook_link_title'])) : '';
	$url         = isset($_POST['oldbook_link_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_link_url'])) : '';
	$description = isset($_POST['oldbook_link_description']) ? sanitize_textarea_field(wp_unslash($_POST['oldbook_link_description'])) : '';
	$icon_url    = isset($_POST['oldbook_link_icon_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_link_icon_url'])) : '';
	$group       = isset($_POST['oldbook_link_group']) ? sanitize_key(wp_unslash($_POST['oldbook_link_group'])) : 'bookmark';

	if ($post_id && ('oldbook_link' !== get_post_type($post_id) || ! current_user_can('edit_post', $post_id))) {
		oldbook_redirect_save_error('add-link', __('找不到这个链接。', 'oldbook'));
	}

	if (! $title || ! $url) {
		oldbook_redirect_save_error('add-link', __('链接需要填写标题和有效网址。', 'oldbook'), $post_id);
	}

	if (! array_key_exists($group, oldbook_get_link_groups())) {
		$group = 'bookmark';
	}

	$post_data = array(
		'post_type'    => 'oldbook_link',
		'post_status'  => $post_id ? get_post_status($post_id) : 'draft',
		'post_title'   => $title,
		'post_content' => $description,
		'post_author'  => get_current_user_id(),
	);

	if ($post_id) {
		$post_data['ID'] = $post_id;
		$saved_id       = wp_update_post($post_data, true);
	} else {
		$saved_id = wp_insert_post($post_data, true);
	}

	if (is_wp_error($saved_id)) {
		oldbook_redirect_save_error('add-link', $saved_id->get_error_message(), $post_id);
	}

	$saved_id = absint($saved_id);
	update_post_meta($saved_id, '_oldbook_link_group', $group);
	update_post_meta($saved_id, '_oldbook_link_url', $url);
	update_post_meta($saved_id, '_oldbook_link_description', $description);

	$icon_attachment_id = oldbook_handle_upload('oldbook_link_icon_file', $saved_id, 'image');

	if (is_wp_error($icon_attachment_id)) {
		oldbook_redirect_save_error('add-link', $icon_attachment_id->get_error_message(), $post_id);
	}

	if ($icon_attachment_id) {
		update_post_meta($saved_id, '_oldbook_link_icon_attachment_id', absint($icon_attachment_id));
		delete_post_meta($saved_id, '_oldbook_link_icon_url');
	} elseif ($icon_url) {
		update_post_meta($saved_id, '_oldbook_link_icon_url', $icon_url);
	}

	wp_update_post(
		array(
			'ID'          => $saved_id,
			'post_status' => 'publish',
		)
	);

	oldbook_admin_redirect('links', array('oldbook_notice' => 'saved'));
}
add_action('admin_post_oldbook_save_link', 'oldbook_handle_save_link');

function oldbook_handle_save_settings() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('你没有管理站点设置的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_save_settings');

	$site_title   = isset($_POST['oldbook_site_title']) ? sanitize_text_field(wp_unslash($_POST['oldbook_site_title'])) : '';
	$signature    = isset($_POST['oldbook_profile_signature']) ? sanitize_textarea_field(wp_unslash($_POST['oldbook_profile_signature'])) : '';
	$overlay      = isset($_POST['oldbook_cover_overlay_color']) ? sanitize_hex_color(wp_unslash($_POST['oldbook_cover_overlay_color'])) : '';
	$opacity      = isset($_POST['oldbook_cover_overlay_opacity']) ? oldbook_sanitize_percentage(wp_unslash($_POST['oldbook_cover_overlay_opacity'])) : 26;
	$gradient     = isset($_POST['oldbook_cover_gradient_enabled']) && '1' === sanitize_key(wp_unslash($_POST['oldbook_cover_gradient_enabled']));
	$gradient_start = isset($_POST['oldbook_cover_gradient_start']) ? sanitize_hex_color(wp_unslash($_POST['oldbook_cover_gradient_start'])) : '';
	$gradient_end   = isset($_POST['oldbook_cover_gradient_end']) ? sanitize_hex_color(wp_unslash($_POST['oldbook_cover_gradient_end'])) : '';
	$cover_height   = isset($_POST['oldbook_cover_height']) ? oldbook_sanitize_cover_height(wp_unslash($_POST['oldbook_cover_height'])) : 320;
	$logo_height    = isset($_POST['oldbook_site_logo_height']) ? oldbook_sanitize_logo_height(wp_unslash($_POST['oldbook_site_logo_height'])) : 24;
	$show_left_sidebar  = isset($_POST['oldbook_show_left_sidebar']) && '1' === sanitize_key(wp_unslash($_POST['oldbook_show_left_sidebar']));
	$show_right_sidebar = isset($_POST['oldbook_show_right_sidebar']) && '1' === sanitize_key(wp_unslash($_POST['oldbook_show_right_sidebar']));
	$mini_left_sidebar  = isset($_POST['oldbook_mini_left_sidebar']) && '1' === sanitize_key(wp_unslash($_POST['oldbook_mini_left_sidebar']));
	$home_content       = isset($_POST['oldbook_home_content']) ? sanitize_key(wp_unslash($_POST['oldbook_home_content'])) : '';
	$home_content       = in_array($home_content, array('articles', 'updates'), true) ? $home_content : 'articles';

	if ($site_title) {
		oldbook_set_theme_mod('oldbook_site_title', $site_title);
	} else {
		oldbook_remove_theme_mod('oldbook_site_title');
	}

	oldbook_remove_theme_mod('oldbook_site_tagline');

	if ($signature) {
		oldbook_set_theme_mod('oldbook_profile_signature', $signature);
	} else {
		oldbook_remove_theme_mod('oldbook_profile_signature');
	}

	oldbook_set_theme_mod('oldbook_cover_overlay_color', $overlay ? $overlay : '#11201a');
	oldbook_set_theme_mod('oldbook_cover_overlay_opacity', $opacity);
	oldbook_set_theme_mod('oldbook_cover_gradient_enabled', $gradient);
	oldbook_set_theme_mod('oldbook_cover_gradient_start', $gradient_start ? $gradient_start : '#11201a');
	oldbook_set_theme_mod('oldbook_cover_gradient_end', $gradient_end ? $gradient_end : '#1d7a55');
	oldbook_set_theme_mod('oldbook_cover_gradient_direction', 'to bottom');
	oldbook_set_theme_mod('oldbook_cover_height', $cover_height);
	oldbook_set_theme_mod('oldbook_site_logo_height', $logo_height);
	oldbook_set_theme_mod('oldbook_show_left_sidebar', $show_left_sidebar);
	oldbook_set_theme_mod('oldbook_show_right_sidebar', $show_right_sidebar);
	oldbook_set_theme_mod('oldbook_mini_left_sidebar', $mini_left_sidebar);
	oldbook_set_theme_mod('oldbook_home_content', $home_content);

	$remove_logo = isset($_POST['oldbook_remove_logo']) && '1' === sanitize_key(wp_unslash($_POST['oldbook_remove_logo']));

	if ($remove_logo) {
		oldbook_remove_theme_mod('oldbook_site_logo_id');
		oldbook_remove_theme_mod('oldbook_site_logo');
	} else {
		$logo_attachment_id = oldbook_handle_upload('oldbook_site_logo_file', 0, 'image');

		if (is_wp_error($logo_attachment_id)) {
			oldbook_redirect_save_error('settings', $logo_attachment_id->get_error_message());
		}

		if ($logo_attachment_id) {
			$logo_url = wp_get_attachment_url($logo_attachment_id);

			if ($logo_url) {
				oldbook_set_theme_mod('oldbook_site_logo_id', absint($logo_attachment_id));
				oldbook_set_theme_mod('oldbook_site_logo', esc_url_raw($logo_url));
			}
		}
	}

	$cover_attachment_id = oldbook_handle_upload('oldbook_cover_file', 0, 'image');

	if (is_wp_error($cover_attachment_id)) {
		oldbook_redirect_save_error('settings', $cover_attachment_id->get_error_message());
	}

	if ($cover_attachment_id) {
		$cover_url = wp_get_attachment_url($cover_attachment_id);

		if ($cover_url) {
			oldbook_set_theme_mod('oldbook_cover_image_id', absint($cover_attachment_id));
			oldbook_set_theme_mod('oldbook_cover_image', esc_url_raw($cover_url));
		}
	}

	oldbook_admin_redirect('settings', array('oldbook_notice' => 'saved'));
}
add_action('admin_post_oldbook_save_settings', 'oldbook_handle_save_settings');

function oldbook_handle_delete_content() {
	$post_id = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;

	if (! current_user_can('edit_posts') || ! $post_id) {
		wp_die(esc_html__('你没有删除这项内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_delete_content_' . $post_id);

	$post_type = get_post_type($post_id);
	$tab       = 'updates';

	if ('oldbook_link' === $post_type) {
		$tab = 'links';
	}

	if (! in_array($post_type, array('oldbook_update', 'oldbook_link'), true)) {
		oldbook_admin_redirect($tab, array('oldbook_error' => __('无法删除这项内容。', 'oldbook')));
	}

	if (! current_user_can('delete_post', $post_id)) {
		wp_die(esc_html__('你没有删除这项内容的权限。', 'oldbook'));
	}

	wp_delete_post($post_id, true);
	oldbook_admin_redirect($tab, array('oldbook_notice' => 'deleted'));
}
add_action('admin_post_oldbook_delete_content', 'oldbook_handle_delete_content');
