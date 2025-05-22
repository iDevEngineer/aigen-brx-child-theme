<?php
// Register the main dashboard menu page
add_action('admin_menu', 'aigen_add_admin_menu');
function aigen_add_admin_menu() {
    add_menu_page(
        'Aigen Settings',
        get_option('aigen_menu_title', 'Aigen Settings'),
        'manage_options',
        'aigen-settings',
        'aigen_settings_page',
        'dashicons-admin-generic',
        99
    );

    // Register subpages here only if they have their own callbacks
    // Example:
    // add_submenu_page('aigen-settings', '301 Redirects', '301 Redirects', 'manage_options', 'aigen-301-redirects', 'aigen_301_redirects_page_callback');
}

// Settings page content with dashboard buttons
function aigen_settings_page() {
    $menu_title = get_option('aigen_menu_title', 'Aigen Settings');

    $menu_items = [
        ['slug' => 'aigen-settings',              'label' => $menu_title.' Settings', 'dashicon' => 'dashicons-admin-home'],
        ['slug' => 'aigen-301-redirects',        'label' => '301 Redirects',            'dashicon' => 'dashicons-share'],
        ['slug' => 'aigen-404-logs',             'label' => '404 Logs',                 'dashicon' => 'dashicons-warning'],
        // ['slug' => 'aigen-security',             'label' => 'Security Settings',        'dashicon' => 'dashicons-shield'],
        ['slug' => 'aigen-smtp-settings',        'label' => 'SMTP Mail Settings',       'dashicon' => 'dashicons-email'],
        ['slug' => 'aigen-homepage-categories',        'label' => 'Homepage Categories',       'dashicon' => 'dashicons-email'],
    ];
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($menu_title); ?> - Bricks Builder Child Theme Settings</h1>

        <div class="aigen-dashboard-buttons">
            <?php foreach ($menu_items as $item): 
                $url = admin_url('admin.php?page=' . $item['slug']);
            ?>
                <a href="<?php echo esc_url($url); ?>" class="aigen-dashboard-button">
                    <span class="dashicons <?php echo esc_attr($item['dashicon']); ?>"></span>
                    <span class="button-label"><?php echo esc_html($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('aigen_settings_group');
            do_settings_sections('aigen-settings');
            submit_button();
            ?>
        </form>
    </div>

    <style>
        .aigen-dashboard-buttons {
            max-width: 1000px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        .aigen-dashboard-button {
            background: #fff;
            border: 1px solid #ccc;
            padding: 20px 10px;
            text-align: center;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s, border-color 0.2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .aigen-dashboard-button:hover {
            transform: scale(1.05);
            border-color: #0073aa;
        }
        .aigen-dashboard-button .dashicons {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .aigen-dashboard-button .button-label {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }
    </style>
    <?php
}

// Register "White Label Name" setting
add_action('admin_init', 'aigen_register_settings');
function aigen_register_settings() {
    register_setting('aigen_settings_group', 'aigen_menu_title');

    add_settings_section(
        'aigen_general_section',
        'General Settings',
        'aigen_general_section_callback',
        'aigen-settings'
    );

    add_settings_field(
        'aigen_menu_title_field',
        'White Label Name',
        'aigen_menu_title_field_callback',
        'aigen-settings',
        'aigen_general_section'
    );
}

function aigen_general_section_callback() {
    echo '<p>Customize the admin menu title for the Aigen Settings dashboard.</p>';
}

function aigen_menu_title_field_callback() {
    $value = get_option('aigen_menu_title', 'Aigen Settings');
    echo '<input type="text" name="aigen_menu_title" value="' . esc_attr($value) . '" class="regular-text">';
}
