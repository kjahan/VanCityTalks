$(document).ready(function () {
	connectHandlers();
	initialize();
});

function initialize() {
	
}

function connectHandlers() {
	$('#findboundry').click(function () {
		var areaname = $('#areaname').val();
		getBoundry(areaname);
	});
}

var res;
function getBoundry(area, callback) {
    var geocoder = new google.maps.Geocoder();
	geocoder.geocode({'address': area}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			callback(results[0].geometry.location);
		} else {
			alert('Geocoder failed due to: ' + status);
		}
	});
}

function findNeighborhood(lat, lng, callback) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    var latlng = new google.maps.LatLng(lat, lng);
    var geocoder = new google.maps.Geocoder();
    var nbh = null;
    geocoder.geocode({'latLng': latlng}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
    	for (var i = 0; i < results.length; i++) {
//    		console.log(results[i].types + " " + (results[i].types.indexOf("neighborhood") != -1 || results[i].types.indexOf("locality") != -1));
    		if (results[i].types.indexOf("neighborhood") != -1 || 
    				results[i].types.indexOf("locality") != -1 || 
    				results[i].types.indexOf("administrative_area_level_3") != -1) {
    			nbh = results[i].formatted_address;
    		}
    	}
      } else {
        alert('Geocoder failed due to: ' + status);
      }
      callback(nbh);
    });
}