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
        // SMTP Settings submenu
        add_submenu_page(
            'webmasterpro',                // Parent menu slug
            'SMTP Settings',               // Page title
            'SMTP Settings',               // Menu title
            'manage_options',              // Capability required to access menu
            'webmasterpro-smtp-settings',  // Menu slug
            array($this, 'cxdc_webmasterpro_smtp_settings_page')  // Callback function to render the submenu page
        );

        // Shortcodes submenu
        add_submenu_page(
            'webmasterpro',                // Parent menu slug
            'Shortcodes',                  // Page title
            'Shortcodes',                  // Menu title
            'manage_options',              // Capability required to access menu
            'webmasterpro-shortcodes',     // Menu slug
            array($this, 'cxdc_webmasterpro_shortcodes_page')  // Callback function to render the submenu page
        );

        // SEO Settings submenu
        add_submenu_page(
            'webmasterpro',                        // Parent menu slug
            'SEO Settings',                        // Page title
            'SEO Settings',                        // Menu title
            'manage_options',                      // Capability required to access menu
            'webmasterpro-seo-settings',           // Menu slug
            array($this, 'cxdc_webmasterpro_seo_settings_page')  // Callback function to render the submenu page
        );
        // Customization submenu
        add_submenu_page(
            'webmasterpro',                // Parent menu slug
            'Customizations',               // Page title
            'Customizations',               // Menu title
            'manage_options',              // Capability required to access menu
            'webmasterpro-customizations',  // Menu slug
            array($this, 'cxdc_webmasterpro_customization_page')  // Callback function to render the submenu page
        );
        // Optimizations submenu
        add_submenu_page(
            'webmasterpro',                        // Parent menu slug
            'Optimizations',                       // Page title
            'Optimizations',                       // Menu title
            'manage_options',                      // Capability required to access menu
            'webmasterpro-optimizations',          // Menu slug
            array($this, 'cxdc_webmasterpro_optimizations_page')  // Callback function to render the submenu page
        );

        // CF7 Submissions submenu
        add_submenu_page(
            'webmasterpro',                        // Parent menu slug
            'CF7 Submissions',                     // Page title
            'CF7 Submissions',                     // Menu title
            'manage_options',                      // Capability required to access menu
            'webmasterpro-cf7-submissions',        // Menu slug
            array($this, 'cxdc_webmasterpro_cf7_submissions_page')  // Callback function to render the submenu page
        );

        // Logs & Activity submenu
        add_submenu_page(
            'webmasterpro',                        // Parent menu slug
            'Logs & Activity',                     // Page title
            'Logs & Activity',                     // Menu title
            'manage_options',                      // Capability required to access menu
            'webmasterpro-logs',                   // Menu slug
            array($this, 'cxdc_webmasterpro_logs_page')  // Callback function to render the submenu page
        );
        // Security submenu
        add_submenu_page(
            'webmasterpro',                // Parent menu slug
            'WebMasterPro Security',                    // Page title
            'WMPro Security',                    // Menu title
            'manage_options',              // Capability required to access menu
            'webmasterpro-security',       // Menu slug
            array($this, 'cxdc_webmasterpro_security_page')  // Callback function to render the submenu page
        );

        // Settings submenu
        add_submenu_page(
            'webmasterpro',                // Parent menu slug
            'WebMasterPro Settings',        // Page title
            'WMPro Settings',                 // Menu title
            'manage_options',              // Capability required to access menu
            'webmasterpro-settings',       // Menu slug
            array($this, 'cxdc_webmasterpro_settings_page')  // Callback function to render the submenu page
        );
        // CXDC Backup submenu
        // add_submenu_page(
        //     'webmasterpro',                        // Parent menu slug
        //     'WebMasterPro Backup',                         // Page title
        //     'WMPro Backup',                         // Menu title
        //     'manage_options',                      // Capability required to access menu
        //     'webmasterpro-cxdc-backup',            // Menu slug
        //     array($this, 'cxdc_webmasterpro_cxdc_backup_page')  // Callback function to render the submenu page
        // );
        // License and Updates submenu
        add_submenu_page(
            'webmasterpro',                        // Parent menu slug
            'License & Updates',                 // Page title
            'License & Updates',                 // Menu title
            'manage_options',                      // Capability required to access menu
            'webmasterpro-license-and-updates',    // Menu slug
            array($this, 'cxdc_webmasterpro_license_and_updates_page')  // Callback function to render the submenu page
        );

        // Add more submenus here as needed
    }

    // Callback function to display content for WebMasterPro dashboard page
    public function cxdc_webmasterpro_dashboard_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        // Include the dashboard page template
        require_once WEBMASTERPRO_PLUGIN_DIR . 'admin/partials/admin-dashboard-page.php';
        $dashboard = new Webmasterpro_Dashboard();
        $dashboard->webmasterpro_dashboard_page();
    }

    // Callback function to display content for WebMasterPro SMTP settings page
    public function cxdc_webmasterpro_smtp_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        // Include the SMTP settings page template
        require_once WEBMASTERPRO_PLUGIN_DIR . 'admin/pages/class-cxdc-smtp-settings-page.php';
        $smtp_settings = new Webmasterpro_Smtp_Settings();
        $smtp_settings->cxdc_webmaster_pro_smtp_settings_page();
    }

    // Callback function to display content for WebMasterPro shortcodes page
    public function cxdc_webmasterpro_shortcodes_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Shortcodes</h1></div>';
    }

    // Callback function to display content for WebMasterPro security page
    public function cxdc_webmasterpro_security_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Security</h1></div>';
    }

    // Callback function to display content for WebMasterPro customization page
    public function cxdc_webmasterpro_customization_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Customization</h1></div>';
    }

    // Callback function to display content for WebMasterPro settings page
    public function cxdc_webmasterpro_settings_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Settings</h1></div>';
    }

    // Callback function to display content for WebMasterPro CF7 submissions page
    public function cxdc_webmasterpro_cf7_submissions_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro CF7 Submissions</h1></div>';
    }

    // Callback function to display content for WebMasterPro logs & activity page
    public function cxdc_webmasterpro_logs_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Logs & Activity</h1></div>';
    }

    // Callback function to display content for WebMasterPro SEO settings page
    public function cxdc_webmasterpro_seo_settings_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro SEO Settings</h1></div>';
    }

    // Callback function to display content for WebMasterPro CXDC Backup page
    public function cxdc_webmasterpro_cxdc_backup_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro CXDC Backup</h1></div>';
    }

    // Callback function to display content for WebMasterPro optimizations page
    public function cxdc_webmasterpro_optimizations_page()
    {
        echo '<div class="wrap"><h1>WebMasterPro Optimizations</h1></div>';
    }

    // Callback function to display content for WebMasterPro license and updates page
    public function cxdc_webmasterpro_license_and_updates_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        // Include the license and updates page template
        require_once WEBMASTERPRO_PLUGIN_DIR . 'admin/pages/class-cxdc-license-and-updates-page.php';
    }
}

new Webmasterpro_Menu();
