<template>
    <JMiniBar>
        <ul class="joms-list inline">
            <li @click="$emit('openUploader')" v-if="addmore">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-images'"></use>
                </svg>
                 <span class="visible-desktop">add more photos</span>
            </li>
            <li @click="$emit('showMoodPicker')">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-happy'" />
                </svg>
                 <span class="visible-desktop">mood</span>
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

export default {
    components: {
        JMiniBar,
        JAction,
        JLoading,
    },

    props: {
        addmore: {
            type: Boolean,
            default: true,
        }
    },

    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;

        return {
            currentUrl,
        }
    },

    computed: {
        isPostable() {
            const numFile = this.$store.state.photo.attachment.id.length;
            const numCharLeft = this.$store.state.numCharLeft;
            return !!numFile && numCharLeft > -1;
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
