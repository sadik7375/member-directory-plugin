<?php
$is_edit = isset($_GET['id']);
$team_id = $is_edit ? absint($_GET['id']) : 0;

$team_name = '';
$short_description = '';

if ($is_edit && get_post_type($team_id) === 'team') {
  $team_post = get_post($team_id);
  if ($team_post) {
    $team_name = $team_post->post_title;
    $short_description = get_post_meta($team_id, 'md_team_short_description', true);
  }
}
?>

<div class="wrap">
  <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Team' : 'Add New Team'; ?></h1>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="md_save_team">
    <?php if ($is_edit): ?>
      <input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
    <?php endif; ?>
    <?php wp_nonce_field('md_add_team_form', 'md_team_nonce'); ?>

    <table class="form-table">
      <tr>
        <th><label>Team Name</label></th>
        <td><input type="text" name="team_name" class="regular-text" value="<?php echo esc_attr($team_name); ?>" required></td>
      </tr>
      <tr>
        <th><label>Short Description</label></th>
        <td><textarea name="short_description" class="large-text"><?php echo esc_textarea($short_description); ?></textarea></td>
      </tr>
    </table>

    <p><input type="submit" class="button button-primary" value="<?php echo $is_edit ? 'Update Team' : 'Add Team'; ?>"></p>
  </form>
</div>
