<template>
    <div class="joms-postbox-charcount" :class="overClass">{{num}}</div>
</template>

<script>
export default {
    props: {
        num: Number,
    },

    computed: {
        overClass() {
            return {
                over: this.num < 0 ? true : false,
            }
        }
    }
}
</script>

<style lang="scss">
.joms-postbox-charcount {
    font-size: 10px;
    background-color: rgba(245, 245, 245, 0.61);
    border-bottom-left-radius: 5px;

    &.over {
        font-weight: bold;
        color: #E91E63;
    }
}
</style>