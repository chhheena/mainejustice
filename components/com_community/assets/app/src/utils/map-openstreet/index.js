export default {
    searchNearby(input) {
        const query = Array.isArray(input) ? input.join(',') : input;
        const API = "https://nominatim.openstreetmap.org/search?q=" + query + "&format=json";

        return new Promise((resolve, reject) => {
            jQuery.getJSON(API).done(data => {
                const positions = data.map(item => {
                    return {
                        id: item.place_id,
                        name: item.display_name,
                        sub: item.display_name,
                        lat: +item.lat,
                        lon: +item.lon,
                    }
                });

                resolve(positions);
            });
        });
        
    }
}