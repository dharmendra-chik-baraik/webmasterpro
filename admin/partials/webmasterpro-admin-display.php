<?php 
class webmasterpro_admin_display
{
    public function __construct()
    {
    }

    public function webmasterpro_get_latest_visitors_logs($limit = 5)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_visitor_logs';
        $query = $wpdb->prepare("SELECT * FROM $table_name ORDER BY time DESC LIMIT %d", $limit);
        return $wpdb->get_results($query);
    }

    public function webmasterpro_get_latest_users_logs($limit = 5)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cxdcwmpro_users_logs';
        $query = $wpdb->prepare("SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d", $limit);
        return $wpdb->get_results($query);
    }

    public function webmasterpro_server_info()
    {
        // Server Information
        $server_info = array(
            'Server Name'        => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'N/A',
            'Server Software'    => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
            'PHP Version'        => PHP_VERSION,
            'MySQL Version'      => $this->get_mysql_version(),
            'Hostname'           => gethostname(),
            'Server IP Address'  => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A',
            'Remote IP Address'  => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A',
            'Operating System'   => php_uname(),
            'Document Root'      => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'N/A',
            'Server Protocol'    => isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'N/A',
            'Request Method'     => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'N/A',
            'Gateway Interface'  => isset($_SERVER['GATEWAY_INTERFACE']) ? $_SERVER['GATEWAY_INTERFACE'] : 'N/A',
            'Server Port'        => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'N/A',
            'Current Script'     => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'N/A'
        );

        return $server_info;
    }

    private function get_mysql_version()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }

    public function webmasterpro_wordpress_environment()
    {
        global $wpdb;
    
        // Collect WordPress environment details
        $environment_info = array(
            'WordPress Version'     => get_bloginfo('version'),
            'Active Theme'          => $this->get_active_theme_info(),
            'PHP Version'           => PHP_VERSION,
            'MySQL Version'         => $wpdb->get_var("SELECT VERSION()"),
            'Site URL'              => get_site_url(),
            'Home URL'              => get_home_url(),
            'WP_DEBUG'              => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
            'WP Memory Limit'       => WP_MEMORY_LIMIT,
            'WP Language'           => get_locale(),
            'Multisite'             => is_multisite() ? 'Enabled' : 'Disabled',
            'Active Plugins'        => count(get_option('active_plugins')),
            'Uploads Directory'     => $this->get_uploads_directory_info(),
            'WP Cron Status'        => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 'Disabled' : 'Enabled',
            'Database Charset'      => $wpdb->charset,
            'Database Collation'    => $wpdb->collate,
        );
    
        return $environment_info;
    }
    
    private function get_active_theme_info()
    {
        // Get active theme details
        $theme = wp_get_theme();
        return $theme->get('Name') . ' ' . $theme->get('Version');
    }
    
    private function get_uploads_directory_info()
    {
        // Get uploads directory details
        $uploads = wp_upload_dir();
        return $uploads['baseurl'] . ' (' . $uploads['basedir'] . ')';
    }
    

    public function webmasterpro_active_plugins()
    {
        // Load the list of all installed plugins
        $all_plugins = get_plugins();
    
        // Array to store the detailed plugin information
        $plugins_info = array();
    
        // Loop through each plugin and gather detailed information
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            // Determine if the plugin is active
            $is_active = is_plugin_active($plugin_file);
    
            // Check if the plugin is a must-use plugin
            $is_mu_plugin = strpos(WPMU_PLUGIN_DIR, dirname($plugin_file)) !== false;
    
            // Get the plugin's directory path
            $plugin_root = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
    
            // Check the security status (dummy check in this case)
            $security_status = $this->check_plugin_security_status($plugin_data['TextDomain'], $plugin_data['Version']);
    
            // Add the plugin information to the array
            $plugins_info[] = array(
                'Name'         => $plugin_data['Name'],
                'Type'         => $is_mu_plugin ? 'Must-Use' : 'Standard',
                'Document Root'=> $plugin_root,
                'Active'       => $is_active ? 'Active' : 'Inactive',
                'Secure'       => $security_status ? 'Secure' : 'Insecure',
                'Version'      => $plugin_data['Version'],
                'Plugin URI'   => $plugin_data['PluginURI'],
                'Author'       => $plugin_data['Author'],
                'Author URI'   => $plugin_data['AuthorURI'],
            );
        }
    
        return $plugins_info;
    }
    
    // Helper function to check the security status of a plugin
    private function check_plugin_security_status($plugin_slug, $current_version)
    {
        // For simplicity, we assume the latest version is secure. 
        // In a real scenario, you would check against a plugin repository or a security database.
    
        // Simulated plugin repository (normally, this data should come from a reliable source)
        $plugin_repository = array(
            'example-plugin-slug' => '1.2.3',
            // Add more plugins and their latest versions as needed
        );
    
        // Check if the plugin slug exists in the repository
        if (isset($plugin_repository[$plugin_slug])) {
            // Compare the current version with the latest version
            return version_compare($current_version, $plugin_repository[$plugin_slug], '>=');
        }
    
        // If the plugin slug is not found, assume it is secure (or handle it differently as per your need)
        return true;
    }
    

    public function webmasterpro_active_theme()
    {
        // Get the active theme object
        $active_theme = wp_get_theme();
    
        // Check if the active theme is a child theme
        $is_child_theme = $active_theme->parent() ? true : false;
    
        // Gather the theme details
        $theme_info = array(
            'Name'         => $active_theme->get('Name'),
            'Version'      => $active_theme->get('Version'),
            'Theme URI'    => $active_theme->get('ThemeURI'),
            'Author'       => $active_theme->get('Author'),
            'Author URI'   => $active_theme->get('AuthorURI'),
            'Description'  => $active_theme->get('Description'),
            'Is Child Theme' => $is_child_theme ? 'Yes' : 'No',
            'Parent Theme' => $is_child_theme ? $active_theme->parent()->get('Name') : 'N/A'
        );
    
        return $theme_info;
    }
    

    public function webmasterpro_performance_insights()
    {
        $performance_data = array();
    
        // Page Load Time
        $start_time = microtime(true);
        $page_load_time = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2); // in milliseconds
        $performance_data['Page Load Time'] = $page_load_time . ' ms';
    
        // Database Queries
        global $wpdb;
        $performance_data['Database Queries'] = $wpdb->num_queries;
        $performance_data['Query Time'] = round($wpdb->timer_stop() * 1000, 2) . ' ms'; // in milliseconds
    
        // Memory Usage
        $memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2); // in MB
        $performance_data['Memory Usage'] = $memory_usage . ' MB';
    
        // PHP Version
        $performance_data['PHP Version'] = phpversion();
    
        // WordPress Version
        $performance_data['WordPress Version'] = get_bloginfo('version');
    
        // Active Theme
        $active_theme = wp_get_theme();
        $performance_data['Active Theme'] = $active_theme->get('Name') . ' (v' . $active_theme->get('Version') . ')';
    
        // Server Information
        $performance_data['Server Name'] = $_SERVER['SERVER_NAME'];
        $performance_data['Server Software'] = $_SERVER['SERVER_SOFTWARE'];
        $performance_data['Document Root'] = $_SERVER['DOCUMENT_ROOT'];
    
        // Caching
        $caching_status = (defined('WP_CACHE') && WP_CACHE) ? 'Enabled' : 'Disabled';
        $performance_data['Caching Status'] = $caching_status;
    
        // CDN Status (basic check)
        $cdn_status = isset($_SERVER['HTTP_CDN_LOOP']) ? 'Using CDN' : 'No CDN Detected';
        $performance_data['CDN Status'] = $cdn_status;
    
        return $performance_data;
    }
    
}
