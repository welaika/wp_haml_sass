<?php
// WooThemes Admin Interface

/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- WooThemes Admin Interface - welaikathemes_add_admin
- Framework options panel - welaikathemes_options_page
- Framework Settings page - welaikathemes_framework_settings_page
- welaika_load_only
- Ajax Save Action - welaika_ajax_callback
- Generates The Options - welaikathemes_machine
- WooThemes Uploader - welaikathemes_uploader_function
- Woothemes Theme Version Checker - welaikathemes_version_checker

-----------------------------------------------------------------------------------*/

function welaikathemes_wp_head() {

  //Styles
   if(!isset($_REQUEST['style']))
     $style = '';
   else
       $style = $_REQUEST['style'];
     if ($style != '') {
      $GLOBALS['stylesheet'] = $style;
          echo '<link href="'. get_bloginfo('template_directory') .'/styles/'. $GLOBALS['stylesheet'] . '.css" rel="stylesheet" type="text/css" />'."\n";
     } else {
          $GLOBALS['stylesheet'] = get_option('welaika_alt_stylesheet');
          if($GLOBALS['stylesheet'] != '')
               echo '<link href="'. get_bloginfo('template_directory') .'/styles/'. $GLOBALS['stylesheet'] .'" rel="stylesheet" type="text/css" />'."\n";
          else
               echo '<link href="'. get_bloginfo('template_directory') .'/styles/default.css" rel="stylesheet" type="text/css" />'."\n";
     }

    //Decode
  if(!isset($_REQUEST['decode']))
    $decode = 'false';
  else
    $decode = $_REQUEST['decode'];

  if ($decode == 'true')
    echo '<meta name="generator" content="' . get_option('welaika_settings_encode') . '" />';

  // Localization
  load_theme_textdomain('welaikathemes');

  // Date format
  $GLOBALS['welaikadate'] = get_option('welaika_date');
  if ( $GLOBALS['welaikadate'] == "" )
    $GLOBALS['welaikadate'] = "d. M, Y";

  // Output CSS from standarized options
  welaika_head_css();

}

function welaikathemes_add_admin() {

    global $query_string;
    $options =  get_option('welaika_template');
    $themename =  get_option('welaika_themename');
    $shortname =  get_option('welaika_shortname');

    if ( isset($_REQUEST['page']) && $_REQUEST['page'] == 'welaikathemes' ) {

    if (isset($_REQUEST['welaika_save']) && 'reset' == $_REQUEST['welaika_save']) {
      global $wpdb;
      $query = "DELETE FROM $wpdb->options WHERE option_name LIKE 'welaika_%'";
      $wpdb->query($query);
      header("Location: admin.php?page=welaikathemes&reset=true");
      die;
    }

    }

    // Check all the Options, then if the no options are created for a relative sub-page... it's not created.
    if(function_exists('add_object_page'))
    {
        add_object_page ('Page Title', $themename, 6,'welaikathemes', 'welaikathemes_options_page');
    }
    else
    {
        add_menu_page ('Page Title', $themename, 6,'welaikathemes_home', 'welaikathemes_options_page');
    }
    $welaikapage = add_submenu_page('welaikathemes', $themename, 'Opzioni del Tema', 6, 'welaikathemes','welaikathemes_options_page'); // Default

  // Add framework functionaily to the head individually
  add_action("admin_print_scripts-$welaikapage", 'welaika_load_only');
  add_action("admin_print_scripts-$welaikaframeworksettings", 'welaika_load_only');

}



/*-----------------------------------------------------------------------------------*/
/* Framework options panel - welaikathemes_options_page */
/*-----------------------------------------------------------------------------------*/

function welaikathemes_options_page(){

    $options =  get_option('welaika_template');
    $themename =  get_option('welaika_themename');
    $shortname =  get_option('welaika_shortname');
    $manualurl =  get_option('welaika_manual');

    //Version in Backend Head
    $theme_data = get_theme_data(TEMPLATEPATH . '/style.css');
    $local_version = $theme_data['Version'];

    //GET themes update RSS feed and do magic
  include_once(ABSPATH . WPINC . '/feed.php');

  $pos = strpos($manualurl, 'documentation');
  $theme_slug = str_replace("/", "", substr($manualurl, ($pos + 13))); //13 for the word documentation

  //add filter to make the rss read cache clear every 4 hours
  add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 14400;' ) );

    //Check for latest version of the theme
    //Checks to prevent 2.9 bugs from wrecking the options panel - will re-activate on 2.9.1
    //$update_core = get_transient('update_core');
    //$core_local_wp_version = $update_core->version_checked;
  $update_message = '';
    if(get_option('welaika_theme_version_checker') == 'true') {
        $update_message = welaikathemes_version_checker ($local_version);
    }

?>
<div class="wrap" id="welaika_container">
<div id="welaika-popup-save" class="welaika-save-popup"><div class="welaika-save-save">Options Updated</div></div>
<div id="welaika-popup-reset" class="welaika-save-popup"><div class="welaika-save-reset">Options Reset</div></div>
    <?php // <form method="post"  enctype="multipart/form-data"> ?>
    <form action="" enctype="multipart/form-data" id="welaikaform">
        <?php
    // Rev up the Options Machine

        $return = welaikathemes_machine($options);
        ?>

        <div id="main">
          <div id="welaika-nav">
        <ul>
          <?php echo $return[1] ?>
        </ul>
      </div>
      <div id="content">
           <?php echo $return[0]; /* Settings */ ?>
          </div>
          <div class="clear"></div>

        </div>
        <div class="save_bar_top">
        <img style="display:none" src="<?php echo bloginfo('template_url'); ?>/lib/functions/images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
        <input type="submit" value="Save All Changes" class="button submit-button" />
        </form>

        <form action="<?php echo wp_specialchars( $_SERVER['REQUEST_URI'] ) ?>" method="post" style="display:inline" id="welaikaform-reset">
            <span class="submit-footer-reset">
            <input name="reset" type="submit" value="Reset Options" class="button submit-button reset-button" onclick="return confirm('Click OK to reset. Any settings will be lost!');" />
            <input type="hidden" name="welaika_save" value="reset" />
            </span>
        </form>


        </div>
        <?php  if (!empty($update_message)) echo $update_message; ?>
        <?php  //wp_nonce_field('reset_options'); echo "\n"; // Legacy ?>


<div style="clear:both;"></div>
</div><!--wrap-->

 <?php
}



