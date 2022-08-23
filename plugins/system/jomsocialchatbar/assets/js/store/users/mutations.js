var Vue = require( 'joms' ).Vue;

module.exports = {

    /**
     * Add a new user.
     * @param {State} state
     * @param {Object} payload
     * @param {Object} payload.data
     */
    add: function( state, payload ) {
        var userData = payload.data,
            userId = userData.id;

        Vue.set( state, userId, userData );
    },

    /**
     * Edit a user.
     * @param {State} state
     * @param {Object} payload
     * @param {number} payload.id
     * @param {Object} payload.data
     */
    edit: function( state, payload ) {
        var userId = payload.id,
            userData = payload.data,
            prevData = state[ userId ];

        if ( prevData ) {
            userData = _.extend({}, prevData, userData );
            Vue.set( state, userId, userData );
        }
    },

    /**
     * Delete a user.
     * @param {State} state
     * @param {Object} payload
     * @param {number} payload.id
     */
    delete: function( state, payload ) {
        var userId = payload.id,
            messageId = payload.messageId;

        if ( state[ userId ] ) {
            delete state[ userId ];
        }
    }

};
