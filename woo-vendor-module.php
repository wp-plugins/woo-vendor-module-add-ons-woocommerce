<?php
/*
 * Plugin Name:Woo Vendor Module
 * Plugin uri:
 * Author:shankaranand12
 * Author uri:http://about.me/shankaranand
 * Description:Woo vendor is used with woocommerce plugin for creating an vendor system.It also works for product management with vendor ,provide vendor shop with unique URL as well as manage the commission of vendor.Administrator have full power to prevent any vendor as well as allow it's shop on own website.
 * Version:1.0
 * Text Domain:woo-vendor-module
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOO_VENDOR__PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOO_VENDOR__PLUGIN_DIR', plugin_dir_path(__FILE__));

/*
 * Befor using plugin services, we need to check that woocommerce is allready activated or not.
 */
if (!class_exists('WooCommerce')) :

    class wvm_check_woocommerce_activate {

        public function __construct() {

            register_activation_hook(__FILE__, array($this, 'wvm_plugin_resource_activation'));
        }

        function wvm_plugin_resource_activation() {

            // Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
            deactivate_plugins(plugin_basename(__FILE__));
            echo '<p>Author Request :Woocommerce Plugin Needed</p>';
            wp_die('This plugin requires woocommerce plugin so please first install woocommerce plugin then activate it.  Sorry about that.');
        }

    }

//call this class
    new wvm_check_woocommerce_activate();
endif;

/*
 * After verify we will start the plugin work .
 */

class wvm_activated_with_woocommerce {

    public function __construct() {
        ob_start();
        add_action('init', array($this, 'wvm_initialize_plugin_key_file'));
    }

    function wvm_initialize_plugin_key_file() {
        /*
         * Woo vendor plugin functions 
         */
        require_once WOO_VENDOR__PLUGIN_DIR . 'include/wv-functions.php';

        /*
         * initialization plugin.
         */
        require_once WOO_VENDOR__PLUGIN_DIR . 'init/wv-init.php';
        /*
         * customize pages
         */
        include_once WOO_VENDOR__PLUGIN_DIR . 'pages/wv-pages.php';
    }

}

new wvm_activated_with_woocommerce(); 