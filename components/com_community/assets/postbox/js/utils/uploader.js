define('utils/uploader',[
    'sandbox',
    'app'
],

// definition
// ----------
function( $, App ) {

    var defaults = {
        runtimes: 'html5,html4',
        url: 'index.php'
    };

    function Uploader( options ) {
        this.queue = [];
        this.ready = false;

        if ( window.plupload ) {
            this.ready = true;
            this.create( options );
            return;
        }

        var that = this;
        joms.$LAB.script( App.legacyUrl + 'assets/vendors/plupload.min.js' ).wait(function() {
            that.ready = true;
            that.create( options );
            that.execQueue();
        });
    }

    Uploader.prototype.create = function( options ) {
        var btn = this.$button = options.browse_button,
            id = false,
            par;

        // Container.
        if ( typeof options.container === 'string' ) {
            par = $( '#' + options.container );
            if ( !par.length ) {
                par = $( '<div id="' + options.container + '" style="width:1px; height:1px; overflow:hidden">' ).appendTo( document.body );
            }
        } else {
            par = $( options.container );
            if ( id = par.attr('id') ) {
                options.container = id;
            } else {
                options.container = id = $.uniqueId('uploader_parent_');
                par.attr( 'id', id );
            }
        }

        // Upload button.
        if ( typeof btn === 'string' ) {
            this.$button = $( '#' + btn );
            if ( !this.$button.length ) {
                this.$button = $( '<button id="' + btn + '">' ).appendTo( par );
            }
        } else if ( id = btn.attr('id') ) {
            this.$button = $( document.getElementById(id) );
        } else {
            options.browse_button = id = $.uniqueId('uploader_');
            btn.attr( 'id', id );
        }

        this.onProgress = options.onProgress || $.noop;
        this.onAdded = options.onAdded || $.noop;

        options = $.extend({}, defaults, options || {});
        this.uploader = new plupload.Uploader( options );
    };

    Uploader.prototype.init = function() {
        if ( !this.ready ) {
            this.queue.push([ 'init', this, arguments ]);
            return;
        }

        this.uploader.init();
        this.uploader.bind( 'FilesAdded', this.onAdded );
        this.uploader.bind( 'Error', this.onError );
        this.uploader.bind( 'BeforeUpload', this.onBeforeUpload );
        this.uploader.bind( 'UploadProgress', this.onProgress );
        this.uploader.bind( 'FileUploaded', this.onUploaded );
        this.uploader.bind( 'UploadComplete', this.onComplete );
    };

    Uploader.prototype.open = function() {
        if ( !this.ready ) {
            this.queue.push([ 'open', this, arguments ]);
            return;
        }

        this.$button.click();
    };

    Uploader.prototype.reset = function() {
        if ( !this.ready ) {
            this.queue.push([ 'reset', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.remove = function() {
        if ( !this.ready ) {
            this.queue.push([ 'remove', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.params = function( data ) {
        this.uploader.settings.multipart_params = data;
    };

    Uploader.prototype.upload = function() {
        if ( !this.ready ) {
            this.queue.push([ 'upload', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.execQueue = function() {
        var cmd;
        while ( this.queue.length ) {
            cmd = this.queue.shift();
            this[ cmd[0] ].apply( cmd[1], cmd[2] );
        }
    };

    // -------------------------------------------------------------------------

    Uploader.prototype.onAdded = $.noop;
    Uploader.prototype.onError = $.noop;
    Uploader.prototype.onBeforeUpload = $.noop;
    Uploader.prototype.onProgress = $.noop;
    Uploader.prototype.onUploaded = $.noop;
    Uploader.prototype.onComplete = $.noop;

    return Uploader;

});
