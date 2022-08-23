(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.photo || (joms.popup.photo = {});
    joms.popup.photo.zoom = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.photo.zoom;
    });

})( window, joms.jQuery, function( window, $ ) {

var $cnt, $win;

function render( popup, url ) {
    var evtName = 'resize.joms-photozoom';

    popup.items[0] = {
        type: 'inline',
        src: buildHtml( url )
    };

    $cnt = popup.container;
    $win = $( window );

    // #719 Wait for image to be loaded.
    setTimeout(function() {
        $cnt.find( '.joms-popup img' ).one( 'load', fixResize ).each(function() {
            if ( this.complete ) $( this ).load();
        });
    }, 1 );

    $win.off( evtName )
        .on( evtName, fixResize );

    popup.updateItemHTML();
    popup.st.callbacks || (popup.st.callbacks = {});
    popup.st.callbacks.close = function() {
        $win.off( evtName );
    };
}

function buildHtml( url ) {
    url = url || '';
    url = url.replace( 'thumb_', '' );
    return [
        '<div class="joms-popup-wrapper" style="width:100%;height:100%;margin:0 auto;text-align:center">',
        '<div class="joms-popup" style="max-width:100%;left:auto;right:auto;position:relative;top:0;display:none;">',
        '<img src="' + url + '" style="width:auto;max-width:100%; display:none;">',
        '</div>',
        '</div>'
    ].join('');
}

var fixResize = joms._.debounce(function() {
    var $pop = $cnt.find( '.joms-popup' ).css({ position: '', display: '', top: '', width: '' });
    var $img = $pop.find( 'img' ).css({ height: '' });

    // Unwrap from temporary wrapper.
    if ( $cnt.find( '.joms-popup-wrapper' ).length ) {
        $cnt.find( '.joms-popup-wrapper .mfp-close' ).appendTo( $pop );
        $pop.unwrap();
    }

    var $mfp = $pop.parents('.mfp-content'),
        xHeight, ratio, height, width;

    height = $img.get(0).height;
    width = $img.get(0).width; 

    ratio = width / height;

    xHeight = $mfp.width() / ratio;

    if (xHeight > $mfp.height()) {
        width = 'auto';
        height = $mfp.height();
    } else {
        width = $mfp.width();
        height = 'auto';
    }

    $img.css({
        display: 'block',
        height: height,
        width: width
    });

    $pop.css({ position: 'relative', width: $img.width() });
    
}, 100 );

// Exports.
return function( url ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, url );
    });
};

});
