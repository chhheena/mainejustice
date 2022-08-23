define('views/postbox/layout',[
    'sandbox',
    'views/base',
    'views/postbox/status',
    'views/postbox/photo',
    'views/postbox/video',
    'views/postbox/event',
    'views/postbox/file',
    'views/postbox/poll',
    'views/postbox/custom',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function(
    $,
    BaseView,
    StatusView,
    PhotoView,
    VideoView,
    EventView,
    FileView,
    PollView,
    CustomView,
    constants,
    language
) {

    return BaseView.extend({

        subflags: {},

        subviews: {
            status: StatusView,
            photo: PhotoView,
            video: VideoView,
            event: EventView,
            file: FileView,
            poll: PollView,
            custom: CustomView
        },

        events: {
            'click .joms-postbox-tab-root li': 'onChangeTab'
        },

        initialize: function() {
            this.listenTo( $, 'postbox:status', this.onOpenStatusTab );
            this.listenTo( $, 'postbox:photo', this.onOpenPhotoTab );
            this.listenTo( $, 'postbox:video', this.onOpenVideoTab );
            this.listenTo( $, 'postbox:file', this.onOpenFileTab );
            this.listenTo( $, 'postbox:poll', this.onOpenPollTab );
        },

        render: function() {
            var settings = constants.get('settings') || {},
                conf = constants.get('conf') || {};

            if ( !settings.isAdmin || !conf.enablecustoms )
                this.subviews = $.omit( this.subviews, 'custom' );

            if ( settings.isProfile && !settings.isMyProfile )
                this.subviews = $.pick( this.subviews, 'status', 'photo', 'video', 'file', 'poll' );

            if ( settings.isEvent )
                this.subviews = $.omit( this.subviews, 'event' );

            if ( settings.isProfile || settings.isGroup || settings.isEvent || settings.isPage ) {
                conf.enablephotos || (this.subviews = $.omit( this.subviews, 'photo' ));
                conf.enablevideos || (this.subviews = $.omit( this.subviews, 'video' ));
                conf.enableevents || (this.subviews = $.omit( this.subviews, 'event' ));
                conf.enablefiles  || (this.subviews = $.omit( this.subviews, 'file' ));
                conf.enablepolls  || (this.subviews = $.omit( this.subviews, 'poll' ));
            }

            // cache subview keys
            this.subkeys = $.keys( this.subviews );

            // cache elements
            this.$subviews = this.$('.joms-postbox-tabs');
            this.$tab = this.$('.joms-postbox-tab-root').hide();

            // remove unused tab
            var that = this;
            this.$tab.find('li').each(function() {
                var elem = $( this ),
                    key = elem.data('tab');

                if ( that.subkeys.indexOf( key ) < 0 )
                    elem.remove();
            });

            if ( this.subkeys && this.subkeys.length ) 
                this.changeTab( this.subkeys[0] );
        },

        show: function() {
            this.$el[ $.isMobile ? 'show' : 'fadeIn' ]();
        },

        changeTab: function( type ) {
            if ( !this.subviews[ type ] )
                return;

            var elem = this.$tab.find( 'li[data-tab=' + type + ']' );
            if ( elem && elem.length ) {
                elem.hasClass('active') || elem.addClass('active');
                elem.siblings('.active').removeClass('active');
            }

            if ( !this.subflags[ type ] )
                this.initSubview( type );

            for ( var prop in this.subflags )
                if ( prop !== type )
                    this.subviews[ prop ].hide();

            this.subviews[ type ].show();
            this.type = type;
            $.trigger( 'postbox:tab:change', type );
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onChangeTab: function( e ) {
            this.changeTab( $( e.currentTarget ).data('tab') );
        },

        onOpenStatusTab: function() {
            this.changeTab('status');
        },

        onOpenPhotoTab: function() {
            this.changeTab('photo');
        },

        onOpenVideoTab: function() {
            this.changeTab('video');
        },

        onOpenFileTab: function() {
            this.changeTab('file');
        },

        onOpenPollTab: function() {
            this.changeTab('poll');
        },

        onShowInitialState: function() {
            if ( this.subkeys.length > 1 )
                this.$tab.show();
        },

        onShowMainState: function() {
            this.$tab.hide();
        },

        // ---------------------------------------------------------------------
        // Lazy subview initialization.
        // ---------------------------------------------------------------------

        initSubview: function( type ) {
            if ( !this.subflags[ type ] ) {
                this.subviews[ type ] = new this.subviews[ type ]({ single: this.subkeys.length <= 1 });
                this.assign( this.getSubviewElement(), this.subviews[ type ] );
                this.listenTo( this.subviews[ type ], 'show:initial', this.onShowInitialState );
                this.listenTo( this.subviews[ type ], 'show:main', this.onShowMainState );
                this.subflags[ type ] = true;
            }
        },

        getSubviewElement: function() {
            var div = $('<div>').hide().appendTo( this.$subviews );
            return div;
        }

    });

});