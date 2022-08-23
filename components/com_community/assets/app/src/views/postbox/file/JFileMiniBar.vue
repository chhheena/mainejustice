<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('openUploader')">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-images'"></use>
                </svg>
                 <span class="visible-desktop">{{addMoreFileText}}</span>
            </li>
            <li @click="$emit('showMoodPicker')" v-if="enableMood">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-happy'" />
                </svg>
                 <span class="visible-desktop">{{moodText}}</span>
            </li>
            <li @click="$emit('showPrivacy')" v-if="isMyProfile">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-' + privacy.icon" />
                </svg>
                 <span class="visible-desktop">{{privacy.title.toLowerCase()}}</span>
            </li>
        </ul>
        <JAction 
            v-if="!isFree" 
            :isPostable="true" 
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
import language from '../../../utils/language';

export default {
    components: {
        JMiniBar,
        JAction,
        JLoading,
    },

    data() {
        const currentUrl = window.joms_current_url;
        const enableMood = constants.get('conf.enablemood');
        const isMyProfile = constants.get('settings.isMyProfile');

        return {
            currentUrl,
            enableMood,
            isMyProfile,
            addMoreFileText: language('file.upload_button_more'),
            moodText: language('status.mood'),
        }
    },

    computed: {
        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },

        privacy() {
            const {file, privacies} = this.$store.state;
            const privacy = privacies.find(item => {
                return item.value === file.attachment.privacy;
            });

            return privacy;
        }
    },
}
</script>
