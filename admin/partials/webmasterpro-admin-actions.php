<?php 
class Webmasterpro_AdminActions
{
    public function __construct()
    {
        // Hook into CF7 submission process
        add_action('wpcf7_before_send_mail', array($this, 'cyberxdc_store_cf7_submission'), 10, 1);

        // Hook into WordPress actions to log activities
        add_action('wp_login', array($this, 'cyberxdc_log_login'), 10, 2);
        add_action('wp_logout', array($this, 'cyberxdc_log_logout'));
        add_action('save_post', array($this, 'cyberxdc_log_post_changes'), 10, 3);
        add_action('before_delete_post', array($this, 'cyberxdc_log_post_deletion'));
        add_action('user_register', array($this, 'cyberxdc_log_user_registration'));
        add_action('profile_update', array($this, 'cyberxdc_log_profile_update'), 10, 2);
        add_action('delete_user', array($this, 'cyberxdc_log_user_deletion'));
        add_action('init', array($this, 'track_visitor_logs'));
    }

    // Method to store CF7 data into the database
    public function cyberxdc_store_cf7_submission($cf7)
    {
        $submission = WPCF7_Submission::get_instance();
        if ($submission) {
            $posted_data = $submission->get_posted_data();
            // Insert the submitted data into your custom table
            global $wpdb;
            $table_name = $wpdb->prefix . 'cxdcwmpro_cf7_submissions';
            $wpdb->insert($table_name, array(
                'form_id' => $cf7->id(),
                'submission_data' => serialize($posted_data),
                'submission_time' => current_time('mysql'),
            ));

            // Log CF7 submission activity
            WebMasterPro_User_Logger::log_activity('admin', 'CF7 Form submitted');
        }
    }

    // Log login activity
    public function cyberxdc_log_login($user_login, $user)
    {
        WebMasterPro_User_Logger::log_activity($user_login, 'User logged in');
    }

    // Log logout activity
    public function cyberxdc_log_logout()
    {
        $user = wp_get_current_user();
        if ($user->exists()) {
            WebMasterPro_User_Logger::log_activity($user->user_login, 'User logged out');
        }
    }

    // Log post changes (add/edit)
    public function cyberxdc_log_post_changes($post_id, $post, $update)
    {
        $user = wp_get_current_user();
        if ($update) {
            $activity = 'Post updated: ' . $post->post_title;
        } else {
            $activity = 'Post added: ' . $post->post_title;
        }
        WebMasterPro_User_Logger::log_activity($user->user_login, $activity);
    }

    // Log post deletion
    public function cyberxdc_log_post_deletion($post_id)
    {
        $user = wp_get_current_user();
        $post = get_post($post_id);
        $activity = 'Post deleted: ' . $post->post_title;
        WebMasterPro_User_Logger::log_activity($user->user_login, $activity);
    }

    // Log user registration
    public function cyberxdc_log_user_registration($user_id)
    {
        $user = get_user_by('id', $user_id);
        WebMasterPro_User_Logger::log_activity($user->user_login, 'User registered');
    }

    // Log profile update
    public function cyberxdc_log_profile_update($user_id, $old_user_data)
    {
        $user = get_user_by('id', $user_id);
        WebMasterPro_User_Logger::log_activity($user->user_login, 'User profile updated');
    }

    // Log user deletion
    public function cyberxdc_log_user_deletion($user_id)
    {
        $user = get_user_by('id', $user_id);
        WebMasterPro_User_Logger::log_activity($user->user_login, 'User deleted');
    }

    // Track visitor logs// Method to track visitor logs
// Track visitor logs// Method to track visitor logs
public function track_visitor_logs()
{
    // Do not track admin users
    if (is_user_logged_in() && current_user_can('manage_options')) {
        return;
    }

    // Get visitor IPs
    $ip4 = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip6 = $_SERVER['HTTP_CLIENT_IP'] ?? '';

    // Get country (using a third-party service like ipinfo.io)
    $country = 'Unknown';
    if ($ip4 && filter_var($ip4, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $response = wp_remote_get("http://ipinfo.io/{$ip4}/country");
        if (is_array($response) && !is_wp_error($response)) {
            $country_data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($country_data['country'])) {
                $country = trim($country_data['country']);
                if (strlen($country) > 255) {
                    $country = substr($country, 0, 255); // Truncate if too long
                }
            } else {
                error_log('Error fetching country info: ' . print_r($response, true));
            }
        } else {
            error_log('Error fetching country info: ' . print_r($response, true));
        }
    }

    // Get browser and device
    $browser = $this->get_user_agent_browser();
    $device = $this->get_device_type();

    // Get current time
    $time = current_time('mysql');

    // Get current page URL
    $page_visited = $_SERVER['REQUEST_URI'];

    // Store the data
    global $wpdb;
    $table_name = $wpdb->prefix . 'cxdcwmpro_visitor_logs'; // Adjust table name as needed
    $result = $wpdb->insert($table_name, [
        'ip4' => $ip4,
        'ip6' => $ip6,
        'country' => $country,
        'browser' => $browser,
        'device' => $device,
        'time' => $time,
        'page_visited' => $page_visited,
    ]);

    // Check for errors
    if ($result === false) {
        error_log('Error inserting visitor log: ' . $wpdb->last_error);
    }
}

// Method to get user agent browser
private function get_user_agent_browser()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $browser = 'Unknown';

    if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
        $browser = 'Internet Explorer';
    } elseif (strpos($user_agent, 'Edge') !== false) {
        $browser = 'Microsoft Edge';
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        $browser = 'Mozilla Firefox';
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        $browser = 'Google Chrome';
    } elseif (strpos($user_agent, 'Safari') !== false) {
        $browser = 'Apple Safari';
    } elseif (strpos($user_agent, 'Opera') !== false || strpos($user_agent, 'OPR') !== false) {
        $browser = 'Opera';
    }

    return $browser;
}

// Method to get device type
private function get_device_type()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (preg_match('/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile/', $user_agent)) {
        return 'Mobile';
    } elseif (preg_match('/Tablet|iPad/', $user_agent)) {
        return 'Tablet';
    } else {
        return 'Desktop';
    }
}

}

// Initialize the class
new Webmasterpro_AdminActions();

class WebMasterPro_User_Logger
{
    public static function log_activity($user, $activity)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cxdcwmpro_users_logs';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $location = '';

        // Check if location is available in session
        if (isset($_SESSION['user_location'])) {
            $location = $_SESSION['user_location'];
        } else {
            // If location is not available in session, fetch it
            if (empty($ip_address) || $ip_address === '127.0.0.1' || $ip_address === '::1') {
                $ip_address = '49.206.201.42'; // Replace with your default IP address for testing or set a default location
            }
            $location = self::get_ip_location($ip_address);

            // Store location in session
            $_SESSION['user_location'] = $location;
        }

        // Insert activity into database
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => current_time('mysql'),
                'user' => $user,
                'activity' => $activity,
                'ip_address' => $ip_address,
                'location' => $location,
            )
        );
    }

    public static function get_ip_location($ip)
    {
        // Your API token from ipinfo.io
        $token = '400a1d917f8378';

        // Check if the IP address is valid
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'Unknown';
        }
        // URL for the ipinfo.io API
        $url = "http://ipinfo.io/{$ip}/json?token={$token}";

        // Send the request to the API
        $response = wp_remote_get($url);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return 'Unknown';
        }

        // Decode the JSON response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        // Return the location if available
        if (isset($data->city) && isset($data->region) && isset($data->country)) {
            return "{$data->city}, {$data->region}, {$data->country}";
        }

        return 'Unknown';
    }
}
