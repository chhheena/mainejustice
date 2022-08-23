import Vue from 'vue';
import Vuex from 'vuex';
import status from './status';
import photo from './photo';
import file from './file';
import video from './video';
import event from './event';
import poll from './poll';
import custom from './custom';
import {constants} from '../../../utils/constants';
import language from '../../../utils/language';

Vue.use(Vuex);

const $ = jQuery;
const DATA = Joomla.getOptions('com_community');
const currentUrl = DATA.current_url;
const privacies = [
    {
        name: 'public',
        title: language('privacy.public'),
        desc: language('privacy.public_desc'),
        icon: 'earth',
        icon_url: currentUrl + '#joms-icon-earth',
        value: '10',
    },
    {
        name: 'site_members',
        title: language('privacy.site_members'),
        desc: language('privacy.site_members_desc'),
        icon: 'users',
        icon_url: currentUrl + '#joms-icon-users',
        value: '20',
    },
    {
        name: 'friends',
        title: language('privacy.friends'),
        desc: language('privacy.friends_desc'),
        icon: 'user',
        icon_url: currentUrl + '#joms-icon-user',
        value: '30',
    },
    {
        name: 'me',
        title: language('privacy.me'),
        desc: language('privacy.me_desc'),
        icon: 'lock',
        icon_url: currentUrl + '#joms-icon-lock',
        value: '40',
    },
];

const bgs = constants.get('conf.enablebackground') ? constants.get('backgrounds') : [];
const isTouch = !!("ontouchstart" in window || (window.DocumentTouch && document instanceof DocumentTouch));

function initVideoPlayers() {
    const initialized = '.joms-js--initialized';
    const cssVideos = '.joms-js--video';
    const videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

    if ( !videos.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    videos.on( 'click.joms-video', cssVideos + '-play', function() {
        const $el = $( this ).closest( cssVideos );
        joms.util.video.play( $el, $el.data() );
    });
}

function parseResponse( response ) {
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
}

function onPostSuccess( response ) {
    var html = parseResponse( response ),
        stream;

    if ( html ) {
        stream = $('.joms-stream__wrapper').first();
        stream.html( html );
        joms.parseEmoji();

        // reinitialize activity stream
        if ( window.joms && joms.view && joms.view.streams ) {
            joms.view.streams.start();
            joms.view.misc.fixSVG();
        }
    }
}

export default new Vuex.Store({
    strict: true,
    state: {
        bgs,
        privacies,
        activeTab: 'status',
        free: true,
        loading: false,
        isTouch,
        filter_config: {
            filter: '',
            value: 'default_value',
            hashtag: false,
        },
        numCharLeft: 0,
    },

    actions: {
        post({commit}, rawData) {
            commit('setLoading', true);

            const data = rawData.map( item => {
                return item ? item.trim() : '';
            });
            return new Promise((resolve, reject) => {
                joms.ajax({
                    func: 'system,ajaxStreamAdd',
                    data: data,
                    callback: res => {
                        onPostSuccess(res);

                        if (+window.joms_infinitescroll) {
                            $('.joms-stream__loadmore').find('a').hide();
                        }

                        initVideoPlayers();
                        commit('setLoading', false);

                        resolve(res);
                    }
                })
            });
        },

        postAdminAnnouncement({commit}, rawData) {
            commit('setLoading', true);

            const data = rawData.map( item => item.trim());
            return new Promise((resolve, reject) => {
                joms.ajax({
                    func: 'activities,ajaxAddPredefined',
                    no_html: 1,
                    data: data,
                    callback: res => {
                        onPostSuccess(res);

                        if (+window.joms_infinitescroll) {
                            $('.joms-stream__loadmore').find('a').hide();
                        }

                        initVideoPlayers();
                        commit('setLoading', false);

                        resolve(res);
                    }
                })
            });
        },
    },

    mutations: {
        setActiveTab(state, tab) {
            state.activeTab = tab;
        },
    
        setFree(state, value) {
            state.free = value;
        },

        setLoading(state, value) {
            state.loading = !!value;
        },
            
        setNumCharLeft(state, num) {
            state.numCharLeft = num;
        },
    },
    
    modules: {
        status,
        photo,
        file,
        video,
        event,
        poll,
        custom,
    },
});