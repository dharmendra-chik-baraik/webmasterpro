<?php
require_once plugin_dir_path(__FILE__) . '../partials/class-cxdc-general-security.php';
require_once plugin_dir_path(__FILE__) . '../partials/class-cxdc-database-security.php';
class Webmasterpro_Security
{

    public function cxdc_webmaster_pro_security_page()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>
        <div class="wrap">
            <div class="container">
                <div class="card">
                    <div style="max-width: 100%;" class="webmasterpro-header">
                        <h1>WebMasterPro Security</h1>
                        <p>Protect your WordPress site with webmasterpro's robust security features. From general site hardening to advanced user, database, and file security measures, webmasterpro ensures your website remains safe from malicious threats. Implement two-factor authentication and configurable firewalls to fortify your defenses. Safeguard sensitive data and maintain trust with your audience through proactive security measures tailored to enhance your site's resilience against cyber threats.</p>
                    </div>
                    <div class="tabs">
                        <h2 class="nav-tab-wrapper">
                            <a href="?page=webmasterpro-security&tab=general" class="nav-tab <?php echo $current_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                            <a href="?page=webmasterpro-security&tab=user_security" class="nav-tab <?php echo $current_tab == 'user_security' ? 'nav-tab-active' : ''; ?>">User Security</a>
                            <a href="?page=webmasterpro-security&tab=database_security" class="nav-tab <?php echo $current_tab == 'database_security' ? 'nav-tab-active' : ''; ?>">Database Security</a>
                            <a href="?page=webmasterpro-security&tab=files_security" class="nav-tab <?php echo $current_tab == 'files_security' ? 'nav-tab-active' : ''; ?>">Files Security</a>
                            <a href="?page=webmasterpro-security&tab=firewalls" class="nav-tab <?php echo $current_tab == 'firewalls' ? 'nav-tab-active' : ''; ?>">Firewalls</a>
                            <a href="?page=webmasterpro-security&tab=two_factor" class="nav-tab <?php echo $current_tab == 'two_factor' ? 'nav-tab-active' : ''; ?>">Two Factor Authentication</a>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="container">
                <?php
                switch ($current_tab) {
                    case 'general':
                        Webmasterpro_General_Security::webmasterpro_general_security_page();
                        break;
                    case 'user_security':
                        Webmasterpro_General_Security::webmasterpro_user_security_page();
                        break;
                    case 'database_security':
                        $webmaster_database_security = new Webmasterpro_Database_Security();
                        $webmaster_database_security->webmasterpro_database_security_page();
                        break;
                    case 'files_security':
                        webmasterpro_files_security_page();
                        break;
                    case 'firewalls':
                        webmasterpro_firewalls_page();
                        break;
                    case 'two_factor':
                        webmasterpro_two_factor_page();
                        break;
                    default:
                        webmasterpro_general_security_page();
                        break;
                }
                ?>
            </div>
        </div>
<?php
    }
}
