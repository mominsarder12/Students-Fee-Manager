<?php

if (! defined('ABSPATH')) exit; // Prevent direct access

function sfm_register_student_cpt() {
    $labels = [
        'name'                  => __('Students', 'sfm'),
        'singular_name'         => __('Student', 'sfm'),
        'menu_name'             => __('Students', 'sfm'),
        'name_admin_bar'        => __('Student', 'sfm'),
        'add_new'               => __('Add New', 'sfm'),
        'add_new_item'          => __('Add New Student', 'sfm'),
        'new_item'              => __('New Student', 'sfm'),
        'edit_item'             => __('Edit Student', 'sfm'),
        'view_item'             => __('View Student', 'sfm'),
        'all_items'             => __('All Students', 'sfm'),
        'search_items'          => __('Search Students', 'sfm'),
        'not_found'             => __('No students found.', 'sfm'),
        'not_found_in_trash'    => __('No students found in Trash.', 'sfm'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,                 // not public
        'exclude_from_search' => true,                  // not searchable
        'publicly_queryable' => false,                 // no frontend
        'show_ui'            => true,                  // show in admin
        'show_in_menu'       => true,                  // top-level menu
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'capability_type'    => 'post',                // can customize later
        'map_meta_cap'       => true,
        'supports'           => ['title'],
        'has_archive'        => false,
        'rewrite'            => false,
    ];

    register_post_type('student', $args);
}
add_action('init', 'sfm_register_student_cpt');
