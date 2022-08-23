<template>
    <div class="joms-postbox-action">
        <button class="joms-postbox-cancel" @click="reset">{{cancel}}</button>
        <button class="joms-postbox-save"
            v-if="isPostable"
            @click="validate">{{save}}</button>
    </div>
</template>

<script>
import language from '../../../utils/language';

export default {
    props: {
        isPostable: {
            type: Boolean,
            default: false,
        },
        cancel: {
            type: String,
            default: language('postbox.cancel_button')
        },
        save: {
            type: String,
            default: language('postbox.post_button'),
        },
    },

    methods: {
        validate() {
            this.$emit('validate');
        },

        reset() {
            this.$emit('reset');
        },
    }
}
</script>
