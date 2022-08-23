/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

 jQuery(function ($) {
    // Stikcy Header
    if ($('body').hasClass('sticky-header')) {
        var header = $('#sp-header');

        if ($('#sp-header').length) {
            var headerHeight = header.outerHeight();
            var stickyHeaderTop = header.offset().top;
            var stickyHeader = function () {
                var scrollTop = $(window).scrollTop();
                if (scrollTop > stickyHeaderTop) {
                    header.addClass('header-sticky');
                } else {
                    if (header.hasClass('header-sticky')) {
                        header.removeClass('header-sticky');
                    }
                }
            };
            stickyHeader();
            $(window).scroll(function () {
                stickyHeader();
            });
        }

        if ($('body').hasClass('layout-boxed')) {
            var windowWidth = header.parent().outerWidth();
            header.css({ 'max-width': windowWidth, left: 'auto' });
        }
    }

    // go to top
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.sp-scroll-up').fadeIn();
        } else {
            $('.sp-scroll-up').fadeOut(400);
        }
    });

    $('.sp-scroll-up').click(function () {
        $('html, body').animate(
            {
                scrollTop: 0,
            },
            600
        );
        return false;
    });

    // Preloader
    $(window).on('load', function () {
        $('.sp-preloader').fadeOut(500, function () {
            $(this).remove();
        });
    });

    //mega menu
    $('.sp-megamenu-wrapper').parent().parent().css('position', 'static').parent().css('position', 'relative');
    $('.sp-menu-full').each(function () {
        $(this).parent().addClass('menu-justify');
    });

    // Offcanvs
    $('#offcanvas-toggler').on('click', function (event) {
        event.preventDefault();
        $('.offcanvas-init').addClass('offcanvas-active');
    });

    $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
        event.preventDefault();
        $('.offcanvas-init').removeClass('offcanvas-active');
    });

    $(document).on('click', '.offcanvas-inner .menu-toggler', function (event) {
        event.preventDefault();
        $(this).closest('.menu-parent').toggleClass('menu-parent-open').find('>.menu-child').slideToggle(400);
    });

    // Tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], .hasTooltip'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
        });
    });

    // Popover
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Article Ajax voting
    $('.article-ratings .rating-star').on('click', function (event) {
        event.preventDefault();
        var $parent = $(this).closest('.article-ratings');

        var request = {
            option: 'com_ajax',
            template: template,
            action: 'rating',
            rating: $(this).data('number'),
            article_id: $parent.data('id'),
            format: 'json',
        };

        $.ajax({
            type: 'POST',
            data: request,
            beforeSend: function () {
                $parent.find('.fa-spinner').show();
            },
            success: function (response) {
                var data = $.parseJSON(response);
                $parent.find('.ratings-count').text(data.message);
                $parent.find('.fa-spinner').hide();

                if (data.status) {
                    $parent.find('.rating-symbol').html(data.ratings);
                }

                setTimeout(function () {
                    $parent.find('.ratings-count').text('(' + data.rating_count + ')');
                }, 3000);
            },
        });
    });

    //  Cookie consent
    $('.sp-cookie-allow').on('click', function (event) {
        event.preventDefault();

        var date = new Date();
        date.setTime(date.getTime() + 30 * 24 * 60 * 60 * 1000);
        var expires = '; expires=' + date.toGMTString();
        document.cookie = 'spcookie_status=ok' + expires + '; path=/';

        $(this).closest('.sp-cookie-consent').fadeOut();
    });

    //Search
    var searchRow = $('.top-search-input-wrap').parent().closest('.row');
    $('.top-search-input-wrap').insertAfter(searchRow);

    $('.search-icon').on('click', function () {
        $('.top-search-input-wrap').slideDown(200);
        $(this).hide();
        $('.close-icon').show();
        $('.top-search-input-wrap').addClass('active');
    });

    $('.close-icon').on('click', function () {
        $('.top-search-input-wrap').slideUp(200);
        $(this).hide();
        $('.search-icon').show();
        $('.top-search-input-wrap').removeClass('active');
    });

    // press esc to hide search
    $(document).keyup(function (e) {
        if (e.keyCode == 27) {
            // esc keycode
            $('.top-search-input-wrap').fadeOut(200);
            $('.close-icon').fadeOut(200);
            $('.search-icon').delay(200).fadeIn(200);
            $('body.off-canvas-menu-init').css({ 'overflow-y': 'initial' });
            $('.sp-weather .collapse-icon').removeClass('active');
            $('.sp-weather .sp-weather-forcasts').slideUp();
            $('.menu-collapse-icon').removeClass('active');
            $('.menu-area').removeClass('active');
        }
    });

    //toggle megamenu
    if ($('.main-megamenu').length > 0) {
        var $this = $('.main-megamenu');
        $this.find('ul.menu').wrapAll("<div class='sp-megamenu-main-wrap'></div>");
        $this
            .find('.sp-megamenu-main-wrap')
            .prepend("<a href='#' class='menu-collapse-icon'><span></span><span></span><span></span></a>");
        $this.find('ul.menu').addClass('menu-area');
        $('.menu-collapse-icon').on('click', function (e) {
            $(this).toggleClass('active');
            e.preventDefault();
            $('.menu-area').toggleClass('active');
        });
    }

    if ($('.nano').length > 0) {
        $('.nano').each(function () {
            $(this).nanoScroller();
        });
    }

    if ($('.marquee').length > 0) {
        jQuery('.marquee').marquee({
            duration: 20 * jQuery('.marquee').width(),
            delayBeforeStart: 0,
            gap: 0,
            duplicated: true,
            startVisible: true,
            pauseOnHover: true,
        });
    }

    //viewport class remove only safari
    let ua = navigator.userAgent.toLowerCase();
    if (ua.indexOf('safari') != -1) {
        if (ua.indexOf('chrome') > -1) {
            //if chrome
        } else {
            //for safari
            $('img.lazyestload').bind('inview', function (event, visible) {
                if (visible == true) {
                    $(this).removeClass('lazyestload');
                }
            });
        }
    }

    //mobile menu
    if (window.innerWidth < 990) {
        $('.main-megamenu .menu-parent>a>.menu-toggler').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.menu-parent').find('.menu-child').slideToggle();
            $(this).toggleClass('active');
        });
    }

    //Pagination
    $('.pagination .page-link.next').closest('li').addClass('next-wrapper');
    $('.pagination .page-link.previous').closest('li').addClass('previous-wrapper');

    //sp weather
    $('.sp-weather .sp-weather-icon-wrap').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).find('.collapse-icon').toggleClass('active');
        $('.sp-weather-forcasts').slideToggle();
    });

    //vertical Slider
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            var newNodes = mutation.addedNodes;
            if (newNodes !== null) {
                var $nodes = $(newNodes);

                $nodes.each(function () {
                    var $node = $(this);
                    $node.find('.sppb-articles-vertical-wrap').each(function () {
                        jQuery('.sppb-articles-vertical-wrap').bxSlider({
                            minSlides: 3,
                            mode: 'vertical',
                            speed: 500,
                            pause: 2000,
                            controls: true,
                            pager: false,
                            nextText: '<i class="fa  fa-angle-up"></i>',
                            prevText: '<i class="fa  fa-angle-down"></i>',
                            autoHover: true,
                            auto: true,
                            moveSlides: 1,
                            touchEnabled: true,
                            swipeThreshold: 50,
                            slideMargin: 15,
                            autoStart: true,
                        });
                    });
                });
            }
        });
    });

    var config = { childList: true, subtree: true };
    // Pass in the target node, as well as the observer options
    observer.observe(document.body, config);

    function showCategoryItems(parent, value) {
        var controlGroup = parent.find('*[data-showon]');

        controlGroup.each(function () {
            var data = $(this).attr('data-showon');
            data = typeof data !== 'undefined' ? JSON.parse(data) : [];
            if (data.length > 0) {
                if (typeof data[0].values !== 'undefined' && data[0].values.includes(value)) {
                    $(this).slideDown();
                } else {
                    $(this).hide();
                }
            }
        });
    }

    $('.btn-group label:not(.active)').click(function () {
        var label = $(this);
        var input = $('#' + label.attr('for'));

        if (!input.prop('checked')) {
            label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
            if (input.val() === '') {
                label.addClass('active btn-primary');
            } else if (input.val() == 0) {
                label.addClass('active btn-danger');
            } else {
                label.addClass('active btn-success');
            }
            input.prop('checked', true);
            input.trigger('change');
        }
        var parent = $(this).parents('#attrib-helix_ultimate_blog_options');
        if (parent) {
            showCategoryItems(parent, input.val());
        }
    });
    $('.btn-group input[checked=checked]').each(function () {
        if ($(this).val() == '') {
            $('label[for=' + $(this).attr('id') + ']').addClass('active btn btn-primary');
        } else if ($(this).val() == 0) {
            $('label[for=' + $(this).attr('id') + ']').addClass('active btn btn-danger');
        } else {
            $('label[for=' + $(this).attr('id') + ']').addClass('active btn btn-success');
        }
        var parent = $(this).parents('#attrib-helix_ultimate_blog_options');
        if (parent) {
            parent.find('*[data-showon]').each(function () {
                $(this).hide();
            });
        }
    });
});
