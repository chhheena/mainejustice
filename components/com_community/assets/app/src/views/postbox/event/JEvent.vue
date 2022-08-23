<template>
    <div class="joms-postbox-event">
        <div class="event-title-input">
            <input 
                type="text" 
                :placeholder="placeholderEventTitle"
                v-model="title"
                @focus="onEventInputFocus" />
        </div>
        <JEventPreview v-if="$store.state.event.attachment.catid"/>
        <JEventComposer ref="composer" />
        <JEventMiniBar 
            v-show="!$store.state.free"
            @showEventConfig="showEventConfig"
            @reset="reset"
            @validate="validate" />
        <keep-alive>
            <JEventConfig 
                v-if="config"
                v-click-outside="hideEventConfig"
                @save="saveEventConfig" />
        </keep-alive>
    </div>
</template>

<script>
import JEventComposer from './JEventComposer.vue';
import JEventMiniBar from './JEventMiniBar.vue';
import JEventConfig from './JEventConfig.vue';
import JEventPreview from './JEventPreview.vue';
import {DateTime} from 'luxon';
import Vue from 'vue';
import language from '../../../utils/language';

export default {
    components: {
        JEventComposer,
        JEventMiniBar,
        JEventConfig,
        JEventPreview,
    },

    data() {
        return {
            config: false,
            placeholderEventTitle: language('event.title_hint')
        }
    },

    computed: {     
        title: {
            get() {
                return this.$store.state.event.attachment.title;
            },

            set(value) {
                this.$store.commit('event/setTitle', value);
            },
        },
    },

    methods: {
        validate() {
            this.post();
        },

        post() {
            const DATA = Joomla.getOptions('com_community');
            const filterParams = DATA.stream_filter_params ? JSON.stringify(DATA.stream_filter_params) : '';

            const state = this.$store.state.event;
            const content = state.content;
            const attachments = JSON.stringify(state.attachment);
            const rawData = [content, attachments, filterParams];

            this.$store.dispatch('post', rawData).then(res => {
                if (res[0] && res[0][1] === "ajax_calls" && res[1] && res[1][1] ) {
                    if (res[1][1] === '__throwError') {
                        return;
                    } else {
                        eval(res[1][1]);
                    }
                }
                this.reset();
            });
        },

        onEventInputFocus() {
            this.$store.commit('setFree', false);
        },

        showEventConfig() {
            this.config = true;
        },

        hideEventConfig() {
            this.config = false;
        },

        saveEventConfig({inviteOnly, catid, location, startDate, endDate}) {
            const start = DateTime.fromISO(startDate);
            const end = DateTime.fromISO(endDate);
            const sDate = [
                start.year,
                start.month < 10 ? '0' + start.month : start.month,
                start.day < 10 ? '0' + start.day : start.day,
            ].join('-');

            const eDate = [
                end.year,
                end.month < 10 ? '0' + end.month : start.month,
                end.day < 10 ? '0' + end.day : end.day,
            ].join('-');

            const details = {
                allday: false,
                event: {
                    private: inviteOnly,
                },
                catid,
                location,
                startdate: sDate,
                "starttime-hour": String(start.hour < 10 ? '0' + start.hour : start.hour),
                "starttime-min": String(start.minute < 10 ? '0' + start.minute : start.minute),
                enddate: eDate,
                "endtime-hour": String(end.hour < 10 ? '0' + end.hour : end.hour),
                "endtime-min": String(end.minute < 10 ? '0' + end.minute : end.minute),
            };

            this.$store.commit('event/setAttachment', details);
            this.hideEventConfig();
        },

        reset() {
            this.$refs.composer.reset();

            this.$store.commit('event/reset');
            this.$store.commit('setFree', true);
        },
    },
}
</script>

<style lang="scss">
.joms-postbox-event {
    .event-title-input {
        padding: 10px 13px;
        border-bottom: solid 1px #f5f5f5;

        input {
            width: 100%;
            padding: 0;
            margin: 0;
            border: none;
            box-shadow: none;

            &:focus {
                box-shadow: none;
            }
        }
    }

    .ql-editor.ql-blank::before {
        font-style: unset;
    }
}
</style>
