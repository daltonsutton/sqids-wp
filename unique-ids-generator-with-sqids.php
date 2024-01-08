<?php
/**
 * Plugin Name:   Unique IDs Generator using Sqids
 * Description:   Generate Short Unique IDs from Post IDs.
 * Version:       1.0.0
 * Requires PHP:  8.1
 * Author:        Dalton Sutton
 * Author URI:    https://dalton.sutton.io/
 * Plugin URI:    https://github.com/daltonsutton/unique-ids-generator-with-sqids
 * License:       GPLv2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:   unique-ids-generator-with-sqids

 * 
 * Author Note: This file is updated remotely. Please DO NOT make any changes to this file, as it will be overwritten.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'vendor/autoload.php';

use Sqids\Sqids;

// Register the "sqid" tag in permalink structure
function sqid_rewrite_tag() {
    add_rewrite_tag('%sqid%', '([^/]+)', 'sqid=');
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

// query var
function sqid_query_vars($query_vars) {
    $query_vars[] = 'sqid';
    return $query_vars;
}
add_filter('query_vars', 'sqid_query_vars');

// decode sqid to post id
function sqid_request($query_vars) {
    if (array_key_exists('sqid', $query_vars)):
        $sqid = $query_vars['sqid'];
        $sqids = new Sqids(minLength: strlen($sqid));
        $decoded_sqid = isset($sqids->decode($sqid)[0]) ? $sqids->decode($sqid)[0] : false;

        if ($decoded_sqid && strlen($sqid) === 10):
            $query_vars['p'] = $decoded_sqid;
        else:
            $query_vars['error'] = '404';
        endif;
    endif;

    return $query_vars;
}
add_filter('request', 'sqid_request');

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