/*-----------------------------------------------------------------------------------*/
/* Framework Settings page - welaikathemes_framework_settings_page */
/*-----------------------------------------------------------------------------------*/

function welaikathemes_framework_settings_page(){

    $options =  get_option('welaika_settings_template');
    $themename =  get_option('welaika_themename');
    $shortname =  get_option('welaika_shortname');
    $manualurl =  get_option('welaika_manual');

    //Version in Backend Head
    $theme_data = get_theme_data(TEMPLATEPATH . '/style.css');
    $local_version = $theme_data['Version'];

    //GET themes update RSS feed and do magic
  include_once(ABSPATH . WPINC . '/feed.php');

  $pos = strpos($manualurl, 'documentation');
  $theme_slug = str_replace("/", "", substr($manualurl, ($pos + 13))); //13 for the word documentation

    //add filter to make the rss read cache clear every 4 hours
    add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 14400;' ) );

    //Check for latest version of the theme
    if(get_option('welaika_theme_version_checker') == 'true') {
        $update_message = welaikathemes_version_checker ($local_version);
    }

  include_once('admin-framework-settings.php'); // Include the page content for that

}



/*-----------------------------------------------------------------------------------*/
/* welaika_load_only */
/*-----------------------------------------------------------------------------------*/

function welaika_load_only() {

  add_action('admin_head', 'welaika_admin_head');

  function welaika_admin_head() {

    echo '<link rel="stylesheet" type="text/css" href="'.get_bloginfo('template_directory').'/lib/functions/admin-style.css" media="screen" />';

     // COLOR Picker ?>
    <link rel="stylesheet" media="screen" type="text/css" href="<?php echo get_bloginfo('template_directory'); ?>/lib/functions/js/colorpicker/css/colorpicker.css" />
    <script type="text/javascript" src="<?php echo get_bloginfo('template_directory'); ?>/lib/functions/js/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" language="javascript">
    jQuery(document).ready(function(){
      //Color Picker
      <?php $options = get_option('welaika_template');

      foreach($options as $option){
      if($option['type'] == 'color' OR $option['type'] == 'typography' OR $option['type'] == 'border'){
        if($option['type'] == 'typography' OR $option['type'] == 'border'){
          $option_id = $option['id'];
          $temp_color = get_option($option_id);
          $option_id = $option['id'] . '_color';
          $color = $temp_color['color'];
        }
        else {
          $option_id = $option['id'];
          $color = get_option($option_id);
        }
        ?>
         jQuery('#<?php echo $option_id; ?>_picker').children('div').css('backgroundColor', '<?php echo $color; ?>');
         jQuery('#<?php echo $option_id; ?>_picker').ColorPicker({
          color: '<?php echo $color; ?>',
          onShow: function (colpkr) {
            jQuery(colpkr).fadeIn(500);
            return false;
          },
          onHide: function (colpkr) {
            jQuery(colpkr).fadeOut(500);
            return false;
          },
          onChange: function (hsb, hex, rgb) {
            //jQuery(this).css('border','1px solid red');
            jQuery('#<?php echo $option_id; ?>_picker').children('div').css('backgroundColor', '#' + hex);
            jQuery('#<?php echo $option_id; ?>_picker').next('input').attr('value','#' + hex);

          }
          });
        <?php } } ?>

    });

    </script>
    <?php
    //AJAX Upload
    ?>
    <script type="text/javascript" src="<?php echo get_bloginfo('template_directory'); ?>/lib/functions/js/ajaxupload.js"></script>
    <script type="text/javascript">
      jQuery(document).ready(function(){

      var flip = 0;

      jQuery('#expand_options').click(function(){
        if(flip == 0){
          flip = 1;
          jQuery('#welaika_container #welaika-nav').hide();
          jQuery('#welaika_container #content').width(755);
          jQuery('#welaika_container .group').add('#welaika_container .group h2').show();

          jQuery(this).text('[-]');

        } else {
          flip = 0;
          jQuery('#welaika_container #welaika-nav').show();
          jQuery('#welaika_container #content').width(595);
          jQuery('#welaika_container .group').add('#welaika_container .group h2').hide();
          jQuery('#welaika_container .group:first').show();
          jQuery('#welaika_container #welaika-nav li').removeClass('current');
          jQuery('#welaika_container #welaika-nav li:first').addClass('current');

          jQuery(this).text('[+]');

        }

      });

        jQuery('.group').hide();
        jQuery('.group:first').fadeIn();
        jQuery('.welaika-radio-img-img').click(function(){
          jQuery(this).parent().parent().find('.welaika-radio-img-img').removeClass('welaika-radio-img-selected');
          jQuery(this).addClass('welaika-radio-img-selected');

        });
        jQuery('.welaika-radio-img-label').hide();
        jQuery('.welaika-radio-img-img').show();
        jQuery('.welaika-radio-img-radio').hide();
        jQuery('#welaika-nav li:first').addClass('current');
        jQuery('#welaika-nav li a').click(function(evt){

            jQuery('#welaika-nav li').removeClass('current');
            jQuery(this).parent().addClass('current');

            var clicked_group = jQuery(this).attr('href');

            jQuery('.group').hide();

              jQuery(clicked_group).fadeIn();

            evt.preventDefault();

          });

        if('<?php if(isset($_REQUEST['reset'])) { echo $_REQUEST['reset'];} else { echo 'false';} ?>' == 'true'){

          var reset_popup = jQuery('#welaika-popup-reset');
          reset_popup.fadeIn();
          window.setTimeout(function(){
               reset_popup.fadeOut();
            }, 2000);
            //alert(response);

        }

      //Update Message popup
      jQuery.fn.center = function () {
        this.animate({"top":( jQuery(window).height() - this.height() - 200 ) / 2+jQuery(window).scrollTop() + "px"},100);
        this.css("left", 250 );
        return this;
      }


      jQuery('#welaika-popup-save').center();
      jQuery('#welaika-popup-reset').center();
      jQuery(window).scroll(function() {

        jQuery('#welaika-popup-save').center();
        jQuery('#welaika-popup-reset').center();

      });



      //AJAX Upload
      jQuery('.image_upload_button').each(function(){

      var clickedObject = jQuery(this);
      var clickedID = jQuery(this).attr('id');
      new AjaxUpload(clickedID, {
          action: '<?php echo admin_url("admin-ajax.php"); ?>',
          name: clickedID, // File upload name
          data: { // Additional data to send
            action: 'welaika_ajax_post_action',
            type: 'upload',
            data: clickedID },
          autoSubmit: true, // Submit file after selection
          responseType: false,
          onChange: function(file, extension){},
          onSubmit: function(file, extension){
            clickedObject.text('Uploading'); // change button text, when user selects file
            this.disable(); // If you want to allow uploading only 1 file at time, you can disable upload button
            interval = window.setInterval(function(){
              var text = clickedObject.text();
              if (text.length < 13){  clickedObject.text(text + '.'); }
              else { clickedObject.text('Uploading'); }
            }, 200);
          },
          onComplete: function(file, response) {

          window.clearInterval(interval);
          clickedObject.text('Upload Image');
          this.enable(); // enable upload button

          // If there was an error
          if(response.search('Upload Error') > -1){
            var buildReturn = '<span class="upload-error">' + response + '</span>';
            jQuery(".upload-error").remove();
            clickedObject.parent().after(buildReturn);

          }
          else{
            var id = response.split(",")[0];
            var url = response.split(",")[1];
            var buildReturn = '<img class="hide welaika-option-image" id="image_'+clickedID+'" src="'+url+'" alt="" />';
            jQuery(".upload-error").remove();
            jQuery("#image_" + clickedID).remove();
            clickedObject.parent().after(buildReturn);
            jQuery('img#image_'+clickedID).fadeIn();
            clickedObject.next('span').fadeIn();
            clickedObject.parent().prev('input').val(id);
          }
          }
        });

      });

      //AJAX Remove (clear option value)
      jQuery('.image_reset_button').click(function(){

          var clickedObject = jQuery(this);
          var clickedID = jQuery(this).attr('id');
          var theID = jQuery(this).attr('title');

          var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';

          var data = {
            action: 'welaika_ajax_post_action',
            type: 'image_reset',
            data: theID
          };

          jQuery.post(ajax_url, data, function(response) {
            var image_to_remove = jQuery('#image_' + theID);
            var button_to_hide = jQuery('#reset_' + theID);
            image_to_remove.fadeOut(500,function(){ jQuery(this).remove(); });
            button_to_hide.fadeOut();
            clickedObject.parent().prev('input').val('');



          });

          return false;

        });



      //Save everything else
      jQuery('#welaikaform').submit(function(){

          function newValues() {
            var serializedValues = jQuery("#welaikaform").serialize();
            return serializedValues;
          }
          jQuery(":checkbox, :radio").click(newValues);
          jQuery("select").change(newValues);
          jQuery('.ajax-loading-img').fadeIn();
          var serializedReturn = newValues();

          var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';

           //var data = {data : serializedReturn};
          var data = {
            <?php if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'welaikathemes_framework_settings'){ ?>
            type: 'framework',
            <?php } ?>
            action: 'welaika_ajax_post_action',
            data: serializedReturn
          };

          jQuery.post(ajax_url, data, function(response) {
            var success = jQuery('#welaika-popup-save');
            var loading = jQuery('.ajax-loading-img');
            loading.fadeOut();
            success.fadeIn();
            window.setTimeout(function(){
               success.fadeOut();


            }, 2000);
          });

          return false;

        });

      });
    </script>

  <?php }
}

