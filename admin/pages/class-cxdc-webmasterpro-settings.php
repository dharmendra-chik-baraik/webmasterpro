<?php
require_once plugin_dir_path(__FILE__) . '../partials/class-cxdc-wp-posts-settings.php';

class Webmasterpro_Settings
{

    public function cxdc_webmaster_pro_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general'; // Default tab is 'general'
?>
        <div class="wrap">
            <div class="container">
                <div class="card">
                    <div style="max-width: 100%;" class="webmasterpro-header">
                        <h1>WebMasterPro Settings</h1>
                        <p>Fine-tune your WordPress site with webmasterpro's comprehensive settings management. From global preferences to specific page, post, media, and file upload configurations, webmasterpro simplifies site optimization. Streamline workflow and enhance user experience with intuitive controls designed to maximize performance and efficiency. Empower your site with tailored settings that cater to your unique needs, ensuring seamless operation and scalability in your WordPress journey.</p>
                    </div>
                    <div class="tabs">
                        <h2 class="nav-tab-wrapper">
                            <a href="?page=webmasterpro-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General Settings</a>
                            <a href="?page=webmasterpro-settings&tab=pages" class="nav-tab <?php echo $active_tab == 'pages' ? 'nav-tab-active' : ''; ?>">Pages Settings</a>
                            <a href="?page=webmasterpro-settings&tab=posts" class="nav-tab <?php echo $active_tab == 'posts' ? 'nav-tab-active' : ''; ?>">Posts Settings</a>
                            <a href="?page=webmasterpro-settings&tab=media" class="nav-tab <?php echo $active_tab == 'media' ? 'nav-tab-active' : ''; ?>">Media Settings</a>
                            <a href="?page=webmasterpro-settings&tab=files" class="nav-tab <?php echo $active_tab == 'files' ? 'nav-tab-active' : ''; ?>">Files Settings</a>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="container">
                <?php
                switch ($active_tab) {
                    case 'general':
                        Webmasterpro_General_Settings::webmasterpro_general_settings_page();
                        break;
                    case 'pages':
                        webmasterpro_Page_Settings::render_page_settings();
                        break;
                    case 'posts':
                        webmasterpro_Post_Settings::render_post_settings();
                        break;
                    case 'media':
                        webmasterpro_Media_Settings::render_settings_page();
                        break;
                    case 'files':
                        webmasterpro_File_Upload_Settings::render_settings_page();
                        break;
                    default:
                        Webmasterpro_General_Settings::webmasterpro_general_settings_page();
                        break;
                }
                ?>
            </div>
        </div>
    <?php
    }
}

class Webmasterpro_General_Settings
{
    public static function webmasterpro_general_settings_page()
    {
        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Update maintenance mode setting
            update_option('webmasterpro_maintenance_mode', isset($_POST['webmasterpro_maintenance_mode']) ? '1' : '0');

            // Update auto-update settings
            update_option('webmasterpro_auto_update_core', isset($_POST['webmasterpro_auto_update_core']) ? '1' : '0');
            update_option('webmasterpro_auto_update_plugins', isset($_POST['webmasterpro_auto_update_plugins']) ? '1' : '0');
            update_option('webmasterpro_auto_update_themes', isset($_POST['webmasterpro_auto_update_themes']) ? '1' : '0');

            // Update excluded premium plugins
            $excluded_plugins = isset($_POST['webmasterpro_exclude_premium_plugins']) ? $_POST['webmasterpro_exclude_premium_plugins'] : '';
            update_option('webmasterpro_exclude_premium_plugins', $excluded_plugins);

            // Update excluded premium themes
            $excluded_themes = isset($_POST['webmasterpro_exclude_premium_themes']) ? $_POST['webmasterpro_exclude_premium_themes'] : '';
            update_option('webmasterpro_exclude_premium_themes', $excluded_themes);

            // Debug IP
            update_option('webmasterpro_debug_ip', sanitize_text_field($_POST['webmasterpro_debug_ip']));

            // Display a notice
            $notice = 'Settings saved successfully.';
        }

        // Get current settings
        $maintenance_mode = get_option('webmasterpro_maintenance_mode', '0');
        $auto_update_core = get_option('webmasterpro_auto_update_core', '0');
        $auto_update_plugins = get_option('webmasterpro_auto_update_plugins', '0');
        $auto_update_themes = get_option('webmasterpro_auto_update_themes', '0');
        $excluded_plugins = get_option('webmasterpro_exclude_premium_plugins', '');
        $excluded_themes = get_option('webmasterpro_exclude_premium_themes', '');
    ?>

