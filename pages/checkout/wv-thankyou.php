<?php
/*
 * Thank you page
 */
/*
 * Adding two extra meta key for storing our value.
 * key : woo_order_product_price_prouductid
 * key : woo_order_product_id
 * key : woo_order_vendor_id_prouductid
 * key : woo_order_commision_prouductid
 * Note: We will replace productid to number productid
 */
add_action('woocommerce_thankyou', 'wvm_create_extra_metakey_after_purchase');

function wvm_create_extra_metakey_after_purchase($order_id) {
    global $woocommerce;
// Lets grab the order
    $order = new WC_Order($order_id);

    /**
     * Put your tracking code here
     * You can get the order total etc e.g. $order->get_order_total();
     * */
    if (count($order) > 0) {

        $items = $order->get_items();
        /*
         * check first = variation id = now if variation id !=0 then variable product have been purchased
         * otherwise , get the product id and surely product have been purchased.
         */
        if (count($items) > 0) {
            $inc = 1;
            foreach ($items as $item) {
                $product_name = $item['name'];
                $product_id = $item['product_id'];
                $product_variation_id = $item['variation_id'];

                $product_purchase_id = 0;
                if ($product_variation_id != 0) {
                    $product_purchase_id = $product_variation_id;
                } else {
                    $product_purchase_id = $product_id;
                }

                $vendor_id = 0;
                /*
                 * Now we will get the author id and if author is vendor type then we will include the above discussed
                 * key other wise leave it .
                 */
                $authorid = wv_thankyou_get_authorid_by_postid($product_purchase_id);
                $funObj = new wvm_core_wvfunctions();
                $userRoles = $funObj->wv_get_user_roles_by_userid($authorid);
                /* 11= productid
                 * key : woo_order_product_price_11
                 * key : woo_order_product_id_11
                 * key : woo_order_vendor_id_11
                 * key : woo_order_vendor_id         : when we trace all the order by vendor then it will be uses
                 * key : woo_order_commision_11
                 */
                if ($userRoles == "vendor") {

                    $pprice = get_post_meta($product_purchase_id, '_price', TRUE);
                    
                    //Commission will be % of product price.
                    
                    $commission = ($pprice*10)/100; //it will be in % i.e 10% .
                    
                    

                    //In every loop,I need for creating an new key 
                    $pid = $product_purchase_id;
                    add_post_meta($order_id, 'woo_order_product_price_' . $pid, $pprice);
                    add_post_meta($order_id, 'woo_order_product_id_' . $pid, $product_purchase_id);
                    add_post_meta($order_id, 'woo_order_vendor_id_' . $pid, $authorid);
                    add_post_meta($order_id, 'woo_order_vendor_id', $authorid);
                    /*
                     * 
                     */
                    add_post_meta($order_id, 'woo_order_commision_' . $pid, $commission);
                    //extra key which provide information i.e how many vendor products have been sold of this order.
                    update_post_meta($order_id, 'woo_order_total_vendor_products', $inc);
                    ++$inc;
                }//user roles vendor
            }//foreach.
        }//count condition.    
    }//condition if 
}

/*
 * get author id by post id
 */

function wv_thankyou_get_authorid_by_postid($postid) {
    global $wpdb;
    $sql_authorid = "SELECT * FROM " . $wpdb->prefix . "posts WHERE ID=$postid";
    $arr_authorid = $wpdb->get_row($sql_authorid);
    $authorid = $arr_authorid->post_author;
    return $authorid;
}

?>