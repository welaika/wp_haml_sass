<?php

  add_action('after_setup_theme', 'manage_thumbnails');
  function manage_thumbnails()
  {
    // add thumbnails to the following post types
    add_theme_support('post-thumbnails', array('page', 'post', 'place'));
    // set default thumb size
    set_post_thumbnail_size( 50, 50, true );
    // add custom thumb sizes here
    add_image_size( '100x100', 100, 100, true );
  }

  add_action('init', 'manage_custom_post_types');
  function manage_custom_post_types()
  {
    register_post_type(
      'place',
      array(
        'labels' => array(
          'name' => "Place"
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'place'),
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'comments', 'editor', 'excerpt', 'thumbnail')
      )
    );
  }

