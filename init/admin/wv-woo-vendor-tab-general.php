<?php
/* Admin : Woocommerce -> woo vendors
 *In admin module,Tab general is descriptions here.
 */

//when form have been submit.
if(isset($_POST['general_settings'])){
    /*
     * key : woovendor_tab_general :
     * use above key and store all these things in it.
     */
    $default_commission = $_POST['default_commission'];
    //default commission
    $default_commission = ($default_commission > 0)?$default_commission : 1;
    
    $email_notification = 0;
    if(isset($_POST['email_notification'])){
        $email_notification =1;
    }
                $valarr = array();
   $valarr['default_commission']  =$default_commission; 
   $valarr['email_notification']  =$email_notification; 
   //save into the database
   update_option('woovendor_tab_general',$valarr);
}
?>
<div class="wrap tab-pages">
    <h2><?php _e('General Settings', 'woo-vendor-module'); ?></h2>
    <!-- pages content structure Start -->
    <form method="POST" action="" >
    <table class="form-table">
        <!-- default commission -->   
        <tr>
          <?php 
          $optionstab = get_option('woovendor_tab_general');
          $dft_comm   = $optionstab['default_commission'];
          $email_fire = $optionstab['email_notification'];
          
          //initialize default commission
          $dft_comm   = (empty($dft_comm))?10:$dft_comm;
          ?>  
            <th><?php _e('Default commission (%)', 'woo-vendor-module'); ?></th>
            <td>
                <input type="text" name="default_commission" value="<?php echo $dft_comm; ?>" />
            </td>
        </tr>
        <!-- General info -->
        <tr>
        <th><?php _e('General info', 'woo-vendor-module'); ?></th>
        <td>
            
            1: Allow users or guests to apply to become a vendor.<br/>
            2: Approve vendor applications manually.<br/>
            3: Any posts will be visible after approval by administrator.
        </td>
        </tr>
        <!-- Payment schedule's -->
        <tr>
            <th><?php _e('Payment schedule', 'woo-vendor-module'); ?></th>
            <td>
                Manual 
            </td>
        </tr>
        <!-- Email notification -->   
        <tr>
            <th><?php _e('Email notification', 'woo-vendor-module'); ?></th>
            <td>
                <input type="checkbox" class="email_notification" name="email_notification" id="email_notification" value="1" 
                       <?php
                       if($email_fire){
                           echo 'checked="checked"';
                       }
                       ?>       
                       >
                <label for="email_notification">
                <?php echo esc_html('An email is fired each time  When payment has been made via the payment schedule options.');?>
                </label>
            </td>
        </tr>
        
        
         <tr>
            <th>&nbsp; </th>
            <td><input type="submit" name="general_settings" value="Save Settings" /></td>
        </tr>

    </table>
    </form>  
    
    
  </div>