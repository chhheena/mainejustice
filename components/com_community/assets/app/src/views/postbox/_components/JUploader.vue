<template>
    <div class="j-uploader dropzone" style="position: relative;" @dragenter="showDropArea">
        <div class="joms-postbox-preview" v-show="preview">
            <div class="joms-postbox-preview-inner"></div>
            <slot name="dropdown_select"></slot>
        </div>
        <div class="joms-postbox--droparea" 
            v-if="dropArea"
            @dragleave="hideDropArea"
            @drop="hideDropArea">
            <div class="joms-postbox--droparea__inner">
                {{config.dropAreaText}}
            </div>
        </div>
        <div class="joms-postbox-inner-panel" style="position:relative" v-show="!preview">
            <div class="joms-postbox-file-upload">
                <span>
                    <svg viewBox="0 0 16 18" class="joms-icon">
                        <use :href="currentUrl + '#joms-icon-images'" class="joms-icon--svg-fixed joms-icon--svg-unmodified"></use>
                    </svg>
                    {{config.uploadAreaText}}
                </span>
            </div>
        </div>
        <slot name="preview_tpl"></slot>
        <slot name="composer"></slot>
    </div>
</template>

<script>
import 'dropzone/dist/dropzone.css';

import debounce from 'lodash/debounce';
import Dropzone from 'dropzone';
import language from '../../../utils/language';
import {constants} from '../../../utils/constants';

Dropzone.autoDiscover = false;

export default {
    props: {
        config: Object,
    },

    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;

        return {
            currentUrl,
            files: [],
            preview: false,
            dropArea: false,
            dropzone: {},
        }
    },

    mounted() {
        const $uploadArea = this.$el.querySelector('.joms-postbox-file-upload');
        const $previewsContainer = this.$el.querySelector('.joms-postbox-preview-inner');
        const fileTypes = this.config.fileTypes;
        const {
            max_input_time
        } = constants.get('conf');

        const dropzone = new Dropzone(this.$el, {
            url: this.config.previewApi,
            timeout: max_input_time * 1000,
            createImageThumbnails: this.config.createImageThumbnails,
            clickable: $uploadArea,
            addRemoveLinks: true,
            maxFiles: this.config.maxFiles,
            maxFilesize: this.config.maxFilesize,
            parallelUploads: 1,
            previewsContainer: $previewsContainer,
            acceptedFiles: fileTypes.map(type => {
                return '.' + type; 
            }).join(','),
            dictRemoveFile: language('remove'),
            dictCancelUpload: language('cancel'),
        });

        dropzone.on('maxfilesexceeded', file => {
            dropzone.removeFile(file);
            this.showError(this.config.batch_notice || 'Too many files to upload');
        });

        dropzone.on('addedfile', file => {
            const frags = file.name.split('\.');
            const ext = frags.pop().toLowerCase();

            if (frags.length < 1 || fileTypes.indexOf(ext) === -1) {
                dropzone.removeFile(file);
                return alert(this.config.file_type_not_permitted || 'Error! File type is not allow. (' + file.name + ')');
            }

            if (file.size > this.config.maxFilesize * 1024 * 1024) {
                const size = Math.round(file.size / 1024 / 1024 * 100);

                dropzone.removeFile(file);
                return alert(this.config.max_upload_size_error || file.name + ' is too big (' + size / 100 + ' MB). Maximum is ' + this.config.maxFilesize + ' MB');
            }
        });

        dropzone.on('queuecomplete', () => {
            this.setFilesChange();
        });

        dropzone.on('sending', () => {
            this.togglePreview();
        });

        dropzone.on('removedfile', file => {
            this.togglePreview();

            if (!file.xhr) {
                return;
            }

            var res = JSON.parse(file.xhr.response);
            this.deleteTempUpload([res.id]);
            this.setFilesChange();
        });

        dropzone.on('success', file => {
            const res = JSON.parse(file.xhr.response);
            if (res.error) {
                alert(res.msg);
                dropzone.removeFile(file);
            }
        });

        dropzone.on('canceled', () => {
            this.togglePreview();
        });

        this.dropzone = dropzone;
    },

    methods: {
        deleteTempUpload(ids) {
            joms.ajax({
                func: this.config.removeTempApi || 'system,ajaxDeleteTempImage',
                data: [ids.join(',')],
            })
        },

        togglePreview() {
            this.preview = this.dropzone.files.length ? true : false;
        },

        showDropArea() {
            this.dropArea = true;
        },

        hideDropArea() {
            this.dropArea = false;
        },

        showError: debounce(function(msg) {
            alert(msg);
        }, 300),

        setFilesChange() {
            const files = this.dropzone.files.filter(file => {
                return file.status === 'success'
            }).map(file => {
                var res = JSON.parse(file.xhr.response);
                return res.id;
            });

            this.$emit('filesChange', files);
        },

        open() {
            this.$el.querySelector('.joms-postbox-file-upload').click();
        },

        reset() {
            this.dropzone.files.forEach(file => {
                this.dropzone.removeFile(file);
            });
        },
    }
}
</script>

<style lang="scss">
.j-uploader {
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

    .joms-postbox--droparea {
        display: block;

        .joms-postbox--droparea__inner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
    }

    .joms-postbox-file-upload {
        display: flex;
        justify-content: center;
        align-items: center;
        height: auto;
        min-height: 40px;
        line-height: unset;
    }
}
</style>
