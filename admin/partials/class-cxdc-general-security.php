<?php

class Webmasterpro_General_Security
{
    public static function webmasterpro_general_security_page()
    {
        // Check if the form has been submitted
        if (isset($_POST['webmasterpro_general_settings_submit'])) {
            // Verify nonce for security
            if (!isset($_POST['webmasterpro_general_settings_nonce']) || !wp_verify_nonce($_POST['webmasterpro_general_settings_nonce'], 'webmasterpro_general_settings_nonce')) {
                wp_die('Nonce verification failed');
            }

            // Sanitize and update settings
            $disable_file_editor = isset($_POST['disable_file_editor']) ? '1' : '0';
            $disable_xml_rpc = isset($_POST['disable_xml_rpc']) ? '1' : '0';

            update_option('webmasterpro_disable_file_editor', $disable_file_editor);
            update_option('webmasterpro_disable_xml_rpc', $disable_xml_rpc);

            // Sanitize and update settings
            $max_login_attempts = isset($_POST['max_login_attempts']) ? intval($_POST['max_login_attempts']) : 5;
            $lockout_duration = isset($_POST['lockout_duration']) ? intval($_POST['lockout_duration']) : 60;

            update_option('webmasterpro_max_login_attempts', $max_login_attempts);
            update_option('webmasterpro_lockout_duration', $lockout_duration);

            // login alerts
            $login_alert = isset($_POST['login_alerts']) ? '1' : '0';
            update_option('webmasterpro_login_alerts', $login_alert);

            $force_https = isset($_POST['force_https']) ? '1' : '0';
            update_option('webmasterpro_force_https', $force_https);

            // Display a notice message
            $notice = 'Settings saved successfully.';
        }

        // Retrieve current settings
        $disable_file_editor = get_option('webmasterpro_disable_file_editor', '0');
        $disable_xml_rpc = get_option('webmasterpro_disable_xml_rpc', '0');
        $login_alerts = get_option('webmasterpro_login_alerts', '0');
        // Retrieve current settings
        $max_login_attempts = get_option('webmasterpro_max_login_attempts', 5);
        $lockout_duration = get_option('webmasterpro_lockout_duration', 60);
        if (!empty($notice)) {
            echo '<div class="notice notice-success is-dismissible"><p>' . $notice . '</p></div>';
        }

?>
        <div class="row">
            <div class="col card">
                <h2>General Security Settings</h2>
                <form method="post" action="">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Disable File Editor:</th>
                            <td>
                                <input type="checkbox" name="disable_file_editor" <?php checked('1', $disable_file_editor); ?> />
                                <p class="description">Prevent file editing from WordPress admin.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Disable XML-RPC:</th>
                            <td>
                                <input type="checkbox" name="disable_xml_rpc" <?php checked('1', $disable_xml_rpc); ?> />
                                <p class="description">Enhance security by disabling remote connections.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Max Login Attempts:</th>
                            <td>
                                <input type="number" name="max_login_attempts" min="1" value="<?php echo esc_attr($max_login_attempts); ?>" />
                                <p class="description">Limit failed login attempts per day.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Lockout Duration (seconds):</th>
                            <td>
                                <input type="number" name="lockout_duration" min="1" value="<?php echo esc_attr($lockout_duration); ?>" />
                                <p class="description">Set lockout time after failed attempts.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Enable Login Alerts:</th>
                            <td>
                                <input type="checkbox" name="login_alerts" <?php checked('1', get_option('webmasterpro_login_alerts', '0')); ?> />
                                <p class="description">Receive email alerts on user logins.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Force SSL/HTTPS:</th>
                            <td>
                                <input type="checkbox" name="force_https" <?php checked('1', get_option('webmasterpro_force_https', '0')); ?> />
                                <p class="description">Ensure secure data transmission.</p>
                            </td>
                        </tr>
                    </table>
                    <?php wp_nonce_field('webmasterpro_general_settings_nonce', 'webmasterpro_general_settings_nonce'); ?>
                    <input type="submit" name="webmasterpro_general_settings_submit" class="button-primary button-large " value="Save Changes">
                </form>
                <hr>
                <div class="child_card">
                    <p><strong>Caution:</strong> Disabling file editing and XML-RPC enhances security but limits customization options.</p>
                    <p><strong>Reminder:</strong> Maximize security by enabling login alerts and using HTTPS for secure communication.</p>
                </div>
            </div>
            <div class="col card">
                <h2>Security Tips & Insights</h2>
                <div class="content-section">
                    <h3>Disable File Editor</h3>
                    <p>Prevent unauthorized changes by disabling the theme and plugin file editor in the WordPress admin area. Use FTP/SFTP for file modifications instead.</p>
                </div>
                <div class="content-section">
                    <h3>Disable XML-RPC</h3>
                    <p>Protect against remote attacks by disabling XML-RPC, which is often exploited for brute-force and DDoS attacks. Disable it if not needed.</p>
                </div>
                <div class="content-section">
                    <h3>Limit Login Attempts</h3>
                    <p>Reducing the number of allowed login attempts minimizes the risk of brute-force attacks. Set a limit to protect your admin area.</p>
                </div>
                <div class="content-section">
                    <h3>Lockout Duration</h3>
                    <p>Define how long a user is locked out after reaching the max login attempts. A longer duration deters attackers but be mindful of user convenience.</p>
                </div>
                <div class="content-section">
                    <h3>General Security Practices</h3>
                    <ul>
                        <li>Keep WordPress, themes, and plugins updated.</li>
                        <li>Use strong, unique passwords.</li>
                        <li>Enable two-factor authentication (2FA).</li>
                        <li>Regularly back up your website.</li>
                    </ul>
                </div>
                <div class="content-section">
                    <h3>Learn More</h3>
                    <p>Explore these resources for more on WordPress security:</p>
                    <ul>
                        <li><a href="https://wordpress.org/support/article/hardening-wordpress/">WordPress: Hardening Tips</a></li>
                        <li><a href="https://www.wpbeginner.com/wordpress-security/">WPBeginner: Security Guide</a></li>
                        <li><a href="https://wordpress.org/plugins/wordfence/">Wordfence Security Plugin</a></li>
                        <li><a href="https://www.wpsecurityauditlog.com/">WP Security Audit Log</a></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    }

