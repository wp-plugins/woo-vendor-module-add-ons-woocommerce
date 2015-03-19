<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * Myaccount page customization
 */
include_once WOO_VENDOR__PLUGIN_DIR.'pages/myaccount/wv-myaccount.php';
/*
 * Adding two extra meta key for storing our value.
 * key : woo_order_product_price_prouductid
 * key : woo_order_product_id
 * key : woo_order_vendor_id_prouductid
 * key : woo_order_commision_prouductid
 * Note : We will replace productid to number productid
 * 
 */
include_once WOO_VENDOR__PLUGIN_DIR.'pages/checkout/wv-thankyou.php';
