<?php


/**
 * Activation handler for the plugin.
 *
 * @return void
 */


function ssl_support_for_webbylife_platform_activation_handler() {

	// require a random generator
	require_once plugin_dir_path(__FILE__) . '../utility/random_generator.php';
	require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';
	// add an option to the database
	add_option(AUTH_KEY_SSL_SUPPORT_TOOL_WEBBYLIFE_PLATFORM, generateRandomString(), '', true);
	// add db table
	require_once plugin_dir_path(__FILE__) . '../dataaccess/tables.php';
	// create the table
	create_ssl_support_for_webbylife_platform_acme_challenge_data_table();

}
