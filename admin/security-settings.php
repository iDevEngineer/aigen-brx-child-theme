<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once AIGEN_PATH . 'admin/disable-xmlrpc.php'; 
require_once AIGEN_PATH . 'admin/disable-wp-json-if-not-logged-in.php'; 
require_once AIGEN_PATH . 'admin/disable-file-editing.php'; 
require_once AIGEN_PATH . 'admin/remove-rss.php'; 
require_once AIGEN_PATH . 'admin/remove-wp-version.php'; 
require_once AIGEN_PATH . 'admin/disable-bundled-theme-install.php'; 

function aigen_add_security_submenu() {
    add_submenu_page(
        'aigen-settings',
        'Security Settings',
        'Security Settings',
        'manage_options',
        'aigen-security',
        'aigen_security_page_callback'
    );
}
add_action('admin_menu', 'aigen_add_security_submenu');

function aigen_security_page_callback() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Security Settings', 'aigen' ); ?></h1>
 
        <?php
            settings_errors();
        ?>
 
        <form method="post" action="options.php">
            <?php
                settings_fields( 'aigen_security_settings_group' );
                do_settings_sections( 'aigen-security' );
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

function aigen_security_settings_init() {
    register_setting(
        'aigen_security_settings_group',
        'aigen_security_options'
    );

    add_settings_section(
        'aigen_security_main_section',
        __( 'Main Settings', 'aigen' ),
        'aigen_security_section_callback',
        'aigen-security'
    );
}
add_action( 'admin_init', 'aigen_security_settings_init' );

function aigen_security_section_callback() {
    echo '<p>' . esc_html__( 'Configure your security settings below:', 'aigen' ) . '</p>';
}

function aigen_math_captcha_callback() {
    $options = get_option('aigen_security_options');
    ?>
    <style> 
    [type="checkbox"]{
        width: 18px !important;
        height: 18px !important;
        float: left;
        margin-right: 10px !important;
    }
    </style>
    <input type="checkbox" name="aigen_security_options[enable_math_captcha]" value="1" <?php checked(isset($options['enable_math_captcha']) && $options['enable_math_captcha'], 1); ?>>
    <p><?php esc_html_e( 'Enable this setting to add a math captcha challenge on the login page to improve security.', 'aigen' ); ?></p>
    <?php
}
?>
