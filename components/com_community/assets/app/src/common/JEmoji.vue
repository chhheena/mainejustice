<template>
    <div class="j-emoji">
        <img class="j-emoji-icon" :src="assets_url + 'mood_19.png'" @click="toggleBoard" >
        <div class="j-emoji-content">
            <keep-alive>
                <JEmojiBoard 
                    v-if="showBoard" 
                    v-click-outside="hideBoard"
                    @selectEmoji="selectEmoji" />
            </keep-alive>
        </div>
    </div>
</template>

<script>
import debounce from 'lodash/debounce';

export default {
    data() {
        const assets_url = Joomla.getOptions('com_community').assets_url;

        return {
            assets_url,
            showBoard: false,
        }
    },

    components: {
        JEmojiBoard: () => import(/* webpackChunkName: 'emoji' */ './JEmojiBoard.vue'),
    },

    methods: {
        toggleBoard: debounce(function() {
            this.showBoard = !this.showBoard;
        }, 100),

        hideBoard(event) {
            const targetClass = event.target.getAttribute("class");
            const listClass = targetClass ? targetClass.split(' ') : [];

            if (listClass.indexOf('j-emoji-icon') > -1) {
                return;
            }

            this.showBoard = false;
        },

        selectEmoji(emoji) {
            this.$emit('selectEmoji', emoji);
        },
    }
}
</script>

<style lang="scss">
.j-emoji {
    .j-emoji-icon {
        display: block;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        box-shadow: 0px 0px 1px 2px rgba(204, 204, 204, 0.42);
        cursor: pointer;
    }

    .j-emoji-content {
        position: relative;
    }
}
</style>