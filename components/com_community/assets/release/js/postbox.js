(function () {
/**
 * @license almond 0.2.9 Copyright (c) 2011-2014, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/jrburke/almond for details
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*jslint sloppy: true */
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
    var main, req, makeMap, handlers,
        defined = {},
        waiting = {},
        config = {},
        defining = {},
        hasOwn = Object.prototype.hasOwnProperty,
        aps = [].slice,
        jsSuffixRegExp = /\.js$/;

    function hasProp(obj, prop) {
        return hasOwn.call(obj, prop);
    }

    /**
     * Given a relative module name, like ./something, normalize it to
     * a real name that can be mapped to a path.
     * @param {String} name the relative name
     * @param {String} baseName a real name that the name arg is relative
     * to.
     * @returns {String} normalized name
     */
    function normalize(name, baseName) {
        var nameParts, nameSegment, mapValue, foundMap, lastIndex,
            foundI, foundStarMap, starI, i, j, part,
            baseParts = baseName && baseName.split("/"),
            map = config.map,
            starMap = (map && map['*']) || {};

        //Adjust any relative paths.
        if (name && name.charAt(0) === ".") {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                //Convert baseName to array, and lop off the last part,
                //so that . matches that "directory" and not name of the baseName's
                //module. For instance, baseName of "one/two/three", maps to
                //"one/two/three.js", but we want the directory, "one/two" for
                //this normalization.
                baseParts = baseParts.slice(0, baseParts.length - 1);
                name = name.split('/');
                lastIndex = name.length - 1;

                // Node .js allowance:
                if (config.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
                    name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
                }

                name = baseParts.concat(name);

                //start trimDots
                for (i = 0; i < name.length; i += 1) {
                    part = name[i];
                    if (part === ".") {
                        name.splice(i, 1);
                        i -= 1;
                    } else if (part === "..") {
                        if (i === 1 && (name[2] === '..' || name[0] === '..')) {
                            //End of the line. Keep at least one non-dot
                            //path segment at the front so it can be mapped
                            //correctly to disk. Otherwise, there is likely
                            //no path mapping for a path starting with '..'.
                            //This can still fail, but catches the most reasonable
                            //uses of ..
                            break;
                        } else if (i > 0) {
                            name.splice(i - 1, 2);
                            i -= 2;
                        }
                    }
                }
                //end trimDots

                name = name.join("/");
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        //Apply map config if available.
        if ((baseParts || starMap) && map) {
            nameParts = name.split('/');

            for (i = nameParts.length; i > 0; i -= 1) {
                nameSegment = nameParts.slice(0, i).join("/");

                if (baseParts) {
                    //Find the longest baseName segment match in the config.
                    //So, do joins on the biggest to smallest lengths of baseParts.
                    for (j = baseParts.length; j > 0; j -= 1) {
                        mapValue = map[baseParts.slice(0, j).join('/')];

                        //baseName segment has  config, find if it has one for
                        //this name.
                        if (mapValue) {
                            mapValue = mapValue[nameSegment];
                            if (mapValue) {
                                //Match, update name to the new value.
                                foundMap = mapValue;
                                foundI = i;
                                break;
                            }
                        }
                    }
                }

                if (foundMap) {
                    break;
                }

                //Check for a star map match, but just hold on to it,
                //if there is a shorter segment match later in a matching
                //config, then favor over this star map.
                if (!foundStarMap && starMap && starMap[nameSegment]) {
                    foundStarMap = starMap[nameSegment];
                    starI = i;
                }
            }

            if (!foundMap && foundStarMap) {
                foundMap = foundStarMap;
                foundI = starI;
            }

            if (foundMap) {
                nameParts.splice(0, foundI, foundMap);
                name = nameParts.join('/');
            }
        }

        return name;
    }

    function makeRequire(relName, forceSync) {
        return function () {
            //A version of a require function that passes a moduleName
            //value for items that may need to
            //look up paths relative to the moduleName
            return req.apply(undef, aps.call(arguments, 0).concat([relName, forceSync]));
        };
    }

    function makeNormalize(relName) {
        return function (name) {
            return normalize(name, relName);
        };
    }

    function makeLoad(depName) {
        return function (value) {
            defined[depName] = value;
        };
    }

    function callDep(name) {
        if (hasProp(waiting, name)) {
            var args = waiting[name];
            delete waiting[name];
            defining[name] = true;
            main.apply(undef, args);
        }

        if (!hasProp(defined, name) && !hasProp(defining, name)) {
            throw new Error('No ' + name);
        }
        return defined[name];
    }

    //Turns a plugin!resource to [plugin, resource]
    //with the plugin being undefined if the name
    //did not have a plugin prefix.
    function splitPrefix(name) {
        var prefix,
            index = name ? name.indexOf('!') : -1;
        if (index > -1) {
            prefix = name.substring(0, index);
            name = name.substring(index + 1, name.length);
        }
        return [prefix, name];
    }

    /**
     * Makes a name map, normalizing the name, and using a plugin
     * for normalization if necessary. Grabs a ref to plugin
     * too, as an optimization.
     */
    makeMap = function (name, relName) {
        var plugin,
            parts = splitPrefix(name),
            prefix = parts[0];

        name = parts[1];

        if (prefix) {
            prefix = normalize(prefix, relName);
            plugin = callDep(prefix);
        }

        //Normalize according
        if (prefix) {
            if (plugin && plugin.normalize) {
                name = plugin.normalize(name, makeNormalize(relName));
            } else {
                name = normalize(name, relName);
            }
        } else {
            name = normalize(name, relName);
            parts = splitPrefix(name);
            prefix = parts[0];
            name = parts[1];
            if (prefix) {
                plugin = callDep(prefix);
            }
        }

        //Using ridiculous property names for space reasons
        return {
            f: prefix ? prefix + '!' + name : name, //fullName
            n: name,
            pr: prefix,
            p: plugin
        };
    };

    function makeConfig(name) {
        return function () {
            return (config && config.config && config.config[name]) || {};
        };
    }

    handlers = {
        require: function (name) {
            return makeRequire(name);
        },
        exports: function (name) {
            var e = defined[name];
            if (typeof e !== 'undefined') {
                return e;
            } else {
                return (defined[name] = {});
            }
        },
        module: function (name) {
            return {
                id: name,
                uri: '',
                exports: defined[name],
                config: makeConfig(name)
            };
        }
    };

    main = function (name, deps, callback, relName) {
        var cjsModule, depName, ret, map, i,
            args = [],
            callbackType = typeof callback,
            usingExports;

        //Use name if no relName
        relName = relName || name;

        //Call the callback to define the module, if necessary.
        if (callbackType === 'undefined' || callbackType === 'function') {
            //Pull out the defined dependencies and pass the ordered
            //values to the callback.
            //Default to [require, exports, module] if no deps
            deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
            for (i = 0; i < deps.length; i += 1) {
                map = makeMap(deps[i], relName);
                depName = map.f;

                //Fast path CommonJS standard dependencies.
                if (depName === "require") {
                    args[i] = handlers.require(name);
                } else if (depName === "exports") {
                    //CommonJS module spec 1.1
                    args[i] = handlers.exports(name);
                    usingExports = true;
                } else if (depName === "module") {
                    //CommonJS module spec 1.1
                    cjsModule = args[i] = handlers.module(name);
                } else if (hasProp(defined, depName) ||
                           hasProp(waiting, depName) ||
                           hasProp(defining, depName)) {
                    args[i] = callDep(depName);
                } else if (map.p) {
                    map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
                    args[i] = defined[depName];
                } else {
                    throw new Error(name + ' missing ' + depName);
                }
            }

            ret = callback ? callback.apply(defined[name], args) : undefined;

            if (name) {
                //If setting exports via "module" is in play,
                //favor that over return value and exports. After that,
                //favor a non-undefined return value over exports use.
                if (cjsModule && cjsModule.exports !== undef &&
                        cjsModule.exports !== defined[name]) {
                    defined[name] = cjsModule.exports;
                } else if (ret !== undef || !usingExports) {
                    //Use the return value from the function.
                    defined[name] = ret;
                }
            }
        } else if (name) {
            //May just be an object definition for the module. Only
            //worry about defining if have a module name.
            defined[name] = callback;
        }
    };

    requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
        if (typeof deps === "string") {
            if (handlers[deps]) {
                //callback in this case is really relName
                return handlers[deps](callback);
            }
            //Just return the module wanted. In this scenario, the
            //deps arg is the module name, and second arg (if passed)
            //is just the relName.
            //Normalize module name, if it contains . or ..
            return callDep(makeMap(deps, callback).f);
        } else if (!deps.splice) {
            //deps is a config object, not an array.
            config = deps;
            if (config.deps) {
                req(config.deps, config.callback);
            }
            if (!callback) {
                return;
            }

            if (callback.splice) {
                //callback is an array, which means it is a dependency list.
                //Adjust args if there are dependencies
                deps = callback;
                callback = relName;
                relName = null;
            } else {
                deps = undef;
            }
        }

        //Support require(['a'])
        callback = callback || function () {};

        //If relName is a function, it is an errback handler,
        //so remove it.
        if (typeof relName === 'function') {
            relName = forceSync;
            forceSync = alt;
        }

        //Simulate async callback;
        if (forceSync) {
            main(undef, deps, callback, relName);
        } else {
            //Using a non-zero value because of concern for what old browsers
            //do, and latest browsers "upgrade" to 4 if lower value is used:
            //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
            //If want a value immediately, use require('id') instead -- something
            //that works in almond on the global level, but not guaranteed and
            //unlikely to work in other AMD implementations.
            setTimeout(function () {
                main(undef, deps, callback, relName);
            }, 4);
        }

        return req;
    };

    /**
     * Just drops the config on the floor, but returns req in case
     * the config return value is used.
     */
    req.config = function (cfg) {
        return req(cfg);
    };

    /**
     * Expose module registry for debugging and tooling
     */
    requirejs._defined = defined;

    define = function (name, deps, callback) {

        //This module may not have dependencies
        if (!deps.splice) {
            //deps is not an array, so probably means
            //an object literal or factory function for
            //the value. Adjust args.
            callback = deps;
            deps = [];
        }

        if (!hasProp(defined, name) && !hasProp(waiting, name)) {
            waiting[name] = [name, deps, callback];
        }
    };

    define.amd = {
        jQuery: true
    };
}());

define("../../vendors/almond", function(){});

