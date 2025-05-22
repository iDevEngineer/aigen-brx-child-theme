<?php
// Register the setting and add the settings field
function aigen_register_gravatar_setting() {
    // Register the setting
    register_setting(
        'aigen_security_options_group', // Option group
        'aigen_security_options'        // Option name
    );

    // Add the settings field
    add_settings_field(
        'disable_gravatar',           
        __('Disable Gravatar Support', 'aigen'), 
        'aigen_disable_gravatar_callback', 
        'aigen-security',               
        'aigen_security_main_section'   
    );
}
add_action('admin_init', 'aigen_register_gravatar_setting');

// Callback function to render the checkbox
function aigen_disable_gravatar_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[disable_gravatar]" value="1" <?php checked(isset($options['disable_gravatar']), 1); ?>>
    <p><?php esc_html_e('Check this box to disable Gravatar support throughout the site.', 'aigen'); ?></p>
    <?php
}

// Function to disable Gravatar support if the setting is enabled
function aigen_maybe_disable_gravatar() {
    $options = get_option('aigen_security_options');
    if (isset($options['disable_gravatar']) && $options['disable_gravatar'] == 1) {
        // Disable Gravatar throughout the site
        add_filter('get_avatar', 'aigen_disable_gravatar', 10, 2);
    }
}
add_action('init', 'aigen_maybe_disable_gravatar');

// Function to return an empty string, effectively disabling Gravatar
function aigen_disable_gravatar($avatar, $id_or_email) {
    return '';
}
?>
