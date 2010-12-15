<?php
  require_once 'lib/helpers.php';
  require_once 'helpers.php';

  // Here you call the proper view (these are placed in src/views/ directory)

  /* Some useful reminders:

     is_front_page()
     is_page("page-slug") or is_page()
     is_post_type_archive("custom-post-type") or is_post_type_archive()
     is_tax("custom-taxonomy-name")

  */

  if (is_single()) {
    render_view("single")
  } else {
    render_view("archive");
  }
