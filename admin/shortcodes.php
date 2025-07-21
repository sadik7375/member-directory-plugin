<?php 


add_shortcode('md_members_list', 'md_render_members_list_shortcode');

function md_render_members_list_shortcode($atts) {
    $args = [
        'post_type'     => 'member',
        'posts_per_page'=> -1,
        'meta_key'      => 'md_status',
        'meta_value'    => 'Active',
        'orderby'       => 'title',
        'order'         => 'ASC',
    ];

    $members = get_posts($args);

    if (empty($members)) {
        return '<p>No active members found.</p>';
    }

    ob_start();

    echo '<table style="width:100%; border-collapse:collapse; border:1px solid #ccc;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th style="border:1px solid #ccc; padding:8px;">Image</th>';
    echo '<th style="border:1px solid #ccc; padding:8px;">Full Name</th>';
    echo '<th style="border:1px solid #ccc; padding:8px;">Email</th>';
    echo '<th style="border:1px solid #ccc; padding:8px;">Teams</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($members as $member) {
        $id = $member->ID;
        $first = get_post_meta($id, 'md_first_name', true);
        $last = get_post_meta($id, 'md_last_name', true);
        $email = get_post_meta($id, 'md_email', true);
        $teams_ids = get_post_meta($id, 'md_teams', true) ?: [];

        $team_names = [];
        foreach ($teams_ids as $team_id) {
            $team_post = get_post($team_id);
            if ($team_post) $team_names[] = esc_html($team_post->post_title);
        }

        $profile_image_id = get_post_meta($id, 'md_profile_image_id', true);
        $img_url = $profile_image_id ? wp_get_attachment_url($profile_image_id) : 'https://via.placeholder.com/80';

        echo '<tr>';
        echo '<td style="border:1px solid #ccc; padding:8px;"><img src="' . esc_url($img_url) . '" alt="Profile" width="60" height="60" style="border-radius:50%; object-fit:cover;"></td>';
      $slug = strtolower(str_replace(' ', '', $first)) . '_' . strtolower(str_replace(' ', '', $last));
$link = site_url('/member/' . $slug);
echo '<td style="border:1px solid #ccc; padding:8px;"><a href="' . esc_url($link) . '">' . esc_html($first . ' ' . $last) . '</a></td>';

        echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($email) . '</td>';
        echo '<td style="border:1px solid #ccc; padding:8px;">' . implode(', ', $team_names) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    return ob_get_clean();
}

