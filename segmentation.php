<?php
class Segmentation{
	public 	$regions = array();	
	# Spherical Law of Cosines: return distance in miles
	function distance_slc($lat1, $lon1, $lat2, $lon2) {
		$earth_radius = 3960.00; # in miles
		$delta_lat = $lat2 - $lat1 ;
		$delta_lon = $lon2 - $lon1 ;
		$distance = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($delta_lon)) ;
		$distance = acos($distance);
		$distance = rad2deg($distance);
		$distance = $distance * 60 * 1.1515;
		$distance = round($distance, 4);
	
		return $distance;
	}
	#                    }
	//boundary definitions
	function segmentize($radius){
		$boundary = array(
				"NW" => array("lon" => -123.23, "lat" => 49.29),
				"NE" => array("lon" => -123.00, "lat" => 49.29),
				"SE" => array("lon" => -123.00, "lat" => 49.194),
				"SW" => array("lon" => -123.00, "lat" => 49.194),
		);
		//var_dump($boundary);
		//box width and height
		$width = $this->distance_slc($boundary["NW"]["lat"], $boundary["NW"]["lon"], $boundary["NE"]["lat"], $boundary["NE"]["lon"]);
		$height = $this->distance_slc($boundary["NW"]["lat"], $boundary["NW"]["lon"], $boundary["SW"]["lat"], $boundary["SW"]["lon"]);
		//$dist = $this->distance_slc(49.267, -123, $boundary["NE"]["lat"], $boundary["NE"]["long"]);
		#
		//echo $width . "<br>";
		//echo $height . "<br>";
		//$radius = 1.0; //in miles
		$k1 = floor($width/(1.0*$radius));
		$k2 = floor($height/(2.0*$radius));
		//echo $k1 . "<br>";
		//echo $k2 . "<br>";
		$lat_step = abs($boundary["NW"]["lat"] - $boundary["SW"]["lat"])/floor($k2);
		$lon_step = abs($boundary["NW"]["lon"] - $boundary["NE"]["lon"])/floor($k1);
		//echo $lon_step . "<br>";
		//echo $lat_step . "<br>";
	
		// populate regions scores from MongoDB

		$cnt = 0;
		for($i = 0; $i < $k1; $i++){
			for($j = 0; $j < $k2; $j++){
				$center_lon = $boundary["NW"]["lon"] + (2*$i + 1)*$lon_step;
				$center_lat = $boundary["NW"]["lat"] - (2*$j + 1)*$lat_step;
				//call function score($lat, $long, $radius)
				//echo $center_lat . "<br>";
				//echo $center_lon . "<br>";
				$region = array(
						"radius" => $radius,
						"lat" => $center_lat,
						"lon" => $center_lon,
				);
				$this->regions[$cnt] = $region;
				$cnt += 1;
			}
		}	
	}	
}
?>