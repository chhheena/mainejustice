define('views/postbox/status',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/fetcher',
    'views/inputbox/status',
    'views/dropdown/mood',
    'views/dropdown/location',
    'views/dropdown/privacy',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    FetcherView,
    InputboxView,
    MoodView,
    LocationView,
    PrivacyView,
    constants,
    language
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            location: LocationView,
            privacy: PrivacyView
        },

        template: _.template(
            '<div class=joms-postbox-status>\
                <div class=joms-postbox-fetched></div>\
                <div class=joms-postbox-inputbox></div>\
                <nav class="joms-postbox-tab selected">\
                    <ul class="joms-list inline">\
                        <li data-tab=mood><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-happy"></use></svg></li>\
                        <li data-tab=location><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-location"></use></svg></li>\
                        <li data-tab=privacy><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-earth"></use></svg> </li>\
                        <% if (enablephoto) { %>\
                        <li data-tab=photo data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-camera"></use></svg></li>\
                        <% } %>\
                        <% if (enablevideo) { %>\
                        <li data-tab=video data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-play"></use></svg></li>\
                        <% } %>\
                        <% if (enablefile) { %>\
                        <li data-tab=file data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip"></use></svg></li>\
                        <% } %>\
                        <% if (enablepoll) { %>\
                        <li data-tab=poll data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-list"></use></svg></li>\
                        <% } %>\
                    </ul>\
                    <div class=joms-postbox-action>\
                        <button class=joms-postbox-cancel><%= language.postbox.cancel_button %></button>\
                        <button class=joms-postbox-save><%= language.postbox.post_button %></button>\
                    </div>\
                    <div class=joms-postbox-loading style="display:none;">\
                        <img src="<%= juri.root %>components/com_community/assets/ajax-loader.gif" alt="loader">\
                    <div>\
                </nav>\
            </div>'            
        ),

        getTemplate: function() {
            var settings = constants.get('settings') || {},
                conf = constants.get('conf') || {},
                enablephoto = true,
                enablevideo = true,
                enablefile = true,
                enablepoll = true;

            if ( settings.isProfile || settings.isGroup || settings.isEvent || settings.isPage ) {
                conf.enablephotos || (enablephoto = false);
                conf.enablevideos || (enablevideo = false);
                conf.enablefiles || (enablefile = false);
                conf.enablepolls || (enablepoll = false);
            }

            var html = this.template({
                juri: constants.get('juri'),
                enablephoto: enablephoto,
                enablevideo: enablevideo,
                enablefile: enablefile,
                enablepoll: enablepoll,
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {}
                }
            });

            return $( html ).hide();
        },

        events: $.extend({}, DefaultView.prototype.events, {
            'click li[data-tab=photo]': 'onAddPhoto',
            'click li[data-tab=video]': 'onAddVideo',
            'click li[data-tab=file]': 'onAddFile',
            'click li[data-tab=poll]': 'onAddPoll'
        }),

        initialize: function() {
            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            this.enableLocation = +constants.get('conf.enablelocation');
            if ( !this.enableLocation )
                this.subviews = $.omit( this.subviews, 'location' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$fetcher = this.$('.joms-postbox-fetched');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tablocation = this.$tabs.find('[data-tab=location]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            if ( !this.enableMood )
                this.$tabmood.remove();

            if ( !this.enableLocation )
                this.$tablocation.remove();

            if ( this.inheritPrivacy ) {
                if ( this.$tabprivacy.next().length )
                    this.$tabprivacy.remove();
                else
                    this.$tabprivacy.css({ visibility: 'hidden' });
            }

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true, status: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );
            this.listenTo( this.inputbox, 'keydown', this.onInputUpdate );
            this.listenTo( this.inputbox, 'paste', this.onInputUpdate );
            this.listenTo( this.inputbox, 'change:type', this.onInputChangeType );

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
            this.fetcher && this.fetcher.remove();
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            this.data.attachment = {};

            if (this.inputbox.colorful) {
                this.data.attachment.colorful = true;
                this.data.attachment.bgid = this.inputbox.bgid;
            }

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            if ( this.fetcher )
                this.data.attachment.fetch = this.fetcher.value();

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: $.noop,

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onInputChangeType: function(type) {
            if (type) {
                this.$tabmood.attr('style', 'display: none !important');
                this.$tablocation.attr('style', 'display: none !important');
            } else {
                this.$tabmood.attr('style', '');
                this.$tablocation.attr('style', '');
            }
        },

        onInputFocus: function() {
            this.showMainState();
        },

        onInputUpdate: function( text, key ) {
            var div;

            text = text || '';
            this.togglePostButton( text );

            if ( key === 32 || key === 13 ) {
                this.fetch( text );
            } else {
                this.fetchProxy( text );
            }
        },

        // ---------------------------------------------------------------------
        // Fetching event handler.
        // ---------------------------------------------------------------------

        fetchProxy: $.debounce(function( text ) {
            this.fetch( text );
        }, 1000 ),

        fetch: function( text ) {
            var div;

            if ( this.fetcher && (this.fetcher.fetching || this.fetcher.fetched) )
                return;

            if ( this.fetcher )
                this.fetcher.remove();

            div = $('<div>').appendTo( this.$fetcher );
            this.fetcher = new FetcherView();
            this.fetcher.setElement( div );
            this.listenTo( this.fetcher, 'fetch:start', this.onFetchStart );
            this.listenTo( this.fetcher, 'fetch:done', this.onFetchDone );
            this.listenTo( this.fetcher, 'remove', this.onFetchRemove );
            this.fetcher.fetch( text.replace( /^\s+|\s+$/g, '' ) );
        },

        onFetchStart: function() {
            this.saving = true;
            this.$loading.show();
        },

        onFetchDone: function() {
            this.$loading.hide();
            this.saving = false;
        },

        onFetchRemove: function() {
            this.fetcher = false;
        },

        onDocumentClick: function( elem ) {
            if ( elem.closest('.joms-postbox').length )
                return;

            var text = this.inputbox.value();
            text = text.replace( /^\s+|\s+$/g, '' );
            if ( !text )
                this.showInitialState();
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
            this.togglePostButton();
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
            this.togglePostButton();
        },

        onLocationSelect: function( location ) {
            this.inputbox.updateAttachment( null, location );
            this.togglePostButton();
        },

        onLocationRemove: function() {
            this.inputbox.updateAttachment( null, false );
            this.togglePostButton();
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Add photo/video event handlers.
        // ---------------------------------------------------------------------

        onAddPhoto: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:photo' );
        },

        onAddVideo: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:video' );
        },

        onAddFile: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:file' );
        },

        onAddPoll: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:poll' );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'message' }
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