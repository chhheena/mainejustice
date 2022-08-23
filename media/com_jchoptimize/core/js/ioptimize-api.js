/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

var jchIOptimizeApi = (function ($) {

    'use strict';

//Initialize timer object
    var timer = null;
//Array of file objects to optimize
    var files = [];
//Array of subdirectories under expanded folder in file tree
    var subdirs = [];
//Count of current file being optimized initialized to 0	
    var current = 0;
//Total amount of files to be optimized
    var cnt = 0;
    var total = 0;
//Amount of files that are actually optimized
    var optimize = 0;
//AMount of files converted to webp
    var webp = 0;
//Path of expanded folder
    var dir = '';
//Path of log file
    var log_path = '';
//Object containing relevant settings saved in the plugin
    var params = {};
//set to fail if request not authenticated
    var status = 'success';
//Message if not authenticated
    var authmessage = '';

    var api_mode = 'auto';

    var intervalID = 0;

    var optimizeImages = function (page, api_mode) {

        if (jch_params === undefined || jch_params === null) {
            params.pro_downloadid = $("input[id$='pro_downloadid']").val();
            params.hidden_api_secret = $("input[id$='hidden_api_secret']").val();
            params.ignore_optimized = $("input:radio[name*='ignore_optimized']:checked").val();
            params.recursive = $("input:radio[name*='recursive']:checked").val();
            params.pro_api_resize_mode = $("input:radio[name*='pro_api_resize_mode']:checked").val();
            params.pro_next_gen_images = $("input:radio[name*='pro_next_gen_images']:checked").val();
        } else {
            params = jch_params;
        }

        //Ensure Download ID is entered before proceeding
        if (params.pro_downloadid.length === 0) {
            alert(jch_noproid);
            return false;
        }

        if (api_mode === 'manual') {

            //Get the root folder in the file tree
            var root = $("#file-tree-container ul.jqueryFileTree li.root > a").data("root");
            //Get the folder in the file tree that is expanded
            var li = $("#file-tree-container ul.jqueryFileTree").find("li.expanded").last();

            //At least one of the subfolder or files in Explorer View needs to be checked
            if ($("#files-container input[type=checkbox]:checked").length) {
                //Save the path of the expanded folder found in the data-url attribute of the anchor tag
                dir = {path: li.find("a").data("url")};

                //Paths of subfolders are saved in the value of each checkbox, push each checked box in subdirs
                $("#files-container li.directory input[type=checkbox]:checked").each(function () {
                    subdirs.push($(this).val());
                });

                //Iterate over each selected file in expanded directory
                $("#files-container li.file input[type=checkbox]:checked").each(function () {
                    //Create file object
                    var file = {};

                    //Save path of file stored in value of checkbox
                    file.path = root + $(this).val();

                    //Get the new width of file if entered
                    if ($(this).parent().parent().find("input[name=width]").val().length) {
                        file.width = $(this).parent().parent().find("input[name=width]").val();
                    }

                    //Get the new height of file if entered
                    if ($(this).parent().parent().find("input[name=height]").val().length) {
                        file.height = $(this).parent().parent().find("input[name=height]").val();
                    }

                    //Push file object in files array.
                    files.push(file);
                });

                addProgressBar('#optimize-images-container');
            } else {
                alert(jch_message);

                return false;
            }
        }

        if (api_mode === 'auto') {
            addProgressBar('#api2-optimize-images-container');
        }
        //Call function to get names of all files in selected subdirectories
        $.when(processAjax(page, {}, params, 'getfiles', api_mode)).then(function () {

            var no_files_msg = ' files found.';

            if (total > 0) {
                no_files_msg += ' Uploading files for optimization...';
            }

            $("div#optimize-status").html(total.toLocaleString() + no_files_msg);

            //call function to optimize files in array
            processFilePacks(page, api_mode);
        });
    }

    var addProgressBar = function (element) {
        //Load progress bar with log window
        $(element)
            .html('<div id="progressbar"></div> \
			 <div id="optimize-status">Gathering files to optimize. Please wait...</div> \
                         <div><ul id="optimize-log"></ul></div>');
        $("#progressbar").progressbar({value: 0});
    }

    var processFilePacks = function (page, api_mode) {
        //array to hold ajax objects
        var deferreds = [];
        //Number of ajax requests to send before waiting for Ajax completion
        var loops = 10;
        //Size of packets of files to send for optimization
        var filepacksize = 5;

        if (api_mode === 'manual') {
            for (var i = 0; i < loops && cnt < total; i++) {

                //Packets of files
                var filepack = [];

                for (var j = 0; j < filepacksize && cnt < total; cnt++, j++) {
                    filepack.push(files[cnt]);
                }

                deferreds.push(processAjax(page, filepack, params, 'optimize', api_mode));
            }
        } else {
            for (var k = 0, l = 0; k < loops && l < files.length; l++, k++) {
                cnt += files[l].images.length;
                deferreds.push(processAjax(page, files[l], params, 'optimize', api_mode));
            }
        }

        //When number of Ajax requests in loop is queued, wait until all Ajax
        //requests are completed before looping in another queue or print
        //completion message
        $.when.apply($, deferreds).then(function () {

            processMoreFilePacks(page, api_mode);
        }, function () {
            //There was a failure in the last loop just move the current count along and continue
            current = cnt;

            updateProgressBar();
            updateStatusBar();
            processMoreFilePacks(page, api_mode);
        });

    }

    /**
     *
     *
     * @param page
     * @param api_mode
     */
    var processMoreFilePacks = function (page, api_mode) {
        if ((api_mode === 'manual' && cnt < total) || (api_mode === 'auto' && cnt < files.length)) {
            processFilePacks(page, api_mode);
        } else {
            let log_container = $("ul#optimize-log");
            log_container.append('<li>Adding logs to '
                + log_path +
                '/com_jchoptimize.logs.php...</li>');

            log_container.append('<li>Done! Reloading page in <span id="reload-timer">10</span> seconds...</li>');

            var reload_timer = 10;

            var intervalFunc = function () {
                $("span#reload-timer").text(--reload_timer);

                if (reload_timer === 0) {
                    window.clearInterval(intervalID);
                }
            }

            intervalID = window.setInterval(intervalFunc, 1000);

            var reload = function () {
                var dir_msg = '';

                if (dir.path !== undefined) {
                    dir_msg = "&dir=" + encodeURIComponent(dir.path);
                }

                window.location.href = page + dir_msg + "&status=success&cnt=" +
                    optimize
            };

            window.setTimeout(reload, 10000);
        }

    }

    /**
     * Communicates with the website server via ajax re the files to be optimized
     *
     * @param page          string    Url of admin settings page
     * @param filepack    array     Package of files to be optimized
     * @param params      object    Array of plugin parameters obtained via javascript from settings page
     * @param task        string    Current task being completed (getfiles|optimize)
     * @param api_mode
     *
     * @return void
     */
    var processAjax = function (page, filepack, params, task, api_mode) {

        //create timestamp to append to ajax call to prevent caching
        var timestamp = getTimeStamp();

        //need to return the jqXHR object to be used as deferreds
        return $.ajax({
            dataType: 'json',
            url: jchPlatform.jch_ajax_url_optimizeimages + '&_=' + timestamp,
            data: {
                'filepack': filepack, 'subdirs': subdirs, 'params': params, 'optimize_task': task, 'api_mode': api_mode
            },
            timeout: 0,
            success: function (response) {

                //If we haven't started optimizing files then get the
                //total amount to be optimized
                if (task === 'getfiles') {
                    //Add the selected files in expanded directory
                    //to the files in selected subdirectories recursively

                    if (api_mode === 'manual') {
                        //convert the data object to an array of objects
                        var dataArray = Object.keys(response.data.files).map(i => response.data.files[i])
                        files = $.merge(files, dataArray);
                        total = files.length;
                    } else {
                        files = response.data.files;
                        var images = files.map(function (value, index) {
                            return value['images'];
                        });
                        var merged_images = [].concat.apply([], images);
                        total = merged_images.length;
                    }

                    log_path = response.data.log_path;
                } else {
                    if (!response.success) {
                        logMessage(response.message);

                        //If authentication or file upload error, abort with
                        //error message
                        if (response.code === 403 || response.code === 499) {
                            status = 'fail';
                            authmessage = response.message;

                            window.location.href = page + "&status=fail&msg=" + encodeURIComponent(response.message);
                        }
                    } else {
                        response.data.forEach(function (item) {

                            //Calculate percentage of files that are currently optimized
                            current++;
                            updateProgressBar();

                            if (item[0].success) {
                                //Increment number of files optimized
                                optimize++;
                            }

                            if (item[1] !== undefined && item[1].success) {
                                //Increment number of files converted to webp
                                webp++;
                            }

                            updateStatusBar();
                            logMessage(item[0].message);

                            if (item[1] !== undefined && item[1].message) {
                                logMessage(item[1].message);
                            }
                        });
                    }
                }

            },
            error: function (jqXHR, textStatus, errorThrown) {
                logMessage(textStatus + ': ' + errorThrown);
                logMessage('Response from server:');
                logMessage(jqXHR.responseText);

                //  var html = jqXHR.responseText.replace(/\\([\s\S])|(")/g, "\\$1$2");
                // logMessage('<iframe src="about:blank" width="600" height="200" srcdoc="' + html + '"></iframe>');
            }
        });
    }

    var updateProgressBar = function () {
        var pbvalue = Math.floor((current / total) * 100);

        if (pbvalue > 0) {
            //Update progress bar with new percentage
            $('#progressbar').progressbar({
                value: pbvalue
            });
        }
    }

    var updateStatusBar = function () {
        $('div#optimize-status').html('Processed ' + current.toLocaleString() + ' / ' + total.toLocaleString() + ' files, ' + optimize.toLocaleString() + ' optimized, ' + webp.toLocaleString() + ' converted to webp format...');
    }

    var logMessage = function (message) {
        var logWindow = $('ul#optimize-log');
        //Append message to log window
        logWindow.append('<li>' + message + '</li>');
        //Scroll to bottom
        logWindow.animate({scrollTop: logWindow.prop("scrollHeight")}, 20);
    }

    var getTimeStamp = function () {
        return new Date().getTime();
    };

    return {
        optimizeImages: optimizeImages
    }

}(jQuery));