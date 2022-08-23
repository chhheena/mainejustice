define('views/postbox/file-preview',[
    'sandbox',
    'app',
    'views/base',
    'utils/ajax',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, App, BaseView, ajax, constants, language ) {

    return BaseView.extend({

        templates: {
            div: _.template(
                '<div class=joms-postbox-photo-preview> \
                    <ul class="joms-list clearfix"></ul> \
                </div>'
            ),

            file: _.template(
                '<li id="<%= id %>" data-fileid="" class=joms-postbox-photo-item> \
                    <div class=img-wrapper> \
                        <svg viewBox="0 0 16 18" class="joms-icon">\
                            <use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip" class="joms-icon--svg-fixed joms-icon--svg-unmodified joms-icon--svg-unmodified"></use>\
                        </svg> \
                        <b class="joms-filename"><%= name %></b>\
                    </div> \
                    <div class=joms-postbox-photo-action style="display:none"> \
                        <span class=joms-postbox-photo-remove>×</span> \
                    </div> \
                    <div class=joms-postbox-file-progressbar> \
                        <div class=joms-postbox-file-progress></div> \
                    </div> \
                </li>'
            )
        },

        events: {
            'click .joms-postbox-photo-remove': 'onRemove'
        },

        render: function() {
            this.$el.html( this.templates.div() );
            this.$list = this.$el.find('ul.joms-list');
            this.$form = this.$el.find('div.joms-postbox-photo-form');
        },

        add: function( file ) {
            this.$list.append( this.templates.file({
                id: this.getFileId( file ),
                name: file.name,
                src: App.legacyUrl + '/assets/photo-upload-placeholder.png'
            }) );

            var settings = constants.get('settings') || {};
            if ( !settings.isMyProfile )
                return;

            if ( this.select )
                return;

            var albums = constants.get('album'),
                privs = language.get('privacy'),
                options = [];

            var privmap = {
                '0': 'public',
                '10': 'public',
                '20': 'site_members',
                '30': 'friends',
                '40': 'me'
            };

            var icons = {
                '0': 'earth',
                '10': 'public',
                '20': 'users',
                '30': 'user',
                '40': 'lock'
            };

            if ( !(albums && albums.length) )
                return;

            this.albummap = {};
            for ( var i = 0, album, permission; i < albums.length; i++ ) {
                album = albums[i];
                permission = '' + album.permissions;
                this.albummap[ '' + album.id ] = permission;
                album = [ album.id, album.name, privs[ privmap[permission] || '0' ], icons[permission || '0'] ];
                options[ +album['default'] ? 'unshift' : 'push' ]( album );
            }
        },

        value: function() {
            var settings = constants.get('settings') || {},
                album_id, privacy, values;

            values = {
                id: this.files || []
            };

            return values;
        },

        updateProgress: function( file ) {
            var id = this.getFileId( file ),
                elem = this.$list.find( '#'+ id ).find('.joms-postbox-file-progress'),
                percent;

            if ( elem && elem.length ) {
                percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
                elem.stop().animate({ width: percent + '%' });
            }
        },

        setFile: function( file, json ) {
            json || (json = {});

            var elem = this.$list.find( '#' + this.getFileId(file) ),
                id = json.id;

            elem.find('.joms-filename').text(file.name);
            elem.attr('data-id', json.id);

            elem.find('.joms-postbox-photo-action').show();
            elem.addClass('joms-postbox-photo-loaded');
            elem.find('.joms-postbox-photo-progressbar').remove();

            this.files || (this.files = []);
            this.files.push( '' + id );

            this.trigger( 'update', this.files.length );
        },

        removeFailed: function() {
            this.$list.find('.joms-postbox-photo-item')
                .not('.joms-postbox-photo-loaded')
                .remove();

            this.trigger( 'update', this.files && this.files.length || 0 );
        },

        // ---------------------------------------------------------------------
        // Thumbnail event handlers.
        // ---------------------------------------------------------------------

        onRemove: function( e ) {
            var li = $( e.target ).closest('li'),
                id = li.data('id'),
                num;

            li.remove();
            this.files = $.without( this.files, '' + id );
            num = this.files.length;

            this.ajaxRemove([ id ]);
            this.trigger( 'update', num );
        },

        remove: function() {
            this.files && this.files.length && this.ajaxRemove( this.files );
            return BaseView.prototype.remove.apply( this, arguments );
        },

        ajaxRemove: function( files ) {
            var params = {};
            params.option = 'community';
            params.no_html = 1;
            params.task = 'azrul_ajax';
            params.func = 'system,ajaxDeleteTempFile';
            params[ window.jax_token_var ] = 1;

            if ( files && files.length )
                params[ 'arg2[]' ] = files;

            $.ajax({
                url: window.jax_live_site,
                type: 'post',
                dataType: 'json',
                data: params
            });
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getFileId: function( file ) {
            return 'postbox-preview-' + file.id;
        },

        getNumFiles: function() {
            return this.$list.find('li').length;
        }

    });

});
