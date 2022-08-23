<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

class SppagebuilderAddonArticles_slider extends SppagebuilderAddons {

    public function render() {
        $settings = $this->addon->settings;

        $title = ( isset($settings->title)) ? $settings->title : '';
        $heading_selector = ( isset($settings->heading_selector)) ? $settings->heading_selector : 'h3';
        $catid = ( isset($settings->catid)) ? $settings->catid : '';
        $ordering = ( isset($settings->ordering)) ? $settings->ordering : '';
        $limit = ( isset($settings->limit)) ? $settings->limit : '';
        $social_share = (isset($settings->social_share) && $settings->social_share) ? $settings->social_share : 1;
        $show_socials = (isset($settings->show_socials) && $settings->show_socials) ? $settings->show_socials : '';
        $hide_thumbnail = ( isset($settings->hide_thumbnail)) ? $settings->hide_thumbnail : '';
        $show_category = ( isset($settings->show_category)) ? $settings->show_category : '';
        $show_date = ( isset($settings->show_date)) ? $settings->show_date : '';
        $show_readmore = ( isset($settings->show_readmore)) ? $settings->show_readmore : '';
        $readmore_text = ( isset($settings->readmore_text)) ? $settings->readmore_text : '';
        $autoplay = ( isset($settings->autoplay)) ? $settings->autoplay : '';
        $arrows = ( isset($settings->arrows)) ? $settings->arrows : 1;
        $class = ( isset($settings->class) && $settings->class) ? ' ' . $settings->class : '';

        require_once JPATH_COMPONENT . '/helpers/articles.php';
        $items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid);


        //Check Auto Play
        $slide_autoplay = ($autoplay) ? 'data-sppb-slide-ride="true"' : '';

        if (count($items)) {
            $output = '<div class="sppb-addon sppb-addon-articles-slider ' . $class . '">';
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
            $output .= '<div class="articles-slider owl-carousel" ' . $slide_autoplay . ' >';
            foreach ($items as $key => $item) {
                $output .= '<div class="sppb-addon-article item">';

                $item->catUrl = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid . ':' . urlencode($item->category_alias)));

                $url = JRoute::_(ContentHelperRoute::getArticleRoute($item->id . ':' . $item->alias, $item->catid, $item->language));
                $root = JURI::base();
                $root = new JURI($root);
                $social_url = $root->getScheme() . '://' . $root->getHost() . $url;

