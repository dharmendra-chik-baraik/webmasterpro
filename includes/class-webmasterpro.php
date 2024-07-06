<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cyberxdc.online
 * @since      1.0.0
 *
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 * @author     CyberXDC <contact@cyberxdc.online>
 */
class Webmasterpro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Webmasterpro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WEBMASTERPRO_VERSION' ) ) {
			$this->version = WEBMASTERPRO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'webmasterpro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Webmasterpro_Loader. Orchestrates the hooks of the plugin.
	 * - Webmasterpro_i18n. Defines internationalization functionality.
	 * - Webmasterpro_Admin. Defines all hooks for the admin area.
	 * - Webmasterpro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webmasterpro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webmasterpro-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webmasterpro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-webmasterpro-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webmasterpro-menu.php';
		$this->loader = new Webmasterpro_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Webmasterpro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Webmasterpro_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Webmasterpro_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Webmasterpro_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Webmasterpro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}



function cyberxdc_webmaster_pro_get_latest_version_from_github() {
    // Get options for repo owner, repo name, and branch/tag
    $repo_owner = get_option('cxdc_webmaster_pro_plugin_repo_owner');
    $repo_name = get_option('cxdc_webmaster_pro_plugin_repo_name');
    $branch_or_tag = get_option('cxdc_webmaster_pro_plugin_repo_tagname');
    $file_path = get_option('cxdc_webmaster_pro_plugin_repo_version_file');

    // Construct the GitHub URL
    $github_url = "https://raw.githubusercontent.com/$repo_owner/$repo_name/$branch_or_tag/$file_path";

    // Fetch the content from the GitHub URL
    $response = wp_remote_get($github_url);

    // Check for errors in the response
    if (is_wp_error($response)) {
        error_log('GitHub API request failed: ' . $response->get_error_message());
        return false;
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the version is set in the response data
    if (isset($data['version'])) {
        return $data['version'];
    }

    // Log an error if the version is not found
    error_log('Version not found in the GitHub response.');
    return false;
}


// Function to compare versions
function cyberxdc_webmaster_pro_compare_versions() {
    // Assume CYBERXDC_VERSION is defined somewhere in your plugin code
    $current_version = defined('WEBMASTERPRO_VERSION') ? WEBMASTERPRO_VERSION : '';

    $latest_version = cyberxdc_webmaster_pro_get_latest_version_from_github();

    if ($latest_version && version_compare($latest_version, $current_version, '>')) {
        return array(
            'has_update' => true,
            'latest_version' => $latest_version,
            'current_version' => $current_version
        );
    }

    return array(
        'has_update' => false,
        'current_version' => $current_version,
        'latest_version' => $latest_version // To ensure consistency
    );
}

function cyberxdc_webmaster_pro_custom_update_functionality()
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
