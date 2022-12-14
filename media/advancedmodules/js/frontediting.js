/**
 * @package         Advanced Module Manager
 * @version         9.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
    $(document).ready(function() {
        // Menu items edit icons:
        setTimeout(function() {
            $('.jmoddiv[data-jmenuedittip] .nav li,.jmoddiv[data-jmenuedittip].nav li,.jmoddiv[data-jmenuedittip] .nav .nav-child li,.jmoddiv[data-jmenuedittip].nav .nav-child li').on({
                mouseenter: function() {
                    const itemids = /\bitem-(\d+)\b/.exec($(this).attr('class'));
                    if (typeof itemids[1] !== 'string') {
                        return;
                    }

                    setTimeout(function() {
                        $('a.jfedit-menu').each(function() {
                            const menuitemEditUrl = $(this).prop('href')
                                .replace(
                                    /(?:\/administrator)?\/index.php\?option=com_advancedmodules.*?edit([^\d]+).+$/,
                                    '/administrator/index.php?option=com_menus&view=item&layout=edit$1' + itemids[1]
                                );

                            $(this).prop('href', menuitemEditUrl);
                        });
                    }, 10);
                }
            });
        }, 1000);
    });
})(jQuery);