define('jst', [],function() {

window["joms"] = window["joms"] || {};
window["joms"]["jst"] = window["joms"]["jst"] || {};
window["joms"]["jst"]["html/dropdown/location-list"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') } if ( data && data.items && data.items.length ) for ( var i = 0; i < data.items.length; i++ ) { ;__p += ' <li data-index="' +((__t = ( i )) == null ? '' : __t) +'"> <p style="margin:0">' +((__t = ( data.items[i].name )) == null ? '' : __t) +'</p> <span>' +((__t = ( data.items[i].vicinity || data.language.geolocation.near_here )) == null ? '' : __t) +'</span> </li> '; } else { ;__p += ' <li><em>' +((__t = ( data.language.geolocation.empty )) == null ? '' : __t) +'</em></li> '; } ;return __p};
window["joms"]["jst"]["html/dropdown/location"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div class="joms-postbox-dropdown location-dropdown"> <div class=joms-postbox-map></div> <div class="joms-postbox-action joms-location-action" style="z-index:99999"> <button class=joms-add-location><i class=joms-icon-map-marker></i>' +((__t = ( data.language.geolocation.select_button )) == null ? '' : __t) +'</button> <button class=joms-remove-location><i class=joms-icon-remove></i>' +((__t = ( data.language.geolocation.remove_button )) == null ? '' : __t) +'</button> </div> <input class="joms-postbox-keyword joms-input" type=text> <ul class=joms-postbox-locations></ul> </div>';return __p};
window["joms"]["jst"]["html/dropdown/mood"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div class="joms-postbox-dropdown mood-dropdown"> <ul class="joms-postbox-emoticon joms-list clearfix"> '; if ( data && data.items && data.items.length ) for ( var i = 0; i < data.items.length; i++ ) { ;__p += ' <li data-mood="' +((__t = ( data.items[i][0] )) == null ? '' : __t) +'"> '; if ( typeof data.items[i][2] !== 'undefined') { ;__p += ' <div><img class=joms-emoticon src="' +((__t = ( data.items[i][2] )) == null ? '' : __t) +'"><span>' +((__t = ( data.items[i][1] )) == null ? '' : __t) +'</span></div> '; } else { ;__p += ' <div><i class="joms-emoticon joms-emo-' +((__t = ( data.items[i][0] )) == null ? '' : __t) +'"></i><span>' +((__t = ( data.items[i][1] )) == null ? '' : __t) +'</span></div> '; } ;__p += ' </li> '; } ;__p += ' <li class=joms-remove-button>' +((__t = ( data.language.status.remove_mood_button )) == null ? '' : __t) +'</li> </ul> </div>';return __p};
window["joms"]["jst"]["html/dropdown/privacy"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div class="joms-postbox-dropdown privacy-dropdown"> <ul class=joms-list> '; if ( data && data.items && data.items.length ) for ( var i = 0; i < data.items.length; i++ ) { ;__p += ' <li data-priv="' +((__t = ( data.items[i][0] )) == null ? '' : __t) +'"> <p class=reset-gap><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-' +((__t = ( data.items[i][1] )) == null ? '' : __t) +'"></use></svg> ' +((__t = ( data.items[i][2] )) == null ? '' : __t) +'</p> <span>' +((__t = ( data.items[i][3] )) == null ? '' : __t) +'</span> </li> '; } ;__p += ' </ul> </div>';return __p};
window["joms"]["jst"]["html/inputbox/eventdesc"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div style="position:relative"> <div class="joms-postbox-input joms-inputbox"> <div class=inputbox> <div style="position:relative" class="joms-textarea__wrapper"> <div style="position:relative"><span class=input></span></div> <textarea class="input joms-textarea" placeholder="' +((__t = ( data && data.placeholder || '' )) == null ? '' : __t) +'" style="min-height:1.4em"></textarea> </div> </div> </div> <div class="joms-icon joms-icon--emoticon" style="top:20px; right: 5px;"><div style="position:relative"><svg viewBox="0 0 16 16" onclick="joms.view.comment.showEmoticonBoard(this);"><use xlink:href="'+window.joms_current_url+'#joms-icon-smiley"></use></svg></div></div> <div class="charcount joms-postbox-charcount"></div> </div>';return __p};
window["joms"]["jst"]["html/inputbox/eventtitle"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div style="position:relative"> <div class="joms-postbox-input joms-inputbox" style="padding:0; border-bottom:0; min-height:0"> <div class=inputbox> <div style="position:relative"> <div style="position:relative"><span class=input></span></div> <textarea class="input joms-textarea"  placeholder="' +((__t = ( data && data.placeholder || '' )) == null ? '' : __t) +'" style="min-height:1.4em"></textarea> </div> </div> </div> </div>';return __p};
window["joms"]["jst"]["html/inputbox/base"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div style="position:relative"> <div class="joms-postbox-input joms-inputbox"> <div class=inputbox> <div style="position:relative"> <div style="position:relative"><span class=input></span></div> <div class=joms-textarea__wrapper><textarea class="input input-photo-desc joms-textarea" placeholder="' +((__t = ( data && data.placeholder || '' )) == null ? '' : __t) +'" style="min-height:1.4em"></textarea></div> </div> </div> </div> <div class="joms-icon joms-icon--emoticon" style="top:20px; right: 5px;"><div style="position:relative"><svg viewBox="0 0 16 16" onclick="joms.view.comment.showEmoticonBoard(this);"><use xlink:href="'+window.joms_current_url+'#joms-icon-smiley"></use></svg></div></div> <div class="charcount joms-postbox-charcount"></div> </div>';return __p};
window["joms"]["jst"]["html/inputbox/video"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div style="position:relative"> <div class="joms-postbox-input joms-inputbox"> <div class=inputbox> <div style="position:relative"> <div style="position:relative"><span class=input></span></div> <div class=joms-textarea__wrapper><textarea class="input joms-postbox-video-description joms-textarea" placeholder="' +((__t = ( data && data.placeholder || '' )) == null ? '' : __t) +'" style="min-height:1.4em"></textarea></div> </div> </div> </div> <div class="joms-icon joms-icon--emoticon" style="top:20px; right: 5px;"><div style="position:relative"><svg viewBox="0 0 16 16" onclick="joms.view.comment.showEmoticonBoard(this);"><use xlink:href="'+window.joms_current_url+'#joms-icon-smiley"></use></svg></div></div> <div class="charcount joms-postbox-charcount"></div> </div>';return __p};
window["joms"]["jst"]["html/inputbox/videourl"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div style="position:relative"> <div class="joms-postbox-input joms-inputbox" style="padding:0; border-bottom:0; min-height:0"> <div class=inputbox> <div style="position:relative"> <div style="position:relative"><span class=input></span></div> <textarea class="input joms-textarea joms-postbox-video-url" placeholder="' +((__t = ( data && data.placeholder || '' )) == null ? '' : __t) +'" style="min-height:1.4em"></textarea> </div> </div> </div> </div>';return __p};
window["joms"]["jst"]["html/postbox/custom"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div> <div class=joms-postbox-inner-panel> <div class=joms-postbox-double-panel> <ul class="joms-list clearfix"> <li class=joms-postbox-predefined-message><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-cog"></use></svg> ' +((__t = ( data.language.custom.predefined_button )) == null ? '' : __t) +'</li> <li class=joms-postbox-custom-message><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-pencil"></use></svg> ' +((__t = ( data.language.custom.custom_button )) == null ? '' : __t) +'</li> </ul> </div> </div> <div class=joms-postbox-custom> <div class=joms-postbox-custom-state-predefined> <div class=joms-postbox-inner-panel> <span>' +((__t = ( data.language.custom.predefined_label )) == null ? '' : __t) +'</span><br> <select name=predefined style="width: 100%"> '; if ( data && data.messages && data.messages.length ) { ;__p += ' '; for ( var i = 0; i < data.messages.length; i++ ) { ;__p += ' <option value="' +((__t = ( data.messages[i][0] )) == null ? '' : __t) +'">' +((__t = ( data.messages[i][1] )) == null ? '' : __t) +'</option> '; } ;__p += ' '; } ;__p += ' </select> </div> </div> <div class=joms-postbox-custom-state-custom> <div class=joms-postbox-inner-panel> <span>' +((__t = ( data.language.custom.custom_label )) == null ? '' : __t) +'</span><br> <textarea name=custom></textarea> </div> </div> <nav class="joms-postbox-tab selected"> <ul class="joms-list inline"> <li data-tab=privacy><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-earth"></use></svg> <span class=visible-desktop></span></li> </ul> <div class=joms-postbox-action> <button class=joms-postbox-cancel>' +((__t = ( data.language.postbox.cancel_button )) == null ? '' : __t) +'</button> <button class=joms-postbox-save>' +((__t = ( data.language.postbox.post_button )) == null ? '' : __t) +'</button> </div> <div class=joms-postbox-loading style="display:none;"> <img src="' +((__t = ( data.juri.root )) == null ? '' : __t) +'components/com_community/assets/ajax-loader.gif" alt="loader"> <div </nav> <div class="joms-postbox-dropdown privacy-dropdown"></div> </div> </div>';return __p};
window["joms"]["jst"]["html/postbox/event"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div class=joms-postbox-event> <div class=joms-postbox-inner-panel> <div class=joms-postbox-title></div> <div class=joms-postbox-event-detail> <div class="joms-postbox-event-panel joms-postbox-event-label-category" style="display:none"> <div class=joms-postbox-event-title style="padding-bottom:0;padding-top:8px"> <div class=joms-input-field-name>' +((__t = ( data.language.event.category )) == null ? '' : __t) +'</div> <span class=joms-input-text style="display:inline-block"></span> </div> </div> <div class="joms-postbox-event-panel joms-postbox-event-label-location" style="display:none"> <div class=joms-postbox-event-title style="padding-bottom:0;padding-top:8px"> <div class=joms-input-field-name>' +((__t = ( data.language.event.location )) == null ? '' : __t) +'</div> <span class=joms-input-text style="display:inline-block"></span> </div> </div> <div class="joms-postbox-event-panel joms-postbox-event-label-date" style="display:none"> <div class=joms-postbox-event-title style="padding-bottom:0;padding-top:8px"> <div class=joms-input-field-name>' +((__t = ( data.language.event.date_and_time )) == null ? '' : __t) +'</div> <span class=joms-input-text style="display:inline-block"></span> </div> </div> </div> </div> <div class=joms-postbox-inputbox></div> <nav class="joms-postbox-tab selected"> <ul class="joms-list inline"> <li data-tab=event><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-cog"></use></svg> <span class=visible-desktop>' +((__t = ( data.language.event.event_detail )) == null ? '' : __t) +'</span></li> </ul> <div class=joms-postbox-action> <button class=joms-postbox-cancel>' +((__t = ( data.language.postbox.cancel_button )) == null ? '' : __t) +'</button> <button class=joms-postbox-save>' +((__t = ( data.language.postbox.post_button )) == null ? '' : __t) +'</button> </div> <div class=joms-postbox-loading style="display:none;"> <img src="' +((__t = ( data.juri.root )) == null ? '' : __t) +'components/com_community/assets/ajax-loader.gif" alt="loader"> <div </nav> </div>';return __p};
window["joms"]["jst"]["html/postbox/fetcher-video"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div class=joms-fetched-wrapper> '; if ( data.image ) { ;__p += ' <div class=joms-fetched-images> <img src="' +((__t = ( data.image )) == null ? '' : __t) +'"> </div> '; } ;__p += ' <div class="joms-fetched-field joms-fetched-title"> <span style="font-weight:bold">' +((__t = ( data.title || data.titlePlaceholder )) == null ? '' : __t) +'</span> <input type=text class=joms-input value="' +((__t = __e( data.title || '' )) == null ? '' : __t) +'" style="display:none;margin:0"> </div> <div class="joms-fetched-field joms-fetched-description"> <span>' +((__t = ( data.description || data.descPlaceholder )) == null ? '' : __t) +'</span> <textarea class=joms-textarea style="display:none;margin:0">' +((__t = ( data.description || '' )) == null ? '' : __t) +'</textarea> </div> <div class=clearfix></div> <span class=joms-fetched-close ><i class=joms-icon-remove></i>' +((__t = ( data.lang.cancel )) == null ? '' : __t) +'</span> <div style="padding-top:10px"> <div class="joms-fetched-category joms-select" style="padding:3px 0"></div> </div> </div>';return __p};
window["joms"]["jst"]["html/postbox/fetcher"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div class=joms-postbox-inner-panel> <div style="position:relative"> <div class=joms-fetched-images> '; if ( data.image && data.image.length ) { ;__p += ' <div style="height:120px"> '; for ( var i = 0; i < data.image.length; i++ ) { ;__p += ' <img src="' +((__t = ( data.image[i] )) == null ? '' : __t) +'" style="width:100%;height:100%;' +((__t = ( i ? 'display:none' : '' )) == null ? '' : __t) +'"> '; } ;__p += ' </div> '; if ( data.image.length > 1 ) { ;__p += ' <div class=joms-fetched-nav style="text-align:center"> <span class=joms-fetched-previmg style="cursor:pointer">&laquo; ' +((__t = ( data.lang.prev )) == null ? '' : __t) +'</span> &nbsp;&nbsp; <span class=joms-fetched-nextimg style="cursor:pointer">' +((__t = ( data.lang.next )) == null ? '' : __t) +' &raquo;</span> </div> '; } ;__p += ' '; } else { ;__p += ' <div style="height:120px;width:120px;background-color:#F9F9F9;color:7F8C8D;text-align:center;vertical"> &nbsp;<br>&nbsp;<br>no<br>thumbnail </div> '; } ;__p += ' </div> <div class="joms-fetched-field joms-fetched-title"> <span style="font-weight:bold">' +((__t = ( data.title || data.titlePlaceholder )) == null ? '' : __t) +'</span> <input type=text class=joms-input value="' +((__t = __e( data.title || '' )) == null ? '' : __t) +'" style="display:none;margin:0"> </div> <div class="joms-fetched-field joms-fetched-description"> <span>' +((__t = ( data.description || data.descPlaceholder )) == null ? '' : __t) +'</span> <textarea class=joms-textarea style="display:none;margin:0">' +((__t = ( data.description || '' )) == null ? '' : __t) +'</textarea> </div> <div class=clearfix></div> <span class=joms-fetched-close style="top:0"><i class=joms-icon-remove></i>' +((__t = ( data.lang.cancel )) == null ? '' : __t) +'</span> </div> </div>';return __p};
window["joms"]["jst"]["html/postbox/photo-item"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<li id="' +((__t = ( data.id )) == null ? '' : __t) +'" class=joms-postbox-photo-item> <div class=img-wrapper> <img src="' +((__t = ( data.src )) == null ? '' : __t) +'" > </div> <div class=joms-postbox-photo-action style="display:none"> <span class=joms-postbox-photo-remove>×</span> </div> <div class=joms-postbox-photo-progressbar> <div class=joms-postbox-photo-progress></div> </div> </li>';return __p};
window["joms"]["jst"]["html/postbox/photo-preview"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div class=joms-postbox-photo-preview> <ul class="joms-list clearfix"></ul> <div class=joms-postbox-photo-form></div> </div>';return __p};
window["joms"]["jst"]["html/postbox/photo"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<div class="joms-postbox-photo-wrapper" style="position:relative;"> <div class="joms-postbox--droparea" id="joms-postbox-photo--droparea">'+ data.language.photo.drop_to_upload + '</div> <div id=joms-postbox-photo-upload style="position:absolute;top:0;left:0;width:1px;height:1px;overflow:hidden"> <button id=joms-postbox-photo-upload-btn>Upload</button> </div> <div class=joms-postbox-inner-panel style="position:relative"> ' + ( data.allowgif ? '<div class=joms-postbox-double-panel> <ul class="joms-list clearfix"> <li>' : '' ) + '<div class=joms-postbox-photo-upload> <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-images"></use></svg> ' +((__t = ( data.language.photo[ data.allowgif ? 'upload_button_2' : 'upload_button' ] )) == null ? '' : __t) +' </div> ' + ( data.allowgif ? ('</li> <li> <div class=joms-postbox-gif-upload> <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-images"></use></svg> ' +((__t = ( data.language.photo.gif_upload_button )) == null ? '' : __t) +' </div> </li> </ul> </div>') : '' ) + ' </div> <div class=joms-postbox-photo> <div class=joms-postbox-preview></div> <div class=joms-postbox-inputbox></div> <nav class="joms-postbox-tab selected"> <ul class="joms-list inline"> <li data-tab=upload data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-images"></use></svg> <span class=visible-desktop>' +((__t = ( data.language.photo.upload_button_more )) == null ? '' : __t) +'</span></li> <li data-tab=mood><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-happy"></use></svg> <span class=visible-desktop>' +((__t = ( data.language.status.mood )) == null ? '' : __t) +'</span></li> </ul> <div class=joms-postbox-action> <button class=joms-postbox-cancel>' +((__t = ( data.language.postbox.cancel_button )) == null ? '' : __t) +'</button> <button class=joms-postbox-save>' +((__t = ( data.language.postbox.post_button )) == null ? '' : __t) +'</button> </div> <div class=joms-postbox-loading style="display:none;"> <img src="' +((__t = ( data.juri.root )) == null ? '' : __t) +'components/com_community/assets/ajax-loader.gif" alt="loader"> </div> </nav> </div> </div>';return __p};
window["joms"]["jst"]["html/postbox/video-item"] = function(data) {var __t, __p = '', __e = _.escape;__p += '<li class=joms-postbox-photo-item> <img width=128 height=128 src="' +((__t = ( data.src )) == null ? '' : __t) +'"> <div class=joms-postbox-photo-action> <span class=joms-postbox-photo-setting><i class=joms-icon-cog></i></span> <span class=joms-postbox-photo-remove><i class=joms-icon-remove></i></span> </div> </li>';return __p};
window["joms"]["jst"]["html/postbox/video"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<div> <div id=joms-postbox-video-upload style="position:absolute;top:0;left:0;width:1px;height:1px;overflow:hidden"> <button id=joms-postbox-video-upload-btn>Upload</button> </div> '; if ( data && data.enable_upload ) { ;__p += ' <div class="joms-postbox-inner-panel joms-initial-panel"> <div class=joms-postbox-double-panel> <ul class="joms-list clearfix"> <li class=joms-postbox-video-url data-action=share><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-link"></use></svg> ' +((__t = ( data.language.video.share_button )) == null ? '' : __t) +'</li> <li class=joms-postbox-video-upload data-action=upload><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-box-add"></use></svg> ' +((__t = ( data.language.video.upload_button )) == null ? '' : __t) +' '; if ( data && data.video_maxsize > 0 ) { ;__p += ' <span>' +((__t = ( data.language.video.upload_maxsize )) == null ? '' : __t) +' ' +((__t = ( data.video_maxsize )) == null ? '' : __t) +'mb</span> '; } ;__p += ' </li> </ul> </div> </div> '; } ;__p += ' <div class=joms-postbox-video> <div class=joms-postbox-video-state-url style="display:none"> <div class=joms-postbox-inner-panel> <div class=joms-postbox-url></div> <div class=joms-postbox-fetched></div> </div> </div> '; if ( data && data.enable_upload ) { ;__p += ' <div class=joms-postbox-video-state-upload style="display:none"> <div class=joms-postbox-inner-panel> <div class=joms-postbox-url></div> <div style="padding-top: 3px;"> <div class=joms-postbox-photo-progressbar style="position:relative;top:auto;bottom:auto;left:auto;right:auto;background-color:#eee;"> <div class=joms-postbox-photo-progress style="width:0"></div> </div> </div> <div style="padding-top: 10px;"> <div class="joms-fetched-category joms-select" style="padding:3px 0"></div> </div> </div> <div class=joms-postbox-inner-panel> <div class=inputbox> <input class=input placeholder="' +((__t = ( data.language.video.upload_title )) == null ? '' : __t) +'" /> </div> </div> </div> '; } ;__p += ' <div class=joms-postbox-inputbox></div> <nav class="joms-postbox-tab selected"> <ul class="joms-list inline"> <li data-tab=mood><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-happy"></use></svg> <span class=visible-desktop>' +((__t = ( data.language.status.mood )) == null ? '' : __t) +'</span></li> <li data-tab=location><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-location"></use></svg> <span class=visible-desktop>' +((__t = ( data.language.video.location )) == null ? '' : __t) +'</span></li> <li data-tab=privacy><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-earth"></use></svg> <span class=visible-desktop></span></li> </ul> <div class=joms-postbox-action> <button class=joms-postbox-cancel>' +((__t = ( data.language.postbox.cancel_button )) == null ? '' : __t) +'</button> <button class=joms-postbox-save>' +((__t = ( data.language.postbox.post_button )) == null ? '' : __t) +'</button> </div> <div class=joms-postbox-loading style="display:none;"> <img src="' +((__t = ( data.juri.root )) == null ? '' : __t) +'components/com_community/assets/ajax-loader.gif" alt="loader"> <div> </nav> </div> </div>';return __p};
window["joms"]["jst"]["html/widget/select-album"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<span>' +((__t = ( data.placeholder || '' )) == null ? '' : __t) +'</span> <ul class=joms-list style="display:none;"> '; if ( data.options && data.options.length ) for ( var i = 0; i < data.options.length; i++ ) { ;__p += ' <li data-value="' +((__t = ( data.options[i][0] )) == null ? '' : __t) +'"> <p style="margin:0">' +((__t = ( data.options[i] && data.options[i][1] || '-' )) == null ? '' : __t) +'</p> <small><i class="joms-icon-' +((__t = ( data.options[i] && data.options[i][3] ? '' + data.options[i][3] : 'globe' )) == null ? '' : __t) +'"></i> ' +((__t = ( data.options[i] && data.options[i][2] ? '' + data.options[i][2] : '-' )) == null ? '' : __t) +'</small> </li> '; } ;__p += ' </ul><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-arrow-down"></use></svg>';return __p};
window["joms"]["jst"]["html/widget/select-form"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<span>' +((__t = ( data.placeholder || '' )) == null ? '' : __t) +'</span> <ul class=joms-list style="display:none;"> '; if ( data.options && data.options.length ) for ( var i = 0; i < data.options.length; i++ ) { ;__p += ' <li data-value="' +((__t = ( data.options[i][0] )) == null ? '' : __t) +'">' +((__t = ( data.options[i][1] )) == null ? '' : __t) +'</li> '; } ;__p += ' </ul> <div>abc</div>';return __p};
window["joms"]["jst"]["html/widget/select"] = function(data) {var __t, __p = '', __e = _.escape, __j = Array.prototype.join;function print() { __p += __j.call(arguments, '') }__p += '<span>' +((__t = ( data.placeholder || '' )) == null ? '' : __t) +'</span> <ul class=joms-list style="display:none;"> '; if ( data.options && data.options.length ) for ( var i = 0; i < data.options.length; i++ ) { ;__p += ' <li data-value="' +((__t = ( data.options[i][0] )) == null ? '' : __t) +'">' +((__t = ( data.options[i][1] )) == null ? '' : __t) +'</li> '; } ;__p += ' </ul><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="' +(window.joms_current_url || '') +'#joms-icon-arrow-down"></use></svg>';return __p};

});


define('sandbox',[],function() {
    var jqr = window.joms.jQuery,
        und = window.joms._,
        bbe = window.joms.Backbone;

    // Sandbox object, also serves as a DOM selector.
    function Sandbox( selector, context ) {
        return jqr( selector, context );
    }

    // Set Backbone to use default jQuery.
    bbe.$ = jqr;

    // Filter used Underscore functions.
    und.pick( und, [
        'each',
        'map',
        'filter',
        'union',
        'intersection',
        'without',
        'bind',
        'debounce',
        'defer',
        'keys',
        'extend',
        'pick',
        'omit',
        'isArray',
        'isNumber',
        'isString',
        'isUndefined',
        'uniqueId'
    ]);

    // Extend sandbox with events, selected Underscore functions,
    // Backbone MVC, and some.
    und.extend( Sandbox, bbe.Events, und, {

        // MV*
        mvc: {
            Model: bbe.Model,
            Models: bbe.Collection,
            View: bbe.View
        },

        // Ajax helper.
        ajax: jqr.ajax,
        param: jqr.param,

        // NOOP
        noop: function() {}

    });

    // Enable deep-extend via jQuery extend.
    Sandbox.__extend = Sandbox.extend;
    Sandbox._$extend = jqr.extend;
    Sandbox.extend = function() {
        var isDeep = arguments[0] === true;
        return Sandbox[ isDeep ? '_$extend' : '__extend' ].apply( null, arguments );
    };

    // Browser detection.
    Sandbox.ua = navigator.userAgent;

    var ua = Sandbox.ua.toLowerCase();
    Sandbox.mobile    = !!ua.match( /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i );
    Sandbox.webkit    = !!ua.match( /webkit/i );
    Sandbox.ie        = !!ua.match( /msie/i );
    Sandbox.ieVersion = Sandbox.ie && +( ua.match( /msie (\d+)\./i )[1] );

    // Experimental flag.
    Sandbox.xpriment = !Sandbox.ie && 1;

    // Publish onclick event.
    Sandbox( document.body ).on( 'click', function( e ) {
        Sandbox.trigger( 'click', Sandbox( e.target ) );
    });

    return Sandbox;

});
define('views/base',[
    'sandbox'
],

// definition
// ----------
function( $ ) {

    return $.mvc.View.extend({

        assign: function( element, view ) {
            if ( !$.isArray( element ) ) {
                element = [ [ element, view ] ];
            }

            $.each( element, function( item ) {
                item[ 1 ].setElement( item[ 0 ] ).render();
            });
        },

        show: function() {
            if ( this.isHidden() ) {
                this.$el.show();
                this.trigger('show');
            }
        },

        hide: function() {
            if ( !this.isHidden() ) {
                this.$el.hide();
                this.trigger('hide');
            }
        },

        toggle: function() {
            this.isHidden() ? this.show() : this.hide();
        },

        isHidden: function() {
            return this.el.style.display === 'none';
        }

    });

});
define('app',[],function() {
    var staticUrl;

    staticUrl = window.joms_script_url || '';
    staticUrl = staticUrl.replace( /js\/$/, '' );

    return {
        staticUrl: staticUrl,
        legacyUrl: staticUrl + '../../'
    };

});
define('utils/ajax',[
    'sandbox'
],

// definition
// ----------
function( $ ) {

    var defaults = {
        type: 'post',
        data: {},
        dataType: 'json',
        error: $.noop,
        success: $.noop,
        complete: $.noop
    };

    function ajax( options ) {
        options = $.extend({}, defaults, options || {});
        options.url = window.jax_live_site;
        options.data = encodeData( options.fn, options.data );
        return $.ajax( options );
    }

    // encode request data
    function encodeData( fn, data ) {
        var params = {};

        // azrul's ajax parameters
        params.option = 'community';
        params.view = window.joms_page || undefined;
        params.task = 'azrul_ajax';
        params.func = fn;
        params.no_html = 1;
        params[ window.jax_token_var ] = 1;

        // azrul's data format
        $.isArray( data ) || (data = []);
        for ( var i = 0, arg; i < data.length; i++ ) {
            arg = data[ i ];
            $.isString( arg ) && ( arg = arg.replace( /"/g, '&quot;' ) );
            $.isArray( arg ) || ( arg = [ '_d_', arg ] );
            params[ 'arg' + ( i + 2 ) ] = JSON.stringify( arg );
        }

        return params;
    }

    return ajax;

});

define('utils/constants',[
    'sandbox'
],

// definition
// ----------
function( $ ) {

    var constants = {};

    function get( key ) {
        if ( typeof key !== 'string' || !key.length )
            return;

        if ( joms && joms.constants ) {
            $.extend( true, constants, joms && joms.constants );
            delete joms.constants;
        }

        var data = constants;

        key = key.split('.');
        while ( key.length ) {
            data = data[ key.shift() ];
        }

        return data;
    }

    function set( key, value ) {
        var data, keys, length;

        if ( typeof key !== 'string' || !key.length )
            return;

        data = constants;
        keys = key.split('.');
        length = key.length;

        while ( keys.length - 1 ) {
            key = keys.shift();
            data[ key ] || (data[ key ] = {});
            data = data[ key ];
        }

        data[ key ] = value;
    }

    return {
        get: get,
        set: set
    };

});

define('views/postbox/default',[
    'sandbox',
    'app',
    'views/base',
    'utils/ajax',
    'utils/constants'
],

// definition
// ----------
function( $, App, BaseView, ajax, constants ) {

    return BaseView.extend({

        subviews: {},

        // @abstract
        template: function() {
            throw new Error('Method not implemented.');
        },

        events: {
            'click li[data-tab]': 'onToggleDropdown',
            'click button.joms-postbox-cancel': 'onCancel',
            'click button.joms-postbox-save': 'onPost',
            'click .joms-postbox-input': 'inputFocus'
        },

        initialize: function( options ) {
            if ( options && options.single )
                this.single = true;

            this.subflags = {};
            this.reset();
        },

        render: function() {
            var div = this.getTemplate();
            this.$el.replaceWith( div );
            this.setElement( div );

            this.$tabs = this.$('.joms-postbox-tab');
            this.$action = this.$('.joms-postbox-action').hide();
            this.$loading = this.$('.joms-postbox-loading').hide();
            this.$save = this.$('.joms-postbox-save').hide();

            return this;
        },

        show: function() {
            this.showInitialState();
            BaseView.prototype.show.apply( this );
        },

        showInitialState: function() {
            this.reset();
            this.$tabs.hide();
            this.$action.hide();
            this.$save.hide();
            this.trigger('show:initial');
        },

        showMainState: function() {
            this.$tabs.show();
            this.$action.show();
            this.trigger('show:main');
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            this.data = {};
            this.data.text = '';
            this.data.attachment = {};
            for ( var prop in this.subflags ) {
                this.subviews[ prop ].reset();
                this.subviews[ prop ].hide();
            }
        },

        value: function( noEncode ) {
            var attachment = $.extend({}, this.getStaticAttachment(), this.data.attachment );

            // DEBUGGING PURPOSE
            // if ( !noEncode ) {
            //  console.log( this.data.text );
            //  console.log( attachment );
            // }

            return [
                this.data.text,
                noEncode ? attachment : JSON.stringify( attachment )
            ];
        },

        inputFocus: function(e) {
            this.$(e.currentTarget)
                .find('.joms-textarea__wrapper')
                .find('.input.joms-textarea').focus();

            this.inputbox.trigger('focus');
        },

        // Data validation method, truthy return value will raise error.
        // Go to `this.onPost` to see how this method is used.
        validate: $.noop,

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onToggleDropdown: function( e ) {
            var elem = $( e.currentTarget );
            if ( elem.data('bypass') )
                return;

            var type = elem.data('tab');
            if ( !this.subviews[ type ] )
                return;

            if ( !this.subflags[ type ] )
                this.initSubview( type );

            if ( !this.subviews[ type ].isHidden() ) {
                this.subviews[ type ].hide();
                return;
            }

            for ( var prop in this.subflags )
                if ( prop !== type )
                    this.subviews[ prop ].hide();

            this.subviews[ type ].show();
        },

        onCancel: function() {
            if ( App.postbox && App.postbox.value )
                App.postbox.value = false;

            if ( !this.saving )
                this.showInitialState();
        },

        onPost: function() {
            if ( this.saving )
                return;

            var error = this.validate();
            if ( error ) {
                window.alert( error );
                return;
            }

            this.saving = true;
            this.$loading.show();

            var that = this;
            var data = this.value();

            // add current filters
            if ( window.joms_filter_params ) {
                data.push( JSON.stringify( window.joms_filter_params ) );
            }

            window.joms_postbox_posting = true;
            var self = this;
            ajax({
                fn: 'system,ajaxStreamAdd',
                data: data,
                success: $.bind( this.onPostSuccess, this ),
                complete: function() {
                    window.joms_postbox_posting = false;
                    that.$loading.hide();
                    that.saving = false;
                    that.showInitialState();

                    if (+window.joms_infinitescroll) {
                        $('.joms-stream__loadmore').find('a').hide()
                    }

                    self.initVideoPlayers();
                }
            });
        },

        initVideoPlayers: function () {
            var initialized = '.joms-js--initialized',
                cssVideos = '.joms-js--video',
                videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

            if ( !videos.length ) {
                return;
            }

            joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
            videos.on( 'click.joms-video', cssVideos + '-play', function() {
                var $el = $( this ).closest( cssVideos );
                joms.util.video.play( $el, $el.data() );
            });
        },

        onPostSuccess: function( response ) {
            var html = this.parseResponse( response ),
                stream;

            if ( html ) {
                stream = $('.joms-stream__wrapper').first();
                stream.html( html );

                // reset postbox to default
                $.trigger('postbox:status');

                // reinitialize activity stream
                if ( window.joms && joms.view && joms.view.streams ) {
                    joms.view.streams.start();
                    joms.view.misc.fixSVG();
                }
            }
        },

        // ---------------------------------------------------------------------
        // Lazy subview initialization.
        // ---------------------------------------------------------------------

        initSubview: function( type, options ) {
            var Type = type.replace( /^./, function( chr ){ return chr.toUpperCase(); });
            if ( !this.subflags[ type ] ) {
                this.subviews[ type ] = new this.subviews[ type ]( options );
                this.assign( this.getSubviewElement(), this.subviews[ type ] );
                this.listenTo( this.subviews[ type ], 'init', this[ 'on' + Type + 'Init' ] );
                this.listenTo( this.subviews[ type ], 'show', this[ 'on' + Type + 'Show' ] );
                this.listenTo( this.subviews[ type ], 'hide', this[ 'on' + Type + 'Hide' ] );
                this.listenTo( this.subviews[ type ], 'select', this[ 'on' + Type + 'Select' ] );
                this.listenTo( this.subviews[ type ], 'remove', this[ 'on' + Type + 'Remove' ] );
                this.subflags[ type ] = true;
            }
        },

        getSubviewElement: function() {
            var div = $('<div>').hide().appendTo( this.$el );
            return div;
        },

        // ---------------------------------------------------------------------
        // Ajax response parser.
        // ---------------------------------------------------------------------

        parseResponse: function( response ) {
            var elid = 'activity-stream-container',
                data, temp;

            if ( response.html ) {
                return response.html;
            }

            if ( response && response.length ) {
                for ( var i = 0; i < response.length; i++ ) {
                    if ( response[i][1] === '__throwError' || response[i][0] === 'al') {
                        temp = response[i][3];
                        window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
                    }
                    if ( !data && ( response[i][1] === elid) ) {
                        data = response[i][3];
                    }
                }
            }

            return data;
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({ juri: constants.get('juri') });
            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: '' }
            );

            return this.staticAttachment;
        }

    });

});
define('utils/image',[],function() {

    function Preloader( images, callback ) {
        this.images = [];
        this.loaded = [];
        this.processed = 0;
        this.callback = callback;

        if ( !images || !images.length ) {
            images = [];
        }

        // sanitize url
        var rUrl = /^(http:|https:)?\/\/([a-z0-9-]+\.)+[a-z]{2,18}\/.*$/i;
        for ( var i = 0; i < images.length; i++ ) {
            if ( images[i].match( rUrl ) )
                this.images.push( images[i] );
        }

    }

    Preloader.prototype.load = function() {
        if ( !this.images || !this.images.length ) {
            this.callback([]);
            return;
        }

        for ( var i = 0, img; i < this.images.length; i++ ) {
            img = new Image();
            img.onload = this.onload;
            img.onerror = this.onerror;
            img.onabort = this.onabort;
            img.preloader = this;
            img.src = img._src = this.images[i];
        }
    };

    Preloader.prototype.onload = function() {
        this.preloader.loaded.push( this._src );
        this.preloader.oncomplete();
    };

    Preloader.prototype.onerror = function() {
        this.preloader.oncomplete();
    };

    Preloader.prototype.onabort = function() {
        this.preloader.oncomplete();
    };

    Preloader.prototype.oncomplete = function() {
        var i, images = [];

        this.processed++;
        if ( this.processed >= this.images.length ) {
            for ( i = 0; i < this.images.length; i++ )
                if ( this.loaded.indexOf( this.images[ i ] ) > -1 )
                    images.push( this.images[ i ] );

            this.callback( images );
        }
    };

    function preload( images, callback ) {
        var pre = new Preloader( images, callback );
        pre.load();
    }

    return {
        preload: preload
    };

});
define('utils/language',[
    'sandbox'
],

// definition
// ----------
function( $ ) {

    var language = {};

    function get( key ) {
        if ( typeof key !== 'string' || !key.length )
            return;

        if ( joms && joms.language ) {
            $.extend( true, language, joms && joms.language );
            delete joms.language;
        }

        var data = language;

        key = key.split('.');
        while ( key.length ) {
            data = data[ key.shift() ];
        }

        return data;
    }

    return {
        get: get
    };

});

define('views/postbox/fetcher',[
    'sandbox',
    'views/base',
    'utils/ajax',
    'utils/image',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, ajax, image, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/postbox/fetcher' ],

        events: {
            'click .joms-fetched-close': 'onClose',
            'click .joms-fetched-field span': 'onFocus',
            'keyup .joms-fetched-field input': 'onKeyup',
            'keyup .joms-fetched-field textarea': 'onKeyup',
            'blur .joms-fetched-field input': 'onBlur',
            'blur .joms-fetched-field textarea': 'onBlur',
            'click .joms-fetched-previmg': 'prevImage',
            'click .joms-fetched-nextimg': 'nextImage'
        },

        initialize: function() {
            var lang = language.get('fetch') || {};
            this.titlePlaceholder = lang.title_hint || '';
            this.descPlaceholder = lang.description_hint || '';
        },

        fetch: function( text ) {
            var rUrl = /^(|.*\s)((https?:\/\/|www\.)([a-z0-9-]+\.)+[a-z]{2,18}(:\d+)?(\/.*)?)(\s.*|)$/i,
                isFetchable = text.match( rUrl );

            if ( this.fetching || !isFetchable )
                return;

            text = isFetchable[2];

            this.fetching = true;
            this.fetched = false;
            delete this.url;

            this.trigger('fetch:start');

            ajax({
                fn: 'system,ajaxGetFetchUrl',
                data: [ text ],
                success: $.bind( this.render, this ),
                complete: $.noop
            });

        },

        render: function( json ) {
            json || (json = {});

            this.fetched = true;
            this.url = json.url || '';

            var data = {
                title: json.title || '',
                titlePlaceholder: this.titlePlaceholder,
                description: json.description || '',
                descPlaceholder: this.descPlaceholder,
                image: ( json.image || [] ).concat( json['og:image'] || [] ),
                lang: {
                    prev: ( language.get('prev') || '' ).toLowerCase(),
                    next: ( language.get('next') || '' ).toLowerCase(),
                    cancel: ( language.get('cancel') || '' ).toLowerCase()
                }
            };

            // normalize url
            if ( !this.url.match( /^https?:\/\//i ) )
                this.url = 'http://' + this.url;

            // normalize images
            for ( var i = 0; i < data.image.length; i++ )
                if ( !data.image[i].match( /^(http:|https:)?\/\//i ) )
                    data.image[i] = '//' + data.image[i];

            // preload images
            image.preload( data.image, $.bind(function( images ) {
                data.image = images;
                this.$el.html( this.template( data ) );
                this.$images = this.$('.joms-fetched-images').find('img');
                this.$title = this.$('.joms-fetched-title').find('input');
                this.$description = this.$('.joms-fetched-description').find('textarea');
                this.fetching = false;
                this.trigger('fetch:done');
            }, this ) );
        },

        change: function( el ) {
            var input = $( el ),
                span = input.prev('span'),
                val = input.val().replace( /^\s+|\s+$/g, '' );

            if ( !val ) {
                val = input.parent().hasClass('joms-fetched-title') ?
                    this.titlePlaceholder :
                    this.descPlaceholder;
            }

            input.hide();
            span.text( val ).show();
        },

        remove: function() {
            delete this.url;
            BaseView.prototype.remove.apply( this );
            this.trigger('remove');
        },

        prevImage: function() {
            var currImg = this.$images.filter(':visible'),
                prevImg = currImg.prev();

            if ( prevImg.length ) {
                currImg.hide();
                prevImg.show();
            }
        },

        nextImage: function() {
            var currImg = this.$images.filter(':visible'),
                nextImg = currImg.next();

            if ( nextImg.length ) {
                currImg.hide();
                nextImg.show();
            }
        },

        value: function() {
            if ( this.fetching || !this.url )
                return;

            return [
                this.url,
                this.$images && this.$images.filter(':visible').attr('src'),
                this.$title && this.escapeValue( this.$title.val() ),
                this.$description && this.escapeValue( this.$description.val() )
            ];
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onClose: function() {
            this.remove();
        },

        onFocus: function( e ) {
            var span = $( e.currentTarget ),
                input = span.next('input,textarea');

            span.hide();
            input.show();
            setTimeout(function() {
                input[0].focus();
            }, 300 );
        },

        onKeyup: function( e ) {
            if ( e.keyCode === 13 ) {
                this.change( e.currentTarget );
            }
        },

        onBlur: function( e ) {
            this.change( e.currentTarget );
        },

        // ---------------------------------------------------------------------
        // Ajax response parser.
        // ---------------------------------------------------------------------

        parseResponse: function( resp ) {
            resp = resp && resp[2] && resp[2][3] && resp[2][3][0] || false;
            if ( !resp )
                return;

            var json;
            try {
                json = JSON.parse( resp );
            } catch ( e ) {}

            return json;
        },

        escapeValue: function( value ) {
            if ( typeof value !== 'string' )
                return value;

            return value
                .replace( /\\/g, '&#92;' )
                .replace( /\t/g, '\\t' )
                .replace( /\n/g, '\\n' )
                .replace( /&quot;/g,  '"' );
        }

    });

});
define('views/inputbox/base', [
    'sandbox',
    'views/base',
    'utils/constants'
],
// definition
// ----------
function ($, BaseView, constants) {

    return BaseView.extend({
        events: {
            'focus .input.joms-textarea': 'onFocus',
            'focus .input': 'onFocus',
            'keydown .input.joms-textarea': 'onKeydownProxy',
            'keydown .input': 'onKeydownProxy',

            'input .input.joms-textarea': 'onInput',
            'input .input': 'onInput',

            'paste .input.joms-textarea': 'onPaste',

            'blur .input.joms-textarea': 'onBlur',
            'blur .input': 'onBlur'
        },
        initialize: function (options) {
            if (!$.mobile) {
                this.onInput = this.onKeydown;
                this.onKeydownProxy = this.onKeydown;
                this.updateCharCounterProxy = this.updateCharCounter;
            }

            // flags
            options || (options = {});
            this.flags = {};
            this.flags.attachment = options.attachment;
            this.flags.charcount = options.charcount;

            this.listenTo($, 'postbox:tab:change', this.reset);
        },
        render: function () {
            this.$mirror = this.$('span.input');
            this.$textarea = this.$('.input.joms-textarea');
            if(this.$textarea.length){

            }else{
                this.$textarea = this.$('textarea.input');
            }
            this.placeholder = this.$textarea.attr('placeholder');
            if (this.flags.attachment)
                this.$attachment = $('<span class=attachment>').insertAfter(this.$mirror);

            this.reset();
        },
        set: function (value) {
            this.$textarea.val(value);
            this.flags.attachment && this.updateAttachment();
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        reset: function () {
            this.$textarea.val('');
            this.flags.attachment && this.updateAttachment();
            this.flags.charcount && this.updateCharCounterProxy();
            this.onKeydownProxy();
        },
        value: function () { 
            var el = this.$textarea[0],
                    value = el.joms_hidden ? el.joms_hidden.val() : el.value;

            return value
                    .replace(/\t/g, '\\t')
                    .replace(/%/g, '%25');
        },
        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onFocus: function () {
            this.trigger('focus');
        },
        onKeydown: function (e) { 
            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            var value = this.value();
            if (value.length >= this.maxchar) {
                if (this.isPrintable(e)) {
                    e.preventDefault();
                    return;
                }
            }

            var that = this;
            $.defer(function () {
                that.updateInput(e);
            });
        },
        onKeydownProxy: $.debounce(function (e) {
            this.onKeydown(e);
        }, 10),
        // Keydown event not always triggered on mobile browsers, so we listen both `keydown` and `input` events.
        // http://stackoverflow.com/questions/14194247/key-event-doesnt-trigger-in-firefox-on-android-when-word-suggestion-is-on
        onInput: function () { 
            this.onKeydownProxy();
        },
        onPaste: function () {
            var that = this;
            this.onKeydownProxy(function () {
                var textarea = that.$textarea[0],
                value = (textarea.tagName == "DIV")?that.$textarea.text():textarea.value ;
                that.trigger('paste', value, 13);
            });
        },
        onBlur: function () {
            var textarea = this.$textarea[0],
            value = (textarea.tagName == "DIV")?this.$textarea.text():textarea.value ;
            this.trigger('blur', value, 13);
        },
        // ---------------------------------------------------------------------
        // Input renderer.
        // ---------------------------------------------------------------------

        updateInput: function (e) {
         
            var keyCode = e && e.keyCode || false,
                    textarea = this.$textarea[0],
                    value = (textarea.tagName == "DIV")?this.value():textarea.value,
                    isEmpty = value.replace(/^\s+|\s+$/g, '') === '';
                   
                  
            if (isEmpty)
                textarea.value = value = '';

            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            if (value.length > this.maxchar) {
                 value = value.substr(0, this.maxchar);

                if(textarea.tagName == "DIV"){
                    this.$textarea.text(value);
                }else{
                    textarea.value = value;
                }
            }

            var mirrorValue = textarea.tagName === 'DIV' ? this.$textarea.html() : this.normalize(value);
            this.$mirror.html( mirrorValue + '.');
            
            if (this.$colorInner) {
                this.setColorStatusValue(value);
                if (value != '') {
                    this.$colorPlaceholder.hide();
                } else {
                    this.$colorPlaceholder.show();
                }

                this.reactColorList();
                if (this.colorfulSelected !== false) {
                    $(this.colorfulSelected).trigger('click');
                }
               
            }

            this.$textarea.css('height', this.$mirror.height());
            this.flags.charcount && this.updateCharCounterProxy();
            this.trigger('keydown', value, keyCode);
            if (typeof e === 'function')
                e();
        },
        updateAttachment: $.noop,
        updateCharCounterProxy: $.debounce(function () {
            this.updateCharCounter();
        }, 300),
        updateCharCounter: function () {
            if (typeof this.maxchar === 'undefined')
                this.maxchar = +constants.get('conf.statusmaxchar') || 0;

            if (!this.$charcount)
                this.$charcount = this.$('.charcount');

            if (!this.maxchar || this.maxchar <= 0) {
                this.$charcount.hide();
                return;
            }
          
            let len = 0;
            if(this.$textarea[0].tagName == "DIV"){

               len =  this.$textarea.text().length  ;
            }else{
                len = this.$textarea.val().length  ;
            }
            
            this.$charcount.html(this.maxchar - len).show();
        },
        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        isPrintable: function (e) {
            if (!e)
                return false;
            if ((e.crtlKey || e.metaKey) && !e.altKey && !e.shiftKey)
                return false;

            var code = e && e.keyCode;
            var printable =
                    (code === 13) || // return key
                    (code === 32) || // spacebar key
                    (code > 47 && code < 58) || // number keys
                    (code > 64 && code < 91) || // letter keys
                    (code > 95 && code < 112) || // numpad keys
                    (code > 185 && code < 193) || // ;=,-./` (in order)
                    (code > 218 && code < 223);   // [\]' (in order)

            return printable;
        },
        normalize: function (text) {
            return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
        },
        resetTextntags: function (textarea, value) {
            try {
                if(this.$textarea.hasClass('joms-postbox-video-description') ){
                    return ;
                }
                textarea = $(textarea);
                //if(textarea.hasClass("input-status")){ debugger;
                    textarea.removeData('joms-tagging');
                    textarea.val(value).jomsTagging();
                    textarea.data('joms-tagging').initialize();
              //  }
               
            } catch (e) {
            }
        }

    });

});
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

define('views/dropdown/base',[
    'sandbox',
    'views/base'
],

// definition
// ----------
function( $, BaseView ) {

    return BaseView.extend({

        initialize: function() {
            this.listenTo( $, 'click', this._onDocumentClick );
        },

        // hide on there is onclick event outside postbox
        _onDocumentClick: function( elem ) {
            if ( !elem.closest('.joms-postbox').length )
                this.hide();
        }

    });

});
define('views/dropdown/mood',[
    'sandbox',
    'views/dropdown/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/dropdown/mood' ],

        events: {
            'click li': 'onSelect',
            'click .joms-remove-button': 'onRemove'
        },

        render: function() {
            var items, hash, id, item, html, div, i;

            this.moods = constants.get('moods');

            items = [];
            hash = {};
            if ( this.moods && this.moods.length ) {
                for ( i = 0; i < this.moods.length; i++ ) {
                    id = this.moods[i].id;
                    item = [ id, this.moods[i].title ];
                    if ( this.moods[i].custom ) {
                        item[2] = this.moods[i].image;
                    }
                    items.push( item );
                    hash[ id ] = item;
                }
            }

            this.moods = hash;
            html = this.template({
                items: items,
                language: { status: language.get('status') || {} }
            });

            div = $( html ).hide();
            this.$el.replaceWith( div );
            this.setElement( div );
            this.$btnremove = this.$('.joms-remove-button').hide();

            return this;
        },

        select: function( mood ) {
            if ( this.moods[ mood ]) {
                this.$btnremove.show();
                this.trigger( 'select', this.mood = mood );
            }
        },

        value: function() {
            return this.mood;
        },

        reset: function() {
            this.mood = false;
            this.$btnremove.hide();
            this.trigger('reset');
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onSelect: function( e ) {
            var item = $( e.currentTarget ),
                mood = item.attr('data-mood');

            this.select( mood );
            this.hide();
        },

        onRemove: function() {
            this.mood = false;
            this.$btnremove.hide();
            this.trigger('remove');
        }

    });

});

define('views/dropdown/location',[
    'sandbox',
    'views/dropdown/base',
    'utils/language',
    'utils/constants'
],

// definition
// ----------
function( $, BaseView, language,constants ) {

    // placeholders language setting.
    var langs = language.get('geolocation') || {};

    return BaseView.extend({

        template: {
            div: joms.jst[ 'html/dropdown/location' ],
            list: joms.jst[ 'html/dropdown/location-list' ]
        },

        placeholders: {
            loading: langs.loading || '',
            loaded: langs.loaded || '',
            error: langs.error || ''
        },

        initialize: function() {
            BaseView.prototype.initialize.apply( this );

            this.location = false;
            this.locations = {};
            this.nearbyLocations = false;
            this.listenTo( $, 'postbox:tab:change', this.onRemove );
        },

        events: {
            'keyup input.joms-postbox-keyword': 'onKeyup',
            'click li': 'onSelect',
            'click button.joms-add-location': 'onAdd',
            'click button.joms-remove-location': 'onRemove'
        },

        render: function() {
            var settings = constants.get('settings') || {},
            conf = constants.get('conf') || {};
            
            this.map_type = conf.maps_api;
            this.$el.html( this.getTemplate() );
            this.$keyword = this.$('.joms-postbox-keyword');
            this.$list = this.$('.joms-postbox-locations');
            this.$map = this.$('.joms-postbox-map');
            this.$btnadd = this.$('.joms-add-location').hide();
            this.$btnremove = this.$('.joms-remove-location').hide();

            this.$keyword.attr( 'placeholder', this.placeholders.loading );

            var that = this;
            this.getService(function() {
                that.setInitialLocation = true;
                that.searchLocation();
            });

            return this;
        },

        show: function() {
            this.$el.show();
            this.trigger('show');
        },

        hide: function() {
            this.$el.hide();
            this.trigger('hide');
        },

        toggle: function() {
            var hidden = this.el.style.display === 'none';
            hidden ? this.show() : this.hide();
        },

        filter: function( e ) {
            return;

            var keyword = this.$keyword.val().replace( /^\s+|\s+$/, '' ),
                filtered = this.locations;

            if ( e && keyword ) {
                this.searchLocation( keyword );
                return;
            }

            if ( keyword.length ) {
                keyword = new RegExp( keyword, 'i' );
                filtered = [];
                for ( var i = 0, item; i < this.locations.length; i++ ) {
                    item = this.locations[i];
                    item = [ item.name, item.vicinity ].join(' ');
                    if ( item.match(keyword) )
                        filtered.push( this.locations[i] );
                }
            }

            this.draw( filtered );
        },

        draw: function( items ) {
            var html = this.template.list({
                language: { geolocation: language.get('geolocation') },
                items: items
            });

            this.filtered = items;

            this.$list.html( html ).css({
                height: '160px',
                overflowY: 'auto'
            });

            if ( this.setInitialLocation ) {
                this.setInitialLocation = false;
                this.select( 0 );
            }
        },

        select: function( index ) {
            var data = this.filtered[ index ];
            if ( data ) {
                this.location = data;
                this.$map.show();
                this.$keyword.val( data.name );
                this.map && this.marker && this.marker.setMap( this.map );
                this.showMap( data.latitude, data.longitude );
                this.$btnadd.show();
                this.$btnremove.hide();
            }
        },

        value: function() {
            var data = [];

            if ( this.location ) {
                data.push( this.location.name );
                data.push( this.location.latitude );
                data.push( this.location.longitude );
                return data;
            }

            return false;
        },

        reset: function() {
            this.location = false;
            this.marker && this.marker.setMap( null );
            this.$keyword.val('');
            this.$btnadd.hide();
            this.$btnremove.hide();
            this.trigger('reset');
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onKeyup: $.debounce(function() {
            this.service && this.searchLocation();
        }, 300 ),

        onSelect: function( e ) {
            var el = $( e.currentTarget ),
                index = el.attr('data-index');

            this.select( +index );
        },

        onAdd: function() {
            if ( this.location ) {
                this.trigger( 'select', this.location );
                this.$btnadd.hide();
                this.$btnremove.show();
                this.hide();
            }
        },

        onRemove: function() {
            this.reset();
            this.trigger('remove');
            this.hide();
            this.filter();
        },

        // ---------------------------------------------------------------------
        // Map functions.
        // ---------------------------------------------------------------------

        initMap: function( lat, lng ) {
            if(this.map_type == "openstreetmap"){
                 this.initOpenstreetMap(lat, lng);
            }else{
                this.initGoogleMap(lat, lng);
            }
        },

        initOpenstreetMap:function(lat, lng){
            var el = $('<div>').prependTo( this.$map );
            el.css( 'height', 110 );

            
            
            this.map = new L.Map(el[0],{attributionControl:false});
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 minZoom: 14,
                 center: [51.505, -0.09],
                 id: 'mapbox.streets'
            }).addTo(this.map);

            this.marker = L.marker();
            this.marker.setMap  = function(map){
                 
                if(map === null){
                    this.map.removeLayer(this);
                }else{
                    this.addTo(map); 
                    this.map = map ;
                }
                
            }
            
        },

        initGoogleMap:function(){

            var el = $('<div>').prependTo( this.$map );
            el.css( 'height', 110 );

            var options = {
                center: new google.maps.LatLng( 1, 1 ),
                zoom: 14,
                mapTypeId: google.maps.MapTypeId.ROADMAD,
                mapTypeControl: false,
                disableDefaultUI: true,
                draggable: false,
                scaleControl: false,
                scrollwheel: false,
                navigationControl: false,
                streetViewControl: false,
                disableDoubleClickZoom: true
            };

            this.map = new google.maps.Map( el[0], options );
            this.marker = new google.maps.Marker({ draggable: false, map: this.map });
            this.marker.setAnimation( null );
        },

        showMap: function( lat, lng ) {
            if(this.map_type == "openstreetmap"){
                 this.showOpenstreetMap(lat, lng);
            }else{
                this.showGoogleMap(lat, lng);
            }
          
        },
        showGoogleMap: function( lat, lng ) {
           
            var position = new google.maps.LatLng( lat, lng );
            this.marker.setPosition( position );
            this.map.panTo( position );
        },

        showOpenstreetMap: function( lat, lng ) {
           
            var position = L.latLng(lat, lng);
            this.marker.setLatLng( position );
           // this.map.fitBounds([lat, lng]);
            //this.map.panTo( position );
            this.map.setView(position, 11, { animation: true });  
            
            
        },

        // ---------------------------------------------------------------------
        // Location detection.
        // ---------------------------------------------------------------------

        getService: function( callback ) {
            var that;

            if ( typeof callback !== 'function' ) {
                callback = function(){};
            }

            if ( this.service ) {
                callback.call( this, this.service );
            } else {
                that = this;
                joms.map.type = this.map_type ;
                joms.map.execute(function() {
                    that.initMap();
                    if(that.map_type == "openstreetmap"){
                        that.service = openstreetmap.PlacesService ;
                    }else{
                        that.service = new google.maps.places.PlacesService( that.map );
                    }
                    
                    callback.call( that, that.service );
                });
            }
        },

        searchLocation: function() {
            var query = this.$keyword.val().replace(/^\s+|\s+$/g, '');

            this.$keyword.attr( 'placeholder', this.placeholders.loading );
            this.searchToken = (this.searchToken || 0) + 1;
            this[ query ? 'searchLocationQuery' : 'searchLocationNearby' ]({
                query: query,
                token: this.searchToken,
                callback: this.searchLocationCallback
            });
        },

        searchLocationQuery: function( params ) {
            var that;

            if ( this.locations[ params.query ] ) {
                params.callback.apply( this, [ this.locations[ params.query ], null, params ]);
                return;
            }

            that = this;
            this.service.textSearch({ query: params.query }, function( results, status ) {
                if ( status !== "OK" ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                if ( !$.isArray( results ) ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                for ( var i = 0, locs = [], loc; i < results.length; i++ ) {
                    loc = results[i];
                    locs.push({
                        name: loc.name,
                        latitude: loc.geometry.location.lat(),
                        longitude: loc.geometry.location.lng(),
                        vicinity: loc.formatted_address
                    });
                }

                that.locations[ params.query ] = locs;
                params.callback.apply( that, [ that.locations[ params.query ], null, params ] );
            });
        },

        searchLocationNearby: function( params ) {
            var that = this;
            navigator.geolocation.getCurrentPosition(
                function( position ) { that.detectLocationSuccess( position, params ) },
                function() { that.detectLocationFallback( params ) },
                { timeout: 10000 }
            );
        },

        searchLocationCallback: function( results, error, params ) {
            if ( this.searchToken !== params.token ) {
                return;
            }

            this.$keyword.attr( 'placeholder', this.placeholders.loaded );
            this.draw( results );
        },

        detectLocationSuccess: function( position, params ) {
            var coords = position && position.coords || {},
                lat = coords.latitude,
                lng = coords.longitude;

            if ( lat && lng ) {
                this.detectLocationNearby( lat, lng, params );
            } else {
                params.callback.apply( this, [ null, this.placeholders.error, params ] );
            }
        },

        // If HTML5 geolocation failed to detect my current location, attempt to use IP-based geolocation.
        detectLocationFallback: function ( params ) {
            var success = false,
                that = this;

            $.ajax({
                url: '//freegeoip.net/json/',
                dataType: 'jsonp',
                success: function( json ) {
                    var lat = json.latitude,
                        lng = json.longitude;

                    if ( lat && lng ) {
                        success = true;
                        that.detectLocationNearby( lat, lng, params );
                    }
                },
                complete: function() {
                    success || params.callback.apply( that, [ null, that.placeholders.error, params ] );
                }
            });
        },

        detectLocationNearby: function( lat, lng, params ) {
            var position, request, that;

            if ( this.nearbyLocations ) {
                params.callback.apply( this, [ this.nearbyLocations, null, params ]);
                return;
            }
            if(this.map_type == "openstreetmap"){

                position = [ lat, lng ];

            }else{
                
                position = new google.maps.LatLng( lat, lng );
            }
            
            request = {
                location: position,
                types: [ 'establishment' ],
                rankBy: 1 // google.maps.places.RankBy.DISTANCE
            };
            
            that = this;
            this.service.nearbySearch( request, function( results, status ) {
                if ( status !== "OK" ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                if ( !$.isArray( results ) ) {
                    params.callback.apply( that, [ null, that.placeholders.error, params ] );
                    return;
                }

                for ( var i = 0, locs = [], loc; i < results.length; i++ ) {
                    loc = results[i];
                    
                    locs.push({
                        name: loc.name,
                        latitude: loc.geometry.location.lat(),
                        longitude: loc.geometry.location.lng(),
                        vicinity: loc.vicinity
                    });
                }

                that.nearbyLocations = locs;
                params.callback.apply( that, [ that.nearbyLocations, null, params ] );
            });
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template.div({
                language: {
                    geolocation: language.get('geolocation') || {}
                }
            });

            return html;
        }

    });

});

define('views/dropdown/privacy',[
    'sandbox',
    'views/dropdown/base',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/dropdown/privacy' ],

        events: { 'click li': 'select' },

        privacies: {
            'public': [ 10, 'earth' ],
            'site_members': [ 20, 'users' ],
            'friends': [ 30, 'user' ],
            'me': [ 40, 'lock' ]
        },

        privmaps: {
            'public': '10',
            'site_members': '20',
            'friends': '30',
            'me': '40',
            '0': 'public',
            '10': 'public',
            '20': 'site_members',
            '30': 'friends',
            '40': 'me'
        },

        initialize: function( options ) {
            BaseView.prototype.initialize.apply( this );

            this.privkeys = $.keys( this.privacies );
            if ( options && options.privacylist && options.privacylist.length )
                this.privkeys = $.intersection( this.privkeys, options.privacylist );

            var langs = language.get('privacy') || {};
            for ( var prop in this.privacies ) {
                this.privacies[ prop ][ 2 ] = langs[ prop ] || prop;
                this.privacies[ prop ][ 3 ] = langs[ prop + '_desc' ] || prop;
            }

            // set default privacy
            this.defaultPrivacy = this.privkeys[0];
            if ( typeof options.defaultPrivacy !== 'undefined' ) {
                options.defaultPrivacy = '' + options.defaultPrivacy;
                if ( options.defaultPrivacy.match(/^\d+$/) ) {
                    options.defaultPrivacy = this.privmaps[ options.defaultPrivacy ] || this.defaultPrivacy;
                }
                if ( this.privkeys.indexOf( options.defaultPrivacy ) >= 0 ) {
                    this.defaultPrivacy = options.defaultPrivacy;
                }
            }
        },

        render: function() {
            var items = [];
            for ( var i = 0, priv; i < this.privkeys.length; i++ ) {
                priv = this.privkeys[ i ];
                items[ i ] = [
                    priv,
                    this.privacies[ priv ][ 1 ],
                    this.privacies[ priv ][ 2 ],
                    this.privacies[ priv ][ 3 ]
                ];
            }

            this.$el.html( this.template({ items: items }) );
            this.setPrivacy( this.defaultPrivacy );
            return this;
        },

        select: function( e ) {
            var item = $( e.currentTarget ),
                priv = item.attr('data-priv');

            this.setPrivacy( priv );
            this.hide();
        },

        setPrivacy: function( priv ) {
            var data = {};

            if ( this.privkeys.indexOf( priv ) >= 0 ) {
                this.privacy = this.privacies[ priv ][ 0 ];
                data.icon = this.privacies[ priv ][ 1 ];
                data.label = this.privacies[ priv ][ 2 ].toLowerCase();
                this.trigger( 'select', data );
            }
        },

        value: function() {
            return this.privacy;
        },

        reset: function() {
            this.setPrivacy( this.defaultPrivacy );
        }

    });

});
define('views/postbox/status',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/fetcher',
    'views/inputbox/status',
    'views/dropdown/mood',
    'views/dropdown/location',
    'views/dropdown/privacy',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    FetcherView,
    InputboxView,
    MoodView,
    LocationView,
    PrivacyView,
    constants,
    language
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            location: LocationView,
            privacy: PrivacyView
        },

        template: _.template(
            '<div class=joms-postbox-status>\
                <div class=joms-postbox-fetched></div>\
                <div class=joms-postbox-inputbox></div>\
                <nav class="joms-postbox-tab selected">\
                    <ul class="joms-list inline">\
                        <li data-tab=mood><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-happy"></use></svg></li>\
                        <li data-tab=location><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-location"></use></svg></li>\
                        <li data-tab=privacy><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-earth"></use></svg> </li>\
                        <% if (enablephoto) { %>\
                        <li data-tab=photo data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-camera"></use></svg></li>\
                        <% } %>\
                        <% if (enablevideo) { %>\
                        <li data-tab=video data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-play"></use></svg></li>\
                        <% } %>\
                        <% if (enablefile) { %>\
                        <li data-tab=file data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip"></use></svg></li>\
                        <% } %>\
                        <% if (enablepoll) { %>\
                        <li data-tab=poll data-bypass=1><svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-list"></use></svg></li>\
                        <% } %>\
                    </ul>\
                    <div class=joms-postbox-action>\
                        <button class=joms-postbox-cancel><%= language.postbox.cancel_button %></button>\
                        <button class=joms-postbox-save><%= language.postbox.post_button %></button>\
                    </div>\
                    <div class=joms-postbox-loading style="display:none;">\
                        <img src="<%= juri.root %>components/com_community/assets/ajax-loader.gif" alt="loader">\
                    <div>\
                </nav>\
            </div>'            
        ),

        getTemplate: function() {
            var settings = constants.get('settings') || {},
                conf = constants.get('conf') || {},
                enablephoto = true,
                enablevideo = true,
                enablefile = true,
                enablepoll = true;

            if ( settings.isProfile || settings.isGroup || settings.isEvent || settings.isPage ) {
                conf.enablephotos || (enablephoto = false);
                conf.enablevideos || (enablevideo = false);
                conf.enablefiles || (enablefile = false);
                conf.enablepolls || (enablepoll = false);
            }

            var html = this.template({
                juri: constants.get('juri'),
                enablephoto: enablephoto,
                enablevideo: enablevideo,
                enablefile: enablefile,
                enablepoll: enablepoll,
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {}
                }
            });

            return $( html ).hide();
        },

        events: $.extend({}, DefaultView.prototype.events, {
            'click li[data-tab=photo]': 'onAddPhoto',
            'click li[data-tab=video]': 'onAddVideo',
            'click li[data-tab=file]': 'onAddFile',
            'click li[data-tab=poll]': 'onAddPoll'
        }),

        initialize: function() {
            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            this.enableLocation = +constants.get('conf.enablelocation');
            if ( !this.enableLocation )
                this.subviews = $.omit( this.subviews, 'location' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$fetcher = this.$('.joms-postbox-fetched');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tablocation = this.$tabs.find('[data-tab=location]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            if ( !this.enableMood )
                this.$tabmood.remove();

            if ( !this.enableLocation )
                this.$tablocation.remove();

            if ( this.inheritPrivacy ) {
                if ( this.$tabprivacy.next().length )
                    this.$tabprivacy.remove();
                else
                    this.$tabprivacy.css({ visibility: 'hidden' });
            }

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true, status: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );
            this.listenTo( this.inputbox, 'keydown', this.onInputUpdate );
            this.listenTo( this.inputbox, 'paste', this.onInputUpdate );
            this.listenTo( this.inputbox, 'change:type', this.onInputChangeType );

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            if ( this.single )
                this.listenTo( $, 'click', this.onDocumentClick );

            return this;
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.fetcher && this.fetcher.remove();
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            this.data.attachment = {};

            if (this.inputbox.colorful) {
                this.data.attachment.colorful = true;
                this.data.attachment.bgid = this.inputbox.bgid;
            }

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            if ( this.fetcher )
                this.data.attachment.fetch = this.fetcher.value();

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: $.noop,

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onInputChangeType: function(type) {
            if (type) {
                this.$tabmood.attr('style', 'display: none !important');
                this.$tablocation.attr('style', 'display: none !important');
            } else {
                this.$tabmood.attr('style', '');
                this.$tablocation.attr('style', '');
            }
        },

        onInputFocus: function() {
            this.showMainState();
        },

        onInputUpdate: function( text, key ) {
            var div;

            text = text || '';
            this.togglePostButton( text );

            if ( key === 32 || key === 13 ) {
                this.fetch( text );
            } else {
                this.fetchProxy( text );
            }
        },

        // ---------------------------------------------------------------------
        // Fetching event handler.
        // ---------------------------------------------------------------------

        fetchProxy: $.debounce(function( text ) {
            this.fetch( text );
        }, 1000 ),

        fetch: function( text ) {
            var div;

            if ( this.fetcher && (this.fetcher.fetching || this.fetcher.fetched) )
                return;

            if ( this.fetcher )
                this.fetcher.remove();

            div = $('<div>').appendTo( this.$fetcher );
            this.fetcher = new FetcherView();
            this.fetcher.setElement( div );
            this.listenTo( this.fetcher, 'fetch:start', this.onFetchStart );
            this.listenTo( this.fetcher, 'fetch:done', this.onFetchDone );
            this.listenTo( this.fetcher, 'remove', this.onFetchRemove );
            this.fetcher.fetch( text.replace( /^\s+|\s+$/g, '' ) );
        },

        onFetchStart: function() {
            this.saving = true;
            this.$loading.show();
        },

        onFetchDone: function() {
            this.$loading.hide();
            this.saving = false;
        },

        onFetchRemove: function() {
            this.fetcher = false;
        },

        onDocumentClick: function( elem ) {
            if ( elem.closest('.joms-postbox').length )
                return;

            var text = this.inputbox.value();
            text = text.replace( /^\s+|\s+$/g, '' );
            if ( !text )
                this.showInitialState();
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
            this.togglePostButton();
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
            this.togglePostButton();
        },

        onLocationSelect: function( location ) {
            this.inputbox.updateAttachment( null, location );
            this.togglePostButton();
        },

        onLocationRemove: function() {
            this.inputbox.updateAttachment( null, false );
            this.togglePostButton();
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Add photo/video event handlers.
        // ---------------------------------------------------------------------

        onAddPhoto: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:photo' );
        },

        onAddVideo: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:video' );
        },

        onAddFile: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:file' );
        },

        onAddPoll: function() {
            App.postbox || (App.postbox = {});
            App.postbox.value = this.value( true );
            App.postbox.value[0] = App.postbox.value[0].replace( /\\n/g, '\n' );
            $.trigger( 'postbox:poll' );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'message' }
            );

            return this.staticAttachment;
        },

        togglePostButton: function( text ) {
            var enabled = false;

            if ( text )
                enabled = true;

            if ( !enabled && this.subflags.mood )
                enabled = this.subviews.mood.value();

            if ( !enabled && this.subflags.location )
                enabled = this.subviews.location.value();

            this.$save[ enabled ? 'show' : 'hide' ]();
        }

    });

});
define('views/widget/select',[
    'sandbox',
    'views/base',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/widget/select' ],

        events: {
            'click span': 'toggle',
            'click svg': 'toggle',
            'click li': 'onSelect'
        },

        initialize: function( options ) {
            this.options = options.options || [];
            if ( +options.width )
                this.width = +options.width + 'px';
        },

        render: function() {
            var data = {};
            data.options = this.options;
            data.width = this.width || false;
            data.placeholder = language.get('select_category');

            this.$el.html( this.template( data ) );
            this.$span = this.$('span');
            this.$ul = this.$('ul');

            if ( data.options ) {
                if ( data.options.length > 6 ) {
                    this.$ul.css({ height: '175px', overflowY: 'auto' });
                }
            }
        },

        select: function( value, text ) {
            this.$span.html( text );
            this.$span.data( 'value', value );
            this.trigger( 'select', value, text );
        },

        toggle: function() {
            this.$ul.toggle();
        },

        value: function() {
            return this.$span.data('value');
        },

        reset: function() {
            this.$ul && this.$ul.hide();
            if ( this.options && this.options.length ) {
                this.$span.html( this.options[0][1] );
                this.$span.data( 'value', this.options[0][0] );
            }
        },

        onSelect: function( e ) {
            var el = $( e.currentTarget ),
                value = el.data('value'),
                text = el.html();

            this.select( value, text );
            this.toggle();
        }

    });

});
define('views/widget/select-album',[
    'sandbox',
    'views/widget/select',
    'utils/language'
],

// definition
// ----------
function( $, SelectWidget, language ) {

    return SelectWidget.extend({

        template: joms.jst[ 'html/widget/select-album' ],

        render: function() {
            var data = {};
            data.options = this.options;
            data.width = this.width || false;
            data.placeholder = language.get('select_category');

            this.$el.html( this.template( data ) );
            this.$span = this.$('span');
            this.$ul = this.$('ul');

            if ( data.options ) {
                if ( data.options.length > 3 ) {
                    this.$ul.css({ height: '160px', overflowY: 'auto' });
                }
                if ( data.options[0] )
                    this.select( data.options[0][0], data.options[0][1] );
            }
        },

        onSelect: function( e ) {
            var el = $( e.currentTarget ),
                value = el.data('value'),
                text = el.find('p').html();

            this.select( value, text );
            this.toggle();
        }

    });

});
define('views/postbox/photo-preview',[
    'sandbox',
    'app',
    'views/base',
    'views/widget/select-album',
    'utils/ajax',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, App, BaseView, SelectWidget, ajax, constants, language ) {

    return BaseView.extend({

        templates: {
            div: joms.jst[ 'html/postbox/photo-preview' ],
            img: joms.jst[ 'html/postbox/photo-item' ]
        },

        events: {
            'click .joms-postbox-photo-remove': 'onRemove'
        },

        render: function() {
            this.$el.html( this.templates.div() );
            this.$list = this.$('ul');
            this.$form = this.$('div.joms-postbox-photo-form');
        },

        add: function( file ) {
            this.$list.append( this.templates.img({
                id: this.getFileId( file ),
                src: App.legacyUrl + '/assets/photo-upload-placeholder.png'
            }) );

            var settings = constants.get('settings') || {};
            if ( !settings.isMyProfile )
                return;

            if ( this.select )
                return;

            var albums = constants.get('album'),
                privs = language.get('privacy'),
                options = [];

            var privmap = {
                '0': 'public',
                '10': 'public',
                '20': 'site_members',
                '30': 'friends',
                '40': 'me'
            };

            var icons = {
                '0': 'earth',
                '10': 'public',
                '20': 'users',
                '30': 'user',
                '40': 'lock'
            };

            if ( !(albums && albums.length) )
                return;

            this.albummap = {};
            for ( var i = 0, album, permission; i < albums.length; i++ ) {
                album = albums[i];
                permission = '' + album.permissions;
                this.albummap[ '' + album.id ] = permission;
                album = [ album.id, album.name, privs[ privmap[permission] || '0' ], icons[permission || '0'] ];
                options[ +album['default'] ? 'unshift' : 'push' ]( album );
            }

            this.select = new SelectWidget({ options: options });

            var div = $('<div class="joms-postbox-select-album joms-select" style="padding:3px 0">').insertAfter( this.$form );
            this.assign( div, this.select );
        },

        value: function() {
            var settings = constants.get('settings') || {},
                album_id, privacy, values;

            values = {
                id: this.pics || []
            };

            if ( this.select && settings.isMyProfile ) {
                values.album_id = '' + this.select.value();
                values.privacy = this.albummap[ album_id ];
            }

            return values;
        },

        updateProgress: function( file ) {
            var id = this.getFileId( file ),
                elem = this.$list.find( '#'+ id ).find('.joms-postbox-photo-progress'),
                percent;

            if ( elem && elem.length ) {
                percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
                elem.stop().animate({ width: percent + '%' });
            }
        },

        setImage: function( file, json ) {
            json || (json = {});

            var elem = this.$list.find( '#' + this.getFileId(file) ),
                src = constants.get('juri.base') + json.thumbnail,
                id = json.id;

            elem.find('img').attr( 'src', src ).data( 'id', id );
            elem.find('img').attr( 'style', 'visibility:visible');
            elem.find('.joms-postbox-photo-action').show();
            elem.addClass('joms-postbox-photo-loaded');
            elem.find('.joms-postbox-photo-progressbar').remove();

            this.pics || (this.pics = []);
            this.pics.push( '' + id );

            this.trigger( 'update', this.pics.length );
        },

        removeFailed: function() {
            this.$list.find('.joms-postbox-photo-item')
                .not('.joms-postbox-photo-loaded')
                .remove();

            this.trigger( 'update', this.pics && this.pics.length || 0 );
        },

        // ---------------------------------------------------------------------
        // Thumbnail event handlers.
        // ---------------------------------------------------------------------

        onRemove: function( e ) {
            var li = $( e.target ).closest('li'),
                id = li.find('img').data('id'),
                num;

            li.remove();
            this.pics = $.without( this.pics, '' + id );
            num = this.pics.length;

            if ( num <= 0 ) {
                this.select && this.select.remove();
            }

            this.ajaxRemove([ id ]);
            this.trigger( 'update', num );
        },

        remove: function() {
            this.pics && this.pics.length && this.ajaxRemove( this.pics );
            return BaseView.prototype.remove.apply( this, arguments );
        },

        ajaxRemove: function( pics ) {
            var params = {};
            params.option = 'community';
            params.no_html = 1;
            params.task = 'azrul_ajax';
            params.func = 'system,ajaxDeleteTempImage';
            params[ window.jax_token_var ] = 1;

            if ( pics && pics.length )
                params[ 'arg2[]' ] = pics;

            $.ajax({
                url: window.jax_live_site,
                type: 'post',
                dataType: 'json',
                data: params
            });
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getFileId: function( file ) {
            return 'postbox-preview-' + file.id;
        },

        getNumPics: function() {
            return this.$list.find('li').length;
        }

    });

});
define('views/postbox/gif-preview',[
    'views/postbox/photo-preview'
],

// definition
// ----------
function( PreviewView ) {

    return PreviewView.extend({

        render: function() {
            PreviewView.prototype.render.apply( this, arguments );
            this.$el.addClass('joms-postbox-gif-preview');
        },

        add: function() {
            PreviewView.prototype.add.apply( this, arguments );
            this.$el.find('.joms-postbox-select-album').hide();
        },

        setImage: function( file, json ) {
            json || (json = {});

            var elem = this.$list.find( '#' + this.getFileId(file) ),
                src = json.image,
                id = json.id;

            elem.find('img').attr( 'src', src ).data( 'id', id );
            elem.find('img').attr( 'style', 'visibility:visible');
            elem.find('.joms-postbox-photo-action').show();
            elem.addClass('joms-postbox-photo-loaded');
            elem.find('.joms-postbox-photo-progressbar').remove();

            this.pics || (this.pics = []);
            this.pics.push( '' + id );

            this.trigger( 'update', this.pics.length );
        }

    });

});
define('views/inputbox/photo',[
    'sandbox',
    'views/inputbox/status',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, constants, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/base' ],

        initialize: function() {
            var hash, item, id, i;

            InputboxView.prototype.initialize.apply( this, arguments );
            this.hint = {
                single: language.get('status.photo_hint') || '',
                multiple: language.get('status.photos_hint') || ''
            };

            this.moods = constants.get('moods');
            hash = {};
            if ( this.moods && this.moods.length ) {
                for ( i = 0; i < this.moods.length; i++ ) {
                    id = this.moods[i].id;
                    item = [ id, this.moods[i].description ];
                    if ( this.moods[i].custom ) {
                        item[2] = this.moods[i].image;
                    }
                    hash[ id ] = item;
                }
            }
            this.moods = hash;
        },

        reset: function() {
            InputboxView.prototype.reset.apply( this, arguments );
            this.single();
        },

        single: function() {
            this.hint.current = this.hint.single;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        multiple: function() {
            this.hint.current = this.hint.multiple;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        updateAttachment: function( mood ) {
            var attachment = [];

            this.mood = mood || mood === false ? mood : this.mood;

            if ( this.mood && this.moods[this.mood] ) {
                if ( typeof this.moods[this.mood][2] !== 'undefined' ) {
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

            if ( !attachment.length ) {
                this.$attachment.html('');
                this.$textarea.attr( 'placeholder', this.hint.current );
                return;
            }

            this.$attachment.html( ' &nbsp;&mdash; ' + attachment.join(' ' + language.get('and') + ' ') + '.' );
            this.$textarea.removeAttr('placeholder');
        },

        getTemplate: function() {
            var html = this.template({ placeholder: this.hint.current = this.hint.single });
            return $( html );
        }

    });

});
define('utils/uploader',[
    'sandbox',
    'app'
],

// definition
// ----------
function( $, App ) {

    var defaults = {
        runtimes: 'html5,html4',
        url: 'index.php'
    };

    function Uploader( options ) {
        this.queue = [];
        this.ready = false;

        if ( window.plupload ) {
            this.ready = true;
            this.create( options );
            return;
        }

        var that = this;
        joms.$LAB.script( App.legacyUrl + 'assets/vendors/plupload.min.js' ).wait(function() {
            that.ready = true;
            that.create( options );
            that.execQueue();
        });
    }

    Uploader.prototype.create = function( options ) {
        var btn = this.$button = options.browse_button,
            id = false,
            par;

        // Container.
        if ( typeof options.container === 'string' ) {
            par = $( '#' + options.container );
            if ( !par.length ) {
                par = $( '<div id="' + options.container + '" style="width:1px; height:1px; overflow:hidden">' ).appendTo( document.body );
            }
        } else {
            par = $( options.container );
            if ( id = par.attr('id') ) {
                options.container = id;
            } else {
                options.container = id = $.uniqueId('uploader_parent_');
                par.attr( 'id', id );
            }
        }

        // Upload button.
        if ( typeof btn === 'string' ) {
            this.$button = $( '#' + btn );
            if ( !this.$button.length ) {
                this.$button = $( '<button id="' + btn + '">' ).appendTo( par );
            }
        } else if ( id = btn.attr('id') ) {
            this.$button = $( document.getElementById(id) );
        } else {
            options.browse_button = id = $.uniqueId('uploader_');
            btn.attr( 'id', id );
        }

        this.onProgress = options.onProgress || $.noop;
        this.onAdded = options.onAdded || $.noop;

        options = $.extend({}, defaults, options || {});
        this.uploader = new plupload.Uploader( options );
    };

    Uploader.prototype.init = function() {
        if ( !this.ready ) {
            this.queue.push([ 'init', this, arguments ]);
            return;
        }

        this.uploader.init();
        this.uploader.bind( 'FilesAdded', this.onAdded );
        this.uploader.bind( 'Error', this.onError );
        this.uploader.bind( 'BeforeUpload', this.onBeforeUpload );
        this.uploader.bind( 'UploadProgress', this.onProgress );
        this.uploader.bind( 'FileUploaded', this.onUploaded );
        this.uploader.bind( 'UploadComplete', this.onComplete );
    };

    Uploader.prototype.open = function() {
        if ( !this.ready ) {
            this.queue.push([ 'open', this, arguments ]);
            return;
        }

        this.$button.click();
    };

    Uploader.prototype.reset = function() {
        if ( !this.ready ) {
            this.queue.push([ 'reset', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.remove = function() {
        if ( !this.ready ) {
            this.queue.push([ 'remove', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.params = function( data ) {
        this.uploader.settings.multipart_params = data;
    };

    Uploader.prototype.upload = function() {
        if ( !this.ready ) {
            this.queue.push([ 'upload', this, arguments ]);
            return;
        }
    };

    Uploader.prototype.execQueue = function() {
        var cmd;
        while ( this.queue.length ) {
            cmd = this.queue.shift();
            this[ cmd[0] ].apply( cmd[1], cmd[2] );
        }
    };

    // -------------------------------------------------------------------------

    Uploader.prototype.onAdded = $.noop;
    Uploader.prototype.onError = $.noop;
    Uploader.prototype.onBeforeUpload = $.noop;
    Uploader.prototype.onProgress = $.noop;
    Uploader.prototype.onUploaded = $.noop;
    Uploader.prototype.onComplete = $.noop;

    return Uploader;

});

define('views/postbox/photo',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/photo-preview',
    'views/postbox/gif-preview',
    'views/inputbox/photo',
    'views/dropdown/mood',
    'views/widget/select',
    'utils/constants',
    'utils/language',
    'utils/uploader'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    PreviewView,
    GifPreviewView,
    InputboxView,
    MoodView,
    SelectWidget,
    constants,
    language,
    Uploader
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView
        },

        template: joms.jst[ 'html/postbox/photo' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-photo-upload': 'onPhotoAdd',
            'click li[data-tab=upload]': 'onPhotoAdd',
            'click .joms-postbox-gif-upload': 'onGifAdd',
            'dragenter': 'onDragEnter',
            'dragleave #joms-postbox-photo--droparea': 'onDragLeave',
            'drop #joms-postbox-photo--droparea': 'onFileDrop'
        }),

        initialize: function() {
            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            this.enableGif = +constants.get('conf.enablephotosgif');
            
            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );
            this.$el.find(".joms-icon--emoticon").data('editor', this);
            this.$initial = this.$('.joms-postbox-inner-panel');
            this.$main = this.$('.joms-postbox-photo');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$preview = this.$('.joms-postbox-preview');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');

            this.$dropArea = this.$el.find('#joms-postbox-photo--droparea');

            if ( !this.enableMood )
                this.$tabmood.remove();

            this.$uploader = this.$('#joms-postbox-photo-upload');
            this.$uploaderParent = this.$uploader.parent();

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            // initialize uploader
            var url = joms.BASE_URL + 'index.php?option=com_community&view=photos&task=ajaxPreview',
                settings = constants.get('settings') || {};

            if ( settings.isGroup )
                url += '&no_html=1&tmpl=component&groupid=' + ( constants.get('groupid') || '' );

            if ( settings.isEvent )
                url += '&no_html=1&tmpl=component&eventid=' + ( constants.get('eventid') || '' );

            if ( settings.isPage ) 
                url += '&no_html=1&tmpl=component&pageid=' + ( constants.get('pageid') || '' ); 

            if ( $.ie ) {
                this.$uploader.appendTo( document.body );
                this.$uploader.show();
            }

            this.maxFileSize = +constants.get('conf.maxuploadsize') || 0;

            this.uploading = [];

            var upConfig = {
                container: 'joms-postbox-photo-upload',
                drop_element: 'joms-postbox-photo--droparea',
                browse_button: 'joms-postbox-photo-upload-btn',
                url: url,
                filters: [{ title: 'Image files', extensions: 'jpg,jpeg,png,gif' }],
                max_file_size: this.maxFileSize + 'mb'
            };

            // resizing on mobile cause errors on android stock browser!
            if ( !$.mobile )
                upConfig.resize = { width: 2100, height: 2100, quality: 90 };

            this.uploader = new Uploader( upConfig );
            this.uploader.onAdded = $.bind( this.onPhotoAdded, this );
            this.uploader.onError = $.bind( this.onPhotoError, this );
            this.uploader.onProgress = $.bind( this.onPhotoProgress, this );
            this.uploader.onUploaded = $.bind( this.onPhotoUploaded, this );
            this.uploader.init();

            if ( $.ie ) {
                this.$uploader.hide();
                this.$uploader.appendTo( this.$uploaderParent );
            }

            if ( this.enableGif ) {
                var gifConfig = {
                    container: 'joms-postbox-gif-upload',
                    browse_button: 'joms-postbox-gif-upload-btn',
                    url: url + '&gifanimation=1',
                    filters: [{ title: 'Image files', extensions: 'gif' }],
                    max_file_size: this.maxFileSize + 'mb',
                    multi_selection: false
                };

                this.gifuploader = new Uploader( gifConfig );
                this.gifuploader.onAdded = $.bind( this.onGifAdded, this );
                this.gifuploader.onError = $.bind( this.onGifError, this );
                this.gifuploader.onProgress = $.bind( this.onGifProgress, this );
                this.gifuploader.onUploaded = $.bind( this.onGifUploaded, this );
                this.gifuploader.init();
            }

            return this;
        },

        showInitialState: function() {
            this.$main.hide();
            this.$initial.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix( true );
            this.inputbox && this.inputbox.single();
            this.preview && this.preview.remove();
            this.preview = false;
            this.gifPreview && this.gifPreview.remove();
            this.gifPreview = false;
            this.showMoreButton();
            DefaultView.prototype.showInitialState.apply( this );
        },

        showMainState: function() {
            DefaultView.prototype.showMainState.apply( this );
            this.$action.hide();
            this.$initial.hide();
            this.$main.show();
            this.$save.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix();

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        showMoreButton: function() {
            this.$tabupload.removeClass('hidden invisible');
        },

        hideMoreButton: function() {
            this.$tabupload.addClass( this.subviews.mood ? 'hidden' : 'invisible' );
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.preview && this.preview.remove();
            this.preview = false;
            this.gifPreview && this.gifPreview.remove();
            this.gifPreview = false;
            this.uploading = [];
        },

        value: function() { 
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            if ( this.preview ) {
                $.extend( this.data.attachment, this.preview.value() );
            } else if ( this.gifPreview ) {
                $.extend( this.data.attachment, this.gifPreview.value() );
            }


            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {};

            if ( !attachment.id && attachment.id.length )
                return 'No image selected.';
        },

        // ---------------------------------------------------------------------
        // Photo preview event handlers.
        // ---------------------------------------------------------------------

        onPhotoAdd: function() {
            if ( this.uploading.length )
                return;

            var conf = constants.get('conf') || {},
                limit = +conf.limitphoto,
                uploaded = +conf.uploadedphoto,
                num_photo_per_upload = +conf.num_photo_per_upload,
                curr = this.preview ? this.preview.getNumPics() : 0 ;

            if ( curr >= num_photo_per_upload ) {
                window.alert( language.get('photo.batch_notice') );
                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                return;
            }

            // Opera 12 and lower (Presto engine), and IE 10, cannot open File Dialog without clicking the input[type=file] element.
            if ( window.opera || ($.ie && $.ieVersion === 10) )
                this.$('#joms-postbox-photo-upload').find('input[type=file]').click();
            else
                this.uploader.open();
        },

        onPhotoAdded: function( up, files ) {
            if ( this.uploading.length )
                return;

            if ( !(files && files.length) )
                return;

            var exts = 'jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF',
                maxFileSize = this.maxFileSize;
                
            files = _.filter( files, function(file) {
                var ex = file.name.split('.').pop();
                return _.contains( exts.split(','), ex );
            });

            files = _.filter( files, function(file) {
                return file.size <= ( maxFileSize * 1024 * 1024 );
            });

             // daily limit checking
            var conf = constants.get('conf') || {},
                limit = +conf.limitphoto,
                uploaded = +conf.uploadedphoto,
                num_photo_per_upload = +conf.num_photo_per_upload,
                curr = this.preview ? this.preview.getNumPics() : 0;

            if ( curr + files.length > num_photo_per_upload ) {
                window.alert( language.get('photo.batch_notice') );
                _.each( files, function(file) {
                    up.removeFile(file);
                })
                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                _.each( files, function(file) {
                    up.removeFile(file);
                })
                return;
            }

            var removed;
            if ( uploaded + files.length > limit ) {
                removed = uploaded + files.length - limit;
                files.splice( 0 - removed, removed );
                up.splice( 0 - removed, removed );
            }

            var div;
            if ( !this.preview ) {
                div = $('<div>').appendTo( this.$preview );
                this.preview = new PreviewView();
                this.assign( div, this.preview );
                this.listenTo( this.preview, 'update', function( num ) {
                    if ( !num || num <= 0 ) {
                        this.showInitialState();
                        this.inputbox.single();
                        this.uploading = [];
                        return;
                    } else if ( num >= num_photo_per_upload ) {
                        this.hideMoreButton();
                    } else {
                        this.showMoreButton();
                    }

                    this.inputbox[ num > 1 ? 'multiple' : 'single' ]();
                } );
            }

            this.showMainState();
            for ( var i = 0; i < files.length; i++ ) 
                this.preview.add( files[i] );

            var self = this;
            _.each( files, function(file) {
                self.uploading.push(file.id);
            });

            this.$action.hide();

            up.start();
            up.refresh();
        },

        onPhotoError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Photos: Selected file type is not permitted.' );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                window.alert( language.get('photo.max_upload_size_error') );
            } else {
                console.log( file.message );
            }
        },

        onPhotoProgress: function( up, file ) {
            this.preview && this.preview.updateProgress( file );
        },

        onPhotoUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.thumbnail ) {
                up.stop();
                up.splice();
                window.alert( json && json.msg || 'Undefined error.' );
                this.uploading = [];
                this.$action.show();
                this.preview && this.preview.removeFailed();
                return;
            }

            this.uploading = _.without( this.uploading, file.id);

            this.uploading.length === 0 && this.$action.show();

            this.preview && this.preview.setImage( file, json );
        },

        // ---------------------------------------------------------------------
        // GIF preview event handlers.
        // ---------------------------------------------------------------------

        onGifAdd: function() {
            this.hideMoreButton();
            this.gifuploader.open();
        },

        onGifAdded: function( up, files ) {
            var div;
            if ( !this.gifPreview ) {
                div = $('<div>').appendTo( this.$preview );
                this.gifPreview = new GifPreviewView();
                this.assign( div, this.gifPreview );
                this.listenTo( this.gifPreview, 'update', function( num ) {
                    if ( !num || num <= 0 ) {
                        this.showInitialState();
                        this.inputbox.single();
                        this.uploading = [];
                        return;
                    }

                    this.hideMoreButton();
                    this.inputbox.single();
                } );
            }

            this.showMainState();
            this.gifPreview.add( files[0] );

            up.start();
            up.refresh();
        },

        onGifError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Gifs: Selected file type is not permitted.' );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                window.alert( language.get('photo.max_upload_size_error') );
            } else {
                console.log( file.message );
            }
        },

        onGifProgress: function( up, file ) {
            this.gifPreview.updateProgress( file );
        },

        onGifUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.image ) {
                up.stop();
                up.splice();
                window.alert( json && json.msg || 'Undefined error.' );
                this.$action.show();
                this.gifPreview && this.gifPreview.removeFailed();
                return;
            }

            this.$action.show();
            if ( this.gifPreview )
                this.gifPreview.setImage( file, json );
        },

        // ---------------------------------------------------------------------
        // Drop event handlers.
        // ---------------------------------------------------------------------

        onDragEnter: function(e) {
            e.preventDefault();
            this.$dropArea.show();
            this.$dropArea.css('line-height', this.$dropArea.height() + 'px');
        },

        onDragLeave: function(e) {
            e.preventDefault();
            this.$dropArea.hide();
        },

        onFileDrop: function(e) {
            e.preventDefault();
            this.$dropArea.hide();
        },


        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                allowgif: constants.get('conf.enablephotosgif') || false,
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    photo: language.get('photo') || {}
                }
            });

            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'photo' }
            );

            return this.staticAttachment;
        },

        ieUploadButtonFix: function( initialState ) {
            if ( !this.ieUploadButtonFix.init ) {
                this.ieUploadButtonFix.init = true;
                this.$uploader.css({
                    display: 'block',
                    position: 'absolute',
                    opacity: 0,
                    width: '',
                    height: ''
                }).children('button,form').css({
                    display: 'block',
                    position: 'absolute',
                    width: '',
                    height: '',
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }).children('input').css({
                    cursor: 'pointer',
                    height: '100%'
                });
            }

            if ( initialState ) {
                this.$uploader.appendTo( this.$uploaderParent );
                this.$uploader.css({
                    top: 12,
                    right: 12,
                    bottom: 12,
                    left: 12
                }).children('form').css({
                    width: '100%',
                    height: '100%'
                });
            } else {
                this.$uploader.appendTo( this.$tabupload );
                this.$uploader.css({
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                });
            }
        }

    });

});
define('views/inputbox/videourl',[
    'sandbox',
    'views/inputbox/base',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/videourl' ],

        render: function() {
            var div = this.getTemplate();
            this.$el.replaceWith( div );
            this.setElement( div );
            InputboxView.prototype.render.apply( this, arguments );
        },

        onKeydown: function( e ) {
            if ( e && e.keyCode === 13 )
                e.preventDefault();

            var that = this;
            $.defer(function() {
                that.updateInput( e );
            });
        },

        getTemplate: function() {
            var hint = language.get('video.link_hint') || '',
                html = this.template({ placeholder: hint });

            return $( html );
        }

    });

});
define('views/postbox/fetcher-video',[
    'sandbox',
    'views/base',
    'views/widget/select',
    'utils/ajax',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, SelectWidget, ajax, language ) {

    return BaseView.extend({

        template: joms.jst[ 'html/postbox/fetcher-video' ],

        events: {
            'click .joms-fetched-close': 'onClose',
            'click .joms-fetched-field span': 'onFocus',
            'keyup .joms-fetched-field input': 'onKeyup',
            'keyup .joms-fetched-field textarea': 'onKeyup',
            'blur .joms-fetched-field input': 'onBlur',
            'blur .joms-fetched-field textarea': 'onBlur'
        },

        initialize: function() {
            var lang = language.get('fetch') || {};
            this.titlePlaceholder = lang.title_hint || '';
            this.descPlaceholder = lang.description_hint || '';
            lang = language.get('video');
            this.categoryLabel = lang.category_label;
        },

        fetch: function( text ) {
            var rUrl = /^(|.*\s)(https?:\/\/|www\.)([a-z0-9-]+\.)+[a-z]{2,18}(:\d+)?(\/.*)?(\s.*|)$/i,
                isFetchable = text.match( rUrl );

            if ( this.fetching || !isFetchable )
                return;

            this.id = false;
            this.url = text;
            this.fetching = true;
            this.fetched = false;

            this.trigger('fetch:start');

            // TODO video limit...

            var that = this;
            ajax({
                fn: 'videos,ajaxLinkVideoPreview',
                data: [ text ],
                complete: function() {
                    that.fetching = false;
                    that.trigger('fetch:done');
                },
                success: $.bind( this.render, this )
            });
        },

        render: function( resp ) {
            resp = this.parseResponse( resp );
            if ( !resp ) {
                this.trigger( 'fetch:failed' );
                return;
            }

            var video = resp && resp.video,
                categories = this.sortCategories( resp && resp.category || [] );

            if ( !(video && video.id) ) {
                this.trigger( 'fetch:failed', resp );
                return;
            }

            this.video_id = video.id;
            this.fetched = true;

            var data = {
                title: video.title || '',
                titlePlaceholder: this.titlePlaceholder,
                description: video.description || '',
                descPlaceholder: this.descPlaceholder,
                image: video.thumb || false,
                lang: {
                    cancel: ( language.get('cancel') || '' ).toLowerCase()
                }
            };

            this.select && this.select.remove();
            this.$el.html( this.template( data ) );
            this.$image = this.$('.joms-fetched-images').find('img');
            this.$title = this.$('.joms-fetched-title').find('input');
            this.$description = this.$('.joms-fetched-description').find('textarea');
            this.$category = this.$('.joms-fetched-category');

            var options = [];
            for ( var i = 0; i < categories.length; i++ ) {
                options.push([ categories[i].id, this.categoryLabel + ': ' + categories[i].name ]);
            }

            this.select = new SelectWidget({ options: options });
            this.assign( this.$category, this.select );

            return this;
        },

        change: function( el ) {
            var input = $( el ),
                span = input.prev('span'),
                val = input.val().replace( /^\s+|\s+$/g, '' );

            if ( !val ) {
                if ( input.parent().hasClass('joms-fetched-title') )
                    val = this.titlePlaceholder;
                else
                    val = this.descPlaceholder;
            }

            input.hide();
            span.text( val ).show();
        },

        remove: function() {
            BaseView.prototype.remove.apply( this );
            this.trigger('remove');
        },

        value: function() {
            if ( this.fetching )
                return [];

            return [
                this.video_id,
                this.url,
                this.$image && this.$image.attr('src'),
                this.$title && this.escapeValue( this.$title.val() ),
                this.$description && this.escapeValue( this.$description.val() ),
                this.select && this.select.value()
            ];
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onClose: function() {
            this.remove();
        },

        onFocus: function( e ) {
            var span = $( e.currentTarget ),
                input = span.next('input,textarea');

            span.hide();
            input.show();
            setTimeout(function() {
                input[0].focus();
            }, 300 );
        },

        onKeyup: function( e ) {
            if ( e.keyCode === 13 ) {
                this.change( e.currentTarget );
            }
        },

        onBlur: function( e ) {
            this.change( e.currentTarget );
        },

        // ---------------------------------------------------------------------
        // Ajax response parser.
        // ---------------------------------------------------------------------

        parseResponse: function( resp ) {
            var json;

            if ( resp && resp.length ) {
                for ( var i = 0; i < resp.length; i++ ) {
                    if ( resp[i][1] === '__throwError' ) {
                        json = { msg: resp[i][3] };
                        break;
                    } else if ( resp[i][1] === '__callback' ) {
                        json = resp[i][3][0];
                        break;
                    }
                }
            }

            return json;
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        sortCategories: function( categories, parent, prefix ) {
            if ( !categories || !categories.length )
                return [];

            parent || (parent = 0);
            prefix || (prefix = '');

            var options = [];
            for ( var i = 0, id, name; i < categories.length; i++ ) {
                if ( +categories[i].parent === parent ) {
                    id = +categories[i].id;
                    name = prefix + categories[i].name;
                    options.push({ id: id, name: name });
                    options = options.concat( this.sortCategories( categories, id, name + ' &rsaquo; ' ) );
                }
            }

            return options;
        },

        escapeValue: function( value ) {
            if ( typeof value !== 'string' )
                return value;

            return value
                .replace( /\\/g, '&#92;' )
                .replace( /\t/g, '\\t' )
                .replace( /\n/g, '\\n' )
                .replace( /&quot;/g,  '"' );
        }

    });

});

define('views/inputbox/video',[
    'sandbox',
    'views/inputbox/status',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/video' ],

        getTemplate: function() {
            var hint = language.get('status.video_hint') || '',
                html = this.template({ placeholder: hint });

            return $( html );
        }

    });

});
define('views/postbox/video',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/inputbox/videourl',
    'views/postbox/fetcher-video',
    'views/inputbox/video',
    'views/dropdown/mood',
    'views/dropdown/location',
    'views/dropdown/privacy',
    'views/widget/select',
    'utils/ajax',
    'utils/constants',
    'utils/language',
    'utils/uploader'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    UrlView,
    FetcherView,
    InputboxView,
    MoodView,
    LocationView,
    PrivacyView,
    SelectWidget,
    ajax,
    constants,
    language,
    Uploader
) {

    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            location: LocationView,
            privacy: PrivacyView
        },

        template: joms.jst[ 'html/postbox/video' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click [data-action=share]': 'onVideoUrl',
            'click [data-action=upload]': 'onVideoUpload'
        }),

        initialize: function() {
            
            this.enableUpload = +constants.get('conf.enablevideosupload');
            this.enableLocation = +constants.get('conf.enablevideosmap');

            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            if ( !this.enableLocation )
                this.subviews = $.omit( this.subviews, 'location' );

            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            this.language = {
                postbox: language.get('postbox'),
                status: language.get('status'),
                video: language.get('video')
            };

            this.onVideoInit();

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            
            DefaultView.prototype.render.apply( this );

            this.$initial = this.$('.joms-initial-panel');
            this.$main = this.$('.joms-postbox-video');
            this.$stateurl = this.$('.joms-postbox-video-state-url');
            this.$stateupload = this.$('.joms-postbox-video-state-upload');

            this.$url = this.$stateurl.find('.joms-postbox-url');
            this.$fetcher = this.$stateurl.find('.joms-postbox-fetched');
            this.$file = this.$stateupload.find('.joms-postbox-url');
            this.$fileprogress = this.$stateupload.find('.joms-postbox-photo-progress');
            this.$title = this.$stateupload.find('input.input');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tablocation = this.$tabs.find('[data-tab=location]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            if ( !this.enableMood )
                this.$tabmood.remove();

            if ( !this.enableLocation )
                this.$tablocation.remove();

            if ( this.inheritPrivacy )
                this.$tabprivacy.css({ visibility: 'hidden' });

            // url
            this.url = new UrlView();
            this.assign( this.$url, this.url );
            this.listenTo( this.url, 'focus', this.onUrlFocus );
            this.listenTo( this.url, 'keydown',  this.onUrlUpdate );
            // this.listenTo( this.url, 'blur', this.onUrlUpdate );
            // this.listenTo( this.url, 'paste', this.onUrlUpdate );

            // inputbox
            
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
                       
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            return this;
        },

        showInitialState: function() {
            this.reset();
            this.$tabs.hide();
            this.$action.hide();
            this.$save.hide();

            if ( this.enableUpload ) {
                this.$main.hide();
                this.$initial.show();
            } else {
                this.showUrlState();
            }

            this.trigger('show:initial');
        },

        showMainState: function( upload ) {
            upload ? this.showUploadState() : this.showUrlState();
            this.$tabs.show();
            this.$action.show();
            this.trigger('show:main');
        },

        showUrlState: function() {
            this.inputbox.$el.find('.joms-postbox-input-placeholder').html( this.language.status.video_hint );
            this.$save.html( this.language.postbox.post_button );
            this.$stateupload.hide();
            this.$stateurl.show();
            this.$initial.hide();
            this.$main.show();
            this.upload = false;

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        showUploadState: function() {
            var categories, options, i;

            if ( !this.uploadcat ) {
                categories = this.sortCategories( constants.get('videoCategories') || [] );
                options = [];
                for ( i = 0; i < categories.length; i++ ) {
                    options.push([ categories[i].id, this.language.video.category_label + ': ' + categories[i].name ]);
                }

                this.uploadcat = new SelectWidget({ options: options });
                this.$uploadcat = this.$stateupload.find('.joms-fetched-category');
                this.assign( this.$uploadcat, this.uploadcat );
            }

            this.inputbox.$el.find('.joms-postbox-input-placeholder').html( this.language.video.upload_hint );
            this.$save.html( this.language.postbox.upload_button );
            this.$stateurl.hide();
            this.$stateupload.show();
            this.$initial.hide();
            this.$main.show();
            this.$save.show();
            this.upload = true;

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.url && this.url.reset();
            this.inputbox && this.inputbox.reset();
            this.fetcher && this.fetcher.remove();
        },

        value: function() { 
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            // video upload
            if ( this.upload ) {
                if ( this.status && this.status.status === 'success' )
                    this.data.attachment.fetch = [ this.status.videoid ];

            // video share
            } else {
                if ( this.fetcher )
                    this.data.attachment.fetch = this.fetcher.value();
            }

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {},
                fetch = attachment.fetch;

            // video upload
            if ( this.upload ) {
                if ( !(this.uploadcat && this.uploadcat.value()) )
                    return ( language.get('video.select_category') || 'Please select video category.' );

            // video share
            } else {
                if ( !fetch || !fetch.length )
                    return ( language.get('video.invalid_url') || 'Please share a valid video url.' );
                if ( !fetch[5] )
                    return ( language.get('video.select_category') || 'Please select video category.' );
            }
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onPost: function() {
            if ( this.saving )
                return;

            var error = this.validate();
            if ( error ) {
                window.alert( error );
                return;
            }

            this.saving = true;
            this.$loading.show();

            if ( this.upload === true ) {
                this.uploader.uploader.start();
                return;
            }

            var that = this;
            var data = this.value();

            // add current filters
            if ( window.joms_filter_params ) {
                data.push( JSON.stringify( window.joms_filter_params ) );
            }

            window.joms_postbox_posting = true;
            ajax({
                fn: 'system,ajaxStreamAdd',
                data: data,
                success: $.bind( this.onPostSuccess, this ),
                complete: function() {
                    window.joms_postbox_posting = false;
                    that.$loading.hide();
                    that.saving = false;
                    that.showInitialState();

                    if (+window.joms_infinitescroll) {
                        $('.joms-stream__loadmore').find('a').hide()
                    }
                }
            });
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
        },

        onLocationSelect: function( location ) {
            this.inputbox.updateAttachment( null, location );
        },

        onLocationRemove: function() {
            this.inputbox.updateAttachment( null, false );
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onUrlFocus: function() {
            if ( !this.enableUpload )
                this.showMainState();
        },

        onUrlUpdate: function( text, key ) {
            var div;

            if ( text )
                text = text.replace( /^\s+|\s+$/g, '' );

            // triggers fetch content on spacebar and return keys
            if ( key === 32 || key === 13 ) {
                this.fetch( text );
            } else {
                this.fetchProxy( text );
            }
        },

        onInputFocus: function() {
            if ( !this.enableUpload )
                this.showMainState();
        },

        // ---------------------------------------------------------------------
        // Video preview event handlers.
        // ---------------------------------------------------------------------

        fetchProxy: $.debounce(function( text ) {
            this.fetch( text );
        }, 1000 ),

        fetch: function( text ) {
            var div;

            if ( this.fetcher && (this.fetcher.fetching || this.fetcher.fetched) )
                return;

            delete this.data.attachment.fetch;

            div = $('<div>').appendTo( this.$fetcher );
            this.fetcher && this.fetcher.remove();
            this.fetcher = new FetcherView();
            this.fetcher.setElement( div );
            this.listenTo( this.fetcher, 'fetch:start', this.onFetchStart );
            this.listenTo( this.fetcher, 'fetch:failed', this.onFetchFailed );
            this.listenTo( this.fetcher, 'fetch:done', this.onFetchDone );
            this.listenTo( this.fetcher, 'remove', this.onFetchRemove );
            this.fetcher.fetch( text );
        },

        onFetchStart: function() {
            this.saving = true;
            this.$loading.show();
            this.$save.hide();
        },

        onFetchFailed: function( resp ) {
            this.fetcher && this.fetcher.remove();
            this.saving = false;
            this.$loading.hide();
            this.$save.hide();

            var msg = resp && resp.msg || 'Undefined error.';
            window.alert( msg );
        },

        onFetchDone: function() {
            this.saving = false;
            this.$loading.hide();
            if ( this.fetcher && this.fetcher.fetched )
                this.$save.show();
        },

        onFetchRemove: function() {
            this.fetcher = false;
            this.$save.hide();
        },

        // ---------------------------------------------------------------------
        // Video upload event handlers.
        // ---------------------------------------------------------------------

        onVideoUrl: function() {
            this.showMainState();
        },

        onVideoInit: function() {
            if ( this.uploader ) {
                return;
            }

            var maxFileSize = +constants.get('conf.maxvideouploadsize') || 0;
            if ( maxFileSize ) {
                maxFileSize += 'mb';
            }

            var url = 'index.php?option=com_community&view=videos&task=uploadvideo';
            var creatortype = constants.get('videocreatortype');
            var settings = constants.get('settings') || {};
            var groupid = +constants.get('groupid');
            var pageid = +constants.get('pageid');
            var eventid = +constants.get('eventid');

            if ( creatortype ) {
                url += '&creatortype=' + creatortype;
            }

            if ( settings.isProfile && !settings.isMyProfile ) {
                url += '&target=' + constants.get('postbox.attachment.target');
            } else if ( +eventid > 0 ) {
                url += '&eventid=' + eventid;
            } else if ( +groupid > 0 ) {
                url += '&groupid=' + groupid;
            } else if ( +pageid > 0 ) {
                url += '&pageid=' + pageid;
            }

            this.uploader = new Uploader({
                container: 'joms-js--videouploader-upload',
                browse_button: 'joms-js--videouploader-upload-button',
                url: url,
                multi_selection: false,
                filters: [{ title: 'Video files', extensions: '3g2,3gp,asf,asx,avi,flv,mov,mp4,mpg,rm,swf,vob,wmv,m4v' }],
                max_file_size: maxFileSize
            });

            // uploader events
            this.uploader.onAdded = $.bind( this.onVideoAdded, this );
            this.uploader.onError = $.bind( this.onVideoError, this );
            this.uploader.onBeforeUpload = $.bind( this.onVideoBeforeUpload, this );
            this.uploader.onProgress = $.bind( this.onVideoProgress, this );
            this.uploader.onUploaded = $.bind( this.onVideoUploaded, this );
            this.uploader.init();
        },

        onVideoUpload: function() {
            var conf = constants.get('conf') || {},
                limit = +conf.limitvideo,
                uploaded = +conf.uploadedvideo;

            if ( uploaded >= limit ) {
                window.alert( language.get('video.upload_limit_exceeded') || 'You have reached the upload limit.' );
                return;
            }

            this.uploader.uploader.splice();
            this.uploader.open();
        },

        onVideoAdded: function( up, files ) {
            if ( !(files && files.length) )
                return;

            var file = files[0],
                name = '<b>' + file.name + '</b>',
                size = file.size || 0,
                unit = 'Bytes';

            for ( var units = [ 'KB', 'MB', 'GB' ]; size >= 1000 && units.length; ) {
                unit = units.shift();
                size = Math.ceil( size / 1000 );
            }

            if ( size )
                name += ' (' + size + ' ' + unit + ')';

            this.$file.html( name );
            this.$fileprogress.css({ width: 0 });
            this.showMainState('upload');
        },

        onVideoError: function( up, file ) {
            var tmp;
            if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                tmp = +constants.get('conf.maxvideouploadsize') || 0;
                window.alert( 'Maximum file size for video upload is ' + tmp + ' MB.' );
            } else if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( 'Selected file type is not permitted.' );
            }
        },

        onVideoBeforeUpload: function() {
            var params = {
                title : this.$title.val(),
                description : this.inputbox.value()
            };

            if ( this.subflags.privacy )
                params.permissions = this.subviews.privacy.value();

            var location = this.subflags.location && this.subviews.location.value();
            if ( location && location.length )
                params.location = location;

            if ( this.uploadcat )
                params.category_id = this.uploadcat && this.uploadcat.value();

            this.uploader.params( params );
        },

        onVideoProgress: function( up, file ) {
            var percent = Math.min( 100, Math.round( 100 * file.loaded / file.size ) );
            this.$fileprogress.animate({ width: percent + '%' });
        },

        onVideoUploaded: function( up, file, info ) {
            var json, that;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            this.status = json || {};
            that = this;
            setTimeout(function() {
                that.$loading.hide();
                that.saving = false;
                that.showInitialState();

                if ( that.status.status !== 'success' ){
                    window.alert( that.status.message || 'Undefined error.' );
                } else {
                    var conf = constants.get('conf') || {};
                    ++conf.uploadedvideo;
                    window.alert( that.status.processing_str );
                }
            }, 1000 );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                enable_upload: this.enableUpload,
                video_maxsize: constants.get('conf.maxvideouploadsize'),
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    video: language.get('video') || {}
                }
            });

            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'video' }
            );

            return this.staticAttachment;
        },

        sortCategories: function( categories, parent, prefix ) {
            if ( !categories || !categories.length )
                return [];

            parent || (parent = 0);
            prefix || (prefix = '');

            var options = [];
            for ( var i = 0, id, name; i < categories.length; i++ ) {
                if ( +categories[i].parent === parent ) {
                    id = +categories[i].id;
                    name = prefix + categories[i].name;
                    options.push({ id: id, name: name });
                    options = options.concat( this.sortCategories( categories, id, name + ' &rsaquo; ' ) );
                }
            }

            return options;
        }

    });

});
define('views/inputbox/eventtitle',[
    'sandbox',
    'views/inputbox/base',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/eventtitle' ],

        render: function() {
            var div = this.getTemplate();
            this.$el.replaceWith( div );
            this.setElement( div );
            InputboxView.prototype.render.apply( this, arguments );
        },

        getTemplate: function() {
            var hint = language.get('event.title_hint') || '',
                html = this.template({ placeholder: hint });

            return $( html );
        }

    });

});
define('views/inputbox/eventdesc',[
    'sandbox',
    'views/inputbox/base',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/eventdesc' ],

        render: function() {
            var div = this.getTemplate();
            this.$el.replaceWith( div );
            this.setElement( div );
            InputboxView.prototype.render.apply( this, arguments );
        },

        getTemplate: function() {
            var hint = language.get('status.event_hint') || '',
                html = this.template({ placeholder: hint });

            return $( html );
        }

    });

});
define('views/dropdown/event',[
    'sandbox',
    'views/dropdown/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: _.template('\
            <div class="joms-postbox-dropdown event-dropdown">\
                <div style="clear:both">\
                    <div class=event-time-column>\
                    <label class="joms-checkbox">\
                        <input type="checkbox" class="joms-checkbox private-event" name="permission" value="1">\
                        <span title="<%= language.event.private_tips %>">\
                        <%= language.event.private %>\
                        </span>\
                    </label>\
                    </div>\
                </div>\
                <div style="clear:both">\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.category %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <div class="joms-select--wrapper">\
                            <select class="joms-event-category joms-select" style="width:100%"></select>\
                        </div>\
                    </div>\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.location %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class=joms-input name=location placeholder="<%= language.event.location_hint %>">\
                    </div>\
                </div>\
                <div style="clear:both">\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.start %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class="joms-input joms-pickadate-startdate" placeholder="<%= language.event.start_date_hint %>" style="background-color:transparent;cursor:text">\
                        <input type=text class="joms-input joms-pickadate-starttime" placeholder="<%= language.event.start_time_hint %>" style="background-color:transparent;cursor:text">\
                        <span class="event-all-day joms-event-allday" style="margin-left:0;visibility:hidden">\
                            <span>All Day Event</span>\
                            <span style="position:relative">\
                                <i class=joms-icon-check-empty></i>\
                            </span>\
                        </span>\
                    </div>\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.end %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class="joms-input joms-pickadate-enddate" placeholder="<%= language.event.end_date_hint %>" style="background-color:transparent;cursor:text">\
                        <input type=text class="joms-input joms-pickadate-endtime" placeholder="<%= language.event.end_time_hint %>" style="background-color:transparent;cursor:text">\
                    </div>\
                </div>\
                <nav class="joms-postbox-tab selected">\
                    <div class="joms-postbox-action event-action">\
                        <button class=joms-postbox-done><%= language.event.done_button %></button>\
                    </div>\
                </nav>\
            </div>\
        '),

        events: {
            'click .joms-postbox-done': 'onSave'
        },

        render: function() {
            var div = this.getTemplate(),
                ampm = +constants.get('conf.eventshowampm'),
                firstDay = +constants.get('conf.firstday'),
                timeFormatLabel = ampm ? 'h:i A' : 'H:i',
                translations = {},
                categories,
                i;

            // Translations.
            translations.monthsFull = [
                language.get('datepicker.january'),
                language.get('datepicker.february'),
                language.get('datepicker.march'),
                language.get('datepicker.april'),
                language.get('datepicker.may'),
                language.get('datepicker.june'),
                language.get('datepicker.july'),
                language.get('datepicker.august'),
                language.get('datepicker.september'),
                language.get('datepicker.october'),
                language.get('datepicker.november'),
                language.get('datepicker.december')
            ];

            translations.monthsShort = [];
            for ( i = 0; i < translations.monthsFull.length; i++ )
                translations.monthsShort[i] = translations.monthsFull[i].substr( 0, 3 );

            translations.weekdaysFull = [
                language.get('datepicker.sunday'),
                language.get('datepicker.monday'),
                language.get('datepicker.tuesday'),
                language.get('datepicker.wednesday'),
                language.get('datepicker.thursday'),
                language.get('datepicker.friday'),
                language.get('datepicker.saturday')
            ];

            translations.weekdaysShort = [];
            for ( i = 0; i < translations.weekdaysFull.length; i++ )
                translations.weekdaysShort[i] = translations.weekdaysFull[i].substr( 0, 3 );

            translations.today = language.get('datepicker.today');
            translations['clear'] = language.get('datepicker.clear');

            translations.firstDay = firstDay;
            translations.selectYears = 200;
            translations.selectMonths = true;

            this.$el.replaceWith( div );
            this.setElement( div );

            this.$category = this.$('.joms-event-category').empty();
            this.$location = this.$('[name=location]').val('');
            this.$startdate = this.$('.joms-pickadate-startdate').pickadate( $.extend({}, translations, { min: new Date(), format: 'd mmmm yyyy', klass: { frame: 'picker__frame startDate' } }) );
            this.$starttime = this.$('.joms-pickadate-starttime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame startTime' } });
            this.$enddate = this.$('.joms-pickadate-enddate').pickadate( $.extend({}, translations, { format: 'd mmmm yyyy', klass: { frame: 'picker__frame endDate' } }) );
            this.$endtime = this.$('.joms-pickadate-endtime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame endTime' } });
            this.$done = this.$('.joms-event-done');

            categories = constants.get('eventCategories') || [];
            if ( categories && categories.length ) {
                for ( i = 0; i < categories.length; i++ ) {
                    this.$category.append( '<option value="' + categories[i].id + '">' + categories[i].name + '</option>' );
                }
            }

            this.startdate = this.$startdate.pickadate('picker');
            this.starttime = this.$starttime.pickatime('picker');
            this.enddate = this.$enddate.pickadate('picker');
            this.endtime = this.$endtime.pickatime('picker');

            this.startdate.on({ set: $.bind( this.onSetStartDate, this ) });
            this.starttime.on({ set: $.bind( this.onSetStartTime, this ) });
            this.enddate.on({ set: $.bind( this.onSetEndDate, this ) });
            this.endtime.on({ set: $.bind( this.onSetEndTime, this ) });

            this.$private = this.$el.find('.private-event');

            return this;
        },

        // ---------------------------------------------------------------------

        value: function() {
            return this.data;
        },

        reset: function() {
            this.$category.val( this.$category.find('option').eq(0).attr('value') );
            this.$location.val('');
            this.$startdate.val('');
            this.$starttime.val('');
            this.$enddate.val('');
            this.$endtime.val('');
            this.$private.prop('checked', false);
        },

        // ---------------------------------------------------------------------

        onSetStartDate: function( o ) {
            var ts = o.select;
            this.enddate.set({ min: new Date(ts) }, { muted: true });
            this._checkTime();
        },

        onSetEndDate: function( o ) {
            var ts = o.select;
            this.startdate.set({ max: new Date(ts) }, { muted: true });
            this._checkTime();
        },

        onSetStartTime: function() {
            this._checkTime('start');
        },

        onSetEndTime: function() {
            this._checkTime('end');
        },

        onSave: function() {
            var category = this.$category.val(),
                location = this.$location.val(),
                startdate = this.startdate.get('select'),
                starttime = this.starttime.get('select'),
                enddate = this.enddate.get('select'),
                endtime = this.endtime.get('select'),
                error;

            // get category
            category = [ category, this.$category.find('[value=' + category + ']').text() ];

            // get start date and time
            startdate && (startdate = [ this.startdate.get('select', 'yyyy-mm-dd'), this.startdate.get('value') ]);
            starttime && (starttime = [ this.starttime.get('select', 'HH:i'), this.starttime.get('value') ]);

            // get end date and time
            enddate && (enddate = [ this.enddate.get('select', 'yyyy-mm-dd'), this.enddate.get('value') ]);
            endtime && (endtime = [ this.endtime.get('select', 'HH:i'), this.endtime.get('value') ]);

            // data
            this.data = {
                category  : category,
                location  : location,
                startdate : startdate,
                starttime : starttime,
                enddate   : enddate,
                endtime   : endtime,
                allday    : false,
                private   : this.$private.is(':checked') ? true : false
            };

            // check values
            if ( !this.data.category ) {
                error = language.get('event.category_not_selected');
            } else if ( !this.data.location ) {
                error = language.get('event.location_not_selected');
            } else if ( !this.data.startdate ) {
                error = language.get('event.start_date_not_selected');
            } else if ( !this.data.starttime ) {
                error = language.get('event.end_date_not_selected');
            } else if ( !this.data.enddate ) {
                error = language.get('event.start_time_not_selected');
            } else if ( !this.data.endtime ) {
                error = language.get('event.end_time_not_selected');
            }

            if ( error ) {
                window.alert( error );
                return;
            }

            this.trigger( 'select', this.data );
            this.hide();
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                language: {
                    event: language.get('event') || {}
                }
            });

            return $( html ).hide();
        },

        _checkTime: function() {
            var startdate = this.startdate.get('select'),
                enddate = this.enddate.get('select'),
                starttime, endtime;

            if ( !startdate || !enddate )
                return;

            if ( enddate.year <= startdate.year && enddate.month <= startdate.month && enddate.date <= startdate.date ) {
                starttime = this.starttime.get('select');
                endtime = this.endtime.get('select');

                if ( !starttime ) {
                    this.endtime.set({ min: false }, { muted: true });
                } else {
                    this.endtime.set({ min: [ starttime.hour, starttime.mins ] }, { muted: true });
                    if ( endtime && endtime.time < starttime.time )
                        this.endtime.set({ select: [ starttime.hour, starttime.mins ] }, { muted: true });
                }

                if ( !endtime ) {
                    this.starttime.set({ max: false }, { muted: true });
                } else {
                    this.starttime.set({ max: [ endtime.hour, endtime.mins ] }, { muted: true });
                    if ( starttime && starttime.time > endtime.time )
                        this.starttime.set({ select: [ endtime.hour, endtime.mins ] }, { muted: true });
                }
            } else {
                this.starttime.set({ max: false }, { muted: true });
                this.endtime.set({ min: false }, { muted: true });
            }
        }

    });

});

