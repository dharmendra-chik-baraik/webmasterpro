<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cyberxdc.online
 * @since      1.0.0
 *
 * @package    Webmasterpro
 * @subpackage Webmasterpro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Webmasterpro
 * @subpackage Webmasterpro/admin
 * @author     cxdc_webmaster_pro <contact@cxdc_webmaster_pro.online>
 */
class Webmasterpro_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		// Register hooks for styles and scripts
		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Register hook for PHPMailer initialization
		add_action('phpmailer_init', array($this, 'cxdc_webmaster_pro_phpmailer_init'));
		
		// Schedule and validate license hooks
		add_action('wp', array($this, 'schedule_daily_license_validation'));
		add_action('wp_login', array($this, 'cxdc_webmaster_pro_validate_license'));
		add_action('cxdc_webmaster_pro_validate_license_event', array($this, 'cxdc_webmaster_pro_validate_license'));
		add_action('cxdc_webmaster_pro_delete_plugin_events', array($this, 'cxdc_webmaster_pro_delete_plugin_directory'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/webmasterpro-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/webmasterpro-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Configure PHPMailer to use the plugin's SMTP settings.
	 *
	 * @since    1.0.0
	 * @param    PHPMailer    $phpmailer    The PHPMailer instance.
	 */
	public function cxdc_webmaster_pro_phpmailer_init($phpmailer) {
        $smtp_settings = get_option('cxdc_webmaster_pro_smtp_settings');

        if (!empty($smtp_settings['host']) && !empty($smtp_settings['username']) && !empty($smtp_settings['password'])) {
            $phpmailer->isSMTP();
            $phpmailer->Host = $smtp_settings['host'];
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = $smtp_settings['port'];
            $phpmailer->Username = $smtp_settings['username'];
            $phpmailer->Password = $smtp_settings['password'];
            $phpmailer->SMTPSecure = $smtp_settings['encryption'];

            $phpmailer->From = $smtp_settings['from_email'];
            $phpmailer->FromName = $smtp_settings['from_name'];
			
			$phpmailer->SMTPDebug = 2;
			$phpmailer->Debugoutput = function($str, $level) {
				error_log($str);
			};
        }
    }

	/**
	 * Schedule a daily event to validate the license.
	 *
	 * @since    1.0.0
	 */
	public function schedule_daily_license_validation()
	{
		if (!wp_next_scheduled('cxdc_webmaster_pro_validate_license_event')) {
			wp_schedule_event(time(), 'daily', 'cxdc_webmaster_pro_validate_license_event');
		}
	}

	/**
	 * Validate the license key.
	 *
	 * @since    1.0.0
	 */
	public function cxdc_webmaster_pro_validate_license() {
		$stored_license_key = get_option('cxdc_webmaster_pro_license_key');
		
		if (!empty($stored_license_key)) {
			$validation_api_url = WEBMASTERPRO_PLUGIN_URL . '/licenses/validate';

			$validation_data = array(
				'license_key' => $stored_license_key,
				'domain' => home_url(),
			);

			$validation_response = wp_remote_post($validation_api_url, array(
				'body' => $validation_data,
			));

			if (is_wp_error($validation_response)) {
				error_log('License validation error: ' . $validation_response->get_error_message());
				return;
			}

			$response_body = wp_remote_retrieve_body($validation_response);
			$response_data = json_decode($response_body, true);
			error_log('License validation response: ' . print_r($response_data, true));

			if (isset($response_data['status']) && $response_data['status'] === 'active') {
				// License is valid
				update_option('cxdc_webmaster_pro_license_status', 'active');
				// Delete scheduled deletion event if it exists
				if (wp_next_scheduled('cxdc_webmaster_pro_delete_plugin_events')) {
					wp_clear_scheduled_hook('cxdc_webmaster_pro_delete_plugin_events');
				}
			} elseif (isset($response_data['status']) && $response_data['status'] === 'invalid') {
				// License is not valid
				error_log('License validation failed: ' . print_r($response_data, true));
				update_option('cxdc_webmaster_pro_license_status', 'invalid');

				// Schedule deletion after 30 days if not already scheduled
				if (!wp_next_scheduled('cxdc_webmaster_pro_delete_plugin_events')) {
					wp_schedule_single_event(time() + 30 * DAY_IN_SECONDS, 'cxdc_webmaster_pro_delete_plugin_events');
					// Update or add option cxdc_webmaster_pro_license_validation_failed_date
					$failed_date = get_option('cxdc_webmaster_pro_license_validation_failed_date');
					if (!$failed_date) {
						add_option('cxdc_webmaster_pro_license_validation_failed_date', current_time('timestamp'));
					} else {
						update_option('cxdc_webmaster_pro_license_validation_failed_date', current_time('timestamp'));
					}
				}
			} elseif (isset($response_data['status']) && $response_data['status'] === 'inactive') {
				error_log('Error validating license: ' . print_r($response_data, true));
				update_option('cyberxdc_license_status', 'inactive');
			}
		} else {
			error_log('No license key found');
			update_option('cxdc_webmaster_pro_license_status', 'invalid');
			// Schedule deletion after 30 days if not already scheduled
			if (!wp_next_scheduled('cxdc_webmaster_pro_delete_plugin_events')) {
				wp_schedule_single_event(time() + 30 * DAY_IN_SECONDS, 'cxdc_webmaster_pro_delete_plugin_events');
				// Update or add option cxdc_webmaster_pro_license_validation_failed_date
				$failed_date = get_option('cxdc_webmaster_pro_license_validation_failed_date');
				if (!$failed_date) {
					add_option('cxdc_webmaster_pro_license_validation_failed_date', current_time('timestamp'));
				} else {
					update_option('cxdc_webmaster_pro_license_validation_failed_date', current_time('timestamp'));
				}
			}
		}
	}

	/**
	 * Delete the plugin directory.
	 *
	 * @since    1.0.0
	 */
	public function cxdc_webmaster_pro_delete_plugin_directory()
	{
		deactivate_plugins(plugin_basename(__FILE__));
		delete_option('cxdc_webmaster_pro_license_validation_failed_date');
		delete_option('cxdc_webmaster_pro_license_status');
		delete_option('cxdc_webmaster_pro_license_key');
		delete_option('cxdc_webmaster_pro_plugin_repo_name');
		delete_option('cxdc_webmaster_pro_plugin_repo_owner');
		delete_option('cxdc_webmaster_pro_plugin_repo_tagname');
		delete_option('cxdc_webmaster_pro_plugin_repo_version_file');
		$plugin_path = plugin_dir_path(__FILE__);
		if (file_exists($plugin_path)) {
			$deleted = $this->recursive_remove_directory($plugin_path);
			if ($deleted) {
				error_log('Plugin directory deleted successfully.');
			}
		}
	}

	/**
	 * Recursively remove a directory and its contents.
	 *
	 * @since    1.0.0
	 * @param    string    $dir    The directory to remove.
	 * @return   bool              True on success, false on failure.
	 */
	private function recursive_remove_directory($dir)
	{
		if (!is_dir($dir)) {
			return false;
		}
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? $this->recursive_remove_directory("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
}
