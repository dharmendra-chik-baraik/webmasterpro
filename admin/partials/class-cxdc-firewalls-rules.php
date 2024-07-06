<?php

class WebmasterproCXDC_Firewalls_Rules
{

    public function __construct()
    {
        //Capture the events that relate to the firewall's rules

    }

    /**
     * Adds all the firewall rule events to the audit log
     * @param $event
     * @param $data
     */
    public function webmasterpro_firewalls_page()
    {
        // Check if form is submitted
        if (isset($_POST['submit_firewalls'])) {
            // Check if firewall is enabled
            $firewall_enabled = isset($_POST['firewall_enabled']) ? 1 : 0;
            update_option('webmasterpo_firewall_enabled', $firewall_enabled);

            $notice = 'Firewall settings saved successfully.';
        }

        // Get current firewall status
        $firewall_enabled = get_option('webmasterpo_firewall_enabled', 0);
?>
        <div class="webmasterpro-wrap">
            <div class="container">
                <div class="card row">
                    <div class="col card">
                        <h2>Firewall Settings</h2>
                        <p>Enable or disable the firewall for added security.</p>
                        <hr>
                        <?php if (isset($notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-success is-dismissible ">
                                <p><strong>Success:</strong> <?php echo $notice; ?></p>
                            </div>
                        <?php endif; ?>
                        <br>
                        <br>
                        <form method="post" action="">
                            <label for="firewall_enabled">
                                <input type="checkbox" id="firewall_enabled" name="firewall_enabled" <?php checked($firewall_enabled, 1); ?>>
                                Enable Firewall
                            </label><br>
                            <br>
                            <br>
                            <?php if ($firewall_enabled) : ?>
                                <div style="margin: 0px;" class="custom-notice custom-notice-info">
                                    <p><strong>Warning:</strong> Enabling the firewall may affect certain functionalities on your site. Make sure to thoroughly test after enabling.</p>
                                </div>
                            <?php endif; ?>
                            <br>
                            <br>
                            <input type="submit" name="submit_firewalls" class="button-primary" value="Save Changes">
                            
                        </form>
                        <br>
                        <hr>
                        <div class="child_card">
                            <h3>Important Notes</h3>
                            <ul>
                                <li>Firewall protects your site from unauthorized access.</li>
                                <li>Enabling the firewall may block certain plugins or features.</li>
                                <li>Always test changes on a staging site before applying to production.</li>
                            </ul>
                        </div>
                        <div class="child_card">
                            <h3>Key Features</h3>
                            <ul>
                                <li>Block malicious requests to sensitive files.</li>
                                <li>Prevent unauthorized access attempts.</li>
                                <li>Log firewall events for security auditing.</li>
                            </ul>
                        </div>
                        <div class="child_card">
                            <h3>Rules List</h3>
                            <ul>
                                <li>Block access to sensitive WordPress files (e.g., wp-config.php).</li>
                                <li>Restrict access to directories with executable files (e.g., wp-content/plugins).</li>
                                <li>Prevent direct access to server configuration files (e.g., .htaccess).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col card">
                        <div class="chil_card">
                            <h3>WebMaster Pro Firewall Rules</h3>
                            <p>These rules help protect your site from various security threats. Enable the firewall to enforce these protections:</p>
                            <ul>
                                <?php if (is_firewall_enabled()) : ?>
                                    <li>
                                        <strong>Rule 1:</strong> Block access to <code>xmlrpc.php</code><br>
                                        <em>Description:</em> XML-RPC is a remote procedure call (RPC) protocol which can be exploited by attackers to perform various actions on your site. Blocking access mitigates this risk.
                                    </li>
                                    <li>
                                        <strong>Rule 2:</strong> Block access to <code>wp-config.php</code><br>
                                        <em>Description:</em> Access to wp-config.php can reveal sensitive database credentials and configurations. Blocking access prevents unauthorized viewing.
                                    </li>
                                    <li>
                                        <strong>Rule 3:</strong> Block access to <code>readme.html</code><br>
                                        <em>Description:</em> The readme file may contain version-specific information that could aid attackers in exploiting known vulnerabilities. Blocking access reduces exposure.
                                    </li>
                                    <li>
                                        <strong>Rule 4:</strong> Block access to <code>license.txt</code><br>
                                        <em>Description:</em> License files often include information about software versions and licenses. Blocking access helps protect this information from unauthorized access.
                                    </li>
                                    <li>
                                        <strong>Rule 5:</strong> Block access to sensitive directories:
                                        <ul>
                                            <li><code>wp-content/uploads</code> - Protects uploaded files from direct access.</li>
                                            <li><code>wp-content/themes</code> - Prevents theme files from being accessed directly.</li>
                                            <li><code>wp-content/plugins</code> - Secures plugin files from direct access.</li>
                                        </ul>
                                        <em>Description:</em> Restricting access to these directories enhances overall site security by preventing direct access to potentially sensitive files.
                                    </li>
                                    <li>
                                        <strong>Rule 6:</strong> Block access to <code>wp-includes</code> directory<br>
                                        <em>Description:</em> The wp-includes directory contains core WordPress files. Blocking access protects these essential files from unauthorized viewing or modification.
                                    </li>
                                    <li>
                                        <strong>Rule 7:</strong> Block access to <code>.php</code> files in <code>uploads</code> directory<br>
                                        <em>Description:</em> Prevents execution of PHP scripts that may have been uploaded to the uploads directory, which could be used for malicious purposes.
                                    </li>
                                    <li>
                                        <strong>Rule 8:</strong> Block access to <code>wp-content/cache</code> directory<br>
                                        <em>Description:</em> The cache directory may contain temporary files that could expose sensitive information if accessed directly. Blocking access helps mitigate this risk.
                                    </li>
                                    <li>
                                        <strong>Rule 9:</strong> Block access to <code>wp-content/upgrade</code> directory<br>
                                        <em>Description:</em> Protects upgrade files from direct access, which may contain installation scripts or temporary files that could be exploited if accessed.
                                    </li>
                                    <li>
                                        <strong>Rule 10:</strong> Block access to <code>wp-content/plugins</code> directory for non-admin users<br>
                                        <em>Description:</em> Restricts non-administrative users from accessing plugin files directly, reducing the risk of unauthorized modifications or exploits.
                                    </li>
                                    <li>
                                        <strong>Rule 11:</strong> Block access to <code>wp-content/themes</code> directory for non-admin users<br>
                                        <em>Description:</em> Similar to plugins, restricts non-admin users from accessing theme files directly, enhancing overall site security.
                                    </li>
                                    <li>
                                        <strong>Rule 12:</strong> Block access to <code>wp-content/uploads</code> directory for non-admin users<br>
                                        <em>Description:</em> Prevents non-admin users from accessing uploaded files directly, safeguarding user-uploaded content from unauthorized access or tampering.
                                    </li>
                                <?php else : ?>
                                    <li>Firewall is currently disabled. Enable it to apply these rules.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
    <?php

    }
}

function is_firewall_enabled()
{
    return get_option('webmasterpo_firewall_enabled', 0) == 1;
}

add_action('init', 'apply_firewall_rules');

function apply_firewall_rules()
{
    if (is_firewall_enabled()) {
        // Rule 1: Block access to xmlrpc.php
        if (strpos($_SERVER['REQUEST_URI'], 'xmlrpc.php') !== false) {
            wp_die('Access to xmlrpc.php is blocked.');
        }

        // Rule 2: Block access to wp-config.php
        if (strpos($_SERVER['REQUEST_URI'], 'wp-config.php') !== false) {
            wp_die('Access to wp-config.php is blocked.');
        }

        // Rule 3: Block access to readme.html
        if (strpos($_SERVER['REQUEST_URI'], 'readme.html') !== false) {
            wp_die('Access to readme.html is blocked.');
        }

        // Rule 4: Block access to license.txt
        if (strpos($_SERVER['REQUEST_URI'], 'license.txt') !== false) {
            wp_die('Access to license.txt is blocked.');
        }

        // Rule 5: Block access to sensitive directories
        $blocked_directories = array('wp-content/uploads', 'wp-content/themes', 'wp-content/plugins');
        foreach ($blocked_directories as $directory) {
            if (strpos($_SERVER['REQUEST_URI'], $directory) !== false) {
                wp_die("Access to $directory is blocked.");
            }
        }
        // Rule 8: Block access to wp-includes directory
        if (strpos($_SERVER['REQUEST_URI'], 'wp-includes') !== false) {
            wp_die('Access to wp-includes directory is blocked.');
        }

        // Rule 9: Block access to .php files in uploads directory
        if (preg_match('/\/uploads\/.*\.php$/i', $_SERVER['REQUEST_URI'])) {
            wp_die('Access to .php files in uploads directory is blocked.');
        }

        // Rule 10: Block access to wp-content/cache directory
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/cache') !== false) {
            wp_die('Access to wp-content/cache directory is blocked.');
        }

        // Rule 11: Block access to wp-content/upgrade directory
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/upgrade') !== false) {
            wp_die('Access to wp-content/upgrade directory is blocked.');
        }

        // Rule 12: Block access to wp-content/plugins directory for non-admin users
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/plugins') !== false && !current_user_can('activate_plugins')) {
            wp_die('Access to wp-content/plugins directory is blocked for non-admin users.');
        }

