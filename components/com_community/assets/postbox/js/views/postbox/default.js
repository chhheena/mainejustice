define('views/postbox/default',[
    'sandbox',
    'app',
    'views/base',
    'utils/ajax',
    'utils/constants'
],

// definition
// ----------
function( $, App, BaseView, ajax, constants ) {

    return BaseView.extend({

        subviews: {},

        // @abstract
        template: function() {
            throw new Error('Method not implemented.');
        },

        events: {
            'click li[data-tab]': 'onToggleDropdown',
            'click button.joms-postbox-cancel': 'onCancel',
            'click button.joms-postbox-save': 'onPost',
            'click .joms-postbox-input': 'inputFocus'
        },

        initialize: function( options ) {
            if ( options && options.single )
                this.single = true;

            this.subflags = {};
            this.reset();
        },

        render: function() {
            var div = this.getTemplate();
            this.$el.replaceWith( div );
            this.setElement( div );

            this.$tabs = this.$('.joms-postbox-tab');
            this.$action = this.$('.joms-postbox-action').hide();
            this.$loading = this.$('.joms-postbox-loading').hide();
            this.$save = this.$('.joms-postbox-save').hide();

            return this;
        },

        show: function() {
            this.showInitialState();
            BaseView.prototype.show.apply( this );
        },

        showInitialState: function() {
            this.reset();
            this.$tabs.hide();
            this.$action.hide();
            this.$save.hide();
            this.trigger('show:initial');
        },

        showMainState: function() {
            this.$tabs.show();
            this.$action.show();
            this.trigger('show:main');
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            this.data = {};
            this.data.text = '';
            this.data.attachment = {};
            for ( var prop in this.subflags ) {
                this.subviews[ prop ].reset();
                this.subviews[ prop ].hide();
            }
        },

        value: function( noEncode ) {
            var attachment = $.extend({}, this.getStaticAttachment(), this.data.attachment );

            // DEBUGGING PURPOSE
            // if ( !noEncode ) {
            //  console.log( this.data.text );
            //  console.log( attachment );
            // }

            return [
                this.data.text,
                noEncode ? attachment : JSON.stringify( attachment )
            ];
        },

        inputFocus: function(e) {
            this.$(e.currentTarget)
                .find('.joms-textarea__wrapper')
                .find('.input.joms-textarea').focus();

            this.inputbox.trigger('focus');
        },

        // Data validation method, truthy return value will raise error.
        // Go to `this.onPost` to see how this method is used.
        validate: $.noop,

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onToggleDropdown: function( e ) {
            var elem = $( e.currentTarget );
            if ( elem.data('bypass') )
                return;

            var type = elem.data('tab');
            if ( !this.subviews[ type ] )
                return;

            if ( !this.subflags[ type ] )
                this.initSubview( type );

            if ( !this.subviews[ type ].isHidden() ) {
                this.subviews[ type ].hide();
                return;
            }

            for ( var prop in this.subflags )
                if ( prop !== type )
                    this.subviews[ prop ].hide();

            this.subviews[ type ].show();
        },

        onCancel: function() {
            if ( App.postbox && App.postbox.value )
                App.postbox.value = false;

            if ( !this.saving )
                this.showInitialState();
        },

        onPost: function() {
            if ( this.saving )
                return;

            var error = this.validate();
            if ( error ) {
                window.alert( error );
                return;
            }

            this.saving = true;
            this.$loading.show();

            var that = this;
            var data = this.value();

            // add current filters
            if ( window.joms_filter_params ) {
                data.push( JSON.stringify( window.joms_filter_params ) );
            }

            window.joms_postbox_posting = true;
            var self = this;
            ajax({
                fn: 'system,ajaxStreamAdd',
                data: data,
                success: $.bind( this.onPostSuccess, this ),
                complete: function() {
                    window.joms_postbox_posting = false;
                    that.$loading.hide();
                    that.saving = false;
                    that.showInitialState();

                    if (+window.joms_infinitescroll) {
                        $('.joms-stream__loadmore').find('a').hide()
                    }

                    self.initVideoPlayers();
                }
            });
        },

        initVideoPlayers: function () {
            var initialized = '.joms-js--initialized',
                cssVideos = '.joms-js--video',
                videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

            if ( !videos.length ) {
                return;
            }

            joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
            videos.on( 'click.joms-video', cssVideos + '-play', function() {
                var $el = $( this ).closest( cssVideos );
                joms.util.video.play( $el, $el.data() );
            });
        },

        onPostSuccess: function( response ) {
            var html = this.parseResponse( response ),
                stream;

            if ( html ) {
                stream = $('.joms-stream__wrapper').first();
                stream.html( html );

                // reset postbox to default
                $.trigger('postbox:status');

                // reinitialize activity stream
                if ( window.joms && joms.view && joms.view.streams ) {
                    joms.view.streams.start();
                    joms.view.misc.fixSVG();
                }
            }
        },

        // ---------------------------------------------------------------------
        // Lazy subview initialization.
        // ---------------------------------------------------------------------

        initSubview: function( type, options ) {
            var Type = type.replace( /^./, function( chr ){ return chr.toUpperCase(); });
            if ( !this.subflags[ type ] ) {
                this.subviews[ type ] = new this.subviews[ type ]( options );
                this.assign( this.getSubviewElement(), this.subviews[ type ] );
                this.listenTo( this.subviews[ type ], 'init', this[ 'on' + Type + 'Init' ] );
                this.listenTo( this.subviews[ type ], 'show', this[ 'on' + Type + 'Show' ] );
                this.listenTo( this.subviews[ type ], 'hide', this[ 'on' + Type + 'Hide' ] );
                this.listenTo( this.subviews[ type ], 'select', this[ 'on' + Type + 'Select' ] );
                this.listenTo( this.subviews[ type ], 'remove', this[ 'on' + Type + 'Remove' ] );
                this.subflags[ type ] = true;
            }
        },

        getSubviewElement: function() {
            var div = $('<div>').hide().appendTo( this.$el );
            return div;
        },

        // ---------------------------------------------------------------------
        // Ajax response parser.
        // ---------------------------------------------------------------------

        parseResponse: function( response ) {
            var elid = 'activity-stream-container',
                data, temp;

            if ( response.html ) {
                return response.html;
            }

            if ( response && response.length ) {
                for ( var i = 0; i < response.length; i++ ) {
                    if ( response[i][1] === '__throwError' || response[i][0] === 'al') {
                        temp = response[i][3];
                        window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
                    }
                    if ( !data && ( response[i][1] === elid) ) {
                        data = response[i][3];
                    }
                }
            }

            return data;
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({ juri: constants.get('juri') });
            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: '' }
            );

            return this.staticAttachment;
        }

    });

});