<template>
    <div class="joms-postbox">
        <div id="joms-postbox-status" class="joms-postbox-content">
            <div class="joms-postbox-tabs">
                <component :is="currentComponent"></component>
            </div>
        </div>
        <JTabNav v-show="tabShow" />
    </div>
</template>

<script>
import JTabNav from './_components/JTabNav.vue';
import JCustom from './custom/JCustom.vue';
import JEvent from './event/JEvent.vue';
import JFile from './file/JFile.vue';
import JGif from './photo/JGif.vue';
import JPhoto from './photo/JPhoto.vue';
import JPoll from './poll/JPoll.vue';
import JStatus from './status/JStatus.vue';
import JVideo from './video/JVideo.vue';

export default  {
    components: {
        JTabNav,
        JCustom,
        JEvent,
        JFile,
        JGif,
        JPhoto,
        JPoll,
        JStatus,
        JVideo,
    },

    computed: {
        currentComponent() {
            const activeTab = this.$store.state.activeTab;
            return 'J' + this.ucfirst(activeTab);
        },

        tabShow() {
            return this.$store.state.free;
        },
    },

    watch: {
        tabShow(value) {
            return;

            if (value) {
                window.onbeforeunload = '';
            } else {
                window.onbeforeunload = () => '';
            }
        },
    },

    methods: {
        ucfirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
    }
}
</script>

<style lang="scss">
.joms-postbox {
    input, textarea {
        outline: unset;
    }
}
</style>