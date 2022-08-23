<template>
    <div class="j-location-google" id="j-location-google-map" style="height: 100%;">
    </div>
</template>

<script>
export default {
    props: {
        latLon: {
            type: Array,
            default() {
                return [51.5, -0.09];
            },
        }
    },

    data() {
        const apiKey = window.joms_gmap_key;
        return {
            API: 'https://maps.googleapis.com/maps/api/js?libraries=places&key='+apiKey,
            map: null,
            marker: {},
        };
    },
    
    mounted() {
        this.setMapView();
    },

    watch: {
        latLon: {
            deep: true,
            handler: 'setMapView'
        },
    },

    methods: {
        createMap() {
            return new Promise((resolve, reject) => {
                if (this.map) {
                    resolve();
                    return;
                }

                jQuery.ajax({
                    url: this.API,
                    dataType: 'script',
                    cache: true,
                }).done(() => {
                    this.map = new google.maps.Map(document.getElementById('j-location-google-map'), {
                        center: {
                            lat: this.latLon[0], 
                            lng: this.latLon[1],
                        },
                        zoom: 16,
                        fullscreenControl: false,
                    });

                    this.$emit('mapLoaded');
                    resolve();
                });
            });
        },

        setMapView() {
            this.createMap().then(() => {
                const latLon = {
                    lat: this.latLon[0], 
                    lng: this.latLon[1],
                };

                this.map.setCenter(latLon);

                this.marker.setMap && this.marker.setMap(null);
                this.marker = new google.maps.Marker({
                    position: latLon,
                    map: this.map,
                });
            });
        },
    }
}
</script>
