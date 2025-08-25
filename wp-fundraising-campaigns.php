<?php
/**
 * Plugin Name: WP Fundraising Campaigns
 * Plugin URI: https://masjidsolutions.net/
 * Description: Display beautiful fundraising campaigns with progress bars and donation options.
 * Version: 1.0.2
 * Author: MASJIDSOLUTIONS
 * Author URI: https://masjidsolutions.net/
 * Text Domain: wpfc
 * Domain Path: /languages
 * GitHub Plugin URI: SmAshiqur/fundraising-campaigns
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('WPFC_VERSION')) {
    define('WPFC_VERSION', '1.0.0');
}
if (!defined('WPFC_PLUGIN_DIR')) {
    define('WPFC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WPFC_PLUGIN_URL')) {
    define('WPFC_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPFC_PLUGIN_BASENAME')) {
    define('WPFC_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
if (!defined('WPFC_API_URL')) {
    define('WPFC_API_URL', 'https://www.secure-api.net/api/v1/fundraiser-campaign/');
}

// Include required files
require_once WPFC_PLUGIN_DIR . 'includes/class-wpfc-loader.php';
require_once WPFC_PLUGIN_DIR . 'admin/class-wpfc-admin.php';
require_once WPFC_PLUGIN_DIR . 'public/class-wpfc-public.php';

// Run the plugin
function run_wpfc() {
    $plugin = new WPFC_Loader();
    $plugin->run();
}
run_wpfc();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'wpfc_activate');
function wpfc_activate() {
    $options = array(
        'company_name' => 'Demo Mosque',
        'cache_time'   => 3600, // 1 hour
    );
    add_option('wpfc_settings', $options);

    $upload_dir = wp_upload_dir();
    $cache_dir  = $upload_dir['basedir'] . '/wpfc-cache';

    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'wpfc_deactivate');
function wpfc_deactivate() {
    // Nothing special on deactivation
}

/**
 * Uninstall hook
 */
register_uninstall_hook(__FILE__, 'wpfc_uninstall');
function wpfc_uninstall() {
    delete_option('wpfc_settings');

    $upload_dir = wp_upload_dir();
    $cache_dir  = $upload_dir['basedir'] . '/wpfc-cache';

    if (file_exists($cache_dir)) {
        wpfc_delete_directory($cache_dir);
    }
}

/**
 * Helper to recursively delete directory
 */
function wpfc_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!wpfc_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

/**
 * GitHub Updater support using Plugin Update Checker
 */
require WPFC_PLUGIN_DIR . 'lib/plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/SmAshiqur/fundraising-campaigns',
    __FILE__,
    'wp-fundraising-campaigns'
);

// Set branch
$updateChecker->setBranch('main');
$updateChecker->getVcsApi()->enableReleaseAssets();