/*-----------------------------------------------------------------------------------*/
/* Ajax Save Action - welaika_ajax_callback */
/*-----------------------------------------------------------------------------------*/

add_action('wp_ajax_welaika_ajax_post_action', 'welaika_ajax_callback');

function welaika_ajax_callback() {
  global $wpdb; // this is how you get access to the database
  $themename = get_option('template') . "_";
  //Uploads
  if(isset($_POST['type'])){
    if($_POST['type'] == 'upload'){

      $clickedID = $_POST['data']; // Acts as the name
      $filename = $_FILES[$clickedID];
      $override['test_form'] = false;
      $override['action'] = 'wp_handle_upload';
      $attachment_id = media_handle_upload($clickedID,0,array(),$override);
      $upload_tracking[] = $clickedID;
      if (is_wp_error($attachment_id)) {
        echo 'Upload Error: ' . $attachment_id->get_error_message();
      } else {
        update_option( $clickedID , $attachment_id );
        $thumb = image_downsize($attachment_id);
        $thumb_url = $thumb[0];
        echo "$attachment_id,$thumb_url";
      }
    }


    elseif($_POST['type'] == 'image_reset'){

        $id = $_POST['data']; // Acts as the name
        global $wpdb;
        $query = "DELETE FROM $wpdb->options WHERE option_name LIKE '$id'";
        $wpdb->query($query);
        die();

    }
    elseif($_POST['type'] == 'framework'){

      $data = $_POST['data'];
      parse_str($data,$output);

      foreach($output as $id => $value){

        if($id == 'welaika_import_options'){

          //Decode and over write options.
          $new_import = base64_decode($value);
          $new_import = unserialize($new_import);
          print_r($new_import);

          if(!empty($new_import)) {
            foreach($new_import as $id2 => $value2){

              if(is_serialized($value2)) {

                update_option($id2,unserialize($value2));

              } else {

                update_option($id2,$value2);

              }
            }
          }
        }

        // Woo Show Option Save
        if(!isset($output['welaika_show_options'])){
          update_option('welaika_show_options','false');
        }
        elseif ( $id == 'welaika_show_options' AND $value == 'true') { update_option($id,'true'); }

        // Woo Theme Version Checker Save
        if(!isset($output['welaika_theme_version_checker'])){
          update_option('welaika_theme_version_checker','false');
        }
        elseif ( $id == 'welaika_theme_version_checker' AND $value == 'true') { update_option($id,'true'); }


        // Woo Core update Save
        if(!isset($output['welaika_framework_update'])){
          update_option('welaika_framework_update','false');
        }
        elseif ( $id == 'welaika_framework_update' AND $value == 'true') { update_option($id,'true'); }

        // Woo Buy Themes Save
        if(!isset($output['welaika_buy_themes'])){
          update_option('welaika_buy_themes','false');
        }
        elseif ( $id == 'welaika_buy_themes' AND $value == 'true') { update_option($id,'true'); }


      }

    }
  }

  else {
    $data = $_POST['data'];
    parse_str($data,$output);

    print_r($output);

    $options =  get_option('welaika_template');

    foreach($options as $option_array){


        if(isset($option_array['id'])) { // Headings...


          $id = $option_array['id'];
          $old_value = get_option($id);
          $new_value = '';

          if(isset($output[$id])){
            $new_value = $output[$option_array['id']];
          }
          $type = $option_array['type'];


          if ( is_array($type)){
                foreach($type as $array){
                  if($array['type'] == 'text'){
                    $id = $array['id'];
                    $new_value = $output[$id];
                    update_option( $id, stripslashes($new_value));
                  }
                }
          }
          elseif($new_value == '' && $type == 'checkbox'){ // Checkbox Save

            update_option($id,'false');
            //update_option($themename . $id,'false');


          }
          elseif ($new_value == 'true' && $type == 'checkbox'){ // Checkbox Save

            update_option($id,'true');
            //update_option($themename . $id,'true');

          }
          elseif($type == 'multicheck'){ // Multi Check Save

            $options = $option_array['options'];

            foreach ($options as $options_id => $options_value){

              $multicheck_id = $id . "_" . $options_id;

              if(!isset($output[$multicheck_id])){
                update_option($multicheck_id,'false');
                //update_option($themename . $multicheck_id,'false');
              }
              else{
                 update_option($multicheck_id,'true');
                 //update_option($themename . $multicheck_id,'true');
              }

            }

          }

          elseif($type == 'typography'){

            $typography_array = array();

            /* Size */
            $typography_array['size'] = $output[$option_array['id'] . '_size'];

            /* Face  */
            $typography_array['face'] = stripslashes($output[$option_array['id'] . '_face']);

            /* Style  */
            $typography_array['style'] = $output[$option_array['id'] . '_style'];

            /* Color  */
            $typography_array['color'] = $output[$option_array['id'] . '_color'];

            update_option($id,$typography_array);


          }
          elseif($type == 'border'){

            $border_array = array();

            /* Width */
            $border_array['width'] = $output[$option_array['id'] . '_width'];

            /* Style  */
            $border_array['style'] = $output[$option_array['id'] . '_style'];

            /* Color  */
            $border_array['color'] = $output[$option_array['id'] . '_color'];

            update_option($id,$border_array);


          }
          elseif($type != 'upload_min'){

            update_option($id,stripslashes($new_value));
          }
        }

    }
  }


  /* Create, Encrypt and Update the Saved Settings */
  global $wpdb;

  $welaika_options = array();

  $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'welaika_%' AND
        option_name != 'welaika_options' AND
        option_name != 'welaika_template' AND
        option_name != 'welaika_custom_template' AND
        option_name != 'welaika_settings_encode' AND
        option_name != 'welaika_export_options' AND
        option_name != 'welaika_import_options' AND
        option_name != 'welaika_framework_version' AND
        option_name != 'welaika_manual' AND
        option_name != 'welaika_shortname'";

  $results = $wpdb->get_results($query);

  $output = "<ul>";

  foreach ($results as $result){
      $name = $result->option_name;
      $value = $result->option_value;

      if(is_serialized($value)) {

        $value = unserialize($value);
        $welaika_array_option = $value;
        $temp_options = '';
        foreach($value as $v){
          if(isset($v))
            $temp_options .= $v . ',';

        }
        $value = $temp_options;
        $welaika_array[$name] = $welaika_array_option;
      } else {
        $welaika_array[$name] = $value;
      }

      $output .= '<li><strong>' . $name . '</strong> - ' . $value . '</li>';
  }
  $output .= "</ul>";
  $output = base64_encode($output);

  update_option('welaika_options',$welaika_array);
  update_option('welaika_settings_encode',$output);



  die();

}



