<?php
/**
 * RunPartner Theme Functions
 *
 * @package RunPartner
 */

if (!defined('ABSPATH')) {
    exit;
}

// Support featured-events-query className on core/query blocks
// Injects meta_key/meta_value to filter events by _rp_event_featured = 1
add_filter('query_loop_block_query_vars', 'runpartner_featured_events_query', 10, 2);
function runpartner_featured_events_query(array $query, WP_Block $block): array {
    $attrs = $block->parsed_block['attrs'] ?? [];
    $class_name = $attrs['className'] ?? '';

    if (strpos($class_name, 'featured-events-query') !== false) {
        $query['meta_key'] = '_rp_event_featured';
        $query['meta_value'] = '1';
    }

    return $query;
}

// Enqueue theme stylesheet
add_action('wp_enqueue_scripts', 'runpartner_enqueue_styles');
function runpartner_enqueue_styles() {
    wp_enqueue_style(
        'runpartner-style',
        get_theme_file_uri('public/style.css'),
        array(),
        wp_get_theme()->get('Version')
    );
}

// Enqueue interactivity script module
add_action('wp_enqueue_scripts', 'runpartner_enqueue_interactivity');

function runpartner_enqueue_interactivity() {
    $script_asset = include get_theme_file_path('public/js/interactivity.asset.php');

    wp_enqueue_script_module(
        'runpartner',
        get_theme_file_uri('public/js/interactivity.js'),
        $script_asset['dependencies'],
        $script_asset['version']
    );
}