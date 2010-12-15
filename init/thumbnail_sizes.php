<?php

function manage_thumbnails()
{
  // add thumbnails to the following post types
  add_theme_support('post-thumbnails', array('page', 'post', 'award', 'partner'));
  set_post_thumbnail_size( 50, 50, true );

  // HP -> Awards
  add_image_size('275x50', 275, 50, true );
  add_image_size('293x180', 293, 180, true );
  add_image_size('300x300', 300, 300, true );
  add_image_size('150x100', 150, 100, false );
}

add_action('after_setup_theme', 'manage_thumbnails');
