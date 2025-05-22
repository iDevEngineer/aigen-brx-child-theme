<?php
// Register the setting and add the settings field
function aigen_register_emoji_setting() {
    // Register the setting
    register_setting(
        'aigen_security_options_group', // Option group
        'aigen_security_options'        // Option name
    );

    // Add the settings field
    add_settings_field(
        'disable_wp_emojicons',             
        __('Disable Emoji Support', 'aigen'), 
        'aigen_disable_wp_emojicons_callback',
        'aigen-security',                     
        'aigen_security_main_section'         
    );
}
add_action('admin_init', 'aigen_register_emoji_setting');

// Callback function to render the checkbox
function aigen_disable_wp_emojicons_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[disable_wp_emojicons]" value="1" <?php checked(isset($options['disable_wp_emojicons']), 1); ?>>
    <p><?php esc_html_e('Check this box to disable emoji support in WordPress both frontend and wp-admin.', 'aigen'); ?></p>
    <?php
}

// Function to disable emoji support on the front-end if the setting is enabled
function aigen_maybe_disable_wp_emojicons() {
    $options = get_option('aigen_security_options');
    if ( isset($options['disable_wp_emojicons']) && $options['disable_wp_emojicons'] == 1 ) {
        // Front-end removal
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Feeds
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');

        // Emails
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        // Embeds
        remove_action('embed_head', 'print_emoji_detection_script');
        remove_action('embed_print_styles', 'print_emoji_styles');

        // TinyMCE editor: Remove the emoji plugin
        add_filter('tiny_mce_plugins', 'aigen_disable_emojicons_tinymce');
    }
}
add_action('init', 'aigen_maybe_disable_wp_emojicons');

// Function to disable emoji support in the admin area if the setting is enabled
function aigen_disable_wp_emojicons_admin() {
    $options = get_option('aigen_security_options');
    if ( isset($options['disable_wp_emojicons']) && $options['disable_wp_emojicons'] == 1 ) {
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }
}
add_action('admin_init', 'aigen_disable_wp_emojicons_admin');

// Filter function to remove the emoji plugin from TinyMCE
function aigen_disable_emojicons_tinymce($plugins) {
    if ( is_array($plugins) ) {
        return array_diff($plugins, array('wpemoji'));
    }
    return array();
}
?>
