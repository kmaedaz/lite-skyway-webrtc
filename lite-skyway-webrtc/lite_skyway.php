<?php
/*
Plugin Name: Lite Skyway WebRTC
Plugin URI: 
Description: Easily use WebRTC by Skyway
Version: 0.1.0
Author: cybertube
Author URI: 
License: GPL2
*/

/*  Copyright 2019 

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


/* 開始 */


class LiteSkyway {
const LITE_SKYWAY_TAG ='[SKYWAY_ROOM]';
const LITE_SKYWAY_HTML= <<<EOT
      <div class="room" id="lite-skyway-webrtc-content">
        <div>
          <video id="js-local-stream"></video>
        </div>

        <div class="remote-streams" id="js-remote-streams"></div>

        <div>
          <button id="js-join-trigger"></button>
          <button id="js-leave-trigger"></button>
          <!--
		  <input type="checkbox" id="js-voice-trigger" name="js-voice-trigger">
		  <label for="js-voice-trigger" id="js-voice-trigger-label"> </label>
		  -->
        </div>

        <div>
          <pre class="messages" id="js-messages"></pre>
		   <textarea  id="js-local-text" rows="2" cols="30"></textarea>
          <button id="js-send-trigger"></button>
        </div>


      </div>
EOT;

  var $lang = null;
  var $display_name = null;
  var $room_mode ="mesh";
  function __construct() {
    if (!function_exists('init_setting')) {
      register_activation_hook(__FILE__, 'init_setting');
    }
	$this->lang =get_locale( );
	$user = wp_get_current_user();
	if($user->user_nicename) {
     $this->display_name = $user->user_nicename; //WordPress上のNiacknameを取得   
    } else {
     $this->display_name = "Guest";    
	}
    add_filter('the_content', array($this, 'replace_tag'), 11);
    add_filter('the_content', array($this, 'add_scripts'), 12);
    add_action('admin_init', array($this, 'init_setting'));
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  function init_setting() {
    if (!get_option('lite_skyway_webrtc_api_key')) {
      add_option('lite_skyway_webrtc_api_key', '');
      add_option('lite_skyway_webrtc_room_sfu_type', 0);
    }
  }

  function replace_tag($content) {
    $user = wp_get_current_user('subscriber');
    $token = get_option('lite_skyway_webrtc_api_key');
    $room = get_site_url()."_".get_the_ID();
	if(get_option('lite_skyway_webrtc_room_sfu_type') ){
		$this->room_mode="sfu";
	}
    $tmp_str = "<script> const ROOM_ID= '" .$room . "';</script>";
    $tmp_str.= "<script> const ROOM_MODE = '" .$this->room_mode  . "';</script>";
    $tmp_str.= "<script> const DISPLAY_NAME = '" .$this->display_name  . "';</script>";

    $tmp_str.= "<script> window.__SKYWAY_KEY__ = '" .$token . "';</script>";
    $html_str = $tmp_str. self::LITE_SKYWAY_HTML;
      return str_replace(self::LITE_SKYWAY_TAG,$html_str, $content);


  }

  function add_scripts($content) {
    if (strpos($content, 'lite-skyway-webrtc-content') !== false) {
      wp_register_script('skyway_latest', 'https://cdn.webrtc.ecl.ntt.com/skyway-latest.js', array(), null, false);
      wp_enqueue_script('skyway_latest');

	  wp_register_style( 'skyway-css', plugins_url( "css/style.css", __FILE__ ), array(), false );
      wp_enqueue_style('skyway-css');
	
      wp_register_script('lite_skyway_bundle', plugins_url('scripts/script.js', __FILE__), array(), null, false);
      wp_enqueue_script('lite_skyway_bundle');
    }
    return $content;
  }

  function add_admin_menu() {
    add_menu_page('Skyway WebRTC', 'Skyway WebRTC', 'administrator', __FILE__, array($this, 'config_page'), '',81);
  }

  function config_page() {
    if (!current_user_can('administrator')) {
      return;
    }
    
    if (!empty($_POST) && check_admin_referer('lite-skyway-options', 'lite-skyway-options-nonce')) {
      update_option('lite_skyway_webrtc_api_key', sanitize_text_field($_POST['lite_skyway_webrtc_api_key']));
      $room_mode_type_checkbox = isset($_POST['lite_skyway_webrtc_room_sfu_type']) ? 1 : 0;
      update_option('lite_skyway_webrtc_room_sfu_type', $room_mode_type_checkbox);
    }
?>

    <div class='wrap'>
      <h2>Skyway WebRTC</h2>
<?php


        if (isset($_POST['submit'])) {
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                  <p><strong>Saved</strong></p></div>';
        }
?>
      <p>First, get API Key from Skyway</P>
      <form method='post' action=''>
        <?php wp_nonce_field('lite-skyway-options', 'lite-skyway-options-nonce'); ?>
        <table class='form-table'>
          <tr>
            <th scope='row'><label for='lite_skyway_webrtc_api_key'>API Key</label></th>
            <td><input id='lite_skyway_webrtc_api_key' class='regular-text' name='lite_skyway_webrtc_api_key' type='text' value='<?php form_option("lite_skyway_webrtc_api_key"); ?>'></td>
          </tr>
          <tr>
            <th scope='row'><label for='lite_skyway_webrtc_room_sfu_type'>Use SFU type room</label></th>
            <td><input id='lite_skyway_webrtc_room_sfu_type' name='lite_skyway_webrtc_room_sfu_type' type='checkbox'<?php checked(1, get_option('lite_skyway_webrtc_room_sfu_type')); ?>'></td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>

<?php
  }
}

add_action('init', 'LiteSkyway', 5);

if (!function_exists('LiteSkyway')) {
  function LiteSkyway() {
    global $LiteSkyway;
    $LiteSkyway = new LiteSkyway();
  }
}
?>