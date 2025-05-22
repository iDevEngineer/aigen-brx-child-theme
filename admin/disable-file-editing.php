<?php

function aigen_disable_file_edit() {
    $options = get_option('aigen_security_options');
    if (isset($options['disable_file_edit'])) {
        define('DISALLOW_FILE_EDIT', true);
    }
}
add_action('init', 'aigen_disable_file_edit');

function aigen_disable_file_edit_setting_field() {
    add_settings_field(
        'disable_file_edit',
        __('Disable File Editing', 'aigen'),
        'aigen_disable_file_edit_callback',
        'aigen-security',
        'aigen_security_main_section'
    );
}
add_action('admin_init', 'aigen_disable_file_edit_setting_field');

function aigen_disable_file_edit_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[disable_file_edit]" value="1" <?php checked(isset($options['disable_file_edit']), 1); ?>>
    <p><?php esc_html_e('Enabling this setting will disable file editing from the WordPress dashboard.', 'aigen'); ?></p>
    <?php
}
?>
