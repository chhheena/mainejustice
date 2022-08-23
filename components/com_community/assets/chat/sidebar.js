(function( $, _, Backbone ) {

    var util = require( './util' );

    /**
     * Conversation sidebar view.
     * @class {Backbone.View}
     */
    module.exports = Backbone.View.extend({

        el: '.joms-chat__conversations-wrapper',

        events: {
            'click .joms-chat__item': 'itemSelect',
            'wheel .joms-js-list': 'scrollSidebar',
            'keyup .joms-chat__search_conversation': 'searchConversation',
            'focus .joms-chat__search_conversation': 'showSearchResultsBox',
            'click .search-close': 'onSearchClose'
        },

        initialize: function () {
            this.$list = this.$('.joms-js-list');
            this.$loading = this.$list.find('.joms-js--chat-sidebar-loading');
            this.$notice = this.$('.joms-js-notice');
            this.$searchInput = this.$('.joms-chat__search_conversation');
            this.$searchBox = this.$('.joms-chat__search-box');
            this.$closeBtn = this.$searchBox.find('.search-close');

            this.$searchResults = this.$('.joms-chat__search-results');

            this.$groupResults = this.$searchResults.find('.joms-js__group-results');
            this.$groupLoading = this.$groupResults.next('.joms-js--chat-sidebar-loading');
                
            this.$contactResults = this.$searchResults.find('.joms-js__contact-results');
            this.$contactLoading = this.$contactResults.next('.joms-js--chat-sidebar-loading');

            this.searching = 0;
            this.no_conversation_left = false;
            this.limit = +joms.getData('message_sidebar_softlimit');

            joms_observer.add_action('chat_user_login', this.userLogin, 10, 1, this);
            joms_observer.add_action('chat_user_logout', this.userLogout, 10, 1, this);
            joms_observer.add_action('chat_conversation_render', this.renderListConversation, 1, 1, this);
            joms_observer.add_action('chat_conversation_open', this.conversationOpen, 10, 1, this);
            joms_observer.add_action('chat_update_preview_message', this.updatePreviewMessage, 10, 5, this);
            joms_observer.add_action('chat_highlight_unread_windows', this.hightlighUnreadWindows, 1, 1, this);
            joms_observer.add_action('chat_hightlight_active_window', this.highlightActiveWindow, 1, 1, this);
            joms_observer.add_action('rename_chat_title', this.renameChatTitle, 1, 1, this);
            joms_observer.add_action('chat_override_draft_chat_window', this.overrideDraftChatWindow, 1, 1, this);
            joms_observer.add_action('chat_remove_draft_conversation', this.removeDraftConversation, 1, 0, this);
            joms_observer.add_action('chat_open_first_window', this.openFirstWindow, 1, 0, this);
            joms_observer.add_action('chat_render_draft_conversation', this.renderDraftConversation, 1, 1, this);
            joms_observer.add_action('chat_open_window_by_chat_id', this.openWindowByChatId, 1, 1, this);
            joms_observer.add_action('chat_set_window_seen', this.setWindowSeen, 1, 1, this);
            joms_observer.add_action('chat_move_window_to_top', this.moveWindowToTop, 1, 1, this);
            joms_observer.add_action('chat_remove_window', this.removeWindow, 1, 1, this);
            joms_observer.add_action('chat_mute', this.muteChat, 1, 1, this);
            joms_observer.add_action('chat_all_marked_read', this.setAllWindowSeen, 1, 1, this);
            joms_observer.add_action('sidebar_change_conversation_name', this.changeConversationName, 1, 2, this);
        },

        changeConversationName: function(name, chat_id) {
            var $conv = this.$list.find('.joms-js--chat-item-'+chat_id);
            $conv.find('.joms-chat__item-body b').html(name);
        },

        /**
         * Update sidebar on login event.
         */
        userLogin: function () {
            this.$loading.hide();
            this.$notice.hide();
            this.$list.show();
        },

        /**
         * Update sidebar on logout event.
         */
        userLogout: function () {
            this.$loading.hide();
            this.$list.hide();
            this.$notice.show();
        },

        showSearchResultsBox: function() {
            this.$list.hide();
            this.$searchResults.show();
            this.$closeBtn.show();
            joms_observer.do_action('chat_hide_new_message_button');
        },

        onSearchClose: function() {
            this.hideSearchResultsBox(true);
        },

        hideSearchResultsBox: function( open ) {
            this.resetSearchResults();
            this.$searchInput.val('')
                .trigger('keyup'); // hide result results
            this.$searchResults.hide();
            this.$closeBtn.hide();
            this.$list.show();
            var $active = this.$list.find('.joms-chat__item.active');
            if ( !$active.length && open ) {
                this.openFirstWindow();
            }

            joms_observer.do_action('chat_show_new_message_button');
        },

        searchConversation: function(e) {
            var keyword = this.$searchInput.val().toLowerCase();
            
            if (keyword === this.keyword) {
                return;
            }

            this.keyword = keyword;

            this.resetSearchResults();
            if (keyword.length < 2) {
                this.$groupLoading.hide();
                this.$contactLoading.hide();
                return;
            }

            if ((e.which < 112 && e.which > 47) || e.which === 8 || e.which === 16) {
                clearTimeout(this.searching);
                
                this.$groupLoading.show();
                this.$contactLoading.show();

                var items = this.$list.find('.joms-chat__item'),
                    self = this,
                    exclusion = [],
                    state,
                    fetchtime,
                    no_contact_template,
                    no_group_template;

                _.each(items, function(item) {
                    var name = $(item).find('b').text().toLowerCase(),
                        id = $(item).data('chat-id'),
                        type = $(item).data('chat-type');

                    exclusion.push( id );

                    if (name.indexOf(keyword) === -1) {
                        return;
                    }

                    var $clone = $(item).clone();
                    $clone.removeClass('active').addClass('result-item');

                    if ( type === 'group') {
                        self.$groupResults.append( $clone );
                    } 

                    if ( type === 'single') {
                        self.$contactResults.append( $clone );
                    }
                });
                
                self._fetchTime = fetchtime = ( new Date ).getTime();
                self.searching = setTimeout( function() {
                    joms.ajax({
                        func: 'chat,ajaxSearchChat',
                        data: [ keyword, exclusion.join(',') ],
                        callback: function (json) {
                            if (self._fetchTime !== fetchtime) {
                                return;
                            }

                            if (json.error) {
                                alert(json.error)
                                return;
                            }

                            var data = {};
                            _.each( json.single, function(item) {
                                var html = self.renderSearchResult(item);
                                self.$contactResults.append(html);
                                data['chat_'+item.chat_id] = item
                            })

                            if (!json.single.length && !self.$contactResults.html()) {
                                no_contact_template = util.getTemplateById( 'joms-js-template-chat-no-contact-found' );
                                self.$contactResults.html(no_contact_template());
                            }
                            self.$contactLoading.hide()

                            _.each( json.group, function(item) {
                                var html = self.renderSearchResult(item);
                                self.$groupResults.append(html);

                                item.name = util.formatName(item.name);
                                data['chat_'+item.chat_id] = item;
                            })

                            joms_observer.do_action('chat_add_conversions', data);

                            if (!json.group.length && !self.$groupResults.html()) {
                                no_contact_template = util.getTemplateById( 'joms-js-template-chat-no-group-found' )
                                self.$groupResults.html(no_contact_template());
                            }

                            self.$groupLoading.hide()
                        }
                    });
                }, 500)
            }
        },

        renderSearchResult: function(data) {
            var template = util.getTemplateById( 'joms-js-template-chat-sidebar-search-result-item' ),
                html;

            html = template({
                id: data.chat_id,
                type: data.type,
                name: util.formatName( data.name ).replace(/<img(.*?)\/>/, ''),
                avatar: data.thumb
            });

            return html;
        },

        resetSearchResults: function() {
            this.$contactResults.html('');
            this.$groupResults.html('');
        },

        scrollSidebar: function(e) {
            var height = this.$list.height();
            var scrollHeight = this.$list[0].scrollHeight;
            var scrollTop = this.$list[0].scrollTop;
            var delta = e.originalEvent.deltaY;
            var dir = delta > 0 ? 'down' : 'up'; 
            if((scrollTop === (scrollHeight - height) && dir === 'down')) {
                e.preventDefault();
                if (!this.no_conversation_left) {
                    if ( this.$loading.is(':hidden')) {
                        this.$list.append(this.$loading);
                        this.$loading.show();

                        var ids = [];
                        var items = this.$list.find('.joms-chat__item');
                        for (var i = 0; i < items.length; i++) {
                            var item = items[i];
                            ids.push(this.$(item).attr('data-chat-id'));
                        }                  
                        this.loadMoreConversation(JSON.stringify(ids));
                    }
                }
            }

            if (scrollTop === 0 && dir === 'up') {
                e.preventDefault();
            }
        },

        loadMoreConversation: function(ids) {
            var self = this;
            joms.ajax({
                func: 'chat,ajaxInitializeChatData',
                data: [ids],
                callback: function(data) {
                    var numList = Object.keys(data.list).length;
                    if (numList) {
                        joms_observer.do_action( 'chat_conversation_render', data.list );
                        joms_observer.do_action('chat_add_conversions', data.list);
                    }

                    if (numList < self.limit) {
                        self.no_conversation_left = true;                        
                    }
                    
                    var numBudy = Object.keys(data.buddies).length;
                    if (numBudy) {
                        for( var key in data.buddies) {
                            var budy = data.buddies[key];
                            joms_observer.do_action('chat_buddy_add', budy.id, budy.name, budy.avatar);
                        }
                    }
                    self.$loading.hide();
                }
            });
        },

        muteChat: function(mute) {
            var mute_icon = [
                '<div class="joms-chat__item-actions">',
                    '<svg viewBox="0 0 16 16" class="joms-icon">',
                      '<use xlink:href="#joms-icon-close"></use>',
                    '</svg>',
                '</div>'
            ].join('');
            var active = this.$list.find('.active');
            if (mute) {
                active.find('.joms-chat__item-actions').remove();
            } else {
                active.append(mute_icon);
            }
        },

        removeWindow: function(chat_id) {
            this.$list.find('.joms-js--chat-item-'+chat_id).remove();
        },

        moveWindowToTop: function(list) {
            for (var i = 0; i < list.length; i++) {
                var $item = this.$list.find('.joms-js--chat-item-'+list[i].chat_id);
                if ($item.length) {
                    this.$list.prepend($item);
                } else {
                    // render searched item after sending message
                    var template = util.getTemplateById( 'joms-js-template-chat-sidebar-item' ),
                        html, data;

                    data = list[i];

                    html = template({
                        id: data.chat_id,
                        type: data.type,
                        name: util.formatName( data.name ).replace(/<img(.*?)\/>/, ''),
                        unread: false,
                        active: true,
                        online: data.online,
                        avatar: data.thumb
                    });

                    this.$list.prepend(html);
                }
            }

            this.hideSearchResultsBox();
        },

        setAllWindowSeen: function() {
            this.$list.find('.joms-chat__item.unread').each(function(){
                $(this).removeClass('unread');
            });
        },

        setWindowSeen: function(chat_id) {
            this.$list.find('.joms-js--chat-item-'+chat_id).removeClass('unread');
        },

        renderDraftConversation: function( data ) {
            var template = util.getTemplateById( 'joms-js-template-chat-sidebar-draft' ),
                html = template();

            this.$list.prepend( html );
            this.$list.find('.joms-js--remove-draft').on('click', function() {
                joms_observer.do_action('chat_selector_hide');
                joms_observer.do_action('chat_selector_hide');
                joms_observer.do_action('chat_selector_reset');
                joms_observer.do_action('chat_remove_draft_conversation');
                joms_observer.do_action('chat_open_first_window');
            });
        },

        openFirstWindow: function () {
            var item = this.$list.find('.joms-chat__item').first(),
                chat_id = item.data('chat-id');
            if (chat_id) {
                this.itemSetActive(item);
                joms_observer.do_action('chat_sidebar_select', item.data('chat-id'));
            }
        },

        openWindowByChatId: function(chat_id) {
            var item = this.$list.find('.joms-js--chat-item-'+chat_id);
            this.itemSetActive(item);
            joms_observer.do_action('chat_sidebar_select', chat_id);
        },

        removeDraftConversation: function () {
            this.$list.find('.joms-js--chat-item-0').remove();
        },

        overrideDraftChatWindow: function (data) {
            var item = $(this.$list.find('.active')),
                avatar = item.find('.joms-avatar img');
            item.attr('data-chat-type', data.type);
            item.attr('data-chat-id', data.chat_id);
            item.removeClass('joms-js--chat-item-0').addClass('joms-js--chat-item-' + data.chat_id);
            avatar.attr('src', data.thumb);
        },

        renameChatTitle: function (name) {
            var item = this.$list.find('.active').find('.joms-chat__item-body b');
            item.text(name);
        },

        /**
         * Render all conversation items.
         * @param {object[]} data
         */
        renderListConversation: function( data ) {
            var $startScreen = $('.joms-js-page-chat-loading'),
                $chatScreen = $('.joms-js-page-chat'),
                key;

            if ( $chatScreen.is(':hidden') ) {
                $chatScreen.show();
                $startScreen.hide();
            }

            for (key in data) {
                this.render(data[key]);
            }
        },

        /**
         * Render a conversation item.
         * @param {object} data
         */
        render: function( data ) {
            var template = util.getTemplateById( 'joms-js-template-chat-sidebar-item' ),
                isActive = false,
                isUnread = ! ( +data.seen ),
                html, $item;

            // Check if item is already exist.
            $item = this.$list.children( '.joms-js--chat-item-' + data.chat_id );
            if ( $item.length && $item.hasClass( 'active' ) ) {
                isActive = true;
                isUnread = false;
            }

            // Generate html from template.
            html = template({
                id: data.chat_id,
                type: data.type,
                name: util.formatName( data.name ).replace(/<img(.*?)\/>/, ''),
                unread: isUnread,
                active: isActive,
                online: data.online,
                avatar: data.thumb,
                mute: +data.mute,
            });

            if ( $item.length ) {
                $item.replaceWith( html );
            } else {
                this.$list.append( html );
            }
        },

        prependRender: function( data ) {
            var template = joms.getData( 'chat_page_list' ) || '',
                html;

            html = template
                .replace(/##type##/g, data.type)
                .replace(/##chat_id##/g, data.chat_id)
                .replace(/##name##/g, data.name)
                .replace(/##thumb##/g, data.thumb)
                .replace(/##unread##/g, '')
                .replace(/##mute##/g, '');

            this.$list.prepend(html);
        },

        /**
         * Show particular conversation item.
         * @param {HTMLEvent} e
         */
        itemSelect: function (e) {
            e.preventDefault();
            var $elm = $(e.currentTarget),
                chatId = $elm.data('chat-id'),
                $item = this.$list.find('.joms-js--chat-item-' + chatId);

            if ($item.length) {
                this.itemSetActive($item);
            } else {
                this.setInactiveAll();
            }

            if (this.$searchInput.val()) {
                this.$searchInput.val('');
                this.$list.find('.joms-chat__item').show();
            }

            joms_observer.do_action('chat_sidebar_select', chatId);
            if (chatId > 0) {
                joms_observer.do_action('chat_selector_hide');
            } else {
                joms_observer.do_action('chat_selector_show');
            }
        },

        /**
         * Set active item on conversation open.
         * @param {jQuery} $item
         */
        itemSetActive: function ($item) {
            $item.siblings('.active').removeClass('active');
            $item.removeClass('unread').addClass('active');
        },

        setInactiveAll: function() {
            this.$list.find('.joms-chat__item').removeClass('active');
        },

        /**
         * Handle open conversation.
         * @param {number} userId
         */
        conversationOpen: function (chatId) {
            var $item = this.$list.find('.joms-js--chat-item-' + chatId);
            if ($item.length) {
                this.itemSetActive($item);
            }
        },

        /**
         * Change display message below avatar.
         * @param {object} message
         * @param {object} active
         */
        updatePreviewMessage: function (message, active) {
            var $item;
            if (active && active.user_id) {
                $item = this.$list.find('.joms-js--chat-item-user-' + active.user_id);
                if ($item.length) {
                    $item.find('.joms-js--chat-item-msg').text(message);
                }
            }
        },

        /**
         * Highlight active sidebar item.
         * @param {Number} chat_id
         */
        highlightActiveWindow: function( chat_id ) {
            var $item = this.$list.find( '.joms-js--chat-item-' + chat_id );
            this.itemSetActive( $item );
        },

        /**
         * Highlight unread sidebar items.
         * @param {Object[]} data
         */
        hightlighUnreadWindows: function( data ) {
            _.each( data, function( item ) {
                var $item = this.$( '.joms-js--chat-item-' + item.chat_id );
                if ( ! $item.hasClass( 'active' ) ) {
                    $item.addClass( 'unread' );
                }
            }, this );
        }

    });

})( joms.jQuery, joms._, joms.Backbone );
