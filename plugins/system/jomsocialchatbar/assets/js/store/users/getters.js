module.exports = {

    /**
     * Get name of particular user.
     * @param {Object} state
     * @param {Object} getters
     */
    name: function( state, getters ) {
        return function( userId ) {
            var name;

            if ( state[ userId ] ) {
                name = state[ userId ].name
            }

            return name;
        }
    },

    /**
     * Get avatar image of particular user.
     * @param {Object} state
     * @param {Object} getters
     */
    avatar: function( state, getters ) {
        return function( userId ) {
            var avatar;

            if ( state[ userId ] ) {
                avatar = state[ userId ].avatar
            }

            return avatar;
        }
    },

    /**
     * Search users by keyword.
     * @param {Object} state
     * @param {Object} getters
     */
    search: function( state, getters ) {
        return function( keyword ) {
            keyword = ( keyword || '' ).trim().toLowerCase();
            return _.filter( state, function( user ) {
                return keyword && ( user.name || '' ).toLowerCase().indexOf( keyword ) !== -1;
            });
        }
    }

};
