<?php

function cxdc_webmaster_pro_license_and_updates_page()
{
    $update_info = cyberxdc_webmaster_pro_compare_versions();
    $current_version = isset($update_info['current_version']) ? $update_info['current_version'] : 'Unknown';
    $has_update = $update_info['has_update'];
    $latest_version = isset($update_info['latest_version']) ? $update_info['latest_version'] : 'Unknown';
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

cxdc_webmaster_pro_license_and_updates_page();
