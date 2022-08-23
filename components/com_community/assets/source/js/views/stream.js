(function( root, $, factory ) {

    joms.view || (joms.view = {});
    joms.view.stream = factory( root, $ );

    define([ 'popups/stream' ], function() {
        return joms.view.stream;
    });

})( window, joms.jQuery, function( window, $ ) {

var container;

function initialize() {
    uninitialize();
    container = $('.joms-stream__wrapper');
}

function uninitialize() {
    if ( container ) {
        container.off();
    }
}

function like( id ) {
    var item = container.find( '.joms-js--stream-' + id );
    if (+item.attr('do-like')) {
        return;
    }
    item.attr('do-like', 1);
    joms.ajax({
        func: 'system,ajaxStreamAddLike',
        data: [ id ],
        callback: function( json ) {
            var item, btn, info, counter, status;

            if ( json.success ) {
                item = container.find( '.joms-js--stream-' + id );
                if ( item.length ) {
                    btn = item.find('.joms-stream__actions').find('.joms-button--liked');
                    btn.attr( 'onclick', 'joms.api.streamUnlike(\'' + id + '\');' );
                    btn.addClass('liked');
                    btn.find('span').html( btn.data('lang-unlike') );
                    btn.find('use').attr( 'xlink:href', window.location + '#joms-icon-thumbs-down' );

                    info = item.find('.joms-stream__status');
                    if ( !json.html ) {
                        info.remove();
                    } else if ( info.length ) {
                        info.html( json.html );
                    } else {
                        info = item.find('.joms-stream__actions');
                        info = $('<div class=joms-stream__status />').insertAfter( info );
                        info.html( json.html );
                    }

                    status = item.find('.joms-stream__status--mobile');
                    if ( status.length ) {
                        counter = status.find( '.joms-like__counter--' + id );
                        counter.html( +counter.eq(0).text() + 1 );
                        status.find('.joms-like__status').show();
                    }
                    item.attr('do-like', 0);
                }
            }
        }
    });
}

function unlike( id ) {
    var item = container.find( '.joms-js--stream-' + id );
    if (+item.attr('do-like')) {
        return;
    }
    item.attr('do-like', 1);
    joms.ajax({
        func: 'system,ajaxStreamUnlike',
        data: [ id ],
        callback: function( json ) {
            var item, btn, info, counter, status;

            if ( json.success ) {
                item = container.find( '.joms-js--stream-' + id );
                if ( item.length ) {
                    btn = item.find('.joms-stream__actions').find('.joms-button--liked');
                    btn.attr( 'onclick', 'joms.api.streamLike(\'' + id + '\');' );
                    btn.removeClass('liked');
                    btn.find('span').html( btn.data('lang-like') );
                    btn.find('use').attr( 'xlink:href', window.location + '#joms-icon-thumbs-up' );

                    info = item.find('.joms-stream__status');
                    if ( !json.html ) {
                        info.remove();
                    } else if ( info.length ) {
                        info.html( json.html );
                    } else {
                        info = item.find('.joms-stream__actions');
                        info = $('<div class=joms-stream__status />').insertAfter( info );
                        info.html( json.html );
                    }

                    status = item.find('.joms-stream__status--mobile');
                    if ( status.length ) {
                        counter = status.find( '.joms-like__counter--' + id );
                        var val = +counter.eq(0).text() - 1;
                        counter.html( val );
                        if (val === 0) {
                            status.find('.joms-like__status').hide();
                        }
                    }
                    item.attr('do-like', 0);
                }
            }
        }
    });
}

function edit( id ) {
    var $stream   = $( '.joms-js--stream-' + id ).eq(0),
        $sbody    = $stream.find('.joms-stream__body'),
        $colorfulContainer = $sbody.find('.colorful-status__container'),
        $scontent = $sbody.find('[data-type=stream-content]'),
        $seditor  = $sbody.find('[data-type=stream-editor]'),
        $textarea = $seditor.find('textarea'),
        origValue = $textarea.val();

    $scontent.hide();
    $seditor.show();
    $textarea.removeData('joms-tagging');
    $textarea.jomsTagging();
    $textarea.off( 'reset.joms-tagging' );
    $textarea.on( 'reset.joms-tagging', function() {
        $seditor.hide();
        $scontent.show();
        $textarea.val( origValue );
    });

    if (!$textarea.hasClass('limited') && $colorfulContainer.length) {
        $textarea.attr('maxlength', 150);
        $textarea
        .on('keydown', function(e) {
            var ENTER = 13;
            if (e.keyCode === 13) {
                var numline = $textarea.val().split('\n').length;
                if (numline === 4) {
                    e.preventDefault();
                }
            }
        })
        $textarea.addClass('limited');
    }

    $textarea.focus();
}

function editSave( id, text, origText ) {
    joms.ajax({
        func: 'activities,ajaxSaveStatus',
        data: [ id, text ],
        callback: function( json ) {
            var $stream   = $('.joms-stream').filter('[data-stream-id=' + id + ']'),
                $sbody    = $stream.find('.joms-stream__body'),
                $scontent = $sbody.find('[data-type=stream-content]'),
                $colorfulContainer = $sbody.find('.colorful-status__container'),
                $seditor  = $sbody.find('[data-type=stream-editor]'),
                $textarea = $seditor.find('textarea');

            if ( json.success ) {
                if ($colorfulContainer.length) {
                    $colorfulContainer.find('.colorful-status__inner').html(json.data)
                } else {
                    $scontent.html( '<span>' + json.data + '</span>' );    
                }
                $scontent.html( '<span>' + json.data + '</span>' );
                $textarea.val( json.unparsed );
            } else {
                $textarea.val( origText );
            }

            $seditor.hide();
            $colorfulContainer.length || $scontent.show();

            joms.parseEmoji();
        }
    });
}

function save( id, el ) {
    var $stream   = $( el ).closest('.joms-js--stream'),
        $sbody    = $stream.find('.joms-stream__body'),
        $seditor  = $sbody.find('[data-type=stream-editor]'),
        $textarea = $seditor.find('textarea'),
        value     = $textarea.val();

    if ($textarea[0].joms_hidden) {
        value = $textarea[0].joms_hidden.val();
    }

    editSave( id, value, value );
}

function cancel( id ) {
    var $stream   = $( '.joms-js--stream-' + id ),
        $sbody    = $stream.find('.joms-stream__body'),
        $scontent = $sbody.find('[data-type=stream-content]'),
        $seditor  = $sbody.find('[data-type=stream-editor]');

    $seditor.hide();
    $scontent.show();
}

function editLocation( id ) {
    joms.popup.stream.editLocation( id );
}

function remove( id ) {
    joms.popup.stream.remove( id );
}

function removeLocation( id ) {
    joms.popup.stream.removeLocation( id );
}

function removeMood( id ) {
    joms.popup.stream.removeMood( id );
}

function removeTag( id ) {
    joms.ajax({
        func: 'activities,ajaxRemoveUserTag',
        data: [ id, 'post' ],
        callback: function( json ) {
            var $stream, $sbody, $soptions, $scontent, $seditor, $textarea;

            if ( json.success ) {
                $stream   = $( '.joms-js--stream-' + id );
                $sbody    = $stream.find('.joms-stream__body');
                $soptions = $stream.find('.joms-list__options').find('.joms-dropdown').find('.joms-js--contextmenu-removetag');
                $scontent = $sbody.find('[data-type=stream-content]');
                $seditor  = $sbody.find('[data-type=stream-editor]');
                $textarea = $seditor.find('textarea');

                $scontent.html( '<span>' + json.data + '</span>' );
                $textarea.val( json.unparsed );
                $soptions.remove();
            }
        }
    });
}

function selectPrivacy( id ) {
    joms.popup.stream.selectPrivacy( id );
}

function share( id ) {
    joms.popup.stream.share( id );
}

function hide( streamId, userId ) {
    joms.ajax({
        func: 'activities,ajaxHideStatus',
        data: [ streamId, userId ],
        callback: function( json ) {
            var streams;

            if ( json.success ) {
                streams = container.find('.joms-stream[data-stream-id=' + streamId + ']');
                streams.fadeOut( 500, function() {
                    streams.remove();
                });
            }
        }
    });
}


function ignoreUser( id ) {
    joms.popup.stream.ignoreUser( id );
}

function showLikes( id, target ) {
    if ( target === 'popup' ) {
        joms.popup.stream.showLikes( id, target );
        return;
    }

    joms.ajax({
        func: 'system,ajaxStreamShowLikes',
        data: [ id ],
        callback: function( json ) {
            var streams;
            if ( json.success ) {
                streams = container.find('.joms-stream[data-stream-id=' + id + ']');
                streams.find('.joms-stream__status').html( json.html || '' );
            }
        }
    });
}

function showComments( id, type ) {
    joms.popup.stream.showComments( id, type );
}

function showOthers( id ) {
    joms.popup.stream.showOthers( id );
}

function report( id, commentid ) {
    joms.popup.stream.report( id, commentid );
}

function addFeatured( id ) {
    joms.popup.stream.addFeatured( id );
}

function removeFeatured( id ) {
    joms.popup.stream.removeFeatured( id );
}

function toggleText( id ) {
    var $text = $( '.joms-js--stream-text-' + id ),
        $full = $( '.joms-js--stream-textfull-' + id ),
        $btn  = $( '.joms-js--stream-texttoggle-' + id );

    if ( $full.is(':visible') ) {
        $full.hide();
        $text.show();
        $btn.html( $btn.data('lang-more') );
    } else {
        $text.hide();
        $full.show();
        $btn.html( $btn.data('lang-less') );
    }
}

function react( uid, reactId, type ) {
    var current = getReaction( reactId),
        text = current.text,
        name = current.name,
        reactClass = 'reaction-btn--' + name,
        $btn = $('.joms-button--reaction[data-uid='+uid+'][data-type=stream]');
    
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
        func: 'system,ajaxStreamAddLike',
        data: [ uid, '', reactId ],
        callback: function( json ) {
            if (json) {
                var $status = $btn.parents('.joms-stream__actions').siblings('.joms-stream__status');
                
                $status.show();
                $status.html(json.html);
                
                var onclick = 'joms.view.stream.unreact('+uid+', '+reactId+')';
                $btn.attr('onclick', onclick);
            }
        }
    });
}

