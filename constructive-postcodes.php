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


function constructive_validate_text_postcode($result, $tag) {
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
