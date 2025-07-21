<?php
get_header();

$slug = get_query_var('md_member_slug');

// Convert slug to first and last name
$name_parts = explode('_', $slug);
$first_name = ucfirst($name_parts[0] ?? '');
$last_name = ucfirst($name_parts[1] ?? '');

$args = [
    'post_type'  => 'member',
    'meta_query' => [
        ['key' => 'md_first_name', 'value' => $first_name],
        ['key' => 'md_last_name', 'value' => $last_name],
        ['key' => 'md_status', 'value' => 'active'],
    ]
];

$query = new WP_Query($args);

if ($query->have_posts()) {
    $query->the_post();
    $member_id = get_the_ID();
    $email     = get_post_meta($member_id, 'md_email', true);
    $address   = get_post_meta($member_id, 'md_address', true);
    $color     = get_post_meta($member_id, 'md_color', true);
    $teams     = get_post_meta($member_id, 'md_teams', true);
    $profile   = wp_get_attachment_url(get_post_meta($member_id, 'md_profile_image_id', true));
    $cover     = wp_get_attachment_url(get_post_meta($member_id, 'md_cover_image_id', true));
    ?>

    <div style="max-width: 800px; margin: auto;">
        <?php if ($cover): ?>
            <img src="<?php echo esc_url($cover); ?>" style="width: 100%; max-height: 300px; object-fit: cover;">
        <?php endif; ?>

        <div style="text-align: center; margin-top: -50px;">
            <?php if ($profile): ?>
                <img src="<?php echo esc_url($profile); ?>" style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid white;">
            <?php endif; ?>
            <h2><?php echo esc_html($first_name . ' ' . $last_name); ?></h2>
            <p><strong>Address:</strong> <?php echo esc_html($address); ?></p>
            <p><strong>Favorite Color:</strong> <span style="background:<?php echo esc_attr($color); ?>;padding:5px 10px;color:#fff;"><?php echo esc_html($color); ?></span></p>
            <p><strong>Teams:</strong>
                <?php
                if (!empty($teams)) {
                    $team_names = array_map(function ($id) {
                        return get_the_title($id);
                    }, $teams);
                    echo esc_html(implode(', ', $team_names));
                } else {
                    echo 'None';
                }
                ?>
            </p>
        </div>

        <hr>

        <h3>Contact <?php echo esc_html($first_name); ?></h3>
        <form method="post">
            <p><input type="text" name="sender_name" placeholder="Your Full Name" required style="width:100%;padding:8px;"></p>
            <p><input type="email" name="sender_email" placeholder="Your Email" required style="width:100%;padding:8px;"></p>
            <p><textarea name="message" placeholder="Your Message" required style="width:100%;padding:8px;"></textarea></p>
            <input type="hidden" name="recipient_email" value="<?php echo esc_attr($email); ?>">
            <input type="hidden" name="member_id" value="<?php echo esc_attr($member_id); ?>">
            <p><input type="submit" name="send_message" value="Send Message" style="background:#0073aa;color:#fff;padding:10px 20px;border:none;"></p>
        </form>
    </div>

    <?php
    wp_reset_postdata();
} else {
    echo '<p style="text-align:center;">Member not found or inactive.</p>';
}

get_footer();
?>
