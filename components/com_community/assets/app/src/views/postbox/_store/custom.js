export default {
    namespaced: true,

    state: {
        type: '',
        content: '',
        privacy: '10',
    },

    mutations: {
        setType(state, value) {
            state.type = value;
        },

        setContent(state, value) {
            state.content = value;
        },

        setPrivacy(state, value) {
            state.privacy = value;
        },

        reset(state) {
            state.type = '';
            state.content = '';
            state.privacy = '10';
        },
    },
}