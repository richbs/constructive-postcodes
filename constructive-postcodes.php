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

if ( ! defined( 'CPC_PLUGIN_DIR' ) )
	define( 'CPC_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

function slugify_postcode($pc) {
	return strtolower(trim(str_replace(' ', '', $pc)));
}

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
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	ini_set('max_execution_time', 120);

	$london_pcs = fopen(CPC_PLUGIN_DIR.'/London_postcodes.csv', 'r');

	while (($row = fgetcsv($london_pcs, 1000, ",")) !== false) {
		$pc = $row[0];
		$pc_slug = slugify_postcode( $pc );
		$affected_rows = $wpdb->insert(
			$cpc_table_name,
			array('postcode' => $pc, 'postcode_slug' => $pc_slug)
		);
	}
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
	global $wpdb;
	global $cpc_table_name;

	$type = $tag['type'];
	$name = $tag['name'];

	if (substr($name, - strlen('postcode')) === 'postcode') {
		// We have a postcode field here
		$value = $_POST[$name];
		$pc_slug = slugify_postcode($value);
		# Check for a minimum length (postcodes must be > 5 chars)
		if (strlen($pc_slug) > 4) {
			# Chop last character off to check against shorter list
			$pc_slug = substr($pc_slug, 0, -1);
			# check if there's a match
			$confirmed_pc = $wpdb->get_row(
				$wpdb->prepare(
					"
						SELECT * FROM $cpc_table_name
						WHERE postcode_slug LIKE %s
					",
					like_escape($pc_slug) . '%'
			) );
			if (null === $confirmed_pc) {
				$result['reason'][$name] = 'Not a London postcode';
				$result['valid'] = false;
			}
		} else {
			$result['reason'][$name] = 'Postcode too short';
			$result['valid'] = false;
		}
	}
	return $result;
}

add_filter( 'wpcf7_validate_text', 'cpc_validate_text_postcode', 10, 2 );
add_filter( 'wpcf7_validate_text*', 'cpc_validate_text_postcode', 10, 2 );
