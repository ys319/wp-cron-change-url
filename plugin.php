<?php

/**
 * @package ys319-change-cron-url
 * @since 0.1.0
 *
 * Plugin Name: Change cron URL
 * Description: Replace cron URL with custom URL.
 * Version: 0.1.0
 * Author: ys319
 * License: MIT
 */

class ChangeCronURL
{
    private $change_cron_url_options;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'change_cron_url_add_plugin_page']);
        add_action('admin_init', [$this, 'change_cron_url_page_init']);
        add_filter('cron_request', [$this, 'replace_cron_request_url'], 10, 2);
    }

    public function replace_cron_request_url($cron_request, $doing_wp_cron)
    {
        $change_cron_url_options = get_option('change_cron_url_option_name');
        $url = $change_cron_url_options['url'];

        if (empty($url)) {
            return $cron_request;
        }

        // Replace home_url with option url.
        $cron_request['url'] = add_query_arg(
            'doing_wp_cron',
            $doing_wp_cron,
            str_replace(home_url(), $url, $cron_request['url']),
        );

        return $cron_request;
    }

    public function change_cron_url_add_plugin_page()
    {
        add_management_page(
            'Change Cron URL',
            'Change Cron URL',
            'manage_options',
            'change-cron-url',
            [$this, 'change_cron_url_create_admin_page']
        );
    }

    public function change_cron_url_create_admin_page()
    {
        $this->change_cron_url_options = get_option('change_cron_url_option_name');
?>
        <div class="wrap">
            <h2>Change Cron URL</h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('change_cron_url_option_group');
                do_settings_sections('change-cron-url-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function change_cron_url_page_init()
    {
        register_setting(
            'change_cron_url_option_group',
            'change_cron_url_option_name',
            [$this, 'change_cron_url_sanitize']
        );

        add_settings_section(
            'change_cron_url_setting_section',
            'Settings',
            [$this, 'change_cron_url_section_info'],
            'change-cron-url-admin'
        );

        add_settings_field(
            'url',
            'URL',
            [$this, 'url_callback'],
            'change-cron-url-admin',
            'change_cron_url_setting_section'
        );
    }

    public function change_cron_url_sanitize($input)
    {
        $sanitary_values = [];
        if (isset($input['url'])) {
            $sanitary_values['url'] = sanitize_text_field($input['url']);
        }

        return $sanitary_values;
    }

    public function change_cron_url_section_info() {}

    public function url_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="change_cron_url_option_name[url]" id="url" value="%s">',
            isset($this->change_cron_url_options['url']) ? esc_attr($this->change_cron_url_options['url']) : ''
        );
    }

    function write_text_file($file_path, $content)
    {
        // Load the Filesystem API if it's not already loaded
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Initialize the WP_Filesystem
        global $wp_filesystem;
        $creds = request_filesystem_credentials('', '', false, false, null);

        // Attempt to initialize WP_Filesystem
        if (!WP_Filesystem($creds)) {
            return 'Failed to initialize WP_Filesystem.';
        }

        // Check if WP_Filesystem is ready and write the content to the file
        if ($wp_filesystem && $wp_filesystem->put_contents($file_path, $content, FS_CHMOD_FILE)) {
            return 'File written successfully!';
        } else {
            return 'Failed to write the file.';
        }
    }
}
