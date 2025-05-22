<?php

function show_homepage_categories_shortcode() {
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return '<p>No categories found.</p>';
    }

    $shown = [];

    foreach ($terms as $term) {
        $show = get_field('show_on_page', 'product_cat_' . $term->term_id);

        if ($show) {
            $sort_order = get_field('sort_order', 'product_cat_' . $term->term_id) ?: 999;
            $shown[] = [
                'term'  => $term,
                'order' => $sort_order,
            ];
        }
    }

    // Sort by custom ACF sort_order
    usort($shown, function($a, $b) {
        return $a['order'] - $b['order'];
    });

    // Limit to 8 categories
    $shown = array_slice($shown, 0, 8);

    if (empty($shown)) {
        return '<p>No categories marked for homepage display.</p>';
    }

    ob_start();
    echo '<ul class="homepage-categories">';
    foreach ($shown as $item) {
        $term = $item['term'];
        $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        $image_url = wp_get_attachment_url($thumbnail_id) ?: 'https://via.placeholder.com/300';
        $term_link = get_term_link($term);

        echo '<li class="category-item">';
        echo '<a href="' . esc_url($term_link) . '">';
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($term->name) . '" />';
        echo '<h2>' . esc_html($term->name) . '</h2>';
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('homepage_categories', 'show_homepage_categories_shortcode');
