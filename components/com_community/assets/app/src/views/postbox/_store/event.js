import Vue from 'vue';
import assign from 'lodash/assign';
import {constants} from '../../../utils/constants';

const {target, element, filter} = constants.get('postbox.attachment');

const raw = {
    element,
    target,
    filter,
    type: "event",
    event: {
        private: false,
    },
    title: "",
    catid: "",
    location: "",
    startdate: "",
    enddate: "",
    allday: false,
    "starttime-hour": "",
    "starttime-min": "",
    "endtime-hour": "",
    "endtime-min": "",
};

export default {
    namespaced: true,

    state: {
        content: '',
        attachment: JSON.parse(JSON.stringify(raw)),
    },

    mutations: {
        setAttachment(state, data) {
            const details = assign(JSON.parse(JSON.stringify(state.attachment)), data);
            Vue.set(state, 'attachment', details);
        },

        setTitle({attachment}, value) {
            attachment.title = value;
        },

        setContent(state, content) {
            state.content = content;
        },

        reset(state) {
            state.content = '',
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    }
}