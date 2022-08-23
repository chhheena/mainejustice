define('views/inputbox/base', [
    'sandbox',
    'views/base',
    'utils/constants'
],
// definition
// ----------
function ($, BaseView, constants) {

    return BaseView.extend({
        events: {
            'focus .input.joms-textarea': 'onFocus',
            'focus .input': 'onFocus',
            'keydown .input.joms-textarea': 'onKeydownProxy',
            'keydown .input': 'onKeydownProxy',

            'input .input.joms-textarea': 'onInput',
            'input .input': 'onInput',

            'paste .input.joms-textarea': 'onPaste',

            'blur .input.joms-textarea': 'onBlur',
            'blur .input': 'onBlur'
        },
        initialize: function (options) {
            if (!$.mobile) {
                this.onInput = this.onKeydown;
                this.onKeydownProxy = this.onKeydown;
                this.updateCharCounterProxy = this.updateCharCounter;
            }

            // flags
            options || (options = {});
            this.flags = {};
            this.flags.attachment = options.attachment;
            this.flags.charcount = options.charcount;

            this.listenTo($, 'postbox:tab:change', this.reset);
        },
        render: function () {
            this.$mirror = this.$('span.input');
            this.$textarea = this.$('.input.joms-textarea');
            if(this.$textarea.length){

            }else{
                this.$textarea = this.$('textarea.input');
            }
            this.placeholder = this.$textarea.attr('placeholder');
            if (this.flags.attachment)
                this.$attachment = $('<span class=attachment>').insertAfter(this.$mirror);

            this.reset();
        },
        set: function (value) {
            this.$textarea.val(value);
            this.flags.attachment && this.updateAttachment();
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        reset: function () {
            this.$textarea.val('');
            this.flags.attachment && this.updateAttachment();
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        value: function () { 
            var el = this.$textarea[0],
                    value = el.joms_hidden ? el.joms_hidden.val() : el.value;

            return value
                    .replace(/\t/g, '\\t')
                    .replace(/%/g, '%25');
        },
        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onFocus: function () {
            this.trigger('focus');
        },
        onKeydown: function (e) { 
            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            var value = this.value();
            if (value.length >= this.maxchar) {
                if (this.isPrintable(e)) {
                    e.preventDefault();
                    return;
                }
            }

            var that = this;
            $.defer(function () {
                that.updateInput(e);
            });
        },
        onKeydownProxy: $.debounce(function (e) {
            this.onKeydown(e);
        }, 10),
        // Keydown event not always triggered on mobile browsers, so we listen both `keydown` and `input` events.
        // http://stackoverflow.com/questions/14194247/key-event-doesnt-trigger-in-firefox-on-android-when-word-suggestion-is-on
        onInput: function () { 
            this.onKeydownProxy();
        },
        onPaste: function () {
            var that = this;
            this.onKeydownProxy(function () {
                var textarea = that.$textarea[0],
                value = (textarea.tagName == "DIV")?that.$textarea.text():textarea.value ;
                that.trigger('paste', value, 13);
            });
        },
        onBlur: function () {
            var textarea = this.$textarea[0],
            value = (textarea.tagName == "DIV")?this.$textarea.text():textarea.value ;
            this.trigger('blur', value, 13);
        },
        // ---------------------------------------------------------------------
        // Input renderer.
        // ---------------------------------------------------------------------

        updateInput: function (e) {
         
            var keyCode = e && e.keyCode || false,
                    textarea = this.$textarea[0],
                    value = (textarea.tagName == "DIV")?this.value():textarea.value,
                    isEmpty = value.replace(/^\s+|\s+$/g, '') === '';
                   
                  
            if (isEmpty)
                textarea.value = value = '';

            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            if (value.length > this.maxchar) {
                 value = value.substr(0, this.maxchar);

                if(textarea.tagName == "DIV"){
                    this.$textarea.text(value);
                }else{
                    textarea.value = value;
                }
            }

            var mirrorValue = textarea.tagName === 'DIV' ? this.$textarea.html() : this.normalize(value);
            this.$mirror.html( mirrorValue + '.');
            
            if (this.$colorInner) {
                this.setColorStatusValue(value);
                if (value != '') {
                    this.$colorPlaceholder.hide();
                } else {
                    this.$colorPlaceholder.show();
                }

                this.reactColorList();
                if (this.colorfulSelected !== false) {
                    $(this.colorfulSelected).trigger('click');
                }
               
            }

            this.$textarea.css('height', this.$mirror.height());
            this.flags.charcount && this.updateCharCounterProxy();
            this.trigger('keydown', value, keyCode);
            if (typeof e === 'function')
                e();
        },
        updateAttachment: $.noop,
        updateCharCounterProxy: $.debounce(function () {
            this.updateCharCounter();
        }, 300),
        updateCharCounter: function () {
            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            if (!this.$charcount)
                this.$charcount = this.$('.charcount');

            if (!this.maxchar || this.maxchar <= 0) {
                this.$charcount.hide();
                return;
            }
          
            let len = 0;
            if(this.$textarea[0].tagName == "DIV"){

               len =  this.$textarea.text().length  ;
            }else{
                len = this.$textarea.val().length  ;
            }
            
            this.$charcount.html(this.maxchar - len).show();
        },
        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        isPrintable: function (e) {
            if (!e)
                return false;
            if ((e.crtlKey || e.metaKey) && !e.altKey && !e.shiftKey)
                return false;

            var code = e && e.keyCode;
            var printable =
                    (code === 13) || // return key
                    (code === 32) || // spacebar key
                    (code > 47 && code < 58) || // number keys
                    (code > 64 && code < 91) || // letter keys
                    (code > 95 && code < 112) || // numpad keys
                    (code > 185 && code < 193) || // ;=,-./` (in order)
                    (code > 218 && code < 223);   // [\]' (in order)

            return printable;
        },
        normalize: function (text) {
            return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
        },
        resetTextntags: function (textarea, value) {
            try {
                if(this.$textarea.hasClass('joms-postbox-video-description') ){
                    return ;
                }
                textarea = $(textarea);
                //if(textarea.hasClass("input-status")){ debugger;
                    textarea.removeData('joms-tagging');
                    textarea.val(value).jomsTagging();
                    textarea.data('joms-tagging').initialize();
              //  }
               
            } catch (e) {
            }
        }

    });

});