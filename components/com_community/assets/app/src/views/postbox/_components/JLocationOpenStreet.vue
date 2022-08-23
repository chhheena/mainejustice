<template>
    <div class="j-location-open-street" id="j-location-open-street-map" style="height: 100%;">
    </div>
</template>

<script>
import 'leaflet/dist/leaflet.css';
import * as L from 'leaflet';

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
        return {
            map: null,
            marker: {},
        }
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
            this.map = L.map('j-location-open-street-map', {attributionControl: false});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);

            this.$emit('mapLoaded');
        },

        setMapView() {
            if (!this.map) {
                this.createMap();
            }

            this.map.removeLayer(this.marker);
            this.map.setView(this.latLon, 13);
            this.marker = L.marker(this.latLon);
            this.marker.addTo(this.map);
            this.map.panTo(this.latLon);
        },
    }
}
</script>
