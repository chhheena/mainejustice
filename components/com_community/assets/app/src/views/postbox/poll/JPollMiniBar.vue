<template>
    <JMiniBar>
        <ul class="joms-list inline">
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
            ref="action"
            v-if="!isFree" 
            :isPostable="isPostable" 
            @validate="$emit('validate')"
            @reset="$emit('reset')"/>
        <JLoading v-if="isLoading" />
    </JMiniBar>
</template>

<script>
import JMiniBar from '../_components/JMiniBar.vue';
import JAction from '../_components/JAction.vue';
import JLoading from '../_components/JLoading.vue';
import {constants} from '../../../utils/constants';

export default {
    components: {
        JMiniBar,
        JAction,
        JLoading,
    },

    data() {
        const DATA = Joomla.getOptions('com_community');
        const currentUrl = DATA.current_url;
        const { isMyProfile } = constants.get('settings');

        return {
            currentUrl,
            isMyProfile,
        }
    },

    computed: {
        isPostable() {
            return !!this.$store.state.poll.content;
        },

        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },

        privacy() {
            const {poll, privacies} = this.$store.state;
            const privacy = privacies.find(item => {
                return item.value === poll.attachment.privacy;
            });

            return privacy;
        }
    },
}
</script>
