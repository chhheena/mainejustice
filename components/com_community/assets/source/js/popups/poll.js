(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.poll = factory( root, joms.popup.poll || {});

    define([ 'popups/poll.removeoption', 'popups/poll.delete', 'popups/poll.voted' ], function() {
        return joms.popup.poll;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
