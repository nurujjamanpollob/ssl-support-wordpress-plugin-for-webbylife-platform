<?php

/**
 * Generates a random string of a given length.
 *
 * @param int $length The length of the random string to generate.
 *
 * @return string The generated random string.
 */
function generateRandomString(int $length = 32): string {
	// characters include small letters, capital letters, numbers and special characters
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
	$charactersLength = strlen($characters);

	// generate random string
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;

}
