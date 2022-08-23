(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.reaction = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var $card;
var showTimer = 0; 
var hideTimer = 0; 
var animateTimer = 0; 
var current = 0;
var holdTimer = 0;
    

var MOUSEOVER_EVENT = 'mouseover.joms-button--reaction';
var MOUSEOUT_EVENT = 'mouseout.joms-button--reaction';
var MOUSEDOWN_EVENT = 'mousedown.joms-button--reaction';
var MOUSEUP_EVENT = 'mouseup.joms-button--reaction';
var TOUCH_START = 'touchstart.joms-button--reaction';
var TOUCH_END = 'touchend.joms-button--reaction';
var TOUCH_MOVE = 'touchmove.joms-button--reaction';
var CONTEXTMENU = 'contextmenu.joms-button--reaction';
var REACT_BTN = 'a.joms-button--reaction';
var CARD = '.joms-reactions';

function initialize() {
    if (!joms.getData('enablereaction')) {
        return;
    }

    createCard();

    // Attach handler.
    if (joms.mobile) {
        $( document.body )
            .off( TOUCH_START ).off( TOUCH_END )
            .off( CONTEXTMENU ).off( TOUCH_MOVE )
            .on( TOUCH_START, REACT_BTN, onTouchStart )
            .on( TOUCH_END, REACT_BTN, onTouchEnd )
            .on( TOUCH_MOVE, onTouchMove )
            .on( CONTEXTMENU, REACT_BTN, onContextMenu );
    } else {
        $( document.body )
            .off( MOUSEOVER_EVENT ).off( MOUSEOUT_EVENT )
            .off( MOUSEDOWN_EVENT ).off( MOUSEUP_EVENT )
            .on( MOUSEOVER_EVENT, REACT_BTN, onMouseOver )
            .on( MOUSEOUT_EVENT, REACT_BTN, onMouseOut )
            .on( MOUSEDOWN_EVENT, REACT_BTN, onMouseDown )
            .on( MOUSEUP_EVENT, REACT_BTN, onMouseUp );

        $( document.body )
            .on( MOUSEOVER_EVENT, CARD, function() { clearTimeout( hideTimer ) })
            .on( MOUSEOUT_EVENT, CARD, onMouseOut );

    }
}

var isTapped = true;
var clickTimer;
var touchMoving = false;

function onTouchStart( e ) {
    clearTimeout( clickTimer );
    clearTimeout( holdTimer );

    var $btn = $(e.target);
    touchMoving = false;
    isTapped = true;
    $btn.attr('onclick', '');
    hideCard();

    $(document).off('click.hide-reaction-bar');
    holdTimer = setTimeout( function() {
        onHold( e );
        isTapped = false;
    }, 300);
}


function onTouchEnd( e ) {
    clearTimeout( holdTimer );
    clearTimeout( clickTimer );

    clickTimer = setTimeout(function() {
        $(document)
            .off('click.hide-reaction-bar')
            .one('click.hide-reaction-bar', hideCard);
    }, 300);

    if (isTapped) {
        onTap( e );
    }

    touchMoving = false;
    isTapped = true;
}

function onTouchMove( e ) {
    clearTimeout( holdTimer );
    clearTimeout( clickTimer );
    clearTimeout( hideTimer );

    isTapped = false;
    touchMoving = true;
    hideTimer = setTimeout( function() {
        hideCard();
    }, 300);
}

function onHold( e ) {
    if (!touchMoving) {
        updateCard( e );
    }
}

function onTap( e ) {
    var $elm = $(e.target),
        type = $elm.attr('data-type'),
        uid = $elm.attr('data-uid'),
        reactId = $elm.attr('data-reactid'),
        action = $elm.attr('data-action');

    if (type === 'stream') {
        if (action === 'react') {
            joms.view.stream.react( uid, reactId );
        } else {
            joms.view.stream.unreact( uid, reactId );
        }
    }

    if (type === 'comment') {
        if (action === 'react') {
            joms.view.comment.react( uid, reactId );
        } else {
            joms.view.comment.unreact( uid, reactId );
        }
    }

    if (type === 'page') {
        if (action === 'react') {
            joms.view.page.react( uid, reactId );
        } else {
            joms.view.page.unreact( uid, reactId );
        }
    }
}

function onContextMenu( e ) {
    e.preventDefault();
    e.stopPropagation();
    return false;
}

function onMouseOver( e ) {
    clearTimeout(hideTimer);
    clearTimeout(animateTimer);
    
    hideCard();
    
    showTimer = setTimeout( function() {
        updateCard( e );
    }, 500)
}

function onMouseOut(e) {
    clearTimeout(showTimer);

    hideTimer = setTimeout( function() {
        hideCard();
    }, 1000)
}

function onMouseDown(e) {
    if (e.which === 1) {
        clearTimeout(showTimer);
    }
}

function onMouseUp(e) {
    if (e.which === 1) {
        clearTimeout(showTimer);
        $card.hide();
    }
}

function hideCard() {
    clearTimeout( animateTimer );

    if ($card.is(':visible')) {
        $card.css({
            display: 'none',
            opacity: 0
        })
    }
}

function updateCard( e ) {
    clearTimeout( animateTimer );

    var $elm = $(e.target);
    var uid = $elm.attr('data-uid');
    var type = $elm.attr('data-type');

    $card.find('.joms-reactions__item').each( function(idx, item ) {
        var $item = $(item),
            reactId = $item.attr('data-react-id'),
            text = $item.attr('data-text'),
            attr = '';

        if (type === 'stream') {
            attr = 'joms.view.stream.react('+uid+', '+reactId+', \'onBar\')';
        }

        if (type === 'comment') {
            attr = 'joms.view.comment.react('+uid+', '+reactId+', \'onBar\')';
        }

        if (type === 'page') {
            attr = 'joms.view.page.react('+uid+', '+reactId+', \'onBar\')';
        }

        $item.attr('onclick', attr);

        $item.off('click.react').one('click.react', function() {
            hideCard();
        });
    })

    var screenWidth = $(document).width();
    var cardWidth = $card.outerWidth();
    var offset = $elm.offset();
    var height = $card.height();
    var top = offset.top - height - 10;
    var isRTL = $('html').attr('dir') === 'rtl';

    if (isRTL) {
        var left = offset.left - cardWidth + $elm.outerWidth() + 10;

        left = left < 0 ? 0 : left;
    } else {
        var left = offset.left - 10;
        var offsetRight = screenWidth - left;

        if (offsetRight < cardWidth) {
            left = left - (cardWidth - offsetRight);
            if ( left < 0 ) {
                left = $(document).width();
            }
        }
    }

    $card.css({
        display: 'block',
        top: top,
        left: left
    });

    animateTimer = setTimeout( function() {
        $card.css({
            opacity: 1,
            top: top - 5
        })

    }, 300);
}

function createCard() {
    $card = $(joms.getLayout('stream.reaction').trim());
    $card.hide().appendTo( document.body );
}

// Exports.
return {
    initialize: initialize
};

});
