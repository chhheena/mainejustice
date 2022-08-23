<template>
    <div class="j-video-upload">
        <div class="joms-postbox-inner-panel" style="position: relative;" v-show="!preview">
            <div class="joms-postbox-file-upload">
                <svg viewBox="0 0 16 18" class="joms-icon">
                    <use :href="currentUrl + '#joms-icon-play'" class="joms-icon--svg-fixed joms-icon--svg-unmodified"></use>
                </svg>
                {{uploadAreaText}}
            </div>
        </div>
        <div class="j-video-upload-area dropzone">
            <div class="joms-postbox--droparea" 
                v-if="dropArea"
                @dragleave="$emit('hideDropArea')"
                @drop="$emit('hideDropArea')">
                <div class="joms-postbox--droparea__inner">
                    {{dropAreaText}}
                </div>
            </div>
            <div class="j-video-upload-preview" v-show="preview"></div>
            
        </div>
        <div class="j-video-upload-info" v-if="preview">
            <div class="j-video-upload-category">
                <JDropdownSelect 
                    v-model="categoryId"
                    :options="categories"
                    :placeholder="selectCategoryText" />
            </div>
            <div class="video-title">
                <input 
                    type="text" 
                    :placeholder="videoTitlePlaceholder"
                    v-model="title" />
            </div>
            <JVideoComposer ref="composer" />
        </div>
    </div>
</template>

<script>
import 'dropzone/dist/dropzone.css';

import Dropzone from 'dropzone';
import sortCategories from '../../../utils/sort-categories';
import {constants} from '../../../utils/constants';
import JDropdownSelect from '../_components/JDropdownSelect.vue';
import JVideoComposer from './JVideoComposer.vue';
import language from '../../../utils/language';

Dropzone.autoDiscover = false;

