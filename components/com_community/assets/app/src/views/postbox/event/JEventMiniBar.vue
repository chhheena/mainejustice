<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('showEventConfig')">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-cog'" />
                </svg>
                 <span class="visible-desktop">{{eventDetailText}}</span>
            </li>
        </ul>
        <JAction 
            v-if="!isFree" 
            :isPostable="isPostable" 
            @reset="$emit('reset')"
            @validate="$emit('validate')"/>
        <JLoading v-if="isLoading" />
    </JMiniBar>
</template>

<script>
import JMiniBar from '../_components/JMiniBar.vue';
import JAction from '../_components/JAction.vue';
import JLoading from '../_components/JLoading.vue';
import language from '../../../utils/language';

export default {
    components: {
        JMiniBar,
        JAction,
        JLoading,
    },

    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;

        return {
            currentUrl,
            eventDetailText: language('event.event_detail').toLowerCase()
        };
    },

    computed: {
        isPostable() {
            const numCharLeft = this.$store.state.numCharLeft;
            const hasTitle = !!this.$store.state.event.attachment.title;
            const hasCategory = !!this.$store.state.event.attachment.catid;

            return hasTitle && hasCategory && numCharLeft > -1;
        },

        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },
    },
}
</script>
