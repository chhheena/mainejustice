var localStorage = require( 'joms' ).storage;

module.exports = {

    opened: function( state, getters ) {
        var opened = [],
            needClose = [],
            limit = state.limitOpened,
            active = state.active,
            idx;

        if ( limit ) {
            var chats = _.filter( state.opened, function( id ) {
                return state.info[id];
            });

            if ( chats.length > limit  && limit ) {
                _.each( chats.splice( limit ), function( id ) {
                    if ( id != active) {
                        needClose.push( id );    
                    }
                });

                idx = chats.indexOf( active ); 
                if ( active && idx === -1 ) {
                    needClose.push( chats.pop() );
                    chats.push( active );
                }
            }

            _.each( chats, function( id ) {
                if ( state.info[id] ) {
                    opened.push( state.info[id] );
                }
            });
        } else {
            needClose = state.opened;
        }

        return {
            opened: opened,
            needClose: needClose
        }
    },

    sidebar: function( state, getters) {
        var sidebar = [];
        _.each( state.sidebar, function ( id ) {
            if (state.info[id]) {
                sidebar.push(state.info[id]);
            }
        });

        return sidebar;
    }
};
