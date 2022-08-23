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
