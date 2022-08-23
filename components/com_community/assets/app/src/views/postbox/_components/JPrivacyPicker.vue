<template>
    <div class="joms-postbox-dropdown privacy-dropdown">
        <ul class="joms-list">
            <li v-for="privacy in privacies" :key="privacy.name" @click="setPrivacy(privacy.value)">
                <p class="reset-gap">
                    <svg viewBox="0 0 16 18" class="joms-icon">
                        <use :href="privacy.icon_url" />
                    </svg>
                    {{ privacy.title }}
                </p>
                <span>{{ privacy.desc }}</span>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        privacies: {
            type: Array,
            default() {
                return JSON.parse(JSON.stringify(this.$store.state.privacies));
            },
        }
    },

    methods: {
        setPrivacy(value) {
            this.$emit('setPrivacy', value);
            this.$emit('hidePrivacy');
        },
    }
}
</script>
