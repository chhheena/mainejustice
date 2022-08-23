<template>
    <div class="joms-postbox-event-detail">
        <div class="joms-postbox-event-panel joms-postbox-event-label-category">
            <div class="joms-postbox-event-title">
                <div class="joms-input-field-name">{{categoryText}}</div>
                <span class="joms-input-text">{{category.name}}</span>
            </div>
        </div>
        <div class="joms-postbox-event-panel joms-postbox-event-label-location">
            <div class="joms-postbox-event-title">
                <div class="joms-input-field-name">{{locationLabel}}</div>
                <span class="joms-input-text">{{location}}</span>
            </div>
        </div>
        <div class="joms-postbox-event-panel joms-postbox-event-label-date">
            <div class="joms-postbox-event-title">
                <div class="joms-input-field-name">{{dateAndTimeText}}</div>
                <span class="joms-input-text">{{startDate}} — {{endDate}}</span>
            </div>
        </div>
    </div>
</template>

<script>
import {DateTime} from 'luxon';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

export default {
    data() {
        return {
            categoryText: language('event.category'),
            locationLabel: language('event.location'),
            dateAndTimeText: language('event.date_and_time')
        }
    },

    computed: {
        category() {
            const categories = constants.get('eventCategories');
            const category = categories.find(cat => {
                return cat.id === this.$store.state.event.attachment.catid;
            });

            return category || {};
        },

        location() {
            return this.$store.state.event.attachment.location;
        },

        startDate() {
            const {attachment} = this.$store.state.event;
            const time = [
                attachment['starttime-hour'],
                attachment['starttime-min'],
                '00',
            ].join(':');
            
            const sqlDate = attachment['startdate'] + ' ' + time;
            const sDate = DateTime.fromSQL(sqlDate);
            return sDate.toFormat('ff');
        },

        endDate() {
            const {attachment} = this.$store.state.event;
            const time = [
                attachment['endtime-hour'],
                attachment['endtime-min'],
                '00',
            ].join(':');
            
            const sqlDate = attachment['enddate'] + ' ' + time;
            const sDate = DateTime.fromSQL(sqlDate);
            return sDate.toFormat('ff');
        },
    }
}
</script>

<style lang="scss">
.joms-postbox-event-detail {
    padding-top: 10px;
    padding-left: 13px;
    padding-right: 13px;
    border-bottom: dashed 1px #f5f5f5;
}
</style>