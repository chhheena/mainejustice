<template>
    <div class="j-editor-pc">
        <quillEditor 
            v-model="content"
            ref="editor"
            :options="editorOptions"
            @change="onEditorChange" />
        <div class="placeholder-style" v-html="placeholderStyle"></div>
    </div>
</template>

<script>
import 'quill/dist/quill.bubble.css';

import Quill from 'quill';
import { quillEditor } from "vue-quill-editor";
import '../utils/quill-emoji';
import '../utils/quill-mention/quill.mention';

import debounce from 'lodash/debounce';
import EventBus from '../utils/eventbus';

export default {
    props: {
        bg: {
            type: Object,
            default() {
                return {};
            },
        },
        placeholder: {
            type: String,
            default: 'What\'s on your mind?',
        },
    },

    components: {
        quillEditor,
    },

    data() {
        return {
            content: '',
            editorOptions: {
                modules: {
                    toolbar: false,
                    mention: {
                        allowedChars: /[^ ]/,
                        isolateCharacter: true,
                        showDenotationChar: false,
                        fixMentionsToQuill: true,
                        mentionDenotationChars: ["@"],
                        source(searchTerm, renderList, mentionChar, quill) {
                            const { ops } = quill.getContents();
                            const added = ops.filter(op => {
                                return op.insert.mention;
                            }).map(op => {
                                return op.insert.mention.id;
                            });

                            const matchedFriends = joms_friends.filter(friend => {
                                return friend.name.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1
                                    && added.indexOf(friend.id) === -1;
                            });

                            const matches = matchedFriends.map(friend => {
                                return {
                                    id: friend.id,
                                    value: friend.name,
                                    avatar: friend.avatar,
                                }
                            });

                            renderList(matches, searchTerm);
                        }
                    },
                },
                formats: ['mention', 'emoji'],
                placeholder: this.placeholder,
            },
        }
    },

    mounted() {
        const $editor = this.$el.querySelector('.ql-editor');
        $editor.addEventListener('focus', event => {
            this.onEditorFocus();
        });

        EventBus.$on('BlotClick', ({event, node}) => {
            const blot = Quill.find(node);
            const {quill} = this.$refs.editor;
            const index = quill.getIndex(blot);

            const $node = jQuery(node);
            const {left} = $node.offset();
            const width = $node.width();

            if ((event.clientX - left) < (width/2)) {
                quill.setSelection(index);
            } else {
                quill.setSelection(index + 1);
            }
        });

        const isFireFox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        if (isFireFox) {
            let onHoldMouse = false;

            $editor.addEventListener('mousedown', event => {
                onHoldMouse = true;
                setTimeout(() => {
                    onHoldMouse = false;
                }, 100);
            });

            $editor.addEventListener('click', event => {
                if (!onHoldMouse) {
                    return;
                }

                const {target} = event;
                if (target.tagName !== 'P') {
                    return;
                }

                if (!target.childNodes || !target.childNodes.length) {
                    return;
                }

                const firstChild = target.childNodes[0];
                const {left} = jQuery(firstChild).offset();
                if (event.clientX < left) {
                    return;
                }

                const lastChild = target.childNodes[target.childNodes.length - 1];
                const blot = Quill.find(lastChild);

                if (!blot.contentNode) {
                    return;
                }

                const {quill} = this.$refs.editor;
                const index = quill.getIndex(blot);
                quill.setSelection(index + 1);
            });
        }
    },

    computed: {
        placeholderStyle() {
            if (this.bg.placeholdercolor) {
                return [
                    '<style>',
                        '.joms-postbox__status-composer .ql-editor.ql-blank::before { color : #'+ this.bg.placeholdercolor +'}',
                    '</style>'
                ].join('\n');
            }

            return '';
        },
    },

    methods: {
        focus() {
            this.$refs.editor.quill.focus();
        },

        reset() {
            this.content = '';
            this.$refs.editor.quill.blur();
        },

        onEditorFocus() {
            this.$emit('focus');
        },

        onEditorChange: debounce(function({quill}) {
            const contents = quill.getContents();
            const cleanContent = this.cleanDuplicateMention(quill, contents);
            const value = cleanContent.ops.map(op => {
                if (op.insert.emoji) {
                    return op.insert.emoji.native;
                }
                
                if (op.insert.mention) {
                    return '@[[' + op.insert.mention.id + ':contact:' + op.insert.mention.value + ']]';
                }

                return op.insert;
            }).join('');

            const tagCleanedString = value.replace(/@\[\[.*?\]\]/g, '0');
            const numchar = tagCleanedString.length - 1;

            this.$emit('change', {value, numchar});
        }),

        cleanDuplicateMention(quill, contents) {
            let hasDuplicate = false;
            const mentioned = [];
            const cleanContent = contents.ops.map(op => {
                if (op.insert.mention) {
                    if (mentioned.indexOf(op.insert.mention.id) > -1) {
                        hasDuplicate = true;
                        return {
                            insert: op.insert.mention.value
                        };
                    }

                    mentioned.push(op.insert.mention.id);
                }

                return JSON.parse(JSON.stringify(op));
            });

            if (hasDuplicate) {
                quill.setContents(cleanContent, 'silent');

                const text = quill.getText();
                quill.setSelection(text.length);
                
                return quill.getContents();
            } else {
                return contents;
            }
        },

        insertEmoji(emoji) {
            const quill = this.$refs.editor.quill;
            const selection = quill.getSelection(true);
            
            quill.insertEmbed(selection.index, 'emoji', {
                id: emoji.id,
                native: emoji.native,
                style: JSON.stringify(emoji.style),
            });

            quill.setSelection(selection.index + 1);
            quill.blur();
        },
    }
}
</script>

<style lang="scss">
.j-editor-pc {
    &.normal-editor {
        .ql-editor {
            font-size: 16px;
            min-height: 70px;
        }

        .quill-emoji {
            > span {
                padding: 3px;
            }
        }

    }

    &.colorful-editor {
        position: relative;
        
        .ql-editor {
            font-size: 30px;
            font-weight: bold;
            text-align: center;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: stretch;
            align-content: stretch;
            padding-left: 21px;
            padding-right: 21px;

            &.ql-blank::before {
                opacity: .7;
                z-index: 1;
                transition: color 300ms;
            }

            > p {
                z-index: 2;
            }
        }

        .quill-emoji {
            > span {
                padding: 5px;
            }

            .quill-emoji-native {
                width: 30px;
                height: 30px;
                line-height: 30px;
            }
        }
        
        .mention {
            background-color: unset;
            text-decoration: underline;
        }
    }
}
</style>