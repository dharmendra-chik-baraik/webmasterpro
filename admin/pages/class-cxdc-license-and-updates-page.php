<?php

function cxdc_webmaster_pro_license_and_updates_page()
{
    $update_info = cyberxdc_webmaster_pro_compare_versions();
    $current_version = isset($update_info['current_version']) ? $update_info['current_version'] : 'Unknown';
    $has_update = $update_info['has_update'];
    $latest_version = isset($update_info['latest_version']) ? $update_info['latest_version'] : 'Unknown';

    $notice = '';
        $license_key = get_option('cxdc_webmaster_pro_license_key');
        $license_status = get_option('cxdc_webmaster_pro_license_status');
        // Handle License Activation
        if (isset($_POST['activate_license'])) {
            check_admin_referer('cxdc_webmaster_pro_activate_license_nonce');
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
            $activation_response = wp_remote_post($activation_api_url, array('body' => $activation_data));

            error_log(print_r($activation_response, true));

            if (is_wp_error($activation_response)) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: ' . $activation_response->get_error_message() . '</p></div>';
            } else {
                $response_code = wp_remote_retrieve_response_code($activation_response);
                $response_body = wp_remote_retrieve_body($activation_response);
                $response_data = json_decode($response_body, true);

                if ($response_code == 200) {
                    // Successful activation
                    update_option('cxdc_webmaster_pro_license_status', 'active');
                    update_option('cxdc_webmaster_pro_license_key', $license_key);
                    delete_option('cxdc_webmaster_pro_license_validation_failed_date');
                    wp_clear_scheduled_hook('cxdc_webmaster_pro_delete_plugin_events');
                    $notice = '<div class="notice notice-success is-dismissible"><p>License activated successfully.</p></div>';
                } else {
                    $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error.';
                    $notice = '<div class="notice notice-error is-dismissible"><p>Error activating license: ' . $error_message . '</p></div>';
                }
            }
        }

        // Handle License Generation
        if (isset($_POST['generate_license'])) {
            check_admin_referer('cxdc_webmaster_pro_generate_license_nonce');

            // Prepare data for generating the license
            $generation_data = array(
                'generator' => home_url(),
                'user_name' => get_option('admin_user'),
                'user_email' => get_option('admin_email'),
            );

            $generation_api_url = WEBMASTERPRO_PLUGIN_URL . '/licenses/generate';

            // Send the POST request to the license generation API
            $generation_response = wp_remote_post($generation_api_url, array('body' => $generation_data));

            error_log('License generation response: ' . print_r($generation_response, true));

            if (is_wp_error($generation_response)) {
                $notice = '<div class="notice notice-error is-dismissible"><p>Error generating license: ' . $generation_response->get_error_message() . '</p></div>';
            } else {
                $response_code = wp_remote_retrieve_response_code($generation_response);
                $response_body = wp_remote_retrieve_body($generation_response);
                $response_data = json_decode($response_body, true);

                if ($response_code == 200 && isset($response_data['license_key'])) {
                    // License generated successfully
                    $generated_license_key = $response_data['license_key'];
                    update_option('cxdc_webmaster_pro_license_key', $generated_license_key);
                    update_option('cxdc_webmaster_pro_license_status', 'inactive');

                    $notice = '<div class="notice notice-success is-dismissible"><p>License generated successfully: ' . esc_html($generated_license_key) . '</p></div>';
                } else {
                    $error_message = isset($response_data['message']) ? $response_data['message'] : 'Unknown error.';
                    $notice = '<div class="notice notice-error is-dismissible"><p>Error generating license: ' . $error_message . '</p></div>';
                }
            }
        }
