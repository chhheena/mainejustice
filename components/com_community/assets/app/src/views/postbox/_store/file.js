import Vue from 'vue';
import {constants} from '../../../utils/constants';
const {target, element, filter} = constants.get('postbox.attachment');

const raw = {
    element,
    target,
    filter,
    type: 'file',
    privacy: constants.get('conf.profiledefaultprivacy'),
    mood: '',
};

export default {
    namespaced: true,

    state: {
        content: '',
        attachment: JSON.parse(JSON.stringify(raw)),
    },

    mutations: {
        setPrivacy(state, value) {
            state.attachment.privacy = value;
        },
    
        setMood(state, mood) {
            state.attachment.mood = mood;
        },
    
        setContent(state, content) {
            state.content = content;
        },

        setFile({attachment}, files) {
            Vue.set(attachment, 'id', files);
        },
    
        reset(state) {
            state.content = '',
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    }
}