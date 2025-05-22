<?php

function aigen_setup_json_disable_field() {
    add_settings_field(
        'disable_json',
        __('Disable JSON API for Guests', 'aigen'),
        'aigen_json_disable_callback',
        'aigen-security',
        'aigen_security_main_section'
    );
}
add_action('admin_init', 'aigen_setup_json_disable_field');

function aigen_json_disable_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[disable_json]" value="1" <?php checked(isset($options['disable_json']), 1); ?>>
    <p><?php esc_html_e('Enabling this setting will disable the JSON API (wp-json) for users who are not logged in.', 'aigen'); ?></p>
    <?php
}

add_filter('rest_authentication_errors', function($result) {
    if (!is_user_logged_in()) {
        $options = get_option('aigen_security_options');
        if (isset($options['disable_json']) && $options['disable_json']) {
            return new WP_Error('rest_not_logged_in', __('You are not logged in.', 'aigen'), array('status' => 401));
        }
    }
    return $result;
});
?>
