<?php
$is_edit = isset($_GET['id']);
$member_id = $is_edit ? absint($_GET['id']) : 0;

// Default values
$values = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'address' => '',
  'color' => '#000000',
  'status' => 'active',
  'teams' => [],
];

$profile_image_id = '';
$cover_image_id = '';

// If editing, load values from post meta
if ($is_edit && get_post_type($member_id) === 'member') {
  foreach ($values as $key => $default) {
    if ($key === 'teams') {
      $values[$key] = get_post_meta($member_id, 'md_teams', true) ?: [];
    } else {
      $values[$key] = get_post_meta($member_id, 'md_' . $key, true) ?: $default;
    }
  }
  $profile_image_id = get_post_meta($member_id, 'md_profile_image_id', true);
  $cover_image_id   = get_post_meta($member_id, 'md_cover_image_id', true);
}
?>

<div class="wrap">
  <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Member' : 'Add New Member'; ?></h1>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="md_save_member">
    <?php if ($is_edit): ?>
      <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
    <?php endif; ?>
    <?php wp_nonce_field('md_add_member_form', 'md_member_nonce'); ?>

    <table class="form-table">
      <tr>
        <th><label>First Name</label></th>
        <td><input type="text" name="first_name" class="regular-text" value="<?php echo esc_attr($values['first_name']); ?>" required></td>
      </tr>
      <tr>
        <th><label>Last Name</label></th>
        <td><input type="text" name="last_name" class="regular-text" value="<?php echo esc_attr($values['last_name']); ?>" required></td>
      </tr>
      <tr>
        <th><label>Email</label></th>
        <td><input type="email" name="email" class="regular-text" value="<?php echo esc_attr($values['email']); ?>" required></td>
      </tr>
      <tr>
        <th><label>Address</label></th>
        <td><textarea name="address" class="large-text"><?php echo esc_textarea($values['address']); ?></textarea></td>
      </tr>
      <tr>
        <th><label>Favorite Color</label></th>
        <td><input type="color" name="color" value="<?php echo esc_attr($values['color']); ?>"></td>
      </tr>
      <tr>
        <th><label>Status</label></th>
        <td>
          <select name="status">
            <option value="active" <?php selected($values['status'], 'active'); ?>>Active</option>
            <option value="draft" <?php selected($values['status'], 'draft'); ?>>Draft</option>
          </select>
        </td>
      </tr>
      <tr>
        <th><label>Profile Image</label></th>
        <td>
          <?php $profile_url = $profile_image_id ? wp_get_attachment_url($profile_image_id) : ''; ?>
          <input type="hidden" name="profile_image_id" id="profile_image_id" value="<?php echo esc_attr($profile_image_id); ?>">
          <img id="profile_image_preview" src="<?php echo esc_url($profile_url); ?>" style="max-width: 150px; display:block; margin-bottom:10px;">
          <button type="button" class="button" onclick="uploadMedia('profile_image_id', 'profile_image_preview')">Select Profile Image</button>
        </td>
      </tr>
      <tr>
        <th><label>Cover Image</label></th>
        <td>
          <?php $cover_url = $cover_image_id ? wp_get_attachment_url($cover_image_id) : ''; ?>
          <input type="hidden" name="cover_image_id" id="cover_image_id" value="<?php echo esc_attr($cover_image_id); ?>">
          <img id="cover_image_preview" src="<?php echo esc_url($cover_url); ?>" style="max-width: 100%; display:block; margin-bottom:10px;">
          <button type="button" class="button" onclick="uploadMedia('cover_image_id', 'cover_image_preview')">Select Cover Image</button>
        </td>
      </tr>
    </table>

    <h2>Assign Teams</h2>
    <ul>
      <?php
      $teams = get_posts(['post_type' => 'team', 'numberposts' => -1]);
      if (!empty($teams)) {
        foreach ($teams as $team) {
          $checked = in_array($team->ID, $values['teams']) ? 'checked' : '';
          echo '<li><label><input type="checkbox" name="teams[]" value="' . esc_attr($team->ID) . '" ' . $checked . '> ' . esc_html($team->post_title) . '</label></li>';
        }
      } else {
        echo '<li><em>No teams available.</em></li>';
      }
      ?>
    </ul>

    <p><input type="submit" class="button button-primary" value="<?php echo $is_edit ? 'Update Member' : 'Add Member'; ?>"></p>
  </form>
</div>

<!-- Media Uploader -->
<script>
function uploadMedia(inputId, previewId) {
  const customUploader = wp.media({
    title: 'Select Image',
    button: { text: 'Use this image' },
    multiple: false
  }).on('select', function () {
    const attachment = customUploader.state().get('selection').first().toJSON();
    document.getElementById(inputId).value = attachment.id;
    document.getElementById(previewId).src = attachment.url;
  }).open();
}
</script>