                $output .= '<div class="sppb-row">';
                $output .= '<div class="sppb-col-sm-6">';
                if (!$hide_thumbnail) {
                    $output .= '<div class="sppb-article-image-wrap">';
                    // social share
                    if ($social_share) {
                        $output .= '<div class="sppb-post-share-social">';
                        $output .= '<span class="share-button"><i class="fa fa-share-square-o"></i></span>';
                        $output .= '<div class="sppb-post-share-social-others">';
                        if (in_array('facebook', $show_socials)) {
                            $output .= '<a class="fa fa-facebook" data-toggle="tooltip" data-placement="bottom" title="' . JText::_('HELIX_SHARE_FACEBOOK') . '" onClick="window.open(\'http://www.facebook.com/sharer.php?u=' . $social_url . '\',\'Facebook\',\'width=600,height=300,left=\'+(screen.availWidth/2-300)+\',top=\'+(screen.availHeight/2-150)+\'\'); return false;" href="http://www.facebook.com/sharer.php?u=' . $social_url . '"></a>';
                        }
                        if (in_array('twitter', $show_socials)) {
                            $output .= '<a class="fa fa-twitter" data-toggle="tooltip" data-placement="bottom" title="' . JText::_('HELIX_SHARE_TWITTER') . '" onClick="window.open(\'http://twitter.com/share?url=' . $social_url . '&amp;text=' . str_replace(" ", "%20", $item->title) . '\',\'Twitter share\',\'width=600,height=300,left=\'+(screen.availWidth/2-300)+\',top=\'+(screen.availHeight/2-150)+\'\'); return false;" href="http://twitter.com/share?url=' . $social_url . '&amp;text=' . str_replace(" ", "%20", $item->title) . '"></a>';
                        }
                        if (in_array('gplus', $show_socials)) {
                            $output .= '<a class="fa fa-google-plus" data-toggle="tooltip" data-placement="bottom" title="' . JText::_('HELIX_SHARE_GOOGLE_PLUS') . '" onClick="window.open(\'https://plus.google.com/share?url=' . $social_url . '\',\'Google plus\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="https://plus.google.com/share?url=' . $social_url . '" ></a>';
                        }
                        if (in_array('pinterest', $show_socials)) {
                            $output .= '<a class="fa fa-pinterest" data-toggle="tooltip" data-placement="bottom" title="' . JText::_('HELIX_SHARE_PINTEREST') . '" onClick="window.open(\'http://pinterest.com/pin/create/button/?url=' . $social_url . '&amp;description=' . $item->title . '\',\'Pinterest\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="http://pinterest.com/pin/create/button/?url=' . $social_url . '&amp;description=' . $item->title . '" ></a>';
                        }
                        if (in_array('linkedin', $show_socials)) {
                            $output .= '<a class="fa fa-linkedin" data-toggle="tooltip" data-placement="bottom" title="' . JText::_('HELIX_SHARE_LINKEDIN') . '" onClick="window.open(\'http://www.linkedin.com/shareArticle?mini=true&url=' . $social_url . '\',\'Linkedin\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="http://www.linkedin.com/shareArticle?mini=true&url=' . $social_url . '" ></a>';
                        }
                        $output .= '</div>'; //.social share others
                        $output .= '</div>'; //.social share
                    }

                    if ($item->post_format == 'gallery') {

                        if (count($item->imagegallery->images)) {

                            $output .= '<div class="sppb-carousel sppb-slide" data-sppb-ride="sppb-carousel">';
                            $output .= '<div class="sppb-carousel-inner">';
                            foreach ($item->imagegallery->images as $gallery_item) {
                                $output .= '<div class="sppb-item">';
                                $output .= '<img src="' . $gallery_item['medium'] . '" alt="' . $item->title . '">';
                                $output .= '</div>';
                            }
                            $output .= '</div>';

                            $output .= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
                            $output .= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';
                            $output .= '</div>';
                        } elseif (isset($item->image_medium) && $item->image_medium) {
                            $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $item->image_medium . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
                        }
                    } else {
                        if (isset($item->image_medium) && $item->image_medium) {
                            $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $item->image_medium . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
                        }
                    }
                    $output .= '</div>'; //.sppb-article-image-wrap
                }
                $output .= '</div>'; //.sppb-col-sm-6

                $output .= '<div class="sppb-col-sm-6">';
                $output .= '<div class="sppb-article-details">';
                if ($show_category) {
                    $output .= '<span class="sppb-meta-category"><a href="' . $item->catUrl . '" itemprop="genre">' . $item->category . '</a></span>';
                }
                if ($show_date) {
                    $output .= '<div class="sppb-article-meta">';
                    if ($show_date) {
                        $output .= '<span class="sppb-meta-date" itemprop="dateCreated">' . Jhtml::_('date', $item->created, 'DATE_FORMAT_LC3') . '</span>';
                    }
                    $output .= '</div>'; //.sppb-article-meta
                }
                $output .= '<h3 class="article-title"><a href="' . $item->link . '" itemprop="url">' . $item->title . '</a></h3>';
                $output .= '<div class="introtext">' . $item->introtext . '</div>';

                if ($show_readmore) {
                    $output .= '<a class="sppb-readmore" href="' . $item->link . '" itemprop="url">' . $readmore_text . '</a>';
                }

                $output .= '</div>'; //.sppb-article-details
                $output .= '</div>'; //.sppb-col-sm-6
                $output .= '</div>'; //sppb-row
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
        return array($base_path . 'owl.carousel.min.js');
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
        if ($autoplay == "true") {
            var $autoplay = true;
        } else {
            var $autoplay = false
        };


        $slideFullwidth.owlCarousel({
            margin: 30,
            loop: true,
            video: true,
            dots: true,
            autoplay: $autoplay,
            animateIn: "fadeIn",
            animateOut: "fadeOut",
            autoplayHoverPause: true,
            autoplaySpeed: 1500,
            items:1
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
