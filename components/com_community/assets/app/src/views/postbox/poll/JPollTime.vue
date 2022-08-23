<template>
    <div class="joms-postbox__poll-time">
        <div class="joms-postbox__poll-time--inner">
            <span>{{expiryDateText}} <span style="color:red">*</span></span>
            <VueDateTime 
                type="date" 
                input-id="joms-postbox-poll-expiry-date"
                input-class="joms-input"
                v-model="date"
                :min-datetime="now"
                :placeholder="expiryDateHintText"
                @input="setExpiryDate" />
        </div>
        <div class="joms-postbox__poll-time--inner">
            <span>{{expiryTimeText}} <span style="color:red">*</span></span>
            <VueDateTime 
                use12-hour
                type="time" 
                input-id="joms-postbox-poll-expiry-time"
                input-class="joms-input"
                v-model="time"
                :format="timeFormat"
                :placeholder="expiryTimeHintText"
                @input="setExpiryTime" />
        </div>
    </div>
</template>

<script>
import 'vue-datetime/dist/vue-datetime.css';

import { Datetime as VueDateTime } from 'vue-datetime';
import {DateTime} from 'luxon';
import language from '../../../utils/language';

export default {
    components: {
        VueDateTime,
    },

    data() {
        const now = (new Date).toISOString();

        return {
            now,
            timeFormat: DateTime.TIME_SIMPLE,
            date: '',
            time: '',
            expiryDateText: language('poll.expired_date'),
            expiryDateHintText: language('poll.expired_date_hint'),
            expiryTimeText: language('poll.expired_time'),
            expiryTimeHintText: language('poll.expired_time_hint')
        };
    },

    methods: {
        setExpiryDate(value) {
            if (!value) {
                return;
            }

            const data = DateTime.fromISO(value);
            const ISODate = data.toISODate()
            const fullDate = data.toFormat('dd MMMM yyyy');
            
            this.$store.commit('poll/setExpiryDate', [ISODate, fullDate]);
        },

        setExpiryTime(value) {
            if (!value) {
                return;
            }

            const data = DateTime.fromISO(value);
            const time = data.toLocaleString(DateTime.TIME_SIMPLE);
            const time24 = data.toLocaleString(DateTime.TIME_24_SIMPLE);

            this.$store.commit('poll/setExpiryTime', [time24, time]);
        },

        reset() {
            this.date = '';
            this.time = '';
        },
    }
}
</script>

<style>

</style>