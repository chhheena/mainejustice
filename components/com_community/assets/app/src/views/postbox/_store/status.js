import Vue from 'vue';
import {constants} from '../../../utils/constants';

const {target, element, filter} = constants.get('postbox.attachment');
const raw = {
    element,
    target,
    filter,
    type: 'message',
    privacy: constants.get('conf.profiledefaultprivacy'),
    mood: '',
    colorful: false,
    bgid: '0',
    location: [],
    fetch: [],
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
    
        setBg(state, bgid) {
            const attachment = state.attachment;
            
            attachment.colorful = bgid !== '0';
            attachment.bgid = bgid;
        },

        setLocation({attachment}, data) {
            const location = data ? [data.name, data.lat, data.lon] : [];
            Vue.set(attachment, 'location', location);
        },

        setPreview({attachment}, data) {
            const content = data ? [data.url, data.image, data.title, data.desc] : [];
            Vue.set(attachment, 'fetch', content);
        },

        reset(state) {
            state.savedBg = '0';
            state.content = '',
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    }
}