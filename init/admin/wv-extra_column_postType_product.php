<?php

/*
 * Adding one extra column named, 'vendor' in wordpress dashboard with 
 */
add_filter('manage_edit-product_columns', 'wvm_set_custom_edit_vendor_columns');
add_action('manage_product_posts_custom_column', 'wvm_custom_vendor_column', 10, 2);

function wvm_set_custom_edit_vendor_columns($columns) {
    
   $columns['vendor'] = __('Vendor', 'wooVendor');

    return $columns;
}

function wvm_custom_vendor_column($column, $post_id) {
    switch ($column) {

        case 'vendor':
            echo wvm_get_author_name_of_curr_post($post_id);
            break;
    }
}

/*
 * Create an function in which we will add the author name of the current post of the post type
 */
function wvm_get_author_name_of_curr_post($post_id){
    global $post;
    $author_id=$post->post_author;
    $authorName = get_userdata($author_id)->display_name;
    return $authorName;
}
