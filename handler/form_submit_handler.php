<?php

// handle post request of when ssl_support_for_webbylife_platform_nonce
//    ssl_support_for_webbylife_platform_key_length
//    ssl_support_for_webbylife_platform_generate_key\
// is submitted

require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';
// verify the request
require_once plugin_dir_path( __FILE__ ) . '../utility/url_utility.php';
require_once plugin_dir_path( __FILE__ ) . '../response/json_responses_string.php';
require_once plugin_dir_path( __FILE__ ) . '../dataaccess/webbyplatformquery.php';

function handle_ssl_support_form_submission() {
	// Check if the required POST fields are set
	if (
		isset( $_POST['ssl_support_for_webbylife_platform_nonce'] ) &&
		wp_verify_nonce( $_POST['ssl_support_for_webbylife_platform_nonce'], 'ssl_support_for_webbylife_platform_nonce' ) &&
		isset( $_POST['ssl_support_for_webbylife_platform_key_length'] ) &&
		isset( $_POST['ssl_support_for_webbylife_platform_generate_key'] )
	) {
		// Check if the user has permission to manage options
		if ( current_user_can( 'manage_options' ) ) {
			// Get the key length from the POST request
			$key_length = intval( $_POST['ssl_support_for_webbylife_platform_key_length'] );

			// Validate the key length
			if ( $key_length < 16 || $key_length > 512 ) {
				// navigate to the settings page, with a query parameter, that the key is not valid
				wp_redirect(
					admin_url( 'admin.php?page=ssl-support-for-webbylife-platform&is_key_valid=false' )
				);
			} else {
				// Require the random generator utility
				require_once plugin_dir_path( __FILE__ ) . '../utility/random_generator.php';

				// Generate a new random key
				$auth_key = generateRandomString( $key_length );

				// Update the auth key in the database
				update_option( AUTH_KEY_SSL_SUPPORT_TOOL_WEBBYLIFE_PLATFORM, $auth_key );

				// navigate to the settings page, with a query parameter, that the key is updated
				wp_redirect(
					admin_url( 'admin.php?page=ssl-support-for-webbylife-platform&is_key_valid=true' )
				);
				exit;

			}
		}
	}
}

// add init hook to handle the form submission
add_action( 'init', 'handle_ssl_support_form_submission' );

