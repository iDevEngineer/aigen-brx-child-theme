<?php
// Apply revision limit based on post type
add_filter('wp_revisions_to_keep', function($num, $post) {
    if (!$post || !isset($post->post_type)) {
        return $num;
    }

    $settings = get_option('revision_limiter_settings', []);

    $enabled_types = $settings['enabled_types'] ?? [];
    $limits = $settings['limits'] ?? [];

    // If post type is not enabled, return 0 (disable revisions)
    if (!in_array($post->post_type, $enabled_types)) {
        return 0;
    }

    // Return custom limit or fallback to 5
    return isset($limits[$post->post_type]) ? intval($limits[$post->post_type]) : 5;
}, 10, 2);

function aigen_add_revision_history_page() {
    add_submenu_page(
        'aigen-settings',
        'Revision Limiter',
        'Revision Limiter',
        'manage_options',
        'aigen-revision-limiter',
        'child_theme_revision_limiter_page'
    );
}
add_action('admin_menu', 'aigen_add_revision_history_page');

// Settings page UI
function child_theme_revision_limiter_page() {
    $post_types = [
        'post' => 'Posts',
        'page' => 'Pages',
        'bricks_template' => 'Bricks Templates',
    ];
    $settings = get_option('revision_limiter_settings', []);
    $enabled_types = $settings['enabled_types'] ?? [];
    $limits = $settings['limits'] ?? [];

    ?>
    <div class="wrap">
        <h1>Revision Limiter</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('revision_limiter_group');
            do_settings_sections('revision-limiter');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Revisions & Set Limit</th>
                    <td>
                        <?php foreach ($post_types as $type => $label): ?>
                            <label>
                                <input type="checkbox" name="revision_limiter_settings[enabled_types][]" value="<?php echo esc_attr($type); ?>"
                                    <?php checked(in_array($type, $enabled_types)); ?>>
                                <?php echo esc_html($label); ?>
                            </label>
                            &nbsp;&nbsp;
                            Limit:
                            <input type="number" name="revision_limiter_settings[limits][<?php echo esc_attr($type); ?>]" value="<?php echo esc_attr($limits[$type] ?? 5); ?>" min="0" style="width: 60px;">
                            <br><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register setting
add_action('admin_init', function() {
    register_setting('revision_limiter_group', 'revision_limiter_settings');
});
