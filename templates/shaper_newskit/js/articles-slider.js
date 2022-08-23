/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(document).ready(function($){'use strict';
    
    // Full width Slideshow
    var $slideFullwidth = $('#articles-slider');

    // Autoplay
    var $autoplay   = $slideFullwidth.attr('data-sppb-slide-ride');
    if ($autoplay == 'true') { var $autoplay = true; } else { var $autoplay = false};

    $slideFullwidth.owlCarousel({
        margin: 30,
        loop: true,
        video:true,
        nav:true,
        dots: false,
        autoplay: $autoplay,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        autoplayHoverPause: true,
        autoplaySpeed: 1500,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            }
        },
    });

});

