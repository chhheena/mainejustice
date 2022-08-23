(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.follower = factory( root, joms.popup.follower || {});

    define([
        'popups/follower.add',
        'popups/follower.addcancel',
        'popups/follower.approve',
        'popups/follower.reject',
        'popups/follower.remove',
        'popups/follower.response'
    ], function() {
        return joms.popup.follower;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
