<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aigen_register_301_redirects_post_type() {
	register_post_type(
		'aigen_301_redirects',
		array(
			'public'             => false,
			'show_ui'            => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'label'              => __( 'AIGEN 301 Redirects', 'aigen' ),
			'supports'           => array( 'title' )
		)
	);
}
add_action('init', 'aigen_register_301_redirects_post_type');

function aigen_register_redirect_logs_post_type() {
	register_post_type(
		'aigen_redirect_logs',
		array(
			'public'             => false,
			'show_ui'            => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'label'              => __( 'AIGEN Redirect Logs', 'aigen' ),
			'supports'           => array( 'title', 'custom-fields' )
		)
	);
}
add_action('init', 'aigen_register_redirect_logs_post_type');

function aigen_add_301_redirects_page() {
	add_submenu_page(
		'aigen-settings',
		__( '301 Redirects', 'aigen' ),
		__( '301 Redirects', 'aigen' ),
		'manage_options',
		'aigen-301-redirects',
		'aigen_render_301_redirects_page'
	);
}
add_action('admin_menu', 'aigen_add_301_redirects_page');

function aigen_normalize_path($url) {
	// strip domain
	$url = preg_replace('/^https?:\/\/[^\/]+/i', '', $url);
	// decode any percent-encoding (so “%C3%B6” → “ö”)
	$url = rawurldecode( $url );
	// ensure leading slash
	if (substr($url, 0, 1) !== '/') {
		$url = '/' . $url;
	}
	// remove trailing slash except for root
	if ($url !== '/' && substr($url, -1) === '/') {
		$url = rtrim($url, '/');
	}
	// lowercase using mb to handle UTF-8
	if (function_exists('mb_strtolower')) {
		$url = mb_strtolower($url, 'UTF-8');
	} else {
		$url = strtolower($url);
	}
	return $url;
}

function aigen_validate_url($url) {
	if (substr($url, 0, 1) === '/') {
		return true;
	}
	if (filter_var($url, FILTER_VALIDATE_URL)) {
		return true;
	}
	return false;
}

