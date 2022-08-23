define('views/postbox/poll-options', [
    'sandbox',
    'views/base',
    'utils/language'
],

function($, BaseView, language) {
    return BaseView.extend({

        template: {
            list: _.template(
                '<div class="joms-postbox__poll-option-list"></div>\
                <a href="javascript:;" class="joms-postbox-poll__add-option">+ <%= language.add_option %></a>'
            ),

            option: _.template(
                '<div class="joms-postbox__poll-option">\
                    <input class="input-option poll_option" type="text" name="poll_options[]" placeholder="<%= language.hint_add_option %>" maxlength="100">\
                    <a href="javascript:;" class="joms-postbox-poll__remove-option">\
                        <svg viewBox="0 0 16 16" class="joms-icon">\
                            <use xlink:href="<%= window.joms_current_url %>#joms-icon-close"></use>\
                        </svg>\
                    </a>\
                </div>'
            )
        },

        getTemplate: function(type) {
            var html = this.template[type]({
                language: language.get('poll')
            });
            
            return $(html);
        },

        render: function() {
            this.$el.html( this.getTemplate('list') );

            this.$list = this.$el.find('.joms-postbox__poll-option-list');
            this.$list.append( this.getTemplate('option') );
            this.$list.append( this.getTemplate('option') );
        },

        events: $.extend({}, BaseView.prototype.events, {
            'click .joms-postbox-poll__remove-option': 'removeOption',
            'click .joms-postbox-poll__add-option': 'addOption',
        }),

        removeOption: function(e) {
            $(e.currentTarget).parents('.joms-postbox__poll-option').remove();
        },

        addOption: function(e) {
            this.$list.append( this.getTemplate('option') );
            this.$list.find('input').last().focus();
        },

        reset: function() {
            this.$el.html('');
            this.render();
        },

        value: function() {
            var $inputs = this.$list.find('.input-option'),
                options = [];
            
            $inputs.each( function() {
                var val = $(this).val();
                val && options.push(val)
            });

            return {
                options: options
            };
        }

    })
});

