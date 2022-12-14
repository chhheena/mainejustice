/**
 * @package         Conditional Content
 * @version         4.0.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsConditionalContentPopup = null;

(function($) {
    "use strict";

    RegularLabsConditionalContentPopup = {
        conditions: [
            'menuitems',
            'homepage',
            'date',
            'seasons',
            'months',
            'days',
            'time',
            'accesslevels',
            'usergrouplevels',
            'users',
            'languages',
            'ips',
            'continents',
            'countries',
            'regions',
            'postalcodes',
            'templates',
            'urls',
            'devices',
            'os',
            'browsers',
            'components',
            'tags',
            'contentpagetypes',
            'cats',
            'articles',
            'php'
        ],

        init: function() {
            $('.reglab-overlay').css('cursor', '').fadeOut();
        },

        insertText: function() {
            window.parent.jInsertEditorText(this.getOutput(), conditionalcontent_editorname);

            return true;
        },

        getOutput: function() {
            return this.getTag() + this.getContents() + this.getTagClose();
        },

        getTag: function() {
            return conditionalcontent_tag_characters[0]
                + (this.getTagType() + ' ' + this.getConditions()).trim()
                + conditionalcontent_tag_characters[1];
        },

        getTagClose: function() {
            return conditionalcontent_tag_characters[0]
                + '/' + this.getTagType()
                + conditionalcontent_tag_characters[1];
        },

        getTagType: function() {
            var tag_type = RegularLabsForm.getValue('tag_type');

            return tag_type == 'hide' ? conditionalcontent_tag_hide : conditionalcontent_tag_show;
        },

        getConditions: function() {
            var self = this;

            var conditions = [];

            var matching_method = RegularLabsForm.getValue('matching_method');
            if (matching_method == 'any' || matching_method == 'or') {
                conditions.push('matching_method="any"');
            }

            $.each(this.conditions, function(i, name) {
                var mode = RegularLabsForm.getValue(name);

                if ( ! mode || mode == 0) {
                    return;
                }

                var selection = mode == 2 ? 'false' : 'true';
                var operator  = '=';

                if (name == 'php') {
                    var php_editor = Joomla.editors.instances[name + '_selection'];
                    var php        = php_editor.getValue();

                    operator = mode == 2 ? '!=' : '=';

                    php = php.replace(/\n/g, '<br>');
                    php = php.replace(/"/g, '\\"');

                    conditions.push(name + operator + '"' + php + '"');

                    return;
                }

                if ($('[name="' + name + '_selection"]').length || $('[name="' + name + '_selection[]"]').length) {
                    selection = RegularLabsForm.getValue(name + '_selection');

                    if (typeof selection !== 'string') {
                        for (var i = 0; i < selection.length; i++) {
                            if (typeof selection[i] !== 'string') {
                                continue;
                            }

                            selection[i] = selection[i].replace(/,/g, '\\,');
                        }

                        var glue = RegularLabsForm.getValue(name + '_match_all') ? ' + ' : ',';

                        selection = selection.join(glue);
                    }

                    operator = mode == 2 ? '!=' : '=';
                }

                switch (name) {
                    case 'date':
                    case 'time':
                        var sel_op = self.getDateTimeRange(mode, name + '_publish_up', name + '_publish_down');
                        selection  = sel_op[0];
                        operator   = sel_op[1];

                        if (selection == '') {
                            return;
                        }

                        break;

                    case 'ips':
                    case 'urls':
                        selection = selection.replace(/,/g, '\\,').replace(/\n/g, ',');

                        break;
                }

                conditions.push(name + operator + '"' + selection.replace(/"/g, '\\"') + '"');

                switch (name) {
                    case 'urls':
                        if (RegularLabsForm.getValue('urls_casesensitive')) {
                            conditions.push('urls_casesensitive="true"');
                        }

                        if (RegularLabsForm.getValue('urls_regex')) {
                            conditions.push('urls_regex="true"');
                        }

                        break;
                }
            });

            return conditions.join(' ');
        },

        getContents: function() {
            var tag_type         = RegularLabsForm.getValue('tag_type');
            var use_content_else = RegularLabsForm.getValue('use_content_else');

            var content_editor = Joomla.editors.instances['content'];
            var content        = content_editor.getValue();

            if (use_content_else) {
                var content_else_editor = Joomla.editors.instances['content_else'];
                var content_else        = content_else_editor.getValue();

                content += conditionalcontent_tag_characters[0]
                    + (tag_type == 'hide' ? conditionalcontent_tag_hide : conditionalcontent_tag_show) + '-else'
                    + conditionalcontent_tag_characters[1]
                    + content_else;
            }

            return content;
        },

        getDateTimeRange: function(mode, publish_up, publish_down) {
            publish_up   = RegularLabsForm.getValue(publish_up);
            publish_down = RegularLabsForm.getValue(publish_down);

            publish_up   = publish_up ? publish_up.trim() : '';
            publish_down = publish_down ? publish_down.trim() : '';

            if ( ! publish_up
                || publish_up == '0000-00-00 00:00'
                || parseInt(publish_up) < 0
            ) {
                publish_up = '';
            }

            if ( ! publish_down
                || publish_down == '0000-00-00 00:00'
                || parseInt(publish_down) < 0
            ) {
                publish_down = '';
            }

            if ( ! publish_up && ! publish_down) {
                return ['', ''];
            }

            var operator = mode == 2 ? '!=' : '=';

            if ( ! publish_down) {
                return [publish_up, operator];
            }

            if ( ! publish_up) {
                return ['to ' + publish_down, operator];
            }

            return [publish_up + ' to ' + publish_down, operator];
        }
    };

    $(document).ready(function() {
        setTimeout(function() {
            RegularLabsConditionalContentPopup.init();
        }, 1000);
    });
})(jQuery);
