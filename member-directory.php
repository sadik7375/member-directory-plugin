<?php
/**
 * Plugin Name: Member Directory
 * Description: A custom plugin for managing members and their associated teams.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ---------------------------------------------
// 1. Register Custom Post Types (Member + Team)
// ---------------------------------------------
add_action('init', function () {
    register_post_type('member', [
        'labels' => [
            'name' => 'Members',
            'singular_name' => 'Member'
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'has_archive' => false,
    ]);

    register_post_type('team', [
        'labels' => [
            'name' => 'Teams',
            'singular_name' => 'Team'
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'has_archive' => false,
    ]);
});

// ---------------------------------------------
// 2. Admin Menus
// ---------------------------------------------
add_action('admin_menu', function () {
    add_menu_page('Member Directory', 'Member Directory', 'manage_options', 'md_members', 'md_render_members_table', 'dashicons-id-alt', 6);
    add_submenu_page('md_members', 'Members', 'Members', 'manage_options', 'md_members', 'md_render_members_table');
    add_submenu_page('md_members', 'Add Member', 'Add Member', 'manage_options', 'md_add_member', 'md_render_add_member_form');
    add_submenu_page('md_members', 'Teams', 'Teams', 'manage_options', 'md_teams', 'md_render_teams_table');
    add_submenu_page('md_members', 'Add Team', 'Add Team', 'manage_options', 'md_add_team', 'md_render_add_team_form');
});

function md_render_members_table() {
    require_once plugin_dir_path(__FILE__) . 'pages/members-table.php';
}
function md_render_add_member_form() {
    require_once plugin_dir_path(__FILE__) . 'pages/add-member-form.php';
}
function md_render_teams_table() {
    require_once plugin_dir_path(__FILE__) . 'pages/teams-table.php';
}
function md_render_add_team_form() {
    require_once plugin_dir_path(__FILE__) . 'pages/add-team-form.php';
}

// ---------------------------------------------
// 3. Enqueue Media Uploader on form pages
// ---------------------------------------------
add_action('admin_enqueue_scripts', function($hook) {
    if (isset($_GET['page']) && strpos($_GET['page'], 'md_') === 0) {
        wp_enqueue_media();
    }
});

// ---------------------------------------------
// 4. Handle Member and Team Form Submission
// ---------------------------------------------
// require_once plugin_dir_path(__FILE__) . 'admin/handlers.php';

// ---------------------------------------------
// 5. Rewrite Rules for Single Member
// ---------------------------------------------
add_action('init', 'md_register_rewrite_rule');
function md_register_rewrite_rule() {
    add_rewrite_rule('^member/([^/]+)/?', 'index.php?md_member_slug=$matches[1]', 'top');
}
add_filter('query_vars', function($vars) {
    $vars[] = 'md_member_slug';
    return $vars;
});
add_filter('template_include', 'md_load_single_member_template');
function md_load_single_member_template($template) {
    $slug = get_query_var('md_member_slug');
    if ($slug) {
        return plugin_dir_path(__FILE__) . 'templates/single-member.php';
    }
    return $template;
}
register_activation_hook(__FILE__, function () {
    md_register_rewrite_rule();
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// ---------------------------------------------
// 6. Include Other Files
// ---------------------------------------------
require_once plugin_dir_path(__FILE__) . 'admin/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'admin/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'admin/utils.php';
