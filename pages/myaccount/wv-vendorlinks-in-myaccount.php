<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Adding Registration fields to the form 

add_filter('register_form', 'wvm_adding_custom_registration_fields');

function wvm_adding_custom_registration_fields() {

    //lets make the field required so that i can show you how to validate it later;
    ?>
    <div class="form-row form-row-wide">
        <label for="reg_firstname"><?php __('First Name', 'woocommerce'); ?></label>
        <label for="vendor_account">
            <input type="checkbox" id="vendor_account" name="vendor_account" class="vendor_account" value="1">
            <?php _e('Create vendor account','woo-vendor-module'); ?> </label>
    <?php
    }

//Updating use meta after registration successful registration
    add_action('woocommerce_created_customer', 'wvm_adding_extra_reg_fields');

    function wvm_adding_extra_reg_fields($user_id) {
        extract($_POST);
        if (isset($_POST['vendor_account'])) {

            update_user_meta($user_id, 'vendor_account', $_POST['vendor_account']);
            /*
             * adding the role as per admin settings.
             * Temporary : I have added the vendor role Now second role whose pending : vendor_pending
             */
            $rolesArr = array('wvm_role_pending_vendor' => 1);
            update_user_meta($user_id, 'wp_capabilities', $rolesArr);
        } else {
            update_user_meta($user_id, 'vendor_account', 0);
        }
    }
    ?>