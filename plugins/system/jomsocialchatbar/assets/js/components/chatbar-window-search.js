var Vue = require( 'joms' ).Vue,
    USER_ID = +( window.joms_my_id || '' ),
    settings = window.joms_plg_jomsocialchatbar || {},
    templates = settings.templates || {};

module.exports = Vue.component( 'chatbar-window-search', {
    template: templates.chatbar_window_search,
    props: [ 'participants' ],
    data: function() {
        return {
            inputWidth: 10,
            queryResults: [],
            selectedIds: [],
            selectedNames: []
        };
    },
    computed: {
        selectedUsers: function() {
            return _.map( this.selectedIds, function( id, index ) {
                return { id: id, name: this.selectedNames[ index ] };
            }, this );
        },
    },
    mounted: function() {
        this.$input = this.$el.querySelector( '.joms-js-input' );
    },
    methods: {

        /**
         * Reset selected users.
         */
        reset: function() {
            this.selectedIds = [];
            this.selectedNames = [];
        },

        /**
         * Submit selected users.
         */
        add: function() {
            var selected = JSON.parse( JSON.stringify( this.selectedIds ) );
            this.$emit( 'done', selected );
            _.each( selected, function( id ) {
                this.participants.push( id );
            }, this );

            this.reset();
        },

        /**
         * Cancel select users.
         */
        cancel: function() {
            this.reset();
            this.$emit( 'hide' );
        },

        /**
         * Search users by keyword.
         * @param {string} keyword
         */
        search: function( keyword ) {
            var users = this.$store.getters[ 'users/search' ]( keyword ),
                filtered = this.filterSelected( users );

            this.queryResults = filtered;
        },

        /**
         * Select user.
         */
        select: function( id ) {
            var name;

            if ( this.selectedIds.indexOf( id ) === -1 ) {
                name = this.$store.getters[ 'users/name' ]( id );
                this.selectedIds.push( id );
                this.selectedNames.push( name );
                this.queryResults = [];
                this.$input.value = '';
                this.$input.focus();
            }
        },

        /**
         * Remove selected user.
         */
        removeSelected: function( id ) {
            var index = this.selectedIds.indexOf( +id );

            if ( index !== -1 ) {
                this.selectedIds.splice( index, 1 );
                this.selectedNames.splice( index, 1 );
                this.queryResults = this.filterSelected( this.queryResults );
            }
        },

        /**
         * Filter-out selected users for the list.
         * @param {Object[]} users
         */
        filterSelected: function( users ) {
            var participants = this.participants,
                selected = this.selectedIds;

            return _.filter( users, function( user ) {
                var id = +user.id,
                    allow = true;

                if ( id === USER_ID ) {
                    allow = false;
                } else if ( participants.indexOf( id ) > -1 ) {
                    allow = false;
                } else if ( selected.indexOf( id ) > -1 ) {
                    allow = false;
                }

                return allow;
            });
        },

        /**
         * Handle click on fake input.
         */
        onInputClick: function( e ) {
            this.$el.querySelector( '.joms-js-input' ).focus();
        },

        /**
         * Handle search users by keyword.
         */
        onInputKeyup: _.throttle(function( e ) {
            if ( ! this.shadowInput ) {
                this.shadowInput = this.$el.querySelector( '.joms-input--shadow' );
            }

            this.shadowInput.innerHTML = e.target.value;
            this.inputWidth = this.shadowInput.clientWidth + 20;
            this.search( e.target.value.trim() );
        }, 50 ),

        /**
         * Handle select user.
         * @param {number} id
         */
        onSelect: function( id ) {
            this.select( +id );
        }

    }
});
