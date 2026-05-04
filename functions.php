<?php
/**
 * RunPartner Theme Functions
 *
 * @package RunPartner
 */

if (!defined('ABSPATH')) {
    exit;
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