<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('showMoodPicker')" v-if="videoType && enableMood" >
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-happy'" />
                </svg>
                 <span class="visible-desktop">mood</span>
            </li>
            <li @click="$emit('showLocation')" v-if="videoType && enableLocation">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-location'" />
                </svg>
                 <span class="visible-desktop">location</span>
            </li>
            <li @click="$emit('showPrivacy')" v-if="isMyProfile">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-' + privacy.icon" />
                </svg>
                 <span class="visible-desktop">{{privacy.title.toLowerCase()}}</span>
            </li>
            <li class="joms-minibar-spacer" style="visibility: hidden;">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-pencil'" />
                </svg>
            </li>
        </ul>
        <JAction 
            v-if="!isFree" 
            :isPostable="isPostable" 
            :save="videoType === 'upload' ? 'Upload' : 'Post'"
            @reset="$emit('reset')"
            @validate="$emit('validate')" />
        <JLoading v-if="isLoading" />
    </JMiniBar>
</template>

<script>
import JMiniBar from '../_components/JMiniBar.vue';
import JAction from '../_components/JAction.vue';
import JLoading from '../_components/JLoading.vue';
import {constants} from '../../../utils/constants';

export default {
    props: {
        videoType: {
            type: String,
            default: '',
        },
    },

    components: {
        JMiniBar,
        JAction,
        JLoading,
    },

    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;
        const {isMyProfile} = constants.get('settings');
        const { enablemood, enablelocation } = constants.get('conf');

        return {
            currentUrl,
            enableMood: enablemood,
            enableLocation: enablelocation,
            isMyProfile,
        }
    },

    computed: {
        isPostable() {
            if (this.videoType === 'fetch') {
                const hasVideo = !!this.$store.state.video.attachment.fetch.length;
                const numCharLeft = this.$store.state.numCharLeft;
                return hasVideo && numCharLeft > -1;
            }
            
            if (this.videoType === 'upload') {
                return true;
            }

            return false;
        },

        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },

        privacy() {
            const {video, privacies} = this.$store.state;
            const privacy = privacies.find(item => {
                return item.value === video.attachment.privacy;
            });

            return privacy;
        }
    },
}
</script>
