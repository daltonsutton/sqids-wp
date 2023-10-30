<?php
/**
 * Plugin Name:   Sqids (Hashids) for WordPress
 * Description:   Generate Short Unique IDs from Post IDs.
 * Version:       0.0.1
 * Requires PHP:  8.1
 * Author:        Dalton Sutton
 * Author URI:    https://dalton.sutton.io/
 * Plugin URI:    https://dalton.sutton.io/plugins/
 * Text Domain:   sqids-wp
  
 * 
 * Author Note: This file is updated remotely. Please DO NOT make any changes to this file, as it will be overwritten.
 */

require_once 'vendor/autoload.php';

use Sqids\Sqids;

// Register the "sqid" tag in permalink structure
function sqid_rewrite_tag() {
    add_rewrite_tag('%sqid%', '([^/]+)');
}
add_action('init', 'sqid_rewrite_tag', 10, 0);

// Replace Post ID with Sqid in permalink
function sqid_permalink($permalink, $post, $leavename) {
    if (strpos($permalink, '%sqid%') === false):
        return $permalink;
    endif;

    $sqids = new Sqids(minLength: 10);
    
    // // Get the Sqid based on the Post ID using Sqids library or logic
    $sqid = $sqids->encode([$post->ID]);
    
    return str_replace('%sqid%', $sqid, $permalink);
}
add_filter('post_type_link', 'sqid_permalink', 10, 3);
add_filter('post_link', 'sqid_permalink', 10, 3);

// Add Sqid to available permalink structure tags
function sqid_permalink_structure_tags($tags) {
    $tags['sqid'] = '%s Generate Short Unique IDs from Post IDs.';
    return $tags;
}
add_filter('available_permalink_structure_tags', 'sqid_permalink_structure_tags');

// Flush rewrite rules on plugin activation
function sqid_flush_rewrite_rules() {
    sqid_rewrite_tag();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'sqid_flush_rewrite_rules' );

// Flush rewrite rules on plugin deactivation
function sqid_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'sqid_deactivation' );

