import Vue from 'vue';
import {constants} from '../../../utils/constants';


const {target, element, filter} = constants.get('postbox.attachment');
const raw = {
    element,
    target,
    filter,
    type: 'poll',
    options: ['', '',],
    settings: {
        allow_multiple: false,
    },
    polltime: {
        enddate: [],
        endtime: [],
    },
    privacy: constants.get('conf.profiledefaultprivacy'),
    catid: ''
};

export default {
    namespaced: true,

    state: {
        content: '',
        attachment: JSON.parse(JSON.stringify(raw)),
    },

    mutations: {
        setContent(state, value) {
            state.content = value;
        },

        updateOption({attachment}, {index, value}) {
            attachment.options[index] = value;
        },

        addOption({attachment}) {
            attachment.options.push('');
        },

        removeOption({attachment}, index) {
            attachment.options.splice(index, 1);
        },

        setMultiple({attachment}, value) {
            attachment.settings.allow_multiple = value;
        },

        setCategory({attachment}, value) {
            attachment.catid = value;
        },

        setExpiryDate({attachment}, date) {
            Vue.set(attachment.polltime, 'enddate', date);
        },

        setExpiryTime({attachment}, time) {
            Vue.set(attachment.polltime, 'endtime', time);
        },

        setPrivacy({attachment}, value) {
            attachment.privacy = value;
        },

        reset(state) {
            state.content = '';
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    },
}