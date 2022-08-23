<template>
    <div class="joms-postbox-poll">
        <div class="joms-postbox-poll-inner">
            <input 
                class="poll-title"
                type="text" 
                :placeholder="pollTitleHint"
                v-model="title"
                @focus="onFocus" >
        </div>
        <div class="joms-postbox-poll-inner">
            <JPollOptions />
            <JPollSettings />
            <JDropdownSelect 
                class="joms-postbox-poll-category" 
                v-model="categoryId"
                :options="categories"
                :placeholder="selectCategoryTitle" />
            <JPollTime ref="timer" />
        </div>
        <JPollMiniBar 
            v-show="!$store.state.free"
            @showPrivacy="showPrivacy"
            @reset="reset"
            @validate="validate" />
        
        <keep-alive>
            <JPrivacyPicker
                v-if="privacy" 
                v-click-outside="hidePrivacy" 
                @hidePrivacy="hidePrivacy"
                @setPrivacy="setPrivacy"/>
        </keep-alive>
    </div>
</template>

<script>
import JPollOptions from './JPollOptions.vue';
import JPollSettings from './JPollSettings.vue';
import JPollTime from './JPollTime.vue';
import JPollMiniBar from './JPollMiniBar.vue';
import JDropdownSelect from "../_components/JDropdownSelect.vue";
import JPrivacyPicker from '../_components/JPrivacyPicker.vue';
import sortCategories from '../../../utils/sort-categories';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    components: {
        JPollOptions,
        JPollSettings,
        JDropdownSelect,
        JPollTime,
        JPollMiniBar,
        JPrivacyPicker,
    },

    data() {
        const sorted = sortCategories(constants.get('pollCategories'));
        const categories = sorted.map(item => {
            return {
                value: item.id,
                text: 'Category: ' + item.name,
            }
        });

        return {
            categories,
            privacy: false,
            pollTitleHint: language('poll.title_hint'),
            selectCategoryTitle: language('select_category'),
        };
    },

    computed: {
        title: {
            get() {
                return this.$store.state.poll.content;
            },

            set(value) {
                this.$store.commit('poll/setContent', value);
            },
        },

        categoryId: {
            get() {
                return this.$store.state.poll.attachment.catid;
            },

            set(value) {
                this.$store.commit('poll/setCategory', value);
            },
        },
    },

    methods: {
        validate() {
            const {poll} = this.$store.state;
            const {content, attachment} = poll;
            
            if (attachment.options.length < 2) {
                return alert('must have at least 2 options');
            }

            const hasEmptyOption = attachment.options.some(value => !value.trim().length);
            if (hasEmptyOption) {
                return alert('option must not empty');
            }

            if (!attachment.catid) {
                return alert('missing category');
            }

            if (!attachment.polltime.enddate.length) {
                return alert('missing date');
            }

            if (!attachment.polltime.endtime.length) {
                return alert('missing time');
            }

            this.post();
        },

        post() {
            const DATA = Joomla.getOptions('com_community');
            const filterParams = DATA.stream_filter_params ? JSON.stringify(DATA.stream_filter_params) : '';

            const state = this.$store.state.poll;
            const content = state.content;
            const attachments = JSON.stringify(state.attachment);
            const rawData = [content, attachments, filterParams];

            this.$store.dispatch('post', rawData).then(res => {
                if (res[0] && res[0][1] === "ajax_calls" && res[1] && res[1][1] ) {
                    if (res[1][1] === '__throwError') {
                        return; 
                    }
                }
                this.reset();
            });
        },

        onFocus() {
            this.$store.commit('setFree', false);
        },

        showPrivacy() {
            this.privacy = true;
        },

        hidePrivacy() {
            this.privacy = false;
        },

        setPrivacy(privacy) {
            this.$store.commit('poll/setPrivacy', privacy);
        },

        reset() {
            this.$refs.timer.reset();
            this.$store.commit('poll/reset');
            this.$store.commit('setFree', true);
        },
    }
}
</script>

<style lang="scss">
.joms-postbox-poll {
    .joms-postbox-poll-inner {
        padding: 10px 13px;
        border-bottom: solid 1px #f5f5f5;
    }
    
    input.poll-title {
        width: 100%;
        padding: 0;
        margin: 0;
        border: none;
        box-shadow: none;

        &:focus {
            box-shadow: none;
        }
    }

    .joms-postbox-poll-category {
        margin-top: 15px;
        margin-bottom: 15px;
    }
}
</style>
