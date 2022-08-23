<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted access');

class SppagebuilderAddonArticles extends SppagebuilderAddons{

	public function render(){
		$settings = $this->addon->settings;

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : 'panel-default';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$layout = ( isset($settings->layout) && $settings->layout ) ? $settings->layout : 'default';
		$leading_item = (isset($settings->leading_item) && $settings->leading_item) ? $settings->leading_item : '';
        $leading_item_end = (isset($settings->leading_item) && $settings->leading_item) ? $settings->leading_item + 1 : '';
		$resource 		= (isset($this->addon->settings->resource) && $this->addon->settings->resource) ? $this->addon->settings->resource : 'article';
		$catid 			= (isset($this->addon->settings->catid) && $this->addon->settings->catid) ? $this->addon->settings->catid : 0;
		$k2catid 		= (isset($this->addon->settings->k2catid) && $this->addon->settings->k2catid) ? $this->addon->settings->k2catid : 0;
		$include_subcat = (isset($this->addon->settings->include_subcat)) ? $this->addon->settings->include_subcat : 1;
		$post_type 		= (isset($this->addon->settings->post_type) && $this->addon->settings->post_type) ? $this->addon->settings->post_type : '';
		$ordering 		= (isset($this->addon->settings->ordering) && $this->addon->settings->ordering) ? $this->addon->settings->ordering : 'latest';
		$limit 			= (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 3;
		$columns 		= (isset($this->addon->settings->columns) && $this->addon->settings->columns) ? $this->addon->settings->columns : 3;
		$column = (isset($settings->column) && $settings->column) ? $settings->column : '';
		// $show_intro 	= (isset($this->addon->settings->show_intro)) ? $this->addon->settings->show_intro : 1;
		$hide_thumbnail = (isset($this->addon->settings->hide_thumbnail)) ? $this->addon->settings->hide_thumbnail : 0;
		$social_share = (isset($settings->social_share) && $settings->social_share) ? $settings->social_share : 0;
		$show_socials = (isset($settings->show_socials) && $settings->show_socials) ? $settings->show_socials : '';

		$show_leading_intro = (isset($settings->show_leading_intro) && $settings->show_leading_intro) ? $settings->show_leading_intro : '';
        $show_intro_item_intro = (isset($settings->show_intro_item_intro)) ? $settings->show_intro_item_intro : 0;
        $leading_intro_limit = (isset($settings->leading_intro_limit) && $settings->leading_intro_limit) ? $settings->leading_intro_limit : '';
        $intro_intro_limit = (isset($settings->intro_intro_limit) && $settings->intro_intro_limit) ? $settings->intro_intro_limit : '';
		$show_author 	= (isset($this->addon->settings->show_author)) ? $this->addon->settings->show_author : 1;
		$show_category 	= (isset($this->addon->settings->show_category)) ? $this->addon->settings->show_category : 1;
		$show_date 		= (isset($this->addon->settings->show_date)) ? $this->addon->settings->show_date : 1;
		$show_readmore 	= (isset($this->addon->settings->show_readmore)) ? $this->addon->settings->show_readmore : 1;
		$readmore_text 	= (isset($this->addon->settings->readmore_text) && $this->addon->settings->readmore_text) ? $this->addon->settings->readmore_text : 'Read More';
		$link_articles 	= (isset($this->addon->settings->link_articles)) ? $this->addon->settings->link_articles : 0;

		$all_articles_btn_text   = (isset($this->addon->settings->all_articles_btn_text) && $this->addon->settings->all_articles_btn_text) ? $this->addon->settings->all_articles_btn_text : 'See all posts';
		$all_articles_btn_class  = (isset($this->addon->settings->all_articles_btn_size) && $this->addon->settings->all_articles_btn_size) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_size : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_type : ' sppb-btn-default';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_shape) && $this->addon->settings->all_articles_btn_shape) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_shape: ' sppb-btn-rounded';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_appearance : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_block) && $this->addon->settings->all_articles_btn_block) ? ' ' . $this->addon->settings->all_articles_btn_block : '';
		$all_articles_btn_icon   = (isset($this->addon->settings->all_articles_btn_icon) && $this->addon->settings->all_articles_btn_icon) ? $this->addon->settings->all_articles_btn_icon : '';
		$all_articles_btn_icon_position = (isset($this->addon->settings->all_articles_btn_icon_position) && $this->addon->settings->all_articles_btn_icon_position) ? $this->addon->settings->all_articles_btn_icon_position: 'left';

		$output   = '';
		//include k2 helper
		$k2helper 			= JPATH_ROOT . '/components/com_sppagebuilder/helpers/k2.php';
		$article_helper = JPATH_ROOT . '/components/com_sppagebuilder/helpers/articles.php';
		$isk2installed  = self::isComponentInstalled('com_k2');

		if ($resource == 'k2') {
			if ($isk2installed == 0) {
				$output .= '<p class="alert alert-danger">' . JText::_('COM_SPPAGEBUILDER_ADDON_ARTICLE_ERORR_K2_NOTINSTALLED') . '</p>';
				return $output;
			} elseif(!file_exists($k2helper)) {
				$output .= '<p class="alert alert-danger">' . JText::_('COM_SPPAGEBUILDER_ADDON_K2_HELPER_FILE_MISSING') . '</p>';
				return $output;
			} else {
				require_once $k2helper;
			}
			$items = SppagebuilderHelperK2::getItems($limit, $ordering, $k2catid, $include_subcat);
		} else {
			require_once $article_helper;
			$items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid, $include_subcat, $post_type);
		}

		if (count($items)) {
            $output = '<div class="sppb-addon sppb-addon-articles' . $class . ' layout-' . $layout . '">';
            $cat_url = JRoute::_(ContentHelperRoute::getCategoryRoute($catid, $catid));

            if ($title) {
                $output .= '<' . $heading_selector . ' class="sppb-addon-title"><a href="' . $cat_url . '">' . $title . '</a></' . $heading_selector . '>';
            }


            $output .= '<div class="sppb-addon-content">';
            $output .= '<div class="sppb-row">';

            $i = 0;
            $total_item = count($items);
            foreach ($items as $key => $item) {
                $url = JRoute::_(ContentHelperRoute::getArticleRoute($item->id . ':' . $item->alias, $item->catid, $item->language));
                $root = JURI::base();
                $root = new JURI($root);
                $social_url = $root->getScheme() . '://' . $root->getHost() . $url;

                $key ++;
                $item->catUrl = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug));
                $image = '';
                $item_type = '';
                if ($key <= $leading_item && (($layout == 'classic') || ($layout == 'modern'))) {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = 7;
                    $item_type = 'leading-item';
                } elseif (( ( $key > $leading_item && $key == $leading_item_end ) ) && $layout == 'classic') {
                    $image = (isset($item->image_small) && $item->image_small) ? $item->image_small : '';
                    $column = 5;
                    $item_type = 'intro-item';
                } elseif ($key > $leading_item && $layout == 'classic') {
                    $image = (isset($item->image_small) && $item->image_small) ? $item->image_small : '';
                    $item_type = 'intro-item';
                } elseif (( ( $key > $leading_item && $key == $leading_item_end ) ) && $layout == 'modern') {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = 5;
                    $item_type = 'intro-item';
                } elseif ($key <= $leading_item && $layout == 'simple') {
                    $image = (isset($item->image_thumbnail) && $item->image_thumbnail) ? $item->image_thumbnail : '';
                    $column = 8;
                    $item_type = 'leading-item';
                } elseif (( ( $key > $leading_item && $key == $leading_item_end ) ) && $layout == 'simple') {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = 4;
                    $item_type = 'intro-item';
                } elseif ($key <= $leading_item && $layout == 'standard') {
                    $image = (isset($item->image_medium) && $item->image_medium) ? $item->image_medium : '';
                    $column = 12;
                    $item_type = 'leading-item';
                } elseif (( ( $key > $leading_item && $key == $leading_item_end ) ) && $layout == 'standard') {
                    $image = (isset($item->image_medium) && $item->image_medium) ? $item->image_medium : '';
                    $column = 12;
                    $item_type = 'intro-item';
                } elseif ($key <= $leading_item && $layout == 'essential') {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = '';
                    $item_type = 'leading-item';
                } elseif (( ( $key > $leading_item ) ) && $layout == 'essential') {
                    $image = (isset($item->image_medium) && $item->image_medium) ? $item->image_medium : '';
                    $column = '';
                    $item_type = 'intro-item';
                } elseif (( ( $key > $leading_item ) ) && $layout == 'basic') {
                    $image = (isset($item->image_thumbnail) && $item->image_thumbnail) ? $item->image_thumbnail : '';
                    $column = round(12 / $columns);
                    $item_type = 'intro-item';
                } elseif ($key <= $leading_item && $layout == 'creative') {
                    $image = (isset($item->image_thumbnail) && $item->image_thumbnail) ? $item->image_thumbnail : '';
                    $column = '';
                    $item_type = 'leading-item';
                } elseif (( ( $key > $leading_item && $key == $leading_item_end ) ) && $layout == 'creative') {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = '';
                    $item_type = 'intro-item';
                } else {
                    $image = (isset($item->image_large) && $item->image_large) ? $item->image_large : '';
                    $column = round(12 / $columns);
                    $item_type = 'intro-item';
                }


                if (( $key <= $leading_item && $key == 1 ) || ( $key > $leading_item && $key == $leading_item_end && (($layout != 'default')) )) {
                    $output .= '<div class="sppb-col-md-' . $column . '">';
                } elseif (($layout == 'default') || ($layout == 'basic')) {
                    $output .= '<div class="sppb-col-sm-12 sppb-col-md-' . $column . '">';
                }

                // if (( $key > $leading_item && $key == $leading_item_end ) && (($layout != 'default'))) {
                //     $output .= '<div>';
                // }

                if ($key <= $leading_item && $key == 1 && $layout == 'creative') {
                    $output .= '<div class="sppb-col-md-8 sppb-main-leading-wrap">';
                } elseif ($key <= $leading_item && $key == 2 && $layout == 'creative') {
                    $output .= '<div class="sppb-col-md-4 sppb-sub-leading-wrap">';
                }

                if ($key <= $leading_item && $key == 1 && $layout == 'essential') {
                    $output .= '<div class="sppb-col-md-' . round(12 / $leading_item) . ' sppb-main-leading-wrap">';
                } elseif ($key <= $leading_item && $key == 2 && $layout == 'essential') {
                    $output .= '<div class="sppb-col-md-' . round(12 / $leading_item) . ' sppb-sub-leading-wrap">';
                }

                if ($key > $leading_item && ($layout == 'creative')) {
                    $output .= '<div class="sppb-col-sm-6 sppb-col-md-4">';
                } elseif ($key > $leading_item && ($layout == 'essential')) {
                    $output .= '<div class="sppb-col-md-12">';
                } elseif ($key > $leading_item && ($layout != 'default')) {
                    $output .= '<div>';
                }


                if ($item->post_format == 'video') {
                    $output .= '<div class="sppb-addon-article video-post ' . $item_type . ' ">';
                } else {
                    $output .= '<div class="sppb-addon-article ' . $item_type . ' ">';
                }

                if (!$hide_thumbnail) {
                    $output .= '<div class="sppb-article-image-wrap">';
                    // social share
                    if ($social_share) {
                        $output .= '<div class="sppb-post-share-social">';
                        $output .= '<span class="share-button"><i class="fa fa-share-square-o"></i></span>';
                        $output .= '<div class="sppb-post-share-social-others">';
                        if (in_array('facebook', $show_socials)) {
                            $output .= '<a class="fa fa-facebook" data-toggle="tooltip" data-placement="top" title="' . JText::_('HELIX_SHARE_FACEBOOK') . '" onClick="window.open(\'http://www.facebook.com/sharer.php?u=' . $social_url . '\',\'Facebook\',\'width=600,height=300,left=\'+(screen.availWidth/2-300)+\',top=\'+(screen.availHeight/2-150)+\'\'); return false;" href="http://www.facebook.com/sharer.php?u=' . $social_url . '"></a>';
                        }
                        if (in_array('twitter', $show_socials)) {
                            $output .= '<a class="fa fa-twitter" data-toggle="tooltip" title="' . JText::_('HELIX_SHARE_TWITTER') . '" onClick="window.open(\'http://twitter.com/share?url=' . $social_url . '&amp;text=' . str_replace(" ", "%20", $item->title) . '\',\'Twitter share\',\'width=600,height=300,left=\'+(screen.availWidth/2-300)+\',top=\'+(screen.availHeight/2-150)+\'\'); return false;" href="http://twitter.com/share?url=' . $social_url . '&amp;text=' . str_replace(" ", "%20", $item->title) . '"></a>';
                        }

                        if (in_array('gplus', $show_socials)) {
                            $output .= '<a class="fa fa-google-plus" data-toggle="tooltip" data-placement="top" title="' . JText::_('HELIX_SHARE_GOOGLE_PLUS') . '" onClick="window.open(\'https://plus.google.com/share?url=' . $social_url . '\',\'Google plus\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="https://plus.google.com/share?url=' . $social_url . '" ></a>';
                        }

                        if (in_array('pinterest', $show_socials)) {
                            $output .= '<a class="fa fa-pinterest" data-toggle="tooltip" data-placement="top" title="' . JText::_('HELIX_SHARE_PINTEREST') . '" onClick="window.open(\'http://pinterest.com/pin/create/button/?url=' . $social_url . '&amp;description=' . $item->title . '\',\'Pinterest\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="http://pinterest.com/pin/create/button/?url=' . $social_url . '&amp;description=' . $item->title . '" ></a>';
                        }

                        if (in_array('linkedin', $show_socials)) {
                            $output .= '<a class="fa fa-linkedin" data-toggle="tooltip" data-placement="top" title="' . JText::_('HELIX_SHARE_LINKEDIN') . '" onClick="window.open(\'http://www.linkedin.com/shareArticle?mini=true&url=' . $social_url . '\',\'Linkedin\',\'width=585,height=666,left=\'+(screen.availWidth/2-292)+\',top=\'+(screen.availHeight/2-333)+\'\'); return false;" href="http://www.linkedin.com/shareArticle?mini=true&url=' . $social_url . '" ></a>';
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
                                $output .= '<img src="' . $gallery_item['thumbnail'] . '" alt="' . $item->title . '">';
                                $output .= '</div>';
                            }
                            $output .= '</div>';

                            $output .= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
                            $output .= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';

                            $output .= '</div>';
                        } elseif (isset($item->image_thumbnail) && $item->image_thumbnail) {
                            $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $image . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
                        }
                    } else {
                        if (isset($item->image_thumbnail) && $item->image_thumbnail && $image) {
                            $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $image . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
                        }
                    }
                    $output .= '</div>'; //.sppb-article-image-wrap
                }

                $output .= '<div class="sppb-article-details">';
                if ($show_category) {
                    $output .= '<span class="sppb-meta-category"><a href="' . $item->catUrl . '" itemprop="genre">' . $item->category . '</a></span>';
                }
                if ($show_author || $show_date) {
                    $output .= '<div class="sppb-article-meta">';
                    if ($show_date) {
                        $output .= '<span class="sppb-meta-date" itemprop="dateCreated">' . Jhtml::_('date', $item->created, 'M d, H:i a') . '</span>';
                    }

                    if ($show_author) {
                        $output .= '<span class="sppb-meta-author" itemprop="name">' . $item->username . '</span>';
                    }
                    $output .= '</div>'; //.sppb-article-meta
                }
                $output .= '<h3 class="sppb-article-title"><a href="' . $item->link . '" itemprop="url">' . $item->title . '</a></h3>';

                if ($key <= $leading_item && $show_leading_intro) {
                    $leading_intro_txt = (strlen($item->introtext) > $leading_intro_limit) ? substr($item->introtext, 0, $leading_intro_limit) : $item->introtext;
                    $output .= '<div class="sppb-article-introtext">' . $leading_intro_txt . '</div>';
                } elseif ($show_intro_item_intro) {
                    $intro_item_intro_txt = (strlen($item->introtext) > $intro_intro_limit) ? substr($item->introtext, 0, $intro_intro_limit) : $item->introtext;
                    $output .= '<div class="sppb-article-introtext">' . $intro_item_intro_txt . '</div>';
                }

                if (($show_readmore) && ($readmore_text != '')) {
                    $output .= '<a class="sppb-readmore" href="' . $item->link . '" itemprop="url">' . $readmore_text . '</a>';
                }
                $output .= '</div>'; //.sppb-article-details

                $output .= '</div>'; //.sppb-addon-article

                if ($key > $leading_item && (($layout != 'default'))) {
                    $output .= '</div>'; // sppb-col inner
                }

                if (($key <= $leading_item && $key == 1 && $layout == 'creative') || ($key == $leading_item && $key > 1 && $layout == 'creative')) {
                    $output .= '</div>'; // sppb-col-sm-8
                }

                if (($key <= $leading_item && $key == 1 && $layout == 'essential') || ($key == $leading_item && $key > 1 && $layout == 'essential')) {
                    $output .= '</div>'; // sppb-col-sm-6
                }

                // if ($key == $total_item && (($layout != 'default'))) {
                //     $output .= '</div>'; // sppb-row
                // }

                if (($key == $leading_item)) {
                    $output .= '</div>'; // leading_item
                } elseif (( $key == $total_item && $layout != 'default')) {
                    $output .= '</div>'; // col-sm
                } elseif (($layout == 'default') || ($layout == 'basic')) {
                    $output .= '</div>'; // col-sm
                }

                $i ++;
            }
            // See all link
            if ($link_articles) {

                if ($resource == 'k2') {
                    $output .= '<a href="' . urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($catid . ':' . urlencode($catid)))) . '" " id="btn-' . $this->addon->id . '" class="sppb-btn">' . $all_articles_btn_text . '</a>';
                } else {
                    $output .= '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($catid)) . '" id="btn-' . $this->addon->id . '" class="sppb-btn">' . $all_articles_btn_text . '</a>';
                }
            }

            if (!$leading_item && ($layout == 'creative' || $layout == 'essential')) {
              $output .= '<div>'; //.sppb-row
            }

            $output .= '</div>'; //.sppb-row
            $output .= '</div>'; //.sppb-addon-content

            $output .= '</div>'; //.sppb-addon-articles

            return $output;
        }

		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' .$this->addon->id;
		$settings = $this->addon->settings;
		$title = (isset($settings->title)) ? $settings->title : '';
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$title_style = (isset($settings->title_margin_top) && $settings->title_margin_top ) ? 'margin-top:' . (int) $settings->title_margin_top . 'px;' : '';
        $title_style .= (isset($settings->title_margin_bottom) && $settings->title_margin_bottom ) ? 'margin-bottom:' . (int) $settings->title_margin_bottom . 'px;' : '';
        $title_style .= (isset($settings->title_text_color) && $settings->title_text_color) ? 'color:' . $settings->title_text_color . ';' : '';
        $title_style .= (isset($settings->title_fontsize) && $settings->title_fontsize) ? 'font-size:' . $settings->title_fontsize . 'px;line-height:' . $settings->title_fontsize . 'px;' : '';
        $title_style .= (isset($settings->title_fontweight) && $settings->title_fontweight) ? 'font-weight:' . $settings->title_fontweight . ';' : '';

        if ($title_style && $title) {
            $css .= $addon_id . ' .sppb-addon-title {';
            $css .= $title_style;
            $css .= '}';
        }
        
		$options = new stdClass;
		$options->button_type = (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? $this->addon->settings->all_articles_btn_type : '';
		$options->button_appearance = (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? $this->addon->settings->all_articles_btn_appearance : '';
		$options->button_color = (isset($this->addon->settings->all_articles_btn_color) && $this->addon->settings->all_articles_btn_color) ? $this->addon->settings->all_articles_btn_color : '';
		$options->button_color_hover = (isset($this->addon->settings->all_articles_btn_color_hover) && $this->addon->settings->all_articles_btn_color_hover) ? $this->addon->settings->all_articles_btn_color_hover : '';
		$options->button_background_color = (isset($this->addon->settings->all_articles_btn_background_color) && $this->addon->settings->all_articles_btn_background_color) ? $this->addon->settings->all_articles_btn_background_color : '';
		$options->button_background_color_hover = (isset($this->addon->settings->all_articles_btn_background_color_hover) && $this->addon->settings->all_articles_btn_background_color_hover) ? $this->addon->settings->all_articles_btn_background_color_hover : '';
		$options->button_fontstyle = (isset($this->addon->settings->all_articles_btn_fontstyle) && $this->addon->settings->all_articles_btn_fontstyle) ? $this->addon->settings->all_articles_btn_fontstyle : '';
		$options->button_letterspace = (isset($this->addon->settings->all_articles_btn_letterspace) && $this->addon->settings->all_articles_btn_letterspace) ? $this->addon->settings->all_articles_btn_letterspace : '';

		return $css_path->render(array('addon_id' => $addon_id, 'options' => $options, 'id' => 'btn-' . $this->addon->id));
	}

	static function isComponentInstalled($component_name){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( 'a.enabled' );
		$query->from($db->quoteName('#__extensions', 'a'));
		$query->where($db->quoteName('a.name')." = ".$db->quote($component_name));
		$db->setQuery($query);
		$is_enabled = $db->loadResult();
		return $is_enabled;
	}

}
