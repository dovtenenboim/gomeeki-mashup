<?php
	/**
	 * Gomeeki Mashup   
	 * 
	 * (c) Dov Tenenboim. All Rights Reserved.
	 *
	 * This project, including its source code, may not be copied, referenced, or linked to
	 * without prior consent. 
	 */
	 
	 
	 
	/**
	 * filterCity
	 * Filters the city name
	 *
	 * @param		$city		The city name
	 */
	function filterCity($city) {
		$city = str_replace(" ", "", $city);
		$city = str_replace("'", "", $city); // This also fixes security issues
		$city = str_replace("%", "", $city); // This (again) also fixes security issues
		$city = str_replace(".", "", $city);
		$city = str_replace("#", "", $city);
		
		return $city;
	}
	
	/**
	 * updateDatabase
	 * Updates information in the databsae
	 *
	 * @param		$city		The city name
	 * @param		$data		Data for the database
	 */
	function updateDatabase($city, $data) {
		global $database;
		
		// Work with an variation of the city name
		$city = filterCity($city);
		
		$city = strtolower($city);
		
		$database->query("DELETE FROM `tweet_cache` WHERE city='{$city}';"); // remove old data (if it exists..)
		
		$r = $database->query("INSERT INTO `tweet_cache` (`city`, `results`, `timedatas`) VALUES ('{$city}', '{$data}', '".time()."');");
	}
	
	/**
	 * shouldUseOldData
	 * Determines if the data for a specific city is too old or not
	 *
	 * @param		$city		The city name
	 */
	function shouldUseOldData($city) {
		global $database;
		
		$city = filterCity($city);
		
		// Run query to get timestamp
		$result = $database->query("SELECT timedatas FROM tweet_cache WHERE city='{$city}' LIMIT 1");
		
		if($result->num_rows == 0) {
			return false; // No old data found 
		}
		// Fetch row
		$result_row = ($result->fetch_row());
		
		// Get time difference 
		
		$oldtime = new DateTime($result_row['timedatas']);
		
		if($oldtime->diff(new DateTime(now()))->h >= 1 ) {
			return false; // Old data too old 
		} 
				
		$result->close();
		
		
		return true; // Old data is ok
	}
	
	/**
	 * getOldData
	 * Get's stored data for a city 
	 *
	 * @param		$city		The city name
	 */
	function getOldData($city) {
		global $database;
		
		$city = filterCity($city);
		
		$result = $database->query("SELECT data FROM tweet_cache WHERE city='".filterCity($city)."' LIMIT 1");
		
		$result_row = ($result->fetch_row());
		
		return($result_row[0]['results']);
		
	}
	