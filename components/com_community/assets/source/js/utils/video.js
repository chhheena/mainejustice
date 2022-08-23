(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.video = factory( root, $ );

    define([ 'utils/loadlib' ], function() {
        return joms.util.video;
    });

})( window, joms.jQuery, function( window, $ ) {

var player = {};

player._initMediaElement = function( id, type, data ) {
    if ( type === 'file' && data.fileType === 'flv' ) {
        joms.util.loadLib( 'flowplayer', function () {
            window.flowplayer( id, {
                    src: joms.ASSETS_URL + 'flowplayer/flowplayer-3.2.7.swf',
                    wmode: 'opaque'
                }, {
                    streamingServer: 'lighttpd',
                    playlist: [{
                        url: data.filePath,
                        autoPlay: false,
                        autoBuffering: true,
                        provider: 'lighttpd',
                        scaling: 'scale'
                    }],
                    plugins: {
                        lighttpd: {
                            url: joms.ASSETS_URL + 'flowplayer/flowplayer.pseudostreaming-3.2.7.swf',
                            queryString: window.escape('?target=${start}')
                        },
                        controls: {
                            url: joms.ASSETS_URL + 'flowplayer/flowplayer.controls-3.2.5.swf'
                        }
                    }

                }
            );
        });

    } else {
        joms.util.loadLib( 'mediaelement', function () {
            var $elem = $( '#' + id ).css({ visibility: '' });

            var options = {
                iPadUseNativeControls: type === 'file' ? true : false,
                iPhoneUseNativeControls: type === 'file' ? true : false,
                success: function( me, el, pl ) {
                    setTimeout(function() {
                        pl.disableControls();
                        pl.enableControls();
                    }, 1 );

                    if ( me.pluginType === 'flash' ) {
                        me.addEventListener( 'canplay', function() {
                            me.play();
                        }, false );
                    } else if ( joms.mobile && ( ( me.pluginType === 'youtube' ) || ( me.pluginType === 'vimeo' ) ) ) {
                        // do nothing
                    } else {
                        me.play();
                    }
                }
            };

            // #638 Play video on firefox is not as good as on chrome.
            if ( type === 'youtube' ) {
                options.defaultVideoWidth = $elem.width();
                options.defaultVideoHeight = $elem.height();
            }

            $elem.mediaelementplayer( options );
        });
    }
}

player.responsivePlayer = function( $ct, data) {
    var $media = $ct.find('.joms-media__thumbnail'),
        $player = $('<div class="joms-media__responsive-player"></div>'),
        maxHeight = 500,
        ctWidth,
        height, width, ratio;

    ctWidth = $ct.width();

    ratio = ( data.width / data.height ) || 16/9;

    height = ctWidth / ratio;

    if (height > maxHeight) {
        height = maxHeight;
        width = height * ratio;
    } else {
        width = ctWidth;
    }

    $player.css({
        width: width,
        height: height,
        margin: '0 auto'
    });

    $ct.addClass('being-played');
    $player.html('<iframe src="'+ data.src +'" width="'+width+'" height="'+height+'" '+ data.options +'"></iframe>');
    
    $media.html($player);
}

player._play_file = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        $video, id, fileType;

    id = joms._.uniqueId('joms-js--video-');
    fileType = data.path.match(/\.flv$/) ? 'flv' : 'mp4';

    if ( fileType === 'flv' ) {
        $video = $(
            '<div class="flowplayer" id="' + id + '" style="width:100%;height:281px;"></div>');
    } else {
        $video = $(
            '<video id="' + id + '" width="480" height="360" preload="metadata" autoplay="autoplay">' +
            '<source src="' + data.path + '" type="video/mp4" />' +
            '</video>'
        );
    }

    $ct.addClass('being-played');
    $player.html( $video );
    player._initMediaElement( id, data.type, {
        fileType: fileType,
        filePath: data.path
    });
}

player._play_youtube = function( $ct, data ) {
    window.joms_videoplayer_native ?  player._play_YoutubeNativePlayer( $ct, data ) : player._play_YoutubeJomsPlayer( $ct, data );
}

player._play_YoutubeJomsPlayer = function( $ct, data ) {
    var id, path, $video, $player;

    id = joms._.uniqueId('joms-js--video-');

    path = data.path;
    if (joms.ios) {
        path = path.replace(/#.*$/, '');
        path = path.replace(/&t=\d+/, '');
    }

    $video = $(
        '<video id="' + id + '" controls="control" preload="none">' +
        '<source src="' + path + '" type="video/youtube" />' +
        '</video>'
    );

    $video.css({ visibility: 'hidden' });

    $player = $ct.find('.joms-media__thumbnail');
    if ( ! $player.length ) {
        $player = $ct;
    }

    $ct.addClass('being-played');
    $player.html( $video );
    player._initMediaElement( id, data.type );
}

player._play_YoutubeNativePlayer = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');
    if ( ! $player.length ) {
        $player = $ct;
    }

    $ct.addClass('being-played joms-media--video-native');
    $player.html(
        '<iframe src="//www.youtube.com/embed/' + data.id +
        '?autoplay=1&rel=0" width="500" height="281" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>'
    );
}

player._play_vimeo = function( $ct, data ) {
    data.src = '//player.vimeo.com/video/' + data.id + '?autoplay=1';
    data.options = 'frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen';
    
    player.responsivePlayer( $ct, data );
}

player._play_myspace = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'https://media.myspace.com/play/video/'+data.id;
    
    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" width="500" height="281" AllowFullScreen></iframe>');
}

