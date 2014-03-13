<?php
	/**
	 * Gomeeki Mashup   
	 * 
	 * (c) Dov Tenenboim. All Rights Reserved.
	 *
	 * This project, including its source code, may not be copied, referenced, or linked to
	 * without prior consent. 
	 */
	
	// Our required files 
	require_once("functions.inc.php");
	require_once("TwitterAPIExchange.php");
	
	// Connect to the database - must be done before interacting with functions 
	$database = new MySQLi("localhost", "gomeeki_mashup", "!TS)%ruMseVi", "gomeeki_mapsmashup");
	
	// Get input 
	$data = json_decode(file_get_contents("php://input"), true);
	
	$city = $data['city'];
	
	// Should we use old data?
	if(shouldUseOldData($city)) {
		echo(getOldData($city)); // Yes, we should.
	} else {
	
		// Authenticate using TwitterAPIExchange Class
		$twitter = new TwitterAPIExchange(array(
		    'oauth_access_token' => "292708594-ulgO1pGigTgZeNRL5wI2g4uHaYMhozh9VhNofhIZ",
		    'oauth_access_token_secret' => "QIThtv9V2GPqdQGynpyTaQc0Conz573B8MT1DgNg5hpDt",
		    'consumer_key' => "tVxqA9OJNEvHzB72F9aE4w",
		    'consumer_secret' => "OJPbcd4rmQB7VF79pa5lYPEyLQf6Ua5dHLWuKpso"
		));
		
		// Create a query to the twitter API 
		$url = 'https://api.twitter.com/1.1/search/tweets.json';
		$getfield = "?count=50&q=".urlencode($city)."&geocode={$data['lat']},{$data['lng']},50km&result_type=recent";
		$requestMethod = 'GET';
		$results = json_decode($twitter->setGetfield($getfield)
		             ->buildOauth($url, $requestMethod)
		             ->performRequest(), true);
		
		
		// Check for tweets
		if(!is_array($results['statuses'])) {
			exit("no data"); // no data.. 
		}
		
		$i = 0;
		
		// Part of the randomiser is to base the location off of previous location data 
		$prev_Long = $data['lng'];
		$prev_Lati = $data['lat'];
				
		foreach($results['statuses'] as $tweet) {
		
			// Fetch tweet information 
			$tweets[$i]['user'] 				= $tweet['user']['screen_name'];
			$tweets[$i]['profile_picture'] 		= $tweet['user']['profile_image_url'];
			$tweets[$i]['tweet'] 				= $tweet['text'];
			$tweets[$i]['timedate']				= explode("+", $tweet['created_at'])[0];
			
			// Are the coordinates public?
			if(isset($tweet['geo']['coordinates'])) {
				$tweets[$i]['long'] 				= $tweet['geo']['coordinates'][0];
				$tweets[$i]['lat'] 					= $tweet['geo']['coordinates'][1];
				
			} else {
				// Coordinates are not public - this is a randomniser to spread them nearby 
				
				// Determine if we should plus or minus 
				if(rand(1,2) == 1) {
					$tweets[$i]['long'] 			= $prev_Long+(mt_rand (1, rand(1,6)) / 1000); // creates a really tiny unit
				} else {
					$tweets[$i]['long'] 			= $prev_Long-(mt_rand (1, rand(1,6)) / 1000);
				}
				
				if(rand(1,2) == 1) {
					$tweets[$i]['lat'] 				= $prev_Lati+(mt_rand (1, rand(1,6)) / 1000);
				} else {
					$tweets[$i]['lat'] 				= $prev_Lati-(mt_rand (1, rand(1,6)) / 1000);
				}
				
			}
			
			// Store the new prev long/lat
			$prev_Long = $tweets[$i]['long'];
			$prev_Lati = $tweets[$i]['lat'];

			$i++;

			
			
		};
		
		// Data is ready 
		$data = (json_encode($tweets));
		
		// Store it in the database
		updateDatabase($city, $data);
		
		// Send it back to the browser
		echo($data);
	}
