(function (root, $, factory) {

    joms.popup || (joms.popup = {});
    joms.popup.chat || (joms.popup.chat = {});
    joms.popup.chat.addRecipient = factory(root, $);

    define(['utils/popup'], function () {
        return joms.popup.chat.addRecipient;
    });

})(window, joms.jQuery, function (window, $) {

    var popup, elem, tabAll, tabSelected, btnSelect, btnLoad, id, keyword, start, limit, xhr, timeout;

    function render(_popup, _id) {
        if (elem)
            elem.off();
        popup = _popup;
        id = _id;

        limit = 200;

        joms.ajax({
            func: 'system,ajaxShowFriendsForm',
            data: [null, '', '', 1, 1],
            callback: function (json) {
                popup.items[0] = {
                    type: 'inline',
                    src: buildHtml(json)
                };

                popup.updateItemHTML();

                // Override limit supplied in json.
                if (json.limit) {
                    limit = +json.limit;
                }

                elem = popup.contentContainer;
                tabAll = elem.find('.joms-tab__content').eq(0);
                tabSelected = elem.find('.joms-tab__content').eq(1);
                btnSelect = elem.find('[data-btn-select]');
                btnLoad = elem.find('[data-btn-load]');

                elem.on('input', '[data-search]', function(e) { search(e, _id)});
                elem.on('click', '.joms-tab__bar a', changeTab);
                elem.on('click', '[data-btn-select]', selectAll);
                elem.on('click', '[data-btn-load]', function(e) { load(e, _id)});
                elem.on('click', '[data-btn-save]', select);
                elem.on('click', 'input[type=checkbox]', toggle);

                getFriendList('', _id);
            }
        });
    }

    function search(e, _id) {
        var elem = $(e.currentTarget);
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            getFriendList(elem.val(), _id);
        }, 300);
    }

    function select() {
        var added_friends = {};
        tabSelected.find('.joms-js--friend').each(function () {
            var item = $(this),
                chk = item.find(':checkbox'),
                id = chk.val(),
                friend = {
                    id : +id,
                    name: item.find('.joms-avatar--comment a img').attr('alt'),
                    avatar: item.find('.joms-avatar--comment a img').attr('src')
                };
            added_friends[id] = friend;    
        });
        joms_observer.do_action('chat_add_people', added_friends);
        // Close popup.
        elem.off();
        popup.close();
    }

    function changeTab(e) {
        var $el = $(e.target),
            selected = $el.attr('href') === '#joms-popup-tab-selected',
            lang = window.joms_lang[ selected ? 'COM_COMMUNITY_UNSELECT_ALL' : 'COM_COMMUNITY_SELECT_ALL' ];

        btnSelect.html(lang);
    }

    function selectAll() {
        var ct = $('.joms-tab__content:visible'),
            clone;

        // Remove selected.
        if (ct.attr('id') === 'joms-popup-tab-selected') {
            ct.find('.joms-js--friend').remove();
            elem.find('input[type=checkbox]').each(function () {
                this.checked = false;
            });
            return;
        }

        // Add selected.
        clone = ct.find('.joms-js--friend').clone();
        clone.find('input[type=checkbox]').add(ct.find('input[type=checkbox]')).prop('checked', 'checked');
        ct = elem.find('#joms-popup-tab-selected');
        ct.html(clone);
    }

    function load(e, _id) {
        getFriendList('', _id);
    }

    function toggle(e) {
        var checkbox = $(e.target),
            ct = checkbox.closest('.joms-tab__content'),
            id, clone;

        // Remove selected.
        if (ct.attr('id') === 'joms-popup-tab-selected') {
            id = checkbox[0].value;
            checkbox.closest('.joms-js--friend').remove();
            elem.find('.joms-js--friend-' + id + ' input[type=checkbox]')[0].checked = false;
            return;
        }

        // Remove selected.
        if (!checkbox[0].checked) {
            id = checkbox[0].value;
            elem.find('#joms-popup-tab-selected .joms-js--friend-' + id).remove();
            return;
        }

        // Add selected.
        ct = elem.find('#joms-popup-tab-selected');
        clone = checkbox.closest('.joms-js--friend').clone();
        checkbox = clone.find('input[type=checkbox]');
        checkbox[0].checked = true;
        ct.append(clone);
    }

    function getFriendList(_keyword, _id) {
        var isReset = typeof _keyword === 'string';
        var users = _id && typeof _id === 'string' ? _id.split(',') : [];

        if (isReset) {
            tabAll.empty();
            start = 0;
            keyword = _keyword;
        } else {
            start += limit;
        }

        xhr && xhr.abort();
        xhr = joms.ajax({
            func: 'system,ajaxLoadFriendsList',
            data: [keyword, 'friends,inviteUsers', id, start, limit],
            callback: function (json) {
                var html;

                if (json.html) {
                    html = $($.trim(json.html));
                    html.each(function () {
                        var elm = $(this),
                            checkbox = elm.find(':checkbox'),
                            value = checkbox.val();

                        if (tabSelected.find(':checkbox[value=' + value + ']').length) {
                            checkbox[0].checked = true;
                        }


                    });

                    tabAll.append(html);

                    _.each(users, function(id) {
                        var added = tabAll.find('.joms-js--friend-' + id);
                        if (added.length) {
                            added.find('.joms-stream__time label').text(joms_lang.COM_COMMUNITY_APPS_LIST_ADDED);
                        }
                    });
                }

                if (!isReset) {
                    tabAll[0].scrollTop = tabAll[0].scrollHeight;
                }

                // Toggle load more.
                if (json.loadMore) {
                    btnSelect.css({width: '49%', marginRight: '2%'});
                    btnLoad.css({width: '49%'}).html(window.joms_lang.COM_COMMUNITY_INVITE_LOAD_MORE + ' (' + json.moreCount + ')').show();
                } else {
                    btnLoad.hide();
                    btnSelect.css({width: '100%', marginRight: '0'});
                }
            }
        });
    }

    function buildHtml(json) {
        return [
            '<div class="joms-popup joms-popup--whiteblock">',
            '<div class="joms-popup__title"><button class="mfp-close" type="button" title="',window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON_TITLE,'">×</button>', json.title, '</div>',
            '<div data-ui-object="popup-step-1"', (json.error ? ' class="joms-popup__hide"' : ''), '>',
            '<div class="joms-popup__content">', json.html, '</div>',
            '<div class="joms-popup__action">',
            '<button class="joms-button--primary" data-btn-save="1">', json.btnSelect, '</button>',
            '</div>',
            '</div>',
            '<div data-ui-object="popup-step-2"', (json.error ? '' : ' class="joms-popup__hide"'), '>',
            '<div class="joms-popup__content joms-popup__content--single" data-ui-object="popup-message">', (json.error || ''), '</div>',
            '</div>',
            '</div>'
        ].join('');
    }

// Exports.
    return function (id) {
        joms.util.popup.prepare(function (mfp) {
            render(mfp, id);
        });
    };

});
