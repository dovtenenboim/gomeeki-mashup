<!--
/**
 * Gomeeki Mashup   
 * 
 * (c) Dov Tenenboim. All Rights Reserved.
 *
 * This project, including its source code, may not be copied, referenced, or linked to
 * without prior consent. 
 */
-->
<!doctype html>
<html ng-app="Twitter">
	<head>
		<title>Dev Project</title>
		
		<meta content="yes" name="apple-mobile-web-app-capable">
		<meta content="minimum-scale=0.6, width=device-width, maximum-scale=0.6, user-scalable=no" name="viewport">
		
		<!-- CSS -->
		<link href="assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="assets/css/main.css" rel="stylesheet">
		
		<!-- JS -->
		<script type="text/javascript" src="assets/js/angular.min.js"></script>
		<script type="text/javascript" src="assets/js/angular-resource.min.js"></script>
		
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAV8SIzPzTPLzOsQGOBBXns-rCvPvlUeck&sensor=false"></script>
		
		<!-- Main Script -->
		<script type="text/javascript" src="assets/js/main.js"></script>
	</head>
	
	<body>
		<div ng-controller="MainController" id="app_contain">
			<div id="map">
				<div id="map-canvas"/>
			</div>
			
			<div id="bar">
				<form>
					<div id="s_field">
						<input type="text" ng-model="CityName" id="CityName">
					</div>
					
					<div id="s_button">
						<button ng-click="doSearch()" id="SearchButton">SEARCH</button>
					</div>
					
					<div id="s_history">
						<button ng-click="showHistory()" id="HistoryButton">HISTORY</button>
					</div>
					
				</form>
			</div>
		</div>
	</body>
</html>