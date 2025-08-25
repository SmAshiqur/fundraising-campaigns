<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Fundraising_Campaigns
 * @subpackage WP_Fundraising_Campaigns/public
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public side of the site.
 *
 * @package    WP_Fundraising_Campaigns
 * @subpackage WP_Fundraising_Campaigns/public
 * @author     Your Name
 */
class WPFC_Public {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('wpfc-public', WPFC_PLUGIN_URL . 'public/css/wpfc-public.css', array(), WPFC_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script('wpfc-public', WPFC_PLUGIN_URL . 'public/js/wpfc-public.js', array('jquery'), WPFC_VERSION, true);
    }

    /**
     * Display campaigns.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            HTML content.
     */
    public function display_campaigns($atts) {
        // Get settings
        $options = get_option('wpfc_settings');
        
        // Default company name from settings
        $default_company = isset($options['company_name']) ? $options['company_name'] : 'Demo Mosque';
        
        // Get company name from attribute or settings
        $company = !empty($atts['company']) ? sanitize_text_field($atts['company']) : $default_company;
        
        // Get count
        $count = isset($atts['count']) ? intval($atts['count']) : 10;
        
        // Get title
        $title = isset($atts['title']) ? sanitize_text_field($atts['title']) : 
                 (isset($options['display_title']) ? $options['display_title'] : 'Your Support Matters: Donate Today!');
        
        // Get description
        $description = isset($atts['description']) ? wp_kses_post($atts['description']) : 
                      (isset($options['display_description']) ? $options['display_description'] : 'Browse our active campaigns and help us bring positiven change to our communities.');
        
        // Get colors
        $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#25785c';
        $secondary_color = isset($options['secondary_color']) ? $options['secondary_color'] : '#f8b84e';
        
        // Fetch campaigns from API or cache
        $campaigns = $this->get_campaigns($company, $count);
        
        // If no campaigns found
        if (empty($campaigns)) {
            return '<div class="wpfc-error">' . esc_html__('No fundraising campaigns found.', 'wpfc') . '</div>';
        }
        
        // Start output buffer
        ob_start();
        
        // Include template
        include WPFC_PLUGIN_DIR . 'public/partials/wpfc-public-display.php';
        
        // Return content
        return ob_get_clean();
    }

    /**
     * Get campaigns from API or cache.
     *
     * @since    1.0.0
     * @param    string    $company    Company name.
     * @param    int       $count      Number of campaigns to retrieve.
     * @return   array                 Array of campaigns.
     */
    private function get_campaigns($company, $count) {
        // Get settings
        $options = get_option('wpfc_settings');
        $cache_time = isset($options['cache_time']) ? intval($options['cache_time']) : 3600;
        
        // Create cache key
        $cache_key = 'wpfc_' . sanitize_title($company) . '_' . $count;
        
        // Try to get from cache
        $campaigns = $this->get_cache($cache_key);
        
        // If not in cache, fetch from API
        if (false === $campaigns) {
            $campaigns = $this->fetch_campaigns($company, $count);
            
            // If successful, save to cache
            if (!empty($campaigns) && !is_wp_error($campaigns)) {
                $this->set_cache($cache_key, $campaigns, $cache_time);
            }
        }
        
        return $campaigns;
    }

    /**
     * Fetch campaigns from API.
     *
     * @since    1.0.0
     * @param    string    $company    Company name.
     * @param    int       $count      Number of campaigns to retrieve.
     * @return   array                 Array of campaigns.
     */
    private function fetch_campaigns($company, $count) {
        // Prepare URL with company name and count
        $api_url = WPFC_API_URL . urlencode($company);
        
        // Make API request
        $response = wp_remote_get($api_url);
        
        // Check if request was successful
        if (is_wp_error($response)) {
            return array();
        }
        
        // Get response body
        $body = wp_remote_retrieve_body($response);
        
        // Decode JSON
        $data = json_decode($body, true);
        
        // Check if data is valid
        if (!is_array($data) || empty($data)) {
            return array();
        }
        
        // Limit to requested count
        if (count($data) > $count) {
            $data = array_slice($data, 0, $count);
        }
        
        return $data;
    }

    /**
     * Get data from cache.
     *
     * @since    1.0.0
     * @param    string    $key    Cache key.
     * @return   mixed             Cached data or false if not found.
     */
    private function get_cache($key) {
        $upload_dir = wp_upload_dir();
        $cache_file = $upload_dir['basedir'] . '/wpfc-cache/' . $key . '.json';
        
        // Check if cache file exists
        if (!file_exists($cache_file)) {
            return false;
        }
        
        // Check if cache has expired
        $options = get_option('wpfc_settings');
        $cache_time = isset($options['cache_time']) ? intval($options['cache_time']) : 3600;
        
        if ((filemtime($cache_file) + $cache_time) < time()) {
            return false;
        }
        
        // Get cache data
        $cache_data = file_get_contents($cache_file);
        
        // Decode JSON
        return json_decode($cache_data, true);
    }

    /**
     * Save data to cache.
     *
     * @since    1.0.0
     * @param    string    $key      Cache key.
     * @param    mixed     $data     Data to cache.
     * @param    int       $time     Cache time in seconds.
     * @return   bool                True on success, false on failure.
     */
    private function set_cache($key, $data, $time) {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wpfc-cache';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        $cache_file = $cache_dir . '/' . $key . '.json';
        
        // Save cache data
        return file_put_contents($cache_file, json_encode($data));
    }

    /**
     * Format percentage for display.
     *
     * @since    1.0.0
     * @param    float    $current    Current amount.
     * @param    float    $target     Target amount.
     * @return   string               Formatted percentage.
     */
    public function format_percentage($current, $target) {
        if ($target <= 0) {
            return '0%';
        }
        
        $percentage = ($current / $target) * 100;
        return number_format($percentage, 2) . '%';
    }
}