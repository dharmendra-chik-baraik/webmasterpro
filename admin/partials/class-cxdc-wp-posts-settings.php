<?php
class webmasterpro_Media_Settings {
    
    // Constructor to initialize hooks
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'apply_image_sizes'));
        add_filter('jpeg_quality', array($this, 'set_jpeg_quality'));
        add_filter('upload_mimes', array($this, 'restrict_upload_types'));
        add_action('admin_init', array($this, 'disable_image_compression'));
        add_action('admin_init', array($this, 'generate_media_metadata'));
    }
    // Register settings
    public function register_settings() {
        register_setting('webmasterpro_media_settings', 'webmasterpro_custom_image_sizes');
        register_setting('webmasterpro_media_settings', 'webmasterpro_jpeg_quality', array(
            'type' => 'integer',
            'default' => 82,
            'sanitize_callback' => 'absint',
            'validate_callback' => array($this, 'validate_jpeg_quality')
        ));
        register_setting('webmasterpro_media_settings', 'webmasterpro_restrict_upload_types');
        register_setting('webmasterpro_media_settings', 'webmasterpro_disable_image_compression');
        register_setting('webmasterpro_media_settings', 'webmasterpro_generate_media_metadata');
    }

    // Validate JPEG quality callback
    public function validate_jpeg_quality($value) {
        if ($value < 1 || $value > 100) {
            add_settings_error(
                'webmasterpro_jpeg_quality',
                'invalid_jpeg_quality',
                'JPEG quality must be between 1 and 100.',
                'error'
            );
            return get_option('webmasterpro_jpeg_quality', 82); // Default to 82 if validation fails
        }
        return $value;
    }

    // Render settings page
    public static function render_settings_page() {
        ?>
        <div class="container card">
            <div class="card">
            <h1>webmasterpro Media Settings</h1>
            <?php settings_errors(); ?>
            <hr>
            <form method="post" action="options.php">
                <?php settings_fields('webmasterpro_media_settings'); ?>
                <table class="form-table">
                    <!-- Custom Image Sizes Option -->
                    <tr>
                        <th scope="row"><label for="webmasterpro_custom_image_sizes">Enable Custom Image Sizes:</label></th>
                        <td>
                            <input type="checkbox" id="webmasterpro_custom_image_sizes" name="webmasterpro_custom_image_sizes" value="1" <?php checked(get_option('webmasterpro_custom_image_sizes', '0'), '1'); ?>>
                            <p class="description">
                                <strong>Note:</strong> Enable this option to add custom image sizes for your uploaded images.
                            </p>
                        </td>
                    </tr>

                    <!-- JPEG Quality Option -->
                    <tr>
                        <th scope="row"><label for="webmasterpro_jpeg_quality">JPEG Quality (1-100):</label></th>
                        <td>
                            <input type="number" id="webmasterpro_jpeg_quality" name="webmasterpro_jpeg_quality" value="<?php echo esc_attr(get_option('webmasterpro_jpeg_quality', '82')); ?>" min="1" max="100">
                            <p class="description">
                                <strong>Note:</strong> Set the quality of JPEG images on a scale of 1 to 100.
                            </p>
                        </td>
                    </tr>

                    <!-- Restrict Upload Types Option -->
                    <tr>
                        <th scope="row"><label for="webmasterpro_restrict_upload_types">Restrict Upload Types:</label></th>
                        <td>
                            <input type="checkbox" id="webmasterpro_restrict_upload_types" name="webmasterpro_restrict_upload_types" value="1" <?php checked(get_option('webmasterpro_restrict_upload_types', '0'), '1'); ?>>
                            <p class="description">
                                <strong>Note:</strong> Enable this option to restrict the types of files that can be uploaded.
                            </p>
                        </td>
                    </tr>

                    <!-- Disable Image Compression Option -->
                    <tr>
                        <th scope="row"><label for="webmasterpro_disable_image_compression">Disable Image Compression:</label></th>
                        <td>
                            <input type="checkbox" id="webmasterpro_disable_image_compression" name="webmasterpro_disable_image_compression" value="1" <?php checked(get_option('webmasterpro_disable_image_compression', '0'), '1'); ?>>
                            <p class="description">
                                <strong>Note:</strong> Enable this option to prevent WordPress from compressing images during upload.
                            </p>
                        </td>
                    </tr>

                    <!-- Generate Media Metadata Option -->
                    <tr valign="top">
                        <th scope="row">Generate Media Metadata</th>
                        <td>
                            <label for="webmasterpro_generate_media_metadata">
                                <input type="checkbox" id="webmasterpro_generate_media_metadata" name="webmasterpro_generate_media_metadata" value="1" <?php checked(get_option('webmasterpro_generate_media_metadata', '0'), '1'); ?>>
                                Enable automatic generation of media metadata
                            </label>
                            <p class="description">
                                <strong>Note:</strong> This option will automatically generate metadata for your media files.
                            </p>
                        </td>
                    </tr>

                    <!-- Save Settings Button -->
                    <tr>
                        <th scope="row"><label for="submit">Save Settings</label></th>
                        <td><input class="button button-primary" type="submit" value="Save Changes"></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    // Apply image sizes if enabled
    public function apply_image_sizes() {
        if (get_option('webmasterpro_custom_image_sizes', '0') === '1') {
            add_image_size('custom_size_1', 400, 400, true);
            add_image_size('custom_size_2', 800, 800, false);
        }
    }

    // Set JPEG quality
    public function set_jpeg_quality($arg) {
        return get_option('webmasterpro_jpeg_quality', 82);
    }

    // Restrict upload types
    public function restrict_upload_types($mimes) {
        if (get_option('webmasterpro_restrict_upload_types', '0') === '1') {
            return array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            );
        }
        return $mimes;
    }

    // Disable image compression
    public function disable_image_compression() {
        if (get_option('webmasterpro_disable_image_compression', '0') === '1') {
            add_filter('wp_editor_set_quality', function ($quality) {
                return 100;
            });
            add_filter('big_image_size_threshold', '__return_false');
        }
    }

    // Generate media metadata
    public function generate_media_metadata() {
        if (get_option('webmasterpro_generate_media_metadata', '0') === '1') {
            add_action('admin_init', array($this, 'update_existing_media_metadata'));
        }
    }

    // Update existing media metadata
    public function update_existing_media_metadata() {
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'numberposts' => -1,
        ));

        foreach ($attachments as $attachment) {
            if (strpos($attachment->post_mime_type, 'image') !== false) {
                $original_title = $attachment->post_title;
                $formatted_title = ucwords(str_replace('-', ' ', $original_title));
                update_post_meta($attachment->ID, '_wp_attachment_image_alt', $formatted_title);
                update_post_meta($attachment->ID, '_wp_attachment_metadata', array(
                    'image_meta' => array(
                        'caption' => $formatted_title,
                        'title' => $formatted_title,
                        'description' => $formatted_title,
                    ),
                ));
            }
        }
    }
}

