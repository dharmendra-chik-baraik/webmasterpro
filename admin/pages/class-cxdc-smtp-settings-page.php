<?php

class Webmasterpro_Smtp_Settings
{
    private $email_sending_logs = '';

    public function __construct()
    {
        add_action('phpmailer_init', array($this, 'cxdc_phpmailer_init'));
    }

    public function cxdc_phpmailer_init($phpmailer)
{
    $phpmailer->isSMTP();

    // SMTP configuration settings
    $smtp_settings = get_option('cxdc_webmaster_pro_smtp_settings');
    $phpmailer->Host = $smtp_settings['host'];
    $phpmailer->Port = $smtp_settings['port'];
    $phpmailer->SMTPAuth = true;
    $phpmailer->Username = $smtp_settings['username'];
    $phpmailer->Password = $smtp_settings['password'];
    $phpmailer->SMTPSecure = $smtp_settings['encryption'];

    // From email settings
    $phpmailer->setFrom($smtp_settings['from_email'], $smtp_settings['from_name']);

    // Enable debugging
    $phpmailer->SMTPDebug = 2;
    $phpmailer->Debugoutput = function ($str, $level) use (&$debug_messages) {
        $debug_messages[] = 'Email sending logs: ' . $str . ' - Level: ' . $level;
        set_transient('cxdc_webmaster_pro_phpmailer_logs', $debug_messages, HOUR_IN_SECONDS); // Expires in 1 hour
    };

    // Logging email send
    $phpmailer->ActionComplete = function ($email_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_email_logs';

        $to = implode(', ', $email_data->to);
        $subject = $email_data->subject;
        $message = $email_data->message;
        // Default status is 'sent'
        $status = 'sent';

        // Determine status based on result
        if (!$email_data->result) {
            $status = 'failed';
        }

        // Insert into database
        $wpdb->insert($table_name, array(
            'sent_to' => $to,
            'subject' => $subject,
            'message' => $message,
            'sent_at' => current_time('mysql'),
            'status' => $status,
        ));
    };
}


    public function cxdc_webmaster_pro_smtp_settings_page()
    {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'smtp_settings'; // Default tab is 'SMTP Settings'
        $notice = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['smtp_settings'])) {
                update_option('cxdc_webmaster_pro_smtp_settings', $_POST['smtp_settings']);
                $notice = '<div class="notice notice-success is-dismissible"><p>SMTP settings saved successfully.</p></div>';
            } elseif (isset($_POST['to_email'])) {
                $notice = $this->handle_test_email();
            }
        }

        // Get SMTP settings from options
        $smtp_settings = get_option('cxdc_webmaster_pro_smtp_settings', array(
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => '',
            'encryption' => '',
            'from_email' => '',
            'from_name' => ''
        ));

        // Render the page content based on the selected tab
