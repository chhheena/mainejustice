/**
 * sh404SEF importer plugin
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2017
 * @package     sh404SEF Importer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.0.1.47
 * @date        2017-06-21
 */

/*! Copyright Weeblr llc 2017 - Licence: http://www.gnu.org/copyleft/gpl.html GNU/GPL */

;
(function (_app, window, document, $) {
    "use strict";

    /**
     * Implementation
     */
    var state = {};


    function buttonStartClick() {
        startProcessing($(this));
    }

    function buttonCancelClick() {
        var scope = $(this).data('scope');
        state[scope].msgArea.removeClass('wb-success wb-error').addClass('wb-warning').html('Cancelling import process.');
        cancelProcessing(scope);
    }

    function startProcessing($element) {
        var scope = $element.data('scope');

        // if already clicked, do nothing
        if (!scope || state[scope]) {
            return;
        }

        // hide settings
        updateDisplay(scope, true);

        // store that we got started, and disable button
        $element.attr('disabled', 'disabled');
        state[scope] = {
            status: "running",  // running | cancelled
            startButton: $element,
            cancelButton: $('.wb-js-button-cancel-' + scope),
            msgArea: $('.wb-js-message-area-' + scope),
            request: null,
            id: null
        };

        // reset msg area
        state[scope].msgArea.removeClass('wb-error wb-warning').addClass('wb-success').html('Starting to import data, please wait...');

        // hide this button, show cancel
        $element.hide();
        state[scope].cancelButton.show();

        process(scope);
    }

    /**
     * Process a request from
     * @param string scope
     */
    function process(scope) {

        if (!state[scope]) {
            console.error('Import error: unknown scope');
            endProcessing(scope);
            return;
        }
        var url = _app.baseUrl + '/index.php?option=com_ajax&group=sh404sefcore&plugin=importer&format=json&scope=' + scope;
        if (!state[scope].id) {
            // new job
            url += '&next_step=start_import';
        }
        else {
            // continue or cancel job
            url += '&next_step=continue_import&id=' + state[scope].id;
        }

        // collect current settings values, and create the POST request data
        var formData = $('#style-form').serialize();

        // perform the POST ajax request
        state[scope].request = $.post(
            {
                url: url,
                data: formData
            }
        ).done(function (res) {
            reqSuccess(scope, res);
        })
            .fail(
                function (error) {
                    reqError(scope, error);
                }
            );
    }

    function reqSuccess(scope, res) {
        if (!state[scope]) {
            var msg = 'Not expecting this response, import may have been cancelled.';
            console.error(msg);
            endProcessing(scope);
            return;
        }

        state[scope].request = null;

        if (!res || !res.success) {
            var msg = res && res.message ? res.message : 'Error communicating with server, aborting.';
            console.error(msg);
            displayError(scope, msg);
            endProcessing(scope);
            return;
        }

        // returned data is in res.data
        dispatch(scope, res);
    }

    function reqError(scope, error) {
        console.log('Ajax request failure');

        // display error
        console.error(error.statusText);

        // try to start a cancel request?
        state[scope].request = null;

        // delete the current scope state record
        displayError(scope, 'Error communicating with server: ' + error.statusText);
        endProcessing(scope);
    }

    /**
     * Dispatch an ajax request response
     * @param res
     */
    function dispatch(scope, res) {
        switch (res.data[0].next_step) {
            case 'continue':
                state[scope].status = 'running';
                state[scope].id = state[scope].id ? state[scope].id : res.data[0].id;
                if (res.data[0].data_type) {
                    var dataType = res.data[0].data_type;
                    state[scope].msgArea.removeClass('wb-error wb-warning').addClass('wb-success').html('Import of ' + res.data[0].data_type + ' in progress: ' + res.data[0].done[dataType] + ' / ' + res.data[0].processed[dataType] + ' / ' + res.data[0].total[dataType]);
                }
                process(scope);
                break;
            case 'cancelled':
                break;
            case 'completed':
                var msg = '';
                $.each(res.data[0].done, function (name, value) {
                    msg += msg ? ' - ' : '';
                    msg += name + ': ' + value + ' / ' + res.data[0].processed[name];
                });
                msg = '<p>Completed (imported/processed): ' + msg + '</p>';
                msg += '<p>Site is offline, and sh404SEF has been disabled. Please verify imported data, enable back sh404SEF and set back site online when ready.</p>';
                state[scope].msgArea.removeClass('wb-error wb-warning').addClass('wb-success').html(msg);
                endProcessing(scope);
                break;
            default:
                break;
        }
    }

    function displayError(scope, error) {
        console.log('Error message: ' + error);
        state[scope].msgArea.removeClass('wb-success wb-warning').addClass('wb-error').html(error);
    }

    function cancelProcessing(scope) {
        // if already clicked, do nothing
        if (!scope || !state[scope]) {
            return;
        }
        if ('running' != state[scope].status) {
            return;
        }

        state[scope].msgArea.removeClass('wb-success wb-error').addClass('wb-warning').html('Import cancelled!');
        endProcessing(scope);
    }


    function endProcessing(scope) {
        // hide cancel, show process
        if (state[scope]) {
            state[scope].cancelButton.hide();
            state[scope].startButton.show();
            state[scope].startButton.attr('disabled', null);
            state[scope] = null;
        }

        updateDisplay(scope, false);
    }

    function updateDisplay(scope, importInProgress) {
        var $toggle = $('#jform_params_' + scope + '_settings_toggle');
        setToggleValue($toggle, !importInProgress);
    }

    function setToggleValue($toggle, value) {
        if (value) {
            $toggle.attr('checked', 'checked');
            $toggle.val(1);
        }
        else {
            $toggle.attr('checked', '');
            $toggle.val(0);

        }
        $toggle.change();
    }

    function onReady() {
        try {
            $('.wb-js-button-start').on('click', buttonStartClick);
            $('.wb-js-button-cancel').on('click', buttonCancelClick);
            $('.wb-js-button-cancel').hide();
        }
        catch (e) {
            console.log('Error setting up sh404sef importer javascript: ' + e.message);
        }
    }

    $(document).ready(onReady);

    return _app;

})
(window.weeblrApp = window.weeblrApp || {}, window, document, jQuery);