    public static function webmasterpro_user_security_page()
    {
        global $wpdb;
        $notice = '';

        // Check if the form has been submitted
        if (isset($_POST['webmasterpro_user_security_submit'])) {
            // Verify nonce for security
            if (!isset($_POST['webmasterpro_user_security_nonce']) || !wp_verify_nonce($_POST['webmasterpro_user_security_nonce'], 'webmasterpro_user_security_nonce')) {
                wp_die('Nonce verification failed');
            }

            // Sanitize and update username
            if (isset($_POST['new_username']) && isset($_POST['user_id'])) {
                $new_username = sanitize_user($_POST['new_username']);
                $user_id = intval($_POST['user_id']);

                if (username_exists($new_username)) {
                    $notice = 'Username already exists.';
                } elseif (empty($new_username)) {
                    $notice = 'Username cannot be empty.';
                } else {
                    $result = $wpdb->update(
                        $wpdb->users,
                        array('user_login' => $new_username),
                        array('ID' => $user_id)
                    );

                    if ($result === false) {
                        $notice = 'Error updating username: ' . $wpdb->last_error;
                    } else {
                        $notice = 'Username changed successfully.';
                    }
                }
            } else {
                $notice = 'Invalid input.';
            }
        }

        // Get administrators
        $admins = get_users(array(
            'role' => 'administrator'
        ));

    ?>
        <div class="webmasterpro-wrap">
            <div class="container">
                <div style="width: 100%; max-width: 100%; " class="card">
                    <h2>User Security Settings</h2>
                    <?php if (!empty($notice)) : ?>
                        <div style="margin: 0px;" class="notice notice-success is-dismissible">
                            <p><?php echo $notice; ?></p>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <h3>Administrator Accounts</h3>
                        <p>These are the accounts that have administrator privileges.</p>
                        <style>
                            #admin-table .id-column {
                                width: 50px;
                            }
                        </style>
                        <table id="admin-table" class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th width="50px" scope="col" class="id-column" class="manage-column">ID</th>
                                    <th scope="col" class="manage-column">Username</th>
                                    <th scope="col" class="manage-column">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin) : ?>
                                    <?php
                                    $username_warning = '';
                                    if ($admin->user_login === 'admin') {
                                        $username_warning = '<span style="color: red;"> - <strong>Danger!</strong> It is recommended to change the "admin" username for security reasons.</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td width="50px"><?php echo esc_html($admin->ID); ?></td>
                                        <td><?php echo esc_html($admin->user_login) . $username_warning; ?></td>
                                        <td><?php echo esc_html($admin->user_email); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="card col">
                                <h3>Change Your Username</h3>
                                <?php if (is_user_logged_in()) : ?>
                                    <?php $current_user = wp_get_current_user(); ?>
                                    <p>
                                        Current Username: <strong><?php echo esc_html($current_user->user_login); ?></strong>
                                    </p>
                                    <table class="form-table">
                                        <tr valign="top">
                                            <th scope="row">New Username</th>
                                            <td>
                                                <input type="text" name="new_username" value="" />
                                                <input type="hidden" name="user_id" value="<?php echo esc_attr($current_user->ID); ?>" />
                                                <p class="description">Enter a new username.</p>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="">
                                        <p class="webmasterpro-custom-info-box">
                                            NOTE: If you are currently logged in as "admin" you will be automatically logged out after changing your username and will be required to log back in.
                                        </p>
                                    </div>
                                    <br>
                                    <?php wp_nonce_field('webmasterpro_user_security_nonce', 'webmasterpro_user_security_nonce'); ?>
                                    <input type="submit" name="webmasterpro_user_security_submit" class="button-primary" value="Save Changes">
                                <?php else : ?>
                                    <p>You need to be logged in to change your username.</p>
                                <?php endif; ?>
                            </div>
                            <div class="card col">
                                <h3>Important Notes</h3>
                                <div class="custom-notice custom-notice-warning">
                                    <p><strong>Security Reminder:</strong></p>
                                    <p>1. Changing the "admin" username reduces the risk of unauthorized access. Always use strong, unique usernames and passwords to secure your site.</p>
                                    <p>2. Limit administrator accounts to necessary personnel only to minimize security risks.</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }
}

