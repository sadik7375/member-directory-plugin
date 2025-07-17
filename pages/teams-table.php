<div class="wrap">
  <h1 class="wp-heading-inline">All Teams</h1>
  <a href="?page=md_add_team" class="page-title-action">Add Team</a>

  <table class="widefat fixed striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Short Description</th>
        <th>Actions</th> <!-- ✅ NEW -->
      </tr>
    </thead>
    <tbody>
      <?php
      $teams = get_posts(['post_type' => 'team', 'numberposts' => -1]);
      if (!empty($teams)) {
        foreach ($teams as $team) {
          $desc = get_post_meta($team->ID, 'md_team_short_description', true);

          // Generate delete link with nonce
          $delete_url = wp_nonce_url(
            admin_url('admin-post.php?action=md_delete_team&id=' . $team->ID),
            'md_delete_team_' . $team->ID
          );

          echo '<tr>';
          echo '<td>' . esc_html($team->post_title) . '</td>';
          echo '<td>' . esc_html(wp_trim_words($desc, 20)) . '</td>';
          echo '<td>
                  <a href="?page=md_add_team&id=' . $team->ID . '" class="button">Edit</a>
                  <a href="' . $delete_url . '" class="button delete-button" onclick="return confirm(\'Are you sure you want to delete this team?\')">Delete</a>
                </td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="3">No teams found.</td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>
