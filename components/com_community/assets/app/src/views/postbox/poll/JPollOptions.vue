<template>
    <div class="joms-postbox-poll-options">
        <div class="joms-postbox__poll-option-list">
            <div class="joms-postbox__poll-option" v-for="(option, idx) in options" :key="idx">
                <input 
                    class="input-option poll_option"
                    type="text" 
                    :placeholder="pollOptionHint" 
                    maxlength="100"
                    :value="option"
                    @input="event => updateOption(idx, event.target.value)">
                <a href="javascript:;" 
                    class="joms-postbox-poll__remove-option"
                    @click="removeOption(idx)">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use :href="currentUrl + '#joms-icon-close'"></use>
                    </svg>
                </a>
            </div>
        </div>
        <a href="javascript:;" 
            class="joms-postbox-poll__add-option"
            @click="addOption">{{addOptionText}}</a>
    </div>
</template>

<script>
import language from '../../../utils/language';
export default {
    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;

        return {
            currentUrl,
            pollOptionHint: language('poll.hint_add_option'),
            addOptionText: language('poll.add_option')

        };
    },

    computed: {
        options() {
            return this.$store.state.poll.attachment.options;
        },
    },

    methods: {
        updateOption(index, value) {
            this.$store.commit('poll/updateOption', {index, value});
        },

        addOption() {
            this.$store.commit('poll/addOption');

            setTimeout(() => {
                const $list = this.$el.querySelector('.joms-postbox__poll-option-list');
                const $last = $list.lastChild;

                $last.querySelector('input').focus();
            });
        },

        removeOption(idx) {
            this.$store.commit('poll/removeOption', idx);
        },
    }
}
</script>
