(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.reaction = factory( root, joms.popup.reaction || {}, $);

})( window, joms.jQuery, function( window, sub, $ ) {
    var popup;
    function render( _popup, element, uid, reactId ) {
        popup = _popup;
        
        $(popup.modal).find(".tingle-modal__close").remove();
        reactId = reactId ? reactId : 0;
        joms.ajax({
            func: 'system,ajaxShowReactedUsers',
            data: [element, uid, reactId],
            context:this,
            callback: function( json ) {


                setTimeout( function() {
                    popup.setContent( buildHtml( json ) );
                    var $content = $(popup.getContent());
                   
                    $content.find(".joms-popup__title").data('popup',popup);
                    $content.find(".mfp-close").on('click',$content,function(){popup.close();})
                    
                    var $react = $content.find('li.joms-reacted__item');
                    $react.on('click', getUsersByReaction);
                }, 100);

              
            }
        })
    }

    function getUsersByReaction(event) {
        var $elm = $(event.currentTarget);
        var element = $elm.attr('data-element');
        var reactId = $elm.attr('data-reactid');
        var uid = $elm.attr('data-uid');
        var $modalContent = $(popup.getContent()); 
        var $loading = $modalContent.find('.joms-js--loading');
        var $content = $modalContent.find('.joms-reacted__content');
        var $items = $modalContent.find('li.joms-reacted__item');

        if (!$elm.hasClass('active')) {
            $items.removeClass('active');
            $elm.addClass('active');
            $content.hide();
            $loading.show();

            joms.ajax({
                func: 'system,ajaxGetUsersByReaction',
                data: [element, uid, reactId],
                callback: function( json ) {
                    $content.html(json.html);
                    $content.show();
                    $loading.hide();
                }
            })
        }
    }

    function buildHtml( json ) { 
        return [
            '<div class="joms-popup--whiteblock">',
                '<div class="joms-popup__title">',
                    '<button class="mfp-close"  type="button" title="',window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON_TITLE,'">×</button>',
                    json.title,
                '</div>',
                '<div class="joms-popup__content joms-popup__reacted">', json.html, '</div>',
            '</div>'
        ].join('');
    }

    // Exports.
    return function( element, uid, reactId ) {
        joms.util.dialog.prepare(function( mfp ) {
            render( mfp, element, uid, reactId );
        });
    }
});