export default {
    components: {
        JDropdownSelect,
        JVideoComposer,
    },

    props: {
        dropArea: {
            type: Boolean,
            default: false,
        },
    },
    
    data() {
        const DATA = Joomla.getOptions('com_community');
        const currentUrl = DATA.current_url;
        const sorted = sortCategories(constants.get('videoCategories'));
        const categories = sorted.map(item => {
            return {
                value: item.id,
                text: 'Category: ' + item.name,
            }
        });

        return {
            categories,
            currentUrl,
            dropzone: {},
            preview: false,
            title: '',
            uploadAreaText: language('video.upload_button'),
            dropAreaText: language('video.drop_to_upload'),
            videoTitlePlaceholder: language('video.upload_title'),
            selectCategoryText: language('select_category'),
        };
    },

    mounted() {
        const state = this.$store.state.video;
        const baseUrl = Joomla.getOptions('com_community').base_url;
        const $uploadArea = this.$el.querySelector('.j-video-upload-area');
        const $btnUpload = this.$el.querySelector('.joms-postbox-file-upload');
        const $previewsContainer = this.$el.querySelector('.j-video-upload-preview');
        const {
            maxvideouploadsize, 
            max_input_time,
            videoextensions
        } = constants.get('conf');

        const exts = videoextensions
            .split(',')
            .filter(val => val.trim())
            .map(val => val.trim().toLowerCase());

        const fileTypes = exts.length ? exts : ['mp4'];

        let uploadUrl;
        switch (state.attachment.element) {
            case 'groups':
                uploadUrl = baseUrl + 'index.php?option=com_community&view=videos&task=uploadvideo&creatortype=group&groupid=' + state.attachment.target;
                break;
            case 'events':
                uploadUrl = baseUrl + 'index.php?option=com_community&view=videos&task=uploadvideo&creatortype=event&eventid=' + state.attachment.target;
                break;
            case 'pages':
                uploadUrl = baseUrl + 'index.php?option=com_community&view=videos&task=uploadvideo&creatortype=page&pageid=' + state.attachment.target;
                break;
            default:
                uploadUrl = baseUrl + 'index.php?option=com_community&view=videos&task=uploadvideo&creatortype=user';
                break;
        }
        const dropzone = new Dropzone($uploadArea, {
            url: uploadUrl,
            timeout: max_input_time * 1000,
            createImageThumbnails: false,
            clickable: $btnUpload,
            addRemoveLinks: true,
            maxFiles: 1,
            maxFilesize: maxvideouploadsize,
            previewsContainer: $previewsContainer,
            acceptedFiles: fileTypes.map(type => {
                return '.' + type; 
            }).join(','),
            autoProcessQueue: false,
            dictRemoveFile: language('remove'),
            dictCancelUpload: language('cancel'),
        });

        dropzone.on('sending', (file, xhr, formData) => {
            formData.append('name', file.name);
            formData.append('title', this.title);
            formData.append('description', state.content.trim());
            formData.append('category_id', state.catid);
            formData.append('mood', state.attachment.mood);
            formData.append('permissions', state.attachment.privacy);

            if (state.attachment.location.length) {
                formData.append('location', state.attachment.location.join(','));
            }
        });

        dropzone.on('maxfilesexceeded', file => {
            dropzone.removeFile(file);
            this.showError('Too many files to upload');
        });

        dropzone.on('addedfile', file => {
            const frags = file.name.split('\.');
            const ext = frags.pop().toLowerCase();

            if (frags.length < 1 || fileTypes.indexOf(ext) === -1) {
                dropzone.removeFile(file);
                alert(language('file.file_type_not_permitted'));
            }

            if (file.size > maxvideouploadsize * 1024 * 1024) {
                const size = Math.round(file.size / 1024 / 1024 * 100);

                dropzone.removeFile(file);
                return alert(language('video.max_upload_size_error') || file.name + ' is too big (' + size / 100 + ' MB). Maximum is ' + maxvideouploadsize + ' MB');
            }

            this.togglePreview();
            this.setFilesChange();
        });

        dropzone.on('removedfile', file => {
            this.togglePreview();
            this.setFilesChange();
        });

        dropzone.on('success', file => {
            const res = JSON.parse(file.xhr.response);
            if (res.error) {
                alert(res.msg);
                dropzone.removeFile(file);
            }

            if (res.status === 'error') {
                alert(res.message);
                dropzone.removeFile(file);
            }
            
            if (res.status === 'success') {
                alert(res.processing_str);
                this.reset();
                this.$emit('reset');
            }

            this.$store.commit('setLoading', false);
        });

        dropzone.on('canceled', () => {
            this.togglePreview();
        });

        this.dropzone = dropzone;
    },

    computed: {
        categoryId: {
            get() {
                return this.$store.state.video.catid;
            },

            set(value) {
                this.$store.commit('video/setCategory', value);
            },
        },
    },

    methods: {
        validate() {
            const state = this.$store.state.video;
            if (!+state.catid) {
                return alert('Please choose category!');
            }

            if (!this.title) {
                return alert('Missing video title');
            }

            this.upload();
        },

        upload() {
            this.$store.commit('setLoading', true);
            this.dropzone.processQueue();
        },

        togglePreview() {
            this.preview = this.dropzone.files.length ? true : false;
        },

        setFilesChange() {
            if (this.dropzone.files.length) {
                this.$emit('typeChange', 'upload');
                this.$store.commit('setFree', false);
            } else {
                this.$emit('reset');
            }
        },

        reset() {
            this.title = '';

            this.dropzone.files.forEach(file => {
                this.dropzone.removeFile(file);
            });

            this.$refs.composer && this.$refs.composer.reset();
        },
    },
}
</script>

<style lang="scss">
.j-video-upload {
    .j-video-upload-preview {
        display: flex;
        justify-content: center;
    }

    .joms-postbox-inner-panel {
        border: none;
    }

    .j-video-upload-area {
        min-height: unset;
        border: none;
        background: unset;
        padding: inherit;

        &.dz-drag-hover {
            border: none;
        }

        .dz-message {
            display: none;
        }
    }

    .joms-postbox--droparea {
        display: block;

        .joms-postbox--droparea__inner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-transform: uppercase;
        }
    }

    .j-video-upload-category {
        padding: 13px;
        border-bottom: solid 1px #f5f5f5;
    }

    .video-title {
        padding: 10px 13px;
        border-bottom: solid 1px #f5f5f5;

        input {
            width: 100%;
            padding: 0;
            margin: 0;
            border: none;
            box-shadow: none;
        }
    }
}
</style>