<?php
/*
  Plugin Name: Bytion Assessment
  Plugin URI: http://donnyprabowo.com
  Description: Bytion Assessment Task 1 - 3
  Version: 1.0
  Author: Donny Prabowo
  Author URI: http://donnyprabowo.com
*/
 
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// load plugin text domain
function bytion_init() { 
	load_plugin_textdomain( 'bytion-assessment', false, dirname( plugin_basename( __FILE__ ) ) . '/translation' );
}
add_action('plugins_loaded', 'bytion_init');

function bytion_style_script() {
	wp_enqueue_style( 'bytion-style', plugins_url('/css/bytion-form.css',__FILE__) );
}
add_action( 'wp_enqueue_scripts', 'bytion_style_script', 99 );

// function to get ip of user
function bytion_get_the_ip() {
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	else {
		return $_SERVER["REMOTE_ADDR"];
	}
}

// include form and cpt files
include 'bytion-form-create-db.php';
include 'bytion-form.php';
include 'bytion-cpt.php';

?>