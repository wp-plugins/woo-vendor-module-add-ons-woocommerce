<?php

/*
 * This file run when plugin activated .
 * Create two types of the user roles.
 * 1: Pending Vendor :pending_vendor
 * 2: Vendor         :vendor
 */
//creating an object from user.
$objfun = new wvm_core_wvfunctions();

/*
 * It is used when anyone registeration from front end then we will assign it.
 */
$capability_pendingvendor = array('read' => true, 'level_0' => true);

$capability_vendor = array(
    'read' => true,
    'upload_files' => true,
    'assign_product_terms' => TRUE,
    'edit_products' => TRUE,
    'manage_product' => true,
    'view_woocommerce_reports' => true,
    'edit_published_products'=>TRUE
);


$objfun->wv_create_user_roles("Pending Vendor", "wvm_role_pending_vendor", $capability_pendingvendor);

$objfun->wv_create_user_roles("Vendor", "wvm_role_vendor", $capability_vendor);
//update the info on the database.
//key: is_role_created    :
$objfun->wv_update_plugin_action_info('is_role_created',1);
?>