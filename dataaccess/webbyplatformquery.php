<?php

class wbPlatformQuery {

	/**
	 * Add new challenge data to the database
	 * @param string $domain is the domain name
	 * @param string $value is the value of the challenge
	 * @param string $challenge_key is the challenge key
	 * @return bool true if the data is added successfully, false otherwise
	 */
	public static function addChallengeData(string $domain, string $value, string $challenge_key): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';


		$domain = sanitize_text_field($domain);
		$value = sanitize_text_field($value);
		$challenge_key = sanitize_text_field($challenge_key);


		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// insert the data into the database
		return $wpdb->insert($table_name, array(
			'domain' => $domain,
			'value' => $value,
			'challenge_key' => $challenge_key
		));
	}

	/**
	 * Get challenge data from the database
	 * @param string $challenge_key is the challenge key
	 * @return string|null the value of the challenge or null if not found
	 */
	public static function getValueOfAChallengeData(string $challenge_key): ?string {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the challenge key
		$challenge_key = sanitize_text_field($challenge_key);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// get the value of the challenge, limit to 1
		$result = $wpdb->get_row($wpdb->prepare("SELECT value FROM $table_name WHERE challenge_key = %s LIMIT 1", $challenge_key));

		// check if the result is not empty
		if ($result) {
			return $result->value;
		}

		// if the result is empty, return null
		return null;
	}

	/**
	 * Get all challenge data from the database
	 * @return array|null the value of the challenge or null if not found
	 */
	public static function getAllChallengeData(): ?array {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';


		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// get the value of the challenge, limit to 1
		$result = $wpdb->get_results("SELECT * FROM $table_name");

		// check if the result is not empty
		if ($result) {
			return $result;
		}

		// if the result is empty, return null
		return null;
	}

	/**
	 * Delete challenge data from the database
	 * @param string $challenge_key is the challenge key
	 * @return bool true if the data is deleted successfully, false otherwise
	 */
	public static function deleteChallengeData(string $challenge_key): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the challenge key
		$challenge_key = sanitize_text_field($challenge_key);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// delete the data from the database
		return $wpdb->delete($table_name, array(
			'challenge_key' => $challenge_key
		));
	}

	/**
	 * Delete all challenge data from the database
	 * @return bool true if the data is deleted successfully, false otherwise
	 */
	public static function deleteAllChallengeData(): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// delete the data from the database
		return $wpdb->query("DELETE FROM $table_name");
	}

	/**
	 * Check if the challenge data exists in the database
	 * @param string $challenge_key is the challenge key
	 * @return bool true if the data exists, false otherwise
	 */
	public static function challengeDataExists(string $challenge_key): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the challenge key
		$challenge_key = sanitize_text_field($challenge_key);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// check if the data exists in the database
		$result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE challenge_key = %s", $challenge_key));

		return $result > 0;
	}

	/**
	 * Delete the list of challenge data from the database
	 * @param array $challenge_keys is the list of challenge keys
	 * @return bool true if the data is deleted successfully, false otherwise
	 */
	public static function deleteChallengeDataList(array $challenge_keys): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the challenge keys
		$challenge_keys = array_map('sanitize_text_field', $challenge_keys);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// delete the data from the database
		return $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE challenge_key IN (" . implode(',', array_fill(0, count($challenge_keys), '%s')) . ")", $challenge_keys));
	}

	/**
	 * Update the challenge value in the database
	 * @param string $challenge_key is the challenge key
	 * @param string $value is the value of the challenge
	 * @return bool true if the data is updated successfully, false otherwise
	 */
	public static function updateChallengeValue(string $challenge_key, string $value): bool {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the challenge key and value
		$challenge_key = sanitize_text_field($challenge_key);
		$value = sanitize_text_field($value);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// update the data in the database
		return $wpdb->update($table_name, array(
			'value' => $value
		), array(
			'challenge_key' => $challenge_key
		));
	}

	/**
	 * Get the challenge data, from a start index to an end index -
	 * For example, fetchChallengeData(0, 10) will return the first 10 challenge data
	 * @param int $start_index is the start index
	 * @param int $end_index is the end index
	 * @return array|null the challenge data or null if not found
	 */
	public static function fetchChallengeData(int $start_index, int $end_index): ?array {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// get the challenge data from the database
		$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name LIMIT %d, %d", $start_index, $end_index));

		// check if the result is not empty
		if ($result) {
			return $result;
		}

		// if the result is empty, return null
		return null;
	}

	/**
	 * This method finds specific challenge data from the database, by, domain or challenge key
	 * @param string $query is the query to search
	 */
	public static function findChallengeData(string $query): ?array {
		require_once plugin_dir_path(__FILE__) . '../utility/Variables.php';

		// sanitize the query
		$query = sanitize_text_field($query);

		global $wpdb;
		$table_name = $wpdb->prefix . SSL_CHALLENGE_DATA_TABLE_NAME;

		// get the challenge data from the database
		$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE domain LIKE %s OR challenge_key LIKE %s", '%' . $wpdb->esc_like($query) . '%', '%' . $wpdb->esc_like($query) . '%'));

		// check if the result is not empty
		if ($result) {
			return $result;
		}

		// if the result is empty, return null
		return null;
	}

}
