<?php


function aigen_remove_rss() {
    $options = get_option('aigen_security_options');
    if (isset($options['remove_rss'])) {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'wlwmanifest_link');
    }
}
add_action('init', 'aigen_remove_rss');




function aigen_remove_rss_setting_field() {
    add_settings_field(
        'remove_rss',
        __('Disable Remove RSS', 'aigen'),
        'aigen_remove_rss_callback',
        'aigen-security',
        'aigen_security_main_section'
    );
}
add_action('admin_init', 'aigen_remove_rss_setting_field');




function aigen_remove_rss_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <input type="checkbox" name="aigen_security_options[remove_rss]" value="1" <?php checked(isset($options['remove_rss']), 1); ?>>
    <p><?php esc_html_e('Enabling this setting will remove the RSS feed links from your website\'s HTML source code.', 'aigen'); ?></p>
    <?php
}
?>
