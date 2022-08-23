<template>
    <div class="joms-postbox-file-wrapper">
        <j-uploader 
            ref="uploader"
            :config="config" 
            @filesChange="onFilesChange">
            <template v-slot:dropdown_select>
                <JDropdownSelect :options="albums" v-model="albumId"/>
            </template>
            <template v-slot:composer>
                <keep-alive v-if="composer">
                    <JPhotoComposer ref="composer" />
                </keep-alive>
            </template>
        </j-uploader>
        <JPhotoMiniBar 
            v-show="!$store.state.free"
            @showMoodPicker="showMoodPicker" 
            @openUploader="openUploader"
            @reset="reset"
            @validate="validate" />
        
        <keep-alive>
            <JMoodPicker 
                v-if="moodPicker" 
                v-click-outside="hideMoodPicker"
                @hideMoodPicker="hideMoodPicker"
                @setMood="setMood" />
        </keep-alive>
    </div>
</template>

<script>
import JPhotoComposer from './JPhotoComposer.vue';
import JPhotoMiniBar from './JPhotoMiniBar.vue';
import JUploader from '../_components/JUploader.vue';
import JMoodPicker from '../_components/JMoodPicker.vue';
import JDropdownSelect from '../_components/JDropdownSelect.vue';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    components: {
        JUploader,
        JDropdownSelect,
        JPhotoComposer,
        JPhotoMiniBar,
        JMoodPicker,
    },

    data() {
        const baseUrl = Joomla.getOptions('com_community').base_url;
        const {
            maxuploadsize, 
            num_photo_per_upload, 
        } = constants.get('conf');

        const {isGroup, isEvent, isPage} = constants.get('settings');
        let previewApi = baseUrl + 'index.php?option=com_community&view=photos&task=ajaxPreview';
        if ( isGroup ) {
            previewApi += '&no_html=1&tmpl=component&groupid=' + ( constants.get('groupid') || '' );
        }

        if ( isEvent ) {
            previewApi += '&no_html=1&tmpl=component&eventid=' + ( constants.get('eventid') || '' );
        }

        if ( isPage )  {
            previewApi += '&no_html=1&tmpl=component&pageid=' + ( constants.get('pageid') || '' ); 
        }

        return {
            config: {
                maxFiles: num_photo_per_upload,
                maxFilesize: maxuploadsize,
                dropAreaText: language('photo.drop_to_upload'),
                uploadAreaText: language('photo.upload_button'),
                previewApi: previewApi,
                fileTypes: ['png', 'jpg', 'jpeg'],
                createImageThumbnails: true,
                batch_notice: language('photo.batch_notice'),
                max_upload_size_error: language('photo.max_upload_size_error'),
                file_type_not_permitted: language('file.file_type_not_permitted'),
            },
            composer: false,
            moodPicker: false,
        }
    },

    computed: {
        albums() {
            return this.$store.state.photo.albums;
        },

        albumId: {
            get() {
                return this.$store.state.photo.attachment.album_id;
            },

            set(value) {
                this.$store.commit('photo/setAlbum', value);
            },
        }
    },

    methods: {
        setMood(mood) {
            this.$store.commit('photo/setMood', mood);
        },

        reset() {
            this.$refs.composer.reset();
            this.$refs.uploader.reset();

            this.$store.commit('photo/reset');
            this.$store.commit('setFree', true);
        },

        validate() {
            const limit = constants.get('conf.limitphoto');
            const uploaded = constants.get('conf.uploadedphoto');
            const photos = this.$store.state.photo.attachment.id;

            if (photos.length > limit - uploaded) {
                return alert(language('photo.upload_limit_exceeded'));
            }

            this.post();
        },

        post() {
            const DATA = Joomla.getOptions('com_community');
            const filterParams = DATA.stream_filter_params ? JSON.stringify(DATA.stream_filter_params) : '';

            const state = this.$store.state.photo;
            const content = state.content;
            const attachments = JSON.stringify(state.attachment);
            const rawData = [content, attachments, filterParams];

            this.$store.dispatch('post', rawData).then(() => {
                this.reset();
            });
        },

        showMoodPicker() {
            this.moodPicker = true;
        },

        openUploader() {
            this.$refs.uploader.open();
        },

        hideMoodPicker() {
            this.moodPicker = false;
        },

        onFilesChange(files) {
            this.composer = !!files.length;

            this.$store.commit('setFree', !files.length);
            this.$store.commit('photo/setPhoto', files);

            if (!files.length) {
                this.$store.commit('photo/reset');
            }
        },

        setAlbum(album_id) {
            this.$store.commit('photo/setAlbum', album_id);
        },
    }
}
</script>