define('utils/format',[
    'sandbox'
],

// definition
// ----------
function( $ ) {

    function pad( str, len, padStr ) {
        if ( !$.isString( str ) )
            return str;

        if ( !$.isNumber( len ) || str.length >= len )
            return str;

        len = len - str.length;
        for ( var i = 0; i < len; i++ )
            str = ( padStr || ' ' ) + str;

        return str;
    }

    return {
        pad: pad
    };

});

define('views/postbox/event',[
    'sandbox',
    'views/postbox/default',
    'views/inputbox/eventtitle',
    'views/inputbox/eventdesc',
    'views/dropdown/event',
    'utils/constants',
    'utils/format',
    'utils/language'
],

// definition
// ----------
function(
    $,
    DefaultView,
    TitleView,
    InputboxView,
    EventView,
    constants,
    format,
    language
) {

    return DefaultView.extend({

        subviews: {
            event: EventView
        },

        template: joms.jst[ 'html/postbox/event' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-event-title': 'onFocus'
        }),

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$title = this.$('.joms-postbox-title');
            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$category = this.$('.joms-postbox-event-label-category');
            this.$location = this.$('.joms-postbox-event-label-location');
            this.$date = this.$('.joms-postbox-event-label-date');

            // title
            this.title = new TitleView();
            this.assign( this.$title, this.title );
            this.listenTo( this.title, 'focus', this.onInputFocus );
            this.listenTo( this.title, 'keydown', this.onNewInputUpdate );

            // inputbox
            this.inputbox = new InputboxView({ charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );
            this.listenTo( this.inputbox, 'keydown', this.onNewInputUpdate );

            return this;
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.title && this.title.reset();
            this.inputbox && this.inputbox.reset();
            this.$category && this.onEventSelect({});
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            var starttime = (this._data.starttime || '').split(':');
            var endtime = (this._data.endtime || '').split(':');

            $.extend( this.data.attachment, {
                title: this.title.value(),
                catid: this._data.category && this._data.category[0] || null,
                location: this._data.location,
                startdate: this._data.startdate,
                enddate: this._data.enddate,
                allday: false,
                'starttime-hour': starttime[0] || null,
                'starttime-min': starttime[1] || null,
                'endtime-hour': endtime[0] || null,
                'endtime-min': endtime[1] || null
            });

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                text = value[0];

            if ( !text )
                return 'Event description cannot be empty.';
        },

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onInputFocus: function() {
            this.showMainState();
        },

        onInputUpdate: $.debounce(function() {
            var value = this.value( true ),
                text = value[0],
                attachment = value[1],
                show = true;

            if ( !this.trim( attachment.title ) )
                show = false;
            else if ( !this.trim( text ) )
                show = false;
            else if ( !attachment.catid )
                show = false;
            else if ( !attachment.location )
                show = false;
            else if ( !attachment.startdate )
                show = false;
            else if ( !attachment.enddate )
                show = false;
            else if ( !attachment['starttime-hour'] && !attachment['starttime-min'] )
                show = false;
            else if ( !attachment['endtime-hour'] && !attachment['endtime-min'] )
                show = false;

            this.$save[ show ? 'show' : 'hide' ]();

        }, 300 ),

        onNewInputUpdate: $.debounce(function() {
            var value = this.value( true ),
                text = value[0],
                attachment = value[1],
                show = true;

            if ( !this.trim( attachment.title ) )
                show = false;
            else if ( !this.trim( text ) )
                show = false;
            else if ( !attachment.catid )
                show = false;
            else if ( !attachment.location )
                show = false;
            else if ( !attachment.startdate )
                show = false;
            else if ( !attachment.enddate )
                show = false;
            else if ( !attachment['starttime-hour'] && !attachment['starttime-min'] )
                show = false;
            else if ( !attachment['endtime-hour'] && !attachment['endtime-min'] )
                show = false;

            this.$save[ show ? 'show' : 'hide' ]();

        }, 300 ),

        onPost: function() {
            var conf = constants.get('conf') || {},
                limit = +conf.limitevent,
                created = +conf.createdevent,
                message;

            if ( created >= limit ) {
                message = language.get('event.create_limit_exceeded') || 'You have reached the event creation limit.';
                message = message.replace( '%1$s', limit );
                window.alert( message );
                return;
            }

            DefaultView.prototype.onPost.apply( this, arguments );
        },

        onPostSuccess: function() {
            DefaultView.prototype.onPostSuccess.apply( this, arguments );
            var conf = constants.get('conf') || {};
            conf.createdevent = +conf.createdevent + 1;
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onEventSelect: function( data ) {
            if ( !data.category ) {
                this.$category.hide();
            } else {
                this.$category.find('.joms-input-text').html( data.category && data.category[1] );
                this.$category.show();
            }

            if ( !data.location ) {
                this.$location.hide();
            } else {
                this.$location.find('.joms-input-text').html( data.location );
                this.$location.show();
            }

            var str = [];
            if ( !data.startdate || !data.enddate ) {
                this.$date.hide();
            } else {
                str.push( data.startdate[1] + ' ' + data.starttime[1] );
                str.push( data.enddate[1] + ' ' + data.endtime[1] );
                this.$date.find('.joms-input-text').html( str.join(' &mdash; ') );
                this.$date.show();
            }

            data.startdate && (data.startdate = data.startdate[0]);
            data.starttime && (data.starttime = data.starttime[0]);
            data.enddate && (data.enddate = data.enddate[0]);
            data.endtime && (data.endtime = data.endtime[0]);

            this._data = data;
            this.onInputUpdate();
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Ajax response parser.
        // ---------------------------------------------------------------------

        parseResponse: function( response ) {
            var elid = 'activity-stream-container',
                data, temp;

            if ( response && response.length ) {
                for ( var i = 0; i < response.length; i++ ) {
                    if ( response[i][1] === elid ) {
                        data = response[i][3];
                    }
                    if ( response[i][0] === 'al' ) {
                        temp = response[i][3];
                        window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
                    }
                    if ( response[i][1] === '__throwError' ) {
                        temp = response[i][3];
                        window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
                    }
                    if ( response[i][0] === 'cs' ) {
                        try {
                            eval( response[i][1] );
                        } catch (e) {}
                    }
                }
            }

            return data;
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var lang = language.get('event') || {};
            if ( lang.event_detail )
                lang.event_detail = lang.event_detail.toLowerCase();

            var html = this.template({
                juri: constants.get('juri'),
                language: {
                    postbox: language.get('postbox') || {},
                    event: lang
                }
            });

            return $( html ).hide();
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'event' }
            );

            return this.staticAttachment;
        },

        trim: function( text ) {
            return (text || '').replace( /^\s+|\s+$/g, '' );
        }

    });

});
define('views/postbox/file-preview',[
    'sandbox',
    'app',
    'views/base',
    'utils/ajax',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, App, BaseView, ajax, constants, language ) {

    return BaseView.extend({

        templates: {
            div: _.template(
                '<div class=joms-postbox-photo-preview> \
                    <ul class="joms-list clearfix"></ul> \
                </div>'
            ),

            file: _.template(
                '<li id="<%= id %>" data-fileid="" class=joms-postbox-photo-item> \
                    <div class=img-wrapper> \
                        <svg viewBox="0 0 16 18" class="joms-icon">\
                            <use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip" class="joms-icon--svg-fixed joms-icon--svg-unmodified joms-icon--svg-unmodified"></use>\
                        </svg> \
                        <b class="joms-filename"><%= name %></b>\
                    </div> \
                    <div class=joms-postbox-photo-action style="display:none"> \
                        <span class=joms-postbox-photo-remove>×</span> \
                    </div> \
                    <div class=joms-postbox-file-progressbar> \
                        <div class=joms-postbox-file-progress></div> \
                    </div> \
                </li>'
            )
        },

        events: {
            'click .joms-postbox-photo-remove': 'onRemove'
        },

        render: function() {
            this.$el.html( this.templates.div() );
            this.$list = this.$el.find('ul.joms-list');
            this.$form = this.$el.find('div.joms-postbox-photo-form');
        },

        add: function( file ) {
            this.$list.append( this.templates.file({
                id: this.getFileId( file ),
                name: file.name,
                src: App.legacyUrl + '/assets/photo-upload-placeholder.png'
            }) );

            var settings = constants.get('settings') || {};
            if ( !settings.isMyProfile )
                return;

            if ( this.select )
                return;

            var albums = constants.get('album'),
                privs = language.get('privacy'),
                options = [];

            var privmap = {
                '0': 'public',
                '10': 'public',
                '20': 'site_members',
                '30': 'friends',
                '40': 'me'
            };

            var icons = {
                '0': 'earth',
                '10': 'public',
                '20': 'users',
                '30': 'user',
                '40': 'lock'
            };

            if ( !(albums && albums.length) )
                return;

            this.albummap = {};
            for ( var i = 0, album, permission; i < albums.length; i++ ) {
                album = albums[i];
                permission = '' + album.permissions;
                this.albummap[ '' + album.id ] = permission;
                album = [ album.id, album.name, privs[ privmap[permission] || '0' ], icons[permission || '0'] ];
                options[ +album['default'] ? 'unshift' : 'push' ]( album );
            }
        },

        value: function() {
            var settings = constants.get('settings') || {},
                album_id, privacy, values;

            values = {
                id: this.files || []
            };

            return values;
        },

        updateProgress: function( file ) {
            var id = this.getFileId( file ),
                elem = this.$list.find( '#'+ id ).find('.joms-postbox-file-progress'),
                percent;

            if ( elem && elem.length ) {
                percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
                elem.stop().animate({ width: percent + '%' });
            }
        },

        setFile: function( file, json ) {
            json || (json = {});

            var elem = this.$list.find( '#' + this.getFileId(file) ),
                id = json.id;

            elem.find('.joms-filename').text(file.name);
            elem.attr('data-id', json.id);

            elem.find('.joms-postbox-photo-action').show();
            elem.addClass('joms-postbox-photo-loaded');
            elem.find('.joms-postbox-photo-progressbar').remove();

            this.files || (this.files = []);
            this.files.push( '' + id );

            this.trigger( 'update', this.files.length );
        },

        removeFailed: function() {
            this.$list.find('.joms-postbox-photo-item')
                .not('.joms-postbox-photo-loaded')
                .remove();

            this.trigger( 'update', this.files && this.files.length || 0 );
        },

        // ---------------------------------------------------------------------
        // Thumbnail event handlers.
        // ---------------------------------------------------------------------

        onRemove: function( e ) {
            var li = $( e.target ).closest('li'),
                id = li.data('id'),
                num;

            li.remove();
            this.files = $.without( this.files, '' + id );
            num = this.files.length;

            this.ajaxRemove([ id ]);
            this.trigger( 'update', num );
        },

        remove: function() {
            this.files && this.files.length && this.ajaxRemove( this.files );
            return BaseView.prototype.remove.apply( this, arguments );
        },

        ajaxRemove: function( files ) {
            var params = {};
            params.option = 'community';
            params.no_html = 1;
            params.task = 'azrul_ajax';
            params.func = 'system,ajaxDeleteTempFile';
            params[ window.jax_token_var ] = 1;

            if ( files && files.length )
                params[ 'arg2[]' ] = files;

            $.ajax({
                url: window.jax_live_site,
                type: 'post',
                dataType: 'json',
                data: params
            });
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getFileId: function( file ) {
            return 'postbox-preview-' + file.id;
        },

        getNumFiles: function() {
            return this.$list.find('li').length;
        }

    });

});

define('views/inputbox/file',[
    'sandbox',
    'views/inputbox/status',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, InputboxView, constants, language ) {

    return InputboxView.extend({

        template: joms.jst[ 'html/inputbox/base' ],

        initialize: function() {
            var hash, item, id, i;

            InputboxView.prototype.initialize.apply( this, arguments );
            this.hint = {
                single: language.get('status.file_hint') || '',
                multiple: language.get('status.files_hint') || ''
            };

            this.moods = constants.get('moods');
            hash = {};
            if ( this.moods && this.moods.length ) {
                for ( i = 0; i < this.moods.length; i++ ) {
                    id = this.moods[i].id;
                    item = [ id, this.moods[i].description ];
                    if ( this.moods[i].custom ) {
                        item[2] = this.moods[i].image;
                    }
                    hash[ id ] = item;
                }
            }
            this.moods = hash;
        },

        reset: function() {
            InputboxView.prototype.reset.apply( this, arguments );
            this.single();
        },

        single: function() {
            this.hint.current = this.hint.single;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        multiple: function() {
            this.hint.current = this.hint.multiple;
            if ( this.$textarea.attr('placeholder') )
                this.$textarea.attr( 'placeholder', this.hint.current );
        },

        updateAttachment: function( mood ) {
            var attachment = [];

            this.mood = mood || mood === false ? mood : this.mood;

            if ( this.mood && this.moods[this.mood] ) {
                if ( typeof this.moods[this.mood][2] !== 'undefined' ) {
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

            if ( !attachment.length ) {
                this.$attachment.html('');
                this.$textarea.attr( 'placeholder', this.hint.current );
                return;
            }

            this.$attachment.html( ' &nbsp;&mdash; ' + attachment.join(' ' + language.get('and') + ' ') + '.' );
            this.$textarea.removeAttr('placeholder');
        },

        getTemplate: function() {
            var html = this.template({ placeholder: this.hint.current = this.hint.single });
            return $( html );
        }

    });

});

define('views/postbox/file',[
    'sandbox',
    'app',
    'views/postbox/default',
    'views/postbox/file-preview',
    'views/inputbox/file',
    'views/dropdown/mood',
    'views/dropdown/privacy',
    'utils/constants',
    'utils/language',
    'utils/uploader'
],

// definition
// ----------
function(
    $,
    App,
    DefaultView,
    PreviewView,
    InputboxView,
    MoodView,
    PrivacyView,
    constants,
    language,
    Uploader
) {
    return DefaultView.extend({

        subviews: {
            mood: MoodView,
            privacy: PrivacyView
        },

        template: _.template(
            '<div class="joms-postbox-file-wrapper" style="position: relative;">\
                <div class="joms-postbox--droparea" id="joms-postbox-file--droparea">\
                    <%= language.file.drop_to_upload %>\
                </div>\
                <div id=joms-postbox-file-upload style="position:absolute;top:0;left:0;width:1px;height:1px;overflow:hidden">\
                    <button id=joms-postbox-file-upload-btn>Upload</button>\
                </div>\
                <div class="joms-postbox-inner-panel" style="position:relative">\
                    <div class="joms-postbox-file-upload">\
                        <svg viewBox="0 0 16 18" class="joms-icon">\
                            <use xlink:href="<%=window.joms_current_url%>#joms-icon-file-zip" class="joms-icon--svg-fixed joms-icon--svg-unmodified"></use>\
                        </svg>\
                         <%= language.file.upload_button %>\
                    </div>\
                </div>\
                <div class="joms-postbox-file">\
                    <div class="joms-postbox-preview"></div>\
                    <div class=joms-postbox-inputbox></div>\
                    <nav class="joms-postbox-tab selected"> \
                        <ul class="joms-list inline"> \
                            <li data-tab=upload data-bypass=1>\
                                <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-file-zip"></use></svg> \
                                <span class=visible-desktop><%= language.file.upload_button_more %></span>\
                            </li> \
                            <li data-tab=mood>\
                                <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-happy"></use></svg> \
                                <span class=visible-desktop><%= language.status.mood %></span>\
                            </li> \
                            <li data-tab=privacy>\
                                <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-earth"></use></svg> \
                                <span class=visible-desktop></span>\
                            </li>\
                        </ul> \
                        <div class=joms-postbox-action> \
                            <button class=joms-postbox-cancel><%= language.postbox.cancel_button %></button> \
                            <button class=joms-postbox-save><%= language.postbox.post_button %></button> \
                        </div> \
                        <div class=joms-postbox-loading style="display:none;"> \
                            <img src="<%= juri.root%>components/com_community/assets/ajax-loader.gif" alt="loader"> \
                        </div> \
                    </nav>\
                </div>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                language: {
                    postbox: language.get('postbox') || {},
                    status: language.get('status') || {},
                    file: language.get('file') || {},
                    privacy: language.get('privacy') || {}
                }
            });

            return $( html ).hide();
        },

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-file-upload': 'onFileAdd',
            'click li[data-tab=upload]': 'onFileAdd',
            'dragenter': 'onDragEnter',
            'dragleave #joms-postbox-file--droparea': 'onDragLeave',
            'drop #joms-postbox-file--droparea': 'onFileDrop'
        }),

        initialize: function() {
            var moods = constants.get('moods');
            this.enableMood = +constants.get('conf.enablemood') && moods && moods.length;
            if ( !this.enableMood )
                this.subviews = $.omit( this.subviews, 'mood' );

            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$initial = this.$('.joms-postbox-inner-panel');
            this.$main = this.$('.joms-postbox-file');

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$preview = this.$('.joms-postbox-preview');
            this.$tabupload = this.$tabs.find('[data-tab=upload]');
            this.$tabmood = this.$tabs.find('[data-tab=mood]');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            this.$dropArea = this.$el.find('#joms-postbox-file--droparea');

            if ( !this.enableMood )
                this.$tabmood.remove();

            this.$uploader = this.$('#joms-postbox-photo-upload');
            this.$uploaderParent = this.$uploader.parent();

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );

            this.uploading = [];

            // initialize uploader
            var url = joms.BASE_URL + 'index.php?option=com_community&view=files&task=multiUpload&type=activities',
                settings = constants.get('settings') || {};

            if ( settings.isGroup )
                url += '&no_html=1&tmpl=component&groupid=' + ( constants.get('groupid') || '' );

            if ( settings.isEvent )
                url += '&no_html=1&tmpl=component&eventid=' + ( constants.get('eventid') || '' ); 

            if ( settings.isPage ) 
                url += '&no_html=1&tmpl=component&pageid=' + ( constants.get('pageid') || '' ); 

            if ( this.inheritPrivacy )
                this.$tabprivacy.css({ visibility: 'hidden' });

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            if ( $.ie ) {
                this.$uploader.appendTo( document.body );
                this.$uploader.show();
            }

            var conf = constants.get('conf') || {};
            this.maxFileSize = 1;
            this.maxFileSize = +constants.get('settings.isProfile') ? conf.file_sharing_activity_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isPage') ? conf.file_sharing_page_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isGroup') ? conf.file_sharing_group_max : this.maxFileSize;
            this.maxFileSize = +constants.get('settings.isEvent') ? conf.file_sharing_event_max : this.maxFileSize;

            this.ext = 'zip';
            this.ext = +constants.get('settings.isProfile') ? conf.file_activity_ext : this.ext;
            this.ext = +constants.get('settings.isPage') ? conf.file_page_ext : this.ext;
            this.ext = +constants.get('settings.isGroup') ? conf.file_group_ext : this.ext;
            this.ext = +constants.get('settings.isEvent') ? conf.file_event_ext : this.ext;

            var upConfig = {
                container: 'joms-postbox-file-upload',
                drop_element: 'joms-postbox-file--droparea',
                browse_button: 'joms-postbox-file-upload-btn',
                url: url,
                filters: [{ title: 'File files', extensions: this.ext }],
                max_file_size: this.maxFileSize + 'mb'
            };

            // resizing on mobile cause errors on android stock browser!
            if ( !$.mobile )
                upConfig.resize = { width: 2100, height: 2100, quality: 90 };

            this.uploader = new Uploader( upConfig );
            this.uploader.onAdded = $.bind( this.onFileAdded, this );
            this.uploader.onError = $.bind( this.onFileError, this );
            this.uploader.onProgress = $.bind( this.onFileProgress, this );
            this.uploader.onUploaded = $.bind( this.onFileUploaded, this );
            this.uploader.init();

            if ( $.ie ) {
                this.$uploader.hide();
                this.$uploader.appendTo( this.$uploaderParent );
            }


            return this;
        },

        showInitialState: function() {
            this.$main.hide();
            this.$initial.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix( true );
            this.inputbox && this.inputbox.single();
            this.preview && this.preview.remove();
            this.preview = false;
            this.showMoreButton();
            DefaultView.prototype.showInitialState.apply( this );
        },

        showMainState: function() {
            DefaultView.prototype.showMainState.apply( this );
            this.$action.hide();
            this.$initial.hide();
            this.$main.show();
            this.$save.show();
            $.ie && ($.ieVersion < 10) && this.ieUploadButtonFix();

            if ( App.postbox && App.postbox.value && App.postbox.value.length ) {
                this.inputbox.set( App.postbox.value[0] );
                App.postbox.value = false;
            }
        },

        showMoreButton: function() {
            this.$tabupload.removeClass('hidden invisible');
        },

        hideMoreButton: function() {
            this.$tabupload.addClass( this.subviews.mood ? 'hidden' : 'invisible' );
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.preview && this.preview.remove();
            this.preview = false;
            this.uploading = [];
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.attachment = {};

            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            if ( this.preview ) {
                $.extend( this.data.attachment, this.preview.value() );
            }

            return DefaultView.prototype.value.apply( this, arguments );
        },

        validate: function() {
            var value = this.value( true ),
                attachment = value[1] || {};

            if ( !attachment.id && attachment.id.length )
                return 'No image selected.';
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // File preview event handlers.
        // ---------------------------------------------------------------------

        onFileAdd: function() {
            if ( this.uploading.length )
                return;

            var conf = constants.get('conf') || {},
                limit = +conf.limitfile,
                uploaded = +conf.uploadedfile,
                num_file_per_upload = +conf.num_file_per_upload,
                curr = this.preview ? this.preview.getNumFiles() : 0;

            if ( curr >= num_file_per_upload ) {
                window.alert( language.get('file.batch_notice') );
                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                return;
            }

            // Opera 12 and lower (Presto engine), and IE 10, cannot open File Dialog without clicking the input[type=file] element.
            if ( window.opera || ($.ie && $.ieVersion === 10) )
                this.$('#joms-postbox-photo-upload').find('input[type=file]').click();
            else
                this.uploader.open();
        },

        onFileAdded: function( up, files ) {
            if ( this.uploading.length )
                return;

            if ( !(files && files.length) )
                return;

            var exts = this.ext,
                maxFileSize = this.maxFileSize;

            files = _.filter( files, function(file) {
                var ex = file.name.split('.').pop();
                return _.contains( exts.split(','), ex );
            });

            files = _.filter( files, function(file) {
                return file.size <= ( maxFileSize * 1024 * 1024 );
            });

            var conf = constants.get('conf') || {},
                num_file_per_upload = +conf.num_file_per_upload,
                limit = +conf.limitfile,
                uploaded = +conf.uploadedfile,
                curr = this.preview ? this.preview.getNumFiles() : 0,
                self = this;

            if ( curr + files.length > num_file_per_upload ) {
                window.alert( language.get('file.batch_notice') );
                _.each( files, function(file) {
                    up.removeFile(file);
                })

                return;
            }

            uploaded += curr;

            if ( uploaded >= limit ) {
                window.alert( language.get('photo.upload_limit_exceeded') || 'You have reached the upload limit.' );
                _.each( files, function(file) {
                    up.removeFile(file);
                })

                return;
            }

            var removed;
            if ( uploaded + files.length > limit ) {
                removed = uploaded + files.length - limit;
                files.splice( 0 - removed, removed );
                up.splice( 0 - removed, removed );
            }

            var div;
            if ( !this.preview ) {
                div = $('<div>').appendTo( this.$preview );
                this.preview = new PreviewView();
                this.assign( div, this.preview );
                this.listenTo( this.preview, 'update', function( num ) {
                    if ( !num || num <= 0 ) {
                        this.showInitialState();
                        this.inputbox.single();
                        this.uploading = [];
                        return;
                    } else if ( num >= 8 ) {
                        this.hideMoreButton();
                    } else {
                        this.showMoreButton();
                    }

                    this.inputbox[ num > 1 ? 'multiple' : 'single' ]();
                } );
            }

            this.showMainState();
            for ( var i = 0; i < files.length; i++ ) {
                this.preview.add( files[i] );
            }

            _.each( files, function(file) {
                self.uploading.push(file.id);
            });

            this.$action.hide();
            up.start();
            up.refresh();
        },

        onFileError: function( up, file ) {
            if ( +file.code === +plupload.FILE_EXTENSION_ERROR ) {
                window.alert( language.get('file.file_type_not_permitted') );
            } else if ( +file.code === +plupload.FILE_SIZE_ERROR ) {
                var msg = '"' + file.file.name + '": ' + language.get('file.max_upload_size_error').replace( '##maxsize##', this.maxFileSize );
                window.alert( msg );
            } else {
                console.log( file.message );
            }
        },

        onFileProgress: function( up, file ) {
            this.preview && this.preview.updateProgress( file );
        },

        onFileUploaded: function( up, file, info ) {
            var json;
            try {
                json = JSON.parse( info.response );
            } catch ( e ) {}

            json || (json = {});

            // onerror
            if ( !json.id ) {
                up.stop();
                up.splice();
                window.alert( json && json.msg || 'Undefined error.' );
                this.uploading = [];
                this.$action.show();
                this.preview && this.preview.removeFailed();
                return;
            }

            this.uploading = _.without( this.uploading, file.id);

            this.uploading.length === 0 && this.$action.show();

            this.preview && this.preview.setFile( file, json );
        },

        // ---------------------------------------------------------------------
        // Drag and drops event handlers.
        // ---------------------------------------------------------------------
        
        onDragEnter: function( e ) {
            e.preventDefault();
            this.$dropArea.show();
            this.$dropArea.css('line-height', this.$dropArea.height() + 'px');
        },

        onDragLeave: function( e ) {
            e.preventDefault();
            this.$dropArea.hide();
        },

        onFileDrop: function( e ) {
            e.preventDefault();
            this.$dropArea.hide();
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onMoodSelect: function( mood ) {
            this.inputbox.updateAttachment( mood );
        },

        onMoodRemove: function() {
            this.inputbox.updateAttachment( false );
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'file' }
            );

            return this.staticAttachment;
        },

        ieUploadButtonFix: function( initialState ) {
            if ( !this.ieUploadButtonFix.init ) {
                this.ieUploadButtonFix.init = true;
                this.$uploader.css({
                    display: 'block',
                    position: 'absolute',
                    opacity: 0,
                    width: '',
                    height: ''
                }).children('button,form').css({
                    display: 'block',
                    position: 'absolute',
                    width: '',
                    height: '',
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }).children('input').css({
                    cursor: 'pointer',
                    height: '100%'
                });
            }

            if ( initialState ) {
                this.$uploader.appendTo( this.$uploaderParent );
                this.$uploader.css({
                    top: 12,
                    right: 12,
                    bottom: 12,
                    left: 12
                }).children('form').css({
                    width: '100%',
                    height: '100%'
                });
            } else {
                this.$uploader.appendTo( this.$tabupload );
                this.$uploader.css({
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                });
            }
        }

    });
});

