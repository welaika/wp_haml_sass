<?php

  // require embedded plugins
  require_once dirname(__FILE__).'/lib/vendor/post-type-archives/post-type-archives.php';
  require_once dirname(__FILE__).'/lib/vendor/attachments/attachments.php';
  require_once dirname(__FILE__).'/lib/vendor/regenerate-thumbnails/regenerate-thumbnails.php';

  // require WP init scripts
  require 'init/custom_post_types.php';
  require 'init/custom_fields.php';
  require 'init/site_options.php';
  require 'init/hooks.php';