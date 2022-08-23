var store = require('./store');

// Initialize `joms` object if not found.
window.joms = window.joms || {};

window.joms.store = store;

(function( global, factory ) {

    if(!joms.getData( 'is_chat_view' )) { 
        joms_observer.add_action('chat_initialized', factory, 1, 1);
    }

})( window, function() {
    var Vue = require( 'joms' ).Vue,
        Chatbar = require( './components/chatbar' ),
        store = require( 'joms' ).store,
        app;

    app = new Vue({
        store: store,
        render: function( createElement ) {
            return createElement( Chatbar );
        },
        mounted: function() {
            var store = this.$store;

            store.dispatch( 'chats/fetch', joms.chat ).then( function() {

                _.each( joms.chat.conversations, function ( item ) {
                    store.dispatch( 'chats/addSidebarItem', item.id );
                })

                store.dispatch( 'chats/syncState' );
            });
        }
    }).$mount();

    // Append element to the document root.
    document.body.appendChild( app.$el );

    jQuery('body').on('click', function( e ) {
        var el = jQuery(e.target).parents('.joms-chatbar');
        if (!el.length) {
            joms.store.dispatch( 'chats/setActiveWindow', 0 );
        }
    });
});