?>
    <div class="webmasterpro-wrap wrap">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1>Webmasterpro Updates & Licenses</h1>
                    <p>Welcome to the Webmasterpro Updates & Licenses page! Keep your plugin secure and feature-rich by staying up-to-date with the latest version.</p>
                </div>
                <div class="webmasterpro-content">
                    <div class="webmasterpro-update-info child_card">
                        <h3>Update Plugin</h3>
                        <p>Welcome to the Webmasterpro Updates & Licenses page! Keep your plugin secure and feature-rich by staying up-to-date with the latest version.</p>
                        <p>Plugin Version: <?php echo esc_html($current_version); ?></p>
                        <?php if ($has_update) : ?>
                            <p class="webmasterpro-update-info-message" style="color: red; background-color: #f8d7da;">New Version Available: <?php echo esc_html($latest_version); ?></p>
                            <p>Click the button below to update the plugin to the latest version.</p>
                            <form method="post">
                                <button type="submit" name="update_plugin" class="button button-primary">Update Plugin to Latest Version</button>
                            </form>
                        <?php else : ?>
                            <div class="webmasterpro-update-info-message">You are using the latest version of the plugin.</div>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col w-100 mw-100 child_card">
                            <div class="license_section">
                                <?php
                                if ($license_key && $license_status == 'active') {
                                    echo "<h3>License Information</h3>";
                                    echo "<p>License Key: " . esc_html(obfuscate_license_key($license_key)) . "</p>";
                                    echo "<p>License Status: Active</p>";
                                } elseif ($license_status == 'inactive') {
                                ?>
                                    <h3>Activate License</h3>
                                    <form method="post">
                                        <?php wp_nonce_field('cxdc_webmaster_pro_activate_license_nonce'); ?>
                                        <input type="text" name="license_key" placeholder="Enter License Key" value="<?php echo $license_key; ?>" required disabled>
                                        <input type="submit" class="button button-primary" name="activate_license" value="Activate License">
                                    </form>
                                <?php
                                } else {
                                ?>
                                    <h3>Generate License</h3>
                                    <form method="post">
                                        <?php wp_nonce_field('cxdc_webmaster_pro_generate_license_nonce'); ?>
                                        <input type="submit" class="button button-primary" name="generate_license" value="Generate License">
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <?php echo $notice; ?>
                    <div class="webmasterpro-license-info">
                        <h2>Licenses & Agreements</h2>
                        <h3>End User License Agreement (EULA)</h3>
                        <p>This End User License Agreement ("EULA") governs your use of the Webmasterpro plugin ("the Software"). By using the Software, you agree to the terms outlined below.</p>
                        <ol>
                            <li><strong>License Grant:</strong> You are granted a non-exclusive, non-transferable license to use the Webmasterpro plugin, provided you have a valid and active license. This allows you to install and use the plugin on your website(s) in accordance with the license type purchased.</li>
                            <li><strong>Ownership:</strong> The Webmasterpro plugin is the intellectual property of Webmasterpro. This EULA does not transfer any ownership rights. You are provided a license to use the plugin under the terms specified.</li>
                            <li><strong>Restrictions:</strong> You may not redistribute, sell, lease, or sublicense the Webmasterpro plugin without explicit permission from Webmasterpro. You are also prohibited from reverse engineering or attempting to derive the source code of the plugin, except where such activity is expressly permitted by applicable law.</li>
                            <li><strong>Third-Party Components:</strong> Webmasterpro may include third-party libraries and components licensed under the GPL. These components are used in accordance with their respective licenses and do not affect the proprietary nature of the Webmasterpro plugin itself.</li>
                            <li><strong>Support and Updates:</strong> With a valid license, you are entitled to receive support and updates for the duration of your license term. Webmasterpro reserves the right to limit or discontinue support for any reason, including end of life of the plugin.</li>
                            <li><strong>Warranty Disclaimer:</strong> The Webmasterpro plugin is provided "as is" without any warranties of any kind. Webmasterpro disclaims all warranties, whether express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, and non-infringement.</li>
                            <li><strong>Limitation of Liability:</strong> In no event shall Webmasterpro be liable for any damages arising from the use or inability to use the plugin, including but not limited to direct, indirect, incidental, special, or consequential damages, even if Webmasterpro has been advised of the possibility of such damages.</li>
                            <li><strong>Indemnification:</strong> You agree to indemnify and hold harmless Webmasterpro from any claims, damages, losses, liabilities, and expenses arising out of your use of the Webmasterpro plugin.</li>
                            <li><strong>Governing Law:</strong> This EULA shall be governed by and construed in accordance with the laws of India. Any disputes arising under this EULA shall be subject to the exclusive jurisdiction of the courts in India for Indian residents. For users outside India, this EULA shall be governed by the laws applicable in their respective jurisdictions, and any disputes shall be subject to the exclusive jurisdiction of the courts in their jurisdiction.</li>
                            <li><strong>Entire Agreement:</strong> This EULA constitutes the entire agreement between you and Webmasterpro regarding the use of the plugin and supersedes all prior agreements and understandings, whether written or oral, relating to the subject matter hereof.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

// Check if update button is clicked

if (isset($_POST['update_plugin'])) {
    $update_result = custom_update_functionality();

    if ($update_result === true) {
        set_transient('cxdc_webmaster_pro_update_notice', 'success', 30); // Set to expire in 30 seconds
        error_log('Plugin updated successfully-update page.');
        wp_redirect(admin_url('admin.php?page=webmasterpro-license-and-updates'));
    } else {
        set_transient('cxdc_webmaster_pro_update_notice', 'failed', 30); // Set to expire in 30 seconds
    }
}

// Custom function to perform update functionality
function custom_update_functionality()
{
    if (!defined('WEBMASTERPRO_PLUGIN_DIRECTORY_NAME')) {
        define('WEBMASTERPRO_PLUGIN_DIRECTORY_NAME', 'webmasterpro');
    }

    $repo_owner = get_option('cxdc_webmaster_pro_plugin_repo_owner');
    $repo_name = get_option('cxdc_webmaster_pro_plugin_repo_name');
    $tag = get_option('cxdc_webmaster_pro_plugin_repo_tagname');
    $download_url = "https://github.com/{$repo_owner}/{$repo_name}/archive/refs/heads/{$tag}.zip";
    $plugin_temp_zip = WP_PLUGIN_DIR . '/cyberxdc-temp.zip';

    // Check if plugin directory is writable
    if (!is_writable(WP_PLUGIN_DIR)) {
        error_log('The plugin directory is not writable.');
        return false;
    }

    // Download the plugin ZIP file
    $response = wp_remote_get($download_url, array('timeout' => 30));
    if (is_wp_error($response)) {
        error_log('Failed to download the plugin ZIP file from GitHub. Error: ' . $response->get_error_message());
        return false;
    }

    // Check HTTP response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log("Failed to download the plugin ZIP file from GitHub. HTTP Response Code: {$response_code}");
        return false;
    }

    // Save the plugin ZIP file to the plugin directory
    $file_saved = file_put_contents($plugin_temp_zip, wp_remote_retrieve_body($response));
    if ($file_saved === false) {
        error_log('Failed to save the plugin ZIP file to the plugin directory.');
        return false;
    }

    // Include WordPress filesystem API
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    WP_Filesystem();
    global $wp_filesystem;

    // Check if the downloaded ZIP file exists
    if (!$wp_filesystem->exists($plugin_temp_zip)) {
        error_log('The downloaded ZIP file does not exist.');
        return false;
    }

    // Unzip the plugin ZIP file
    $unzip_result = unzip_file($plugin_temp_zip, WP_PLUGIN_DIR);
    if (is_wp_error($unzip_result)) {
        error_log('Failed to extract the plugin ZIP file. Error: ' . $unzip_result->get_error_message());
        unlink($plugin_temp_zip);
        return false;
    }

    // Delete the temporary plugin ZIP file
    if (!$wp_filesystem->delete($plugin_temp_zip)) {
        error_log('Failed to delete the temporary plugin ZIP file.');
    }

    // Define the extracted folder path based on constant or default
    $extracted_folder_old = WP_PLUGIN_DIR . '/' . WEBMASTERPRO_PLUGIN_DIRECTORY_NAME . '-main';
    $extracted_folder_new = WP_PLUGIN_DIR . '/' . WEBMASTERPRO_PLUGIN_DIRECTORY_NAME;

    // Check if the extracted folder with "-main" suffix exists
    if ($wp_filesystem->exists($extracted_folder_old)) {
        // Attempt to rename the folder without "-main" suffix
        if (!$wp_filesystem->move($extracted_folder_old, $extracted_folder_new, true)) {
            error_log('Failed to rename the extracted plugin folder.');
            return false;
        }
    } else {
        // Check if the extracted folder without "-main" suffix exists
        if (!$wp_filesystem->exists($extracted_folder_new)) {
            error_log('Extracted plugin folder does not exist.');
            return false;
        }
    }

    return true;
}
// Notice for successful plugin update
function update_success_notice()
{
?>
    <div class="notice notice-success is-dismissible">
        <p>Plugin updated successfully!</p>
    </div>
<?php
}

// Notice for failed plugin update
function update_failed_notice()
{
?>
    <div class="notice notice-error is-dismissible">
        <p>Failed to update plugin. Please try again later.</p>
    </div>
<?php
}

function obfuscate_license_key($license_key)
{
    $key_length = strlen($license_key);
    $half_length = ceil($key_length / 2);

    $visible_part = substr($license_key, 0, $half_length);

    $obfuscated_license = $visible_part . str_repeat('*', $key_length - $half_length);

    return $obfuscated_license;
}
cxdc_webmaster_pro_license_and_updates_page();
