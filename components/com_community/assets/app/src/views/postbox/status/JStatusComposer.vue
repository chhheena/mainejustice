<template>
    <div class="joms-postbox__status-composer" :style="composerStyle">
        <JEditorTouchDevice ref="editor" 
            :bg="bg"
            :isColorful="isColorful"
            :class="editorClass"
            :placeholder="placeholder"
            @focus="onEditorFocus"
            @change="onEditorChange" />
        <JCharCount :num="numCharLeft" />
        <JComposerAdditionInfo 
            v-if="moodId || location" 
            :moodId="moodId" 
            :location="location" />
        <div class="j-status-composer-toolbar">
            <div class="toolbar-bg-picker">
                <JStatusBackgroundPicker 
                    v-if="bg.id" 
                    v-show="!hideColorPicker" 
                    @setBg="setBg" />
            </div>
            <div class="toolbar-emoji" v-if="!isTouch">
                <JEmoji @selectEmoji="selectEmoji"/>
            </div>
        </div>
    </div>
</template>

<script>
import JStatusBackgroundPicker from "./JStatusBackgroundPicker.vue";
import JEditorTouchDevice from '../../../common/JEditorTouchDevice.vue';
import JEmoji from '../../../common/JEmoji.vue';
import JCharCount from '../_components/JCharCount.vue';
import JComposerAdditionInfo from '../_components/JComposerAdditionInfo.vue';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

import debounce from 'lodash/debounce';

export default {
    components: {
        JStatusBackgroundPicker,
        JEditorTouchDevice,
        JCharCount,
        JEmoji,
        JComposerAdditionInfo,
    },

    data() {
        return {
            charLimit: +constants.get('conf.statusmaxchar'),
            numchar: 0,
            savedBg: '0',
            bgPicker: true,
            urlAppeared: false,
            placeholder: language('status.status_hint'),
        }
    },
    
    mounted() {
        this.$store.commit('setNumCharLeft', this.charLimit - this.numchar);
    },

    computed: {
        isTouch() {
            return this.$store.state.isTouch;
        },

        isColorful() {
            return this.$store.state.status.attachment.colorful;
        },

        hasUrlPreview() {
            return !!this.$store.state.status.attachment.fetch.length;
        },

        hideColorPicker() {
            if (this.hasUrlPreview) {
                return true;                
            }

            const content = this.$store.state.status.content;
            const numline = content.split('\n').length;

            const maxLine = 4;
            const maxChar = 160;

            return numline > maxLine || this.numchar > maxChar;
        },

        editorClass() {
            return {
                'colorful-editor': this.isColorful,
                'normal-editor': !this.isColorful,
            }
        },

        bg() {
            const bgs = this.$store.state.bgs;
            if (!bgs.length) {
                return {};
            }

            const bgid = this.$store.state.status.attachment.bgid;
            const bg = bgs.find(item => {
                return item.id === bgid;
            });

            return bg;
        },
                
        moodId() {
            return this.$store.state.status.attachment.mood;
        },

        location() {
            const location = this.$store.state.status.attachment.location;

            return location.length ? location[0] : '';
        },

        composerStyle() {
            if (!this.bg) {
                return {};
            }

            return {
                backgroundImage: this.bg.image ? 'url(' + this.bg.image + ')' : '',
                color: this.bg.textcolor ? '#' + this.bg.textcolor : '',
            }
        },

        numCharLeft() {
            return this.$store.state.numCharLeft;
        },
    },

    watch: {
        hideColorPicker(value) {
            if (value && this.savedBg !== '0') {
                this.$store.commit('status/setBg', '0');
                return;
            }

            if (!value && this.savedBg !== '0') {
                this.$store.commit('status/setBg', this.savedBg);
                return;
            }
        }
    },

    methods: {
        onEditorFocus() {
            this.$store.commit('setFree', false);
        },

        onEditorChange({value, numchar}) {
            this.numchar = numchar;
            this.$store.commit('status/setContent', value);
            this.$store.commit('setNumCharLeft', this.charLimit - this.numchar);

            if (!this.urlAppeared && !this.isColorful) {
                this.fetchUrlPreview(value);
            }
        },

        fetchUrlPreview: debounce(function(value) {
            const urlRegex = /\s?((http|https):\/\/[a-z0-9-]+\.[a-z0-9-]+\S+)/;
            const match = value.match(urlRegex);

            if (match) {
                this.urlAppeared = true;
                this.$emit('urlAppear', match[1]);
            }
        }, 300),

        reset() {
            this.savedBg = '0';
            this.urlAppeared = false;
            this.$refs.editor.reset();
        },

        setBg(bgid) {
            this.savedBg = bgid;

            if (!this.isTouch) {
                this.$refs.editor.focus();
            }
        },

        selectEmoji(emoji) {
            this.$refs.editor.insertEmoji(emoji);
        },
    }
}
</script>

<style lang="scss">
.joms-postbox__status-composer {
    position: relative;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: background-image 300ms;

    .j-status-composer-toolbar {
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