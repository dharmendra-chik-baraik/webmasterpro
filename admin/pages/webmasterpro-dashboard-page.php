<?php
require_once WEBMASTERPRO_PLUGIN_DIR . 'admin/partials/webmasterpro-admin-display.php';
class Webmasterpro_Dashboard
{

    private $admin_display;

    public function __construct()
    {
        $this->admin_display = new webmasterpro_admin_display();
    }

    public function webmasterpro_dashboard_page()
    {

        $notice = '';
        // Retrieve the saved license key
        $license_key = get_option('cxdc_webmaster_pro_license_key');
        // Retrieve the saved license status
        $license_status = get_option('cxdc_webmaster_pro_license_status');

        // Check if the license activation form was submitted
        if (isset($_POST['activate_license'])) {
            // Verify nonce for security
            check_admin_referer('cxdc_webmaster_pro_activate_license_nonce');

            // Get the license key from the form
            if (!isset($_POST['license_key'])) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: License key not found.</p></div>';
                return;
            }
            if (empty($_POST['license_key'])) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: License key is empty.</p></div>';
                return;
            }
            if (!is_string($_POST['license_key'])) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: License key is not a string.</p></div>';
                return;
            }
            if (strlen($_POST['license_key']) < 10) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: License key is too short.</p></div>';
                return;
            }

            // Update the license key variable
            $license_key = sanitize_text_field($_POST['license_key']);

            // API endpoint for license activation (replace with your actual endpoint)
            $activation_api_url = WEBMASTERPRO_PLUGIN_URL . '/licenses/activate';

            // Prepare data for the activation request
            $activation_data = array(
                'license_key' => $license_key,
                'domain' => home_url(),
                'user_email' => get_option('admin_email'),
                'user_name' => get_option('admin_user'),
                'server_name' => gethostname(),
            );

            // Send activation request
            $activation_response = wp_remote_post($activation_api_url, array(
                'body' => $activation_data,
            ));

            // Check for errors in the activation request
            if (is_wp_error($activation_response)) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: ' . $activation_response->get_error_message() . '</p></div>';
            } else {
                $response_code = wp_remote_retrieve_response_code($activation_response);
                $response_body = wp_remote_retrieve_body($activation_response);
                $response_data = json_decode($response_body, true);
                if ($response_code == 200) {
                    // Assuming HTTP status 200 indicates success
                    update_option('cxdc_webmaster_pro_license_status', 'active');
                    update_option('cxdc_webmaster_pro_license_key', $license_key);
                    delete_option('cxdc_webmaster_pro_license_validation_failed_date');
                    wp_clear_scheduled_hook('cxdc_webmaster_pro_delete_plugin_events');

                    $notice = '<div class="notice notice-success is-dismissible"><p>License activated successfully.</p></div>';
                } else {
                    // Handle other status codes or error responses
                    $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error.';
                    $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: ' . $error_message . '</p></div>';
                }
            }
        }
?>
        <div class="wrap">
            <div class="container">
                <div class="card">
                    <h1>WebMasterPro Dashboard</h1>
                    <h2>Empowering Your Online Presence with WebMasterPro</h2>
                    <p>Discover the power of the WebMasterPro Dashboard, your comprehensive solution for optimizing and securing your WordPress site. Elevate your management experience with advanced tools designed to enhance performance, streamline security, and enrich user engagement—all from a unified and user-friendly interface.</p>

                    <!-- License Information Section -->
                    <div class="row">
                        <div class="col w-100 mw-100 child_card">
                            <div class="license_section">
                                <?php
                                if ($license_key && $license_status == 'active') {
                                    echo "<h3>License Information</h3>";
                                    echo "<p>License Key: " . esc_html($this->obfuscate_license_key($license_key)) . "</p>";
                                    echo "<p>License Status: Active</p>";
                                } else {
                                ?>
                                    <h3>Activate License</h3>
                                    <?php echo $notice; ?>
                                    <form method="post">
                                        <?php wp_nonce_field('cxdc_webmaster_pro_activate_license_nonce'); ?>
                                        <input type="text" name="license_key" placeholder="Enter License Key" required>
                                        <input type="submit" class="button button-primary" name="activate_license" value="Activate License">
                                    </form>
                                    <br>
                                    <?php
                                    // Display warning if the license will be deactivated soon
                                    $failed_date = get_option('cxdc_webmaster_pro_license_validation_failed_date');
                                    if ($failed_date) {
                                        $days_remaining = 30 - floor((time() - $failed_date) / DAY_IN_SECONDS);
                                        if ($days_remaining > 0) {
                                    ?>
                                            <p style="margin: 0px; padding: 12px;" class="update-message notice inline notice-error notice-alt">This plugin will be deactivated in <?php echo esc_html($days_remaining) . ' days.'; ?></p>
                                        <?php
                                        } else {
                                        ?>
                                            <p style="margin: 0px; padding: 12px;" class="update-message notice inline notice-error notice-alt">This plugin will be deactivated soon.</p>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- WordPress Environment and Server Information -->
                <div class="row">
                    <div class="card col">
                        <h2>WordPress Environment</h2>
                        <table class="widefat striped">
                            <tbody>
                                <?php
                                $wp_environment = $this->admin_display->webmasterpro_wordpress_environment();
                                foreach ($wp_environment as $key => $value) {
                                    echo "<tr><td><strong>$key:</strong></td><td>$value</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card col">
                        <h2>Server Information</h2>
                        <table class="widefat striped">
                            <tbody>
                                <?php
                                $server_info = $this->admin_display->webmasterpro_server_info();
                                foreach ($server_info as $key => $value) {
                                    echo "<tr><td><strong>$key:</strong></td><td>$value</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Active Plugins and Performance Insights -->
                <div class="row">
                    <div class="card col">
                        <h2>Active Plugins</h2>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Plugin Name</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Secure</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $active_plugins = $this->admin_display->webmasterpro_active_plugins();
                                foreach ($active_plugins as $plugin) {
                                    echo "<tr><td>{$plugin['Name']}</td><td>{$plugin['Version']}</td><td>{$plugin['Active']}</td><td>{$plugin['Secure']}</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card col">
                        <h2>Performance Insights</h2>
                        <table class="widefat striped">
                            <tbody>
                                <?php
                                $performance_insights = $this->admin_display->webmasterpro_performance_insights();
                                foreach ($performance_insights as $key => $value) {
                                    echo "<tr><td><strong>$key:</strong></td><td>$value</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    // Function to partially obfuscate the license key
    private function obfuscate_license_key($license_key)
    {
        $key_length = strlen($license_key);
        $half_length = ceil($key_length / 2);

        $visible_part = substr($license_key, 0, $half_length);

        $obfuscated_license = $visible_part . str_repeat('*', $key_length - $half_length);

        return $obfuscated_license;
    }
}
?>