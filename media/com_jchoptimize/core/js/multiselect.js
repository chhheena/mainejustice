/**
 * JCH Optimize - performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

var jchMultiselect = (function ($) {
    $(document).ready(function () {

        var timestamp = getTimeStamp();
        var datas = [];
        //Get all the multiple select fields and iterate through each
        $('select.jch-multiselect').each(function () {
            var el = $(this);

            datas.push({
                'id': el.attr('id'),
                'type': el.attr('data-jch_type'),
                'param': el.attr('data-jch_param'),
                'group': el.attr('data-jch_group')
            });

        });

        var xhr = $.ajax({
            dataType: 'json',
            url: jchPlatform.jch_ajax_url_multiselect + '&_=' + timestamp,
            data: {'data': datas},
            method: 'POST',
            success: function (response) {
                $.each(response.data, function (id, obj) {

                    const select = $("#" + id);

                    $.each(obj.data, function (value, option) {
                        select.append('<option value="' + value + '">' + option + '</option>');
                    });

                    select.trigger("liszt:updated");
                    select.trigger("chosen:updated");
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error returned from ajax function \'getmultiselect\'');
                console.error('textStatus: ' + textStatus);
                console.error('errorThrown: ' + errorThrown);
                console.warn('response: ' + jqXHR.responseText);
            },
            complete: function () {
                //Remove all loading images
                $('img.jch-multiselect-loading-image').each(function () {
                    $(this).remove();
                });
                //Show add item buttons
                $('button.jch-multiselect-add-button').each(function () {
                    $(this).css('display', 'inline-block');
                });
            },

        });

    });


    function addJchOption(id) {
        const input = $("#" + id + " + .chzn-container > .chzn-choices > .search-field > input, #" + id + " + .chosen-container > .chosen-choices > .search-field > input");
        let txt = input.val();

        if (txt === input.prop("defaultValue")) {
            txt = null;
        }

        if (txt === null || txt === "") {
            alert("Please input an item in the box to add to the drop-down list");
            return false;
        }

        const select = $("#" + id);

        select.append($("<option/>", {
            value: txt.replace("...", ""),
            text: txt
        }).attr("selected", "selected"));

        select.trigger("liszt:updated");
        select.trigger("chosen:updated");
    }

    return {
        addJchOption: addJchOption
    }

})(jQuery);



