<template>
    <div class="joms-postbox-dropdown location-dropdown">
        <div class="joms-postbox-map" style="height: 110px;">
            <component ref="map" :is="map" :latLon="latLon" @mapLoaded="setMapLoaded"></component>
        </div>
        <div class="joms-postbox-action joms-location-action" style="z-index: 999;">
            <button class="joms-postbox-select joms-button--primary" 
                v-if="selectedLocation.name && value"
                @click="setLocation">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-location'" />
                </svg>
                {{language('geolocation.select_button')}}
            </button>
            <button class="joms-postbox-remove joms-button--neutral" 
                v-if="locationName"
                @click="removeLocation">{{language('remove')}}</button>
        </div>
        <input class="joms-postbox-keyword joms-input" 
            type="text" 
            v-model="value"
            :placeholder="language('geolocation.loaded')" 
            :disabled="!mapLoaded"
            @input="onInputLocationSearch" />

        <ul class="joms-postbox-locations" style="height: 160px; overflow-y: auto;">
            <li v-if="loading">
                <em>{{language('geolocation.loading')}}</em>
            </li>
            <li v-if="!locations.length && !loading">
                <em>{{language('geolocation.error')}}</em>
            </li>
            <li v-for="loca in locations" :key="loca.id" @click="selectLocation(loca)">
                <p>{{loca.name}}</p>
                <span>{{loca.sub}}</span>
            </li>
        </ul>
    </div>
</template>

<script>
import Vue from 'vue';
import GoogleMap from '../../../utils/map-google';
import OpenStreetMap from '../../../utils/map-openstreet';
import language from '../../../utils/language';
import debounce from 'lodash/debounce';

const useOpenstreeetMap = window.joms_maps_api === 'openstreetmap';
const service = useOpenstreeetMap ? OpenStreetMap : GoogleMap;
const {searchNearby} = service;

export default {
    components: {
        JLocationOpenStreet: () => import(/* webpackChunkName: 'openstreetmap' */ './JLocationOpenStreet.vue'),
        JLocationGoogle: () => import(/* webpackChunkName: 'googlemap' */ './JLocationGoogle.vue'),
    },

    props: {
        locationName: {
            type: String,
            default: '',
        }
    },

    data() {
        const map = useOpenstreeetMap ? 'JLocationOpenStreet' : 'JLocationGoogle';
        const currentUrl = window.joms_current_url;
        return {
            mapLoaded: false,
            currentUrl,
            map,
            value: '',
            yourLocation: [51.5, -0.09],
            locations: [],
            loading: false,
            selectedLocation: {
                name: '',
                lat: '',
                lon: '',
            }
        };
    },

    computed: {
        latLon() {
            return [
                this.selectedLocation.lat ? this.selectedLocation.lat : 51.5,
                this.selectedLocation.lon ? this.selectedLocation.lon : -0.09,
            ];
        },
    },

    watch: {
        mapLoaded() {
            this.autoDetectLocation();
        }
    },

    methods: {
        language(str) {
            return language(str)
        },

        autoDetectLocation() {
            this.loading = true;

            window.navigator.geolocation.getCurrentPosition(
                position => {
                    const latLon = [position.coords.latitude, position.coords.longitude];
                    this.detectLocationSuccess( latLon )
                },
                () => this.detectLocationFail(),
                {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        },

        detectLocationSuccess(latLon) {
            Vue.set(this, 'yourLocation', latLon);

            Vue.set(this, 'selectedLocation', {
                name: '',
                lat: latLon[0],
                lon: latLon[1],
            });

            const map = this.$refs.map.map;
            searchNearby(latLon, map).then(data => {
                Vue.set(this, 'locations', data);
                this.loading = false;
            });
        },

        detectLocationFail() {
            this.lookUpLocationByIp().then(latLon => this.detectLocationSuccess(latLon));
        },

        lookUpLocationByIp() {
            const API = 'https://extreme-ip-lookup.com/json/';

            return new Promise((resolve, reject) => {
                jQuery.getJSON(API).done(data => {
                    if (data.status !== 'success') {
                        alert('Error when detect location, please turn on location on browser.')
                    }

                    resolve([data.lat, data.lon]);
                });
            });
        },

        onInputLocationSearch() {
            Vue.set(this, 'locations', []);

            this.loading = true;
            this.debounceSearch();
        },

        debounceSearch: debounce(function() {
            const query = this.value ? this.value : this.yourLocation;
            const map = this.$refs.map.map;

            searchNearby(query, map).then(data => {
                Vue.set(this, 'locations', data);
                this.loading = false;
            });
        }, 300),

        selectLocation(location) {
            this.value = location.name;
            
            Vue.set(this, 'selectedLocation', {
                name: location.name,
                lat: location.lat,
                lon: location.lon,
            });
        },

        setLocation() {
            this.$emit('setLocation', this.selectedLocation);
        },

        removeLocation() {
            this.value = '';
            this.$emit('removeLocation');
        },

        setMapLoaded() {
            this.mapLoaded = true;
        }
    }
}
</script>

<style lang="scss">
.location-dropdown {
    .joms-postbox-select {
        padding: 2.04257px 8.65248px;

        svg {
            fill: #fff;
        }
    }

    .joms-postbox-remove {
        padding: 2.04257px 8.65248px;
    }
}
</style>
