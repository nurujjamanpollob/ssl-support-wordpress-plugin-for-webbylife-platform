<?php

/**
 * Returns true if the url contains a number of paths
 * @param string $url
 * @param int $path_count path_count
 * @return bool. If true, the url contains the number of paths. This is starts from 0
 */
function url_contains_path_count(string $url, int $path_count): bool
{
	// parse the url
	$parsed_url = parse_url($url);

	// get the paths count
	$paths = explode('/', $parsed_url['path']);

	// check if the path count is equal to the number of paths in the url
	return $path_count === count($paths);
}

/**
 * Get the url paths
 * @param string $url
 * @return array. The paths in the url
 *
 */
function get_url_paths(string $url): array
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the path is set
	if (!isset($parsed_url['path'])) {
		return [];
	}

	// get the paths count
	$paths = explode('/', $parsed_url['path']);

	// remove empty paths
	return array_filter($paths, function ($path) {
		return !empty($path);
	});
}

/**
 * Returns true if url contains query string
 * @param string $url
 * @return bool. If true, the url contains query string
 */
function url_contains_query_string(string $url): bool
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the query string is set
	if (!isset($parsed_url['query'])) {
		return false;
	}

	return true;
}

/**
 *  Returns true if url contains a fragment
 * @param string $url
 * @return bool. If true, the url contains a fragment
 */
function url_contains_fragment(string $url): bool
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the fragment is set
	if (!isset($parsed_url['fragment'])) {
		return false;
	}

	return true;
}

/**
 * Returns true if url contains a user
 * @param string $url
 * @return bool. If true, the url contains a user
 */
function url_contains_user(string $url): bool
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the user is set
	if (!isset($parsed_url['user'])) {
		return false;
	}

	return true;
}

/**
 * Returns true if url contains a password
 * @param string $url
 * @return bool. If true, the url contains a password
 */
function url_contains_password(string $url): bool
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the password is set
	if (!isset($parsed_url['pass'])) {
		return false;
	}

	return true;
}

/**
 * Get how many paths are in the url
 * @param string $url
 * @return int. The number of paths in the url
 */
function url_path_count(string $url): int
{
	// parse the url
	$parsed_url = parse_url($url);

	// get the paths count
	$paths = explode('/', $parsed_url['path']);

	// remove empty paths
	$paths = array_filter($paths, function ($path) {
		return !empty($path);
	});

	return count($paths);
}

/**
 * Get a value for an url parameter
 * @param string $url
 * @param string $param
 * @return string|null. If the parameter is not found, return null
 */
function get_url_param(string $url, string $param): ?string
{
	// parse the url
	$parsed_url = parse_url($url);

	// check if the query string is set
	if (!isset($parsed_url['query'])) {
		return null;
	}

	// parse the query string
	parse_str($parsed_url['query'], $query_params);

	// check if the parameter is set
	if (!isset($query_params[$param])) {
		return null;
	}

	return $query_params[$param];
}

/**
 * Get the nth path from the url
 *
 * @param string $url
 * @param int $path_index
 *
 * @return string|null. If the path is not found, return null
 */
function get_url_path(string $url, int $path_index): ?string
{
	$url_paths = get_url_paths($url);

	// checking index
	if ($path_index < 0 || $path_index > count($url_paths)) {
		return null;
	}

	return $url_paths[$path_index];
}
