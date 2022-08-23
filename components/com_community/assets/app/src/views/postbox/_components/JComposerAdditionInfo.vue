<template>
    <div class="j-composer-addition-info">
        <JMoodDisplay :moodId="moodId" />
        <JLocationDisplay :location="location" />
    </div>
</template>

<script>
import JLocationDisplay from '../_components/JLocationDisplay.vue';
import JMoodDisplay from '../_components/JMoodDisplay.vue';

export default {
    components: {
        JLocationDisplay,
        JMoodDisplay,
    },

    props: {
        moodId: {
            type: String,
            default: '',
        },

        location: {
            type: String,
            default: '',
        }
    }
}
</script>

<style lang="scss">
.j-composer-addition-info {
    padding-left: 13px;
    padding-right: 13px;
    line-height: 23px;
}
</style>