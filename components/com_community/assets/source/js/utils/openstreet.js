google = {};
google.maps = {};
google.maps.MapTypeId = {};
google.maps.MapTypeId.ROADMAP = "mapbox.streets";
google.maps.GeocoderStatus = {};
google.maps.GeocoderStatus.OK = "OK";
google.maps.event = {};
google.maps.places = {};
google.maps.event.addDomListener = function(element,event,call){

	call();

}
google.maps.LatLng = function(lat, lng){
   
   return L.latLng(lat, lng);

}


google.maps.Map = function(element,options){

	this.map = new L.Map(element,{attributionControl:false});
	this.map.zoom = options.zoom;
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
               
                 center: [51.505, -0.09],
                 id: options['mapTypeId']
            }).addTo(this.map);
    this.map.setCenter = function(loc){
    	
    };

	return this.map;
}


google.maps.Geocoder = function(){

	this.geocode = function(request, callback){

		var API = "https://nominatim.openstreetmap.org/search?q=" + request.address + "&format=json&addressdetails=1";

		this.callback = callback;
		var that = this;
    	joms.jQuery.getJSON(API, {
        	format: "json"
    	}).done(function (data) {

            if (data.length) {

            	data = that.convertToGooglePlace(data);
            	
              	callback(data, "OK");

            } else {

               callback(data, "ERROR");

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
	this.marker.addTo(this.map);
	if(typeof options.position != "undefined"){

		var position =  options.position ;
		var lat = (typeof position.lat === "function")?position.lat():position.lat;
    	var lng = (typeof position.lng === "function")?position.lng():position.lng;
    	position =  L.latLng(lat,lng);
    	this.marker.setLatLng(position );
    	this.map.setView(position,this.map.zoom); 
	}
	
    var that = this;
    this.marker.setPosition = function(position){ 

    	var lat = (typeof position.lat === "function")?position.lat():position.lat;
    	var lng = (typeof position.lng === "function")?position.lng():position.lng;
    	var position =  L.latLng(lat,lng);
    	this.setLatLng(position );
    	
    	that.map.setView(position,that.map.zoom); 
    }

    return this.marker;

}

google.maps.places.AutocompleteService = function(){
	
  this.getPlacePredictions = function(request,callback){
  		
  		this.PlacesService =   openstreetmap.PlacesServiceObj;
  		request.query = request.input;
  		this.PlacesService.textSearch(request,callback);

  }

}

google.maps.places.PlacesService = function(element){
	
	this.element = element;

	this.getDetails = function(params,callback){

		this.PlacesService =   openstreetmap.PlacesServiceObj;
		this.PlacesService.getDetails(params.placeId,callback);
		
		
	}

}
window.google = google ;
openstreetmap = {};
openstreetmap._cache = {};
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

openstreetmap.PlacesService = openstreetmap.PlacesServiceObj = {
	 
	getDetails:function(place_id, callback){

		var API ="https://nominatim.openstreetmap.org/details.php?&format=json&place_id="+place_id;
		
		this.callback = callback;
		var that = this;
    	joms.jQuery.getJSON(API, {
        	format: "json"
    	}).done(function (data) {

            if (typeof data.place_id != "undefined") {
            	
            	data = that.convertToGooglePlaceOne(data);
            	data.place_id = place_id;
                
            	if(typeof openstreetmap._cache[place_id] != "undefined" && typeof data.formatted_address == "undefined"  ){
            		data.formatted_address = openstreetmap._cache[place_id].formatted_address;
            	}else{
            		//data.formatted_address = "";
            	}
            	
              	callback(data, "OK");

            } else {

                callback(data, "ERROR");

            }
         });
		 
	},

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
              	callback(data, "OK");

            } else {

                callback(data, "ERROR");

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
              	callback(data, "OK");

            } else {

                callback(data, "ERROR");

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
			//place.place_id = v.osm_type.charAt(0).toUpperCase()+v.osm_id

			place.place_id = v.place_id;
			place.description = v.display_name;
			openstreetmap._cache[v.place_id] = place;

			ret.push(place);

		});

		return ret ;
		



	},

	convertToGooglePlaceOne: function(v){
	 
			
			
			let place = {};
			place.name = v.localname;
			place.vicinity = v.localname;
			//place.formatted_address = v.localname ;//  joms.jQuery.map( v.address,function(val,i){ return val } );
			place.geometry = new openstreetmap.geometry(v.centroid.coordinates[1] ,v.centroid.coordinates[0]);
			//place.place_id = v.osm_type.charAt(0).toUpperCase()+v.osm_id

			place.place_id = v.place_id;

			

	

		return place ;
		



	}




}