// Initialize the class
new webmasterpro_Media_Settings();

/**
 * webmasterpro Page Settings Class
 */
class webmasterpro_Page_Settings {
    
    /**
     * Initialize hooks and settings
     */
    public function __construct() {
        // Hook to save_post to apply page settings
        add_action('save_post', array($this, 'apply_page_settings'), 10, 2);
    }
    
    /**
     * Callback to render the page settings HTML
     */
    public static function render_page_settings() {
        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            update_option('webmasterpro_default_parent_page', isset($_POST['webmasterpro_default_parent_page']) ? $_POST['webmasterpro_default_parent_page'] : '');
            update_option('webmasterpro_default_page_template', isset($_POST['webmasterpro_default_page_template']) ? $_POST['webmasterpro_default_page_template'] : 'default');
            update_option('webmasterpro_page_disable_comments', isset($_POST['webmasterpro_page_disable_comments']) ? '1' : '0');
            update_option('webmasterpro_default_page_author', isset($_POST['webmasterpro_default_page_author']) ? $_POST['webmasterpro_default_page_author'] : '');
    
            // Display a notice
            $notice = 'Settings saved successfully.';
        }
    
        // Get current settings
        $default_parent_page = get_option('webmasterpro_default_parent_page', '');
        $default_page_template = get_option('webmasterpro_default_page_template', 'default');
        $page_disable_comments = get_option('webmasterpro_page_disable_comments', '0');
        $default_page_author = get_option('webmasterpro_default_page_author', '');
        $pages = get_pages();
        $templates = wp_get_theme()->get_page_templates();
        $users = get_users(array('who' => 'authors'));
    
