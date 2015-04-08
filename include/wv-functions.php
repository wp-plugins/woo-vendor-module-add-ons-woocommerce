<?php
/*
 * This function explains the plugin functions which interact
 * with woocommerce hooks and add the vendor feature 
 */

class wvm_core_wvfunctions {

    function __construct() {
        //attachment media will access only own uploaded image.
        add_filter('parse_query', array($this, 'wv_files_only'));
        //show posts only own i.e woocommerce product.
        add_filter('pre_get_posts', array($this, 'wv_posts_for_current_author'));
        /*
         * When our created posts is called then woocommerce key class added in body tag
         */
        add_filter('body_class', array($this, 'wv_add_woocommerce_classs'), 10);
        /*
         * When our shortcode page is called then we will modified the pagetitle
         */
        //  add_filter('the_title', array($this,'wv_plugin_page_title'),10,2);
        /*
         * Enqueue of the  js and css file which is used in frontend.
         */
        add_action('wp_enqueue_scripts', array($this, 'wv_enqueue_scripts'));
        //admin enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'wv_admin_enqueue_scripts'));
    }

    /*
     * encryption and decryption functions.
     */

    function wv_base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '$$#', '%%*'), '=');
    }

    function wv_base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '%%*', '$$#'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /*
     * adding css and js file.
     */

    function wv_enqueue_scripts() {

        wp_enqueue_style('wvDatepickerCSS', WOO_VENDOR__PLUGIN_URL . 'assets/css/jquery-ui.css');
        wp_enqueue_style('wv-style.css', WOO_VENDOR__PLUGIN_URL . 'assets/css/wv-style.css');
           
        //datepicker js file from WordPress
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('wvCustomjs', WOO_VENDOR__PLUGIN_URL . 'assets/js/jquery-custom.js', array('jquery'), '', TRUE);
    }

    /*
     * Admin enqueue scripts
     */

    function wv_admin_enqueue_scripts() {
        wp_enqueue_style('wvadminstyle', WOO_VENDOR__PLUGIN_URL . 'assets/css/admin-style.css');

        $userRoles = $this->wv_get_loggedin_user_role();
        //remove some points when vendor login :admin
        if ($userRoles == "wvm_role_vendor") {
            ?>
            <style>
                ul.subsubsub li.all,ul.subsubsub li.publish{display:none;}
            </style>    
         <?php
        }//end condition.
    }

    /*
     * posts showing only own author.
     */

    function wv_posts_for_current_author($query) {

        if ($query->is_admin) {

            global $user_ID;
            $roles = $this->wv_get_loggedin_user_role();
            //if roles is vendor then set the posts by author wise.
            if ($roles == "wvm_role_vendor") {
                $query->set('author', $user_ID);
            }
        }
        return $query;
    }

    /*
     * generate the thumbnail with size
     */

    function wv_get_thumbnail_with_sizearr($postid, $sizearr) {
        $imgHTML = "";
        $post_thumbnail_id = get_post_thumbnail_id($postid);
        if (has_post_thumbnail($postid)) {
            //generate the thumbnail image via exact resolution
            $imgHTML = get_the_post_thumbnail($postid, $sizearr);
            return $imgHTML;
        }
        return $imgHTML;
    }

    /*
     * create an new user roles.
     */

    function wv_create_user_roles($roleName, $roleKey, $capability_Arr) {
        add_role($roleKey, $roleName, $capability_Arr);
    }

    /*
     * get logged in user roles.
     */

    function wv_get_loggedin_user_role() {
        global $current_user;

        $user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);

        return $user_role;
    }

    function wv_get_user_roles_by_userid($uid) {
        $user_info = get_userdata($uid);
        $user_roles = $user_info->roles[0];
        return $user_roles;
    }

    /*
     * check if this page title exists or not .
     * exists    = 1
     * not exits = 0
     */

    function wv_check_page_exists($pageTitle) {

        $output = 0;
        if (get_page_by_title($pageTitle)) {
            $output = 1;
        }

        return $output;
    }

    /*
     * create an page in admin
     */

    function wv_create_post($title, $content, $postType = 'page') {
        $my_post = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => $postType
        );

        // Insert the post into the database
        $pid = wp_insert_post($my_post);
        return $pid;
    }

    /*
     * make child parent relationship .
     */

    function wv_make_child_page($postid, $parentid) {
        $my_post = array(
            'ID' => $postid,
            'post_parent' => $parentid
        );

        // Update the post into the database
        wp_update_post($my_post);
    }

    /*
     * list all the wp pages
     */

    function wv_wp_page_list() {

        $arglist = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $mypages = get_pages($arglist);
        return $mypages;
    }

    /*
     * user can access the attachement image of own only.
     */

    function wv_files_only($wp_query) {

        global $current_user;

        // User can only view their own uploaded files
        if (isset($_POST['action']) && $_POST['action'] == 'query-attachments') {

            // Adding extra query parameter to limit sql for current user
            $wp_query->set('author', $current_user->ID);
        }
    }

    /*
     * check user is logged in or not
     * logged in  : 1
     * not logged in : 0
     */

    function wv_is_user_loggedin() {
        $output = 0;
        if (is_user_logged_in()) {
            $output = 1;
        } else {
            $output = 0;
        }
        return $output;
    }

    /*
     * Add the body class on selected page which is created by the plugin
     * or
     * if anyone page use my shortcode then our class "woocommerce , woocommerce-page " automatically added on there.
     */

    function wv_add_woocommerce_classs($classes) {
        $classes[] = "woocommerce woocommerce-page";
        return $classes;
    }

    /*
     * Verify the session is allready started or not
     * @return bool
     */

    function wv_is_session_started() {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

    /*
     * when plugin actions have been fired the update the one by one record in database so that next time it can't be
     * executed.
     * Key    : plugin_key_actions : 
      subkey : is_role_created    :         Init
      : is_create_page     :         Init
      : is_create_woovendor_submenu (Admin)
      : is_create_extracolumn       (Admin)
      : is_create_woovendor_shortcode :Page called.
     */

    function wv_update_plugin_action_info($key, $value) {

        //first call all the options in database.
        $allRecord = get_option('plugin_key_actions');
        $allRecord[$key] = $value;
        //update the record.
        update_option('plugin_key_actions', $allRecord);
    }

//==================================== Commission functions ============================
    /*
     * Commission heading table list
     */

    function wv_commission_table_heading_list($type = "thead") {
        $output = "";

        //verify the header and footer of the table list
        $type = ($type == "thead") ? "thead" : "tfoot";

        $output .="<$type>";
        $output .=" <tr>
                <th>#OrderId</th>
                <th>Order Date</th>
                <th>ProductName</th>
                <th>Vendor</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Commission Date</th>
            </tr>";

        $output .="</$type>";
        return $output;
    }

    /*
     * Commission table record. 
     */

    function wv_commission_table_record($oid) {
        global $wpdb;
        $orderid = $oid;
        $this->wv_commission_get_order_item_record($orderid);
    }

    /*
     * Count how many products in one order
     * get : order_item_id 
     */

    function wv_commission_get_order_item_record($oid) {

        global $wpdb;
        $sql_order_itemid = "SELECT DISTINCT WOIM.order_item_id FROM `" . $wpdb->prefix . "woocommerce_order_items` AS WOI INNER JOIN `" . $wpdb->prefix . "woocommerce_order_itemmeta` AS WOIM ON WOI.order_item_id = WOIM.order_item_id WHERE WOI.order_item_type = 'line_item' AND WOI.order_id =$oid";
        $arr_order_itemid = $wpdb->get_results($sql_order_itemid);

        if (count($arr_order_itemid) > 0) {
            $i = 1;
            foreach ($arr_order_itemid as $item) {

                $order_item_id = $item->order_item_id;
                $productid = $this->wv_commission_get_woo_orderitem_metavalue($order_item_id, '_product_id');
                //output HTML here.
                ?>
                <tr>
                    <!-- 
                    In case of more than 1 item, order id should show only one times : use= rowspan :
                    -->   
                <?php if ($i == 1) { ?>
                        <td rowSpan="<?php echo count($arr_order_itemid); ?>" >#<?php echo $oid; ?></td>
                    <?php } ++$i; ?>
                   <!-- order date -->
                    <td><?php echo get_post_time("dM,Y", FALSE, $oid); ?></td>
                   
                    <!-- product id -->
                    <td><?php echo get_the_title($productid); ?></td>
                    
                    <!-- vendor -->
                    <td><?php echo $this->wv_get_username_by_userid(get_post_meta($oid, 'woo_order_vendor_id_' . $productid, TRUE)); ?></td>
                    
                 <!-- commission -->   
                    <td>
                        <?php
                        echo get_woocommerce_currency_symbol(). get_post_meta($oid, 'woo_order_commision_' . $productid, TRUE);
                        ?>
                    </td>
                    
                 <!-- Status -->
                    <td>
                        <?php 
                        //change status functionality
                        $sts  = $this->wv_get_commission_status($oid,$productid);
                       $newsts= ($sts==1)?0:1;
                        ?>
                        <a title="Change Status" href="<?php echo admin_url("admin.php?page=woo_vendors&tab=commission&action=sts&oid=$oid&pid=$productid&sts=$newsts"); ?>" onclick="return confirm('Are you sure want to change status ?');">
                            <?php 
                               echo $newsts= ($sts==1)?"paid":"due";;
                            ?>
                        </a>    
                    </td>
                    
                    <!-- Commission Date -->
                    <td>
                       <?php 
                       //get commission date
                      echo $this->wv_get_commission_date($oid,$productid);
                       ?> 
                   </td>
                </tr>
                <?php
            }
        }//end of the count.
    }

    /*
     * get meta_value from order_item_meta_table
     */

    function wv_commission_get_woo_orderitem_metavalue($order_itemid, $metakey) {
        global $wpdb;

        $sql_query = "SELECT meta_value FROM `" . $wpdb->prefix . "woocommerce_order_itemmeta` WHERE `meta_key`='$metakey' AND `order_item_id`=$order_itemid";
        $val = $wpdb->get_row($sql_query);

        return $val->meta_value;
    }
  
    /*
     * Get commission status : i.e due or paid
     */
    function wv_get_commission_status($oid,$pid){
        
        $status = get_post_meta($oid,'woo_commision_status_'.$pid,TRUE);
        
        $status = ($status =="")? 0 :$status;
        return $status;
        
    }
    
    /*
     * Update commission status : i.e due or paid
     */
    function wv_update_commission_status($oid,$pid,$sts){
        
        update_post_meta($oid,'woo_commision_status_'.$pid,$sts);
        
        
    }
    /*
     * Get commission date : i.e which date commission is paid 
     */
    function wv_get_commission_date($oid,$pid){
        
        $displayDate = get_post_meta($oid,'woo_commision_date_'.$pid,TRUE);
        $displayDate = ($displayDate =="")?"Commission is due.":date("dM, Y, g:i a",strtotime($displayDate));;
        return $displayDate;
    }

//======================== Commission function end =================================
    /*
     * get user Name by user id
     */
    function wv_get_username_by_userid($uid) {

        $user = get_user_by('id', $uid);
        $username = $user->user_login;
        return $username;
    }

    /*
     * default page settings : Woo Vendors => pages (tab)
     */
    function wp_default_woovendors_pages_setting(){
        
        $settingPages = array();
        
        $pagemyaccount = get_page_by_title('Vendor MyAccount' );
        $idmyaccount   = $pagemyaccount->ID;

        $pageorder     = get_page_by_title('Vendor Order History' );
        $idorder       = $pageorder->ID;

        $pageshop      = get_page_by_title('Vendor Shop' );
        $idshop        = $pageshop->ID;

        $settingPages['vendor_myaccount'] = $idmyaccount;
        $settingPages['vendor_shop']      = $idshop;
        $settingPages['vendor_orders']    = $idorder;

        update_option('woovendors_pages',$settingPages);
    }
    /*
     * modified the page title 
     */
// function wv_plugin_page_title($title,$modifiedName){
//     
//     return $title."&nbsp:".$modifiedName;
// }
// 
//===============================================================================
}

new wvm_core_wvfunctions();
?>
