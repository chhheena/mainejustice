define('views/postbox/poll', [
    'sandbox',
    'app',
    'views/postbox/default',
    'views/inputbox/poll',
    'views/widget/select',
    'views/postbox/poll-options',
    'views/postbox/poll-settings',
    'views/postbox/poll-time',
    'views/dropdown/privacy',
    'utils/constants',
    'utils/language'
],

function(
    $,
    App,
    DefaultView,
    InputboxView,
    SelectWidget,
    OptionsView,
    SettingsView,
    PollTimeView,
    PrivacyView,
    constants,
    language
) {
    return DefaultView.extend({
        
        subviews: {
            privacy: PrivacyView
        },

        template: _.template(
            '<div class="joms-postbox-poll">\
                <div class="joms-postbox-inner-panel">\
                    <div class="joms-postbox-inputbox" style=""></div> \
                </div>\
                <div class="joms-postbox-inner-panel">\
                    <div class=joms-postbox-poll-options></div>\
                    <div class=joms-postbox-poll-settings></div>\
                    <div class="joms-postbox-poll-category joms-fetched-category joms-select"></div>\
                    <div class="joms-postbox__poll-time"></div>\
                </div>\
                <nav class="joms-postbox-tab selected"> \
                    <ul class="joms-list inline"> \
                        <li data-tab=privacy>\
                            <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-earth"></use></svg> \
                            <span class=visible-desktop></span>\
                        </li>\
                    </ul> \
                    <div class=joms-postbox-action> \
                        <button class=joms-postbox-cancel><%= language.postbox.cancel_button %></button> \
                        <button class=joms-postbox-save><%= language.postbox.post_button %></button> \
                    </div> \
                    <div class=joms-postbox-loading style="display:none;"> \
                        <img src="<%= juri.root%>components/com_community/assets/ajax-loader.gif" alt="loader"> \
                    </div> \
                </nav>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                language: {
                    postbox: language.get('postbox') || {}
                }
            });
            return $(html).hide();
        },

        initialize: function() {
            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');
            this.$tabpolltime = this.$tabs.find('[data-tab=polltime]');
            this.$options = this.$('.joms-postbox-poll-options');
            this.$settings = this.$('.joms-postbox-poll-settings');
            this.$category = this.$('.joms-postbox-poll-category');
            this.$polltime = this.$('.joms-postbox__poll-time');

            if ( this.inheritPrivacy ) {
                if ( this.$tabprivacy.next().length )
                    this.$tabprivacy.remove();
                else
                    this.$tabprivacy.css({ visibility: 'hidden' });
            }

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );
            this.listenTo( this.inputbox, 'keydown', this.onInputUpdate );
            this.listenTo( this.inputbox, 'paste', this.onInputUpdate );

            // category
            var pollCats = this.sortCategories( constants.get('pollCategories') );
            var pollOptions = pollCats.map( function(item) {
                return [ item.id, item.name];
            });
            this.category = new SelectWidget({ options: pollOptions });
            this.assign( this.$category, this.category );

            // options
            this.options = new OptionsView();
            this.assign( this.$options, this.options) 

            // settings
            this.settings = new SettingsView();
            this.assign( this.$settings, this.settings );

            // poll time
            this.polltime = new PollTimeView();
            this.assign( this.$polltime, this.polltime);

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            if ( this.single )
                this.listenTo( $, 'click', this.onDocumentClick );

            return this;
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.options && this.options.reset();
            this.settings && this.settings.reset();
            this.polltime && this.polltime.reset();
            this.staticAttachment = null;
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            this.data.attachment = {};
            this.data.attachment.polltime = this.polltime.value();
            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            this.data.attachment.catid = this.category.value();

            return DefaultView.prototype.value.apply( this );
        },

        validate: function() {
            var options = this.options.value(),
                text = this.inputbox.value(),
                catid = this.category.value(),
                polltime = this.polltime.value(),
                poll_lang = language.get('poll');
            
            if (!text.trim()) {
                return poll_lang.no_title;
            }

            if (options.options.length < 2) {
                return poll_lang.not_enough_option;
            }

            if (!catid) {
                return poll_lang.no_category;
            }

            if (!polltime) {
                return poll_lang.no_expiry_date;
            }

            if (!polltime.enddate) {
                return poll_lang.no_expiry_date;
            }

            if (!polltime.endtime) {
                return poll_lang.no_expiry_time;
            }

            return null;
        },

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onInputFocus: function() {
            this.showMainState();
        },

        onInputUpdate: function( text, key ) {
            var div;

            text = text || '';
            this.togglePostButton( text );
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        sortCategories: function( categories, parent, prefix ) {
            if ( !categories || !categories.length )
                return [];

            parent || (parent = 0);
            prefix || (prefix = '');

            var options = [];
            for ( var i = 0, id, name; i < categories.length; i++ ) {
                if ( +categories[i].parent === parent ) {
                    id = +categories[i].id;
                    name = prefix + categories[i].name;
                    options.push({ id: id, name: name });
                    options = options.concat( this.sortCategories( categories, id, name + ' &rsaquo; ' ) );
                }
            }

            return options;
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'poll' },
                this.options.value(),
                this.settings.value()
            );

            return this.staticAttachment;
        },

        togglePostButton: function( text ) {
            var enabled = false;

            if ( text )
                enabled = true;

            if ( !enabled && this.subflags.mood )
                enabled = this.subviews.mood.value();

            if ( !enabled && this.subflags.location )
                enabled = this.subviews.location.value();

            this.$save[ enabled ? 'show' : 'hide' ]();
        }

    });
});