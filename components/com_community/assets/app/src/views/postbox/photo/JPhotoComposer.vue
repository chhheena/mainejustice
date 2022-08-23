<template>
    <div class="joms-postbox__photo-composer">
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
            v-if="moodId" 
            :moodId="moodId" />
        <div class="j-photo-composer-toolbar">
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
import JMoodDisplay from '../_components/JMoodDisplay.vue';
import JEditorTouchDevice from '../../../common/JEditorTouchDevice.vue';
import JCharCount from '../_components/JCharCount.vue';
import JComposerAdditionInfo from '../_components/JComposerAdditionInfo.vue';
import {constants} from '../../../utils/constants';
import debounce from 'lodash/debounce';
import language from '../../../utils/language';

export default {
    components: {
        JEmoji,
        JEditorTouchDevice,
        JCharCount,
        JComposerAdditionInfo,
    },

    data() {
        return {
            charLimit: +constants.get('conf.statusmaxchar'),
            numchar: 0,
            placeholder: language('status.photo_hint')
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
            return this.$store.state.photo.attachment.mood;
        },

        numCharLeft() {
            return this.$store.state.numCharLeft;
        },
    },

    methods: {
        onEditorFocus() {
            this.$store.commit('setFree', false);
        },

        onEditorChange({value, numchar}) {
            this.numchar = numchar;
            this.$store.commit('photo/setContent', value);
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
.joms-postbox__photo-composer {
    position: relative;

    .j-photo-composer-toolbar {
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