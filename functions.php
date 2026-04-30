<?php
/**
 * RunPartner Theme Functions
 *
 * @package RunPartner
 */

if (!defined('ABSPATH')) {
    exit;
}

// Step 1: Enable interactivity on core/group block for server-side directive processing
add_filter('block_type_metadata_settings', 'runpartner_enable_block_interactivity');

function runpartner_enable_block_interactivity(array $settings) {
    if (isset($settings['name']) && 'core/group' === $settings['name']) {
        $settings['supports']['interactivity'] = true;
    }
    return $settings;
}

// Step 2: Enqueue theme stylesheet
add_action('wp_enqueue_scripts', 'runpartner_enqueue_styles');
function runpartner_enqueue_styles() {
    wp_enqueue_style(
        'runpartner-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}

// Step 3: Enqueue interactivity script module
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

// Step 4: Add interactivity directives to header group via render filter
add_filter('render_block_core/group', 'runpartner_render_header_interactivity', 10, 2);

function runpartner_render_header_interactivity(string $content, array $block) {
    // Only process if this is the header group (identified by class)
    if (strpos($content, 'runpartner-header') === false) {
        return $content;
    }

    $processor = new WP_HTML_Tag_Processor($content);

    if (!$processor->next_tag(['class_name' => 'runpartner-header'])) {
        return $processor->get_updated_html();
    }

    // Add interactivity directives programmatically
    $processor->set_attribute('data-wp-interactive', 'runpartner');
    $processor->set_attribute('data-wp-init', 'callbacks.initScroll');
    $processor->set_attribute('data-wp-class--is-hidden', 'state.isHidden');

    return $processor->get_updated_html();
}