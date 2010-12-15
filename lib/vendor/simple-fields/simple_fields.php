<?php
/*
Plugin Name: Simple Fields
Plugin URI: http://eskapism.se/code-playground/simple-fields/
Description: Add groups of textareas, input-fields, dropdowns, radiobuttons, checkboxes and files to your edit post screen.
Version: 0.3.5
Author: Pär Thernström
Author URI: http://eskapism.se/
License: GPL2
*/

/*  Copyright 2010  Pär Thernström (email: par.thernstrom@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// if called directly, load wordpress
if (isset($_GET["wp_abspath"])) {
  define( 'WP_USE_THEMES', false );
  require( $_GET["wp_abspath"] . './wp-blog-header.php' );
}


define( "EASY_FIELDS_URL", get_bloginfo('template_directory').'/lib/vendor/simple-fields/');
define( "EASY_FIELDS_NAME", "Simple Fields");
define( "EASY_FIELDS_VERSION", "0.3.5");
#define( "EASY_FIELDS_FILE", "options-general.php?page=simple-fields-options"); // this still feels nasty...

// on admin init: add styles and scripts
add_action( 'admin_init', 'simple_fields_admin_init' );
add_action( 'admin_menu', "simple_fields_admin_menu" );
add_action( 'admin_head', 'simple_fields_admin_head' );

// ajax. that's right baby.
add_action('wp_ajax_simple_fields_field_group_add_field', 'simple_fields_field_group_add_field');

function simple_fields_admin_init() {

  wp_enqueue_script("jquery");
  wp_enqueue_script("jquery-ui-core");
  wp_enqueue_script("jquery-ui-sortable");

  // check if jquery should be loaded via http och https
  $http = "http";
  if (is_ssl()) {
    $http = "https";
  }

  wp_enqueue_script("jquery-ui-effects-core", "$http://jquery-ui.googlecode.com/svn/tags/1.7.3/ui/effects.core.js");
  wp_enqueue_script("jquery-ui-effects-highlight", "$http://jquery-ui.googlecode.com/svn/tags/1.7.3/ui/effects.highlight.js");
  wp_enqueue_script("thickbox");
  wp_enqueue_style("thickbox");

  define( "EASY_FIELDS_FILE", menu_page_url("simple-fields-options", false) );

}

require("functions_admin.php");
require("functions_post.php");

