<?php


function aigen_remove_wp_version() {
    $options = get_option('aigen_security_options');
    if (isset($options['remove_wp_version'])) {
        return '';
    }
    return;
}
add_filter('the_generator', 'aigen_remove_wp_version');


function aigen_remove_wp_version_setting_field() {
    add_settings_field(
        'remove_wp_version',
        __('Remove/Hide WP Version', 'aigen'),
        'aigen_remove_wp_version_callback',
        'aigen-security',
        'aigen_security_main_section'
    );
}
add_action('admin_init', 'aigen_remove_wp_version_setting_field');


function aigen_remove_wp_version_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[remove_wp_version]" value="1" <?php checked(isset($options['remove_wp_version']), 1); ?>>
    <p><?php esc_html_e('Enabling this setting will remove the WordPress version number from your website\'s HTML source code.', 'aigen'); ?></p>
    <?php
}
?>
