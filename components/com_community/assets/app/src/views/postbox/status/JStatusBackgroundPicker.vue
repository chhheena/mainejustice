<template>
    <div class="j-status-background-picker colorful-status__color">
        <span class="joms-direction joms-left" 
            v-show="showDirection"
            @click="scrollLeft">
            <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </span>
        <ul class="colorful-status__color-list">
            <li class="colorful-status__color-selector"
                v-for="bg in bgs"
                :key="bg.id"
                :title="bg.title"
                :class="bg.class"
                :style="bg.style"
                @click="setBg(bg.id)">
            </li>
        </ul>
        <span class="joms-direction joms-right"
            v-show="showDirection"
            @click="scrollRight">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
        </span>
    </div>
</template>

<script>
import animateScrollTo from 'animated-scroll-to';

export default {
    data() {
        return {
            showDirection: false,
            range: 200,
        }
    },

    mounted() {
        const $list = this.$el.querySelector('.colorful-status__color-list');

        if ($list.offsetWidth < $list.scrollWidth) {
            this.showDirection = true;
        }

        const isTouch = this.$store.state.isTouch;

        if (isTouch && window.innerWidth < 600) {
            $list.style.overflowX = 'auto';
            this.range = 100;
        }
    },

    computed: {
        bgs() {
            const bgs = JSON.parse(JSON.stringify(this.$store.state.bgs));
            const bgid = this.$store.state.status.attachment.bgid;

            bgs.map(bg => {
                bg.class = {
                    active: bg.id === bgid,
                };

                bg.style = {
                    backgroundImage: bg.thumbnail ? 'url(' + bg.thumbnail + ')' : '',
                };

                return bg;
            });

            return bgs;
        }
    },

    methods: {
        setBg(bgid) {
            this.$store.commit('status/setBg', bgid);
            this.$emit('setBg', bgid);
        },

        scrollLeft() {
            const $list = this.$el.querySelector('.colorful-status__color-list');

            animateScrollTo([$list.scrollLeft - this.range, null], {
                elementToScroll: $list,
            });
        },

        scrollRight() {
            const $list = this.$el.querySelector('.colorful-status__color-list');

            animateScrollTo([$list.scrollLeft + this.range, null], {
                elementToScroll: $list,
            });
        }
    }
}
</script>

<style lang="scss">
.colorful-status__color {
    position: relative;
    height: 25px;
    color: #333;

    span.joms-left {
        position: absolute;
        left: 0;

        i.fa {
            left: 25%;
        }
    }

    span.joms-right {
        position: absolute;
        right: 0;

        i.fa {
            right: 25%;
        }
    }

    span.joms-direction {
        height: 25px;
        width: 25px;
        border: solid 1px #ddd;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f5f5f5;
        transition: box-shadow 300ms;
        user-select: none;

        i.fa {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        &:hover {
            box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
        }
    }

    ul.colorful-status__color-list {
        white-space: nowrap;
        overflow: hidden;
        position: absolute;
        left: 30px;
        right: 30px;

        li {
            display: inline-block;
            margin-right: 5px;
            background-size: cover;
            cursor: pointer;
            border-radius: 5px;

            &.active {
                border: solid 3px #ddd;
            }
        }

        li:last-child {
            margin-right: 0;
        }
    }

    .colorful-status__color-selector {
        height: 25px;
        width: 25px;
        border: solid 2px #cecece;
        user-select: none;
    }
}
</style>