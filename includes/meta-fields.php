<?php
if (! defined('ABSPATH')) exit;

// -------------------- Meta Boxes --------------------
function sfm_add_student_meta_boxes() {
    add_meta_box('sfm_student_info', 'Student Information', 'sfm_student_info_callback', 'student', 'normal', 'high');
    add_meta_box('sfm_student_payment', 'Payment Tracking (Jul - Jun)', 'sfm_student_payment_callback', 'student', 'normal', 'high');
}
add_action('add_meta_boxes', 'sfm_add_student_meta_boxes');

// ---------- Student Info Callback ----------
function sfm_student_info_callback($post) {
    wp_nonce_field('sfm_student_meta_nonce_action', 'sfm_student_meta_nonce');

    $guardian = get_post_meta($post->ID, '_guardian', true);
    $contact  = get_post_meta($post->ID, '_contact', true);
    $subject  = get_post_meta($post->ID, '_subject', true);
    $monthly  = get_post_meta($post->ID, '_monthly_fee', true);
?>
    <p><label>Guardian Name:</label><br>
        <input type="text" class="sfm-input-text" name="guardian" value="<?php echo esc_attr($guardian); ?>" style="width:100%;">
    </p>
    <p><label>Contact:</label><br>
        <input type="text" class="sfm-input-text" name="contact" value="<?php echo esc_attr($contact); ?>" style="width:100%;">
    </p>
    <p><label>Subject:</label><br>
        <input type="text" class="sfm-input-text" name="subject" value="<?php echo esc_attr($subject); ?>" style="width:100%;">
    </p>
    <p><label>Monthly Fee:</label><br>
        <input type="number" class="sfm-input-number" name="monthly_fee" value="<?php echo esc_attr($monthly); ?>" style="width:100%;" min="0" step="0.01">
    </p>
<?php
}

// ---------- Payment Callback ----------
function sfm_student_payment_callback($post) {
    $months_full  = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
    $months_short = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

    $payments = get_post_meta($post->ID, '_payments', true);
    if (!is_array($payments)) $payments = [];

    echo '<table class="widefat fee-table">';
    echo '<thead><tr><th>Month</th><th>Paid Amount</th></tr></thead><tbody>';

    foreach ($months_full as $i => $full) {
        $short = $months_short[$i];
        $val = isset($payments[$short]) ? floatval($payments[$short]) : 0;
        echo "<tr>
            <td>{$short}</td>
            <td><input type='number' class='sfm-input-number' name='payments[{$full}]' value='{$val}' min='0' step='0.01'></td>
        </tr>";
    }

    echo '</tbody></table>';
}

// ---------- Save Meta ----------
function sfm_save_student_meta($post_id) {
    if (
        !isset($_POST['sfm_student_meta_nonce']) ||
        !wp_verify_nonce($_POST['sfm_student_meta_nonce'], 'sfm_student_meta_nonce_action')
    ) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save basic fields
    if (isset($_POST['guardian'])) update_post_meta($post_id, '_guardian', sanitize_text_field($_POST['guardian']));
    if (isset($_POST['contact']))  update_post_meta($post_id, '_contact', sanitize_text_field($_POST['contact']));
    if (isset($_POST['subject']))  update_post_meta($post_id, '_subject', sanitize_text_field($_POST['subject']));
    if (isset($_POST['monthly_fee'])) update_post_meta($post_id, '_monthly_fee', floatval($_POST['monthly_fee']));

    // Save payments
    if (isset($_POST['payments']) && is_array($_POST['payments'])) {
        $monthly = get_post_meta($post_id, '_monthly_fee', true);
        $months_full  = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
        $months_short = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

        $clean_payments = [];
        foreach ($months_full as $i => $full) {
            $short = $months_short[$i];
            $val = isset($_POST['payments'][$full]) ? floatval($_POST['payments'][$full]) : 0;
            if ($monthly && $val > $monthly) $val = $monthly;
            $clean_payments[$short] = $val;
        }
        update_post_meta($post_id, '_payments', $clean_payments);
    }
}
add_action('save_post_student', 'sfm_save_student_meta');
