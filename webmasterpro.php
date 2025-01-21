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
 * @since             1.0.1
 * @package           Webmasterpro
 *
 * @wordpress-plugin
 * Plugin Name:       WebMasterPro
 * Plugin URI:        https://cyberxdc.online
 * Description:       WebMasterPro offers essential WordPress plugin solutions for seamless website management, including SMTP, security enhancements, SEO optimization, contact forms, database management, and versatile shortcodes.
 * Version:           1.0.1
 * Author:            CyberXDC
 * Author URI:        https://cyberxdc.online/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webmasterpro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
ob_start();
if (!defined('WPINC')) {
    die;
}
if (!defined('WEBMASTERPRO_VERSION')) {
    // Replace the version number of the plugin with a date, like YYYYMMDD to bust cache
    define('WEBMASTERPRO_VERSION', '1.0.1');
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
    // Ensure no output has been sent before calling wp_redirect
    if (ob_get_level()) ob_end_clean();  
    // Include the activator class and run the activation function
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
require plugin_dir_path(__FILE__) . 'admin/pages/class-cxdc-shortcodes-page.php';
require plugin_dir_path(__FILE__) . 'admin/partials/webmasterpro-admin-actions.php';
require plugin_dir_path(__FILE__) . 'admin/partials/class-cxdc-general-security.php';
require plugin_dir_path(__FILE__) . 'admin/partials/class-cxdc-database-security.php';
require plugin_dir_path(__FILE__) . 'admin/partials/class-cxdc-firewalls-rules.php';
require plugin_dir_path(__FILE__) . 'admin/partials/class-cxdc-two-factor-auth.php';
require plugin_dir_path(__FILE__) . 'admin/pages/class-cxdc-webmasterpro-settings.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.1
 */
function run_webmasterpro()
{

    $plugin = new Webmasterpro();
    $plugin->run();
}
run_webmasterpro();


add_action('admin_footer', 'initialize_color_picker');
function initialize_color_picker()
{
?>
    <script>
        jQuery(document).ready(function($) {
            $('#background_color_picker').wpColorPicker({
                change: function(event, ui) {
                    $('#background_color').val(ui.color.toString());
                },
                clear: function() {
                    $('#background_color').val('');
                }
            });

            $('#text_color_picker').wpColorPicker({
                change: function(event, ui) {
                    $('#text_color').val(ui.color.toString());
                },
                clear: function() {
                    $('#text_color').val('');
                }
            });
        });
    </script>
<?php
}

function cyberxdc_custom_login_styles()
{
    // Retrieve options from database
    $login_page_options = get_option('webmasterpro_login_page_settings');
    $background_color = isset($login_page_options['background_color']) ? $login_page_options['background_color'] : '';
    $text_color = isset($login_page_options['text_color']) ? $login_page_options['text_color'] : '';
    $background_image = isset($login_page_options['background_image']) ? $login_page_options['background_image'] : '';
    $logo_image = isset($login_page_options['logo_image']) ? $login_page_options['logo_image'] : '';
    $logo_url = isset($login_page_options['logo_url']) ? $login_page_options['logo_url'] : '';



    // Output custom styles
    echo '<style type="text/css">
        body.login {
            background-color: ' . esc_attr($background_color) . ';
            background-image: url(' . esc_url($background_image) . ');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
        .login h1 a {
            background-image: url(' . esc_url($logo_image) . ') !important;
            background-size: contain;
            width: auto !important;
            font-size: 40px !important;
            height: auto !important;
            background-size: contain !important;
            background-position: center;
            background-repeat: no-repeat;
        }
            .login #backtoblog a, .login #nav a {
                color: ' . esc_attr($text_color) . ' !important;
            }
        </style>';
}
add_action('login_enqueue_scripts', 'cyberxdc_custom_login_styles');
function add_custom_class_to_admin_body($classes)
{
    // Check if the current URL contains "cyberxdc"
    if (strpos($_SERVER['REQUEST_URI'], 'webmasterpro') !== false) {
        $classes .= ' webmasterpro-admin';
    }
    return $classes;
}
add_filter('admin_body_class', 'add_custom_class_to_admin_body');

function cyberxdc_custom_login_logo_url()
{
    // Retrieve options from database
    $login_page_options = get_option('webmasterpro_login_page_settings');
    $logo_url = isset($login_page_options['logo_url']) ? $login_page_options['logo_url'] : '';
    if (!empty($logo_url)) {
        return $logo_url;
    }
    return home_url();
}
add_filter('login_headerurl', 'cyberxdc_custom_login_logo_url');

// Add a custom update link to the plugin's action links in the plugins.php page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cyberxdc_webmaster_pro_add_update_link');

function cyberxdc_webmaster_pro_add_update_link($links)
{
    $update_info = cyberxdc_webmaster_pro_compare_versions();
    $settings_url = admin_url('admin.php?page=webmasterpro-settings');
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

        error_log('Update result: ' . var_export($update_result, true));

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


add_action('admin_enqueue_scripts', function ($hook_suffix) {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media();
});


// Add the dashboard widget
function cyberxdc_webmasterpro_dashboard_widget()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cxdcwmpro_users_logs';

    // Query to get the latest 5 rows from the cyberxdc_users_logs table
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT 5");

?>
    <div class="cyberxdc-dashboard-widget">
        <h3>About WebMasterPro Plugin</h3>
        <p>WebMasterPro is a security-focused plugin designed to enhance the security of your website. It provides various features to protect your site from common security threats.</p>
        <p>Explore the plugin settings and features to learn more about how WebMasterPro can help safeguard your website.</p>
        <br>
        <a href="<?php echo admin_url('admin.php?page=webmasterpro'); ?>" class="button button-primary">Explore More</a>
        <br>
        <br>
        <h4><b>Recent Activity Log</b></h4>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Timestamp</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">User</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Activity</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)) : ?>
                    <?php foreach ($results as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log->timestamp); ?></td>
                            <td><?php echo esc_html($log->user); ?></td>
                            <td><?php echo esc_html($log->activity); ?></td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No log entries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}

// Hook into the WordPress dashboard
function add_cyberxdc_webmasterpro_dashboard_widgets()
{
    wp_add_dashboard_widget('cyberxdc_webmasterpro_dashboard_widget', 'WebMasterPro', 'cyberxdc_webmasterpro_dashboard_widget');
}

// Function to ensure our widget is first
function prioritize_cyberxdc_webmasterpro_dashboard_widget()
{
    global $wp_meta_boxes;

    // Backup all dashboard widgets
    $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

    // Remove all widgets first
    $wp_meta_boxes['dashboard']['normal']['core'] = [];

    // Add our widget first
    add_cyberxdc_webmasterpro_dashboard_widgets();

    // Re-add other widgets
    if (is_array($normal_dashboard)) {
        $wp_meta_boxes['dashboard']['normal']['core'] = array_merge(
            ['cyberxdc_webmasterpro_dashboard_widget' => $wp_meta_boxes['dashboard']['normal']['core']['cyberxdc_webmasterpro_dashboard_widget']],
            $normal_dashboard
        );
    }
}

add_action('wp_dashboard_setup', 'prioritize_cyberxdc_webmasterpro_dashboard_widget', 99);

// Enqueue custom CSS in header and footer

function cyberxdc_webmasterpro_enqueue_custom_css()
{
    // Retrieve custom CSS options
    $custom_style_options = get_option('webmasterpro_custom_style_settings');
    $header_css = isset($custom_style_options['header_css']) ? $custom_style_options['header_css'] : '';
    $footer_css = isset($custom_style_options['footer_css']) ? $custom_style_options['footer_css'] : '';

    // Enqueue header CSS
    if (!empty($header_css)) {
        add_action('wp_head', function () use ($header_css) {
            echo '<style>' . $header_css . '</style>';
        });
    }

    // Enqueue footer CSS
    if (!empty($footer_css)) {
        add_action('wp_footer', function () use ($footer_css) {
            echo '<style>' . $footer_css . '</style>';
        });
    }
}

add_action('wp_enqueue_scripts', 'cyberxdc_webmasterpro_enqueue_custom_css');


// Enqueue custom scripts in the header and footer
function cyberxdc_webmasterpro_enqueue_custom_scripts() {
    $custom_script_options = get_option('webmasterpro_custom_script_settings');

    // Check if custom script options exist and if they are in the expected format
    if (is_array($custom_script_options) && isset($custom_script_options['header_script']) && isset($custom_script_options['footer_script'])) {
        $header_script = $custom_script_options['header_script'];
        $footer_script = $custom_script_options['footer_script'];

        // Remove slashes using WordPress function wp_unslash()
        $header_script = wp_unslash($header_script);
        $footer_script = wp_unslash($footer_script);

        // Sanitize scripts
        $sanitized_header_script = !empty($header_script) ? wp_strip_all_tags($header_script) : '';
        $sanitized_footer_script = !empty($footer_script) ? wp_strip_all_tags($footer_script) : '';

        // Add header script to wp_head
        if (!empty($sanitized_header_script)) {
            add_action('wp_head', function () use ($sanitized_header_script) {
                echo "<script>{$sanitized_header_script}</script>";
            });
        }

        // Add footer script to wp_footer
        if (!empty($sanitized_footer_script)) {
            add_action('wp_footer', function () use ($sanitized_footer_script) {
                echo "<script>{$sanitized_footer_script}</script>";
            });
        }
    }
}

add_action('init', 'cyberxdc_webmasterpro_enqueue_custom_scripts');