function aigen_render_301_redirects_page() {
	global $wpdb;

	// Handle Add Redirect
	if (isset($_POST['submit_redirect']) && check_admin_referer('aigen_301_redirect_nonce')) {
		$redirect_from = aigen_normalize_path(sanitize_text_field($_POST['redirect_from']));
		$redirect_to   = sanitize_text_field($_POST['redirect_to']);

		if (!aigen_validate_url($redirect_to)) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid redirect destination URL!', 'aigen' ) . '</p></div>';
		} else {
			$existing_redirect = get_posts(array(
				'post_type'      => 'aigen_301_redirects',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => 'redirect_from',
						'value'   => $redirect_from,
						'compare' => '='
					)
				)
			));

			if (!empty($existing_redirect)) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'A redirect for this path already exists!', 'aigen' ) . '</p></div>';
			} else {
				$post_data = array(
					'post_type'   => 'aigen_301_redirects',
					'post_status' => 'publish',
					'post_title'  => $redirect_from
				);
				$post_id = wp_insert_post($post_data);

				if ($post_id) {
					update_post_meta($post_id, 'redirect_from', $redirect_from);
					update_post_meta($post_id, 'redirect_to', $redirect_to);
					update_post_meta($post_id, 'created_date', current_time('mysql'));
					update_post_meta($post_id, 'redirect_clicks', 0);

					flush_rewrite_rules();
					echo '<div class="notice notice-success"><p>' . esc_html__( 'Redirect added successfully!', 'aigen' ) . '</p></div>';
				}
			}
		}
	}

	// Handle Delete Redirect
	if (isset($_POST['delete_redirect']) && check_admin_referer('aigen_301_redirect_delete_nonce')) {
		$post_id = intval($_POST['redirect_id']);
		if (wp_delete_post($post_id, true)) {
			flush_rewrite_rules();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Redirect deleted successfully!', 'aigen' ) . '</p></div>';
		}
	}

	// Handle Edit Redirect
	if (isset($_POST['edit_redirect']) && check_admin_referer('aigen_301_redirect_edit_nonce')) {
		$post_id = intval($_POST['redirect_id']);
		$new_redirect_from = aigen_normalize_path(sanitize_text_field($_POST['edit_redirect_from']));
		$new_redirect_to   = sanitize_text_field($_POST['edit_redirect_to']);

		if (!aigen_validate_url($new_redirect_to)) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid redirect destination URL!', 'aigen' ) . '</p></div>';
		} else {
			$existing_redirect = get_posts(array(
				'post_type'      => 'aigen_301_redirects',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => 'redirect_from',
						'value'   => $new_redirect_from,
						'compare' => '='
					)
				),
				'exclude'        => array($post_id),
			));

			if (!empty($existing_redirect)) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'A redirect for this path already exists!', 'aigen' ) . '</p></div>';
			} else {
				wp_update_post(array(
					'ID'         => $post_id,
					'post_title' => $new_redirect_from,
				));
				update_post_meta($post_id, 'redirect_from', $new_redirect_from);
				update_post_meta($post_id, 'redirect_to', $new_redirect_to);

				echo '<div class="notice notice-success"><p>' . esc_html__( 'Redirect updated successfully!', 'aigen' ) . '</p></div>';
			}
		}
	}

	// Handle Clear All Logs
	if (isset($_POST['clear_all_logs']) && check_admin_referer('aigen_301_clear_logs_nonce')) {
		$all_logs = get_posts(array(
			'post_type'      => 'aigen_redirect_logs',
			'posts_per_page' => -1,
			'post_status'    => 'publish'
		));
		if (!empty($all_logs)) {
			foreach ($all_logs as $log_post) {
				wp_delete_post($log_post->ID, true);
			}
		}
		echo '<div class="notice notice-success"><p>' . esc_html__( 'All logs have been cleared!', 'aigen' ) . '</p></div>';
	}

	// Handle Update Settings (Maximum Logs & Days to Keep Logs)
	if (isset($_POST['save_settings']) && check_admin_referer('aigen_301_update_settings_nonce')) {
		$max_logs = intval($_POST['max_logs_to_keep']);
		$days_to_keep = intval($_POST['days_to_keep_logs']);
		$error = false;
		if ($max_logs < 1) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'The maximum number of logs must be at least 1.', 'aigen' ) . '</p></div>';
			$error = true;
		}
		if ($days_to_keep < 1) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'The number of days must be at least 1.', 'aigen' ) . '</p></div>';
			$error = true;
		}
		if (!$error) {
			update_option('aigen_max_logs_to_keep', $max_logs);
			update_option('aigen_days_to_keep_logs', $days_to_keep);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings updated successfully!', 'aigen' ) . '</p></div>';
		}
	}

	$max_logs = get_option('aigen_max_logs_to_keep', 100);
	$days_to_keep = get_option('aigen_days_to_keep_logs', 30);
	$recent_logs = get_posts(array(
		'post_type'      => 'aigen_redirect_logs',
		'posts_per_page' => $max_logs,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	));
	?>

	<div class="wrap">

		<h2 class="nav-tab-wrapper">
			<a href="#tab1" class="nav-tab nav-tab-active" data-tab="tab1"><?php esc_html_e( '301 Redirect Rules', 'aigen' ); ?></a>
			<a href="#tab2" class="nav-tab" data-tab="tab2"><?php esc_html_e( 'Recent Redirect Logs', 'aigen' ); ?></a>
		</h2>

		<div id="tab1" class="tab-content" style="display: block;">
			<h1><?php esc_html_e( '301 Redirect Rules', 'aigen' ); ?></h1>

			<button id="show-add-redirect-form" class="button button-primary" style="margin-bottom: 15px;"><?php esc_html_e( 'Add Redirect', 'aigen' ); ?></button>

			<div class="postbox" id="add-redirect-form" style="display: none;">
				<div class="inside">
					<form method="post" action="">
						<?php wp_nonce_field('aigen_301_redirect_nonce'); ?>
						<table class="form-table">
							<tr>
								<th>
									<label for="redirect_from"><?php esc_html_e( 'Redirect From', 'aigen' ); ?></label>
									<p class="description"><?php esc_html_e( 'Enter the path (e.g., /old-page or /category/old-post). Use /* at the end to match everything after.', 'aigen' ); ?></p>
								</th>
								<td>
									<input type="text" id="redirect_from" name="redirect_from" class="regular-text" required>
								</td>
							</tr>
							<tr>
								<th>
									<label for="redirect_to"><?php esc_html_e( 'Redirect To', 'aigen' ); ?></label>
									<p class="description"><?php esc_html_e( 'Enter the full URL or path (e.g., https://example.com/new-page or /new-page). Use /* at the end if you used /* in the "Redirect From."', 'aigen' ); ?></p>
								</th>
								<td>
									<input type="text" id="redirect_to" name="redirect_to" class="regular-text" required>
								</td>
							</tr>
						</table>
						<p class="submit">
							<input type="submit" name="submit_redirect" class="button button-primary" value="<?php esc_attr_e( 'Add Redirect', 'aigen' ); ?>">
							<button type="button" id="cancel-add-redirect" class="button"><?php esc_html_e( 'Cancel', 'aigen' ); ?></button>
						</p>
					</form>
				</div>
			</div>

			<?php
			$redirects = get_posts(array(
				'post_type'      => 'aigen_301_redirects',
				'posts_per_page' => -1,
				'meta_key'       => 'redirect_clicks',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC'
			));

			if ($redirects) : ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Redirect From', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Redirect To', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Added Date', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Clicks', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'aigen' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($redirects as $redirect) :
						$redirect_id   = $redirect->ID;
						$redirect_from = get_post_meta($redirect_id, 'redirect_from', true);
						$redirect_to   = get_post_meta($redirect_id, 'redirect_to', true);
						$created_date  = get_post_meta($redirect_id, 'created_date', true);
						$clicks        = (int) get_post_meta($redirect_id, 'redirect_clicks', true);
						?>
						<tr id="redirect-row-<?php echo esc_attr($redirect_id); ?>">
							<td>
								<a href="<?php echo esc_url(home_url($redirect_from)); ?>" target="_blank">
									<?php echo esc_html($redirect_from); ?>
								</a>
							</td>
							<td>
								<a href="<?php echo esc_url($redirect_to); ?>" target="_blank">
									<?php echo esc_html($redirect_to); ?>
								</a>
							</td>
							<td><?php echo esc_html($created_date); ?></td>
							<td><?php echo esc_html($clicks); ?></td>
							<td>
								<form method="post" action="" style="display:inline;">
									<?php wp_nonce_field('aigen_301_redirect_delete_nonce'); ?>
									<input type="hidden" name="redirect_id" value="<?php echo esc_attr($redirect_id); ?>">
									<input type="submit" name="delete_redirect" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'aigen' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this redirect?', 'aigen' ); ?>');">
								</form>
								<button
									type="button"
									class="button button-small edit-redirect"
									data-redirect-id="<?php echo esc_attr($redirect_id); ?>"
									data-redirect-from="<?php echo esc_attr($redirect_from); ?>"
									data-redirect-to="<?php echo esc_attr($redirect_to); ?>"
									>
									<?php esc_html_e( 'Edit', 'aigen' ); ?>
								</button>
							</td>
						</tr>
						<tr id="edit-form-row-<?php echo esc_attr($redirect_id); ?>" style="display: none;">
							<td colspan="5">
								<form method="post" action="">
									<?php wp_nonce_field('aigen_301_redirect_edit_nonce'); ?>
									<input type="hidden" name="edit_redirect" value="1">
									<input type="hidden" name="redirect_id" value="<?php echo esc_attr($redirect_id); ?>">
									<table class="form-table" style="margin: 0;">
										<tr>
											<th><?php esc_html_e( 'Redirect From', 'aigen' ); ?></th>
											<td>
												<input type="text" name="edit_redirect_from" id="edit-redirect-from-<?php echo esc_attr($redirect_id); ?>" class="regular-text" required>
											</td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Redirect To', 'aigen' ); ?></th>
											<td>
												<input type="text" name="edit_redirect_to" id="edit-redirect-to-<?php echo esc_attr($redirect_id); ?>" class="regular-text" required>
											</td>
										</tr>
									</table>
									<p class="submit">
										<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'aigen' ); ?></button>
										<button type="button" class="button cancel-edit" data-redirect-id="<?php echo esc_attr($redirect_id); ?>"><?php esc_html_e( 'Cancel', 'aigen' ); ?></button>
									</p>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No redirects found.', 'aigen' ); ?></p>
			<?php endif; ?>
		</div>

		<div id="tab2" class="tab-content" style="display: none;">
			<h2><?php esc_html_e( 'Recent Redirect Logs', 'aigen' ); ?></h2>

			<form method="post" action="" style="margin-bottom: 2em;">
				<?php wp_nonce_field('aigen_301_update_settings_nonce'); ?>
				<table class="form-table1">
					<tr>
						<th scope="row"><label for="max_logs_to_keep"><?php esc_html_e( 'Max number of logs to keep', 'aigen' ); ?></label></th>
						<td>
							<input type="number" id="max_logs_to_keep" name="max_logs_to_keep" value="<?php echo esc_attr($max_logs); ?>" min="1" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="days_to_keep_logs"><?php esc_html_e( 'Max days to keep logs', 'aigen' ); ?></label></th>
						<td>
							<input type="number" id="days_to_keep_logs" name="days_to_keep_logs" value="<?php echo esc_attr($days_to_keep); ?>" min="1" class="small-text">
						</td>
					</tr>
				</table>
				<p class="submit" style="margin-top:0; padding-top:0">
					<input type="submit" name="save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'aigen' ); ?>">
				</p>
			</form>

			<form method="post" action="" style="margin-bottom: 1em;">
				<?php wp_nonce_field('aigen_301_clear_logs_nonce'); ?>
				<input type="submit" name="clear_all_logs" class="button button-secondary" value="<?php esc_attr_e( 'Clear All Logs', 'aigen' ); ?>"
						onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to clear all logs? This action cannot be undone.', 'aigen' ); ?>');">
			</form>

			<?php if (!empty($recent_logs)): ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Requested URL', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Redirected URL', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'User Agent', 'aigen' ); ?></th>
							<th><?php esc_html_e( 'Referral', 'aigen' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($recent_logs as $log): ?>
							<?php
								$redirect_from = get_post_meta($log->ID, 'redirect_from', true);
								$redirect_to   = get_post_meta($log->ID, 'redirect_to', true);
								$created_date  = get_post_meta($log->ID, 'created_date', true);
								$ip_address    = get_post_meta($log->ID, 'ip_address', true);
								$user_agent    = get_post_meta($log->ID, 'user_agent', true);
								$referral      = get_post_meta($log->ID, 'referral', true);
							?>
							<tr>
								<td><?php echo esc_html($created_date); ?></td>
								<td>
									<a href="<?php echo esc_url(home_url($redirect_from)); ?>" target="_blank">
										<?php echo esc_html($redirect_from); ?>
									</a>
								</td>
								<td>
									<a href="<?php echo esc_url($redirect_to); ?>" target="_blank">
										<?php echo esc_html($redirect_to); ?>
									</a>
								</td>
								<td>
									<a href="https://radar.cloudflare.com/ip/<?php echo esc_html(preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $ip_address, $matches) ? $matches[1] : ''); ?>" target="_blank" class="ip-out-cloudflare">
										<?php echo esc_html($ip_address); ?>
									</a>
								</td>
								<td><?php echo esc_html($user_agent); ?></td>
								<td><?php echo esc_html($referral); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				<p><?php esc_html_e( 'No recent logs found.', 'aigen' ); ?></p>
			<?php endif; ?>
		</div>

	</div><script>
	document.addEventListener('DOMContentLoaded', function() {
		// Simple tab switching
		const tabLinks = document.querySelectorAll('.nav-tab');
		const tabContents = document.querySelectorAll('.tab-content');

		tabLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				const target = this.getAttribute('data-tab');

				// Remove active class from all tabs
				tabLinks.forEach(function(tab) {
					tab.classList.remove('nav-tab-active');
				});
				// Hide all tab contents
				tabContents.forEach(function(content) {
					content.style.display = 'none';
				});

				// Add active class to clicked tab and show corresponding content
				this.classList.add('nav-tab-active');
				document.getElementById(target).style.display = 'block';
			});
		});

		// Hide all edit forms
		function hideAllEditForms() {
			const editFormRows = document.querySelectorAll('tr[id^="edit-form-row-"]');
			editFormRows.forEach(function(row) {
				row.style.display = 'none';
			});
		}

		// Edit button click event
		const editRedirectButtons = document.querySelectorAll('.edit-redirect');
		editRedirectButtons.forEach(function(button) {
			button.addEventListener('click', function() {
				const redirectId   = this.dataset.redirectId;
				const redirectFrom = this.dataset.redirectFrom;
				const redirectTo   = this.dataset.redirectTo;

				hideAllEditForms();

				const editFormRow = document.getElementById('edit-form-row-' + redirectId);
				if (editFormRow) {
					editFormRow.style.display = 'table-row';
				}

				document.getElementById('edit-redirect-from-' + redirectId).value = redirectFrom;
				document.getElementById('edit-redirect-to-' + redirectId).value   = redirectTo;
			});
		});

		// Cancel edit
		const cancelEditButtons = document.querySelectorAll('.cancel-edit');
		cancelEditButtons.forEach(function(button) {
			button.addEventListener('click', function() {
				const redirectId = this.dataset.redirectId;
				const editFormRow = document.getElementById('edit-form-row-' + redirectId);
				if (editFormRow) {
					editFormRow.style.display = 'none';
				}
			});
		});

		// Show Add Redirect Form
		const showAddRedirectButton = document.getElementById('show-add-redirect-form');
		const addRedirectForm = document.getElementById('add-redirect-form');
		const cancelAddRedirectButton = document.getElementById('cancel-add-redirect');

		if (showAddRedirectButton && addRedirectForm && cancelAddRedirectButton) {
			showAddRedirectButton.addEventListener('click', function() {
				addRedirectForm.style.display = 'block';
				showAddRedirectButton.style.display = 'none';
			});

			cancelAddRedirectButton.addEventListener('click', function() {
				addRedirectForm.style.display = 'none';
				showAddRedirectButton.style.display = 'inline-block';
			});
		}
	});
	</script>
	<?php
}

