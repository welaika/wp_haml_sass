<?php

  function manage_custom_post_types_and_custom_taxonomies() {
    new_post_type("partner", array('title', 'thumbnail'), false);
    new_post_type("technology", array('excerpt', 'thumbnail'));
    new_taxonomy("technology-genre", array("technology"));
  }

  add_action('init', 'manage_custom_post_types_and_custom_taxonomies');

