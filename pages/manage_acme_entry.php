<?php

/**
 * Manage the acme entry
 * It lists the acme entry, show option to mark al entries, delete, edit and pagination buttons.
 * Create the menu page
 */
require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';
require_once plugin_dir_path( __FILE__ ) . '../dataaccess/webbyplatformquery.php';
require_once plugin_dir_path( __FILE__ ) . '../output/html_output.php';


/**
 * Output the acme challenge data page
 * It lists the acme challenge data, it also accepts from a startentry index and endentry index
 */
function ssl_support_for_webbylife_platform_acme_challenge_data_page() {

	// check current user is admin
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// check if the user is logged in
	if ( ! is_user_logged_in() ) {
		return;
	}
	// check if the query and page are set, and non empty
	$query = is_query_string_set( 'q' ) ? get_query_string_value('q') : null;
	$page  = is_query_string_set( 'p' ) ? get_query_string_value('p') : 1;
	// convert to int
	$page = intval( $page );

	$entries = get_acme_challenge_data_by_key( $query );

	// if null, then return
	if ( $entries == null ) {
		// show no data message
		echo get_no_content_found_html_template();
	} else {

		// encode query string
		$query = urlencode( $query );

		echo get_acme_records_html_template(
			get_acme_challenge_data_for_page(
				$entries,
				$page
			),
			$page,
			count( $entries ),
			$query
		);
	}


}

/**
 * Used to handle database queries to get challenge data
 *
 * @param array|null $entries , the entries to be displayed
 * @param int $page , the page number, default is 1
 *
 * @return array
 */
function get_acme_challenge_data_for_page( array $entries = null, int $page = 1 ): ?array {

	// check if the entries are null
	if ( $entries == null ) {
		return null;
	}

	require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';

	// if page is less than 1, set it to 1
	if ( $page < 1 ) {
		$page = 1;
	}
	// handle first page
	if ( $page == 1 ) {
		//
		 return array_slice( $entries, 0, ACME_RECORDS_ITEMS_PER_PAGE );
	} else {

		// find the starting index by multiplying the page number by the items per page
		$start_index = ( $page - 1 ) * ACME_RECORDS_ITEMS_PER_PAGE;
		// find the end index by adding the items per page to the start index
		$end_index   = ACME_RECORDS_ITEMS_PER_PAGE;

		// if the end index is not bound, make it bound by the count of the entries
		if ( $start_index + $end_index > count( $entries ) ) {
			$end_index = count( $entries ) - $start_index;
		}

		// return the entries from the start index to the end index
		return array_slice( $entries, $start_index, $end_index );

	}
}

// makes the query and return results
function get_acme_challenge_data_by_key( ?string $query ): ?array {
	require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';

	$entries = $query == null ? wbPlatformQuery::getAllChallengeData() : wbPlatformQuery::findChallengeData( $query );

	// return null if no entries found
	if ( $entries == null || count( $entries ) == 0 ) {
		return null;
	}

	// return the entries
	return $entries;
}


// returns true if the query string is set and not empty
function is_query_string_set( $query_string ): bool {
	// get current url
	$current_url = $_SERVER['REQUEST_URI'];

	// parse the url
	$parsed_url = parse_url( $current_url );
	// check if the query string is set and not empty
	if ( isset( $parsed_url['query'] ) ) {
		parse_str( $parsed_url['query'], $query_params );
		if ( ! empty( $query_params[ $query_string ] ) ) {
			return true;
		}
	}

	return false;
}

// get a value for a query string
function get_query_string_value( $query_string ): ?string {
	// get current url
	$current_url = $_SERVER['REQUEST_URI'];

	// parse the url
	$parsed_url = parse_url( $current_url );
	// check if the query string is set and not empty
	if ( isset( $parsed_url['query'] ) ) {
		parse_str( $parsed_url['query'], $query_params );
		if ( ! empty( $query_params[ $query_string ] ) ) {
			return $query_params[ $query_string ];
		}
	}

	return null;
}