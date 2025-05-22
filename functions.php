<?php

define( 'AIGEN_PATH', trailingslashit( get_stylesheet_directory() ) );
define( 'AIGEN_PATH_ASSETS', trailingslashit( AIGEN_PATH . 'assets' ) );
define( 'AIGEN_URL', trailingslashit( get_stylesheet_directory_uri() ) );
define( 'AIGEN_URL_ASSETS', trailingslashit( AIGEN_URL . 'assets' ) );

// Main Features & Settings
require_once AIGEN_PATH . 'includes/settings-page.php';
require_once AIGEN_PATH . 'admin/404-logging.php';
require_once AIGEN_PATH . 'admin/301-redirects.php';
require_once AIGEN_PATH . 'admin/smtp-settings.php';
require_once AIGEN_PATH . 'admin/mail-logging.php';
require_once AIGEN_PATH . 'admin/homepage-categories.php';
require_once AIGEN_PATH . 'admin/search-logging.php';


require_once AIGEN_PATH . 'admin/security-settings.php';
require_once AIGEN_PATH . 'admin/disable-gravatar.php';
require_once AIGEN_PATH . 'admin/disable-emojis.php';
require_once AIGEN_PATH . 'admin/disable-xmlrpc.php';
require_once AIGEN_PATH . 'admin/disable-file-editing.php';
require_once AIGEN_PATH . 'admin/disable-bundled-theme-install.php';
require_once AIGEN_PATH . 'admin/disable-wp-json-if-not-logged-in.php';
require_once AIGEN_PATH . 'admin/remove-rss.php';


// Register Custom Bricks Builder Elements
add_action('init', function () {
\Bricks\Elements::register_element(AIGEN_PATH . 'includes/elements/link-wrapper.php');
\Bricks\Elements::register_element(AIGEN_PATH . 'includes/elements/parent-link.php');

}, 11);