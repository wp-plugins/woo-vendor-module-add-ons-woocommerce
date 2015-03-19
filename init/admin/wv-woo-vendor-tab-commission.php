<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//change commission status functionality .

if(isset($_REQUEST['action'])){
    
    $oid = $_REQUEST['oid'];
    $pid = $_REQUEST['pid'];
    $sts = $_REQUEST['sts'];
    
    $todayDate = date("Y-m-d H:i:s");
    //update post meta 
    update_post_meta($oid,'woo_commision_status_'.$pid,$sts);
    update_post_meta($oid,'woo_commision_date_'.$pid,$todayDate);
    
    wp_redirect(admin_url("admin.php?page=woo_vendors&tab=commission"));exit;
}



?>
<div class="tab-commission wrap">

    <h2><?php _e('Commission Settings', 'wooVendor'); ?></h2>
    <?php
    //call all the pages
    $objFun = new wvm_core_wvfunctions();
    ?>
    <table class="wp-list-table widefat fixed commissions" border="2">
        <?php
        //header list
        echo $objFun->wv_commission_table_heading_list("thead");
        //footer list
        echo $objFun->wv_commission_table_heading_list("tfoot");
        //message body describing here.

        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

         //get_query_var is not working so I 'm using this method.
        if(isset($_REQUEST['paged'])){
            $paged = $_REQUEST['paged'];
        }
        
        
        $query1Args = array(
            'post_type' => 'shop_order',
            'post_status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'),
            'paged'=>$paged
        );

        $query1 = new WP_Query($query1Args);
        if ($query1->have_posts()):
            while ($query1->have_posts()): $query1->the_post();

                $objFun->wv_commission_table_record(get_the_ID());

            endwhile;
            //woocommerce pagination.
             //pagination environment
                $maxpage = $query1->max_num_pages;
                echo (new wvm_wooVendorShortcode)->wv_woo_vendor_pagination($paged, $maxpage);
                
        else:
            echo '<tr><td colSpan="6">Sorry,No order take here.</td></tr>';
        endif;
        wp_reset_query();
        ?>   
    </table>
</div>    