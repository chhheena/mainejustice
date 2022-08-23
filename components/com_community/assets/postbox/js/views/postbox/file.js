define('views/postbox/file',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/file-preview',
    'views/inputbox/file',
    'views/dropdown/mood',
    'views/dropdown/privacy',
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
    InputboxView,
    MoodView,
    PrivacyView,
    constants,
    language,
    Uploader
) {
    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            privacy: PrivacyView
        },

        template: _.template(
            '<div class="joms-postbox-file-wrapper" style="position: relative;">\
                <div class="joms-postbox--droparea" id="joms-postbox-file--droparea">\
                    <%= language.file.drop_to_upload %>\
                </div>\
                <div id=joms-postbox-file-upload style="position:absolute;top:0;left:0;width:1px;height:1px;overflow:hidden">\
                    <button id=joms-postbox-file-upload-btn>Upload</button>\
                </div>\
                <div class="joms-postbox-inner-panel" style="position:relative">\
                    <div class="joms-postbox-file-upload">\
                        <svg viewBox="0 0 16 18" class="joms-icon">\
                            <use xlink:href="<%=window.joms_current_url%>#joms-icon-file-zip" class="joms-icon--svg-fixed joms-icon--svg-unmodified"></use>\
                        </svg>\
                         <%= language.file.upload_button %>\
                    </div>\
                </div>\
                <div class="joms-postbox-file">\
                    <div class="joms-postbox-preview"></div>\
                    <div class=joms-postbox-inputbox></div>\
                    <nav class="joms-postbox-tab selected"> \
                        <ul class="joms-list inline"> \
                            <li data-tab=upload data-bypass=1>\
                                <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip"></use></svg> \
                                <span class=visible-desktop><%= language.file.upload_button_more %></span>\
                            </li> \
                            <li data-tab=mood>\
                                <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-happy"></use></svg> \
                                <span class=visible-desktop><%= language.status.mood %></span>\
                            </li> \
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
                </div>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    file: language.get('file') || {},
                    privacy: language.get('privacy') || {}
                }
            });

            return $( html ).hide();
        },

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-file-upload': 'onFileAdd',
            'click li[data-tab=upload]': 'onFileAdd',
            'dragenter': 'onDragEnter',
            'dragleave #joms-postbox-file--droparea': 'onDragLeave',
            'drop #joms-postbox-file--droparea': 'onFileDrop'
        }),

        initialize: function() {
            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$initial = this.$('.joms-postbox-inner-panel');
            this.$main = this.$('.joms-postbox-file');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$preview = this.$('.joms-postbox-preview');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            this.$dropArea = this.$el.find('#joms-postbox-file--droparea');

            if ( !this.enableMood )
                this.$tabmood.remove();

            this.$uploader = this.$('#joms-postbox-photo-upload');
            this.$uploaderParent = this.$uploader.parent();

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            this.uploading = [];

            // initialize uploader
            var url = joms.BASE_URL + 'index.php?option=com_community&view=files&task=multiUpload&type=activities',
                settings = constants.get('settings') || {};

            if ( settings.isGroup )
                url += '&no_html=1&tmpl=component&groupid=' + ( constants.get('groupid') || '' );

            if ( settings.isEvent )
                url += '&no_html=1&tmpl=component&eventid=' + ( constants.get('eventid') || '' ); 

            if ( settings.isPage ) 
                url += '&no_html=1&tmpl=component&pageid=' + ( constants.get('pageid') || '' ); 

            if ( this.inheritPrivacy )
                this.$tabprivacy.css({ visibility: 'hidden' });

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            if ( $.ie ) {
                this.$uploader.appendTo( document.body );
                this.$uploader.show();
            }

            var conf = constants.get('conf') || {};
            this.maxFileSize = 1;
            this.maxFileSize = +constants.get('settings.isProfile') ? conf.file_sharing_activity_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isPage') ? conf.file_sharing_page_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isGroup') ? conf.file_sharing_group_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isEvent') ? conf.file_sharing_event_max : this.maxFileSize;

            this.ext = 'zip';
            this.ext = +constants.get('settings.isProfile') ? conf.file_activity_ext : this.ext;
            this.ext = +constants.get('settings.isPage') ? conf.file_page_ext : this.ext;
            this.ext = +constants.get('settings.isGroup') ? conf.file_group_ext : this.ext;
            this.ext = +constants.get('settings.isEvent') ? conf.file_event_ext : this.ext;

            var upConfig = {
                container: 'joms-postbox-file-upload',
                drop_element: 'joms-postbox-file--droparea',
                browse_button: 'joms-postbox-file-upload-btn',
                url: url,
                filters: [{ title: 'File files', extensions: this.ext }],
                max_file_size: this.maxFileSize + 'mb'
            };

            // resizing on mobile cause errors on android stock browser!
            if ( !$.mobile )
                upConfig.resize = { width: 2100, height: 2100, quality: 90 };

            this.uploader = new Uploader( upConfig );
            this.uploader.onAdded = $.bind( this.onFileAdded, this );
            this.uploader.onError = $.bind( this.onFileError, this );
            this.uploader.onProgress = $.bind( this.onFileProgress, this );
            this.uploader.onUploaded = $.bind( this.onFileUploaded, this );
            this.uploader.init();

            if ( $.ie ) {
                this.$uploader.hide();
                this.$uploader.appendTo( this.$uploaderParent );
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
            }

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {};

            if ( !attachment.id && attachment.id.length )
                return 'No image selected.';
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // File preview event handlers.
        // ---------------------------------------------------------------------

        onFileAdd: function() {
            if ( this.uploading.length )
                return;

            var conf = constants.get('conf') || {},
                limit = +conf.limitfile,
                uploaded = +conf.uploadedfile,
                num_file_per_upload = +conf.num_file_per_upload,
                curr = this.preview ? this.preview.getNumFiles() : 0;

            if ( curr >= num_file_per_upload ) {
                window.alert( language.get('file.batch_notice') );
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

        onFileAdded: function( up, files ) {
            if ( this.uploading.length )
                return;

            if ( !(files && files.length) )
                return;

            var exts = this.ext,
                maxFileSize = this.maxFileSize;

            files = _.filter( files, function(file) {
                var ex = file.name.split('.').pop();
                return _.contains( exts.split(','), ex );
            });

            files = _.filter( files, function(file) {
                return file.size <= ( maxFileSize * 1024 * 1024 );
            });

            var conf = constants.get('conf') || {},
                num_file_per_upload = +conf.num_file_per_upload,
                limit = +conf.limitfile,
                uploaded = +conf.uploadedfile,
                curr = this.preview ? this.preview.getNumFiles() : 0,
                self = this;

            if ( curr + files.length > num_file_per_upload ) {
                window.alert( language.get('file.batch_notice') );
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
                    } else if ( num >= 8 ) {
                        this.hideMoreButton();
                    } else {
                        this.showMoreButton();
                    }

                    this.inputbox[ num > 1 ? 'multiple' : 'single' ]();
                } );
            }

            this.showMainState();
            for ( var i = 0; i < files.length; i++ ) {
                this.preview.add( files[i] );
            }

            _.each( files, function(file) {
                self.uploading.push(file.id);
            });

            this.$action.hide();
            up.start();
            up.refresh();
        },

        onFileError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( language.get('file.file_type_not_permitted') );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                var msg = '"' + file.file.name + '": ' + language.get('file.max_upload_size_error').replace( '##maxsize##', this.maxFileSize );
                window.alert( msg );
            } else {
                console.log( file.message );
            }
        },

        onFileProgress: function( up, file ) {
            this.preview && this.preview.updateProgress( file );
        },

        onFileUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.id ) {
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

            this.preview && this.preview.setFile( file, json );
        },

        // ---------------------------------------------------------------------
        // Drag and drops event handlers.
        // ---------------------------------------------------------------------
        
        onDragEnter: function( e ) {
            e.preventDefault();
            this.$dropArea.show();
            this.$dropArea.css('line-height', this.$dropArea.height() + 'px');
        },

        onDragLeave: function( e ) {
            e.preventDefault();
            this.$dropArea.hide();
        },

        onFileDrop: function( e ) {
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

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'file' }
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
