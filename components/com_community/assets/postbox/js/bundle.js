window.joms_init_postbox = function() {
    
    require([
       'jst',
       'sandbox',
       'views/postbox/layout',
       'views/stream/layout',
       'utils/constants'
    ],

    // description
    // -----------
    function( jst, $, PostboxView, StreamView, constants ) {
        
        function initPostbox() {
            var el = $('.joms-postbox'),
                postbox;

            if ( el.length ) {
                postbox = new PostboxView({ el: el  });
                postbox.render();
                postbox.show();
            }
        }

        function initStream() {
            var stream = new StreamView();
            stream.render();
        }

        function fetchAllFriends() {
            // var url   = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&allfriends=1',
            //  settings = constants.get('settings') || {},
            //  data     = [];

            constants.set( 'friends', 'fetching' );
            // if ( settings.isGroup ) url += '&groupid=' + constants.get('groupid');
            // else if ( settings.isEvent ) url += '&eventid=' + constants.get('eventid');

            var timer = window.setInterval(function() {
                if ( window.joms_friends ) {
                    window.clearInterval( timer );
                    constants.set( 'friends', window.joms_friends );
                }
            }, 200 );
        }

        initPostbox();
        initStream();

        if ( +window.joms_my_id )
            fetchAllFriends();

    });
}