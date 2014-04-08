<?php
/**
 * Plugin Name: Constructive Postcodes
 * Plugin URI: http://richbs.org/
 * Description: Validates a text field against a database list of postcodes
 * Version: 0.1
 * Author: Rich Barrett-Small
 * Author URI: http://richbs.org/
 * License: BSD
 */

global $cpc_table_name;
global $wpdb;
$cpc_table_name = $wpdb->prefix . 'constructive_postcodes';

function cpc_install() {
	global $wpdb;
	global $cpc_table_name;
	$sql = "CREATE TABLE $cpc_table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  postcode VARCHAR(8) DEFAULT '' NOT NULL,
	  postcode_slug VARCHAR(7) DEFAULT '' NOT NULL,
	  UNIQUE KEY id (id),
	  UNIQUE KEY postcode (postcode),
	  UNIQUE KEY postcode_slug (postcode_slug)
	);";
	var_dump($sql);
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'cpc_install' );

function cpc_uninstall()
{
	global $wpdb;
	global $cpc_table_name;

	$table_name = $wpdb->prefix . 'constructive_postcodes';
	$sql = "DROP TABLE $cpc_table_name;";
	$wpdb->query($sql);
}

register_deactivation_hook(__FILE__, 'cpc_uninstall');

function cpc_validate_text_postcode($result, $tag) {
	$type = $tag['type'];
	$name = $tag['name'];

	if (substr($name, - strlen('postcode')) === 'postcode') {
		// We have a postcode field here
		$value = $_POST[$name];
		if ('N4 4NL' !== $value) {
			$result['reason'][$name] = 'Not a London Postcode';
			$result['valid'] = false;
		}
	}
	return $result;
}

add_filter( 'wpcf7_validate_text', 'constructive_validate_text_postcode', 10, 2 );
add_filter( 'wpcf7_validate_text*', 'constructive_validate_text_postcode', 10, 2 );
