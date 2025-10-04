<?php
if (! defined('ABSPATH')) exit; // Prevent direct access
// Add custom columns
function sfm_add_student_columns($columns) {
    $columns['guardian'] = 'Guardian';
    $columns['contact'] = 'Contact';
    $columns['subject'] = 'Subject';
    $columns['monthly_fee'] = 'Monthly Fee';
    $columns['total_paid'] = 'Total Paid';
    $columns['total_due'] = 'Total Due';
    return $columns;
}
add_filter('manage_student_posts_columns', 'sfm_add_student_columns');

// Fill custom columns
function sfm_render_student_columns($column, $post_id) {
    $monthly = get_post_meta($post_id, '_monthly_fee', true);
    $payments = get_post_meta($post_id, '_payments', true);
    if (!is_array($payments)) $payments = [];
    $total_paid = array_sum($payments);
    $total_due = (12 * floatval($monthly)) - $total_paid;

    switch ($column) {
        case 'guardian':
            echo esc_html(get_post_meta($post_id, '_guardian', true));
            break;
        case 'contact':
            echo esc_html(get_post_meta($post_id, '_contact', true));
            break;
        case 'subject':
            echo esc_html(get_post_meta($post_id, '_subject', true));
            break;
        case 'monthly_fee':
            echo esc_html($monthly);
            break;
        case 'total_paid':
            echo esc_html($total_paid);
            break;
        case 'total_due':
            echo esc_html(max($total_due, 0));
            break;
    }
}
add_action('manage_student_posts_custom_column', 'sfm_render_student_columns', 10, 2);



// view of the all students

function sfm_register_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=student',   // parent = Students
        'Manage Students Fee',          // page title
        'Manage Fees',                  // menu title
        'manage_options',               // capability
        'sfm-manage-fees',              // slug
        'sfm_manage_fees_page'          // callback
    );
}
add_action('admin_menu', 'sfm_register_submenu_page');

// ---------- Admin Table Display ----------
function sfm_manage_fees_page() {
?>
    <div class="wrap">
        <h1 style="margin-bottom:20px;">📊 Manage Students Fee</h1>
        <table id="sfm-table" class="widefat sfm-fee-table" style="overflow-x:auto;">
            <thead>
                <tr>
                    <th style="background:#333;color:#fff;">Student</th>
                    <th style="background:#555;color:#fff;">Guardian</th>
                    <th style="background:#666;color:#fff;">Contact</th>
                    <th style="background:#777;color:#fff;">Subject</th>
                    <th style="background:#888;color:#fff;">Monthly Fee</th>
                    <?php
                    $months_short = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
                    foreach ($months_short as $m) {
                        echo "<th style='background:#17a2b8;color:#fff;'>{$m}</th>";
                    }
                    ?>
                    <th style="background:#009879;color:#fff;">Total Paid</th>
                    <th style="background:#d9534f;color:#fff;">Total Due</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $students = get_posts(['post_type' => 'student', 'numberposts' => -1]);
                foreach ($students as $s) {
                    $guardian = get_post_meta($s->ID, '_guardian', true);
                    $contact  = get_post_meta($s->ID, '_contact', true);
                    $subject  = get_post_meta($s->ID, '_subject', true);
                    $monthly  = get_post_meta($s->ID, '_monthly_fee', true);
                    $payments = get_post_meta($s->ID, '_payments', true);
                    if (!is_array($payments)) $payments = [];

                    $total_paid = array_sum($payments);
                    $total_amount = $monthly * 12;
                    $total_due  = (12 * floatval($monthly)) - $total_paid;

                    echo "<tr class='item-row'>
        <td><a href='" . esc_url(get_edit_post_link($s->ID)) . "'><strong>" . esc_html($s->post_title) . "</strong></a></td>
        <td>" . esc_html($guardian) . "</td>
        <td>" . esc_html($contact) . "</td>
        <td>" . esc_html($subject) . "</td>
        <td style='text-align:center;'>" . esc_html($monthly) . "</td>";

                    foreach ($months_short as $m) {
                        $paid = isset($payments[$m]) ? floatval($payments[$m]) : 0;
                        $cell_style = $paid > 0
                            ? "background:#d4edda;color:#155724;font-weight:bold;text-align:center;"
                            : "background:#f8d7da;color:#721c24;text-align:center;";
                        echo "<td style='{$cell_style}'>" . esc_html($paid) . "</td>";
                    }

                    echo "<td style='text-align:center;background:#d4edda;color:#155724;font-weight:bold;'>" . esc_html($total_paid) . "</td>
          <td style='text-align:center;background:#f8d7da;color:#721c24;font-weight:bold;'>" . esc_html(max($total_due, 0)) . "</td>
    </tr>";
                }
                ?>
            </tbody>

        </table>
    </div>
<?php
}



add_action('admin_head', function () {
    echo '<style>
        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>';
});
