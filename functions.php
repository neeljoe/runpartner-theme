<?php
/**
 * RunPartner Theme Functions
 *
 * @package RunPartner
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detect featured query classNames on core/query blocks via render_block_data.
 *
 * The query_loop_block_query_vars filter receives inner blocks (e.g. post-template),
 * not the core/query block itself, so $block->parsed_block['attrs']['className']
 * is the inner block's class — not the parent query block's class.
 *
 * Instead, we intercept the core/query block during rendering, capture its queryId
 * when it has our target className, and match on queryId in the query_loop filter.
 */

add_filter('render_block_data', 'runpartner_track_featured_query_blocks', 10, 1);
function runpartner_track_featured_query_blocks(array $parsed_block): array {
    if ($parsed_block['blockName'] !== 'core/query') {
        return $parsed_block;
    }
    $class_name = $parsed_block['attrs']['className'] ?? '';

    if (str_contains($class_name, 'featured-front-page-query')) {
        $GLOBALS['_rp_featured_front_page_query_id'] = $parsed_block['attrs']['queryId'] ?? null;
    }
    if (str_contains($class_name, 'featured-events-query')) {
        $GLOBALS['_rp_featured_events_query_id'] = $parsed_block['attrs']['queryId'] ?? null;
    }

    return $parsed_block;
}

// Injects meta_key/meta_value to filter events by _rp_event_featured_front_page = 1
add_filter('query_loop_block_query_vars', 'runpartner_featured_front_page_query', 10, 2);
function runpartner_featured_front_page_query(array $query, WP_Block $block): array {
    $target_id = $GLOBALS['_rp_featured_front_page_query_id'] ?? null;
    if ($target_id !== null && ($block->context['queryId'] ?? null) === $target_id) {
        $query['meta_key'] = '_rp_event_featured_front_page';
        $query['meta_value'] = '1';
    }

    return $query;
}

// Injects meta_key/meta_value to filter events by _rp_event_featured = 1
add_filter('query_loop_block_query_vars', 'runpartner_featured_events_query', 10, 2);
function runpartner_featured_events_query(array $query, WP_Block $block): array {
    $target_id = $GLOBALS['_rp_featured_events_query_id'] ?? null;
    if ($target_id !== null && ($block->context['queryId'] ?? null) === $target_id) {
        $query['meta_key'] = '_rp_event_featured';
        $query['meta_value'] = '1';
    }

    return $query;
}

// Ensures className from core/query block attributes renders as CSS class on frontend
add_filter('render_block', 'runpartner_add_featured_query_class', 10, 2);
function runpartner_add_featured_query_class(string $block_content, array $block): string {
    if ($block['blockName'] !== 'core/query') {
        return $block_content;
    }
    $class_name = $block['attrs']['className'] ?? '';
    if (!str_contains($class_name, 'featured-front-page-query') && !str_contains($class_name, 'featured-events-query')) {
        return $block_content;
    }
    $classes = esc_attr($class_name);
    $block_content = preg_replace(
        '/class="wp-block-query/',
        'class="wp-block-query ' . $classes,
        $block_content,
        1
    );
    return $block_content;
}

// Injects image credit from attachment caption into cover blocks with useFeaturedImage
// Skips coach/athlete post types — credit handled by portrait filter instead
add_filter('render_block_core/cover', 'runpartner_cover_image_credit', 10, 2);
function runpartner_cover_image_credit(string $block_content, array $block): string {
	if (empty($block['attrs']['useFeaturedImage'])) {
		return $block_content;
	}

	$post_id = $block['context']['postId'] ?? get_the_ID();
	if (!$post_id || !has_post_thumbnail($post_id)) {
		return $block_content;
	}

	if (in_array(get_post_type($post_id), ['coach', 'athlete'], true)) {
		return $block_content;
	}

	$caption = wp_get_attachment_caption(get_post_thumbnail_id($post_id));
	if (empty($caption)) {
		return $block_content;
	}

	$credit = sprintf(
		'<div class="wp-block-cover__image-credit">%s</div>',
		esc_html($caption)
	);

	$pos = strrpos($block_content, '</div>');
	if ($pos !== false) {
		$block_content = substr_replace($block_content, $credit . "\n", $pos, 0);
	}

	return $block_content;
}

// Injects image credit below the portrait on coach and athlete single pages
add_filter('render_block_core/post-featured-image', 'runpartner_portrait_image_credit', 10, 2);
function runpartner_portrait_image_credit(string $block_content, array $block): string {
	$post_id = $block['context']['postId'] ?? get_the_ID();
	if (!$post_id || !has_post_thumbnail($post_id)) {
		return $block_content;
	}

	if (!in_array(get_post_type($post_id), ['coach', 'athlete'], true)) {
		return $block_content;
	}

	$caption = wp_get_attachment_caption(get_post_thumbnail_id($post_id));
	if (empty($caption)) {
		return $block_content;
	}

	$credit = sprintf(
		'<div class="wp-block-post-featured-image__credit">%s</div>',
		esc_html($caption)
	);

	return $block_content . "\n" . $credit;
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