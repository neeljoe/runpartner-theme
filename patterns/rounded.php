<?php
/**
 * Title: Rounded Header
 * Slug: runpartner-theme/rounded
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Rounded navigation bar with site title and navigation.
 *
 * @package WordPress
 * @subpackage Runpartner_Theme
 * @since Runpartner Theme 1.0
 */

?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:group {"metadata":{"patternName":"runpartner-theme/rounded","name":"Rounded Header","description":"Rounded navigation bar with site title and navigation.","categories":["header"]},"align":"wide","className":"has-accent-7-background-color has-background","style":{"elements":{"link":{"color":{"text":"var:preset|color|accent-5"}}},"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"backgroundColor":"accent-7","border":{"radius":{"topLeft":"20px","topRight":"20px","bottomLeft":"20px","bottomRight":"20px"}}},"textColor":"accent-5","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<div class="wp-block-group alignwide has-accent-7-background-color has-background has-accent-5-color has-text-color has-link-color" style="border-top-left-radius:20px;border-top-right-radius:20px;border-bottom-left-radius:20px;border-bottom-right-radius:20px;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--30)"><!-- wp:group {"layout":{"type":"flex"}} -->
<div class="wp-block-group"><!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"textColor":"accent-5","fontSize":"medium"} /--></div>
<!-- /wp:group -->

<!-- wp:navigation {"textColor":"accent-5","overlayBackgroundColor":"accent-7","overlayTextColor":"accent-5","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"right"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
