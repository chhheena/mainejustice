/**
 * @package         DB Replacer
 * @version         7.4.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RLDBReplacer = null;

(function($) {
	"use strict";

	RLDBReplacer = {
		params: {}, // holds the form values

		initialize: function() {
			this.updateColumns();
		},

		getXML: function(field, params) {
			if ( ! field) {
				field = 'columns';
			}

			var self = this;

			this.startLoad(field);

			$.ajax({
				type   : 'post',
				url    : 'index.php?rl_qp=1&folder=administrator.components.com_dbreplacer&file=ajax.php&field=' + field + '&params=' + btoa(encodeURIComponent(params)),
				success: function(data) {
					self.insertData(data, field);
				},
				error  : function(data) {
					var text = DBR_INVALID_QUERY;
					var pos  = data.statusText.indexOf('SQL=SELECT');
					if (pos > 1 && data.statusText.indexOf('You have an error') < 0) {
						text = text + '<br />' + data.statusText.substr(0, pos);
					}
					self.insertData(
						'<div class="alert alert-danger">' + text + '</div>',
						field);
				}
			});
		},

		updateColumns: function(clear) {
			this.hideReplace();
			this.setParams();
			this.updateField('columns', clear);
		},

		updateRows: function() {
			this.setParams();
			this.updateField('rows');
		},

		hideReplace: function() {
			$('#dbr_submit a').attr('onclick', 'return false;');
			$('#dbr_submit').fadeTo('fast', 0.1);
		},

		showReplace: function() {
			$('#dbr_submit a').attr('onclick', 'submitform();');
			$('#dbr_submit').fadeTo('fast', 1);
		},

		clearSearch: function() {
			var form          = document.adminForm;
			form.search.value = '';
			form.case.checked = false;
		},

		clearReplace: function() {
			var form           = document.adminForm;
			form.replace.value = '';
		},

		clearWhere: function() {
			var form         = document.adminForm;
			form.where.value = '';
		},

		setParams: function() {
			var self = this;

			$('.dbr #dbr-table, .dbr #dbr-columns, .dbr .element').each(function(i, el) {
				var value = el.value;

				switch (el.type) {
					case 'checkbox':
						value = (el.checked) ? el.value : '';
						break;
					case 'radio':
					case 'select-multiple':
						value = self.multipleSelectValues(el);
						break;
				}

				var element_name = el.name.replace('[]', '');

				self.params[element_name] = value;
			});
		},

		protectSpaces: function() {
			$('.dbr .element').each(function(i, el) {
				if (el.type == 'textarea') {
					el.value = el.value.replace(/^ /, '||space||').replace(/ $/, '||space||');
				}
			});
		},

		toggleInactiveColumns: function() {
			$('#dbr_results').toggleClass('hide-inactive');
		},

		createTrimmedTogglers() {
			var self = this;

			$('.show-trimmed, .hide-trimmed').remove();

			$('.trimmed').each(function(i, el) {
				$(el).before(
					$('<span class="toggle-trimmed" id="' + el.id + '-toggle">')
						.click(function() {
							self.toggleTrimmed(el.id);
						})
				);
			});
		},

		toggleTrimmed: function(id) {
			if ($('#' + id + '-toggle').hasClass('hide-trimmed')) {
				this.hideTrimmed(id);
				return;
			}

			this.showTrimmed(id);
		},

		showTrimmed: function(id) {
			$('#' + id).show();
			$('#' + id + '-toggle').addClass('hide-trimmed');
		},

		hideTrimmed: function(id) {
			$('#' + id).hide();
			$('#' + id + '-toggle').removeClass('hide-trimmed');
		},

		updateField: function(type, clear) {
			if (clear) {
				this.params[type] = '';
			}
			this.getXML(type, JSON.stringify(this.params));
		},

		removeActions: function() {
			$('.dbr #dbr-table, .dbr #dbr-columns, .dbr .element')
				.unbind('click.dbreplacer')
				.unbind('keyup.dbreplacer')
				.unbind('change.dbreplacer');
		},

		updateActions: function() {
			var self = this;

			this.removeActions();

			var updateColumns = (function() {
				self.updateColumns(true);
			});

			var hideReplace = (function() {
				self.hideReplace();
			});

			$('.dbr #dbr-table').bind('change.dbreplacer keyup.dbreplacer', updateColumns);

			$('.dbr #dbr-columns').bind('change.dbreplacer keyup.dbreplacer', hideReplace);

			$('.dbr .element').each(function(i, el) {
				switch (el.type) {
					case 'radio':
					case 'checkbox':
						$(el).bind('click.dbreplacer keyup.dbreplacer', hideReplace);
						break;
					case 'select':
					case 'select-one':
					case 'select-multiple':
					case 'text':
					case 'textarea':
						$(el).bind('change.dbreplacer keyup.dbreplacer', hideReplace);
						break;
					default:
						$(el).bind('change.dbreplacer', hideReplace);
						break;
				}
			});
		},

		startLoad: function(field) {
			$('#dbr_' + field).css('opacity', 0.2);
		},

		finishLoad: function(field) {
			this.updateActions();
			$('#dbr_' + field).fadeTo('fast', 1);
		},

		insertData: function(data, field) {
			var el = $('#dbr_' + field);

			if (el) {
				el.html(data);
			}

			if (field == 'rows') {
				if (data.indexOf('<span class="replace_string">') > -1) {
					this.showReplace();
				} else {
					this.hideReplace();
				}
			}

			this.finishLoad(field);
		},

		multipleSelectValues: function(el) {
			var vals = [];
			for (var j = 0; j < el.options.length; j++) {
				if (el.options[j].selected) {
					vals[vals.length] = el.options[j].value;
				}
			}
			return vals.join(',');
		}
	};

	$(document).ready(function() {
		RLDBReplacer.initialize();
	});
})(jQuery);
