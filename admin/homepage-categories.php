<?php

function aigen_register_homepage_categories_post_type() {
    register_post_type('aigen_homepage_categories', array(
        'public'  => false,
        'show_ui' => false
    ));
}
add_action('init', 'aigen_register_homepage_categories_post_type');

function aigen_add_home_categories_page() {
    add_submenu_page(
        'aigen-settings',
        'Homepage Categories',
        'Homepage Categories',
        'manage_options',
        'aigen-homepage-categories',
        'aigen_render_home_categories_page'
    );
}
add_action('admin_menu', 'aigen_add_home_categories_page');

function aigen_render_home_categories_page() {
    // Exit early if ACF isn't loaded
    if ( ! function_exists('get_field') ) {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> Advanced Custom Fields (ACF) plugin is not active. This page requires ACF.</p></div>';
        return;
    }

    if (isset($_POST['save_categories'])) {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ]);

        foreach ($terms as $term) {
            $term_id = $term->term_id;

            // Use submitted value or fallback to unchecked
            $data = $_POST['category'][$term_id] ?? [];
            $show  = isset($data['show']) && $data['show'] == '1' ? 1 : 0;
            $order = isset($data['order']) ? intval($data['order']) : null;

            update_field('show_on_page', $show, 'product_cat_' . $term_id);

            if (!is_null($order)) {
                update_field('sort_order', $order, 'product_cat_' . $term_id);
            }
        }

        echo '<div class="updated"><p>Changes saved!</p></div>';
    }

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);

    // Sort alphabetically by name
    usort($terms, function($a, $b) {
        return strcmp($a->name, $b->name);
    });

    echo '<div class="wrap">';
    echo '<h1>Homepage Categories</h1>';
    echo '<form method="post">';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Category</th><th>Show on Homepage</th><th>Sort Order</th></tr></thead>';
    echo '<tbody>';

    foreach ($terms as $term) {
        $show = get_field('show_on_page', 'product_cat_' . $term->term_id);
        $order = get_field('sort_order', 'product_cat_' . $term->term_id);

        echo '<tr>';
        echo '<td>' . esc_html($term->name) . '</td>';
        echo '<td>';
        echo '<input type="checkbox" name="category[' . $term->term_id . '][show]" value="1"' . checked($show, 1, false) . '>';
        echo '</td>';
        echo '<td><input type="number" name="category[' . $term->term_id . '][order]" value="' . esc_attr($order) . '" style="width:60px;"></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<p><input type="submit" name="save_categories" class="button button-primary" value="Save Changes"></p>';
    echo '</form>';
    echo '</div>';
}
