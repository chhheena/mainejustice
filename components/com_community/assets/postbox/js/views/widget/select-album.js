define('views/widget/select-album',[
    'sandbox',
    'views/widget/select',
    'utils/language'
],

// definition
// ----------
function( $, SelectWidget, language ) {

    return SelectWidget.extend({

        template: joms.jst[ 'html/widget/select-album' ],

        render: function() {
            var data = {};
            data.options = this.options;
            data.width = this.width || false;
            data.placeholder = language.get('select_category');

            this.$el.html( this.template( data ) );
            this.$span = this.$('span');
            this.$ul = this.$('ul');

            if ( data.options ) {
                if ( data.options.length > 3 ) {
                    this.$ul.css({ height: '160px', overflowY: 'auto' });
                }
                if ( data.options[0] )
                    this.select( data.options[0][0], data.options[0][1] );
            }
        },

        onSelect: function( e ) {
            var el = $( e.currentTarget ),
                value = el.data('value'),
                text = el.find('p').html();

            this.select( value, text );
            this.toggle();
        }

    });

});