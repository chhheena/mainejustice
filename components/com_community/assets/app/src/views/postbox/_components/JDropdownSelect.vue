<template>
    <div class="joms-postbox-dropdown-select">
        <span v-if="current" @click="toggleDropdown">{{current.text}}</span>
        <span v-else @click="toggleDropdown">{{placeholder}}</span>
        <ul class="joms-list" v-show="showDropdown">
            <li v-for="option in options" :key="option.value" @click="setValue(option)">
                <p style="margin:0">{{option.text}}</p>
                <small>{{option.sub}}</small>
            </li> 
        </ul>
        <svg viewBox="0 0 16 18" class="joms-icon">
            <use :href="currentUrl + '#joms-icon-arrow-down'"></use>
        </svg>
    </div>
</template>

<script>
export default {
    props: {
        options: Array,
        value: String,
        placeholder: String,
    },

    data() {
        const currentUrl = Joomla.getOptions('com_community').current_url;

        return {
            currentUrl,
            showDropdown: false,
        }
    },

    computed: {
        current() {
            return this.options.find(option => {
                return this.value === option.value;
            });
        }
    },

    methods: {
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },

        setValue(option) {
            this.toggleDropdown();

            if (this.current && this.current.value === option.value) {
                return;
            }
            
            this.$emit('input', option.value);
        },
    }
}
</script>

<style lang="scss">
.joms-postbox-dropdown-select {
    min-height: 28px;
    margin: 0;
    padding: 0 !important;
    background-color: rgba(0, 0, 0, .05);
    cursor: pointer;
    position: relative;
    user-select: none;

    span {
        display: block;
        padding: 4px 8px;
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 20px;
    }

    svg {
        position: absolute;
        top: 8px;
        right: 13px;
    }

    .joms-list {
        border: 1px solid #eaeaea;
        position: absolute;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        width: 100%;
        z-index: 10;
        background-color: #ffffff;
        padding: 4px;
        -webkit-box-shadow: inset 0px 2px 1px rgba(0, 0, 0, 0.06);
        -moz-box-shadow: inset 0px 2px 1px rgba(0, 0, 0, 0.06);
        box-shadow: inset 0px 2px 1px rgba(0, 0, 0, 0.06);

        li {
            padding: 4px 8px;
            color: #7f8c8d;

            &:hover {
                background-color: #f9f9f9;
            }
        }
    }
}
</style>
