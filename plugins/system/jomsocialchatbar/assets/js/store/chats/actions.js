function formatName( names ) {
    if ( ! _.isArray( names ) ) {
        names = [ names ];
    }

    if ( names.length === 1 ) {
        names = names[0];
    } else if ( names.length > 1 ) {
        names = _.map( names, function( str, span ) {
            // Remove badge on group conversations.
            if ( str.indexOf( '<' ) >= 0 ) {
                span = document.createElement( 'span' );
                span.innerHTML = str;
                str = span.innerText;
            }

            str = str.split( ' ' );
            return str[0];
        });
        names = names.sort();
        names = names.join( ', ' );
        names = names.replace( /,\s([^\s]*)$/, ' ' + Joomla.JText._('PLG_JOMSOCIALCHATBAR_AND') + ' $1' );
    } else {
        names = '';
    }

    return names;
}

module.exports = {

    /**
     * Fetch conversation list.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     */
    fetch: function( context, payload ) {
        return jQuery.Deferred( function( defer ) {
            _.each( payload.buddies, function( item ) {
                context.commit( 'users/add', { data: item }, { root: true });
            });

            _.each( payload.conversations, function( item ) {
                item.id = String( item.chat_id );
                item.name = formatName( item.name );
                context.commit('add', item);
            });
        
            _.each( payload.opened, function( item ) {
                item.id = String( item.chat_id );
                item.name = formatName( item.name );

                context.commit('add', item);
            });

            defer.resolve();
        });
    },

    fetchMore: function(context, payload) {
        return jQuery.Deferred( function( defer ) {
            joms.ajax({
                func: 'chat,ajaxInitializeChatData',
                data: [JSON.stringify( payload.list )],
                callback: function(json) {
                    var empty = true;
                    _.each( json.buddies, function( item ) {
                        context.commit( 'users/add', { data: item }, { root: true });
                    });

                    _.each( json.list, function( item ) {
                        item.id = String( item.chat_id );
                        item.name = formatName( item.name );

                        context.commit('add', item);
                        context.commit('addSidebarItem', item.id)
                        empty = false;
                    });
                    
                    if ( empty ) {
                        defer.reject();
                    } else {
                        defer.resolve();
                    }
                }
            });
        });
    },

    initialized: function (context, payload) {
        context.commit('initialized', payload);
    },

    /**
     * Check updates on conversation state.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @return {jQuery.Deferred}
     */
    check: function( context ) {
        var opened = _.map( context.getters[ 'opened' ], function( item ) {
            return item.id;
        });

        return context.dispatch( 'messages/check', { ids: opened });
    },

    /**
     * Open a conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {number} id
     */
    open: function( context, id ) {
        var chat = context.state.info[id];
        if ( chat ) {
            context.commit( 'open', id );
            context.commit( 'storeState' );
        }
    },

    /**
     * Close a conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {number} id
     */
    close: function( context, id ) {
        var chat = context.state.info[id];
        if ( chat && chat.open ) {
            context.commit( 'close', id );
            context.commit( 'storeState' );
        }
    },

    /**
     * Toggle a conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {number} id
     */
    toggle: function( context, id ) {
        var chat = context.state.info[id];
        if ( chat && chat.open ) {
            context.commit( 'toggle', id );
            context.commit( 'storeState' );
        }
    },

    /**
     * Leave a conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {number} id
     */
    leave: function( context, id ) {
        joms.ajax({ 
            func: 'chat,ajaxLeaveChat', 
            data: [ id ],
            callback: function() {
                context.commit( 'delete', id );
            } 
        });
    },

    /**
     * Add users to the conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     * @param {number[]} payload.users
     */
    addUsers: function( context, payload ) {
        var chatid = payload.chatid,
            userids = payload.userids;

        userids = _.map( userids, String );
        userids = JSON.stringify( userids );

        context.commit('addUsers', payload);
        joms.ajax({
            func: 'chat,ajaxAddPeople',
            data: [ chatid, userids ],
            callback: function() {
                context.commit('addUsers', payload);
            }
        });    
    },

    /**
     * Mute conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     */
    mute: function( context, payload ) {
        var id = payload.id;

        context.commit( 'mute', id );
        joms.ajax({ func: 'chat,ajaxMuteChat', data: [ id, 1 ] });
    },

    /**
     * Unmute conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     */
    unmute: function( context, payload ) {
        var id = payload.id;

        context.commit( 'unmute', id );
        joms.ajax({ func: 'chat,ajaxMuteChat', data: [ id, 0 ] });
    },

    seen: function( context, id ) {
        return jQuery.Deferred( function( defer ) {
            joms.ajax({
                func: 'chat,ajaxSeen',
                data: [id],
                callback: function() {
                    context.commit( 'seen', id );
                    defer.resolve();
                }
            });
        });
    },

    seenBy: function( context, payload ) {
        if ( payload.userid != window.joms_my_id ) {
            context.commit( 'seenBy', payload );
        }
    },

    clearSeen: function( context, payload ) {
        context.commit( 'clearSeen', payload );
    },

    unread: function( context, id ) {
        context.commit( 'unread', id );
    },

    syncState: function ( context ) {
        context.commit( 'syncState' );
    },

    setTopSidebar: function ( context, id ) {
        context.commit( 'setTopSidebar', id );
    },

    addSidebarItem: function ( context, id ) {
        context.commit( 'addSidebarItem', id);
    },

    setActiveWindow: function ( context, id ) {
        context.commit( 'setActiveWindow', id );
    },

    setLimitOpened: function( context ) {
        var body_width = document.body.clientWidth,
            sidebar_width = 220,
            left_space = 20,
            limit = 0;

        limit = parseInt(( body_width - sidebar_width - left_space - 60 ) / 240);
        context.commit( 'setLimitOpened', limit );
    },

    changeName: function( context, payload ) {
        context.commit( 'changeName', payload );
    }

};