/*-----------------------------------------------------------------------------------*/
/* Generates The Options - welaikathemes_machine */
/*-----------------------------------------------------------------------------------*/

function welaikathemes_machine($options) {

    $counter = 0;
  $menu = '';
  $output = '';
  foreach ($options as $value) {

    $counter++;
    $val = '';
    //Start Heading
     if ( $value['type'] != "heading" )
     {
       $class = ''; if(isset( $value['class'] )) $class = $value['class'];
      //$output .= '<div class="section section-'. $value['type'] .'">'."\n".'<div class="option-inner">'."\n";
      $output .= '<div class="section section-'.$value['type'].'">'."\n";
      $output .= '<h3 class="heading">'. $value['name'] .'</h3>'."\n";
      $output .= '<div class="option '. $class .'">'."\n" . '<div class="controls">'."\n";

     }
     //End Heading
    $select_value = '';
    switch ( $value['type'] ) {

    case 'text':
      $val = $value['std'];
      $std = get_option($value['id']);
      if ( $std != "") { $val = $std; }
      $output .= '<input class="welaika-input" name="'. $value['id'] .'" id="'. $value['id'] .'" type="'. $value['type'] .'" value="'. $val .'" />';
    break;

    case 'select':

      $output .= '<select class="welaika-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';

      $select_value = get_option($value['id']);
      foreach ($value['options'] as $option) {

        $selected = '';

         if($select_value != '') {
           if ( $select_value == $option) { $selected = ' selected="selected"';}
           } else {
           if ( isset($value['std']) )
             if ($value['std'] == $option) { $selected = ' selected="selected"'; }
         }

         $output .= '<option'. $selected .'>';
         $output .= $option;
         $output .= '</option>';

       }
       $output .= '</select>';


    break;
    case 'select2':

      $output .= '<select class="welaika-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';

      $select_value = get_option($value['id']);

      foreach ($value['options'] as $option => $name) {

        $selected = '';

         if($select_value != '') {
           if ( $select_value == $option) { $selected = ' selected="selected"';}
           } else {
           if ( isset($value['std']) )
             if ($value['std'] == $option) { $selected = ' selected="selected"'; }
         }

         $output .= '<option'. $selected .' value="'.$option.'">';
         $output .= $name;
         $output .= '</option>';

       }
       $output .= '</select>';


    break;
    case 'textarea':

      if(isset($value['options']) && isset($value['std'])) {
        $ta_options = $value['options'];
        $cols = $ta_options['cols'];
        $ta_value = $value['std'];
      } else {
        $cols = '8';
        $ta_value = '';
      }
        $std = get_option($value['id']);
        if( $std != "") { $ta_value = stripslashes( $std ); }
        $output .= '<textarea class="welaika-input" name="'. $value['id'] .'" id="'. $value['id'] .'" cols="'. $cols .'" rows="8">'.$ta_value.'</textarea>';


    break;
    case "radio":

       $select_value = get_option( $value['id']);

       foreach ($value['options'] as $key => $option)
       {

         $checked = '';
           if($select_value != '') {
            if ( $select_value == $key) { $checked = ' checked'; }
           } else {
          if ($value['std'] == $key) { $checked = ' checked'; }
           }
        $output .= '<input class="welaika-input welaika-radio" type="radio" name="'. $value['id'] .'" value="'. $key .'" '. $checked .' />' . $option .'<br />';

      }

    break;
    case "checkbox":

       $std = $value['std'];

       $saved_std = get_option($value['id']);

       $checked = '';

      if(!empty($saved_std)) {
        if($saved_std == 'true') {
        $checked = 'checked="checked"';
        }
        else{
           $checked = '';
        }
      }
      elseif( $std == 'true') {
         $checked = 'checked="checked"';
      }
      else {
        $checked = '';
      }
      $output .= '<input type="checkbox" class="checkbox welaika-input" name="'.  $value['id'] .'" id="'. $value['id'] .'" value="true" '. $checked .' />';

    break;
    case "multicheck":

      $std =  $value['std'];

      foreach ($value['options'] as $key => $option) {

      $welaika_key = $value['id'] . '_' . $key;
      $saved_std = get_option($welaika_key);

      if(!empty($saved_std))
      {
          if($saved_std == 'true'){
           $checked = 'checked="checked"';
          }
          else{
            $checked = '';
          }
      }
      elseif( $std == $key) {
         $checked = 'checked="checked"';
      }
      else {
        $checked = '';                                                                                    }
      $output .= '<input type="checkbox" class="checkbox welaika-input" name="'. $welaika_key .'" id="'. $welaika_key .'" value="true" '. $checked .' /><label for="'. $welaika_key .'">'. $option .'</label><br />';

      }
    break;
    case "upload":

      $output .= welaikathemes_uploader_function($value['id'],$value['std'],null);

    break;
    case "upload_min":

      $output .= welaikathemes_uploader_function($value['id'],$value['std'],'min');

    break;
    case "color":
      $val = $value['std'];
      $stored  = get_option( $value['id'] );
      if ( $stored != "") { $val = $stored; }
      $output .= '<div id="' . $value['id'] . '_picker" class="colorSelector"><div></div></div>';
      $output .= '<input class="welaika-color" name="'. $value['id'] .'" id="'. $value['id'] .'" type="text" value="'. $val .'" />';
    break;

    case "typography":

      $default = $value['std'];
      $typography_stored = get_option($value['id']);

      /* Font Size */
      $val = $default['size'];
      if ( $typography_stored['size'] != "") { $val = $typography_stored['size']; }
      $output .= '<select class="welaika-typography welaika-typography-size" name="'. $value['id'].'_size" id="'. $value['id'].'_size">';
        for ($i = 9; $i < 71; $i++){
          if($val == $i){ $active = 'selected="selected"'; } else { $active = ''; }
          $output .= '<option value="'. $i .'" ' . $active . '>'. $i .'px</option>'; }
      $output .= '</select>';

      /* Font Unit
      $val = $default['unit'];
      if ( $typography_stored['unit'] != "") { $val = $typography_stored['unit']; }
        $em = ''; $px = '';
      if($val == 'em'){ $em = 'selected="selected"'; }
      if($val == 'px'){ $px = 'selected="selected"'; }
      $output .= '<select class="welaika-typography welaika-typography-unit" name="'. $value['id'].'_unit" id="'. $value['id'].'_unit">';
      $output .= '<option value="px '. $px .'">px</option>';
      $output .= '<option value="em" '. $em .'>em</option>';
      $output .= '</select>';
      */

      /* Font Face */
      /* Font Face */
      $val = $default['face'];
      if ( $typography_stored['face'] != "")
        $val = $typography_stored['face'];

      $font01 = '';
      $font02 = '';
      $font03 = '';
      $font04 = '';
      $font05 = '';
      $font06 = '';
      $font07 = '';
      $font08 = '';
      $font09 = '';
      $font10 = '';
      $font11 = '';
      $font12 = '';
      $font13 = '';
      $font14 = '';
      $font15 = '';

      if (strpos($val, 'Arial, sans-serif') !== false){ $font01 = 'selected="selected"'; }
      if (strpos($val, 'Verdana, Geneva') !== false){ $font02 = 'selected="selected"'; }
      if (strpos($val, 'Trebuchet') !== false){ $font03 = 'selected="selected"'; }
      if (strpos($val, 'Georgia') !== false){ $font04 = 'selected="selected"'; }
      if (strpos($val, 'Times New Roman') !== false){ $font05 = 'selected="selected"'; }
      if (strpos($val, 'Tahoma, Geneva') !== false){ $font06 = 'selected="selected"'; }
      if (strpos($val, 'Palatino') !== false){ $font07 = 'selected="selected"'; }
      if (strpos($val, 'Helvetica') !== false){ $font08 = 'selected="selected"'; }
      if (strpos($val, 'Calibri') !== false){ $font09 = 'selected="selected"'; }
      if (strpos($val, 'Myriad') !== false){ $font10 = 'selected="selected"'; }
      if (strpos($val, 'Lucida') !== false){ $font11 = 'selected="selected"'; }
      if (strpos($val, 'Arial Black') !== false){ $font12 = 'selected="selected"'; }
      if (strpos($val, 'Gill') !== false){ $font13 = 'selected="selected"'; }
      if (strpos($val, 'Geneva, Tahoma') !== false){ $font14 = 'selected="selected"'; }
      if (strpos($val, 'Impact') !== false){ $font15 = 'selected="selected"'; }

      $output .= '<select class="welaika-typography welaika-typography-face" name="'. $value['id'].'_face" id="'. $value['id'].'_face">';
      $output .= '<option value="Arial, sans-serif" '. $font01 .'>Arial</option>';
      $output .= '<option value="Verdana, Geneva, sans-serif" '. $font02 .'>Verdana</option>';
      $output .= '<option value="&quot;Trebuchet MS&quot;, Tahoma, sans-serif"'. $font03 .'>Trebuchet</option>';
      $output .= '<option value="Georgia, serif" '. $font04 .'>Georgia</option>';
      $output .= '<option value="&quot;Times New Roman&quot;, serif"'. $font05 .'>Times New Roman</option>';
      $output .= '<option value="Tahoma, Geneva, Verdana, sans-serif"'. $font06 .'>Tahoma</option>';
      $output .= '<option value="Palatino, &quot;Palatino Linotype&quot;, serif"'. $font07 .'>Palatino</option>';
      $output .= '<option value="&quot;Helvetica Neue&quot;, Helvetica, sans-serif" '. $font08 .'>Helvetica*</option>';
      $output .= '<option value="Calibri, Candara, Segoe, Optima, sans-serif"'. $font09 .'>Calibri*</option>';
      $output .= '<option value="&quot;Myriad Pro&quot;, Myriad, sans-serif"'. $font10 .'>Myriad Pro*</option>';
      $output .= '<option value="&quot;Lucida Grande&quot;, &quot;Lucida Sans Unicode&quot;, &quot;Lucida Sans&quot;, sans-serif"'. $font11 .'>Lucida</option>';
      $output .= '<option value="&quot;Arial Black&quot;, sans-serif" '. $font12 .'>Arial Black</option>';
      $output .= '<option value="&quot;Gill Sans&quot;, &quot;Gill Sans MT&quot;, Calibri, sans-serif" '. $font13 .'>Gill Sans*</option>';
      $output .= '<option value="Geneva, Tahoma, Verdana, sans-serif" '. $font14 .'>Geneva*</option>';
      $output .= '<option value="Impact, Charcoal, sans-serif" '. $font15 .'>Impact</option>';
      $output .= '</select>';

      /* Font Weight */
      $val = $default['style'];
      if ( $typography_stored['style'] != "") { $val = $typography_stored['style']; }
        $normal = ''; $italic = ''; $bold = ''; $bolditalic = '';
      if($val == 'normal'){ $normal = 'selected="selected"'; }
      if($val == 'italic'){ $italic = 'selected="selected"'; }
      if($val == 'bold'){ $bold = 'selected="selected"'; }
      if($val == 'bold italic'){ $bolditalic = 'selected="selected"'; }

      $output .= '<select class="welaika-typography welaika-typography-style" name="'. $value['id'].'_style" id="'. $value['id'].'_style">';
      $output .= '<option value="normal" '. $normal .'>Normal</option>';
      $output .= '<option value="italic" '. $italic .'>Italic</option>';
      $output .= '<option value="bold" '. $bold .'>Bold</option>';
      $output .= '<option value="bold italic" '. $bolditalic .'>Bold/Italic</option>';
      $output .= '</select>';

      /* Font Color */
      $val = $default['color'];
      if ( $typography_stored['color'] != "") { $val = $typography_stored['color']; }
      $output .= '<div id="' . $value['id'] . '_color_picker" class="colorSelector"><div></div></div>';
      $output .= '<input class="welaika-color welaika-typography welaika-typography-color" name="'. $value['id'] .'_color" id="'. $value['id'] .'_color" type="text" value="'. $val .'" />';

    break;

    case "border":

      $default = $value['std'];
      $border_stored = get_option( $value['id'] );

      /* Border Width */
      $val = $default['width'];
      if ( $border_stored['width'] != "") { $val = $border_stored['width']; }
      $output .= '<select class="welaika-border welaika-border-width" name="'. $value['id'].'_width" id="'. $value['id'].'_width">';
        for ($i = 0; $i < 21; $i++){
          if($val == $i){ $active = 'selected="selected"'; } else { $active = ''; }
          $output .= '<option value="'. $i .'" ' . $active . '>'. $i .'px</option>'; }
      $output .= '</select>';

      /* Border Style */
      $val = $default['style'];
      if ( $border_stored['style'] != "") { $val = $border_stored['style']; }
        $solid = ''; $dashed = ''; $dotted = '';
      if($val == 'solid'){ $solid = 'selected="selected"'; }
      if($val == 'dashed'){ $dashed = 'selected="selected"'; }
      if($val == 'dotted'){ $dotted = 'selected="selected"'; }

      $output .= '<select class="welaika-border welaika-border-style" name="'. $value['id'].'_style" id="'. $value['id'].'_style">';
      $output .= '<option value="solid" '. $solid .'>Solid</option>';
      $output .= '<option value="dashed" '. $dashed .'>Dashed</option>';
      $output .= '<option value="dotted" '. $dotted .'>Dotted</option>';
      $output .= '</select>';

      /* Border Color */
      $val = $default['color'];
      if ( $border_stored['color'] != "") { $val = $border_stored['color']; }
      $output .= '<div id="' . $value['id'] . '_color_picker" class="colorSelector"><div></div></div>';
      $output .= '<input class="welaika-color welaika-border welaika-border-color" name="'. $value['id'] .'_color" id="'. $value['id'] .'_color" type="text" value="'. $val .'" />';

    break;

    case "images":
      $i = 0;
      $select_value = get_settings( $value['id']);

      foreach ($value['options'] as $key => $option)
       {
       $i++;

         $checked = '';
         $selected = '';
           if($select_value != '') {
            if ( $select_value == $key) { $checked = ' checked'; $selected = 'welaika-radio-img-selected'; }
            } else {
            if ($value['std'] == $key) { $checked = ' checked'; $selected = 'welaika-radio-img-selected'; }
            elseif ($i == 1  && !isset($select_value)) { $checked = ' checked'; $selected = 'welaika-radio-img-selected'; }
            elseif ($i == 1  && $value['std'] == '') { $checked = ' checked'; $selected = 'welaika-radio-img-selected'; }
            else { $checked = ''; }
          }

        $output .= '<span>';
        $output .= '<input type="radio" id="welaika-radio-img-' . $value['id'] . $i . '" class="checkbox welaika-radio-img-radio" value="'.$key.'" name="'. $value['id'].'" '.$checked.' />';
        $output .= '<div class="welaika-radio-img-label">'. $key .'</div>';
        $output .= '<img src="'.$option.'" alt="" class="welaika-radio-img-img '. $selected .'" onClick="document.getElementById(\'welaika-radio-img-'. $value['id'] . $i.'\').checked = true;" />';
        $output .= '</span>';

      }

    break;

    case "heading":

      if($counter >= 2){
         $output .= '</div>'."\n";
      }
      $jquery_click_hook = ereg_replace("[^A-Za-z0-9]", "", strtolower($value['name']) );
      $jquery_click_hook = "welaika-option-" . $jquery_click_hook;
//      $jquery_click_hook = "welaika-option-" . str_replace("&","",str_replace("/","",str_replace(".","",str_replace(")","",str_replace("(","",str_replace(" ","",strtolower($value['name'])))))));
      $menu .= '<li><a title="'.  $value['name'] .'" href="#'.  $jquery_click_hook  .'">'.  $value['name'] .'</a></li>';
      $output .= '<div class="group" id="'. $jquery_click_hook  .'"><h2>'.$value['name'].'</h2>'."\n";
    break;
    }

    // if TYPE is an array, formatted into smaller inputs... ie smaller values
    if ( is_array($value['type'])) {
      foreach($value['type'] as $array){

          $id =   $array['id'];
          $std =   $array['std'];
          $saved_std = get_option($id);
          if($saved_std != $std && !empty($saved_std) ){$std = $saved_std;}
          $meta =   $array['meta'];

          if($array['type'] == 'text') { // Only text at this point

             $output .= '<input class="input-text-small welaika-input" name="'. $id .'" id="'. $id .'" type="text" value="'. $std .'" />';
             $output .= '<span class="meta-two">'.$meta.'</span>';
          }
        }
    }
    if ( $value['type'] != "heading" ) {
      if ( $value['type'] != "checkbox" )
        {
        $output .= '<br/>';
        }
      $output .= '</div><div class="explain">'. $value['desc'] .'</div>'."\n";
      $output .= '<div class="clear"> </div></div></div>'."\n";
      }

  }
    $output .= '</div>';
    return array($output,$menu);

}