        <div class="container">
            <div style="max-width: 100%; width: 100%;" class="card">
                <h3>webmasterpro General Settings</h3>
                <?php if (!empty($notice)) : ?>
                    <div style="margin: 0px;" class="notice notice-success is-dismissible">
                        <p><?php echo $notice; ?></p>
                    </div>
                <?php endif; ?>
                <hr>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_maintenance_mode">Maintenance Mode:</label></th>
                            <td><input class="checkbox" type="checkbox" id="webmasterpro_maintenance_mode" name="webmasterpro_maintenance_mode" value="1" <?php checked($maintenance_mode, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_auto_update_core">Enable Auto WP Core Update:</label></th>
                            <td><input class="checkbox" type="checkbox" id="webmasterpro_auto_update_core" name="webmasterpro_auto_update_core" value="1" <?php checked($auto_update_core, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_auto_update_plugins">Enable Auto Plugin Update:</label></th>
                            <td><input class="checkbox" type="checkbox" id="webmasterpro_auto_update_plugins" name="webmasterpro_auto_update_plugins" value="1" <?php checked($auto_update_plugins, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_exclude_premium_plugins">Exclude Premium Plugins (Comma separated list of plugin slugs):</label></th>
                            <td><input type="text" id="webmasterpro_exclude_premium_plugins" name="webmasterpro_exclude_premium_plugins" value="<?php echo esc_attr($excluded_plugins); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_auto_update_themes">Enable Auto Themes Update:</label></th>
                            <td><input class="checkbox" type="checkbox" id="webmasterpro_auto_update_themes" name="webmasterpro_auto_update_themes" value="1" <?php checked($auto_update_themes, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_exclude_premium_themes">Exclude Premium Themes (Comma separated list of theme slugs):</label></th>
                            <td><input type="text" id="webmasterpro_exclude_premium_themes" name="webmasterpro_exclude_premium_themes" value="<?php echo esc_attr($excluded_themes); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label class="label" for="webmasterpro_debug_ip">Enable Debug Mode for Your IP:</label></th>
                            <td><input type="text" id="webmasterpro_debug_ip" name="webmasterpro_debug_ip" value="<?php echo esc_attr(get_option('webmasterpro_debug_ip', '')); ?>" placeholder="Enter your IP address"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="submit">Save Settings</label></th>
                            <td><input class="button" type="submit" class="button-primary" value="Save Changes"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <style>
            .form-table th {
                width: 420px;
                max-width: 420px;
            }
        </style>
    <?php
    }
}

// Hook to enable maintenance mode if the setting is enabled
function webmasterpro_enable_maintenance_mode()
{
    // Get the maintenance mode option
    $maintenance_mode = get_option('webmasterpro_maintenance_mode', '0');

    // Check if maintenance mode is enabled
    if ($maintenance_mode === '1') {
        // Display maintenance message
    ?>
        <div style="max-width: 600px; margin: 50px auto; padding: 40px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); text-align: center;">
            <h1 style="color: #333333; font-size: 32px; margin-bottom: 20px;">webmasterpro</h1>
            <h2 style="color: #333333; font-size: 24px; margin-bottom: 10px;">Site Maintenance</h2>
            <p style="color: #666666; font-size: 16px;">This site is currently undergoing maintenance. Please check back later.</p>
        </div>
    <?php
        // Prevent further execution of WordPress
        exit;
    }
}

add_action('template_redirect', 'webmasterpro_enable_maintenance_mode');

// Hook to enable auto-updates
define('WP_AUTO_UPDATE_CORE', true);
add_filter('auto_update_plugin', '__return_true');
add_filter('auto_update_theme', '__return_true');

// Exclude premium themes from auto-updates
add_filter('auto_update_theme', 'webmasterpro_exclude_premium_themes');
function webmasterpro_exclude_premium_themes($update)
{
    try {
        $excluded_themes = get_option('webmasterpro_exclude_premium_themes', '');
        $premium_themes = array_map('trim', explode(',', $excluded_themes));

        if (isset($update->theme) && in_array($update->theme, $premium_themes)) {
            return false;
        }
        return $update;
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
    }
}

// Exclude premium plugins from auto-updates
add_filter('auto_update_plugin', 'webmasterpro_exclude_premium_plugins', 10, 2);
function webmasterpro_exclude_premium_plugins($update, $item)
{
    try {
        $excluded_plugins = get_option('webmasterpro_exclude_premium_plugins', '');
        $premium_plugins = array_map('trim', explode(',', $excluded_plugins));

        if (isset($item->plugin) && array_intersect($premium_plugins, explode('/', $item->plugin))) {
            return false;
        }
        return $update;
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
    }
}

class webmasterpro_File_Upload_Settings
{

    // Constructor to initialize hooks
    public function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('upload_mimes', array($this, 'modify_upload_mimes'));
    }

    // Register settings and sections
    public function register_settings()
    {
        register_setting('webmasterpro_file_upload_settings', 'blocked_extensions');
        register_setting('webmasterpro_file_upload_settings', 'allowed_extensions');
    }

    // Render settings page
    public static function render_settings_page()
    {
    ?>
        <div class="row card">
            <div class="col card">
                <h1>File Upload Settings</h1>
                <!-- Block File Upload Type Settings -->
                <div class="card-header">
                    <h3 class="wp-heading-inline">Block File Upload Type</h3>
                    <p>Enter file extensions below to block file upload in WordPress library</p>
                </div>
                <div class="card-body">
                    <form action="options.php" method="post">
                        <?php settings_fields('webmasterpro_file_upload_settings'); ?>
                        <?php do_settings_sections('webmasterpro_file_upload_settings'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="blocked_extensions">Blocked Extensions</label>
                                </th>
                                <td>
                                    <input type="text" id="blocked_extensions" name="blocked_extensions" value="<?php echo esc_attr(get_option('blocked_extensions')); ?>" class="regular-text" placeholder=".exe, .bat, .php">
                                    <p class="description">Enter comma-separated file extensions to block (e.g., .exe, .bat, .php).</p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Save Settings'); ?>
                    </form>
                </div>
            </div>
            <!-- Allow File Upload Type Settings -->
            <div class="col card">
                <div class="card-header">
                    <h3 class="wp-heading-inline">Allow File Upload Type</h3>
                    <p>Enter file extensions below to allow file upload in WordPress library</p>
                </div>
                <div class="card-body">
                    <form action="options.php" method="post">
                        <?php settings_fields('webmasterpro_file_upload_settings'); ?>
                        <?php do_settings_sections('webmasterpro_file_upload_settings'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="allowed_extensions">Allowed Extensions</label>
                                </th>
                                <td>
                                    <input type="text" id="allowed_extensions" name="allowed_extensions" value="<?php echo esc_attr(get_option('allowed_extensions')); ?>" class="regular-text" placeholder=".svg, .jpg, .png">
                                    <p class="description">Enter comma-separated file extensions (e.g., .svg, .jpg, .png).</p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Save Settings'); ?>
                    </form>
                </div>
            </div>
        </div>
<?php
    }

    // Modify upload MIME types based on settings
    public function modify_upload_mimes($mimes)
    {
        // Check if $mimes is an array
        if (!is_array($mimes)) {
            // Log an error or handle it gracefully
            error_log('MIME types array is missing or invalid.');
            return $mimes; // Return original $mimes array
        }

        // Get blocked extensions from the options table
        $blocked_extensions = get_option('blocked_extensions');

        // Check if blocked_extensions is empty or not set
        if (!empty($blocked_extensions)) {
            // Split blocked extensions into an array
            $extensions = explode(',', $blocked_extensions);

            // Loop through each extension and unset its corresponding MIME type
            foreach ($extensions as $extension) {
                if (isset($mimes[$extension])) {
                    unset($mimes[$extension]);
                }
            }
        }

        // Get allowed extensions from the options table
        $allowed_extensions = get_option('allowed_extensions');

        // Check if allowed_extensions is empty or not set
        if (!empty($allowed_extensions)) {
            // Split allowed extensions into an array
            $extensions = explode(',', $allowed_extensions);

            // Loop through each extension and add its corresponding MIME type
            foreach ($extensions as $extension) {
                // Determine MIME type based on extension
                switch ($extension) {
                    case 'exe':
                        $mimes['exe'] = 'application/octet-stream';
                        break;
                    case 'bat':
                        $mimes['bat'] = 'application/octet-stream';
                        break;
                    case 'svg':
                        $mimes['svg'] = 'image/svg+xml';
                        break;
                    case 'jpg':
                        $mimes['jpg'] = 'image/jpeg';
                        break;
                    case 'jpeg':
                        $mimes['jpeg'] = 'image/jpeg';
                        break;
                    case 'png':
                        $mimes['png'] = 'image/png';
                        break;
                    case 'gif':
                        $mimes['gif'] = 'image/gif';
                        break;
                    case 'bmp':
                        $mimes['bmp'] = 'image/bmp';
                        break;
                    case 'tiff':
                        $mimes['tiff'] = 'image/tiff';
                        break;
                    case 'webp':
                        $mimes['webp'] = 'image/webp';
                        break;
                    case 'avif':
                        $mimes['avif'] = 'image/avif';
                        break;
                    default:
                        // Log an error or handle unsupported extension gracefully
                        error_log('Unsupported extension: ' . $extension);
                        break;
                }
            }
        }

        // Return the modified MIME types array
        return $mimes;
    }
}

// Initialize the class
new webmasterpro_File_Upload_Settings();
