<template>
    <div class="joms-postbox-fetched" v-if="data.title">
        <div class="joms-postbox-inner-panel">
            <div style="position: relative;">
                <div class="joms-fetched-container" v-if="data.title">
                    <div class="joms-fetched-images" v-if="data.image">
                        <img :src="data.image" style="width: 100%; height: 100%;">
                    </div>
                    <div class="joms-fetched-field joms-fetched-title">
                        <span style="font-weight: bold; display: inline;">{{data.title}}</span>
                    </div>
                    <div class="joms-fetched-field joms-fetched-description" v-if="data.desc">
                        <span style="display: inline;">{{data.desc}}</span>
                    </div>
                </div>
                <div class="clearfix"></div>
                <span class="joms-fetched-close" style="top:0" @click="removePreview">
                    <i class="joms-icon-remove"></i>cancel
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Object,
            default() {
                return {
                    url: '',
                    title: '',
                    desc: '',
                    image: '',
                }
            }
        }
    },

    methods: {
        removePreview() {
            this.$emit('removePreview');
        },
    }
}
</script>

<style lang="scss">
.joms-postbox-fetched {
    .j-fetching {
        text-align: center;
    }

    .joms-fetched-container {
        position: relative;
    }
}
</style>