        // Rule 13: Block access to wp-content/themes directory for non-admin users
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/themes') !== false && !current_user_can('activate_plugins')) {
            wp_die('Access to wp-content/themes directory is blocked for non-admin users.');
        }

        // Rule 14: Block access to wp-content/uploads directory for non-admin users
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/uploads') !== false && !current_user_can('activate_plugins')) {
            wp_die('Access to wp-content/uploads directory is blocked for non-admin users.');
        }

        // Rule 15: Block access to wp-content/uploads/*.php files
        $uploaded_php_files = glob(WP_CONTENT_DIR . '/uploads/*.php');
        foreach ($uploaded_php_files as $uploaded_php_file) {
            $filename = basename($uploaded_php_file);
            if (strpos($_SERVER['REQUEST_URI'], $filename) !== false) {
                wp_die("Access to $filename is blocked.");
            }
        }

        //  Rule 16: Block access to wp-content/uploads/.htaccess file
        if (strpos($_SERVER['REQUEST_URI'], 'wp-content/uploads/.htaccess') !== false) {
            wp_die('Access to wp-content/uploads/.htaccess file is blocked.');
        }


        // Rule 17: Block access to wp-includes/*.php files
        $includes_php_files = glob(WP_CONTENT_DIR . '/wp-includes/*.php');
        foreach ($includes_php_files as $includes_php_file) {
            $filename = basename($includes_php_file);
            if (strpos($_SERVER['REQUEST_URI'], $filename) !== false) {
                wp_die("Access to $filename is blocked.");
            }
        }

        // Rule 18: Block access to wp-includes/js/*.php files
        $includes_js_php_files = glob(WP_CONTENT_DIR . '/wp-includes/js/*.php');
        foreach ($includes_js_php_files as $includes_js_php_file) {
            $filename = basename($includes_js_php_file);
            if (strpos($_SERVER['REQUEST_URI'], $filename) !== false) {
                wp_die("Access to $filename is blocked.");
            }
        }
    }
}
