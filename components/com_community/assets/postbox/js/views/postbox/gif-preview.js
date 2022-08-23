define('views/postbox/gif-preview',[
    'views/postbox/photo-preview'
],

// definition
// ----------
function( PreviewView ) {

    return PreviewView.extend({

        render: function() {
            PreviewView.prototype.render.apply( this, arguments );
            this.$el.addClass('joms-postbox-gif-preview');
        },

        add: function() {
            PreviewView.prototype.add.apply( this, arguments );
            this.$el.find('.joms-postbox-select-album').hide();
        },

        setImage: function( file, json ) {
            json || (json = {});

            var elem = this.$list.find( '#' + this.getFileId(file) ),
                src = json.image,
                id = json.id;

            elem.find('img').attr( 'src', src ).data( 'id', id );
            elem.find('img').attr( 'style', 'visibility:visible');
            elem.find('.joms-postbox-photo-action').show();
            elem.addClass('joms-postbox-photo-loaded');
            elem.find('.joms-postbox-photo-progressbar').remove();

            this.pics || (this.pics = []);
            this.pics.push( '' + id );

            this.trigger( 'update', this.pics.length );
        }

    });

});