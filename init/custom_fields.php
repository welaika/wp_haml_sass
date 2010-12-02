<?php

  function address_box() {
    global $post, $wpdb;
    $address = get_post_meta($post->ID, '_address', true);
    echo '<label for="product_data">' . __("Please, insert the address here:") . '</label> ';
    echo '<input type="text" value="'.$address.'" name="address" class="widefat" />';
  }

  function save_post_meta($post_id, $meta_name, $value) {
    add_post_meta($post_id, $meta_name, $value, true) or update_post_meta($post_id, $meta_name, $value);
  }

  function save_custom_fields($post_id) {
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
      return $post_id;
    }
    save_post_meta($post_id, "_address", $_POST["address"]);
  }
  add_action('save_post', 'save_custom_fields');

  function add_custom_box() {
    add_meta_box('address_box', __('Address'), 'address_box', 'place', 'normal', 'default');
  }
  add_action('add_meta_boxes', 'add_custom_box');