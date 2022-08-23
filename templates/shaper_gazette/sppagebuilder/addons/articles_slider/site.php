<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

class SppagebuilderAddonArticles_slider extends SppagebuilderAddons {

    public function render() {
        $settings = $this->addon->settings;

        $title = ( isset($settings->title)) ? $settings->title : '';
        $heading_selector = ( isset($settings->heading_selector)) ? $settings->heading_selector : 'h3';
        $lazyestload	= (isset($this->addon->settings->lazyestload) && $this->addon->settings->lazyestload) ? $this->addon->settings->lazyestload : 0;
        $catid = ( isset($settings->catid)) ? $settings->catid : '';
        $article_items = ( isset($settings->article_items)) ? $settings->article_items : 1;
        $ordering = ( isset($settings->ordering)) ? $settings->ordering : '';
        $limit = ( isset($settings->limit)) ? $settings->limit : '';
        $hide_thumbnail = ( isset($settings->hide_thumbnail)) ? $settings->hide_thumbnail : '';
        $show_category = ( isset($settings->show_category)) ? $settings->show_category : '';
        $show_author 	= (isset($this->addon->settings->show_author)) ? $this->addon->settings->show_author : 0;
        $show_date = ( isset($settings->show_date)) ? $settings->show_date : '';
        $show_readmore = ( isset($settings->show_readmore)) ? $settings->show_readmore : '';
        $readmore_text = ( isset($settings->readmore_text)) ? $settings->readmore_text : '';
        $autoplay = ( isset($settings->autoplay)) ? $settings->autoplay : '';
        $arrows = ( isset($settings->arrows)) ? $settings->arrows : 1;
        $show_intro 	= (isset($this->addon->settings->show_intro)) ? $this->addon->settings->show_intro : 1;
        $slider_style 	= (isset($this->addon->settings->slider_style)) ? $this->addon->settings->slider_style : 'default';
        $items_gutter 	= (isset($this->addon->settings->items_gutter)) ? $this->addon->settings->items_gutter : 30;
        $class = ( isset($settings->class) && $settings->class) ? ' ' . $settings->class : '';

        require_once JPATH_COMPONENT . '/helpers/articles.php';
        $items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid);


        //Check Auto Play
        $slide_autoplay = ($autoplay) ? 'data-sppb-slide-ride="true"' : '';
        
        if (count($items)) {
            $lazyestload_class = '';
			if($lazyestload){
				$lazyestload_class = 'lazyestload';
            }
            
            $output = '<div class="sppb-addon sppb-addon-articles-slider ' . $class . ' ' . $slider_style . '">';
            if ($title) {
                $output .= '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>';
            }
            
            // has next/previous arrows
            if ($arrows) {
                $output .= '<div class="customNavigation">';
                $output .= '<a class="sppbSlidePrev"><i class="fa fa-angle-left"></i></a>';
                $output .= '<a class="sppbSlideNext"><i class="fa fa-angle-right"></i></a>';
                $output .= '</div>'; // END:: /.customNavigation
            }

            $output .= '<div class="sppb-addon-content">';
            $output .= '<div class="sppb-row">';
            $output .= '<div class="sppb-col-sm-12">';
            $output .= '<div class="articles-slider owl-carousel" ' . $slide_autoplay . ' data-sppb-article-items="'.$article_items.'" data-sppb-article-items-gutter="'.$items_gutter.'" >';
            foreach ($items as $key => $item) {
                //bg image
                $bgimage = '';
                if($slider_style == "bg_image"){
                    $bgimage = 'style="background-image: url('. $item->image_medium .');"';
                }
                $output .= '<div class="sppb-addon-article item" '. $bgimage .'>';

                $item->catUrl = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid . ':' . urlencode($item->category_alias)));

                $url = JRoute::_(ContentHelperRoute::getArticleRoute($item->id . ':' . $item->alias, $item->catid, $item->language));
                $root = JURI::base();
                $root = new JURI($root);

