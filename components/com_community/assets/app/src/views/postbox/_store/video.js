import Vue from 'vue';
import {constants} from '../../../utils/constants';

const {target, element, filter} = constants.get('postbox.attachment');
const raw = {
    element,
    target,
    filter,
    type: 'video',
    privacy: constants.get('conf.profiledefaultprivacy'),
    mood: '',
    fetch: [],
    location: [],
};

export default {
    namespaced: true,

    state: {
        catid: '0',
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

        setPreview({attachment}, data) {
            const content = data ? [data.id, data.url, data.image, data.title, data.desc, data.catid] : [];
            Vue.set(attachment, 'fetch', content);
        },

        setCategory(state, value) {
            state.catid = value;

            if (state.attachment.fetch[5]) {
                return state.attachment.fetch[5] = value;
            }
        },

        setLocation({attachment}, data) {
            const location = data ? [data.name, data.lat, data.lon] : [];
            Vue.set(attachment, 'location', location);
        },

        reset(state) {
            state.content = '';
            state.catid = '0';
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    },
}