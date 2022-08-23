define('app',[],function() {
    var staticUrl;

    staticUrl = window.joms_script_url || '';
    staticUrl = staticUrl.replace( /js\/$/, '' );

    return {
        staticUrl: staticUrl,
        legacyUrl: staticUrl + '../../'
    };

});