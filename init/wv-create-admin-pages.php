<?php

/*
 * This file creates the following pages in admin
 * i)- vendor myaccount       -[woovendor_vendor_myaccount]
 * ii)- vendor order history  -[woovendor_vendor_order_history]
 * iii)-ter
 */
//creating an object of the functions class.

$funObj = new wvm_core_wvfunctions();

$pageName              = "Vendor MyAccount";
$pageName_content      = '[woo_vendor_myaccount]';

$pageName_child        = "Vendor Order History";
$pageName_child_content= "[woo_vendor_orders]";

$pageName_child2       ="Vendor Shop";
$pageName_child_content2="[woo_vendor_shop]";

/*
 * Befor creating an page, we need to check that page is allready created or not.
 * page created     : 1
 * Page not created : 0
 */
$postid = 0;
$result = 0;
$result = $funObj->wv_check_page_exists($pageName);

if ($result == 0) {
    $postid = $funObj->wv_create_post($pageName, $pageName_content, "page");

    if ($postid != 0) {
        $child_postid = $funObj->wv_create_post($pageName_child, $pageName_child_content, "page");
        $funObj->wv_make_child_page($child_postid,$postid);
    }
    //create an order page
    if ($postid != 0) {
        $child_postid2 = $funObj->wv_create_post($pageName_child2, $pageName_child_content2, "page");
        $funObj->wv_make_child_page($child_postid2,$postid);
    }
    
}
//create an child page if parent page created.
//update the info on the database.
//key: is_create_page     :
$funObj->wv_update_plugin_action_info('is_create_page',1);
//when plugin activated the setting page have blank so that time, we do the settings page
 //so that it can work when plugin activated.
     $funObj->wp_default_woovendors_pages_setting();
?>