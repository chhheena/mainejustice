(function( root, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.page = factory( root, joms.popup.page || {});

    define([ 
      'popups/page.share',
      'popups/page.invite',
      'popups/page.delete',
      'popups/page.unpublish',
      'popups/page.addfeatured',
      'popups/page.removefeatured',
      'popups/page.banmember',
      'popups/page.unbanmember',
      'popups/page.removemember',
      'popups/page.report',
    ], function() {
        return joms.popup.page;
    });

})( window, function( window, sub ) {

// Exports.
return joms._.extend({}, sub );

});
