<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class wvm_wooVendorShortcode {

    function __construct() {
        //check session is started or not and start .
        wvm_wooVendorShortcode::wv_session_started();
    }

    /*
     * verify,session is started or not and if not then started.
     */

    function wv_session_started() {
        if ((new wvm_core_wvfunctions)->wv_is_session_started() === FALSE) {
            session_start();
        }
    }

    /*
     * Shortcode for shop : [woo_vendor_shop]
     * it is used to show the shop by vendor
     */

   static function wvm_callback_woo_vendor_shop($atts, $content = "") {
        $output = "";
        $vendor_username ="";
        $objFun = new wvm_core_wvfunctions();
        
        if(isset($_REQUEST['vendor'])){
        $vendor_username = $_REQUEST['vendor'];
        }
        
        $userinfo = get_user_by('login', $vendor_username);


        if (!empty($userinfo)) {
            $vendor_userid = $userinfo->ID;
            $output = (new wvm_wooVendorShortcode)->wv_vendor_shop_html($vendor_userid, $vendor_username);
        } else {
            $output = "<p>Vendor shop URL is not valid so please contact the vendor and if still not resolved then please inform the administrator.</p>";
        }

        return $output;
    }

    /*
     * Myaccount shop [woovendor_vendor_myaccount]
     */

   static function wvm_callback_woovendor_vendor_myaccount($atts, $content = "") {

        //output variables.
        $output = "";

        $objFun = new wvm_core_wvfunctions();
        $userLogin = $objFun->wv_is_user_loggedin();
        //user login condition
        // 0= Not logged in,
        // 1= Logged in

        if ($userLogin == 0) {
            return "<p>You should login before accessing this URL.</p>";
        }

        //else condition if user logged in.
        $userRoles = $objFun->wv_get_loggedin_user_role();
        //get the logged in user : username
        $current_user = wp_get_current_user();
        $userName = $current_user->user_login;
        $userID = $current_user->ID;

        ob_start();
        ?>
        <h2><?php _e('Control center : Vendor -','woo-vendor-module');?> <?php echo $userName; ?></h2>
        <small>
            <code>
                <p>
                    <?php
                    $optionsPage = get_option('woovendors_pages');
                    $shopPageid = $optionsPage['vendor_shop'];
                    ?>
                    <b><?php _e('My shop','woo-vendor-module');?> </b><br>
                    <a href='<?php echo esc_url(get_permalink($shopPageid) . "?vendor=$userName"); ?>'><?php echo esc_html(get_permalink($shopPageid)."?vendor=$userName"); ?></a>
                </p>
                <p>
                    <b><?php _e('Submit a product','woo-vendor-module');?></b><br>
                    <a target="_TOP" href="<?php echo esc_url(admin_url('post-new.php?post_type=product')); ?>"><?php echo esc_html(admin_url('post-new.php?post_type=product')); ?></a>
                </p>

                <hr><h2><?php _e('Sales Record','woo-vendor-module');?></h2>
                <form method="POST" action="">
                    <?php
                    $from ="";
                    $to   ="";
                    
                    if(isset($_POST['datepicker_from'])){
                    $from = $_SESSION['datepicker_from'] = ($_POST['datepicker_from']) ? $_POST['datepicker_from'] : $_SESSION['datepicker_from'];
                    }
                    
                    if(isset($_POST['datepicker_to'])){
                    $to = $_SESSION['datepicker_to'] = ($_POST['datepicker_to']) ? $_POST['datepicker_to'] : $_SESSION['datepicker_to'];
                    }
                    
                    //initialization from today's date.
                    if(empty($from)){
                        $from=date('m/d/Y');
                    }
                    if(empty($to)){
                        $to=date('m/d/Y');
                    }
                    
                    
                    ?> 
                    <!-- web form key value  --> 
                    <input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>">
                    <!-- web form key value  -->

                    <p class="datefield form-field">
                        <label for="">From</label>
                        <input type="text" id="datepicker_from" name="datepicker_from" value="<?php echo $from; ?>">
                        <label for="">To</label>
                        <input type="text" id="datepicker_to" name="datepicker_to" value="<?php echo $to; ?>">
                        &nbsp;
                        <input type="submit" name="salesSubmit" value="Submit">
                    </p>
                </form>    
                <?php
                //if userid !=0 it means any user is logged in.
                $userid = (new wvm_wooVendorShortcode)->wv_get_curent_loggedin_userid();

                //if user id =0 it means something eror.
                if ($userid == 0) {
                    return;
                }

                (new wvm_wooVendorShortcode)->wv_generate_sales_productreport($from, $to, $userid);
                //Generate order report of the author 
                (new wvm_wooVendorShortcode)->wv_generate_order_authorReport($from, $to, $userid);
                ?>
            </code>
        </small>
        <?php
        $output = ob_get_contents();
        ob_get_clean();
        return $output;
    }

    /*
     * Shortcode :vendor order history page
     */

  static  function wvm_callback_woovendor_vendor_orders($atts, $content = "") {
   $output="";        
//create object of the functions.
        $objFun = new wvm_core_wvfunctions();
        //order history record.
        $productid_encrypted="";
        if(isset($_REQUEST['key1'])){
        $productid_encrypted = $_REQUEST['key1'];
        }
        
        $productid = $objFun->wv_base64url_decode($productid_encrypted);
        $output = (new wvm_wooVendorShortcode)->wv_generate_order_productreport($productid);
        return $output; //
    }

    /*
     * Generate the order HTML report
     */

    function wv_generate_order_productreport($productid) {
               
        $pid = $productid;
        //output variables.
        $output = "";
       $paged   = get_query_var('paged');

        $argsProductorder = array(
            'post_type' => 'shop_order',
            'posts_per_page' => 10,
            'paged'      =>$paged,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => 'woo_order_product_id_'.$pid,
                    'value' => $pid,
                    'compare' => '=',
                ),
            ),
        );


        $quer1 = new WP_Query($argsProductorder);
        ob_start();
        if ($quer1->have_posts()):
            ?>
        <h2 class="prod-heading">Product: #<?php echo get_the_title($pid); ?></h2>
        <p class="prod-desc">Below is the product order history.</p>
            <table border="1" class="order-report product-order-report">
                
                <thead>
                    <tr>
                        <td rowspan="2">ORDER</td>
                        <td colspan="5"><center>BILLING ADDRESS</center></td>
            <td rowspan="2">Date</td>
            </tr>
            <tr class="col-last">
                <td>Full Name</td>
                <td>Address</td>
                <td>City</td>
                <td>State/Country</td>
                <td>Zip</td>
            </tr>   
            </thead>
            <tbody>
                <?php
                while ($quer1->have_posts()): $quer1->the_post();
                    $oid = get_the_ID();
                    $date = get_the_time('dM,Y h:m:s');
                    //generate report in HTML
                    ?>
                    <!-- Billing and shipping address. -->
                    <?php echo (new wvm_wooVendorShortcode)->wv_order_bodyHTML($pid,$oid, $date); ?>
                    <?php
                endwhile;
                ?>
            </tbody>
            </table> 
        <!-- paging start here -->
          <nav class="woocommerce-pagination">
                <?php
                //pagination environment
                $maxpage = $quer1->max_num_pages;
                echo (new wvm_wooVendorShortcode)->wv_woo_vendor_pagination($paged, $maxpage);
                ?> 
            </nav>
        <!-- paging start here -->
            <?php
        else:
            echo "<p>Sorry No order take place.</p>";
        endif;
        wp_reset_query();
        $output = ob_get_contents();
        ob_get_clean();

        return $output;
    }

    /*
     * Generate order HTML
     */

    function wv_order_bodyHTML($pid,$oid, $date) {
        //generate billing and shipping address.
        $complete_addr = "";
        ob_start();
        ?>
        <tr>
            <td rowspan="2">#<?php echo $oid; ?></td>
            <td><?php echo get_post_meta($oid, '_billing_first_name', TRUE) . '&nbsp;' . get_post_meta($oid, '_billing_last_name', TRUE); ?></td>
            <td><?php echo get_post_meta($oid, '_billing_address_1', TRUE) . "&nbsp;," . get_post_meta($oid, '_billing_address_2', TRUE); ?></td>
            <td><?php echo get_post_meta($oid, '_billing_city', TRUE); ?></td>
            <td>
                <?php echo get_post_meta($oid, '_billing_state', TRUE); ?>
                /<?php echo WC()->countries->countries[get_post_meta($oid,"_billing_country",TRUE)] ?>
            </td>
            <td><?php echo get_post_meta($oid, '_billing_postcode', TRUE); ?></td>
            <td><?php echo $date; ?></td>
        </tr>   
        <tr class="col-last">
            <td colspan="1">Qty:<?php echo $qty = (new wvm_wooVendorShortcode)->wv_get_qty_in_order($oid,$pid); ?> </td>    
            <td colspan="2">Email: <?php echo get_post_meta($oid, '_billing_email', TRUE); ?> </td>    
            <td colspan="1"><?php echo (new wvm_wooVendorShortcode)->wv_get_comment_html($pid); ?></td>    
            <td colspan="1">Mob: <?php echo get_post_meta($oid, '_billing_phone', TRUE); ?></td>    
            <td colspan="1">Price: <?php echo $qty; ?>X<?php echo get_woocommerce_currency_symbol().get_post_meta($oid,'woo_order_product_price_'.$pid, TRUE); ?></td>
        </tr>

        <?php
        $complete_addr = ob_get_contents();
        ob_get_clean();
        return $complete_addr;
    }

    /*
     * Generate the vendor shop HTML
     */

    function wv_vendor_shop_html($vendorID, $userName) {
        $output = "";
        //creating an argument query
        $argsAuthorPost = array();
        ob_start();
        //add the sorting 
        $actionURL = get_permalink(get_the_ID());
        //collecting ordering argument
        $orderby = "";
        if (isset($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
            switch ($orderby) {
                case "popularity":
                    $argsAuthorPost['meta_key'] = "total_sales";
                    $argsAuthorPost['orderby'] = "meta_value_num";
                    $argsAuthorPost['order'] = "DESC";
                    break;

                case "rating":
                    $argsAuthorPost['orderby'] = "comment_count";
                    break;

                case "date":
                    $argsAuthorPost['orderby'] = "date";
                    break;

                case "price":
                    $argsAuthorPost['meta_key'] = "_price";
                    $argsAuthorPost['orderby'] = "meta_value_num";
                    $argsAuthorPost['order'] = "ASC";
                    break;

                case "price-desc":
                    $argsAuthorPost['meta_key'] = "_price";
                    $argsAuthorPost['orderby'] = "meta_value_num";
                    $argsAuthorPost['order'] = "DESC";
                    break;
            }//switch case. 
        }

        //pagination.
        global $paged;
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        $perPage = get_option('posts_per_page');

        //creating an query argument

        $argsAuthorPost['post_type'] = "product";
        $argsAuthorPost['author'] = $vendorID;
        $argsAuthorPost['paged'] = $paged;

        $loop = new WP_Query($argsAuthorPost);
        if ($loop->have_posts()) {

            $totalposts = $loop->found_posts;
            
            /*
             * Showing 1 to 3 posts i.e
             * 1 = paged starting posts
             * 3 = showing posts till.
             */
            $allready_showedpost = $perPage * $paged;
            $showedposts    = ($totalposts > $allready_showedpost)? $allready_showedpost :$totalposts;
            $startshowposts = 1;
            
            $startshowposts = ($paged > 1) ? (($perPage * ($paged-1)) + 1):$startshowposts;
            
           //+1 = Because our posts start from 1 not 0
           //and next page, it will start from +1 i.e suppose we have viewed 4 posts then next posts start 5. 
            
            ?>
            <div class="woocommerce">
                <h2><?php _e('You are visiting the shop:','woo-vendor-module');?> <?php echo $userName; ?></h2>
                <p class="woocommerce-result-count"> Showing <?php echo $startshowposts; ?> – <?php echo $showedposts; ?> of <?php echo $totalposts; ?> results</p>
                <form class="woocommerce-ordering" action="<?php echo $actionURL; ?>" method="get">

                    <input type="hidden" name="vendor" value="<?php echo $userName; ?>" />

                    <?php
                    //selected
                    $selectedText = "";
                    if (isset($_GET['orderby'])) {
                        $selectedText = $_GET['orderby'];
                    }
                    ?>

                    <select name="orderby" class="orderby">
                        <option value="popularity" <?php
                        if ($selectedText == "popularity") {
                            echo 'selected="selected"';
                        }
                        ?>  >Sort by popularity</option>
                        <option value="rating" <?php
                        if ($selectedText == "rating") {
                            echo 'selected="selected"';
                        }
                        ?> >Sort by average rating</option>
                        <option value="date" <?php
                        if ($selectedText == "date") {
                            echo 'selected="selected"';
                        }
                        ?>>Sort by newness</option>
                        <option value="price" <?php
                        if ($selectedText == "price") {
                            echo 'selected="selected"';
                        }
                        ?> >Sort by price: low to high</option>
                        <option value="price-desc" <?php
                        if ($selectedText == "price-desc") {
                            echo 'selected="selected"';
                        }
                        ?> >Sort by price: high to low</option>
                    </select>
                    <input type="hidden" name="post_type" value="product">
                    <input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>">
                </form>

                <ul class="products">
                    <?php
                    //add woocommerce class in body
                    while ($loop->have_posts()) : $loop->the_post();
                        woocommerce_get_template_part('content', 'product');
                    endwhile;
                    ?> 
                </ul> 
                <?php
            } else {
                ?><p><?php _e('No products found','woo-vendor-module');?></p><?php
            }
            ?>
            <nav class="woocommerce-pagination">
                <?php
                //pagination environment
                $paged = get_query_var('paged');
                $maxpage = $loop->max_num_pages;
                echo (new wvm_wooVendorShortcode)->wv_woo_vendor_pagination($paged, $maxpage);
                ?> 
            </nav>
        </div>      
        <?php
        $output = ob_get_contents();
        wp_reset_postdata();
        ob_get_clean();

        return $output;
    }

    /*
     * Pagination in woo vendor plugin 
     * it inherits the woocommerce pagination.
     */

    function wv_woo_vendor_pagination($paged, $maxpage) {

        $big = 999999999; // need an unlikely integer
        $paginateLink = paginate_links(apply_filters('woocommerce_pagination_args', array(
            'base' => (new wvm_wooVendorShortcode)->wv_correct_pagelink(str_replace($big, '%#%', esc_url(get_pagenum_link($big)))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $maxpage,
            'prev_text' => '←',
            'next_text' => '→',
            'type' => 'list',
            'end_size' => 3,
            'mid_size' => 3
        )));
        return $paginateLink;
    }

    function wv_correct_pagelink($link) {
        return str_replace('#038;', '&', $link);
    }

    /*
     * collecting sorting keys
     */

    function wv_filter_sorting_args($argsArr) {
        print_r($argsArr);
    }

    /*
     * get current logged in user id
     */

    function wv_get_curent_loggedin_userid() {
        $userID = 0;
        //get the logged in user : username
        $current_user = wp_get_current_user();
        if (count($current_user) > 0) {
            $userName = $current_user->user_login;
            $userID = $current_user->ID;
        }
        return $userID;
    }

    /*
     * Generate sales Report
     */

    function wv_generate_sales_productreport($from, $to, $userid) {
        global $wpdb;

        //sales report generated here.

        $paged = get_query_var('paged');
        $perPage = 5;
        $argspostslist = array(
            'post_type' => 'product',
            'paged' => $paged,
            'author' => $userid,
            'date_query' => array(
                array(
                    'after' => array(
                        'year' => date('Y', strtotime($from)),
                        'month' => date('m', strtotime($from)),
                        'day' => date('d', strtotime($from)),
                    ),
                    'before' => array(
                        'year' => date('Y', strtotime($to)),
                        'month' => date('m', strtotime($to)),
                        'day' => date('d', strtotime($to)),
                    ),
                    'inclusive' => true,
                ),
            ),
            'posts_per_page' => $perPage,
        );
        $wpquery = new WP_Query($argspostslist);

        if ($wpquery->have_posts()) :
            ?>
            <table class="wv-product-record">

                <thead>
                    <tr>
                        <td colspan="5">Result From:<?php echo date('dM,Y', strtotime($from)); ?> To:<?php echo date('dM,Y', strtotime($to)); ?>.</td>
                    </tr>

                    <tr>
                        <th>Sno.</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Total Sales</th>
                        <th>Actions</th>
                    </tr>
                </thead>   
                <tbody>
                    <?php
                    $inc = 0;
                    if (isset($_REQUEST['paged']) AND $_REQUEST['paged'] != 1) {
                        $inc = ($_REQUEST['paged'] - 1) * $perPage;
                    }



                    while ($wpquery->have_posts()) : $wpquery->the_post();
                        ?>
                        <tr>
                            <td><?php echo ++$inc; ?></td>
                            <td>
                                <!-- product thumbnail -->
                                <?php
                                $imgHTML = (new wvm_core_wvfunctions)->wv_get_thumbnail_with_sizearr(get_the_ID(), array(90, 90));
                                if (empty($imgHTML)) {
                                    $imgHTML = '<img src="' . WOO_VENDOR__PLUGIN_URL . 'assets/images/cardbase_blank.gif" alt="image" title="" />';
                                }
                                echo $imgHTML;
                                ?>
                            </td>
                            <td><a href='<?php the_permalink(); ?>' title="<?php the_title(); ?>"><?php the_title(); ?></a></td>
                            <td><?php echo get_post_meta(get_the_ID(), 'total_sales', TRUE); ?></td>
                            <td>
                                <!-- All actions --> 
                                <?php
                                //order page link
                                $wv_pages_settings = get_option('woovendors_pages');
                                $orderPageid = $wv_pages_settings['vendor_orders'];
                                $objFun = new wvm_core_wvfunctions();
                                ?>
                                <a href='<?php the_permalink(); ?>' class="button wvmyproduct-action" title="<?php the_title(); ?>">View Product</a><br/>
                                <a href='<?php echo esc_url(get_permalink($orderPageid) . '?key1=' . $objFun->wv_base64url_encode(get_the_ID())); ?>' class="button wvmyproduct-action" title="Order History">Order History</a>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                    //Add paging here.
                    ?>
                </tbody>  
                <tfoot>
                    <tr>
                        <th>Sno.</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Total Sales</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>   
            </table>
            <!-- woocommerce pagination start  -->
            <nav class="woocommerce-pagination wv-salesReport">
                <?php
                //pagination environment
                //for the paged value see the above.
                $maxpage = $wpquery->max_num_pages;
                echo (new wvm_wooVendorShortcode)->wv_woo_vendor_pagination($paged, $maxpage);
                ?> 
            </nav>
            <!-- woocommerce pagination end  -->
            <?php
        else:
            echo '<p>Sorry No product found on selected date range.</p>';
        endif;
        wp_reset_query();
    }

    /*
     * Generate order report of the author
     */

    function wv_generate_order_authorReport($from, $to, $userid) {

        $paged = get_query_var('paged');
        $perPage = 5;
        $argspostslist = array(
            'post_type' => 'shop_order',
            'paged' => $paged,
            'post_status' => 'any',
            'date_query' => array(
                array(
                    'after' => array(
                        'year' => date('Y', strtotime($from)),
                        'month' => date('m', strtotime($from)),
                        'day' => date('d', strtotime($from)),
                    ),
                    'before' => array(
                        'year' => date('Y', strtotime($to)),
                        'month' => date('m', strtotime($to)),
                        'day' => date('d', strtotime($to)),
                    ),
                    'inclusive' => true,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'woo_order_vendor_id',
                    'value' => $userid,
                    'compare' => '=',
                ),
            ),
            'posts_per_page' => $perPage,
        );
        $wpquery = new WP_Query($argspostslist);
        ?>
        <h2>Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Shipping</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Link</th>
                </tr>
            </thead>

            <?php
            if ($wpquery->have_posts()) :
                ?>
                <tbody> 
                    <?php
                    while ($wpquery->have_posts()) : $wpquery->the_post();
                        $oid = get_the_ID();
                        $date =get_the_time('dM,Y h:m:s');
                        ?>
                        <tr>
                            <td>#<?php echo $oid; ?></td>
                            <td><?php echo (new wvm_wooVendorShortcode)->wv_generate_address_report($oid,"_billing_"); ?></td>
                            <td><?php echo get_woocommerce_currency_symbol(). get_post_meta($oid, '_order_total', TRUE); ?></td>
                            <td><?php echo $date; ?></td>
                            <?php $orderkey=get_post_meta($oid,'_order_key',TRUE); ?>
                            <td><a href="<?php echo site_url("/checkout/order-received/$oid/?key=$orderkey"); ?>" class="button">View</a></td>
                        </tr>     
                        <?php
                    endwhile;
                    ?>
                        <tr><td colspan="5">
                <!-- woocommerce pagination start  -->
            <nav class="woocommerce-pagination wv-salesReport">
                <?php
                //pagination environment
                //for the paged value see the above.
                $maxpage = $wpquery->max_num_pages;
                echo (new wvm_wooVendorShortcode)->wv_woo_vendor_pagination($paged, $maxpage);
                ?> 
            </nav>
                            </td></tr>
            <!-- woocommerce pagination end  -->
            </tbody>
                <?php
            else:
                ?>
                <tr>
                    <td colspan="5"><?php _e('You have no orders during this period.','woo-vendor-module');?></td>  
                </tr>
            <?php
            endif;
            wp_reset_query();
            ?>
        </table>
        <?php
    }
    /*
     * Generate shipping report
     */
    function wv_generate_address_report($oid,$type="_billing_"){
        
        $output ="";
       $address_key = array('first_name','last_name','address_1','address_2','city','state','postcode','country'); 
       
       //comma mechanism
       // Now in first 4 key comma will added every 2 , after that every 1.
       
       if(count($address_key)>0){
           $inc=1;
           foreach($address_key as $addr){
               
                $key = $type.$addr;
                $value="";
                //specially for key :country
                if($addr == "country"){
                $value=WC()->countries->countries[get_post_meta($oid,$key,TRUE)];    
                }else{
                $value=get_post_meta($oid,$key,TRUE);    
                }

                
                if($inc <=4){
                $comma = ($inc%2 == 0)?",<br/>":"";
                }else{
                $comma = ",<br/>";
                }
              $output .=$value.$comma.'&nbsp;';  
             ++$inc;   
           }
       }
       return $output;
    }
/*
 * Get product quanitity in single order.
 */    
function wv_get_qty_in_order($oid,$pid){
    global $wpdb;
    $key="woo_order_product_id_$pid";
    
    $sql_total_qty_in_order ="SELECT count(*) FROM ".$wpdb->prefix."postmeta WHERE meta_key='$key'";
               $total_qty   =$wpdb->query($sql_total_qty_in_order);
               return $total_qty;
}
//* Generate sales Report             
/*
 * Get comments link by post id
 */
function wv_get_comment_html($pid){
    
    $numcomment = get_comments_number( $pid );
    $link       = get_permalink($pid).'#comment-'.$numcomment;
    $linkHTML   = '<a href="'.esc_url($link).'" class="comment comment-link">comment-'.$numcomment.'</a>';
    return $linkHTML;
}
//end of the class    
}

new wvm_wooVendorShortcode();
?>