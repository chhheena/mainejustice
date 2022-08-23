(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.poll || (joms.popup.poll = {});
    joms.popup.poll.voted = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.poll.voted;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, tabAll, tabSelected, btnSelect, btnLoad, id, keyword, start, limit, xhr;

function render( _popup, poll_id, option_id ) {
    if ( elem ) elem.off();
    popup = _popup;

    joms.ajax({
        func: 'polls,ajaxShowVotedUsers',
        data: [ poll_id, option_id ],
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();
        }
    });
}


function buildHtml( json ) {
    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="',window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON_TITLE,'">×</button>', json.title, '</div>',
        '<div data-ui-object="popup-step-1"', ( json.error ? ' class="joms-popup__hide"' : '' ), '>',
            '<div class="joms-popup__content">', json.html, '</div>',
        '</div>',
        '<div data-ui-object="popup-step-2"', ( json.error ? '' : ' class="joms-popup__hide"' ), '>',
            '<div class="joms-popup__content joms-popup__content--single" data-ui-object="popup-message">', (json.error || ''), '</div>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( poll_id, option_id ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, poll_id, option_id );
    });
};

});
