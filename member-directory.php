<?php
/**
 * Plugin Name: Member Directory
 * Description: A custom plugin for managing members and their associated teams.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------
// 1. Admin Menu Setup
// ---------------------------------------------
add_action('admin_menu', 'md_register_admin_menu');

function md_register_admin_menu() {
    add_menu_page(
        'Member Directory',
        'Member Directory',
        'manage_options',
        'md_members',
        'md_render_members_table',
        'dashicons-id-alt',
        6
    );

    add_submenu_page(
        'md_members',
        'All Members',
        'Members',
        'manage_options',
        'md_members',
        'md_render_members_table'
    );

    add_submenu_page(
        'md_members',
        'Add Member',
        'Add Member',
        'manage_options',
        'md_add_member',
        'md_render_add_member_form'
    );

    add_submenu_page(
        'md_members',
        'Teams',
        'Teams',
        'manage_options',
        'md_teams',
        'md_render_teams_table'
    );

    add_submenu_page(
        'md_members',
        'Add Team',
        'Add Team',
        'manage_options',
        'md_add_team',
        'md_render_add_team_form'
    );
}

// ---------------------------------------------
// 2. Admin Page Render Functions
// ---------------------------------------------
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
// 3. Add Member Form Handler
// ---------------------------------------------
add_action('admin_post_md_save_member', 'md_handle_add_member_form');

function md_handle_add_member_form() {
  if (!current_user_can('manage_options') || !check_admin_referer('md_add_member_form', 'md_member_nonce')) {
    wp_die('Unauthorized action.');
  }

  $member_id = isset($_POST['member_id']) ? absint($_POST['member_id']) : 0;
  $email = sanitize_email($_POST['email']);

  // ✅ Check for unique email across members
  if (!md_is_email_unique($email, $member_id)) {
    wp_die('A member with this email already exists. Please use a different email.');
  }

  $data = [
    'post_type'   => 'member',
    'post_status' => 'publish',
    'post_title'  => sanitize_text_field($_POST['first_name']) . ' ' . sanitize_text_field($_POST['last_name']),
  ];

  if ($member_id > 0) {
    $data['ID'] = $member_id;
    wp_update_post($data);
  } else {
    $member_id = wp_insert_post($data);
  }

  if ($member_id) {
    update_post_meta($member_id, 'md_first_name', sanitize_text_field($_POST['first_name']));
    update_post_meta($member_id, 'md_last_name', sanitize_text_field($_POST['last_name']));
    update_post_meta($member_id, 'md_email', $email);
    update_post_meta($member_id, 'md_address', sanitize_textarea_field($_POST['address']));
    update_post_meta($member_id, 'md_color', sanitize_hex_color($_POST['color']));
    update_post_meta($member_id, 'md_status', sanitize_text_field($_POST['status']));
    update_post_meta($member_id, 'md_teams', array_map('intval', $_POST['teams'] ?? []));
    update_post_meta($post_id, 'md_profile_image_id', intval($_POST['profile_image_id']));
update_post_meta($post_id, 'md_cover_image_id', intval($_POST['cover_image_id']));

  }

  wp_redirect(admin_url('admin.php?page=md_members'));
  exit;
}



add_action('admin_post_md_delete_member', 'md_delete_member');

function md_delete_member() {
  if (!current_user_can('manage_options')) wp_die('Unauthorized');

  $id = absint($_GET['id'] ?? 0);
  check_admin_referer('md_delete_member_' . $id);

  wp_delete_post($id, true);
  wp_redirect(admin_url('admin.php?page=md_members'));
  exit;
}


// ---------------------------------------------
// 4. Add Team Form Handler
// ---------------------------------------------
add_action('admin_post_md_save_team', 'md_handle_add_team_form');

function md_handle_add_team_form() {
  if (!current_user_can('manage_options') || !check_admin_referer('md_add_team_form', 'md_team_nonce')) {
    wp_die('Unauthorized action.');
  }

  $team_id = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
  $tname = sanitize_text_field($_POST['team_name']);
  $desc = sanitize_textarea_field($_POST['short_description']);

  $data = [
    'post_type' => 'team',
    'post_status' => 'publish',
    'post_title' => $tname,
  ];

  if ($team_id > 0) {
    $data['ID'] = $team_id;
    wp_update_post($data);
  } else {
    $team_id = wp_insert_post($data);
  }

  if ($team_id && !is_wp_error($team_id)) {
    update_post_meta($team_id, 'md_team_short_description', $desc);
  }

  wp_redirect(admin_url('admin.php?page=md_teams'));
  exit;
}

add_action('admin_post_md_delete_team', 'md_delete_team');

function md_delete_team() {
  if (!current_user_can('manage_options')) wp_die('Unauthorized');
  $id = absint($_GET['id'] ?? 0);
  check_admin_referer('md_delete_team_' . $id);

  wp_delete_post($id, true);
  wp_redirect(admin_url('admin.php?page=md_teams'));
  exit;
}




add_action('admin_enqueue_scripts', 'md_enqueue_admin_scripts');
function md_enqueue_admin_scripts($hook) {
    // Only enqueue on our custom plugin pages
    if (isset($_GET['page']) && in_array($_GET['page'], ['md_add_member', 'md_add_team', 'md_edit_member', 'md_edit_team'])) {
        wp_enqueue_media(); // This enables media uploader
    }
}


// ---------------------------------------------
// 5. Load Meta Boxes (Optional, if using them)
// ---------------------------------------------
require_once plugin_dir_path(__FILE__) . 'admin/meta-boxes.php';

require_once plugin_dir_path(__FILE__) . 'admin/shortcodes.php';

require_once plugin_dir_path(__FILE__) . 'admin/utils.php';
