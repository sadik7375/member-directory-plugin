<?php
/**
 * Plugin Name: Member Directory
 * Description: A custom plugin for managing members and their associated teams.
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register custom post types on init
add_action( 'init', 'md_register_custom_post_types' );

function md_register_custom_post_types() {
    // Register Member CPT
    register_post_type( 'member', [
        'labels' => [
            'name' => 'Members',
            'singular_name' => 'Member',
            'add_new_item' => 'Add New Member',
            'edit_item' => 'Edit Member',
            'new_item' => 'New Member',
            'view_item' => 'View Member',
            'search_items' => 'Search Members',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'members'],
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'thumbnail', 'editor'],
        'show_in_rest' => true,
    ] );

    // Register Team CPT
    register_post_type( 'team', [
        'labels' => [
            'name' => 'Teams',
            'singular_name' => 'Team',
            'add_new_item' => 'Add New Team',
            'edit_item' => 'Edit Team',
            'new_item' => 'New Team',
            'view_item' => 'View Team',
            'search_items' => 'Search Teams',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'teams'],
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ] );
}





// Include Meta Boxes
require_once plugin_dir_path(__FILE__) . 'admin/meta-boxes.php';