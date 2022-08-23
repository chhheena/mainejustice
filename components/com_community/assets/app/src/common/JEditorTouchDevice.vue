<template>
    <div class="j-editor-touch-device" >
        <div class="j-textarea__beautifier" v-html="beautiferValue"></div>
        <textarea-autosize
            class="j-textarea"
            v-model="value"
            :placeholder="placeholder"
            :min-height="isColorful ? 200 : 50"
            @input="onInputTextarea"
            @keydown.native="onKeyDownTextarea"
            @focus.native="onFocus" />
        <div v-html="placeholderStyle"></div>
        <div class="j-mention-list" v-if="mentionListShow">
            <div class="j-mention-item" 
                v-for="friend in mentionList" 
                :key="friend.id" 
                @click="addTag(friend)">
                <img :src="friend.avatar">
                <span>{{friend.name}}</span>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import VueTextareaAutosize from "vue-textarea-autosize";
import debounce from 'lodash/debounce';

Vue.use(VueTextareaAutosize);

export default {
    props: {
        bg: {
            type: Object,
            default() {
                return {};
            },
        },
        isColorful: {
            type: Boolean,
            default: false,
        },

        placeholder: {
            type: String,
            default: "What's on your mind?",
        },
    },

    data() {
        return {
            beautiferValue: '',
            value: '',
            tagsAdded: [],
            mentionList: [],
            mentionListShow: false,
            mentionCharPos: 0,
            cursorPos: 0,
            afterTagging: false,
        }
    },

    computed: {
        placeholderStyle() {
            if (this.bg.placeholdercolor) {
                return `
                <style>
                    .joms-postbox__status-composer textarea.j-textarea::-webkit-input-placeholder {
                        color: #${this.bg.placeholdercolor};
                    }

                    .joms-postbox__status-composer textarea.j-textarea:-moz-placeholder {
                        color: #${this.bg.placeholdercolor};  
                    }

                    .joms-postbox__status-composer textarea.j-textarea::-moz-placeholder {
                        color: #${this.bg.placeholdercolor};  
                    }

                    .joms-postbox__status-composer textarea.j-textarea:-ms-input-placeholder {
                        color: #${this.bg.placeholdercolor};  
                    }

                    .joms-postbox__status-composer textarea.j-textarea::placeholder {
                        color: #${this.bg.placeholdercolor};  
                    }

                    .joms-postbox__status-composer .colorful-editor .j-textarea__beautifier span {
                        color: #${this.bg.textcolor};
                    }
                </style>`;
            }

            return '';
        },
    },

    methods: {
        addTag(friend) {
            this.tagsAdded.push({
                id: friend.id,
                name: friend.name,
                start: this.mentionCharPos,
                length: friend.name.length,
            });

            const head = this.value.slice(0, this.mentionCharPos);
            const tail = this.value.substring(this.cursorPos);

            this.value = head + friend.name + tail;
            this.afterTagging = true;
            setTimeout(() => {
                this.afterTagging = false
            }, 300);

            this.updateBeautifier(this.value, this.tagsAdded);
            this.updateHiddenInput(this.value, this.tagsAdded);
            this.hideMentionList();
            this.focus();
        },

        updateBeautifier: debounce(function(value, tags) {
            const rEol = /\n/g;
            const rEolReplace ='<br>';

            let rMatch, rReplace, start, tag, i;

            if ( tags.length ) {
                rMatch = '^';
                rReplace = '';
                start = 0;

                for ( i = 0; i < tags.length; i++ ) {
                    tag = tags[i];
                    rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
                    rReplace += '$' + ( i * 2 + 1 ) + '[span]' + tag.name + '[/span]';
                    start = tag.start + tag.length;
                }

                rMatch = new RegExp( rMatch );
                value = value.replace( rMatch, rReplace );
            }

            value = value.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
            value = value.replace( /\[(\/?span)\]/g, '<$1>' );
            value = value.replace( rEol, rEolReplace );

            this.beautiferValue = value;
        }, 1),

        updateHiddenInput: debounce(function(value, tags) {
            let rMatch, rReplace, start, tag, i;

            if ( tags.length ) {
                rMatch = '^';
                rReplace = '';
                start = 0;

                for ( i = 0; i < tags.length; i++ ) {
                    tag = tags[i];
                    rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
                    rReplace += '$' + ( i * 2 + 1 ) + '@[[' + tag.id + ':contact:' + tag.name + ']]';
                    start = tag.start + tag.length;
                }

                rMatch = new RegExp( rMatch );
                value = value.replace( rMatch, rReplace );
            }

            const tagCleanedString = value.replace(/@\[\[.*?\]\]/g, '0');
            const numchar = tagCleanedString.length;

            this.$emit('change', {value, numchar});
        }, 100),

        onKeyDownTextarea() {
            const $textarea = this.$el.querySelector('.j-textarea');

            this.prevSelStart = $textarea.selectionStart;
            this.prevSelEnd = $textarea.selectionEnd;
        },

        onInputTextarea(value) {
            var delta, tag, length, name, tmp, index, rMatch, rReplace, shift, i, j;
            
            const $textarea = this.$el.querySelector('.j-textarea');
       
            if (this.tagsAdded.length && !this.afterTagging) {

                // if text is selected (selectionStart !== selectionEnd)
                if ( this.prevSelStart !== this.prevSelEnd ) {
                    for ( i = 0; i < this.tagsAdded.length; i++ ) {
                        tag = this.tagsAdded[i];
                        length = tag.start + tag.length;
                        if (
                            // Intersection.
                            ( this.prevSelStart > tag.start && this.prevSelStart < length ) ||
                            ( this.prevSelEnd > tag.start && this.prevSelEnd < length ) ||
                            // Enclose.
                            ( tag.start >= this.prevSelStart && length <= this.prevSelEnd )
                        ) {
                            this.tagsAdded.splice( i--, 1 );
                        }
                    }
                }

                delta = $textarea.selectionStart - this.prevSelStart - ( this.prevSelEnd - this.prevSelStart );

                for ( i = 0; i < this.tagsAdded.length; i++ ) {
                    tag = this.tagsAdded[i];
                    length = tag.start + tag.length;
                    
                    const cursorBeforStartTag = tag.start > this.prevSelStart;
                    const cursorAtStartTag = tag.start === this.prevSelStart
                    const cursorAfterEndTag = length < this.prevSelStart;
                    const cursorInsideTag = length > this.prevSelStart;
                    const backspace = delta < 0;

                    if (cursorBeforStartTag || cursorAtStartTag) {
                        tag.start += delta;
                    } else if (!cursorAfterEndTag && (cursorInsideTag || backspace)) {
                        this.tagsAdded.splice( i--, 1 );
                    }
                }
            }

            this.updateBeautifier(this.value, this.tagsAdded);
            this.updateHiddenInput(this.value, this.tagsAdded);
            this.toggleMentionList(value);
        },

        onFocus() {
            this.$emit('focus');
        },

        toggleMentionList: debounce(function(value) {
            const $textarea = this.$el.querySelector('.j-textarea');
            this.cursorPos = $textarea.selectionStart;
            
            const beforeCursorPos = value.slice(0, this.cursorPos);
            const mentionCharIndex = beforeCursorPos.lastIndexOf('@');
            
            if (mentionCharIndex < 0) {
                this.hideMentionList();
                return;
            }

            if (!(mentionCharIndex === 0 || !!beforeCursorPos[mentionCharIndex - 1].match(/\s/g))) {
                this.hideMentionList();
                return;
            }

            this.mentionCharPos =  this.cursorPos - (beforeCursorPos.length - mentionCharIndex);

            const textAfter = beforeCursorPos.substring(mentionCharIndex + 1);
            if (textAfter.length) {
                const mentionChar = beforeCursorPos[mentionCharIndex];

                const matchedFriends = joms_friends.filter(friend => {
                    const matchedName = friend.name.toLowerCase().indexOf(textAfter.toLowerCase()) > -1;
                    const isTagged = this.tagsAdded.find(tagged => tagged.id === friend.id);

                    return matchedName && !isTagged;
                });

                if (matchedFriends.length) {
                    Vue.set(this, 'mentionList', matchedFriends);
                    this.showMentionList();

                    return;
                }
            }

            this.hideMentionList();
        }, 300),

        hideMentionList() {
            this.mentionListShow = false;
        },

        showMentionList() {
            this.mentionListShow = true;
        },

        reset() {
            this.value = '';
            Vue.set(this, 'tagsAdded', []);
        },

        focus() {
            const $textarea = this.$el.querySelector('.j-textarea');
            
            $textarea && $textarea.focus();
        },

        insertEmoji(emoji) {
            const $textarea = this.$el.querySelector('.j-textarea');
            const head = $textarea.value.slice(0, $textarea.selectionStart);
            const tail = $textarea.value.substring($textarea.selectionStart);

            this.value = head + emoji.native + tail;

            setTimeout(() => {
                $textarea.selectionStart = head.length + emoji.native.length;
                this.focus();
            });
        }
    }
}
</script>