player._play_blip = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//blip.tv/play/' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

player._play_dailymotion = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');

    $ct.addClass('being-played');
    $player.html( '<iframe frameborder="0" width="500" height="270" src="//www.dailymotion.com/embed/video/'+data.id+'?autoPlay=1" allowfullscreen="" allow="autoplay"></iframe>' );
}


player._play_liveleak = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//www.liveleak.com/ll_embed?i=' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

player._play_yahoo = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = data.path;

    path = path.replace('www.yahoo.com/movies/v', 'movies.yahoo.com/video');
    path = path + '?format=embed&player_autoplay=true';

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" allowtransparency="true"></iframe>' );
}

player._play_metacafe = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'http://www.metacafe.com/embed/' + data.id + '/';

    $ct.addClass('being-played');
    $player.html( '<iframe src="' + path + '" width="500" height="281" frameborder="0" allowfullscreen></iframe>' );
}

player._play_funnyordie = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//www.funnyordie.com/embed/' + data.id + '/';

    $ct.addClass('being-played');
    $player.html( '<iframe src="'+path+'" width="500" height="300" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>' );
}

player._play_collegehumor = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'http://www.collegehumor.com/e/' + data.id;

    $ct.addClass('being-played');
    $player.html( '<iframe src="'+path+'" width="500" height="281" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>' );
    
}

player._play_flickr = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'https://www.flickr.com/photos/'+data.id;

    $ct.addClass('being-played');
    $player.html('<a data-flickr-embed="true" href="'+path+'" ><img src="https://farm2.staticflickr.com/1729/42437754852_936a2d5e9f_b.jpg" width="1024" height="576" alt="flickr"></a><script async src="https://embedr.flickr.com/assets/client-code.js" charset="utf-8"></script>');
}

player._play_dotsub = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'https://dotsub.com/media/'+data.id+'/embed/';
    
    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" width="500" height="281" AllowFullScreen></iframe>');
}

player._play_gloria = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'https://gloria.tv/video/'+data.id+'/embed/';
    
    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" width="500" height="265" AllowFullScreen></iframe>');
}

player._play_sapo = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'http://rd.videos.sapo.pt/playhtml?file=http://rd.videos.sapo.pt/'+data.id+'/mov/1';
    
    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" width="500" height="265" AllowFullScreen></iframe>');
}

player._play_facebook = function( $ct, data ) {
    console.log(data);
    data.src = 'https://www.facebook.com/plugins/video.php?href='+encodeURIComponent('https://www.facebook.com/watch/?v='+data.id) +'&show_text=false';
    data.options = 'style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"';
    
    player.responsivePlayer( $ct, data );
}

