<template>
    <div class="joms-postbox-file-wrapper joms-postbox-gif-wrapper">
        <j-uploader 
            ref="uploader"
            :config="config" 
            @filesChange="onFilesChange">
            <template v-slot:composer>
                <keep-alive v-if="composer">
                    <JPhotoComposer ref="composer" />
                </keep-alive>
            </template>
        </j-uploader>
        <JPhotoMiniBar 
            v-show="!$store.state.free"
            :addmore="false"
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
import language from '../../../utils/language';
import JPhoto from './JPhoto.vue';

export default {
    extends: JPhoto,

    data() {
        const baseUrl = Joomla.getOptions('com_community').base_url;

        return {
            config: {
                maxFiles: 1,
                dropAreaText: language('photo.drop_to_upload'),
                uploadAreaText: language('photo.gif_upload_button'),
                previewApi: baseUrl + 'index.php?option=com_community&view=photos&task=ajaxPreview&gifanimation=1',
                fileTypes: ['gif'],
                createImageThumbnails: true,
            },
            composer: false,
            moodPicker: false,
        }
    },
}
</script>

<style lang="scss">
.joms-postbox-gif-wrapper {
    .joms-postbox-preview-inner {
        display: flex;
        justify-content: center;
    }
}
</style>