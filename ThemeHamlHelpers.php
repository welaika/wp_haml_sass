<?php

  // Put all your custom helper functions here! Will be callable from your views and partials

  function say_hello_to($name) {
    return "Hello $name!";
  }

  // If you have any custom HAML block, put it in here

  class ThemeHamlHelpers extends HamlHelpers {

    public static function render_parametrized_partial($block, $partial, $options) {
      $src = get_partial_content($partial);
      foreach ($options as $option => $value) {
        $src = preg_replace("/\{$option\}/", $value, $src);
      }
      $src = preg_replace('/\{block\}/', $block, $src);
      echo eval('?>' . stripslashes($src) . '<?php ');
    }

    public static function custom_block($block, $title) {
      ThemeHamlHelpers::render_parametrized_partial($block, '_custom_block', array('title' => $title));
    }

  }