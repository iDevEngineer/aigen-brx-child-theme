<?php 

function aigen_disable_bundled_theme_install() {
$options = get_option('aigen_security_options'); 
if (isset($options['disable_bundled_theme_install'])) { 
define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true); 
} 
} 
add_action('init', 'aigen_disable_bundled_theme_install');

function aigen_disable_bundled_theme_install_setting_field() {
add_settings_field( 
'disable_bundled_theme_install', 
__('Disable Bundled Theme Install', 'aigen'), 
'aigen_disable_bundled_theme_install_callback', 
'aigen-security', 
'aigen_security_main_section' 
); 
} 
add_action('admin_init', 'aigen_disable_bundled_theme_install_setting_field');

function aigen_disable_bundled_theme_install_callback() {
$options = get_option('aigen_security_options'); 
?> 
<input type="checkbox" name="aigen_security_options[disable_bundled_theme_install]" value="1" <?php checked(isset($options['disable_bundled_theme_install']), 1); ?>>
<p><?php esc_html_e('Enabling this setting will disable bundled theme install when upgrading WordPress.', 'aigen'); ?></p>
<?php 
} 
