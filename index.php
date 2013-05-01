<?php
//$curTime_mongo = microtime(true);
error_reporting(E_ALL);
require_once 'segmentation.php';
require_once 'rank.php';
require_once 'helper.php';

//define radius for segmentation
$radius = 1.0;
//segmentize first
$segments = new Segmentation;
$segments->segmentize($radius);
//var_dump($segments);
$rank = new Rank();
//print_r($rank);
$rank->computeScores($segments->regions);
$scores = $rank->getScores();
//$mongo_timeConsumed = round(microtime(true) - $curTime_mongo,3);
//print "mongo total time: $mongo_timeConsumed<br />";
//print_r($scores);
$helper = new Helper;
$js_array = $helper->phpArrayToJSArray($scores);
//print_r($js_array);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Vancouver City Talks</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 25px; padding: 0 }
      #map_canvas { height: 100% }
    </style>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?libraries=visualization&key=AIzaSyDTd_bReBaN0_QCy2OvqCczXEHoD6pMrmQ&sensor=false">
    </script>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script src="maps/getareas.js"></script>
	<script src="maps/assetMapping.js"></script>
    <script type="text/javascript">
    var locations = <?php echo $js_array; ?>;
    var radius = <?php echo $radius; ?> * 150;

    var map;
    var hmap;
    
      function initialize() {
        var mapOptions = {
          center: new google.maps.LatLng(49.25954525059982, -123.10933642578),
          zoom: 12,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map_canvas"),
            mapOptions);

        //map = createMap ("#map_canvas");
        google.maps.event.addListener(map, 'zoom_changed', function () {
//            $('#radius').html(map.getWorldWidth());
            });
         	
 	hmap = new google.maps.visualization.HeatmapLayer({
 	 	data: new google.maps.MVCArray(),
 	 	map: map,
 	 	radius: radius,
 	 	//maxIntensity: 40,
 	 	/*gradient: [
			'rgba(0, 0, 0, 0)',
			'rgba(0, 255, 0, 1.0)',
			'rgba(255, 0, 0, 1.0)'
 	 	],*/
 	 	gradient: [
					'rgba(0, 0, 0, 0.0)',
 	 				'rgba(255, 0, 0, 0.1)',
 	 				'rgba(255, 0, 0, 0.4)',
 	 				'rgba(0, 255, 0, 0.6)',
 	 				'rgba(0, 255, 0, 0.9)'
 	 	 	 	],
 	 	opacity: 1.0
 	});

	 /*var customLayer = new google.maps.KmlLayer('http://142.104.68.169/cov_localareas.kml');
	 customLayer.setMap(map);*/

	var infowindow = new google.maps.InfoWindow();

	var rect = null;
	google.maps.event.addListener(map, 'click', function () {
		if (rect)
			rect.setMap(null);
	});
	var marker, i;
	for (i = 0; i < locations.length; i++) {  
		marker = new google.maps.Marker({
		position: new google.maps.LatLng(locations[i][0], locations[i][1]),
		//map: map
		});

		var weightedpoint = {
				location: new google.maps.LatLng(locations[i][0], locations[i][1]),
				weight: locations[i][2]*100
				};
		hmap.getData().push(weightedpoint);


		google.maps.event.addListener(marker, 'click', (function(marker, i) {
		return function() {
				/*hmap.getData().push({
					location: marker.position,
					weight: Math.random()*20
				});*/
				if (locations[i][2] != 0) {
					infowindow.setContent(locations[i][2]);
			  		infowindow.open(map, marker);
				}
				//findNeighborhood(locations[i][1], locations[i][2], function (neighborhood) {
			  		//infowindow.setContent(neighborhood);
			  		//infowindow.open(map, marker);
			  		/*getBoundry(neighborhood, function (bounds) {
					  		if(rect)
						  		rect.setMap(null);
				  			rect = new google.maps.Rectangle({
						         bounds: bounds,
						         fillColor: "#FFFF00",
						         fillOpacity: 0.3,
						         strokeColor: "#0000FF",
						         strokeWeight: 2,
						         map: map
						       });
						       
					       google.maps.event.addListener(rect, 'click', function(event) {
					         alert("clicked");
					       });
				  		});*/
				//});
		};
		})(marker, i));
	}
      }
    </script>
  </head>
  <body onload="initialize()">
  <h1>Vancouver City Talks</h1>
	<!-- <input id="areaname" value="40.714224,-73.961452"/>
	<button id="findboundry">Find Boundries</button>
	-->
	<span id="radius"></span>
   <div id="map_canvas" style="width:80%; height:80%"></div>

</body>
</html>