/*-----------------------------------------------------------------------------------*/
/* WooThemes Uploader - welaikathemes_uploader_function */
/*-----------------------------------------------------------------------------------*/

function welaikathemes_uploader_function($id,$std,$mod){

    //$uploader .= '<input type="file" id="attachement_'.$id.'" name="attachement_'.$id.'" class="upload_input"></input>';
    //$uploader .= '<span class="submit"><input name="save" type="submit" value="Upload" class="button upload_save" /></span>';

  $uploader = '';
    $upload = get_option($id);

  if($mod != 'min') {
      $val = $std;
            if ( get_option( $id ) != "") { $val = get_option($id); }
            $uploader .= '<input class="welaika-input" name="'. $id .'" id="'. $id .'_upload" type="text" value="'. $val .'" />';
  }

  $uploader .= '<div class="upload_button_div"><span class="button image_upload_button" id="'.$id.'">Upload Image</span>';

  if(!empty($upload)) {$hide = '';} else { $hide = 'hide';}

  $uploader .= '<span class="button image_reset_button '. $hide.'" id="reset_'. $id .'" title="' . $id . '">Remove</span>';
  $uploader .='</div>' . "\n";
    $uploader .= '<div class="clear"></div>' . "\n";
  if(!empty($upload)){
      $thumb = image_downsize($upload);
      $thumb_url = $thumb[0];
    //$upload = cleanSource($upload); // Removed since V.2.3.7 it's not showing up
      $uploader .= '<a class="welaika-uploaded-image" href="'. $thumb_url . '">';
      $uploader .= '<img id="image_'.$id.'" src="'.$thumb_url.'" alt="" />';
      $uploader .= '</a>';
    }
  $uploader .= '<div class="clear"></div>' . "\n";


return $uploader;
}



