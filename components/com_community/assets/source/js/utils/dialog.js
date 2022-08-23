define([ 'core' ], function() {
	var getScrollbarWidth = function getScrollbarWidth() {
		var outer = document.createElement("div");

		outer.style.visibility = "hidden";
		outer.style.width = "100px";
		outer.style.msOverflowStyle = "scrollbar";

		document.body.appendChild(outer);

		var widthNoScroll = outer.offsetWidth;

		outer.style.overflow = "scroll";

		var inner = document.createElement("div");

		inner.style.width = "100%";
		outer.appendChild(inner);        

		var widthWithScroll = inner.offsetWidth;

		outer.parentNode.removeChild(outer);

		return widthNoScroll - widthWithScroll;
	}

	var $ = joms.jQuery;
	var $body = $(document.body);
	var cachedStyle = '';
	var scrollbarWidth = getScrollbarWidth();
	var Dialog = function Dialog() {};

	Dialog.prototype.prepare = function prepare( callback ) {
		var modal = this.show();
		callback = callback || function() {};
		callback( modal )
	}

	Dialog.prototype.show = function show() {
		var modal = new window.tingle.modal({
			closeMethods: ['overlay', 'button', 'escape'],
			closeLabel: window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON,
			cssClass: ['joms-dialog'],
			onClose: function() {
				var $content = joms.jQuery(modal.getContent());
				var $container = $content.parents('.tingle-modal');
				
				$container.remove();
			},

			beforeClose: function() {
				$body.attr('style', cachedStyle ? cachedStyle : '');
				return true;
			},

			beforeOpen: function() {
				cachedStyle = $body.attr('style');
				var paddingRight = parseInt($body.css('paddingRight'));
				$body.css('padding-right', scrollbarWidth + paddingRight);
			}
		});

		var loader = '<div style="width: 100%; text-align: center;"><img src="' + joms.BASE_URL + 'components/com_community/assets/ajax-loader.gif" /></div>';
		modal.setContent(loader);

		modal.open();

		return modal;
	}

	
	joms.util || (joms.util = {});
	joms.util.dialog = new Dialog();
});