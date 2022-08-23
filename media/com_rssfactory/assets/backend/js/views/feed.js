jQuery(document).ready(function ($) {
    $('#jform_protocol').change(function (event) {
        var elem = $(this);
        var value = elem.val();
        var http = $('#fieldset-http');
        var ftp = $('#fieldset-ftp');

        if ('http' == value) {
            http.show();
            ftp.hide();
        } else {
            http.hide();
            ftp.show();
        }
    }).change();

    $('#jform_ftp_test_connection').bind('getRequestData', function () {
        var elem = $(this);
        var data = [];

        data.push('ftp[host]=' + $('#jform_ftp_host').val());
        data.push('ftp[username]=' + $('#jform_ftp_username').val());
        data.push('ftp[password]=' + $('#jform_ftp_password').val());
        data.push('ftp[path]=' + $('#jform_ftp_path').val());

        elem.attr('data-request', data.join('&'));
    });

    $('#jform_refresh_icon').bind('getRequestData', function () {
        var elem = $(this);
        var data = [];

        data.push('data[url]=' + encodeURIComponent($('#jform_url').val()));
        data.push('data[id]=' + encodeURIComponent($('#jform_id').val()));

        elem.attr('data-request', data.join('&'));
    });

    // Preview button.
    $('.preview-rules').click(function (event) {
        var form = $('#adminForm');
        form.find('input[name="task"]').val('feed.preview');

        var story = $('input[name="jform[i2c_rules_preview_story]"]:checked', '#adminForm').val();

        if ('undefined' == typeof story) {
            return false;
        }

        var url = 'index.php?option=com_rssfactory&format=raw';

        var bsVersion = 'undefined' === typeof $.fn.tooltip.Constructor.VERSION ? 2 : 4;

        $.post(url, form.serialize(), function (response) {
            $('#collapseModal' + bsVersion).find('.modal-body').html(response);
        });

        $('#collapseModal' + bsVersion).find('.modal-body').html('Loading...').end().modal();

        $('body').on('hidden', '.modal', function () {
            $(this).removeData('modal');
        });
    });

    // Add rule button.
    $('.button-add-rule').click(function (event) {
        event.preventDefault();

        var templates = $('.rules-templates:first');
        var rule = $('#rules').val();
        var template = templates.attr('data-template-' + rule);
        var rules = $('.rules');
        var last = parseInt(templates.attr('data-last')) + 1;

        rules.append(template.replace(/__i__/g, last));
        var fieldset = rules.find('fieldset.fieldset-rule:last');

        fieldset.find('select').chosen({disable_search_threshold: 10, allow_single_deselect: true}).end();

        // Turn radios into btn-group
        fieldset.find('.radio.btn-group label').addClass('btn');

        fieldset.find(".btn-group label:not(.active)").click(function () {
            var label = $(this);
            var input = $('#' + label.attr('for'));

            if (!input.prop('checked')) {
                label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
                if (input.val() == '') {
                    label.addClass('active btn-primary');
                } else if (input.val() == 0) {
                    label.addClass('active btn-danger');
                } else {
                    label.addClass('active btn-success');
                }
                input.prop('checked', true);
            }
        });
        fieldset.find(".btn-group input[checked=checked]").each(function () {
            if ($(this).val() == '') {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
            } else if ($(this).val() == 0) {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
            } else {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
            }
        });

        $$('.hasTip').each(function (el) {
            var title = el.get('title');
            if (title) {
                var parts = title.split('::', 2);
                el.store('tip:title', parts[0]);
                el.store('tip:text', parts[1]);
            }
        });
        var JTooltips = new Tips($$('.hasTip'), {maxTitleChars: 50, fixed: false});

        templates.attr('data-last', last);
    });

    // Remove rule button.
    $(document).on('click', '.button-remove-rule', function (event) {
        event.preventDefault();

        $(this).parents('fieldset:first').remove();
    });

    // Sortable rules.
    $('.rules').sortable({
        axis: 'y',
        handle: '.icon-move',
        stop: function (event, ui) {
            ui.item.parent().find('input[name$="[order]"]').each(function (index, element) {
                $(this).val(index + 1);
            });
        }
    });

    // Toggle rule.
    $(document).on('click', '.btn-toggle-rule', function (event) {
        event.preventDefault();

        var elem = $(this);
        var params = elem.parents('fieldset:first').find('div.params');

        elem.find('i:first').toggleClass('icon-arrow-up icon-arrow-down');
        params.toggle();

        elem.parents('fieldset:first').find('input[name$="[collapse]"]').val(params.is(':visible') ? 0 : 1);
    });
});
