define('views/dropdown/location',[
    'sandbox',
    'views/dropdown/base',
    'utils/language',
    'utils/constants'
],

// definition
// ----------
function( $, BaseView, language,constants ) {

    // placeholders language setting.
    var langs = language.get('geolocation') || {};

    return BaseView.extend({

        template: {
            div: joms.jst[ 'html/dropdown/location' ],
            list: joms.jst[ 'html/dropdown/location-list' ]
        },

        placeholders: {
            loading: langs.loading || '',
            loaded: langs.loaded || '',
            error: langs.error || ''
        },

        initialize: function() {
            BaseView.prototype.initialize.apply( this );

            this.location = false;
            this.locations = {};
            this.nearbyLocations = false;
            this.listenTo( $, 'postbox:tab:change', this.onRemove );
        },

        events: {
            'keyup input.joms-postbox-keyword': 'onKeyup',
            'click li': 'onSelect',
            'click button.joms-add-location': 'onAdd',
            'click button.joms-remove-location': 'onRemove'
        },

        render: function() {
            var settings = constants.get('settings') || {},
            conf = constants.get('conf') || {};
            
            this.map_type = conf.maps_api;
            this.$el.html( this.getTemplate() );
            this.$keyword = this.$('.joms-postbox-keyword');
            this.$list = this.$('.joms-postbox-locations');
            this.$map = this.$('.joms-postbox-map');
            this.$btnadd = this.$('.joms-add-location').hide();
            this.$btnremove = this.$('.joms-remove-location').hide();

            this.$keyword.attr( 'placeholder', this.placeholders.loading );

            var that = this;
            this.getService(function() {
                that.setInitialLocation = true;
                that.searchLocation();
            });

            return this;
        },

        show: function() {
            this.$el.show();
            this.trigger('show');
        },

        hide: function() {
            this.$el.hide();
            this.trigger('hide');
        },

        toggle: function() {
            var hidden = this.el.style.display === 'none';
            hidden ? this.show() : this.hide();
        },

        filter: function( e ) {
            return;

            var keyword = this.$keyword.val().replace( /^\s+|\s+$/, '' ),
                filtered = this.locations;

            if ( e && keyword ) {
                this.searchLocation( keyword );
                return;
            }

            if ( keyword.length ) {
                keyword = new RegExp( keyword, 'i' );
                filtered = [];
                for ( var i = 0, item; i < this.locations.length; i++ ) {
                    item = this.locations[i];
                    item = [ item.name, item.vicinity ].join(' ');
                    if ( item.match(keyword) )
                        filtered.push( this.locations[i] );
                }
            }

            this.draw( filtered );
        },

        draw: function( items ) {
            var html = this.template.list({
                language: { geolocation: language.get('geolocation') },
                items: items
            });

            this.filtered = items;

            this.$list.html( html ).css({
                height: '160px',
                overflowY: 'auto'
            });

            if ( this.setInitialLocation ) {
                this.setInitialLocation = false;
                this.select( 0 );
            }
        },

        select: function( index ) {
            var data = this.filtered[ index ];
            if ( data ) {
                this.location = data;
                this.$map.show();
                this.$keyword.val( data.name );
                this.map && this.marker && this.marker.setMap( this.map );
                this.showMap( data.latitude, data.longitude );
                this.$btnadd.show();
                this.$btnremove.hide();
            }
        },

        value: function() {
            var data = [];

            if ( this.location ) {
                data.push( this.location.name );
                data.push( this.location.latitude );
                data.push( this.location.longitude );
                return data;
            }

            return false;
        },

        reset: function() {
            this.location = false;
            this.marker && this.marker.setMap( null );
            this.$keyword.val('');
            this.$btnadd.hide();
            this.$btnremove.hide();
            this.trigger('reset');
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onKeyup: $.debounce(function() {
            this.service && this.searchLocation();
        }, 300 ),

        onSelect: function( e ) {
            var el = $( e.currentTarget ),
                index = el.attr('data-index');

            this.select( +index );
        },

        onAdd: function() {
            if ( this.location ) {
                this.trigger( 'select', this.location );
                this.$btnadd.hide();
                this.$btnremove.show();
                this.hide();
            }
        },

        onRemove: function() {
            this.reset();
            this.trigger('remove');
            this.hide();
            this.filter();
        },

        // ---------------------------------------------------------------------
        // Map functions.
        // ---------------------------------------------------------------------

        initMap: function( lat, lng ) {
            if(this.map_type == "openstreetmap"){
                 this.initOpenstreetMap(lat, lng);
            }else{
                this.initGoogleMap(lat, lng);
            }
        },

        initOpenstreetMap:function(lat, lng){
            var el = $('<div>').prependTo( this.$map );
            el.css( 'height', 110 );

            
            
            this.map = new L.Map(el[0],{attributionControl:false});
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 minZoom: 14,
                 center: [51.505, -0.09],
                 id: 'mapbox.streets'
            }).addTo(this.map);

            this.marker = L.marker();
            this.marker.setMap  = function(map){
                 
                if(map === null){
                    this.map.removeLayer(this);
                }else{
                    this.addTo(map); 
                    this.map = map ;
                }
                
            }
            
        },

        initGoogleMap:function(){

            var el = $('<div>').prependTo( this.$map );
            el.css( 'height', 110 );

            var options = {
                center: new google.maps.LatLng( 1, 1 ),
                zoom: 14,
                mapTypeId: google.maps.MapTypeId.ROADMAD,
                mapTypeControl: false,
                disableDefaultUI: true,
                draggable: false,
                scaleControl: false,
                scrollwheel: false,
                navigationControl: false,
                streetViewControl: false,
                disableDoubleClickZoom: true
            };

            this.map = new google.maps.Map( el[0], options );
            this.marker = new google.maps.Marker({ draggable: false, map: this.map });
            this.marker.setAnimation( null );
        },

        showMap: function( lat, lng ) {
            if(this.map_type == "openstreetmap"){
                 this.showOpenstreetMap(lat, lng);
            }else{
                this.showGoogleMap(lat, lng);
            }
          
        },
        showGoogleMap: function( lat, lng ) {
           
            var position = new google.maps.LatLng( lat, lng );
            this.marker.setPosition( position );
            this.map.panTo( position );
        },

        showOpenstreetMap: function( lat, lng ) {
           
            var position = L.latLng(lat, lng);
            this.marker.setLatLng( position );
           // this.map.fitBounds([lat, lng]);
            //this.map.panTo( position );
            this.map.setView(position, 11, { animation: true });  
            
            
        },

        // ---------------------------------------------------------------------
        // Location detection.
        // ---------------------------------------------------------------------

        getService: function( callback ) {
            var that;

            if ( typeof callback !== 'function' ) {
                callback = function(){};
            }

            if ( this.service ) {
                callback.call( this, this.service );
            } else {
                that = this;
                joms.map.type = this.map_type ;
                joms.map.execute(function() {
                    that.initMap();
                    if(that.map_type == "openstreetmap"){
                        that.service = openstreetmap.PlacesService ;
                    }else{
                        that.service = new google.maps.places.PlacesService( that.map );
                    }
                    
                    callback.call( that, that.service );
                });
            }
        },

        searchLocation: function() {
            var query = this.$keyword.val().replace(/^\s+|\s+$/g, '');

            this.$keyword.attr( 'placeholder', this.placeholders.loading );
            this.searchToken = (this.searchToken || 0) + 1;
            this[ query ? 'searchLocationQuery' : 'searchLocationNearby' ]({
                query: query,
                token: this.searchToken,
                callback: this.searchLocationCallback
            });
        },

        searchLocationQuery: function( params ) {
            var that;

            if ( this.locations[ params.query ] ) {
                params.callback.apply( this, [ this.locations[ params.query ], null, params ]);
                return;
            }

            that = this;
            this.service.textSearch({ query: params.query }, function( results, status ) {
                if ( status !== "OK" ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                if ( !$.isArray( results ) ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                for ( var i = 0, locs = [], loc; i < results.length; i++ ) {
                    loc = results[i];
                    locs.push({
                        name: loc.name,
                        latitude: loc.geometry.location.lat(),
                        longitude: loc.geometry.location.lng(),
                        vicinity: loc.formatted_address
                    });
                }

                that.locations[ params.query ] = locs;
                params.callback.apply( that, [ that.locations[ params.query ], null, params ] );
            });
        },

        searchLocationNearby: function( params ) {
            var that = this;
            navigator.geolocation.getCurrentPosition(
                function( position ) { that.detectLocationSuccess( position, params ) },
                function() { that.detectLocationFallback( params ) },
                { timeout: 10000 }
            );
        },

        searchLocationCallback: function( results, error, params ) {
            if ( this.searchToken !== params.token ) {
                return;
            }

            this.$keyword.attr( 'placeholder', this.placeholders.loaded );
            this.draw( results );
        },

        detectLocationSuccess: function( position, params ) {
            var coords = position && position.coords || {},
                lat = coords.latitude,
                lng = coords.longitude;

            if ( lat && lng ) {
                this.detectLocationNearby( lat, lng, params );
            } else {
                params.callback.apply( this, [ null, this.placeholders.error, params ] );
            }
        },

        // If HTML5 geolocation failed to detect my current location, attempt to use IP-based geolocation.
        detectLocationFallback: function ( params ) {
            var success = false,
                that = this;

            $.ajax({
                url: '//freegeoip.net/json/',
                dataType: 'jsonp',
                success: function( json ) {
                    var lat = json.latitude,
                        lng = json.longitude;

                    if ( lat && lng ) {
                        success = true;
                        that.detectLocationNearby( lat, lng, params );
                    }
                },
                complete: function() {
                    success || params.callback.apply( that, [ null, that.placeholders.error, params ] );
                }
            });
        },

        detectLocationNearby: function( lat, lng, params ) {
            var position, request, that;

            if ( this.nearbyLocations ) {
                params.callback.apply( this, [ this.nearbyLocations, null, params ]);
                return;
            }
            if(this.map_type == "openstreetmap"){

                position = [ lat, lng ];

            }else{
                
                position = new google.maps.LatLng( lat, lng );
            }
            
            request = {
                location: position,
                types: [ 'establishment' ],
                rankBy: 1 // google.maps.places.RankBy.DISTANCE
            };
            
            that = this;
            this.service.nearbySearch( request, function( results, status ) {
                if ( status !== "OK" ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                if ( !$.isArray( results ) ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                for ( var i = 0, locs = [], loc; i < results.length; i++ ) {
                    loc = results[i];
                    
                    locs.push({
                        name: loc.name,
                        latitude: loc.geometry.location.lat(),
                        longitude: loc.geometry.location.lng(),
                        vicinity: loc.vicinity
                    });
                }

                that.nearbyLocations = locs;
                params.callback.apply( that, [ that.nearbyLocations, null, params ] );
            });
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template.div({
                language: {
                    geolocation: language.get('geolocation') || {}
                }
            });

            return html;
        }

    });

});
