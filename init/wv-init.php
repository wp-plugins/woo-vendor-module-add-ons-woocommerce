<?php
/* 
 *When plugin will activated then it will creates all the modules in admin as well as frontend which 
 * is needed for this plugin. 
 */

class wvm_plugin_init_action{

    //constructor 
    function __construct() {
     
   //called the init level action
    wvm_plugin_init_action::wvm_init_level_action();     
    
   //below functions will called when in admin .
    if(is_admin()){
    wvm_plugin_init_action::wvm_init_admin_level_action_continue();      
    }
    
   wvm_plugin_init_action::wvm_init_frontend_level_action_conditional();      
   
    /*
     * When any order made then an email is fired to the vendor also .
     * Ref: http://stackoverflow.com/questions/23091169/woocommerce-bcc-on-order-email-to-admin
     */
     add_filter( 'woocommerce_email_headers',array($this,'wvm_add_bcc_to_wc_admin_new_order', 10, 3 ));
    
   
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
    function wvm_init_level_action(){
    
        $plugin_key_actions = get_option('plugin_key_actions');
        $is_role_created    = $plugin_key_actions['is_role_created'];
        
       if(empty($is_role_created) OR $is_role_created == 0){
       /*
        *Create two types of the user roles.
        * 1: pending vendor
        * 2: vendor 
        */
       include_once WOO_VENDOR__PLUGIN_DIR.'init/wv-create-userRoles-capabilities.php';
       }
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
       
    }
  //END of the :init_level_action :  
    /*
     * When any order made then an email is fired to the vendor also .
     * Ref: http://stackoverflow.com/questions/23091169/woocommerce-bcc-on-order-email-to-admin
     */
   function wvm_add_bcc_to_wc_admin_new_order( $headers = '', $id = '', $wc_email = array() ) {
            if ( $id == 'new_order' ) {
                $headers .= "Bcc:maurya.ets@gmail.com\r\n"; // replace my_personal@email.com with your email
            }
            return $headers;
        }
        
    /*
     * This function create an submenu in admin = Woocommerce => woo vendor 
     * It will execute when admin will called.
     */
    function wvm_init_admin_level_action_continue(){
        //our functions will execute when anyone come into the admin.
        if(is_admin()){
            /*
             * create a submenu :woo vendor: which is used for settings.
             */
            include_once WOO_VENDOR__PLUGIN_DIR.'init/admin/wv-woo-vendors.php';            
        
      /*
       * Another actions when product menu under product post type of woocoommerce is called.
       */  
         if(isset($_REQUEST['post_type'])){
             if($_REQUEST['post_type']=="product"){
               /*
                * Adding one extra column in wordpress admin product post type
                * Product => vendor (column Name)
                */
               include_once WOO_VENDOR__PLUGIN_DIR.'init/admin/wv-extra_column_postType_product.php';
             }
         }//when product menu is called.   
         
        }
        
    }
  /*
   * functions which executes on frontend when active page is called.
   * like: myvendor account page is called then it's shortcode is called.
   */  
    function wvm_init_frontend_level_action_conditional(){
        
        include_once WOO_VENDOR__PLUGIN_DIR.'init/shortcode/woo-vendor-shortcode.php';        
   
        $pages    = get_option('woovendors_pages');
        $shopid   = $pages['vendor_shop'];
        $myaccount= $pages['vendor_myaccount'];
        $orders   = $pages['vendor_orders'];

           /*
            * Shortcode for shop : [woo_vendor_shop]
            * it is used to show the shop by vendor
            */
          add_shortcode('woo_vendor_shop', array('wvm_wooVendorShortcode', 'wvm_callback_woo_vendor_shop'));
            /*
            * Shortcode for vendor Myaccount : [woo_vendor_myaccount]
            * it is used for vendor myaccount.
            */
         
           add_shortcode('woo_vendor_myaccount', array('wvm_wooVendorShortcode', 'wvm_callback_woovendor_vendor_myaccount'));
           /*
            * Shortcode for vendor OrderHistory :[woo_vendor_orders]
            * It is used in vendor order history page
            */
           add_shortcode('woo_vendor_orders', array('wvm_wooVendorShortcode', 'wvm_callback_woovendor_vendor_orders'));
       
    }
  //end of the function : init_frontend_level_action_conditional  
}

new wvm_plugin_init_action();

/*
 * Vendor shop product
 */
?>