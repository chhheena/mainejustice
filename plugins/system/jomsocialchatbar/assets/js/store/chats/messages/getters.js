module.exports = {
    /**
     * Group oldest message ID of a conversation.
     * @param {Object} state
     * @param {Object} getters
     */
    newestMessageID: function( state, getters ) {
        return function( chatId ) {
            var keys, key;

            if ( state[ chatId ] ) {
                keys = _.chain( state[ chatId ] ).keys()
                    .filter(function( messageId ) { return +messageId })
                    .sortBy(function( messageId ) { return +messageId })
                    .value();

                if ( keys.length ) {
                    key = keys[ keys.length - 1 ];
                }
            }

            return key;
        }
    }

};
