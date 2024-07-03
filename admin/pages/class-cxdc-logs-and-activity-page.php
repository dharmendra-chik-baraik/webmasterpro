<?php

class Webmasterpro_Logs_and_Activity
{
    public function cxdc_webmaster_pro_logs_and_activity_page()
    {
        if (isset($_POST['action']) && $_POST['action'] === 'delete_users' && isset($_POST['log_ids'])) {
            $log_ids = $_POST['log_ids'];
            $this->delete_user_logs($log_ids);
            $notice = '<div class="notice notice-success is-dismissible"><p>User logs deleted successfully.</p></div>';
        } 

        if (isset($_POST['action']) && $_POST['action'] === 'delete_visitors' && isset($_POST['log_ids'])) {
            $log_ids = $_POST['log_ids'];
            $this->delete_visitor_logs($log_ids);
            $notice = '<div class="notice notice-success is-dismissible"><p>Visitor logs deleted successfully.</p></div>';
        }
        
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'user-logs';
?>
        <div class="wrap">
            <div class="container">
                <div class="row">
                    <div class="col card m-100 mw-100">
                        <h1>Logs & Activity</h1>
                        <p>Stay informed and proactive with WebMasterPro's comprehensive Logs and Activities feature. Gain detailed insights into user interactions and visitor activities across your WordPress site. Monitor login attempts, content changes, and visitor behavior effortlessly from a centralized dashboard. Our robust tracking system enables you to detect anomalies, enhance security, and optimize site performance. Empower your site management with actionable data and detailed activity logs to ensure a seamless and secure user experience.</p>
                        <?php echo $notice ?? ''; ?>
                        <div class="tabs-contaienr">
                            <h2 class="nav-tab-wrapper">
                                <a href="?page=webmasterpro-logs&tab=user-logs" class="nav-tab <?php echo $current_tab === 'user-logs' ? 'nav-tab-active' : ''; ?>">Users logs</a>
                                <a href="?page=webmasterpro-logs&tab=visitor-logs" class="nav-tab <?php echo $current_tab === 'visitor-logs' ? 'nav-tab-active' : ''; ?>">Visitors logs</a>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    switch ($current_tab) {
                        case 'user-logs':
                            $this->cxdc_webmaster_pro_user_logs();
                            break;
                        case 'visitor-logs':
                            $this->cxdc_webmaster_pro_visitor_logs();
                            break;
                        default:
                            $this->cxdc_webmaster_pro_user_logs();
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    public function cxdc_webmaster_pro_user_logs()
    {
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = 10; // Number of logs per page

        // Fetch user logs from database
        $logs = $this->get_user_logs($current_page, $per_page);
    ?>
        <div class="row">
            <div class="col card m-100 mw-100">
                <h2>User logs</h2>
                <?php if ($logs) : ?>
                    <form method="post" action="">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Timestamp</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log) : ?>
                                    <tr>
                                        <th scope="row" class="check-column">
                                            <input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log->id); ?>">
                                        </th>
                                        <td><?php echo esc_html($log->id); ?></td>
                                        <td><?php echo esc_html($log->user); ?></td>
                                        <td><?php echo esc_html($log->activity); ?></td>
                                        <td><?php echo esc_html($log->timestamp); ?></td>
                                        <td><?php echo esc_html($log->ip_address); ?></td>
                                        <td><?php echo esc_html($log->location); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="col" class="manage-column column-cb check-column">
                                        <input type="checkbox" id="select-all-bottom">
                                    </th>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Timestamp</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="tablenav bottom">
                            <div class="alignleft actions">
                                <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                                <select name="action" id="bulk-action-selector-bottom">
                                    <option value="-1">Bulk Actions</option>
                                    <option value="delete_users">Delete</option>
                                </select>
                                <input type="submit" name="doaction" id="doaction" class="button action" value="Apply">
                            </div>
                            <?php
                            $total_logs = $this->count_user_logs();
                            $total_pages = ceil($total_logs / $per_page);

                            // Pagination links
                            $page_links = paginate_links(array(
                                'base'      => add_query_arg('paged', '%#%'),
                                'format'    => '',
                                'prev_text' => __('&laquo; Previous'),
                                'next_text' => __('Next &raquo;'),
                                'total'     => $total_pages,
                                'current'   => $current_page,
                                'type'      => 'array', // Return value as array to customize links
                            ));

                            if ($page_links) :
                            ?>
                                <div class="tablenav-pages">
                                    <span class="displaying-num"><?php echo sprintf(_n('1 item', '%s items', $total_logs), number_format_i18n($total_logs)); ?></span>
                                    <span class="pagination-links">
                                        <?php foreach ($page_links as $link) : ?>
                                            <?php echo str_replace('page-numbers', 'button', $link); ?>
                                        <?php endforeach; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php else : ?>
                    <p>No logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    public function cxdc_webmaster_pro_visitor_logs()
    {
        global $wpdb;
        $table_name_visitors = $wpdb->prefix . 'cxdcwmpro_visitor_logs'; // Replace with your visitors logs table name

        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['log_ids'])) {
            $deleted = $this->delete_visitor_logs($_POST['log_ids']);
            if ($deleted) {
                echo '<div class="notice notice-success is-dismissible"><p>Selected logs deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-warning is-dismissible"><p>Error deleting logs. Please try again.</p></div>';
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
            echo '<div class="notice notice-warning is-dismissible"><p>Please select logs to delete.</p></div>';
        }

        // Pagination settings
        $per_page = 10;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        // Fetch visitor logs from database
        $logs = $wpdb->get_results("SELECT * FROM $table_name_visitors ORDER BY time DESC LIMIT $per_page OFFSET $offset");

        // Total logs count for pagination
        $total_logs = $wpdb->get_var("SELECT COUNT(id) FROM $table_name_visitors");
        $total_pages = ceil($total_logs / $per_page);

    ?>
        <div class="row">
            <div class="col card m-100 mw-100">
                <h2>Visitors logs</h2>
                <p>View visitor activity logs.</p>

                <!-- Display logs in a table -->
                <form method="post" action="">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th style="width:50px;">ID</th>
                                <th style="width:100px;">IP</th>
                                <th style="width:100px;">Country</th>
                                <th style="width:100px;">Browser</th>
                                <th style="width:100px;">Device</th>
                                <th style="width:100px;">Time</th>
                                <th>Page Visited</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log->id); ?>">
                                    </th>
                                    <td><?php echo esc_html($log->id); ?></td>
                                    <td><?php echo esc_html($log->ip4); ?></td>
                                    <td><?php echo esc_html($log->country); ?></td>
                                    <td><?php echo esc_html($log->browser); ?></td>
                                    <td><?php echo esc_html($log->device); ?></td>
                                    <td><?php echo esc_html($log->time); ?></td>
                                    <td><?php echo esc_html($log->page_visited); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col" class="manage-column column-cb check-column">
                                    <input type="checkbox" id="select-all-bottom">
                                </th>
                                <th>ID</th>
                                <th>IP</th>
                                <th>Country</th>
                                <th>Browser</th>
                                <th>Device</th>
                                <th>Time</th>
                                <th>Page Visited</th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="tablenav bottom">
                        <div class="alignleft actions">
                            <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                            <select name="action" id="bulk-action-selector-bottom">
                                <option value="-1">Bulk Actions</option>
                                <option value="delete_visitors">Delete</option>
                            </select>
                            <input type="submit" name="doaction" id="doaction" class="button action" value="Apply">
                        </div>
                        <?php if ($total_pages > 1) : ?>
                            <div class="tablenav-pages">
                                <span class="displaying-num"><?php echo sprintf(_n('1 item', '%s items', $total_logs), number_format_i18n($total_logs)); ?></span>
                                <span class="pagination-links">
                                    <?php
                                    $page_links = paginate_links(array(
                                        'base'      => add_query_arg('paged', '%#%'),
                                        'format'    => '',
                                        'prev_text' => __('&laquo; Previous'),
                                        'next_text' => __('Next &raquo;'),
                                        'total'     => $total_pages,
                                        'current'   => $current_page,
                                        'type'      => 'array', // Return value as array to customize links
                                    ));

