define('views/stream/filterbar',[
    'sandbox',
    'views/base',
    'utils/ajax'
],

// definition
// ----------
function(
    $,
    BaseView,
    ajax
) {

    return BaseView.extend({

        render: function() {
            this.$btn = $('.joms-activity-filter-action');
            this.$list = $('.joms-activity-filter-dropdown');
            this.$options = $('.joms-activity-filter__options');

            this.$btn.on( 'click', $.bind( this.toggle, this ) );
            this.$list.on( 'click', 'li', $.bind( this.select, this ) );
            this.$list.on( 'change', 'select', $.bind( this.filterChange, this ) );
            this.$list.on( 'keyup', 'input[type=text]', $.bind( this.filterKeyup, this ) );
            this.$list.on( 'click', 'button.joms-button--primary', $.bind( this.filterSearch, this ) );

            this.$options.find('li').not('.noselect').addClass('joms-js-filterbar-item');
            $( document ).on( 'click', '.joms-js-filterbar-item', $.bind( this.makeDefault, this ));

            this.listenTo( $, 'click', this.onDocumentClick );
        },

        toggle: function() {
            var collapsed = this.$list[0].style.display === 'none';
            collapsed ? this.expand() : this.collapse();
        },

        expand: function() {
            this.$list.show();
        },

        collapse: function() {
            this.$list.hide();
        },

        select: function( e ) {
            var li = $( e.currentTarget ),
                url = li.data('url') || '/',
                filter = li.data('filter');

            if ( filter === '__filter__' ) {
                return;
            }

            this.toggle();
            window.location = url;
        },

        filterChange: function( e ) {
            var value = e.target.value,
                $input = this.$list.find('input[type=text]'),
                $button = this.$list.find('.joms-button--primary');

            if ( value === 'hashtag' || value === 'keyword' ) {
                $input.attr( 'placeholder', $input.data('label-' + value) );
                $button.html( $button.data('label-' + value) );
            }
        },

        filterKeyup: function( e ) {
            var input = $( e.currentTarget ),
                value = input.val(),
                newValue = value;

            newValue = newValue.replace( /#/g, '' );
            if ( newValue.length !== value.length ) {
                input.val( newValue );
            }
        },

        filterSearch: function( e ) {
            var btn, li, filter, input, value, url;

            e.preventDefault();
            e.stopPropagation();

            btn = $( e.currentTarget );
            li = btn.closest('li');
            filter = li.find('select').val();
            input = li.find('input');
            value = input.val().replace(/^\s+|\s+$/g, '');

            if ( !value ) {
                return;
            }

            if ( filter === 'hashtag' ) {
                value = value.split(' ');
                value = value[0];
            }

            url = li.data('url'),
            url = url.replace( '__filter__', filter );
            url = url.replace( '__value__', value );
            window.location = url;
        },

        makeDefault: function( e ) {
            var btn, value, loading, json;

            e.preventDefault();
            e.stopPropagation();

            btn = $( e.currentTarget );
            value = btn.find('a').data('value');
            loading = this.$options.find('.noselect > img');

            if ( loading.css('visibility') !== 'hidden' )
                return;

            loading.css( 'visibility', 'visible' );
            json = {};

            ajax({
                fn: 'system,ajaxDefaultUserStream',
                data: [ value ],
                success: function( resp ) {
                    if ( resp ) {
                        json = resp;
                    }
                },
                complete: $.bind(function() {
                    if ( json.error ) {
                        joms.popup.info( 'Error', json.error );
                    } else if ( json.success ) {
                        joms.popup.info( '', json.message );
                        this.$options.find('.joms-dropdown').hide();
                        btn.addClass('active').siblings('li').removeClass('active');
                        loading.css( 'visibility', 'hidden' );
                    }
                }, this )
            });
        },

        onDocumentClick: function( elem ) {
            if ( elem.closest('.joms-activity-filter').length )
                return;

            this.collapse();
        }

    });

});
