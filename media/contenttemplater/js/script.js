/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var ContentTemplater = null;

(function($) {
	"use strict";

	$(document).ready(function() {
		ContentTemplater.init();
	});

	ContentTemplater = {
		// private property
		keyStr    : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
		timer     : null,
		link      : null,
		text_types: [
			'text',
			'textarea',
			'color',
			'date',
			'datetime-local',
			'email',
			'month',
			'number',
			'range',
			'search',
			'tel',
			'time',
			'url',
			'week'
		],

		init: function(timeout) {
			if ( ! $('[class*=" rl_ct_button"]').length) {
				timeout += 100;
				setTimeout(function() {
					ContentTemplater.init();
				}, timeout);

				return;
			}

			$('.contenttemplater-default-item').closest('.btn').remove();
			$('.contenttemplater-default-item').closest('.mce-btn-group').remove();

			$('.contenttemplater-list-up').append(' <span class="icon-arrow-up-3"></span>');
			$('.contenttemplater-list-down').append(' <span class="icon-arrow-down-3"></span>');

			this.link = $('[class*=" rl_ct_button"]').first();
			$('a, button').mouseenter(function() {
				ContentTemplater.link = this;
			});

			const ct_delayed_load = JSON.parse(sessionStorage.getItem('ct_delayed_load'));
			if (ct_delayed_load) {
				this.loadTemplate(ct_delayed_load.id, ct_delayed_load.editorname, ct_delayed_load.article_id, true, false, true);
			}
			sessionStorage.removeItem('ct_delayed_load');
		},

		showList: function(id, editorname) {
			// Escape special characters inside the id
			editorname = editorname.replace(/(:|\.|\[|\]|,|=|@)/g, '\\$1');

			let list = $('#contenttemplater-list-' + editorname + '-' + id);

			if ( ! list.length) {
				return;
			}

			list = list.clone();

			setTimeout(function() {
				$(document).one('click', function() {
					list.remove();
				});
			}, 10);

			this.fadeOut(list);

			$('body').append(list);

			const ul = list.find('ul').first();

			list.css('left', this.getOffsetLeft(ul)).css('top', this.getOffsetTop(ul));
		},

		getOffsetLeft: function(el) {
			const windowWidth = $(window).width() + $(window).scrollLeft();

			if ( ! $(this.link).length) {
				return windowWidth - ($(window).width() / 2) - (el.outerWidth() / 2); // Use middle of screen as fallback
			}

			const listMargin = 4;
			let offsetLeft   = $(this.link).offset().left;
			const listWidth  = el.outerWidth() + listMargin;

			// Set offset to listmargin if it is too much to the left
			if (offsetLeft < $(window).scrollLeft() + listMargin) {
				offsetLeft = $(window).scrollLeft() + listMargin;
			}

			// Check if the list fits on the right side of the window
			if (windowWidth >= offsetLeft + listWidth) {
				return offsetLeft;
			}

			// if it fall outside the right window margin, place the list as right as possible
			return windowWidth - listWidth;
		},

		getOffsetTop: function(el) {
			const windowHeight = $(window).height() + $(window).scrollTop() - 31; // subtract the bottom status bar

			if ( ! $(this.link).length) {
				return windowHeight - ($(window).height() / 2) - (el.outerHeight() / 2); // Use middle of screen as fallback
			}

			const listMargin = 4;
			const listHeight = el.outerHeight() + listMargin;
			const offsetTop  = $(this.link).offset().top + $(this.link).outerHeight();

			// Check if the list fits on the right side of the window
			if (windowHeight >= offsetTop + listHeight) {
				return offsetTop;
			}

			// if it fall outside the bottom window margin, place the list above the button
			// @Todo This makes no sense... what to check for?
			const bottomMargin = $(this.link).offset().top - listHeight;
			if (bottomMargin >= offsetTop) {
				return offsetTop;
			}

			// if it then falls outside the top window margin, place the list as low as possible
			return windowHeight - listHeight;
		},

		fadeOut: function(element) {
			let fadeOut       = null;
			let fadeOutRemove = null;

			element.on('mouseenter', function() {
				clearInterval(fadeOut);
				clearInterval(fadeOutRemove);
				element.stop(true, true).show();
			});

			const func = (function() {
				clearInterval(fadeOut);
				element.fadeOut(2000);
				fadeOutRemove = setTimeout(function() {
					element.remove();
				}, 2500);
			});

			fadeOut = setTimeout(function() {
				func();
			}, 5000);

			element.on('mouseleave', function() {
				fadeOut = setTimeout(function() {
					func();
				}, 5000);
			});
		},

		loadTemplate: function(id, editorname, articleID, noContent, isModal, onlyFields) {
			const overlay = $('<div id="contenttemplater-overlay"/>').css({
				backgroundColor: 'black',
				position       : 'fixed',
				left           : 0,
				top            : 0,
				width          : '100%',
				height         : '100%',
				zIndex         : 5000,
				opacity        : 0.4
			}).hide().on('click', function() {
				this.remove();

				if (isModal) {
					window.parent.SqueezeBox.close();
				}
			}).appendTo('body');

			overlay.css('cursor', 'wait').fadeIn();

			noContent  = noContent ? 1 : 0;
			onlyFields = onlyFields ? 1 : 0;

			const url = 'index.php?rl_qp=1&folder=plugins.editors-xtd.contenttemplater&file=data.php&id=' + id + '&article_id=' + articleID + '&no_content=' + noContent + '&only_fields=' + onlyFields;

			RegularLabsScripts.loadajax(url, 'ContentTemplater.insertTexts( ' + id + ', data, \'' + editorname + '\', ' + articleID + ', ' + (isModal ? 'true' : 'false') + ' )');
		},

		insertTexts: function(id, data, editorname, articleID, isModal) {
			data = this.decode(data);
			data = data.split('[/CT]');

			const params = {};

			for (let i = 0; i < data.length; i++) {
				if (data[i].indexOf('[CT]') < 0) {
					continue;
				}

				const values = data[i].split('[CT]');
				const key    = values[1].trim();

				params[key] = {
					'default': values[2].trim(),
					'value'  : values[3].trim()
				};
			}

			let overrideContent  = false;
			let overrideSettings = false;
			let hasContent       = false;

			// check if settings override is set and if template has content
			for (let key in params) {
				const param = params[key];

				if (key === 'override_content') {
					overrideContent = param['value'] === '1';
					continue;
				}

				if (key === 'override_settings') {
					overrideSettings = param['value'] === '1';
					continue;
				}

				if (key === 'content' && param['value'].length !== 0) {
					hasContent = true;
				}
			}

			const catids = {};

			// set all content settings
			for (let key in params) {
				if (key == 'content') {
					continue;
				}

				const param = params[key];

				if (param['value'] == -1) {
					continue;
				}

				let fieldValue = this.getValue(key);

				if (fieldValue === null) {
					fieldValue = '';
				}

				if (fieldValue === param['value']) {
					continue;
				}

				if (key === 'jform[language]' && fieldValue === '*' && param['default'] === '') {
					fieldValue = param['default'];
				}

				if ( ! overrideSettings
					&& fieldValue !== param['default']
				) {
					continue;
				}

				if (key.indexOf('catid') > -1) {
					catids[key] = param['value'];

					continue;
				}

				this.setValue(key, param['value'], overrideSettings);
			}

			// insert content
			if (hasContent && editorname) {
				for (let key in params) {
					if (key != 'content' || ! params[key]['value'].length) {
						continue;
					}

					const ed = document.getElementById(editorname);

					if (ed && typeof ed.ctdone !== 'undefined' && (overrideSettings || ! overrideContent)) {
						continue;
					}

					this.jInsertEditorText(params[key]['value'], editorname, overrideContent);
				}
			}

			for (let key in catids) {
				this.setValue(key, catids[key]);
				sessionStorage.setItem('ct_delayed_load', JSON.stringify({'id': id, 'editorname': editorname, 'article_id': articleID}));
			}

			$('#contenttemplater-overlay').remove();

			if (isModal) {
				window.parent.SqueezeBox.close();
			}
		},

		jInsertEditorText: function(value, editor, replace, count) {
			const self  = this;
			const ed    = document.getElementById(editor);
			replace     = replace ? true : false;
			count       = (count == null) ? 1 : ++count;
			let success = false;

			// check id the editor is finished loading for max 17.5 seconds
			// 5 * 500ms
			// 5 * 1000ms
			// 5 * 2000ms
			if (count < 15) {
				const wait = (count > 10) ? 2000 : (count > 5) ? 1000 : 500;
				try {
					let text = value;
					if (ed) {
						if (ed.className != '' && ed.className == 'mce_editable'
							&& text.substr(0, 3) == '<p>' && text.substr(text.length - 4, 4) == '</p>'
						) {
							text = text.substr(3, text.length - 7);
						}

						this.placeContent(text, replace, editor);

						success = true;
					}
				} catch (err) {
				}

				if ( ! success) {
					this.timer = window.setTimeout(function() {
						self.jInsertEditorText(value, editor, replace, count);
					}, wait);

					return;
				}
			}

			window.clearTimeout(this.timer);

			if ( ! ed) {
				alert('Could not find the editor!');
			}
		},

		placeContent: function(text, replace, editor) {
			if (replace) {
				this.replaceContent(text, editor);
				return;
			}

			this.insertContent(text, editor);
		},

		insertContent: function(text, editor) {
			let ed = document.getElementById(editor);

			Joomla.editors.instances[editor] && Joomla.editors.instances[editor].replaceSelection(text);

			if ( ! Joomla.editors.instances[editor]) {
				jInsertEditorText(text, editor);
			}

			if (typeof tinyMCE !== 'undefined') {
				ed = tinyMCE.get(editor);

				if (ed) {
					ed.formatter.apply();
					ed.value += text;
				}
			}
		},

		replaceContent: function(text, editor) {
			let ed = document.getElementById(editor);

			Joomla.editors.instances[editor] && Joomla.editors.instances[editor].setValue(text);

			if ( ! Joomla.editors.instances[editor]) {
				jInsertEditorText(text, editor);
			}

			if (typeof tinyMCE !== 'undefined') {
				ed = tinyMCE.get(editor);

				if (ed) {
					ed.formatter.apply();
					ed.value = text;
				}
			}
		},

		getValue: function(key) {
			const id = this.getIdFromName(key);

			let element = document.getElementById(id);

			if ( ! element && typeof document.adminForm !== 'undefined' && typeof document.adminForm.elements !== 'undefined') {
				element = document.adminForm.elements[key];
				if ( ! element) {
					element = document.adminForm.elements[key + '[]'];
				}
			}

			if ( ! element) {
				return null;
			}

			if (element.type == 'textarea') {
				const editor = Joomla.editors.instances[id];
				if (typeof editor !== 'undefined') {
					return editor.getValue().replace(/^<p><\/p>$/, '');
				}
			}

			let value = element.value ? element.value : '';

			if (element.type == 'select-one') {
				if (element.type == 'checkbox' && ! element.checked) {
					return '';
				}

				return value;
			}

			if (element.type == 'fieldset') {

				const elements = element.querySelectorAll('input');

				elements.forEach((el) => {
					if ((el.type == 'checkbox' || el.type == 'radio') && el.checked) {
						value = el.value;
						return;
					}
					if (el.selected) {
						value = el.value;
					}
				});
			}

			return value;
		},

		setValue: function(key, value, override_settings) {
			const self = this;

			if (value == '-empty-') {
				value = '';
			}

			const $els = this.getElements(key);

			$els.each(function(i, el) {
				const $el = $(el);

				if ( ! self.text_types.indexOf(el.type) > -1) {
					$el.removeAttr("selected").removeAttr("checked");
					$el.find("option:selected").removeAttr("selected");
				}
			});

			$els.each(function(i, el) {
				const $el = $(el);

				if (el.type == 'textarea') {
					self.jInsertEditorText(value.toString(), el.id, override_settings);
					const ed  = document.getElementById(el.id);
					ed.ctdone = true;
				}

				if (self.text_types.indexOf(el.type) > -1) {
					if ($el.attr('data-alt-value') !== undefined) {
						$el.attr('data-alt-value', value.toString());
					}
					$el.val(value.toString());
					$el.change();

					return;
				}

				const values       = value.replace('\\,', '[:COMMA:]').split(',');
				const valuesLength = values.length;

				for (let i = 0; i < valuesLength; i++) {
					const val = values[i].toString().replace('[:COMMA:]', ',');

					if (el.type.substr(0, 6) === 'select') {
						$el.find('option[value="' + val + '"]').attr("selected", "selected");
						$el.trigger('liszt:updated');

						continue;
					}

					if ($el.val() === val) {
						$('label[for="' + $el.attr('id') + '"]').trigger('click');
						$el.attr("checked", "checked");
					}

					$el.val(val);
				}

				if ($el.hasClass('field-user-input')) {
					$('#' + $el.attr('id').replace(/_id/, '') + '.field-user-input-name').val('User: ' + value);
				}

				$el.change();
			});
		},

		getElements: function(key) {
			const types = ['input', 'select', 'textarea'];
			const names = [key.replace(/\[/g, '\\[').replace(/\]/g, '\\]')];

			const frontendkey = this.getFrontendKey(key);

			if (frontendkey != key) {
				names.push(frontendkey.replace(/\[/g, '\\[').replace(/\]/g, '\\]'));
			}

			const cleankey = frontendkey.replace(/^.*\[(.*)\]$/g, '$1');

			if (cleankey != key && cleankey != frontendkey) {
				names.push(cleankey);
			}

			const selects = [];

			for (let t = 0, tlen = types.length; t < tlen; t++) {
				for (let n = 0, nlen = names.length; n < nlen; n++) {
					selects.push(types[t] + '[name=' + names[n] + ']');
					selects.push(types[t] + '[name=' + names[n] + '\\[\\]]');
					selects.push(types[t] + '[name=jform\\[' + names[n] + '' + '\\]]');
					selects.push(types[t] + '[name=jform\\[' + names[n] + '' + '\\]\\[\\]]');
				}
			}

			return $(selects.join(','));
		},

		getIdFromName: function(string) {
			return string.replace(/[\[\]-]/g, '_').replace('__', '_').replace(/_$/, '');
		},

		getFrontendKey: function(key) {

			return key.replace('details', '');
		},

		decode: function(input) {
			let output = "";
			let chr1, chr2, chr3;
			let enc1, enc2, enc3, enc4;
			let i      = 0;

			input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

			while (i < input.length) {
				enc1 = this.keyStr.indexOf(input.charAt(i++));
				enc2 = this.keyStr.indexOf(input.charAt(i++));
				enc3 = this.keyStr.indexOf(input.charAt(i++));
				enc4 = this.keyStr.indexOf(input.charAt(i++));

				chr1 = (enc1 << 2) | (enc2 >> 4);
				chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
				chr3 = ((enc3 & 3) << 6) | enc4;

				output = output + String.fromCharCode(chr1);

				if (enc3 != 64) {
					output = output + String.fromCharCode(chr2);
				}
				if (enc4 != 64) {
					output = output + String.fromCharCode(chr3);
				}

			}

			return this.utf8_decode(output);
		},

		utf8_decode: function(utftext) {
			let string = "";
			let i      = 0;

			while (i < utftext.length) {
				const c = utftext.charCodeAt(i);
				i++;

				if (c < 128) {
					string += String.fromCharCode(c);
					continue;
				}

				const c2 = utftext.charCodeAt(i);
				i++;

				if ((c > 191) && (c < 224)) {
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					continue;
				}

				const c3 = utftext.charCodeAt(i);
				i++;

				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			}

			return string;
		}
	};
})(jQuery);
