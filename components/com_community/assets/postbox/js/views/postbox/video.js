define('views/postbox/video',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/inputbox/videourl',
    'views/postbox/fetcher-video',
    'views/inputbox/video',
    'views/dropdown/mood',
    'views/dropdown/location',
    'views/dropdown/privacy',
    'views/widget/select',
    'utils/ajax',
    'utils/constants',
    'utils/language',
    'utils/uploader'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    UrlView,
    FetcherView,
    InputboxView,
    MoodView,
    LocationView,
    PrivacyView,
    SelectWidget,
    ajax,
    constants,
    language,
    Uploader
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            location: LocationView,
            privacy: PrivacyView
        },

        template: joms.jst[ 'html/postbox/video' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click [data-action=share]': 'onVideoUrl',
            'click [data-action=upload]': 'onVideoUpload'
        }),

        initialize: function() {
            
            this.enableUpload = +constants.get('conf.enablevideosupload');
            this.enableLocation = +constants.get('conf.enablevideosmap');

            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            if ( !this.enableLocation )
                this.subviews = $.omit( this.subviews, 'location' );

            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            this.language = {
                postbox: language.get('postbox'),
                status: language.get('status'),
                video: language.get('video')
            };

            this.onVideoInit();

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            
            DefaultView.prototype.render.apply( this );

            this.$initial = this.$('.joms-initial-panel');
            this.$main = this.$('.joms-postbox-video');
            this.$stateurl = this.$('.joms-postbox-video-state-url');
            this.$stateupload = this.$('.joms-postbox-video-state-upload');

            this.$url = this.$stateurl.find('.joms-postbox-url');
            this.$fetcher = this.$stateurl.find('.joms-postbox-fetched');
            this.$file = this.$stateupload.find('.joms-postbox-url');
            this.$fileprogress = this.$stateupload.find('.joms-postbox-photo-progress');
            this.$title = this.$stateupload.find('input.input');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tablocation = this.$tabs.find('[data-tab=location]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            if ( !this.enableMood )
                this.$tabmood.remove();

            if ( !this.enableLocation )
                this.$tablocation.remove();

            if ( this.inheritPrivacy )
                this.$tabprivacy.css({ visibility: 'hidden' });

            // url
            this.url = new UrlView();
            this.assign( this.$url, this.url );
            this.listenTo( this.url, 'focus', this.onUrlFocus );
            this.listenTo( this.url, 'keydown',  this.onUrlUpdate );
            // this.listenTo( this.url, 'blur', this.onUrlUpdate );
            // this.listenTo( this.url, 'paste', this.onUrlUpdate );

            // inputbox
            
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
                       
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            return this;
        },

        showInitialState: function() {
            this.reset();
            this.$tabs.hide();
            this.$action.hide();
            this.$save.hide();

            if ( this.enableUpload ) {
                this.$main.hide();
                this.$initial.show();
            } else {
                this.showUrlState();
            }

            this.trigger('show:initial');
        },

        showMainState: function( upload ) {
            upload ? this.showUploadState() : this.showUrlState();
            this.$tabs.show();
            this.$action.show();
            this.trigger('show:main');
        },

        showUrlState: function() {
            this.inputbox.$el.find('.joms-postbox-input-placeholder').html( this.language.status.video_hint );
            this.$save.html( this.language.postbox.post_button );
            this.$stateupload.hide();
            this.$stateurl.show();
            this.$initial.hide();
            this.$main.show();
            this.upload = false;

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        showUploadState: function() {
            var categories, options, i;

            if ( !this.uploadcat ) {
                categories = this.sortCategories( constants.get('videoCategories') || [] );
                options = [];
                for ( i = 0; i < categories.length; i++ ) {
                    options.push([ categories[i].id, this.language.video.category_label + ': ' + categories[i].name ]);
                }

                this.uploadcat = new SelectWidget({ options: options });
                this.$uploadcat = this.$stateupload.find('.joms-fetched-category');
                this.assign( this.$uploadcat, this.uploadcat );
            }

            this.inputbox.$el.find('.joms-postbox-input-placeholder').html( this.language.video.upload_hint );
            this.$save.html( this.language.postbox.upload_button );
            this.$stateurl.hide();
            this.$stateupload.show();
            this.$initial.hide();
            this.$main.show();
            this.$save.show();
            this.upload = true;

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.url && this.url.reset();
            this.inputbox && this.inputbox.reset();
            this.fetcher && this.fetcher.remove();
        },

        value: function() { 
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            // video upload
            if ( this.upload ) {
                if ( this.status && this.status.status === 'success' )
                    this.data.attachment.fetch = [ this.status.videoid ];

            // video share
            } else {
                if ( this.fetcher )
                    this.data.attachment.fetch = this.fetcher.value();
            }

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {},
                fetch = attachment.fetch;

            // video upload
            if ( this.upload ) {
                if ( !(this.uploadcat && this.uploadcat.value()) )
                    return ( language.get('video.select_category') || 'Please select video category.' );

            // video share
            } else {
                if ( !fetch || !fetch.length )
                    return ( language.get('video.invalid_url') || 'Please share a valid video url.' );
                if ( !fetch[5] )
                    return ( language.get('video.select_category') || 'Please select video category.' );
            }
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

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

            if ( this.upload === true ) {
                this.uploader.uploader.start();
                return;
            }

            var that = this;
            var data = this.value();

            // add current filters
            if ( window.joms_filter_params ) {
                data.push( JSON.stringify( window.joms_filter_params ) );
            }

            window.joms_postbox_posting = true;
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
                }
            });
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
        },

        onLocationSelect: function( location ) {
            this.inputbox.updateAttachment( null, location );
        },

        onLocationRemove: function() {
            this.inputbox.updateAttachment( null, false );
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onUrlFocus: function() {
            if ( !this.enableUpload )
                this.showMainState();
        },

        onUrlUpdate: function( text, key ) {
            var div;

            if ( text )
                text = text.replace( /^\s+|\s+$/g, '' );

            // triggers fetch content on spacebar and return keys
            if ( key === 32 || key === 13 ) {
                this.fetch( text );
            } else {
                this.fetchProxy( text );
            }
        },

        onInputFocus: function() {
            if ( !this.enableUpload )
                this.showMainState();
        },

        // ---------------------------------------------------------------------
        // Video preview event handlers.
        // ---------------------------------------------------------------------

        fetchProxy: $.debounce(function( text ) {
            this.fetch( text );
        }, 1000 ),

        fetch: function( text ) {
            var div;

            if ( this.fetcher && (this.fetcher.fetching || this.fetcher.fetched) )
                return;

            delete this.data.attachment.fetch;

            div = $('<div>').appendTo( this.$fetcher );
            this.fetcher && this.fetcher.remove();
            this.fetcher = new FetcherView();
            this.fetcher.setElement( div );
            this.listenTo( this.fetcher, 'fetch:start', this.onFetchStart );
            this.listenTo( this.fetcher, 'fetch:failed', this.onFetchFailed );
            this.listenTo( this.fetcher, 'fetch:done', this.onFetchDone );
            this.listenTo( this.fetcher, 'remove', this.onFetchRemove );
            this.fetcher.fetch( text );
        },

        onFetchStart: function() {
            this.saving = true;
            this.$loading.show();
            this.$save.hide();
        },

        onFetchFailed: function( resp ) {
            this.fetcher && this.fetcher.remove();
            this.saving = false;
            this.$loading.hide();
            this.$save.hide();

            var msg = resp && resp.msg || 'Undefined error.';
            window.alert( msg );
        },

        onFetchDone: function() {
            this.saving = false;
            this.$loading.hide();
            if ( this.fetcher && this.fetcher.fetched )
                this.$save.show();
        },

        onFetchRemove: function() {
            this.fetcher = false;
            this.$save.hide();
        },

        // ---------------------------------------------------------------------
        // Video upload event handlers.
        // ---------------------------------------------------------------------

        onVideoUrl: function() {
            this.showMainState();
        },

        onVideoInit: function() {
            if ( this.uploader ) {
                return;
            }

            var maxFileSize = +constants.get('conf.maxvideouploadsize') || 0;
            if ( maxFileSize ) {
                maxFileSize += 'mb';
            }

            var url = 'index.php?option=com_community&view=videos&task=uploadvideo';
            var creatortype = constants.get('videocreatortype');
            var settings = constants.get('settings') || {};
            var groupid = +constants.get('groupid');
            var pageid = +constants.get('pageid');
            var eventid = +constants.get('eventid');

            if ( creatortype ) {
                url += '&creatortype=' + creatortype;
            }

            if ( settings.isProfile && !settings.isMyProfile ) {
                url += '&target=' + constants.get('postbox.attachment.target');
            } else if ( +eventid > 0 ) {
                url += '&eventid=' + eventid;
            } else if ( +groupid > 0 ) {
                url += '&groupid=' + groupid;
            } else if ( +pageid > 0 ) {
                url += '&pageid=' + pageid;
            }

            this.uploader = new Uploader({
                container: 'joms-js--videouploader-upload',
                browse_button: 'joms-js--videouploader-upload-button',
                url: url,
                multi_selection: false,
                filters: [{ title: 'Video files', extensions: '3g2,3gp,asf,asx,avi,flv,mov,mp4,mpg,rm,swf,vob,wmv,m4v' }],
                max_file_size: maxFileSize
            });

            // uploader events
            this.uploader.onAdded = $.bind( this.onVideoAdded, this );
            this.uploader.onError = $.bind( this.onVideoError, this );
            this.uploader.onBeforeUpload = $.bind( this.onVideoBeforeUpload, this );
            this.uploader.onProgress = $.bind( this.onVideoProgress, this );
            this.uploader.onUploaded = $.bind( this.onVideoUploaded, this );
            this.uploader.init();
        },

        onVideoUpload: function() {
            var conf = constants.get('conf') || {},
                limit = +conf.limitvideo,
                uploaded = +conf.uploadedvideo;

            if ( uploaded >= limit ) {
                window.alert( language.get('video.upload_limit_exceeded') || 'You have reached the upload limit.' );
                return;
            }

            this.uploader.uploader.splice();
            this.uploader.open();
        },

        onVideoAdded: function( up, files ) {
            if ( !(files && files.length) )
                return;

            var file = files[0],
                name = '<b>' + file.name + '</b>',
                size = file.size || 0,
                unit = 'Bytes';

            for ( var units = [ 'KB', 'MB', 'GB' ]; size >= 1000 && units.length; ) {
                unit = units.shift();
                size = Math.ceil( size / 1000 );
            }

            if ( size )
                name += ' (' + size + ' ' + unit + ')';

            this.$file.html( name );
            this.$fileprogress.css({ width: 0 });
            this.showMainState('upload');
        },

        onVideoError: function( up, file ) {
            var tmp;
            if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                tmp = +constants.get('conf.maxvideouploadsize') || 0;
                window.alert( 'Maximum file size for video upload is ' + tmp + ' MB.' );
            } else if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Selected file type is not permitted.' );
            }
        },

        onVideoBeforeUpload: function() {
            var params = {
                title : this.$title.val(),
                description : this.inputbox.value()
            };

            if ( this.subflags.privacy )
                params.permissions = this.subviews.privacy.value();

            var location = this.subflags.location && this.subviews.location.value();
            if ( location && location.length )
                params.location = location;

            if ( this.uploadcat )
                params.category_id = this.uploadcat && this.uploadcat.value();

            this.uploader.params( params );
        },

        onVideoProgress: function( up, file ) {
            var percent = Math.min( 100, Math.round( 100 * file.loaded / file.size ) );
            this.$fileprogress.animate({ width: percent + '%' });
        },

        onVideoUploaded: function( up, file, info ) {
            var json, that;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            this.status = json || {};
            that = this;
            setTimeout(function() {
                that.$loading.hide();
                that.saving = false;
                that.showInitialState();

                if ( that.status.status !== 'success' ){
                    window.alert( that.status.message || 'Undefined error.' );
                } else {
                    var conf = constants.get('conf') || {};
                    ++conf.uploadedvideo;
                    window.alert( that.status.processing_str );
                }
            }, 1000 );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                enable_upload: this.enableUpload,
                video_maxsize: constants.get('conf.maxvideouploadsize'),
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    video: language.get('video') || {}
                }
            });

            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'video' }
            );

            return this.staticAttachment;
        },

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
        }

    });

});