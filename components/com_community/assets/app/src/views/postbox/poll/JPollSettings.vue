<template>
    <div class="settings-item">
        <input id="joms-allow-multiple-checkbox" type="checkbox" v-model="allowMultiple">
        <label style="cursor:pointer" class="settings-item--label" for="joms-allow-multiple-checkbox">
            {{allowMultiChoicesText}}
        </label>
    </div>
</template>

<script>
import language from '../../../utils/language';
export default {
    data() {
        return {
            allowMultiChoicesText: language('poll.allow_multiple_choices'),
        }
    },
    computed: {
        allowMultiple: {
            get() {
                return this.$store.state.poll.attachment.settings.allow_multiple;
            },

            set(value) {
                this.$store.commit('poll/setMultiple', value);
            },
        },
    }
}
</script>

<style lang="scss">
.settings-item {
    .settings-item--label {
        display: inline;
    }
}
</style>