        // Render HTML
        ?>
        <div class="container">
            <div style="max-width: 100%; width: 100%;" class="card">
                <h3>webmasterpro Page Settings</h3>
                <?php if (!empty($notice)) : ?>
                    <div style="margin: 0px;" class="notice notice-success is-dismissible">
                        <p><?php echo $notice; ?></p>
                    </div>
                <?php endif; ?>
                <hr>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_parent_page">Default Parent Page:</label></th>
                            <td>
                                <select id="webmasterpro_default_parent_page" name="webmasterpro_default_parent_page">
                                    <option value="">Select Parent Page</option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($default_parent_page, $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_page_template">Default Page Template:</label></th>
                            <td>
                                <select id="webmasterpro_default_page_template" name="webmasterpro_default_page_template">
                                    <option value="default" <?php selected($default_page_template, 'default'); ?>>Default Template</option>
                                    <?php foreach ($templates as $template_name => $template_filename) : ?>
                                        <option value="<?php echo esc_attr($template_filename); ?>" <?php selected($default_page_template, $template_filename); ?>>
                                            <?php echo esc_html($template_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_page_disable_comments">Disable Comments:</label></th>
                            <td><input type="checkbox" id="webmasterpro_page_disable_comments" name="webmasterpro_page_disable_comments" value="1" <?php checked($page_disable_comments, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_page_author">Default Author:</label></th>
                            <td>
                                <select id="webmasterpro_default_page_author" name="webmasterpro_default_page_author">
                                    <option value="">Select Author</option>
                                    <?php foreach ($users as $user) : ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($default_page_author, $user->ID); ?>>
                                            <?php echo esc_html($user->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="submit">Save Settings</label></th>
                            <td><input class="button" type="submit" class="button-primary" value="Save Changes"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Apply the page settings when a page is saved
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function apply_page_settings($post_id, $post) {
        if ($post->post_type != 'page') {
            return;
        }
    
        // Set default parent page
        $default_parent_page = get_option('webmasterpro_default_parent_page', '');
        if ($default_parent_page && empty($post->post_parent)) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_parent' => $default_parent_page
            ));
        }
    
        // Set default page template
        $default_page_template = get_option('webmasterpro_default_page_template', 'default');
        if ($default_page_template && get_post_meta($post_id, '_wp_page_template', true) == 'default') {
            update_post_meta($post_id, '_wp_page_template', $default_page_template);
        }
    
        // Disable comments
        if (get_option('webmasterpro_page_disable_comments', '0') === '1') {
            remove_post_type_support('page', 'comments');
        }
    
        // Set default author
        $default_page_author = get_option('webmasterpro_default_page_author', '');
        if ($default_page_author) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_author' => $default_page_author
            ));
        }
    }
}

// Instantiate the class
new webmasterpro_Page_Settings();

class webmasterpro_Post_Settings {
    
    /**
     * Initialize hooks and settings
     */
    public function __construct() {
        
        // Hook to save_post to apply post settings
        add_action('save_post', array($this, 'apply_post_settings'), 10, 2);
    }
    
