<template>
    <div class="joms-postbox-status">
        <JUrlPreview :data="preview" @removePreview="removePreview"/>
        <JStatusComposer ref="composer" @urlAppear="urlAppear"/>
        <JStatusMiniBar 
            v-show="!$store.state.free"
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
import JStatusComposer from './JStatusComposer.vue';
import JStatusMiniBar from './JStatusMiniBar.vue';
import JMoodPicker from '../_components/JMoodPicker.vue';
import JLocationPicker from '../_components/JLocationPicker.vue';
import JPrivacyPicker from '../_components/JPrivacyPicker.vue';
import JUrlPreview from '../_components/JUrlPreview.vue';
import unescape from 'unescape';

export default {
    components: {
        JStatusComposer,
        JStatusMiniBar,
        JMoodPicker,
        JLocationPicker,
        JPrivacyPicker,
        JUrlPreview,
    },

    data() {
        return {
            moodPicker: false,
            location: false,
            privacy: false,
            preview: {
                url: '',
                title: '',
                desc: '',
                image: '',
            },
        }
    },

    computed: {
        locationName() {
            const location = this.$store.state.status.attachment.location;
            return location.length === 3 ? location[0] : '';
        },
    },

    methods: {
        validate() {
            this.post();
        },

        post() {
            const DATA = Joomla.getOptions('com_community');
            const state = this.$store.state.status;
            const content = state.content;
            const attachments = JSON.stringify(state.attachment);
            const rawData = [content, attachments];
            
            if (DATA.stream_filter_params) {
                const filterParams = JSON.stringify(DATA.stream_filter_params);
                rawData.push(filterParams);
            }

            this.$store.dispatch('post', rawData).then(() => {
                this.reset();
            });
        },

        showMoodPicker() {
            this.moodPicker = true;
        },

        hideMoodPicker() {
            this.moodPicker = false;
        },

        showLocation() {
            this.location = true;
        },

        hideLocation() {
            this.location = false;
        },

        showPrivacy() {
            this.privacy = true;
        },

        hidePrivacy() {
            this.privacy = false;
        },

        reset() {
            this.resetPreview();

            this.$refs.composer.reset();

            this.$store.commit('status/reset');
            this.$store.commit('setFree', true);
        },

        setPrivacy(privacy) {
            this.$store.commit('status/setPrivacy', privacy);
        },

        setMood(mood) {
            this.$store.commit('status/setMood', mood);
        },

        setLocation(location) {
            this.$store.commit('status/setLocation', location);
            this.hideLocation();
        },

        removeLocation() {
            this.$store.commit('status/setLocation', '');
            this.hideLocation();
        },

        urlAppear(url) {
            this.$store.commit('setLoading', true);

            joms.ajax({
                func: 'system,ajaxGetFetchUrl',
                data: [ url ],
                callback: json => {
                    const images = ( json.image || [] ).concat( json['og:image'] || [] );
                    
                    this.preview.image = images.length ? images[0] : '';
                    this.preview.url = url;
                    this.preview.title = unescape(json.title) || url;
                    this.preview.desc = unescape(json.description) || '';

                    this.setPreview(this.preview);
                    this.$store.commit('setLoading', false);
                }
            })
        },

        setPreview(data) {
            this.$store.commit('status/setPreview', data);
        },

        removePreview() {
            this.resetPreview();
            this.$store.commit('status/setPreview', false);
        },

        resetPreview() {
            this.preview.url = '';
            this.preview.title = '';
            this.preview.desc = '';
            this.preview.image = '';
        },
    },
}
</script>
