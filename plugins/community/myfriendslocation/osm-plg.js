function plgFriendsLocInitializeMap()
{
    map = new L.Map('floc_map_canvas',{attributionControl:false});

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: zoom,
        id: 'mapbox.streets',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var retry = 1
    points = [];
	flocmarkers = L.markerClusterGroup({ chunkedLoading: true});
    joms.jQuery(address).each(function (index) {
        plgFriendsLocCodeAddress(address[index].address, address[index].userdetails, retry);
    });
}

function plgFriendsLocCodeAddress(address, userdetails, retry)
{
    bounds = new L.LatLngBounds();
    var contentString = [
        "<div class=\'joms-avatar--stream\' style=\'float: left; width: 45px; margin-top: 4px;\'>",
        "<img src=\'", userdetails.avatar, "\' width=\'40\' height=\'40\' alt=\'\' style=\'margin-left: 0; margin-top: 0;overflow:hidden;\'>",
        "</div>",
        "<div style=\'margin-left:40px;overflow:hidden\'>",
        "<a href=\'", userdetails.link, "\'><b>", userdetails.username, "</b></a><br/>",
        "<span>", address, "</span>",
        "</div>"
    ].join("");
    var API = "https://nominatim.openstreetmap.org/search?q=" + address + "&format=json&addressdetails=0";

    joms.jQuery.getJSON(API, {
        format: "json"
    }).done(function (data) {
        if (data.length) {
            var marker_point = data[0];
            var marker = L.marker([marker_point.lat, marker_point.lon]).bindPopup(contentString);
			
			flocmarkers.addLayer(marker);
            points.push([marker_point.lat, marker_point.lon]);

			map.fitBounds(points);
            map.addLayer(flocmarkers);

        } else {
            retry = retry - 1;
            plgFriendsLocCodeAddress(address, userdetails, retry);
        }
    });
}

function plgFriendsLocLoadScript() 
{
    plgFriendsLocLoadOMS();
}
