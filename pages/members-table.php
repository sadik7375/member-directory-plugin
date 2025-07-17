<div class="wrap">
  <h1 class="wp-heading-inline">All Members</h1>
  <a href="?page=md_add_member" class="page-title-action">Add Member</a>
<table class="widefat fixed striped">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Status</th>
      <th>Teams</th>
      <th>Actions</th> <!-- New -->
    </tr>
  </thead>
  <tbody>
    <?php
    $members = get_posts(['post_type' => 'member', 'numberposts' => -1]);
    foreach ($members as $member) {
      $first = get_post_meta($member->ID, 'md_first_name', true);
      $last = get_post_meta($member->ID, 'md_last_name', true);
      $email = get_post_meta($member->ID, 'md_email', true);
      $status = get_post_meta($member->ID, 'md_status', true);
      $teams = get_post_meta($member->ID, 'md_teams', true);
      $team_names = [];

      if (is_array($teams)) {
        foreach ($teams as $team_id) {
          $team_names[] = get_the_title($team_id);
        }
      }

      echo '<tr>';
      echo '<td>' . esc_html($first . ' ' . $last) . '</td>';
      echo '<td>' . esc_html($email) . '</td>';
      echo '<td>' . esc_html(ucfirst($status)) . '</td>';
      echo '<td>' . esc_html(implode(', ', $team_names)) . '</td>';
      echo '<td>
              <a href="?page=md_add_member&id=' . $member->ID . '" class="button">Edit</a>
              <a href="' . wp_nonce_url(admin_url('admin-post.php?action=md_delete_member&id=' . $member->ID), 'md_delete_member_' . $member->ID) . '" class="button delete-button" onclick="return confirm(\'Are you sure?\')">Delete</a>
            </td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>

</div>
