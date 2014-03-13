	/**
	 * Gomeeki Mashup   
	 * 
	 * (c) Dov Tenenboim. All Rights Reserved.
	 *
	 * This project, including its source code, may not be copied, referenced, or linked to
	 * without prior consent. 
	 */
	 
	 
/**
 * addHistory
 * Adds city to the history array
 *
 * @param	city		Value to add to history 
 */

function addHistory(city) {
	// Confirm it's length
	if(window.historyobj.length > 10) {
		// Shorten the array 
		window.historyobj = window.historyobj.slice(window.historyobj.length - 9 , window.historyobj.length)
	}
	
	// Add new value 
	window.historyobj.push(city);
	
}

/**
 * rebuild
 * Rebuilds the main map.
 */
function rebuild() {
	// Default map location 
	window.mapOptions = {
		center: new google.maps.LatLng(-34.397, 150.644),
		zoom: 8
	};
	
	// Create the map
	window.map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
}

/**
 * doSearch
 * Runs a city search
 * 
 * @param	CityName		Search value (the city)
 */
function doSearch(CityName, $http) {
	// Rebuild the map
	rebuild();
	
	// Update inputbox value 
	document.getElementById("CityName").value = CityName;
	
	// Some quick fixes to the names 
	/* CityName = CityName.replace("newyork", "new york");
	CityName = CityName.replace("newzealand", "new zealand"); */
	
	
	// Use geocode to find the address location 
	window.geocode.geocode( {'address': CityName }, function(results, status) {
		if(results[0] == null) {
			// Nothing was found
			// Stop searching 
			cCountUp(); // Add to the counter checker 
			return;
		} else {
			// Update map to city location 
			window.map.setOptions({
				center: results[0].geometry.location,
				zoom: 10
			});
			
				
			// Array to store the values in
			var longlat_res = [];
			i = 0;
			
			// Fetch the longitude and latitude: always in order, however the
			// identifiers change a lot 
			for (var prop in results[0].geometry.location) {
				if (results[0].geometry.location.hasOwnProperty(prop)) { 
					longlat_res[i] = results[0].geometry.location[prop];
					i++;
				}
			}
			
			
			// Post to twitter.php to get tweets 
			$http.post(
				'./twitter.php', { 'city': (CityName), 'lng': longlat_res[1], 'lat': longlat_res[0], 'components': 'locality' }
			).success(function(data, status, headers, config) {
				
				document.getElementById("CityName").value = window.currentSearch;
				window.isSearching = false; 
				
				if (data != '') {
					console.log(data.length + " results given.");					
					// Sort through tweets 
					for (i = 0; i < data.length; i++) {
						// Create marker
						cTweet = '<div class="infowindow"><strong><a href="http://twitter.com/'+data[i].user+'">'+data[i].user+'</a></strong><br />'+data[i].tweet+'<br><i>'+data[i].timedate+'</i></div>';
						//cTweetID = data[i].long + ':' + data[i].lat;
						
						//window.tweetData[cTweetID] = cTweet;
						
						currentTweet[i] = new google.maps.Marker({
							position: new google.maps.LatLng(data[i].long, data[i].lat),
							map: window.map,
							icon: data[i].profile_picture,
							title: data[i].user+": " + data[i].tweet
						});
						
						// Add infobox to marker - use function outside of the loop to avoid conflicts 
						addMarker(currentTweet[i], cTweet, i);
	
					}
					
				} else {
					// No data returned, may not be an error - simply no data possibly
					console.log('Error: empty data');
					console.log(data);
				}
			}).error(function(data, status) {
				// An error occured, tell the console 
				console.log('Error: ('+status+') ' + data);
			});
		}

	});  

		
}

/**
 * addMaker
 * Adds a marker to the existing map
 * 
 * @param	currentTweet	A Marker
 * @param	message 		The content of the infowindow
 */