define('views/inputbox/poll',[
    'views/inputbox/videourl',
    'sandbox',
    'utils/language'
],

// definition
// ----------
function( VideoUrlView, $, language ) {

    return VideoUrlView.extend({

        getTemplate: function() {
            var hint = language.get('poll.title_hint') || 'Poll title',
                html = this.template({ placeholder: hint });

            return $( html );
        }
    })
});

define('views/postbox/poll-options', [
    'sandbox',
    'views/base',
    'utils/language'
],

function($, BaseView, language) {
    return BaseView.extend({

        template: {
            list: _.template(
                '<div class="joms-postbox__poll-option-list"></div>\
                <a href="javascript:;" class="joms-postbox-poll__add-option">+ <%= language.add_option %></a>'
            ),

            option: _.template(
                '<div class="joms-postbox__poll-option">\
                    <input class="input-option poll_option" type="text" name="poll_options[]" placeholder="<%= language.hint_add_option %>" maxlength="100">\
                    <a href="javascript:;" class="joms-postbox-poll__remove-option">\
                        <svg viewBox="0 0 16 16" class="joms-icon">\
                            <use xlink:href="<%= window.joms_current_url %>#joms-icon-close"></use>\
                        </svg>\
                    </a>\
                </div>'
            )
        },

        getTemplate: function(type) {
            var html = this.template[type]({
                language: language.get('poll')
            });
            
            return $(html);
        },

        render: function() {
            this.$el.html( this.getTemplate('list') );

            this.$list = this.$el.find('.joms-postbox__poll-option-list');
            this.$list.append( this.getTemplate('option') );
            this.$list.append( this.getTemplate('option') );
        },

        events: $.extend({}, BaseView.prototype.events, {
            'click .joms-postbox-poll__remove-option': 'removeOption',
            'click .joms-postbox-poll__add-option': 'addOption',
        }),

        removeOption: function(e) {
            $(e.currentTarget).parents('.joms-postbox__poll-option').remove();
        },

        addOption: function(e) {
            this.$list.append( this.getTemplate('option') );
            this.$list.find('input').last().focus();
        },

        reset: function() {
            this.$el.html('');
            this.render();
        },

        value: function() {
            var $inputs = this.$list.find('.input-option'),
                options = [];
            
            $inputs.each( function() {
                var val = $(this).val();
                val && options.push(val)
            });

            return {
                options: options
            };
        }

    })
});