function aigen_handle_301_redirects() {
	if (is_admin()) return;

	$request_uri   = $_SERVER['REQUEST_URI'];
	$parsed_url    = parse_url($request_uri);
	$path          = isset($parsed_url['path']) ? rawurldecode($parsed_url['path']) : '/';
	$current_path  = aigen_normalize_path($path);
	$query_string  = isset($parsed_url['query']) ? $parsed_url['query'] : '';

	// Get all 301 redirect rules
	$redirects = get_posts(array(
		'post_type'      => 'aigen_301_redirects',
		'posts_per_page' => -1
	));

	// First process exact (non-wildcard) redirects
	foreach ($redirects as $redirect) {
		$redirect_from = get_post_meta($redirect->ID, 'redirect_from', true);
		if (substr($redirect_from, -2) !== '/*') {
			if ($redirect_from === $current_path || $redirect_from === $current_path . '?' . $query_string) {
				$redirect_to = get_post_meta($redirect->ID, 'redirect_to', true);
				if ($query_string) {
					$redirect_to .= (strpos($redirect_to, '?') !== false) ? '&' : '?';
					$redirect_to .= $query_string;
				}
				if (strpos($redirect_to, 'http') !== 0) {
					$redirect_to = home_url($redirect_to);
				}
				$clicks = (int) get_post_meta($redirect->ID, 'redirect_clicks', true);
				update_post_meta($redirect->ID, 'redirect_clicks', $clicks + 1);
				aigen_log_redirect($redirect_from, $redirect_to);
				nocache_headers();
				wp_redirect($redirect_to, 301);
				exit;
			}
		}
	}

	// Then process wildcard redirects (redirects ending with "/*")
	foreach ($redirects as $redirect) {
		$redirect_from = get_post_meta($redirect->ID, 'redirect_from', true);
		if (substr($redirect_from, -2) === '/*') {
			$redirect_to = get_post_meta($redirect->ID, 'redirect_to', true);
			$base_from = substr($redirect_from, 0, -2);
			if ($current_path === $base_from || strpos($current_path, $base_from . '/') === 0) {
				$leftover = '';
				if (strlen($current_path) > strlen($base_from)) {
					$leftover = substr($current_path, strlen($base_from));
				}
				$leftover = ltrim($leftover, '/');

				if (strpos($leftover, '..') !== false) {
					continue;
				}

				$base_to = $redirect_to;
				if (substr($redirect_to, -2) === '/*') {
					$base_to = substr($redirect_to, 0, -2);
				}
				$final_destination = rtrim($base_to, '/');
				if ($leftover !== '') {
					$final_destination .= '/' . $leftover;
				}
				if ($query_string) {
					$final_destination .= (strpos($final_destination, '?') !== false) ? '&' : '?';
					$final_destination .= $query_string;
				}
				if (strpos($final_destination, 'http') !== 0) {
					$final_destination = home_url($final_destination);
				}
				$clicks = (int) get_post_meta($redirect->ID, 'redirect_clicks', true);
				update_post_meta($redirect->ID, 'redirect_clicks', $clicks + 1);
				aigen_log_redirect($current_path, $final_destination);
				nocache_headers();
				wp_redirect($final_destination, 301);
				exit;
			}
		}
	}
}
add_action('template_redirect', 'aigen_handle_301_redirects', 0);

