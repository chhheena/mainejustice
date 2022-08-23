function waitForGoogleMap(query, map) {
    return new Promise((resolve, reject) => {
        const inter = setInterval(() => {
            if (typeof google === 'undefined') {
                return;
            }

            clearInterval(inter);
            search(query, map).then(data => {
                resolve(data);
            });
        }, 500);
    });
}

function search(query, map) {
    return Array.isArray(query) ? getPlaceNearby(query, map) : autoCompleteAdress(query, map);
}

function getPlaceNearby(query, map) {
    const latLon = new google.maps.LatLng(query[0], query[1]);
    const request = {
        location: latLon,
        radius: 100,
    };

    return new Promise((resolve, reject) => {
        const service = new google.maps.places.PlacesService(map);

        service.nearbySearch(request, (results, status) => {
            if (status !== google.maps.places.PlacesServiceStatus.OK) {
                resolve([]);
            }
            
            resolve(results.map(item => {
                return {
                    id: item.place_id,
                    name: item.name,
                    sub: item.vicinity,
                    lat: item.geometry.location.lat(),
                    lon: item.geometry.location.lng(),
                }
            }));
        });
    });
}

function autoCompleteAdress(query, map) {
    if (Array.isArray(query)) {
        return getPlaceNearby(query, map);
    }

    return new Promise((resolve, reject) => {
        var request = {
            query: query,
        };
        
        var service = new google.maps.places.PlacesService(map);
        
        service.textSearch(request, function(results, status) {
            if (status !== google.maps.places.PlacesServiceStatus.OK) {
                resolve([]);
            }

            resolve(results.map(item => {
                return {
                    id: item.place_id,
                    name: item.name,
                    sub: item.formatted_address,
                    lat: item.geometry.location.lat(),
                    lon: item.geometry.location.lng(),
                };
            }));
        });
    });
}

export default {
    searchNearby(query, map) {
        if (typeof google !== 'undefined') {
            return search(query, map);
        }

        return waitForGoogleMap(query, map);
    },
}