define('views/postbox/poll-settings', [
    'sandbox',
    'views/base',
    'utils/language'
],

function($, BaseView, language) {
    return BaseView.extend({

        template: _.template(
            '<!--<div class="settings-item">\
                <input type="checkbox" name="allow_add">\
                <span style="cursor:pointer" class="settings-item--label">Allow any one to add options</span>\
            </div>-->\
            <div class="settings-item">\
                <input type="checkbox" name="allow_multiple">\
                <span style="cursor:pointer" class="settings-item--label"><%= language.allow_multiple_choices %></span>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                language: language.get('poll')
            });
            return $(html);
        },

        render: function() {
            this.$el.html( this.getTemplate() );
        },

        events: $.extend({}, BaseView.prototype.events, {
            'click .settings-item--label': 'toggleCheckbox',
        }),

        toggleCheckbox: function(e) {
            var $checkbox = $(e.currentTarget).siblings('input[type=checkbox]');

            $checkbox.prop('checked', !$checkbox.prop('checked'));

        },

        reset: function() {
            this.$el.html('');
            this.render();
        },

        value: function() {
            var allow_add = this.$el.find('input[name=allow_add]'),
                allow_multiple = this.$el.find('input[name=allow_multiple]');
            return {
                settings: {
                    allow_add: allow_add.prop('checked'),
                    allow_multiple: allow_multiple.prop('checked')
                }
            }
        }
    })
});

