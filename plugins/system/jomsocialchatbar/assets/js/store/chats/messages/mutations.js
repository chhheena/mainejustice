var Vue = require( 'joms' ).Vue;

module.exports = {

    add: function( state, payload ) {
        _.each( payload.data, function( message ) {
            var existed = state.filter( function( item ) {
                return +item.id === +message.id;
            })

            if ( existed.length === 0 ) {
                state.unshift( message );
            }
        })
    },

    append: function( state, payload ) {
        _.each( payload.messages, function( message ) {
            var existed = state.filter( function( item ) {
                return +item.id === +message.id;
            })

            if ( existed.length === 0 ) {
                state.unshift( message );
            }
        })
    },
    prepend: function( state, payload ) {
        _.each( payload.messages, function( message ) {
            var existed = state.filter( function( item ) {
                return +item.id === +message.id;
            })

            if ( existed.length === 0 ) {
                state.push( message );
            }
        })
    },

    updateTemp: function( state, payload ) {

        var temp = state.filter( function( item ) {
            return payload.tempid === item.id;
        })

        if ( temp.length ) {
            _.each( temp, function( msg ) {
                Vue.set( msg, 'id', payload.id );
            })
        }
    },
    error: function( state, payload ) {
        var msg = state.filter( function( item ) {
            return payload.tempid === item.id;
        })

        if ( msg.length ) {
            _.each( msg, function( msg ) {
                Vue.set( msg, 'error', true );
            })
        } 
    }
};
