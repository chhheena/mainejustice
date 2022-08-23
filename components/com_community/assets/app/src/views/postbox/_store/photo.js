import Vue from 'vue';
import {constants} from '../../../utils/constants';

const privmap = {
    '0': 'public',
    '10': 'public',
    '20': 'site_members',
    '30': 'friends',
    '40': 'me'
};
const albums = constants.get('album').map(album => {
    return {
        value: album.id,
        text: album.name,
        sub: privmap[album.permissions],
    }
});

const {target, element, filter} = constants.get('postbox.attachment');

const raw = {
    element,
    target,
    filter,
    type: 'photo',
    id: [],
    album_id: albums.length ? albums[0].value : '',
    mood: '',
};

export default {
    namespaced: true,

    state: {
        albums,
        content: '',
        attachment: JSON.parse(JSON.stringify(raw)),
    },

    mutations: {
        setContent(state, content) {
            state.content = content;
        },

        setAlbum({attachment}, id) {
            attachment.album_id = id;
        },

        setPhoto({attachment}, photos) {
            Vue.set(attachment, 'id', photos);
        },

        setMood(state, mood) {
            state.attachment.mood = mood;
        },

        reset(state) {
            state.content = '';
            Vue.set(state, 'attachment', JSON.parse(JSON.stringify(raw)));
        },
    }
}