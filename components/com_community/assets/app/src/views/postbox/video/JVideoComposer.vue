<template>
    <div class="joms-postbox__video-composer">
        <component ref="editor" 
            class="normal-editor"
            :is="currentEditor"
            :isColorful="false"
            :placeholder="placeholder"
            @focus="onEditorFocus"
            @change="onEditorChange">
        </component>
        <JCharCount :num="numCharLeft" />
        <JComposerAdditionInfo 
            v-if="moodId || location" 
            :moodId="moodId"
            :location="location" />
        <div class="j-video-composer-toolbar">
            <div class="toolbar-bg-picker">
            </div>
            <div class="toolbar-emoji" v-if="!isTouch">
                <JEmoji @selectEmoji="selectEmoji"/>
            </div>
        </div>
    </div>
</template>

<script>
import JEmoji from '../../../common/JEmoji.vue';
import JEditorTouchDevice from '../../../common/JEditorTouchDevice.vue';

import JCharCount from '../_components/JCharCount.vue';
import JComposerAdditionInfo from '../_components/JComposerAdditionInfo.vue';

import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    components: {
        JEditorTouchDevice,
        JComposerAdditionInfo,
        JCharCount,
        JEmoji,
    },

    data() {
        return {
            charLimit: +constants.get('conf.statusmaxchar'),
            numchar: 0,
            placeholder: language('status.video_hint'),
        }
    },
    
    mounted() {
        this.$store.commit('setNumCharLeft', this.charLimit - this.numchar);
    },

    computed: {
        currentEditor() {
            return 'JEditorTouchDevice';
        },

        isTouch() {
            return this.$store.state.isTouch;
        },

        moodId() {
            return this.$store.state.video.attachment.mood;
        },

        numCharLeft() {
            return this.$store.state.numCharLeft;
        },

        location() {
            const location = this.$store.state.video.attachment.location;

            return location.length ? location[0] : '';
        },
    },

    methods: {
        onEditorFocus() {
            this.$store.commit('setFree', false);
        },

        onEditorChange({value, numchar}) {
            this.numchar = numchar;
            this.$store.commit('video/setContent', value);
            this.$store.commit('setNumCharLeft', this.charLimit - this.numchar);
        },

        reset() {
            this.$refs.editor.reset();
        },

        selectEmoji(emoji) {
            this.$refs.editor.insertEmoji(emoji);
        },
    }
}
</script>

<style lang="scss">
.joms-postbox__video-composer {
    position: relative;

    .j-video-composer-toolbar {
        display: flex;
        padding: 10px;

        .toolbar-bg-picker {
            flex: 1;
        }

        .toolbar-emoji {
            margin-left: 20px;
        }
    }

    .joms-emo2 {
        cursor: inherit;
    }
}
</style>