function addMarker(currentTweet, message, unique) {
	
	// Check for current infoWindow object 
	if(window.infoWindow != null) {
		window.infoWindow.close();
	}
	
	// Create InfoWindow function  
	window.InfoBoxFunction[unique] = function() {
		
		if(window.current_ib != null) { window.current_ib.close(); }
		
		window.infoWindow = new google.maps.InfoWindow({
			content: message
		})
		
		window.infoWindow.open(window.map, currentTweet);
		window.current_ib = infoWindow;
	};
	
	// Add event 
	google.maps.event.addListener(currentTweet, 'click', InfoBoxFunction[unique])
	
	
}

/**
 * MainController
 * The main controller
 * 
 * @param	$scope		The scope
 * @param	$http 		HTTP Resource
 */
function MainController($scope, $http) {
	// 'HISTORY' button
	$scope.showHistory = function() {
		
		// Take of the map canvas 
		document.getElementById('map-canvas').style.backgroundColor='#FFFFFF';
		
		// Add the back button
		document.getElementById('map-canvas').innerHTML = '<span class="history_item"><a onclick="rebuild()" href="#">BACK TO THE TWEETS!</a></span><br>';
		
		// Loop through history elements 
		for (i = 0; i < window.historyobj.length; i++) {
			document.getElementById('map-canvas').innerHTML = document.getElementById('map-canvas').innerHTML + '<span class="history_item"><a onclick="rebuild();customSearch(\''+window.historyobj[i]+'\')" href="#">'+window.historyobj[i]+'</a></span><br>';
		}
		
		
	}
	
	// 'SEARCH' button
	$scope.doSearch = function () {
		
		// Re-set the cCount value 
		window.ccount = 0;
		
		// Let it know we're searching
		window.currentSearch = $scope.CityName;
		window.isSearching = true;
		
		// Add to the history
		addHistory($scope.CityName);
		
		// Search for a hash tag
		testa = doSearch("#"+$scope.CityName.replace(' ', '').replace('#', ''), $http);
		
		// Search for the city name 
		testb = doSearch($scope.CityName.replace('#', ''), $http);
		
		
	};
}


/**
 * cCountUp
 * This counts how many failed searchers there as, as we're searching twice we're looking
 * for two failed searches. Once there is two failed searches we'll display the message
 */
function cCountUp() {
	window.ccount++;
	
	if(ccount == 2) {
		// Two searches have failed. Re-set. 
		document.getElementById("CityName").value = "Error, please enter a valid city!";
		window.isSearching = false; 
	}
}

/**
 * initialise
 * This function initialises most of the script
 * triggered by Google Maps 
 * 
 */
function initialise() {
	window.geocode = new google.maps.Geocoder();
	
	// Default location of map
	window.mapOptions = {
		center: new google.maps.LatLng(-34.397, 150.644),
		zoom: 8
	};
	
	// Create the map
	window.map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
	
	// Default variable states
	window.currentTweet = [];
	window.infowindow = [];
	window.infoBoxContent = [];
	window.gmaplisten = [];
	
	window.historyobj = [];
	
	window.isSearching = false;
	window.searchStage = 0;
	
	window.infoWindow = null;
	window.current_ib = null;
	
	window.InfoBoxFunction = [];
	
	// Loop for load detector 
	setInterval(function(){
		loadDetect();
	}, 400);
}

/**
 * loadDetect()
 * Shows loading message in inputbox
 */
function loadDetect() {
	// Detect if searching
	if(window.isSearching) {
	
		// Disable inputs 
		document.getElementById('SearchButton').disabled = true;
		document.getElementById("CityName").disable = true;
		
		// Create a loading animation
		if(window.searchStage == 0) {
			document.getElementById("CityName").value = 'Loading .';
			window.searchStage = 1;
		} else if(window.searchStage == 1) {
			document.getElementById("CityName").value = 'Loading . . ';
			window.searchStage = 2;
		} else if(window.searchStage == 2) {
			document.getElementById("CityName").value = 'Loading . . .';
			window.searchStage = 0;
		}
	} else {
		// since not searching, enable the inputs 
		document.getElementById('SearchButton').disabled = false;
		document.getElementById("CityName").disable = false;
	}
}

/**
 * Here we go! 
 */
 
// Angular
angular.module('Twitter', ['ngResource']);

// Google maps
google.maps.event.addDomListener(window, 'load', initialise);