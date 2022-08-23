<template>
    <div class="joms-postbox-custom-wrapper">
        <div class="joms-postbox-inner-panel" v-if="!type">
            <div class="joms-postbox-double-panel">
                <ul class="joms-list clearfix">
                    <li class="joms-postbox-predefined-message" @click="setType('predefined')">
                        <svg viewBox="0 0 16 18" class="joms-icon">
                            <use :href="currentUrl + '#joms-icon-cog'" class="joms-icon--svg-fixed joms-icon--svg-unmodified"></use>
                        </svg>
                         {{predefinedButtonText}}
                    </li>
                    <li class="joms-postbox-custom-message" @click="setType('custom')">
                        <svg viewBox="0 0 16 18" class="joms-icon">
                            <use :href="currentUrl + '#joms-icon-pencil'" class="joms-icon--svg-fixed joms-icon--svg-unmodified joms-icon--svg-unmodified"></use>
                        </svg>
                         {{customButtonText}}
                    </li>
                </ul>
            </div>
        </div>
        <div class="joms-postbox-custom" v-if="type">
            <div class="joms-postbox-custom-state-predefined" v-if="type === 'predefined'">
                <div class="joms-postbox-inner-panel">
                    <span>{{predefinedLabel}}</span>
                    <br>
                    <select 
                        style="width: 100%" 
                        v-model="activity"
                        @change="setActivity">
                        <option 
                            v-for="act in activities" 
                            :key="act.value" 
                            :value="act.value">
                            {{act.text}}
                        </option>
                    </select>
                </div>
            </div>
            <div class="joms-postbox-custom-state-custom" v-if="type === 'custom'">
                <div class="joms-postbox-inner-panel">
                    <span>{{customLabel}}</span>
                    <br>
                    <textarea-autosize
                        class="j-textarea"
                        v-model="customMessage"
                        :max-height="350"
                        @input.native="onInputCustom" />
                </div>
            </div>
        </div>
        <JCustomMiniBar 
            v-show="!$store.state.free"
            @showPrivacy="showPrivacy"
            @reset="reset"
            @validate="validate" />
        <keep-alive>
            <JPrivacyPicker
                v-if="privacy" 
                v-click-outside="hidePrivacy" 
                :privacies="privacies"
                @hidePrivacy="hidePrivacy"
                @setPrivacy="setPrivacy"/>
        </keep-alive>
    </div>
</template>

<script>
import Vue from 'vue';
import VueTextareaAutosize from "vue-textarea-autosize";
import JCustomMiniBar from "./JCustomMiniBar.vue";
import JPrivacyPicker from '../_components/JPrivacyPicker.vue';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

Vue.use(VueTextareaAutosize);

export default {
    components: {
        JCustomMiniBar,
        JPrivacyPicker,
    },

    data() {
        const DATA = Joomla.getOptions('com_community');
        const currentUrl = DATA.current_url;
        const activities = [];
        const customActivities = constants.get('customActivities');

        for (const key in customActivities) {
            activities.push({
                text: customActivities[key],
                value: key,
            });
        }

        return {
            type: '',
            currentUrl,
            activities,
            activity: '',
            customMessage: '',
            privacy: false,
            predefinedButtonText: language('custom.predefined_button'),
            customButtonText: language('custom.custom_button'),
            predefinedLabel: language('custom.predefined_label'),
            customLabel: language('custom.custom_label'),
        };
    },

    computed: {
        privacies() {
            const data = JSON.parse(JSON.stringify(this.$store.state.privacies));
            return data.filter(item => item.name === 'public' || item.name === 'site_members');
        },
    },

    methods: {
        validate() {
            this.post();
        },

        post() {
            const state = this.$store.state.custom;
            const {type, content, privacy} = state;
            const rawData = [type, content, privacy];

            this.$store.dispatch('postAdminAnnouncement', rawData).then(() => {
                this.reset();
            });
        },
    
        setActivity() {
            const currentActivity = this.activities.find(act => act.value === this.activity);

            if (currentActivity) {
                this.$store.commit('custom/setContent', currentActivity.text);
            }
            this.$store.commit('custom/setType', this.activity);
        },

        onInputCustom(event) {
            this.$store.commit('custom/setContent', event.target.value);
        },

        setType(type) {
            this.type = type;
            if (type === 'custom') {
                this.$store.commit('custom/setType', 'system.message');
            } else if(this.activity) {
                this.$store.commit('custom/setType', this.activity);
            }

            this.$store.commit('setFree', false);
        },

        showPrivacy() {
            this.privacy = true;
        },

        hidePrivacy() {
            this.privacy = false;
        },

        setPrivacy(privacy) {
            this.$store.commit('custom/setPrivacy', privacy);
        },

        reset() {
            this.type = '';
            this.activity = '';
            this.customMessage = '';
            this.$store.commit('custom/reset');
            this.$store.commit('setFree', true);
        },
    },
}
</script>
