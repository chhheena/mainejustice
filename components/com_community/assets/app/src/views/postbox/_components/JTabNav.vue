<template>
    <nav class="joms-postbox-tab joms-postbox-tab-root">
        <ul class="joms-list inline">
            <li v-for="tab in tabs" 
                :class="{active: tab.name === $store.state.activeTab}"
                :key="tab.name"
                :title="tab.title"
                @click="setActive(tab.name)">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="tab.icon" />
                </svg>
                <span class="visible-desktop">{{tab.title}}</span>
            </li>
        </ul>
    </nav>
</template>
<script>
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    data() {
        const { 
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

        const tabs = [
            {
                name: 'status',
                title: language('postbox.status'),
                icon: window.joms_current_url + '#joms-icon-pencil',
            },
        ];

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
            tabs,
        }
    },

    methods: {
        setActive(tab) {
            if (tab === this.$store.state.activeTab) {
                return;
            }

            this.$store.commit('setActiveTab', tab);
        }
    }
};
</script>