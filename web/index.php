<!DOCTYPE html>
<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '1757820037876451',
  'app_secret' => '60c8c8ea0050124a74860fda9d797db9',
  'default_graph_version' => 'v2.8',
]);

$helper = $fb->getCanvasHelper();

$permissions = ['email', 'user_tagged_places', 'user_likes']; // optionnal

try {
	if (isset($_SESSION['facebook_access_token'])) {
	$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {

	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		$_SESSION['facebook_access_token'] = (string) $accessToken;

	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	// validating the access token
	try {
		$request = $fb->get('/me');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		if ($e->getCode() == 190) {
			unset($_SESSION['facebook_access_token']);
			$helper = $fb->getRedirectLoginHelper();
			$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
			echo "<script>window.top.location.href='".$loginUrl."'</script>";
			exit;
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	// getting basic info about user
	try {
		$profile_request = $fb->get('/me?fields=id,name,about,gender,email,tagged_places{place}');
		$profile = $profile_request->getGraphNode()->asArray();
		$tagged_places = $profile["tagged_places"];
		$GLOBALS['tagged_places'] = $tagged_places;

		
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		unset($_SESSION['facebook_access_token']);
		echo "<script>window.top.location.href='https://apps.facebook.com/walkbangladesh/'</script>";
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	// get list of pages liked by user
	try {
		$requestLikes = $fb->get('/me/likes?limit=100');
		$likes = $requestLikes->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
 		echo 'Graph returned an error: ' . $e->getMessage();
  		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	$totalLikes = array();
	if ($fb->next($likes)) {	
		$likesArray = $likes->asArray();
		$totalLikes = array_merge($totalLikes, $likesArray); 
		while ($likes = $fb->next($likes)) { 
			$likesArray = $likes->asArray();
			$totalLikes = array_merge($totalLikes, $likesArray);
		}
	} else {
		$likesArray = $likes->asArray();
		$totalLikes = array_merge($totalLikes, $likesArray);
	}
	// printing data on screen
	$GLOBALS['isLiked'] = false;
	foreach ($totalLikes as $key) {
		if($key['id'] == '28506992167643'){
			$GLOBALS['isLiked'] = true;
		}
		
	}

	// priting basic info about user on the screen
	//print_r($tagged_places);

  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper();
	$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/walkbangladesh/', $permissions);
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}

?>

<html>
  <head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style>
       #map {
        height: 600px;
        width: 100%;
       }
       .labels {
     color: red;
     background-color: white;
     font-family: "Lucida Grande", "Arial", sans-serif;
     font-size: 10px;
     font-weight: bold;
     text-align: center;
     width: 40px;
     border: 2px solid black;
     white-space: nowrap;
   }
    </style>

    
  </head>
  <body>
    <h3>Walk Bangladesh</h3>
    <div id="map"></div>
    <div><input type="text" id="data" /></div>
    <script>
     

      function initMap() {
        var uluru = {lat: 23.685, lng: 90.3563};
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: uluru,
          draggable: false,
          scaleControl: false,
          scrollwheel: false,
           styles: [
          {
            "featureType": "water",
            "elementType": "geometry",
            "stylers": [
              { "visibility": "on" }
            ]
          },{
            "featureType": "landscape",
            "stylers": [
              { "visibility": "on" }
            ]
          },{
            "featureType": "road",
            "stylers": [
              { "visibility": "on" }
            ]
          },{
            "featureType": "administrative",
            "stylers": [
              { "visibility": "on" }
            ]
          },{
            "featureType": "poi",
            "stylers": [
              { "visibility": "on" }
            ]
          },{
            "elementType": "labels",
            "stylers": [
              { "visibility": "off" }
            ]
          },{
          }
        ]
        });
       /* var marker = new google.maps.Marker({
          position: uluru,
          map: map
        });*/

        var locations = [
      ['Dhaka', 23.51, 90.24],
      ['Chittagong', 22.3475,  91.8123],
      ['Sylhet', 24.54, 91.52],
      ['Mymensingh', 24.45, 90.24],
      ['Rajsahi',  24.3636, 88.6241],
      ['Rangpur', 25.7468, 89.2508],
      ['Barisal', 22.7029,  90.3466],
      ['Khulna', 22.8456,  89.5403]

    
    ];

     var marker, i;

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map,
        title : locations[i][0],
        label: locations[i][0],
        icon : {}

      });

	
      /* marker = new MarkerWithLabel({
       position: new google.maps.LatLng(locations[i][1], locations[i][2]),
       draggable: true,
       raiseOnDrag: true,
       map: map,
       labelContent: "$425K",
       labelAnchor:  new google.maps.Point(22, 0),
       labelClass: "labels", // the CSS class for the label
       icon: {}
     });*/

     
    }

    var tagged_places_array = <?php echo json_encode($GLOBALS['tagged_places']); ?>;
	
	for(var ii =0 ; ii< tagged_places_array.length; ii++){
		if(tagged_places_array[ii]["place"]["location"]["city"] != undefined){
			//console.log(tagged_places_array[ii]["place"]["location"]["city"]);

			marker = new google.maps.Marker({
	        position: new google.maps.LatLng(tagged_places_array[ii]["place"]["location"]["latitude"], tagged_places_array[ii]["place"]["location"]["longitude"]),
	        map: map,
	        title : tagged_places_array[ii]["place"]["location"]["name"],
	        
	        icon : {}

	      });
		}
		else{
			//console.log(tagged_places_array[ii]["place"]["location"]["name"]);

			marker = new google.maps.Marker({
	        position: new google.maps.LatLng(tagged_places_array[ii]["place"]["location"]["latitude"], tagged_places_array[ii]["place"]["location"]["longitude"]),
	        map: map,
	        title : tagged_places_array[ii]["place"]["location"]["name"],
	        
	        icon : {}

	      });
		}
	}	





      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCidXFiuqXTl47kMUQUEEK4EwSCpG8ZBoY&callback=initMap">
    </script>


  </body>
</html>