add_action( 'init', function () {
	// Get the current request URI
	$request_uri = $_SERVER['REQUEST_URI'];

	// Check if the request matches the desired pattern[acme-challenge] get data
	if ( strpos( $request_uri, '/.well-known/acme-challenge/' ) === 0
	     && url_path_count( $request_uri ) == 3 ) {

		$is_contains_query_string = url_contains_query_string( $request_uri );
		$is_contains_user         = url_contains_user( $request_uri );
		$is_contains_password     = url_contains_password( $request_uri );


		// if either the url contains a query string or user or password, return false
		if ( $is_contains_query_string || $is_contains_user || $is_contains_password ) {

			output_acme_challenge_error_response_json( 'Invalid request.', 500 );
		}

		// get the 3rd path from the url
		$acme_challenge_key = get_url_path( $request_uri, 3 );

		// if the acme_challenge_key is matches with the auth key, that means testing is conducting and is successful
		if ( $acme_challenge_key == 'ping' ) {
			// create json response
			$success_response = array(
				'status' => 'success',
				'message' => 'Testing GET API Integration is successful.',
				'key' => 'ping'
			);
			// output the success response
			$output = json_encode( $success_response );
			// return the response
			header( 'Content-Type: application/json' );
			header( 'HTTP/1.1 200 OK' );
			echo $output;
			exit;
		}

		// now query the database to get the value of the challenge
		$value = wbPlatformQuery::getValueOfAChallengeData( $acme_challenge_key );

		// if the value is null, return error
		if ( $value == null ) {
			output_acme_challenge_error_response_json( 'Failed to get challenge data.' );
		}

		// send the acme challenge key to the client
		output_acme_challenge_success_response( $value );

	}

	// Check if the request matches the desired pattern: site.com/api/v1/add-ssl-challenge-data with post request
	// needs: domain, value, challenge_key, api_key
	if ( strpos( $request_uri, '/api/v1/add-ssl-challenge-data' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

		// check all parameters are set
		if ( ! isset( $_POST['domain'] ) || ! isset( $_POST['value'] ) || ! isset( $_POST['challenge_key'] ) || ! isset( $_POST['api_key'] ) ) {
			output_acme_challenge_error_response_json( 'Invalid request.', 400 );
		}

		// get the post-data
		$domain        = $_POST['domain'];
		$value         = $_POST['value'];
		$challenge_key = $_POST['challenge_key'];
		$api_key       = $_POST['api_key'];


		// check if the api key is valid
		if ( $api_key != get_option( AUTH_KEY_SSL_SUPPORT_TOOL_WEBBYLIFE_PLATFORM ) ) {
			output_acme_challenge_error_response_json( 'Invalid API key.', 401 );
		}

		// now add them to the database
		if ( wbPlatformQuery::addChallengeData( $domain, $value, $challenge_key ) ) {
			output_acme_challenge_success_response( 'Challenge data added successfully.' );
		} else {
			output_acme_challenge_error_response_json( 'Failed to add challenge data.', 500 );
		}

	}

	// handle clean all acme challenge data: pattern: /api/v1/clean-all-acme-challenge-data
	// needs: action, nonce
	if ( strpos( $request_uri, '/api/v1/clean-all-acme-challenge-data' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

		// check all parameters are set
		if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) ) {
			output_acme_challenge_error_response_json( 'Invalid request.', 400 );
		}

		// check if the action is delete_all_acme_challenge_data
		if ( $_POST['action'] != 'delete_all_acme_challenge_data' ) {
			output_acme_challenge_error_response_json( 'Invalid action.', 400 );
		}

		// check if the nonce is valid
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ssl_support_for_webbylife_platform_nonce' ) ) {
			output_acme_challenge_error_response_json( 'Invalid nonce.', 401 );
		}

		// check if the user is admin and administrator
		if ( ! is_user_admin_and_administrator() ) {
			output_acme_challenge_error_response_json( 'You do not have permission to perform this action.', 403 );
		}

		wbPlatformQuery::deleteAllChallengeData();

		// success response json formtted String
		$success_response = array(
			'status'  => 'success',
			'message' => 'All acme challenge data deleted successfully.',
		);
		// output the success response
		$output = json_encode( $success_response );

		// return the response
		header( 'Content-Type: application/json' );
		header( 'HTTP/1.1 200 OK' );
		echo $output;
		exit;


	}

	// handle edit acme challenge data: pattern: /api/v1/edit-acme-challenge-data
	// needs: challenge_key, new_value, nonce
	if ( strpos( $request_uri, '/api/v1/edit-acme-challenge-data' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

		// check all parameters are set
		if ( ! isset( $_POST['challenge_key'] ) || ! isset( $_POST['new_value'] ) || ! isset( $_POST['nonce'] ) ) {
			output_acme_challenge_error_response_json( 'Invalid request.', 400 );
		}

		// check if the nonce is valid
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ssl_support_for_webbylife_platform_nonce' ) ) {
			output_acme_challenge_error_response_json( 'Invalid nonce.', 401 );
		}

		// check if the user is admin and administrator
		if ( ! is_user_admin_and_administrator() ) {
			output_acme_challenge_error_response_json( 'You do not have permission to perform this action.', 403 );
		}

		 $result = wbPlatformQuery::updateChallengeValue(
			$_POST['challenge_key'],
			$_POST['new_value']
		);

		// output the result in JSON format
		if ( $result ) {
			$success_response = array(
				'status'  => 'success',
				'message' => 'Challenge data updated successfully.',
			);
			// output the success response
			$output = json_encode( $success_response );
			// return the response
			header( 'Content-Type: application/json' );
			header( 'HTTP/1.1 200 OK' );
			echo $output;
			exit;
		} else {
			output_acme_challenge_error_response_json( 'Failed to update challenge data.', 500 );
		}

	}

	// handle delete acme challenge data: pattern: /api/v1/delete-acme-challenge-data
	// needs: challenge_keys[comma separated], nonce
	if ( strpos( $request_uri, '/api/v1/delete-acme-challenge-data' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

		// check all parameters are set
		if ( ! isset( $_POST['challenge_keys'] ) || ! isset( $_POST['nonce'] ) ) {
			output_acme_challenge_error_response_json( 'Invalid request.', 400 );
		}

		// check if the nonce is valid
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ssl_support_for_webbylife_platform_nonce' ) ) {
			output_acme_challenge_error_response_json( 'Invalid nonce.', 401 );
		}

		// check if the user is admin and administrator
		if ( ! is_user_admin_and_administrator() ) {
			output_acme_challenge_error_response_json( 'You do not have permission to perform this action.', 403 );
		}

		$challenge_keys = explode( ',', $_POST['challenge_keys'] );

		$result = wbPlatformQuery::deleteChallengeDataList( $challenge_keys );

		// output the result in JSON format
		if ( $result ) {
			$success_response = array(
				'status'  => 'success',
				'message' => 'Challenge data deleted successfully.',
			);
			// output the success response
			$output = json_encode( $success_response );
			// return the response
			header( 'Content-Type: application/json' );
			header( 'HTTP/1.1 200 OK' );
			echo $output;
			exit;
		} else {
			output_acme_challenge_error_response_json( 'Failed to delete challenge data.', 500 );
		}


	}

	// handle single entry delete acme challenge data: pattern: /api/v1/delete-single-acme-challenge-data
	// needs: challenge_key, nonce
	if ( strpos( $request_uri, '/api/v1/delete-single-acme-challenge-data' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

		// check all parameters are set
		if ( ! isset( $_POST['challenge_key'] ) || ! isset( $_POST['nonce'] ) ) {
			output_acme_challenge_error_response_json( 'Invalid request.', 400 );
		}

		// check if the nonce is valid
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ssl_support_for_webbylife_platform_nonce' ) ) {
			output_acme_challenge_error_response_json( 'Invalid nonce.', 401 );
		}

		// check if the user is admin and administrator
		if ( ! is_user_admin_and_administrator() ) {
			output_acme_challenge_error_response_json( 'You do not have permission to perform this action.', 403 );
		}

		$result = wbPlatformQuery::deleteChallengeDataList( array( $_POST['challenge_key'] ) );

		if ( $result ) {

			$success_response = array(
				'status'  => 'success',
				'message' => 'Challenge data deleted successfully.',
			);
			// output the success response
			$output = json_encode( $success_response );
			// return the response
			header( 'Content-Type: application/json' );
			header( 'HTTP/1.1 200 OK' );
			echo $output;
			exit;

		} else {
			output_acme_challenge_error_response_json( 'Failed to delete challenge data.', 500 );
		}

	}

	// listen to ping request, to ensure service is up
	if ( strpos( $request_uri, '/api/v1/ping' ) === 0 && url_path_count($request_uri) == 3
	     && $_SERVER['REQUEST_METHOD'] === 'GET' ) {

		// output the result in JSON format
		$success_response = array(
			'status'  => 'success',
			'message' => 'Service is up and running.',
		);
		// output the success response
		$output = json_encode( $success_response );
		// return the response
		header( 'Content-Type: application/json' );
		header( 'HTTP/1.1 200 OK' );
		echo $output;
		exit;

	}


} );

function is_user_admin_and_administrator(): bool {
	if (!current_user_can('manage_options')) {
		return false;
	}

	// check if the user is logged in
	if (!is_user_logged_in()) {
		return false;
	}

	// check if the user has the required capability
	if (!current_user_can('administrator')) {
		return false;
	}

	return true;
}
