<?php
/*
Plugin Name: Disable Feeds
Plugin URI: https://www.littlebizzy.com/plugins/disable-feeds
Description: Disables RSS and 301s to parent
Version: 1.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: littlebizzy/disable-feeds
Primary Branch: master
Tested up to: 6.6
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable WordPress.org updates for this plugin
add_filter( 'gu_override_dot_org', function( $overrides ) {
    $overrides[] = 'disable-feeds/disable-feeds.php';
    return $overrides;
});

// Disable all RSS Feeds and redirect to the parent URL
function disable_all_feeds() {
    if ( is_feed() ) {
        // Get current URL and redirect to parent URL
        $current_url = home_url( add_query_arg( null, null ) );
        $parent_url = preg_replace( '#(/feed.*)#', '', $current_url );
        
        // Safe redirect to the parent URL
        wp_safe_redirect( esc_url_raw( $parent_url ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'disable_all_feeds', 20 );

// Disable built-in feed types
function disable_default_feeds() {
    remove_action( 'do_feed_rdf', 'do_feed_rdf', 20 );
    remove_action( 'do_feed_rss', 'do_feed_rss', 20 );
    remove_action( 'do_feed_rss2', 'do_feed_rss2', 20 );
    remove_action( 'do_feed_atom', 'do_feed_atom', 20 );
    remove_action( 'do_feed_rss2_comments', 'do_feed_rss2_comments', 20 );
    remove_action( 'do_feed_atom_comments', 'do_feed_atom_comments', 20 );
}
add_action( 'init', 'disable_default_feeds', 20 );

// Disable feeds for custom post types
function disable_custom_post_type_feeds() {
    // Get all public custom post types and disable their feeds
    foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
        remove_action( "do_feed_{$post_type}", 'do_feed_rss2', 20 );
    }
}
add_action( 'init', 'disable_custom_post_type_feeds', 20 );

// Remove feed links from <head>
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

// Disable feed discovery HTTP headers
remove_action( 'template_redirect', 'feed_links' );
remove_action( 'template_redirect', 'feed_links_extra' );

// Disable comment feeds
add_filter( 'feed_links_show_comments_feed', '__return_false' );

// Ref: ChatGPT
