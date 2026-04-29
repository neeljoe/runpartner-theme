<?php
/**
 * RunPartner Theme Functions
 *
 * @package RunPartner
 */

if (!defined('ABSPATH')) {
    exit;
}

// Step 1: Enable interactivity on core blocks
add_filter('block_type_metadata_settings', 'runpartner_enable_block_interactivity');

function runpartner_enable_block_interactivity(array $settings) {
    // Enable interactivity on core/group for header
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

// Step 4: Enqueue interactivity script module
add_action('wp_enqueue_scripts', 'runpartner_enqueue_interactivity');

function runpartner_enqueue_interactivity() {
    // Initialize state early
    wp_interactivity_state('runpartner', [
        'scrollY' => 0,
        'scrollDirection' => 'none',
        'isScrolled' => false,
        'headerHidden' => false,
    ]);

    $script_asset = include get_theme_file_path('public/js/interactivity.asset.php');

    wp_enqueue_script_module(
        'runpartner',
        get_theme_file_uri('public/js/interactivity.js'),
        $script_asset['dependencies'],
        $script_asset['version']
    );
}