define('views/postbox/poll-settings', [
    'sandbox',
    'views/base',
    'utils/language'
],

function($, BaseView, language) {
    return BaseView.extend({

        template: _.template(
            '<!--<div class="settings-item">\
                <input type="checkbox" name="allow_add">\
                <span style="cursor:pointer" class="settings-item--label">Allow any one to add options</span>\
            </div>-->\
            <div class="settings-item">\
                <input type="checkbox" name="allow_multiple">\
                <span style="cursor:pointer" class="settings-item--label"><%= language.allow_multiple_choices %></span>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                language: language.get('poll')
            });
            return $(html);
        },

        render: function() {
            this.$el.html( this.getTemplate() );
        },

        events: $.extend({}, BaseView.prototype.events, {
            'click .settings-item--label': 'toggleCheckbox',
        }),

        toggleCheckbox: function(e) {
            var $checkbox = $(e.currentTarget).siblings('input[type=checkbox]');

            $checkbox.prop('checked', !$checkbox.prop('checked'));

        },

        reset: function() {
            this.$el.html('');
            this.render();
        },

        value: function() {
            var allow_add = this.$el.find('input[name=allow_add]'),
                allow_multiple = this.$el.find('input[name=allow_multiple]');
            return {
                settings: {
                    allow_add: allow_add.prop('checked'),
                    allow_multiple: allow_multiple.prop('checked')
                }
            }
        }
    })
});
