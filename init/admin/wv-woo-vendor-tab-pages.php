<?php
/*
 * In woo vendors sub menu under the woocommerce menu
 * Describe the :pages : module
 */
$objFun = new wvm_core_wvfunctions();
//call the settings.
$settingPages = get_option('woovendors_pages');
//print_R($settingPages);
//when plugin activated the setting page have blank so that time, we do the settings page
 //so that it can work when plugin activated.
if(empty($settingPages)){
 $objFun->wp_default_woovendors_pages_setting();
}
//=================== Initialization END =========
 
 //****************** Form submission ***************************
 if(isset($_POST['options'])){
 if($_POST['options'] == "woovendors_pages"){
    
    $redirect = $_POST['redirect'];
    
    $vendorMyaccount = $_POST['vendor_myaccount'];
    $vendorShop      = $_POST['vendor_shop'];
    $vendorOrders    = $_POST['vendor_orders'];
    
    //save in options table with key : woovendors_pages
    $settArr    =  array();
    $settArr['vendor_myaccount']= $vendorMyaccount;
    $settArr['vendor_shop']     = $vendorShop;
    $settArr['vendor_orders']   = $vendorOrders;
    
    //update in table.
    update_option('woovendors_pages', $settArr);
    wp_redirect($redirect, 301);exit;
    
   }
 }
 //****************** Form submission End  ***************************
    $vendorMyaccount = $settingPages['vendor_myaccount'];
    $vendorShop      = $settingPages['vendor_shop'];
    $vendorOrders    = $settingPages['vendor_orders'];

?>
<div class="tab-pages">
    <h2><?php _e('Page setup', 'woo-vendor-module'); ?></h2>
    <!-- pages content structure Start -->
    <form method="POST" action=""> 
        <input type="hidden" name="redirect" value="<?php echo admin_url('admin.php?page=woo_vendors&tab=pages'); ?>">
        <input type="hidden" name="options" value="woovendors_pages">
    <table class="form-table">
        <!-- Myaccount page -->   
        <tr>
            <th><?php _e('Vendor Myaccount', 'woo-vendor-module'); ?> </th>
            <td>
                <?php
                //call all the pages
                
                $listPages = $objFun->wv_wp_page_list();
                if (count($listPages) > 0) {
                    ?>
                    <select name="vendor_myaccount">
                        <option value="">Select Page</option>
                        <?php
                        foreach ($listPages as $page) {
                            ?>
                            <option value="<?php echo $page->ID; ?>" <?php echo ($vendorMyaccount == $page->ID)?"selected='selected'":""; ?>     ><?php echo $page->post_title; ?></option>
                            <?php
                        }
                        ?>
                    </select>    
                    <p class="desc">Please enter this shortcode <code>[woo_vendor_myaccount]</code> on selected page.</p>
                <?php } ?> 
            </td>
        </tr>   
        <!-- Shop page -->   
        <tr>
            <th><?php _e('Vendor shop', 'woo-vendor-module'); ?> </th>
            <td>
                <?php
                //call all the pages
                $objFun = new wvm_core_wvfunctions();
                $listPages = $objFun->wv_wp_page_list();
                if (count($listPages) > 0) {
                    ?>
                    <select name="vendor_shop">
                        <option value="">Select Page</option>
                        <?php
                        foreach ($listPages as $page) {
                            ?>
                            <option value="<?php echo $page->ID; ?>" <?php echo ($vendorShop == $page->ID)?"selected='selected'":""; ?> ><?php echo $page->post_title; ?></option>
                            <?php
                        }
                        ?>
                    </select>    
                    <p class="desc">Please enter this shortcode <code>[woo_vendor_shop]</code> on selected page.</p>
                <?php } ?> 
            </td>
        </tr>

        <!-- order page -->   
        <tr>
            <th><?php _e('Vendor Orders', 'woo-vendor-module'); ?> </th>
            <td>
                <?php
                //call all the pages
                $listPages = $objFun->wv_wp_page_list();
                if (count($listPages) > 0) {
                    ?>
                    <select name="vendor_orders">
                        <option value="">Select Page</option>
                        <?php
                        foreach ($listPages as $page) {
                            ?>
                            <option value="<?php echo $page->ID; ?>" <?php echo ($vendorOrders == $page->ID)?"selected='selected'":""; ?> ><?php echo $page->post_title; ?></option>
                            <?php
                        }
                        ?>
                    </select>    
                    <p class="desc">Please enter this shortcode <code>[woo_vendor_orders]</code> on selected page.</p>
                <?php } ?> 
            </td>
        </tr>
     
         <tr>
            <th>&nbsp; </th>
            <td><input type="submit" name="woosubmit" value="Save Settings" /></td>
        </tr>

    </table>    
  </form>  
    <!-- pages content structure End   -->
</div>    