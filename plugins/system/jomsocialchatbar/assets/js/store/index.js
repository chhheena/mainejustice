var Vuex = require( 'joms' ).Vuex;

module.exports = new Vuex.Store({
    modules: {
        users: require( './users' ),
        chats: require( './chats' )
    }
});
