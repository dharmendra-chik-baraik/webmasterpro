<?php

class Webmasterpro_Menu extends Webmasterpro_Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menus'));
    }

    public function register_menus()
    {
        // Register main menu
        add_menu_page(
            'WebMasterPro',            // Page title
            'WebMasterPro',            // Menu title
            'manage_options',          // Capability required to access menu
            'webmasterpro',            // Menu slug
            array($this, 'cxdc_webmasterpro_dashboard_page'), // Callback function to render the menu page
            'dashicons-admin-generic', // Icon URL or dashicon name
            80                         // Menu position (optional)
        );

        // Register submenu pages
        $this->register_submenus();
    }

    public function register_submenus()
    {
        // Array of submenus to register
        $submenus = [
            [
                'title' => 'SMTP Settings',
                'slug' => 'webmasterpro-smtp-settings',
                'callback' => 'cxdc_webmasterpro_smtp_settings_page',
                'class' => 'Webmasterpro_Smtp_Settings',
                'file' => 'class-cxdc-smtp-settings-page.php'
            ],
            [
                'title' => 'Shortcodes',
                'slug' => 'webmasterpro-shortcodes',
                'callback' => 'cxdc_webmasterpro_shortcodes_page',
                'class' => 'WebMasterPro_Shortcodes',
                'file' => 'class-cxdc-shortcodes-page.php'
            ],
            [
                'title' => 'Customizations',
                'slug' => 'webmasterpro-customizations',
                'callback' => 'cxdc_webmasterpro_customization_page',
                'class' => 'WebMasterPro_Customization',
                'file' => 'class-cxdc-customizations-page.php'
            ],
            [
                'title' => 'CF7 Submissions',
                'slug' => 'webmasterpro-cf7-submissions',
                'callback' => 'cxdc_webmasterpro_cf7_submissions_page',
                'class' => 'Webmasterpro_Cf7db',
                'file' => 'class-cxdc-cf7db-page.php'
            ],
            [
                'title' => 'Logs & Activity',
                'slug' => 'webmasterpro-logs',
                'callback' => 'cxdc_webmasterpro_logs_page',
                'class' => 'Webmasterpro_Logs_and_activity',
                'file' => 'class-cxdc-logs-and-activity-page.php'
            ],
            [
                'title' => 'WMPro Security',
                'slug' => 'webmasterpro-security',
                'callback' => 'cxdc_webmasterpro_security_page',
                'class' => 'Webmasterpro_Security',
                'file' => 'class-cxdc-webmasterpro-security.php'
            ],
            [
                'title' => 'WMPro Settings',
                'slug' => 'webmasterpro-settings',
                'callback' => 'cxdc_webmasterpro_settings_page',
                'class' => 'Webmasterpro_Settings',
                'file' => 'class-cxdc-webmasterpro-settings.php'
            ],
            [
                'title' => 'License & Updates',
                'slug' => 'webmasterpro-license-and-updates',
                'callback' => 'cxdc_webmasterpro_license_and_updates_page',
                'class' => 'Webmasterpro_License_and_Updates',
                'file' => 'class-cxdc-license-and-updates-page.php'
            ]
        ];

        // Loop through each submenu and register it
        foreach ($submenus as $submenu) {
            add_submenu_page(
                'webmasterpro',                // Parent menu slug
                $submenu['title'],             // Page title
                $submenu['title'],             // Menu title
                'manage_options',              // Capability required to access menu
                $submenu['slug'],              // Menu slug
                array($this, $submenu['callback']) // Callback function to render the submenu page
            );
        }
    }

    // Callback function to display content for WebMasterPro dashboard page
    public function cxdc_webmasterpro_dashboard_page()
    {
        $this->load_page('webmasterpro-dashboard-page.php', 'Webmasterpro_Dashboard', 'webmasterpro_dashboard_page');
    }

    // Callback function to display content for WebMasterPro SMTP settings page
    public function cxdc_webmasterpro_smtp_settings_page()
    {
        $this->load_page('class-cxdc-smtp-settings-page.php', 'Webmasterpro_Smtp_Settings', 'cxdc_webmaster_pro_smtp_settings_page');
    }

    // Callback function to display content for WebMasterPro shortcodes page
    public function cxdc_webmasterpro_shortcodes_page()
    {
        $this->load_page('class-cxdc-shortcodes-page.php', 'WebMasterPro_Shortcodes', 'webmasterpro_shortcodes_page');
    }

    // Callback function to display content for WebMasterPro security page
    public function cxdc_webmasterpro_security_page()
    {
        $this->load_page('class-cxdc-webmasterpro-security.php', 'Webmasterpro_Security', 'cxdc_webmaster_pro_security_page');
    }

    // Callback function to display content for WebMasterPro customization page
    public function cxdc_webmasterpro_customization_page()
    {
        $this->load_page('class-cxdc-customizations-page.php', 'WebMasterPro_Customization', 'render_customization_page');
    }

    // Callback function to display content for WebMasterPro settings page
    public function cxdc_webmasterpro_settings_page()
    {
        $this->load_page('class-cxdc-webmasterpro-settings.php', 'Webmasterpro_Settings', 'cxdc_webmaster_pro_settings_page');
    }

    // Callback function to display content for WebMasterPro CF7 submissions page
    public function cxdc_webmasterpro_cf7_submissions_page()
    {
        $this->load_page('class-cxdc-cf7db-page.php', 'Webmasterpro_Cf7db', 'cxdc_webmaster_pro_cf7db_page');
    }

    // Callback function to display content for WebMasterPro logs & activity page
    public function cxdc_webmasterpro_logs_page()
    {
        $this->load_page('class-cxdc-logs-and-activity-page.php', 'Webmasterpro_Logs_and_activity', 'cxdc_webmaster_pro_logs_and_activity_page');
    }

    // Callback function to display content for WebMasterPro license and updates page
    public function cxdc_webmasterpro_license_and_updates_page()
    {
        $this->load_page('class-cxdc-license-and-updates-page.php', 'Webmasterpro_License_and_Updates', 'cxdc_webmaster_pro_license_and_updates_page');
    }

    // Function to load the page class and call its method dynamically
    private function load_page($file, $class, $method)
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require_once WEBMASTERPRO_PLUGIN_DIR . 'admin/pages/' . $file;

        if (class_exists($class)) {
            $page_instance = new $class();
            if (method_exists($page_instance, $method)) {
                $page_instance->$method();
            } else {
                echo '<p>Method ' . esc_html($method) . ' does not exist in class ' . esc_html($class) . '.</p>';
            }
        } else {
            echo '<p>Class ' . esc_html($class) . ' not found.</p>';
        }
    }
}

new Webmasterpro_Menu();
