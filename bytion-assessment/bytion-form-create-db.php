<?php 

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Creates database table for Bytion Form
*/
function bytion_form_create_db() {

	global $wpdb;
	global $charset_collate;
	
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'bytion_form';

	$sql = "CREATE TABLE $table_name (
			form_id INT(9) NOT NULL AUTO_INCREMENT,
			form_name VARCHAR(250) NOT NULL,
			form_email VARCHAR(100) NOT NULL,
			PRIMARY KEY  (form_id)
	) $charset_collate;";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $sql );
	
	$version = get_option( 'bytion_version' , '1.0' );
	
	if( version_compare($version, '2.0', '<=')) {
    // Update database table to include a field for date/time submitted
		$sql = "CREATE TABLE $table_name (
		  form_id INT(9) NOT NULL AUTO_INCREMENT,
			form_name VARCHAR(250) NOT NULL,
			form_email VARCHAR(100) NOT NULL,
			form_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (form_id)
		) $charset_collate;";
		
		dbDelta( $sql );	
	  update_option( 'bytion_version', BYTION_VERSION );
	}
	
}
register_activation_hook( __FILE__, 'bytion_form_create_db' );
add_action( 'init', 'bytion_form_create_db', 1 );

?>