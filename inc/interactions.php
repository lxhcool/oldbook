<?php
/**
 * Inline interactions for dynamic cards.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_is_public_update($post_id) {
	$post = get_post(absint($post_id));

	return $post && 'oldbook_update' === $post->post_type && 'publish' === $post->post_status;
}

function oldbook_get_like_count($post_id = 0) {
	return absint(get_post_meta(absint($post_id), '_oldbook_like_count', true));
}

function oldbook_get_like_cookie_name($post_id) {
	return 'oldbook_liked_' . absint($post_id);
}

function oldbook_has_liked($post_id) {
	$post_id = absint($post_id);

	if (is_user_logged_in()) {
		return (bool) get_user_meta(get_current_user_id(), '_oldbook_liked_' . $post_id, true);
	}

	$cookie_name = oldbook_get_like_cookie_name($post_id);

	return isset($_COOKIE[$cookie_name]) && '1' === $_COOKIE[$cookie_name];
}

function oldbook_set_guest_like_cookie($post_id, $liked) {
	$cookie_name   = oldbook_get_like_cookie_name($post_id);
	$cookie_path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
	$cookie_domain = defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : '';

	setcookie(
		$cookie_name,
		$liked ? '1' : '',
		array(
			'expires'  => $liked ? time() + YEAR_IN_SECONDS : time() - HOUR_IN_SECONDS,
			'path'     => $cookie_path,
			'domain'   => $cookie_domain,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	if ($liked) {
		$_COOKIE[$cookie_name] = '1';
	} else {
		unset($_COOKIE[$cookie_name]);
	}
}

function oldbook_set_like_state($post_id, $liked) {
	$post_id = absint($post_id);

	if (is_user_logged_in()) {
		$meta_key = '_oldbook_liked_' . $post_id;

		if ($liked) {
			update_user_meta(get_current_user_id(), $meta_key, 1);
		} else {
			delete_user_meta(get_current_user_id(), $meta_key);
		}

		return;
	}

	oldbook_set_guest_like_cookie($post_id, $liked);
}

function oldbook_render_comment_item($comment) {
	$comment_id = absint($comment->comment_ID);
	$author     = get_comment_author($comment);
	$author     = $author ? $author : __('评论者', 'oldbook');

	ob_start();
	?>
	<li class="oldbook-comment" data-comment-id="<?php echo esc_attr($comment_id); ?>">
		<div class="oldbook-comment__meta">
			<strong><?php echo esc_html($author); ?></strong>
			<time datetime="<?php echo esc_attr(get_comment_date(DATE_W3C, $comment)); ?>"><?php echo esc_html(get_comment_date('Y.m.d H:i', $comment)); ?></time>
		</div>
		<div class="oldbook-comment__text"><?php echo wp_kses_post(get_comment_text($comment)); ?></div>
	</li>
	<?php

	return ob_get_clean();
}

function oldbook_render_update_social($post_id, $compact = false) {
	$post_id        = absint($post_id);
	$comments       = get_comments(
		array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'type'    => 'comment',
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC',
			'number'  => 20,
		)
	);
	$comment_count  = get_comments_number($post_id);
	$comments_open  = comments_open($post_id);
	$comments_id    = 'oldbook-comments-' . $post_id;
	$comments_class = $comments ? '' : ' is-collapsed';
	$like_count     = oldbook_get_like_count($post_id);
	$liked          = oldbook_has_liked($post_id);
	$like_label     = $liked ? __('取消点赞', 'oldbook') : __('点赞', 'oldbook');

	if ($compact) {
		?>
		<section class="oldbook-update__social oldbook-update__social--compact" aria-label="<?php esc_attr_e('动态互动', 'oldbook'); ?>">
			<div class="oldbook-update__actions">
				<button class="oldbook-update__action oldbook-like-button<?php echo $liked ? ' is-liked' : ''; ?>" type="button" data-oldbook-like data-post-id="<?php echo esc_attr($post_id); ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr($like_label); ?>" title="<?php echo esc_attr($like_label); ?>">
					<?php echo oldbook_icon('thumbs-up'); ?>
					<span data-oldbook-like-count><?php echo esc_html(number_format_i18n($like_count)); ?></span>
				</button>
				<span class="oldbook-update__rowcount" aria-label="<?php esc_attr_e('评论数', 'oldbook'); ?>">
					<?php echo oldbook_icon('message-square'); ?>
					<span><?php echo esc_html(number_format_i18n($comment_count)); ?></span>
				</span>
			</div>
			<p class="oldbook-update__interaction-status" data-oldbook-interaction-status role="status" aria-live="polite"></p>
		</section>
		<?php
		return;
	}
	?>
	<section class="oldbook-update__social" aria-label="<?php esc_attr_e('动态互动', 'oldbook'); ?>">
		<div class="oldbook-update__actions">
			<button class="oldbook-update__action oldbook-like-button<?php echo $liked ? ' is-liked' : ''; ?>" type="button" data-oldbook-like data-post-id="<?php echo esc_attr($post_id); ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr($like_label); ?>" title="<?php echo esc_attr($like_label); ?>">
				<?php echo oldbook_icon('thumbs-up'); ?>
				<span data-oldbook-like-count><?php echo esc_html(number_format_i18n($like_count)); ?></span>
			</button>
			<button class="oldbook-update__action oldbook-comment-toggle" type="button" data-oldbook-comment-toggle aria-controls="<?php echo esc_attr($comments_id); ?>" aria-expanded="<?php echo $comments ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e('查看评论', 'oldbook'); ?>" title="<?php esc_attr_e('查看评论', 'oldbook'); ?>">
				<?php echo oldbook_icon('message-square'); ?>
				<span data-oldbook-comment-count><?php echo esc_html(number_format_i18n($comment_count)); ?></span>
			</button>
		</div>
		<p class="oldbook-update__interaction-status" data-oldbook-interaction-status role="status" aria-live="polite"></p>

		<div id="<?php echo esc_attr($comments_id); ?>" class="oldbook-update__comments<?php echo esc_attr($comments_class); ?>" data-oldbook-comments>
			<div class="oldbook-update__comments-inner">
				<ol class="oldbook-comment-list" data-oldbook-comment-list>
					<?php foreach ($comments as $comment) : ?>
						<?php echo oldbook_render_comment_item($comment); ?>
					<?php endforeach; ?>
				</ol>

				<?php if ($comments_open && (! get_option('comment_registration') || is_user_logged_in())) : ?>
					<form class="oldbook-comment-form" data-oldbook-comment-form>
						<input type="hidden" name="action" value="oldbook_add_comment">
						<input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('oldbook_comment')); ?>">
						<input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

						<?php if (! is_user_logged_in()) : ?>
							<div class="oldbook-comment-form__identity">
								<label>
									<span class="screen-reader-text"><?php esc_html_e('昵称', 'oldbook'); ?></span>
									<input type="text" name="author" autocomplete="name" placeholder="<?php esc_attr_e('昵称', 'oldbook'); ?>" required>
								</label>
								<label>
									<span class="screen-reader-text"><?php esc_html_e('邮箱', 'oldbook'); ?></span>
									<input type="email" name="email" autocomplete="email" placeholder="<?php esc_attr_e('邮箱', 'oldbook'); ?>"<?php echo get_option('require_name_email') ? ' required' : ''; ?>>
								</label>
							</div>
						<?php endif; ?>

						<div class="oldbook-comment-form__field">
							<label class="screen-reader-text" for="oldbook-comment-<?php echo esc_attr($post_id); ?>"><?php esc_html_e('评论内容', 'oldbook'); ?></label>
							<textarea id="oldbook-comment-<?php echo esc_attr($post_id); ?>" name="content" rows="1" placeholder="<?php esc_attr_e('说点什么', 'oldbook'); ?>" required></textarea>
							<button class="oldbook-comment-form__submit" type="submit">
								<?php echo oldbook_icon('send'); ?>
								<span><?php esc_html_e('发送', 'oldbook'); ?></span>
							</button>
						</div>
						<p class="oldbook-comment-form__status" data-oldbook-comment-status role="status" aria-live="polite"></p>
					</form>
				<?php elseif (! $comments_open) : ?>
					<p class="oldbook-comment-form__notice"><?php esc_html_e('评论已关闭。', 'oldbook'); ?></p>
				<?php else : ?>
					<p class="oldbook-comment-form__notice">
						<a href="<?php echo esc_url(wp_login_url(home_url('/'))); ?>"><?php esc_html_e('登录后发表评论。', 'oldbook'); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

function oldbook_ajax_toggle_like() {
	check_ajax_referer('oldbook_like', 'nonce');

	$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

	if (! oldbook_is_public_update($post_id)) {
		wp_send_json_error(array('message' => __('这条动态暂时不能点赞。', 'oldbook')));
	}

	$liked = oldbook_has_liked($post_id);
	$liked = ! $liked;
	$count = oldbook_get_like_count($post_id);
	$count = $liked ? $count + 1 : max(0, $count - 1);

	update_post_meta($post_id, '_oldbook_like_count', $count);
	oldbook_set_like_state($post_id, $liked);

	wp_send_json_success(
		array(
			'liked' => $liked,
			'count' => $count,
		)
	);
}
add_action('wp_ajax_oldbook_toggle_like', 'oldbook_ajax_toggle_like');
add_action('wp_ajax_nopriv_oldbook_toggle_like', 'oldbook_ajax_toggle_like');

function oldbook_ajax_add_comment() {
	check_ajax_referer('oldbook_comment', 'nonce');

	$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
	$content = isset($_POST['content']) ? trim(sanitize_textarea_field(wp_unslash($_POST['content']))) : '';

	if (! oldbook_is_public_update($post_id) || ! comments_open($post_id)) {
		wp_send_json_error(array('message' => __('这条动态暂时不能评论。', 'oldbook')));
	}

	if (get_option('comment_registration') && ! is_user_logged_in()) {
		wp_send_json_error(array('message' => __('请先登录后再评论。', 'oldbook')));
	}

	if (! $content) {
		wp_send_json_error(array('message' => __('评论内容不能为空。', 'oldbook')));
	}

	$current_user = wp_get_current_user();
	$author       = is_user_logged_in() ? $current_user->display_name : (isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '');
	$email        = is_user_logged_in() ? $current_user->user_email : (isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '');

	if (! is_user_logged_in() && get_option('require_name_email') && (! $author || ! is_email($email))) {
		wp_send_json_error(array('message' => __('请填写昵称和有效邮箱。', 'oldbook')));
	}

	if ($email && ! is_email($email)) {
		wp_send_json_error(array('message' => __('邮箱格式不正确。', 'oldbook')));
	}

	$comment_id = wp_new_comment(
		wp_slash(
			array(
				'comment_post_ID'      => $post_id,
				'comment_content'      => $content,
				'comment_author'       => $author,
				'comment_author_email' => $email,
				'comment_author_url'   => '',
				'comment_author_IP'    => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
				'comment_agent'        => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
				'comment_type'         => '',
				'comment_parent'       => 0,
				'user_id'              => get_current_user_id(),
			)
		),
		true
	);

	if (is_wp_error($comment_id)) {
		wp_send_json_error(array('message' => $comment_id->get_error_message()));
	}

	$comment  = get_comment($comment_id);
	$approved = $comment && 1 === (int) $comment->comment_approved;

	wp_send_json_success(
		array(
			'approved' => $approved,
			'count'    => get_comments_number($post_id),
			'html'     => $approved ? oldbook_render_comment_item($comment) : '',
			'message'  => $approved ? __('评论已发布。', 'oldbook') : __('评论已提交，等待审核。', 'oldbook'),
		)
	);
}
add_action('wp_ajax_oldbook_add_comment', 'oldbook_ajax_add_comment');
add_action('wp_ajax_nopriv_oldbook_add_comment', 'oldbook_ajax_add_comment');