function aigen_log_redirect($redirect_from, $redirect_to) {
	$log_post = array(
		'post_type'   => 'aigen_redirect_logs',
		'post_title'  => sprintf( __( 'Redirect from %1$s to %2$s', 'aigen' ), $redirect_from, $redirect_to ),
		'post_status' => 'publish',
		'post_author' => 0,
	);

	$log_id = wp_insert_post($log_post);

	if ($log_id) {
		update_post_meta($log_id, 'redirect_from', $redirect_from);
		update_post_meta($log_id, 'redirect_to', $redirect_to);
		update_post_meta($log_id, 'created_date', current_time('mysql'));
		update_post_meta($log_id, 'ip_address', aigen_get_client_ip());
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		update_post_meta($log_id, 'user_agent', $user_agent);
		$referral = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		update_post_meta($log_id, 'referral', $referral);

		// Enforce the maximum number of logs and remove old ones if needed
		aigen_enforce_max_logs();
	}
}

function aigen_enforce_max_logs() {
	$days_to_keep = get_option('aigen_days_to_keep_logs', 30);
	if ($days_to_keep > 0) {
		$date_threshold = date('Y-m-d H:i:s', strtotime("-$days_to_keep days"));
		$old_logs = get_posts(array(
			'post_type'      => 'aigen_redirect_logs',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'date_query'     => array(
				array(
					'column' => 'post_date',
					'before' => $date_threshold,
				),
			),
			'fields'         => 'ids',
		));
		if (!empty($old_logs)) {
			foreach ($old_logs as $log_id) {
				wp_delete_post($log_id, true);
			}
		}
	}

	$max_logs = get_option('aigen_max_logs_to_keep', 100);
	$total_logs = wp_count_posts('aigen_redirect_logs')->publish;

	if ($total_logs > $max_logs) {
		$logs_to_delete = $total_logs - $max_logs;
		$old_logs = get_posts(array(
			'post_type'      => 'aigen_redirect_logs',
			'posts_per_page' => $logs_to_delete,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		));

		if (!empty($old_logs)) {
			foreach ($old_logs as $log_id) {
				wp_delete_post($log_id, true);
			}
		}
	}
}

function aigen_get_client_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return sanitize_text_field($ip);
}

function aigen_activate_301_redirects() {
	aigen_register_301_redirects_post_type();
	aigen_register_redirect_logs_post_type();
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'aigen_activate_301_redirects');

function aigen_deactivate_301_redirects() {
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'aigen_deactivate_301_redirects');