    /**
     * Callback to render the post settings HTML
     */
    public static function render_post_settings() {
        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            update_option('webmasterpro_default_post_category', isset($_POST['webmasterpro_default_post_category']) ? $_POST['webmasterpro_default_post_category'] : '');
            update_option('webmasterpro_default_post_format', isset($_POST['webmasterpro_default_post_format']) ? $_POST['webmasterpro_default_post_format'] : 'standard');
            update_option('webmasterpro_disable_comments', isset($_POST['webmasterpro_disable_comments']) ? '1' : '0');
            update_option('webmasterpro_default_author', isset($_POST['webmasterpro_default_author']) ? $_POST['webmasterpro_default_author'] : '');
    
            // Display a notice
            $notice = 'Settings saved successfully.';
        }
    
        // Get current settings
        $default_post_category = get_option('webmasterpro_default_post_category', '');
        $default_post_format = get_option('webmasterpro_default_post_format', 'standard');
        $disable_comments = get_option('webmasterpro_disable_comments', '0');
        $default_author = get_option('webmasterpro_default_author', '');
        $categories = get_categories(array('hide_empty' => false));
        $post_formats = get_theme_support('post-formats');
        $users = get_users(array('who' => 'authors'));
    
        // Render HTML
        ?>
        <div class="container">
            <div style="max-width: 100%; width: 100%;" class="card">
                <h3>webmasterpro Post Settings</h3>
                <?php if (!empty($notice)) : ?>
                    <div style="margin: 0px;" class="notice notice-success is-dismissible">
                        <p><?php echo $notice; ?></p>
                    </div>
                <?php endif; ?>
                <hr>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_post_category">Default Post Category:</label></th>
                            <td>
                                <select id="webmasterpro_default_post_category" name="webmasterpro_default_post_category">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($default_post_category, $category->term_id); ?>>
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_post_format">Default Post Format:</label></th>
                            <td>
                                <select id="webmasterpro_default_post_format" name="webmasterpro_default_post_format">
                                    <option value="standard" <?php selected($default_post_format, 'standard'); ?>>Standard</option>
                                    <?php if (isset($post_formats[0])) : ?>
                                        <?php foreach ($post_formats[0] as $format) : ?>
                                            <option value="<?php echo esc_attr($format); ?>" <?php selected($default_post_format, $format); ?>>
                                                <?php echo ucfirst($format); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_disable_comments">Disable Comments:</label></th>
                            <td><input type="checkbox" id="webmasterpro_disable_comments" name="webmasterpro_disable_comments" value="1" <?php checked($disable_comments, '1'); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="webmasterpro_default_author">Default Author:</label></th>
                            <td>
                                <select id="webmasterpro_default_author" name="webmasterpro_default_author">
                                    <option value="">Select Author</option>
                                    <?php foreach ($users as $user) : ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($default_author, $user->ID); ?>>
                                            <?php echo esc_html($user->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="submit">Save Settings</label></th>
                            <td><input class="button" type="submit" class="button-primary" value="Save Changes"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Apply the post settings when a post is saved
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function apply_post_settings($post_id, $post) {
        if ($post->post_type != 'post') {
            return;
        }
    
        // Set default post category
        $default_category = get_option('webmasterpro_default_post_category', '');
        if ($default_category && !has_term('', 'category', $post_id)) {
            wp_set_post_categories($post_id, array($default_category));
        }
    
        // Set default post format
        $default_format = get_option('webmasterpro_default_post_format', 'standard');
        set_post_format($post_id, $default_format);
    
        // Disable comments
        if (get_option('webmasterpro_disable_comments', '0') === '1') {
            remove_post_type_support('post', 'comments');
        }
    
        // Set default author
        $default_author = get_option('webmasterpro_default_author', '');
        if ($default_author) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_author' => $default_author
            ));
        }
    }
}

// Instantiate the class
new webmasterpro_Post_Settings();
?>

