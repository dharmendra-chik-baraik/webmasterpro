<?php
class Webmasterpro_Database_Security
{

    public function webmasterpro_database_security_page()
    {
        global $wpdb;

        $current_prefix = $wpdb->prefix;
        $is_default_prefix = $current_prefix === 'wp_';
        $success_notice = '';
        $error_notice = '';

        // Check if the form has been submitted
        if (isset($_POST['webmasterpro_database_security_submit'])) {
            // Verify nonce for security
            if (!isset($_POST['webmasterpro_database_security_nonce']) || !wp_verify_nonce($_POST['webmasterpro_database_security_nonce'], 'webmasterpro_database_security_nonce')) {
                wp_die('Nonce verification failed');
            }

            // Sanitize and update settings
            if (isset($_POST['new_database_prefix'])) {
                $new_prefix = sanitize_text_field($_POST['new_database_prefix']);
                if (strlen($new_prefix) > 0) {
                    if (substr($new_prefix, -1) !== '_') {
                        $new_prefix .= '_';
                    }
                    $result = $this->change_database_prefix($new_prefix);
                    if (is_wp_error($result)) {
                        $error_notice = $result->get_error_message();
                    } else {
                        $success_notice = 'Database prefix changed successfully.';
                    }
                } else {
                    $error_notice = 'Please enter a new database prefix.';
                }
            }
        }

?>
        <div class="webmasterpro-wrap">
            <div class="container">
                <div class="row">
                    <div class="card col w-100 mx-100">
                        <h2>Database Security Settings</h2>
                        <?php if ($is_default_prefix) : ?>
                            <div style="margin: 0px;" class="custom-notice custom-notice-info">
                                <p>Your current database prefix is the default <strong><?php echo esc_html($current_prefix); ?></strong>. Using the default prefix makes your WordPress database more susceptible to targeted attacks. It is highly recommended to change your database prefix to something unique and difficult to guess. This will significantly enhance the security of your WordPress site.</p>
                            </div>
                        <?php else : ?>
                            <div style="margin: 0px;" class="custom-notice custom-notice-info">
                                <p>Your current database prefix is <strong><?php echo esc_html($current_prefix); ?></strong>. Changing it to a custom prefix enhances security by preventing easy prediction of your database structure, reducing the risk of SQL injection and other vulnerabilities. Choose an alphanumeric prefix ending with an underscore.</p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($success_notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-success is-dismissible">
                                <p><?php echo $success_notice; ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($error_notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-error is-dismissible">
                                <p><?php echo $error_notice; ?></p>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Current Database Prefix</th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($current_prefix); ?>" readonly="readonly" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Change Database Prefix</th>
                                    <td>
                                        <input type="text" name="new_database_prefix" id="new_database_prefix" value="" />
                                        <p class="description">Enter a new database prefix or generate a random one.</p>
                                    </td>
                                </tr>
                            </table>
                            <hr>
                            <?php wp_nonce_field('webmasterpro_database_security_nonce', 'webmasterpro_database_security_nonce'); ?>
                            <input type="submit" name="webmasterpro_database_security_submit" class="button-primary" value="Save Changes">
                            <button type="button" class="button-secondary" onclick="generateRandomPrefix()">Generate Random Prefix</button>
                        </form>
                        <hr>
                        <div class="child_card">
                            <h2>Database Details</h2>
                            <div class="custom-notice custom-notice-info">
                                <p><strong>Database Name:</strong> <?php echo esc_html($wpdb->dbname); ?></p>
                                <p><strong>Database Type:</strong> MySQL</p>
                                <p><strong>Database Collation:</strong> <?php echo esc_html($wpdb->collate); ?></p>
                                <p><strong>Database Size:</strong> <?php echo esc_html($this->get_database_size()); ?> MB</p>
                            </div>
                        </div>

                    </div>
                    <div class="card col w-100 mx-100">
                        <h2>Database Security Tips</h2>
                        <div class="custom-notice custom-notice-info">
                            <p><strong>Why Change Your Database Prefix?</strong></p>
                            <p>Your current database prefix is the default <strong><?php echo esc_html($current_prefix); ?></strong>. Changing it enhances security by making it harder for attackers to exploit known vulnerabilities.</p>
                        </div>
                        <div class="custom-notice custom-notice-warning">
                            <p><strong>Important Warning:</strong></p>
                            <p>Changing the database prefix requires careful execution as it renames all tables and updates configurations. Please ensure to back up your database before proceeding.</p>
                        </div>
                        <div class="custom-notice custom-notice-info">
                            <p><strong>Generating a Random Prefix:</strong></p>
                            <p>Generate a random prefix to increase security complexity. Click "Generate Random Prefix" to create a unique prefix for your database structure.</p>
                        </div>
                        <div class="custom-notice custom-notice-info">
                            <p><strong>Regular Backups:</strong></p>
                            <p>Always maintain recent backups of your database. This precaution ensures you can restore data in case of accidental changes or security incidents.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function generateRandomPrefix() {
                const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let randomPrefix = '';
                for (let i = 0; i < 6; i++) {
                    randomPrefix += characters.charAt(Math.floor(Math.random() * characters.length));
                }
                document.getElementById('new_database_prefix').value = randomPrefix + '_';
            }
        </script>
    <?php
    }

    /**
     * Rename WordPress database tables and update configuration with a new prefix.
     *
     * @param string $newPrefix The new prefix to be assigned to the tables.
     * @return mixed WP_Error on failure, or a success message on completion.
     */
    private function change_database_prefix($newPrefix)
    {
        global $wpdb;

        $oldPrefix = strtolower($wpdb->prefix); // Convert to lowercase for case-insensitive comparison
        $newPrefix = strtolower($newPrefix); // Convert to lowercase for consistency

        error_log("Old prefix: " . $oldPrefix . " New prefix: " . $newPrefix);

        // Update table names
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$oldPrefix}%'");
        if ($wpdb->last_error) {
            return new WP_Error('database_error', __('Error retrieving table list from the database: ') . $wpdb->last_error);
        }

        foreach ($tables as $table) {
            $oldTableName = current($table);
            error_log("Old table name: " . $oldTableName);

            // Convert table name to lowercase for consistent comparison
            $currentTableName = strtolower($oldTableName);

            // Check if the table name contains the old prefix anywhere (case-insensitive)
            if (strpos($currentTableName, $oldPrefix) === 0) {
                // Replace old prefix with new prefix in table name
                $newTableName = $newPrefix . substr($oldTableName, strlen($oldPrefix));
                error_log("New table name: " . $newTableName);

                // Rename the table with the new name
                $wpdb->query("RENAME TABLE {$oldTableName} TO {$newTableName}");
                if ($wpdb->last_error) {
                    return new WP_Error('database_error', __('Error renaming table ') . $oldTableName . __(': ') . $wpdb->last_error);
                }
            } else {
                error_log("Table name '{$oldTableName}' does not start with '{$oldPrefix}', skipping.");
            }
        }

        // Update options table
        $options_query = $wpdb->prepare("UPDATE {$newPrefix}options SET option_name = %s WHERE option_name = %s", $newPrefix . 'user_roles', $oldPrefix . 'user_roles');
        $wpdb->query($options_query);
        if ($wpdb->last_error) {
            return new WP_Error('database_error', __('Error updating options table: ') . $wpdb->last_error);
        }

        // Update usermeta table
        $usermeta_query = $wpdb->prepare("UPDATE {$newPrefix}usermeta SET meta_key = REPLACE(meta_key, %s, %s) WHERE meta_key LIKE %s", $oldPrefix, $newPrefix, $oldPrefix . '%');
        $wpdb->query($usermeta_query);
        if ($wpdb->last_error) {
            return new WP_Error('database_error', __('Error updating usermeta table: ') . $wpdb->last_error);
        }

        // Update wp-config.php
        // Update prefix in wp-config.php
        $config_path = ABSPATH . 'wp-config.php';

        if (file_exists($config_path)) {
            $config_content = file_get_contents($config_path);
            $config_content = preg_replace("/\\\$table_prefix\\s*=\\s*[\"'].*?[\"'];/i", "\$table_prefix = '{$newPrefix}';", $config_content);
            file_put_contents($config_path, $config_content);
        } else {
            return new WP_Error('config_file_error', __('wp-config.php file not found.'));
        }

        // Update the global $table_prefix variable
        $wpdb->set_prefix($newPrefix);

        return __('Database prefix changed successfully.', 'text-domain');
    }
    private function get_database_size()
    {
        global $wpdb;

        // Query to calculate the total size of all tables in the database
        $query = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_in_mb
                  FROM information_schema.tables
                  WHERE table_schema = '{$wpdb->dbname}'";

        // Fetch the result
        $result = $wpdb->get_row($query);

        if ($result) {
            return round(floatval($result->size_in_mb), 2); // Round to two decimal places
        } else {
            return 0.00; // Return 0 if query fails or no tables found
        }
    }
}

class WebMasterPro_File_Security
{
    // Constructor to initialize actions and hooks
    public function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'display_admin_notice'));
    }
    
    // Register settings
    public function register_settings()
    {
        register_setting('webmasterpro_file_security_options', 'webmasterpro_file_security_options', array($this, 'sanitize_options'));
    }

    // Sanitize options
    public function sanitize_options($input)
    {
        $sanitized_input = array();

        // Sanitize each option if set
        if (isset($input['enable_iframe_protection'])) {
            $sanitized_input['enable_iframe_protection'] = sanitize_text_field($input['enable_iframe_protection']);
        }
        if (isset($input['disable_php_editing'])) {
            $sanitized_input['disable_php_editing'] = sanitize_text_field($input['disable_php_editing']);
        }
        if (isset($input['disable_theme_editing'])) {
            $sanitized_input['disable_theme_editing'] = sanitize_text_field($input['disable_theme_editing']);
        }
        if (isset($input['disable_plugins_editing'])) {
            $sanitized_input['disable_plugins_editing'] = sanitize_text_field($input['disable_plugins_editing']);
        }
        if (isset($input['disable_right_click'])) {
            $sanitized_input['disable_right_click'] = sanitize_text_field($input['disable_right_click']);
        }
        if (isset($input['rename_error_log'])) {
            $sanitized_input['rename_error_log'] = sanitize_text_field($input['rename_error_log']);
        }
        if (isset($input['disable_executable_uploads'])) {
            $sanitized_input['disable_executable_uploads'] = sanitize_text_field($input['disable_executable_uploads']);
        }

        // Add a notice upon successful save
        if (get_option('webmasterpro_file_security_options') !== false) {
            add_settings_error('webmasterpro_file_security_options', 'settings_updated', 'Settings saved.', 'updated');
        }

        return $sanitized_input;
    }

    // Display admin notice
    public function display_admin_notice()
    {
        settings_errors('webmasterpro_file_security_options');
    }

    // Render the Files Security page
    public function webmasterpro_files_security_page()
    {
        ?>
        <div class="webmasterpro-files-security">
            <div class="container">
                <div class="row card justify-content-center">
                    <div class="col card mw-100 w-100">
                        <h1 class="text-center mb-4">Files Security Scan</h1>
                        <form method="post" action="options.php">
                            <?php settings_fields('webmasterpro_file_security_options'); ?>
                            <?php $options = get_option('webmasterpro_file_security_options'); ?>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[enable_iframe_protection]" value="1" <?php checked(isset($options['enable_iframe_protection']) && $options['enable_iframe_protection'], 1); ?>>
                                    Enable iFrame Protection
                                </label>
                                <p class="description">Prevents other sites from embedding your content in frames or iframes, enhancing security against content theft.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[disable_php_editing]" value="1" <?php checked(isset($options['disable_php_editing']) && $options['disable_php_editing'], 1); ?>>
                                    Disable PHP Files Editing
                                </label>
                                <p class="description">Enhances security by preventing direct modification of PHP files, reducing the risk of unauthorized code execution.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[disable_theme_editing]" value="1" <?php checked(isset($options['disable_theme_editing']) && $options['disable_theme_editing'], 1); ?>>
                                    Disable Theme Files Editing
                                </label>
                                <p class="description">Protects your theme files from unauthorized changes, ensuring stability and security of your site's design.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[disable_plugins_editing]" value="1" <?php checked(isset($options['disable_plugins_editing']) && $options['disable_plugins_editing'], 1); ?>>
                                    Disable Plugins Files Editing
                                </label>
                                <p class="description">Secures your plugins' files against unauthorized access or modification, reducing vulnerabilities and ensuring plugin integrity.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[disable_right_click]" value="1" <?php checked(isset($options['disable_right_click']) && $options['disable_right_click'], 1); ?>>
                                    Disable Right Click (Copy Protection)
                                </label>
                                <p class="description">Prevents users from copying content by disabling the right-click context menu, offering basic protection against content theft.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[rename_error_log]" value="1" <?php checked(isset($options['rename_error_log']) && $options['rename_error_log'], 1); ?>>
                                    Rename Error Log File (error_log)
                                </label>
                                <p class="description">Obscures the location and name of your error log file, making it harder for attackers to locate and exploit.</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="webmasterpro_file_security_options[disable_executable_uploads]" value="1" <?php checked(isset($options['disable_executable_uploads']) && $options['disable_executable_uploads'], 1); ?>>
                                    Disable Upload of Executable Files
                                </label>
                                <p class="description">Prevents users from uploading executable files (like PHP scripts), reducing the risk of malware injections and unauthorized code execution.</p>
                            </div>

                            <p class="submit">
                                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                            </p>
                        </form>
                    </div>

                    <div class="col card w-100 mw-100">
                        <div class="child_card">
                            <h3>Important Notes</h3>
                            <ul>
                                <li><strong>iFrame Protection:</strong> Enable this to prevent other sites from embedding your content in frames or iframes, enhancing security against content theft.</li>
                                <li><strong>Disable PHP Files Editing:</strong> Restrict PHP file modifications to enhance stability and security.</li>
                                <li><strong>Disable Theme Files Editing:</strong> Prevent unauthorized changes to theme files, ensuring consistent appearance and security.</li>
                                <li><strong>Disable Plugins Files Editing:</strong> Enhance security by restricting modifications to plugin files.</li>
                                <li><strong>Disable Right Click (Copy Protection):</strong> Prevent users from copying content by disabling right-click functionality.</li>
                                <li><strong>Rename Error Log File:</strong> Enhance security by renaming the error log file (error_log) to obscure potential attack vectors.</li>
                                <li><strong>Disable Upload of Executable Files:</strong> Prevent uploading executable files that could pose security risks.</li>
                            </ul>

                            <h3>Additional Information</h3>
                            <p>For more information on securing your WordPress files and best practices:</p>
                            <ul>
                                <li><a href="https://wordpress.org/support/article/hardening-wordpress/">WordPress Hardening Guide</a></li>
                                <li><a href="https://www.wpbeginner.com/wordpress-security/">WordPress Security Tips</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}



// Instantiate the class
if (class_exists('WebMasterPro_File_Security')) {
    $webmasterpro_file_security = new WebMasterPro_File_Security();
}
