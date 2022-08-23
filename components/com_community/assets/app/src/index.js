import parseEmoji from './utils/parseEmoji';

if (!window.joms) {
    window.joms = {};
}

window.joms.parseEmoji = parseEmoji;

document.addEventListener('DOMContentLoaded', () => {
    window.joms.parseEmoji();
})

const DATA = Joomla.getOptions('com_community');
__webpack_public_path__ = DATA.assets_url + 'app/dist/';

document.addEventListener('JomsFriendsFetched', () => {
    if (document.querySelector('.joms-postbox')) {
        import(/* webpackChunkName: 'postbox' */ './views/postbox').then(resolve => {
            resolve.default();
        })
    }

    if (document.querySelector('.joms-activity-filter')) {
        import(/* webpackChunkName: 'stream-filter' */ './views/steam-filter').then(resolve => {
            resolve.default();
        })
    }
});
