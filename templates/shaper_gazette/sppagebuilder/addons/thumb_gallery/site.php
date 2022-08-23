<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');
require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/articles.php';

class SppagebuilderAddonThumb_gallery extends SppagebuilderAddons {

    public function render() {
        $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
        $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

        $title_text_color = (isset($this->addon->settings->title_text_color) && $this->addon->settings->title_text_color) ? $this->addon->settings->title_text_color : '';
        $lazyestload	= (isset($this->addon->settings->lazyestload) && $this->addon->settings->lazyestload) ? $this->addon->settings->lazyestload : 0;
        $category = (isset($this->addon->settings->category) && $this->addon->settings->category) ? $this->addon->settings->category : '';
        $item_limit = (isset($this->addon->settings->item_limit) && $this->addon->settings->item_limit) ? $this->addon->settings->item_limit : '';
        $order_by = (isset($this->addon->settings->order_by) && $this->addon->settings->order_by) ? $this->addon->settings->order_by : '';
        $autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? $this->addon->settings->autoplay : '';
        $arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : '';
        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';

        $items = SppagebuilderHelperArticles::getArticles($item_limit, $order_by, $category);

        //autoplay, controllers & arrow
        $slide_autoplay = ($autoplay) ? 'data-sppb-tg-autoplay="true"' : 'data-sppb-tg-autoplay="false"';
        $slide_arrows = ($arrows) ? 'data-sppb-tg-arrows="true"' : 'data-sppb-tg-arrows="false"';

        $output = '<div class="sppb-addon sppb-thumb-gallery-wrapper sppb-addon-thumb-gallery ' . $class . '">';

        if ($title) {
            $title_style = '';
            if ($title_text_color)
                $title_style .= 'color:' . $title_text_color . ';';

            $output .= '<' . $heading_selector . ' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</' . $heading_selector . '>';
        }

        $lazyestload_class = '';
        if($lazyestload){
            $lazyestload_class = 'lazyestload';
        }
            
        $output .= '<div id="slider" class="flexslider sppb-tg-slider" ' . $slide_autoplay . ' ' . $slide_arrows . '>';
        $output .= '<ul class="slides">';
        foreach ($items as &$item) {
            if (isset($item->image_large) && $item->image_large) {
                $output .= '<li><img class="sppb-img-responsive '. $lazyestload_class .'" src="' . $item->image_large . '" data-src="'. $item->image_large .'" alt="' . $item->title . '" itemprop="thumbnailUrl"> </li>';
            } elseif (isset($item->image_medium) && $item->image_medium) {
                $output .= '<li> <img class="sppb-img-responsive '. $lazyestload_class .'" src="' . $item->image_medium . '" data-src="'. $item->image_medium .'" alt="' . $item->title . '" itemprop="thumbnailUrl"> </li>';
            }
        }
        $output .= '</ul>'; //ul.slides
        $output .= '</div>'; // END /#slider


        $output .= '<div id="carousel" class="flexslider">';
        $output .= '<ul class="slides">'; // END /#slider
        foreach ($items as $thumb_item) {
            if (isset($thumb_item->image_small) && $thumb_item->image_small) {
                $output .= '<li> <img class="sppb-img-responsive '. $lazyestload_class .'" src="' . $thumb_item->image_small . '" data-src="'. $thumb_item->image_small .'" alt="' . $item->title . '" itemprop="thumbnailUrl"> </li>';
            }
        }
        $output .= '</ul>'; // END /#slider
        $output .= '</div>'; // END /#scarousel

        $output .= '</div>'; // END /.flexslider

        $sppbSlideArray = array();
        return $output;
    }

    public function scripts() {
        $app = JFactory::getApplication();
        $base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/js/';
        return array($base_path . 'jquery.flexslider-min.js', $base_path . 'lazyestload.js');
    }

    public function stylesheets() {
        $app = JFactory::getApplication();
        return array(JURI::base() . '/templates/' . $app->getTemplate() . '/css/flexslider.css');
    }

    public function js() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        return 'jQuery( document ).ready(function( $ ) {
            if ( $( "' . $addon_id . ' #carousel" ).is( ".flexslider" ) ) {
                var $sppbTgOptions = $(".sppb-tg-slider");
		        var $autoplay   = $sppbTgOptions.data("sppb-tg-autoplay");
		        var $arrows   = $sppbTgOptions.data("sppb-tg-arrows");
                
		        
		        $("' . $addon_id . ' #carousel").flexslider({
		            animation: "slide",
                            controlNav: false,
                            directionNav: $arrows,
                            animationLoop: false,
                            slideshow: $autoplay,
                            itemWidth: 108,
                            itemMargin: 15,
                            asNavFor: "' . $addon_id . ' #slider",
                            after: function (slider) {
                                if (!slider.playing) {
                                    slider.play();
                                }
                            }
		        });

		        $("' . $addon_id . ' #slider").flexslider({
		            animation: "slide",
                            controlNav: false,
                            directionNav: false,
                            animationLoop: false,
                            slideshow: $autoplay,
                            sync: "' . $addon_id . ' #carousel",
                            after: function (slider) {
                                if (!slider.playing) {
                                    slider.play();
                                }
                            }
		        });

		    };
    	});';
    }
}
