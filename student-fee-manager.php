<?php

/**
 * Plugin Name: Student Fee Manager
 * Description: Manage Students, Guardians, Monthly Fees & Payments
 * Version: 1.0.0
 * Author: Sarder
 */

if (! defined('ABSPATH')) exit;

define('SFM_PATH', plugin_dir_path(__FILE__));
define('SFM_URL', plugin_dir_url(__FILE__));



include SFM_PATH . 'includes/cpt.php';
include SFM_PATH . 'includes/meta-fields.php';
include SFM_PATH . 'includes/admin-page.php';

// Load CSS/JS for admin
// function sfm_enqueue_assets($hook) {
//     if (strpos($hook, 'sfm-students') !== false) {
//         wp_enqueue_style('sfm-style', SFM_URL . 'assets/style.css');
//         wp_enqueue_script('sfm-script', SFM_URL . 'assets/script.js', ['jquery'], null, true);
//     }
// }
// add_action('admin_enqueue_scripts', 'sfm_enqueue_assets');


function sfm_enqueue_assets($hook) {
    global $post_type;

    if (
        // Student CPT (list, add/edit)
        ($post_type === 'student' && in_array($hook, ['edit.php', 'post.php', 'post-new.php']))
        // OR our custom Manage Fees page
        || $hook === 'student_page_sfm-manage-fees'
    ) {
        wp_enqueue_style('sfm-style', SFM_URL . 'assets/style.css', [], '1.0');
        wp_enqueue_script('sfm-script', SFM_URL . 'assets/script.js', ['jquery'], '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'sfm_enqueue_assets');
