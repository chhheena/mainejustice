define('views/postbox/photo',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/photo-preview',
    'views/postbox/gif-preview',
    'views/inputbox/photo',
    'views/dropdown/mood',
    'views/widget/select',
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
    PreviewView,
    GifPreviewView,
    InputboxView,
    MoodView,
    SelectWidget,
    constants,
    language,
    Uploader
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView
        },

        template: joms.jst[ 'html/postbox/photo' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-photo-upload': 'onPhotoAdd',
            'click li[data-tab=upload]': 'onPhotoAdd',
            'click .joms-postbox-gif-upload': 'onGifAdd',
            'dragenter': 'onDragEnter',
            'dragleave #joms-postbox-photo--droparea': 'onDragLeave',
            'drop #joms-postbox-photo--droparea': 'onFileDrop'
        }),

        initialize: function() {
            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            this.enableGif = +constants.get('conf.enablephotosgif');
            
            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );
            this.$el.find(".joms-icon--emoticon").data('editor', this);
            this.$initial = this.$('.joms-postbox-inner-panel');
            this.$main = this.$('.joms-postbox-photo');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$preview = this.$('.joms-postbox-preview');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');

            this.$dropArea = this.$el.find('#joms-postbox-photo--droparea');

            if ( !this.enableMood )
                this.$tabmood.remove();

            this.$uploader = this.$('#joms-postbox-photo-upload');
            this.$uploaderParent = this.$uploader.parent();

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            // initialize uploader
            var url = joms.BASE_URL + 'index.php?option=com_community&view=photos&task=ajaxPreview',
                settings = constants.get('settings') || {};

            if ( settings.isGroup )
                url += '&no_html=1&tmpl=component&groupid=' + ( constants.get('groupid') || '' );

            if ( settings.isEvent )
                url += '&no_html=1&tmpl=component&eventid=' + ( constants.get('eventid') || '' );

            if ( settings.isPage ) 
                url += '&no_html=1&tmpl=component&pageid=' + ( constants.get('pageid') || '' ); 

            if ( $.ie ) {
                this.$uploader.appendTo( document.body );
                this.$uploader.show();
            }

            this.maxFileSize = +constants.get('conf.maxuploadsize') || 0;

            this.uploading = [];

            var upConfig = {
                container: 'joms-postbox-photo-upload',
                drop_element: 'joms-postbox-photo--droparea',
                browse_button: 'joms-postbox-photo-upload-btn',
                url: url,
                filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
                max_file_size: this.maxFileSize + 'mb'
            };

            // resizing on mobile cause errors on android stock browser!
            if ( !$.mobile )
                upConfig.resize = { width: 2100, height: 2100, quality: 90 };

            this.uploader = new Uploader( upConfig );
            this.uploader.onAdded = $.bind( this.onPhotoAdded, this );
            this.uploader.onError = $.bind( this.onPhotoError, this );
            this.uploader.onProgress = $.bind( this.onPhotoProgress, this );
            this.uploader.onUploaded = $.bind( this.onPhotoUploaded, this );
            this.uploader.init();

            if ( $.ie ) {
                this.$uploader.hide();
                this.$uploader.appendTo( this.$uploaderParent );
            }

            if ( this.enableGif ) {
                var gifConfig = {
                    container: 'joms-postbox-gif-upload',
                    browse_button: 'joms-postbox-gif-upload-btn',
                    url: url + '&gifanimation=1',
                    filters: [{ title: 'Image files', extensions: 'gif' }],
                    max_file_size: this.maxFileSize + 'mb',
                    multi_selection: false
                };

                this.gifuploader = new Uploader( gifConfig );
                this.gifuploader.onAdded = $.bind( this.onGifAdded, this );
                this.gifuploader.onError = $.bind( this.onGifError, this );
                this.gifuploader.onProgress = $.bind( this.onGifProgress, this );
                this.gifuploader.onUploaded = $.bind( this.onGifUploaded, this );
                this.gifuploader.init();
            }

            return this;
        },

        showInitialState: function() {
            this.$main.hide();
            this.$initial.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix( true );
            this.inputbox && this.inputbox.single();
            this.preview && this.preview.remove();
            this.preview = false;
            this.gifPreview && this.gifPreview.remove();
            this.gifPreview = false;
            this.showMoreButton();
            DefaultView.prototype.showInitialState.apply( this );
        },

        showMainState: function() {
            DefaultView.prototype.showMainState.apply( this );
            this.$action.hide();
            this.$initial.hide();
            this.$main.show();
            this.$save.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix();

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        showMoreButton: function() {
            this.$tabupload.removeClass('hidden invisible');
        },

        hideMoreButton: function() {
            this.$tabupload.addClass( this.subviews.mood ? 'hidden' : 'invisible' );
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.preview && this.preview.remove();
            this.preview = false;
            this.gifPreview && this.gifPreview.remove();
            this.gifPreview = false;
            this.uploading = [];
        },

        value: function() { 
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            if ( this.preview ) {
                $.extend( this.data.attachment, this.preview.value() );
            } else if ( this.gifPreview ) {
                $.extend( this.data.attachment, this.gifPreview.value() );
            }


            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {};

            if ( !attachment.id && attachment.id.length )
                return 'No image selected.';
        },

        // ---------------------------------------------------------------------
        // Photo preview event handlers.
        // ---------------------------------------------------------------------

        onPhotoAdd: function() {
            if ( this.uploading.length )
                return;

            var conf = constants.get('conf') || {},
                limit = +conf.limitphoto,
                uploaded = +conf.uploadedphoto,
                num_photo_per_upload = +conf.num_photo_per_upload,
                curr = this.preview ? this.preview.getNumPics() : 0 ;

            if ( curr >= num_photo_per_upload ) {
                window.alert( language.get('photo.batch_notice') );
                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                return;
            }

            // Opera 12 and lower (Presto engine), and IE 10, cannot open File Dialog without clicking the input[type=file] element.
            if ( window.opera || ($.ie && $.ieVersion === 10) )
                this.$('#joms-postbox-photo-upload').find('input[type=file]').click();
            else
                this.uploader.open();
        },

        onPhotoAdded: function( up, files ) {
            if ( this.uploading.length )
                return;

            if ( !(files && files.length) )
                return;

            var exts = 'jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF',
                maxFileSize = this.maxFileSize;
                
            files = _.filter( files, function(file) {
                var ex = file.name.split('.').pop();
                return _.contains( exts.split(','), ex );
            });

            files = _.filter( files, function(file) {
                return file.size <= ( maxFileSize * 1024 * 1024 );
            });

             // daily limit checking
            var conf = constants.get('conf') || {},
                limit = +conf.limitphoto,
                uploaded = +conf.uploadedphoto,
                num_photo_per_upload = +conf.num_photo_per_upload,
                curr = this.preview ? this.preview.getNumPics() : 0;

            if ( curr + files.length > num_photo_per_upload ) {
                window.alert( language.get('photo.batch_notice') );
                _.each( files, function(file) {
                    up.removeFile(file);
                })
                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                _.each( files, function(file) {
                    up.removeFile(file);
                })
                return;
            }

            var removed;
            if ( uploaded + files.length > limit ) {
                removed = uploaded + files.length - limit;
                files.splice( 0 - removed, removed );
                up.splice( 0 - removed, removed );
            }

            var div;
            if ( !this.preview ) {
                div = $('<div>').appendTo( this.$preview );
                this.preview = new PreviewView();
                this.assign( div, this.preview );
                this.listenTo( this.preview, 'update', function( num ) {
                    if ( !num || num <= 0 ) {
                        this.showInitialState();
                        this.inputbox.single();
                        this.uploading = [];
                        return;
                    } else if ( num >= num_photo_per_upload ) {
                        this.hideMoreButton();
                    } else {
                        this.showMoreButton();
                    }

                    this.inputbox[ num > 1 ? 'multiple' : 'single' ]();
                } );
            }

            this.showMainState();
            for ( var i = 0; i < files.length; i++ ) 
                this.preview.add( files[i] );

            var self = this;
            _.each( files, function(file) {
                self.uploading.push(file.id);
            });

            this.$action.hide();

            up.start();
            up.refresh();
        },

        onPhotoError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Photos: Selected file type is not permitted.' );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                window.alert( language.get('photo.max_upload_size_error') );
            } else {
                console.log( file.message );
            }
        },

        onPhotoProgress: function( up, file ) {
            this.preview && this.preview.updateProgress( file );
        },

        onPhotoUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.thumbnail ) {
                up.stop();
                up.splice();
                window.alert( json && json.msg || 'Undefined error.' );
                this.uploading = [];
                this.$action.show();
                this.preview && this.preview.removeFailed();
                return;
            }

            this.uploading = _.without( this.uploading, file.id);

            this.uploading.length === 0 && this.$action.show();

            this.preview && this.preview.setImage( file, json );
        },

        // ---------------------------------------------------------------------
        // GIF preview event handlers.
        // ---------------------------------------------------------------------

        onGifAdd: function() {
            this.hideMoreButton();
            this.gifuploader.open();
        },

        onGifAdded: function( up, files ) {
            var div;
            if ( !this.gifPreview ) {
                div = $('<div>').appendTo( this.$preview );
                this.gifPreview = new GifPreviewView();
                this.assign( div, this.gifPreview );
                this.listenTo( this.gifPreview, 'update', function( num ) {
                    if ( !num || num <= 0 ) {
                        this.showInitialState();
                        this.inputbox.single();
                        this.uploading = [];
                        return;
                    }

                    this.hideMoreButton();
                    this.inputbox.single();
                } );
            }

            this.showMainState();
            this.gifPreview.add( files[0] );

            up.start();
            up.refresh();
        },

        onGifError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Gifs: Selected file type is not permitted.' );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                window.alert( language.get('photo.max_upload_size_error') );
            } else {
                console.log( file.message );
            }
        },

        onGifProgress: function( up, file ) {
            this.gifPreview.updateProgress( file );
        },

        onGifUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.image ) {
                up.stop();
                up.splice();
                window.alert( json && json.msg || 'Undefined error.' );
                this.$action.show();
                this.gifPreview && this.gifPreview.removeFailed();
                return;
            }

            this.$action.show();
            if ( this.gifPreview )
                this.gifPreview.setImage( file, json );
        },

        // ---------------------------------------------------------------------
        // Drop event handlers.
        // ---------------------------------------------------------------------

        onDragEnter: function(e) {
            e.preventDefault();
            this.$dropArea.show();
            this.$dropArea.css('line-height', this.$dropArea.height() + 'px');
        },

        onDragLeave: function(e) {
            e.preventDefault();
            this.$dropArea.hide();
        },

        onFileDrop: function(e) {
            e.preventDefault();
            this.$dropArea.hide();
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

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                allowgif: constants.get('conf.enablephotosgif') || false,
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    photo: language.get('photo') || {}
                }
            });

            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'photo' }
            );

            return this.staticAttachment;
        },

        ieUploadButtonFix: function( initialState ) {
            if ( !this.ieUploadButtonFix.init ) {
                this.ieUploadButtonFix.init = true;
                this.$uploader.css({
                    display: 'block',
                    position: 'absolute',
                    opacity: 0,
                    width: '',
                    height: ''
                }).children('button,form').css({
                    display: 'block',
                    position: 'absolute',
                    width: '',
                    height: '',
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }).children('input').css({
                    cursor: 'pointer',
                    height: '100%'
                });
            }

            if ( initialState ) {
                this.$uploader.appendTo( this.$uploaderParent );
                this.$uploader.css({
                    top: 12,
                    right: 12,
                    bottom: 12,
                    left: 12
                }).children('form').css({
                    width: '100%',
                    height: '100%'
                });
            } else {
                this.$uploader.appendTo( this.$tabupload );
                this.$uploader.css({
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                });
            }
        }

    });

});