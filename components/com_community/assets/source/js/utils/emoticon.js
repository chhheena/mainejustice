(function (root, $, factory) {

    joms.util || (joms.util = {});
    joms.util.emoticon = factory(root, $);

    define([], function () {
        return joms.util.emoticon;
    });

})(window, joms.jQuery, function (window, $) {
    function initialize() {

    }

    function showBoard(elm) {
        joms.util.emoji.showBoard(elm);

        return;
        this.$editor = {};
        
        if ($(elm).parent().parent().data('editor') != "") {
            this.$editor = $(elm).parent().parent().data('editor')
        }
        $('.joms-emoticon-js__board').remove();
        var $body = $('body'),
                $board = $('.joms-emoticon-js__board'),
                $icon = $(elm).parents('.joms-icon--emoticon'),
                offset = $(elm).offset(),
                offsetTop = 0,
                emoticons = joms.getData('joms_emo'),
                isRTL = $('html').attr('dir') === 'rtl';

        if (!$board.length) {
            html = renderBoard(emoticons);
            $body.append(html);
            $board = $('.joms-emoticon-js__board');
        }

        var spacer = isRTL ? 15 : ($board.outerWidth() - 30);
        var above = {
            display: 'block',
            top: (offset.top - $board.outerHeight()) + 'px',
            left: (offset.left - spacer) + 'px',
            position: 'absolute'
        }

        var animate_above = {
            opacity: '1',
            top: (offset.top - $board.outerHeight() - 10) + 'px'
        }

        var below = {
            display: 'block',
            top: (offset.top + 20) + 'px',
            left: (offset.left - spacer) + 'px',
            position: 'absolute'
        }

        var animate_below = {
            opacity: '1',
            top: (offset.top + 24) + 'px'
        }

        offsetTop = offset.top - $(window).scrollTop();
        var pos, ani, positionClass;

        if (offsetTop > ($board.outerHeight() + 30)) {
            pos = above;
            ani = animate_above;
            positionClass = 'joms-board--above'
        } else {
            pos = below;
            ani = animate_below;
            positionClass = 'joms-board--below';
        }

        $board.is(':hidden') && setTimeout(function () {
            $('.joms-icon--active').removeClass('joms-icon--active');
            $icon.addClass('joms-icon--active');
            $board.css(pos);
            $board.addClass(positionClass)
            setTimeout(function () {
                $board.css(ani);
            }, 100);

            $(document).one('click', function () {
                $board.css({
                    display: 'none',
                    opacity: '0'
                });

                $board.removeClass('joms-board--above joms-board--below');
            });
        }, 100)
    }

    function renderBoard(emoticons) {
        var html = '<ul class="joms-emoticon__board joms-emoticon-js__board">';
        for (var key in emoticons) {
            var emo = emoticons[key];
            html += '\
        <li>\
            <span\
                title="' + key + '"\
                onclick="joms.util.emoticon.insert(this)"\
                class="joms-emo2 joms-emo2-' + key + '"\
                data-code="' + emo[0] + '" >\
            </span>\
        </li>\
        ';
        }

        html += '</ul>';

        return html;
    }

    function insert(elm) {
        var code = $(elm).attr('data-code') ;
        this.$editor.smileyCallback(code );
    }

// Exports.
    return {
        start: initialize,
        showBoard: showBoard,
        insert: insert
    };

});
