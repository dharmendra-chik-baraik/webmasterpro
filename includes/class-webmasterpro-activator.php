<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cyberxdc.online
 * @since      1.0.0
 *
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 * @author     CyberXDC <contact@cyberxdc.online>
 */
class Webmasterpro_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		global $wpdb;

		// Create table for activity logs
		$table_name_logs = $wpdb->prefix . 'cxdcwmpro_activity_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql_logs = "CREATE TABLE $table_name_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user varchar(50) NOT NULL,
            activity text NOT NULL,
            ip_address varchar(100) NOT NULL,
            location varchar(100) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		// Create table for Contact Form 7 submissions
		$table_name_submissions = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';

		$sql_submissions = "CREATE TABLE $table_name_submissions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            submission_data text NOT NULL,
            submission_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		// Create table for visitor logs
		$table_name_visitors = $wpdb->prefix . 'cxdcwmpro_visitor_logs';

		$sql_visitors = "CREATE TABLE $table_name_visitors (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip4 varchar(45) NOT NULL,
            ip6 varchar(45),
            country varchar(100),
            browser text NOT NULL,
            device varchar(100) NOT NULL,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            page_visited text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		// Create table for support requests
		$table_name_support = $wpdb->prefix . 'cxdcwmpro_support';

		$sql_support = "CREATE TABLE $table_name_support (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email text NOT NULL,
            subject text NOT NULL,
            message text NOT NULL,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		$table_name = $wpdb->prefix . 'cxdcwmpro_email_logs';

		$sql_email_logs = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			sent_from varchar(100) NOT NULL,
			sent_to varchar(100) NOT NULL,
			subject varchar(255) NOT NULL,
			message text NOT NULL,
			sent_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'sent',
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Include necessary WordPress functions
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Create the tables
		dbDelta($sql_logs);
		dbDelta($sql_submissions);
		dbDelta($sql_visitors);
		dbDelta($sql_support);
		dbDelta($sql_email_logs);

		// Initialize plugin options if not already set
		self::initialize_options();
	}

	/**
	 * Initialize plugin options on activation
	 */
	private static function initialize_options()
	{
		// Add options for license key and status
		if (get_option('cxdc_webmaster_pro_license_key') === false) {
			add_option('cxdc_webmaster_pro_license_key', '');
		}
		if (get_option('cxdc_webmaster_pro_license_status') === false) {
			add_option('cxdc_webmaster_pro_license_status', 'inactive');
		}

		// Fetch and store plugin repository information
		self::fetch_and_store_repository_info();
	}

	/**
	 * Fetch and store plugin repository information
	 */
	private static function fetch_and_store_repository_info()
	{
		$plugin_download_url = WEBMASTERPRO_PLUGIN_URL . '/plugin-download-url';
		$response = wp_remote_get($plugin_download_url);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			error_log("Error fetching plugin repository data: $error_message");
			return;
		}

		$cxdc_webmaster_pro_repo_data = json_decode($response['body'], true);

		if (empty($cxdc_webmaster_pro_repo_data) || !isset($cxdc_webmaster_pro_repo_data['repo_name'], $cxdc_webmaster_pro_repo_data['repo_owner'], $cxdc_webmaster_pro_repo_data['tag'], $cxdc_webmaster_pro_repo_data['version_file'])) {
			error_log("Invalid plugin repository data received.");
			return;
		}

		// Store repository information in options
		if (get_option('cxdc_webmaster_pro_plugin_repo_name') === false) {
			add_option('cxdc_webmaster_pro_plugin_repo_name', $cxdc_webmaster_pro_repo_data['repo_name']);
		} else {
			update_option('cxdc_webmaster_pro_plugin_repo_name', $cxdc_webmaster_pro_repo_data['repo_name']);
		}
		if (get_option('cxdc_webmaster_pro_plugin_repo_owner') === false) {
			add_option('cxdc_webmaster_pro_plugin_repo_owner', $cxdc_webmaster_pro_repo_data['repo_owner']);
		} else {
			update_option('cxdc_webmaster_pro_plugin_repo_owner', $cxdc_webmaster_pro_repo_data['repo_owner']);
		}
		if (get_option('cxdc_webmaster_pro_plugin_repo_tagname') === false) {
			add_option('cxdc_webmaster_pro_plugin_repo_tagname', $cxdc_webmaster_pro_repo_data['tag']);
		} else {
			update_option('cxdc_webmaster_pro_plugin_repo_tagname', $cxdc_webmaster_pro_repo_data['tag']);
		}
		if (get_option('cxdc_webmaster_pro_plugin_repo_version_file') === false) {
			add_option('cxdc_webmaster_pro_plugin_repo_version_file', $cxdc_webmaster_pro_repo_data['version_file']);
		} else {
			update_option('cxdc_webmaster_pro_plugin_repo_version_file', $cxdc_webmaster_pro_repo_data['version_file']);
		}
	}
}
