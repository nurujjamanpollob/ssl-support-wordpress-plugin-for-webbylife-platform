<?php

/**
 * Error response for the acme challenge get challenge content
 * @param string $message is the message to be output in message key
 * @param int $code is the response code. default is 400
 * @note output the message in json format
 */
function output_acme_challenge_error_response_json(string $message, int $code = 400) {

	// set the content type to application/json
	header('Content-Type: application/json');
	// set the response code to 400
	http_response_code($code);

	// create the response
	$response = [
		'status' => 'error',
		'message' => $message,
	];

	// return the JSON encoded string
	echo json_encode($response);
	// exit the script
	exit;
}

/**
 * Success response for the acme challenge get challenge content
 * @param string $message is the message to be output
 */
function output_acme_challenge_success_response(string $message) {
	// set the content type to text/plain
	header('Content-Type: text/plain');
	// set the response code to 200
	http_response_code(200);
	// create the response
	echo $message;

	// exit the script
	exit;
}
