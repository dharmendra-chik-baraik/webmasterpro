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

    $oldPrefix = $wpdb->prefix;

    // Validate new prefix format
    if (!preg_match('/^[A-Za-z0-9_]+$/', $newPrefix)) {
        return new WP_Error('invalid_prefix', __('Invalid prefix format. Only alphanumeric characters and underscores are allowed.'));
    }

    // Log the old and new prefixes
    error_log("Changing database prefix from '{$oldPrefix}' to '{$newPrefix}'.");

    // Get tables with the old prefix
    $tables = $wpdb->get_results("SHOW TABLES LIKE '{$oldPrefix}%'");
    if ($wpdb->last_error) {
        return new WP_Error('database_error', __('Error retrieving table list from the database: ') . $wpdb->last_error);
    }

    // Log the tables for debugging purposes
    error_log("Tables to rename: " . print_r($tables, true));

    // Iterate over each table and rename if a table with the new name doesn't exist
    foreach ($tables as $table) {
        // Extract the table name from the stdClass object
        $table_array = (array) $table; // Cast the object to an array
        $oldTableName = reset($table_array); // Get the first element of the array
    
        // Generate the new table name with the updated prefix
        $newTableName = preg_replace('/^' . preg_quote($oldPrefix, '/') . '/', $newPrefix, $oldTableName);
    
        // Rename the table, regardless of whether the new table already exists
        $rename_query = "RENAME TABLE `{$oldTableName}` TO `{$newTableName}`";
        $wpdb->query($rename_query);
        if ($wpdb->last_error) {
            error_log("WordPress database error {$wpdb->last_error} for query RENAME TABLE {$oldTableName} TO {$newTableName}");
            return new WP_Error('database_error', __('Error renaming table ') . $oldTableName . __(': ') . $wpdb->last_error);
        }
    
        // Verify the new table exists
        $new_table_check = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $newTableName));
        if (!$new_table_check) {
            error_log("Failed to find the new table {$newTableName} after renaming.");
            return new WP_Error('database_error', __('Failed to verify existence of the new table after renaming: ') . $newTableName);
        } else {
            // Log success for renaming
            error_log("Successfully renamed {$oldTableName} to {$newTableName}.");
        }
    }
    
    

    // Verify the existence of critical tables before proceeding with updates
    $newOptionsTable = "{$newPrefix}options";
    $newUsermetaTable = "{$newPrefix}usermeta";

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $newOptionsTable)) != $newOptionsTable) {
        return new WP_Error('database_error', __('New options table does not exist: ') . $newOptionsTable);
    }

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $newUsermetaTable)) != $newUsermetaTable) {
        return new WP_Error('database_error', __('New usermeta table does not exist: ') . $newUsermetaTable);
    }

    // Update options table prefix in the new options table
    $options_query = $wpdb->prepare("UPDATE `{$newOptionsTable}` SET option_name = %s WHERE option_name = %s", $newPrefix . 'user_roles', $oldPrefix . 'user_roles');
    $wpdb->query($options_query);
    if ($wpdb->last_error) {
        error_log("WordPress database error {$wpdb->last_error} for query {$options_query}");
        return new WP_Error('database_error', __('Error updating options table: ') . $wpdb->last_error);
    }

    // Update usermeta table prefix in the new usermeta table
    $usermeta_query = $wpdb->prepare("UPDATE `{$newUsermetaTable}` SET meta_key = REPLACE(meta_key, %s, %s) WHERE meta_key LIKE %s", $oldPrefix, $newPrefix, $oldPrefix . '%');
    $wpdb->query($usermeta_query);
    if ($wpdb->last_error) {
        error_log("WordPress database error {$wpdb->last_error} for query {$usermeta_query}");
        return new WP_Error('database_error', __('Error updating usermeta table: ') . $wpdb->last_error);
    }

    // Update wp-config.php with the new prefix
    $config_path = ABSPATH . 'wp-config.php';

    if (file_exists($config_path) && is_writable($config_path)) {
        $config_content = file_get_contents($config_path);
        $config_content = preg_replace("/\\\$table_prefix\\s*=\\s*[\"'].*?[\"'];/i", "\$table_prefix = '{$newPrefix}';", $config_content);
        if (file_put_contents($config_path, $config_content) === false) {
            return new WP_Error('config_file_error', __('Failed to write to wp-config.php file.'));
        }
    } else {
        return new WP_Error('config_file_error', __('wp-config.php file not found or not writable.'));
    }

    // Update the global $table_prefix variable
    $wpdb->set_prefix($newPrefix);

    return __('Database prefix changed successfully.', 'text-domain');
}



}
