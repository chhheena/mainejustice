(function ($) {
    window.initJomsTab = function ($tab, id) {
        var path = id + '-' + Joomla.getOptions('cookiePath');
        var selectors = [];

        $tab.find('li').each(function (idx, li) {
            var $li = $(li);
            var selector = $li.find('a').attr('href');
            selectors.push(selector);
        }).on('click', function (e) {
            e.preventDefault();

            var $el = $(e.currentTarget);
            if ($el.hasClass('active')) {
                return;
            };

            $tab.find('li').removeClass('active');
            $el.addClass('active');

            selectors.forEach(function (selector) {
                $(selector).removeClass('active');
            })

            var ref = $el.find('a').attr('href');
            $(ref).addClass('active');

            Cookies.set(path, $el.find('a').attr('href'));

            $(document).trigger('joms_tab_change', [id]);
        })
    }
})(jQuery)