define('views/postbox/poll-time', [
    'sandbox',
    'views/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: _.template(
            '<div class="joms-postbox__poll-time--inner">\
                <span>\
                    <%= language.poll.expired_date %> <span style="color:red">*</span>\
                </span>\
                <input type=text class="joms-input joms-pickadate-enddate" placeholder=" <%= language.poll.epxired_date_hint %>" style="background-color:transparent;cursor:text">\
                <span>\
                    <%= language.poll.expired_time %> <span style="color:red">*</span>\
                </span>\
                <input type=text class="joms-input joms-pickadate-endtime" placeholder=" <%= language.poll.expired_time_hint %>" style="background-color:transparent;cursor:text">\
            </div>'
        ),

        events: {
        },

        render: function() {
            var div = this.getTemplate(),
                ampm = +constants.get('conf.eventshowampm'),
                firstDay = +constants.get('conf.firstday'),
                timeFormatLabel = ampm ? 'h:i A' : 'H:i',
                translations = {},
                categories,
                i;

            // Translations.
            translations.monthsFull = [
                language.get('datepicker.january'),
                language.get('datepicker.february'),
                language.get('datepicker.march'),
                language.get('datepicker.april'),
                language.get('datepicker.may'),
                language.get('datepicker.june'),
                language.get('datepicker.july'),
                language.get('datepicker.august'),
                language.get('datepicker.september'),
                language.get('datepicker.october'),
                language.get('datepicker.november'),
                language.get('datepicker.december')
            ];

            translations.monthsShort = [];
            for ( i = 0; i < translations.monthsFull.length; i++ )
                translations.monthsShort[i] = translations.monthsFull[i].substr( 0, 3 );

            translations.weekdaysFull = [
                language.get('datepicker.sunday'),
                language.get('datepicker.monday'),
                language.get('datepicker.tuesday'),
                language.get('datepicker.wednesday'),
                language.get('datepicker.thursday'),
                language.get('datepicker.friday'),
                language.get('datepicker.saturday')
            ];

            translations.weekdaysShort = [];
            for ( i = 0; i < translations.weekdaysFull.length; i++ )
                translations.weekdaysShort[i] = translations.weekdaysFull[i].substr( 0, 3 );

            translations.today = language.get('datepicker.today');
            translations['clear'] = language.get('datepicker.clear');

            translations.firstDay = firstDay;
            translations.selectYears = 200;
            translations.selectMonths = true;

            this.$el.html( div );

            this.$enddate = this.$('.joms-pickadate-enddate').pickadate( $.extend({}, translations, { format: 'd mmmm yyyy', klass: { frame: 'picker__frame endDate' }, min: true }) );
            this.$endtime = this.$('.joms-pickadate-endtime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame endTime' } });

            this.enddate = this.$enddate.pickadate('picker');
            this.endtime = this.$endtime.pickatime('picker');

            this.enddate.on({ set: $.bind( this.onSetEndDate, this ) });
            this.endtime.on({ set: $.bind( this.onSetEndTime, this ) });

            return this;
        },

        // ---------------------------------------------------------------------

        value: function() {
            if (!this.$enddate.val() && this.data) {
                this.data.enddate = null;    
            }

            if (!this.$endtime.val() && this.data) {
                this.data.endtime = null; 
            }

            return this.data;
        },

        reset: function() {
            this.data = null;
            this.$enddate.val('');
            this.$endtime.val('');
        },

        // ---------------------------------------------------------------------

        onSetEndDate: function( o ) {
            // if (o.hasOwnProperty('clear')) {
            //     return;
            // }

            this.onSave();
        },

        onSetEndTime: function() {
            this.onSave();
        },

        onSave: function() {
            var enddate = this.enddate.get('select'),
                endtime = this.endtime.get('select'),
                error;

            // get end date and time
            enddate && (enddate = [ this.enddate.get('select', 'yyyy-mm-dd'), this.enddate.get('value') ]);
            endtime && (endtime = [ this.endtime.get('select', 'HH:i'), this.endtime.get('value') ]);

            // data
            this.data = {
                enddate   : enddate,
                endtime   : endtime
            };

            // check values
            if ( this.$enddate.val() ) {
                this.$enddate.css('border-color', '');
            } else {
                this.$enddate.css('border-color', '#ec0000');
            }

            if ( this.$endtime.val() ) {
                this.$endtime.css('border-color', '');
            } else {
                this.$endtime.css('border-color', '#ec0000');
            }

            this.trigger( 'select', this.data );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                language: {
                    event: language.get('event') || {},
                    poll: language.get('poll') || {}
                }
            });

            return $( html );
        }
    })
});