                if($slider_style != "bg_image"){
                    $output .= '<div class="sppb-article-img-wrapper">';
                    $output .= '<img src="'. $item->image_small .'" class="'. $lazyestload_class .'" data-src="'. $item->image_small .'" alt="image">';
                    $output .= '</div>';
                }
                $output .= '<div class="sppb-article-details">';
                if ($show_category) {
                    $output .= '<span class="sppb-meta-category"><a href="' . $item->catUrl . '" itemprop="genre">' . $item->category . '</a></span>';
                }
                $output .= '<h3 class="article-title"><a href="' . $item->link . '" itemprop="url">' . $item->title . '</a></h3>';
                if($show_intro){
                    $output .= '<div class="introtext gazette-custom-font">' . $item->introtext . '</div>';
                }
                if ($show_date || $show_author) {
                    $output .= '<div class="sppb-article-meta">';
                    if($show_author) {
                        $author = ( $item->created_by_alias ?  $item->created_by_alias :  $item->username);
                        $output .= '<span class="sppb-meta-author" itemprop="name">' . JTEXT::_('CREATED_BY') . ' ' . $author . '</span>';
                    }
                    if ($show_date) {
                        $output .= '<span class="sppb-meta-date" itemprop="dateCreated">' . Jhtml::_('date', $item->created, 'DATE_FORMAT_LC3') . '</span>';
                    }
                    $output .= '</div>'; //.sppb-article-meta
                }
                if ($show_readmore) {
                    $output .= '<a class="sppb-readmore" href="' . $item->link . '" itemprop="url">' . $readmore_text . '</a>';
                }

                $output .= '</div>'; //.sppb-article-details
                $output .= '</div>'; //sppb-addon-article item
            }
            $output .= '</div>'; //sppb-col-sm-12
            $output .= '</div>'; //sppb-row
            $output .= '</div>'; //sppb-addon-content
            $output .= '</div>';
            $output .= '</div>';

            return $output;
        }

        return false;
    }

    public function scripts() {
        $app = JFactory::getApplication();
        $base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/js/';
        return array($base_path . 'owl.carousel.min.js', $base_path . 'lazyestload.js');
    }

    public function stylesheets() {
        $app = JFactory::getApplication();
        $base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/css/';
        return array($base_path . 'owl.carousel.css', $base_path . 'owl.theme.css');
    }

    public function css() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        $settings = $this->addon->settings;
        $css = '';

        $title = (isset($settings->title)) ? $settings->title : '';

        $title_style = (isset($settings->title_margin_top) && $settings->title_margin_top ) ? 'margin-top:' . (int) $settings->title_margin_top . 'px;' : '';
        $title_style .= (isset($settings->title_margin_bottom) && $settings->title_margin_bottom ) ? 'margin-bottom:' . (int) $settings->title_margin_bottom . 'px;' : '';
        $title_style .= (isset($settings->title_fontsize) && $settings->title_fontsize ) ? 'font-size:' . $settings->title_fontsize . 'px;line-height:' . $settings->title_fontsize . 'px;' : '';
        $title_style .= (isset($settings->title_fontweight) && $settings->title_fontweight ) ? 'font-weight:' . $settings->title_fontweight . ';' : '';
        $title_style .= (isset($settings->title_text_color) && $settings->title_text_color ) ? 'color:' . $settings->title_text_color . ';' : '';

        if ($title_style && $title) {
            $css .= $addon_id . ' .sppb-addon-title {';
            $css .= $title_style;
            $css .= '}';
        }
        return $css;
    }

    public function js() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        return 'jQuery( document ).ready(function( $ ) {
        var $slideFullwidth = $("' . $addon_id . ' .articles-slider");
        var $autoplay = $slideFullwidth.attr("data-sppb-slide-ride");
        var $article_items = Number($slideFullwidth.attr("data-sppb-article-items"));
        var $article_mobile_items = 1;
        if($article_items>1){
            $article_mobile_items = Math.floor($article_items/2);
        }
        var $items_gutter = $slideFullwidth.attr("data-sppb-article-items-gutter");
        if ($autoplay == "true") {
            var $autoplay = true;
        } else {
            var $autoplay = false
        };
        
        $slideFullwidth.owlCarousel({
            margin: Number($items_gutter),
            loop: true,
            video: true,
            dots: true,
            autoplay: $autoplay,
            animateIn: "fadeIn",
            animateOut: "fadeOut",
            autoplayHoverPause: true,
            autoplaySpeed: 1500,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: $article_mobile_items
                },
                1200: {
                    items: $article_items
                }
            }
        });

        $("' . $addon_id . ' .sppbSlidePrev").click(function() {
            $slideFullwidth.trigger("prev.owl.carousel", [400]);
        });

        $("' . $addon_id . ' .sppbSlideNext").click(function() {
            $slideFullwidth.trigger("next.owl.carousel", [400]);
        });
      });';
    }

}
