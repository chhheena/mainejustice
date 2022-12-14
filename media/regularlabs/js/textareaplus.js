/**
 * @package         Regular Labs Library
 * @version         22.8.11825
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

"use strict";

(function($) {
    $(document).ready(function() {
        $('.rl_resize_textarea').click(function() {
            var $el    = $(this);
            var $field = $(`#${$el.attr('data-id')}`);

            if ($el.hasClass('rl_minimize')) {
                $el.removeClass('rl_minimize').addClass('rl_maximize');
                $field.css({'height': $el.attr('data-min')});
                return;
            }

            $el.removeClass('rl_maximize').addClass('rl_minimize');
            $field.css({'height': $el.attr('data-max')});
        });
    });
})(jQuery);
