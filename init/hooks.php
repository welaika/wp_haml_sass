<?php

  /*
    // Exclude certain categories from home
    function exclude_category() {
      if (is_home()) {
        $cat = '-' . get_category_id_by_name("Interviste");
        set_query_var('cat', $cat);
      }
    }
    add_filter('pre_get_posts', 'exclude_category');
  */

  /*
    // Hide/remove some features from Wordpress backend
    function simplify_backend() {
      echo '<script type="text/javascript" charset="utf-8">';
      echo '  jQuery(document).ready(function() {';
      echo '    jQuery("#menu-links").remove();';
      echo '    jQuery("#pageparentdiv, #media-buttons, #commentstatusdiv, #trackbacksdiv, #postcustom, #authordiv, #revisionsdiv, #tagsdiv-post_tag, *[href=edit-tags.php?taxonomy=post_tag]").hide();';
      echo '  });';
      echo '</script>';
    }
    add_action('admin_head', 'simplify_backend' );
  */

  /*
    // Add custom post types to the general feed
    function add_custom_posts_to_feed($qv) {
      if (isset($qv['feed']))
        $qv['post_type'] = array('review', 'journey', 'news', 'magazine_edition', 'event', 'post');
      return $qv;
    }
    add_filter('request', 'add_custom_posts_to_feed');
  */

  /*
    // Enable upload of custom MIME types
    function custom_upload_mimes ( $existing_mimes=array() ) {
      $existing_mimes['bib'] = 'text/x-bibtex';
      return $existing_mimes;
    }
    add_filter('upload_mimes', 'custom_upload_mimes');
  */