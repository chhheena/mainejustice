google = {};
google.maps = {};
google.maps.MapTypeId = {};
google.maps.MapTypeId.ROADMAP = "mapbox.streets";
google.maps.GeocoderStatus = {};
google.maps.GeocoderStatus.OK = "OK";
google.maps.event = {};
google.maps.event.addDomListener = function(element,event,call){

	call();

}
google.maps.LatLng = function(lat, lng){
   
   return L.latLng(lat, lng);

}


google.maps.Map = function(element,options){

	this.map = new L.Map(element,{attributionControl:false});
	if(typeof options.zoom =="undefined" ){
		options.zoom = 8;
	}
	this.map.zoom = options.zoom;
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
               
                 center: [44.5403,-78.5463],
                 id: options['mapTypeId']
            }).addTo(this.map);

	this.map.setView(new L.LatLng(40.737, -73.923),this.map.zoom);
    this.map.setCenter = function(loc){
    	
    };

	return this.map;
}


google.maps.Geocoder = function(){
		joms.jQuery.ajaxSetup({
  			headers : {
   				"User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
    
  				}
		});
	this.geocode = function(request, callback){

		var API = "https://nominatim.openstreetmap.org/search?q=" + request.address + "&format=json&addressdetails=1";

		this.callback = callback;
		var that = this;
    	joms.jQuery.getJSON(API, {
        	format: "json"
    	}).done(function (data) {

            if (data.length) {

            	data = that.convertToGooglePlace(data);
            	
              	that.callback(data, "OK");

            } else {

                that.callback(data, "ERROR");

            }
         });

	},

	this.convertToGooglePlace = function(data){
		var ret = [] ;
		joms.jQuery.each(data,function(k,v){
			
			let place = {};
			place.name = v.display_name;
			place.vicinity = joms.jQuery.map( v.address,function(val,i){ return val } );
			place.formatted_address =  joms.jQuery.map( v.address,function(val,i){ return val } );
			place.geometry = new openstreetmap.geometry(v.lat,v.lon);

			ret.push(place);

		});

		return ret ;
		



	}

	
}

google.maps.Marker =  function(options){

	this.map = options.map;
	this.marker = L.marker();
	
	var position =  L.latLng(options.position.lat(),options.position.lng());
    this.marker.setLatLng(position );
    this.marker.addTo(this.map);

    this.map.setView(position,this.map.zoom);  
    return this.marker;

}
openstreetmap = {};
openstreetmap.location = function (lat,lng) {
	this.latitude = lat;
	this.longitude = lng;
	

	

};
openstreetmap.location.prototype.lat = function(){
	return this.latitude;
}
openstreetmap.location.prototype.lng = function(){
	return this.longitude;
}

openstreetmap.geometry =  function (lat,lng) {
	this.lat = lat;
	this.lng = lng;
	
	this.location = new openstreetmap.location (lat,lng);
	

};

openstreetmap.PlacesService = {

	 textSearch: function(request, callback){
		/* 
		Sample request

		var request = {
    	query: 'Museum of Contemporary Art Australia',
    	fields: ['photos', 'formatted_address', 'name', 'rating', 'opening_hours', 'geometry'],
  		};


		*/


		var API = "https://nominatim.openstreetmap.org/search?q=" + request.query + "&format=json&addressdetails=1";

		this.callback = callback;
		var that = this;
    	joms.jQuery.getJSON(API, {
        	format: "json"
    	}).done(function (data) {

            if (data.length) {

            	data = that.convertToGooglePlace(data);
              	that.callback(data, "OK");

            } else {

                that.callback(data, "ERROR");

            }
         });

	},

	nearbySearch: function (request, callback){
		// lat , lon
		// https://nominatim.openstreetmap.org/search?q=45.8364043,24.8345179&format=json&addressdetails=1

		/*
			sample data

   			request = {
                location: position,
                types: [ 'establishment' ],
                rankBy: 1 // google.maps.places.RankBy.DISTANCE
            };
		*/

		var API = "https://nominatim.openstreetmap.org/search?q=" + request.location.join(",") + "&format=json&addressdetails=1";

		this.callback = callback;
		var that = this;
    	joms.jQuery.getJSON(API, {
        	format: "json"
    	}).done(function (data) {

            if (data.length) {

            	data = that.convertToGooglePlace(data);
              	that.callback(data, "OK");

            } else {

                that.callback(data, "ERROR");

            }
         });

	},

	convertToGooglePlace: function(data){
		var ret = [] ;
		joms.jQuery.each(data,function(k,v){
			
			let place = {};
			place.name = v.display_name;
			place.vicinity = joms.jQuery.map( v.address,function(val,i){ return val } );
			place.formatted_address =  joms.jQuery.map( v.address,function(val,i){ return val } );
			place.geometry = new openstreetmap.geometry(v.lat,v.lon);

			ret.push(place);

		});

		return ret ;
		



	}




}