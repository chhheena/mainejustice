<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('showMoodPicker')" v-if="enablemood">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-happy'" />
                </svg>
            </li>
            <li @click="$emit('showLocation')" v-if="enablelocation">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-location'" />
                </svg>
            </li>
            <li v-if="isMyProfile" @click="$emit('showPrivacy')">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-' + privacyIcon" />
                </svg>
            </li>
            <li v-for="tab in tabs" :key="tab.name" :title="tab.title" @click="setActiveTab(tab)">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="tab.icon" />
                </svg>
            </li>
        </ul>
        <JAction 
            v-if="!isFree" 
            :isPostable="isPostable" 
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
        const { 
            enablemood,
            enablelocation,
            enablephotos, 
            enablephotosgif,
            enablevideos,
            enableevents,
            enablefiles,
            enablepolls,
            enablecustoms,
        } = constants.get('conf');

        const { 
            isAdmin, 
            isMyProfile,
            isProfile,
            isGroup,
            isEvent,
            isPage
        } = constants.get('settings');

        const tabs = [];

        if ((isProfile || isGroup || isEvent || isPage) && enablephotos) {
            tabs.push({
                name: 'photo',
                title: language('postbox.photo'),
                icon: window.joms_current_url + '#joms-icon-camera',
            });

            if (enablephotosgif) {
                tabs.push({
                    name: 'gif',
                    title: language('postbox.gif'),
                    icon: window.joms_current_url + '#joms-icon-images',
                });
            }
        }

        if ((isProfile || isGroup || isEvent || isPage) && enablevideos) {
            tabs.push({
                name: 'video',
                title: language('postbox.video'),
                icon: window.joms_current_url + '#joms-icon-play',
            });
        }

        if ((isProfile || isGroup || isPage) && enableevents) {
            tabs.push({
                name: 'event',
                title: language('postbox.event'),
                icon: window.joms_current_url + '#joms-icon-calendar',
            });
        }

        if ((isProfile || isGroup || isEvent || isPage) && enablefiles) {
            tabs.push({
                name: 'file',
                title: language('postbox.file'),
                icon: window.joms_current_url + '#joms-icon-file-zip',
            });
        }

        if ((isProfile || isGroup || isEvent || isPage) && enablepolls) {
            tabs.push({
                name: 'poll',
                title: language('postbox.poll'),
                icon: window.joms_current_url + '#joms-icon-list',
            });
        }

        if (isAdmin && enablecustoms) {
            tabs.push({
                name: 'custom',
                title: language('postbox.custom'),
                icon: window.joms_current_url + '#joms-icon-bullhorn',
            });
        }

        return {
            currentUrl,
            enablemood,
            enablelocation,
            isMyProfile,
            tabs,
        }
    },

    computed: {
        isPostable() {
            const content = this.$store.state.status.content.trim();
            const numCharLeft = this.$store.state.numCharLeft;
            return !!content && numCharLeft > -1;
        },

        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },

        privacyIcon() {
            const {status, privacies} = this.$store.state;
            const privacy = privacies.find(item => {
                return item.value === status.attachment.privacy;
            });

            return privacy.icon;
        },
    },

    methods: {
        setActiveTab(tab) {
            this.$emit('reset');
            this.$store.commit('setActiveTab', tab.name);
            this.$store.commit('setFree', true);
        },
    }
}
</script>
