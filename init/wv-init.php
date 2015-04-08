<?php
/* 
 *When plugin will activated then it will creates all the modules in admin as well as frontend which 
 * is needed for this plugin. 
 */

class wvm_plugin_init_action{

    //constructor 
    function __construct() {
     
    
   //below functions will called when in admin .
    if(is_admin()){
    wvm_plugin_init_action::wvm_init_admin_level_action_continue();      
    }
    
   wvm_plugin_init_action::wvm_init_frontend_level_action_conditional();      
   
    /*
     * When any order made then an email is fired to the vendor also .
     * Ref: http://stackoverflow.com/questions/23091169/woocommerce-bcc-on-order-email-to-admin
     * New Ref: 06apr: http://stackoverflow.com/questions/21700372/send-email-to-the-customer-on-order-complete-in-woocommerce
     */
     add_filter( 'woocommerce_email_headers',array($this,'wvm_add_bcc_to_wc_admin_new_order', 10, 3 ));
    //06apr
     add_action( 'woocommerce_payment_complete', 'wvm_order_completed' );
   
    }
    //06apr :sending email to the vendor.
   function order_completed( $order_id ) {
  
    $order = new WC_Order( $order_id );
    $to_email = $order["billing_address"];
    
    //adding the vendor email to the css
    @session_start();
    $vendor_email_arr = json_decode($_SESSION['VENDOR_EMAILS']);
    if(count($vendor_email_arr)>0){
        foreach($vendor_email_arr as $email){
    $headers = 'Cc: '.$email.' <'.$email.'>' . "\r\n";        
        }
    }
    
    wp_mail($to_email, 'subject', 'message', $headers );

  }
   
    /*
     * When any order made then an email is fired to the vendor also .
     * Ref: http://stackoverflow.com/questions/23091169/woocommerce-bcc-on-order-email-to-admin
     */
   function wvm_add_bcc_to_wc_admin_new_order( $headers = '', $id = '', $wc_email = array() ) {
       
       @session_start();
       
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