                                    foreach ($page_links as $link) {
                                        echo str_replace('page-numbers', 'button', $link);
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    <?php
    }

    // Function to delete visitor logs
    private function delete_visitor_logs($log_ids)
    {
        global $wpdb;
        $table_name_visitors = $wpdb->prefix . 'cxdcwmpro_visitor_logs';

        // Sanitize and prepare log IDs
        $log_ids = array_map('absint', $log_ids);
        $in_ids = implode(',', $log_ids);

        // Perform deletion query
        $deleted = $wpdb->query("DELETE FROM $table_name_visitors WHERE id IN ($in_ids)");

        return $deleted !== false;
    }

    // Function to delete user logs
    private function delete_user_logs($log_ids)
    {
        global $wpdb;
        $table_name_users = $wpdb->prefix . 'cxdcwmpro_users_logs';

        // Sanitize and prepare log IDs
        $log_ids = array_map('absint', $log_ids);
        $in_ids = implode(',', $log_ids);

        // Perform deletion query
        $deleted = $wpdb->query("DELETE FROM $table_name_users WHERE id IN ($in_ids)");

        return $deleted !== false;
    }

    // Function to retrieve user logs from the database
    private function get_user_logs($page, $per_page)
    {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'cxdcwmpro_users_logs';
        $offset = ($page - 1) * $per_page;

        $logs = $wpdb->get_results("SELECT * FROM $logs_table ORDER BY timestamp DESC LIMIT $per_page OFFSET $offset");

        return $logs;
    }

    // Function to count total user logs for pagination
    private function count_user_logs()
    {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'cxdcwmpro_users_logs';

        $total_logs = $wpdb->get_var("SELECT COUNT(id) FROM $logs_table");

        return $total_logs;
    }
}
?>
