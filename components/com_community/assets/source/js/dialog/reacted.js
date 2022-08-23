(function( root, $, factory ) {
	joms.dialog || (joms.dialog = {});
    joms.dialog.reacted = factory( root, $);

    define([ 'dialog/reacted' ], function() {
        return joms.dialog.reacted;
    });

})( window, joms.jQuery, function( window, $ ) {
	var modal;

	function render( _modal, element, uid, reactId ) {
		modal = _modal;
        reactId = reactId ? reactId : 0;
        joms.ajax({
            func: 'system,ajaxShowReactedUsers',
            data: [element, uid, reactId],
            callback: function( json ) {
                setTimeout( function() {
                modal.setContent( json.html );
                var $content = $(modal.getContent());
                var $react = $content.find('li.joms-reacted__item');
                $react.on('click', getUsersByReaction);
                }, 5000);

            }
        })
    }

    function getUsersByReaction(e) {
        var $elm = $(e.currentTarget);
        var element = $elm.attr('data-element');
        var reactId = $elm.attr('data-reactid');
        var uid = $elm.attr('data-uid');
        var $modalContent = $(modal.getContent()); 
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

	return function( element, uid, reactId ) {
		joms.util.dialog.prepare( function( modal ) {
			render( modal, element, uid, reactId );
		})
	}
});