(function( $, _, Backbone ) {

    var util = require( './util' );

    /**
     * Conversation messages view.
     * @class {Backbone.View}
     */
    module.exports = Backbone.View.extend({

        el: '.joms-chat__messages',

        events: {
            'click .joms-chat__message-actions a': 'recallMessage',
            'mouseenter [data-tooltip]': 'showTooltip',
            'mouseleave [data-tooltip]': 'hideTooltip'
        },

        initialize: function (config) {
            this.$loading = this.$('.joms-js--chat-conversation-loading');
            this.$messages = this.$('.joms-js--chat-conversation-messages');
            this.$noParticipants = this.$('.joms-js--chat-conversation-no-participants');

            joms_observer.add_action('chat_conversation_open', this.render, 10, 2, this);
            joms_observer.add_action('chat_conversation_update', this.update, 10, 1, this);
            joms_observer.add_action('chat_messages_loading', this.messagesLoading, 10, 1, this);
            joms_observer.add_action('chat_messages_loaded', this.messagesLoaded, 10, 3, this);
            joms_observer.add_action('chat_messages_received', this.messagesReceived, 10, 3, this);
            joms_observer.add_action('chat_messages_render', this.messagesRender, 10, 10, this);
            joms_observer.add_action('chat_message_sending', this.messageSending, 10, 5, this);
            joms_observer.add_action('chat_message_sent', this.messageSent, 10, 3, this);
            joms_observer.add_action('chat_empty_message_view', this.emptyView, 1, 0, this);
            joms_observer.add_action('chat_seen_message', this.seenMessage, 1, 3, this);
            joms_observer.add_action('chat_remove_seen_message', this.removeSeenMessage, 1, 2, this);
            joms_observer.add_action('chat_previous_messages_loaded', this.previousMessagesLoaded, 1, 2, this);
            joms_observer.add_action('chat_error_message', this.errorMessage, 1, 1, this);
            
            // Handle scrolling through the messages.
            this.$messages.on( 'mousewheel DOMMouseScroll', $.proxy( this.onScroll, this ) );
        },

        render: function () {
            this.$messages.empty().hide();
            this._updateRecallAbility();
        },

        update: function( item ) {
            var participants;

            if ( ! item.active ) {
                return;
            }

            participants = +item.participants;
            if ( item.type !== 'group' ) {
                participants = 1;
            }

            this._toggleEmptyParticipants( participants );
        },

        errorMessage: function(now) {
            var $error = this.$messages.find('[data-timestamp='+now+']');
            $error.addClass('joms-chat__message-error');
            $error.find('.joms-js-chat-loading').hide();
            $error.find('.joms-chat__message-actions').hide();
        },

        /**
         * Get older messages for current conversation.
         */
        getOlderMessages: _.debounce(function() {
            var $ct = this.$messages,
                $end = $ct.children( '.joms-js--chat-conversation-end' ),
                $msg, msgId;

            // Do not proceed if all older messages are already loaded.
            if ( $end.length ) {
                return;
            }

            // Get ID of the oldest message.
            $msg = $ct.find( '.joms-js-chat-content[data-id]' ).first();
            msgId = $msg.data( 'id' );

            // Get previous messages if ID found.
            if ( msgId ) {
                this.$loading.show();
                joms_observer.do_action( 'chat_get_previous_messages', null, msgId );
            }
        }, 500, true ),

        seenMessage: function( data, me, buddies ) {
            var seen, names, template, html, $seen;

            if ( ! ( _.isArray( data ) && data.length ) ) {
                return;
            }

            seen = _.chain( data )
                .filter(function( item ) { return ( +me.id !== +item.user_id ) })
                .map(function( item ) { return buddies[ item.user_id ] })
                .value();

            if ( ! seen.length ) {
                return;
            }

            // Merge with previous seen users.
            this._seen = _.chain( ( this._seen || [] ).concat( seen ) )
                .uniq(function( item ) { return +item.id })
                .sortBy(function( item ) { return item.name })
                .value();

            // Removes previous seen html.
            $seen = this.$messages.children( '.joms-js--seen' );
            if ( $seen.length ) {
                $seen.remove();
            }

            // Render new seen html.
            template = util.getTemplateById( 'joms-js-template-chat-seen-by' );
            names = _.map( this._seen, function( item ) { return item.name });
            html = template({ seen: this._seen, names: util.formatName( names ) });
            $seen = $( html ).addClass( 'joms-js--seen' );

            this.$messages.append( $seen );
            this.scrollToBottom();
        },

        removeSeenMessage: function() {
            this._seen = false;
            this.$messages.children( '.joms-js--seen' ).remove();
        },

        emptyView: function () {
            this._seen = false;
            this.$loading.hide();
            this.$messages.empty().show().css('opacity', '');
        },

        messagesLoading: function () {
            this.$messages.css('opacity', 0);
            this.$loading.show();
        },

        messagesLoaded: function (data, buddies) {
            this.$loading.hide();
            this.$messages.css('opacity', '');

            data.reverse();
            _.each(data, function (item) {
                var user = buddies[item.user_id];
                var time = item.created_at * 1000;
                this.messagesRender(item.id, item.content, item.attachment ? JSON.parse(item.attachment) : {}, user, time, item.action, item.params ? JSON.parse(item.params) : {});
            }, this);
            this._updateRecallAbility();
            this.scrollToBottom();
        },

        messagesRender: function(id, message, attachment, user, timestamp, action, params) {
            var $container = this.$messages,
                date = new Date( timestamp ),
                timeFormatted = util.formatDateTime( timestamp ),
                dGroup, $dGroup, template, html, $last, name, mine;

            // Get date group for messages.
            dGroup = date.toJSON().slice( 0, 10 ).replace( /-/g, '' );
            $dGroup = $container.children( '.joms-js-chat-message-dgroup' )
                .filter( '[data-id="' + dGroup + '"]' );

            if ( ! $dGroup.length ) {
                template = util.getTemplateById( 'joms-tpl-chat-message-dgroup' );
                $dGroup = $( template({ id: dGroup, date: util.formatDate( timestamp ) }) );
                $dGroup.appendTo( $container );
            }

            $container = $dGroup.children( '.joms-js-content' );

            mine = user && ( +user.id === +window.joms_my_id ) || false;
            name = mine ? 'you' : '';

            if ( action === 'sent' ) {

                // Format links.
                message = message.replace( /((http|https):\/\/.*?[^\s]+)/g,
                    '<a target="_blank" style="text-decoration:underline" href="$1">$1</a>' );

                // Replace newlines.
                message = message.replace( /\\n/g, '<br />' );
                message = message.replace( /\r?\n/g, '<br />' );

                var att = '';
                if (attachment.type) {
                    att = this.attachmentView(attachment);
                }

                $last = $container.find('.joms-chat__message-item').last();

                if ( ! $last.length || +$last.data( 'user-id' ) !== +user.id ) {
                    if ( user.name.indexOf( '<' ) >= 0 ) {
                        var span = document.createElement( 'span' );
                        span.innerHTML = user.name;
                        user.name = span.innerText;
                    }

                    template = util.getTemplateById( 'joms-js-template-chat-message' );
                    html = template({
                        timestamp: timestamp,
                        name: name,
                        user_id: user.id,
                        user_name: user.name,
                        user_avatar: user.avatar,
                        online: user.online,
                        profile_link: user.profile_link
                    });

                    $last = $( html );
                    $last.appendTo( $container );
                }

                template = util.getTemplateById( 'joms-js-template-chat-message-content' );
                html = template({
                    message: util.getEmoticon( message ),
                    time: timeFormatted,
                    timestamp: timestamp,
                    id: id,
                    attachment: att,
                    mine: mine
                });
                $last.find( '.joms-js-chat-message-item-body' ).append( html );

            } else if ( action === 'leave' ) {
                template = util.getTemplateById( 'joms-js-template-chat-leave' );
                html = template({
                    id: id,
                    mine: mine,
                    name: user.name,
                    time: timeFormatted
                });
                $container.append( html );
            } else if ( action === 'add' ) {
                template = util.getTemplateById( 'joms-js-template-chat-added' );
                html = template({
                    id: id,
                    mine: mine,
                    name: user.name,
                    time: timeFormatted
                });
                $container.append( html );
            } else if ( action === 'change_chat_name' ) {
                template = util.getTemplateById( 'joms-js-template-chat-name-changed' );
                html = template({
                    id: id,
                    mine: mine,
                    name: user.name,
                    groupname: _.escape( params.groupname ),
                    time: timeFormatted
                });
                $container.append( html );
                this.scrollToBottom();
            }
        },

        previousMessagesLoaded: function (data, buddies) {
            this.$loading.hide();
            if (!data.length) {
                return;
            }

            _.each(data, function (item) {
                var user = buddies[item.user_id];
                var time = item.created_at * 1000;
                this.preMessagesRender(item.id, item.content, JSON.parse(item.attachment), user, time, item.action);
            }, this);

            this._updateRecallAbility();

            var parent_offset = this.$messages.offset();
            var first_element = data[0];
            var first_item = this.$messages.find('.joms-chat__message-content[data-id="'+first_element.id+'"]');
            var offset = first_item.offset();
            var padding_top = +this.$messages.css('padding-top').replace('px', '');
            this.$messages.scrollTop(offset.top - parent_offset.top - padding_top);
        },

        preMessagesRender: function(id, message, attachment, user, timestamp, action) {
            var $container = this.$messages,
                date, timeFormatted, dGroup, $dGroup, template, html, $first, name, mine;

            // Special case on end message.
            if ( action === 'end' ) {
                template = util.getTemplateById( 'joms-js-template-chat-message-end' );
                html = template();
                $container.prepend( html );
                return;
            }

            // Format date and time.
            date = new Date( timestamp ),
            timeFormatted = util.formatDateTime( timestamp ),

            // Get date group for messages.
            dGroup = date.toJSON().slice( 0, 10 ).replace( /-/g, '' );
            $dGroup = $container.children( '.joms-js-chat-message-dgroup' )
                .filter( '[data-id="' + dGroup + '"]' );

            if ( ! $dGroup.length ) {
                template = util.getTemplateById( 'joms-tpl-chat-message-dgroup' );
                $dGroup = $( template({ id: dGroup, date: util.formatDate( timestamp ) }) );
                $dGroup.prependTo( $container );
            }

            $container = $dGroup.children( '.joms-js-content' );

            mine = user && ( +user.id === +window.joms_my_id ) || false;
            name = mine ? 'you' : '';

            if ( action === 'sent' ) {

                // Format links.
                message = message.replace( /((http|https):\/\/.*?[^\s]+)/g,
                    '<a target="_blank" style="text-decoration:underline" href="$1">$1</a>' );

                // Replace newlines.
                message = message.replace( /\\n/g, '<br />' );
                message = message.replace( /\r?\n/g, '<br />' );

                var att = '';
                if (attachment.type) {
                    att = this.attachmentView(attachment);
                }

                $first = $container.find('.joms-chat__message-item').first();

                if ( ! $first.length || +$first.data( 'user-id' ) !== +user.id ) {
                    if ( user.name.indexOf( '<' ) >= 0 ) {
                        var span = document.createElement( 'span' );
                        span.innerHTML = user.name;
                        user.name = span.innerText;
                    }

                    template = util.getTemplateById( 'joms-js-template-chat-message' );
                    html = template({
                        timestamp: timestamp,
                        name: name,
                        user_id: user.id,
                        user_name: user.name,
                        user_avatar: user.avatar,
                        online: user.online
                    });

                    $first = $( html );
                    $first.prependTo( $container );
                }

                template = util.getTemplateById( 'joms-js-template-chat-message-content' );
                html = template({
                    message: util.getEmoticon( message ),
                    time: timeFormatted,
                    timestamp: timestamp,
                    id: id,
                    date: date,
                    attachment: att,
                    mine: mine
                });
                $first.find( '.joms-js-chat-message-item-body' ).prepend( html );

            } else if ( action === 'leave' ) {
                template = util.getTemplateById( 'joms-js-template-chat-leave' );
                html = template({
                    id: id,
                    mine: mine,
                    name: user.name,
                    time: timeFormatted
                });
                $container.prepend( html );
            } else if ( action === 'add' ) {
                template = util.getTemplateById( 'joms-js-template-chat-added' );
                html = template({
                    id: id,
                    mine: mine,
                    name: user.name,
                    time: timeFormatted
                });
                $container.prepend( html );
            }
        },

        messagesReceived: function (data, buddies) {
            if (data.length > 0) {
                _.each(data, function (item) {
                    var user = buddies[item.user_id];
                    var time = item.created_at * 1000;
                    this.messagesRender(item.id, item.content, item.attachment ? JSON.parse(item.attachment) : {}, user, time, item.action, item.params ? JSON.parse(item.params) : {});
                }, this);
                this.scrollToBottom();
            }
        },

        attachmentView: function( attachment ) {
            var type = attachment.type,
                template;

            if ( ! attachment.url ) {
                return '';
            } else if ( type === 'file' ) {
                template = util.getTemplateById( 'joms-js-template-chat-message-file' );
                return template({ url: attachment.url, name: attachment.name });
            } else if ( type === 'image' ) {
                template = util.getTemplateById( 'joms-js-template-chat-message-image' );
                return template({ url: attachment.url });
            } else if ( type === 'video' ) {
                template = util.getTemplateById( 'joms-js-template-chat-message-video' );
                return template( $.extend( { url: attachment.url }, attachment.video ) );
            } else if ( type === 'url' ) {
                template = util.getTemplateById( 'joms-js-template-chat-message-url' );
                return template({
                    url: attachment.url,
                    title: attachment.title,
                    images: attachment.images,
                    description: attachment.description
                });
            }
        },

        messageAppend: function (message, attachment, me, timestamp) {
            this.messagesRender(null, message, attachment, me, timestamp, 'sent');
        },

        messageSending: function (message, attachment, me, timestamp) {
            message = _.escape(message);
            this.messageAppend(message, attachment, me, timestamp);
            this.scrollToBottom();

            // Show loading if ajax request is taking too long.
            setTimeout( $.proxy( function() {
                var $msg = this.$messages.find( '.joms-js-chat-content.' + timestamp ),
                    $loading = $msg.siblings( '.joms-js-chat-loading' ),
                    is_error = !!$msg.parents('.joms-chat__message-error').length;
      
                if ( !is_error && $loading.length ) {
                    $loading.show();
                }
            }, this ), 1500 );
        },

        messageSent: function ( id, timestamp, attachment ) {
            var $msg = this.$messages.find( '.joms-js-chat-content.' + timestamp ),
                $loading = $msg.siblings( '.joms-js-chat-loading' ),
                $attachment;

            $msg.attr( 'data-id', id );
            $loading.remove();

            // Updates link preview.
            if ( attachment && ( attachment.type === 'url' || attachment.type === 'video' ) ) {
                $attachment = $msg.next( '.joms-js-chat-attachment' );
                if ( $attachment ) {
                    $attachment.remove();
                }
                $attachment = $( this.attachmentView( attachment ) );
                $attachment.insertAfter( $msg );
            }
        },

        recallMessage: function( e ) {
            if (!confirm(joms_lang.COM_COMMUNITY_CHAT_ARE_YOU_SURE_TO_DELETE_THIS_MESSAGE)) {
                return;
            }

            var $btn = $( e.currentTarget ).closest( '.joms-chat__message-actions' ),
                $msg = $btn.siblings( '.joms-chat__message-content' ),
                $group = $msg.closest( '.joms-chat__message-item' ),
                isMine = +$group.data( 'user-id' ) === +window.joms_my_id,
                id = +$msg.data( 'id' ),
                $prevGroup, $nextGroup;

            e.preventDefault();
            e.stopPropagation();

            if ( isMine ) {
                $msg = $msg.parent();

                if ( $msg.siblings().length ) {
                    $msg.remove();
                } else {
                    $prevGroup = $group.prev();
                    $nextGroup = $group.next();
                    $group.remove();

                    if ( +$prevGroup.data( 'user-id' ) === +$nextGroup.data( 'user-id' ) ) {
                        $prevGroup.find( '.joms-chat__message-body' ).children()
                            .prependTo( $nextGroup.find( '.joms-chat__message-body' ) );
                        $prevGroup.remove();
                    }
                }

                joms_observer.do_action( 'chat_message_recall', id );
            }
        },

        scrollToBottom: function () {
            var div = this.$messages[0];
            div.scrollTop = div.scrollHeight;
        },

        _updateRecallAbility: function() {
            var now = ( new Date() ).getTime(),
                maxElapsed = +joms.getData( 'chat_recall' ),
                $btns;

            if ( ! maxElapsed ) {
                return;
            }

            $btns = this.$messages.find( '.joms-chat__message-actions' );
            if ( $btns.length ) {
                maxElapsed = maxElapsed * 60 * 1000;
                $btns.each(function() {
                    var $btn = $( this ),
                        ts = +$btn.parent().data( 'timestamp' );

                    if ( ts && ( now - ts > maxElapsed ) ) {
                        $btn.remove();

                    }
                });
            }

            // Check every 30s.
            clearInterval( this._checkRecallTimer );
            this._checkRecallTimer = setInterval( $.proxy( this._updateRecallAbility, this ), 30 * 1000 );
        },

        _toggleEmptyParticipants: function( count ) {
            if ( count > 0 ) {
                this.$noParticipants.hide();
            } else {
                this.$noParticipants.show();
            }
        },

        showTooltip: function( e ) {
            var that = this;

            this._tooltipTimer = setTimeout(function() {
                var $el = $( e.currentTarget ),
                    tooltip = $el.data( 'tooltip' ),
                    position = $el.offset();

                if ( ! that.$tooltip ) {
                    that.$tooltip = $( '<div class="joms-tooltip joms-js-chat-tooltip" />' )
                        .appendTo( document.body );
                }

                that.$tooltip.html( tooltip )
                    .css( position )
                    .show();

                // Adjust position.
                that.$tooltip.css({
                    left: position.left - that.$tooltip.outerWidth() - 10,
                    top: position.top + ( $el.outerHeight() / 2 ),
                    transform: 'translateY(-50%)'
                });

            }, 800 );
        },

        hideTooltip: function() {
            clearTimeout( this._tooltipTimer );

            if ( this.$tooltip ) {
                this.$tooltip.hide();
            }
        },

        onScroll: function( e ) {
            var $ct, height, delta, scrollTop, scrollHeight;

            e.stopPropagation();

            $ct = this.$messages;
            delta = e.originalEvent.wheelDelta || -e.originalEvent.detail;
            scrollTop = $ct.scrollTop();

            // Reaching the top-most of the div.
            if ( delta > 0 && scrollTop <= 0 ) {

                // Try to load older messages.
                try {
                    this.getOlderMessages();
                } catch(e) {}

                return false;
            }

            height = $ct.outerHeight();
            scrollHeight = $ct[0].scrollHeight;

            // Reaching the bottom-most of the div.
            if ( delta < 0 && scrollTop >= scrollHeight - height ) {
                return false;
            }

            return true;
        }

    });

})( joms.jQuery, joms._, joms.Backbone );
