<template>
    <span class="j-location-display" v-if="location">
        - {{language('at')}}
        <b>{{location}}</b>
    </span>
</template>

<script>
import language from '../../../utils/language';
export default {
    props: {
        location: {
            type: String,
            default: '',
        }
    },

    methods: {
        language(str) {
            return language(str);
        }
    }
}
</script>
