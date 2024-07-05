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
                <div style="width: 100%; max-width: 100%; " class="card">
                    <h2>Database Security Settings</h2>
                    <?php if ($is_default_prefix) : ?>
                        <div style="margin: 0px;" class="notice notice-warning">
                            <p>The current database prefix is the default prefix <strong><?php echo esc_html($current_prefix); ?></strong>. It is recommended to change it for improved security.</p>
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
                        <?php wp_nonce_field('webmasterpro_database_security_nonce', 'webmasterpro_database_security_nonce'); ?>
                        <input type="submit" name="webmasterpro_database_security_submit" class="button-primary" value="Save Changes">
                        <button type="button" class="button-secondary" onclick="generateRandomPrefix()">Generate Random Prefix</button>
                    </form>
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
}
