(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.page = factory( root, $ );

    define([ 'utils/hovercard', 'popups/page' ], function() {
        return joms.view.page;
    });

})( window, joms.jQuery, function( window, $ ) {

function initialize() {
    // joms.util.hovercard.initialize();
}

function like( type, id ) {
    if (type === 'profile') {
        $('a.joms-js--like-profile-' + id).attr('onclick', 'javascript:;')
    }

    joms.ajax({
        func: 'system,ajaxLike',
        data: [ type, id ],
        callback: function( json ) {
            if ( json.success ) {
                update( 'like', type, id, json.likeCount );
                location.reload();
            }
        }
    });
}

function unlike( type, id ) {
    if (type === 'profile') {
        $('a.joms-js--like-profile-' + id).attr('onclick', 'javascript:;')
    }
    
    joms.ajax({
        func: 'system,ajaxUnlike',
        data: [ type, id ],
        callback: function( json ) {
            if ( json.success ) {
                update( 'unlike', type, id, json.likeCount );
                location.reload();
            }
        }
    });
}

function share( url ) {
    joms.popup.page.share( url );
}

function update( action, type, id, count ) {
    var elem;

    elem = $( '.joms-js--like-' + type + '-' + id );
    elem.each(function() {
        var tagName = this.tagName.toLowerCase(),
            elem = $( this );

        if ( tagName === 'a' ) {
            if ( elem.hasClass('joms-popup__btn-like') ) {
                updatePopupButton( elem, action, type, id, count );
            } else {
                updateFocusButton( elem, action, type, id, count );
            }
        } else if ( tagName === 'button' ) {
            if ( elem.hasClass('joms-popup__btn-like') ) {
                updatePopupButton( elem, action, type, id, count );
            } else {
                updateButton( elem, action, type, id, count );
            }
        }
    });
}

function updatePopupButton( elem, action, type, id, count ) {
    var icon = '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-thumbs-up"></use></svg>',
        lang;

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.addClass('liked');
        lang = elem.data('lang-liked');
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.removeClass('liked');
        lang = elem.data('lang-like');
    }

    lang = lang || elem.data('lang');
    count = +count;
    if ( count > 0 ) {
        lang += ' (' + count + ')';
    }

    elem.html( icon + ' <span>' + lang + '</span>' );
}

function updateFocusButton( elem, action, type, id, count ) {
    var lang;

    elem.find('span').html( count );

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.addClass('liked');
        if ( lang = elem.data('lang-liked') ) {
            elem.find('.joms-js--lang').text( lang );
        }
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.removeClass('liked');
        if ( lang = elem.data('lang-like') ) {
            elem.find('.joms-js--lang').text( lang );
        }
    }
}

function updateButton( elem, action, type, id, count ) {
    var lang;

    if ( action === 'like' ) {
        elem.attr( 'onclick', 'joms.view.page.unlike("' + type + '", "' + id + '");' );
        elem.removeClass('joms-button--neutral');
        elem.addClass('joms-button--primary');
        lang = elem.data('lang-liked');
    } else if ( action === 'unlike' ) {
        elem.attr( 'onclick', 'joms.view.page.like("' + type + '", "' + id + '");' );
        elem.addClass('joms-button--neutral');
        elem.removeClass('joms-button--primary');
        lang = elem.data('lang-like');
    }

    lang = lang || elem.data('lang') || '';
    count = +count;
    if ( count > 0 ) {
        lang += ' (' + count + ')';
    }

    elem.html( lang );
}

function react( uid, reactId, type ) {
    var current = getReaction( reactId),
        text = current.text,
        name = current.name,
        reactClass = 'reaction-btn--' + name,
        $btn = $('.joms-button--reaction[data-uid='+uid+'][data-type=page]'),
        element = $btn.attr('data-element'),
        streamBtn;
    
    // sync status between photo popup and stream item
    if (element === 'photo') {
        $streamBtn = $('.joms-button--reaction[data-element=photo'+ uid +']');
        if ($streamBtn.length) {
            $btn = $btn.add( $streamBtn );
        }
    }

    var classes = [
        'reaction-btn--like',
        'reaction-btn--love',
        'reaction-btn--haha',
        'reaction-btn--wow',
        'reaction-btn--sad',
        'reaction-btn--angry'
    ];
    
    $btn.removeClass(classes.join(' '));

    if (type === 'onBar') {
        $btn.addClass('reaction-btn--animate');
        setTimeout(function() {
            $btn.removeClass('reaction-btn--animate');
        }, 200);
    }

    $btn.addClass( reactClass );
    $btn.text( text );

    $btn.attr('data-reactid', reactId);
    $btn.attr('data-action', 'unreact');
    $btn.attr('onclick', 'javascript:;');

    joms.ajax({
        func: 'system,ajaxLike',
        data: [ element, uid, reactId ],
        callback: function( json ) {
            var $status = $btn.parents('.joms-stream__actions').siblings('.joms-stream__status');
            
            $status.show();
            $status.html(json.html);
            
            var onclick = 'joms.view.page.unreact('+uid+', '+reactId+')';
            $btn.attr('onclick', onclick);
        }
    });
}

function unreact( uid, reactId ) {
    var $btn = $('.joms-stream__actions .joms-button--reaction[data-uid='+uid+'][data-type=page]'),
        element = $btn.attr('data-element'),
        text = $btn.attr('data-lang-like'),
        streamBtn;
    
    // sync status between photo popup and stream item
    if (element === 'photo') {
        $streamBtn = $('.joms-button--reaction[data-element=photo'+ uid +']');
        if ($streamBtn.length) {
            $btn = $btn.add( $streamBtn );
        }
    }

    var classes = [
        'reaction-btn--like',
        'reaction-btn--love',
        'reaction-btn--haha',
        'reaction-btn--wow',
        'reaction-btn--sad',
        'reaction-btn--angry'
    ];
    
    $btn.removeClass(classes.join(' '));
    $btn.text( text );
    $btn.attr('data-reactid', 1);
    $btn.attr('data-action', 'react');
    $btn.attr('onclick', 'javascript:;');

    joms.ajax({
        func: 'system,ajaxUnlike',
        data: [ element, uid, reactId ],
        callback: function( json ) {
            var $status = $btn.parents('.joms-stream__actions').siblings('.joms-stream__status');

            if (!json.html) {
                $status.hide();
            }

            $status.html(json.html);

            var onclick = 'joms.view.page.react('+uid+', 1)';
            $btn.attr('onclick', onclick);
        }
    });
}

function getReaction( reactId ) {
    var data = joms.getData('joms_reaction');
    var react = data.filter(function(item) {
        return item.id == reactId;
    }).pop();

    return react;
}
// Exports.
return {
    initialize: initialize,
    like: like,
    unlike: unlike,
    share: share,

    react: react,
    unreact: unreact
};

});
