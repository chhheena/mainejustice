var $ = require( 'jquery' ),
    joms = require( 'joms' ),
    autosize = require('joms').autosize_textarea,
    Vue = require( 'joms' ).Vue,
    user_id = String( window.joms_my_id || '' ),
    settings = window.joms_plg_jomsocialchatbar || {},
    templates = settings.templates || {};

module.exports = Vue.component( 'chatbar-window-input', {
    template: templates.chatbar_window_input,
    props: [ 'chat', 'active' ],
    data: function() {
        return {
            attachment: false,
            ta_original_height: ''
        };
    },
    mounted: function() {
        if (this.active) {
            this.focus();
        }
        autosize(this.$el.querySelector('textarea'));
        this.ta_original_height = this.$el.querySelector('textarea').style.height;
    },
    watch: {
        active: function( val ) {
            if (val) {
                this.focus();
            }
        }   
    },
    methods: {
        focus: function() {
            this.$el.querySelector('textarea').focus();
        },
        submit: function( event ) {
            var $input = event.target,
                timestamp = ( new Date ).getTime(),
                attachment = JSON.parse( JSON.stringify( this.attachment || {} ) ),
                data;
            
            if ($input.value.trim() || this.attachment) {
                data = {
                    chat_id: this.chat.id,
                    user_id: user_id,
                    action: 'sent',
                    content: $input.value,
                    attachment: attachment,
                    params: { attachment: attachment },
                    created_at: Math.floor( timestamp / 1000 )
                };

                $input.value = '';
                this.attachment = false;
                this.resetInputStyle($input);
                this.$emit( 'submit', data );
            }

        },

        resetInputStyle: function($el) {
            $el.style.height = this.ta_original_height;
            $el.style.overflow = 'hidden';
        },

        attachImage: function( e ) {
            var that = this,
                baseUrl = joms.getData( 'base_url' ),
                url = baseUrl + 'index.php?option=com_community&view=photos&task=ajaxPreviewComment',
                extensions = 'jpg,jpeg,png,gif';

            doUpload({
                url: url,
                filters: { mime_types: [{ title: 'Image files', extensions: extensions }] },
                max_file_size: undefined
            }).done(function( file, json ) {
                that.attachment = {
                    type: 'image',
                    id: json.photo_id,
                    url: json.thumb_url
                };
            });
        },
        attachFile: function( e ) {
            var that = this,
                baseUrl = joms.getData( 'base_url' ),
                url = baseUrl + 'index.php?option=com_community&view=files&task=multiUpload&type=chat&id' + this.chat.id,
                extensions = 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';

            doUpload({
                url: url,
                filters: { mime_types: [{ title: 'Document files', extensions: extensions }] },
                max_file_size: undefined
            }).done(function( file, json ) {
                that.attachment = {
                    type: 'file',
                    id: json.id,
                    url: json.path,
                    name: file.name
                };
            })
        },
        removeAttachment: function( e ) {
            this.attachment = false;
        }
    }
});


////////////////////////////////////////////////////////////////////////////////////////////////////
// FILE UPLOADER
////////////////////////////////////////////////////////////////////////////////////////////////////

var _uploader,
    _uploaderButton,
    _uploaderDefer;

function doUpload( settings ) {
    return $.Deferred(function( defer ) {
        initUpload( defer ).done(function( uploader, $button ) {
            uploader.refresh();
            uploader.settings.url = settings.url;
            uploader.settings.filters = settings.filters;
            uploader.settings.max_file_size = settings.max_file_size;
            uploader.refresh();
            $button.click();
        });
    });
}

function initUpload( defer ) {
    // Save defer object for later use.
    _uploaderDefer = defer;

    return $.Deferred(function( defer ) {
        if ( _uploader ) {
            defer.resolve( _uploader, _uploaderButton );
            return;
        }

        joms.util.loadLib( 'plupload', function() {
            setTimeout(function() {
                var id = 'joms-js--jomsocialchatbar-uploader',
                    url = joms.getData( 'base_url' ) + 'index.php?option=com_community',
                    $container, $button;

                $container = $( '<div id="' + id + '" aria-hidden="true" />' )
                    .css({ width: 1, height: 1, overflow: 'hidden' })
                    .appendTo( document.body );

                $button = $( '<button id="' + id + '-button" />' ).appendTo( $container );

                _uploader = new window.plupload.Uploader({
                    url: url,
                    container: id,
                    browse_button: id + '-button',
                    runtimes: 'html5,html4',
                    multi_selection: false
                });

                _uploader.bind( 'FilesAdded', function( up ) {
                    setTimeout(function() {
                        up.start();
                        up.refresh();
                    }, 1 );
                });

                _uploader.bind( 'Error', function( up, error ) {
                    window.alert( error && error.message || 'Undefined error.' );
                });

                _uploader.bind( 'FileUploaded', function( up, file, info ) {
                    var json, ct, loading, thumb, button, img, label;

                    try {
                        json = JSON.parse( info.response );
                    } catch ( e ) {}

                    json = json || {};

                    if ( json.error || json.msg ) {
                        window.alert( json.error || json.msg );
                        _uploaderDefer.reject();
                        return;
                    }

                    if ( ! ( ( json.thumb_url && json.photo_id ) || json.id ) ) {
                        window.alert( 'Undefined error.' );
                        _uploaderDefer.reject();
                        return;
                    }

                    _uploaderDefer.resolve( file, json );
                });

                _uploader.init();

                _uploaderButton = $container.find( 'input[type=file]' );
                defer.resolve( _uploader, _uploaderButton );
            });
        });
    });
}
