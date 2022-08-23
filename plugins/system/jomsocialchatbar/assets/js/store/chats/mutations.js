var Vue = require( 'joms' ).Vue,
    localStorage = require( 'joms' ).storage,
    CHATSTATE_MAXIMIZED = 1,
    CHATSTATE_MINIMIZED = 2,
    CHATSTATE_CLOSED = 0;

module.exports = {

    /**
     * Add a new chat.
     * @param {State} state
     * @param {Object} chat
     */
    add: function( state, chat ) {
        if (state.info[chat.id]) {
            _.extend( chat, state.info[chat.id]);
        }
        Vue.set( state.info, chat.id, chat);
    },

    addSidebarItem: function( state, id) {
        if ( state.sidebar.indexOf( +id ) === -1 ) {
            state.sidebar.push( +id ); 
        }
    },

    addUsers: function (state, payload) {
        var chatid = payload.chatid,
            userids = payload.userids,
            chat = state.info[chatid];

        if (chat) {
            userids.forEach(function (id) {
                if (chat.users.indexOf(+id) < 0) {
                    Vue.set(chat, 'participants', +chat.participants + 1 );
                    chat.users.push(+id);
                }
            });
        }   
    },

    initialized: function (state, payload) {
        var chat = state.info[payload.id];
        if (chat) {
            Vue.set( chat, 'initialized', true);
        }
    },

    /**
     * Update an existing chat.
     * @param {State} state
     * @param {Object} payload
     */
    update: function( state, payload ) {
        var id = payload.id,
            chat = payload.chat,
            prevChat = state.info[ id ];

        chat = _.extend({}, prevChat || {}, chat );
        Vue.set( state.info, id, chat );
    },

    /**
     * Remove an existing chat.
     * @param {State} state
     * @param {number} id
     */
    delete: function( state, id ) {
        if ( state.info[id] ) {
            Vue.delete( state.info, id );
        }

        Vue.set( state, 'active', 0 );
    },

    /**
     * Open a chat.
     * @param {State} state
     * @param {number} id
     */
    open: function( state, id ) {
        var info = state.info[id],
            idx = state.opened.indexOf( +id );

        if ( idx === - 1 ) {
            state.opened.push(+id);
        }
        
        if (  info && info.open !== 1 ) {
            Vue.set( state.info[id], 'open', CHATSTATE_MAXIMIZED );
        }
    },

    /**
     * Close a chat.
     * @param {State} state
     * @param {number} id
     */
    close: function( state, id ) {
        var info,
            chatbarState,
            chatState,
            idx = state.opened.indexOf(+id);

        if (idx != -1) {
            state.opened.splice(idx, 1);
        }

        if ( state.info[id] ) {
            info = state.info[id];

            Vue.set( info, 'open', CHATSTATE_CLOSED );
            
        }
    },

    /**
     * Toggle a chat.
     * @param {State} state
     * @param {number} id
     */
    toggle: function( state, id ) {
        var info = state.info[id],
            chatbarState,
            chatState,
            openedState;

        if ( info ) {
            openedState = info.open === CHATSTATE_MINIMIZED ? CHATSTATE_MAXIMIZED : CHATSTATE_MINIMIZED;
            Vue.set( state.info[id], 'open', openedState );
        }
    },

    /**
     * Mute a chat.
     * @param {State} state
     * @param {number} id
     */
    mute: function( state, id ) {
        var info;

        _.each( state.info, function(item) {
            if (item.id == id) {
                info = item;
            }
        })

        if ( info ) {
            Vue.set( info, 'mute', 1 );
        }
    },

    /**
     * Unmute a chat.
     * @param {State} state
     * @param {number} id
     */
    unmute: function( state, id ) {
        var info;

        _.each( state.info, function(item) {
            if (item.id == id) {
                info = item;
            }
        })

        if ( info ) {
            Vue.set( info, 'mute', 0 );
        }
    },

    seen: function( state, id ) {
        var chat = state.info[ id ];
        if (chat) {
            Vue.set( chat, 'seen', 1 );
        }
    },

    seenBy: function( state, payload ) {
        var userid = payload.userid,
            chatid = payload.chatid,
            chat = state.info[ chatid ];
        if ( !chat ) {
            return;
        }

        if ( !chat.seenBy ) {
            Vue.set( chat, 'seenBy', [] );
        }

        chat.seenBy.push( userid );    
    },

    clearSeen: function( state, payload ) {
        var chatid = payload.chatid,
            chat = state.info[ chatid ];
        if ( !chat ) {
            return;
        }

        if ( chat.seenBy ) {
            chat.seenBy.splice( 0, chat.seenBy.length )
        }
    },

    unread: function( state, id ) {
        var chat = state.info[id];
        Vue.set( chat, 'seen', 0 );
    },

    storeState: function ( state ) {
        var chatbarState = localStorage.get('chatbar') || {},
            info = chatbarState.info || {};

        _.each( state.info, function(item) {
            if (typeof item.open !== 'undefined' ) {
                info[item.id] = {};
                info[item.id].open = item.open;
            }
        });
        
        chatbarState.info = info;
        chatbarState.opened = state.opened;
        localStorage.set('chatbar', chatbarState);
    },

    syncState: function ( state ) {
        var chatbarState = localStorage.get( 'chatbar' ) || {},
            info = chatbarState.info || {},
            opened = chatbarState.opened || [];

        _.each( info, function(item, key) {
            if (state.info[key]) {
                Vue.set(state.info[key], 'open', item.open);
            } 
        });

        _.each( opened, function (id) {
            if ( state.opened.indexOf( +id ) === -1 ) {
                state.opened.push(id);
            }
        });
    },

    setTopSidebar: function ( state, id ) {
        var idx = state.sidebar.indexOf( +id );
        if ( idx !== - 1 ) {
            state.sidebar.splice( idx, 1 );
            state.sidebar.unshift ( +id );
        } else if ( state.info[id] ) {
            state.sidebar.unshift( +id );
        }
    },

    setActiveWindow: function ( state, id ) {
        var chat = state.info[id];
        if ( chat && chat.open === CHATSTATE_MAXIMIZED) {
            Vue.set( state, 'active', +id );
        } else {
            Vue.set( state, 'active', 0 );
        }
    },

    setLimitOpened: function( state, limit ) {
        Vue.set( state, 'limitOpened', +limit);
    },

    changeName: function( state, payload ) {
        var chat = state.info[payload.chat_id];

        if (chat) {
            Vue.set( chat, 'name', _.escape( payload.groupname ) );
        }
    }

};
