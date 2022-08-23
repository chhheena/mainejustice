(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.location || (joms.popup.location = {});
    joms.popup.location.view = factory( root );

    define([ 'utils/popup' ], function() {
        return joms.popup.location.view;
    });

})( window, function( root ) {

var popup, elem, id;

function render( _popup, _id ) {
    if ( elem ) elem.off();
    popup = _popup;
    id = _id;

    joms.ajax({
        func: 'activities,ajaxShowMap',
        data: [ id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
        }
    });
}

function buildHtml(json){
    if(root.joms_maps_api == "openstreetmap"){
       return buildHtml_openstreetmap(json);
    }else{
        return buildHtml_google(json);
    }
}

function buildHtml_openstreetmap( json ) {
    var latlng, location, src;

    json || (json = {});
    joms.map.executeOpenStreetmap(function(){
      setTimeout(function(){
        var el = joms.jQuery("#joms-locaton-popup-openstreet");
        el.css( 'height', 500 );
        el.css( 'width', 500 );

        var map = new L.Map(el[0],{attributionControl:false});
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 minZoom: 14,
                 center: [51.505, -0.09],
                 id: 'mapbox.streets'
            }).addTo(map);

        var marker = L.marker().addTo(map);
        var position = L.latLng(json.latitude, json.longitude);
        marker.setLatLng( position );
        map.setView(position, 11, { animation: false });  
      },500);
       

    });
   
    return [
        '<div id="joms-locaton-popup" class="joms-popup">',
        '<div id="joms-locaton-popup-openstreet">',
        '</div>',
        '</div>'
    ].join('');
}

function buildHtml_google( json ) {
    var latlng, location, src;

    json || (json = {});

    latlng = json.latitude + ',' + json.longitude;
    location = json.location;
    src = 'https://maps.googleapis.com/maps/api/staticmap?center=' + latlng +
        '&markers=color:red%7Clabel:S%7C' + latlng + '&zoom=14&size=600x350&maptype=roadmap';

    if ( root.joms_gmap_key ) {
        src += '&key=' + root.joms_gmap_key;
    }

    return [
        '<div class="joms-popup joms-popup--location-view">',
        '<div', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<a href="//www.google.com/maps/@', latlng, ',19z" target="_blank">',
            '<img src="', src, '">',
            '</a>',
        '</div>',
        '<div', ( json.error ? '' : ' class="joms-popup__hide"' ), '>',
            '<div class="joms-popup__content joms-popup__content--single">', json.error, '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id );
    });
};

});
