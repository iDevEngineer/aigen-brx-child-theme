<?php

define( 'AIGEN_PATH', trailingslashit( get_stylesheet_directory() ) );
define( 'AIGEN_PATH_ASSETS', trailingslashit( AIGEN_PATH . 'assets' ) );
define( 'AIGEN_URL', trailingslashit( get_stylesheet_directory_uri() ) );
define( 'AIGEN_URL_ASSETS', trailingslashit( AIGEN_URL . 'assets' ) );

// Main Features & Settings
require_once AIGEN_PATH . 'includes/settings-page.php';
require_once AIGEN_PATH . 'admin/404-logging.php';
require_once AIGEN_PATH . 'admin/301-redirects.php';
// require_once AIGEN_PATH . 'admin/security-settings.php';
require_once AIGEN_PATH . 'admin/smtp-settings.php';
require_once AIGEN_PATH . 'admin/homepage-categories.php';


// Register Custom Bricks Builder Elements
add_action('init', function () {
\Bricks\Elements::register_element(AIGEN_PATH . 'includes/elements/link-wrapper.php');
\Bricks\Elements::register_element(AIGEN_PATH . 'includes/elements/parent-link.php');

}, 11);