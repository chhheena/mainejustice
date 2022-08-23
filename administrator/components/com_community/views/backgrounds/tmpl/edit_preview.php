<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<h3 style="text-align: center"><?php echo JText::_('COM_COMMUNITY_PREVIEW') ?></h3>
<div class="color-container">
	<div class="placeholder" unselectable="on">Put your text here</div>
	<div id="your_editable_element" contenteditable="true">
		<div><br></div>
	</div>
	
</div>

<script>
jQuery(document).ready(function($) {
	var isComposing = function(key) {
		return key === 32
			|| ( key > 47 && key < 91)
			|| ( key > 95 && key < 112)
			|| ( key > 183 && key < 224);
	}

	var $container = $('.color-container'),
		$content = $('#your_editable_element'),
		$placeholder = $('.placeholder'),
		ie = !!navigator.userAgent.match(/Trident.*rv\:11\./),
		BACKSPACE = 8,
		ENTER = 13,
		DELETE = 46,
		LIMIT = 150,
		android = navigator.userAgent.toLowerCase().indexOf("android") > -1;

	var $key = $('.key'),
		newText,
		oldOffset,
		newOffset,
		selection;

	$content.on('keydown', function(e) {
		selection = window.getSelection();
		oldOffset = selection.focusOffset;

		var remove = [BACKSPACE , DELETE].indexOf(e.keyCode) > -1 ,
			enter = e.keyCode === ENTER,
			el = this;

		if ( !android && remove ) {
			var html = $content.html().trim();

			if(html === '<div><br></div>') { 
				e.preventDefault();
			} else if (!html) {
				e.preventDefault();
				$content.html('<div><br></div>');
				setCaret(el);
			}
			return;
		}

		var content = $content.text(),
			contentLength = content.length;

		if ( enter ) {
			var numChild = $content.children().length;

			if ( contentLength >= LIMIT || numChild > 3 ) {
				e.preventDefault();
				return;
			}

			var selection = window.getSelection(),
				$focusNode =$(selection.focusNode),
				text = $focusNode.text();

			if (!text) {
				e.preventDefault();
				return;
			}
		}

		if ( !android && isComposing(e.keyCode) && !e.ctrlKey && contentLength > LIMIT) {
			e.preventDefault();
		}
	})
	.on('keyup', function(e) {
		$('.key').text($content.html());
		var remove = ([BACKSPACE , DELETE].indexOf(e.keyCode) > -1 ),
			el = this;

		if ( remove || android ) {
			var html = $content.html().trim();

			if (html === '<br>' || !html) {
				e.preventDefault();
				$content.html('<div><br></div>');
				setCaret(el);
			}
		}
	})
	.on('input', function(e) {
		if (android) {
			selection = window.getSelection();
			newOffset = selection.focusOffset;
			newText = $content.text();

			if (newText.length > LIMIT ) {
				var range = document.createRange();
				range.setStart(selection.focusNode, oldOffset);
      			range.setEnd(selection.focusNode, newOffset);
      			range.deleteContents();
			}
		}

		var content = $content.text(),
			numChild = $content.children().length;

		if (content || numChild > 1) {
			$placeholder.hide();
		} else {
			$placeholder.show();
		}
	})
	.on('paste', function(e) {
		e.preventDefault();
	})

	function setCaret(el, position) {
	    var range = document.createRange(),
	    	sel = window.getSelection();
	    el.childNodes[0];
	    range.setStart(el.childNodes[0], position || 0);
	    range.collapse(true);
	    sel.removeAllRanges();
	    sel.addRange(range);
	    el.focus();
	}

	if (ie) {
		var element = $content[0],
			observer = new MutationObserver(function (mutations) {
				mutations.forEach(function (mutation) {
					var content = $content.text(),
						numChild = $content.children().length;

					if (content || numChild > 1) {
						$placeholder.hide();
					} else {
						$placeholder.show();
					}
				})
			}
		);

		observer.observe(element, {
			childList: true,
			characterData: true,
			subtree: true,
		});
	}

	var initPreview = function() {
		var $background = $('.backgroundImage'), 
		 	image = $background.attr('src'),
		 	$text = $('#scss-color-text'),
			textColor = $text.val(),
			$placeholderInput = $('#scss-color-placeholder');
			placeholderColor = $placeholderInput.val();

		image && $container.css('background-image', 'url(\''+ image +'\'');
		textColor && $content.css('color', '#'+textColor);
		placeholderColor && $placeholder.css('color', '#'+ placeholderColor);

		var $input = $('input#background_image');
		$input.on('change', function() {
			if (this.files && this.files[0]) {
				var reader = new FileReader();

				reader.onload = function(e) {
					$background.attr('src', e.target.result);
					$container.css('background-image', 'url(\''+ e.target.result +'\'');
				}

				reader.readAsDataURL(this.files[0]);
			}
		})

		$text.on('change', function() {
			$content.css('color', '#'+ $text.val());
		})

		$placeholderInput.on('change', function() {
			$placeholder.css('color', '#'+ $placeholderInput.val());
		})
	}

	initPreview();
});
	
</script>