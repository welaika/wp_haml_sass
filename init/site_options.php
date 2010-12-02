<?php

  require_once (TEMPLATEPATH . '/lib/options-interface.php');

  add_action('wp_head', 'welaikathemes_wp_head');
  add_action('admin_menu', 'welaikathemes_add_admin');

  $options = array();

  $options[] = array( "name" => "Impostazioni Generali",
                      "type" => "heading");

  /*
    Add your settings here:

    $options[] = array( "name" => "Immagine Homepage",
              "desc" => "L'immagine presente sotto il form di ricerca, nella homepage del sito.",
              "id" => "alp_homepage_image",
              "std" => "",
              "type" => "upload");

  */

  update_option('welaika_template',$options);
  update_option('welaika_themename', get_bloginfo("name"));
  update_option('welaika_shortname', get_bloginfo("name"));
  update_option('welaika_manual', "#");
