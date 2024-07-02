<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cyberxdc.online
 * @since             1.0.0
 * @package           Webmasterpro
 *
 * @wordpress-plugin
 * Plugin Name:       WebMasterPro
 * Plugin URI:        https://cyberxdc.online
 * Description:       WebMasterPro offers essential WordPress plugin solutions for seamless website management, including SMTP, security enhancements, SEO optimization, contact forms, database management, and versatile shortcodes.
 * Version:           1.0.0
 * Author:            CyberXDC
 * Author URI:        https://cyberxdc.online/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webmasterpro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if (!defined('WEBMASTERPRO_VERSION')) {
	// Replace the version number of the plugin with a date, like YYYYMMDD to bust cache
	define('WEBMASTERPRO_VERSION', '1.0.0');
}
if (!defined('WEBMASTERPRO_PATH')) {
	define('WEBMASTERPRO_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WEBMASTERPRO_PLUGIN_URL')) {
	define('WEBMASTERPRO_PLUGIN_URL', 'https://cyberxdc.online');
}
if (!defined('WEBMASTERPRO_AUTHOR_URI')) {
	define('WEBMASTERPRO_AUTHOR_URI', 'https://cyberxdc.online');
}
if (!defined('WEBMASTERPRO_TEXT_DOMAIN')) {
	define('WEBMASTERPRO_TEXT_DOMAIN', 'webmasterpro');
}
if (!defined('WEBMASTERPRO_PLUGIN_FILE')) {
	define('WEBMASTERPRO_PLUGIN_FILE', __FILE__);
}
if (!defined('WEBMASTERPRO_PLUGIN_BASENAME')) {
	define('WEBMASTERPRO_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('WEBMASTERPRO_PLUGIN_DIR')) {
	define('WEBMASTERPRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WEBMASTERPRO_PLUGIN_NAME')) {
	define('WEBMASTERPRO_PLUGIN_NAME', 'WebMasterPro');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webmasterpro-activator.php
 */
function activate_webmasterpro()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-webmasterpro-activator.php';
	Webmasterpro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webmasterpro-deactivator.php
 */
function deactivate_webmasterpro()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-webmasterpro-deactivator.php';
	Webmasterpro_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_webmasterpro');
register_deactivation_hook(__FILE__, 'deactivate_webmasterpro');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-webmasterpro.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webmasterpro()
{

	$plugin = new Webmasterpro();
	$plugin->run();
}
run_webmasterpro();
function add_custom_class_to_admin_body($classes)
{
	// Check if the current URL contains "cyberxdc"
	if (strpos($_SERVER['REQUEST_URI'], 'webmasterpro') !== false) {
		$classes .= ' webmasterpro-admin';
	}
	return $classes;
}
add_filter('admin_body_class', 'add_custom_class_to_admin_body');

// Add a custom update link to the plugin's action links in the plugins.php page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cyberxdc_webmaster_pro_add_update_link');

function cyberxdc_webmaster_pro_add_update_link($links)
{
    $update_info = cyberxdc_webmaster_pro_compare_versions();
    $settings_url = admin_url('options-general.php?page=webmasterpro');
    $settings_link = '<a href="' . esc_url($settings_url) . '">Settings</a>';
    array_unshift($links, $settings_link);
    if ($update_info['has_update']) {
        $update_link = '<a href="' . wp_nonce_url(admin_url('plugins.php?action=cyberxdc_webmaster_pro_update_plugin_action'), 'cyberxdc_webmaster_pro_update_plugin_nonce') . '" style="color: red;">Update to ' . esc_html($update_info['latest_version']) . '</a>';
        array_unshift($links, $update_link);
    }
    return $links;
}

// Hook into the admin_init action to handle the update process
add_action('admin_init', 'cyberxdc_webmaster_pro_handle_plugin_update_function');

function cyberxdc_webmaster_pro_handle_plugin_update_function()
{
    // Check if the action and nonce are set and valid
    if (isset($_GET['action']) && $_GET['action'] === 'cyberxdc_webmaster_pro_update_plugin_action' && check_admin_referer('cyberxdc_webmaster_pro_update_plugin_nonce')) {
        // Check user capabilities
        if (!current_user_can('update_plugins')) {
            wp_die('You do not have sufficient permissions to update plugins for this site.');
        }

        // Perform the update
        $update_result = cyberxdc_webmaster_pro_custom_update_functionality();

        // Set a transient to store the update result
        if ($update_result === true) {
            set_transient('cxdc_webmaster_pro_update_notice', 'success', 30); // Set to expire in 30 seconds
			error_log('Webmaster Pro plugin updated successfully.');
        } else {
            set_transient('cxdc_webmaster_pro_update_notice', 'failed', 30); // Set to expire in 30 seconds
        }

        // Redirect to the plugins page
        wp_redirect(admin_url('plugins.php'));
        exit; // Make sure to exit after the redirect to stop further execution
    }
}

// Display update success or failure notice
add_action('admin_notices', 'cyberxdc_webmaster_pro_display_update_notice');

function cyberxdc_webmaster_pro_display_update_notice()
{
    // Check if the transient exists
    $update_notice = get_transient('cxdc_webmaster_pro_update_notice');

    if ($update_notice) {
        // Display the appropriate notice
        if ($update_notice === 'success') {
            echo '<div class="notice notice-success is-dismissible">
                    <p>Webmaster Pro plugin updated successfully.</p>
                  </div>';
        } elseif ($update_notice === 'failed') {
            echo '<div class="notice notice-error is-dismissible">
                    <p>Failed to update the Webmaster Pro plugin. Please try again.</p>
                  </div>';
        }

        // Delete the transient after displaying the notice
        delete_transient('cxdc_webmaster_pro_update_notice');
    }
}


