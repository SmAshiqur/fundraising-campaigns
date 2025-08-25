<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Fundraising_Campaigns
 * @subpackage WP_Fundraising_Campaigns/admin
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 *
 * @package    WP_Fundraising_Campaigns
 * @subpackage WP_Fundraising_Campaigns/admin
 * @author     Your Name
 */
class WPFC_Admin {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($hook) {
        // Only load assets on plugin settings page
        if ('settings_page_wpfc-settings' !== $hook) {
            return;
        }

        wp_enqueue_style('wpfc-admin', WPFC_PLUGIN_URL . 'admin/css/wpfc-admin.css', array(), WPFC_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook) {
        // Only load assets on plugin settings page
        if ('settings_page_wpfc-settings' !== $hook) {
            return;
        }

        wp_enqueue_script('wpfc-admin', WPFC_PLUGIN_URL . 'admin/js/wpfc-admin.js', array('jquery'), WPFC_VERSION, false);
    }

    /**
     * Add an options page under the Settings submenu
     * 
     * @since  1.0.0
     */
    public function add_admin_menu() {
        add_options_page(
            __('Fundraising Campaigns Settings', 'wpfc'),
            __('Fundraising Campaigns', 'wpfc'),
            'manage_options',
            'wpfc-settings',
            array($this, 'display_options_page')
        );
    }

    /**
     * Register settings for the admin area.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'wpfc_settings',
            'wpfc_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'wpfc_section_api',
            __('API Settings', 'wpfc'),
            array($this, 'section_api_callback'),
            'wpfc-settings'
        );

        add_settings_field(
            'company_name',
            __('Company Name', 'wpfc'),
            array($this, 'company_name_callback'),
            'wpfc-settings',
            'wpfc_section_api'
        );

        add_settings_field(
            'cache_time',
            __('Cache Duration (seconds)', 'wpfc'),
            array($this, 'cache_time_callback'),
            'wpfc-settings',
            'wpfc_section_api'
        );

        add_settings_section(
            'wpfc_section_display',
            __('Display Settings', 'wpfc'),
            array($this, 'section_display_callback'),
            'wpfc-settings'
        );

        add_settings_field(
            'display_title',
            __('Default Title', 'wpfc'),
            array($this, 'display_title_callback'),
            'wpfc-settings',
            'wpfc_section_display'
        );

        add_settings_field(
            'display_description',
            __('Default Description', 'wpfc'),
            array($this, 'display_description_callback'),
            'wpfc-settings',
            'wpfc_section_display'
        );

        add_settings_field(
            'primary_color',
            __('Primary Color', 'wpfc'),
            array($this, 'primary_color_callback'),
            'wpfc-settings',
            'wpfc_section_display'
        );

        add_settings_field(
            'secondary_color',
            __('Secondary Color', 'wpfc'),
            array($this, 'secondary_color_callback'),
            'wpfc-settings',
            'wpfc_section_display'
        );
    }

    /**
     * Sanitize the settings.
     *
     * @since    1.0.0
     * @param    array    $input    The input settings.
     * @return   array              The sanitized settings.
     */
    public function sanitize_settings($input) {
        $output = array();
        
        // Company name
        if (isset($input['company_name'])) {
            $output['company_name'] = sanitize_text_field($input['company_name']);
        }

        // Cache time
        if (isset($input['cache_time'])) {
            $output['cache_time'] = absint($input['cache_time']);
        }

        // Display title
        if (isset($input['display_title'])) {
            $output['display_title'] = sanitize_text_field($input['display_title']);
        }

        // Display description
        if (isset($input['display_description'])) {
            $output['display_description'] = sanitize_textarea_field($input['display_description']);
        }

        // Primary color
        if (isset($input['primary_color'])) {
            $output['primary_color'] = sanitize_hex_color($input['primary_color']);
        }

        // Secondary color
        if (isset($input['secondary_color'])) {
            $output['secondary_color'] = sanitize_hex_color($input['secondary_color']);
        }

        // Clear cache when settings are updated
        $this->clear_cache();

        return $output;
    }

    /**
     * Clear the cache.
     *
     * @since    1.0.0
     */
    private function clear_cache() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wpfc-cache';
        
        if (file_exists($cache_dir)) {
            foreach (scandir($cache_dir) as $file) {
                if ($file != '.' && $file != '..') {
                    unlink($cache_dir . '/' . $file);
                }
            }
        }
    }

