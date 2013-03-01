// element : DOM element or jQuery query to render the map in
// startPoint : JSON {lat: ..., lng: ...}
// startZoom (default = 11)

function createMap (element, startPoint, startZoom) {
	if (! startPoint) {
		startPoint = {
				lat: 49.19954525059982,
				lng: -123.0027233642578
		};
	}
	var lat = startPoint.lat | 49.19954525059982;
	var lng = startPoint.lng | -123.0027233642578;
	
	startZoom = startZoom | 11;
	var mapOptions = {
		center: new google.maps.LatLng(lat, lng),
		zoom: startZoom,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map($(element).get(0), mapOptions);
	
	return map;
}

var BasicType = function () {
	this.convert = function (d) {
		return d;
	};
};

var ColorType = function () {
	this.convert = function (d) {
		return 255 * d;
	};
};

var Scaler = function (max, type) {
	this.max = max | 1;
	this.type = type;
	
	this.scale = function (value) {
		return this.type.scale(value / max);
	};
};

// data can be either:
// data = {lat:..., lng: ..., value: ...}
// data = {address:...,value}
function addMarker (map, data, scaler) {
	if (typeof data != "object") {
		console.log("skipping an item (" + data + ")");
		return;
	}
	var value = data.value;
	value = value | data.data;
	value = value | 1;
	// use value to add marker
	if (typeof data.lat == "undefined" && 
			typeof data.lng == "undefined" &&
			typeof data.address == "undefined") {
		console.log("skipping an item (no address)");
		return;
	}
	if (typeof data.address != "undefined") {
		var address = data.address;
		getBoundry(address, function (value) {
			var lat = value.lat();
			var lng = value.lng();
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(lat, lng),
				map: map
				});
		});
	} else {
		if (typeof data.lat == "undefined" || 
				typeof data.lng == "undefined") {
			console.log("both lat and lng are needed");
			return;
		}
		var lat = data.lat;
		var lng = data.lng;
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(lat, lng),
			map: map
			});
		return marker;
	}
}

//data can be either:
//data = {lat:..., lng: ..., value:..., shape:...}
//data = {address:..., value:..., shape:...}
function addCustomAsset (data, scaler) {
	var value = data | 1;
	if (typeof value == "object")
		value = data.value;
}