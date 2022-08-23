(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.hovercard = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var card, 
    showTimer = 0, 
    hideTimer = 0, 
    animateTimer = 0, 
    cache = {}, 
    mouseOver = false,
    current = 0;

var MOUSEOVER_EVENT = 'mouseover.joms-hcard',
    MOUSEOUT_EVENT = 'mouseout.joms-hcard',
    IMG_SELECTOR = 'img[data-author]';

function initialize() {
    // Only enable on desktop browser.
    if ( joms.mobile ) {
        return;
    }

    createCard();

    // Attach handler.
    $( document.body )
        .off( MOUSEOVER_EVENT ).off( MOUSEOUT_EVENT )
        .on( MOUSEOVER_EVENT, IMG_SELECTOR, onMouseOver )
        .on( MOUSEOUT_EVENT, IMG_SELECTOR, onMouseOut );
}

function onMouseOver( e ) {
    var img = $( e.target ),
        id = img.data('author');

    // Remove title attribute to avoid redundancy.
    img.removeAttr('title');

    resetCard();
    
    mouseOver = true;
    
    current = id;

    clearTimeout( hideTimer );

    if ( cache[id] ) {
        clearTimeout( showTimer );
        showTimer = setTimeout(function() {
            mouseOver && updateCard( cache[id], img );
        }, 100 );
        return;
    }

    joms.ajax({
        func: 'profile,ajaxFetchCard',
        data: [ id ],
        callback: function( json ) {
            if ( json.html ) {
                cache[id] = json.html;
                clearTimeout( showTimer );
                mouseOver && current == id && updateCard( json.html, img );
            }
        }
    });
}

function onMouseOut() {
    mouseOver = false;
    clearTimeout( showTimer );
    hideTimer = setTimeout(function() {
        card && resetCard();
    }, 100 );
}

function createCard() {
    card = $('<div>Loading...</div>');
    card.css({ 
        position: 'absolute', 
        zIndex: 2000, 
        display: 'none', 
        opacity:0, 
        transition: 'opacity 300ms, top 300ms' 
    });

    card.appendTo( document.body );

    card.on( MOUSEOVER_EVENT, function() { clearTimeout( hideTimer ); });
    card.on( MOUSEOUT_EVENT, onMouseOut );
}

function resetCard() {
    clearTimeout( animateTimer );
    card && card.css({
        display: 'none',
        opacity: 0
    });
}

function updateCard( html, img ) {
    var doc = $( document ),
        offset = img.offset(),
        width = img.width(),
        height = img.height(),
        maxWidth = window.innerWidth,
        maxHeight = window.innerHeight + doc.scrollTop(),
        cardHeight = 140,
        alignLeft = offset.left + 320 < maxWidth,
        top = offset.top + height + 10;

    card.html( html );

    cardHeight = card.height();

    if ( top + cardHeight > maxHeight ) {
        top = offset.top - cardHeight - 4;
    }

    card.css({
       top: top - 10,
       left: alignLeft ? offset.left : '',
       right: alignLeft ? '' : maxWidth - offset.left - width,
       display: 'block',
       opacity: 0
    });

    animateTimer = setTimeout(function() {
        card.css({
            top: top,
            opacity: 1
        })
    }, 300)
}

// Exports.
return {
    initialize: initialize
};

});
