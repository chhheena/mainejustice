;(function( root, undef ) {

/**
 * Cache $LAB.js instance.
 * @type {object} joms.$LAB - Local copy of $LAB.js.
 */
root.joms.$LAB = root.$LAB;
root.$LAB = root.joms_cache_$LAB;
delete root.joms_cache_$LAB;

/**
 * Cache jQuery instance.
 * @type {object} joms.jQuery
 */
root.joms.jQuery = undef;

/**
 * Cache Hammer.js instance.
 * @type {object} joms.Hammer
 */
root.joms.Hammer = undef;

/**
 * Cache Underscore.js instance.
 * @type {object} joms._
 */
root.joms._ = undef;

/**
 * Cache Backbone.js instance.
 * @type {object} joms.Backbone
 */
root.joms.Backbone = undef;

/**
 * Attach additional ajax response.
 * @function joms.onAjaxReponse
 * @param {function}
 */
root.joms.onAjaxReponse = function( id, callback ) {
    if ( !root.joms._onAjaxReponseQueue ) root.joms._onAjaxReponseQueue = [];
    if ( !root.joms._onAjaxReponseQueue[ id ] ) root.joms._onAjaxReponseQueue[ id ] = [];
    root.joms._onAjaxReponseQueue[ id ].push( callback );
};

/**
 * Attach function to trigger when application is starting.
 * @function joms.onStart
 * @param {function}
 */
root.joms.onStart = function( fn ) {
    if ( root.joms._onStartQueue === undef ) root.joms._onStartQueue = [];
    if ( root.joms._onStartStarted ) fn( root.joms.jQuery );
    else root.joms._onStartQueue.push( fn );
};

/**
 * Triggers start application.
 * @function joms.start
 */
root.joms.start = function() {
    if ( root.joms_queue && root.joms_queue.length ) {
        if ( root.joms._onStartQueue === undef ) root.joms._onStartQueue = [];
        root.joms._onStartQueue = root.joms_queue.concat( root.joms._onStartQueue );
        root.joms_queue = [];
    }

    if ( root.joms._onStartQueue !== undef ) {
        while ( root.joms._onStartQueue.length ) {
            try {
                ( root.joms._onStartQueue.shift() )( root.joms.jQuery );
            } catch (e) {}
        }
    }

    root.joms._onStartStarted = true;
};

/**
 * Fix some ui quirks which cannot be handled with PHP or CSS.
 * @todo Should be moved elsewhere instead of in loader.js
 * @function joms.fixUI
 */
root.joms.fixUI = function() {

    // Remove empty module wrappers.
    var tabbed = document.getElementsByClassName('joms-module__wrapper'),
        stacked = document.getElementsByClassName('joms-module__wrapper--stacked'),
        sidebar, main, mobile, cname, i;

    for ( i = tabbed.length - 1; i >= 0; i-- ) {
        if ( ! tabbed[i].innerHTML.match(/joms-tab__content|joms-js--app-new/) ) {
            tabbed[i].parentNode.removeChild( tabbed[i] );
        }
    }

    for ( i = stacked.length - 1; i >= 0; i-- ) {
        if ( ! stacked[i].innerHTML.match(/joms-module--stacked|joms-js--app-new/) ) {
            stacked[i].parentNode.removeChild( stacked[i] );
        }
    }

    // Remove sidebar if no modules there.
    sidebar = document.getElementsByClassName('joms-sidebar')[0];
    if ( sidebar && ( ! sidebar.innerHTML.match(/joms-module|app-position/) ) ) {
        main = document.getElementsByClassName('joms-main')[0];
        sidebar.parentNode.removeChild( sidebar );
        if ( main ) {
            main.className += ' joms-main--full';
        }
    }

    // Assumes non-touchable for non-mobile browsers.
    mobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
    if ( !mobile.test( navigator.userAgent ) ) {
        cname = document.documentElement.className || '';
        document.documentElement.className = cname + (cname.length ? ' ' : '') + 'joms-no-touch';
    }

    // Needs to add wrapper to .joms-select.
    joms.onStart(function( $ ) {
        $(function() {
            $('.joms-select').each(function() {
                var el = $(this),
                    multiple;

                if ( !el.parent('.joms-select--wrapper').length ) {
                    multiple = el.attr('size') || el.attr('multiple');
                    el.wrap( '<div class="joms-select--wrapper' + (multiple ? ' joms-select--expand' : '') + '"></div>' );
                }
            });
        });
        
        if (location.href.includes("oauth_verifier") && location.href.includes("twitterlogin")){joms.api.fbcUpdate();}
        else if (location.href.includes("linkedincode")) {joms.api.fbcUpdate();}
        else if (location.href.includes("googleid")) {joms.api.fbcUpdate();}
    });
};

root.joms.ie  = !!navigator.userAgent.match(/Trident.*rv\:11\./); // joms only support ie 11

var getOptions = root.Joomla && root.Joomla.getOptions,
    DATA;

// Retrieve all data and language translations.
DATA = getOptions && getOptions( 'com_community' ) || (root.joms_data || {});

// Deprecated!
root.joms_lang = DATA.translations || {};

// Data getter.
root.joms.getData = function( key ) {
    return DATA[ key ];
}

root.joms.getLayout = function( key ) {
    var layouts = DATA.layouts || {};
    return layouts[key];
}

// FROM THIS POINT BELOW IS A JOMSOCIAL JAVASCRIPT LOADING SEQUENCE!

// Path mapper.
var path_source  = 'source/js/',
    path_release = 'release/js/',
    path_vendors = 'vendors/',
    path_jquery  = path_vendors + 'jquery.min.js',
    path_require = path_vendors + 'require.min.js',
    path_bundle  = 'bundle.js';

// Loading sequence.
function load() {
    var isdev = root.joms.DEBUG,
        relpath = root.joms_assets_url || '',
        relpath_bundle = relpath + ( isdev ? path_source : path_release ) + path_bundle;

    root.joms.$LAB
        // Load jQuery.
        .script(function() { return root.jQuery ? null : ( relpath + path_jquery ); })
        .wait(function() { root.joms_init_toolkit(); })
        .wait( postLoad )
        // Load RequireJS on development environment.
        .script(function() { return isdev ? ( relpath + path_require ) : null; })
        .wait(function() { isdev && require.config({ baseUrl: relpath + path_source }); })
        // Load bundled script.
        .script( relpath_bundle )
      
        // Load legacy scripts.
        .wait(function() {
            root.joms_init_postbox();
            root.joms.misc.view.fixSVG();
           
        });
}

// Post-loading initialization.
function postLoad() {
    root.joms.jQuery = root.jQuery;
    root.joms.loadCSS = root.loadCSS;

    root.joms.Hammer = root.Hammer;
    root.Hammer = root.joms_cache_Hammer;
    delete root.joms_cache_Hammer;

    root.joms._ = root._; // .noConflict();
    root.joms.Backbone = root.Backbone.noConflict();
    root.joms.Backbone.$ = root.joms.jQuery;
}

// EXECUTE LOADING SEQUENCE!

document.addEventListener("DOMContentLoaded", function() {
    if ( root.joms_assets_url ) {
        load();
       
    } else {
        root.joms.warn( 'Variable `joms_assets_url` is not defined.' );
    }
});

})( this );

