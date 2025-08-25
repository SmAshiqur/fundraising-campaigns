<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Fundraising_Campaigns
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WP_Fundraising_Campaigns
 * @author     Your Name
 */
class WPFC_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Already loaded in main plugin file
    }
    public function admin_page() {
        echo '<div class="wrap"><h1>Fundraising Campaigns</h1><p>Welcome to the admin panel.</p></div>';
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WPFC_Admin();
        
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new WPFC_Public();
        
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Register all shortcodes.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_shortcodes() {
        add_shortcode('wpfc_campaigns', array($this, 'campaigns_shortcode'));
    }

    /**
     * Shortcode callback function.
     *
     * @since    1.0.0
     * @access   public
     * @param    array    $atts    Shortcode attributes.
     * @return   string            Shortcode output.
     */
    public function campaigns_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'company' => '',
                'count' => 10,
                'title' => 'Your Support Matters: Donate Today!',
                'description' => 'Browse our active campaigns and help us bring positive change to our communities.',
            ),
            $atts,
            'wpfc_campaigns'
        );
        
        // Create an instance of the public class
        $public = new WPFC_Public();
        
        // Return the campaigns HTML
        return $public->display_campaigns($atts);
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $hook             The name of the WordPress action that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the action is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    private function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $hook             The name of the WordPress filter that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the filter is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    private function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array     $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string    $hook             The name of the WordPress filter that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the filter is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         The priority at which the function should be fired.
     * @param    int       $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                        The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }

    /**
     * Shortcode handler for [wpfc_campaigns]
     */
        public function wpfc_campaigns_shortcode($atts) {
            // Extract shortcode attributes
            $atts = shortcode_atts(array(
                'company' => '',
                'count' => 10,
                'title' => '',
                'description' => ''
            ), $atts, 'wpfc_campaigns');
            
            // Get settings from options
            $settings = get_option('wpfc_settings', array());
            
            // Get company from URL parameter if provided
            $url_company = isset($_GET['company']) ? sanitize_text_field($_GET['company']) : '';
            
            // Priority: 1. URL parameter, 2. Shortcode attribute, 3. Admin setting
            $company_name = '';
            if (!empty($url_company)) {
                $company_name = $url_company; // URL parameter has highest priority
            } elseif (!empty($atts['company'])) {
                $company_name = $atts['company']; // Shortcode attribute second priority
            } elseif (!empty($settings['company_name'])) {
                $company_name = $settings['company_name']; // Admin setting third priority
            }
            
            // Define colors for the template
            $primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#25785c';
            $secondary_color = isset($settings['secondary_color']) ? $settings['secondary_color'] : '#f8b84e';
            
            // Start output buffering
            ob_start();
            
            // Include template file
            include WPFC_PLUGIN_DIR . 'public/partials/wpfc-public-display.php';
            
            // Return the buffered content
            return ob_get_clean();
        }
}