<style lang="scss">
.j-editor-touch-device {
    position: relative;
    background-color: transparent;

    &.colorful-editor {
        .j-textarea__beautifier {
            font-size: 25px;
            line-height: 32px;
            padding-top: 25px;
            padding-left: 30px;
            padding-right: 30px;
            text-align: center;
            
            span {
                background-color: inherit;
                background-image: inherit;
                box-shadow: unset;
                border-radius: unset;
                display: inline;
                text-decoration: underline;
            }
        }

        textarea {
            font-size: 25px;
            line-height: 32px;
            padding-top: 25px;
            padding-left: 30px;
            padding-right: 30px;
            text-align: center;
        }
    }

    .j-textarea {
        width: 100%;
        padding: 10px;
        margin: 0;
        background-color: transparent;
        border: none;
        box-shadow: none;
        position: relative;
        color: inherit;
    }

    .j-textarea__beautifier {
        padding: 10px;
        padding-top: 10px;
        color: transparent;
        height: auto;
        min-height: 0;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        white-space: pre-wrap;
        word-wrap: break-word;

        span {
            background: #dcdfea;
            background-image: linear-gradient(#dce6f8,#bdcff1);
            box-shadow: 0 0 0 1px #a3bcea;
            border-radius: 2px;
            display: inline;
        }
    }

    .j-mention-list {
        position: absolute;
        padding-top: 5px;
        padding-bottom: 5px;
        width: 100%;
        border: 1px solid #F0F0F0;
        border-radius: 4px;
        background-color: #FFFFFF;
        box-shadow: 0 2px 4px 0px #1e1e1e;
        z-index: 3;
    }

    .j-mention-item {
        padding: 5px;
        color: #000;

        &:hover {
            color: #fff;
            background-color: darkcyan;
        }
    }
}
</style>