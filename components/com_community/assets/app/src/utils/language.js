export default function (key) {
    if (typeof key !== 'string' || !key.length)
        return;

    var data = joms.language;

    key = key.split('.');
    while (key.length) {
        data = data[key.shift()];
    }

    return data;
}