<?php

require_once plugin_dir_path(__FILE__) . '../../includes/GoogleAuthenticator.php';

class Webmasterpro_TwoFactorAuth
{

    public function __construct()
    {
        add_filter('authenticate', array($this, 'webmasterpro_verify_otp_or_secret_key_on_login'), 30, 3);
        add_action('login_form', array($this, 'webmasterpro_add_otp_or_secret_key_field'));
    }

    public function webmasterpro_two_factor_auth_page()
    {
        // Check if the form has been submitted
        if (isset($_POST['webmasterpro_two_factor_settings_submit'])) {
            // Verify nonce for security
            if (!isset($_POST['webmasterpro_two_factor_settings_nonce']) || !wp_verify_nonce($_POST['webmasterpro_two_factor_settings_nonce'], 'webmasterpro_two_factor_settings_nonce')) {
                wp_die('Nonce verification failed');
            }

            // Sanitize and update settings for 2FA
            $enable_two_factor = isset($_POST['enable_two_factor']) ? '1' : '0';
            update_option('webmasterpro_enable_two_factor', $enable_two_factor);

            // Generate a new secret key if 2FA is enabled
            if ($enable_two_factor === '1') {
                $secret_key = $this->generate_secret_key();
                update_option('webmasterpro_ga_secret', $secret_key);
            }

            // Display a notice message
            $notice = 'Two-factor authentication settings saved successfully.';
        }

        // Retrieve current settings for 2FA
        $enable_two_factor = get_option('webmasterpro_enable_two_factor', '0');
        $secret_key = get_option('webmasterpro_ga_secret', '');

?>
        <div class="webmasterpro-wrap">
            <div class="container">
                <div class="row">
                    <div class="col card">
                        <h2>Two-Factor Authentication Settings</h2>
                        <?php if (!empty($notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-success is-dismissible">
                                <p><?php echo $notice; ?></p>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Enable Two-Factor Authentication</th>
                                    <td>
                                        <input type="checkbox" name="enable_two_factor" <?php checked('1', $enable_two_factor); ?> />
                                        <p class="description">Enable or disable two-factor authentication.</p>
                                    </td>
                                </tr>
                                <?php $this->webmasterpro_display_qr_code(); ?>
                                <tr valign="top">
                                    <th scope="row">Download Authenticator App</th>
                                    <td>
                                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Authenticator</a>
                                        <p class="description">Download the Google Authenticator app from the Google Play Store.</p>
                                    </td>
                                </tr>
                            </table>
                            <?php wp_nonce_field('webmasterpro_two_factor_settings_nonce', 'webmasterpro_two_factor_settings_nonce'); ?>
                            <input type="submit" name="webmasterpro_two_factor_settings_submit" class="button-primary" value="Save Changes">
                        </form>
                    </div>
                    <div class="col card">
                        <?php if (!empty($secret_key)) : ?>
                            <div class="custom-notice custom-notice-warning ">
                                <h3>Google Authenticator Secret Key</h3>
                                <p class="custom-notice custom-notice-info"><?php echo $secret_key; ?></p>
                                <p>Make sure to securely store your Google Authenticator QR code and secret key.</p>
                            </div>
                        <?php endif; ?>
                        <div class="child_card">
                            <h3>Important Notes</h3>
                            <ul>
                                <li>Enabling two-factor authentication adds an extra layer of security to your account.</li>
                                <li>Make sure to securely store your Google Authenticator QR code and secret key.</li>
                            </ul>
                            <h3>Warnings</h3>
                            <ul>
                                <li>If you lose access to your Google Authenticator app, you may not be able to log in.</li>
                                <li>Keep your email address up-to-date to receive important notifications.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    public function webmasterpro_display_qr_code()
    {
        if (get_option('webmasterpro_enable_two_factor', '0') === '1') {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = get_option('webmasterpro_ga_secret');

            // If the secret is not already generated, generate it
            if (!$secret) {
                $secret = $this->generate_secret_key();
                update_option('webmasterpro_ga_secret', $secret);
            }

            $qrCodeUrl = $ga->getQRCodeGoogleUrl(get_bloginfo('name'), $secret);
            echo '<tr valign="top">
                <th scope="row">Google Authenticator QR Code</th>
                <td>
                    <img src="' . esc_url($qrCodeUrl) . '" alt="QR Code" />
                    <p class="description">Scan this QR code with your Google Authenticator app.</p>
                </td>
              </tr>';
        }
    }

    public function generate_secret_key()
    {
        $ga = new PHPGangsta_GoogleAuthenticator();
        return $ga->createSecret( $length = 64 );
    }

    public function webmasterpro_verify_otp_or_secret_key_on_login($user, $username, $password)
    {
        if (get_option('webmasterpro_enable_two_factor', '0') === '1') {
            // Check if OTP or Secret Key is provided
            $otp = '';
            if (isset($_POST['otp_code'])) {
                $otp = $_POST['otp_code'];
            } else {
                return new WP_Error('otp_required', __('An OTP or Secret Key is required to complete the login.'));
            }
    
            $secret = get_option('webmasterpro_ga_secret');
            $ga = new PHPGangsta_GoogleAuthenticator();
    
            // Verify OTP or Secret Key
            if (!$ga->verifyCode($secret, $otp, 2) && $secret !== $otp) { // 2 = 2*30sec clock tolerance
                return new WP_Error('invalid_otp', __('Invalid OTP code or Secret Key.'));
            }
        }
        return $user;
    }
    

    public function webmasterpro_add_otp_or_secret_key_field()
    {
        if (get_option('webmasterpro_enable_two_factor', '0') === '1') {
            echo '<p>
                <label for="otp_code">OTP Code or Secret Key<br />
                <input type="text" name="otp_code" id="otp_code" class="input" size="20" /></label>
              </p>';
        }
    }
}

$webmasterpro_two_factor_auth = new Webmasterpro_TwoFactorAuth();