?>
        <div class="cxdc_webmaster_pro-wrap wrap">
            <div class="container">
                <div class="cxdc_webmaster_pro-header card">
                    <h1>SMTP Settings</h1>
                    <p>Effortlessly configure and manage SMTP settings directly from your WordPress admin panel with cxdc_webmaster_pro. Ensure reliable email delivery and seamless communication with your audience. Integrate with your preferred email service providers and validate configurations with our convenient test email feature. Simplify your workflow and enhance user engagement with efficient email management, all within a few clicks.</p>
                    <h2 class="nav-tab-wrapper">
                        <a href="?page=webmasterpro-smtp-settings&tab=smtp_settings" class="nav-tab <?php echo $active_tab === 'smtp_settings' ? 'nav-tab-active' : ''; ?>">SMTP Settings</a>
                        <a href="?page=webmasterpro-smtp-settings&tab=test_email" class="nav-tab <?php echo $active_tab === 'test_email' ? 'nav-tab-active' : ''; ?>">Test Email</a>
                        <a href="?page=webmasterpro-smtp-settings&tab=smtp_logs" class="nav-tab <?php echo $active_tab === 'smtp_logs' ? 'nav-tab-active' : ''; ?>">SMTP Logs</a>
                        <a href="?page=webmasterpro-smtp-settings&tab=email_logs" class="nav-tab <?php echo $active_tab === 'email_logs' ? 'nav-tab-active' : ''; ?>">Email Logs</a>
                    </h2>
                    <?php if (!empty($notice)) {
                        echo $notice;
                    } ?>
                </div>

                <div class="row">
                    <div class="row">
                        <?php if ($active_tab === 'smtp_settings') : ?>
                            <div class="col card">
                                <div class="card-header">
                                    <h2>SMTP Configuration</h2>
                                    <p>Enter your SMTP settings.</p>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <table class="form-table">
                                            <tr>
                                                <th scope="row"><label for="host">SMTP Host</label></th>
                                                <td><input type="text" name="smtp_settings[host]" id="host" value="<?php echo esc_attr($smtp_settings['host']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="port">SMTP Port</label></th>
                                                <td><input type="text" name="smtp_settings[port]" id="port" value="<?php echo esc_attr($smtp_settings['port']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="username">SMTP Username</label></th>
                                                <td><input type="text" name="smtp_settings[username]" id="username" value="<?php echo esc_attr($smtp_settings['username']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="password">SMTP Password</label></th>
                                                <td><input type="password" name="smtp_settings[password]" id="password" value="<?php echo esc_attr($smtp_settings['password']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="encryption">Encryption</label></th>
                                                <td>
                                                    <select name="smtp_settings[encryption]" id="encryption">
                                                        <option value="" <?php selected($smtp_settings['encryption'], ''); ?>>None</option>
                                                        <option value="ssl" <?php selected($smtp_settings['encryption'], 'ssl'); ?>>SSL</option>
                                                        <option value="tls" <?php selected($smtp_settings['encryption'], 'tls'); ?>>TLS</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="from_email">From Email</label></th>
                                                <td><input type="email" name="smtp_settings[from_email]" id="from_email" value="<?php echo esc_attr($smtp_settings['from_email']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="from_name">From Name</label></th>
                                                <td><input type="text" name="smtp_settings[from_name]" id="from_name" value="<?php echo esc_attr($smtp_settings['from_name']); ?>" class="regular-text"></td>
                                            </tr>
                                        </table>
                                        <?php submit_button('Save SMTP Settings'); ?>
                                    </form>
                                </div>
                            </div>
                            <div class="col card">
                                <div class="card-header">
                                    <h2>Important Notes for SMTP Configuration</h2>
                                </div>
                                <div class="card-body">
                                    <p>Before configuring SMTP settings, please take note of the following important information:</p>
                                    <ul>
                                        <li><strong>SMTP Host:</strong> This is the server through which your emails will be sent. Common SMTP hosts include <code>smtp.gmail.com</code> for Gmail, <code>smtp.office365.com</code> for Office 365, and <code>smtp.mail.yahoo.com</code> for Yahoo Mail.</li>
                                        <li><strong>SMTP Port:</strong> Typical ports are <code>25</code>, <code>465</code> (for SSL), and <code>587</code> (for TLS). Check with your email provider for the correct port.</li>
                                        <li><strong>SMTP Username and Password:</strong> These are usually your email address and its corresponding password. For increased security, some services may require an app-specific password.</li>
                                        <li><strong>Encryption:</strong> Choose the appropriate encryption method (None, SSL, TLS) based on your email provider's recommendations. SSL and TLS provide added security for your email communications.</li>
                                        <li><strong>From Email and Name:</strong> Ensure that the "From Email" is verified or allowed by your SMTP provider to avoid email delivery issues.</li>
                                    </ul>
                                    <p>Here are some general tips to ensure smooth SMTP configuration:</p>
                                    <ul>
                                        <li>Double-check your SMTP server credentials to avoid misconfigurations.</li>
                                        <li>Some email providers, such as Gmail and Yahoo, require you to enable "Allow less secure apps" or generate an app password for SMTP access.</li>
                                        <li>Test your SMTP settings by sending a test email to ensure they are working correctly. Use the <strong>Test Email</strong> tab provided.</li>
                                        <li>Be mindful of your provider's sending limits to avoid having your account temporarily locked or restricted.</li>
                                    </ul>
                                    <p>If you encounter issues with your SMTP configuration, refer to your email provider's documentation or contact their support for assistance. Proper SMTP setup is crucial for reliable email delivery.</p>
                                </div>
                            </div>
                        <?php elseif ($active_tab === 'test_email') : ?>
                            <div class="col card">
                                <div class="card-header">
                                    <h2>Send Test Email</h2>
                                    <p>Send a test email to verify SMTP settings.</p>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <table class="form-table">
                                            <tr>
                                                <th scope="row"><label for="from_email">From Email</label></th>
                                                <td><input type="email" name="from_email" id="from_email" value="<?php echo esc_attr($smtp_settings['from_email']); ?>" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="to_email">To Email</label></th>
                                                <td><input type="email" name="to_email" id="to_email" value="" class="regular-text"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label for="test_email_content">Test Email Content</label></th>
                                                <td><textarea rows="12" name="test_email_content" id="test_email_content" class="regular-text">This is a test email to verify SMTP settings.</textarea></td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <?php submit_button('Send Test Email'); ?>
                                    </form>
                                </div>
                            </div>
                            <div class="card col">
                                <div class="card-header">
                                    <h2>Important Notes for Sending Test Emails</h2>
                                </div>
                                <div class="card-body">
                                    <p>Before sending a test email, please consider the following important information:</p>
                                    <ul>
                                        <li><strong>From Email:</strong> Make sure that the "From Email" field is correctly filled with a valid email address. This address should ideally match the domain of your SMTP settings or be verified by your email provider to avoid delivery issues.</li>
                                        <li><strong>To Email:</strong> Enter a valid recipient email address. This can be any address you have access to for testing purposes. Avoid using spammy or temporary email addresses.</li>
                                        <li><strong>Test Email Content:</strong> Customize the test email content as needed to better simulate real-world scenarios. This can help identify potential formatting or delivery issues.</li>
                                    </ul>
                                    <p>Here are some additional tips to ensure successful test email sending:</p>
                                    <ul>
                                        <li>Check your "From Email" and "To Email" addresses to ensure they are correctly formatted and active.</li>
                                        <li>Verify that your SMTP settings are accurately configured. If you encounter any issues, refer back to the SMTP Configuration section for guidance.</li>
                                        <li>Monitor your inbox and spam/junk folders for the test email. Sometimes, legitimate emails can end up in these folders due to spam filtering rules.</li>
                                        <li>After sending the test email, check the SMTP Logs for any potential issues or errors that may have occurred during the sending process.</li>
                                        <li>If you receive the test email successfully, it indicates that your SMTP settings are likely configured correctly.</li>
                                    </ul>
                                    <p>If you face any challenges while sending test emails, review your SMTP settings and ensure all details are accurate. You can also consult your email service provider's support for further assistance.</p>
                                </div>
                            </div>
                        <?php elseif ($active_tab === 'smtp_logs') : ?>
                            <div class="card col">
                                <div class="card-header">
                                    <h2>SMTP Logs</h2>
                                    <p>View SMTP sending logs.</p>
                                </div>
                                <div class="card-body">
                                    <div class="smtp_logs">
                                        <?php
                                        $debug_messages = get_transient('cxdc_webmaster_pro_phpmailer_logs');
                                        if ($debug_messages && is_array($debug_messages)) {
                                            foreach ($debug_messages as $message) {
                                                echo '<p>' . esc_html($message) . '</p>';
                                            }
                                        } else {
                                            echo '<p>No logs found.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card col">
                                <div class="card-header">
                                    <h2>Understanding PHPMailer Debug Logs</h2>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li><strong>Debug Levels:</strong> PHPMailer logs can have different debug levels, such as 1 (client messages), 2 (client and server messages), 3 (full information including data exchange). Higher levels provide more detailed information.</li>
                                        <li><strong>Connection Establishment:</strong> Look for logs related to the initial connection to the SMTP server. Successful connection logs should mention "SMTP -> FROM SERVER" or similar messages.</li>
                                        <li><strong>Server Responses:</strong> Pay attention to the server's response codes. Common codes include:
                                            <ul>
                                                <li>220 - Service ready</li>
                                                <li>250 - Requested mail action okay, completed</li>
                                                <li>354 - Start mail input; end with <CRLF>.<CRLF>
                                                </li>
                                                <li>550 - Requested action not taken: mailbox unavailable</li>
                                            </ul>
                                        </li>
                                        <li><strong>Authentication Process:</strong> Debug logs often include steps where the SMTP server verifies the user’s credentials. Look for messages related to "AUTH LOGIN" and responses that confirm successful authentication.</ <li><strong>Data Transmission:</strong> After authentication, the logs will show the process of sending the email data. Successful transmission will include logs like "SMTP -> FROM CLIENT" indicating the data being sent.
                                        <li><strong>Error Messages:</strong> If an error occurs, PHPMailer will log specific messages detailing what went wrong. Common issues include:
                                            <ul>
                                                <li><strong>Could not connect to SMTP host:</strong> Indicates problems with server connectivity. Check if the SMTP host and port settings are correct.</li>
                                                <li><strong>SMTP Error: Data not accepted:</strong> The server did not accept the email data. This could be due to spam filters, invalid recipients, or message content issues.</li>
                                                <li><strong>Authentication failed:</strong> The provided credentials were not accepted. Verify the username and password.</li>
                                            </ul>
                                        </li>
                                        <li><strong>SSL/TLS Issues:</strong> If using SSL/TLS, ensure that your server supports the required encryption protocols. Look for logs indicating "STARTTLS" to confirm the initiation of a secure connection.</li>
                                        <li><strong>Debugging Tips:</strong>
                                            <ul>
                                                <li>Increase the debug level in PHPMailer to get more detailed logs if you encounter issues.</li>
                                                <li>Check your server’s firewall settings to ensure that it is not blocking outgoing SMTP connections.</li>
                                                <li>Review your email provider’s guidelines for any specific requirements or restrictions on SMTP usage.</li>
                                            </ul>
                                        </li>
                                        <li><strong>Regular Monitoring:</strong> Regularly monitor the logs to preemptively catch issues before they affect email delivery. This can help maintain smooth email operations.</li>
                                        <li><strong>Refer to PHPMailer Documentation:</strong> For a comprehensive understanding of the logs and error codes, refer to the official [PHPMailer documentation](https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting).</li>
                                    </ul>
                                </div>
                            </div>
                        <?php elseif ($active_tab === 'email_logs') : ?>
                            <div class="card w-100 mw-100 col">
                                <div class="card-header">
                                    <h2>Email Logs</h2>
                                    <p>View email sending logs.</p>
                                    <?php echo $notice; ?>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <?php
                                        // Handle bulk actions
                                        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['log_ids'])) {
                                            global $wpdb;
                                            $logs_table = $wpdb->prefix . 'cxdcwmpro_email_logs';
                                            $log_ids = array_map('absint', $_POST['log_ids']);
                                            $in_ids = implode(',', $log_ids);
                                            $wpdb->query("DELETE FROM $logs_table WHERE id IN ($in_ids)");

                                           echo '<div class="notice notice-success is-dismissible"><p>Selected logs deleted successfully.</p></div>';
                                        }else{
                                            echo '<div class="notice notice-warning is-dismissible"><p>Please select logs to delete.</p></div>';
                                        }
                                        ?>
                                        <table class="wp-list-table widefat fixed striped">
                                            <thead>
                                                <tr>
                                                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                                                        <input type="checkbox" id="select-all">
                                                    </th>
                                                    <th>ID</th>
                                                    <th>Sent To</th>
                                                    <th>Sent At</th>
                                                    <th>Subject</th>
                                                    <th>Message</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                global $wpdb;
                                                $logs_table = $wpdb->prefix . 'cxdcwmpro_email_logs';
                                                $per_page = 10; // Number of logs per page
                                                $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
                                                $offset = ($current_page - 1) * $per_page;

                                                $logs = $wpdb->get_results("SELECT * FROM $logs_table ORDER BY sent_at DESC LIMIT $per_page OFFSET $offset");

                                                foreach ($logs as $log) {
                                                ?>
                                                    <tr>
                                                        <th scope="row" class="check-column">
                                                            <input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log->id); ?>">
                                                        </th>
                                                        <td><?php echo esc_html($log->id); ?></td>
                                                        <td><?php echo esc_html($log->sent_to); ?></td>
                                                        <td><?php echo esc_html($log->sent_at); ?></td>
                                                        <td><?php echo esc_html($log->subject); ?></td>
                                                        <td><?php echo esc_html($log->message); ?></td>
                                                        <td><?php echo esc_html($log->status); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th scope="col" class="manage-column column-cb check-column">
                                                        <input type="checkbox" id="select-all-bottom">
                                                    </th>
                                                    <th>ID</th>
                                                    <th>Sent To</th>
                                                    <th>Sent At</th>
                                                    <th>Subject</th>
                                                    <th>Message</th>
                                                    <th>Status</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="tablenav bottom">
                                            <div class="alignleft actions">
                                                <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                                                <select name="action" id="bulk-action-selector-bottom">
                                                    <option value="-1">Bulk Actions</option>
                                                    <option value="delete">Delete</option>
                                                </select>
                                                <input type="submit" name="doaction" id="doaction" class="button action" value="Apply">
                                            </div>
                                            <?php
                                            global $wpdb;
                                            $logs_table = $wpdb->prefix . 'cxdcwmpro_email_logs'; // Replace with your actual table name
                                            $per_page = 10; // Number of items per page

                                            // Calculate pagination
                                            $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
                                            $total_logs = $wpdb->get_var("SELECT COUNT(id) FROM $logs_table");
                                            $total_pages = ceil($total_logs / $per_page);

                                            // Generate pagination links
                                            $page_links = paginate_links(array(
                                                'base'      => add_query_arg('paged', '%#%'),
                                                'format'    => '',
                                                'prev_text' => __('&laquo; Previous'),
                                                'next_text' => __('Next &raquo;'),
                                                'total'     => $total_pages,
                                                'current'   => $current_page,
                                                'type'      => 'array', // Return value as array to customize links
                                            ));

                                            if ($page_links) :
                                            ?>
                                                <div class="tablenav-pages">
                                                    <span class="displaying-num"><?php echo sprintf(_n('1 item', '%s items', $total_logs), number_format_i18n($total_logs)); ?></span>
                                                    <span class="pagination-links">
                                                        <?php foreach ($page_links as $link) : ?>
                                                            <?php echo str_replace('page-numbers', 'button', $link); ?>
                                                        <?php endforeach; ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
    private function handle_test_email()
    {
        // Sanitize input values
        $from_email = sanitize_email($_POST['from_email']);
        $to_email = sanitize_email($_POST['to_email']);
        $subject = 'SMTP Test Email';
        $message = 'This is a test email to verify SMTP settings.';
        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: ' . $from_email);

        // Send test email
        $result = wp_mail($to_email, $subject, $message, $headers);

        // Determine result and log
        $status = 'sent';
        if (!$result) {
            $status = 'failed';
        }

        // Log test email sending
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_email_logs';
        $wpdb->insert($table_name, array(
            'sent_to' => $to_email,
            'subject' => $subject,
            'message' => $message,
            'sent_at' => current_time('mysql'),
            'status' => $status,
        ));

        // Return notice based on result
        if ($result) {
            return '<div class="notice notice-success is-dismissible"><p>Test email sent successfully.</p></div>';
        } else {
            return '<div class="notice notice-error is-dismissible"><p>Error sending test email.</p></div>';
        }
    }
}
?>