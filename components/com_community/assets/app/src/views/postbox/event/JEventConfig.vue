<template>
    <div class="joms-postbox-dropdown event-dropdown">
        <div class="event-row">
            <div class="event-time-column">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox private-event" name="permission" v-model="inviteOnly">
                    <span :title="eventPrivateTipsText">
                        {{eventPrivateText}}
                    </span>
                </label>
            </div>
        </div>
        <div class="event-row">
            <div class="event-time-column">
                <span>{{categoryText}}<span style="color:red">*</span></span>
                <div class="joms-select--wrapper">
                    <select class="joms-event-category joms-select" style="width:100%" v-model="catid">
                        <option disabled value="">{{selectCategoryText}}</option>
                        <option v-for="category in categories" :value="category.id" :key="category.id">
                            {{category.name}}
                        </option>
                    </select>
                </div>
            </div>
            <div class="event-time-column">
                <span>{{locationLabel}}<span style="color:red">*</span></span>
                <input 
                    type="text" 
                    class="joms-input" 
                    :placeholder="locationHint"
                    v-model="location" />
            </div>
        </div>
        <div class="event-row">
            <span>{{startText}}<span style="color:red">*</span></span>
            <VueDateTime 
                use12-hour
                type="datetime" 
                input-id="joms-postbox-event-start-date"
                input-class="joms-input"
                v-model="startDate"
                :min-datetime="now"
                :placeholder="startDateText" />
        </div>
        <div class="event-row">
            <span>{{endText}}<span style="color:red">*</span></span>
            <VueDateTime 
                use12-hour
                type="datetime" 
                input-id="joms-postbox-event-end-date"
                input-class="joms-input"
                v-model="endDate"
                :min-datetime="now"
                :placeholder="endDateText" />
        </div>
        <div class="joms-postbox-action event-action">
            <button class="joms-button--primary joms-button--smallest" @click="save">{{doneText}}</button>
        </div>
    </div>
</template>

<script>
import 'vue-datetime/dist/vue-datetime.css';

import { Datetime as VueDateTime } from 'vue-datetime';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    components: {
        VueDateTime,
    },

    data() {
        return {
            categories: constants.get('eventCategories'),
            catid: '',
            location: '',
            startDate: '',
            endDate: '',
            now: (new Date).toISOString(),
            inviteOnly: false,
            categoryText: language('event.category'),
            selectCategoryText: language('select_category'),
            eventPrivateText: language('event.private'),
            eventPrivateTipsText: language('event.private_tips'),
            locationLabel: language('event.location'),
            locationHint: language('event.location_hint'),
            startText: language('event.start'),
            startDateText: language('event.start_date_hint'),
            endText: language('event.end'),
            endDateText: language('event.end_date_hint'),
            doneText: language('event.done_button'),
        }
    },

    methods: {
        save() {
            if (!this.catid) {
                return alert(language('event.category_not_selected'));
            }

            if (!this.location) {
                return alert(language('event.location_not_selected'));
            }

            if (!this.startDate) {
                return alert(language('event.start_date_not_selected'));
            }

            if (!this.endDate) {
                return alert(language('event.end_date_not_selected'));
            }

            if (this.endDate <= this.startDate) {
                return alert(language('event.end_date_too_early'));
            }

            const {inviteOnly, catid, location, startDate, endDate} = this;
            this.$emit('save', {
                inviteOnly,
                catid,
                location,
                startDate,
                endDate,
            });
        },

        reset() {

        },
    }
}
</script>

<style lang="scss">
.event-dropdown {
    .event-row {
        clear: both;
    }
}
</style>
