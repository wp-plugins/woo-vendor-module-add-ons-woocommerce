<?php
/*
 * Plugin Name:Woo Vendor Module
 * Plugin uri:
 * Author:shankaranand12
 * Author uri:https://shankaranandmaurya.wordpress.com/woo-vendor-module/
 * Description:Woo vendor is used with woocommerce plugin for creating an vendor system.It also works for product management with vendor ,provide vendor shop with unique URL as well as manage the commission of vendor.Administrator have full power to prevent any vendor as well as allow it's shop on own website.
 * Version:1.2
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
       /*
        * When plugin activated then initialize the some key feature like
        * 1: user roles,
        * 2: check the registration form on myaccount page in woocommerce.
        */
        register_activation_hook(__FILE__, array($this, 'wvm_initialize_key_feature'));
                    
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
    
     /*
     * Init level actions will handle the two files.
     * 1:Create user roles and capability
     * 2:Create admin pages
     * Note:Befor adding the actions file,I need to check it from Database and if not created then create it.
     * 0 or "" => means not created.
     * 1       => created.
     * Key : plugin_key_actions
     * Sub key :: is_role_created    :
     *          : is_create_page     :
     */
   
  function wvm_initialize_key_feature(){
      
      $this->wvm_initialize_plugin_key_file();
      
      $plugin_key_actions = get_option('plugin_key_actions');
        $is_role_created    = $plugin_key_actions['is_role_created'];
        
      // if(empty($is_role_created) OR $is_role_created == 0){
       /*
        *Create two types of the user roles.
        * 1: pending vendor
        * 2: vendor 
        */
       include_once WOO_VENDOR__PLUGIN_DIR.'init/wv-create-userRoles-capabilities.php';
      // }
       //for page created.
      $is_create_page     = $plugin_key_actions['is_create_page'];
       if(empty($is_create_page) OR $is_create_page == 0){
           /*
            * create three pages,
            * i)- vendor myaccount       -[woovendor_vendor_myaccount]
            * ii)- vendor order history  -[woovendor_vendor_order_history]
            * iii)- Vendor shop          -[woo_vendor_shop]   
            */
           include_once WOO_VENDOR__PLUGIN_DIR.'init/wv-create-admin-pages.php';           
       }
     /*
      * When plugin activated then it will check the registration form which is established in myaccount page.
      * Woocommerce => settings => option(Enable registration on the "My Account" page)
      */  
     update_option('woocommerce_enable_myaccount_registration','yes');
  }  

}

new wvm_activated_with_woocommerce(); 