player._play_soundcloud = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/'+data.id+'&color=%23ff5500&auto_play=true&hide_related=false&show_comments=false&show_user=false&show_reposts=false&show_teaser=true&visual=true';

    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" ></iframe>');
}

player._play_godtube = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        splitted = data.id.split('|'),
        videoID = splitted[0],
        type = splitted[1],
        embedID = splitted[2];

    if (type === 'godtube') {
        $ct.addClass('being-played');
        var path = 'https://www.godtube.com/embed/watch/'+splitted[0].toLowerCase()+'/?w=728&h=408&ap=true&sl=true&title=true&dp=true';
        $player.html('<iframe width="500" height="250" aspectratio="16:9" frameborder="0" scrolling="no" src="'+path+'"></iframe>"');
    } 
    else if (type === 'youtube') {
        $ct.addClass('being-played');
        var xdata = {
            id: embedID,
            path: 'https://www.youtube.com/watch?v='+embedID,
            type: type
        }
        player._play_youtube( $ct, xdata );
    }
    else {
        player._play_other( $ct, data );
    }

}

player._play_rutube = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path = '//rutube.ru/play/embed/'+data.id;

    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" width="500" height="265" AllowFullScreen></iframe>');
}

player._play_ku6 = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');

    $ct.addClass('being-played');
    $player.html('<iframe src="'+data.id+'" frameborder="0" width="500" height="265" AllowFullScreen></iframe>');
}

player._play_twitch = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        videoid = data.id.split('|')[0],
        type = data.id.split('|')[1],
        path;
    
    $ct.addClass('being-played');
    if (type === 'clip') {
        path ='https://clips.twitch.tv/embed?autoplay=true&clip=' + videoid + '&parent=' + location.hostname;
    } else {
        path ='https://player.twitch.tv/?autoplay=true&video=' + videoid + '&parent=' + location.hostname;
    }
    
    $player.html('<iframe src="'+path+'" frameborder="0" allowfullscreen="true" scrolling="no" height="265" width="500"></iframe>');
}

player._play_flic = function( $ct, data ) {
    player._play_flickr( $ct, data );
}

player._play_vbox7 = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail'),
        path ='https://www.vbox7.com/emb/external.php?vid=' + data.id + '&autoplay=1';

    $ct.addClass('being-played');
    $player.html('<iframe src="'+path+'" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen height="265" width="500"></iframe>');
}

player._play_veoh = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');
    $ct.addClass('being-played');
    $player.html([
        '<object width="500" height="280">',
			'<embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1509&permalinkId=' + data.id + '&player=videodetailsembedded&videoAutoPlay=1&id=anonymous"',
				'type="application/x-shockwave-flash"',
				'allowscriptaccess="always"',
				'allowfullscreen="true"',
				'width="500"',
				'height="280"',
				'>',
			'</embed>',
		'</object>'
    ].join(''));
}

player._play_videa = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');
    $ct.addClass('being-played');
    $player.html('<iframe width="500" height="280" src="//videa.hu/player?v='+data.id+'&autoplay=1" allowfullscreen="allowfullscreen" webkitallowfullscreen="webkitallowfullscreen" mozallowfullscreen="mozallowfullscreen" frameborder="0"></iframe>');
}

player._play_youku = function( $ct, data ) {
    var $player = $ct.find('.joms-media__thumbnail');
    $ct.addClass('being-played');
    $player.html('<iframe height=280 width=500 src="http://player.youku.com/embed/' + data.id + '" frameborder=0 allowfullscreen></iframe>');
}

player._play_other = function( $ct, data ) {
    window.open( data.path );
}
    
var play = function ( $ct, data ) {
    var fn = '_play_' + data.type;
    if ( typeof player[fn] === 'function') {
        player[fn]( $ct, data);
    } else {
        player._play_other( $ct, data );
    }
}

// Exports.
return {
    play: play
};

});
