<?php

function aigen_disable_xmlrpc($enabled) {
    $options = get_option('aigen_security_options');
    if (isset($options['disable_xmlrpc'])) {
        return false;
    }
    return $enabled;
}
add_filter('xmlrpc_enabled', 'aigen_disable_xmlrpc');

function aigen_disable_xmlrpc_setting_field() {
    add_settings_field(
        'disable_xmlrpc',
        __('Disable XML-RPC', 'aigen'),
        'aigen_disable_xmlrpc_callback',
        'aigen-security',
        'aigen_security_main_section'
    );
}
add_action('admin_init', 'aigen_disable_xmlrpc_setting_field');

function aigen_disable_xmlrpc_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[disable_xmlrpc]" value="1" <?php checked(isset($options['disable_xmlrpc']), 1); ?>>
    <p><?php esc_html_e('Enabling this setting will disable the XML-RPC functionality in WordPress.', 'aigen'); ?></p>
    <?php
}
?>
