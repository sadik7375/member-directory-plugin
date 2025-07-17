<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if the given email is unique across all members
 */
function md_is_email_unique($email, $current_id = 0) {
    $query = new WP_Query([
        'post_type'      => 'member',
        'post_status'    => 'any',
        'meta_key'       => 'md_email',
        'meta_value'     => $email,
        'post__not_in'   => [$current_id],
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ]);

    return $query->found_posts === 0;
}
