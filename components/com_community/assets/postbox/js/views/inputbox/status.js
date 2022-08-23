define('views/inputbox/status', [
    'sandbox',
    'views/inputbox/base',
    'utils/constants',
    'utils/language'
],
// definition
// ----------
function ($, InputboxView, constants, language) {

    return InputboxView.extend({
        template: _.template('\
    <div class="joms-postbox__status-composer" style="position:relative">\
        <div class="joms-postbox-input joms-inputbox" style="<%= enablebackground ? \'padding-bottom: 60px\' : \'\' %>">\
            <div class=inputbox>\
                <div style="position:relative">\
                    <div style="position:relative"><span class=input></span></div>\
                    <div class=joms-textarea__wrapper>\
                        <textarea class="input input-status joms-textarea" placeholder="<%= placeholder %>" style="min-height:1.4em"></textarea>\
                    </div>\
                </div>\
            </div>\
        </div>\
        <div class="joms-icon joms-icon--emoticon" style="top:20px; right: 5px;">\
            <div style="position:relative"><svg viewBox="0 0 16 16" onclick="joms.util.emoticon.showBoard(this);">\
                <use xlink:href="<%= window.joms_current_url %>#joms-icon-smiley"></use>\
                </svg></div>\
        </div>\
        <div class="charcount joms-postbox-charcount"></div>\
        <% if(enablebackground) { %>\
        <div class="colorful-status__container" style="display: none;">\
        <div class="charcount joms-postbox-charcount"></div>\
        <div class="joms-icon joms-icon--emoticon" style="top:20px; right: 5px;">\
            <div style="position:relative;z-index:99999"><svg viewBox="0 0 16 16" onclick="joms.util.emoticon.showBoard(this);">\
                <use xlink:href="<%= window.joms_current_url %>#joms-icon-smiley"></use>\
                </svg></div>\
        </div>\
            <div class="colorful-status__placeholder" unselectable="on"><%= placeholder %></div>\
            <div class="colorful-status__inner" contenteditable="true"><div><br></div></div>\
        </div>\
        <div class="colorful-status__color">\
            <span class="joms-direction joms-left"><i class="fa fa-chevron-left" aria-hidden="true"></i></span>\
            <ul class="colorful-status__color-list"></ul>\
            <span class="joms-direction joms-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>\
        </div>\
        <% } %>\
    </div>\
'),
        getTemplate: function () {
            var hint = language.get('status.status_hint') || '',
                    enablebackground = constants.get('conf').enablebackground,
                    html = this.template({placeholder: hint, enablebackground: enablebackground});
            return $(html);
        },
        events: function () {
            return _.extend({}, InputboxView.prototype.events, {
                'keydown .colorful-status__inner': 'onColorStatusKeydown',
                'keyup .colorful-status__inner': 'onColorStatusKeyup',
                'input .colorful-status__inner div': 'onColorStatusInput',
                'input .colorful-status__inner': 'onColorStatusInput',
                'paste .colorful-status__inner': 'onColorStatusPaste',
                'focus .colorful-status__inner': 'onColorStatusFocus',
                'click .colorful-status__inner div': 'onColorContentClick',
                'click .colorful-status__inner': 'onColorContentClick',
                'click .colorful-status__color-selector': 'onColorSelect',
                'click .joms-left': 'colorSlideToLeft',
                'click .joms-right': 'colorSlideToRight',
                'click .colorful-status__container': 'focusColorContent'
            });
        },
        smileyCallback: function (smiley_code) {
            
            var $input = this.$inputbox.find("textarea"),
                    $hiddenInput = this.$inputbox.find('input.joms-textarea__hidden'),
                    code = smiley_code;
            var start = this.getSmileyInsertPosition();
            
            value = $input.val().slice(0, start) + code + $input.val().slice(start);
            if(value.length > this.maxchar){
                return;
            }
            $hiddenInput.val(value);
            $input.val(value);
            if (this.colorful == false) {
                $input.prop("selectionStart", start + code.length);
                $input.prop("selectionEnd", start + code.length);
                $input.focus();
                $input.trigger('keydown');
            } else if (typeof this.$colorContainer != "undefined") {
                this.setColorStatusValue(value);

                var pos = start + code.length;
                this.$colorInner.attr('cachedPostion', pos);
                this.cachedPostion = pos;
                var node_pos = this.getPos(pos);
                this.setCaretPostion(node_pos.node, node_pos.position);
            }

            this.flags.charcount && this.updateCharCounterProxy();
            this.trigger('keydown', value);




        },
        getSmileyInsertPosition: function () {
         
            var $input = this.$inputbox.find("textarea"),
                    start = $input.prop("selectionStart");
            if (this.colorful == false) {
                return start;
            } else {
                return this.cachedPostion;
            }

        },
        getCaretPosition: function () {
            var element = this.$colorInner[0];
            var caretOffset = 0;
            var doc = element.ownerDocument || element.document;
            var win = doc.defaultView || doc.parentWindow;
            var sel;
            if (typeof win.getSelection != "undefined") {
                sel = win.getSelection();
                if (sel.rangeCount > 0) {
                    var range = win.getSelection().getRangeAt(0);
                    var preCaretRange = range.cloneRange();
                    preCaretRange.selectNodeContents(element);
                    preCaretRange.setEnd(range.endContainer, range.endOffset);
                    caretOffset = preCaretRange.toString().length;
                }
            } else if ((sel = doc.selection) && sel.type != "Control") {
                var textRange = sel.createRange();
                var preCaretTextRange = doc.body.createTextRange();
                preCaretTextRange.moveToElementText(element);
                preCaretTextRange.setEndPoint("EndToEnd", textRange);
                caretOffset = preCaretTextRange.text.length;
            }


            var index_of_current_focus_element = this.$colorInner.children().index($(sel.focusNode).parent());
            if(index_of_current_focus_element == -1){
                var index_of_current_focus_element = this.$colorInner.children().index($(sel.focusNode));
            }
            
            caretOffset += index_of_current_focus_element;
            if (caretOffset < 0) {
                caretOffset = 0;
            }
            
            return caretOffset;
        },
        setColorStatusValue: function (value) {

            var parts = value.split(/\r?\n/);
            var html = "";
            $.each(parts, function (val, key) {
                if (val != "") {
                    html += '<div>' + val + '</div>';
                } else {
                    html += '<div><br></div>';
                }

            });
            if (html == "") { 
                html = "<div><br></div>";
                this.$colorPlaceholder.show();
            } else {
                this.$colorPlaceholder.hide();
            }

            this.$colorInner.html(html);
        },
        initialize: function (config) {
            var hash, item, id, i;
            InputboxView.prototype.initialize.apply(this, arguments);
            this.moods = constants.get('moods');
            hash = {};
            if (this.moods && this.moods.length) {
                for (i = 0; i < this.moods.length; i++) {
                    id = this.moods[i].id;
                    item = [id, this.moods[i].description];
                    if (this.moods[i].custom) {
                        item[2] = this.moods[i].image;
                    }
                    hash[ id ] = item;
                }
            }
            this.moods = hash;
            this.colorful = false;
            this.blankDiv = '<div><br></div>';
            this.isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1;
            this.newText = '';
            this.oldOffset = 0;
            this.newOffset = 0;
            // define status tab
            this.status = config.status;
            this.status_text = {hidden: '', textarea: '', div: '',
                set: function (value) {

                }
            };
        },
        render: function () {
            var div = this.getTemplate();
            this.$el.replaceWith(div);
            this.setElement(div),
                    conf = constants.get('conf');
            InputboxView.prototype.render.apply(this, arguments);
            this.$el.find(".joms-icon--emoticon").data('editor', this);
            this.$inputbox = this.$el.find('.joms-postbox-input');
            if (this.status && conf.enablebackground) {
                this.$cachedElement = null;
                this.cachedPostion = 0;
                this.$colorContainer = this.$el.find('.colorful-status__container');
                this.$colorInner = this.$colorContainer.find('.colorful-status__inner');
                this.$colorPlaceholder = this.$colorContainer.find('.colorful-status__placeholder');
                this.$color = this.$el.find('.colorful-status__color');
                this.$colorList = this.$color.find('.colorful-status__color-list');
                this.$colorContainer.find(".joms-icon--emoticon").data('editor', this);
                this.$colorList.html(this.renderColorList());
                if ($.mobile) {
                    this.$colorList.css('overflow-x', 'auto')
                }

                var self = this;
                setTimeout(function () {
                    self.initColorSlide();
                }, 100)

                $(window).on('resize', function () {
                    self.initColorSlide()
                });
                if (joms.ie) {
                    setTimeout(function () {
                        self.onInputPolyfill();
                    }, 300)
                }
            }
        },
        renderColorList: function () {
            var joms_bg = constants.get('backgrounds');
            var list = joms_bg.map(function (item) {
                return '<li class="colorful-status__color-selector"\
                    data-bgid="' + item.id + '"\
                    data-text-color="#' + item.textcolor + '"\
                    data-placeholder-color="#' + item.placeholdercolor + '"\
                    data-image="' + item.image + '"\
                    style="background-image: url(\'' + item.thumbnail + '\')">\
                    <img style="display:none" src="' + item.image + '" />\
                </li>';
            })

            list.unshift('<li class="colorful-status__color-selector active"\ style="background-image: url(\'' + joms.ASSETS_URL + 'photos/none.png\')"></li>');
            return list.join('');
        },
        set: function (value) {
            this.resetTextntags(this.$textarea, value);
            this.flags.attachment && this.updateAttachment(false, false);
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        reset: function () {
            this.colorfulSelected = false;
            this.colorful = false;
            this.$inputbox && this.$inputbox.show();
            this.resetColorfulStatus();
            this.$cachedElement = null;
            this.cachedPostion = 0;
            this.$colorList && this.$colorList.html(this.renderColorList());
            this.resetTextntags(this.$textarea, '');
            this.flags.attachment && this.updateAttachment(false, false);
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        resetColorfulStatus: function () {
            this.$colorContainer && this.$colorContainer.hide(); 
            
            this.$colorInner && this.$colorInner.html(this.blankDiv);
            this.$colorPlaceholder && this.$colorPlaceholder.show();
        },
        reactColorList: function () {
            var numChild = this.$colorInner.children().length;

            if (numChild > 4 || (this.$textarea[0].value.length) >= 150) {
                this.$color.hide();
               // this.$colorList.hide();
            }
            else {
                  this.$color.show();
               // this.$colorList.show();
            }
        },
        value: function () {
            var value = '';
            if (this.colorful) {
                this.$colorInner.children().each(function (idx, item) {
                    var text = $(item).text();
                    if (value) {
                        value += '\n' + text;
                    } else {
                        value = text
                    }
                });
                if(this.$colorInner.children().length == 0){
                    return this.$colorInner.html();
                }
                
                return value;
            } else {
                var el = this.$textarea[0];
                value = el.joms_hidden ? el.joms_hidden.val() : el.value;
                return value
                        .replace(/\t/g, '\\t')
                        .replace(/%/g, '%25');
            }
        },
        updateInput: function () {
            InputboxView.prototype.updateInput.apply(this, arguments);
        },
        updateAttachment: function (mood, location) {
            var attachment = [];
            this.mood = mood || mood === false ? mood : this.mood;
            this.location = location || location === false ? location : this.location;
            if (this.location && this.location.name) {
                attachment.push('<b>' + language.get('at') + ' ' + this.location.name + '</b>');
            }

            if (this.mood && this.moods[this.mood]) {
                if (typeof this.moods[this.mood][2] !== 'undefined') {
                    attachment.push(
                            '<img class="joms-emoticon" src="' + this.moods[this.mood][2] + '"> ' +
                            '<b>' + this.moods[this.mood][1] + '</b>'
                            );
                } else {
                    attachment.push(
                            '<i class="joms-emoticon joms-emo-' + this.mood + '"></i> ' +
                            '<b>' + this.moods[this.mood][1] + '</b>'
                            );
                }
            }

            if (!attachment.length) {
                this.$attachment.html('');
                this.$textarea.attr('placeholder', this.placeholder);
                return;
            }

            this.$attachment.html(' &nbsp;&mdash; ' + attachment.join(' ' + language.get('and') + ' ') + '.');
            this.$textarea.removeAttr('placeholder');
        },
        // colorful status event

        onInputPolyfill: function () {
            var self = this,
                    element = self.$colorInner[0],
                    observer = new MutationObserver(function (mutations) {
                        mutations.forEach(function (mutation) {
                            var content = self.$colorInner.text(),
                                    numChild = self.$colorInner.children().length;
                            if (content || numChild > 1) {
                                self.$colorPlaceholder.hide();
                            } else {
                                self.$colorPlaceholder.show();
                            }
                        })
                    }
                    );
            observer.observe(element, {
                childList: true,
                characterData: true,
                subtree: true,
            });
        },
        focusColorContent: function (e) {
            var $srcElement = $(e.srcElement), $elm;
            if ($srcElement.hasClass('colorful-status__container')) {
                e.preventDefault();
                $elm = this.$cachedElement ? this.$cachedElement[0] : this.$colorInner[0];
                var node_pos = this.getPos(this.cachedPostion);
                this.setCaretPostion(node_pos.node, node_pos.position);
                // this.setCaretPostion(this.$colorInner[0], this.cachedPostion);
            }
        },
        initColorSlide: function () {
            var width = this.$colorList.width(),
                    sWidth = this.$colorList[0].scrollWidth;
            if (width < sWidth) {
                this.$color.find('.joms-direction').show();
            } else {
                this.$color.find('.joms-direction').hide();
            }
        },
        colorSlideToLeft: function () {
            var left = this.$colorList.scrollLeft(),
                    spacer = $.mobile ? 100 : 200;
            this.$colorList.stop().animate({
                scrollLeft: left - spacer
            })
        },
        colorSlideToRight: function () {
            var left = this.$colorList.scrollLeft(),
                    spacer = $.mobile ? 100 : 200;
            this.$colorList.stop().animate({
                scrollLeft: left + spacer
            })
        },
        isComposing: function (key) {
            return key === 32
                    || (key > 47 && key < 91)
                    || (key > 95 && key < 112)
                    || (key > 183 && key < 224);
        },
        setCaretPostion: function (el, position) {
            if(this.$textarea[0].value.length == 0){
                this.$colorInner[0].childNodes[0].innerHTML = '\u00a0';

                el = this.$colorInner[0].childNodes[0];
            }
           
            var range = document.createRange(),
                    sel = window.getSelection();

            range.setStart(el, position || 0);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            //el.focus();
            this.$colorInner[0].focus();

          
        },
        cacheCaret: function () {
            
            var sel = window.getSelection();
            this.$cachedElement = $(sel.focusNode).parent();
            this.cachedPostion = this.getCaretPosition();
            this.$cachedElement.attr('cachedPostion', this.cachedPostion);
            this.$colorInner.attr('cachedPostion', this.cachedPostion);
        },
        onColorContentClick: function () {
            this.cacheCaret();
        },
        onColorStatusKeydown: function (e) {

            var BACKSPACE = 8,
                    ENTER = 13,
                    DELETE = 46,
                    LIMIT = 149,
                    remove = [BACKSPACE, DELETE].indexOf(e.keyCode) > -1,
                    enter = e.keyCode === ENTER,
                    selection = window.getSelection();
            this.oldOffset = selection.focusOffset;
            if (!this.isAndroid && remove) {
                var html = this.$colorInner.html().trim();
                if (html === this.blankDiv) {
                    e.preventDefault();
                } else if (!html) {
                    e.preventDefault(); 
                    this.$colorInner.html(this.blankDiv);
                    this.setCaretPostion(this.$colorInner[0]);
                }
                return;
            }
            var numChild = this.$colorInner.children().length;
            var content = this.$colorInner.text(),
                    contentLength = content.length;
                    
            if (enter) {
                var numChild = this.$colorInner.children().length;
                if ((numChild + contentLength) > LIMIT || numChild > 3) {
                    // e.preventDefault();
                    this.colorful = false;
                    this.$inputbox.show();
                    this.$colorContainer.hide();
                    this.$textarea.css('height', this.$mirror.height());
                    this.$textarea[0].focus();
                    this.$textarea[0].selectionStart = this.cachedPostion;
                    this.$textarea[0].selectionEnd = this.cachedPostion;
                    this.reactColorList();
                    return;
                }
                this.cacheCaret();
               
                var $focusNode = this.$(selection.focusNode),
                        text = $focusNode.text();
                if (!text) {
                    e.preventDefault();
                    return;
                }
            }
            this.cacheCaret();

            if ((numChild + contentLength) >= LIMIT) {
                // e.preventDefault();
                this.colorful = false;
                this.$inputbox.show();
                this.$colorContainer.hide();
                this.$textarea.css('height', this.$mirror.height());
                this.$textarea[0].focus();
                this.$textarea[0].selectionStart = this.cachedPostion;
                this.$textarea[0].selectionEnd = this.cachedPostion;
                this.reactColorList();
                return;
            }

            if (!this.isAndroid && this.isComposing(e.keyCode) && !e.ctrlKey && (numChild + contentLength) > LIMIT) {
                e.preventDefault();
            }
        },
        onColorStatusKeyup: function (e) {
            
            var BACKSPACE = 8,
                    ENTER = 13,
                    DELETE = 46,
                    LIMIT = 149,
                    remove = ([BACKSPACE, DELETE].indexOf(e.keyCode) > -1),
                    text = this.$colorInner.text().trim();
            this.trigger('keydown', text ? '1' : '', e.keyCode);
            
            if (remove || this.isAndroid) { 
                var html = this.$colorInner.html().trim();
                if (html === '<br>' || !html) {
                    e.preventDefault(); 
                    this.$colorInner.html(this.blankDiv);
                    this.setCaretPostion(this.$colorInner[0]);
                }
            }

            this.cacheCaret();
            if(this.$colorInner.children().length == 0){
                var temp = this.$colorInner.html();
                var pos = temp.length ;
                if(temp.trim() == ""){ temp = "<br>" }
                this.$colorInner.html('<div>'+temp+'</div>');
                 var node_pos = this.getPos(pos);
                this.setCaretPostion(node_pos.node, node_pos.position);
            }
        },
        limitChar:function(){

            var value = this.value();
            if(value.length > this.maxchar){
                value = value.trim().substr(0, this.maxchar);
                this.setColorStatusValue(value);
                var node_pos = this.getPos((this.maxchar));
                this.setCaretPostion(node_pos.node, node_pos.position);
               
            }

        },
        onColorStatusInput: function (e) {
            
            if (this.isAndroid) {
                var content = this.$colorInner.text(),
                        numChild = this.$colorInner.children().length,
                        selection = window.getSelection(),
                        LIMIT = 149;
                this.newOffset = selection.focusOffset;
                this.newText = content;
                 var numChild = this.$colorInner.children().length;
                if (this.newText.length > LIMIT) {
                   // var range = document.createRange();
                  //  range.setStart(selection.focusNode, this.oldOffset);
                  //  range.setEnd(selection.focusNode, this.newOffset);
                  //  range.deleteContents();
                }
            }

            var value =  this.value();
            var numChild = this.$colorInner.children().length;
            this.limitChar();
            this.setTextareaValue(value);
           
            if (value != "") {
                this.$colorPlaceholder.hide();
            } else {
                this.$colorPlaceholder.show();
            }
        },
        setTextareaValue: function (content) {  
            var value = content.substr(0, this.maxchar);
            this.resetTextntags(this.$textarea, value);
            this.flags.attachment && this.updateAttachment(false, false);
            this.flags.charcount && this.updateCharCounterProxy();
            this.$mirror.html(this.normalize(value) + '.');
        },
        onColorStatusPaste: function (e) {
            //e.preventDefault();
        },
        onColorSelect: function (e) {

            var $el = this.$(e.currentTarget);
            var textColor = $el.attr('data-text-color');
            var placeholder = $el.attr('data-placeholder-color');
            var image = $el.attr('data-image');
            var bgid = $el.attr('data-bgid');

            this.colorfulSelected = $el;

            var numChild = this.$colorInner.children().length;
            if (numChild > 4 || (this.$textarea[0].value.length) >= 150) {
                e.preventDefault();
                this.colorful = false;
                this.$inputbox.show();
                this.$colorContainer.hide();


                this.$textarea.css('height', this.$mirror.height());

                this.$textarea[0].focus();
                this.reactColorList();
                return;
            }else{
                 this.reactColorList();
            }
            if (!$el.hasClass('active')) {
                this.$('.colorful-status__color-selector').removeClass('active');
                $el.addClass('active');
            }
            if (this.colorful) {


                var pos = this.cachedPostion;
                
            } else {

                var pos = this.$textarea[0].selectionStart;

            }

            if (bgid) {
                this.colorful = true;
                this.bgid = bgid;
                this.$inputbox.hide();
                this.$colorContainer.show();
                this.$colorContainer.css('background-image', 'url(\'' + image + '\')');
                textColor && this.$colorInner.css('color', textColor);
                placeholder && this.$colorPlaceholder.css('color', placeholder);

            } else {
                this.colorfulSelected = false;
                this.colorful = false;
                this.$inputbox.show();
                this.$colorContainer.hide();
                this.$textarea.css('height', this.$mirror.height());
                this.$textarea[0].focus();
                this.$textarea[0].selectionStart = this.cachedPostion;
                this.$textarea[0].selectionEnd = this.cachedPostion;

            }

            if (this.$colorInner.children().length && this.colorful) {
                var $colorInner = this.$cachedElement ? this.$cachedElement[0] : this.$colorInner.children()[0];



                this.$colorInner.attr('cachedPostion', pos);
                this.cachedPostion = pos;
                var node_pos = this.getPos(pos);
                this.setCaretPostion(node_pos.node, node_pos.position);
                // this.setCaretPostion(this.$colorInner[0], this.cachedPostion);
            }
            this.reactColorList();
            this.trigger('change:type', bgid);

        },
        getPos: function (pos) {

            var elem = jQuery(this.$colorInner[0]);
            var child = null;
            var current_pos = 0;
            var offset = 0;
            jQuery.each(elem.children(), function (k, v) {
                current_pos += jQuery(v).text().length
                if (current_pos >= pos) {

                    child = v;
                    offset = jQuery(v).text().length - (current_pos - pos);
                    if (current_pos == pos) {
                        //offset--;
                    }

                    return false;
                } else {

                }
                current_pos++;

            });

            return {node: child.childNodes[0], position: offset}
        },
        onColorStatusFocus: function () {
            this.trigger('focus');
        }
    });
});
