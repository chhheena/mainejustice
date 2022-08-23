const DATA = jQuery.extend(true, {}, joms && joms.constants);

export const constants = {
    get( key ) {
        if ( typeof key !== 'string' || !key.length )
        return;
        
        let data = DATA;

        key = key.split('.');
        while ( key.length ) {
            data = data[ key.shift() ];
        }

        return data;
    },

    set( key, value ) {
        if ( typeof key !== 'string' || !key.length )
            return;

        let data = DATA;
        let keys = key.split('.');
        let k;

        for (let idx = 0; idx < keys.length; idx++) {
            k = keys[idx];
            if (idx !== keys.length - 1) {
                data = data[k];
            }
        }

        data[ k ] = value;
    }
}