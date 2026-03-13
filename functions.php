<?php

function fictional_university_assets() {

    /*
    -----------------------------------------
    Frontend + Shared CSS
    -----------------------------------------
    */

    $style_path = get_stylesheet_directory() . '/build/style-index.css';

    if ( file_exists( $style_path ) ) {
        wp_enqueue_style(
            'fictional-university-style',
            get_stylesheet_directory_uri() . '/build/style-index.css',
            array(),
            wp_get_theme()->get('Version')
        );
    }

    /*
    -----------------------------------------
    Frontend JS
    -----------------------------------------
    */

    $asset_path = get_stylesheet_directory() . '/build/index.asset.php';

    if ( file_exists( $asset_path ) ) {

        $asset = include $asset_path;

        wp_enqueue_script(
            'fictional-university-js',
            get_stylesheet_directory_uri() . '/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

}

add_action( 'wp_enqueue_scripts', 'fictional_university_assets' );
function fictional_university_editor_assets() {

    $editor_style = get_stylesheet_directory() . '/build/style-index.css';

    if ( file_exists( $editor_style ) ) {
        wp_enqueue_style(
            'fictional-university-editor-style',
            get_stylesheet_directory_uri() . '/build/style-index.css',
            array(),
            wp_get_theme()->get('Version')
        );
    }

}

add_action( 'enqueue_block_editor_assets', 'fictional_university_editor_assets' );
function runpartner_pattern_category() {
    register_block_pattern_category(
        'runpartner',
        array(
            'label' => __('RunPartner Patterns', 'runpartner')
        )
    );
}
add_action('init', 'runpartner_pattern_category');

