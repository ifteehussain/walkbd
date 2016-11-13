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

    <script src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' type='text/javascript'></script>
<style>
#fanback {
display:none;
background:rgba(0,0,0,0.8);
width:100%;
height:100%;
position:fixed;
top:0;
left:0;
z-index:99999;
}
#fan-exit {
width:100%;
height:100%;
}
#JasperRoberts {
background:white;
width:420px;
height:270px;
position:absolute;
top:58%;
left:63%;
margin:-220px 0 0 -375px;
-webkit-box-shadow: inset 0 0 50px 0 #939393;
-moz-box-shadow: inset 0 0 50px 0 #939393;
box-shadow: inset 0 0 50px 0 #939393;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
margin: -220px 0 0 -375px;
}
#TheBlogWidgets {
float:right;
cursor:pointer;
background:url(http://3.bp.blogspot.com/-NRmqfyLwBHY/T4nwHOrPSzI/AAAAAAAAAdQ/8b9O7O1q3c8/s1600/TheBlogWidgets.png) repeat;
height:15px;
padding:20px;
position:relative;
padding-right:40px;
margin-top:-20px;
margin-right:-22px;
}
.remove-borda {
height:1px;
width:366px;
margin:0 auto;
background:#F3F3F3;
margin-top:16px;
position:relative;
margin-left:20px;
}
#linkit,#linkit a.visited,#linkit a,#linkit a:hover {
color:#80808B;
font-size:10px;
margin: 0 auto 5px auto;
float:center;
}
</style>


<script type='text/javascript'>
//jQuery.cookie = function (key, value, options) {

// key and at least value given, set cookie...
if (arguments.length > 1 && String(value) !== "[object Object]") {
options = jQuery.extend({}, options);

if (value === null || value === undefined) {
options.expires = -1;
}

if (typeof options.expires === 'number') {
var days = options.expires, t = options.expires = new Date();
t.setDate(t.getDate() + days);
}

value = String(value);

return (document.cookie = [
encodeURIComponent(key), '=',
options.raw ? value : encodeURIComponent(value),
options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
options.path ? '; path=' + options.path : '',
options.domain ? '; domain=' + options.domain : '',
options.secure ? '; secure' : ''
].join(''));
}

// key and possibly options given, get cookie...
options = value || {};
var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};
//]]>
</script>
<script type='text/javascript'>
jQuery(document).ready(function($){
if($.cookie('popup_user_login') != 'yes'){
$('#fanback').delay(5000).fadeIn('medium');
$('#TheBlogWidgets, #fan-exit').click(function(){
$('#fanback').stop().fadeOut('medium');
});
}

});
</script>

    
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

    <iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&width=51&layout=button&action=like&size=small&show_faces=true&share=false&height=65&appId=1757820037876451" width="51" height="65" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>


  </body>
</html>