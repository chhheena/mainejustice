/**
 * store.js
 * Copyright (c) 2010-2017 Marcus Westin 
 * @license MIT https://github.com/marcuswestin/store.js/blob/master/LICENSE
 */
import localStorage from 'store';
import localStorageEventsPlugin from 'store/plugins/events';

/**
 * moment
 * Copyright (c) JS Foundation and other contributors
 * @license MIT https://github.com/moment/moment/blob/develop/LICENSE
 */
import moment from 'moment';

/**
 * vue
 * (c) opyright (c) 2013-present, Yuxi (Evan) You
 * @license MIT https://github.com/vuejs/vue/blob/dev/LICENSE
 */
import Vue from 'vue';

/**
 * vuex v3.0.1
 * (c) 2017 Evan You
 * @license MIT https://github.com/vuejs/vue/blob/dev/LICENSE
 */
import Vuex from 'vuex';

/**
 * autosize
 * Copyright (c) 2015 Jack Moore
 * @license MIT https://github.com/jackmoore/autosize/blob/master/LICENSE.md
 */
import autosize_textarea from 'autosize';

(function( root, $, factory ) {
    root.joms = root.joms || {};
    root.joms = $.extend( root.joms, factory( root ) );
})( window, jQuery, function( root ) {
    let map = Array.prototype.map,
        langDate;

    // Configuration for store.js
    localStorage.addPlugin( localStorageEventsPlugin );

    // Configuration for moment.js
    langDate = root.joms_lang.date || {};
    moment.defineLocale( 'jomsocial', {
        parentLocale: 'en',
        months: langDate.months,
        monthsShort: map.call( langDate.months, function( s ) { return s.substr( 0, 3 ) }),
        weekdays: langDate.days,
        weekdaysShort: map.call( langDate.days, function( s ) { return s.substr( 0, 3 ) }),
        weekdaysMin: map.call( langDate.days, function( s ) { return s.substr( 0, 2 ) })
    })

    // Configuration for Vue.js
    Vue.use( Vuex );

    return {
        localStorage,      /* deprecated -> */ storage: localStorage,
        moment,
        Vue,
        Vuex,
        autosize_textarea
    };
});
