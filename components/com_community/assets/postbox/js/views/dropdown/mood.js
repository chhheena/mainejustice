define('views/dropdown/mood',[
    'sandbox',
    'views/dropdown/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/dropdown/mood' ],

        events: {
            'click li': 'onSelect',
            'click .joms-remove-button': 'onRemove'
        },

        render: function() {
            var items, hash, id, item, html, div, i;

            this.moods = constants.get('moods');

            items = [];
            hash = {};
            if ( this.moods && this.moods.length ) {
                for ( i = 0; i < this.moods.length; i++ ) {
                    id = this.moods[i].id;
                    item = [ id, this.moods[i].title ];
                    if ( this.moods[i].custom ) {
                        item[2] = this.moods[i].image;
                    }
                    items.push( item );
                    hash[ id ] = item;
                }
            }

            this.moods = hash;
            html = this.template({
                items: items,
                language: { status: language.get('status') || {} }
            });

            div = $( html ).hide();
            this.$el.replaceWith( div );
            this.setElement( div );
            this.$btnremove = this.$('.joms-remove-button').hide();

            return this;
        },

        select: function( mood ) {
            if ( this.moods[ mood ]) {
                this.$btnremove.show();
                this.trigger( 'select', this.mood = mood );
            }
        },

        value: function() {
            return this.mood;
        },

        reset: function() {
            this.mood = false;
            this.$btnremove.hide();
            this.trigger('reset');
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onSelect: function( e ) {
            var item = $( e.currentTarget ),
                mood = item.attr('data-mood');

            this.select( mood );
            this.hide();
        },

        onRemove: function() {
            this.mood = false;
            this.$btnremove.hide();
            this.trigger('remove');
        }

    });

});
