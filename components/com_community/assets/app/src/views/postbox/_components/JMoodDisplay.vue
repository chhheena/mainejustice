<template>
    <span class="j-mood-display" v-if="mood && mood.id">
        - <img class="joms-emoticon" :title="mood.title" :src="mood.image"> <b>{{mood.description}}</b>
    </span>
</template>

<script>
import {constants} from '../../../utils/constants';

export default {
    props: {
        moodId: {
            type: String,
            default: '0',
        },  
    },

    computed: {
        mood() {
            const { enablemood } = constants.get('conf');

            if (!enablemood) {
                return;
            }

            const moods = JSON.parse(JSON.stringify(constants.get('moods')));

            return moods.find(mood => {
                return mood.id === this.moodId;
            });
        }
    }
}
</script>
