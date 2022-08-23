module.exports = {

    /**
     * Fetch messages for a conversion.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     * @return {jQuery.Deferred}
     */
    fetch: function( context, payload ) {
        return jQuery.Deferred(function( defer ) {
            var chatid = payload.id,
                offset = payload.oldestMsg,
                seen = 0,
                params = [ chatid, offset, seen ];

            joms.ajax({
                func: 'chat,ajaxGetLastChat',
                data: params,
                callback: function( json ) {
                    var messages = []; 
                    if ( _.isArray( json.messages ) && json.messages.length ) {
                        _.each( json.messages, function( message ) {
                            messages.push( message );
                        })
                    }

                    if ( _.isArray( json.seen) && json.seen.length ) {
                        _.each( json.seen, function( item ) {
                            messages.push( item );
                        });
                    }
                    
                    messages.sort( function( a, b) {
                        return +b.id - +a.id;
                    });

                    if ( messages.length ) {
                        context.commit( 'append', { messages: messages } );
                    }

                    // Let caller know that the action is complete.
                    defer.resolve( { messages: messages, seen: json.seen } );
                }
            });
        });
    },

    /**
     * Submit message for a conversation.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     * @param {Object} payload.data
     * @return {jQuery.Deferred}
     */
    submit: function( context, payload ) {
        return jQuery.Deferred(function( defer ) {
            var chatId = payload.id,
                data = payload.data,
                attachment = data.attachment || {},
                timestamp = ( new Date ).getTime(),
                messageId = 'temp_' + timestamp;

            // Assign temporary id to the message.
            data = _.extend({}, data, { id: messageId });

            // Add temporary message.
            context.commit( 'prepend', { messages: [ data ] });

            // Sanitize attachment parameter
            if ( attachment.type && attachment.id ) {
                attachment = { type: attachment.type, id: attachment.id };
            } else {
                attachment = {};
            }

            // Submit actual message.
            joms.ajax({
                func: 'chat,ajaxAddChat',
                data: [ chatId, data.content, JSON.stringify( attachment ) ],
                callback: function( json ) {
                    if ( json.reply_id ) {
                        var item = {
                            tempid: messageId,
                            id: json.reply_id,
                            chatid: json.chat_id
                        }
                        context.commit( 'updateTemp', item );
                        defer.resolve();
                    } else {
                        context.commit( 'error', {tempid: messageId} );
                        json.error && alert(json.error)
                    }
                }
            });
        });
    },

    /**
     * Check new messages for a conversion.
     * @param {Object} context
     * @param {Function} context.commit
     * @param {Object} context.state
     * @param {Object} payload
     * @param {number} payload.id
     * @param {Object} payload.lastMessageId
     * @return {jQuery.Deferred}
     */
    check: function( context, payload ) {
        return jQuery.Deferred(function( defer ) {
            var ids = payload.ids,
                xhr;

            // Sanity check.
            if ( ! ( _.isArray( ids ) && ids.length ) ) {
                defer.resolve();
                return;
            }

            // Get latest messages for each conversations.
            ids = _.map( ids, function( id ) {
                var messages = context.getters[ 'sortedMessages' ]( id ),
                    last;

                if ( messages && messages.length ) {
                    messages = _.filter( messages, function( item ) { return +item.id });
                    last = messages[ messages.length - 1 ];
                    if ( last && +last.id ) {
                        last = +last.id;
                    }
                }

                return [ +id, last || 0 ];
            });

            xhr = joms.ajax({
                func: 'chat,ajaxGetLatestActivities',
                data: [ JSON.stringify( ids ) ],
                callback: function( json ) {
                    if ( json ) {
                        _.each( json, function( messages, chatId ) {
                            _.each( messages, function( item ) {
                                if ( typeof item.attachment === 'string' ) {
                                    item.attachment = JSON.parse( item.attachment );
                                };

                                context.commit( 'add', {
                                    id: chatId,
                                    message: item
                                });
                            });
                        });
                    }

                    defer.resolve();
                }
            });
        });
    }

};