function unreact( uid, reactId ) {
    var $btn = $('.joms-stream__actions .joms-button--reaction[data-uid='+uid+'][data-type=stream]'),
        text = $btn.attr('data-lang-like');

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
        func: 'system,ajaxStreamUnlike',
        data: [ uid, '', reactId ],
        callback: function( json ) {
            if (json) {
                var $status = $btn.parents('.joms-stream__actions').siblings('.joms-stream__status');

                if (!json.html) {
                    $status.hide();
                }

                $status.html(json.html);

                var onclick = 'joms.view.stream.react('+uid+', 1)';
                $btn.attr('onclick', onclick);
            }
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
    start: initialize,
    stop: uninitialize,
    like: like,
    unlike: unlike,
    edit: edit,
    save: save,
    cancel: cancel,
    editLocation: editLocation,
    remove: remove,
    removeLocation: removeLocation,
    removeMood: removeMood,
    removeTag: removeTag,
    selectPrivacy: selectPrivacy,
    share: share,
    hide: hide,
    ignoreUser: ignoreUser,
    showLikes: showLikes,
    showComments: showComments,
    showOthers: showOthers,
    report: report,
    toggleText: toggleText,
    addFeatured: addFeatured,
    removeFeatured: removeFeatured,
    react: react,
    unreact: unreact
};

});