define('views/postbox/poll', [
    'sandbox',
    'app',
    'views/postbox/default',
    'views/inputbox/poll',
    'views/widget/select',
    'views/postbox/poll-options',
    'views/postbox/poll-settings',
    'views/postbox/poll-time',
    'views/dropdown/privacy',
    'utils/constants',
    'utils/language'
],

function(
    $,
    App,
    DefaultView,
    InputboxView,
    SelectWidget,
    OptionsView,
    SettingsView,
    PollTimeView,
    PrivacyView,
    constants,
    language
) {
    return DefaultView.extend({
        
        subviews: {
            privacy: PrivacyView
        },

        template: _.template(
            '<div class="joms-postbox-poll">\
                <div class="joms-postbox-inner-panel">\
                    <div class="joms-postbox-inputbox" style=""></div> \
                </div>\
                <div class="joms-postbox-inner-panel">\
                    <div class=joms-postbox-poll-options></div>\
                    <div class=joms-postbox-poll-settings></div>\
                    <div class="joms-postbox-poll-category joms-fetched-category joms-select"></div>\
                    <div class="joms-postbox__poll-time"></div>\
                </div>\
                <nav class="joms-postbox-tab selected"> \
                    <ul class="joms-list inline"> \
                        <li data-tab=privacy>\
                            <svg viewBox="0 0 16 18" class="joms-icon"><use xlink:href="<%= window.joms_current_url %>#joms-icon-earth"></use></svg> \
                            <span class=visible-desktop></span>\
                        </li>\
                    </ul> \
                    <div class=joms-postbox-action> \
                        <button class=joms-postbox-cancel><%= language.postbox.cancel_button %></button> \
                        <button class=joms-postbox-save><%= language.postbox.post_button %></button> \
                    </div> \
                    <div class=joms-postbox-loading style="display:none;"> \
                        <img src="<%= juri.root%>components/com_community/assets/ajax-loader.gif" alt="loader"> \
                    </div> \
                </nav>\
            </div>'
        ),

        getTemplate: function() {
            var html = this.template({
                juri: constants.get('juri'),
                language: {
                    postbox: language.get('postbox') || {}
                }
            });
            return $(html).hide();
        },

        initialize: function() {
            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            DefaultView.prototype.initialize.apply( this );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$inputbox = this.$('.joms-postbox-inputbox');
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');
            this.$tabpolltime = this.$tabs.find('[data-tab=polltime]');
            this.$options = this.$('.joms-postbox-poll-options');
            this.$settings = this.$('.joms-postbox-poll-settings');
            this.$category = this.$('.joms-postbox-poll-category');
            this.$polltime = this.$('.joms-postbox__poll-time');

            if ( this.inheritPrivacy ) {
                if ( this.$tabprivacy.next().length )
                    this.$tabprivacy.remove();
                else
                    this.$tabprivacy.css({ visibility: 'hidden' });
            }

            // inputbox
            this.inputbox = new InputboxView({ attachment: true, charcount: true });
            this.assign( this.$inputbox, this.inputbox );
            this.listenTo( this.inputbox, 'focus', this.onInputFocus );
            this.listenTo( this.inputbox, 'keydown', this.onInputUpdate );
            this.listenTo( this.inputbox, 'paste', this.onInputUpdate );

            // category
            var pollCats = this.sortCategories( constants.get('pollCategories') );
            var pollOptions = pollCats.map( function(item) {
                return [ item.id, item.name];
            });
            this.category = new SelectWidget({ options: pollOptions });
            this.assign( this.$category, this.category );

            // options
            this.options = new OptionsView();
            this.assign( this.$options, this.options) 

            // settings
            this.settings = new SettingsView();
            this.assign( this.$settings, this.settings );

            // poll time
            this.polltime = new PollTimeView();
            this.assign( this.$polltime, this.polltime);

            // init privacy
            var defaultPrivacy, settings;
            if ( !this.inheritPrivacy ) {
                settings = constants.get('settings') || {};
                if ( settings.isProfile && settings.isMyProfile )
                    defaultPrivacy = constants.get('conf.profiledefaultprivacy');
                this.initSubview('privacy', { privacylist: window.joms_privacylist, defaultPrivacy: defaultPrivacy || 'public' });
            }

            if ( this.single )
                this.listenTo( $, 'click', this.onDocumentClick );

            return this;
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.inputbox && this.inputbox.reset();
            this.options && this.options.reset();
            this.settings && this.settings.reset();
            this.polltime && this.polltime.reset();
            this.staticAttachment = null;
        },

        value: function() {
            this.data.text = this.inputbox.value() || '';
            this.data.text = this.data.text.replace( /\n/g, '\\n' );

            this.data.attachment = {};
            this.data.attachment.polltime = this.polltime.value();
            var value;
            for ( var prop in this.subflags )
                if ( value = this.subviews[ prop ].value() )
                    this.data.attachment[ prop ] = value;

            this.data.attachment.catid = this.category.value();

            return DefaultView.prototype.value.apply( this );
        },

        validate: function() {
            var options = this.options.value(),
                text = this.inputbox.value(),
                catid = this.category.value(),
                polltime = this.polltime.value(),
                poll_lang = language.get('poll');
            
            if (!text.trim()) {
                return poll_lang.no_title;
            }

            if (options.options.length < 2) {
                return poll_lang.not_enough_option;
            }

            if (!catid) {
                return poll_lang.no_category;
            }

            if (!polltime) {
                return poll_lang.no_expiry_date;
            }

            if (!polltime.enddate) {
                return poll_lang.no_expiry_date;
            }

            if (!polltime.endtime) {
                return poll_lang.no_expiry_time;
            }

            return null;
        },

        // ---------------------------------------------------------------------
        // Inputbox event handlers.
        // ---------------------------------------------------------------------

        onInputFocus: function() {
            this.showMainState();
        },

        onInputUpdate: function( text, key ) {
            var div;

            text = text || '';
            this.togglePostButton( text );
        },

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        sortCategories: function( categories, parent, prefix ) {
            if ( !categories || !categories.length )
                return [];

            parent || (parent = 0);
            prefix || (prefix = '');

            var options = [];
            for ( var i = 0, id, name; i < categories.length; i++ ) {
                if ( +categories[i].parent === parent ) {
                    id = +categories[i].id;
                    name = prefix + categories[i].name;
                    options.push({ id: id, name: name });
                    options = options.concat( this.sortCategories( categories, id, name + ' &rsaquo; ' ) );
                }
            }

            return options;
        },

        getStaticAttachment: function() {
            if ( this.staticAttachment )
                return this.staticAttachment;

            this.staticAttachment = $.extend({},
                constants.get('postbox.attachment') || {},
                { type: 'poll' },
                this.options.value(),
                this.settings.value()
            );

            return this.staticAttachment;
        },

        togglePostButton: function( text ) {
            var enabled = false;

            if ( text )
                enabled = true;

            if ( !enabled && this.subflags.mood )
                enabled = this.subviews.mood.value();

            if ( !enabled && this.subflags.location )
                enabled = this.subviews.location.value();

            this.$save[ enabled ? 'show' : 'hide' ]();
        }

    });
});
define('views/postbox/custom',[
    'sandbox',
    'views/postbox/default',
    'views/dropdown/privacy',
    'utils/ajax',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function(
    $,
    DefaultView,
    PrivacyView,
    ajax,
    constants,
    language
) {

    return DefaultView.extend({

        subviews: {
            privacy: PrivacyView
        },

        template: joms.jst[ 'html/postbox/custom' ],

        events: $.extend({}, DefaultView.prototype.events, {
            'click .joms-postbox-predefined-message': 'onCustomPredefined',
            'click .joms-postbox-custom-message': 'onCustomCustom',
            'keyup [name=custom]': 'onTextareaUpdate'
        }),

        initialize: function() {
            var settings = constants.get('settings') || {};
            if ( this.inheritPrivacy = (settings.isPage || settings.isGroup || settings.isEvent || !settings.isMyProfile))
                this.subviews = $.omit( this.subviews, 'privacy' );

            DefaultView.prototype.initialize.apply( this );
            this.attachment = { type: 'custom' };
            $.extend( this.attachment, constants.get('postbox.attachment') || {} );
        },

        render: function() {
            DefaultView.prototype.render.apply( this );

            this.$initial = this.$el.children('.joms-postbox-inner-panel');
            this.$main = this.$el.children('.joms-postbox-custom');
            this.$statepredefined = this.$('.joms-postbox-custom-state-predefined');
            this.$statecustom = this.$('.joms-postbox-custom-state-custom');

            this.$predefined = this.$('[name=predefined]');
            this.$custom = this.$('[name=custom]');
            this.$divs = this.$('.joms-postbox-dropdown').hide();
            this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

            if ( this.inheritPrivacy )
                this.$tabprivacy.css({ visibility: 'hidden' });

            // init privacy
            if ( !this.inheritPrivacy ) {
                this.initSubview('privacy', { privacylist: [ 'public', 'site_members' ] });
                this.subviews.privacy.setPrivacy('public');
            }

            return this;
        },

        showInitialState: function() {
            this.$main.hide();
            this.$initial.show();
            DefaultView.prototype.showInitialState.apply( this );
        },

        showMainState: function( predefined ) {
            DefaultView.prototype.showMainState.apply( this );
            predefined ? this.showPredefinedState() : this.showCustomState();
        },

        showPredefinedState: function() {
            this.$initial.hide();
            this.$statepredefined.show();
            this.$statecustom.hide();
            this.$main.show();
            this.$save.show();
            this.predefined = true;
        },

        showCustomState: function() {
            this.$initial.hide();
            this.$statepredefined.hide();
            this.$statecustom.show();
            this.$main.show();
            this.predefined = false;
        },

        // ---------------------------------------------------------------------
        // Data validation and retrieval.
        // ---------------------------------------------------------------------

        reset: function() {
            DefaultView.prototype.reset.apply( this );
            this.$predefined && this.$predefined.val( this.$predefined.find('option:first').val() );
            this.$custom && this.$custom.val('');
        },

        value: function() {
            var data = [];
            if ( this.predefined ) {
                data.push( this.$predefined.val() );
                data.push( this.$predefined.find('option:selected').text() );
            } else {
                data.push( 'system.message' );
                data.push( this.$custom.val().replace( /\n/g, '\\n' ) );
            }

            if ( 'privacy' in this.subflags )
                data.push( this.subviews.privacy.value() );

            return data;
        },

        validate: function() {
            var value = this.value(),
                error;

            if ( this.predefined ) {
                value[0] || (error = 'Predefined message cannot be empty.');
            } else  {
                value[1] || (error = 'Custom message cannot be empty.');
            }

            return error;
        },

        // ---------------------------------------------------------------------
        // Textare event handlers.
        // ---------------------------------------------------------------------

        onTextareaUpdate: function() {
            var value = this.$custom.val();
            value = value.replace( /^\s+|\s+$/g, '' );
            this.$save[ value ? 'show' : 'hide' ]();
        },

        // ---------------------------------------------------------------------
        // Panel event handlers.
        // ---------------------------------------------------------------------

        onCustomPredefined: function() {
            this.showMainState('predefined');
        },

        onCustomCustom: function() {
            this.showMainState();
        },

        onPost: function() {
            if ( this.saving )
                return;

            var error = this.validate();
            if ( error ) {
                window.alert( error );
                return;
            }

            this.saving = true;
            this.$loading.show();

            var that = this;
            ajax({
                fn: 'activities,ajaxAddPredefined',
                data: this.value(),
                success: $.bind( this.onPostSuccess, this ),
                complete: function() {
                    that.$loading.hide();
                    that.saving = false;
                    that.showInitialState();
                }
            });
        },

        // ---------------------------------------------------------------------
        // Dropdowns event handlers.
        // ---------------------------------------------------------------------

        onPrivacySelect: function( data ) {
            var icon = this.$tabprivacy.find('use'),
                href = icon.attr('xlink:href');

            href = href.replace(/#.+$/, '#joms-icon-' + data.icon );

            this.$tabprivacy.find('use').attr( 'xlink:href', href );
            this.$tabprivacy.find('span').html( data.label );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var obj = constants.get('customActivities') || {},
                messages = [];

            for ( var prop in obj )
                messages.push([ prop, obj[ prop ] ]);

            var html = this.template({
                juri: constants.get('juri'),
                messages: messages,
                language: {
                    postbox: language.get('postbox'),
                    custom: language.get('custom')
                }
            });

            return $( html ).hide();
        }

    });

});

define('views/postbox/layout',[
    'sandbox',
    'views/base',
    'views/postbox/status',
    'views/postbox/photo',
    'views/postbox/video',
    'views/postbox/event',
    'views/postbox/file',
    'views/postbox/poll',
    'views/postbox/custom',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function(
    $,
    BaseView,
    StatusView,
    PhotoView,
    VideoView,
    EventView,
    FileView,
    PollView,
    CustomView,
    constants,
    language
) {

    return BaseView.extend({

        subflags: {},

        subviews: {
            status: StatusView,
            photo: PhotoView,
            video: VideoView,
            event: EventView,
            file: FileView,
            poll: PollView,
            custom: CustomView
        },

        events: {
            'click .joms-postbox-tab-root li': 'onChangeTab'
        },

        initialize: function() {
            this.listenTo( $, 'postbox:status', this.onOpenStatusTab );
            this.listenTo( $, 'postbox:photo', this.onOpenPhotoTab );
            this.listenTo( $, 'postbox:video', this.onOpenVideoTab );
            this.listenTo( $, 'postbox:file', this.onOpenFileTab );
            this.listenTo( $, 'postbox:poll', this.onOpenPollTab );
        },

        render: function() {
            var settings = constants.get('settings') || {},
                conf = constants.get('conf') || {};

            if ( !settings.isAdmin || !conf.enablecustoms )
                this.subviews = $.omit( this.subviews, 'custom' );

            if ( settings.isProfile && !settings.isMyProfile )
                this.subviews = $.pick( this.subviews, 'status', 'photo', 'video', 'file', 'poll' );

            if ( settings.isEvent )
                this.subviews = $.omit( this.subviews, 'event' );

            if ( settings.isProfile || settings.isGroup || settings.isEvent || settings.isPage ) {
                conf.enablephotos || (this.subviews = $.omit( this.subviews, 'photo' ));
                conf.enablevideos || (this.subviews = $.omit( this.subviews, 'video' ));
                conf.enableevents || (this.subviews = $.omit( this.subviews, 'event' ));
                conf.enablefiles  || (this.subviews = $.omit( this.subviews, 'file' ));
                conf.enablepolls  || (this.subviews = $.omit( this.subviews, 'poll' ));
            }

            // cache subview keys
            this.subkeys = $.keys( this.subviews );

            // cache elements
            this.$subviews = this.$('.joms-postbox-tabs');
            this.$tab = this.$('.joms-postbox-tab-root').hide();

            // remove unused tab
            var that = this;
            this.$tab.find('li').each(function() {
                var elem = $( this ),
                    key = elem.data('tab');

                if ( that.subkeys.indexOf( key ) < 0 )
                    elem.remove();
            });

            if ( this.subkeys && this.subkeys.length ) 
                this.changeTab( this.subkeys[0] );
        },

        show: function() {
            this.$el[ $.isMobile ? 'show' : 'fadeIn' ]();
        },

        changeTab: function( type ) {
            if ( !this.subviews[ type ] )
                return;

            var elem = this.$tab.find( 'li[data-tab=' + type + ']' );
            if ( elem && elem.length ) {
                elem.hasClass('active') || elem.addClass('active');
                elem.siblings('.active').removeClass('active');
            }

            if ( !this.subflags[ type ] )
                this.initSubview( type );

            for ( var prop in this.subflags )
                if ( prop !== type )
                    this.subviews[ prop ].hide();

            this.subviews[ type ].show();
            this.type = type;
            $.trigger( 'postbox:tab:change', type );
        },

        // ---------------------------------------------------------------------
        // Event handlers.
        // ---------------------------------------------------------------------

        onChangeTab: function( e ) {
            this.changeTab( $( e.currentTarget ).data('tab') );
        },

        onOpenStatusTab: function() {
            this.changeTab('status');
        },

        onOpenPhotoTab: function() {
            this.changeTab('photo');
        },

        onOpenVideoTab: function() {
            this.changeTab('video');
        },

        onOpenFileTab: function() {
            this.changeTab('file');
        },

        onOpenPollTab: function() {
            this.changeTab('poll');
        },

        onShowInitialState: function() {
            if ( this.subkeys.length > 1 )
                this.$tab.show();
        },

        onShowMainState: function() {
            this.$tab.hide();
        },

        // ---------------------------------------------------------------------
        // Lazy subview initialization.
        // ---------------------------------------------------------------------

        initSubview: function( type ) {
            if ( !this.subflags[ type ] ) {
                this.subviews[ type ] = new this.subviews[ type ]({ single: this.subkeys.length <= 1 });
                this.assign( this.getSubviewElement(), this.subviews[ type ] );
                this.listenTo( this.subviews[ type ], 'show:initial', this.onShowInitialState );
                this.listenTo( this.subviews[ type ], 'show:main', this.onShowMainState );
                this.subflags[ type ] = true;
            }
        },

        getSubviewElement: function() {
            var div = $('<div>').hide().appendTo( this.$subviews );
            return div;
        }

    });

});
define('views/stream/filterbar',[
    'sandbox',
    'views/base',
    'utils/ajax'
],

// definition
// ----------
function(
    $,
    BaseView,
    ajax
) {

    return BaseView.extend({

        render: function() {
            this.$btn = $('.joms-activity-filter-action');
            this.$list = $('.joms-activity-filter-dropdown');
            this.$options = $('.joms-activity-filter__options');

            this.$btn.on( 'click', $.bind( this.toggle, this ) );
            this.$list.on( 'click', 'li', $.bind( this.select, this ) );
            this.$list.on( 'change', 'select', $.bind( this.filterChange, this ) );
            this.$list.on( 'keyup', 'input[type=text]', $.bind( this.filterKeyup, this ) );
            this.$list.on( 'click', 'button.joms-button--primary', $.bind( this.filterSearch, this ) );

            this.$options.find('li').not('.noselect').addClass('joms-js-filterbar-item');
            $( document ).on( 'click', '.joms-js-filterbar-item', $.bind( this.makeDefault, this ));

            this.listenTo( $, 'click', this.onDocumentClick );
        },

        toggle: function() {
            var collapsed = this.$list[0].style.display === 'none';
            collapsed ? this.expand() : this.collapse();
        },

        expand: function() {
            this.$list.show();
        },

        collapse: function() {
            this.$list.hide();
        },

        select: function( e ) {
            var li = $( e.currentTarget ),
                url = li.data('url') || '/',
                filter = li.data('filter');

            if ( filter === '__filter__' ) {
                return;
            }

            this.toggle();
            window.location = url;
        },

        filterChange: function( e ) {
            var value = e.target.value,
                $input = this.$list.find('input[type=text]'),
                $button = this.$list.find('.joms-button--primary');

            if ( value === 'hashtag' || value === 'keyword' ) {
                $input.attr( 'placeholder', $input.data('label-' + value) );
                $button.html( $button.data('label-' + value) );
            }
        },

        filterKeyup: function( e ) {
            var input = $( e.currentTarget ),
                value = input.val(),
                newValue = value;

            newValue = newValue.replace( /#/g, '' );
            if ( newValue.length !== value.length ) {
                input.val( newValue );
            }
        },

        filterSearch: function( e ) {
            var btn, li, filter, input, value, url;

            e.preventDefault();
            e.stopPropagation();

            btn = $( e.currentTarget );
            li = btn.closest('li');
            filter = li.find('select').val();
            input = li.find('input');
            value = input.val().replace(/^\s+|\s+$/g, '');

            if ( !value ) {
                return;
            }

            if ( filter === 'hashtag' ) {
                value = value.split(' ');
                value = value[0];
            }

            url = li.data('url'),
            url = url.replace( '__filter__', filter );
            url = url.replace( '__value__', value );
            window.location = url;
        },

        makeDefault: function( e ) {
            var btn, value, loading, json;

            e.preventDefault();
            e.stopPropagation();

            btn = $( e.currentTarget );
            value = btn.find('a').data('value');
            loading = this.$options.find('.noselect > img');

            if ( loading.css('visibility') !== 'hidden' )
                return;

            loading.css( 'visibility', 'visible' );
            json = {};

            ajax({
                fn: 'system,ajaxDefaultUserStream',
                data: [ value ],
                success: function( resp ) {
                    if ( resp ) {
                        json = resp;
                    }
                },
                complete: $.bind(function() {
                    if ( json.error ) {
                        joms.popup.info( 'Error', json.error );
                    } else if ( json.success ) {
                        joms.popup.info( '', json.message );
                        this.$options.find('.joms-dropdown').hide();
                        btn.addClass('active').siblings('li').removeClass('active');
                        loading.css( 'visibility', 'hidden' );
                    }
                }, this )
            });
        },

        onDocumentClick: function( elem ) {
            if ( elem.closest('.joms-activity-filter').length )
                return;

            this.collapse();
        }

    });

});

define('views/stream/layout',[
    'sandbox',
    'views/base',
    'views/stream/filterbar'
],

// definition
// ----------
function(
    $,
    BaseView,
    FilterbarView
) {

    return BaseView.extend({

        initialize: function() {
            this.filterbar = new FilterbarView();
        },

        render: function() {
            this.filterbar.render();
        }

    });

});

window.joms_init_postbox = function() {
    
    require([
       'jst',
       'sandbox',
       'views/postbox/layout',
       'views/stream/layout',
       'utils/constants'
    ],

    // description
    // -----------
    function( jst, $, PostboxView, StreamView, constants ) {
        
        function initPostbox() {
            var el = $('.joms-postbox'),
                postbox;

            if ( el.length ) {
                postbox = new PostboxView({ el: el  });
                postbox.render();
                postbox.show();
            }
        }

        function initStream() {
            var stream = new StreamView();
            stream.render();
        }

        function fetchAllFriends() {
            // var url   = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&allfriends=1',
            //  settings = constants.get('settings') || {},
            //  data     = [];

            constants.set( 'friends', 'fetching' );
            // if ( settings.isGroup ) url += '&groupid=' + constants.get('groupid');
            // else if ( settings.isEvent ) url += '&eventid=' + constants.get('eventid');

            var timer = window.setInterval(function() {
                if ( window.joms_friends ) {
                    window.clearInterval( timer );
                    constants.set( 'friends', window.joms_friends );
                }
            }, 200 );
        }

        initPostbox();
        initStream();

        if ( +window.joms_my_id )
            fetchAllFriends();

    });
};
define("bundle", function(){});

}());