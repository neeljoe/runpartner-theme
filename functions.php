<?php
function fictional_university_child_styles() {
    wp_enqueue_style(
        'fictional-university-child-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'fictional_university_child_styles' );