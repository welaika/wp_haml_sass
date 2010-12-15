<?php

  // require embedded plugins
  require_once dirname(__FILE__).'/lib/vendor/post-type-archives/post-type-archives.php';
  require_once dirname(__FILE__).'/lib/vendor/simple-fields/simple_fields.php';
  require_once dirname(__FILE__).'/lib/vendor/regenerate-thumbnails/regenerate-thumbnails.php';

  // our beloved helpers
  require_once 'lib/helpers.php';

  // require WP init scripts
  require_once 'init/custom_post_types.php';
  require_once 'init/thumbnail_sizes.php';
  require_once 'init/site_options.php';
  require_once 'init/hooks.php';