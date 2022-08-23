(function ($) {
    "use strict";

    var dummyTip = null;
    var _config = null;

    function getConfig() {
        if (_config !== null) {
            return _config;
        } else if ($('#youtuber-cfg').length) {
            _config = JSON.parse($('#youtuber-cfg').html());
            if (!_config || !_config['ajax_url']) {
                console.log('No YouTubeR config');
            }
            return _config;
        } else {
            console.log('No YouTubeR config');
        }
    }
    
    function getConfigValue(_name){
        var params = getConfig();
        return params[_name];
    }
    
    function getLangString(key) {
        var params = getConfig();
        return params.lang[key];
    }
    
    function reBindScripts() {
        if ($('a.mxyt-lightbox').length) {
            $('a.mxyt-lightbox').fancybox(JSON.parse(getConfigValue('fancybox_params')));
        }

        $('.mxyt-tip').each(function (index, element) {
            var $this = $(this);
            var mainContainer = $this.closest('.mxYouTubeR');
            var _class = '';
            if (mainContainer.attr('class')) {
                var matches = mainContainer.attr('class').match(/(mxYouTubeR_theme_[a-z0-9\-_]+)/);
                if (matches && matches.length > 0) {
                    _class = matches[0];
                }
            }
            $this.off('mouseenter');
            $this.on('mouseenter', function () {
                if (!dummyTip.hasClass(_class)) {
                    dummyTip.addClass(_class);
                }
                dummyTip.children('span').html($this.attr('title'));
                var pos = $this.offset();
                dummyTip.css('left', pos.left + $this.innerWidth() / 2 - dummyTip.width() / 2);
                dummyTip.css('top', pos.top);

                dummyTip.addClass('active');
                dummyTip.stop().css('opacity', 0).animate({
                    'opacity': 0.9,
                    'margin-top': -25
                }, 200, 'swing', function () {
                    //console.info(tip,this);
                });
            });
            $this.off('mouseleave');
            $this.on('mouseleave', function () {
                dummyTip.removeClass(_class);
                dummyTip.removeClass('active');
                dummyTip.css('margin-top', -15);
            });
        });

        $('.mxyt-text-description.mxyt-less').each(function (index, element) {
            var $this = $(this);
            if ($this.next().is('.mxyt-text-description-btn')) {
                return;
            }
            var moreBtn = $('<div class="mxyt-text-description-btn">' + getLangString('more') + '</div>');
            var fullHeight = $this.children().height();
            if ($this.height() < fullHeight) {
                $this.addClass('hasMore');
                moreBtn.insertAfter($this);
                moreBtn.off('click');
                moreBtn.on('click', function () {
                    var btn = $(this);
                    if ($this.hasClass('mxyt-less')) {
                        $this.removeClass('mxyt-less');
                        btn.html(getLangString('less'));
                    } else {
                        $this.addClass('mxyt-less');
                        btn.html(getLangString('more'));
                    }
                });
            } else {
                $this.removeClass('hasMore');
                $this.addClass('noMore');
            }
        });

        $('.mxyt-playlist-select span').off('click');
        $('.mxyt-playlist-select span').on('click', function () {
            var $this = $(this);
            playlistLoader.loadPlaylist($this.closest('.mxyt-channel-videos'), $this.attr('data-mxyt-cfg'));
            return false;
        });

        $('.mxyt-load-more').each(function (index, element) {
            var $this = $(this);
            if ($this.attr('data-checked')) {
                return;
            }
            $this.attr('data-checked', 'true');
            if ($this.hasClass('mxyt-infinite-scroll')) {
                $(window).on('scroll resize', function () {
                    playlistLoader.checkLoadMore($this);
                });
                playlistLoader.checkLoadMore($this);
            } else {
                $this.off('click');
                $this.on('click', function () {
                    playlistLoader.loadMore($this);
                    return false;
                });
            }
        });
        $('.ch-item').on('mouseenter', function () {
            $(this).addClass('mx-active');
        });
        $('.ch-item').on('mouseleave', function () {
            $(this).removeClass('mx-active');
        });
    }

    var playlistLoader = {
        loading: false,
        haveMore: false,
        button: null,
        loadingBar: null,
        params: {},
        target: null,

        bind: function (jqBtn) {
            this.button = jqBtn;
            this.haveMore = jqBtn.attr('data-mxyt-havemore')=='1';
            this.params = jqBtn.attr('data-mxyt-cfg');
            this.target = jqBtn.closest('.mxYouTubeR').find('.mxyt-playlist');
        },
        checkLoadMore: function(jqBtn){
            if (jqBtn.hasClass('mxyt-infinite-scroll')) {
                var top = $(window).scrollTop() + $(window).height();
                if (top > jqBtn.offset().top) {
                    this.loadMore(jqBtn);
                }
            }
        },
        loadMore: function (jqBtn) {
            if (this.loading) {
                return false;
            }
            this.bind(jqBtn);
            if (!this.haveMore) {
                return false;
            }
            this.loading = true;
            this.loadingBar = $('<i class="mxyt-icon mxyt-icon-spinner"></i>');
            this.button.hide();
            this.button.parent().append(this.loadingBar);

            var loader = this;
            $.ajax({
                url: getConfigValue('ajax_url'),
                type: 'post',
                dataType: "json",
                data: {
                    action: 'youtuber',
                    params: loader.params,
                },
                success: function (result) {
                    if (result && result.success) {
                        loader.loaded(result);
                    } 
                    else if(result && result.error){
                        alert(result.error);
                    }
                    else {
                        alert('mxYouTubeR ajax error.');
                    }
                },
                error: function () {
                    alert('mxYouTubeR ajax error.');
                }
            });
        },
        loadPlaylist: function (_container, _params) {
            if (this.loading) {
                return false;
            }
            this.loading = true;
            var loadingBar = $('<p align="center"><i class="mxyt-icon mxyt-icon-spinner"></i></p>');
            _container.html(loadingBar);

            var loader = this;
            $.ajax({
                url: getConfigValue('ajax_url'),
                type: 'post',
                dataType: "json",
                data: {
                    action: 'youtuber',
                    params: _params
                },
                success: function (result) {
                    if (result.success) {
                        loader.loading = false;
                        loadingBar.remove();
                        var items = $($(result.html).find('.mxyt-channel-videos').html());
                        _container.append(items);
                        reBindScripts();
                        items.css('opacity', 0);
                        items.stop().animate({
                            'opacity': 1
                        }, 300);
                    } else {
                        alert('mxYouTubeR ajax error.');
                    }
                },
                error: function () {
                    alert('mxYouTubeR ajax error.');
                }
            });
        },
        loaded: function (result) {
            this.haveMore = result.haveMore;
            this.loading = false;
            this.loadingBar.remove();
            this.button.attr('data-mxyt-havemore', (this.haveMore?'1':'0'));
            if (this.haveMore) {
                this.button.show();
            }
            var html = $(result.html);
            var items = html.find('.mxyt-row, .mxyt-brow');
            this.params = this.params.replace(/\&start=\d+/,'&start='+html.find('.mxyt-load-more').attr('data-mxyt-start'));
            this.button.attr('data-mxyt-cfg', this.params);
            this.target.append(items);
            reBindScripts();
            items.css('opacity', 0);
            items.stop().animate({
                'opacity': 1
            }, 300);
            
            this.checkLoadMore(this.button);
        }
    };

    window.mxYouTuberInit = function () {
        if (!dummyTip) {
            dummyTip = $('<div id="mxyt-tooltip" class="mxyt-tooltip"><span></span><div></div></div>');
            $('body').append(dummyTip);
        }
        reBindScripts();
    }

    $(document).ready(function (e) {
        window.mxYouTuberInit();
    });

})(jQuery);