    /**
     * Render the options page.
     *
     * @since    1.0.0
     */
    public function display_options_page() {
        ?>
        <div class="wrap">
            <h2><?php echo esc_html__('Fundraising Campaigns Settings', 'wpfc'); ?></h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('wpfc_settings');
                do_settings_sections('wpfc-settings');
                submit_button();
                ?>
            </form>

            <div class="wpfc-admin-shortcode-section">
                <h3><?php echo esc_html__('How to Use', 'wpfc'); ?></h3>
                <p><?php echo esc_html__('Use the following shortcode to display fundraising campaigns on your website:', 'wpfc'); ?></p>
                <code>[wpfc_campaigns]</code>
                
                <h4><?php echo esc_html__('Available Shortcode Attributes:', 'wpfc'); ?></h4>
                <ul>
                    <li><code>company</code> - <?php echo esc_html__('Override the company name (default: uses the value from settings)', 'wpfc'); ?></li>
                    <li><code>count</code> - <?php echo esc_html__('Number of campaigns to display (default: 10)', 'wpfc'); ?></li>
                    <li><code>title</code> - <?php echo esc_html__('Custom section title (default: "Your Support Matters: Donate Today!")', 'wpfc'); ?></li>
                    <li><code>description</code> - <?php echo esc_html__('Custom section description (default: "Browse our active campaigns and ....")', 'wpfc'); ?></li>
                </ul>
                
                <h4><?php echo esc_html__('Example:', 'wpfc'); ?></h4>
                <code>[wpfc_campaigns company="Demo Mosque" count="4" title="Our Fundraising Campaigns" description="Help us make a difference in our community"]</code>
            </div>
        </div>
        <?php
    }

    /**
     * Render the API section.
     *
     * @since    1.0.0
     */
    public function section_api_callback() {
        echo '<p>' . esc_html__('Configure the API settings for Fundraising Campaigns.', 'wpfc') . '</p>';
    }

    /**
     * Render the company name field.
     *
     * @since    1.0.0
     */
    public function company_name_callback() {
        $options = get_option('wpfc_settings');
        $company_name = isset($options['company_name']) ? $options['company_name'] : '';
        
        echo '<input type="text" id="company_name" name="wpfc_settings[company_name]" value="' . esc_attr($company_name) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Enter the company name used to fetch data from the API (e.g., "Demo Mosque").', 'wpfc') . '</p>';
    }

    /**
     * Render the cache time field.
     *
     * @since    1.0.0
     */
    public function cache_time_callback() {
        $options = get_option('wpfc_settings');
        $cache_time = isset($options['cache_time']) ? $options['cache_time'] : 3600;
        
        echo '<input type="number" id="cache_time" name="wpfc_settings[cache_time]" value="' . esc_attr($cache_time) . '" class="small-text" />';
        echo '<p class="description">' . esc_html__('Time in seconds to cache API results (default: 3600 = 1 hour).', 'wpfc') . '</p>';
    }

    /**
     * Render the display section.
     *
     * @since    1.0.0
     */
    public function section_display_callback() {
        echo '<p>' . esc_html__('Configure the display settings for Fundraising Campaigns.', 'wpfc') . '</p>';
    }

    /**
     * Render the display title field.
     *
     * @since    1.0.0
     */
    public function display_title_callback() {
        $options = get_option('wpfc_settings');
        $display_title = isset($options['display_title']) ? $options['display_title'] : 'Your Support Matters: Donate Today!';
        
        echo '<input type="text" id="display_title" name="wpfc_settings[display_title]" value="' . esc_attr($display_title) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Default title to display above the campaigns.', 'wpfc') . '</p>';
    }

    /**
     * Render the display description field.
     *
     * @since    1.0.0
     */
    public function display_description_callback() {
        $options = get_option('wpfc_settings');
        $display_description = isset($options['display_description']) ? $options['display_description'] : 'Browse our active campaigns and help us bring positive change to our communities.';
        
        echo '<textarea id="display_description" name="wpfc_settings[display_description]" class="large-text" rows="3">' . esc_textarea($display_description) . '</textarea>';
        echo '<p class="description">' . esc_html__('Default description to display below the title.', 'wpfc') . '</p>';
    }

    /**
     * Render the primary color field.
     *
     * @since    1.0.0
     */
    public function primary_color_callback() {
        $options = get_option('wpfc_settings');
        $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#25785c';
        
        echo '<input type="color" id="primary_color" name="wpfc_settings[primary_color]" value="' . esc_attr($primary_color) . '" />';
        echo '<p class="description">' . esc_html__('Primary color for buttons and progress bars.', 'wpfc') . '</p>';
    }

    /**
     * Render the secondary color field.
     *
     * @since    1.0.0
     */
    public function secondary_color_callback() {
        $options = get_option('wpfc_settings');
        $secondary_color = isset($options['secondary_color']) ? $options['secondary_color'] : '#f8b84e';
        
        echo '<input type="color" id="secondary_color" name="wpfc_settings[secondary_color]" value="' . esc_attr($secondary_color) . '" />';
        echo '<p class="description">' . esc_html__('Secondary color for video buttons and accents.', 'wpfc') . '</p>';
    }
}