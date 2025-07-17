<?php
if ( ! defined( 'ABSPATH' ) ) exit;



// Add meta field for member 

add_action( 'add_meta_boxes', 'md_add_member_meta_boxes' );
function md_add_member_meta_boxes() {
    add_meta_box('md_member_details', 'Member Details', 'md_render_member_meta_box', 'member', 'normal', 'high');
}

function md_render_member_meta_box($post) {
    $fields = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'profile_image' => '',
        'cover_image' => '',
        'address' => '',
        'color' => '',
        'status' => 'active',
    ];

    foreach ($fields as $field => $default) {
        $fields[$field] = get_post_meta($post->ID, 'md_' . $field, true) ?: $default;
    }

    wp_nonce_field('md_save_member_fields', 'md_member_nonce');
    ?>

    <p>
        <label><strong>First Name</strong></label><br>
        <input type="text" name="md_first_name" value="<?= esc_attr($fields['first_name']) ?>" style="width: 100%;" required>
    </p>

    <p>
        <label><strong>Last Name</strong></label><br>
        <input type="text" name="md_last_name" value="<?= esc_attr($fields['last_name']) ?>" style="width: 100%;" required>
    </p>

    <p>
        <label><strong>Email</strong> (must be unique)</label><br>
        <input type="email" name="md_email" value="<?= esc_attr($fields['email']) ?>" style="width: 100%;" required>
    </p>

    <p>
        <label><strong>Profile Image</strong></label><br>
        <input type="text" name="md_profile_image" id="md_profile_image" value="<?= esc_attr($fields['profile_image']) ?>" style="width: 80%;" />
        <button class="button" type="button" onclick="md_upload_image('md_profile_image')">Upload</button>
    </p>

    <p>
        <label><strong>Cover Image</strong></label><br>
        <input type="text" name="md_cover_image" id="md_cover_image" value="<?= esc_attr($fields['cover_image']) ?>" style="width: 80%;" />
        <button class="button" type="button" onclick="md_upload_image('md_cover_image')">Upload</button>
    </p>

    <p>
        <label><strong>Address</strong></label><br>
        <textarea name="md_address" style="width: 100%;"><?= esc_textarea($fields['address']) ?></textarea>
    </p>

    <p>
        <label><strong>Favorite Color</strong></label><br>
        <input type="color" name="md_color" value="<?= esc_attr($fields['color']) ?>">
    </p>


<p><label><strong>Assign Teams</strong></label></p>
<div style="margin-bottom: 10px;">
    <?php
    $teams = get_posts([
        'post_type' => 'team',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $selected_teams = get_post_meta($post->ID, 'md_teams', true);
    $selected_teams = is_array($selected_teams) ? $selected_teams : [];

    if (!empty($teams)) {
        foreach ($teams as $team) {
            $checked = in_array($team->ID, $selected_teams) ? 'checked' : '';
            echo '<label style="display: block; margin-bottom: 4px;">';
            echo '<input type="checkbox" name="md_teams[]" value="' . esc_attr($team->ID) . '" ' . $checked . '> ';
            echo esc_html($team->post_title);
            echo '</label>';
        }
    } else {
        echo '<p><em>No teams found.</em></p>';
    }
    ?>
</div>



    <p>
        <label><strong>Status</strong></label><br>
        <select name="md_status">
            <option value="active" <?= $fields['status'] == 'active' ? 'selected' : '' ?>>Active</option>
            <option value="draft" <?= $fields['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </p>

    <script>
        function md_upload_image(field_id) {
            const customUploader = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false
            });
            customUploader.on('select', function () {
                const attachment = customUploader.state().get('selection').first().toJSON();
                document.getElementById(field_id).value = attachment.url;
            });
            customUploader.open();
        }
    </script>
    <?php
}

add_action( 'save_post_member', 'md_save_member_meta_fields' );
function md_save_member_meta_fields($post_id) {
    if ( ! isset($_POST['md_member_nonce']) || ! wp_verify_nonce($_POST['md_member_nonce'], 'md_save_member_fields') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;


    $email = sanitize_email($_POST['md_email']);

if (!md_is_email_unique($email, $post_id)) {
    wp_die('Error: A member with this email already exists. Please use a unique email address.');
}


    $email = sanitize_email($_POST['md_email']);
    $existing = new WP_Query([
        'post_type' => 'member',
        'meta_key' => 'md_email',
        'meta_value' => $email,
        'post__not_in' => [$post_id],
        'fields' => 'ids',
    ]);

    if ( $existing->have_posts() ) {
        wp_die('A member with this email already exists. Please use a unique email.');
    }

    $fields = [
        'first_name' => sanitize_text_field($_POST['md_first_name']),
        'last_name' => sanitize_text_field($_POST['md_last_name']),
        'email' => $email,
        'profile_image' => esc_url_raw($_POST['md_profile_image']),
        'cover_image' => esc_url_raw($_POST['md_cover_image']),
        'address' => sanitize_textarea_field($_POST['md_address']),
        'color' => sanitize_hex_color($_POST['md_color']),
        'status' => $_POST['md_status'] === 'draft' ? 'draft' : 'active',
    ];

    foreach ($fields as $key => $val) {
        update_post_meta($post_id, 'md_' . $key, $val);
    }
}







//Add meta box for team memmber


// Add Team Meta Box
add_action( 'add_meta_boxes', 'md_add_team_meta_box' );
function md_add_team_meta_box() {
    add_meta_box('md_team_details', 'Team Details', 'md_render_team_meta_box', 'team', 'normal', 'high');
}


add_filter('enter_title_here', 'md_custom_team_title_placeholder');
function md_custom_team_title_placeholder($title) {
    $screen = get_current_screen();
    if ( 'team' === $screen->post_type ) {
        $title = 'Enter Team Name';
    }
    return $title;
}



function md_render_team_meta_box($post) {
    $short_description = get_post_meta($post->ID, 'md_team_short_description', true);
    wp_nonce_field('md_save_team_fields', 'md_team_nonce');
    ?>
    <p>
        <label><strong>Short Description</strong></label><br>
        <textarea name="md_team_short_description" style="width: 100%;"><?= esc_textarea($short_description) ?></textarea>
    </p>
    <?php
}

add_action( 'save_post_team', 'md_save_team_meta_fields' );
function md_save_team_meta_fields($post_id) {
    if ( ! isset($_POST['md_team_nonce']) || ! wp_verify_nonce($_POST['md_team_nonce'], 'md_save_team_fields') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    update_post_meta($post_id, 'md_team_short_description', sanitize_textarea_field($_POST['md_team_short_description']));
}
