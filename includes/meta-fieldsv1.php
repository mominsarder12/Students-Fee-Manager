<?php
function sfm_add_student_meta_boxes() {
    add_meta_box('sfm_student_info', 'Student Information', 'sfm_student_info_callback', 'student', 'normal', 'high');
    add_meta_box('sfm_student_payment', 'Payment Tracking (July - June)', 'sfm_student_payment_callback', 'student', 'normal', 'high');
}
add_action('add_meta_boxes', 'sfm_add_student_meta_boxes');

// ---------- Student Info ----------
function sfm_student_info_callback($post) {
    // Security nonce field
    wp_nonce_field('sfm_student_meta_nonce_action', 'sfm_student_meta_nonce');

    $guardian = get_post_meta($post->ID, '_guardian', true);
    $contact  = get_post_meta($post->ID, '_contact', true);
    $subject  = get_post_meta($post->ID, '_subject', true);
    $monthly  = get_post_meta($post->ID, '_monthly_fee', true);
?>
    <p><label>Guardian Name:</label><br>
        <input type="text" name="guardian" value="<?php echo esc_attr($guardian); ?>" style="width:100%;">
    </p>
    <p><label>Contact:</label><br>
        <input type="text" name="contact" value="<?php echo esc_attr($contact); ?>" style="width:100%;">
    </p>
    <p><label>Subject:</label><br>
        <input type="text" name="subject" value="<?php echo esc_attr($subject); ?>" style="width:100%;">
    </p>
    <p><label>Monthly Fee:</label><br>
        <input type="number" name="monthly_fee" value="<?php echo esc_attr($monthly); ?>" style="width:100%;">
    </p>
<?php
}

// ---------- Payment Table ----------
function sfm_student_payment_callback($post) {
    $months   = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
    $payments = get_post_meta($post->ID, '_payments', true);
    if (!is_array($payments)) $payments = [];

    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Month</th><th>Paid Amount</th></tr></thead><tbody>';
    foreach ($months as $m) {
        $val = isset($payments[$m]) ? esc_attr($payments[$m]) : '';
        echo "<tr>
            <td>{$m}</td>
            <td><input type='number' name='payments[{$m}]' value='{$val}'></td>
        </tr>";
    }
    echo '</tbody></table>';
}

// ---------- Save Meta ----------
function sfm_save_student_meta($post_id) {
    // ---- Security Checks ----
    if (
        !isset($_POST['sfm_student_meta_nonce']) ||
        !wp_verify_nonce($_POST['sfm_student_meta_nonce'], 'sfm_student_meta_nonce_action')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;

    // ---- Save Fields ----
    if (isset($_POST['guardian'])) {
        update_post_meta($post_id, '_guardian', sanitize_text_field($_POST['guardian']));
    }

    if (isset($_POST['contact'])) {
        update_post_meta($post_id, '_contact', sanitize_text_field($_POST['contact']));
    }

    if (isset($_POST['subject'])) {
        update_post_meta($post_id, '_subject', sanitize_text_field($_POST['subject']));
    }

    if (isset($_POST['monthly_fee'])) {
        update_post_meta($post_id, '_monthly_fee', floatval($_POST['monthly_fee']));
    }

    // ---- Payments ----
    if (isset($_POST['payments']) && is_array($_POST['payments'])) {
        $monthly = get_post_meta($post_id, '_monthly_fee', true);
        $clean_payments = [];
        foreach ($_POST['payments'] as $month => $amount) {
            $val = floatval($amount);
            // Prevent over-payment
            if ($monthly && $val > $monthly) {
                $val = $monthly;
            }
            $clean_payments[sanitize_text_field($month)] = $val;
        }
        update_post_meta($post_id, '_payments', $clean_payments);
    }
}
add_action('save_post_student', 'sfm_save_student_meta');
