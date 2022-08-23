<template>
    <div class="j-emoji-board">
        <Picker 
            set="twitter" 
            :infiniteScroll="false" 
            :showPreview="false"
            :emojiTooltip="true"
            :useEmojiClass="false"
            @select="selectEmoji" />
    </div>
</template>

<script>
import { Picker } from './emoji-mart-vue';

export default {
    components: {
        Picker,
    },

    methods: {
        selectEmoji(emoji) {
            this.$emit('selectEmoji', emoji);
        },
    }
}
</script>

<style lang="scss">
.j-emoji-board {
    position: absolute;
    top: 5px;
    right: -10px;
    z-index: 1;

    .joms-emo2 {
        width: 24px;
        height: 24px;
    }

    .emoji-mart-category-label {
        display: none;
    }
}
</style>