define('views/inputbox/file',[
    'sandbox',
    'views/inputbox/status',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, constants, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/base' ],

        initialize: function() {
            var hash, item, id, i;

            InputboxView.prototype.initialize.apply( this, arguments );
            this.hint = {
                single: language.get('status.file_hint') || '',
                multiple: language.get('status.files_hint') || ''
            };

            this.moods = constants.get('moods');
            hash = {};
            if ( this.moods && this.moods.length ) {
                for ( i = 0; i < this.moods.length; i++ ) {
                    id = this.moods[i].id;
                    item = [ id, this.moods[i].description ];
                    if ( this.moods[i].custom ) {
                        item[2] = this.moods[i].image;
                    }
                    hash[ id ] = item;
                }
            }
            this.moods = hash;
        },

        reset: function() {
            InputboxView.prototype.reset.apply( this, arguments );
            this.single();
        },

        single: function() {
            this.hint.current = this.hint.single;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        multiple: function() {
            this.hint.current = this.hint.multiple;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        updateAttachment: function( mood ) {
            var attachment = [];

            this.mood = mood || mood === false ? mood : this.mood;

            if ( this.mood && this.moods[this.mood] ) {
                if ( typeof this.moods[this.mood][2] !== 'undefined' ) {
                    attachment.push(
                        '<img class="joms-emoticon" src="' + this.moods[this.mood][2] + '"> ' +
                        '<b>' + this.moods[this.mood][1] + '</b>'
                    );
                } else {
                    attachment.push(
                        '<i class="joms-emoticon joms-emo-' + this.mood + '"></i> ' +
                        '<b>' + this.moods[this.mood][1] + '</b>'
                    );
                }
            }

            if ( !attachment.length ) {
                this.$attachment.html('');
                this.$textarea.attr( 'placeholder', this.hint.current );
                return;
            }

            this.$attachment.html( ' &nbsp;&mdash; ' + attachment.join(' ' + language.get('and') + ' ') + '.' );
            this.$textarea.removeAttr('placeholder');
        },

        getTemplate: function() {
            var html = this.template({ placeholder: this.hint.current = this.hint.single });
            return $( html );
        }

    });

});