class webmasterpro_General_Security_actions
{

    public function __construct()
    {
        // Add hooks
        add_filter('xmlrpc_methods', [$this, 'disable_xml_rpc']);
        add_action('wp_login_failed', [$this, 'track_login_attempts']);
        add_action('authenticate', [$this, 'check_lockout_status'], 30, 1);
        add_action('wp_login', [$this, 'reset_login_attempts'], 10, 2);
        add_action('template_redirect', array($this, 'enforce_https'));

        // Handle activation and deactivation hooks
        register_activation_hook(__FILE__, [$this, 'flush_rewrite_rules']);
        register_deactivation_hook(__FILE__, [$this, 'flush_rewrite_rules']);

        // Disable File Editor
        if (get_option('webmasterpro_disable_file_editor', '0') === '1') {
            define('DISALLOW_FILE_EDIT', true);
        }
        add_action('wp_login', array($this, 'send_login_alert'), 10, 2);
    }
    public function enforce_https()
    {
        if (get_option('webmasterpro_force_https', '0') === '1') {
            if (!is_ssl()) {
                if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
                    wp_safe_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
                    exit();
                } else {
                    wp_safe_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
        }
    }

    // Disable XML-RPC methods
    public function disable_xml_rpc($methods)
    {
        if (get_option('webmasterpro_disable_xml_rpc', '0') === '1') {
            unset($methods['pingback.ping']);
            unset($methods['pingback.extensions.getPingbacks']);
        }
        return $methods;
    }

    // Flush rewrite rules on activation and deactivation
    public function flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }

    // Track login attempts
    public function track_login_attempts($username)
    {
        $current_date = date('Y-m-d');
        $user_attempts_key = 'webmasterpro_login_attempts_' . $username . '_' . $current_date;
        $login_attempts = get_option($user_attempts_key, 0);
        $login_attempts++;
        update_option($user_attempts_key, $login_attempts);

        $max_login_attempts = get_option('webmasterpro_max_login_attempts', 5);
        if ($login_attempts >= $max_login_attempts) {
            $this->lockout_user($username);
        }
    }

    // Lockout user after too many failed login attempts
    private function lockout_user($username)
    {
        $lockout_duration = get_option('webmasterpro_lockout_duration', 3600); // Default to 1 hour
        $lockout_expiration = time() + $lockout_duration;
        update_option('webmasterpro_lockout_' . $username, $lockout_expiration);
    }

    // Check if user is locked out during authentication
    public function check_lockout_status($user)
    {
        if (isset($_POST['log'])) {
            $username = $_POST['log'];
            $lockout_expiration = get_option('webmasterpro_lockout_' . $username, 0);

            if (time() < $lockout_expiration) {
                $error_msg = sprintf(__('You have been locked out due to multiple failed login attempts. Please try again after %s minutes.', 'text-domain'), ceil(($lockout_expiration - time()) / 60));
                return new WP_Error('webmasterpro_lockout', $error_msg);
            }
        }
        return $user;
    }

    // Reset login attempts on successful login
    public function reset_login_attempts($username, $user)
    {
        $current_date = date('Y-m-d');
        $user_attempts_key = 'webmasterpro_login_attempts_' . $username . '_' . $current_date;
        delete_option($user_attempts_key);
        delete_option('webmasterpro_lockout_' . $username);
    }

    public function send_login_alert($user_login, $user)
    {
        if (get_option('webmasterpro_login_alerts', '0') === '1') {
            $admin_email = get_option('admin_email');
            $from_email = get_option('webmasterpro_login_alerts_email', $admin_email);
            $subject = 'Login Alert: ' . $user_login;

            // Compose the HTML message
            $message = sprintf(
                "<html>
                <body>
                    <p>Dear Admin,</p>
                    <p>We would like to inform you that the user <strong>'%s'</strong> has successfully logged into your website <strong>'%s'</strong> on %s.</p>
                    <p>Here are the details of the user:</p>
                    <ul>
                        <li><strong>Username:</strong> %s</li>
                        <li><strong>Email:</strong> %s</li>
                        <li><strong>Roles:</strong> %s</li>
                    </ul>
                    <p>If you do not recognize this login activity, please take appropriate action to secure your account.</p>
                    <p>Best regards,<br>Your Website Security Team</p>
                </body>
                </html>",
                esc_html($user_login),
                esc_html(get_bloginfo('name')),
                esc_html(current_time('mysql')),
                esc_html($user_login),
                esc_html($user->user_email),
                esc_html(implode(', ', $user->roles))
            );
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $from_email
            );

            // Attempt to send the email
            $notification_mail_status = wp_mail($admin_email, $subject, $message, $headers);

            // Log the email sending process
            if ($notification_mail_status) {
                error_log('Login alert sent successfully.');
            } else {
                error_log('Failed to send login alert.');
            }
        }
    }
}

// Instantiate the security class
new webmasterpro_General_Security_actions();
