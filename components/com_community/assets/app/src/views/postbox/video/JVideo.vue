<template>
    <div class="joms-postbox-video">
        <div class="joms-postbox-video--inner" @dragenter="showDropArea">
            <JVideoUpload 
                ref="uploader" 
                v-if="!type || type === 'upload'" 
                :dropArea="dropArea"
                @typeChange="setType"
                @hideDropArea="hideDropArea"
                @reset="reset" />
            <div class="joms-postbox-video-spacer" v-if="!type">{{orText}}</div>
            <JVideoFetcher 
                ref="fetcher" 
                v-if="!type || type === 'fetch'" 
                :videoType="type"
                @typeChange="setType"
                @reset="reset" />
        </div>
        <JVideoMiniBar 
            v-show="!$store.state.free"
            :videoType="type"
            @showMoodPicker="showMoodPicker" 
            @showLocation="showLocation"
            @showPrivacy="showPrivacy"
            @reset="reset"
            @validate="validate" />
        <keep-alive>
            <JMoodPicker 
                v-if="moodPicker" 
                v-click-outside="hideMoodPicker"
                @hideMoodPicker="hideMoodPicker"
                @setMood="setMood" />
        </keep-alive>
        <keep-alive>
            <JLocationPicker
                v-if="location" 
                v-click-outside="hideLocation"
                :locationName="locationName"
                @setLocation="setLocation"
                @removeLocation="removeLocation" />
        </keep-alive>
        <keep-alive>
            <JPrivacyPicker
                v-if="privacy" 
                v-click-outside="hidePrivacy" 
                @hidePrivacy="hidePrivacy"
                @setPrivacy="setPrivacy"/>
        </keep-alive>
    </div>
</template>

<script>
import JVideoFetcher from './JVideoFetcher.vue';
import JVideoMiniBar from './JVideoMiniBar.vue';
import JVideoUpload from './JVideoUpload.vue';
import JMoodPicker from '../_components/JMoodPicker.vue';
import JPrivacyPicker from '../_components/JPrivacyPicker.vue';
import JLocationPicker from '../_components/JLocationPicker.vue';
import language from '../../../utils/language';
import { constants } from '../../../utils/constants';

export default {
    components: {
        JVideoMiniBar,
        JVideoUpload,
        JMoodPicker,
        JPrivacyPicker,
        JLocationPicker,
        JVideoFetcher,
    },

    data() {
        const { enablevideosupload } = constants.get('conf');
        const defaultType = enablevideosupload ? '' : 'fetch';

        return {
            moodPicker: false,
            privacy: false,
            location: false,
            type: defaultType,
            defaultType,
            dropArea: false,
            orText: language('video.or'),
        }
    },

    computed: {
        categoryId: {
            get() {
                return this.$store.state.video.catid;
            },

            set(value) {
                this.$store.commit('video/setCategory', value);
            },
        },

        locationName() {
            const location = this.$store.state.video.attachment.location;
            return location.length === 3 ? location[0] : '';
        },
    },

    methods: {
        showDropArea() {
            if (this.type === 'fetch') {
                return;
            }

            this.dropArea = true;
        },

        hideDropArea() {
            if (this.type === 'fetch') {
                return;
            }

            this.dropArea = false;
        },

        showLocation() {
            this.location = true;
        },

        hideLocation() {
            this.location = false;
        },

        setLocation(location) {
            this.$store.commit('video/setLocation', location);
            this.hideLocation();
        },

        removeLocation() {
            this.$store.commit('video/setLocation', '');
            this.hideLocation();
        },

        setType(value) {
            this.type = value;
        },

        validate() {
            if (this.type === 'upload') {
                return this.$refs.uploader.validate();
            }
            
            if (this.type === 'fetch') {
                return this.$refs.fetcher.post();
            }
        },

        showMoodPicker() {
            this.moodPicker = true;
        },

        hideMoodPicker() {
            this.moodPicker = false;
        },

        showPrivacy() {
            this.privacy = true;
        },

        hidePrivacy() {
            this.privacy = false;
        },

        setMood(mood) {
            this.$store.commit('video/setMood', mood);
        },

        setPrivacy(privacy) {
            this.$store.commit('video/setPrivacy', privacy);
        },

        reset() {
            this.type = this.defaultType;

            this.$refs.fetcher && this.$refs.fetcher.reset();
            this.$refs.uploader && this.$refs.uploader.reset();

            this.$store.commit('video/reset');
            this.$store.commit('setFree', true);
        },
    },
}
</script>

<style lang="scss">
.joms-postbox-video {
    .joms-postbox-video--inner {
        position: relative;
    }

    .joms-postbox-video-spacer {
        text-align: center;
        border-top: dotted 1px #f5f5f5;
        border-bottom: dotted 1px #f5f5f5;
        padding-top: 5px;
        padding-bottom: 5px;
    }
}
</style>