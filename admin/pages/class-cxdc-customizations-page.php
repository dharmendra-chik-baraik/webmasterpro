<?php

class WebMasterPro_Customization
{

    private $option_prefix = 'webmasterpro_';

    public function __construct()
    {
        // Enqueue scripts and styles
    }

    public function render_customization_page()
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Sanitize the 'tab' parameter
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'login_page';
?>
        <div class="wrap">
            <div class="container">
                <div class="card">
                    <h1>WebMasterPro Customizations</h1>
                    <p>Select the tab you want to customize.</p>
                    <h2 class="nav-tab-wrapper">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=webmasterpro-customizations&tab=login_page')); ?>" class="nav-tab <?php echo $active_tab == 'login_page' ? 'nav-tab-active' : ''; ?>">Login Page</a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=webmasterpro-customizations&tab=custom_style')); ?>" class="nav-tab <?php echo $active_tab == 'custom_style' ? 'nav-tab-active' : ''; ?>">Custom Style</a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=webmasterpro-customizations&tab=custom_script')); ?>" class="nav-tab <?php echo $active_tab == 'custom_script' ? 'nav-tab-active' : ''; ?>">Custom Script</a>
                    </h2>
                </div>

                <div class="tab-content">
                    <?php
                    switch ($active_tab) {
                        case 'custom_style':
                            $this->render_custom_style_tab();
                            break;
                        case 'custom_script':
                            $this->render_custom_script_tab();
                            break;
                        case 'login_page':
                        default:
                            $this->render_login_page_tab();
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    public function render_login_page_tab()
    {
        $options = get_option($this->option_prefix . 'login_page_settings');
        $background_color = isset($options['background_color']) ? $options['background_color'] : '';
        $text_color = isset($options['text_color']) ? $options['text_color'] : '';
        $background_image = isset($options['background_image']) ? $options['background_image'] : '';
        $logo_image = isset($options['logo_image']) ? $options['logo_image'] : '';
        $logo_url = isset($options['logo_url']) ? $options['logo_url'] : '';
        $notice = '';

        // Handle form submission
        if (isset($_POST['login_page_submit'])) {
            // Verify nonce
            if (!isset($_POST['webmasterpro_login_page_settings_nonce']) || !wp_verify_nonce($_POST['webmasterpro_login_page_settings_nonce'], 'webmasterpro_login_page_settings_save')) {
                wp_die(__('Nonce verification failed.', 'webmasterpro'));
            }

            // Sanitize and update options
            $background_color = sanitize_hex_color($_POST['background_color']);
            $text_color = sanitize_hex_color($_POST['text_color']);
            $background_image = esc_url($_POST['background_image']);
            $logo_image = esc_url($_POST['logo_image']);
            $logo_url = esc_url($_POST['logo_url']);

            $options = array(
                'background_color' => $background_color,
                'text_color' => $text_color,
                'background_image' => $background_image,
                'logo_image' => $logo_image,
                'logo_url' => $logo_url
            );

            update_option($this->option_prefix . 'login_page_settings', $options);
            $notice = __('Settings saved successfully.', 'webmasterpro');
        }
    ?>
        <div class="login-tab-wrapper row card ">
            <div style="max-width: 100%;" class="col-md-12">
                <form method="post" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field('webmasterpro_login_page_settings_save', 'webmasterpro_login_page_settings_nonce'); ?>
                    <h3>Login Page Settings</h3>
                    <?php if (!empty($notice)) : ?>
                        <div style="margin: 0px;" class="notice notice-success is-dismissible">
                            <p><?php echo $notice; ?></p>
                        </div>
                    <?php endif; ?>
                    <table class="form-table">
                        <!-- Background Color Setting -->
                        <tr>
                            <th scope="row">
                                <label for="background_color">Background Color</label>
                            </th>
                            <td>
                                <input type="text" name="background_color" id="background_color" value="<?php echo esc_attr($background_color); ?>" class="color-picker" />
                                <div id="background_color_picker" class="color-picker"></div>
                                <p class="description">Select the background color for the login page. This will apply to the entire page behind the login form.</p>
                            </td>
                        </tr>
                        <!-- Text Color Setting -->
                        <tr>
                            <th scope="row">
                                <label for="text_color">Text Color</label>
                            </th>
                            <td>
                                <input type="text" name="text_color" id="text_color" value="<?php echo esc_attr($text_color); ?>" class="color-picker" />
                                <div id="text_color_picker" class="color-picker"></div>
                                <p class="description">Choose the color for the text on the login page, including labels and links.</p>
                            </td>
                        </tr>
                        <!-- Redirect Login Page Logo URL Setting -->
                        <tr>
                            <th scope="row">
                                <label for="logo_url">Redirect Login Page Logo URL</label>
                            </th>
                            <td>
                                <input type="text" name="logo_url" id="logo_url" value="<?php echo esc_url($logo_url); ?>" />
                                <p class="description">Set the URL that the login page logo should link to when clicked. Typically, this could be your site's homepage or a custom page.</p>
                            </td>
                        </tr>
                        <!-- Background Image Setting -->
                        <tr>
                            <th scope="row">
                                <label for="background_image">Background Image</label>
                            </th>
                            <td>
                                <input type="text" name="background_image" id="background_image" value="<?php echo esc_url($background_image); ?>" />
                                <input type="button" name="upload_background_image_button" id="upload_background_image_button" class="button" value="Upload Image">
                                <div id="background_image_preview" style="margin-top: 10px;">
                                    <?php if ($background_image) : ?>
                                        <img src="<?php echo esc_url($background_image); ?>" alt="Background Image" style="max-width: 120px; margin-bottom: 10px; display: block;" />
                                        <button type="button" id="delete_background_image_button" class="button button-danger">Delete Image</button>
                                    <?php endif; ?>
                                </div>
                                <p class="description">Upload or select an image to use as the background for the login page. This image will display behind the login form.</p>
                            </td>
                        </tr>
                        <!-- Logo Image Setting -->
                        <tr>
                            <th scope="row">
                                <label for="logo_image">Logo Image</label>
                            </th>
                            <td>
                                <input type="text" name="logo_image" id="logo_image" value="<?php echo esc_url($logo_image); ?>" />
                                <input type="button" name="upload_logo_image_button" id="upload_logo_image_button" class="button" value="Upload Image">
                                <div id="logo_image_preview" style="margin-top: 10px;">
                                    <?php if ($logo_image) : ?>
                                        <img src="<?php echo esc_url($logo_image); ?>" alt="Logo Image" style="max-width: 120px; margin-bottom: 10px; display: block;" />
                                        <button type="button" id="delete_logo_image_button" class="button button-danger">Delete Image</button>
                                    <?php endif; ?>
                                </div>
                                <p class="description">Upload or select an image to replace the default WordPress logo on the login page.</p>
                            </td>
                        </tr>
                    </table>
                    <!-- Submit Button -->
                    <p class="submit">
                        <input type="submit" name="login_page_submit" class="button-primary" value="Save Changes">
                    </p>
                </form>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#upload_background_image_button').click(function() {
                    var mediaUploader;
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media({
                        title: 'Choose Background Image',
                        button: {
                            text: 'Choose Image'
                        },
                        multiple: false
                    });
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#background_image').val(attachment.url);
                        $('#background_image_preview').html('<img src="' + attachment.url + '" alt="Background Image" style="max-width: 200px; border:1px solid black; display: block;" />' +
                            '<button type="button" id="delete_background_image_button" class="button">Delete Image</button>');
                    });
                    mediaUploader.open();
                });

                $('#upload_logo_image_button').click(function() {
                    var mediaUploader;
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media({
                        title: 'Choose Logo Image',
                        button: {
                            text: 'Choose Image'
                        },
                        multiple: false
                    });
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#logo_image').val(attachment.url);
                        $('#logo_image_preview').html('<img src="' + attachment.url + '" alt="Logo Image" style="max-width: 200px; display: block;" />' +
                            '<button type="button" id="delete_logo_image_button" class="button">Delete Image</button>');
                    });
                    mediaUploader.open();
                });

                // Delete background image
                $(document).on('click', '#delete_background_image_button', function() {
                    $('#background_image').val('');
                    $('#background_image_preview').html('');
                });

                // Delete logo image
                $(document).on('click', '#delete_logo_image_button', function() {
                    $('#logo_image').val('');
                    $('#logo_image_preview').html('');
                });
            });
        </script>
    <?php
    }

    public function render_custom_style_tab()
    {
        $custom_style_options = get_option('webmasterpro_custom_style_settings');
        $notice = '';
        $header_css = isset($custom_style_options['header_css']) ? $custom_style_options['header_css'] : '';
        $footer_css = isset($custom_style_options['footer_css']) ? $custom_style_options['footer_css'] : '';

        // Handle form submission
        if (isset($_POST['custom_style_submit'])) {
            // Sanitize and save options
            $header_css = wp_kses_post($_POST['header_css']);
            $footer_css = wp_kses_post($_POST['footer_css']);

            $custom_style_options = array(
                'header_css' => $header_css,
                'footer_css' => $footer_css
            );

            update_option('webmasterpro_custom_style_settings', $custom_style_options);
            $notice = 'Settings saved successfully.';
        }
    ?>
        <div class="custom-css-wrapper">
            <div class="row card">
                <div class="col w-100 mw-100">
                    <form method="post" action="">
                        <h3>Custom Style Settings</h3>
                        <?php if (!empty($notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-success is-dismissible">
                                <p><?php echo $notice; ?></p>
                            </div>
                        <?php endif; ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="header_css">Custom CSS for Header</label></th>
                                <td><textarea name="header_css" id="header_css" class="large-text input_bg" rows="10"><?php echo $header_css; ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="footer_css">Custom CSS for Footer</label></th>
                                <td><textarea name="footer_css" id="footer_css" class="large-text input_bg" rows="10"><?php echo $footer_css; ?></textarea></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="custom_style_submit" class="button-primary" value="Save Changes">
                        </p>
                    </form>
                    
                    <div class="notes child_card">
                        <h3>Custom CSS Notes</h3>
                        <p>Custom CSS allows you to style your website's header and footer sections precisely as you desire. Here are some tips:</p>
                        <ul>
                            <li>Use selectors specific to your theme or plugin elements.</li>
                            <li>Test your CSS changes in a development environment before applying them live.</li>
                            <li>Consider using CSS preprocessors like Sass or Less for more efficient coding.</li>
                            <li>Regularly review and optimize your CSS to improve website performance.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function render_custom_script_tab()
    {
        // Retrieve options from database
        $custom_script_options = get_option('webmasterpro_custom_script_settings');
        $notice = '';
        $header_script = isset($custom_script_options['header_script']) ? $custom_script_options['header_script'] : '';
        $footer_script = isset($custom_script_options['footer_script']) ? $custom_script_options['footer_script'] : '';

        // Handle form submission
        if (isset($_POST['custom_script_submit'])) {
            // Sanitize and save options
            $header_script = $_POST['header_script'];
            $footer_script = $_POST['footer_script'];

            // Validate and sanitize the input
            $header_script = wp_unslash($header_script);
            $footer_script = wp_unslash($footer_script);

            $custom_script_options = array(
                'header_script' => $header_script,
                'footer_script' => $footer_script
            );

            update_option('webmasterpro_custom_script_settings', $custom_script_options);
            $notice = 'Settings saved successfully.';
        }
    ?>
        <div class="custom_script_wrapper">
            <div class="row card">
                <div class="col m-100 mw-100">
                    <form method="post" action="">
                        <h3>Custom Script Settings</h3>
                        <?php if (!empty($notice)) : ?>
                            <div style="margin: 0px;" class="notice notice-success is-dismissible">
                                <p><?php echo esc_html($notice); ?></p>
                            </div>
                        <?php endif; ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="header_script">Custom CSS/JavaScript for Header</label></th>
                                <td><textarea name="header_script" id="header_script" class="large-text input_bg" rows="10"><?php echo esc_textarea($header_script); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="footer_script">Custom CSS/JavaScript for Footer</label></th>
                                <td><textarea name="footer_script" id="footer_script" class="large-text input_bg" rows="10"><?php echo esc_textarea($footer_script); ?></textarea></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="custom_script_submit" class="button-primary" value="Save Changes">
                        </p>
                    </form>
                    <div class="notes child_card">
                        <h3>Custom Script Notes</h3>
                        <p>Custom JavaScript allows you to enhance your website's functionality in specific areas. Here are some guidelines:</p>
                        <ul>
                            <li>Target only necessary elements with your scripts to avoid unnecessary processing.</li>
                            <li>Test your JavaScript thoroughly in a development environment to ensure compatibility and performance.</li>
                            <li>Consider using asynchronous loading for scripts that don't need to block page rendering.</li>
                            <li>Regularly review and optimize your scripts to maintain website performance.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }



    public function initialize_color_picker()
    {
    ?>
        <script>
            jQuery(document).ready(function($) {
                $('.color-picker').wpColorPicker();
            });
        </script>
<?php
    }
}
