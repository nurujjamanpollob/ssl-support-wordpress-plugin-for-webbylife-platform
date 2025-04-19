<?php

/**
 * Create database table at plugin activation, only if it does not exist.
 * It creates table called ssl_support_for_webbylife_platform_acme_challenge_data
 */
function create_ssl_support_for_webbylife_platform_acme_challenge_data_table() {

	require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';

	global $wpdb;
	$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

	// use create table if not exists
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        domain varchar(255) NOT NULL,
        value varchar(1000) NOT NULL,
        challenge_key varchar(255) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY challenge_key (challenge_key)
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

}

