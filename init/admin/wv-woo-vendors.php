<?php
/*
 * Create submenu of the woocommerce
 */

/*
 * creating an submenu named : woo vendor
 */
add_action('admin_menu', 'wvm_register_woo_vendor_submenu_page', 10);

function wvm_register_woo_vendor_submenu_page() {
    add_submenu_page('woocommerce', 'Woo Vendors', 'Woo Vendors', 'manage_options', 'woo_vendors', 'wvm_woo_vendor_callback');
}

function wvm_woo_vendor_callback() {
    ?>
    <div class="wrap">
        <h2><?php _e('Woo Vendors','woo-vendor-module');?></h2>
        <!-- Creating tabbing -->
        <h2 class="nav-tab-wrapper">
         <?php 
         $activeTab="nav-tab-active";  
            ?>
            <a href="?page=woo_vendors&amp;tab=general" title="General" class="nav-tab 
               <?php if(isset($_REQUEST['tab'])){ echo $activeTab=($_REQUEST['tab']=="general")?"nav-tab-active":"";}else{ echo "nav-tab-active";} ?>
               " id="general-tab">General</a>
            <a href="?page=woo_vendors&amp;tab=pages" title="Pages" class="nav-tab 
               <?php if(isset($_REQUEST['tab'])){ echo $activeTab=($_REQUEST['tab']=="pages")?"nav-tab-active":"";} ?>
               " id="pages-tab">Pages</a>
            <a href="?page=woo_vendors&amp;tab=commission" title="Commission" class="nav-tab 
               <?php if(isset($_REQUEST['tab'])){ echo $activeTab=($_REQUEST['tab']=="commission")?"nav-tab-active":"";} ?>
               " id="products-tab">Commission</a>
               
        </h2>
        <!-- Creating tabbing heading end -->   
        <!-- Tabbing descriptions Start -->
      <?php 
         if(isset($_REQUEST['tab'])){
             if($_REQUEST['tab']=="general"){
                 //adding the general tab.
                 require_once WOO_VENDOR__PLUGIN_DIR . 'init/admin/wv-woo-vendor-tab-general.php';
             }
             
             if($_REQUEST['tab']=="pages"){
                 //adding the pages tab.
                 require_once WOO_VENDOR__PLUGIN_DIR . 'init/admin/wv-woo-vendor-tab-pages.php';
             }
             
            if($_REQUEST['tab']=="commission"){
                 //adding the products tab.
                 require_once WOO_VENDOR__PLUGIN_DIR . 'init/admin/wv-woo-vendor-tab-commission.php';
             }
             
         }else{
           //general tab descriptions here.
             require_once WOO_VENDOR__PLUGIN_DIR . 'init/admin/wv-woo-vendor-tab-general.php';
         }//if tabbing exists.
        ?>
        <!-- Tabbing descriptions END   -->
    </div>
    <?php
}
?>