/*-----------------------------------------------------------------------------------*/
/* Woothemes Theme Version Checker - welaikathemes_version_checker */
/* @local_version is the installed theme version number */
/*-----------------------------------------------------------------------------------*/

function welaikathemes_version_checker ($local_version) {

  // Get a SimplePie feed object from the specified feed source.
  $rss = fetch_feed('http://www.welaikathemes.com/?feed=updates&theme=' . get_option('template'));

  // Of the RSS is failed somehow.
  if ( is_wp_error($rss) ) {

    $error = $rss->get_error_code();

    $update_message = '<div class="update_available">Update notifier failed (<code>'.$error.'</code>)</div>';

    return $update_message;

  }

  //Figure out how many total items there are, but limit it to 5.
  $maxitems = $rss->get_item_quantity(1);

  // Build an array of all the items, starting with element 0 (first element).
  $rss_items = $rss->get_items(0, $maxitems);
  if ($maxitems == 0) $latest_version_via_rss = 0;
    else
    // Loop through each feed item and display each item as a hyperlink.
    foreach ( $rss_items as $item ) :
      $latest_version_via_rss = $item->get_title();
    endforeach;

  //Check if version is the latest - assume standard structure x.x.x
  $pieces_rss = explode(".", $latest_version_via_rss);
  $pieces_local = explode(".", $local_version);
  //account for null values in second position x.2.x

  if(isset($pieces_rss[0]) && $pieces_rss[0] != 0) {

    if (!isset($pieces_rss[1]))
      $pieces_rss[1] = '0';

    if (!isset($pieces_local[1]))
      $pieces_local[1] = '0';

    //account for null values in third position x.x.3
    if (!isset($pieces_rss[2]))
      $pieces_rss[2] = '0';


    if (!isset($pieces_local[2]))
      $pieces_local[2] = '0';


    //do the comparisons
    $version_sentinel = false;

    if ($pieces_rss[0] > $pieces_local[0]) {
      $version_sentinel = true;
    }
    if (($pieces_rss[1] > $pieces_local[1]) AND ($version_sentinel == false) AND ($pieces_rss[0] == $pieces_local[0])) {
      $version_sentinel = true;
    }
    if (($pieces_rss[2] > $pieces_local[2]) AND ($version_sentinel == false) AND ($pieces_rss[0] == $pieces_local[0]) AND ($pieces_rss[1] == $pieces_local[1])) {
      $version_sentinel = true;
    }

    //set version checker message
    if ($version_sentinel == true) {
      $update_message = '<div class="update_available">Theme update is available (v.' . $latest_version_via_rss . ') - <a href="http://www.welaikathemes.com/amember">Get the new version</a>.</div>';
    }
    else {
      $update_message = '';
    }
  } else {
      $update_message = '';
  }

  return $update_message;

}


?>