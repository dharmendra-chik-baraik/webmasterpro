<?php
class Webmasterpro_Cf7db
{
    public function cxdc_webmaster_pro_cf7db_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if CF7DB plugin is active
        if (!function_exists('wpcf7_contact_form')) {
?>
            <div class="wrap">
                <div class="container">
                    <div class="card row">
                        <p>CF7DB plugin is not active. Please activate the plugin to use this feature.</p>
                    </div>
                </div>
            </div>
        <?php
            return;
        }

        // Check if a delete request is made
        if (isset($_GET['delete_id']) && isset($_GET['form_id'])) {
            // Get the submission ID and form ID from the URL
            $delete_id = intval($_GET['delete_id']);
            $form_id = intval($_GET['form_id']);

            // Delete the submission
            $this->webmasterpro_delete_submission($delete_id);

            // Redirect to the submissions list page for the form
            wp_redirect(admin_url('admin.php?page=webmasterpro-cf7-submissions&form_id=' . $form_id . '&delete_notice=1'));
            exit;
        }

        // Fetch all Contact Form 7 forms
        $contact_forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1
        ));

        if (isset($_GET['form_id'])) {
            // Get the form ID from the URL
            $form_id = intval($_GET['form_id']);
            // Load the page to display submissions for the specific form
            $this->webmasterpro_render_submissions_page($form_id);
            return;
        }

        // If view ID is provided in URL
        if (isset($_GET['view_id'])) {
            // Get the submission ID from the URL
            $submission_id = intval($_GET['view_id']);
            // Load the page to display the specific submission details
            $this->webmasterpro_render_submissions_page_view($submission_id);
            return;
        }
        ?>
        <div class="wrap">
            <div class="container">
                <div class="card">
                    <div style="max-width: 100%;" class="webmasterpro-header">
                        <h1>WebMasterPro Contact Form 7 Database</h1>
                        <p class="desc">Centralize and manage your Contact Form 7 submissions with ease using WebMasterPro. Our integrated database solution captures every form entry, ensuring you never miss critical communication from your audience. Access, search, and organize submissions directly from your WordPress dashboard, enhancing your ability to respond swiftly and maintain comprehensive records. Simplify your workflow and improve data handling efficiency, all while keeping valuable information at your fingertips.</p>
                    </div>
                </div>
                <div class="card">
                    <h2>Available Contact Forms</h2>
                    <?php if (empty($contact_forms)) : ?>
                        <p>No Contact Form 7 forms found.</p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" scope="col">ID</th>
                                    <th scope="col">Form Name</th>
                                    <th scope="col">Shortcode</th>
                                    <th style="width: 50px;" scope="col">Count</th>
                                    <th style="width: 132px;" scope="col">Submissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contact_forms as $form) : ?>
                                    <tr>
                                        <td style="width: 50px;"><?php echo esc_html($form->ID); ?></td>
                                        <td><?php echo esc_html($form->post_title); ?></td>
                                        <td>[contact-form-7 id="<?php echo esc_html($form->ID); ?>" title="<?php echo esc_html($form->post_title); ?>"]</td>
                                        <td style="width: 50px;">
                                            <?php
                                            global $wpdb;
                                            $table_name = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';
                                            $submissions_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE form_id = %d", $form->ID));
                                            echo $submissions_count;
                                            ?>
                                        </td>
                                        <td style="width: 132px;">
                                            <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions&form_id=' . $form->ID); ?>" class="button">View Submissions</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function webmasterpro_render_submissions_page($form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';

        // Fetch submissions for the specific form ID
        $submissions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE form_id = %d ORDER BY submission_time DESC", $form_id));

        $contact_forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ));

        $contact_form = null;
        foreach ($contact_forms as $form) {
            if ($form->ID == $form_id) {
                $contact_form = $form;
                break;
            }
        }

        if (!$contact_form) {
        ?>
            <div class="webmasterpro-wrap wrap">
                <div class="container">
                    <div style="max-width: 100%;" class="card">
                        <p>Form not found.</p>
                    </div>
                </div>
            </div>
        <?php
            return;
        }
        ?>
        <div class="wrap">
            <div class="container">
                <div class="card row">
                    <div class="col m-100 mw-100">
                        <h1>Contact Form 7 Submissions</h1>
                        <p class="desc">This is list of form data submissions on this form</p>
                        <h3 class="form-name">Form Name: <?php echo $contact_form->post_title; ?></h3>
                        <p>Submissions for Form ID: <?php echo $form_id; ?></p>
                    </div>
                </div>
                <div style="max-width: 100%; margin-top: 20px; " class="card">
                    <table style="margin-top: 20px;" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;" scope="col">ID</th>
                                <?php if (!empty($submissions)) :
                                    // Get the first submission to determine keys
                                    $first_submission = reset($submissions);
                                    $submission_data = unserialize($first_submission->submission_data);
                                    $keys = array_keys($submission_data);
                                    $first_key = isset($keys[0]) ? $keys[0] : '';
                                    $second_key = isset($keys[1]) ? $keys[1] : '';
                                    $third_key = isset($keys[2]) ? $keys[2] : '';
                                ?>
                                    <?php if (!empty($first_key)) : ?>
                                        <th scope="col"><?php echo esc_html(ucfirst($first_key)); ?></th>
                                    <?php endif; ?>
                                    <?php if (!empty($second_key)) : ?>
                                        <th scope="col"><?php echo esc_html(ucfirst($second_key)); ?></th>
                                    <?php endif; ?>
                                    <?php if (!empty($third_key)) : ?>
                                        <th scope="col"><?php echo esc_html(ucfirst($third_key)); ?></th>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                <?php endif; ?>
                                <th scope="col">Submission Time</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission) :
                                $submission_data = unserialize($submission->submission_data);

                                // Extract the first three keys from submission data
                                $keys = array_keys($submission_data);
                                $first_key = isset($keys[0]) ? $keys[0] : '';
                                $second_key = isset($keys[1]) ? $keys[1] : '';
                                $third_key = isset($keys[2]) ? $keys[2] : '';

                                // Format submission data for display
                                $first_value = isset($submission_data[$first_key]) ? esc_html($submission_data[$first_key]) : '';
                                $second_value = isset($submission_data[$second_key]) ? esc_html($submission_data[$second_key]) : '';
                                $third_value = isset($submission_data[$third_key]) ? esc_html($submission_data[$third_key]) : '';
                            ?>
                                <tr>
                                    <td><?php echo esc_html($submission->id); ?></td>
                                    <td><?php echo $first_value; ?></td>
                                    <td><?php echo $second_value; ?></td>
                                    <td><?php echo $third_value; ?></td>
                                    <td><?php echo esc_html($submission->submission_time); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions&view_id=' . $submission->id); ?>" class="button button-secondary">View</a>
                                        <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions&form_id=' . $form_id . '&delete_id=' . $submission->id); ?>" class="button button-danger" onclick="return confirm('Are you sure you want to delete this submission?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top: 20px;" class="action">
                        <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions'); ?>" class="button">Back</a>
                    </div>
                </div>
            </div>
        </div>
    <?php

    }

    public function webmasterpro_render_submissions_page_view($submission_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';
        // Fetch the submission details
        $submission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $submission_id));
        $submission_data = unserialize($submission->submission_data);

        $contact_forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ));

        $contact_form = null;
        foreach ($contact_forms as $form) {
            if ($form->ID == $submission->form_id) {
                $contact_form = $form;
                break;
            }
        }
    ?>
        <div class="wrap">
            <div class="container">
                <div class="row card">
                    <div class="col m-100 mw-100">
                        <h2>Contact Form name : <?php echo $contact_forms[0]->post_title; ?></h2>
                        <p class="text">Submission ID: <?php echo $submission_id; ?></p>
                        <p class="desc">Submission Time: <?php echo $submission->submission_time; ?></p>
                        <p class="desc">This is single view of contact form data</p>
                    </div>
                </div>
                <div class="row card">
                    <div class="col m-100 mw-100">
                        <table style="margin-top: 20px;" class="wp-list-table widefat fixed striped">
                            <tbody>
                                <?php foreach ($submission_data as $key => $value) : ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($key); ?>:</strong></td>
                                        <td><?php echo esc_html($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td><strong>Submission Time:</strong></td>
                                    <td><?php echo esc_html($submission->submission_time); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="max-width: 100%; width:100%; margin-top: 20px; " class="actions">
                            <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions&delete_id=' . $submission_id . '&form_id=' . $submission->form_id); ?>" class="button">Delete</a>
                            <a href="<?php echo admin_url('admin.php?page=webmasterpro-cf7-submissions&form_id=' . $submission->form_id); ?>" class="button">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    private function webmasterpro_delete_submission($submission_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';
        // Delete the submission from the database
        $wpdb->delete($table_name, array('id' => $submission_id), array('%d'));
    }
}
