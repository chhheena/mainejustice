<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('showPrivacy')">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-' + privacy.icon" />
                </svg>
                 <span class="visible-desktop">{{privacy.title.toLowerCase()}}</span>
            </li>
        </ul>
        <JAction 
            ref="action"
            v-if="!isFree" 
            :isPostable="isPostable" 
            @validate="$emit('validate')"
            @reset="$emit('reset')" />
        <JLoading v-if="isLoading" />
    </JMiniBar>
</template>

<script>
import JMiniBar from '../_components/JMiniBar.vue';
import JAction from '../_components/JAction.vue';
import JLoading from '../_components/JLoading.vue';

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
        }
    },

    computed: {
        isPostable() {
            return !!this.$store.state.custom.content && !!this.$store.state.custom.type;
        },

        isFree() {
            return this.$store.state.free;
        },

        isLoading() {
            return this.$store.state.loading;
        },

        privacy() {
            const {custom, privacies} = this.$store.state;
            const privacy = privacies.find(item => {
                return item.value === custom.privacy;
            });

            return privacy;
        }
    },
}
</script>