joms || (joms = {});
joms.map = {

    // callback queue
    queue: [],

    // google map proxy
    execute: function( callback ) { 
        
        if(this.type == "openstreetmap"){
            this.executeOpenStreetmap(callback);
        }else{
            this.executeGoogle(callback);
        }
      
    },
    
    executeGoogle: function(callback){
        if ( window.google && window.google.maps && window.google.maps.places ) {
            callback();
            return;
        }

        if ( joms.map.loading ) {
            joms.map.queue.push( callback );
            return;
        }

        joms.map.loading = true;
        joms.map.queue.push( callback );
        joms.map.loadScript();
    },
    
    executeOpenStreetmap: function(callback){
        if ( window.L && window.L.map) {
            callback();
            return;
        }

        if ( joms.map.loading ) {
            joms.map.queue.push( callback );
            return;
        }

        joms.map.loading = true;
        joms.map.queue.push( callback );
        joms.map.loadScriptOpenstreet();
    },

    // google map library loader
    loadScript: function() { 
        if(this.type == "openstreetmap"){
            this.executeOpenStreetmap();
        }else{
            this.loadScriptGoogle();
        }
    },

    loadScriptGoogle: function(){

        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = '//maps.googleapis.com/maps/api/js?libraries=places&key='+joms_gmap_key+'&sensor=false&callback=joms.map.loadScriptCallback';
        document.body.appendChild(script);

    },

    loadScriptOpenstreet: function(){
        var r ;
        r = false;
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "https://unpkg.com/leaflet@1.3.4/dist/leaflet.js";

        document.body.appendChild(script);
        
        script.onload = script.onreadystatechange = function() {
            
            if (!r && (!this.readyState || this.readyState == 'complete')) {
                 r = true;
                joms.map.loadScriptCallback();
            }
        };

        //script = document.createElement("script");
        //script.type = "text/javascript";
        //script.src = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster-src.js";

       //document.body.appendChild(script);
        var script2 = document.createElement("script");
        if(typeof openstreetmap == "undefined"){
                script2 = document.createElement( 'script' );
                script2.type = 'text/javascript';
                script2.src = joms.ASSETS_URL+'source/js/utils/openstreet.js';
            
                document.body.appendChild( script2 );
        }
        jQuery("<link>")
                .appendTo("head")
                .attr({
                    type: "text/css",
                    rel: "stylesheet",
                            href: "https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
                });
         jQuery("<link>")
                .appendTo("head")
                .attr({
                    type: "text/css",
                    rel: "stylesheet",
                            href: "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css"
                });
         jQuery("<link>")
                .appendTo("head")
                .attr({
                    type: "text/css",
                    rel: "stylesheet",
                            href: "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css"
                }); 
        

    },

    // callback for google map library loader
    loadScriptCallback: function() {
        if ( joms.map.queue && joms.map.queue.length )
            while ( joms.map.queue.length )
                ( joms.map.queue.shift() )();

        joms.map.loading = false;
    }

};

