<template>
    <div class="joms-postbox-dropdown mood-dropdown">
        <ul class="joms-postbox-emoticon joms-list clearfix">
            <li v-for="mood in moods" :key="mood.id" @click="setMood(mood.id)">
                <div :title="mood.description">
                    <img class="joms-emoticon" :src="mood.image" />
                    <span>{{mood.title}}</span>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import {constants} from '../../../utils/constants';

export default {
    data() {
        return {
            moods: constants.get('moods'),
        }
    },

    methods: {
        setMood(mood) {
            this.$emit('setMood', mood);
            this.$emit('hideMoodPicker');
        }
    },
}
</script>

<style>

</style>